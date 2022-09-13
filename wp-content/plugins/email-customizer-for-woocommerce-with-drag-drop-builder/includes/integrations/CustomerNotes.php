<?php

if (!defined('ABSPATH')) {
    exit;
}

class WMIntegrationCustomerNotes extends WooMailIntegration
{
  private $name="Customer Notes";
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
    $customer_notes = $this->order->get_customer_order_notes();
    $customer_note_result = '';
    $customer_note_result_last_month = '';
    $customer_note_result_last_message = '';
    if (!empty($customer_notes) && count($customer_notes)) {
        $customer_note_result = $this->get_order_customer_notes($customer_notes);
        $this->array_sort_bycolumn($customer_notes, 'comment_date', 'desc');

        $customer_note_result_last_month = $this->get_order_customer_notes($customer_notes);
        $customer_note_result_last_message = $customer_notes[0]->comment_content;
    }

    $this->shortcodes=array();
    $this->shortcodes['customer_notes'] = $customer_note_result;
    $this->shortcodes['customer_notes_last_message'] = $customer_note_result_last_message;
    $this->shortcodes['customer_notes_last_month'] = $customer_note_result_last_month;
    $this->shortcodes['customer_provided_note'] = $this->order->get_customer_note();
    return $this->shortcodes;
  }
  private function get_order_customer_notes($customer_notes)
  {
      $template = $this->get_template_override('ec-woo-mail-helper/customer-notes.php');
      $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/customer-notes.php';
      if($template){
          $path = $template;
      }
      ob_start();
      include($path);
      $html = ob_get_contents();
      ob_end_clean();
      return $html;
  }
  private function array_sort_bycolumn(&$array, $column, $dir = 'asc')
  {
      foreach ($array as $a) {
          $sortcol[$a->$column][] = $a;
      }
      ksort($sortcol);
      foreach ($sortcol as $col) {
          foreach ($col as $row) {
              $newarr[] = $row;
          }
      }
      if ($dir=='desc') {
          $array = array_reverse($newarr);
      } else {
          $array = $newarr;
      }
  }
}