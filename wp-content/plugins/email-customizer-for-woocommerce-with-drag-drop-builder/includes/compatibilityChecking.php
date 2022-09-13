<?php

if (!defined('ABSPATH')) {
    exit;
}

//TODO: class kimi yaz


/**
 * Is WooCommerce active.
 */
function ec_woo_builder_is_woocommerce_active()
{
    $active_plugins = (array)get_option('active_plugins', array());

    if (is_multisite()) {
        $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
    }

    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
}

function ec_woo_builder_woocommerce_inactive_message()
{
    if (current_user_can('activate_plugins')) :
        if (!class_exists('WooCommerce')) :
            ?>
            <div id="ec-message" class="error">
                <p>
                    <?php
                    printf(
                        __('%sEmail Customizer with Drag Drop Builder needs WooCommerce%s %sWooCommerce%s must be active. Please install & activate WooCommerce.', EC_WOO_BUILDER_TEXTDOMAIN),
                        '<strong>',
                        '</strong><br>',
                        '<a href="http://wordpress.org/extend/plugins/woocommerce/" target="_blank" >',
                        '</a>'
                    ); ?>
                </p>
            </div>
        <?php
        //TODO: use support.emailcustomizer.com
        elseif (version_compare(get_option('woocommerce_db_version'), EC_WOO_BUILDER_REQUIRED_WOO_VERSION, '<')) :
            ?>
            <div id="ec-message" class="error">
                <p>
                    <?php
                    printf(
                        __('%sEmail Customizer with Drag Drop Builder needs WooCommerce%s This version of Email Customizer requires WooCommerce %s or newer. For more information about our WooCommerce version support %sclick here%s.', EC_WOO_BUILDER_TEXTDOMAIN),
                        '<strong>',
                        '</strong><br>',
                        EC_WOO_BUILDER_REQUIRED_WOO_VERSION,
                        '<a href="http://support.cidcode.net" target="_blank" style="color: inheret;" >',
                        '</a>'
                    ); ?>
                </p>
                <div style="clear:both;"></div>
            </div>
        <?php
        endif;
    endif;
}
if (!ec_woo_builder_is_woocommerce_active() || version_compare(get_option('woocommerce_version'), EC_WOO_BUILDER_REQUIRED_WOO_VERSION, '<')) {
    add_action('admin_notices', 'ec_woo_builder_woocommerce_inactive_message');
    return;
}