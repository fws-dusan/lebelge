<?php

if (! defined('ABSPATH')) {
    exit;
}
$gateway    = new WC_Gateway_BACS();
$country    = WC()->countries->get_base_country();
$locale     = $gateway->get_country_locale();
$bacs_info  = get_option( 'woocommerce_bacs_accounts',false);
if ($bacs_info==false){
    return '';
}
// Get sortcode label in the $locale array and use appropriate one
$sort_code_label = isset( $locale[ $country ]['sortcode']['label'] ) ? $locale[ $country ]['sortcode']['label'] : __( 'Sort code', 'woocommerce' );

?>
<div class="woocommerce-bacs-bank-details">
<h2 class="wc-bacs-bank-details-heading"><?php _e('Our bank details',EC_WOO_BUILDER_TEXTDOMAIN); ?></h2>
<?php
$i = -1;

if ( $bacs_info ) : foreach ( $bacs_info as $account ) :
$i++;

$account_name   = esc_attr( wp_unslash( $account['account_name'] ) );
$bank_name      = esc_attr( wp_unslash( $account['bank_name'] ) );
$account_number = esc_attr( $account['account_number'] );
$sort_code      = esc_attr( $account['sort_code'] );
$iban_code      = esc_attr( $account['iban'] );
$bic_code       = esc_attr( $account['bic'] );
?>
<h3 class="wc-bacs-bank-details-account-name"><?php echo $account_name; ?>:</h3>
<ul class="wc-bacs-bank-details order_details bacs_details">
    <li class="bank_name"><?php _e('Bank',EC_WOO_BUILDER_TEXTDOMAIN); ?>: <strong><?php echo $bank_name; ?></strong></li>
    <li class="account_number"><?php _e('Account number',EC_WOO_BUILDER_TEXTDOMAIN); ?>: <strong><?php echo $account_number; ?></strong></li>
    <li class="sort_code"><?php echo $sort_code_label; ?>: <strong><?php echo $sort_code; ?></strong></li>
    <li class="iban"><?php _e('IBAN',EC_WOO_BUILDER_TEXTDOMAIN); ?>: <strong><?php echo $iban_code; ?></strong></li>
    <li class="bic"><?php _e('BIC',EC_WOO_BUILDER_TEXTDOMAIN); ?>: <strong><?php echo $bic_code; ?></strong></li>
</ul>
<?php endforeach; endif; ?>
</div>
