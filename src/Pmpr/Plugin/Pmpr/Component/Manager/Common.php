<?php

namespace Pmpr\Plugin\Pmpr\Component\Manager;

use Pmpr\Plugin\Pmpr\Container\Container;

/**
 * Class Common
 * @package Pmpr\Plugin\Pmpr\Component\Manager
 */
abstract class Common extends Container
{
    /**
     * @return string
     */
    public function getConfigPath(): string
    {
        $filepath   = '';
        $fileHelper = $this->getHelper()->getFile();
        $basePath   = $fileHelper->getBaseDirPath();
        if ($fileHelper->isWritable($basePath)) {

            $filepath = "{$basePath}/config";
        }

        return $filepath;
    }
}