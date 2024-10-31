<?php

namespace Pmpr\Plugin\Pmpr\Component\ListTable;

use Pmpr\Plugin\Pmpr\Interfaces\Constants;
use Pmpr\Plugin\Pmpr\Traits\APITrait;
use Pmpr\Plugin\Pmpr\Traits\HelperTrait;
use Pmpr\Plugin\Pmpr\Traits\HookTrait;
use Pmpr\Plugin\Pmpr\Traits\ManagerTrait;
use WP_List_Table;

/**
 * Class ListTable
 * @package Pmpr\Plugin\Pmpr\Component
 */
abstract class ListTable extends WP_List_Table
{
    use APITrait,
        HookTrait,
        HelperTrait,
        ManagerTrait;

    /**
     * Install constructor.
     *
     * @param array $args
     */
    public function __construct($args = [])
    {
        $args['screen'] = "{$this->getArg(Constants::MENU_SLUG)}-{$this->getArg(Constants::NAME)}";

        global $page, $s;
        if (isset($_REQUEST['s'])) {

            $s                      = $this->getHelper()->getServer()->getRequest('s');
            $_SERVER['REQUEST_URI'] = add_query_arg('s', wp_unslash($s));
        }

        $page = $this->get_pagenum();

        parent::__construct($args);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return (string)$this->getArg(Constants::TYPE, '');
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getArg($key)
    {
        return $this->getHelper()->getType()->arrayGetItem($this->_args, $key, false);
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getInstalled(string $type = ''): array
    {
        if (!$type) {

            $type = $this->getType();
        }

        return $this->getManager()->getInstalledCachedDataByType($type);
    }
}