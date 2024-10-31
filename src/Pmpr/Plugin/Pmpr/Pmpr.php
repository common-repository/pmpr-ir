<?php

namespace Pmpr\Plugin\Pmpr;

use Exception;
use Pmpr\Plugin\Pmpr\Component\Component;
use Pmpr\Plugin\Pmpr\Component\Update;
use Pmpr\Plugin\Pmpr\Container\Container;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;
use Pmpr\Plugin\Pmpr\REST\REST;
use Pmpr\Plugin\Pmpr\Traits\ManagerTrait;

/**
 * Class Pmpr
 * @package Pmpr\Plugin\Pmpr
 */
class Pmpr extends Container
{
    use ManagerTrait;

    const ACTIVE_COMPONENTS = Constants::PLUGIN_PREFIX . 'active_components';

    /**
     * Pmpr constructor.
     */
    public function __construct()
    {
        register_activation_hook(PR__PLG__PMPR__NAME, [$this, 'activate']);
        register_deactivation_hook(PR__PLG__PMPR__NAME, [$this, 'deactivate']);
        parent::__construct();
        $this->loadRequirement();
    }

    public function addActions()
    {
        $this
            ->addAction('admin_menu', [$this, 'adminMenu'])
            ->addAction('admin_notices', [$this, 'adminNotice'])
            ->addAction('plugins_loaded', [$this, 'pluginsLoaded'])
            ->addAction('admin_enqueue_scripts', [$this, 'enqueue'])
            ->addAction('admin_head', [$this, 'addFontStyle'], 9999)
        ;
    }

    public function addFilters()
    {
        $plugin = PR__PLG__PMPR__NAME;
        $this->addFilter('get_pmpr_plugin_api_key', [$this, 'getAPIKey'])
             ->addFilter('mce_css', [$this, 'updateMCEFont'], 100)
             ->addFilter("plugin_action_links_{$plugin}", [$this, 'addSettingLink'])
             ->addFilter(Setting::SETTING_KEY . '_tabs', [$this, 'addRemoteTabToSetting']);
    }

    public function getAPIKey()
    {
        return $this->getHelper()->getSetting()->getAPIKey();
    }

    public function loadRequirement()
    {
        Component::getInstance();
        if (is_admin()) {

            Setting::getInstance();
            if ($this->getHelper()->getTool()->isAjax()) {
                Ajax::getInstance();
            } else {
                Asset::getInstance();
            }
            if (wp_is_rest_endpoint()) {
                REST::getInstance();
            }
        }

        $fileHelper = $this->getHelper()->getFile();
        $filepath   = $fileHelper->getPath('/vendor/woocommerce/action-scheduler/action-scheduler.php');
        if ($fileHelper->exists($filepath)) {
            require_once $filepath;
        }
    }

    /**
     * @param $tabs
     *
     * @return mixed
     */
    public function addRemoteTabToSetting($tabs)
    {
        if ($this->getHelper()->getComponent()->isRequiredInstalled()) {

            $assetHelper = $this->getHelper()->getAsset();
            $filesystem  = $this->getHelper()->getFile()->getFilesystem();

            $remoteTab = get_transient(self::REMOTE_TAB_CONTENT);
            if (!$remoteTab) {

                $remoteTab = API::getInstance()->getRemoteTabContent();
                if ($remoteTab && !is_wp_error($remoteTab)) {

                    $path = $assetHelper->getPath('/img/remote');
                    if ($filesystem && $filesystem->exists($path)) {

                        $filesystem->delete($path);
                    }
                    set_transient(self::REMOTE_TAB_CONTENT, $remoteTab, WEEK_IN_SECONDS);
                }
            }

            if ($remoteTab && !is_wp_error($remoteTab)) {

                $HTMLHelper = $this->getHelper()->getHTML();
                $typeHelper = $this->getHelper()->getType();

                $fields = [
                    'compares' => [
                        'html' => function () use ($remoteTab, $HTMLHelper, $typeHelper) {

                            $HTMLHelper->renderTemplate('setting/remote/main', [
                                'items'       => $typeHelper->arrayGetItem($remoteTab, 'items', []),
                                'description' => $typeHelper->arrayGetItem($remoteTab, 'description', ''),
                            ]);
                        },
                    ],
                ];

                $sidebar = false;
                if ($side = $typeHelper->arrayGetItem($remoteTab, 'side')) {

                    $sidebar = function () use ($side, $HTMLHelper, $typeHelper, $assetHelper) {

                        $prefix = 'img/remote/';
                        if ($logo = $typeHelper->arrayGetItem($side, 'logo')) {

                            $logo = $assetHelper->maybeSave($logo, $prefix);
                        }
                        if ($badges = $typeHelper->arrayGetItem($side, 'badges')) {

                            foreach ($badges as $index => $badge) {

                                $icon                 = $typeHelper->arrayGetItem($badge, 'icon');
                                $badges[$index]->icon = $typeHelper->arrayGetItem($badge, 'icon', $assetHelper->maybeSave($icon, $prefix));
                            }
                        }
                        if ($stackoverflow = $typeHelper->arrayGetItem($side, 'stackoverflow')) {

                            $image                = $typeHelper->arrayGetItem($stackoverflow, 'image');
                            $stackoverflow->image = $typeHelper->arrayGetItem($stackoverflow, 'image', $assetHelper->maybeSave($image, $prefix, 'stackoverflow'));
                        }

                        $HTMLHelper->renderTemplate('setting/remote/side', [
                            'url'           => $this->getHelper()->getTool()->getPMPRBaseURL(),
                            'logo'          => $logo,
                            'items'         => $badges,
                            'title'         => $typeHelper->arrayGetItem($side, Constants::TITLE),
                            'stackoverflow' => $stackoverflow,
                        ]);

                    };
                }

                if ($title = $typeHelper->arrayGetItem($remoteTab, Constants::TITLE)) {

                    $tabs['remote-tab'] = [
                        Constants::PRIORITY  => 1,
                        Constants::TITLE     => $title,
                        Constants::FIELDS    => $fields,
                        Constants::SIDEBAR   => $sidebar,
                        Constants::NO_SUBMIT => true,
                    ];
                }

            }
        }

        return $tabs;
    }

    public function pluginsLoaded()
    {
        $locale = get_locale();
        if (!in_array($locale, ['fa_IR', 'en_US'])) {

            $locale = 'en_US';
        }

        load_textdomain(PR__PLG__PMPR, PR__PLG__PMPR__DIR . "/translation/{$locale}.mo");
        $this->prepareDirectories();
    }

    /**
     * @param $links
     *
     * @return mixed
     */
    public function addSettingLink($links)
    {
        if (current_user_can('manage_options')) {

            $links[] = $this->getHelper()->getSetting()->getLinkElement(__('Setting', PR__PLG__PMPR));
        }

        return $links;
    }

    public function prepareDirectories()
    {
        try {

            $fileHelper = $this->getHelper()->getFile();
            if ($basePath = $fileHelper->getBaseDirPath()) {

                // crate component directories
                $types = $this->getHelper()->getComponent()->getTypes();
                foreach ($types as $type) {

                    $fileHelper->mkdir("{$basePath}/component/{$type}");
                }

                // create .htaccess protection
                $fileHelper->create("{$basePath}/.htaccess", 'Deny from all');
            }
        } catch (Exception $exception) {

        }
    }

    public function adminNotice()
    {
        $this->getHelper()->getComponent()->requirementsNotices(true);
        $updateInstance = Update::getInstance();
        if (!$updateInstance->isCurrentPage()
            && $this->getHelper()->getServer()->isPluginPage()
            && $this->getHelper()->getComponent()->getUpdateCount() > 0) {

            $HTMLHelper = $this->getHelper()->getHTML();

            $HTMLHelper->renderNotice(
                sprintf(
                    __('A new update is available for some of Pmpr components, %s.', PR__PLG__PMPR),
                    $HTMLHelper->createElement('a', [
                        'href' => $updateInstance->getPageLink(),
                    ], __('Please install them', PR__PLG__PMPR))
                ),
                [
                    Constants::ID   => 'update_components',
                    Constants::TYPE => 'primary',
                ]
            );
        }
    }

    /**
     * @param string $filepath
     * @param string $require
     * @param array $extra
     */
    public function createRequireFile(string $filepath, string $require, $extra = [])
    {
        $lines = [
            '<?php defined("ABSPATH") || exit;',
            "\$filepath =  {$require};",
            'if (file_exists($filepath)) {',
            '	try {require_once $filepath;} catch (Exception $e) {}',
            '}',
        ];

        if ($extra) {

            $lines = array_merge($lines, $extra);
        }
        $this->getHelper()->getFile()->create($filepath, implode(PHP_EOL, $lines));
    }

    /**
     * @param string $filepath
     * @param array $data
     */
    public function createJSONFile(string $filepath, array $data)
    {
        try {

            $this->getHelper()->getFile()->create($filepath, json_encode(
                $data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ));
        } catch (Exception $exception) {

        }
    }

    public function activate()
    {
        $activeComponents = get_option(self::ACTIVE_COMPONENTS, []);
        if ($activeComponents) {

            $componentHelper = $this->getHelper()->getComponent();
            foreach ($activeComponents as $component) {

                if ($componentHelper->isInstalled($component)) {

                    $componentHelper->changeStatus($component, Constants::ACTIVE);
                }
            }
        }

        delete_transient(Constants::FETCHED_REQUIRED_COMPONENTS);

        $this->getManager()->fetchAndStoreAPIKey();
    }

    public function deactivate()
    {
        $types = [
            Constants::MDL,
            Constants::CVR,
            Constants::CST,
        ];

        $activeComponents = [];
        $componentHelper  = $this->getHelper()->getComponent();
        foreach ($types as $type) {

            $components = $componentHelper->getInstalledByType($type);
            foreach ($components as $component) {

                if ($componentHelper->changeStatus($component, Constants::INACTIVE)) {

                    $activeComponents[] = $component;
                }
            }
        }

        update_option(self::ACTIVE_COMPONENTS, $activeComponents);

        delete_transient(Constants::FETCHED_REQUIRED_COMPONENTS);
    }

    public function adminMenu()
    {
        $count = $this->getHelper()->getComponent()->getUpdateCount();
        $title = sprintf("%s&nbsp;%s", __('Pmpr', PR__PLG__PMPR), $this->getHelper()->getHTML()->createBubbleNotification($count));
        add_menu_page($title, $title, 'manage_options', PR__PLG__PMPR, null, $this->getLogoIcon(), 0);
    }

    /**
     * @return string
     */
    public function getLogoIcon(): string
    {
        $assetHelper = $this->getHelper()->getAsset();

        return $assetHelper->getBase64SVG($assetHelper->getPath('img/logo.svg'));
    }

    public function enqueue()
    {
        if ($font = $this->getFontURL()) {

            $handle = Constants::PLUGIN_PREFIX . '-admin-font';
            wp_register_style($handle, $font);
            wp_enqueue_style($handle);
        }
    }

    public function addFontStyle()
    {
        if ($font = $this->getFontName()) {

            $this->getHelper()->getHTML()->renderTemplate('font', [
                'font' => $font,
            ]);
        }
    }

    /**
     * @param $stylesheets
     *
     * @return mixed|string
     */
    public function updateMCEFont($stylesheets)
    {
        if ($font = $this->getFontURL()) {

            $stylesheets .= ", {$font}";
        }

        return $stylesheets;
    }

    /**
     * @return mixed|null
     */
    public function getFontName()
    {
        return $this->getHelper()->getSetting()->getOption(Setting::ADMIN_FONT, 'Vazir');
    }

    /**
     * @return bool|string
     */
    public function getFontURL()
    {
        $font = $this->getHelper()->getSetting()->getFonts($this->getFontName());
        if (!is_string($font)) {

            $font = false;
        }

        return $font;

    }
}