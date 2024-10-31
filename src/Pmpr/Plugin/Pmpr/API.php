<?php

namespace Pmpr\Plugin\Pmpr;

use Pmpr\Plugin\Pmpr\Container\Container;
use Pmpr\Plugin\Pmpr\Interfaces\Constants;
use WP_Error;

/**
 * Class API
 * @package Pmpr\Plugin\Pmpr
 */
class API extends Container
{
    /***
     * @param       $query
     * @param array $params
     * @param array $default
     *
     * @return array|mixed|WP_Error
     */
    private function get($query, $params = [], $default = [])
    {
        $default['host']    = get_home_url();
        $default['api_key'] = $this->getHelper()->getSetting()->getAPIKey();

        $params = wp_parse_args($params, $default);

//        $query = add_query_arg(['XDEBUG_SESSION_START' => 'PHPSTORM'], $query);

        $response = wp_remote_get($this->getRESTApiEndpoint() . $query, [
            'body' => $params,
        ]);

        if ($body = wp_remote_retrieve_body($response)) {

            $body = json_decode($body);
            if ((int)wp_remote_retrieve_response_code($response) === 200) {

                $result = $body;
            } else if (isset($body->code, $body->message)) {

                $result = new WP_Error($body->code, $body->message);
            } else {

                $result = false;
            }
        } else {

            $result = $response;
        }

        return $result;
    }

    /**
     * @param string $authKey
     *
     * @return bool|WP_Error
     */
    public function fetchAPIKey(string $authKey)
    {
        $result = $this->get('/domain-manager/get-apikey', [
            'auth_key' => $authKey,
        ]);

        if ($result && !is_wp_error($result)) {

            $result = $this->getHelper()->getType()->arrayGetItem($result, 'api_key');
        }

        return $result;
    }

    /**
     * @param string $apikey
     * @param        $error
     *
     * @return bool
     */
    public function checkAPIKeyValidation(string $apikey, &$error): bool
    {
        $result = false;
        if (strlen($apikey) === Constants::APIKEY_LENGTH) {

            $response = $this->get('/domain-manager/check-apikey', [
                'api_key' => $apikey,
            ]);
            if (!is_wp_error($response)) {

                $result = true;
            } else {

                $error = implode('', $response->get_error_messages());
            }
        } else {

            $error = __('API key is not valid.', PR__PLG__PMPR);
        }

        return $result;
    }

    /**
     * @param array $args
     *
     * @return array|false|mixed|WP_Error
     */
    public function getComponents(array $args = [])
    {
        return $this->get('/component/get-items', $args, [
            Constants::IPS      => $this->getHelper()->getServer()->getIPs(),
            Constants::TYPE     => Constants::MODULE,
            Constants::PAGE     => 1,
            Constants::SEARCH   => '',
            Constants::PER_PAGE => 12,
            Constants::REQUIRED => false,
        ]);
    }

    /**
     * @param string $name
     * @param string $type
     *
     * @return array|mixed|WP_Error
     */
    public function getComponent(string $name, string $type = Constants::MODULE)
    {
        return $this->get('/component/get-item', [
            Constants::IPS  => $this->getHelper()->getServer()->getIPs(),
            Constants::TYPE => $type,
            Constants::NAME => $name,
        ]);
    }

    /**
     * @return array|false|mixed|WP_Error
     */
    public function getRemoteTabContent()
    {
        return $this->get('/component/get-plugin-tab', [
            Constants::IPS => $this->getHelper()->getServer()->getIPs(),
        ]);
    }

    /**
     * @return string
     */
    public function getRESTApiEndpoint(): string
    {
        return "{$this->getHelper()->getTool()->getPMPRBaseURL()}/wp-json/pmpr/v1";
    }
}