<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Posts class
 */
class Helper_Activation
{
    private $helper_post;

    public function __construct()
    {
        $this->helper_post=new EC_Helper_Posts();
    }
    public function activate()
    {
        $files = glob(EC_WOO_BUILDER_PATH.'/library/woo/*.json');
        $lang='en_us';
        foreach ($files as $file) {
            $basename=basename($file, '.json');
            $file_content=file_get_contents($file);

            if ($this->helper_post->exists_email($lang, $basename)==false) {
                $this->helper_post->insert($lang, $basename, addslashes($file_content));
            }
        }
    }
    public function deactivate()
    {
      $purchase_code=get_option('ec_woo_purchase_code', '');
      if (!isset($purchase_code) || $purchase_code=='') {
        delete_option('ec_woo_purchase_code');
        delete_option('ec_woo_show_activate_updates');
      }
    }
}