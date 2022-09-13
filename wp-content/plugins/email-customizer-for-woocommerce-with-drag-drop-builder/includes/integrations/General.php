<?php

if (!defined('ABSPATH')) {
    exit;
}

class WMIntegrationGeneral implements IWooMailIntegration
{
  private $name="General";
  private $shortcodes;

  public function __construct()
  {
    $this->wpDateFormat=get_option('date_format','M d, Y');
    $this->wpTimeFormat=get_option('time_format','H:i:s');
    $this->wpDateTimeFormat=$this->wpDateFormat.' '.$this->wpTimeFormat;
  }

  public function getName()
  {
    return $this->name;
  }

  public function collectData()
  {
    $this->shortcodes=array();
    $this->shortcodes['site_name'] = get_bloginfo('name');
    $this->shortcodes['current_date'] = date($this->wpDateTimeFormat);
    $this->shortcodes['current_date_timezone'] = date_i18n($this->wpDateTimeFormat);
    $this->shortcodes['site_url'] = '<a href="' . site_url() . '"> ' . esc_html__('Visit Website', EC_WOO_BUILDER_TEXTDOMAIN) . ' </a>';
    $this->shortcodes['current_year'] = date("Y");
    $this->shortcodes['copyright'] = "Copyright © " . date("Y");
    $this->shortcodes['user_account_url'] = '<a href="' . site_url() . '/my-account/"> ' . site_url() . '/my-account </a>';
    $this->shortcodes['user_account_url_2'] = '<a class="ec-user-account-url" href="' . site_url() . '/my-account/"> ' . esc_html__('My account', EC_WOO_BUILDER_TEXTDOMAIN) . '</a>';
    return $this->shortcodes;
  }
}