<?php

namespace Pmpr\Plugin\Pmpr;

use Pmpr\Plugin\Pmpr\Component\Ajax as ComponentAjax;
use Pmpr\Plugin\Pmpr\Container\Container;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;

/**
 * Class Asset
 * @package Pmpr\Plugin\Pmpr
 */
class Asset extends Container
{
    const HANDLE_PREFIX = 'pr-plg-asset-';

    public function addActions()
    {
        $this->addAction('admin_enqueue_scripts', [$this, 'enqueue'], 9999)
             ->addAction('admin_footer', [$this, 'renderDialog'], 999);

        parent::addActions();
    }

    public function renderDialog()
    {
        $this->getHelper()->getHTML()->renderElement('div', [
            Constants::ID => 'pmpr_dialog',
            'class'  => 'hidden pmpr-plg-dialog',
        ]);
    }

    public function enqueue()
    {
        $ver         = PR__PLG__PMPR__VER;
        $assetHelper = $this->getHelper()->getAsset();

//        wp_enqueue_script(self::HANDLE_PREFIX . 'upload', $assetHelper->getURL("js/upload-{$ver}.js"), ['jquery'], PR__PLG__PMPR__VER);
        $src  = $assetHelper->getURL("css/admin-{$ver}.css");
        if (is_rtl()) {

            $src = str_replace('.css', '.rtl.css', $src);
        }
        wp_enqueue_style(self::HANDLE_PREFIX . 'admin', $src);

        if ($this->getHelper()->getComponent()->isRequiredInstalled()) {

            $page = $this->getHelper()->getServer()->getGet(Constants::PAGE, '');
            if ($page && strpos($page, Constants::PLUGIN_PREFIX) !== false) {

                wp_enqueue_script(self::HANDLE_PREFIX . 'admin', $assetHelper->getURL("js/admin-{$ver}.js"), ['cmn__fndtn-helper', 'cmn__fndtn-backend-helper'], PR__PLG__PMPR__VER);
                wp_localize_script(self::HANDLE_PREFIX . 'admin', 'PMPRPLGVar', [
                    'ajax'         => array_merge(Ajax::ACTIONS, ComponentAjax::ACTIONS),
                    'ajax_url'     => admin_url('admin-ajax.php'),
                    'translations' => [

                        'delete' => __('Delete', PR__PLG__PMPR),

                        'activate'     => __('Activate', PR__PLG__PMPR),
                        'deactivate'   => __('Deactivate', PR__PLG__PMPR),
                        'deactivation' => __('Deactivation', PR__PLG__PMPR),

                        'update'   => __('Update', PR__PLG__PMPR),
                        'updated'  => __('Updated', PR__PLG__PMPR),
                        'updating' => __('Updating', PR__PLG__PMPR),

                        'activation' => __('Activation', PR__PLG__PMPR),
                        'activated'  => __('Activated', PR__PLG__PMPR),
                        'activating' => __('Activating...', PR__PLG__PMPR),

                        'install'    => __('Install', PR__PLG__PMPR),
                        'installing' => __('Installing...', PR__PLG__PMPR),
                        'installed'  => __('Installed', PR__PLG__PMPR),

                    ],
                    'confirms'     => [
                        'delete' => __('Are you sure about remove { name }?', PR__PLG__PMPR),
                    ],
                ]);
            }
        }
    }
}