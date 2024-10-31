<?php

namespace Pmpr\Plugin\Pmpr;

use Exception;
use Pmpr\Plugin\Pmpr\Component\Manager\Installer;
use Pmpr\Plugin\Pmpr\Container\Container;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;
use WP_Ajax_Upgrader_Skin;
use WP_Error;

/**
 * Class Manager
 * @package Pmpr\Plugin\Pmpr
 */
class Manager extends Container
{
    /**
     * @var string|null
     */
    protected ?string $basePath = null;

    /**
     * @var Installer|null
     */
    protected ?Installer $installer = null;

    /**
     * @param $skin
     *
     * @return Installer
     */
    public function getInstaller($skin = null): Installer
    {
        if (!$this->installer) {

            if (!class_exists('WP_Upgrader')) {

                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            }

            if ($skin === null) {
                $skin = new WP_Ajax_Upgrader_Skin();
            }
            $this->installer = new Installer($skin);
        }

        return $this->installer;
    }

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->basePath = $this->getHelper()->getFile()->getBaseDirPath();
        parent::__construct();
    }

    /**
     * @return string|null
     */
    public function getBasePath(): ?string
    {
        return $this->basePath;
    }

    /**
     * @param string $component
     * @param array $args
     *
     * @return array|bool|WP_Error
     */
    public function install(string $component, array $args = [])
    {
        $this->updateOption($component, Constants::INACTIVE, true);

        $result = true;

        if (!isset($args['check_requirements'])
            || $args['check_requirements']) {

            $result = $this->checkRequirements($component);
        }

        if ($result && !is_wp_error($result)) {

            $result = $this->getInstaller()->install($component, $args);
            if ($result && !is_wp_error($result)) {

                $this->afterChange($component);
            }
        }

        return $result;
    }

    /**
     * @return array|bool|WP_Error
     */
    public function updateAll()
    {
        $this->checkUpdaets();
        $components = $this->getHelper()->getComponent()->getNeedUpdates();
        return $this->getInstaller()->updateAll(array_keys($components));
    }

    /**
     * @param string $component
     *
     * @return bool|WP_Error
     */
    public function remove(string $component)
    {
        $this->updateOption($component, Constants::INACTIVE, true);
        return $this->getInstaller()->remove($component);
    }

    /**
     * @param string $component
     */
    public function activate(string $component)
    {
        $this->updateOption($component, Constants::ACTIVE);
        $this->afterChange($component);
    }

    /**
     * @param string $component
     */
    public function deactivate(string $component)
    {
        $this->updateOption($component, Constants::INACTIVE);
        $this->afterChange($component);
    }

    /**
     * @param string $component
     *
     * @return array|true|WP_Error
     */
    public function checkRequirements(string $component)
    {
        $result = $this->getInstaller()->getLatestInfo($component);
        if (!is_wp_error($result)) {

            $requires = $result['prod-require'] ?? [];

            $result = $this->getInstaller()->checkRequirementsByComposer($component, $requires, function ($require) {

                $result = true;
                if (!$this->getHelper()->getComponent()->isInstalled($require)) {

                    $result = $this->install($require);
                } else {

                    // check need update
                }
                return $result;
            });
        }

        return $result;
    }

    /**
     * @param string $component
     * @param string $status
     * @param bool $force
     */
    public function updateOption(string $component, string $status, bool $force = false)
    {
        if ($component) {

            $componentHelper = $this->getHelper()->getComponent();

            $data = $componentHelper->getData($component);
            if (!$data || $force) {

                $data = API::getInstance()->getComponent($component);
            }
            if (is_wp_error($data)) {

                if ($componentHelper->isInstalled($component)) {

                    $data = [
                        Constants::NAME  => $component,
                        Constants::TITLE => $component,
                    ];
                } else {

                    $data = [];
                }
            }
            if ($data) {

                $data               = (array)$data;
                $data[Constants::STATUS] = $status;
                $componentHelper->updateData($component, $data);
            }
        }
    }

    public function checkUpdaets()
    {
        $componentHelper = $this->getHelper()->getComponent();

        $components = $componentHelper->getInstalled();
        foreach ($components as $component) {

            $info            = $this->getInstaller()->getLatestInfo($component);
            $componentObject = $componentHelper->getObject($component);
            $versionKey = $componentHelper->isUseTestRepository() ? 'timestamp' : 'tag';
            if (!is_wp_error($info) && !empty($info[$versionKey]) && $componentObject->hasUpdate($info[$versionKey])) {

                $this->fetchAndStoreComponentData($component, [Constants::NEW_VERSION => $info[$versionKey]]);
            }
        }

        $this->storeUpdates();
    }

    public function storeUpdates()
    {
        update_option(Constants::UPDATE_COUNT, count($this->getHelper()->getComponent()->getNeedUpdates()));
    }

    /**
     * @return bool|WP_Error
     */
    public function fetchAndStoreAPIKey()
    {
        if (empty($this->getHelper()->getSetting()->getAPIKey())) {

            $authKey = '';
            if ($fs = $this->getHelper()->getFile()->getFilesystem()) {

                $rootPath = trailingslashit(ABSPATH);

                $files    = glob($rootPath . 'pmpr-*.txt');
                $filaPath = $files[0] ?? '';

                if (!$filaPath || !$fs->exists($filaPath)) {

                    // fetch api key
                    $string = $this->getHelper()->getType()->getConstant('AUTH_SALT');

                    if (!$string) {

                        try {

                            $randomNumber = random_int(10, 200000);
                        } catch (Exception $exception) {

                            $randomNumber = 10;
                        }
                        $string = home_url() . $randomNumber;
                    }

                    $authKey  = sanitize_file_name(md5($string));
                    $filename = sprintf('pmpr-%s.txt', $authKey);

                    if ($fs->put_contents(trailingslashit($rootPath) . $filename, $authKey)) {

                        update_option(Constants::CONNECTION_AUTH_KEY, $authKey);
                    }
                } else {

                    $authKey = $fs->get_contents($filaPath);

                    if (empty($authKey)) {

                        $fs->put_contents($filaPath, $authKey);
                    }

                    if (get_option(Constants::CONNECTION_AUTH_KEY, '') !== $authKey) {

                        update_option(Constants::CONNECTION_AUTH_KEY, $authKey);
                    }
                }
            }

            if ($authKey) {

                $apikey = $this->getApi()->fetchAPIKey($authKey);
                if ($apikey && !is_wp_error($apikey)) {

                    Setting::getInstance()->saveOption(Setting::APIKEY, $apikey);
                } else if (is_wp_error($apikey)) {

                    return $apikey;
                } else {

                    return new WP_Error('api_key_empty', __('Fetched api key is empty.', PR__PLG__PMPR));
                }
            } else {

                return new WP_Error('auth_key_not_found', __('Can not generate auth key file.', PR__PLG__PMPR));
            }
        }

        return true;
    }

    /**
     * @param $component
     *
     * @return mixed
     */
    public function getComponentData($component)
    {
        $componentHelper = $this->getHelper()->getComponent();

        $componentData = $componentHelper->getData($component);
        if (!$componentData || (is_array($componentData) && count($componentData) < 5)) {

            $this->fetchAndStoreComponentData($component, $componentData);
        }

        return $componentData;
    }

    /**
     * @param $component
     * @param array $extraData
     *
     * @return mixed
     */
    public function fetchAndStoreComponentData($component, array $extraData = [])
    {
        $componentData = $this->getHelper()->getComponent()->getData($component);
        if ($api = $this->getApi()) {

            // Do not apply markup/translate as it will be cached.
            $fetchedData = $api->getComponent($component);
            if ($componentData && !is_wp_error($fetchedData)) {

                $extraData[Constants::STATUS] = $this->getHelper()->getType()->arrayGetItem($componentData, Constants::STATUS, 'inactive');

                $fetchedData = array_merge((array)$fetchedData, $extraData);

                $this->getHelper()->getComponent()->updateData($component, $fetchedData);

                $componentData = $fetchedData;
            } else if ($extraData) {

                $this->getHelper()->getComponent()->overwriteData($component, $extraData);
            }
        }
        return $componentData;
    }

    /**
     * @param string $type
     *
     * @return array|null
     */
    public function getInstalledCachedDataByType(string $type): ?array
    {
        $typeHelper      = $this->getHelper()->getType();
        $componentHelper = $this->getHelper()->getComponent();

        $components     = [];
        $componentRoot  = $componentHelper->getRootPathByType($type);
        $componentFiles = $componentHelper->getInstalledByType($type);
        if ($componentFiles) {

            foreach ($componentFiles as $componentFile) {

                $componentData = [];
                if (is_readable("{$componentRoot}/{$componentFile}")) {

                    $componentData = $this->getComponentData($componentFile);

                    $title = $description = '';
                    if ($componentData && !is_wp_error($componentData)) {


                        if (is_object($componentData)) {

                            $componentData = (array)$componentData;
                        }

                        if (get_user_locale() === 'fa_IR') {

                            $title       = $typeHelper->arrayGetItem($componentData, 'fa_title');
                            $description = $typeHelper->arrayGetItem($componentData, 'fa_description');
                        } else {

                            $title       = $typeHelper->arrayGetItem($componentData, 'en_title');
                            $description = $typeHelper->arrayGetItem($componentData, 'en_description');
                        }
                    } else {

                        $componentData = [
                            Constants::NAME => $componentFile,
                        ];
                    }

                    if (!$title) {

                        $title       = $componentFile;
                        $description = $componentFile;
                    }

                    $componentData[Constants::TITLE]       = $title;
                    $componentData[Constants::DESCRIPTION] = $description;
                }
                if ($componentData) {

                    if (!isset($componentData[Constants::NAME])) {

                        $componentData[Constants::NAME] = $componentFile;
                    }
                    $components[$componentFile] = $componentData;
                }
            }
            if ($components) {

                uasort($components, [$this, 'sortNames']);
            }
        }

        return $components;
    }

    /**
     * @param $a
     * @param $b
     *
     * @return int
     */
    public function sortNames($a, $b): int
    {
        $typeHelper = $this->getHelper()->getType();

        $aName = $typeHelper->arrayGetItem($a, Constants::NAME);
        $bName = $typeHelper->arrayGetItem($b, Constants::NAME);

        return strnatcasecmp($aName, $bName);
    }

    /**
     * @param string $component
     */
    protected function afterChange(string $component)
    {
        $type = $this->getHelper()->getComponent()->getType($component);

        $this->doAction('pmpr_component_changed', $component, $type);

        $this->doAction("pmpr_{$type}_component_changed", $component);

        $this->doAction("pmpr_{$component}_component_changed");
    }
}