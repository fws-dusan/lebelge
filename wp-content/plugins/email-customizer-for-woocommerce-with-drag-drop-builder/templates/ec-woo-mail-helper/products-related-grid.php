<?php
if (!defined('ABSPATH')) {
    exit;
}
$items = $order->get_items();
$product_ids = [];
foreach ($items as $item_id => $item) {
    $_product = $item->get_product();
    $product_ids[] = EC_Helper::get_product_id($_product);
}
$config = array(
    'columns' => get_option('ec_woo_related_items_columns', EC_WOO_BUILDER_RELATED_ITEMS_COLUMNS),
    'count' =>  get_option('ec_woo_related_items_count', EC_WOO_BUILDER_RELATED_ITEMS_COUNT),
    'product_by' => get_option('ec_woo_related_items_product_by', EC_WOO_BUILDER_RELATED_ITEMS_BY),
    'show_name' =>  get_option('ec_woo_related_items_show_name', EC_WOO_BUILDER_RELATED_ITEMS_SHOW_NAME),
    'show_price' => get_option('ec_woo_related_items_show_price', EC_WOO_BUILDER_RELATED_ITEMS_SHOW_PRICE),
    'show_image' => get_option('ec_woo_related_items_show_image', EC_WOO_BUILDER_RELATED_ITEMS_SHOW_IMAGE),
    'is_preview' =>  $params['is_preview']
);

$column_width=100/intval($config['columns']);
$rows_count=ceil(intval($config['count'])/intval($config['columns']));


$related_products_by = $config['product_by'];
$number_related_products = $config['count'];
$product_based_id = [];
$custom_terms = get_terms($related_products_by);//product_type || product_cat
foreach ($custom_terms as $term) {
    $product_based_id[] = $term->term_id;
}

$args = array(
    'post__not_in' => $product_ids,
    //'post__in' 				=> 	$related,
    'posts_per_page' => $number_related_products,
    'post_type' => 'product',
    'orderby' => 'rand',
    'order' => 'DESC',
    'ignore_sticky_posts' => 1,
    'no_found_rows' => 1,
    'tax_query' => array(
        array(
            'taxonomy' => $related_products_by,
            'field' => 'id',
            'terms' => $product_based_id,
        ),
    ),

);

$wp_query = new WP_Query($args);
if (!$wp_query->have_posts()) {
    return;
}

$new_row=true;
$counter_column=0;
?>
<?php if ($wp_query->have_posts()):?>

  <table width="100%" border="0" padding="0" cellspacing="0" cellpadding="0">

<?php
   while ($wp_query->have_posts()) : $wp_query->the_post();
   global $post, $product;
   $url=$config['is_preview']==true?'javascript:void(0)': get_permalink($post->ID);

   if ( $new_row ) {
     $new_row=false;
     echo '<tr>';
   }
?>

      <td align="center" width="<?php echo $column_width ?>%" v-align="top" style="width:<?php echo $column_width ?>%;vertical-align:top;text-align:center;padding:5px;">
        <div class="" style="text-align:center;">
          <?php if($config['show_image']==1): ?>
            <a href="<?php echo $url; ?>">
              <img src="<?php echo ($product->get_image_id() ? current(wp_get_attachment_image_src($product->get_image_id(), 'thumbnail')) : wc_placeholder_img_src()); ?>"  width="100%" style="width:100%" />
            </a>
          <?php endif; ?>
        </div>
        <div class="" style="text-align:center">
            <?php if($config['show_name']==1): ?>
              <div class="ec-related-item-name">
                <a href="<?php echo $url; ?>">
                  <?php echo $post->post_title; ?>
                </a>
              </div>
            <?php endif; ?>
            <?php if($config['show_price']==1): ?>
                <div class="ec-related-item-price">
                    <?php echo $product->get_price_html() ?>
                </div>
            <?php endif; ?>
        </div>

      </td>

      <?php
         $counter_column++;
        if (($counter_column%intval($config['columns']))==0 && $counter_column!=0) {
         $new_row=true;
         $counter_column=0;
         echo '</tr>';
        }

       ?>

<?php endwhile;	 ?>
</table>
<?php endif;  ?>
