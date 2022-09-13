<?php

if (!defined('ABSPATH')) {
    exit;
}

class WMIntegrationUserInfo  extends WooMailIntegration
{
  private $name="User Info";
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
    $user_data = $this->order->get_user();
    if (isset($user_data->user_nicename)) {
        $this->shortcodes['user_name'] = $user_data->user_nicename;
    } else {
        $this->shortcodes['user_name'] = $this->get_order_billing_first_name() . ' ' . $this->get_order_billing_last_name();
    }
    if (isset($user_data->user_email)) {
        $this->shortcodes['user_email'] = $user_data->user_email;
    } else {
        $this->shortcodes['user_email'] = $this->get_order_billing_email();
    }

    if (isset($user_data->user_email['user_email']) && $this->shortcodes['user_email'] != '') {
        $user = get_user_by('email', $this->shortcodes['user_email']);
        $this->shortcodes['user_id'] = (isset($user->ID)) ? $user->ID : '';
    }
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
}