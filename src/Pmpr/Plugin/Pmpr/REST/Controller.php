<?php

namespace Pmpr\Plugin\Pmpr\REST;

use Pmpr\Plugin\Pmpr\Interfaces\Constants;
use Pmpr\Plugin\Pmpr\Traits\APITrait;
use Pmpr\Plugin\Pmpr\Traits\HelperTrait;
use Pmpr\Plugin\Pmpr\Traits\ManagerTrait;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Controller
 * @package Pmpr\Plugin\Pmpr\REST
 */
class Controller extends WP_REST_Controller
{
    use APITrait,
        HelperTrait,
        ManagerTrait;

    public function __construct()
    {
        $this->namespace = 'pmpr-ir/v1';
        $this->rest_base = 'api';
    }

    /**
     * @param string $path
     * @param array $args
     *
     * @return void
     */
    public function register(string $path, array $args = [])
    {
        $args = wp_parse_args($args, [
            Constants::ARGS                => [],
            Constants::METHODS             => WP_REST_Server::READABLE,
            Constants::PERMISSION_CALLBACK => [$this, 'permissionCheck'],
        ]);

        register_rest_route($this->namespace, "/{$this->rest_base}/{$path}", $args);
    }

    public function register_routes()
    {
        $this->register('push-update', [
            Constants::ARGS     => [],
            Constants::METHODS  => WP_REST_Server::CREATABLE,
            Constants::CALLBACK => [$this, 'pushUpdate']
        ]);
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_HTTP_Response|WP_REST_Response
     */
    public function pushUpdate(WP_REST_Request $request)
    {
//        $token     = $request->get_param('token');
//        $version   = $request->get_param(Constants::VERSION);
//        $component = $request->get_param(Constants::COMPONENT);
//        if ($token && $component && $version) {
//
//            if ($token === $this->getAPIKey()) {
//
//                $componentHelper = $this->getHelper()->getComponent();
//
//                if ($componentHelper->isInstalled($component)) {
//
//                    $component = $componentHelper->getData($component);
//                    if (version_compare($component[Constants::VERSION] ?? '', $version, '<')) {
//
//                        if ($this->getManager()->updateAll()) {
//
//                            $response = $this->successResponse(__('Your request is ok.', PR__PLG__PMPR));
//                        } else {
//
//                            $response = $this->wpError(sprintf(__('Something wrong on install update for %s: %s', PR__PLG__PMPR), $component, $message), 'Error');
//                        }
//                    } else {
//
//                        $response = $this->wpError(sprintf(__('Version %s or higher version already installed.', PR__PLG__PMPR), $version), 'Already Installed');
//                    }
//                } else {
//
//                    $response = $this->wpError(sprintf(__('%s not installed in %s', PR__PLG__PMPR), $component, get_home_url()), 'Bad Request');
//                }
//            } else {
//
//                $response = $this->wpError(__('Your request is not valid.', PR__PLG__PMPR), 'Request Denied');
//            }
//        } else {
//
//            $response = $this->wpError(__('Require parameters missing.', PR__PLG__PMPR), 'Bad Request');
//        }
        $response = $this->getHelper()->getTool()->wpError(__('it\'s deprecated.', PR__PLG__PMPR), 'deprecated');

        return rest_ensure_response($response);
    }

    /**
     * @param string $message
     * @param array $data
     *
     * @return array
     */
    public function successResponse(string $message, array $data = []): array
    {
        return [
            'data'    => $data,
            'status'  => 200,
            'success' => true,
            'message' => $message,
        ];
    }

    public function permissionCheck()
    {
        return true;
    }
}