<?php

namespace Pmpr\Plugin\Pmpr\Component\Manager;

/**
 * Class Preparation
 * @package Pmpr\Plugin\Pmpr\Component\Manager
 */
class Preparation extends Common
{
    public function __construct()
    {
        parent::__construct();
        $this->prepareConfigDirectory();
    }

    public function prepareConfigDirectory()
    {
        $fileHelper = $this->getHelper()->getFile();
        if ($fullPath = $fileHelper->templateExists('config')) {

            $path     = $this->getConfigPath();
            $filepath = "{$path}/index.php";
            if ($path && !$fileHelper->exists($filepath)) {

                $fileHelper->mkdir(dirname($filepath));
                $fileHelper->copy($fullPath, $filepath);
            }
        }
    }
}