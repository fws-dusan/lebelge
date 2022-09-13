<?php


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Preview Mail Class.
 */
class EC_WOO_Preview_Mail
{
    private $id = EC_WOO_BUILDER_PREVIEW_PAGE;

    private static $instance;

    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    public function init()
    {
        add_action('admin_menu', array($this, 'admin_menu'));

        if (isset($_REQUEST["page"]) && $_REQUEST["page"] == $this->id) {
            if (isset($_REQUEST["ecwoo_render_email"])) {
                if ($_REQUEST["ecwoo_render_email"] == 'yes') {
                    add_action('admin_init', array($this, 'render_template_page'));
                }
            }
        }
    }

    public function admin_menu()
    {
        add_submenu_page(
            null,
            __('EC WOO Preview Email', EC_WOO_BUILDER_TEXTDOMAIN),
            __('EC WOO Preview Email', EC_WOO_BUILDER_TEXTDOMAIN),
            'manage_woocommerce',
            $this->id,
            array($this, 'render_admin_page')
        );
    }

    public function render_admin_page()
    {
        require_once(EC_WOO_BUILDER_PATH.'pages/preview-page.php');
    }

    public function render_template_page()
    {
        require_once(EC_WOO_BUILDER_PATH.'/pages/preview-page.php');
    }


    /**
     *
     */
    public function populate_mail_object($order, &$mail)
    {
        global $cxec_cache_email_message;


        // Force the email to seem enabled in-case it has been tuned off programmatically.
        $mail->enabled = 'yes';

        /**
         * Get a User ID for the preview.
         */

        // Get the Customer user from the order, or the current user ID if guest.
        if (0 === ($user_id = (int)get_post_meta(EC_Helper::get_order_number($order), '_customer_user', true))) {
            $user_id = get_current_user_id();
        }
        $user = get_user_by('id', $user_id);

        /**
         * Get a Product ID for the preview.
         */

        // Get a product from the order. If it doesnt exist anymore then get the latest product.
        $items = $order->get_items();
        foreach ($items as $item) {
            $product_id = $item['product_id'];
            if (null !== get_post($product_id)) {
                break;
            }
            //$product_variation_id = $item['variation_id'];
        }

        if (null === get_post($product_id)) {
            $products_array = get_posts(array(
                'posts_per_page' => 1,
                'orderby' => 'date',
                'post_type' => 'product',
                'post_status' => 'publish',
            ));

            if (isset($products_array[0]->ID)) {
                $product_id = $products_array[0]->ID;
            }
        }

        /**
         * Generate the required email for use with Sending or Previewing.
         *
         * All the email types in all the varying plugins require specific
         * properties to be set before they generate the email for our
         * preview, or send a test email.
         */

        $compatabiltiy_warning = false; // Default.

        switch ($mail->id) {

            /**
             * WooCommerce (default transactional mails).
             */

            case 'new_order':
            case 'cancelled_order':
            case 'customer_processing_order':
            case 'customer_completed_order':
            case 'customer_refunded_order':
            case 'customer_partially_refunded_order':
            case 'customer_on_hold_order':
            case 'customer_invoice':
            case 'failed_order':

                $mail->object = $order;
                $mail->find['order-date'] = '{order_date}';
                $mail->find['order-number'] = '{order_number}';
                $mail->replace['order-date'] = date_i18n(wc_date_format(), strtotime(EC_Helper::get_order_get_date_created($mail->object)));
                $mail->replace['order-number'] = $mail->object->get_order_number();
                break;

            case 'customer_new_account':

                $mail->object = $user;
                $mail->user_pass = '{user_pass}';
                $mail->user_login = stripslashes($mail->object->user_login);
                $mail->user_email = stripslashes($mail->object->user_email);
                $mail->recipient = $mail->user_email;
                $mail->password_generated = true;
                break;

            case 'customer_note':

                $mail->object = $order;
                $mail->customer_note = 'Hello';
                $mail->find['order-date'] = '{order_date}';
                $mail->find['order-number'] = '{order_number}';
                $mail->replace['order-date'] = date_i18n(wc_date_format(), strtotime(EC_Helper::get_order_get_date_created($mail->object)));
                $mail->replace['order-number'] = $mail->object->get_order_number();
                break;

            case 'customer_reset_password':

                $mail->object = $user;
                $mail->user_login = $user->user_login;
                $mail->reset_key = '{{reset-key}}';
                break;

            /**
             * WooCommerce Wait-list Plugin (from WooCommerce).
             */

            case 'woocommerce_waitlist_mailout':

                $mail->object = get_product($product_id);
                $mail->find[] = '{product_title}';
                $mail->replace[] = $mail->object->get_title();
                break;

            /**
             * WooCommerce Subscriptions Plugin (from WooCommerce).
             */

            case 'new_renewal_order':
            case 'new_switch_order':
            case 'customer_processing_renewal_order':
            case 'customer_completed_renewal_order':
            case 'customer_completed_switch_order':
            case 'customer_renewal_invoice':

                $mail->object = $order;
                break;

            case 'cancelled_subscription':

                $mail->object = false;
                $compatabiltiy_warning = true;
                break;

            /**
             * Everything else, including all default WC emails.
             */

            default:

                $mail->object = $order;
                $compatabiltiy_warning = true;
                break;
        }

        return $compatabiltiy_warning;
    }
}