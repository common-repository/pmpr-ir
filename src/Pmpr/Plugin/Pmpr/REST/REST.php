<?php

namespace Pmpr\Plugin\Pmpr\REST;

use Pmpr\Plugin\Pmpr\Container\Container;

/**
 * Class REST
 * @package Pmpr\Plugin\Pmpr\REST
 */
class REST extends Container
{
    public function addActions()
    {
        $this->addAction('rest_api_init', [$this, 'initRests']);
    }

    public function initRests()
    {
        $controller = new Controller();
        $controller->register_routes();
    }
}