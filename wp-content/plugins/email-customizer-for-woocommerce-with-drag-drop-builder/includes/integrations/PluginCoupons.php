<?php

if (!defined('ABSPATH')) {
    exit;
}
/*
* Coupons
*/
class WMIntegrationCoupons extends WooMailIntegration
{
  private $name="Coupons";
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
    $n=0;
    foreach( $this->order->get_coupon_codes() as $coupon_code ){
      $n++;
      $coupon_post_obj = get_page_by_title($coupon_code, OBJECT, 'shop_coupon');
      $coupon_id       = $coupon_post_obj->ID;
      $coupon = new WC_Coupon($coupon_id);
      $this->shortcodes['coupon_code_'.$n]= $coupon->get_code();
      $this->shortcodes['coupon_amount_'.$n]= $coupon->get_amount();

      if (!empty($coupon->get_date_expires())) {
        $expire_date = strtotime($coupon->get_date_expires());
        $this->shortcodes['coupon_expire_date_'.$n]= date($this->wpDateFormat, $expire_date);
      }
    }
    return $this->shortcodes;
  }
}