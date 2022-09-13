<?php

if (!defined('ABSPATH')) {
    exit;
}
/*
* Name :  Checkout Fields PRO for WooCommerce from ThemeHigh
*/
class WMIntegrationCheckoutFieldEditorPRO extends WooMailIntegration
{
  private $name="Checkout Field Editor PRO";
  private $shortcodes;
  private $order_id;

  public function __construct($order,$order_id)
  {
    EC_Validation::checkNullOrEmpty($order_id,'WMIntegrationCheckoutFieldsPRO->$order_id');

    parent::__construct($order);
    $this->order_id=$order_id;
  }

  public function getName()
  {
    return $this->name;
  }

  public function collectData()
  {
    $this->shortcodes=array();

    $custom_fields = $this->getCustomFields($this->order_id);
    if (empty($custom_fields) || count($custom_fields) == 0) {
      return $this->shortcodes;
    }

    foreach ($custom_fields as $key => $custom_fields_flexible_checkout_field) {
        $this->shortcodes[$key] = get_post_meta($this->order_id, $key, true);
    }
    return $this->shortcodes;
  }
  private function getCustomFields($order_id)
  {
      $fields = array();
      if (!$this->check()) {
         return $fields;
      }
      $_custom_field_arr=[];
      $field_sections=get_option("thwcfe_sections", '');

      foreach ($field_sections as  $section_name=>$section_value) {
          foreach ($section_value as $field_name => $fields) {
            if ($field_name=='fields') {
              foreach ($fields as $key => $value) {
                if ($value->custom_field=="1") {
                    $_custom_field_arr[$key]='';
                }
              }
            }
          }
      }
      foreach ($_custom_field_arr as $key => $value) {
          $fields[$key]=$this->get_custom_field_value($order_id, $key);
      }
      return $fields;
  }
  private function check()
  {
      if (class_exists('THWCFE')) {
          return true;
      }
      return false;
  }
  private function get_custom_field_value($order_id, $key)
  {
      global $wpdb;
      $query = "select meta_value from $wpdb->postmeta  where meta_key = '".$key."' and post_id=".$order_id." limit 1";
      $result = $wpdb->get_results($query);
      if (sizeof($result)!=0) {
        return $result[0]->meta_value;
      }
      return "";
  }
}