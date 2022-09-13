<?php


if (!defined('ABSPATH')) {
    exit;
}

$email = (isset($email) ? $email : '');
$ec_woo_settings_border_padding = get_option('ec_woo_settings_border_padding', EC_WOO_BUILDER_BORDER_PADDING);
$ec_woo_settings_image_width = get_option('ec_woo_settings_image_width', EC_WOO_BUILDER_IMG);
$ec_woo_settings_image_height = get_option('ec_woo_settings_image_height', EC_WOO_BUILDER_IMG);
$ec_woo_settings_show_image = get_option('ec_woo_settings_show_image', EC_WOO_BUILDER_SHOW_IMAGE);
$ec_woo_settings_show_sku = get_option('ec_woo_settings_show_sku', EC_WOO_BUILDER_SHOW_SKU);
$ec_woo_settings_rtl = get_option('ec_woo_settings_rtl', EC_WOO_BUILDER_RTL);
$ec_woo_settings_show_meta = get_option('ec_woo_settings_show_meta', EC_WOO_BUILDER_SHOW_META)==1?true:false;
$ec_woo_settings_show_product_link = get_option('ec_woo_settings_show_product_link', EC_WOO_BUILDER_SHOW_PRODUCT_LINK)==1?true:false;

$items = $order->get_items();
$args = array(
    'order' => $order,
    'items' => $items,
    'show_download_links' => $order->is_download_permitted(),
    'show_sku' => $ec_woo_settings_show_sku,
    'show_product_link' => $ec_woo_settings_show_product_link,
    'show_purchase_note' => $order->is_paid(),
    'show_image' => $ec_woo_settings_show_image == '1' ? true : false,
    'image_width' => $ec_woo_settings_image_width,
    'image_height' => $ec_woo_settings_image_height,
    'rtl' => $ec_woo_settings_rtl
);
$path_order_item = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/order-items-rows-7.php';

if ($ec_woo_settings_show_meta) {
  do_action( 'woocommerce_email_before_order_table', $order, '', '', $email);
}
?>


<?php include($path_order_item); ?>

<table class="woo-items-list-4-total" width="100%" bg-color="#f9f9f9" cellpadding="0" border="0" cellspacing="0"
       style="width:100%;background-color:#f9f9f9;">
    <?php
    $total_values = $order->get_order_item_totals();
    if (isset($total_values)) {
        $index = 0;
        foreach ($total_values as $item) {
            $index++; ?>
            <tr>
                <?php if ($ec_woo_settings_rtl == '0'): ?>
                    <td class="col-total-label" scope="row" width="65%" colspan="2"
                        style="text-align: left;    color: #606060;font-size: 13px;font-family: sans-serif;font-weight: normal;padding-top: 5px;  padding-bottom: 5px;padding-left: 20px;letter-spacing: 0.5px;  <?php echo $index == 1 ? 'padding-top:20px;' : '';
                        echo $index == sizeof($total_values) ? 'font-weight: bold;padding-bottom:20px;' : 'font-weight: 300;'; ?>">
                        <?php echo $item['label']; ?>
                    </td>
                    <td class="col-total-value" width="35%"
                        style="text-align: right; color: #262626; padding-right: 20px; font-family: Helvetica, sans-serif;font-size: 14px;font-weight: normal;<?php echo $index == 1 ? 'padding-top:30px;' : '';
                        echo $index == sizeof($total_values) ? 'font-weight: bold;padding-bottom:20px;' : 'font-weight: 300;'; ?>">
                        <?php echo $item['value']; ?>
                    </td>
                <?php endif; ?>
                <?php if ($ec_woo_settings_rtl == '1'): ?>

                    <td class="col-total-label" scope="row" width="65%" colspan="2"
                        style="text-align: right;    color: #606060;font-size: 13px;font-family: sans-serif;font-weight: normal;padding-top: 5px;  padding-bottom: 5px;padding-right: 20px;letter-spacing: 0.5px;  <?php echo $index == 1 ? 'padding-top:20px;' : '';
                        echo $index == sizeof($total_values) ? 'font-weight: bold;padding-bottom:20px;' : 'font-weight: 300;'; ?>">
                        <?php echo $item['label']; ?>
                    </td>
                    <td class="col-total-value" width="35%"
                        style="text-align: right; color: #262626; padding-right: 20px; font-family: Helvetica, sans-serif;font-size: 14px;font-weight: normal;<?php echo $index == 1 ? 'padding-top:30px;' : '';
                        echo $index == sizeof($total_values) ? 'font-weight: bold;padding-bottom:20px;' : 'font-weight: 300;'; ?>">
                        <?php echo $item['value']; ?>
                    </td>
                <?php endif; ?>

            </tr>
            <?php
        }
    }
    ?>
</table>
<?php
if ($ec_woo_settings_show_meta) {
do_action('woocommerce_email_after_order_table', $order, '', '', $email);
}?>