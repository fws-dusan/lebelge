<?php

if (!defined('ABSPATH')) {
    exit;
}

class WMIntegrationResetPassword implements IWooMailIntegration
{
  private $name="Reset Password";
  private $shortcodes;
  private $args;

  public function __construct($args)
  {
    $this->args=$args;
  }

  public function getName()
  {
    return $this->name;
  }

  public function collectData()
  {
    $this->shortcodes=array();
    if (!isset($this->args) || count($this->args)==0) {
      return $this->shortcodes;
    }
    $this->shortcodes['user_name'] = $this->args['email']->user_login;
    $this->shortcodes['user_email'] = $this->args['email']->user_email;

    $resetURL = esc_url(add_query_arg(array('key' => $this->args['email']->reset_key, 'login' => rawurlencode($this->args['email']->user_login)), wc_get_endpoint_url('lost-password', '', wc_get_page_permalink('myaccount'))));
    $this->shortcodes['user_password_reset_url'] = '<a href=' . $resetURL . '>' . $resetURL . '</a>';
    $this->shortcodes['user_password_reset_url_2'] = '<a class="ec-customer-reset-password-url" href=' . $resetURL . '>' . esc_html__('Click here to reset your password', EC_WOO_BUILDER_TEXTDOMAIN) . '</a>';
    $this->shortcodes['user_password_reset_link'] = $resetURL;
    return $this->shortcodes;
  }
}