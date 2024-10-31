<?php

namespace Pmpr\Plugin\Pmpr\Helper;

use Exception;
use WP_Filesystem_Base;

/**
 * Class File
 * @package Pmpr\Plugin\Pmpr\Helper
 */
class File extends Common
{
    public function getFilesystem(): ?WP_Filesystem_Base
    {
        global $wp_filesystem;

        if (!$wp_filesystem) {

            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $filesystem = null;
        if ($wp_filesystem instanceof WP_Filesystem_Base) {

            $filesystem = $wp_filesystem;
        }

        return $filesystem;
    }

    /**
     * @param string|null $filepath
     *
     * @return null|string|bool
     */
    public function getContent(?string $filepath)
    {
        $content = false;

        $filesystem = $this->getFilesystem();
        if ($filesystem && $filesystem->exists($filepath)) {

            $content = $filesystem->get_contents($filepath);
        }

        return $content;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function isdir($path): bool
    {
        $return = false;
        if ($filesystem = $this->getFilesystem()) {

            $return = $filesystem->is_dir($path);
        }

        return $return;
    }

    /**
     * @param $filepath
     *
     * @return bool
     */
    public function exists($filepath): bool
    {
        $return = false;
        if ($filesystem = $this->getFilesystem()) {

            $return = $filesystem->exists($filepath);
        }

        return $return;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function isWritable($path): bool
    {
        $return = false;
        if ($filesystem = $this->getFilesystem()) {

            $return = $filesystem->is_writable($path);
        }

        return $return;
    }

    /**
     * @param $source
     * @param $destination
     *
     * @return bool
     */
    public function copy($source, $destination)
    {
        $return = false;
        if ($filesystem = $this->getFilesystem()) {

            $return = $filesystem->copy($source, $destination);
        }

        return $return;
    }

    /**
     * @param $path
     * @param bool $recursive
     *
     * @return bool
     */
    public function mkdir($path, bool $recursive = true): bool
    {
        $return = false;
        if ($this->isdir($path)) {

            $return = true;
        } else if ($recursive && function_exists('wp_mkdir_p')) {

            $return = wp_mkdir_p($path);
        } else if ($filesystem = $this->getFilesystem()) {

            $return = $filesystem->mkdir($path);
        }

        return $return;
    }

    /**
     * @param string $filepath
     * @param string $content
     *
     * @return bool
     */
    public function create(string $filepath, string $content = ''): bool
    {
        $return     = false;
        $filesystem = $this->getFilesystem();
        if ($filesystem
            && !$filesystem->exists($filepath)
            && $this->mkdir(dirname($filepath))) {

            $filesystem->touch($filepath);
            $return = $filesystem->put_contents($filepath, $content);
        }

        return $return;
    }

    /**
     * @return string
     */
    public function getBaseDirPath(): string
    {
        $typeHelper = $this->getHelper()->getType();

        $path = '';

        if ($rootPath = $typeHelper->getConstant('ABSPATH')) {

            $rootPath = dirname($rootPath);

            if (!$this->isWritable($rootPath)) {

                $rootPath = $typeHelper->getConstant('WP_CONTENT_DIR');
            }

            $path = trailingslashit($rootPath) . 'base';
        }

        if ($path && !$this->isdir($path)) {

            $this->mkdir($path);
        }

        if (!$path || !$this->isdir($path)) {

            $path = '';
        }

        return untrailingslashit($path);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getPath(string $path = ''): string
    {
        $pluginPath = PR__PLG__PMPR__DIR;
        if ($path) {

            $pluginPath .= $path;
        }

        return $pluginPath;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function templateExists(string $path): string
    {
        $path = PR__PLG__PMPR__DIR . "/template/{$path}.php";
        if (!$this->getHelper()->getFile()->exists($path)) {

            $path = '';
        }

        return $path;
    }
}