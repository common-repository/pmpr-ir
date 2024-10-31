<?php

namespace Pmpr\Plugin\Pmpr;

use Exception;
use Pmpr\Plugin\Pmpr\Component\Process;
use Pmpr\Plugin\Pmpr\Container\Container;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;
use Pmpr\Plugin\Pmpr\Traits\ManagerTrait;
use WP_Error;

/**
 * Class Ajax
 * @package Pmpr\Plugin\Pmpr
 */
class Ajax extends Container
{
    use ManagerTrait;

    const RUN_JOB = Constants::PLUGIN_PREFIX . 'run_job';
    const CHECK_JOB = Constants::PLUGIN_PREFIX . 'check_job';
    const SAVE_APIKEY = Constants::PLUGIN_PREFIX . 'save_apikey';
    const FETCH_API_KEY = Constants::PLUGIN_PREFIX . 'fetch_api_key';
    const REGENERATE_STATIC_RESOURCES = Constants::PLUGIN_PREFIX . 'regenerate_static_resources';

    const ACTIONS = [
        'run_job'                     => self::RUN_JOB,
        'check_job'                   => self::CHECK_JOB,
        'save_apikey'                 => self::SAVE_APIKEY,
        'regenerate_static_resources' => self::REGENERATE_STATIC_RESOURCES,
    ];

    public function addActions()
    {
        $this->addAjaxAction(self::RUN_JOB, [$this, 'runJob'])
             ->addAjaxAction(self::FETCH_API_KEY, [$this, 'fetchAPIKey'])
             ->addAjaxAction(self::REGENERATE_STATIC_RESOURCES, [$this, 'regenerateAssets']);
    }

    public function runJob()
    {
        $success = false;
        $result  = '';
        if ($this->getHelper()->getComponent()->isRequirementsSatisfied()) {

            if (current_user_can('manage_options')) {
                if ($this->isValidRequest($result)) {

                    $serverHelper    = $this->getHelper()->getServer();
                    $componentHelper = $this->getHelper()->getComponent();

                    $job  = $serverHelper->getPost(Constants::JOB);
                    $name = $serverHelper->getPost(Constants::NAME);

                    $manager = $this->getManager();

                    $component = $this->getHelper()->getHTML()->createStrong('{ name }');

                    try {
                        switch ($job) {
                            case 'check_update':

                                $manager->checkUpdaets();
                                $success = true;
                                break;
                            case 'cancel_update':
                                if (current_user_can('install_plugins')) {

                                    Process::getInstance()->cancelAll(Process::MIDNIGHT_UPDATE_JOB);
                                    $result  = __('Scheduled midnight update canceled successfully.', PR__PLG__PMPR);
                                    $success = true;
                                } else {

                                    $result = __('You have not permission to cancel components update.', PR__PLG__PMPR);
                                }
                                break;
                            case 'update_now':
                            case 'update_later':
                                if (current_user_can('install_plugins')) {

                                    $processIns = Process::getInstance();
                                    if ($job === 'update_later') {

                                        if ($processIns->scheduleMidnightUpdateSingleJob()) {

                                            $due     = $processIns->getScheduleDueTime(Process::MIDNIGHT_UPDATE_JOB);
                                            $result  = sprintf(
                                                __('Process scheduled for midnight (%s).', PR__PLG__PMPR),
                                                $this->getHelper()->getType()->translateDate($due->format('Y-m-d H:i:s'))
                                            );
                                            $success = true;
                                        } else {

                                            $due    = $processIns->getScheduleDueTime(Process::MIDNIGHT_UPDATE_JOB);
                                            $result = sprintf(
                                                __('Another schedule already set for midnight (%s).', PR__PLG__PMPR),
                                                $this->getHelper()->getType()->translateDate($due->format('Y-m-d H:i:s'))
                                            );
                                        }
                                    } else {

                                        $processIns->cancelAll(Process::MIDNIGHT_UPDATE_JOB);
                                        $result = $this->getManager()->updateAll();
                                        if (!is_wp_error($result)) {

                                            if ($result && is_array($result)) {

                                                foreach ($result as $component => $res) {

                                                    if ($res && !is_wp_error($res)) {

                                                        $success = true;
                                                    } else {

                                                        $success = false;
                                                        if (is_wp_error($res)) {

                                                            $result = $res->get_error_messages();
                                                        } else {

                                                            $result = sprintf(__('Can not update %s.', PR__PLG__PMPR), $component);
                                                        }
                                                        break;
                                                    }
                                                }
                                                if ($success) {

                                                    $result = __('All components updated successfully.', PR__PLG__PMPR);
                                                }
                                            } else {

                                                $result = __('Something wrong on updating process, please try again.', PR__PLG__PMPR);
                                            }
                                        }
                                    }
                                } else {

                                    $result = __('You have not permission to update components.', PR__PLG__PMPR);
                                }
                                break;
                            case Constants::INSTALL:
                                if (current_user_can('install_plugins')) {

                                    if ($componentHelper->isInstalled($name)) {

                                        $result  = sprintf(__('%s already installed.', PR__PLG__PMPR), $component);
                                        $success = true;
                                    } else {

                                        $manager->updateAll();
                                        $result = $manager->install($name);
                                        if ($result && !is_wp_error($result)
                                            && $componentHelper->isInstalled($name)) {

                                            $componentObject = $componentHelper->getDataAsObject($name);

                                            $result = $componentObject->createAction([
                                                Constants::JOB   => 'activate',
                                                Constants::COLOR => 'primary',
                                                Constants::TITLE => __('Activate', PR__PLG__PMPR),
                                            ]);

                                            $success = true;
                                        } else if (!is_wp_error($result)) {

                                            $result = sprintf(__('Can not install %s.', PR__PLG__PMPR), $component);
                                        }
                                    }
                                } else {

                                    $result = __('You have not permission to install components.', PR__PLG__PMPR);
                                }
                                break;
                            case 'remove':
                            case 'delete':
                                if (current_user_can('delete_plugins')) {

                                    $result = $manager->remove($name);
                                    if ($result && !is_wp_error($result)) {

                                        $result  = sprintf(__('%s deleted successfully.', PR__PLG__PMPR), $component);
                                        $success = true;
                                        $componentHelper->removeData($name);
                                    }
                                } else {

                                    $result = __('You have not permission to delete components.', PR__PLG__PMPR);
                                }
                                break;
                            case 'activate':
                                $manager->activate($name);
                                $success = true;
                                if ($serverHelper->getRequest('install_page')) {

                                    $result = $componentHelper->getObject($name)->createAction([
                                        Constants::TITLE  => __('Active', PR__PLG__PMPR),
                                        Constants::ENABLE => false,
                                    ]);
                                } else {

                                    $result = sprintf(__('%s status changed to activate successfully.', PR__PLG__PMPR), $component);
                                }
                                break;
                            case 'deactivate':
                                $manager->deactivate($name);
                                $success = true;
                                $result  = sprintf(__('%s status changed to inactive successfully.', PR__PLG__PMPR), $component);
                                break;
                        }
                        if ($success) {

                            $this->doAction("pmpr_{$job}_component", $name);
                            $this->doAction("pmpr_{$job}_component_{$name}");
                        } else {

                            $this->doAction("pmpr_{$job}_component_failed", $name);
                            $this->doAction("pmpr_{$job}_component_{$name}_failed");
                        }
                    } catch (Exception $exception) {

                        $result = $exception->getMessage();
                    }
                }
            } else {

                $result = new WP_Error('forbidden', __('Sorry, you have no permission to do this.', PR__PLG__PMPR));
            }
        } else {

            $result = new WP_Error('bad_request', __('Some requirement not satisfied, please check notices in admin area.', PR__PLG__PMPR));
        }

        $this->getHelper()->getServer()->ajaxResponse($result, $success, false);
    }

    public function fetchAPIKey()
    {
        $success = false;
        if ($this->isValidRequest()) {

            $response = $this->getManager()->fetchAndStoreAPIKey();
            if (!is_wp_error($response)) {

                $success  = true;
                $response = __('API key Fetched successfully.', PR__PLG__PMPR);
            }
        } else {

            $response = __('Your request is not valid', PR__PLG__PMPR);
        }

        $this->getHelper()->getServer()->ajaxResponse($response, $success, false);
    }

    public function regenerateAssets()
    {
        if ($this->isValidRequest()) {

            $this->getHelper()->getAsset()->clearBuildPath();
            $this->doAction('pmpr_move_static_assets');
            $success = true;
            $message = __('Reconstruction of static resources completed successfully.', PR__PLG__PMPR);
        } else {

            $success = false;
            $message = __('Your request is not valid', PR__PLG__PMPR);
        }
        $this->getHelper()->getServer()->ajaxResponse($message, $success, false);
    }

    /**
     * @param string|null $message
     *
     * @return bool
     */
    public function isValidRequest(?string &$message = null): bool
    {
        $return = check_ajax_referer(PR__PLG__PMPR . '_nonce_action', 'nonce', false);
        if (!$return) {

            $message = __('Your ajax request is not valid', PR__PLG__PMPR);
        }
        return $return;
    }
}