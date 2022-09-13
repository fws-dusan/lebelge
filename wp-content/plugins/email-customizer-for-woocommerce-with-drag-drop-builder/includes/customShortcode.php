<?php


if (!defined('ABSPATH')) {
    exit;
}

//TODO: class-a cevir. register/deregister methodlari olsun


$ec_woo_settings_show_custom_shortcode = get_option('ec_woo_settings_show_custom_shortcode', EC_WOO_BUILDER_SHOW_CUSTOM_SHORTCODE);


function register_ec_woo_custom_shortcode()
{
    $args = array(
        'labels' => array(
            'name' => 'EC WOO Custom Shortcode',
            'singular_name' => 'EC WOO Custom Shortcode',
            'add_new' => 'Add shortcode',
            'all_items' => 'All shortcodes',
            'add_new_item' => 'Tag name ( prefix is [ec_woo_] )',
            'edit_item' => 'Edit shortcode',
            'new_item' => 'New shortcode',
            'view_item' => 'View shortcode',
            'search_item' => 'Search',
            'not_found' => 'No shortcodes found',
            'not_found_in_trash' => 'No shortcodes found in trash',
            'parent_item_colon' => 'Parent Shortcode'
        ),
        'public' => true,
        'has_archive' => true,
        'publicly_queryable' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => array(
            'title',
            'editor'
        ),
        'menu_position' => 8,
        'exclude_from_search' => false
    );
    register_post_type(EC_WOO_BUILDER_POST_TYPE_CUSTOM_CODE, $args);
}



if ($ec_woo_settings_show_custom_shortcode=='1') {
 add_action('init', 'register_ec_woo_custom_shortcode');
}
