<?php
/**
 * Initialize Admin - User Journey.
 *
 * @since 8.5.0
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Admin functions and init functionality.
 *
 * @since 8.5.0
 */
final class MonsterInsights_Pro_User_Journey_Admin {

    /**
     * Screens on which we want to load the assets.
     *
     * @since 8.5.0
     *
     * @var array
     */
    public $screens = array( 'shop_order' );

    /**
	 * Holds singleton instance
	 *
	 * @since 8.5.0
     *
	 * @var MonsterInsights_User_Journey_Admin
	 */
	private static $instance;

	/**
	 * Return Singleton instance
	 *
	 * @since 8.5.0
     *
	 * @return MonsterInsights_User_Journey_Admin
	 */
	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

    /**
     * Class constructor.
     *
     * @since 8.5.0
     */
    public function __construct() {
        add_action( 'admin_head', array( $this, 'add_admin_scripts' ) );
    }

    /**
     * Add required admin scripts.
     *
     * @since 8.5.0
     *
     * @return void
     */
    public function add_admin_scripts() {
        $current_screen = get_current_screen();

		if ( is_object( $current_screen ) ) {
			if ( in_array( $current_screen->id, $this->screens, true ) ) {
				wp_enqueue_style( 'monsterinsights-pro-user-journey-admin', MONSTERINSIGHTS_PLUGIN_URL . 'pro/includes/admin/user-journey/assets/css/user-journey.css', MONSTERINSIGHTS_VERSION );
				wp_enqueue_script( 'monsterinsights-pro-user-journey-admin-js', MONSTERINSIGHTS_PLUGIN_URL . 'pro/includes/admin/user-journey/assets/js/user-journey.js', MONSTERINSIGHTS_VERSION );

                wp_localize_script( 'monsterinsights-pro-user-journey-admin-js', 'monsterinsights_user_journey',
                    array(
                        'ajax_url'             => admin_url( 'admin-ajax.php' ),
                        'activate_addon_nonce' => wp_create_nonce( 'monsterinsights-activate' ),
                        'is_network'           => is_multisite() ? true : false,
                        'redirect_url'         => wc_get_current_admin_url()
                    )
                );
			}
		}
    }
}
// Initialize the class
MonsterInsights_Pro_User_Journey_Admin::get_instance();
