<?php

namespace Pmpr\Plugin\Pmpr\Component;

use Pmpr\Plugin\Pmpr\Component\ListTable\Common\Installed;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;

/**
 * Class Common
 * @package Pmpr\Plugin\Pmpr\Component
 */
class Common extends Base
{
    /**
     * Cover constructor.
     */
    public function __construct()
    {
        $this->args = [
            Constants::TYPE     => Constants::CMN,
            Constants::NAME     => Constants::COMMON,
            Constants::POSITION => 0,
        ];

        parent::__construct();
    }

    public function setTranslations()
    {
        parent::setTranslations();

        $this->addArg(Constants::PLURAL_NAME, __('Commons', PR__PLG__PMPR))
             ->addArg(Constants::SINGULAR_NAME, __('Common', PR__PLG__PMPR));
    }

    public function addActions()
    {
        $this->addAction('admin_init', [$this, 'adminInis'])
             ->addAction(Process::INSTALL_REQUIRED_COMMONS, [$this, 'installRequiredComponents']);

        parent::addActions();
    }

    public function addFilters()
    {
        $this->addFilter('pmpr_plugin_commons_url', [$this, 'getPageLink'], 10, 0);

        parent::addFilters();
    }

    public function initListTables()
    {
        $args = $this->getArgs();

        $this->installed = new Installed($args);
    }

    public function adminInis()
    {
        $installRequiredRequest = $this->getHelper()->getServer()->getRequest('pr-install-required');
        if ($installRequiredRequest) {

            $result = $this->installRequiredComponents();
            if (is_string($result) || is_wp_error($result)) {
                $this->addFilter('admin_notices', function () use ($result) {
                    $this->getHelper()->getHTML()->renderNotice($result);
                });
            }
        } else if ($this->getHelper()->getComponent()->isRequirementsSatisfied()
            && !$this->getHelper()->getComponent()->isRequiredInstalled()) {

            Process::getInstance()->addInstallRequiredCommonsAsyncJob();
        }
    }

    public function installRequiredComponents()
    {
        if ($this->getHelper()->getComponent()->isRequirementsSatisfied()) {

            $componentHelper = $this->getHelper()->getComponent();
            if (!$componentHelper->isRequiredInstalled()) {

                $result     = true;
                $components = $componentHelper->getRequired();
                foreach ($components as $component) {

                    $installResult = $this->getManager()->install($component);
                    if (is_wp_error($installResult)) {
                        $result = $installResult;
                        break;
                    }
                }
            } else {
                $result = __('Require components already installed.', PR__PLG__PMPR);
            }
        } else {

            $result = __('Some requirements not provided.', PR__PLG__PMPR);
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function canAddNew(): bool
    {
        return false;
    }
}