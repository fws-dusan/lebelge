<?php

if (!defined('ABSPATH')) {
    exit;
}
/*
* Checkout Field Editor
* https://woocommerce.com/products/woocommerce-checkout-field-editor/
*/
class WMIntegrationCheckoutFieldEditor extends WooMailIntegration
{
  private $name="Checkout Field Editor";
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
    if (empty($this->order) || is_null($this->order)) {
      return $this->shortcodes;
    }
    if (!function_exists('wc_get_custom_checkout_fields')) {
      return $this->shortcodes;
    }

    $custom_fields = wc_get_custom_checkout_fields($this->order);
    if (!empty($custom_fields)) {
        foreach ($custom_fields as $key => $custom_field) {
            $this->shortcodes[$key] = get_post_meta($this->get_order_number(), $key, true);
        }
    }


    return $this->shortcodes;
  }
}