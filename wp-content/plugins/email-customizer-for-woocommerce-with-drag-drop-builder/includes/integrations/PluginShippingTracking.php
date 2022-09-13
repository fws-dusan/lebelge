<?php

if (!defined('ABSPATH')) {
    exit;
}
/*
* WooCommerce Shipping Tracking
* https://codecanyon.net/item/woocommerce-shipping-tracking/11363158
*/
class WMIntegrationShippingTracking extends WooMailIntegration
{
  private $name="Woo Shipping Tracking";
  private $shortcodes;
  private $order_id;

  public function __construct($order,$order_id)
  {
    EC_Validation::checkNullOrEmpty($order_id,'WMIntegrationShippingTracking->$order_id');

    parent::__construct($order);
    $this->order_id=$order_id;
  }

  public function getName()
  {
    return $this->name;
  }

  public function collectData()
  {
    $this->shortcodes=array();
    if (function_exists('wcst_setup') == false) {
      return $this->shortcodes;
    }
    $woo_shipping_tracking_data = wcst_get_order_tracking_data($this->order_id);
    $woo_shipping_i = 0;
    foreach ($woo_shipping_tracking_data as $shipping) {
        $woo_shipping_i++;
        $this->shortcodes['wcst_tracking_number_' . $woo_shipping_i ] = esc_attr($shipping['tracking_number']);
        $this->shortcodes['wcst_dispatch_date_' . $woo_shipping_i ] = esc_attr($shipping['dispatch_date']);
        $this->shortcodes['wcst_custom_text_' . $woo_shipping_i ] = esc_attr($shipping['custom_text']);
        $this->shortcodes['wcst_company_name_' . $woo_shipping_i ] = esc_attr($shipping['company_name']);
        $this->shortcodes['wcst_company_id_' . $woo_shipping_i ] = esc_attr($shipping['company_id']);
        $this->shortcodes['wcst_tracking_url_' . $woo_shipping_i ] = esc_attr($shipping['tracking_url']);
    }
    return $this->shortcodes;
  }
}