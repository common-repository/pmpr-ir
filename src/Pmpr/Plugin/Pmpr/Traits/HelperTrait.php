<?php

namespace Pmpr\Plugin\Pmpr\Traits;

use Pmpr\Plugin\Pmpr\Helper\Helper;

/**
 * Trait HelperTrait
 * @package Pmpr\Plugin\Pmpr\Traits
 */
trait HelperTrait
{
    /**
     * @var Helper|null
     */
    public ?Helper $helper = null;

    /**
     * @return Helper
     */
    public function getHelper(): Helper
    {
        if (!$this->helper) {

            $this->helper = new Helper();
        }

        return $this->helper;
    }

}