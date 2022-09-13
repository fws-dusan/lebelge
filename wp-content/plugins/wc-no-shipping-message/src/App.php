<?php
namespace Wnsm;

use RuntimeException;


class App
{
    /**
     * @param string $pluginFile
     */
    public function __construct($pluginFile)
    {
        $this->pluginFile = $pluginFile;
    }

    public function getAssetUrl($asset)
    {
        return plugins_url("assets/{$asset}", $this->pluginFile);
    }

    /**
     * @param string $string
     * @return string
     */
    public function translate($string)
    {
        return __($string, 'wc-no-shipping-methods');
    }

    public static function getVersion($pluginFile)
    {
        $pluginFileAttributes = get_file_data($pluginFile, array('Version' => 'Version'));
        if (empty($pluginFileAttributes['Version'])) {
            throw new RuntimeException("Couldn't detect the version of the plugin.");
        }
        return $pluginFileAttributes['Version'];
    }

    /** @var string */
    private $pluginFile;
}