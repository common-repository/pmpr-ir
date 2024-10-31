<?php

namespace Pmpr\Plugin\Pmpr\Container;

use Exception;

/**
 * Class Storage
 * @package Pmpr\Plugin\Pmpr\Container
 */
class Storage
{
    /**
     * @var Container[]
     */
    protected static array $instances = [];

    /**
     * @return object[]|null
     */
    private static function getInstances(): ?array
    {
        return self::$instances;
    }

    /**
     * @param string $namespace
     * @param null $id
     */
    public static function remove(string $namespace, $id = null)
    {
        $key = self::getKey($namespace, $id);

        if (isset(self::$instances[$key])) {

            unset(self::$instances[$key]);
        }
    }

    /**
     * @param string $key
     *
     * @param object $instance
     */
    public static function add(string $key, object $instance)
    {
        self::$instances[$key] = $instance;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public static function has(string $key)
    {
        $instance  = null;
        if ($instances = self::getInstances()) {

            $instance = $instances[$key] ?? null;
        }

        return $instance;
    }

    /**
     * @param string $class
     * @param string|null $id
     * @param mixed ...$args
     *
     * @return mixed
     */
    public static function get(string $class, ?string $id = null, ...$args)
    {
        $key      = self::getKey($class, $id);
        $instance = self::has($key);
        if (!$instance) {

            if ($id) {

                $args[] = $id;
            }
            try {

                $instance = new $class(...$args);
                self::add($key, $instance);
            } catch (Exception $exception) {

                // TODO Something wrong on running object
                wp_die($exception->getMessage());
            }
        }

        return $instance;
    }

    /**
     * @param string $class
     * @param string|null $id
     *
     * @return string
     */
    public static function getKey(string $class, ?string $id = null): string
    {
        return "{$class}{$id}";
    }

}