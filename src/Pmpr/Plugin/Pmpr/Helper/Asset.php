<?php

namespace Pmpr\Plugin\Pmpr\Helper;

use Exception;

/**
 * Class Asset
 * @package Pmpr\Plugin\Pmpr\Helper
 */
class Asset extends Common
{
    public function clearBuildPath()
    {
        if ($filesystem = $this->getHelper()->getFile()->getFilesystem()) {

            $constName = 'PR_BUILD_PATH';
            if (defined($constName)) {

                $path = constant($constName);
            } else {

                $path = ABSPATH . 's/b';
            }
            if ($filesystem->is_dir($path)
                && $filesystem->is_writable($path)) {

                $filesystem->rmdir($path, true);
            }
        }
    }

    /**
     * @param string $asset
     *
     * @return bool
     */
    public function exists(string $asset): bool
    {
        $exists     = false;
        $filesystem = $this->getHelper()->getFile()->getFilesystem();
        if ($filesystem && $filesystem->exists($this->getPath($asset))) {

            $exists = true;
        }

        return $exists;
    }

    /**
     * @param string $asset
     *
     * @return string
     */
    public function getPath(string $asset): string
    {
        return $this->getHelper()->getFile()->getPath("/build/{$asset}");
    }

    /**
     * @param $url
     * @param $prefix
     * @param string $name
     * @param string $suffix
     *
     * @return string
     */
    public function maybeSave($url, $prefix, string $name = '', string $suffix = ''): string
    {
        $asset = '';
        if (is_string($url)) {

            $extension = pathinfo($url, PATHINFO_EXTENSION);
            if (!$name) {

                $name = (string)pathinfo($url, PATHINFO_FILENAME);
            }
            $filename = sanitize_file_name("{$name}{$suffix}.{$extension}");

            $asset = $prefix . $filename;

            if (!$this->exists($asset)) {

                if ($filesystem = $this->getHelper()->getFile()->getFilesystem()) {

                    $filepath = $this->getPath($asset);
                    if ($url) {

                        try {
                            // remove old files
                            $files = glob($this->getPath($prefix . sanitize_file_name($name) . '*'));
                            if (is_array($files) && count($files) > 0) {

                                foreach ($files as $file) {

                                    $filesystem->delete($file);
                                }
                            }
                        } catch (Exception $exception) {

                        }

                        $path = dirname($filepath);
                        if ($filesystem->exists($path)
                            || $filesystem->mkdir($path)) {

                            $contents = $filesystem->get_contents($url);
                            $saveFile = fopen($filepath, 'wb');
                            fwrite($saveFile, $contents);
                            fclose($saveFile);
                        }
                    }
                }
            }
        }

        if (!$asset || !$this->exists($asset)) {

            $asset = 'img/placeholder.png';
        }

        return $this->getURL($asset);
    }

    /**
     * @param string $asset
     *
     * @return string
     */
    public function getURL(string $asset): string
    {
        return $this->getHelper()->getServer()->getURL("/build/{$asset}");
    }

    /**
     * @param string|null $str
     *
     * @return string
     */
    public function unescapeSVG(?string $str): string
    {
        $ret = '';
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {

            if ($str [$i] === '%' && $str[$i + 1] === 'u') {

                $val = hexdec(substr($str, $i + 2, 4));
                if ($val < 0x7f) {
                    $ret .= chr($val);
                } else if ($val < 0x800) {
                    $ret .= chr(0xc0 | ($val >> 6)) . chr(0x80 | ($val & 0x3f));
                } else {
                    $ret .= chr(0xe0 | ($val >> 12)) . chr(0x80 | (($val >> 6) & 0x3f)) . chr(0x80 | ($val & 0x3f));
                }
                $i += 5;
            } else if ($str [$i] === '%') {

                $ret .= urldecode(substr($str, $i, 3));
                $i   += 2;
            } else {

                $ret .= $str [$i];
            }
        }

        return $ret;
    }

    /**
     * @param string|null $svg
     *
     * @return string
     */
    public function getBase64SVG(?string $svg): string
    {
        if ($base64 = $this->getHelper()->getFile()->getContent($svg)) {

            $base64 = base64_encode($this->unescapeSVG(rawurlencode($base64)));
            if ($base64) {

                $base64 = "data:image/svg+xml;base64,{$base64}";
            }
        }

        return $base64;
    }
}