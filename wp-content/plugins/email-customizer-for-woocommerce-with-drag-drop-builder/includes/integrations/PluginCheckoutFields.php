<?php

if (!defined('ABSPATH')) {
    exit;
}
/*
* Name : Flexible Checkout Fields for WooCommerce
* URL : https://wordpress.org/plugins/flexible-checkout-fields/
*/
class WMIntegrationCheckoutFields extends WooMailIntegration
{
  private $name="Flexible Checkout Fields for WooCommerce";
  private $shortcodes;
  private $order_id;

  public function __construct($order,$order_id)
  {
    EC_Validation::checkNullOrEmpty($order_id,'WMIntegrationCheckoutFields->$order_id');

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
    $custom_fields_flexible_checkout = $this->getCustomFieldsOf_FCFP($this->order_id);
    if (empty($custom_fields_flexible_checkout) || count($custom_fields_flexible_checkout) == 0) {
      return $this->shortcodes;
    }
    foreach ($custom_fields_flexible_checkout as $key => $custom_fields_flexible_checkout_field) {
        $this->shortcodes[$key] = wpdesk_get_order_meta($this->order, $key, true);
    }
    return $this->shortcodes;
  }
  private function getCustomFieldsOf_FCFP($order_id)
  {
      $fields = array();
      if (!$this->checkFCFPlugin()) {
         return $fields;
      }
      $_custom_field_arr=[];
      $field_names=get_option("inspire_checkout_fields_settings", '');

      foreach ($field_names as  $field_name=>$field_value) {
          foreach ($field_value as $key => $value) {
            if ($value['custom_field']=="1") {
                $_custom_field_arr[$key]='';
            }
          }
      }
      foreach ($_custom_field_arr as $key => $value) {
          $fields[$key]=$this->get_custom_field_value_FCFP($order_id, $key);
      }
      return $fields;
  }
  private function checkFCFPlugin()
  {
      if (function_exists('wpdesk_get_order_meta')) {
          return true;
      }
      return false;
  }
  private function get_custom_field_value_FCFP($order_id, $key)
  {
      global $wpdb;
      $query = "select meta_value from $wpdb->postmeta  where meta_key = '_".$key."' and post_id=".$order_id." limit 1";
      $result = $wpdb->get_results($query);
      if (sizeof($result)!=0) {
        return $result[0]->meta_value;
      }
      return "";
  }
}