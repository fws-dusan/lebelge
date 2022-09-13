<?php

if (!defined('ABSPATH')) {
    exit;
}
/*
* Order Delivery Date for WooCommerce
* https://wordpress.org/support/plugin/order-delivery-date-for-woocommerce/
*/
class WMIntegrationOrderDelivery extends WooMailIntegration
{
  private $name="Order Delivery Date";
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
    $delivery_date = $this->getOrderDeliveryDate($this->get_order_number());
    if ($delivery_date != false) {
        $this->shortcodes['order_delivery_date'] = $delivery_date;
    }
    return $this->shortcodes;
  }
  private function getOrderDeliveryDate($order_id)
  {
      if (class_exists('orddd_lite_common')) {
          $delivery_date = orddd_lite_common::orddd_lite_get_order_delivery_date($order_id);
          return $delivery_date;
      }
      return false;
  }
}