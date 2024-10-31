<?php

namespace Pmpr\Plugin\Pmpr\Component\Manager;

use Exception;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;
use Pmpr\Plugin\Pmpr\Traits\ManagerTrait;

/**
 * Class Initiator
 * @package Pmpr\Plugin\Pmpr\Component\Manager
 */
class Initiator extends Common
{
    use ManagerTrait;

    public function __construct()
    {
        parent::__construct();

        if (isset($_REQUEST['dont-load-components'])) {
            return;
        }
       $this->loadComponents();
    }

    public function loadComponents()
    {
        $configPath = $this->getConfigPath();
        if ($configPath) {

            $configFilepath = "{$configPath}/index.php";
            $fileHelper     = $this->getHelper()->getFile();
            if ($fileHelper->exists($configFilepath)) {
                require_once $configFilepath;
            }

            if ($sld = $this->getHelper()->getType()->getConstant('PR_ENV_SLD')) {

                $functionsFile = "{$configPath}/{$sld}/functions.php";
                if ($fileHelper->exists($functionsFile)) {
                    require_once $functionsFile;
                }
            }

            if ($this->loadCommons()) {

                $this->loadModulesAndCustoms();

                $this->doAction('components_loaded');
            }
        }
    }

    /**
     * @return bool
     */
    public function loadCommons(): bool
    {
        $commons = $this->getHelper()->getComponent()->getInstalledByType(Constants::COMMON);
        foreach ($commons as $component) {

            if (!$this->loadComponent($component)) {

                return false;
            }
        }

        return true;
    }

    public function loadModulesAndCustoms()
    {
        $types = [
            Constants::MODULE,
            Constants::CUSTOM,
        ];

        foreach ($types as $type) {

            $components = $this->getHelper()->getComponent()->getInstalledByType($type);
            foreach ($components as $component) {

                $this->loadComponent($component);
            }
        }
    }

    /**
     * @param string $componentName
     *
     * @return bool
     */
    public function loadComponent(string $componentName): bool
    {
        global $pmpr_loaded_components;

        $loaded = false;

        if (!isset($pmpr_loaded_components[$componentName])) {

            $componentHelper = $this->getHelper()->getComponent();

            $component = $componentHelper->getDataAsObject($componentName);
            $autoload  = $componentHelper->getPath($componentName) . '/vendor/autoload.php';

            if ($component->isInstalled() && $component->isActive()
                && $this->getHelper()->getFile()->exists($autoload)) {

                $requires = (array)$component->getComposerField('prod-require');

                $canLoad = $this->getManager()->getInstaller()->checkRequirementsByComposer($componentName, $requires, function ($require) {

                    return $this->loadComponent($require);
                });

                if ($canLoad && !is_wp_error($canLoad)) {

                    try {

                        include_once $autoload;
                        $loaded                                 = true;
                        $pmpr_loaded_components[$componentName] = true;
                        $this->doAction("component_{$componentName}_loaded");
                    } catch (Exception $ex) {

                    }
                }
            }
        } else {

            $loaded = true;
        }

        return $loaded;
    }
}