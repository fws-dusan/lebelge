<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper class
 */
class WooMailCore
{
  private $integrations;
  private $shortcodesGroupByIntegration;
  private $order_id;
  private $email_type;
  private $order;
  private $isPreview;

  public function __construct($order_id,$email_type,$isPreview)
  {
    // EC_Validation::checkNullOrEmpty($order_id,'WooMailCore->$order_id');
    // EC_Validation::checkNullOrEmpty($email_type,'WooMailCore->$email_type');
    // EC_Validation::checkNullOrEmpty($isPreview,'WooMailCore->$isPreview');

    $this->order_id=$order_id;
    $this->email_type=$email_type;
    $this->isPreview=$isPreview;

    $this->loadOrder();
  }
  private function loadOrder()
  {
    if (is_null($this->order_id)) {
      return;
    }

    if ($this->order_id && class_exists('WC_Order')) {
        $this->order = new WC_Order($this->order_id);
    }
  }
  public function getOrderId()
  {
      return $this->order_id;
  }
  public function getEmailType()
  {
      return $this->email_type;
  }
  public function getShortcodesGroupByIntegration()
  {
      return $this->shortcodesGroupByIntegration;
  }
  public function getShortcodeList()
  {
    return $this->shortcodeList;
  }
  private function registerIntegrations($args = array())
  {
    $this->integrations[] = new WMIntegrationGeneral($this->order);
    $this->integrations[] = new WMIntegrationCustomShortcode();

    if (!empty($args) && $this->email_type=='customer_reset_password') {
      $this->integrations[] = new WMIntegrationResetPassword($args);
      return;
    }
    if (!empty($args) && ($this->email_type=='customer_new_account' || $this->email_type=='customer_new_account_activation')) {
      $this->integrations[] = new WMIntegrationNewAccount($args);
      return;
    }

    if (empty($this->order) || is_null($this->order)) {
      return;
    }
    $this->integrations[] = new WMIntegrationUserInfo($this->order);
    $this->integrations[] = new WMIntegrationBankDetails($this->order);
    $this->integrations[] = new WMIntegrationBilling($this->order);
    $this->integrations[] = new WMIntegrationShipping($this->order);
    $this->integrations[] = new WMIntegrationThemeMotors($this->order);
    $this->integrations[] = new WMIntegrationOrderDelivery($this->order);
    $this->integrations[] = new WMIntegrationOrderDeliveryPro($this->order);
    $this->integrations[] = new WMIntegrationCustomerManager($this->order);
    $this->integrations[] = new WMIntegrationCustomerNotes($this->order);
    $this->integrations[] = new WMIntegrationCheckoutFieldEditor($this->order);
    $this->integrations[] = new WMIntegrationCoupons($this->order);    
    $this->integrations[] = new WMIntegrationFlexibleCheckoutFieldEditor($this->order,$this->getOrderId());
    $this->integrations[] = new WMIntegrationCheckoutManager($this->order,$this->getOrderId());
    $this->integrations[] = new WMIntegrationRelatedItems($this->order,$this->isPreview);
    $this->integrations[] = new WMIntegrationCheckoutFields($this->order,$this->getOrderId());
    $this->integrations[] = new WMIntegrationShippingTracking($this->order,$this->getOrderId());
    $this->integrations[] = new WMIntegrationOrder($this->order,$this->getOrderId());
    $this->integrations[] = new WMIntegrationWooSubscription($this->order,$this->getOrderId());
    $this->integrations[] = new WMIntegrationCheckoutFieldEditorPRO($this->order,$this->getOrderId());
  }

  public function collectData($args = array())
  {
    $this->registerIntegrations($args);

    $this->shortcodesGroupByIntegration = array();
    foreach ($this->integrations as $item) {
      foreach ($item->collectData()  as $key => $value) {
        $this->shortcodesGroupByIntegration[$item->getName()]['['.EC_WOO_BUILDER_SHORTCODE_PRE.$key.']']=$value;
      }
    }
    $this->convertData($this->shortcodesGroupByIntegration);
  }

  public function addShortcodesToWP()
  {
    foreach ($this->shortcodeList as $key=>$value) {
     add_shortcode(str_replace(array("[", "]"),array("", ""),$key), array($this, 'shortcode_generate'));
    }

    add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'custom_code', array($this, 'get_custom_code'));
    add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'custom_field', array($this, 'get_custom_field'));
  }

  /*
  * Convert shortcode data for using shortcode_generate
  */
  private function convertData($arr)
  {
      $converted = array();
      foreach ($arr as $group => $group_items) {
          foreach ($group_items as $shortcode => $value) {
              $converted[$shortcode] = $value;
          }
      }
      $this->shortcodeList = $converted;
  }
  public function shortcode_generate($atts, $content, $tag)
  {
      $key='['.$tag.']';
      if (!isset($this->shortcodeList[$key])) {
          return '';
      }

      return $this->shortcodeList[$key];
  }
  public function get_custom_field($attr, $content, $tag){
    $name = $attr['name'];
    if (isset($name)) {
      return get_post_meta($this->order-> get_order_number(), $name, true );
    }
  }
  public function get_custom_code($attr, $content, $tag){
    ob_start();
    $template = locate_template(array(trailingslashit( dirname(EC_WOO_BUILDER_PLUGIN_SLUG) ) . 'ec-woo-mail-helper/custom-code.php','ec-woo-mail-helper/custom-code.php'));
    $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/custom-code.php';
    if($template){
      $path = $template;
    }
    $order = $this->order;
    $email_id = $this->email_type;
    include($path);
    $html = ob_get_contents();
    ob_end_clean();

    return $html;
  }
}