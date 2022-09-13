<?php

if (!defined('ABSPATH')) {
    exit;
}

class WMIntegrationShipping extends WooMailIntegration
{
  private $name="Shipping";
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
    $this->shortcodes['shipping_address'] = $this->order->get_formatted_shipping_address();
    $this->shortcodes['shipping_first_name'] = $this->get_shipping_first_name();
    $this->shortcodes['shipping_last_name'] = $this->get_shipping_last_name();
    $this->shortcodes['shipping_company'] = $this->get_shipping_company();
    $this->shortcodes['shipping_address_1'] = $this->get_shipping_address_1();
    $this->shortcodes['shipping_address_2'] = $this->get_shipping_address_2();
    $this->shortcodes['shipping_city'] = $this->get_shipping_city();
    $this->shortcodes['shipping_state'] = $this->get_shipping_state();
    $this->shortcodes['shipping_postcode'] = $this->get_shipping_postcode();
    $this->shortcodes['shipping_country'] = $this->get_shipping_country();
    $this->shortcodes['shipping_method'] = $this->order->get_shipping_method();
    return $this->shortcodes;
  }

  public function get_shipping_first_name()
  {
      return method_exists($this->order, 'get_shipping_first_name') ? $this->order->get_shipping_first_name() : $this->order->shipping_first_name;
  }

  public function get_shipping_last_name()
  {
      return method_exists($this->order, 'get_shipping_last_name') ? $this->order->get_shipping_last_name() : $this->order->shipping_last_name;
  }

  public function get_shipping_company()
  {
      return method_exists($this->order, 'get_shipping_company') ? $this->order->get_shipping_company() : $this->order->shipping_company;
  }

  public function get_shipping_address_1()
  {
      return method_exists($this->order, 'get_shipping_address_1') ? $this->order->get_shipping_address_1() : $this->order->shipping_address_1;
  }

  public function get_shipping_address_2()
  {
      return method_exists($this->order, 'get_shipping_address_2') ? $this->order->get_shipping_address_2() : $this->order->shipping_address_2;
  }

  public function get_shipping_city()
  {
      return method_exists($this->order, 'get_shipping_city') ? $this->order->get_shipping_city() : $this->order->shipping_city;
  }

  public function get_shipping_state()
  {
      return method_exists($this->order, 'get_shipping_state') ? $this->order->get_shipping_state() : $this->order->shipping_state;
  }

  public function get_shipping_postcode()
  {
      return method_exists($this->order, 'get_shipping_postcode') ? $this->order->get_shipping_postcode() : $this->order->shipping_postcode;
  }

  public function get_shipping_country()
  {
      return method_exists($this->order, 'get_shipping_country') ? $this->order->get_shipping_country() : $this->order->shipping_country;
  }

}