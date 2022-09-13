<?php
/**
 * Template for days to arrival.
 *
 * @package Flexible Shipping Ups Pro
 *
 * @var int $ups_days_to_arrival_date
 */

?>
<div class="ups-delivery-time">
	<?php
	if ( 0 === intval( $ups_days_to_arrival_date ) ) {
		_e( '(Delivery Days: 0 days)', 'flexible-shipping-ups-pro' ); // WPCS: XSS ok.
	} else {
		// Translators: time in transit.
		echo sprintf( _n( '(Delivery Days: %1$d day)', '(Delivery Days: %1$d days)', $ups_days_to_arrival_date, 'flexible-shipping-ups-pro' ), $ups_days_to_arrival_date ); // WPCS: XSS ok.
	}
	?>
</div>
