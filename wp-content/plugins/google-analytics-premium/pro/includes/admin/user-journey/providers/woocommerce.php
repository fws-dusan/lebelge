<?php
/**
 * This file contains the code to display metabox for WooCommerce Admin Orders Page.
 *
 * @since 8.5.0
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */
/**
 * Class to add metabox to woocommerce admin order page.
 *
 * @since 8.5.0
 */
class MonsterInsights_Pro_User_Journey_WooCommerce_Metabox {

    /**
     * URL to assets folder.
     *
     * @since 8.5.0
     *
     * @var string
     */
    public $assets_url = MONSTERINSIGHTS_PLUGIN_URL . 'pro/includes/admin/user-journey/assets/';

    /**
     * Class constructor.
     *
     * @since 8.5.0
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_user_journey_metabox' ) );
    }

    /**
     * Add metabox
     *
     * @uses add_meta_boxes WP Hook
     *
     * @since 8.5.0
     *
     * @return void
     */
    public function add_user_journey_metabox() {
        add_meta_box(
            'woocommerce-monsterinsights-pro-user-journey-metabox',
            esc_html__( 'User Journey by MonsterInsights', 'monsterinsights-user-journey' ),
            array( $this, 'display_meta_box' ),
            'shop_order',
            'normal',
            'core'
        );
    }

    /**
     * Display metabox HTML.
     *
     * @since 8.5.0
     *
     * @param object $post WooCommerce Order custom post
     *
     * @return void
     */
    public function display_meta_box( $post ) {
        $this->metabox_html( $post );
    }

    /**
     * Contains HTML to display inside the metabox
     *
     * @since 8.5.0
     *
     * @param array  $user_journey User Journey entries.
     * @param object $post         Woo Order Post
     *
     * @return void
     */
    public function metabox_html( $post ) {
        ?>
            <!-- User Journey metabox -->
            <div class="monsterinsights-pro-uj-backdrop-pic" style="background-image: url( '<?php echo $this->assets_url; ?>img/user-journey-backdrop.png' )"></div>
            <div id="monsterinsights-pro-entry-user-journey" class="postbox">
                <div class="monsterinsights-pro-uj-container desktop">
                    <div class="monsterinsights-pro-uj-modal-content">
                        <div class="monsterinsights-pro-modal-left">
                            <h4><?php esc_html_e( 'Activate User Journey Addon', 'monsterinsights' ); ?></h4>
                            <p><?php esc_html_e( 'Easily see which steps each customer takes before making a purchase on your store.', 'monsterinsights' ); ?></p>
                            <a target="_blank" id="monsterinsights-activate-user-journey" href="#" title="" class="monsterinsights-uj-button">
                                <?php esc_html_e( 'Activate', 'monsterinsights' ); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    }
}
