<?php


if (!defined('ABSPATH')) {
    exit;
}
$plain_text = "";
if (array_key_exists("plain_text", $args)) {
    $plain_text = $args['plain_text'];
}
$row_index = 0;
foreach ($items as $item_id => $item) :
    $_product = $item->get_product();
    $row_index++;
    if (apply_filters('woocommerce_order_item_visible', true, $item)) {
        ?>
      <div class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'order_item', $item, $order)); ?>" style="background-color:transparent;" >
      	<div class="block-grid three-up no-stack"	style="margin: 0 auto; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;">
      		<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
      			<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:700px"><tr class="layout-full-width" style="background-color:transparent"><![endif]-->
      			<!--[if (mso)|(IE)]><td align="center" width="350" style="background-color:transparent;width:350px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 15px; padding-top:5px; padding-bottom:5px;"><![endif]-->
      			<div class="col num6" style="display: table-cell; vertical-align: top; ">
      				<div style="width:100% !important;">
      					<!--[if (!mso)&(!IE)]><!-->
      					<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 15px;">
      						<!--<![endif]-->
      						<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px; font-family: Arial, sans-serif"><![endif]-->
      						<div style="color:#121212;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;line-height:120%;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
      							<div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 14px; color: #121212;">
                      <?php
                      if ($args['show_image'] && ($_product instanceof WC_Product) && $_product->get_image_id()) {
                        echo apply_filters('woocommerce_order_item_thumbnail',
                        '<div class="product-image" style="display:inline-block;vertical-align:middle">'.
                        '<img src="' . ($_product->get_image_id() ? current(wp_get_attachment_image_src($_product->get_image_id(), 'thumbnail')) : wc_placeholder_img_src()) .'" alt="' . esc_attr__('Product Image', 'woocommerce') . '" height="'.esc_attr($args['image_height']).'" width="'.esc_attr($args['image_width']).'" style="height:'.esc_attr($args['image_height']).'px !important;width:'.esc_attr($args['image_width']).'px !important; vertical-align:middle; margin-right: 10px;" />'.
                        '</div>', $item);
                      }?>
                      <p class="product-name-wrapper" style="font-size: 14px; line-height: 19px; margin: 0;  display: inline-block;">
      									<span class="product-name" style="font-size: 16px;">
                          <?php
                              if ($args['show_product_link']){
                                echo apply_filters('woocommerce_order_item_name',
                                    '<a href="' . get_permalink($item->get_product_id()) . '">' . $item->get_name() . '</a>',
                                    $item, false);
                              }else {
                                echo apply_filters('woocommerce_order_item_name', $item->get_name(),
                                    $item, false);
                              }?>
      									</span>
                        <?php
                        if ($args['show_sku'] && is_object($_product) && $_product->get_sku()) {
                            echo '<br/> <span class="product-sku"> (#' . $_product->get_sku() . ') </span>';
                        } ?>
                      </p>
                        <?php

                        do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text);

                        echo '<br/><small class="product-meta">' . wc_display_item_meta($item) . '</small>';

                        if ($args['show_download_links']) {
                          echo wc_display_item_downloads( $item ,array('before' => "<div class='ec-download-item-list'><div class='ec-download-item'>", 'separator' => "</div><div class='ec-download-item'>", 'after' => "</div></div>", 'echo' => false));
                        }
                        do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text);
                        ?>

      							</div>
      						</div>
      						<!--[if mso]></td></tr></table><![endif]-->
      						<!--[if (!mso)&(!IE)]><!-->
      					</div>
      					<!--<![endif]-->
      				</div>
      			</div>
      			<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
      			<!--[if (mso)|(IE)]></td><td align="center" width="175" style="background-color:transparent;width:175px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 5px; padding-left: 5px; padding-top:5px; padding-bottom:5px;"><![endif]-->
      			<div class="col num3"
      				style="display: table-cell; vertical-align: top; max-width: 320px; min-width: 50px;vertical-align: middle;">
      				<div style="width:100% !important;">
      					<!--[if (!mso)&(!IE)]><!-->
      					<div
      						style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 5px; padding-left: 5px;">
      						<!--<![endif]-->
      						<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px; font-family: Arial, sans-serif"><![endif]-->
      						<div
      							style="color:#6d7173;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;line-height:120%;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
      							<div
      								style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 14px; color: #6d7173;">
      								<p style="font-size: 12px; line-height: 19px; text-align: right; margin: 0;">
      									<span style="font-size: 16px;">
      										x<?php echo apply_filters('woocommerce_email_order_item_quantity', $item->get_quantity(), $item); ?>
      									</span>
      								</p>
      							</div>
      						</div>
      						<!--[if mso]></td></tr></table><![endif]-->
      						<!--[if (!mso)&(!IE)]><!-->
      					</div>
      					<!--<![endif]-->
      				</div>
      			</div>
      			<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
      			<!--[if (mso)|(IE)]></td><td align="center" width="175" style="background-color:transparent;width:175px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 15px; padding-left: 0px; padding-top:5px; padding-bottom:5px;"><![endif]-->
      			<div class="col num3"
      				style="display: table-cell; vertical-align: top; max-width: 320px; min-width: 80px;width:150px; vertical-align: middle;">
      				<div style="width:100% !important;">
      					<!--[if (!mso)&(!IE)]><!-->
      					<div
      						style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 15px; padding-left: 0px;">
      						<!--<![endif]-->
      						<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px; font-family: Arial, sans-serif"><![endif]-->
      						<div
      							style="color:#121212;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;line-height:120%;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
      							<div
      								style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 14px; color: #121212;">
      								<p style="font-size: 14px; line-height: 19px; text-align: right; margin: 0;">
      									<span style="font-size: 16px;">
      										<?php echo $order->get_formatted_line_subtotal($item); ?>
      									</span>
      								</p>
      							</div>
      						</div>
      						<!--[if mso]></td></tr></table><![endif]-->
      						<!--[if (!mso)&(!IE)]><!-->
      					</div>
      					<!--<![endif]-->
      				</div>
      			</div>
      			<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
      			<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
      		</div>
      	</div>
      </div>
      <div style="background-color:transparent;">
						<div class="block-grid " style="margin: 0 auto;  max-width: 700px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;">
							<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
								<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:700px"><tr class="layout-full-width" style="background-color:transparent"><![endif]-->
								<!--[if (mso)|(IE)]><td align="center" width="200" style="background-color:transparent;width:700px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;"><![endif]-->
								<div class="col num12" style="min-width: 320px; max-width: 700px; display: table-cell; vertical-align: top; width: 700px;">
									<div style="width:100% !important;">
										<!--[if (!mso)&(!IE)]><!-->
										<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;">
											<!--<![endif]-->
											<table class="divider" border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" role="presentation" valign="top">
												<tbody>
													<tr style="vertical-align: top;" valign="top">
														<td class="divider_inner" style="word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 5px; padding-right: 20px; padding-bottom: 5px; padding-left: 20px;" valign="top">
															<table class="divider_content" border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 1px solid #dee3e4; height: 0px; width: 100%;" align="center" role="presentation" height="0" valign="top">
																<tbody>
																	<tr style="vertical-align: top;" valign="top">
																		<td style="word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;" height="0" valign="top"><span></span></td>
																	</tr>
																</tbody>
															</table>
														</td>
													</tr>
												</tbody>
											</table>
											<!--[if (!mso)&(!IE)]><!-->
										</div>
										<!--<![endif]-->
									</div>
								</div>
								<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
								<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
							</div>
						</div>
					</div>
      <?php
  }

  if ($args['show_purchase_note'] && is_object($_product) && ($purchase_note = $_product->get_purchase_note())) : ?>
      <div>
          <?php echo wpautop(do_shortcode(wp_kses_post($purchase_note))); ?>
      </div>
  <?php endif; ?>
<?php endforeach; ?>
