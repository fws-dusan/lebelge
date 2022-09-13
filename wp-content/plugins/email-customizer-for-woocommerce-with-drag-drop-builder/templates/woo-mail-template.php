<?php

if (! defined('ABSPATH')) {
    exit;
}

EC_Helper::logger();

$ec_woo_settings_custom_css=get_option('ec_woo_settings_custom_css', '');
$ec_woo_settings_rtl=get_option('ec_woo_settings_rtl', EC_WOO_BUILDER_RTL)=='1'?'rtl':'ltr';

$email_type='';
$order_id=null;
$lang=strtolower(EC_Helper::get_locale());


if (!isset($args['email']) && isset($args['order']) && isset($args['email_heading'])) {
    global $woocommerce;
    $mailerWC = $woocommerce->mailer();
    if (isset($mailerWC->emails)) {
        $emailWC = $mailerWC->emails;
        foreach ($emailWC as $mailer) {
            if (!empty($mailer->object) && $args['email_heading'] == $mailer->heading) {
                $args['email'] = $mailer;
                break;
            }
        }
    }
}

if (isset($args['order']) && $args['order']->get_id()) {
  $order_id=$args['order']->get_id();
}
if (!empty( $args ) ) {
    $email_type=$args['email']->id;

    $lang=strtolower(EC_Helper::get_order_language($order_id));
    $email_core = new WooMailCore($order_id,$email_type,false);
    // $email_core->is_preview(false);
    // $email_core->set_order_id($order_id);
    // $email_core->set_email_type($email_type);

    $email_core->collectData($args);
    $email_core->addShortcodesToWP();

    $post_helper=new EC_Helper_Posts();
    $email_content=$post_helper->get_email_content($lang, $email_type);
    $generated_html=json_decode($email_content)->html;
    $generated_html = do_shortcode($generated_html);


    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($generated_html, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//*");
    foreach ($nodes as $node) {
        $node->setAttribute('dir', $ec_woo_settings_rtl);
    }

    echo $dom->saveHTML();
} else {
    echo 'has problem';
}
?>

<style id="page-custom-style">
  <?php echo $ec_woo_settings_custom_css; ?>
</style>
