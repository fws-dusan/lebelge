<?php

namespace Objectiv\Plugins\Checkout\Compatibility;
use Objectiv\Plugins\Checkout\SingletonAbstract;

abstract class CompatibilityAbstract extends SingletonAbstract {
	/**
	 * Base constructor.
	 */
	final public function init() {
		$this->compat_init();
	}

	/**
	 * Run after init (normative use case)
	 */
	final public function compat_init() {
		if ( $this->is_available() && ( ! is_admin() || wp_doing_ajax() ) ) {
			// Allow scripts and styles for certain plugins
			add_filter( 'cfw_blocked_style_handles', array( $this, 'remove_theme_styles' ), 10, 1 );
			add_filter( 'cfw_blocked_style_handles', array( $this, 'remove_styles' ), 10, 1 );
			add_filter( 'cfw_blocked_script_handles', array( $this, 'remove_theme_scripts' ), 10, 1 );
			add_filter( 'cfw_blocked_script_handles', array( $this, 'remove_scripts' ), 10, 1 );
			add_filter( 'cfw_typescript_compatibility_classes_and_params', array( $this, 'typescript_class_and_params' ), 10, 1 );

			// Run if on checkout
			$this->run_immediately();
			add_action( 'wp', array( $this, 'queue_checkout_and_order_pay_page_actions' ), 0 );
			add_action( 'wp', array( $this, 'queue_order_received_actions' ), 0 );
			add_action( 'cfw_checkout_update_order_review', array( $this, 'run' ) );
			add_action( 'wp_loaded', array( $this, 'run_on_wp_loaded' ), 0 );
		}
	}

	/**
	 * Allow some things to be run before init
	 *
	 * SHOULD BE AVOIDED
	 */
	public function pre_init() {
		// Silence is golden
	}

	final public function queue_checkout_and_order_pay_page_actions() {
		if ( cfw_is_checkout() || is_checkout_pay_page() ) {
			$this->run();
		}
	}

	/***
	 * Kick-off everything here
	 */
	public function run() {
		// Silence be golden
	}

	final public function queue_order_received_actions() {
		if ( is_order_received_page() ) {
			$this->run_on_thankyou();
		}
	}

	/**
	 * Only run on order-received page
	 */
	public function run_on_thankyou() {
		// Silence is golden
	}

	/***
	 * Kick-off everything here immediately
	 */
	public function run_immediately() {
		// Silence be golden
	}

	/**
	 * Kick-off everything here on wp_loaded hook
	 */
	public function run_on_wp_loaded() {
		// Silence is golden
	}

	/**
	 * Is dependency for this compatibility class available?
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return false;
	}

	/**
	 * @param array $compatibility
	 *
	 * @return array
	 */
	public function typescript_class_and_params( array $compatibility ): array {
		return $compatibility;
	}

	/**
	 * @param $styles array Array of handles to remove from styles queue.
	 *
	 * @return mixed
	 */
	public function remove_styles( array $styles ) {
		return $styles;
	}

	/**
	 * Add WP theme styles to list of blocked style handles.
	 *
	 * @param $styles
	 *
	 * @return mixed
	 */
	public function remove_theme_styles( $styles ) {
		global $wp_styles;

		$theme_directory_uri = get_theme_root_uri();
		$theme_directory_uri = str_replace( array( 'http:', 'https:' ), '', $theme_directory_uri ); // handle both http/https/and relative protocol URLs

		foreach ( $wp_styles->registered as $wp_style ) {
			if ( ! empty( $wp_style->src ) && stripos( $wp_style->src, $theme_directory_uri ) !== false && stripos( $wp_style->src, '/checkout-wc/' ) === false ) {
				$styles[] = $wp_style->handle;
			}
		}

		return $styles;
	}

	/**
	 * @param $scripts array Array of handles to remove from scripts queue.
	 *
	 * @return mixed
	 */
	public function remove_scripts( array $scripts ) {
		return $scripts;
	}

	/**
	 * Add WP theme styles to list of blocked style handles.
	 *
	 * @param $scripts
	 *
	 * @return mixed
	 */
	public function remove_theme_scripts( $scripts ) {
		global $wp_scripts;

		$theme_directory_uri = get_theme_root_uri();
		$theme_directory_uri = str_replace( array( 'http:', 'https:' ), '', $theme_directory_uri ); // handle both http/https/and relative protocol URLs

		foreach ( $wp_scripts->registered as $wp_script ) {
			if ( ! empty( $wp_script->src ) && stripos( $wp_script->src, $theme_directory_uri ) !== false && stripos( $wp_script->src, '/checkout-wc/' ) === false ) {
				$scripts[] = $wp_script->handle;
			}
		}

		return $scripts;
	}

	/**
	 * For gateways that add buttons above checkout form
	 *
	 * @param string $class
	 * @param string $id
	 * @param string $style
	 */
	public function add_separator( $class = '', $id = '', $style = '' ) {
		if ( ! defined( 'CFW_PAYMENT_BUTTON_SEPARATOR' ) ) {
			define( 'CFW_PAYMENT_BUTTON_SEPARATOR', true );
		} else {
			return;
		}
		?>
		<div id="payment-info-separator-wrap" class="<?php echo $class; ?>">
			<p <?php echo ( $id ) ? "id='{$id}'" : ''; ?> <?php echo ( $style ) ? "style='{$style}'" : ''; ?> class="pay-button-separator">
				<span>
					<?php
					/**
					 * Filters payment request button separator text
					 *
					 * @since 2.0.0
					 *
					 * @param string $separator_label The separator label (default: Or)
					 */
					echo esc_html( apply_filters( 'cfw_express_pay_separator_text', __( 'Or', 'checkout-wc' ) ) );
					?>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * @param string $hook
	 * @param string $function_name
	 * @param int $priority
	 * @return false|mixed
	 */
	public function get_hook_instance_object( string $hook, string $function_name, int $priority = 10 ) {
		global $wp_filter;

		$existing_hooks = $wp_filter[ $hook ];

		if ( $existing_hooks[ $priority ] ) {
			foreach ( $existing_hooks[ $priority ] as $key => $callback ) {
				if ( false !== stripos( $key, $function_name ) ) {
					return $callback['function'][0];
				}
			}
		}

		return false;
	}
}
