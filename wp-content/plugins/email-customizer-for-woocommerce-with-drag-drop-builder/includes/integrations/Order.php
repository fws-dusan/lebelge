<?php

if (!defined('ABSPATH')) {
    exit;
}

class WMIntegrationOrder extends WooMailIntegration
{
  private $name="Order";
  private $shortcodes;
  private $order_id;

  public function __construct($order,$order_id)
  {
    EC_Validation::checkNullOrEmpty($order_id,'WMIntegrationOrder->$order_id');

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


    $order_fee = 0;
    $order_refund = 0;

    $totals = $this->order->get_order_item_totals();
    foreach ($totals as $index => $value) {
        if (strpos($index, 'fee') !== false) {
            if (is_numeric($value['value'])) {
                $order_fee += $value['value'];
            }
        }
        if (strpos($index, 'refund') !== false) {
            $order_refund += $value['value'];
        }
    }

    $order_data = $this->order->get_data();

    //Order totals
    if (isset($totals['cart_subtotal']['value'])) {
        $this->shortcodes['order_sub_total'] = $totals['cart_subtotal']['value'];
    } else {
        $this->shortcodes['order_sub_total'] = '';
    }
    if (isset($totals['payment_method']['value'])) {
        $this->shortcodes['order_payment_method'] = $totals['payment_method']['value'];
    } else {
        $this->shortcodes['order_payment_method'] = '';
    }
    $this->shortcodes['order_total'] = $this->get_order_total($this->order);
    $this->shortcodes['order_fee'] = $order_fee;
    $this->shortcodes['order_refund'] = $order_refund;
    //$order->add_rate();
    $this->shortcodes['order_shipping'] = $this->order->calculate_shipping();
    $this->shortcodes['order_shipping_total'] = $order_data['shipping_total'];

    $this->shortcodes['order_payment_url'] = esc_url($this->order->get_checkout_payment_url());
    $this->shortcodes['order_payment_url'] = '<a href="' . esc_url($this->order->get_checkout_payment_url()) . '">' . esc_html__('Payment page', EC_WOO_BUILDER_TEXTDOMAIN) . '</a>';
    $this->shortcodes['order_payment_url_2'] = esc_url($this->order->get_checkout_payment_url());

    //Order Info
    $this->shortcodes['order_id'] = $this->get_order_number();
    $admin_order_link=admin_url('post.php?post=' . $this->order_id . '&action=edit');
    $this->shortcodes['order_link'] = '<a href="'.$admin_order_link.'">'.$this->get_order_number() . '</a>';
    $order_date = strtotime($this->order->get_date_created());
    $this->shortcodes['order_date'] = date($this->wpDateFormat, $order_date);
    $this->shortcodes['order_time'] = date($this->wpTimeFormat, $order_date);
    $this->shortcodes['order_datetime'] = date($this->wpDateTimeFormat, $order_date);


    $this->shortcodes['order_date_timezone'] = date_i18n($this->wpDateFormat, $order_date);
    $this->shortcodes['order_datetime_timezone'] = $this->getDatetimeUTC($this->order->get_date_created());

    $this->shortcodes['payment_method'] = $this->get_order_payment_method_title();

    $this->shortcodes['view_order_url'] = '<a href="' . $this->order->get_view_order_url() . '" >' . $this->order->get_view_order_url() . '</a>';
    $this->shortcodes['transaction_id'] = $this->order->get_transaction_id();


    $this->shortcodes['order_total_refunded_amount'] = $this->order->get_total_refunded();
    $this->shortcodes['order_formatted_total'] = $this->order->get_formatted_order_total();
    $this->shortcodes['order_remaining_refund_amount'] = $this->order->get_remaining_refund_amount();
    $this->shortcodes['order_remaining_refund_amount_formatted'] = number_format($this->order->get_remaining_refund_amount(),2);
    $this->shortcodes['order_total_amount'] = $this->order->get_total();

    $this->shortcodes['order_total_refunded_amount_formatted'] = number_format($this->order->get_total_refunded(),2);
    $this->shortcodes['order_currency'] = $this->order->get_currency();
    $this->shortcodes['order_currency_symbol'] = get_woocommerce_currency_symbol($this->order->get_currency());

    $order_items = $this->get_order_items($this->order->get_items('array'));
    $this->shortcodes['items'] = $this->get_order_items_template($this->order, $order_items);
    $this->shortcodes['items_1'] = $this->get_order_items_template_1($this->order, $order_items,true);
    $this->shortcodes['items_2'] = $this->get_order_items_template_2($this->order, $order_items);
    $this->shortcodes['items_3'] = $this->get_order_items_template_3($this->order, $order_items);
    $this->shortcodes['items_4'] = $this->get_order_items_template_4($this->order, $order_items);
    $this->shortcodes['items_5'] = $this->get_order_items_template_5($this->order, $order_items);
    $this->shortcodes['items_6'] = $this->get_order_items_template_6($this->order, $order_items);
    $this->shortcodes['items_7'] = $this->get_order_items_template_7($this->order, $order_items);
    $this->shortcodes['items_1_withouttotal'] = $this->get_order_items_template_1($this->order, $order_items,false);

    $partialRefund=$this->get_partial_refund_latest($this->order_id);
    $this->shortcodes['partial_refund'] = wc_price($partialRefund);

    return $this->shortcodes;
  }
  private function get_order_items($order_items)
  {
      $item_list = array();
      foreach ($order_items as $index => $item) {
          $item_list[$index]['product_name'] = $this->get_first_or_default($item['name']);
          $item_list[$index]['type'] = $this->get_first_or_default($item['type']);
          $item_list[$index]['qty'] = $this->get_first_or_default($item['item_meta']['_qty']);
          $item_list[$index]['tax_class'] = $this->get_first_or_default($item['item_meta']['_tax_class']);
          $item_list[$index]['product_id'] = $this->get_first_or_default($item['item_meta']['_product_id']);
          $item_list[$index]['variation_id'] = $this->get_first_or_default($item['item_meta']['_variation_id']);
          $item_list[$index]['line_total'] = $this->get_first_or_default($item['item_meta']['_line_total']);
          $item_list[$index]['line_subtotal'] = $this->get_first_or_default($item['item_meta']['_line_subtotal']);
          $item_list[$index]['line_subtotal_tax'] = $this->get_first_or_default($item['item_meta']['_line_subtotal_tax']);
          $item_list[$index]['line_tax'] = $this->get_first_or_default($item['item_meta']['_line_tax']);
          $item_list[$index]['line_tax_data'] = $this->get_first_or_default($item['item_meta']['_line_tax_data']);
          $item_list[$index]['item'] = $item;
      }
      return $item_list;
  }

  private function get_partial_refund_latest($order_id)
  {
      global $wpdb;
      $query = "Select pm.meta_value from $wpdb->postmeta pm inner join $wpdb->posts p on pm.post_id=p.ID where p.post_parent=$order_id and pm.meta_key='_refund_amount' order by pm.meta_id desc limit 1";
      $result = $wpdb->get_var($query);
      return $result;
  }

  private function get_order_payment_method_title()
  {
      return method_exists($this->order, 'get_payment_method_title') ? $this->order->get_payment_method_title() : $this->order->payment_method_title;
  }
  private function get_order_total($order)
    {
        $template = $this->get_template_override('ec-woo-mail-helper/order-totals.php');
        $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/order-totals.php';
        if($template){
            $path = $template;
        }
        ob_start();
        include($path);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
  private function getDatetimeUTC($value)
  {
    try {
      $timezone=get_option('timezone_string','empty');
      $gmt_offset=get_option('gmt_offset','empty');
      if ($timezone!='empty') {
        $date = new DateTime($value, new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone($timezone));
        return $date->format($this->wpDateTimeFormat);
      }else if ($gmt_offset!='empty') {
        $date=strtotime($value)+($gmt_offset.'hours');
        return date($this->wpDateTimeFormat,$date);
      }
    } catch (Exception $e) {
      return $value;
    }
  }
  private function get_order_items_template($order, $items)
  {
      $template = $this->get_template_override('ec-woo-mail-helper/order-items.php');
      $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/order-items.php';
      if($template){
          $path = $template;
      }
      //return $this->get_template_content($path);
      ob_start();
      $config = $items;
      include($path);
      $html = ob_get_contents();
      ob_end_clean();
      return $html;
  }

  private function get_order_items_template_1($order, $items,$show_total)
  {
      $template = $this->get_template_override('ec-woo-mail-helper/order-items-1.php');
      $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/order-items-1.php';
      if($template){
          $path = $template;
      }
      ob_start();
      $config = $items;
      include($path);
      $html = ob_get_contents();
      ob_end_clean();
      return $html;
  }

  private function get_order_items_template_2($order, $items)
  {
      $template = $this->get_template_override('ec-woo-mail-helper/order-items-2.php');
      $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/order-items-2.php';
      if($template){
          $path = $template;
      }
      ob_start();
      $config = $items;
      include($path);
      $html = ob_get_contents();
      ob_end_clean();
      return $html;
  }

  private function get_order_items_template_3($order, $items)
  {
      $template = $this->get_template_override('ec-woo-mail-helper/order-items-3.php');
      $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/order-items-3.php';
      if($template){
          $path = $template;
      }
      ob_start();
      $config = $items;
      include($path);
      $html = ob_get_contents();
      ob_end_clean();
      return $html;
  }

  private function get_order_items_template_4($order, $items)
  {
      $template = $this->get_template_override('ec-woo-mail-helper/order-items-4.php');
      $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/order-items-4.php';
      if($template){
          $path = $template;
      }
      //return $this->get_template_content($path);
      ob_start();
      $config = $items;
      include($path);
      $html = ob_get_contents();
      ob_end_clean();
      return $html;
  }

  private function get_order_items_template_5($order, $items)
  {
    $template = $this->get_template_override('ec-woo-mail-helper/order-items-5.php');
    $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/order-items-5.php';
    if($template){
        $path = $template;
    }
      ob_start();
      $config = $items;
      include($path);
      $html = ob_get_contents();
      ob_end_clean();
      return $html;
  }

  private function get_order_items_template_6($order, $items)
  {
      $template = $this->get_template_override('ec-woo-mail-helper/order-items-6.php');
      $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/order-items-6.php';
      if($template){
          $path = $template;
      }
      //return $this->get_template_content($path);
      ob_start();
      $config = $items;
      include($path);
      $html = ob_get_contents();
      ob_end_clean();
      return $html;
  }

  private function get_order_items_template_7($order, $items)
  {
      $template = $this->get_template_override('ec-woo-mail-helper/order-items-7.php');
      $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/order-items-7.php';
      if($template){
          $path = $template;
      }
      //return $this->get_template_content($path);
      ob_start();
      $config = $items;
      include($path);
      $html = ob_get_contents();
      ob_end_clean();
      return $html;
  }
}