<?php
/**
 * Class TrackerNotices
 *
 * @package WPDesk\FS\ConditionalMethods\Tracker
 */

namespace WPDesk\FS\ConditionalMethods\Tracker;

use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Sets WPDesk tracker notices.
 *
 * @package WPDesk\FS\ConditionalMethods\Tracker
 */
class TrackerNotices implements Hookable {

	const USAGE_DATA_URL = 'https://flexibleshipping.com/usage-tracking/';

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_filter( 'wpdesk_tracker_notice_screens', array( $this, 'screens_where_notice_show' ) );
		add_filter( 'wpdesk_tracker_notice_content', array( $this, 'tracker_notice' ), 10, 3 );
	}

	/**
	 * On these screens tracker notice will be shown.
	 *
	 * @param string[] $screens .
	 *
	 * @return string[]
	 */
	public function screens_where_notice_show( $screens ) {
		$current_screen = get_current_screen();
		if ( $current_screen && isset( $_GET['section'] ) && 'flexible_shipping_conditional_methods' === $_GET['section'] ) {
			$screens[] = $current_screen->id;
		}

		return $screens;
	}

	/**
	 * Tracker notice content.
	 *
	 * @param string $notice .
	 * @param string $username .
	 * @param string $terms_url .
	 *
	 * @return string
	 */
	public function tracker_notice( $notice, $username, $terms_url ) {
		ob_start();
		?>
		<?php
			// Translators: username.
			echo esc_html( sprintf( __( 'Hey %s,', 'flexible-shipping-conditional-methods' ), $username ) );
		?>
		<br/>
		<?php echo wp_kses_post( __( 'We need your help to improve <strong>Flexible Shipping Conditional Methods</strong>, so it\'s more useful for you and the rest of our <strong>100,000+ users</strong>. By collecting data on how you use our plugins, you will help us a lot. We will not collect any sensitive data, so you can feel safe.', 'flexible-shipping-conditional-methods' ) ); ?>
		<a href="<?php echo esc_url( self::USAGE_DATA_URL ); ?>" target="_blank"><?php echo wp_kses_post( __( 'Find out more &raquo;', 'flexible-shipping-conditional-methods' ) ); ?></a><br/>
		<?php
		echo wp_kses_post( __( 'Thank you! ~ Piotr @ Flexible Shipping Team', 'flexible-shipping-conditional-methods' ) );
		$out = ob_get_clean();
		return $out ? $out : '';
	}
}
