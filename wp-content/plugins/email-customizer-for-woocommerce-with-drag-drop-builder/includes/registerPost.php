<?php


if (!function_exists('ec_woo_builder_email_post_register')) {
    function ec_woo_builder_email_post_register()
    {
        $labels = array(
            'name' => _x('Email Template', 'post type general name'),
            'singular_name' => _x('Email Template', 'post type singular name'),
            'add_new' => _x('Add New Email Template', 'Team item'),
            'add_new_item' => __('Add a new post of type Email Template'),
            'edit_item' => __('Edit Email Template'),
            'new_item' => __('New Email Template'),
            'view_item' => __('View Email Template'),
            'search_items' => __('Search Email Template'),
            'not_found' => __('No Email Template found'),
            'not_found_in_trash' => __('No Email Template currently trashed'),
            'parent_item_colon' => ''
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => false,
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => EC_WOO_BUILDER_POST_TYPE,
            'capabilities' => array(),
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'author', 'thumbnail')
        );
        register_post_type(EC_WOO_BUILDER_POST_TYPE, $args);
    }

    add_action('init', 'ec_woo_builder_email_post_register');
}
