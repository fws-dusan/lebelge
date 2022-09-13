<?php
/**
 * @package Flexible Shipping
 */

?><script type="text/javascript">
	jQuery(function (){
		jQuery('.wc-shipping-zone-add-method').after('<button class="button fs-import-export"><?php echo esc_html( __( 'Flexible Shipping Import/Export', 'flexible-shipping-import-export' ) ); ?></button>');
		jQuery('button.fs-import-export').on('click', function(event) {
			event.preventDefault();
			window.flexible_shipping_import_export();
		});
	});
</script>
