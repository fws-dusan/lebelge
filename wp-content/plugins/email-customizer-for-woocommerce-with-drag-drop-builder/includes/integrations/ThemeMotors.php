<?php

if (!defined('ABSPATH')) {
    exit;
}

class WMIntegrationThemeMotors extends WooMailIntegration
{
  private $name="Theme Motors";
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
    if (defined('motors')) {
        $orderPD = get_post_meta($order->get_id(), 'order_pickup_date', true);
        $orderPL = get_post_meta($order->get_id(), 'order_pickup_location', true);
        $orderDD = get_post_meta($order->get_id(), 'order_drop_date', true);
        $orderDL = get_post_meta($order->get_id(), 'order_drop_location', true);
        $this->shortcodes['order_pickup_date'] = $orderPD;
        $this->shortcodes['order_pickup_location'] = $orderPL;
        $this->shortcodes['order_drop_date'] = $orderDD;
        $this->shortcodes['order_drop_location'] = $orderDL;
    }
    return $this->shortcodes;
  }
}