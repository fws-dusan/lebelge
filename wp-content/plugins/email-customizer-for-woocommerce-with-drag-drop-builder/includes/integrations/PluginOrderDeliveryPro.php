<?php

if (!defined('ABSPATH')) {
    exit;
}
/*
* Order Delivery Date for WooCommerce
* https://wordpress.org/support/plugin/order-delivery-date-for-woocommerce/
*/
class WMIntegrationOrderDeliveryPro extends WooMailIntegration
{
  private $name="Order Delivery Date PRO";
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
    $delivery_time = $this->getOrderDeliveryTime($this->get_order_number());
    if ($delivery_time != false) {
        $this->shortcodes['order_delivery_time'] = $delivery_time;
    }
    return $this->shortcodes;
  }

  private function getOrderDeliveryDate($order_id)
  {
      if (class_exists('orddd_common')) {
          $delivery_date = orddd_common::orddd_get_order_delivery_date($order_id);
          return $delivery_date;
      }
      return false;
  }

  public static function getOrderDeliveryTime($order_id)
  {
    if (class_exists ('orddd_common')) {
        $delivery_time = orddd_common::orddd_get_order_timeslot($order_id);
        return $delivery_time;
    }
    return false;
  }
}