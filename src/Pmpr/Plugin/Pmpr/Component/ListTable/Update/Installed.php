<?php

namespace Pmpr\Plugin\Pmpr\Component\ListTable\Update;

use Pmpr\Plugin\Pmpr\Component\Item;
use Pmpr\Plugin\Pmpr\Component\ListTable\ListTable;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;

/**
 * Class Installed
 *
 * @package Pmpr\Plugin\Pmpr\Component\ListTable\Update
 */
class Installed extends ListTable
{
    /**
     * @return array
     */
    public function get_columns()
    {
        return [
            Constants::TITLE       => __('Component', PR__PLG__PMPR),
            Constants::VERSION     => __('Current Version', PR__PLG__PMPR),
            Constants::NEW_VERSION => __('New Version', PR__PLG__PMPR),
        ];
    }

    /**
     * @param array $components
     */
    public function prepare_items(array $components = [])
    {
        $this->items = $components;
    }

    /**
     * @param $item
     *
     * @return string
     */
    public function column_title($item): string
    {
        $html = '';

        if ($item instanceof Item) {

            $HTMLHelper = $this->getHelper()->getHTML();

            $html .= $HTMLHelper->createElement('img', [
                'src'    => $item->getImageURL(),
                'width'  => 64,
                'height' => 64,
            ]);
            $html .= $HTMLHelper->createElement('strong', [], $item->getTitle());
            $html .= $item->getDescription();

            $html = $HTMLHelper->createDiv($html, [
                'class' => 'plugin-title'
            ]);
        }

        return $html;
    }

    /**
     * @param $item
     * @return string
     */
    public function column_version($item): string
    {
        $version = '';
        if ($item instanceof Item) {

            $version = $this->getVersionHTML($item->getVersion('view'));
        }

        return $version;
    }

    /**
     * @param $item
     *
     * @return string
     */
    public function column_new_version($item): string
    {
        $version = '';
        if ($item instanceof Item) {

            $version = $this->getVersionHTML($item->getNewVersion('view'));
        }

        return $version;
    }

    /**
     * @return array
     */
    protected function get_table_classes()
    {
        return ['widefat', 'updates-table', 'plugins', 'components-update'];
    }

    protected function display_tablenav($which)
    {

    }

    /**
     * @param $version
     *
     * @return string
     */
    public function getVersionHTML($version): string
    {
        return $this->getHelper()->getHTML()->createElement('span', [], $version);
    }
}