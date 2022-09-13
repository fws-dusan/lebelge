<?php
namespace Wnsm;


use WP_Error;

class Hooks
{
    public function __construct(App $app, Settings $settings)
    {
        $this->settings = $settings;
        $this->app = $app;
    }

    public function woocommerceShippingSettings($fields)
    {
        if (is_array($fields)) {
            $fields = array_merge($fields, $this->settings->getFields());
        }

        return $fields;
    }

    public function woocommerceNoShippingAvailableHtml($msgId) {
        return function($value) use ($msgId) {
            $msg = $this->settings->getMsgHtml($msgId);
            if (isset($msg)) {
                $value = $msg;
            }
            return $value;
        };
    }

    public function woocommerceCheckoutAfterValidation($data, $errors) {

        /** @var WP_Error $errors */

        if (!isset($errors->errors['shipping'])) {
            return;
        }

        $shippingErrors = &$errors->errors['shipping'];
        if (!$shippingErrors) {
            return;
        }

        $builtinError = __('No shipping method has been selected. Please double check your address, or contact us if you need any help.', 'woocommerce');
        $idx = array_search($builtinError, $shippingErrors, true);
        if ($idx === false) {
            return;
        }

        $msg = $this->settings->getMsgHtml(Settings::MSG_CHECKOUT_NOTICE);
        if (!isset($msg)) {
            return;
        }

        $shippingErrors[$idx] = $msg;
    }

    public function pluginActionLinks($links)
    {
        $settingsUrl = admin_url('admin.php?page=wc-settings&tab=shipping&section=options#' . urlencode(Settings::SETTINGS_SECTION_ID.'-description'));
        array_unshift($links, '<a href="'.esc_html($settingsUrl).'">'.$this->app->translate('Edit messages').'</a>');
        return $links;
    }

    public function adminEnqueueScripts($hookSuffix)
    {
        if (!($hookSuffix === 'woocommerce_page_wc-settings' &&
            isset($_GET['tab'], $_GET['section']) &&
            $_GET['tab'] === 'shipping' &&
            $_GET['section'] === 'options')) {
            return;
        }

        wp_enqueue_script('wnsm_admin_js', $this->app->getAssetUrl('admin.js'), [], false, true);
        wp_enqueue_style('wnsm_admin_css', $this->app->getAssetUrl('admin.css'));
    }

    /** @var Settings */
    private $settings;

    /** @var App */
    private $app;
}
