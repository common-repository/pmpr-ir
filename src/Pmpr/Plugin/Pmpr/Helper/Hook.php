<?php

namespace Pmpr\Plugin\Pmpr\Helper;

/**
 * Class Hook
 * @package Pmpr\Plugin\Pmpr\Helper
 */
class Hook extends Common
{
    /**
     * @param string $hook
     * @param ...$args
     *
     * @return mixed|null
     */
    public function customApplyFilters(string $hook, ...$args)
    {
        $return = $args[0] ?? null;
        if (function_exists('pmpr_apply_filters')) {

            $return = pmpr_apply_filters($hook, ...$args);
        } else {

            _doing_it_wrong(__FUNCTION__, 'foundation not init', PR__PLG__PMPR__VER);
        }

        return $return;
    }

    /**
     * @param string $hook
     * @param ...$args
     *
     * @return bool
     */
    public function customDoAction(string $hook, ...$args)
    {
        $return = false;
        if (function_exists('pmpr_do_action')) {

            pmpr_do_action($hook, ...$args);
            $return = true;
        } else {

            _doing_it_wrong(__FUNCTION__, 'foundation not init', PR__PLG__PMPR__VER);
        }

        return $return;
    }
}