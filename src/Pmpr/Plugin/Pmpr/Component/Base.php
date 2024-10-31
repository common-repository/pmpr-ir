<?php

namespace Pmpr\Plugin\Pmpr\Component;

use File_Upload_Upgrader;
use Pmpr\Plugin\Pmpr\Component\ListTable\Install;
use Pmpr\Plugin\Pmpr\Component\ListTable\Installed;
use Pmpr\Plugin\Pmpr\Component\Manager\Skin\Upgrader;
use Pmpr\Plugin\Pmpr\Container\Container;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;
use Pmpr\Plugin\Pmpr\Traits\ManagerTrait;

/**
 * Class Base
 * @package Pmpr\Plugin\Pmpr\Component
 */
abstract class Base extends Container
{
    use ManagerTrait;

    /**
     * @var array
     */
    protected array $args = [
        Constants::PRIVATE => false,
    ];

    /**
     * @var Install|null
     */
    protected ?Install $install = null;

    /**
     * @var Installed|null
     */
    protected ?Installed $installed = null;

    public function __construct()
    {
        if (!$this->isPrivate()) {

            $this->addArg(Constants::MENU_SLUG, Constants::PLUGIN_PREFIX . $this->getName());
        }

        parent::__construct();
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return (bool)$this->getArg(Constants::PRIVATE);
    }

    /**
     * {cmn, mdl, cvr, ctm}
     * @return string
     */
    public function getType(): string
    {
        return $this->getArg(Constants::TYPE);
    }

    /**
     * {Common, Module, Cover, Custom}
     * @return string
     */
    public function getName(): string
    {
        return $this->getArg(Constants::NAME);
    }

    /**
     * @return string
     */
    public function getMenuSlug(): string
    {
        return $this->getArg(Constants::MENU_SLUG);
    }

    /**
     * @return Install|null
     */
    public function getInstall(): ?Install
    {
        return $this->install;
    }

    /**
     * @return Installed|null
     */
    public function getInstalled(): ?Installed
    {
        return $this->installed;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @param string $key
     * @param false $default
     *
     * @return false|mixed
     */
    public function getArg(string $key, $default = false)
    {
        return $this->args[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function addArg(string $key, $value = ''): self
    {
        $this->args[$key] = $value;

        return $this;
    }

    public function addActions()
    {
        if (!$this->isPrivate()) {

            $this->addAction('admin_menu', [$this, 'adminMenu'])
                 ->addAction('plugins_loaded', [$this, 'setTranslations']);
        }
    }

    public function setTranslations()
    {
        $this->addArg('page_title', __('Available %s', PR__PLG__PMPR))
             ->addArg('add_page_title', __('Add %s', PR__PLG__PMPR))
             ->addArg('search_label', __('Search %s', PR__PLG__PMPR))
             ->addArg('search_placeholder', __('Search %s ...', PR__PLG__PMPR));
    }

    /**
     * @return string
     */
    public function getPageLink(): string
    {
        return $this->getHelper()->getServer()->getAdminURL([
            Constants::PAGE => $this->getMenuSlug(),
        ]);
    }

    /**
     * @param string|null $text
     * @param array $attrs
     *
     * @return string
     */
    public function getPageLinkElement(?string $text, array $attrs = []): string
    {
        $attrs['href']  = $this->getPageLink();
        $attrs['class'] = 'text-decoration-none';
        return $this->getHelper()->getHTML()->createElement('a', $attrs, $text);
    }

    /**
     * @return string
     */
    public function getScheduleHook(): string
    {
        return sprintf('check_%s_update_schedule_hook', $this->getName());
    }

    public function adminMenu()
    {
        $menuTitle = $this->getArg(Constants::MENU_TITLE);
        if (!$menuTitle) {

            $menuTitle = $this->getArg(Constants::PLURAL_NAME);
        }

        add_submenu_page(
            PR__PLG__PMPR,
            sprintf($this->getArg('page_title'), $this->getArg(Constants::PLURAL_NAME)),
            $this->updateMenuTitle($menuTitle),
            apply_filters('pmpr_menu_capability', 'manage_options'),
            $this->getMenuSlug(),
            [$this, 'pageOutput'],
            $this->getArg(Constants::POSITION, 0)
        );

        remove_submenu_page(PR__PLG__PMPR, PR__PLG__PMPR);
    }

    /**
     * @param string $menuTitle
     *
     * @return string
     */
    public function updateMenuTitle(string $menuTitle): string
    {
        return $menuTitle;
    }

    public function pageOutput()
    {
        $serverHelper = $this->getHelper()->getServer();

        $tab     = $serverHelper->getGet(Constants::TAB);
        $context = $serverHelper->getGet(Constants::CONTEXT);

        $this->render($context, $tab);
    }

    /**
     * @param $context
     * @param $tab
     */
    public function render($context, $tab)
    {
        $parameters = $this->getParameters($context);

        $serverHelper = $this->getHelper()->getServer();
        if (Constants::INSTALL === $context) {

            $prefix = Constants::INSTALL;
            if ('upload-component' === $serverHelper->getRequest('action')) {

                $tab  = 'uploading';
                $mask = __('Uploading %s', PR__PLG__PMPR);
            } else if ($tab === 'upload') {

                $mask = __('Upload %s', PR__PLG__PMPR);
            } else {

                $mask = $this->getArg('add_page_title');
            }

            $parameters[Constants::TAB]   = $tab;
            $parameters[Constants::TITLE] = sprintf($mask, $this->getArg(Constants::SINGULAR_NAME));
        } else {

            $prefix                  = Constants::INSTALLED;
            $parameters[Constants::TITLE] = $this->getArg(Constants::PLURAL_NAME);
        }

        $parameters['args']        = $this->getArgs();
        $parameters['object']      = $this;
        $parameters['component']   = $this;
        $parameters['current_url'] = $serverHelper->getRequestedURL(true);

        $HTMLHelper = $this->getHelper()->getHTML();
        $filename   = $this->getHelper()->getType()->getClassShortName($this);
        if ($this->getHelper()->getFile()->templateExists("{$prefix}/{$filename}")) {

            $HTMLHelper->renderTemplate("{$prefix}/{$filename}", $parameters);
        } else {

            $HTMLHelper->renderTemplate("{$prefix}/index", $parameters);
        }
    }

    /**
     * @param $context
     *
     * @return array
     */
    public function getParameters($context)
    {
        $parameters = [];
        if (method_exists($this, 'initListTables')) {

            $this->initListTables();
            if (Constants::INSTALL === $context) {

                $table = $this->getInstall();
            } else {

                global $status;

                $table = $this->getInstalled();

                $parameters = [
                    's'          => $this->getHelper()->getServer()->getRequest('s', ''),
                    Constants::STATUS => $status,
                ];
                $table->prepare_items();
            }

            $parameters['table'] = $table;
        }

        return $parameters;
    }

    public function uploadProcess()
    {
        $serverHelper = $this->getHelper()->getServer();
        if ('upload-component' === $serverHelper->getRequest('action')) {

            if (!current_user_can('upload_plugins')) {
                wp_die(__('Sorry, you are not allowed to install components on this site.', PR__PLG__PMPR));
            }

            check_admin_referer('component-upload');

            $filename = strtolower($_FILES['componentzip']['name'] ?? '');
            if (!$filename || !str_ends_with($filename, '.zip')) {

                wp_die(__('Only .zip archives may be uploaded.', PR__PLG__PMPR));
            } else {

                $type = $serverHelper->getRequest('type');
                if (!str_starts_with($filename, "wp-{$type}-")) {

                    wp_die(__('Your uploaded file is not valid.', PR__PLG__PMPR));
                }
            }

            if (!class_exists('File_Upload_Upgrader')) {

                require_once ABSPATH . 'wp-admin/includes/class-file-upload-upgrader.php';
            }

            $fileUpload = new File_Upload_Upgrader('componentzip', 'package');

            $overwrite = $serverHelper->getRequest('overwrite', '');
            $overwrite = in_array($overwrite, ['update-component', 'downgrade-component'], true) ? $overwrite : '';

            $package   = $fileUpload->package;
            $component = pathinfo($filename, PATHINFO_FILENAME);

            if (!class_exists('WP_Upgrader_Skin')) {

                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';
            }

            $upgrader = $this->getManager()->getInstaller(new Upgrader());

            $result = $upgrader->install($component, [
                'package'            => $package,
                'overwrite_package'  => $overwrite,
                'check_requirements' => false,
            ]);

            if ($result || is_wp_error($result)) {

                $fileUpload->cleanup();
            }
        }
    }

    public function compareNotice()
    {
        if ($alternative = $this->getArg(Constants::ALTERNATIVE_NAME)) {

            $remoteTab = get_transient(self::REMOTE_TAB_CONTENT);
            if ($remoteTab && $this->getHelper()->getComponent()->isRequiredInstalled()) {

                $HTMLHelper = $this->getHelper()->getHTML();

                $message = sprintf(
                    __('difference between %s and %s', PR__PLG__PMPR),
                    $HTMLHelper->createSpan(sprintf(__('Wordpress %s', PR__PLG__PMPR), $this->getArg('singular_name'))),
                    $HTMLHelper->createSpan(sprintf(__('Wordpress %s', PR__PLG__PMPR), $alternative))
                );

                $content = $this->getHelper()->getSetting()->getLinkElement($message, 'remote-tab');

                $HTMLHelper->renderNotice($content, [
                    'class'      => 'd-inline-block ml-3',
                    'prefix'     => false,
                    'dismiss'    => false,
                    Constants::TYPE   => 'info',
                    Constants::CUSTOM => true,
                ]);
            }
        }
    }

    public function afterPageTitle()
    {
        if ($this->canAddNew()) {

            $this->getHelper()->getHTML()->renderElement('a', [
                'href'  => $this->getHelper()->getServer()->getAdminURL([
                    Constants::TAB     => Constants::GENERAL,
                    Constants::PAGE    => $this->getMenuSlug(),
                    Constants::CONTEXT => Constants::INSTALL,
                ]),
                'class' => 'page-title-action',
            ], __('Add New', PR__PLG__PMPR));
        }
    }

    /**
     * @return bool
     */
    public function canAddNew(): bool
    {
        return current_user_can(apply_filters('pmpr_menu_capability', 'install_plugins'))
            && (!is_multisite() || is_network_admin());
    }

    /**
     * @return bool
     */
    public function isCurrentPage(): bool
    {
        return $this->getHelper()->getServer()->getGet('page', '') === $this->getMenuSlug();
    }
}