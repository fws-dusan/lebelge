<?php

if (!defined('ABSPATH')) {
    exit;
}
/**
* Checkout Field Editor (Checkout Manager) for WooCommerce
* https://wordpress.org/plugins/woo-checkout-field-editor-pro/
*/
class WMIntegrationCheckoutManager extends WooMailIntegration
{
  private $name="Checkout Field Editor (Checkout Manager) for WooCommerce";
  private $shortcodes;
  private $order_id;

  public function __construct($order,$order_id)
  {
    EC_Validation::checkNullOrEmpty($order_id,'$order_id');

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
    $custom_fields__thwcfd = $this->get_custom_fields_thwcfd($this->order_id);
    if (empty($custom_fields__thwcfd) || !isset($custom_fields__thwcfd) || count($custom_fields__thwcfd) > 0) {
      return $this->shortcodes;
    }

    foreach ($custom_fields__thwcfd as $key => $value) {
        $this->shortcodes[$key] = $value;
    }
    return $this->shortcodes;
  }

  private function get_custom_fields_thwcfd($order_id)
  {
    $_custom_field_arr=[];
    if ($this->check_thwcfd()) {
       return $_custom_field_arr;
    }

    $fields=array("wc_fields_billing","wc_fields_shipping","wc_fields_additional");

    foreach ($fields as $custom_field) {
        $field_names=get_option($custom_field, '');

        if(!isset($custom_fields__thwcfd))
          continue;

        foreach ($field_names as  $field_name=>$value) {

            if ($value['custom']=="1") {
                $_custom_field_arr[$field_name]='';
            }
        }
    }
    foreach ($_custom_field_arr as $key => $value) {
        $_custom_field_arr[$key]=$this->get_custom_field_value_thwcfd($order_id, $key);
    }
    return $_custom_field_arr;
  }
  private function check_thwcfd()
  {
      if (class_exists('THWCFD')) {
          return true;
      }
      return false;
  }
  private function get_custom_field_value_thwcfd($order_id, $key)
  {
      global $wpdb;
      $query = "select meta_value from $wpdb->postmeta  where meta_key = '".$key."' and post_id='".$order_id."' limit 1";
      $result = $wpdb->get_results($query);
      if (sizeof($result)!=0) {
        return $result[0]->meta_value;
      }
      return "";
  }
}