<?php

namespace Pmpr\Plugin\Pmpr\Component\ListTable;

use Pmpr\Plugin\Pmpr\Component\Item;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;

/**
 * Class Installed
 * @package Pmpr\Plugin\Pmpr\Component\ListTable
 */
abstract class Installed extends ListTable
{
    /**
     * @var bool
     */
    protected bool $show_autoupdates = false;

    /**
     * Installed constructor.
     *
     * @param array $args
     */
    public function __construct($args = [])
    {
        global $status;

        $status = $this->getHelper()->getServer()->getRequest(Constants::STATUS, Constants::ALL);

        $statuses = ['active', 'inactive', 'search', 'paused'];
        if (in_array($status, $statuses, true)) {

            $_SERVER['REQUEST_URI'] = add_query_arg('status', wp_unslash($status));
        }

        parent::__construct($args);
    }

    /**
     * @return array
     */
    public function get_columns()
    {
        $columns = [];
        if ($this->get_bulk_actions()) {

            $columns['cb'] = '<input type="checkbox" />';
        }

        $columns = array_merge($columns, [
            Constants::TITLE       => $this->getArg(Constants::SINGULAR_NAME),
            Constants::DESCRIPTION => __('Description', PR__PLG__PMPR),
        ]);

        if ($this->getArg(Constants::AUTO_UPDATE)) {

            $columns['auto-updates'] = __('Automatic Updates', PR__PLG__PMPR);
        }

        return $columns;
    }

    /**
     * @return array
     * @global string $status
     * @global array $totals
     */
    protected function get_views()
    {
        global $totals, $status, $s;

        $links = [];
        foreach ($totals as $type => $count) {
            if (!$count) {
                continue;
            }

            $text = '';
            $mask = '%s <span class="count">(%s)</span>';
            switch ($type) {
                case 'all':

                    $text = sprintf($mask, __('All', PR__PLG__PMPR), $count);
                    break;
                case 'active':

                    $text = sprintf($mask, __('Active', PR__PLG__PMPR), $count);
                    break;
                case 'inactive':

                    $text = sprintf($mask, __('Inactive', PR__PLG__PMPR), $count);
                    break;
            }

            if ('search' !== $type) {

                $args = [
                    Constants::PAGE   => $this->getArg(Constants::MENU_SLUG),
                    Constants::STATUS => $type,
                ];

                if ($s) {

                    $args['s'] = $s;
                }
                $url          = $this->getHelper()->getServer()->getAdminURL($args);
                $links[$type] = sprintf(
                    "<a href='%s'%s>%s</a>", $url,
                    ($type === $status) ? ' class="current" aria-current="page"' : '',
                    sprintf($text, number_format_i18n($count))
                );
            }
        }

        return $links;
    }

    /**
     * @return array
     */
    protected function get_table_classes()
    {
        return ['widefat', 'plugins', $this->_args['plural']];
    }

    /**
     * @global array $plugins
     */
    public function no_items()
    {
        global $components, $s;

        $plural = $this->getArg('plural_name');
        if (!empty($s)) {

            printf(__('No %s found for: %s.', PR__PLG__PMPR), $plural, "<strong>{$s}</strong>");
            if (!is_multisite() && current_user_can('install_plugins')) {

                $this->getHelper()->getHTML()->renderElement('a', [
                    'href' => $this->getHelper()->getServer()->getAdminURL([
                        's'           => urlencode($s),
                        Constants::TAB     => Constants::SEARCH,
                        Constants::PAGE    => $this->getArg(Constants::MENU_SLUG),
                        Constants::CONTEXT => Constants::INSTALL,
                    ]),
                ], sprintf(__('Search for %s in the Pmpr Modules Directory.', PR__PLG__PMPR), $this->getArg(Constants::PLURAL_NAME)));
            }

        } else if (!empty($components['all'])) {

            printf(__('No %s found.', PR__PLG__PMPR), $plural);
        } else {

            printf(__('No %s are currently available.', PR__PLG__PMPR), $plural);
        }
    }

    /**
     * @global string $status
     * @global array $plugins
     * @global array $totals
     * @global int $page
     * @global string $orderby
     * @global string $order
     * @global string $s
     */
    public function prepare_items()
    {
        global $status, $components, $totals, $page, $orderby, $order, $s;

        wp_reset_vars(['orderby', 'order']);

        $allComponents = $this->getInstalled();

        $components = [
            Constants::ALL      => $allComponents,
            Constants::SEARCH   => [],
            Constants::ACTIVE   => [],
            Constants::INACTIVE => [],
        ];

        $screen = $this->screen;

        if ($s) {

            $status                   = Constants::SEARCH;
            $components[Constants::SEARCH] = array_filter($components[Constants::ALL], [$this, '_search_callback']);
        }

        $typeHelper = $this->getHelper()->getType();
        foreach ($components[Constants::ALL] as $data) {

            $componentStatus = $typeHelper->arrayGetItem($data, Constants::STATUS);
            if (isset($components[$componentStatus])) {

                $components[$componentStatus][] = $data;
            }
        }

        $totals = [];
        foreach ($components as $type => $list) {

            $totals[$type] = count($list);
        }

        if (empty($components[$status])
            && !in_array($status, [Constants::ALL, Constants::SEARCH], true)) {

            $status = Constants::ALL;
        }

        $total       = $totals[$status];
        $this->items = $components[$status];
        if (!$orderby) {

            $orderby = Constants::NAME;
        } else {

            $orderby = ucfirst($orderby);
        }

        $order = strtoupper($order);

        uasort($this->items, [$this, '_order_callback']);

        $perPage = $this->get_items_per_page(str_replace('-', '_', $screen->id . '_per_page'), 999);

        $start = ($page - 1) * $perPage;

        if ($total > $perPage) {

            $this->items = array_slice($this->items, $start, $perPage);
        }

        $this->set_pagination_args(
            [
                'total_items'  => $total,
                Constants::PER_PAGE => $perPage,
            ]
        );
    }

    /**
     * @param array $item
     *
     * @global int $page
     * @global string $s
     * @global array $totals
     *
     * @global string $status
     */
    public function single_row($item)
    {
        static $componentIDs = [];

        $componentData  = $this->getHelper()->getComponent()->getObject($item);
        $componentName  = $componentData->getName();
        $componentTitle = $componentData->getTitle();

        $componentID = $componentSlug = $componentName;

        // Ensure the ID attribute is unique.
        $suffix = 2;
        while (in_array($componentID, $componentIDs, true)) {

            $componentID = "{$componentSlug}-{$suffix}";
            $suffix++;
        }

        $componentIDs[] = $componentID;

        // Pre-order.
        $actions = [
            'deactivate' => '',
            'activate'   => '',
            'details'    => '',
            'delete'     => '',
        ];

        $isActive = $componentData->isActive();
        if ($isActive) {

            if (current_user_can('deactivate_plugin', $componentName)) {

                $actions['deactivate'] = $this->createAction(__('Deactivate', PR__PLG__PMPR), 'deactivate', $componentData);
            }
        } else {
            if (current_user_can('activate_plugin', $componentName)) {

                $actions['activate'] = $this->createAction(__('Activate', PR__PLG__PMPR), 'activate', $componentData);
            }

            if (!is_multisite() && current_user_can('delete_plugins')) {

                $actions['delete'] = $this->createAction(__('Delete', PR__PLG__PMPR), 'delete', $componentData);
            }
        }

        $class       = $isActive ? 'active' : 'inactive';
        $actions     = array_filter($actions);
        $checkbox_id = 'checkbox_' . md5($componentName);

        $checkbox = sprintf(
            '<label class="screen-reader-text" for="%1$s">%2$s</label>' .
            '<input type="checkbox" name="checked[]" value="%3$s" id="%1$s" />',
            $checkbox_id,
            sprintf(__('Select %s', PR__PLG__PMPR), $componentName),
            esc_attr($componentName)
        );

        if ($componentData->hasUpdate()) {

            $class .= " update";
        }

        [$columns] = $this->get_column_info();
        $actions = $this->applyFilters("pmpr_plg_{$componentName}_row_actions", $actions);
        $cells   = [];

        $HTMLHelper = $this->getHelper()->getHTML();

        foreach ($columns as $name => $title) {
            switch ($name) {
                case 'cb':
                    $cells[] = $HTMLHelper->createElement('th', [
                        'class' => 'check-column',
                        'scope' => 'row',
                    ], $checkbox);
                    break;
                case 'title':
                    $cells[] = $HTMLHelper->createElement('td', [
                        'class' => 'plugin-title column-primary',
                    ], $HTMLHelper->createStrong($componentTitle) . $this->row_actions($actions, true));
                    break;
                case 'auto-updates':
                    $cells[] = $HTMLHelper->createElement('td', [
                        'class' => 'column-auto-update',
                    ], __('Coming Soon', PR__PLG__PMPR));
                    break;
                case 'description':
                    $metas       = [];
                    $description = $componentData->getDescription();

                    if ($version = $componentData->getVersion('view')) {

                        $metas[] = sprintf(__('Version %s', PR__PLG__PMPR), $version);
                    }

                    if ($permalink = $componentData->getPermalink()) {

                        $metas[] = $HTMLHelper->createElement('a', [
                            'href' => $permalink,
                        ], __('View Details', PR__PLG__PMPR));
                    }

                    $cells[] = $HTMLHelper->createElement('td', [
                        'class' => "column-description desc",
                    ], [
                        $HTMLHelper->createDiv($description, ['class' => 'plugin-description']),
                        $HTMLHelper->createDiv(implode(' | ', $metas), ['class' => 'second plugin-version-author-uri']),
                    ]);
                    break;
                default:
                    $cells[] = $HTMLHelper->createElement('td', [
                        'class' => "{$name} column-{$name} {$class}",
                    ]);
            }
        }

        $HTMLHelper->renderElement('tr', [
            'class'       => $class,
            'data-slug'   => $componentSlug,
            'data-plugin' => $componentName,
        ], $cells);

        $type = $this->getType();

        $this->doAction("after_{$type}_row", $componentName, $componentData);

        $this->doAction("after_{$type}_row_{$componentSlug}", $componentName, $componentData);
    }

    /**
     * @return array
     */
    protected function get_sortable_columns()
    {
        return [];
    }

    /**
     * @param array $component
     *
     * @return bool
     * @global string $s URL encoded search term.
     *
     */
    public function _search_callback($component)
    {
        global $s;

        foreach ($component as $value) {

            if (is_string($value)
                && false !== stripos(strip_tags($value), urldecode($s))) {

                return true;
            }
        }

        return false;
    }

    /**
     * Displays the search box.
     *
     * @param string $text The 'submit' button label.
     * @param string $input_id ID attribute value for the search input field.
     *
     * @since 4.6.0
     *
     */
    public function search_box($text, $input_id)
    {
        $serverHelper = $this->getHelper()->getServer();

        if (!empty($serverHelper->getRequest('s'))
            || $this->has_items()) {

            $this->getHelper()->getHTML()->renderTemplate('installed/search', [
                'args'     => [
                    'orderby'  => sanitize_text_field($serverHelper->getRequest('orderby') ?? ''),
                    'order'    => sanitize_text_field($serverHelper->getRequest('order') ?? ''),
                    Constants::PAGE => $this->getArg(Constants::MENU_SLUG),
                ],
                'text'     => $text,
                Constants::TYPE => $this->getArg(Constants::NAME),
                Constants::NAME => $this->getArg(Constants::SINGULAR_NAME),
                'input_id' => $input_id . '-search-input',
            ]);
        }
    }

    protected function get_primary_column_name()
    {
        return Constants::TITLE;
    }

    /**
     * @param array $plugin_a
     * @param array $plugin_b
     *
     * @return int
     * @global string $order
     * @global string $orderby
     */
    public function _order_callback($plugin_a, $plugin_b)
    {
        global $orderby, $order;

        $typeHelper = $this->getHelper()->getType();

        $a = $typeHelper->arrayGetItem($plugin_a, $orderby);
        $b = $typeHelper->arrayGetItem($plugin_b, $orderby);

        if ($a === $b) {

            return 0;
        }

        return 'DESC' === $order ? strcasecmp($b, $a) : strcasecmp($a, $b);
    }

    /**
     * @param bool $echo
     *
     * @return false|string
     */
    public function search_result(bool $echo = true)
    {
        return $this->getHelper()->getHTML()->renderTemplate('installed/search_result', [], $echo);
    }

    /**
     * @return array
     * @global string $status
     */
    protected function get_bulk_actions()
    {
        global $status;

        $actions = [];

        if ('active' !== $status) {
            $actions['activate-selected'] = $this->screen->in_admin('network') ? __('Network Activate', PR__PLG__PMPR) : __('Activate', PR__PLG__PMPR);
        }

        if ('inactive' !== $status && 'recent' !== $status) {
            $actions['deactivate-selected'] = $this->screen->in_admin('network') ? __('Network Deactivate', PR__PLG__PMPR) : __('Deactivate', PR__PLG__PMPR);
        }

        if (!is_multisite() || $this->screen->in_admin('network')) {

            if ('active' !== $status && current_user_can('delete_plugins')) {
                $actions['delete-selected'] = __('Delete', PR__PLG__PMPR);
            }

            if ($this->show_autoupdates) {

                if ('auto-update-enabled' !== $status) {
                    $actions['enable-auto-update-selected'] = __('Enable Auto-updates', PR__PLG__PMPR);
                }
                if ('auto-update-disabled' !== $status) {
                    $actions['disable-auto-update-selected'] = __('Disable Auto-updates', PR__PLG__PMPR);
                }
            }
        }

        return $actions;
    }

    /**
     * @param string $title
     * @param string $job
     * @param Item $item
     * @param array $args
     *
     * @return string
     */
    public function createAction(string $title, string $job, Item $item, $args = []): string
    {
        $args = $this->getHelper()->getType()->parseArgs($args, [
            'attrs'       => [
                'href'  => '#',
                'class' => 'pr-installed-action' . ((!isset($args['reload']) || $args['reload']) ? ' pr-component-reload' : ''),
            ],
            Constants::JOB     => $job,
            Constants::NAME    => $item->getName(),
            Constants::TYPE    => $item->getType(),
            Constants::SLUG    => $item->getSlug(),
            Constants::TITLE   => $title,
            Constants::ELEMENT => 'a',
        ]);

        return $this->getHelper()->getHTML()->createAction($args);
    }
}