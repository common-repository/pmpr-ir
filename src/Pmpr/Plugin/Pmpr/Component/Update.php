<?php

namespace Pmpr\Plugin\Pmpr\Component;

use Pmpr\Plugin\Pmpr\Component\ListTable\Update\Installed;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;

/**
 * Class Update
 * @package Pmpr\Plugin\Pmpr\Component
 */
class Update extends Base
{
    /**
     * Cover constructor.
     */
    public function __construct()
    {
        $this->args = [
            Constants::NAME     => 'update',
            Constants::POSITION => 10,
        ];

        parent::__construct();
    }

    public function setTranslations()
    {
        parent::setTranslations();

        $this->addArg(Constants::PLURAL_NAME, __('Update', PR__PLG__PMPR))
             ->addArg(Constants::SINGULAR_NAME, __('Update', PR__PLG__PMPR));
    }

    public function addActions()
    {
        $this->addAction('admin_init', [$this, 'adminInit'])
             ->addAction(Process::MIDNIGHT_UPDATE_JOB, [$this, 'updateComponents'])
             ->addAction(Process::CHECK_COMPONENTS_UPDATE_JOB, [$this, 'checkComponentsUpdate']);

        parent::addActions();
    }

    public function adminInit()
    {
        Process::getInstance()->scheduleComponentsUpdateCheckerJob();
        if ($this->getUpdateCount() === false) {

            $this->getManager()->storeUpdates();
        }
    }

    public function updateComponents()
    {
        $this->getManager()->updateAll();
    }

    public function checkComponentsUpdate()
    {
        $this->getManager()->checkUpdaets();
    }

    /**
     * @param string $menuTitle
     *
     * @return string
     */
    public function updateMenuTitle(string $menuTitle): string
    {
        if ($count = $this->getUpdateCount()) {

            $menuTitle = sprintf("%s&nbsp;%s", $menuTitle, $this->getHelper()->getHTML()->createBubbleNotification($count));
        }

        return $menuTitle;
    }

    /**
     * @param $context
     *
     * @return array
     */
    public function getParameters($context): array
    {
        $parameters = parent::getParameters($context);

        $listTable = new Installed($this->getArgs());

        $types = [
            Constants::COMMON => [
                Constants::TITLE => __('Commons', PR__PLG__PMPR),
                Constants::COUNT => 0,
            ],
            Constants::MODULE => [
                Constants::TITLE => __('Modules', PR__PLG__PMPR),
                Constants::COUNT => 0,
            ],
            Constants::COVER  => [
                Constants::TITLE => __('Covers', PR__PLG__PMPR),
                Constants::COUNT => 0,
            ],
            Constants::CUSTOM => [
                Constants::TITLE => __('Customs', PR__PLG__PMPR),
                Constants::COUNT => 0,
            ],
        ];

        $hasUpdate       = false;
        $componentHelper = $this->getHelper()->getComponent();
        foreach ($types as $type => $args) {

            $installed = $componentHelper->getInstalledByType($type);
            foreach ($installed as $componentName => $component) {

                $component = $componentHelper->getDataAsObject($component);
                if ($component->hasUpdate()) {

                    $hasUpdate = true;
                    $types[$type][Constants::COUNT]++;
                    $types[$type][Constants::UPDATE][$componentName] = $component;
                }
            }
        }

        $parameters['has_update'] = $hasUpdate;

        $parameters['process'] = Process::getInstance();
        $parameters['table']   = $listTable;
        $parameters['types']   = $types;
        $parameters['masks']   = [
            'updated'  => __('All your %s is updated.', PR__PLG__PMPR),
            'updating' => __('%s are updating..', PR__PLG__PMPR),
        ];

        return $parameters;
    }

    /**
     * @return bool|int|string|null
     */
    public function getUpdateCount()
    {
        return $this->getHelper()->getComponent()->getUpdateCount();
    }
}