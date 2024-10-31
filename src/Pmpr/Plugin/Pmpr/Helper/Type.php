<?php

namespace Pmpr\Plugin\Pmpr\Helper;

use ReflectionClass;
use ReflectionException;

/**
 * Class Type
 * @package Pmpr\Plugin\Pmpr\Helper
 */
class Type extends Common
{
    /**
     * @param array|object $args
     * @param array $default
     *
     * @return array
     */
    public function parseArgs($args, array $default = []): array
    {
        return wp_parse_args($args, $default);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function hasConstant(string $name): bool
    {
        return @defined($name);
    }

    /**
     * @param $object
     *
     * @return string
     */
    public function getClassShortname($object): string
    {
        $class = get_class($object);
        return strtolower((string)substr($class, strrpos($class, '\\') + 1));
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return mixed|null
     */
    public function setConstant(string $name, $value)
    {
        if (!$this->hasConstant($name)) {

            $return = @define($name, $value);
        } else {

            $return = $this->getConstant($name);
        }

        return $return;
    }

    /**
     * @param string $name
     * @param $default
     * @param $class
     *
     * @return false|mixed|null
     */
    public function getConstant(string $name, $default = null, $class = null)
    {
        if ($class) {
            try {

                $reflection = new ReflectionClass($class);
                /**
                 * @fescate(exclude=true)
                 */
                $constant = $reflection->getConstant($name);
            } catch (ReflectionException $exception) {

                $constant = $default;
            }
        } else if ($this->hasConstant($name)) {

            $constant = constant($name);
        } else {

            $constant = $default;
        }

        return $constant;
    }

    /**
     * @param array $array
     * @param string|null $key
     *
     * @return array
     */
    public function arraySort(array $array, ?string $key = null): array
    {
        if ($key) {

            uasort($array, static function ($a, $b) use ($key) {
                $va = $a[$key] ?? '';
                $vb = $b[$key] ?? '';
                if ($va === $vb) {
                    return 0;
                }
                return ($va < $vb) ? -1 : 1;
            });
        }
        return $array;
    }

    /**
     * @param array $array
     * @param int $options
     * @param int $depth
     * @param bool $escape
     *
     * @return string
     */
    public function array2json(array $array = [], int $options = 0, bool $escape = false, int $depth = 512): string
    {
        $result = '';
        if ($array) {

            $result = wp_json_encode($array, $options, $depth);
            if ($escape && is_string($result)) {

                $result = htmlspecialchars($result, ENT_QUOTES);
            }
        }

        return $result;
    }

    /**
     * @param        $array
     * @param string $key
     * @param null $default
     *
     * @return mixed|null
     */
    public function arrayGetItem($array, string $key, $default = null)
    {
        $result = $default;
        if (is_array($array)
            && isset($array[$key])) {

            $result = $array[$key];
        } else if (is_object($array)
            && isset($array->{$key})) {

            $result = $array->{$key};
        }
        return $result;
    }

    /**
     * @param        $array
     * @param string $key
     * @param        $value
     *
     * @return mixed
     */
    public function arraySetItem($array, string $key, $value = null)
    {
        if (is_array($array)) {

            $array[$key] = $value;
        } else if (is_object($array)) {

            $array->{$key} = $value;
        }
        return $array;
    }

    /**
     * @param $search
     * @param $replace
     * @param $str
     *
     * @return string|string[]
     */
    public static function stringReplaceLast($search, $replace, $str)
    {
        if (($pos = strrpos($str, $search)) !== false) {

            $length = strlen($search);
            $str    = substr_replace($str, $replace, $pos, $length);
        }

        return $str;
    }

    /**
     * @param $date
     * @param $format
     * @param $locale
     *
     * @return string
     */
    public function translateDate($date, $format = null, $locale = null): string
    {
        if(!$format) {

            $dateFormat = get_option('date_format', 'Y-m-d');
            $timeFormat = get_option('time_format', 'H:i:s');

            $format = sprintf(__('%s \\a\\t %s', PR__PLG__PMPR), $dateFormat, $timeFormat);
        }

        return (string)$this->getHelper()->getHook()->customApplyFilters('trans_datetime', $date, $format, $locale);
    }
}