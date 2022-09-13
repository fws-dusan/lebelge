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
$path_order_item = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/order-items-rows-1.php';

if ($ec_woo_settings_show_meta) {
  do_action( 'woocommerce_email_before_order_table', $order, '', '', $email);
}
?>


    <table class="woo-items-list-1"
           cellspacing="0"
           cellpadding="<?php echo $ec_woo_settings_border_padding; ?>"
           style="width: 100% !important;font-family: 'Avenir Next',Avenir,Roboto,'Century Gothic','Franklin Gothic Medium','Helvetica Neue',Helvetica,Arial,sans-serif;"
           border="0">
        <thead>
        <tr>
            <?php if ($ec_woo_settings_rtl == '0'): ?>
                <th scope="col" width="53%" class="col-product"
                    style="text-align:left;border-bottom: 1px solid #ccc;font-size: 16px;"><?php _e('Product', 'woocommerce'); ?></th>
                <th scope="col" width="22%" class="col-quantity"
                    style="text-align:center;border-bottom: 1px solid #ccc;font-size: 16px;"><?php _e('Quantity', 'woocommerce'); ?></th>
                <th scope="col" width="25%" class="col-price"
                    style="text-align:right;border-bottom: 1px solid #ccc;font-size: 16px;"><?php _e('Price', 'woocommerce'); ?></th>
            <?php endif; ?>
            <?php if ($ec_woo_settings_rtl == '1'): ?>
                <th scope="col" width="53%" class="col-product"
                    style="text-align:right;border-bottom: 1px solid #ccc;font-size: 16px;"><?php _e('Product', 'woocommerce'); ?></th>
                <th scope="col" width="22%" class="col-quantity"
                    style="text-align:center;border-bottom: 1px solid #ccc;font-size: 16px;"><?php _e('Quantity', 'woocommerce'); ?></th>
                <th scope="col" width="25%" class="col-price"
                    style="text-align:right;border-bottom: 1px solid #ccc;font-size: 16px;"><?php _e('Price', 'woocommerce'); ?></th>

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
        if (isset($total_values) && $show_total==true) {
            $index = 0;
            foreach ($total_values as $item) {
                $index++; ?>
                <tr>
                    <?php if ($ec_woo_settings_rtl == '1'): ?>
                        <th scope="row" class="col-total-label" width="75%" colspan="2"
                            style="text-align: right;font-size: 13px;padding-bottom: 3px;padding-right: 20px;  <?php echo $index == 1 ? 'padding-top:30px;' : '';
                            echo $index == sizeof($total_values) ? 'font-weight: bold' : 'font-weight: 300;'; ?>">
                            <?php echo $item['label']; ?>
                        </th>
                        <td width="25%" class="col-total-value"
                            style="text-align: right; font-size: 13px;   padding-bottom: 3px;<?php echo $index == 1 ? 'padding-top:30px;' : '';
                            echo $index == sizeof($total_values) ? 'font-weight: bold' : 'font-weight: 300;'; ?>">
                            <?php echo $item['value']; ?>
                        </td>

                    <?php endif; ?>

                    <?php if ($ec_woo_settings_rtl == '0'): ?>
                        <th scope="row" class="col-total-label" width="75%" colspan="2"
                            style="text-align: right;font-size: 13px;padding-bottom: 3px;padding-right: 20px;  <?php echo $index == 1 ? 'padding-top:30px;' : '';
                            echo $index == sizeof($total_values) ? 'font-weight: bold' : 'font-weight: 300;'; ?>">
                            <?php echo $item['label']; ?>
                        </th>
                        <td width="25%" class="col-total-value"
                            style="text-align: right; font-size: 13px;   padding-bottom: 3px;<?php echo $index == 1 ? 'padding-top:30px;' : '';
                            echo $index == sizeof($total_values) ? 'font-weight: bold' : 'font-weight: 300;'; ?>">
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
<?php
if ($ec_woo_settings_show_meta) {
    do_action('woocommerce_email_after_order_table', $order, '', '', $email);
} ?>
