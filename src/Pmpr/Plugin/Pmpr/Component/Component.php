<?php

namespace Pmpr\Plugin\Pmpr\Component;

use Pmpr\Plugin\Pmpr\Component\Manager\Initiator;
use Pmpr\Plugin\Pmpr\Component\Manager\Preparation;
use Pmpr\Plugin\Pmpr\Container\Container;
use Pmpr\Plugin\Pmpr\Traits\ManagerTrait;

/**
 * Class Component
 * @package Pmpr\Plugin\Pmpr\Component
 */
class Component extends Container
{
    use ManagerTrait;

    /**
     * Component constructor.
     */
    public function __construct()
    {
        $this->loadRequirement();
        parent::__construct();
    }

    public function addActions()
    {
        $this->addAction('admin_init', [$this, 'maybeHandleActions']);
    }

    public function loadRequirement()
    {
        Initiator::getInstance();
        if (is_admin()) {

            if ($this->getHelper()->getTool()->isAjax()) {
                Ajax::getInstance();
            } else {

                Cover::getInstance();
                Custom::getInstance();
                Module::getInstance();
                Common::getInstance();
                Update::getInstance();
                Preparation::getInstance();
            }
        }
    }

    public function maybeHandleActions()
    {
        $serverHelper = $this->getHelper()->getServer();
        if ($action = $serverHelper->getRequest('action')) {

            $components = $serverHelper->getRequest('checked');
            if (is_array($components) && $components) {

                $manager = $this->getManager();
                switch ($action) {
                    case 'activate-selected':
                        foreach ($components as $component) {

                            $manager->activate($component);
                        }
                        break;
                    case 'deactivate-selected':
                        foreach ($components as $component) {

                            $manager->deactivate($component);
                        }
                        break;
                    case 'delete-selected':
                        foreach ($components as $component) {

                            if (!$this->getHelper()->getComponent()->isActive($component)) {

                                $manager->remove($component);
                            }
                        }
                        break;
                }
            }
        }
    }
}