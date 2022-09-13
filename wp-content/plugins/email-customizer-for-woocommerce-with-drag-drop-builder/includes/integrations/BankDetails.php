<?php

if (!defined('ABSPATH')) {
    exit;
}

class WMIntegrationBankDetails extends WooMailIntegration
{
  private $name="Bank Details";

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
    $this->shortcodes=[];
    $bacs_info = $this->getBankDetails();
    if ($bacs_info != false) {
        //$this->shortcodes['bank_accounts'] = $this->getBankDetailsTemplate();
        $account_i = 0;
        foreach ($bacs_info as $account) {
          //print_r($account);
            $account_i++;
            $this->shortcodes['account_name_' . $account_i . ''] = esc_attr(wp_unslash($account['account_name']));
            $this->shortcodes['bank_name_' . $account_i . ''] = esc_attr(wp_unslash($account['bank_name']));
            $this->shortcodes['account_number_' . $account_i . ''] = esc_attr($account['account_number']);
            $this->shortcodes['routing_number_' . $account_i . ''] = esc_attr($account['sort_code']);
            $this->shortcodes['iban_' . $account_i . ''] = esc_attr($account['iban']);
            $this->shortcodes['bic_' . $account_i . ''] = esc_attr($account['bic']);
        }
    }
    return $this->shortcodes;
  }
  private function getBankDetails()
  {
      $bacs_info = get_option('woocommerce_bacs_accounts', '-1');
      if ($bacs_info=='-1') {
          return false;
      }
      return $bacs_info;
  }
  private function getBankDetailsTemplate()
  {
      $template = $this->get_template_override('ec-woo-mail-helper/bacs-info.php');
      $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/bacs-info.php';
      if($template){
          $path = $template;
      }
      ob_start();
      include($path);
      $html = ob_get_contents();
      ob_end_clean();
      return $html;
  }
}