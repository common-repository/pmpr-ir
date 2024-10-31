<?php

namespace Pmpr\Plugin\Pmpr;

use Pmpr\Plugin\Pmpr\Container\Container;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;
use WP_Error;

/**
 * Class Setting
 * @package Pmpr\Plugin\Pmpr
 */
class Setting extends Container
{
    // tabs
    const GENERAL = 'general';
    const SUPPORT = 'support';
    const CONTACT = 'contact';

    const SETTING_KEY = Constants::PLUGIN_PREFIX . 'settings';
    const APIKEY = 'apikey';
    const ADMIN_FONT = 'admin_font';

    /**
     * @var WP_Error
     */
    public WP_Error $error;

    /**
     * @var array
     */
    public array $success = [];

    /**
     * @var array
     */
    protected array $options = [];

    public function __construct()
    {
        $this->error = new WP_Error();
        parent::__construct();
    }

    public function addFilters()
    {
        $this->addFilter(self::SETTING_KEY . '_get_option', [$this, 'getOptionByFilter'], 10, 2);
    }

    public function addActions()
    {
        $this->addAction('admin_init', [$this, 'maybeSave'])
             ->addAction('admin_menu', [$this, 'adminMenu'], 99)
             ->addAction('admin_notices', [$this, 'showError'], 99);
    }

    /**
     * @return WP_Error
     */
    public function getError(): WP_Error
    {
        return $this->error;
    }

    public function showError()
    {
        $error = $this->getError();
        if ($error->has_errors()) {

            $this->showNotice($error->get_error_messages());
        } else if ($this->getSuccess()) {

            $this->showNotice($this->getSuccess(), 'success');
        }
    }

    /**
     * @param array $messages
     * @param string $type
     */
    private function showNotice(array $messages, string $type = 'warning')
    {
        $this->getHelper()->getHTML()->renderNotice($messages, [Constants::TYPE => $type]);
    }

    public function adminMenu()
    {
        add_submenu_page(
            PR__PLG__PMPR,
            __('PMPR Plugin Settings', PR__PLG__PMPR),
            __('Settings', PR__PLG__PMPR),
            apply_filters('pmpr_menu_capability', 'manage_options'),
            self::SETTING_KEY,
            [$this, 'pageOutput'],
            99
        );
    }

    public function maybeSave()
    {
        $serverHelper = $this->getHelper()->getServer();

        $nonce = $serverHelper->getPost('_wpnonce');
        if ($nonce && $serverHelper->getPost('submit')
            && self::SETTING_KEY === $serverHelper->getPost('option_page')
            && wp_verify_nonce($nonce, self::SETTING_KEY)) {

            if (current_user_can('manage_options')) {

                $options = [];
                $fields  = $this->getCurrentTabFields();
                if ($fields) {

                    foreach ($fields as $field => $args) {

                        $value   = $serverHelper->getPost($field);
                        $isValid = true;


                        switch ($field) {
                            case self::APIKEY;
                                $value = str_replace(" ", "", $value);
                                if ($value) {

                                    if (!$this->getHelper()->getComponent()->isRequirementsSatisfied(false)) {

                                        $this->getError()->add('requirements_not_satisfied', __('Requirements not satisfied', PR__PLG__PMPR));
                                        $isValid = false;
                                        $value   = '';
                                    } else if (!API::getInstance()->checkAPIKeyValidation($value, $error)) {

                                        $this->getError()->add('api_key_not_valid', $error);
                                        $isValid = false;
                                    }
                                }
                                break;
                            default;
                                $value = esc_sql($value);
                                break;
                        }
                        if ($isValid) {

                            $options[$field] = $value;
                        }
                    }
                    if ($options) {

                        $this->saveOptions($options);
                    }
                }
            } else {

                $this->getError()->add('forbidden', __('Sorry, you have no permission to do this.', PR__PLG__PMPR));
            }
        }
    }

    /**
     * @return array|mixed
     */
    public function getCurrentTabFields()
    {
        $tab    = $this->getCurrentTab();
        $fields = [];
        if ($tab && isset($tab[Constants::FIELDS])) {

            $fields = $tab[Constants::FIELDS];
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function getCurrentTab(): array
    {
        $serverHelper = $this->getHelper()->getServer();

        $tab  = $serverHelper->getGet(Constants::TAB);
        $tabs = $this->getTabs();
        if (!$tab) {

            $tab = $serverHelper->getPost(Constants::TAB);
        }
        if (!$tab || !isset($tabs[$tab])) {

            $tab = self::GENERAL;
        }

        $current           = $tabs[$tab];
        $current[Constants::ID] = $tab;

        return $current;
    }

    /**
     * @return array[]
     */
    public function getTabs(): array
    {
        $HTMLHelper = $this->getHelper()->getHTML();
        $tabs       = [
            self::GENERAL => [
                Constants::PRIORITY => 10,
                Constants::TITLE    => __('General', PR__PLG__PMPR),
                Constants::FIELDS   => [
                    self::APIKEY                      => [
                        'type'  => 'text',
                        'class' => 'direction-ltr',
                        'label' => __('API Key', PR__PLG__PMPR),
                        'after' => $HTMLHelper->createElement('a', [
                            'id'         => Ajax::FETCH_API_KEY,
                            'href'       => '#',
                            'class'      => 'button button-default pr-ajax-action ml-2' . ($this->getAPIKey() ? ' disabled' : ''),
                            'data-nonce' => wp_create_nonce(PR__PLG__PMPR . '_nonce_action'),
                        ], __('Fetch API Key', PR__PLG__PMPR)),
                    ],
                    self::ADMIN_FONT                  => [
                        'type'    => 'select',
                        'label'   => __('Admin Area Font', PR__PLG__PMPR),
                        'options' => $this->getFonts(false, true),
                    ],
                    Ajax::REGENERATE_STATIC_RESOURCES => [
                        'type'  => 'action',
                        'color' => 'default',
                        'label' => __('Regenerate Static Resources', PR__PLG__PMPR),
                        'desc'  => __('Only use in situations in which the plugin does not function properly', PR__PLG__PMPR),
                    ],
                ],
            ],
            self::CONTACT => [
                Constants::PRIORITY  => 20,
                Constants::TITLE     => __('Contact', PR__PLG__PMPR),
                Constants::FIELDS    => [
                    'contact'  => [
                        'label' => __('Contact US', PR__PLG__PMPR),
                        'html'  => $HTMLHelper->createElement('a', [
                            'href'   => $this->getHelper()->getTool()->getPMPRBaseURL('contact'),
                            'class'  => 'button',
                            'target' => '_blank',
                        ], __('Contact Form', PR__PLG__PMPR)),
                    ],
                    'whatsapp' => [
                        'label' => __('Whatsapp', PR__PLG__PMPR),
                        'html'  => $HTMLHelper->createElement('a', [
                            'href'   => 'https://wa.me/989028882747',
                            'class'  => 'button',
                            'target' => '_blank',
                        ], __('Send message on Whatsapp', PR__PLG__PMPR)),
                    ],
                    'tel1'     => [
                        'label' => __('Tel 1', PR__PLG__PMPR),
                        'html'  => $HTMLHelper->createElement('a', ['href' => 'tel:09028882747'], '09028882747'),
                    ],
                    'tel2'     => [
                        'label' => __('Tel 2', PR__PLG__PMPR),
                        'html'  => $HTMLHelper->createElement('a', ['href' => 'tel:02144879127'], '02144879127'),
                    ],
                ],
                Constants::NO_SUBMIT => true,
            ],
        ];

        return $this->applyFilters(self::SETTING_KEY . '_tabs', $tabs);
    }

    public function pageOutput()
    {
        $tabs = $this->getTabs();
        $tab  = $this->getCurrentTab();
        if ($tab) {

            foreach ($tab[Constants::FIELDS] as $field => $args) {

                if ($args) {

                    $args[Constants::VALUE] = $this->getValue($field);

                    $tab[Constants::FIELDS][$field] = $args;
                }
            }
        }

        $HTMLHelper = $this->getHelper()->getHTML();

        $tabs = $this->getHelper()->getType()->arraySort($tabs, Constants::PRIORITY);

        $HTMLHelper->renderTemplate('setting/index', [
            'tabs'    => $tabs,
            Constants::ID  => self::SETTING_KEY,
            'current' => $tab,
        ]);
    }

    /**
     * @param $default
     * @param $key
     *
     * @return mixed|string|null
     */
    public function getOptionByFilter($default, $key)
    {
        return $this->getOption($key, $default);
    }

    /**
     * @param       $key
     * @param null $default
     * @param array $options
     *
     * @return mixed|null
     */
    public function getOption($key, $default = null, array $options = [])
    {
        if (!$options) {

            $options = $this->getOptions();
        }

        return $options[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param        $value
     */
    public function saveOption(string $key, $value)
    {
        $this->saveOptions([$key => $value]);
    }

    /**
     * @param array $data
     */
    public function saveOptions($data = [])
    {
        if ($data) {

            $options = $this->getOptions();
            $changed = false;
            foreach ($data as $key => $value) {

                if (!isset($options[$key])
                    || $options[$key] != $value) {

                    $changed       = true;
                    $options[$key] = $value;
                }
            }

            if ($changed) {

                wp_cache_delete(self::SETTING_KEY, 'setting');
                update_option(self::SETTING_KEY, maybe_serialize($options));
                $this->addSuccess(__('Setting updated successfully.', PR__PLG__PMPR));
            } else {

                $this->addSuccess(__('Setting updated successfully without no change.', PR__PLG__PMPR));
            }
        }
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        if (empty($this->options)) {

            $options = wp_cache_get(self::SETTING_KEY, 'setting');
            if (!$options) {

                $options = get_option(self::SETTING_KEY, []);
                if ($options) {

                    $options = maybe_unserialize($options);
                    wp_cache_set(self::SETTING_KEY, $options, 'setting');
                }
            }
            if(is_array($options)) {
                $this->options = $options;
            }
        }

        return $this->options;
    }

    /**
     * @param      $key
     * @param null $default
     *
     * @return mixed|null
     */
    public function getValue($key, $default = null)
    {
        $value = $this->getHelper()->getServer()->getPost($key);
        if (!$value) {

            $value = $this->getOption($key, $default);
        }
        return $value;
    }

    /**
     * @return bool
     */
    public function isSettingPage(): bool
    {
        return $this->getHelper()->getServer()->getGet(Constants::PAGE, '') === self::SETTING_KEY;
    }

    /**
     * @param string|null $tab
     *
     * @return string
     */
    public function getPageURL(?string $tab = self::GENERAL): string
    {
        $args = [
            Constants::PAGE => self::SETTING_KEY,
        ];
        if ($tab) {

            $args[Constants::TAB] = $tab;
        }

        return $this->getHelper()->getServer()->getAdminURL($args);
    }

    /**
     * @return array
     */
    public function getSuccess(): array
    {
        return $this->success;
    }

    /**
     * @param string $message
     */
    public function addSuccess(string $message)
    {
        $this->success[] = $message;
    }

    /**
     * @param null $font
     * @param false $trans
     *
     * @return array|string
     */
    public function getFonts($font = null, $trans = false)
    {
        if ($trans) {

            $fonts = [
                'Vazir'   => __('Vazir', PR__PLG__PMPR),
                'Lotus'   => __('Lotus', PR__PLG__PMPR),
                'Yekan'   => __('Yekan', PR__PLG__PMPR),
                'Sahel'   => __('Sahel', PR__PLG__PMPR),
                'Mitra'   => __('Mitra', PR__PLG__PMPR),
                'Samim'   => __('Samim', PR__PLG__PMPR),
                'Nazanin' => __('Nazanin', PR__PLG__PMPR),
                'Shabnam' => __('Shabnam', PR__PLG__PMPR),
            ];
        } else {

            $fonts = [
                'Vazir'   => 'https://cdnjs.cloudflare.com/ajax/libs/vazir-font/27.2.1/font-face.css',
                'Lotus'   => 'https://cdn.fontcdn.ir/Font/Persian/Lotus/Lotus.css',
                'Yekan'   => 'https://cdn.fontcdn.ir/Font/Persian/Yekan/Yekan.css',
                'Sahel'   => 'https://cdn.fontcdn.ir/Font/Persian/Sahel/Sahel.css',
                'Mitra'   => 'https://cdn.fontcdn.ir/Font/Persian/Mitra/Mitra.css',
                'Samim'   => 'https://cdn.fontcdn.ir/Font/Persian/Samim/Samim.css',
                'Nazanin' => 'https://cdn.fontcdn.ir/Font/Persian/Nazanin/Nazanin.css',
                'Shabnam' => 'https://cdn.fontcdn.ir/Font/Persian/Shabnam/Shabnam.css',
            ];
        }

        if ($font) {

            $fonts = $this->getHelper()->getType()->arrayGetItem($fonts, $font, 'vazir');
        }

        return $fonts;
    }

    /**
     * @param $apikey
     *
     * @return bool|string
     */
    public function getAPIKey($apikey = '')
    {
        $apikey = (string)$this->getOption(self::APIKEY, $apikey);

        if ($apikey && strlen($apikey) !== Constants::APIKEY_LENGTH) {

            $apikey = false;
        }
        return $apikey;
    }

    /**
     * @param string $text
     * @param string $tab
     * @param array $attrs
     *
     * @return string
     */
    public function getLinkElement(string $text, string $tab = '', array $attrs = []): string
    {
        $attrs['href']  = $this->getPageURL($tab);
        $attrs['class'] = 'text-decoration-none';
        return $this->getHelper()->getHTML()->createElement('a', $attrs, $text);
    }
}