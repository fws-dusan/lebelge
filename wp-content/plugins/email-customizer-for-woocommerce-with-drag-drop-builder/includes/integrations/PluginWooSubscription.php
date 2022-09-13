<?php

if (!defined('ABSPATH')) {
    exit;
}
/**
* WooCommerce Subscriptions
* https://woocommerce.com/products/woocommerce-subscriptions/
*/
class WMIntegrationWooSubscription extends WooMailIntegration
{
  private $name="WooCommerce Subscriptions";
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

    $subs_data = new WooSubsLoader($this->order_id);
    if (!$subs_data->hasData()) {
      return $this->shortcodes;
    }

    $this->shortcodes['wcs_id'] = $subs_data->get_subscription_id();
    $this->shortcodes['wcs_id_hyperlink'] = $subs_data->get_subscription_hyperlink();
    $this->shortcodes['wcs_id_url'] = $subs_data->get_subscription_id_url();
    $this->shortcodes['wcs_status'] = $subs_data->get_subscription_status();
    $this->shortcodes['wcs_billing_period'] =  $subs_data->get_billing_period();
    $this->shortcodes['wcs_billing_interval'] = $subs_data->get_billing_interval();
    $this->shortcodes['wcs_start_date'] = $subs_data->get_subscription_start_date();
    $this->shortcodes['wcs_next_payment_date'] =$subs_data->get_subscription_next_payment_date();
    $this->shortcodes['wcs_info_1'] = $this->get_wcs_info_1($subs_data,$this->order->get_formatted_order_total());
    $this->shortcodes['wcs_info_2'] = $this->get_wcs_info_2($subs_data,$this->order->get_formatted_order_total());
    $this->shortcodes['wcs_end_date'] =  $subs_data->get_subscription_end_date();

    return $this->shortcodes;

    //
    // $subscription=$this->get_woo_subscription( $this->order_id );
    // if (empty($subscription) || is_null($subscription)) {
    //   return $this->shortcodes;
    // }
    //
    // $subscription_id=$subscription->ID;
    // $temp_data=$this->get_woo_subscription_meta( $subscription_id );
    // foreach ($temp_data as $row) {
    //   $subscription_meta[$row->meta_key]=$row->meta_value;
    // }

    // $this->shortcodes['wcs_id'] = $subscription_id;
    // $this->shortcodes['wcs_id_hyperlink'] = '<a class="ec-woo-wcs-id" href="' . site_url() . '/my-account/view-subscription/'.$subscription_id.'"> #'. $subscription_id .' </a>';
    // $this->shortcodes['wcs_id_url'] = site_url() . '/my-account/view-subscription/'.$subscription_id;
    // $this->shortcodes['wcs_status'] = $this->subscriptionTypes[$subscription->post_status];
    // $this->shortcodes['wcs_billing_period'] = $subscription_meta['_billing_period'];
    // $this->shortcodes['wcs_billing_interval'] = $subscription_meta['_billing_interval'];
    // $this->shortcodes['wcs_start_date'] = $this->formatted_date($subscription->post_date);
    // $this->shortcodes['wcs_next_payment_date'] = $this->formatted_date($subscription_meta['_schedule_next_payment']);
    // $this->shortcodes['wcs_info_1'] = $this->get_wcs_info_1($subscription,$this->order->get_formatted_order_total());
    // $this->shortcodes['wcs_info_2'] = $this->get_wcs_info_2($subscription,$this->order->get_formatted_order_total());
    // //$this->shortcodes['wcs_price'] =$this->subs_meta['_order_total'];
    //
    // $subscriptionEndDate=$this->formatted_date($subscription_meta['_schedule_end']);
    // $this->shortcodes['wcs_end_date'] = $subscriptionEndDate==''?__("When Cancelled", EC_WOO_BUILDER_TEXTDOMAIN):$subscriptionEndDate;
    //
    // return $this->shortcodes;
  }
  // private function formatted_date($str_date)
  // {
  //   if (is_null($str_date) || !isset($str_date) || empty($str_date)) {
  //     return '';
  //   }
  //   $datetime = strtotime($str_date);
  //   return date($this->wpDateFormat, $datetime);
  // }
  // /*
  // * Get Single WooSubscription info
  // */
  // private function get_woo_subscription($order_id)
  // {
  //     global $wpdb;
  //     $query = "select * FROM $wpdb->posts where `post_type`='shop_subscription' and `post_parent`='".$order_id."'";
  //     $result = $wpdb->get_results($query);
  //     if (sizeof($result)==0) {
  //       return;
  //     }
  //     return $result[0];
  // }
  // private function get_woo_subscription_meta($subscription_id)
  // {
  //     global $wpdb;
  //     $query = "select `meta_key`,`meta_value` FROM $wpdb->postmeta where  `post_id`='".$subscription_id."'";
  //     $result = $wpdb->get_results($query);
  //     return $result;
  // }
  /*
  *  Woocommerce subscription
  */
  private function get_wcs_info_1($wcs_data,$order_total)
  {
      $template = $this->get_template_override('ec-woo-mail-helper/wcs-info-1.php');
      $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/wcs-info-1.php';
      if($template){
          $path = $template;
      }
      ob_start();
      include($path);
      $output = ob_get_clean();
      return $output;
  }
  /*
  *  Woocommerce subscription
  */
  private function get_wcs_info_2($wcs_data,$order_total)
  {
      $template = $this->get_template_override('ec-woo-mail-helper/wcs-info-2.php');
      $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/wcs-info-2.php';
      if($template){
          $path = $template;
      }
      ob_start();
      include($path);
      $output = ob_get_clean();
      return $output;
  }
}