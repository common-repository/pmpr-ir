<?php

/*
	Plugin Name: Pmpr-IR
	Description: Pmpr official Wordpress plugin. Pmpr development team offers high quality products and services on Wordpress.
	Version: 1.4.0
	Requires PHP: 7.4
	Author: Pmpr Development Team
	Author URI: https://pmpr.ir/
	License: GPLv2
	Text Domain: pmpr-ir
	Domain Path: ./translation
*/

defined('ABSPATH') || exit;
defined('WPINC') || exit;

use Pmpr\Plugin\Pmpr\Pmpr;

require_once __DIR__ . '/vendor/autoload.php';

@define('PR__PLG__PMPR', 'pmpr-ir');
@define('PR__PLG__PMPR__DIR', __DIR__);
@define('PR__PLG__PMPR__VER', '1.4.0');
@define('PR__PLG__PMPR__NAME', plugin_basename(__DIR__) . '/' . basename(__FILE__));


if (!function_exists('call_class_method')) {

    /**
     * @param object $object
     * @param string $method
     *
     * @return mixed
     */
    function call_class_method(object $object, string $method)
    {
        $return = '';
        if (method_exists($object, $method)) {

            $return = $object->$method();
        }
        return $return;
    }
}

if (!function_exists('pmpr_get_plugin_object')) {

    function pmpr_get_plugin_object()
    {
        return Pmpr::getInstance();
    }
}

pmpr_get_plugin_object();