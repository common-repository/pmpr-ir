<?php

namespace Pmpr\Plugin\Pmpr\Helper;

use Exception;
use Pmpr\Plugin\Pmpr\Setting;

/**
 * Class Helper
 * @package Pmpr\Plugin\Pmpr\Helper
 */
class Helper
{
    /**
     * @var array
     */
    protected array $instances = [];

    /**
     * @param string $class
     *
     * @return bool
     */
    private function hasInstance(string $class): bool
    {
        return isset($this->instances[$class])
            && !empty($this->instances[$class]);
    }

    /**
     * @param string $class
     *
     * @return mixed
     */
    private function getInstance(string $class)
    {
        if ($this->hasInstance($class)) {

            $instance = $this->instances[$class];
        } else {

            try {

                if (class_exists($class)) {

                    $instance                = new $class;
                    $this->instances[$class] = $instance;
                } else {

                    wp_die("can not create instance fro $class, requested class not exists.");
                }
            } catch (Exception $exception) {

                wp_die($exception);
            }
        }

        return $instance;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->getInstance(Type::class);
    }

    /**
     * @return Setting
     */
    public function getSetting(): Setting
    {
        return Setting::getInstance();
    }

    /**
     * @return Tool
     */
    public function getTool(): Tool
    {
        return $this->getInstance(Tool::class);
    }

    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->getInstance(File::class);
    }

    /**
     * @return HTML
     */
    public function getHTML(): HTML
    {
        return $this->getInstance(HTML::class);
    }

    /**
     * @return Asset
     */
    public function getAsset(): Asset
    {
        return $this->getInstance(Asset::class);
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->getInstance(Server::class);
    }

    /**
     * @return Component
     */
    public function getComponent(): Component
    {
        return $this->getInstance(Component::class);
    }

    /**
     * @return Hook
     */
    public function getHook(): Hook
    {
        return $this->getInstance(Hook::class);
    }
}