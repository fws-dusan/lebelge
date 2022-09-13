<?php


if (!defined('ABSPATH')) {
    exit;
}

$plain_text = "";
if (array_key_exists("plain_text", $args)) {
    $plain_text = $args['plain_text'];
}


foreach ($items as $item_id => $item) :
    $_product = $item->get_product();

    if (apply_filters('woocommerce_order_item_visible', true, $item)) {
        ?>
        <tr class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'order_item', $item, $order)); ?>">
            <?php if ($args['rtl'] == '0'): ?>
                <td class="td col-product"
                    style="text-align:left; vertical-align:middle;word-wrap:break-word;color:#000;border:1px solid #000"><?php
                    echo '<table width="100%"><tr>';

                    if ($args['show_image'] && ($_product instanceof WC_Product) && $_product->get_image_id()) {
                      echo '<td style="text-align: left; width:' . esc_attr($args['image_width']) . 'px">';
                      echo apply_filters('woocommerce_order_item_thumbnail',
                      '<div class="product-image" style="display:inline-block;vertical-align:middle">'.
                      '<img src="' . ($_product->get_image_id() ? current(wp_get_attachment_image_src($_product->get_image_id(), 'thumbnail')) : wc_placeholder_img_src()) .'" alt="' . esc_attr__('Product Image', 'woocommerce') . '" height="'.esc_attr($args['image_height']).'" width="'.esc_attr($args['image_width']).'" style="height:'.esc_attr($args['image_height']).'px !important;width:'.esc_attr($args['image_width']).'px !important; vertical-align:middle; margin-right: 10px;" /></div>', $item);
                      echo '</td>';
                    }


                    echo '<td>';
                    echo apply_filters('woocommerce_order_item_name', '<div class="product-name">' .
                        '<a href="' . get_permalink($item->get_product_id()) . '">' . $item->get_name() . '</a>'
                        . '</div>', $item, false);

                    if ($args['show_sku'] && is_object($_product) && $_product->get_sku()) {
                        echo ' <span class="product-sku"> (#' . $_product->get_sku() . ') </span>';
                    }

                    do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text);

                    echo '<br/><small class="product-meta">' . wc_display_item_meta($item) . '</small>';


                    if ($args['show_download_links']) {
                      echo wc_display_item_downloads( $item ,array('before' => "<div class='ec-download-item-list'><div class='ec-download-item'>", 'separator' => "</div><div class='ec-download-item'>", 'after' => "</div></div>", 'echo' => false));
                    }

                    do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text);
                    echo '</td>';
                    echo '</tr></table>'; ?></td>
                <td class="td col-quantity"
                    style="text-align:left; vertical-align:middle;color:#000;border:1px solid #000">
                    <?php echo apply_filters('woocommerce_email_order_item_quantity', $item->get_quantity(), $item); ?>
                </td>
                <td class="td col-price"
                    style="text-align:left; vertical-align:middle;color:#000;border:1px solid #000">
                    <?php echo $order->get_formatted_line_subtotal($item); ?>
                </td>
            <?php endif; ?>
            <?php if ($args['rtl'] == '1'): ?>

                <td class="td col-product"
                    style="text-align:right; vertical-align:middle;word-wrap:break-word;color:#000;border:1px solid #000"><?php
                    echo '<table width="100%"><tr>';

                    if ($args['show_image'] && ($_product instanceof WC_Product) && $_product->get_image_id()) {
                      echo '<td style="text-align: right; width:' . esc_attr($args['image_width']) . 'px">';
                       echo apply_filters('woocommerce_order_item_thumbnail',
                      '<div class="product-image" style="display:inline-block;vertical-align:middle">'.
                      '<img src="' . ($_product->get_image_id() ? current(wp_get_attachment_image_src($_product->get_image_id(), 'thumbnail')) : wc_placeholder_img_src()) .'" alt="' . esc_attr__('Product Image', 'woocommerce') . '" height="'.esc_attr($args['image_height']).'" width="'.esc_attr($args['image_width']).'" style="height:'.esc_attr($args['image_height']).'px !important;width:'.esc_attr($args['image_width']).'px !important; vertical-align:middle; margin-right: 10px;" /></div>', $item);
                      echo '</td>';
                    }


                    echo '<td style="text-align: right;">';
                    echo apply_filters('woocommerce_order_item_name', '<div class="product-name">' .
                        '<a href="' . get_permalink($item->get_product_id()) . '">' . $item->get_name() . '</a>'
                        . '</div>', $item, false);

                    if ($args['show_sku'] && is_object($_product) && $_product->get_sku()) {
                        echo ' <span class="product-sku"> (#' . $_product->get_sku() . ') </span>';
                    }

                    do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text);

                    echo '<br/><small class="product-meta">' . wc_display_item_meta($item) . '</small>';

                    if ($args['show_download_links']) {
                      echo wc_display_item_downloads( $item ,array('before' => "<div class='ec-download-item-list'><div class='ec-download-item'>", 'separator' => "</div><div class='ec-download-item'>", 'after' => "</div></div>", 'echo' => false));
                    }

                    do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text);
                    echo '</td>';
                    echo '</tr></table>'; ?></td>

                <td class="td col-quantity"
                    style="text-align:right; vertical-align:middle;color:#000;border:1px solid #000">
                    <?php echo apply_filters('woocommerce_email_order_item_quantity', $item->get_quantity(), $item); ?>
                </td>
                <td class="td col-price"
                    style="text-align:right; vertical-align:middle;color:#000;border:1px solid #000">
                    <?php echo $order->get_formatted_line_subtotal($item); ?>
                </td>
            <?php endif; ?>

        </tr>
        <?php
    }

    if ($args['show_purchase_note'] && is_object($_product) && ($purchase_note = $_product->get_purchase_note())) : ?>
        <tr>
            <td colspan="3"
                style="text-align:left; vertical-align:middle;"><?php echo wpautop(do_shortcode(wp_kses_post($purchase_note))); ?></td>
        </tr>
    <?php endif; ?>

<?php endforeach; ?>
