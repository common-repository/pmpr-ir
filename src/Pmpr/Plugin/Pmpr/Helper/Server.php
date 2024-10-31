<?php

namespace Pmpr\Plugin\Pmpr\Helper;

use Pmpr\Plugin\Pmpr\Interfaces\Constants;

/**
 * Class Server
 * @package Pmpr\Plugin\Pmpr\Helper
 */
class Server extends Common
{
    /**
     * @param $object
     * @param string $key
     * @param $default
     *
     * @return string|array|null
     */
    private function get($object, string $key, $default = null)
    {
        $value = $object[$key] ?? $default;
        if (is_string($value)) {

            $value = sanitize_text_field($value);
        }

        return $value;
    }

    /**
     * @param $key
     * @param $default
     *
     * @return array|string|null
     */
    public function getGet($key, $default = null)
    {
        return $this->get($_GET, $key, $default);
    }

    /**
     * @param $key
     * @param $default
     *
     * @return array|string|null
     */
    public function getPost($key, $default = null)
    {
        return $this->get($_POST, $key, $default);
    }

    /**
     * @param $key
     * @param $default
     *
     * @return array|string|null
     */
    public function getRequest($key, $default = null)
    {
        return $this->get($_REQUEST, $key, $default);
    }

    /**
     * @param string|int $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getServer($key = null, $default = null)
    {
        return $this->get($_SERVER, $key, $default);
    }

    /**
     * @param bool $withQueryString
     * @param array $excludedQueries
     *
     * @return string
     */
    public function getRequestedURL(bool $withQueryString = false, array $excludedQueries = []): string
    {
        $url = '';
        if ($path = $this->getServer('REQUEST_URI')) {

            if ($pos = strrpos($path, "?")) {

                $path = substr($path, 0, $pos);
            }

            $host     = $this->getServer('HTTP_HOST');
            $protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === 0 ? 'https://' : 'http://';

            if ($host) {

                $homeURL = $protocol . $host;
            } else {

                $homeURL = home_url();
            }

            $url = untrailingslashit($homeURL) . $path;

            if ($withQueryString) {

                $queryString = $this->getServer('QUERY_STRING');
                if ($excludedQueries) {

                    parse_str($queryString, $array);
                    if ($array) {

                        $queryString = array_diff_key($array, array_flip($excludedQueries));
                    }
                }
                $url = add_query_arg($queryString, '', $url);
            }
        }

        return $url;
    }

    /**
     * @param string|null $path
     *
     * @return string
     */
    public function getURL(?string $path = ''): string
    {
        $pluginURL = plugin_dir_url('') . basename(PR__PLG__PMPR__DIR);
        if ($path) {

            $pluginURL .= $path;
        }
        return $pluginURL;
    }

    /**
     * @return array
     */
    public function getIPs(): array
    {
        return [
            'server' => $this->getServerIP(),
            'user'   => $this->getUserIP(),
        ];
    }

    /**
     * @return string|null
     */
    public function getUserIP(): ?string
    {
        $keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        ];

        foreach ($keys as $key) {

            $ip = $this->getRequest($key);
            if ($ip) {

                break;
            }
        }

        return $ip;
    }

    /**
     * @return string|null
     */
    public function getServerIP(): ?string
    {
        return gethostbyname(gethostname());
    }

    /**
     * @param array $args
     *
     * @return string
     */
    public function getAdminURL(array $args = []): string
    {
        return add_query_arg($args, get_admin_url() . 'admin.php');
    }

    /**
     * @return bool
     */
    public function isPluginPage(): bool
    {
        return str_contains($this->getGet('page', ''), Constants::PLUGIN_PREFIX);
    }

    /**
     * @param       $data
     * @param bool $success
     * @param bool|string $notice
     */
    public function ajaxResponse($data, bool $success = true, $notice = true)
    {
        if ($notice && is_string($data)) {

            if (is_bool($notice)) {

                $notice = $success ? 'success' : 'error';
            }

            $data = $this->getHelper()->getHTML()->createNotice($data, [
                Constants::TYPE => $notice,
            ]);
        }
        if ($success) {

            wp_send_json_success($data);
        } else {

            wp_send_json_error($data);
        }
    }
}