<?php
/*
  Plugin Name:  WooMail - WooCommerce Email Customizer
  Description:  The customizing email has never been easier
  Plugin URI:   https://emailcustomizer.com
  Version:      3.0.34
  Author:       CidCode
  Author URI:   https://emailcustomizer.com
  Text Domain:  ec-for-woo-with-drag-drop-builder
  Domain Path: /languages/
  Tested up to: 5.4
*/

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('EC_WOO_BUILDER_SLUG')) {
    //TODO: create new file for getting default values.
    define('EC_WOO_BUILDER_SLUG', 'email-customizer-for-woocommerce-with-drag-drop-builder');
    define('EC_WOO_BUILDER_POST_TYPE', 'ec_woo_ddb_template');
    define('EC_WOO_BUILDER_POST_TYPE_CUSTOM_CODE', 'ecwoo_csc');
    define('EC_WOO_BUILDER_SHORTCODE_PRE', 'ec_woo_');
    define('EC_WOO_BUILDER_VERSION', '3.0.34');
    define('EC_WOO_BUILDER_FILE', __FILE__);
    define('EC_WOO_BUILDER_PATH', plugin_dir_path(__FILE__));
    define('EC_WOO_BUILDER_URL', plugin_dir_url(__FILE__));
    define('EC_WOO_BUILDER_PLUGIN_SLUG', plugin_basename(__FILE__));
    define('EC_WOO_BUILDER_TEXTDOMAIN', 'email-customizer-for-woocommerce-with-drag-drop-builder');
    define('EC_WOO_BUILDER_REQUIRED_WOO_VERSION', '2.4');
    define('EC_WOO_BUILDER_PREVIEW_PAGE', 'ecwoo_preview');
    define('EC_WOO_BUILDER_SHOW_ACTIVATE', 'yes');
    //
    //defaults
    define('EC_WOO_BUILDER_IMG', 32);
    define('EC_WOO_BUILDER_SHOW_IMAGE', 1);
    define('EC_WOO_BUILDER_SHOW_SKU', 1);
    define('EC_WOO_BUILDER_BORDER_PADDING', 3);
    define('EC_WOO_BUILDER_CUSTOM_CSS', '/*add your css here*/');
    define('EC_WOO_BUILDER_REPLACE_MAIL', 1);
    define('EC_WOO_BUILDER_RTL', 0);

    define('EC_WOO_BUILDER_RELATED_ITEMS_COLUMNS', 3);
    define('EC_WOO_BUILDER_RELATED_ITEMS_COUNT', 3);
    define('EC_WOO_BUILDER_RELATED_ITEMS_SHOW_NAME', 1);
    define('EC_WOO_BUILDER_RELATED_ITEMS_SHOW_PRICE', 1);
    define('EC_WOO_BUILDER_RELATED_ITEMS_SHOW_IMAGE', 1);
    define('EC_WOO_BUILDER_RELATED_ITEMS_BY', 'product_type');
    define('EC_WOO_BUILDER_SHOW_CUSTOM_SHORTCODE', 1);

    define('EC_WOO_BUILDER_SHOW_META', 1);
    define('EC_WOO_BUILDER_SHOW_PRODUCT_LINK', 1);
}


require_once(EC_WOO_BUILDER_PATH . '/includes/init.php');

$ec_helper_activate = new Helper_Activation();
register_activation_hook(EC_WOO_BUILDER_FILE, array($ec_helper_activate, 'activate'));
register_deactivation_hook(EC_WOO_BUILDER_FILE, array($ec_helper_activate, 'deactivate'));


$purchase_code = get_option('ec_woo_purchase_code', '');
if (isset($purchase_code) && $purchase_code != '') {
    require_once(EC_WOO_BUILDER_PATH . '/includes/updater/updater.php');
    $myUpdateChecker = WooMail_Puc_v4_Factory::buildUpdateChecker(
        'https://emailcustomizer.com/api/update.php?key=' . $purchase_code . '&version=' . EC_WOO_BUILDER_VERSION.'&web_url='.get_home_url(),
        __FILE__,
        EC_WOO_BUILDER_SLUG
    );
}

$ec_woo_preview_mail = EC_WOO_Preview_Mail::get_instance();
