<?php
defined( 'ABSPATH' ) || exit;

class XLWCTY_Flexible_Checkout_Fields {
    private static $ins = null;

    public function __construct() {
        if ( !is_admin() ) {

            add_filter( 'woocommerce_order_formatted_billing_address', array( $this, 'xlwcty_format_billing_address_campatibility' ), 9999999, 2 );
            add_filter( 'woocommerce_order_formatted_shipping_address', array( $this, 'xlwcty_format_shipping_address_campatibility' ), 9999999, 2 );
        }
    }

    public static function get_instance() {
        if ( self::$ins == null ) {
            self::$ins = new self;
        }

        return self::$ins;
    }

    public function xlwcty_format_billing_address_campatibility( $billing_address, $order ) {

        if ( isset( $billing_address['title'] ) ) {
            unset( $billing_address['title'] );
            $billing_address = array_filter( $billing_address );
        }

        if ( isset( $billing_address['first_name'] ) ) {
            unset( $billing_address['first_name'] );
            $billing_address = array_filter( $billing_address );
        }
        if ( isset( $billing_address['last_name'] ) ) {
            unset( $billing_address['last_name'] );
            $billing_address = array_filter( $billing_address );
        }

        return $billing_address;
    }

    public function xlwcty_format_shipping_address_campatibility( $shipping_address, $order ) {
        if ( isset( $shipping_address['title'] ) ) {
            unset( $shipping_address['title'] );
            $shipping_address = array_filter( $shipping_address );
        }

        if ( isset( $shipping_address['first_name'] ) ) {
            unset( $shipping_address['first_name'] );
            $shipping_address = array_filter( $shipping_address );
        }

        if ( isset( $shipping_address['last_name'] ) ) {
            unset( $shipping_address['last_name'] );
            $shipping_address = array_filter( $shipping_address );
        }

        return $shipping_address;
    }
}

if ( defined( 'FLEXIBLE_CHECKOUT_FIELDS_VERSION' ) ) {
    add_action( 'plugins_loaded', function () {
        XLWCTY_Flexible_Checkout_Fields::get_instance();
    } );

}

