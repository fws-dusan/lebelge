<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Subs Loader
 */
class WooSubsLoader
{
  private $post_helper;
  private $order_id;
  private $subs_data;
  private $subs_meta;
  private $subs_types;
  private $wp_date_format;

  function __construct($order_id)
  {
    $this->order_id=$order_id;
    $this->post_helper=new EC_Helper_Posts();

    $this->subs_types=array();
    $this->subs_types['wc-active'] = __("Active", EC_WOO_BUILDER_TEXTDOMAIN);
    $this->subs_types['wc-cancelled'] = __("Cancelled", EC_WOO_BUILDER_TEXTDOMAIN);
    $this->subs_types['wc-on-hold'] = __("On hold", EC_WOO_BUILDER_TEXTDOMAIN);
    $this->subs_types['wc-pending'] = __("Pending", EC_WOO_BUILDER_TEXTDOMAIN);

    $this->wp_date_format=get_option('date_format','F j, Y');
    //load data from DB
    $this->loadData();
  }

  private function loadData()
  {
    $this->subs_data=$this->get_woo_subscription( $this->order_id );
    if ($this->hasData()) {
      $temp_data=$this->get_woo_subscription_meta( $this->get_subscription_id() );
      foreach ($temp_data as $row) {
        $this->subs_meta[$row->meta_key]=$row->meta_value;
      }
    }
  }

  public function hasData()
  {
    if (empty($this->subs_data) || is_null($this->subs_data)) {
      return false;
    }
    return true;
  }

  private function get_woo_subscription($order_id)
  {
      global $wpdb;
      $query = "select * FROM $wpdb->posts where `post_type`='shop_subscription' and `post_parent`='".$order_id."'";
      $result = $wpdb->get_results($query);
      if (sizeof($result)==0) {
        return;
      }
      return $result[0];
  }

  private function get_woo_subscription_meta($subscription_id)
  {
      global $wpdb;
      $query = "select `meta_key`,`meta_value` FROM $wpdb->postmeta where  `post_id`='".$subscription_id."'";
      $result = $wpdb->get_results($query);
      return $result;
  }

  public function get_subscription_hyperlink()
  {
    return '<a class="ec-woo-wcs-id" href="' . site_url() . '/my-account/view-subscription/'.$this->get_subscription_id().'"> #'
      . $this->get_subscription_id() .
     ' </a>';
  }
  public function get_subscription_id_url()
  {
    return site_url() . '/my-account/view-subscription/'.$this->get_subscription_id();
  }
  public function get_subscription_id()
  {
    return $this->subs_data->ID;
  }
  public function get_subscription_status()
  {
    return $this->subs_types[$this->subs_data->post_status];
  }
  public function get_billing_period()
  {
    return $this->subs_meta['_billing_period'];
  }
  public function get_billing_interval()
  {
    return $this->subs_meta['_billing_interval'];
  }
  public function get_subscription_start_date()
  {
    return $this->formatted_date($this->subs_data->post_date);
  }

  public function get_subscription_end_date()
  {
    $date=$this->formatted_date($this->subs_meta['_schedule_end']);
    if ($date=='') {
      return __("When Cancelled", EC_WOO_BUILDER_TEXTDOMAIN);
    }
    return $date;
  }
  public function get_subscription_price()
  {
    return $this->subs_meta['_order_total'];
  }

  public function get_subscription_next_payment_date()
  {
    return $this->formatted_date($this->subs_meta['_schedule_next_payment']);
  }

  private function formatted_date($str_date)
  {
    if (is_null($str_date) || !isset($str_date) || empty($str_date)) {
      return '';
    }
    $datetime = strtotime($str_date);
    return date($this->wp_date_format, $datetime);
  }
}
