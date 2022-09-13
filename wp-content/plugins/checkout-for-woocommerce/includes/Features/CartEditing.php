<?php

namespace Objectiv\Plugins\Checkout\Features;

use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Interfaces\RunsOnPluginActivationInterface;
use Objectiv\Plugins\Checkout\Interfaces\SettingsGetterInterface;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 */
class CartEditing extends FeaturesAbstract implements RunsOnPluginActivationInterface {
	protected function run_if_cfw_is_enabled() {
		add_action( 'cfw_update_checkout_after_customer_save', array( $this, 'handle_update_checkout' ), 10, 1 );
		add_filter( 'cfw_cart_item_quantity_control', array( $this, 'get_cart_edit_item_quantity_control' ), 10, 4 );
	}

	public function init() {
		parent::init();

		add_action( 'cfw_cart_summary_after_admin_page_controls', array( $this, 'output_admin_fields' ), 10, 1 );
	}

	/**
	 * @param array $post_data
	 */
	public function handle_update_checkout( array $post_data ) {
		if ( ! isset( $post_data['cart'] ) || 'true' !== $post_data['cfw_update_cart'] ) {
			return;
		}

		foreach ( $post_data['cart'] as $cart_item_key => $value ) {
			$cart_item = WC()->cart->get_cart_item( $cart_item_key );

			/** @var \WC_Product $cart_item_product */
			$cart_item_product = $cart_item['data'];

			$max_quantity = apply_filters( 'woocommerce_quantity_input_max', $cart_item_product->get_max_purchase_quantity() > 0 ? $cart_item_product->get_max_purchase_quantity() : PHP_INT_MAX, $cart_item_product );

			if ( $value['qty'] > $max_quantity ) {
				$value['qty'] = $max_quantity;
			}

			/**
			 * Remove items from the cart contents
			 * Ensures things like subscriptions update their output properly
			 * Note: Using strval() here instead of intval to handle partial quantities like 0.5
			 *
			 * We don't use WC()->cart->set_quantity()'s understanding of setting a 0 quantity because it causes a bug we can't remember.
			 */
			if ( '0' === strval( $value['qty'] ) ) {
				WC()->cart->remove_cart_item( $cart_item_key );
			} elseif ( WC()->cart->cart_contents[ $cart_item_key ]['quantity'] !== $value['qty'] ) {
				WC()->cart->set_quantity( $cart_item_key, $value['qty'], false );
			}
		}

		// Check cart has contents.
		if ( WC()->cart->is_empty() && ! is_customize_preview() && apply_filters( 'woocommerce_checkout_redirect_empty_cart', true ) ) {
			/**
			 * Filters whether to suppress checkout is not available message
			 * when editing cart results in empty cart
			 *
			 * @since 3.14.0
			 *
			 * @param bool $supress_notice Whether to suppress the message
			 */
			if ( false === apply_filters( 'cfw_cart_edit_redirect_suppress_notice', false ) ) {
				wc_add_notice( cfw__( 'Checkout is not available whilst your cart is empty.', 'woocommerce' ), 'notice' );
			}

			// Allow shortcodes to be used in empty cart redirect URL field
			// This is necessary so that WPML (etc) can swap in a locale specific URL
			$cart_editing_redirect_url = do_shortcode( $this->settings_getter->get_setting( 'cart_edit_empty_cart_redirect' ) );

			$redirect = empty( $cart_editing_redirect_url ) ? wc_get_cart_url() : $cart_editing_redirect_url;

			add_filter(
				'cfw_update_checkout_redirect',
				function() use ( $redirect ) {
					return $redirect;
				}
			);
		}
	}

	/**
	 * @param string $output
	 * @param array $cart_item
	 * @param \WC_Product $product
	 * @param string $cart_item_key
	 *
	 * @return false|string
	 */
	public function get_cart_edit_item_quantity_control( string $output, array $cart_item, \WC_Product $product, string $cart_item_key ) {
		/**
		 * Get the output of the cart quantity control to determine if it's being modified
		 *
		 * Output filtering is required because some very stupid YITH plugins echo on the filter instead of returning something.
		 */
		$product_quantity = woocommerce_quantity_input(
			array(
				'input_name'   => "cart[{$cart_item_key}][qty]",
				'input_value'  => $cart_item['quantity'],
				'max_value'    => $product->get_max_purchase_quantity(),
				'min_value'    => '0',
				'product_name' => $product->get_name(),
			),
			$product,
			false
		);

		ob_start();

		$woocommerce_core_cart_quantity = apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.

		$filter_output = ob_get_clean();

		if ( ! empty( $filter_output ) ) {
			$woocommerce_core_cart_quantity = $filter_output;
		}

		$max_quantity = apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity() > 0 ? $product->get_max_purchase_quantity() : PHP_INT_MAX, $product );
		$maxed        = $cart_item['quantity'] >= $max_quantity || $product->is_sold_individually();

		/**
		 * Filters cart item minimum quantity
		 *
		 * @since 2.0.0
		 *
		 * @param int $min_quantity Cart item minimum quantity
		 * @param array $cart_item The cart item
		 * @param string $cart_item_key The cart item key
		 */
		$min_quantity = apply_filters( 'cfw_cart_item_quantity_min_value', 1, $cart_item, $cart_item_key );

		/**
		 * Filters cart item quantity step
		 *
		 * Determines how much to increment or decrement by
		 *
		 * @since 2.0.0
		 *
		 * @param int $quantity_step Cart item quantity step amount
		 * @param array $cart_item The cart item
		 * @param string $cart_item_key The cart item key
		 */
		$quantity_step = apply_filters( 'cfw_cart_item_quantity_step', 1, $cart_item, $cart_item_key );

		ob_start();
		if ( $woocommerce_core_cart_quantity === $product_quantity && cfw_is_checkout() ) {
			?>
			<div class="cfw-edit-item-quantity-control-wrap">
				<div class="cfw-quantity-stepper">
					<input type="hidden" data-min-value="<?php echo $min_quantity; ?>" data-step="<?php echo $quantity_step; ?>" data-max-quantity="<?php echo $max_quantity; ?>" class="cfw-edit-item-quantity-value" name="cart[<?php echo $cart_item_key; ?>][qty]" value="<?php echo $cart_item['quantity']; ?>" />
					<div class="cfw-quantity-stepper-btn-minus"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M376 232H8c-4.42 0-8 3.58-8 8v32c0 4.42 3.58 8 8 8h368c4.42 0 8-3.58 8-8v-32c0-4.42-3.58-8-8-8z"/></svg></div>
					<div class="cfw-quantity-stepper-btn-plus <?php echo $maxed ? 'maxed' : ''; ?>">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M376 232H216V72c0-4.42-3.58-8-8-8h-32c-4.42 0-8 3.58-8 8v160H8c-4.42 0-8 3.58-8 8v32c0 4.42 3.58 8 8 8h160v160c0 4.42 3.58 8 8 8h32c4.42 0 8-3.58 8-8V280h160c4.42 0 8-3.58 8-8v-32c0-4.42-3.58-8-8-8z"/></svg>
					</div>
				</div>
				<a href="javascript:" data-quantity="<?php echo esc_attr( $cart_item['quantity'] ); ?>" class="cfw-quantity-bulk-edit cfw-xtra-small"><?php cfw_e( 'Edit', 'woocommerce' ); ?></a>
			</div>

			<?php
			return ob_get_clean();
		}
		?>
		<div class="cfw-edit-item-quantity-control-wrap">
			<input type="hidden" data-min-value="<?php echo $min_quantity; ?>" data-step="<?php echo $quantity_step; ?>" data-max-quantity="<?php echo $max_quantity; ?>" class="cfw-edit-item-quantity-value" name="cart[<?php echo $cart_item_key; ?>][qty]" value="<?php echo $cart_item['quantity']; ?>" />
			<a href="javascript:" data-quantity="<?php echo esc_attr( $cart_item['quantity'] ); ?>" class="cfw-quantity-remove-item cfw-xtra-small"><?php cfw_e( 'Remove', 'woocommerce' ); ?></a>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param PageAbstract $cart_summary_admin_page
	 */
	public function output_admin_fields( PageAbstract $cart_summary_admin_page ) {
		if ( ! $this->available ) {
			$notice = $cart_summary_admin_page->get_upgrade_required_notice( $this->required_plans_list );
		}

		$cart_summary_admin_page->output_checkbox_row(
			'enable_cart_editing',
			cfw__( 'Cart Editing', 'checkout-wc' ),
			cfw__( 'Enable cart editing.', 'checkout-wc' ),
			cfw__( 'Enable or disable cart editing feature. Allows customer to remove or adjust quantity of cart items.', 'checkout-wc' ),
			$this->available,
			$notice ?? ''
		);

		$cart_summary_admin_page->output_text_input_row(
			'cart_edit_empty_cart_redirect',
			cfw__( 'Cart Editing Empty Cart Redirect', 'checkout-wc' ),
			cfw__( 'URL to redirect to when customer empties cart from checkout page.', 'checkout-wc' ) . '<br/>' . cfw__( 'If left blank, customer will be redirected to the cart page.', 'checkout-wc' )
		);
	}

	public function run_on_plugin_activation() {
		SettingsManager::instance()->add_setting( 'enable_cart_editing', 'no' );
		SettingsManager::instance()->add_setting( 'cart_edit_empty_cart_redirect', '' );
	}
}
