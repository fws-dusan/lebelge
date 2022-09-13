<?php

add_filter( 'wp_targeted_link_rel', '__return_false' );

function ec_woo_add_query_vars_filter($vars)
{
    $vars[] = "page";
    return $vars;
}

add_filter('query_vars', 'ec_woo_add_query_vars_filter');

if (!class_exists('EC_Helper')) {
    throw new Exception("EC_Helper is not defined");
}

$ec_helper=new EC_Helper();
add_filter('wc_get_template', array($ec_helper, 'ec_woo_get_new_template'), 10, 5);
