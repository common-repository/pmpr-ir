<?php

namespace Pmpr\Plugin\Pmpr\Helper;

use WP_Error;

/**
 * Class Tool
 * @package Pmpr\Plugin\Pmpr\Helper
 */
class Tool extends Common
{
    /**
     * @param string|null $message
     * @param string $code
     * @param array $data
     *
     * @return WP_Error
     */
    public function wpError(?string $message, string $code, array $data = []): WP_Error
    {
        return new WP_Error($code, $message, $data);
    }

    /**
     * @return bool
     */
    public function isAjax(): bool
    {
        return (bool)$this->getHelper()->getType()->getConstant('DOING_AJAX', false);
    }

    /**
     * @param string $path
     * @return string
     */
    public function getPMPRBaseURL(string $path = ''): string
    {
        $baseURL = $this->getHelper()->getType()->getConstant('PR_SITE_URL', 'https://pmpr.ir');

        if ($path) {

            $baseURL = trailingslashit($baseURL) . $path;
        }

        return untrailingslashit($baseURL);
    }
}