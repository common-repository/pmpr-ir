<?php

namespace Pmpr\Plugin\Pmpr\Component\ListTable\Common;

use Pmpr\Plugin\Pmpr\Component\ListTable\Installed as BaseClass;

/**
 * Class Installed
 * @package Pmpr\Plugin\Pmpr\Component\ListTable\Common
 */
class Installed extends BaseClass
{
    protected function row_actions($actions, $always_visible = false)
    {
        return '';
    }

    protected function get_bulk_actions()
    {
        return [];
    }

    protected function get_views()
    {
        return [];
    }
}