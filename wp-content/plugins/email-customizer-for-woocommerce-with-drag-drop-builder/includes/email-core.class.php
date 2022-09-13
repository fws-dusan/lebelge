<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper class
 */
class EC_Email_Core
{
    private $order_id = -1;
    private $email_type = '';
    private $shortcode_data;
    private $full_shortcode_data;
    private $post_helper;
    private $order;
    private $is_preview;
    private $wpDateFormat;
    private $wpTimeFormat;
    private $wpDateTimeFormat;
    private $log;

    public function __construct()
    {
      $this->log=new Log(LogType::AJAX);
      $this->post_helper = new EC_Helper_Posts();
      $this->is_preview=false;

      $this->wpDateFormat=get_option('date_format','M d, Y');
      $this->wpTimeFormat=get_option('time_format','H:i:s');
      $this->wpDateTimeFormat=$this->wpDateFormat.' '.$this->wpTimeFormat;

      $this->full_shortcode_data=array();
    }

    public function get_order_id()
    {
        return $this->order_id;
    }

    public function is_preview($isPreview)
    {
        $this->is_preview = $isPreview;
    }
    public function set_order_id($id)
    {
        $this->order_id = $id;
    }

    public function get_email_type()
    {
        return $this->email_type;
    }

    public function set_email_type($type)
    {
        $this->email_type = $type;
    }

    public function shortcode_generate($atts, $content, $tag)
    {
        $tag = '[' . $tag . ']';
        if (!isset($this->shortcode_data[$tag])) {
            return '';
        }
        $value = $this->shortcode_data[$tag];
        if (isset($atts['format']) && isset($atts['type']) && $atts['type'] == 'date') {
            $datetime = strtotime($value);
            $new_dateformat = date($atts['format'], $datetime);
            return $new_dateformat;
        }

        return $value;
    }

    public function shortcode_init()
    {

        /*
        * Order Shortcodes
        */
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_id', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_link', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'transaction_id', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_sub_total', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_payment_method', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_payment_url', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_total', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_fee', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_refund', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_date', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_time', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_datetime', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'view_order_url', array($this, 'shortcode_generate'));

        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_date_timezone', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_datetime_timezone', array($this, 'shortcode_generate'));

        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_total_refunded_amount', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_formatted_total', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_remaining_refund_amount', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_total_amount', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_shipping', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'order_shipping_total', array($this, 'shortcode_generate'));

        //customer notes

        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'payment_method', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'items', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'items_1', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'items_2', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'items_3', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'items_4', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'items_5', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'items_6', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'items_1_withouttotal', array($this, 'shortcode_generate'));

        /*
        * User Shortcodes
        */
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'user_name', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'user_id', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'user_email', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'user_activation_link', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'user_password_reset_url', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'user_account_url', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'user_password', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'user_password_reset_url_2', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'user_password_reset_link', array($this, 'shortcode_generate'));
        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'user_account_url_2', array($this, 'shortcode_generate'));

        add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'custom_code', array($this, 'get_custom_code'));



        $subs_data = new WooSubsLoader($this->order_id);
        if ($subs_data->hasData()) {
          add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_id', array($this, 'shortcode_generate'));
          add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_id_url', array($this, 'shortcode_generate'));
          add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_status', array($this, 'shortcode_generate'));
          add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_billing_period', array($this, 'shortcode_generate'));
          add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_billing_interval', array($this, 'shortcode_generate'));
          add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_start_date', array($this, 'shortcode_generate'));
          add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_end_date', array($this, 'shortcode_generate'));
          add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_next_payment_date', array($this, 'shortcode_generate'));
          add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_info_1', array($this, 'shortcode_generate'));
          add_shortcode(EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_info_2', array($this, 'shortcode_generate'));
        }


    }

    public function get_shortcode_data()
    {
        return $this->shortcode_data;
    }

    public function get_full_shortcode_data()
    {
        return $this->full_shortcode_data;
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
        $this->log->write('getDatetimeUTC', $e->getMessage());
        return $value;
      }
    }
    public function collect_data($args = array())
    {
        $_temp_data = array();

        if (is_null($this->order_id)) {
            return;
        }
        //  $_order = wc_get_order( $this->order_id );


        //if ($this->order_id && class_exists('WC_Order')) {
        //  $order = wc_get_order($this->order_id);
        //}
        if ($this->order_id && class_exists('WC_Order')) {
            $this->order = new WC_Order($this->order_id);
        }


        if (is_null($this->order)) {
            return;
        }
        // $order=$this->order;
        //
        // $this->order = $order;
        //
        // $order_items = $this->get_order_items($order->get_items('array'));
        //
        // $order_fee = 0;
        // $order_refund = 0;
        //
        // $totals = $order->get_order_item_totals();
        // foreach ($totals as $index => $value) {
        //     if (strpos($index, 'fee') !== false) {
        //         if (is_numeric($value['value'])) {
        //             $order_fee += $value['value'];
        //         }
        //     }
        //     if (strpos($index, 'refund') !== false) {
        //         $order_refund += $value['value'];
        //     }
        // }
        // //unset($order_total);
        //
        //
        // $order_data = $order->get_data();
        //
        // //TODO: integration 6
        // //Order totals
        // if (isset($totals['cart_subtotal']['value'])) {
        //     $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_sub_total]'] = $totals['cart_subtotal']['value'];
        // } else {
        //     $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_sub_total]'] = '';
        // }
        // if (isset($totals['payment_method']['value'])) {
        //     $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_payment_method]'] = $totals['payment_method']['value'];
        // } else {
        //     $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_payment_method]'] = '';
        // }
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_total]'] = $this->get_order_total($order);
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_fee]'] = $order_fee;
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_refund]'] = $order_refund;
        // //$order->add_rate();
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_shipping]'] = $order->calculate_shipping();
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_shipping_total]'] = $order_data['shipping_total'];
        //
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_payment_url]'] = esc_url($order->get_checkout_payment_url());
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_payment_url]'] = '<a href="' . esc_url($order->get_checkout_payment_url()) . '">' . esc_html__('Payment page', EC_WOO_BUILDER_TEXTDOMAIN) . '</a>';
        //
        // //Order Info
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_id]'] = $this->get_order_number();
        // $admin_order_link=admin_url('post.php?post=' . $this->get_order_number() . '&action=edit');
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_link]'] = '<a href="'.$admin_order_link.'">'.$this->get_order_number() . '</a>';
        // //$_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_link]'] = str_replace('[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_id]', $this->get_order_number(), $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_link]']);
        // $order_date = strtotime($order->get_date_created());
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_date]'] = date($this->wpDateFormat, $order_date);
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_time]'] = date($this->wpTimeFormat, $order_date);
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_datetime]'] = date($this->wpDateTimeFormat, $order_date);
        //
        //
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_date_timezone]'] = date_i18n($this->wpDateFormat, $order_date);
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_datetime_timezone]'] = $this->getDatetimeUTC($order->get_date_created());
        //
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'payment_method]'] = $this->get_order_payment_method_title();
        //
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'view_order_url]'] = '<a href="' . $order->get_view_order_url() . '" >' . $order->get_view_order_url() . '</a>';
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'transaction_id]'] = $order->get_transaction_id();
        //
        //
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_total_refunded_amount]'] = $order->get_total_refunded();
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_formatted_total]'] = $order->get_formatted_order_total();
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_remaining_refund_amount]'] = $order->get_remaining_refund_amount();
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'order_total_amount]'] = $order->get_total();
        //
        //
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'items]'] = $this->get_order_items_template($order, $order_items);
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'items_1]'] = $this->get_order_items_template_1($order, $order_items,true);
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'items_2]'] = $this->get_order_items_template_2($order, $order_items);
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'items_3]'] = $this->get_order_items_template_3($order, $order_items);
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'items_4]'] = $this->get_order_items_template_4($order, $order_items);
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'items_5]'] = $this->get_order_items_template_5($order, $order_items);
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'items_6]'] = $this->get_order_items_template_6($order, $order_items);
        // $_temp_data['Order']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'items_1_withouttotal]'] = $this->get_order_items_template_1($order, $order_items,false);

        //TODO: integration 8
        //WooCommerce Subscription info
        // $subs_data = new WooSubsLoader($this->order_id);
        // if ($subs_data->hasData()) {
        //   $_temp_data['Woo Subscription']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_id]'] = $subs_data->get_subscription_id();
        //   $_temp_data['Woo Subscription']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_id_url]'] = $subs_data->get_subscription_id_url();
        //   $_temp_data['Woo Subscription']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_status]'] = $subs_data->get_subscription_status();
        //   $_temp_data['Woo Subscription']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_billing_period]'] = $subs_data->get_billing_period();
        //   $_temp_data['Woo Subscription']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_billing_interval]'] = $subs_data->get_billing_interval();
        //   $_temp_data['Woo Subscription']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_start_date]'] = $subs_data->get_subscription_start_date();
        //   $_temp_data['Woo Subscription']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_end_date]'] = $subs_data->get_subscription_end_date();
        //   $_temp_data['Woo Subscription']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_next_payment_date]'] = $subs_data->get_subscription_next_payment_date();
        //   $_temp_data['Woo Subscription']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_info_1]'] = $this->get_wcs_info_1($subs_data,$order->get_formatted_order_total());
        //   $_temp_data['Woo Subscription']['[' . EC_WOO_BUILDER_SHORTCODE_PRE . 'wcs_info_2]'] = $this->get_wcs_info_2($subs_data,$order->get_formatted_order_total());
        // }


        $this->convert_shortcode_data($_temp_data);
        $this->full_shortcode_data = $_temp_data;
    }

    public function convert_shortcode_data($arr)
    {
        $converted = array();
        foreach ($arr as $group => $group_items) {
            foreach ($group_items as $shortcode => $value) {
                $converted[$shortcode] = $value;
            }
        }
        $this->shortcode_data = $converted;
    }

    /**
     * To load Custom code
     * */
    public function get_custom_code($attr, $content, $tag){
        ob_start();
        $template = $this->get_template_override('ec-woo-mail-helper/custom-code.php');
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

    public function get_order_total($order)
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

    // public function get_order_customer_notes($customer_notes)
    // {
    //     $template = $this->get_template_override('ec-woo-mail-helper/customer-notes.php');
    //     $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/customer-notes.php';
    //     if($template){
    //         $path = $template;
    //     }
    //     //return $this->get_template_content($path);
    //     ob_start();
    //     include($path);
    //     $html = ob_get_contents();
    //     ob_end_clean();
    //     return $html;
    // }

    public function get_order_items_template($order, $items)
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

    public function get_order_items_template_1($order, $items,$show_total)
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

    public function get_order_items_template_2($order, $items)
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

    public function get_order_items_template_3($order, $items)
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

    public function get_order_items_template_4($order, $items)
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

    public function get_order_items_template_5($order, $items)
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

    public function get_order_items_template_6($order, $items)
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

    private function get_bacs_account_details_html()
    {
        //$template = $this->get_template_override('ec-woo-mail-helper/bacs-info.php');
        $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/bacs-info.php';
        // if($template){
        //     $path = $template;
        // }
        ob_start();
        include($path);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
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

    private function get_template_content($path)
    {
        ob_start();
        include($path);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
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

    public function get_first_or_default($arr)
    {
        $result = $arr;
        if (is_array($arr)) {
            if (isset($arr[0])) {
                $result = $arr[0];
            }
        }
        return $result;
    }

    public function get_order_number()
    {
        return method_exists($this->order, 'get_order_number') ? $this->order->get_order_number() : $this->order->id;
    }

    public function get_order_billing_first_name()
    {
        return method_exists($this->order, 'get_billing_first_name') ? $this->order->get_billing_first_name() : $this->order->billing_first_name;
    }

    public function get_order_billing_last_name()
    {
        return method_exists($this->order, 'get_billing_last_name') ? $this->order->get_billing_last_name() : $this->order->billing_last_name;
    }

    public function get_order_billing_email()
    {
        return method_exists($this->order, 'get_billing_email') ? $this->order->get_billing_email() : $this->order->billing_email;
    }

    public function get_order_payment_method_title()
    {
        return method_exists($this->order, 'get_payment_method_title') ? $this->order->get_payment_method_title() : $this->order->payment_method_title;
    }

    public function get_template_override($template){
        $template = locate_template(
            array(
                trailingslashit( dirname(EC_WOO_BUILDER_PLUGIN_SLUG) ) . $template,
                $template,
            )
        );

        return $template;
    }
}
