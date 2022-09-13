<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper class
 */
class Helper_Export
{
    public function __construct()
    {
    }

    public function clean_export_folder()
    {
        try {
            $files = glob(EC_WOO_BUILDER_PATH.'/exports/*');
            foreach ($files as $file) {
                if (is_file($file) && strpos($file, 'index.php') === false) {
                    unlink($file);
                }
            }
            return 0;
        } catch (Exception $e) {
            return -1;
        }
    }
    public function save_file($filename, $content)
    {
        try {
            $fp = fopen($filename, "wb");
            fwrite($fp, $content);
            fclose($fp);
            return 0;
        } catch (Exception $e) {
            return -1;
        }
    }
    public function create_zip($zip_filename, $html_filename, $zip_html_filename)
    {
        try {
            $zip = new ZipArchive();
            $zip->open($zip_filename, ZipArchive::CREATE);
            $zip->addFile($html_filename, $zip_html_filename);
            $zip->close();
            return 0;
        } catch (Exception $e) {
            return 1;
        }
    }
}