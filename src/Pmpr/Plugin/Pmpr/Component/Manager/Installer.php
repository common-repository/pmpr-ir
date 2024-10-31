<?php

namespace Pmpr\Plugin\Pmpr\Component\Manager;

use Exception;
use Pmpr\Plugin\Pmpr\Component\Update;
use Pmpr\Plugin\Pmpr\Traits\HelperTrait;
use Pmpr\Plugin\Pmpr\Traits\HookTrait;
use WP_Error;
use WP_Upgrader;

/**
 * Class Installer
 * @package Pmpr\Plugin\Pmpr\Component\Manager
 */
class Installer extends WP_Upgrader
{
    use HookTrait,
        HelperTrait;

    /**
     * Plugin upgrade result.
     *
     * @since 2.8.0
     * @var array|WP_Error $result
     *
     * @see WP_Upgrader::$result
     */
    public $result;

    /**
     * Whether a bulk upgrade/installation is being performed.
     *
     * @since 2.9.0
     * @var bool $bulk
     */
    public $bulk = false;

    /**
     * New plugin info.
     *
     * @since 5.5.0
     * @var array $data
     *
     * @see check_package()
     */
    public $data = [];

    /**
     * @param string $key
     *
     * @return string
     */
    public function getString(string $key): string
    {
        return (string)$this->getHelper()->getType()->arrayGetItem($this->strings, $key, '');
    }

    /**
     * @param string $code
     *
     * @return WP_Error
     */
    public function stringError(string $code): WP_Error
    {
        return new WP_Error($code, $this->getString($code));
    }

    public function generic_strings()
    {
        parent::generic_strings();
        $this->strings['fs_destination_available'] = __('Unable to locate destination directory.', PR__PLG__PMPR);
    }

    public function removeStrings()
    {
        $this->strings['remove_failed']   = __('Component remove process failed.', PR__PLG__PMPR);
        $this->strings['already_removed'] = __('Component already removed.', PR__PLG__PMPR);
    }

    /**
     * Initializes the installation strings.
     *
     * @since 2.8.0
     */
    public function installStrings()
    {
        $this->strings['no_package']               = __('Installation package not available.', PR__PLG__PMPR);
        $this->strings['downloading_package']      = sprintf(__('Downloading installation package from %s&#8230;', PR__PLG__PMPR), '<span class="code pre">%s</span>');
        $this->strings['unpack_package']           = __('Unpacking the component&#8230;', PR__PLG__PMPR);
        $this->strings['installing_package']       = __('Installing the component&#8230;', PR__PLG__PMPR);
        $this->strings['no_files']                 = __('The component contains no files.', PR__PLG__PMPR);
        $this->strings['process_failed']           = __('Component installation failed.', PR__PLG__PMPR);
        $this->strings['process_success']          = __('Component installed successfully.', PR__PLG__PMPR);
        $this->strings['process_success_specific'] = __('Successfully installed the component <strong>%1$s %2$s</strong>.', PR__PLG__PMPR);
    }

    /**
     * Initializes the upgrade strings.
     */
    public function updateStrings()
    {
        $this->strings['up_to_date']           = __('The component is at the latest version.', PR__PLG__PMPR);
        $this->strings['no_package']           = __('Update package not available.', PR__PLG__PMPR);
        $this->strings['downloading_package']  = sprintf(__('Downloading update from %s&#8230;', PR__PLG__PMPR), '<span class="code pre">%s</span>');
        $this->strings['unpack_package']       = __('Unpacking the update&#8230;', PR__PLG__PMPR);
        $this->strings['process_failed']       = __('Component update failed.', PR__PLG__PMPR);
        $this->strings['process_success']      = __('Component updated successfully.', PR__PLG__PMPR);
        $this->strings['process_bulk_success'] = __('Components updated successfully.', PR__PLG__PMPR);
    }

    /**
     * @param string $component
     * @param array $args
     *
     * @return array|bool|string|WP_Error
     */
    private function _run(string $component, array $args = [])
    {
        $package = $args['package'] ?? null;
        if (!$package) {

            $item    = $this->getHelper()->getComponent()->getObject($component);
            $package = $item->getDownloadLink();
        }
        $destination = $this->getHelper()->getComponent()->getPath($component);
        if (!$destination) {

            return $this->stringError('fs_destination_available');
        }

        $args['package']                 = $package;
        $args['destination']             = $destination;
        $args['clear_working']           = true;
        $args['hook_extra']['type']      = 'component';
        $args['hook_extra']['name']      = $component;
        $args['hook_extra']['component'] = $component;

        return $this->run($args);
    }

    public function remove(string $component)
    {
        $fileHelper      = $this->getHelper()->getFile();
        $componentHelper = $this->getHelper()->getComponent();

        $this->init();
        $this->removeStrings();

        $destination = $componentHelper->getPath($component);
        if (!$destination) {

            return $this->stringError('fs_destination_available');
        }

        if (!$fileHelper->exists($destination)) {

            return $this->stringError('already_removed');
        }

        $filesystem = $fileHelper->getFilesystem();

        if (!$filesystem) {

            return $this->stringError('fs_unavailable');
        }

        if (!$filesystem->delete($destination, true)) {

            return $this->stringError('remove_failed');
        }

        return true;
    }

    /**
     * Install a component package.
     *
     * @param string $component
     * @param array $args
     *
     * @return bool|WP_Error True if the installation was successful, false or a WP_Error otherwise.
     */
    public function install(string $component, array $args = [])
    {
        $defaults = [
            'clear_update_cache' => true,
            'overwrite_package'  => false, // Do not overwrite files.
        ];

        $parsedArgs = $this->getHelper()->getType()->parseArgs($args, $defaults);

        $this->init();
        $this->installStrings();

        $this->addFilter('upgrader_source_selection', [$this, 'checkPackage'], 10, 3);

        if ($parsedArgs['clear_update_cache']) {
            // Clear cache so wp_update_plugins() knows about the new plugin.
//            add_action('upgrader_process_complete', 'wp_clean_components_cache', 9, 0);
        }

        $this->_run($component, [
            'package'           => $parsedArgs['package'] ?? null,
            'clear_destination' => $parsedArgs['overwrite_package'],
            'hook_extra'        => [
                'action' => 'install',
            ],
        ]);

//        $this->removeAction('upgrader_process_complete', 'wp_clean_components_cache', 9);
        $this->removeFilter('upgrader_source_selection', [$this, 'checkPackage']);

        if (!$this->result || is_wp_error($this->result)) {

            return $this->result;
        }

        // Force refresh of plugin update information.
        $this->getHelper()->getComponent()->clearCache();
//        wp_clean_components_cache( $parsed_args['clear_update_cache'] );

        if ($parsedArgs['overwrite_package']) {

            /**
             * Fires when the upgrader has successfully overwritten a currently installed
             * plugin or theme with an uploaded zip package.
             *
             * @param string $package The package file.
             * @param array $data The new plugin or theme data.
             * @param string $package_type The package type ('plugin' or 'theme' or 'component').
             */
            $this->doAction('upgrader_overwrote_package', $component, $this->data, 'component');
        }

        return true;
    }

    /**
     * @param array $components
     * @param array $args
     *
     * @return bool|array|WP_Error
     */
    public function updateAll(array $components, array $args = [])
    {
        $defaults = [
            'clear_update_cache' => true,
        ];

        $parsedArgs = $this->getHelper()->getType()->parseArgs($args, $defaults);

//        $this->addFilter('upgrader_clear_destination', [$this, 'deleteOld'], 10, 4);

        $this->init();
        $this->bulk = true;
        $this->updateStrings();

        $result = $this->fs_connect([$this->getHelper()->getFile()->getBaseDirPath()]);

        if (!$result || is_wp_error($result)) {

            return $result;
        }

        $componentHelper = $this->getHelper()->getComponent();

        $maintenance = (is_multisite() && !empty($components));

        foreach ($components as $component) {

            $maintenance = $maintenance || $componentHelper->isActive($component);
        }

        if ($maintenance) {

            $this->maintenance_mode(true);
        }

        $results = [];

        $this->update_count   = count($components);
        $this->update_current = 0;
        foreach ($components as $component) {

            ++$this->update_current;
            $result = $this->getLatestInfo($component);
            if (!is_wp_error($result)) {

                $result = $this->checkRequirementsByComposer($component, $result['prod-require'] ?? [], function ($require) {
                    if (!$this->getHelper()->getComponent()->isInstalled($require)) {

                        return Update::getInstance()->getManager()->install($require);
                    }

                    return true;
                });

                if ($result && !is_wp_error($result)) {

                    $this->addFilter('upgrader_source_selection', [$this, 'checkPackage'], 10, 3);

                    $result = $this->_run($component, [
                        'clear_destination' => true,
                        'is_multi'          => true,
                        'hook_extra'        => [
                            'action'      => 'update',
                            'temp_backup' => [
                                'slug' => $component,
                                'src'  => $componentHelper->getRootPathByType($componentHelper->getType($component)),
                                'dir'  => 'component',
                            ],
                        ],
                    ]);

                    $this->removeFilter('upgrader_source_selection', [$this, 'checkPackage']);
                }
            }

            $results[$component] = $result;

            if (false === $result
                || is_wp_error($result)) {

                break;
            }
        }

        $this->maintenance_mode(false);

        // Force refresh of plugin update information.
        $componentHelper->clearCache();

        $this->doAction(
            'upgrader_process_complete',
            $this,
            [
                'bulk'       => true,
                'type'       => 'component',
                'action'     => 'update',
                'components' => $components,
            ]
        );

        // Cleanup our hooks, in case something else does an upgrade on this connection.
//        $this->removeFilter('upgrader_clear_destination', [$this, 'deleteOld']);

        return $results;

    }

    /**
     * Deactivates a component before it is upgraded.
     *
     * @param bool|WP_Error $response The installation response before the installation has started.
     * @param array $component Plugin package arguments.
     *
     * @return bool|WP_Error The original `$response` parameter or WP_Error.
     */
    public function deactivateBeforeUpdate($response, $component)
    {
        if (is_wp_error($response)) { // Bypass.
            return $response;
        }

        // When in cron (background updates) don't deactivate the plugin, as we require a browser to reactivate it.
        if (wp_doing_cron()) {
            return $response;
        }

        $component = $component['name'] ?? '';
        if (empty($component)) {

            return $this->stringError('bad_request');
        }

        $componentHelper = $this->getHelper()->getComponent();
        if ($componentHelper->isActive($component)) {
            // Deactivate the component silently, Prevent deactivation hooks from running.
            $componentHelper->deactivate($component);
        }

        return $response;
    }

    /**
     * @param $source
     * @param $remote_source
     * @param $extraHooks
     *
     * @return mixed
     */
    public function checkPackage($source, $remote_source, $extraHooks)
    {
        return $source;
    }

    /**
     * Turns on maintenance mode before attempting to background update an active component.
     *
     * @param $response
     * @param $component
     *
     * @return mixed
     */
    public function activeBefore($response, $component)
    {
        if (is_wp_error($response)) {

            return $response;
        }

        // Only enable maintenance mode when in cron (background update).
        if (!wp_doing_cron()) {

            return $response;
        }

        $component = $component['name'] ?? '';

        // Only run if plugin is active.
        if (!$this->getHelper()->getComponent()->isActive($component)) {

            return $response;
        }

        // Change to maintenance mode. Bulk edit handles this separately.
        if (!$this->bulk) {

            $this->maintenance_mode(true);
        }

        return $response;
    }

    /**
     * Turns off maintenance mode after upgrading an active component.
     *
     * @param $response
     * @param $component
     *
     * @return mixed
     */
    public function activeAfter($response, $component)
    {
        if (is_wp_error($response)) {

            return $response;
        }

        // Only disable maintenance mode when in cron (background update).
        if (!wp_doing_cron()) {

            return $response;
        }

        $component = $component['name'] ?? '';

        // Only run if plugin is active.
        if (!$this->getHelper()->getComponent()->isActive($component)) {

            return $response;
        }

        // Time to remove maintenance mode. Bulk edit handles this separately.
        if (!$this->bulk) {

            $this->maintenance_mode();
        }

        return $response;
    }

    /**
     * Deletes the old component during an upgrade.
     *
     * @param bool|WP_Error $removed Whether the destination was cleared.
     *                                          True on success, WP_Error on failure.
     * @param string $local_destination The local package destination.
     * @param string $remote_destination The remote package destination.
     * @param array $component Extra arguments passed to hooked filters.
     *
     * @return bool|WP_Error
     */
    public function deleteOld($removed, $local_destination, $remote_destination, $component)
    {
        if (is_wp_error($removed)) {

            return $removed; // Pass errors through.
        }

        $component = $component['name'] ?? '';

        if (empty($component)) {

            return $this->stringError('bad_request');
        }

        $filesystem = $this->getHelper()->getFile()->getFilesystem();
        if (!$filesystem) {

            return $this->stringError('fs_unavailable');
        }

        $basePath      = $this->getHelper()->getFile()->getBaseDirPath();
        $componentPath = $this->getHelper()->getComponent()->getPath($component);

        if (!$filesystem->exists($componentPath)) { // If it's already vanished.

            return $removed;
        }

        $deleted = false;
        /*
         * If plugin is in its own directory, recursively delete the directory.
         * Base check on if plugin includes directory separator AND that it's not the root plugin folder.
         */
        if ($componentPath !== $basePath && strpos($component, '/')) {

            $deleted = $filesystem->delete($componentPath, true);
        }

        if (!$deleted) {

            return $this->stringError('remove_old_failed');
        }

        return true;
    }

    /**
     * @param string $component
     *
     * @return array|mixed|WP_Error
     */
    public function getLatestInfo(string $component)
    {
        $object   = $this->getHelper()->getComponent()->getObject($component);
        $response = wp_safe_remote_get(add_query_arg(['ver' => time()], $object->getInfoLink()), [
            'timeout' => 30,
        ]);
        if ($response && !is_wp_error($response)) {

            if ($body = wp_remote_retrieve_body($response)) {

                try {

                    $result = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                } catch (Exception $exception) {

                    $result = new WP_Error($exception->getCode(), $exception->getMessage());
                }
            } else {

                $result = new WP_Error('not_found', __('Component information response is empty.', PR__PLG__PMPR));
            }
        } else {

            $result = $response;
        }

        return $result;
    }

    /**
     * @param string $component
     * @param array $requires
     * @param callable $callback
     *
     * @return bool|WP_Error
     */
    public function checkRequirementsByComposer(string $component, array $requires, callable $callback)
    {
        $result = true;

        if ($requires) {

            foreach ($requires as $require => $version) {

                switch ($require) {
                    case 'php':
                        if (!is_php_version_compatible($version)) {

                            $result = new WP_Error(
                                'incompatible_php_required_version', $this->getString('incompatible_archive'),
                                sprintf(__('The PHP version on your server is %1$s, however the uploaded component requires %2$s.', PR__PLG__PMPR), PHP_VERSION, $version)
                            );
                        }
                        break;
                    case 'wp':
                    case 'wordpress':

                        global $wp_version;
                        if (!is_wp_version_compatible($version)) {

                            $result = new WP_Error(
                                'incompatible_wp_required_version', $this->getString('incompatible_archive'),
                                sprintf(__('Your WordPress version is %1$s, however the uploaded component requires %2$s.', PR__PLG__PMPR), $wp_version, $version)
                            );
                        }
                        break;
                    default:
                        if ($require !== $component) {

                            $result = $callback($require);
                        }
                        break;
                }

                if (!$result || is_wp_error($result)) {
                    break;
                }
            }
        }

        return $result;
    }
}