<?php

if (!defined('ABSPATH')) {
    exit;
}

class WMIntegrationCustomShortcode implements IWooMailIntegration
{
  private $name="Custom shortcode";
  private $shortcodes;

  public function __construct()
  {
  }

  public function getName()
  {
    return $this->name;
  }

  public function collectData()
  {
    $this->shortcodes=array();
    $custom_code_list = $this->get_custom_codes();
    if (empty($custom_code_list) || count($custom_code_list) == 0) {
      return $this->shortcodes;
    }
    foreach ($custom_code_list as $item) {
        $this->shortcodes[$item->post_title] = $item->post_content;
    }
    return $this->shortcodes;
  }

  private function get_custom_codes()
  {
      global $wpdb;
      $query = "select id,post_title,post_content FROM $wpdb->posts where post_status='publish'  and post_type='".EC_WOO_BUILDER_POST_TYPE_CUSTOM_CODE."'";
      $result = $wpdb->get_results($query);
      return $result;
  }
}