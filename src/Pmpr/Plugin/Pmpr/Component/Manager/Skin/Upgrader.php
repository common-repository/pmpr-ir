<?php

namespace Pmpr\Plugin\Pmpr\Component\Manager\Skin;

use Pmpr\Plugin\Pmpr\Traits\HelperTrait;
use WP_Upgrader_Skin;

/**
 * Class Upgrader
 * @package Pmpr\Plugin\Pmpr\Component\Manager\Skin
 */
class Upgrader extends WP_Upgrader_Skin
{
    use HelperTrait;

    /**
     * @var string
     */
    public string $type = '';

    /**
     * @var bool|string
     */
    public $overwrite = '';

    /**
     * Holds the component name in the component directory.
     *
     * @var string
     */
    public string $component = '';

    /**
     * Whether the component is active.
     *
     * @var bool
     */
    public bool $active = false;

    /**
     * @var bool
     */
    private bool $isDowngrading = false;

    /**
     * @param $args
     */
    public function __construct($args = [])
    {
        $defaults = [
            'url'       => '',
            'type'      => '',
            'nonce'     => '',
            'title'     => __('Update Component', PR__PLG__PMPR),
            'action'    => 'web',
            'overwrite' => '',
            'component' => '',
        ];

        $args = wp_parse_args($args, $defaults);

        $this->component = $args['component'];
        $this->overwrite = $args['overwrite'];
        $this->active    = $this->getHelper()->getComponent()->isActive($this->component);
        $this->type      = $args['type'];

        parent::__construct($args);
    }

    /**
     * @param $wp_error
     *
     * @return bool
     */
    public function hide_process_failed($wp_error)
    {
        if ('upload' === $this->type
            && '' === $this->overwrite
            && 'folder_exits' === $wp_error->get_error_code()) {

            return true;
        }

        return false;
    }

    public function after()
    {
        if ($this->doOverwrite()) {
            return;
        }


    }


    /**
     * Checks if the component can be overwritten and outputs the HTML for overwriting a component on upload.
     *
     * @return bool Whether the plugin can be overwritten and HTML was outputted.
     * @since 5.5.0
     *
     */
    private function doOverwrite()
    {
        if ('upload' !== $this->type
            || !is_wp_error($this->result)
            || 'folder_exists' !== $this->result->get_error_code()) {

            return false;
        }

        $componentHelper = $this->getHelper()->getComponent();

        $folder = $this->result->get_error_data('folder_exists');
        $folder = ltrim(substr($folder, strlen($this->getHelper()->getFile()->getBaseDirPath())), '/');


        $componentData = false;
        $allComponents = $componentHelper->getInstalledByType($componentHelper->getType($this->component, false));

        foreach ($allComponents as $component => $data) {

            if (strrpos($component, $folder) !== 0) {
                continue;
            }

            $componentData = $data;
        }

//        $newComponentData = $this->upgrader->data;
//
//        if (!$componentData || !$newComponentData) {
//
//            return false;
//        }

        $HTMLHelper = $this->getHelper()->getHTML();

        $HTMLHelper->renderElement('h2', ['class' => 'update-from-upload-heading'], __('This component is already installed.', PR__PLG__PMPR));

//        $this->isDowngrading = version_compare($componentData['Version'], $newComponentData['Version'], '>');
//
//        $rows = [
//            'Name'        => __('Plugin name'),
//            'Version'     => __('Version'),
//            'Author'      => __('Author'),
//            'RequiresWP'  => __('Required WordPress version'),
//            'RequiresPHP' => __('Required PHP version'),
//        ];
//
//        $table = '<table class="update-from-upload-comparison"><tbody>';
//        $table .= '<tr><th></th><th>' . esc_html_x('Current', 'plugin') . '</th>';
//        $table .= '<th>' . esc_html_x('Uploaded', 'plugin') . '</th></tr>';
//
//        $is_same_plugin = true; // Let's consider only these rows.
//
//        foreach ($rows as $field => $label) {
//            $old_value = !empty($componentData[$field]) ? (string)$componentData[$field] : '-';
//            $new_value = !empty($newComponentData[$field]) ? (string)$newComponentData[$field] : '-';
//
//            $is_same_plugin = $is_same_plugin && ($old_value === $new_value);
//
//            $diff_field   = ('Version' !== $field && $new_value !== $old_value);
//            $diff_version = ('Version' === $field && $this->is_downgrading);
//
//            $table .= '<tr><td class="name-label">' . $label . '</td><td>' . wp_strip_all_tags($old_value) . '</td>';
//            $table .= ($diff_field || $diff_version) ? '<td class="warning">' : '<td>';
//            $table .= wp_strip_all_tags($new_value) . '</td></tr>';
//        }
//
//        $table .= '</tbody></table>';
//
//        /**
//         * Filters the compare table output for overwriting a plugin package on upload.
//         *
//         * @param string $table The output table with Name, Version, Author, RequiresWP, and RequiresPHP info.
//         * @param array $componentData Array with current plugin data.
//         * @param array $newComponentData Array with uploaded plugin data.
//         * @since 5.5.0
//         *
//         */
//        echo apply_filters('install_plugin_overwrite_comparison', $table, $componentData, $newComponentData);
//
//        $install_actions = [];
//        $can_update      = true;
//
//        $blocked_message = '<p>' . esc_html__('The plugin cannot be updated due to the following:') . '</p>';
//        $blocked_message .= '<ul class="ul-disc">';
//
//        $requires_php = isset($newComponentData['RequiresPHP']) ? $newComponentData['RequiresPHP'] : null;
//        $requires_wp  = isset($newComponentData['RequiresWP']) ? $newComponentData['RequiresWP'] : null;
//
//        if (!is_php_version_compatible($requires_php)) {
//            $error = sprintf(
//            /* translators: 1: Current PHP version, 2: Version required by the uploaded plugin. */
//                __('The PHP version on your server is %1$s, however the uploaded plugin requires %2$s.'),
//                PHP_VERSION,
//                $requires_php
//            );
//
//            $blocked_message .= '<li>' . esc_html($error) . '</li>';
//            $can_update      = false;
//        }
//
//        if (!is_wp_version_compatible($requires_wp)) {
//            $error = sprintf(
//            /* translators: 1: Current WordPress version, 2: Version required by the uploaded plugin. */
//                __('Your WordPress version is %1$s, however the uploaded plugin requires %2$s.'),
//                get_bloginfo('version'),
//                $requires_wp
//            );
//
//            $blocked_message .= '<li>' . esc_html($error) . '</li>';
//            $can_update      = false;
//        }
//
//        $blocked_message .= '</ul>';
//
//        if ($can_update) {
//            if ($this->is_downgrading) {
//                $warning = sprintf(
//                /* translators: %s: Documentation URL. */
//                    __('You are uploading an older version of a current plugin. You can continue to install the older version, but be sure to <a href="%s">back up your database and files</a> first.'),
//                    __('https://developer.wordpress.org/advanced-administration/security/backup/')
//                );
//            } else {
//                $warning = sprintf(
//                /* translators: %s: Documentation URL. */
//                    __('You are updating a plugin. Be sure to <a href="%s">back up your database and files</a> first.'),
//                    __('https://developer.wordpress.org/advanced-administration/security/backup/')
//                );
//            }
//
//            echo '<p class="update-from-upload-notice">' . $warning . '</p>';
//
//            $overwrite = $this->is_downgrading ? 'downgrade-plugin' : 'update-plugin';
//
//            $install_actions['overwrite_plugin'] = sprintf(
//                '<a class="button button-primary update-from-upload-overwrite" href="%s" target="_parent">%s</a>',
//                wp_nonce_url(add_query_arg('overwrite', $overwrite, $this->url), 'plugin-upload'),
//                _x('Replace current with uploaded', 'plugin')
//            );
//        } else {
//            echo $blocked_message;
//        }
//
//        $cancel_url = add_query_arg('action', 'upload-plugin-cancel-overwrite', $this->url);
//
//        $install_actions['plugins_page'] = sprintf(
//            '<a class="button" href="%s">%s</a>',
//            wp_nonce_url($cancel_url, 'plugin-upload-cancel-overwrite'),
//            __('Cancel and go back')
//        );
//
//        /**
//         * Filters the list of action links available following a single plugin installation failure
//         * when overwriting is allowed.
//         *
//         * @param string[] $install_actions Array of plugin action links.
//         * @param object $api Object containing WordPress.org API plugin data.
//         * @param array $newComponentData Array with uploaded plugin data.
//         * @since 5.5.0
//         *
//         */
//        $install_actions = apply_filters('install_plugin_overwrite_actions', $install_actions, $this->api, $newComponentData);
//
//        if (!empty($install_actions)) {
//            printf(
//                '<p class="update-from-upload-expired hidden">%s</p>',
//                __('The uploaded file has expired. Please go back and upload it again.')
//            );
//            echo '<p class="update-from-upload-actions">' . implode(' ', (array)$install_actions) . '</p>';
//        }

        return true;
    }
}