<div class="tracking-header">
	<?php do_action( 'trackship_tracking_header_before', $order_id, $tracker, $item['formatted_tracking_provider'], $item['tracking_number'] ); ?>
	<div class="provider_image_div" style="<?php esc_html_e( ( 1 == $hide_tracking_provider_image ) ? 'display:none' : '' ); ?>">
		<img class="provider_image" src="<?php esc_html_e( $item['tracking_provider_image'] ); ?>">
	</div>
	<div class="tracking_number_div">
		<ul>			
			<li>
				<?php esc_html_e( apply_filters( 'ast_provider_title', esc_html( $item['formatted_tracking_provider'] ) ) ); ?>:</span> 
				<?php if ( 1 == $wc_ast_link_to_shipping_provider ) { ?>
					<a href="<?php esc_html_e( $item['formatted_tracking_link'] ); ?>" target="blank"><strong><?php esc_html_e( $item['tracking_number'] ); ?></strong></a>	
				<?php } else { ?>
					<strong><?php esc_html_e( $item['tracking_number'] ); ?></strong>	
				<?php } ?>
			</li>
		</ul>
	</div>					
	<h1 class="shipment_status_heading <?php esc_html_e( $tracker->ep_status ); ?>">
		<?php esc_html_e( apply_filters( 'trackship_status_filter', $tracker->ep_status ) ); ?>
	</h1>	
	<span class="est_delivery_date">
		<?php esc_html_e( 'Est. Delivery Date', 'woo-advanced-shipment-tracking' ); ?>: <strong>
		<?php 
		if ( $tracker->est_delivery_date ) {
			echo esc_html( date_i18n( 'l, M d', strtotime( $tracker->est_delivery_date ) ) );
		} else {
			echo 'N/A';
		}
		?>
		</strong>
	</span>		
</div>
