<?php

namespace Pmpr\Plugin\Pmpr\Helper;

use DirectoryIterator;
use Pmpr\Plugin\Pmpr\API;
use Pmpr\Plugin\Pmpr\Component\Item;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;

/**
 * Class Component
 * @package Pmpr\Plugin\Pmpr\Helper
 */
class Component extends Common
{
    const COMPONENT_ = 'component-';

    /**
     * @return bool
     */
    public function isUseTestRepository(): bool
    {
        return $this->getHelper()->getType()->getConstant('PR_PLG_USE_TEST_REPOSITORY', false);
    }

    /**
     * @param string|object|array $component
     *
     * @return Item
     */
    public function getObject($component): Item
    {
        return new Item($component);
    }

    /**
     * @param string $component
     *
     * @return string
     */
    public function getPath(string $component): string
    {
        $path = '';
        if ($basePath = $this->getHelper()->getFile()->getBaseDirPath()) {

            $path = "{$basePath}/component/{$this->getType($component, false)}/{$component}";
        }

        return $path;
    }

    /**
     * @param $type
     *
     * @return string
     */
    public function getCacheKeyByType($type): string
    {
        return "{$type}_component_list";
    }

    /**
     * @return array
     */
    public function getInstalled(): array
    {
        $components = [];
        foreach ($this->getTypes() as $type) {

            $components += $this->getInstalledByType($type);
        }

        return $components;
    }

    /**
     * @return array
     */
    public function getNeedUpdates(): array
    {
        $needUpdates = [];
        $components  = $this->getInstalled();
        foreach ($components as $componentName) {

            $component = $this->getDataAsObject($componentName);
            if ($component->hasUpdate()) {

                $needUpdates[$componentName] = $component;
            }
        }

        return $needUpdates;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getInstalledByType(string $type): array
    {
        $components    = [];
        $componentRoot = $this->getRootPathByType($type);

        if ($this->getHelper()->getFile()->isdir($componentRoot)) {

            $iterator = new DirectoryIterator($componentRoot);
            foreach ($iterator as $file) {

                if ($file->isDir() && !$file->isDot()
                    && $this->isInstalled($file->getFilename())) {

                    $components[$file->getFilename()] = $file->getFilename();
                }
            }
        }

        return $components;
    }

    /**
     * @param $fetchedList
     * @param $type
     *
     * @return void
     */
    public function checkDataByType($type, $fetchedList = [])
    {
        if (!$fetchedList) {

            $fetchedList = [];
            $components  = $this->getInstalledByType($type);
            foreach ($components as $component) {

                if ($fetched = API::getInstance()->getComponent($component, $type)) {

                    $fetchedList[] = $fetched;
                }
            }
        }

        foreach ($fetchedList as $fetched) {

            $typeHelper = $this->getHelper()->getType();

            $name = $typeHelper->arrayGetItem($fetched, Constants::NAME);
            if ($name && ($component = $this->getData($name))) {

                do_action('pmpr_check_component_update', $fetched, $component);
                do_action("pmpr_check_{$type}_component", $fetched, $component);
            }
        }
        do_action("pmpr_check_{$type}_components_update_done");
    }

    /**
     * @param $type
     *
     * @return string
     */
    public function getRootPathByType($type): string
    {
        $type     = $this->sanitizeType($type, false);
        $basePath = $this->getHelper()->getFile()->getBaseDirPath();
        $pathMask = $this->getHelper()->getType()->getConstant('PR_COMPONENT_ROOT_PATH_MASK', "%s/component/{$type}");

        return sprintf($pathMask, $basePath, $type);
    }

    /**
     * @param string $component
     *
     * @return bool
     */
    public function removeData(string $component)
    {
        return delete_option(self::COMPONENT_ . $component);
    }

    /**
     * @param string $component
     * @param array $data
     */
    public function updateData(string $component, array $data)
    {
        if (update_option(self::COMPONENT_ . $component, $data)) {

            $type = $this->getType($component);

            do_action('pmpr_component_updated', $component, $type);

            do_action("pmpr_{$type}_component_updated", $component, $type);

            do_action("pmpr_{$component}_component_updated", $component, $type);
        }
    }

    /**
     * @param $component
     * @param $status
     *
     * @return bool
     */
    public function changeStatus($component, $status): bool
    {
        $data    = $this->getData($component);
        $changed = false;
        if ($data && isset($data[Constants::STATUS])
            && $data[Constants::STATUS] !== $status) {

            $data[Constants::STATUS] = $status;
            $this->updateData($component, $data);
            $changed = true;
        }

        return $changed;
    }

    /**
     * @param $component
     *
     * @return bool
     */
    public function active($component): bool
    {
        return $this->changeStatus($component, Constants::ACTIVE);
    }

    /**
     * @param $component
     *
     * @return bool
     */
    public function deactivate($component): bool
    {
        return $this->changeStatus($component, Constants::INACTIVE);
    }

    /**
     * @param $component
     * @param bool $short
     *
     * @return string
     */
    public function getType($component, bool $short = true): string
    {
        $type = '';
        if (is_string($component)
            && preg_match('/-([^-]+)-/', $component, $matches)) {

            $type = $matches[1] ?? null;
            $type = $this->sanitizeType($type, $short);
        }

        return $type;
    }

    /**
     * @param string $component
     * @param string $output
     *
     * @return false|mixed|Item|null
     */
    public function getData(string $component, string $output = Constants::ARRAY)
    {
        $return = get_option(self::COMPONENT_ . $component, []);

        if (is_array($return) && empty($return[Constants::NAME])) {

            $return[Constants::NAME] = $component;
        }

        if (Constants::OBJECT === $output) {

            $return = $this->getObject($return);
        }

        return $return;
    }

    /**
     * @param string $component
     *
     * @return false|mixed|Item|null
     */
    public function getDataAsObject(string $component)
    {
        return $this->getData($component, Constants::OBJECT);
    }

    /**
     * @param $component
     *
     * @return bool
     */
    public function isInstalled($component): bool
    {
        $installed = false;
        if ($type = $this->getType($component, false)) {

            $path = $this->getRootPathByType($type);
            if ($path) {

                $fs = $this->getHelper()->getFile()->getFilesystem();

                if ($fs) {

                    $path      .= "/{$component}";
                    $installed = $fs->exists($path) && count(scandir($path)) > 2;
                }
            }
        }
        return $installed;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isActive(string $name): bool
    {
        $component = $this->getData($name);
        return $this->isInstalled($name)
            && ($component[Constants::STATUS] ?? '') === Constants::ACTIVE;
    }

    /**
     * @param bool $short
     *
     * @return array
     */
    public function getTypes(bool $short = false): array
    {
        $types = [
            Constants::COMMON  => Constants::CMN,
            Constants::UTILITY => Constants::UTL,
            Constants::MODULE  => Constants::MDL,
            Constants::COVER   => Constants::CVR,
            Constants::CUSTOM  => Constants::CST,
        ];

        if (!$short) {

            $types = array_flip($types);
        }

        return $types;
    }

    /**
     * @param string $type
     * @param bool $short
     *
     * @return string
     */
    public function sanitizeType(string $type, bool $short = true): string
    {
        $types = $this->getTypes($short);

        if (!empty($types[$type])) {

            $type = (string)$types[$type];
        }

        return $type;
    }

    /**
     * @param string $component
     * @param        $data
     */
    public function overwriteData(string $component, $data)
    {
        if ($component) {

            $current = $this->getData($component);
            $data    = wp_parse_args($data, $current);
            $this->updateData($component, $data);
        }
    }

    /**
     * @return string[]
     */
    public function getRequired(): array
    {
        return [
            'wp-cmn-foundation',
        ];
    }

    /**
     * @return bool
     */
    public function isRequiredInstalled(): bool
    {
        static $requiredComponentsInstalled;
        if (null === $requiredComponentsInstalled) {

            $requiredComponentsInstalled = true;
            if ($required = $this->getRequired()) {

                foreach ($required as $component) {

                    if (!$this->isInstalled($component)) {

                        $requiredComponentsInstalled = false;
                        break;
                    }
                }
            } else {

                $requiredComponentsInstalled = false;
            }
        }

        return $requiredComponentsInstalled;
    }

    public function clearCache()
    {
        delete_option(Constants::UPDATE_COUNT);
        $this->getHelper()->getAsset()->clearBuildPath();
    }

    /**
     * @return bool|string|int|null
     */
    public function getUpdateCount()
    {
        return get_option(Constants::UPDATE_COUNT);
    }

    /**
     * @param bool $checkAPI
     * @return bool
     */
    public function isRequirementsSatisfied(bool $checkAPI = true): bool
    {
        $satisfied    = true;
        $typeHelper   = $this->getHelper()->getType();
        $requirements = $this->requirementsNotices();
        foreach ($requirements as $requirement) {

            if ($typeHelper->arrayGetItem($requirement, 'show')) {

                $satisfied = false;
                break;
            }
        }

        if ($satisfied && $checkAPI
            && !$this->getHelper()->getSetting()->getAPIKey()) {

            $satisfied = false;
        }

        return $satisfied;
    }

    /**
     * @param bool $echo
     * @param array $args
     * @return array
     */
    public function requirementsNotices(bool $echo = false, array $args = []): array
    {
        $base = $this->getHelper()->getFile()->getBaseDirPath();
        $settingHelper = $this->getHelper()->getSetting();

        $requirements['writable'] = [
            Constants::SHOW        => !$base || !$this->getHelper()->getFile()->isWritable($base),
            Constants::TEXT        => __('Please contact us.', PR__PLG__PMPR),
            Constants::TITLE       => __('Components Installation Possibility', PR__PLG__PMPR),
            Constants::DESCRIPTION => __('Access to domain root directory is required to complete the installation process.', PR__PLG__PMPR),
        ];

        $requirements['api-key'] = [
            Constants::SHOW        => !$settingHelper->getAPIKey() && !$settingHelper->isSettingPage(),
            Constants::TEXT        => sprintf(
                '%s, %s.',
                __('To complete the installation of PMPR plugin', PR__PLG__PMPR),
                $settingHelper->getLinkElement(__('enter your api key in here', PR__PLG__PMPR))
            ),
            Constants::TYPE        => 'info',
            Constants::TITLE       => __('API Key', PR__PLG__PMPR),
            Constants::DESCRIPTION => __('It\'s required to communicate with the server.', PR__PLG__PMPR),
        ];

        foreach ($requirements as $index => $requirement) {

            $text    = $requirement[Constants::TEXT];
            $desc    = $requirement[Constants::DESCRIPTION];
            $message = sprintf('%s %s', $text, $desc);

            $args[Constants::TYPE] = $requirement[Constants::TYPE] ?? ($args[Constants::TYPE] ?? 'warning');

            if ($echo) {

                if ($requirement['show']) {

                    $this->getHelper()->getHTML()->renderNotice($message, $args);
                } else {

                    unset($requirements[$index]);
                }
            } else {

                $requirements[$index][Constants::TEXT] = $this->getHelper()->getHTML()->createNotice($message, $args);
            }
        }

        return $requirements;
    }

    /**
     * @return string
     */
    public function getCommonsNotice(): string
    {
        $link = $this->getHelper()->getHTML()->createElement('a', [
            'href' => apply_filters('pmpr_plugin_commons_url', ''),
        ], __('here', PR__PLG__PMPR));

        return sprintf(__('Please install commons from %s.', PR__PLG__PMPR), $link);
    }
}
