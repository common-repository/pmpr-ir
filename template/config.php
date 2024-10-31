<?php

if (!defined('ABSPATH')) {
    exit();
}

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
    && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {

    $_SERVER['HTTPS'] = 'on';
}

if (!function_exists('pr_config_define_constant')) {

    function pr_config_define_constant($constant, $value)
    {
        if (!defined($constant)) {

            define($constant, $value);
            return true;
        }
        return false;
    }
}

if (!function_exists('pr_config_get_sld')) {

    function pr_config_get_sld($parts)
    {
        $sld   = [];
        $count = count($parts);
        if ($count === 1) {

            $sld[] = $parts[array_key_first($parts)];
        } else if ($count === 2) {

            $sld[] = implode('.', $parts);
            $sld[] = $parts[array_key_last($parts)];
        }

        // common config
        $sld[] = '';

        return $sld;
    }
}
if (!function_exists('pr_config_get_tld')) {
    function pr_config_get_tld(&$parts)
    {
        $tld  = '';
        $last = array_key_last($parts);
        if (isset($parts[$last])) {

            $tld = $parts[$last];
            unset($parts[$last]);
            $maybeTLD = $parts[$last - 1] ?? false;
            if (strlen($maybeTLD) <= 3) {

                unset($parts[$last - 1]);
                $tld = "{$maybeTLD}.{$tld}";
            }
        }

        return $tld;
    }
}
if (!function_exists('pr_config_load_env_file')) {

    /**
     * @param string $filepath
     *
     * @return array
     */
    function pr_config_load_env_file(string $filepath): array
    {
        $variables = [];
        if (file_exists($filepath)) {

            try {

                $configs = (array)json_decode(file_get_contents($filepath), true, 512, JSON_THROW_ON_ERROR);
            } catch (Exception $e) {

                $configs = [];
            }

            $constants = $configs['constant'] ?? [];
            foreach ($constants as $name => $value) {

                if (!defined($name)) {

                    define($name, $value);
                }
            }

            $variables = $configs['variable'] ?? [];
        }

        return $variables;
    }
}

$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === 0 ? 'https://' : 'http://';
$host     = $_SERVER['SERVER_NAME'] ?? '';
$url      = $protocol . $host;

$parts = explode('.', str_replace('www.', '', $host));

$tld = pr_config_get_tld($parts);
$sld = pr_config_get_sld($parts);

switch ($tld) {
    case 'local':
    case '':
        $env   = 'development';
        $files = ['dev', $env];
        break;
    default:
        $env   = 'production';
        $files = ['prod', $env];
}

foreach ($sld as $context) {

    foreach ($files as $filename) {

        $filepath = __DIR__ . "/$context/{$filename}.json";
        if ($variables = pr_config_load_env_file($filepath)) {

            foreach ($variables as $name => $value) {

                if (!isset(${$name})) {

                    ${$name} = $value;
                }
            }

            if (!pr_config_define_constant('WP_HOME', $url)) {
                $url = WP_HOME;
            }

            $constants = [
                'PR_ENV'        => $env,
                'WP_SITEURL'    => $url,
                'PR_ENV_SLD'    => $context,
                'PR_ENV_FILE'   => $filepath,
                'COOKIE_DOMAIN' => $host,
            ];
            foreach ($constants as $constant => $value) {
                pr_config_define_constant($constant, $value);
            }

            break;
        }
    }
}
