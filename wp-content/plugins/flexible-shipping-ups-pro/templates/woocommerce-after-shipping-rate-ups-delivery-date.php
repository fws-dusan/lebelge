<?php
/**
 * Template for delivary date.
 *
 * @package Flexible Shipping Ups Pro
 * @var int $ups_delivery_date
 */

?>
<div class="ups-delivery-time">
	<?php
		// Translators: delivery date.
		echo sprintf( __( '(Delivery Date: %1$s)', 'flexible-shipping-ups-pro' ), $ups_delivery_date ); // WPCS: XSS ok.
	?>
</div>
