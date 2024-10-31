<?php

namespace Pmpr\Plugin\Pmpr\Component\ListTable;

use Pmpr\Plugin\Pmpr\Interfaces\Constants;
use WP_Error;

/**
 * Class Install
 * @package Pmpr\Plugin\Pmpr\Component
 */
abstract class Install extends ListTable
{
    /**
     * @var string|null
     */
    public ?string $order = 'ASC';

    /**
     * @var string|null
     */
    public ?string $orderby = null;

    /**
     * @var array
     */
    public array $groups = [];

    /**
     * @var WP_Error|null
     */
    private ?WP_Error $error = null;

    /**
     * @return WP_Error|null
     */
    public function getError(): ?WP_Error
    {
        return $this->error;
    }

    /**
     * @return array
     */
    public function get_columns()
    {
        return [];
    }

    /**
     * @return bool
     */
    public function ajax_user_can(): bool
    {
        return current_user_can('install_plugins');
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getArg(Constants::NAME);
    }

    public function prepare_items()
    {
        // These are the tabs which are shown on the page.
        global $tabs, $tab, $paged, $s;

        wp_reset_vars([Constants::TAB]);

        $perPage = 36;
        $paged   = $this->get_pagenum();
        $name    = $this->getName();
        $tabs    = $this->applyFilters("install_{$name}_tabs", $tabs);

        if (Constants::SEARCH === $tab) {

            $tabs[Constants::SEARCH] = __('Search Results', PR__PLG__PMPR);
            $args[Constants::SEARCH] = $s;
        }

        $tabs[Constants::GENERAL] = __('General', PR__PLG__PMPR);

        $tabs = $this->getTabs($tabs);

        $componentHelper = $this->getHelper()->getComponent();

        if ($componentHelper->isRequiredInstalled()) {

            $args = [
                Constants::PAGE      => $paged,
                Constants::TYPE      => $name,
                Constants::LOCALE    => get_user_locale(),
                Constants::PER_PAGE  => $perPage,
                Constants::DEDICATED => isset($tabs[Constants::DEDICATED]) && Constants::DEDICATED === $tab,
            ];

            $args = $this->applyFilters("install_{$name}_api_{$tab}_args", $args);

            if ($args && $api = $this->getApi()) {

                $result = $api->getComponents($args);
                if ($result && !is_wp_error($result)) {

                    $this->items = $result->items;

                    $componentHelper->checkDataByType($this->getName(), $this->items);

                    $this->set_pagination_args(
                        [
                            'total_items'       => $result->pagination->total,
                            Constants::PER_PAGE => $perPage,
                        ]
                    );
                }
            }
        }
    }

    /**
     * @return array
     * @global string $tab
     *
     * @global array $tabs
     */
    protected function get_views()
    {
        global $tabs, $tab;

        $displayTabs  = [];
        $HTMLHelper   = $this->getHelper()->getHTML();
        $serverHelper = $this->getHelper()->getServer();
        foreach ((array)$tabs as $action => $text) {

            $attrs = [];
            if ($action === $tab) {

                $attrs = [
                    'class'        => 'current',
                    'aria-current' => Constants::PAGE,
                ];
            }
            $attrs['href'] = $serverHelper->getAdminURL([
                Constants::TAB     => $action,
                Constants::PAGE    => $this->getArg(Constants::MENU_SLUG),
                Constants::CONTEXT => Constants::INSTALL,
            ]);

            $displayTabs["{$this->getName()}-install-" . $action] = $HTMLHelper->createElement('a', $attrs, $text);
        }

        return $displayTabs;
    }

    public function get_filters()
    {
        $views = $this->get_views();
        /** This filter is documented in wp-admin/inclues/class-wp-list-table.php */
        $views = apply_filters("views_{$this->screen->id}", $views);

        $this->screen->render_screen_reader_content('heading_views');
        return $this->getHelper()->getHTML()->renderTemplate('install/filter', [
            'views' => $views,
        ], false);
    }

    /**
     * Override parent views so we can use the filter bar display.
     */
    public function views()
    {
        global $tab;

        $plural     = $this->getArg(Constants::PLURAL_NAME);
        $search     = '';
        $HTMLHelper = $this->getHelper()->getHTML();
        if (Constants::DEDICATED !== $tab) {

            $search = $HTMLHelper->renderTemplate('install/search', [
                'args'               => [
                    Constants::TAB     => Constants::SEARCH,
                    Constants::PAGE    => $this->getArg(Constants::MENU_SLUG),
                    Constants::CONTEXT => Constants::INSTALL,
                ],
                'search_label'       => sprintf($this->getArg('search_label'), $plural),
                'search_placeholder' => sprintf($this->getArg('search_placeholder'), $plural),
            ], false);
        }

        $HTMLHelper->renderElement('div', [
            'class' => 'wp-filter',
            'style' => 'margin-bottom: 0;',
        ], $this->get_filters() . $search);
    }

    public function no_items()
    {
        global $tab;

        $singular = $this->getArg(Constants::SINGULAR_NAME);
        if (Constants::DEDICATED === $tab) {

            $message = sprintf(__('No exclusive %s found for your domain.', PR__PLG__PMPR), $singular);
        } else {

            $message = $this->getArg(Constants::NOT_FOUND);
            if (!$message) {

                if (Constants::SEARCH === $tab) {

                    $message = sprintf(__('No %s found. Try a different search.', PR__PLG__PMPR), $singular);
                } else {

                    $message = sprintf(__('No %s found.', PR__PLG__PMPR), $singular);
                }
            }
        }

        $this->getHelper()->getHTML()->renderTemplate('install/not-found', [
            Constants::ERROR     => $this->getError(),
            Constants::NOT_FOUND => $message,
        ]);
    }

    public function display()
    {
        $singular = $this->getArg('singular');
        $dataAttr = '';

        if ($singular) {

            $dataAttr = " data-wp-lists='list:$singular'";
        }

        $this->display_tablenav('top');

        $this->getHelper()->getHTML()->renderTemplate("install/list", [
            'table'     => $this,
            'data_attr' => $dataAttr,
        ]);

        $this->display_tablenav('bottom');
    }

    /**
     * @param string $which
     *
     * @global string $tab
     *
     */
    protected function display_tablenav($which)
    {
        $this->getHelper()->getHTML()->renderTemplate("install/nav", [
            'table' => $this,
            'which' => $which,
        ]);
    }

    public function display_rows()
    {
        $HTMLHelper = $this->getHelper()->getHTML();
        foreach ($this->items as $component) {

            $component = (array)$component;

            $component = $this->getHelper()->getComponent()->getObject($component);
            $HTMLHelper->renderTemplate("install/item", [
                'component' => $component,
            ]);
        }
    }

    /**
     * @param $tabs
     * @return mixed
     */
    public function getTabs($tabs)
    {
        return $tabs;
    }
}