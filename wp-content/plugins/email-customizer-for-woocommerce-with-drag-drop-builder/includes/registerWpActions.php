<?php
if (!class_exists('EC_General_Settings')) {
    throw new Exception("EC_General_Settings is not defined");
}
if (!class_exists('WooMail_Ajax')) {
    throw new Exception("WooMail_Ajax is not defined");
}

function remove_admin_notices()
{
    if (empty($_GET)) {
        return;
    }
    if (!isset($_GET['page'])) {
        return;
    }
    $page = sanitize_text_field($_GET['page']);
    if ($page == EC_WOO_BUILDER_SLUG) {
        remove_all_actions('admin_notices');
    }
}

add_action('admin_head', 'remove_admin_notices');


$generalSettings=new EC_General_Settings();
$generalSettings->loadSettings();

$woomailAjax=new WooMail_Ajax();
add_action('wp_ajax_save_panel_position', array($woomailAjax, 'save_panel_position'));
add_action('wp_ajax_export_html', array($woomailAjax, 'export_html'));
add_action('wp_ajax_export_all', array($woomailAjax, 'export_all'));
add_action('wp_ajax_export_json', array($woomailAjax, 'export_json'));
add_action('wp_ajax_import_json', array($woomailAjax, 'import_json'));
add_action('wp_ajax_import_all', array($woomailAjax, 'import_all'));
add_action('wp_ajax_send_email', array($woomailAjax, 'send_email'));
add_action('wp_ajax_template_load', array($woomailAjax, 'template_load'));
add_action('wp_ajax_template_save', array($woomailAjax, 'template_save'));
add_action('wp_ajax_template_new_save', array($woomailAjax, 'template_new_save'));
add_action('wp_ajax_template_load_saved', array($woomailAjax, 'template_load_saved'));
add_action('wp_ajax_template_delete_saved', array($woomailAjax, 'template_delete_saved'));
add_action('wp_ajax_template_save_as', array($woomailAjax, 'template_save_as'));
add_action('wp_ajax_save_settings', array($woomailAjax, 'save_settings'));
add_action('wp_ajax_save_custom_css', array($woomailAjax, 'save_custom_css'));
add_action('wp_ajax_activate_updates', array($woomailAjax, 'activate_updates'));
add_action('wp_ajax_skip_activate_updates', array($woomailAjax, 'skip_activate_updates'));
add_action('wp_ajax_generate_shortcode', array($woomailAjax, 'generate_shortcode'));
add_action('wp_ajax_save_settings_replace_email_type', array($woomailAjax, 'save_settings_replace_email_type'));
add_action('wp_ajax_save_related_items', array($woomailAjax, 'save_related_items'));