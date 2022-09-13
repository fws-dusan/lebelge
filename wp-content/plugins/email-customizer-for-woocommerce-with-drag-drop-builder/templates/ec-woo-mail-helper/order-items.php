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

$items = $order->get_items();
$args = array(
    'order' => $order,
    'items' => $items,
    'show_download_links' => $order->is_download_permitted(),
    'show_sku' => $ec_woo_settings_show_sku,
    'show_purchase_note' => $order->is_paid(),
    'show_image' => $ec_woo_settings_show_image == '1' ? true : false,
    'image_width' => $ec_woo_settings_image_width,
    'image_height' => $ec_woo_settings_image_height,
    'rtl' => $ec_woo_settings_rtl
);
$path_order_item = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/order-items-rows.php';

if ($ec_woo_settings_show_meta) {
  do_action( 'woocommerce_email_before_order_table', $order, '', '', $email);
}
?>
<h2 style="color:#000">
    <a class="link order-url"
       href="<?php echo esc_url(admin_url('post.php?post=' . EC_Helper::get_order_number($order) . '&action=edit')); ?>"
       style="color: #0073aa;font-weight:bold;text-decoration:none">
        <?php printf(__('Order #%s', 'woocommerce'), EC_Helper::get_order_number($order)); ?>
    </a>
    <?php printf('<time datetime="%s" class="order-time" >%s</time>', date_i18n('c', strtotime(EC_Helper::get_order_date($order))), date_i18n(wc_date_format(), strtotime(EC_Helper::get_order_date($order)))); ?>
</h2>

<table class="woo-items-list"
       cellspacing="0"
       cellpadding="<?php echo $ec_woo_settings_border_padding; ?>"
       style="width: 100% !important;"
       border="1">
    <thead>
    <tr>
        <?php if ($ec_woo_settings_rtl == '0'): ?>
            <th scope="col" class="col-product" width="60%"
                style="text-align:left;"><?php _e('Product', 'woocommerce'); ?></th>
            <th scope="col" class="col-quantity" width="15%"
                style="text-align:left;"><?php _e('Quantity', 'woocommerce'); ?></th>
            <th scope="col" class="col-price" width="25%"
                style="text-align:left;"><?php _e('Price', 'woocommerce'); ?></th>
        <?php endif; ?>
        <?php if ($ec_woo_settings_rtl == '1'): ?>
            <th scope="col" class="col-product" width="60%"
                style="text-align:right;"><?php _e('Product', 'woocommerce'); ?></th>
            <th scope="col" class="col-quantity" width="15%"
                style="text-align:right;"><?php _e('Quantity', 'woocommerce'); ?></th>
            <th scope="col" class="col-price" width="25%"
                style="text-align:right;"><?php _e('Price', 'woocommerce'); ?></th>
        <?php endif; ?>

    </tr>
    </thead>
    <tbody>
    <?php

    include($path_order_item);
    ?>
    </tbody>
    <tfoot>
    <?php
    $total_values = $order->get_order_item_totals();
    if (isset($total_values)) {
        foreach ($total_values as $item) {
            ?>
            <tr>
                <?php if ($ec_woo_settings_rtl == '0'): ?>
                    <th scope="row" class="col-total-label" colspan="2" style="text-align:left;">
                        <?php echo $item['label']; ?>
                    </th>
                    <td class="col-total-value" style="text-align:left;">
                        <?php echo $item['value']; ?>
                    </td>
                <?php endif; ?>
                <?php if ($ec_woo_settings_rtl == '1'): ?>

                    <th scope="row" class="col-total-label" colspan="2" style="text-align:left;">
                        <?php echo $item['label']; ?>
                    </th>
                    <td class="col-total-value" style="text-align:right;">
                        <?php echo $item['value']; ?>
                    </td>
                <?php endif; ?>

            </tr>
            <?php
        }
    }
    ?>
    </tfoot>
</table>

<?php if ($ec_woo_settings_show_meta) {
  do_action('woocommerce_email_after_order_table', $order, '', '', $email);
} ?>
