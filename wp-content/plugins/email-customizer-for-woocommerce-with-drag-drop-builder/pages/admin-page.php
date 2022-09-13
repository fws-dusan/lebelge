<?php


// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
global $current_user;

$helper_menu_position = new Helper_Menu_Position();
$mails = EC_Helper::get_mails_list();

$limit_orders = 10;

$order_collection = new WP_Query(array(
    'post_type' => 'shop_order',
    'post_status' => array_keys(wc_get_order_statuses()),
    'posts_per_page' => $limit_orders,
));

$order_collection = $order_collection->posts;
if (isset($_GET['purchase_code'])) {
  $purchase_code_param = sanitize_text_field($_GET['purchase_code']);
  if (!is_null($purchase_code_param) && !empty($purchase_code_param) && isset($purchase_code_param)) {
    EC_Helper_Posts::save_settings_option('ec_woo_purchase_code', $purchase_code_param);
    //TODO: add other variables which are needed for checking purchase code . take from ajax.php file
  }
}

//remove all files from exports folder
$helper_export = new Helper_Export();
$helper_export->clean_export_folder();
$ec_woo_settings_show_image = get_option('ec_woo_settings_show_image', EC_WOO_BUILDER_SHOW_IMAGE);
$ec_woo_settings_show_sku = get_option('ec_woo_settings_show_sku', EC_WOO_BUILDER_SHOW_SKU);
$ec_woo_settings_custom_css = get_option('ec_woo_settings_custom_css', EC_WOO_BUILDER_CUSTOM_CSS);
$ec_woo_settings_replace_mail = get_option('ec_woo_settings_replace_mail', EC_WOO_BUILDER_REPLACE_MAIL);
$ec_woo_settings_show_custom_shortcode = get_option('ec_woo_settings_show_custom_shortcode', EC_WOO_BUILDER_SHOW_CUSTOM_SHORTCODE);
$ec_woo_settings_rtl = get_option('ec_woo_settings_rtl', EC_WOO_BUILDER_RTL);

//related items
$ec_woo_related_items_columns = get_option('ec_woo_related_items_columns', EC_WOO_BUILDER_RELATED_ITEMS_COLUMNS);
$ec_woo_related_items_count = get_option('ec_woo_related_items_count', EC_WOO_BUILDER_RELATED_ITEMS_COUNT);
$ec_woo_related_items_show_name = get_option('ec_woo_related_items_show_name', EC_WOO_BUILDER_RELATED_ITEMS_SHOW_NAME);
$ec_woo_related_items_show_price = get_option('ec_woo_related_items_show_price', EC_WOO_BUILDER_RELATED_ITEMS_SHOW_PRICE);
$ec_woo_related_items_show_image = get_option('ec_woo_related_items_show_image', EC_WOO_BUILDER_RELATED_ITEMS_SHOW_IMAGE);
$ec_woo_related_items_product_by = get_option('ec_woo_related_items_product_by', EC_WOO_BUILDER_RELATED_ITEMS_BY);
$ec_woo_settings_show_meta = get_option('ec_woo_settings_show_meta', EC_WOO_BUILDER_SHOW_META);
$ec_woo_settings_show_product_link = get_option('ec_woo_settings_show_product_link', EC_WOO_BUILDER_SHOW_PRODUCT_LINK);

$purchase_code = get_option('ec_woo_purchase_code', '');
$is_activated = (isset($purchase_code) && $purchase_code != '');
?>


<style id="page-custom-style">
    <?php echo $ec_woo_settings_custom_css; ?>
</style>


<div class="ec-wrapper  ec-clear <?php echo is_rtl() == true ? 'ec-rtl' : 'ec-ltr'; ?> <?php echo $helper_menu_position->get() == 'right' ? 'ec-panel-right' : ''; ?>">
    <div class="ec-builder-header">
        <div class="ec-builder-controls">
            <div class="ec-row ec-margin-right10">
                <div class="ec-label">
                    <?php _e("Language", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                </div>
                <div class="ec-input">

                    <?php

                    $lang_select = wp_dropdown_languages(array(
                        'id' => 'ec_woo_lang',
                        'name' => 'ec_woo_lang',
                        'languages' => EC_Helper::get_available_languages(),
                        'selected' => EC_Helper::get_locale(),
                        'echo' => 0,
                        'show_available_translations' => false
                    ));


                    echo $lang_select;
                    ?>
                </div>
            </div>

            <div class="ec-row ec-margin-right10" data-step="1"
                 data-intro="Please select the email type for starting customizing,then click 'Next' button"
                 data-position='bottom'>
                <div class="ec-label">
                    <?php _e("Email type", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                </div>
                <div class="ec-input">
                    <select id="ec_woo_type">
                        <option value="">
                            <?php _e("Email to show", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                        </option>
                        <?php
                        if (!empty($mails)) {
                            foreach ($mails as $mail) {
                                if (!in_array($mail->id, array())) {
                                    ?>
                                    <option value="<?php echo $mail->id ?>">
                                        <?php echo ucwords($mail->title); ?>
                                    </option>
                                    <?php
                                }
                            } ?>
                            <option value="customer_partially_refunded_order"><?php _e("Partial Refunded Order", EC_WOO_BUILDER_TEXTDOMAIN); ?></option><?php
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="ec-row ec-margin-right10">
                <div class="ec-label">
                    <?php _e("Order", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                </div>
                <div class="ec-input">
                    <select id="ec_woo_order">
                        <?php if (count($order_collection)) {
                            ?>

                            <?php
                        } else {
                            ?>
                            <option value="">
                                <?php _e("Unfortunately,there is not any order to show", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                            </option>
                            <?php
                        }

                        // Show the orders.
                        foreach ($order_collection as $order_item) {
                            $order = wc_get_order($order_item->ID);
                            if ($order_item->ID !== '') {
                                ?>
                                <option value="<?php echo $order_item->ID ?>">
                                    <?php echo EC_Helper::get_order_number($order) ?>
                                    - <?php echo EC_Helper::get_order_billing_first_name($order) ?> <?php EC_Helper::get_order_billing_last_name($order) ?>
                                    (<?php echo EC_Helper::get_order_billing_email($order) ?>)
                                </option>
                                <?php
                            }
                        }
                        // If more than the orders limit then let the user know.
                        if ($limit_orders <= count($order_collection)) {
                            ?>
                            <option><?php printf(__('...Showing the most recent %u orders', EC_WOO_BUILDER_TEXTDOMAIN), $limit_orders); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

        </div>
        <div class="ec-builder-menu">

            <div id="header-menu-main" class="ec-preview-header active">
                <div class="ec-preview-header-control-container">
                    <div class="ec-preview-header-control-list ec-clear">
                        <div class="ec-preview-header-control-item ec-email-preview ec-preview-header-control-item-disable"
                             data-title="Preview">
                            <div class="ec-preview-header-control-item-icon">
                                <i class="ec-eye"></i>
                            </div>
                        </div>
                        <!-- <div class="ec-preview-header-control-item ec-control-import" data-title="Import" data-has-modal="true" data-modal="#modal-import" data-hint="Nice to meet you" data-position="bottom">
                          <div class="ec-preview-header-control-item-icon">
                            <i class=" ec-export"></i>
                          </div>
                        </div> -->

                        <div class="ec-preview-header-control-item ec-control-export ec-header-has-sub-menu ec-preview-header-control-item-disable"
                             data-title="Export" data-hint="Nice to meet you" data-position="bottom">
                            <div class="ec-preview-header-control-item-icon">
                                <i class="ec-import"></i>
                            </div>
                            <div class="ec-preview-header-control-item-submenu-wrapper">
                                <ul class="ec-preview-header-control-item-submenu">
                                    <li class="ec-preview-header-control-item-submenu-item  ec-export-json">
                                        <div class="ec-preview-header-control-item-submenu-item-icon">
                                            <i class="ec-blank-page"></i>
                                        </div>
                                        <div class="ec-preview-header-control-item-submenu-item-text">
                                            <?php _e("Export as JSON", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                        </div>
                                    </li>
                                    <li class="ec-preview-header-control-item-submenu-item ec-export-html">
                                        <div class="ec-preview-header-control-item-submenu-item-icon">
                                            <i class="ec-blank-page"></i>
                                        </div>
                                        <div class="ec-preview-header-control-item-submenu-item-text">
                                            <?php _e("Export as HTML", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                        </div>
                                    </li>
                                    <li class="ec-preview-header-control-item-submenu-item  ec-export-all">
                                        <div class="ec-preview-header-control-item-submenu-item-icon">
                                            <i class="ec-blank-page"></i>
                                        </div>
                                        <div class="ec-preview-header-control-item-submenu-item-text">
                                            <?php _e("Export All Templates", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="ec-preview-header-control-item ec-control-blank" data-title="Blank"
                             data-hint="Nice to meet you" data-position="bottom">
                            <div class="ec-preview-header-control-item-icon">
                                <i class="ec-blank-page"></i>
                            </div>
                        </div>

                        <div class="ec-preview-header-control-item ec-control-templates" data-title="Templates"
                             data-step="3" data-position='bottom'
                             data-intro="If you do not like created template, open the library and select anyone">
                            <div class="ec-preview-header-control-item-icon">
                                <i class="ec-load-template"></i>
                            </div>
                        </div>
                        <div class="ec-preview-header-control-item ec-control-help" data-title="Help"
                             data-hint="Nice to meet you" data-position="bottom">
                            <div class="ec-preview-header-control-item-icon">
                                <i class="fa fa-info"></i>
                            </div>
                        </div>

                        <div class="ec-preview-header-control-item  ec-control-save ec-control-save-disabled "
                             data-step="4" data-position='bottom'
                             data-intro="After customizing email, you should save it.">
                            <div class="ec-preview-header-control-item-icon ">
                                <div class="ec-save-label">
                                    <?php _e("Save", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                </div>
                                <div class="ec-save-loading">
                                    <img class="loading-icon"
                                         src="<?php echo EC_WOO_BUILDER_URL; ?>assets/img/loading.gif" alt="loading">
                                </div>
                            </div>
                        </div>

                        <div class="ec-preview-header-control-item ec-control-save-sub ec-header-has-sub-menu ec-preview-header-control-item-disable"
                             data-title=" Options"
                             data-step="5" data-position='bottom'
                             data-intro="You can send test email, save as template and save as like another email type.">
                            <div class="ec-preview-header-control-item-icon">
                                <div class="ec-preview-header-control-item-icon-open">
                                    <i class="ec-caret-up"></i>
                                </div>
                                <div class="ec-preview-header-control-item-icon-closed">
                                    <i class="ec-caret-down"></i>
                                </div>
                            </div>

                            <div class="ec-preview-header-control-item-submenu-wrapper">
                                <ul class="ec-preview-header-control-item-submenu">

                                    <li class="ec-preview-header-control-item-submenu-item " data-has-modal="true"
                                        data-modal="#modal-save">
                                        <div class="ec-preview-header-control-item-submenu-item-icon">
                                            <i class="ec-template"></i>
                                        </div>
                                        <div class="ec-preview-header-control-item-submenu-item-text">
                                            <?php _e("Save as Template", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                        </div>
                                    </li>
                                    <li class="ec-preview-header-control-item-submenu-item " data-has-modal="true"
                                        data-modal="#modal-save-as">
                                        <div class="ec-preview-header-control-item-submenu-item-icon">
                                            <i class="ec-template"></i>
                                        </div>
                                        <div class="ec-preview-header-control-item-submenu-item-text">
                                            <?php _e("Save as...", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                        </div>
                                    </li>
                                    <li class="ec-preview-header-control-item-submenu-item" data-has-modal="true"
                                        data-modal="#modal-send-email">
                                        <div class="ec-preview-header-control-item-submenu-item-icon">
                                            <i class="ec-mail"></i>
                                        </div>
                                        <div class="ec-preview-header-control-item-submenu-item-text">
                                            <?php _e("Send Mail", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                        </div>
                                    </li>
                                    <li class="ec-preview-header-control-item-submenu-item ec-control-import"
                                        data-has-modal="true" data-modal="#modal-import">
                                        <div class="ec-preview-header-control-item-submenu-item-icon">
                                            <i class="ec-export"></i>
                                        </div>
                                        <div class="ec-preview-header-control-item-submenu-item-text">
                                            <?php _e("Import", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                        </div>
                                    </li>
                                    <li class="ec-preview-header-control-item-submenu-item ec-control-import-all"
                                        data-has-modal="true" data-modal="#modal-import-all">
                                        <div class="ec-preview-header-control-item-submenu-item-icon">
                                            <i class="ec-export"></i>
                                        </div>
                                        <div class="ec-preview-header-control-item-submenu-item-text">
                                            <?php _e("Import all", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                        </div>
                                    </li>

                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="ec-panel" data-step="2" data-position='right'
         data-intro="You can select element from 'Elements' tab,add new structure element,also you can change the general email settings in here">
        <div class="ec-panel-wrapper">

            <div class="ec-panel-header ec-clear">
                <div class="ec-panel-header-text">
                    <div class="ec-brand-info">
                        <span class="ec-brand-name"><?php _e("WooMail", EC_WOO_BUILDER_TEXTDOMAIN); ?></span>
                        <span class="ec-brand-version">v<?php echo EC_WOO_BUILDER_VERSION; ?></span>
                    </div>
                    <div class="ec-activate-info">
                        <?php echo $is_activated == true ? 'Activated' : '<span class="ec-activate-modal">Not activated. Click for activating</span>'; ?>
                    </div>
                </div>
                <div class="ec-panel-header-icon" data-hint="Nice to meet you" data-position="right">
                    <i class="ec-element"></i>
                </div>
            </div>
            <div class="ec-panel-content ec-tab-elements ec-active">
                <div class="ec-panel-tabs ec-clear">
                    <div class="ec-panel-tab-item active" data-tab-content="elements" data-hint="Nice to meet you"
                         data-position="bottom">
                        <div class="ec-panel-tab-icon">
                            <i class="ec-element"></i>
                        </div>
                        <div class="ec-panel-tab-text">

                            <?php _e("Elements", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                        </div>
                    </div>
                    <div class="ec-panel-tab-item " data-tab-content="structure" data-hint="Nice to meet you"
                         data-position="bottom">
                        <div class="ec-panel-tab-icon">
                            <i class="ec-structure"></i>
                        </div>
                        <div class="ec-panel-tab-text">

                            <?php _e("Structure", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                        </div>
                    </div>
                    <div class="ec-panel-tab-item " data-tab-content="settings" data-hint="Nice to meet you"
                         data-position="bottom">
                        <div class="ec-panel-tab-icon">
                            <i class="ec-settings"></i>
                        </div>
                        <div class="ec-panel-tab-text">

                            <?php _e("Settings", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                        </div>
                    </div>
                </div>
                <div class="ec-panel-tab-content">
                    <div class="ec-panel-tab-content-item ec-panel-tab-padding active" data-tab="elements">

                        <div class="ec-panel-tab-content-search" data-hint="Nice to meet you" data-position="right">
                            <input type="text" name="" placeholder="Search element here..." value="">
                            <i class="ec-search"></i>
                        </div>

                        <div class="ec-panel-accordion">

                        </div>
                    </div>

                    <div class="ec-panel-tab-content-item ec-panel-tab-padding " data-tab="structure">
                        <div class="ec-structure-elements">

                            <div class="ec-structure-element-item" data-id="1">
                                <div class="ec-structure-element-preview">
                                    <div class="ec-structure-element-item-container ec-clear">
                                        <div class="ec-structure-element-col-12 ec-structure-element-column">
                                        </div>
                                    </div>
                                </div>
                                <div class="ec-structure-element-view">

                                </div>
                            </div>

                            <div class="ec-structure-element-item" data-id="2">
                                <div class="ec-structure-element-preview">
                                    <div class="ec-structure-element-item-container ec-clear">
                                        <div class="ec-structure-element-col-6 ec-structure-element-column">
                                        </div>
                                        <div class="ec-structure-element-col-6 ec-structure-element-column">
                                        </div>
                                    </div>
                                </div>
                                <div class="ec-structure-element-view">

                                </div>
                            </div>

                            <div class="ec-structure-element-item" data-id="3">
                                <div class="ec-structure-element-preview">
                                    <div class="ec-structure-element-item-container ec-clear">
                                        <div class="ec-structure-element-col-4 ec-structure-element-column">
                                        </div>
                                        <div class="ec-structure-element-col-4 ec-structure-element-column">
                                        </div>
                                        <div class="ec-structure-element-col-4 ec-structure-element-column">
                                        </div>
                                    </div>
                                </div>
                                <div class="ec-structure-element-view">

                                </div>
                            </div>

                            <div class="ec-structure-element-item" data-id="4">
                                <div class="ec-structure-element-preview">
                                    <div class="ec-structure-element-item-container ec-clear">
                                        <div class="ec-structure-element-col-3 ec-structure-element-column">
                                        </div>
                                        <div class="ec-structure-element-col-3 ec-structure-element-column">
                                        </div>
                                        <div class="ec-structure-element-col-3 ec-structure-element-column">
                                        </div>
                                        <div class="ec-structure-element-col-3 ec-structure-element-column">
                                        </div>
                                    </div>
                                </div>
                                <div class="ec-structure-element-view">
                                    xxx
                                </div>
                            </div>

                            <div class="ec-structure-element-item" data-id="5">
                                <div class="ec-structure-element-preview">
                                    <div class="ec-structure-element-item-container ec-clear">
                                        <div class="ec-structure-element-col-8 ec-structure-element-column">
                                        </div>
                                        <div class="ec-structure-element-col-4 ec-structure-element-column">
                                        </div>
                                    </div>
                                </div>
                                <div class="ec-structure-element-view">

                                </div>
                            </div>

                            <div class="ec-structure-element-item" data-id="6">
                                <div class="ec-structure-element-preview">
                                    <div class="ec-structure-element-item-container ec-clear">
                                        <div class="ec-structure-element-col-4 ec-structure-element-column">
                                        </div>
                                        <div class="ec-structure-element-col-8 ec-structure-element-column">
                                        </div>
                                    </div>
                                </div>
                                <div class="ec-structure-element-view">
                                    xxx
                                </div>
                            </div>

                            <div class="ec-structure-element-item" data-id="7">
                                <div class="ec-structure-element-preview">
                                    <div class="ec-structure-element-item-container ec-clear">
                                        <div class="ec-structure-element-col-3 ec-structure-element-column">
                                        </div>
                                        <div class="ec-structure-element-col-6 ec-structure-element-column">
                                        </div>
                                        <div class="ec-structure-element-col-3 ec-structure-element-column">
                                        </div>
                                    </div>
                                </div>
                                <div class="ec-structure-element-view">

                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="ec-panel-tab-content-item" data-tab="settings">
                        <div class="ec-panel-settings">
                            <div class="ec-panel-settings-wrapper">
                                <div class="ec-panel-settings-item " id="ec-woo-email-settings-panel">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="ec-blank-page"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            <?php _e("Email Settings", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">
                                        <div class="ec-panel-settings-row ec-panel-settings-row-color">
                                            <div class="ec-panel-settings-label">
                                                <?php _e("Background Color", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                            <div class="ec-panel-settings-color-container ec-clear">
                                                <div id="settings-bg-color"
                                                     class="ec-panel-settings-color-box setting-bg">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row ec-panel-settings-row-color">
                                            <div class="ec-panel-settings-label">
                                                <?php _e("Content Color", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                            <div class="ec-panel-settings-color-container ec-clear">
                                                <div id="settings-content-color"
                                                     class="ec-panel-settings-color-box setting-bg">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                <?php _e("Email width", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                                <span id="settings-email-width-value" class="value" style="display:none">640</span>
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-email-width" type="number"
                                                        step="5" name="" value="640"
                                                       min="480" max="900">
                                            </div>
                                            <div class="ec-panel-settings-secondary-text">
                                                <?php _e("Email width must be 480 - 900", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label ec-inline-block">
                                                <?php _e("Replace Email for ", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                                <span class="ec-woo-replace-email-type"></span>
                                            </div>
                                            <div class="ec-panel-settings-checkbox-container">
                                                <input id="ec-settings-replace-mail-for-type" type="checkbox"
                                                       class="ec-panel-settings-checkbox ec-settings-item single-request"/>
                                            </div>
                                            <div class="ec-panel-settings-secondary-text">
                                                <?php _e("If you want to use the new templates, please turn on. Otherwise, you will use default email templates.<strong> it will activate replacing all emails</strong> ", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="ec-panel-settings-item active ">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="ec-blank-page"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            <?php _e("General Settings", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">

                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                <?php _e("Panel position", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <label for="ec-settings-menu-left">Left</label>
                                                <input id="ec-settings-menu-left" type="radio" name="ec-settings-menu"
                                                       class="ec-settings-menu"
                                                       value="left" <?php echo $helper_menu_position->get() == 'left' ? 'checked' : ''; ?> >
                                                <hr>
                                                <label for="ec-settings-menu-right">Right</label>
                                                <input id="ec-settings-menu-right" type="radio" name="ec-settings-menu"
                                                       class="ec-settings-menu"
                                                       value="right" <?php echo $helper_menu_position->get() == 'right' ? 'checked' : ''; ?> >
                                            </div>
                                            <div class="ec-panel-settings-secondary-text">
                                                <?php _e("After selecting position will save automatically", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">

                                                <?php _e("Table cell padding", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="ec-settings-border-padding" type="number"
                                                       class="ec-panel-settings-input ec-settings-item"
                                                       value="<?php echo get_option('ec_woo_settings_border_padding', EC_WOO_BUILDER_BORDER_PADDING); ?>"
                                                       min="0" height="100">
                                            </div>
                                            <div class="ec-panel-settings-secondary-text">

                                                <?php _e("The table which shows the product list", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label ec-inline-block">
                                                <?php _e("Replace Email for all types", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                            <div class="ec-panel-settings-checkbox-container">
                                                <input id="ec-settings-replace-mail" type="checkbox"
                                                       class="ec-panel-settings-checkbox ec-settings-item"
                                                    <?php echo($ec_woo_settings_replace_mail == '1' ? 'checked="checked"' : ''); ?>>
                                            </div>
                                            <div class="ec-panel-settings-secondary-text">
                                                <?php _e("If you want to use the new templates, please turn on. Otherwise, you will use default email templates.<strong> it will activate replacing all emails</strong> ", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label ec-inline-block">
                                                <?php _e("RTL Support", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                            <div class="ec-panel-settings-checkbox-container">
                                                <input id="ec-settings-rtl" type="checkbox"
                                                       class="ec-panel-settings-checkbox ec-settings-item"
                                                    <?php echo($ec_woo_settings_rtl == '1' ? 'checked="checked"' : ''); ?>>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label ec-inline-block">

                                                <?php _e("Show product sku", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                            <div class="ec-panel-settings-checkbox-container">
                                                <input id="ec-settings-show-product-sku" type="checkbox"
                                                       class="ec-panel-settings-checkbox ec-settings-item"
                                                    <?php echo($ec_woo_settings_show_sku == '1' ? 'checked="checked"' : ''); ?>>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label ec-inline-block">
                                                <?php _e("Show product image", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                            <div class="ec-panel-settings-checkbox-container">
                                                <input id="ec-settings-show-product-img" type="checkbox"
                                                       class="ec-panel-settings-checkbox ec-settings-item"
                                                    <?php echo($ec_woo_settings_show_image == '1' ? 'checked="checked"' : ''); ?>>
                                            </div>
                                        </div>

                                        <div class="ec-panel-settings-row ec-settings-product-image"
                                             style="<?php echo($ec_woo_settings_show_image == '1' ? '' : 'display:none'); ?>">
                                            <div class="ec-panel-settings-label">
                                                <?php _e("Product image width", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="ec-settings-product-img-width" type="number"
                                                       class="ec-panel-settings-input ec-settings-item"
                                                       value="<?php echo get_option('ec_woo_settings_image_width', EC_WOO_BUILDER_IMG); ?>"
                                                       min="0" height="100">
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row ec-settings-product-image"
                                             style="<?php echo($ec_woo_settings_show_image == '1' ? '' : 'display:none'); ?>">
                                            <div class="ec-panel-settings-label">

                                                <?php _e("Product image height", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="ec-settings-product-img-height" type="number"
                                                       class="ec-panel-settings-input ec-settings-item"
                                                       value="<?php echo get_option('ec_woo_settings_image_height', EC_WOO_BUILDER_IMG); ?>"
                                                       min="0" height="100">
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label ec-inline-block">
                                                <?php _e("Show Custom Shortcode in the menu", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                            <div class="ec-panel-settings-checkbox-container">
                                                <input id="ec-settings-show-custom-shortcode" type="checkbox"
                                                       class="ec-panel-settings-checkbox ec-settings-item"
                                                    <?php echo($ec_woo_settings_show_custom_shortcode == '1' ? 'checked="checked"' : ''); ?>>
                                            </div>
                                            <div class="ec-panel-settings-secondary-text">
                                                <?php _e("If you want to hide the custom shortcode page, please turn off<br><strong>After saving please refresh page for seeing the result</strong>", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label ec-inline-block">
                                                <?php _e("Show information which is added from other plugins ", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                            <div class="ec-panel-settings-checkbox-container">
                                                <input id="ec-settings-show-meta" type="checkbox"
                                                       class="ec-panel-settings-checkbox ec-settings-item single-request"
                                                    <?php echo($ec_woo_settings_show_meta == '1' ? 'checked="checked"' : ''); ?>/>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label ec-inline-block">
                                                <?php _e("Show product item link", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                            </div>
                                            <div class="ec-panel-settings-checkbox-container">
                                                <input id="ec-settings-show-product-link" type="checkbox"
                                                       class="ec-panel-settings-checkbox ec-settings-item single-request"
                                                    <?php echo($ec_woo_settings_show_product_link == '1' ? 'checked="checked"' : ''); ?>/>
                                            </div>
                                        </div>
                                        <input id="save_general_settings" type="button"
                                               class="ec-panel-settings-button" value="<?php _e("SAVE", EC_WOO_BUILDER_TEXTDOMAIN); ?>">
                                    </div>
                                </div>
                                <div class="ec-panel-settings-item active ">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="ec-blank-page"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            <?php _e("Related Items Settings", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">

                                      <div class="ec-panel-settings-row">
                                          <div class="ec-panel-settings-label ec-inline-block">
                                              <?php _e("Columns", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                          </div>
                                          <div class="ec-panel-settings-input-container">
                                              <input id="ec-related-items-columns" type="number"
                                              class="ec-panel-settings-input ec-settings-item"
                                              value="<?php echo $ec_woo_related_items_columns; ?>"
                                              min="1" max="3" height="3">
                                          </div>
                                      </div>
                                      <div class="ec-panel-settings-row">
                                          <div class="ec-panel-settings-label ec-inline-block">
                                              <?php _e("Product count", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                          </div>
                                          <div class="ec-panel-settings-input-container">
                                              <input id="ec-related-items-count" type="number"
                                              class="ec-panel-settings-input ec-settings-item"
                                              value="<?php echo $ec_woo_related_items_count; ?>"
                                              min="3" height="20">
                                          </div>
                                      </div>
                                      <div class="ec-panel-settings-row">
                                          <div class="ec-panel-settings-label">
                                              <?php _e("Products By", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                          </div>
                                          <div class="ec-panel-settings-input-container">
                                            <select id="ec-related-items-products-by" class="ec-panel-settings-select ec-settings-item" name="">
                                              <option value="product_type" <?php echo $ec_woo_related_items_product_by=='product_type'?'selected':''; ?>>
                                                 <?php _e("Product Type", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                               </option>
                                               <option value="product_cat" <?php echo $ec_woo_related_items_product_by=='product_cat'?'selected':''; ?>>
                                                  <?php _e("Product Category", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                                </option>
                                            </select>
                                          </div>
                                      </div>
                                      <div class="ec-panel-settings-row">
                                          <div class="ec-panel-settings-label ec-inline-block">
                                              <?php _e("Show name", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                          </div>
                                          <div class="ec-panel-settings-checkbox-container">
                                              <input id="ec-related-items-show-name" type="checkbox"
                                                     class="ec-panel-settings-checkbox ec-settings-item"
                                                  <?php echo($ec_woo_related_items_show_name == '1' ? 'checked="checked"' : ''); ?>>
                                          </div>
                                      </div>
                                      <div class="ec-panel-settings-row">
                                          <div class="ec-panel-settings-label ec-inline-block">
                                              <?php _e("Show price", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                          </div>
                                          <div class="ec-panel-settings-checkbox-container">
                                              <input id="ec-related-items-show-price" type="checkbox"
                                                     class="ec-panel-settings-checkbox ec-settings-item"
                                                  <?php echo($ec_woo_related_items_show_price == '1' ? 'checked="checked"' : ''); ?>>
                                          </div>
                                      </div>
                                      <div class="ec-panel-settings-row">
                                          <div class="ec-panel-settings-label ec-inline-block">
                                              <?php _e("Show image", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                          </div>
                                          <div class="ec-panel-settings-checkbox-container">
                                              <input id="ec-related-items-show-image" type="checkbox"
                                                     class="ec-panel-settings-checkbox ec-settings-item"
                                                  <?php echo($ec_woo_related_items_show_image == '1' ? 'checked="checked"' : ''); ?>>
                                          </div>
                                      </div>

                                        <input id="save_related_items" type="button"
                                               class="ec-panel-settings-button" value="<?php _e("SAVE", EC_WOO_BUILDER_TEXTDOMAIN); ?>">
                                    </div>
                                </div>
                                <div class="ec-panel-settings-item active collapsed">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="ec-blank-page"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            <?php _e("Custom CSS", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content" style="display:none">

                                        <div class="ec-panel-settings-row">

                                            <div class="ec-panel-settings-input-container">
                                                <textarea id="custom_css"
                                                          class="custom-css"><?php echo $ec_woo_settings_custom_css; ?></textarea>
                                                <input id="save_custom_css" type="button"
                                                       class="ec-panel-settings-button" value="<?php _e("SAVE", EC_WOO_BUILDER_TEXTDOMAIN); ?>">
                                            </div>

                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="ec-panel-content ec-tab-styles ">
                <div class="ec-panel-tabs ec-clear ec-tabs-2">
                    <div class="ec-panel-tab-item active" data-tab-content="content">
                        <div class="ec-panel-tab-icon">
                            <i class="ec-edit"></i>
                        </div>
                        <div class="ec-panel-tab-text">
                            <?php _e("Content", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                        </div>
                    </div>
                    <div class="ec-panel-tab-item " data-tab-content="style">
                        <div class="ec-panel-tab-icon">
                            <i class="ec-font"></i>
                        </div>
                        <div class="ec-panel-tab-text">
                            <?php _e("Style", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                        </div>
                    </div>
                </div>
                <div class="ec-panel-tab-content ">
                    <div class="ec-panel-tab-content-item ec-panel-tab-padding " data-tab="content">
                        <div class="ec-panel-accordion">
                            <textarea id="content-text-editor" class="ec-panel-content-editor"></textarea>
                        </div>
                    </div>

                    <div class="ec-panel-tab-content-item active" data-tab="style">
                        <div class="ec-panel-settings">
                            <div class="ec-panel-settings-wrapper">

                                <div class="ec-panel-settings-item " data-group="row">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="fa fa-file"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            Row Style
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">

                                        <div class="ec-panel-settings-row ec-panel-settings-row-color">
                                            <div class="ec-panel-settings-label">
                                                Background Color
                                            </div>
                                            <div class="ec-panel-settings-color-container ec-clear">
                                                <div id="row-bg-color"
                                                     class="ec-panel-settings-color-box style-bg-color">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="ec-panel-settings-row ec-panel-settings-row-color">
                                            <div class="ec-panel-settings-label">
                                                Content Color
                                            </div>
                                            <div class="ec-panel-settings-color-container ec-clear">
                                                <div id="row-content-color"
                                                     class="ec-panel-settings-color-box style-bg-color">
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>

                                <div class="ec-panel-settings-item " data-group="button" data-type="general">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="fa fa-file"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            General
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">

                                        <div class="ec-panel-settings-row ec-panel-settings-row-color">
                                            <div class="ec-panel-settings-label">
                                                Background Color
                                            </div>
                                            <div class="ec-panel-settings-color-container ec-clear">
                                                <div id="settings-button-bg-color"
                                                     class="ec-panel-settings-color-box style-bg-color">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row ec-panel-settings-row-color">
                                            <div class="ec-panel-settings-label">
                                                Text Color
                                            </div>
                                            <div class="ec-panel-settings-color-container ec-clear">
                                                <div id="settings-button-text-color"
                                                     class="ec-panel-settings-color-box style-bg-color">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label ec-inline-block">
                                                Auto width
                                            </div>
                                            <div class="ec-panel-settings-checkbox-container">
                                                <input id="settings-button-width-auto" type="checkbox"
                                                       class="ec-panel-settings-checkbox">
                                            </div>

                                        </div>
                                        <div id="settings-button-width-row" class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                <span id="settings-button-width-value" class="value"></span>
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-button-width-slider" type="range"
                                                       class="ec-panel-settings-slider" min="0" max="100">
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Align
                                            </div>
                                            <div id="settings-button-align"
                                                 class="ec-panel-settings-align-container ec-clear ec-align-3">
                                                <div class="ec-panel-settings-align-element active" data-type="left">
                                                    <i class="fa fa-align-left"></i>
                                                </div>
                                                <div class="ec-panel-settings-align-element" data-type="center">
                                                    <i class="fa fa-align-center"></i>
                                                </div>
                                                <div class="ec-panel-settings-align-element" data-type="right">
                                                    <i class="fa fa-align-right"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Padding
                                            </div>
                                            <div id="settings-button-padding"
                                                 class="ec-panel-settings-dimension-container ">
                                                <ul class="ec-panel-settings-dimension-list ec-clear ">
                                                    <li class="ec-panel-settings-dimension-item active">
                                                        <input type="number" class="ec-padding" min="0" data-type="top">
                                                        <label class="ec-panel-settings-dimension-label">Top</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item ">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="right">
                                                        <label class="ec-panel-settings-dimension-label">Right</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="bottom">
                                                        <label class="ec-panel-settings-dimension-label">Bottom</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="left">
                                                        <label class="ec-panel-settings-dimension-label">Left</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <a href="javascript:void(0)"
                                                           class="ec-panel-settings-dimension-button">
                                                            <i class="fa fa-link"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                        </div>

                                    </div>
                                </div>

                                <div class="ec-panel-settings-item " data-group="button">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="fa fa-square"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            Button
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Text
                                            </div>
                                            <textarea id="settings-button-text" class="ec-panel-settings-textarea"></textarea>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                URL
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-button-url" type="text"
                                                       class="ec-panel-settings-text">
                                            </div>

                                        </div>

                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Line height
                                                <span id="settings-button-line-height-value" class="value"></span>
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-button-line-height" type="range"
                                                       class="ec-panel-settings-slider" min="15" max="55" step="5">
                                            </div>

                                        </div>

                                        <div class="ec-panel-settings-row ec-panel-settings-row-color">
                                            <div class="ec-panel-settings-label">
                                                Border color
                                            </div>
                                            <div class="ec-panel-settings-color-container ec-clear">
                                                <div id="settings-button-border-color"
                                                     class="ec-panel-settings-color-box style-bg-color"
                                                     style="background-color: rgb(0, 0, 0);">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label ec-inline-block ">
                                                Border type
                                            </div>
                                            <div class="ec-panel-settings-select-container ec-clear">
                                                <select id="settings-button-border-type"
                                                        class="ec-panel-settings-select">
                                                    <option value="solid">Solid</option>
                                                    <option value="dotted">Dotted</option>
                                                    <option value="dashed">Dashed</option>
                                                </select>
                                            </div>

                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Border width
                                                <span id="settings-button-border-width-value" class="value"></span>
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-button-border-width" type="range"
                                                       class="ec-panel-settings-slider" min="0" max="30">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="ec-panel-settings-item " data-group="divider" data-type="general">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="fa fa-file"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            General
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Width
                                                <span id="settings-divider-width-value" class="value">100%</span>
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-divider-width" type="range"
                                                       class="ec-panel-settings-slider" min="0" max="100">
                                            </div>

                                        </div>

                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Alignment
                                            </div>
                                            <div id="settings-divider-align"
                                                 class="ec-panel-settings-align-container ec-clear ec-align-3">
                                                <div class="ec-panel-settings-align-element active" data-type="left">
                                                    <i class="fa fa-align-left"></i>
                                                </div>
                                                <div class="ec-panel-settings-align-element" data-type="center">
                                                    <i class="fa fa-align-center"></i>
                                                </div>
                                                <div class="ec-panel-settings-align-element" data-type="right">
                                                    <i class="fa fa-align-right"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Padding
                                            </div>
                                            <div id="settings-divider-padding"
                                                 class="ec-panel-settings-dimension-container ">
                                                <ul class="ec-panel-settings-dimension-list ec-clear ">
                                                    <li class="ec-panel-settings-dimension-item active">
                                                        <input type="number" class="ec-padding" min="0" data-type="top">
                                                        <label class="ec-panel-settings-dimension-label">Top</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item ">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="right">
                                                        <label class="ec-panel-settings-dimension-label">Right</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="bottom">
                                                        <label class="ec-panel-settings-dimension-label">Bottom</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="left">
                                                        <label class="ec-panel-settings-dimension-label">Left</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <a href="javascript:void(0)"
                                                           class="ec-panel-settings-dimension-button">
                                                            <i class="fa fa-link"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                        </div>


                                    </div>
                                </div>
                                <div class="ec-panel-settings-item " data-group="divider">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="fa fa-arrows-h"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            Divider
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">

                                        <div class="ec-panel-settings-row ec-panel-settings-row-color">
                                            <div class="ec-panel-settings-label">
                                                Line color
                                            </div>
                                            <div class="ec-panel-settings-color-container ec-clear">
                                                <div id="settings-divider-line-color"
                                                     class="ec-panel-settings-color-box style-bg-color">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label ec-inline-block ">
                                                Line type
                                            </div>
                                            <div class="ec-panel-settings-select-container ec-clear">
                                                <select id="settings-divider-line-type"
                                                        class="ec-panel-settings-select">
                                                    <option value="solid">Solid</option>
                                                    <option value="dotted">Dotted</option>
                                                    <option value="dashed">Dashed</option>
                                                </select>
                                            </div>

                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Line height
                                                <span id="settings-divider-line-height-value" class="value">xxx</span>
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-divider-line-height" type="range"
                                                       class="ec-panel-settings-slider" min="0" max="30">
                                            </div>
                                        </div>


                                    </div>
                                </div>


                                <div class="ec-panel-settings-item " data-group="text">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="fa fa-file"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            General
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">

                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Padding
                                            </div>
                                            <div id="settings-text-padding"
                                                 class="ec-panel-settings-dimension-container ">
                                                <ul class="ec-panel-settings-dimension-list ec-clear ">
                                                    <li class="ec-panel-settings-dimension-item active">
                                                        <input type="number" class="ec-padding" min="0" data-type="top">
                                                        <label class="ec-panel-settings-dimension-label">Top</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item ">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="right">
                                                        <label class="ec-panel-settings-dimension-label">Right</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="bottom">
                                                        <label class="ec-panel-settings-dimension-label">Bottom</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="left">
                                                        <label class="ec-panel-settings-dimension-label">Left</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <a href="javascript:void(0)"
                                                           class="ec-panel-settings-dimension-button">
                                                            <i class="fa fa-link"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                        </div>


                                    </div>
                                </div>

                                <div class="ec-panel-settings-item " data-group="spacer">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="fa fa-arrows-v"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            Spacer
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">

                                        <div class="ec-panel-settings-row ec-panel-settings-row-color">
                                            <div class="ec-panel-settings-label">
                                                Content color
                                            </div>
                                            <div class="ec-panel-settings-color-container ec-clear">
                                                <div id="spacer-content-bg-color"
                                                     class="ec-panel-settings-color-box style-bg-color">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Height
                                                <span id="settings-spacer-height" class="value"></span>
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-spacer-slider" type="range"
                                                       class="ec-panel-settings-slider" min="0" max="200" height="100">
                                            </div>

                                        </div>


                                    </div>
                                </div>

                                <div class="ec-panel-settings-item " data-group="video" data-type="general">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="fa fa-file"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            General
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Padding
                                            </div>
                                            <div id="settings-video-padding"
                                                 class="ec-panel-settings-dimension-container ">
                                                <ul class="ec-panel-settings-dimension-list ec-clear ">
                                                    <li class="ec-panel-settings-dimension-item active">
                                                        <input type="number" class="ec-padding" min="0" data-type="top">
                                                        <label class="ec-panel-settings-dimension-label">Top</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item ">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="right">
                                                        <label class="ec-panel-settings-dimension-label">Right</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="bottom">
                                                        <label class="ec-panel-settings-dimension-label">Bottom</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="left">
                                                        <label class="ec-panel-settings-dimension-label">Left</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <a href="javascript:void(0)"
                                                           class="ec-panel-settings-dimension-button">
                                                            <i class="fa fa-link"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                                <div class="ec-panel-settings-item " data-group="video">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="fa fa-video-camera"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            Video
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                URL
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-video-url" type="text"
                                                       class="ec-panel-settings-input" placeholder="URL...">
                                            </div>
                                            <div class="ec-panel-settings-secondary-text">
                        <span>
                          Add a YouTube or Vimeo URL to automatically generate a preview image. The image will link to the provided URL.
                        </span>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="ec-panel-settings-item " data-group="image" data-type="general">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="fa fa-file"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            General
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label ec-inline-block">
                                                Auto width
                                            </div>
                                            <div class="ec-panel-settings-checkbox-container">
                                                <input id="settings-image-width-auto" type="checkbox"
                                                       class="ec-panel-settings-checkbox" checked="checked">
                                            </div>
                                        </div>

                                        <div id="settings-image-width-row" class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                <span id="settings-image-width-value" class="value">100%</span>
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-image-width" type="range"
                                                       class="ec-panel-settings-slider" min="0" value="100"
                                                       height="100">
                                            </div>

                                        </div>
                                        <div class="ec-panel-settings-row" style="display:none">
                                            <div class="ec-panel-settings-label">
                                                Height
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-image-height" type="number"
                                                       class="ec-panel-settings-input" min="0" height="100">
                                            </div>

                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Align
                                            </div>
                                            <div id="settings-image-align"
                                                 class="ec-panel-settings-align-container ec-clear ec-align-3">
                                                <div class="ec-panel-settings-align-element active" data-type="left">
                                                    <i class="fa fa-align-left"></i>
                                                </div>
                                                <div class="ec-panel-settings-align-element" data-type="center">
                                                    <i class="fa fa-align-center"></i>
                                                </div>
                                                <div class="ec-panel-settings-align-element" data-type="right">
                                                    <i class="fa fa-align-right"></i>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Padding
                                            </div>
                                            <div id="settings-image-padding"
                                                 class="ec-panel-settings-dimension-container ">
                                                <ul class="ec-panel-settings-dimension-list ec-clear ">
                                                    <li class="ec-panel-settings-dimension-item active">
                                                        <input type="number" class="ec-padding" min="0" data-type="top">
                                                        <label class="ec-panel-settings-dimension-label">Top</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item ">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="right">
                                                        <label class="ec-panel-settings-dimension-label">Right</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="bottom">
                                                        <label class="ec-panel-settings-dimension-label">Bottom</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="left">
                                                        <label class="ec-panel-settings-dimension-label">Left</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <a href="javascript:void(0)"
                                                           class="ec-panel-settings-dimension-button">
                                                            <i class="fa fa-link"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                                <div class="ec-panel-settings-item " data-group="image">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="fa fa-picture-o"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            Image
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">

                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-image-change" type="button"
                                                       class="ec-panel-settings-button" min="0" height="100"
                                                       value="Change image">
                                            </div>

                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Source URL
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-image-source-url" type="text"
                                                       class="ec-panel-settings-input">
                                            </div>

                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Alternative text
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-image-alt-text" type="text"
                                                       class="ec-panel-settings-input">
                                            </div>

                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                URL
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-image-url" type="text"
                                                       class="ec-panel-settings-input" placeholder="URL...">
                                            </div>

                                        </div>


                                    </div>
                                </div>

                                <div class="ec-panel-settings-item " data-group="social" data-type="general">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="fa fa-file"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            General
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">

                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Align
                                            </div>
                                            <div id="settings-social-align"
                                                 class="ec-panel-settings-align-container ec-clear ec-align-3">
                                                <div class="ec-panel-settings-align-element active" data-type="left">
                                                    <i class="fa fa-align-left"></i>
                                                </div>
                                                <div class="ec-panel-settings-align-element" data-type="center">
                                                    <i class="fa fa-align-center"></i>
                                                </div>
                                                <div class="ec-panel-settings-align-element" data-type="right">
                                                    <i class="fa fa-align-right"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Padding
                                            </div>
                                            <div id="settings-social-padding"
                                                 class="ec-panel-settings-dimension-container ">
                                                <ul class="ec-panel-settings-dimension-list ec-clear ">
                                                    <li class="ec-panel-settings-dimension-item active">
                                                        <input type="number" class="ec-padding" min="0" data-type="top">
                                                        <label class="ec-panel-settings-dimension-label">Top</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item ">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="right">
                                                        <label class="ec-panel-settings-dimension-label">Right</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="bottom">
                                                        <label class="ec-panel-settings-dimension-label">Bottom</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <input type="number" class="ec-padding" min="0"
                                                               data-type="left">
                                                        <label class="ec-panel-settings-dimension-label">Left</label>
                                                    </li>
                                                    <li class="ec-panel-settings-dimension-item">
                                                        <a href="javascript:void(0)"
                                                           class="ec-panel-settings-dimension-button">
                                                            <i class="fa fa-link"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                        </div>

                                    </div>
                                </div>

                                <div class="ec-panel-settings-item " data-group="social">
                                    <div class="ec-panel-settings-title">
                                        <div class="ec-panel-settings-icon">
                                            <i class="fa fa-share-square"></i>
                                        </div>
                                        <div class="ec-panel-settings-name">
                                            Social
                                        </div>
                                        <div class="ec-panel-settings-indicator">
                                            <i class="ec-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="ec-panel-settings-content">

                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label ec-inline-block">
                                                Icons type
                                            </div>
                                            <div class="ec-panel-settings-select-container ec-clear">
                                                <select id="settings-social-type" class="ec-panel-settings-select">
                                                    <option value="1">Set 1</option>
                                                    <option value="2">Set 2</option>
                                                    <option value="3">Set 3</option>
                                                    <option value="4">Set 4</option>
                                                    <option value="5">Set 5</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Icon spacing
                                                <span id="settings-social-spacing-value" class="value"></span>
                                            </div>
                                            <div class="ec-panel-settings-input-container">
                                                <input id="settings-social-spacing" type="range"
                                                       class="ec-panel-settings-slider" min="1" step="1" max="20">
                                            </div>
                                        </div>
                                        <div class="ec-panel-settings-row">
                                            <div class="ec-panel-settings-label">
                                                Icons
                                            </div>

                                        </div>
                                        <div class="ec-social-elements">

                                            <div class="ec-panel-settings-row" data-type="instagram">
                                                <div class="ec-social-handle">
                                                </div>
                                                <div class="ec-panel-settings-label ec-inline-block">
                                                    Instagram
                                                </div>
                                                <div class="ec-panel-settings-checkbox-container">
                                                    <input type="checkbox" class="ec-panel-settings-checkbox"/>
                                                </div>
                                                <div class="ec-panel-settings-input-container">
                                                    <input type="text" class="ec-panel-settings-input ec-social-url"
                                                           placeholder="URL..."/>
                                                </div>
                                                <div class="ec-panel-settings-input-container ec-social-img-url-wrapper">
                                                    <input type="text" class="ec-panel-settings-input ec-social-img-url"
                                                           placeholder="Image URL"/>
                                                </div>
                                            </div>

                                            <div class="ec-panel-settings-row" data-type="pinterest">
                                                <div class="ec-social-handle">
                                                </div>
                                                <div class="ec-panel-settings-label ec-inline-block">
                                                    Pinterest
                                                </div>
                                                <div class="ec-panel-settings-checkbox-container">
                                                    <input type="checkbox" class="ec-panel-settings-checkbox"/>
                                                </div>
                                                <div class="ec-panel-settings-input-container">
                                                    <input type="text" class="ec-panel-settings-input ec-social-url"
                                                           placeholder="URL..."/>
                                                </div>
                                                <div class="ec-panel-settings-input-container ec-social-img-url-wrapper">
                                                    <input type="text" class="ec-panel-settings-input ec-social-img-url"
                                                           placeholder="Image URL"/>
                                                </div>
                                            </div>

                                            <div class="ec-panel-settings-row" data-type="google-plus">
                                                <div class="ec-social-handle">
                                                </div>
                                                <div class="ec-panel-settings-label ec-inline-block">
                                                    Google plus
                                                </div>
                                                <div class="ec-panel-settings-checkbox-container">
                                                    <input type="checkbox" class="ec-panel-settings-checkbox"/>
                                                </div>
                                                <div class="ec-panel-settings-input-container">
                                                    <input type="text" class="ec-panel-settings-input ec-social-url"
                                                           placeholder="URL..."/>
                                                </div>
                                                <div class="ec-panel-settings-input-container ec-social-img-url-wrapper">
                                                    <input type="text" class="ec-panel-settings-input ec-social-img-url"
                                                           placeholder="Image URL"/>
                                                </div>
                                            </div>

                                            <div class="ec-panel-settings-row" data-type="facebook">
                                                <div class="ec-social-handle">
                                                </div>
                                                <div class="ec-panel-settings-label ec-inline-block">
                                                    Facebook
                                                </div>
                                                <div class="ec-panel-settings-checkbox-container">
                                                    <input type="checkbox" class="ec-panel-settings-checkbox"/>
                                                </div>
                                                <div class="ec-panel-settings-input-container">
                                                    <input type="text" class="ec-panel-settings-input ec-social-url"
                                                           placeholder="URL..."/>
                                                </div>
                                                <div class="ec-panel-settings-input-container ec-social-img-url-wrapper">
                                                    <input type="text" class="ec-panel-settings-input ec-social-img-url"
                                                           placeholder="Image URL"/>
                                                </div>
                                            </div>

                                            <div class="ec-panel-settings-row" data-type="twitter">
                                                <div class="ec-social-handle">
                                                </div>
                                                <div class="ec-panel-settings-label ec-inline-block">
                                                    Twitter
                                                </div>
                                                <div class="ec-panel-settings-checkbox-container">
                                                    <input type="checkbox" class="ec-panel-settings-checkbox"/>
                                                </div>
                                                <div class="ec-panel-settings-input-container">
                                                    <input type="text" class="ec-panel-settings-input ec-social-url"
                                                           placeholder="URL..."/>
                                                </div>
                                                <div class="ec-panel-settings-input-container ec-social-img-url-wrapper">
                                                    <input type="text" class="ec-panel-settings-input ec-social-img-url"
                                                           placeholder="Image URL"/>
                                                </div>
                                            </div>

                                            <div class="ec-panel-settings-row" data-type="linkedin">
                                                <div class="ec-social-handle">
                                                </div>
                                                <div class="ec-panel-settings-label ec-inline-block">
                                                    Linkedin
                                                </div>
                                                <div class="ec-panel-settings-checkbox-container">
                                                    <input type="checkbox" class="ec-panel-settings-checkbox"/>
                                                </div>
                                                <div class="ec-panel-settings-input-container">
                                                    <input type="text" class="ec-panel-settings-input ec-social-url"
                                                           placeholder="URL..."/>
                                                </div>
                                                <div class="ec-panel-settings-input-container ec-social-img-url-wrapper">
                                                    <input type="text" class="ec-panel-settings-input ec-social-img-url"
                                                           placeholder="Image URL"/>
                                                </div>
                                            </div>

                                            <div class="ec-panel-settings-row" data-type="youtube">
                                                <div class="ec-social-handle">
                                                </div>
                                                <div class="ec-panel-settings-label ec-inline-block">
                                                    Youtube
                                                </div>
                                                <div class="ec-panel-settings-checkbox-container">
                                                    <input type="checkbox" class="ec-panel-settings-checkbox"/>
                                                </div>
                                                <div class="ec-panel-settings-input-container">
                                                    <input type="text" class="ec-panel-settings-input ec-social-url"
                                                           placeholder="URL..."/>
                                                </div>
                                                <div class="ec-panel-settings-input-container ec-social-img-url-wrapper">
                                                    <input type="text" class="ec-panel-settings-input ec-social-img-url"
                                                           placeholder="Image URL"/>
                                                </div>
                                            </div>

                                            <div class="ec-panel-settings-row" data-type="skype">
                                                <div class="ec-social-handle">
                                                </div>
                                                <div class="ec-panel-settings-label ec-inline-block">
                                                    Skype
                                                </div>
                                                <div class="ec-panel-settings-checkbox-container">
                                                    <input type="checkbox" class="ec-panel-settings-checkbox"/>
                                                </div>
                                                <div class="ec-panel-settings-input-container">
                                                    <input type="text" class="ec-panel-settings-input ec-social-url"
                                                           placeholder="URL..."/>
                                                </div>
                                                <div class="ec-panel-settings-input-container ec-social-img-url-wrapper">
                                                    <input type="text" class="ec-panel-settings-input ec-social-img-url"
                                                           placeholder="Image URL"/>
                                                </div>
                                            </div>

                                            <div class="ec-panel-settings-row" data-type="telegram">
                                                <div class="ec-social-handle">
                                                </div>
                                                <div class="ec-panel-settings-label ec-inline-block">
                                                    Telegram
                                                </div>
                                                <div class="ec-panel-settings-checkbox-container">
                                                    <input type="checkbox" class="ec-panel-settings-checkbox"/>
                                                </div>
                                                <div class="ec-panel-settings-input-container">
                                                    <input type="text" class="ec-panel-settings-input ec-social-url"
                                                           placeholder="URL..."/>
                                                </div>
                                                <div class="ec-panel-settings-input-container ec-social-img-url-wrapper">
                                                    <input type="text" class="ec-panel-settings-input ec-social-img-url"
                                                           placeholder="Image URL"/>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>


                            </div>
                        </div>
                    </div>

                </div>


            </div>

        </div>
    </div>
    <div class="ec-preview ec-loading" data-loading-text="Please wait. Loading...">
        <div class="ec-panel-switcher " data-hint="Nice to meet you" data-position="right">
            <div class="ec-panel-switcher-container">
                <div class="ec-panel-switcher-icon">
                    <div class="ec-panel-switcher-icon-collapse">
                        <i class="ec-caret-left"></i>
                    </div>
                    <div class="ec-panel-switcher-icon-expand">
                        <i class="ec-caret-right"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="ec-preview-content">
            <div class="ec-preview-content-wrapper">
                <div class="ec-preview-content-body" style="padding:25px 0">
                    <div class="ec-preview-content-sortable">

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modals -->
    <div id="modal-import" class="ec-modal">

        <!-- Modal content -->
        <div class="ec-modal-content">
            <div class="ec-modal-close-container">
        <span class="ec-modal-close">
          <i class="fa fa-times"></i>
        </span>
            </div>
            <div class="ec-modal-title">
                <span>Import file</span>
            </div>
            <div class="ec-modal-sub-title">
                <span>Select a JSON file for the import</span>
            </div>
            <div class="ec-modal-input-container">
                <div class="ec-modal-input ec-modal-input-padding" id="ec-import-filename"> No file selected</div>
                <div id="ec-import-submit" class="ec-modal-input-submit">
                    <div class="ec-modal-input-submit-label">
                        Import
                    </div>
                    <div class="ec-modal-input-submit-loading">
                        <img src="<?php echo EC_WOO_BUILDER_URL; ?>assets/img/loading.gif" alt="loading">
                    </div>
                </div>
            </div>
            <div class="ec-modal-file-container">
                <label class="ec-modal-file-label" for="import-file">Choose file</label>
                <input type="file" class="ec-modal-file-input" id="import-file" accept=".json,application/json" data-change-label="#ec-import-filename">
            </div>
            <div class="ec-modal-sub-title">
        <span style="font-style: italic;">
        <small>NOTE: Import works the only exported JSON file</small>
        </span>
            </div>
        </div>

    </div>
    <div id="modal-import-all" class="ec-modal">

        <!-- Modal content -->
        <div class="ec-modal-content">
            <div class="ec-modal-close-container">
                <span class="ec-modal-close">
                  <i class="fa fa-times"></i>
                </span>
            </div>
            <div class="ec-modal-title">
                <span>Import All Templates</span>
            </div>
            <div class="ec-modal-sub-title">
                <span>Select a JSON file for the import</span>
            </div>
            <div class="ec-modal-input-container">
                <div class="ec-modal-input ec-modal-input-padding" id="ec-import-all-filename"> No file selected</div>
                <div id="ec-import-all-submit" class="ec-modal-input-submit">
                  <div class="ec-modal-input-submit-label">
                      Import
                  </div>
                  <div class="ec-modal-input-submit-loading">
                      <img src="<?php echo EC_WOO_BUILDER_URL; ?>assets/img/loading.gif" alt="loading">
                  </div>
                </div>
            </div>
            <div class="ec-modal-file-container">
                <label class="ec-modal-file-label" for="import-all-file">Choose file</label>
                <input type="file" class="ec-modal-file-input" id="import-all-file" accept=".json,application/json" data-change-label="#ec-import-all-filename">
            </div>
            <div class="ec-modal-sub-title">
                <span id="ec-import-all-file-error" style="color:red"></span>
                <span style="font-style: italic;">
                    <small>NOTE: Import works the only exported all templates JSON file</small><br><br>
                    <small>It will update existing templates which you have now</small>
                </span>

            </div>
        </div>

    </div>
    <div id="modal-activate" class="ec-modal">
        <!-- Modal content -->
        <div class="ec-modal-content">
            <div class="ec-modal-close-container">
        <span class="ec-modal-close">
          <i class="fa fa-times"></i>
        </span>
            </div>
            <div class="ec-modal-title">
                <span>Activate Updates</span>
            </div>
            <div class="ec-modal-sub-title">
                <span>If you want to get updates, please enter your purchase code</span>
            </div>
            <div class="ec-modal-input-container active">
                <input id="ec-purchase-code" class="ec-modal-input " type="text" placeholder="Enter Purchase Code"/>
                <div id="ec-activate-updates-submit" class="ec-modal-input-submit">
                    <div class="ec-modal-input-submit-label">
                        Activate
                    </div>
                    <div class="ec-modal-input-submit-loading">
                        <img src="<?php echo EC_WOO_BUILDER_URL; ?>assets/img/loading.gif" alt="loading">
                    </div>
                </div>
            </div>
            <div class="ec-modal-sub-title ec-activate-updates-skip-container">
        <span>
          <a href="#" id="ec-activate-updates-skip">Do not show again</a>
        </span>
                <br><br><br>
                <span>
          If you skip this step, then you should update manually everytime.
        </span>
            </div>
            <div class="ec-modal-sub-title">
        <span id="ec-activate-updates-error" style="color:red">

        </span>
            </div>
        </div>
    </div>


    <div id="modal-save" class="ec-modal">

        <!-- Modal content -->
        <div class="ec-modal-content">
            <div class="ec-modal-close-container">
        <span class="ec-modal-close">
          <i class="fa fa-times"></i>
        </span>
            </div>
            <div class="ec-modal-title">
                <span>Save Your Design as Template</span>
            </div>
            <div class="ec-modal-sub-title">
                <span>Your templates will be ready to reuse at any time</span>
            </div>
            <div class="ec-modal-input-container active">
                <input id="ec-modal-template-name" class="ec-modal-input" type="text"
                       placeholder="Enter Template Name"/>
                <div id="ec-modal-template-save" class="ec-modal-input-submit">
                    <div class="ec-modal-input-submit-label">
                        Save
                    </div>
                    <div class="ec-modal-input-submit-loading">
                        <img src="<?php echo EC_WOO_BUILDER_URL; ?>assets/img/loading.gif" alt="loading">
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div id="modal-save-as" class="ec-modal">

        <!-- Modal content -->
        <div class="ec-modal-content">
            <div class="ec-modal-close-container">
        <span class="ec-modal-close">
          <i class="fa fa-times"></i>
        </span>
            </div>
            <div class="ec-modal-title">
                <span>Save Your Template as Other Email Type</span>
            </div>

            <div class="ec-modal-input-container active">
                <?php

                $lang_select = wp_dropdown_languages(array(
                    'id' => 'ec_woo_save_as_lang',
                    'name' => 'ec_woo_save_as_lang',
                    'languages' => EC_Helper::get_available_languages(),
                    'selected' => EC_Helper::get_locale(),
                    'echo' => 0,
                    'show_available_translations' => false
                ));
                echo $lang_select;

                ?>

                <select id="ec_woo_save_as_email_type" class="ec-modal-input" style="width: calc(70% - 10.833em);">
                    <option value="">
                        <?php _e("Select an email type", EC_WOO_BUILDER_TEXTDOMAIN); ?>
                    </option>
                    <?php
                    if (!empty($mails)) {
                        foreach ($mails as $mail) {
                            if (!in_array($mail->id, array())) {
                                ?>
                                <option value="<?php echo $mail->id ?>">
                                    <?php echo ucwords($mail->title); ?>
                                </option>
                                <?php
                            }
                        } ?>
                        <option value="customer_partially_refunded_order"><?php _e("Partial Refunded Order", EC_WOO_BUILDER_TEXTDOMAIN); ?></option><?php
                    }
                    ?>
                </select>

                <div id="ec-modal-template-save-as" class="ec-modal-input-submit">
                    <div class="ec-modal-input-submit-label">
                        Save
                    </div>
                    <div class="ec-modal-input-submit-loading">
                        <img src="<?php echo EC_WOO_BUILDER_URL; ?>assets/img/loading.gif" alt="loading">
                    </div>
                </div>
            </div>
            <div class="ec-modal-sub-title">
        <span style="font-style:italic">
          <small>
            NOTE: After saving the template as another email type, you cannot reverse it.  It will override the current template
          </small>
        </span>
            </div>
        </div>

    </div>
    <div id="modal-send-email" class="ec-modal">
        <!-- Modal content -->
        <div class="ec-modal-content">
            <div class="ec-modal-close-container">
        <span class="ec-modal-close">
          <i class="fa fa-times"></i>
        </span>
            </div>
            <div class="ec-modal-title">
                <span>Send Email</span>
            </div>
            <div class="ec-modal-sub-title">
                <span>Send template to your email address for testing</span>
            </div>
            <div class="ec-modal-input-container active">
                <input id="ec-email-address" class="ec-modal-input " type="text" placeholder="Enter Email Address"
                       value="<?php echo $current_user->user_email; ?>"/>
                <div id="ec-email-submit" class="ec-modal-input-submit">
                    <div class="ec-modal-input-submit-label">
                        Send
                    </div>
                    <div class="ec-modal-input-submit-loading">
                        <img src="<?php echo EC_WOO_BUILDER_URL; ?>assets/img/loading.gif" alt="loading">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="modal-library" class="ec-modal-library ">
        <div class="ec-modal-library-content-container">
            <div class="ec-modal-library-content">
                <div class="ec-modal-library-header">
                    <div class="ec-modal-library-tabs-container">
                        <div class="ec-modal-library-tabs-list ec-modal-library-tabs-list-3">
                            <div class="ec-modal-library-tabs-item " data-content="#modal-library-blocks">
                                <span>Blocks</span>
                            </div>
                            <div class="ec-modal-library-tabs-item active" data-content="#modal-library-templates">
                                <span>Templates</span>
                            </div>
                            <div class="ec-modal-library-tabs-item " data-content="#modal-library-my-templates">
                                <span>My templates</span>
                            </div>
                        </div>
                    </div>
                    <div class="ec-modal-library-close-container">
                        <div class="ec-modal-library-close" data-type="close">
                            <i class="fa fa-times"></i>
                        </div>
                    </div>
                </div>
                <div class="ec-modal-library-tab-content-container">
                    <div id="modal-library-blocks" class="ec-modal-library-tab-content-item ">
                        <div class="ec-modal-library-toolbar-container">
                            <div class="ec-modal-library-toolbar-category ec-modal-library-toolbar-item">
                                <select class="ec-modal-library-toolbar-category-selector ec-category-select2">
                                    <option value="0">Category 1</option>
                                    <option value="1">Category 2 234234</option>
                                </select>
                            </div>
                            <div class="ec-modal-library-toolbar-search ec-modal-library-toolbar-item">
                                <div class="ec-modal-library-toolbar-search-wrapper">
                                    <div class="ec-modal-library-toolbar-search-input-container">
                                        <input type="text" class="ec-modal-library-toolbar-search-input"
                                               placeholder="Search">
                                    </div>
                                    <div class="ec-modal-library-toolbar-search-icon">
                                        <i class="ec-search"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ec-modal-library-element-list">
                            <div class="ec-modal-library-element-item">
                                <div class="ec-modal-library-element-item-image-container">
                                    <div class="ec-modal-library-element-item-image"
                                         style="background-image:url('/library/templates/images/thumbs/image-1.png')">
                                    </div>
                                    <div class="ec-modal-library-element-item-image-preview">
                    <span>
                      <i class="ec-detail"></i>
                    </span>
                                    </div>
                                </div>
                                <div class="ec-modal-library-element-item-name-container">
                                    <div class="ec-modal-library-element-item-name">
                                        <span>Block name</span>
                                    </div>
                                    <div class="ec-modal-library-element-item-name-hover" data-action="load-template">
                                        <i class="ec-download"></i>
                                        <span>Insert</span>
                                    </div>
                                </div>
                            </div>
                            <div class="ec-modal-library-element-item">
                                <div class="ec-modal-library-element-item-image-container">
                                    <div class="ec-modal-library-element-item-image"
                                         style="background-image:url('/library/templates/images/thumbs/image-1.png')">
                                    </div>
                                    <div class="ec-modal-library-element-item-image-preview">
                    <span>
                      <i class="ec-detail"></i>
                    </span>
                                    </div>
                                </div>
                                <div class="ec-modal-library-element-item-name-container">
                                    <div class="ec-modal-library-element-item-name">
                                        <span>Block name</span>
                                    </div>
                                    <div class="ec-modal-library-element-item-name-hover" data-action="load-template">
                                        <i class="ec-download"></i>
                                        <span>Insert</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div id="modal-library-templates" class="ec-modal-library-tab-content-item active">
                        <div class="ec-modal-library-toolbar-container">
                            <div class="ec-modal-library-toolbar-category ec-modal-library-toolbar-item">
                                <select id="ec-modal-library-toolbar-category-selector"
                                        class="ec-modal-library-toolbar-category-selector ec-category-select2">
                                </select>
                            </div>
                            <div class="ec-modal-library-toolbar-search ec-modal-library-toolbar-item">
                                <div class="ec-modal-library-toolbar-search-wrapper">
                                    <div class="ec-modal-library-toolbar-search-input-container">
                                        <input type="text" id="ec-modal-library-toolbar-search-input"
                                               class="ec-modal-library-toolbar-search-input" placeholder="Search...">
                                    </div>
                                    <div class="ec-modal-library-toolbar-search-icon">
                                        <i class="ec-search"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ec-modal-library-element-list">

                        </div>
                    </div>
                    <div id="modal-library-my-templates" class="ec-modal-library-tab-content-item ">
                        <div class="ec-modal-library-toolbar-container">
                            <div class="ec-modal-library-toolbar-category ec-modal-library-toolbar-item">
                                &nbsp;
                            </div>
                            <div class="ec-modal-library-toolbar-search ec-modal-library-toolbar-item">
                                <div class="ec-modal-library-toolbar-search-wrapper">
                                    <div class="ec-modal-library-toolbar-search-input-container">
                                        <input type="text" class="ec-modal-library-toolbar-search-input"
                                               placeholder="Search">
                                    </div>
                                    <div class="ec-modal-library-toolbar-search-icon">
                                        <i class="ec-search"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ec-modal-library-grid-container">
                            <div class="ec-modal-library-grid">


                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ec-modal-library-preview">
                <div class="ec-modal-library-preview-header">
                    <div class="ec-modal-library-preview-back">
                        <i class="ec-chevron-left"></i>
                        <span>Back</span>
                    </div>
                    <div class="ec-modal-library-preview-actions">
                        <div class="ec-modal-library-preview-actions-insert" data-action="load-template">
                            <i class="ec-download"></i>
                            <span>Insert</span>
                        </div>
                        <div class="ec-modal-library-preview-actions-close" data-type="close">
                            <i class="fa fa-times"></i>
                        </div>
                    </div>
                </div>
                <div class="ec-modal-library-preview-content-container">
                    <div class="ec-modal-library-preview-content-body">
                        <div class="ec-modal-library-preview-content-loading">
                            <img class="loading-icon" src="<?php echo EC_WOO_BUILDER_URL; ?>assets/img/loading.gif"
                                 alt="loading...">
                        </div>
                        <iframe class="ec-modal-library-preview-content-frame">
                        </iframe>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <div id="modal-confirm" class="ec-modal ec-modal-confirm">

        <!-- Modal content -->
        <div class="ec-modal-content">
            <div class="ec-modal-close-container">
        <span class="ec-modal-close">
          <i class="fa fa-times"></i>
        </span>
            </div>
            <div class="ec-modal-title">
                <span>Are you sure?</span>
            </div>
            <div class="ec-modal-sub-title">
                <span>You won't be able to revert this!</span>
            </div>
            <div class="ec-modal-input-container">
                <button type="button" id="modal-confirm-ok" class="modal-confirm-button modal-confirm-button-ok">Yes
                </button>
                <button type="button" id="modal-confirm-cancel"
                        class="modal-confirm-button modal-confirm-button-cancel">Cancel
                </button>

            </div>
        </div>

    </div>

    <div id="modal-help" class="ec-modal-library ">
        <div class="ec-modal-library-content-container">
            <div class="ec-modal-library-content">
                <div class="ec-modal-library-header">
                    <div class="ec-modal-library-tabs-container">
                        <div class="ec-modal-library-tabs-list ec-modal-library-tabs-list-2">
                            <div class="ec-modal-library-tabs-item active" data-content="#modal-help-shortcodes">
                                <span>Shortcodes</span>
                            </div>
                            <div class="ec-modal-library-tabs-item " data-content="#modal-help-support">
                                <span>Support</span>
                            </div>
                        </div>
                    </div>
                    <div class="ec-modal-library-close-container">
                        <div class="ec-modal-library-close" data-type="close">
                            <i class="fa fa-times"></i>
                        </div>
                    </div>
                </div>
                <div class="ec-modal-library-tab-content-container">
                    <div id="modal-help-shortcodes" class="ec-modal-library-tab-content-item active">
                        <div class="ec-modal-library-toolbar-container">
                            <div class="ec-modal-library-toolbar-category ec-modal-library-toolbar-item">
                                &nbsp;
                            </div>
                            <div class="ec-modal-library-toolbar-search ec-modal-library-toolbar-item">
                                <div class="ec-modal-library-toolbar-search-wrapper">
                                    <div class="ec-modal-library-toolbar-search-input-container">
                                        <input type="text" id="library_shortcode_txt"
                                               class="ec-modal-library-toolbar-search-input" placeholder="Search">
                                    </div>
                                    <div class="ec-modal-library-toolbar-search-icon">
                                        <i class="ec-search"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ec-modal-library-grid ec-shortcode-list">

                        </div>
                    </div>

                    <div id="modal-help-support" class="ec-modal-library-tab-content-item ">
                        <p class="ec-support-text">
                            Dear <strong><?php echo $current_user->display_name; ?></strong> ,
                            <br><br>
                            Thank you for purchasing our product! We’re glad that you found what you were looking for.
                            <br>
                            It is our goal that you are always happy with what you bought from us, so please <a
                                    target="_blank" href="https://codecanyon.net/downloads">let us know</a> if your
                            buying experience was anything short of
                            excellent. We look forward to seeing you again. Have a great day!
                            <br><br>
                            If you have any question or suggestion, do not hesitate to contact <a target="_blank"
                                                                                                  href="http://support.cidcode.net">our
                                support center</a>
                            <br><br>
                            Best Regards,<br>
                            CidCode</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div id="modal-preview" class="ec-modal-library ">
        <div class="ec-modal-library-content-container">
            <div class="ec-modal-library-content">
                <div class="ec-modal-library-header">
                    <div class="ec-modal-library-tabs-container">
                        <div class="ec-modal-library-tabs-list ec-modal-library-tabs-list-1">
                            <div class="ec-modal-library-tabs-item " style="text-align: left;" onclick="return false;">
                                <span>Email Preview</span><br>
                                <small>
                                    Preview shows the final version of your template.
                                    If you were turn off the <strong>replace email</strong> functionality in this case
                                    you will see the default template
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="ec-modal-library-close-container">
                        <div class="ec-modal-library-close" data-type="close">
                            <i class="fa fa-times"></i>
                        </div>
                    </div>
                </div>
                <div class="ec-modal-library-tab-content-container">
                    <div class="ec-modal-library-preview-content-body">
                        <div class="ec-modal-library-preview-content-loading">
                            <img class="loading-icon" src="<?php echo EC_WOO_BUILDER_URL; ?>assets/img/loading.gif"
                                 alt="loading...">
                        </div>
                        <iframe class="ec-modal-library-preview-content-frame">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
