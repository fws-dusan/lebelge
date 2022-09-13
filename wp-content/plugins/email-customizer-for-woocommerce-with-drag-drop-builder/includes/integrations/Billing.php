<?php

if (!defined('ABSPATH')) {
    exit;
}

class WMIntegrationBilling extends WooMailIntegration
{
  private $name="Billing";
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
    $this->shortcodes['billing_address'] = $this->order->get_formatted_billing_address();
    $this->shortcodes['billing_first_name'] = $this->get_order_billing_first_name();
    $this->shortcodes['billing_last_name'] = $this->get_order_billing_last_name();
    $this->shortcodes['billing_company'] = $this->get_billing_company();
    $this->shortcodes['billing_address_1'] = $this->get_billing_address_1();
    $this->shortcodes['billing_address_2'] = $this->get_billing_address_2();
    $this->shortcodes['billing_city'] = $this->get_billing_city();
    $this->shortcodes['billing_state'] = $this->get_billing_state();
    $this->shortcodes['billing_postcode'] = $this->get_billing_postcode();
    $this->shortcodes['billing_country'] = $this->get_billing_country();
    $this->shortcodes['billing_phone'] = $this->get_billing_phone();
    $this->shortcodes['billing_email'] = $this->get_order_billing_email();
    return $this->shortcodes;
  }

  private function get_order_billing_first_name()
  {
      return method_exists($this->order, 'get_billing_first_name') ? $this->order->get_billing_first_name() : $this->order->billing_first_name;
  }

  private function get_order_billing_last_name()
  {
      return method_exists($this->order, 'get_billing_last_name') ? $this->order->get_billing_last_name() : $this->order->billing_last_name;
  }

  private function get_order_billing_email()
  {
      return method_exists($this->order, 'get_billing_email') ? $this->order->get_billing_email() : $this->order->billing_email;
  }

  private function get_order_payment_method_title()
  {
      return method_exists($this->order, 'get_payment_method_title') ? $this->order->get_payment_method_title() : $this->order->payment_method_title;
  }

  private function get_billing_company()
  {
      return method_exists($this->order, 'get_billing_company') ? $this->order->get_billing_company() : $this->order->billing_company;
  }

  private function get_billing_address_1()
  {
      return method_exists($this->order, 'get_billing_address_1') ? $this->order->get_billing_address_1() : $this->order->billing_address_1;
  }

  private function get_billing_address_2()
  {
      return method_exists($this->order, 'get_billing_address_2') ? $this->order->get_billing_address_2() : $this->order->billing_address_2;
  }

  private function get_billing_city()
  {
      return method_exists($this->order, 'get_billing_city') ? $this->order->get_billing_city() : $this->order->billing_city;
  }

  private function get_billing_state()
  {
      return method_exists($this->order, 'get_billing_state') ? $this->order->get_billing_state() : $this->order->billing_state;
  }

  private function get_billing_postcode()
  {
      return method_exists($this->order, 'get_billing_postcode') ? $this->order->get_billing_postcode() : $this->order->billing_postcode;
  }

  private function get_billing_country()
  {
      return method_exists($this->order, 'get_billing_country') ? $this->order->get_billing_country() : $this->order->billing_country;
  }

  private function get_billing_phone()
  {
      return method_exists($this->order, 'get_billing_phone') ? $this->order->get_billing_phone() : $this->order->billing_phone;
  }
}