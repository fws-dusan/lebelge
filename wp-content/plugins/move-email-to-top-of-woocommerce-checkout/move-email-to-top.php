<?php

/*
Plugin Name: Move Email to Top of WooCommerce Checkout
Description: Moves email field to the top of the checkout to capture email early.
Version: 1.0
Author: Caitlin Chou
Author URI: caitlinchou.me
License: GPL3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
WC tested up to: 3.4.3
*/

add_filter('woocommerce_billing_fields','mett_move_email_to_top', 10, 1);
function mett_move_email_to_top($address_fields){
    $address_fields['billing_email']['priority'] = 1;
    return $address_fields;
}
