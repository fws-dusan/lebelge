<?php

if (!defined('ABSPATH')) {
    exit;
}
/*
* Flexible Woocommerce Checkout Field Editor
* https://wordpress.org/plugins/flexible-woocommerce-checkout-field-editor/
*/
class WMIntegrationFlexibleCheckoutFieldEditor extends WooMailIntegration
{
  private $name="Flexible Woocommerce Checkout Field Editor";
  private $shortcodes;
  private $order_id;

  public function __construct($order,$order_id)
  {
    EC_Validation::checkNullOrEmpty($order_id,'WMIntegrationFlexibleCheckoutFieldEditor->$order_id');

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
    $custom_fields_fcew = $this->get_custom_fields_flexible_checkout_editor_woo($this->order_id);
    if (empty($custom_fields_fcew) || count($custom_fields_fcew) == 0) {
        return $this->shortcodes;
    }
    foreach ($custom_fields_fcew as $key => $value) {
        $this->shortcodes[$key] = $value;
    }
    return $this->shortcodes;
  }

  private function get_custom_fields_flexible_checkout_editor_woo($order_id)
  {
    $_custom_field_arr=[];
    if (!$this->check_flexible_checkout_editor_woo()) {
      return $_custom_field_arr;
    }
    $fields=array("orderFieldsOptions",
                "accountFieldsOptions",
                "shippingFieldsOptions",
                "billingFieldsOptions");

    foreach ($fields as $custom_field) {
      $field_names=json_decode(get_option($custom_field, ''));
      foreach ($field_names as  $field_name) {
        if (strpos($field_name->name, 'wc_')>-1) {
            $_custom_field_arr[$field_name->name]='';
        }
      }
    }

    foreach ($_custom_field_arr as $key => $value) {
        $_custom_field_arr[$key]=$this->get_custom_field_value_flexible_checkout_editor_woo($order_id, $key);
    }
    return $_custom_field_arr;
  }
  private function check_flexible_checkout_editor_woo()
  {
      if (class_exists('FWCFE_Settings_Page_Manager')) {
          return true;
      }
      return false;
  }
  private function get_custom_field_value_flexible_checkout_editor_woo($order_id, $key)
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