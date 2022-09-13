<?php

if (!defined('ABSPATH')) {
    exit;
}
/*
* WooCommerce Customer Manager
* https://wordpress.org/plugins/customer-manager-for-woocommerce/
*/
class WMIntegrationCustomerManager extends WooMailIntegration
{
  private $name="Customer Manager";
  private $shortcodes;

  public function __construct($order)
  {
    parent::__construct($order);
  }

  public function getName()
  {
    return $this->name;
  }

  public function collectData()
  {
    $this->shortcodes=array();
    if ($this->check_woo_customer_manager()) {
      if ($customer = wpo_wccm_get_customer_by_order( $this->order ) ) {
          $this->shortcodes['customer_number'] = $customer->get_formatted_number();
      }
    }
    return $this->shortcodes;
  }
  public static function check_woo_customer_manager()
  {
    return function_exists('wpo_wccm_get_customer_by_order');
  }
}