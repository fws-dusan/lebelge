<?php
/**
 * Settings sidebar.
 *
 * @package WPDesk\FlexibleShippingUpsPro
 *
 * @var $url string .
 */

?>
<div class="wpdesk-metabox">
	<div class="wpdesk-stuffbox">
		<h3 class="title"><?php esc_html_e( 'Hide UPS shipping method based on:', 'flexible-shipping-ups-pro' ); ?></h3>
		<div class="inside">
			<div class="main">
				<ul>
					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Products', 'flexible-shipping-ups-pro' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Shipping Class', 'flexible-shipping-ups-pro' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Cart weight', 'flexible-shipping-ups-pro' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Cart value', 'flexible-shipping-ups-pro' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Time (Day/Hour)', 'flexible-shipping-ups-pro' ); ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Location', 'flexible-shipping-ups-pro' ); ?>
					</li>
				</ul>

				<a class="button button-primary" href="<?php echo esc_attr( $url ); // @phpstan-ignore-line ?>"
				   target="_blank"><?php esc_html_e( 'Get Conditional Shipping Methods →', 'flexible-shipping-ups-pro' ); ?></a>
			</div>
		</div>
	</div>
</div>
