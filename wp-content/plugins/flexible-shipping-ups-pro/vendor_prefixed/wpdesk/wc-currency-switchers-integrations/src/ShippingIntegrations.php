<?php

/**
 * Shipping integrations.
 *
 * @package WPDesk\WooCommerce\CurrencySwitchers
 */
namespace UpsProVendor\WPDesk\WooCommerce\CurrencySwitchers;

use UpsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use UpsProVendor\WPDesk\WooCommerce\CurrencySwitchers\Switcher\WooCommerceMultiCurrency\ShippingMethodIntegration;
/**
 * Can create integrations for shipping methods.
 * Creates integrations for plugins which do not works by default, ie. WooCommerce MultiCurrency.
 * @see https://woocommerce.com/products/multi-currency/
 */
class ShippingIntegrations implements \UpsProVendor\WPDesk\PluginBuilder\Plugin\Hookable
{
    /**
     * @var string
     */
    private $shipping_method_id;
    /**
     * @param string $shipping_method_id .
     */
    public function __construct($shipping_method_id)
    {
        $this->shipping_method_id = $shipping_method_id;
    }
    /**
     * Hooks.
     */
    public function hooks()
    {
        \add_action('woocommerce_loaded', array($this, 'add_integrations_for_shipping_method'));
    }
    /**
     * Add integration to WooCommerce MultiCurrency for shipping method.
     */
    public function add_integrations_for_shipping_method()
    {
        $woocommerce_multicurrency_integration = new \UpsProVendor\WPDesk\WooCommerce\CurrencySwitchers\Switcher\WooCommerceMultiCurrency\ShippingMethodIntegration($this->shipping_method_id);
        $woocommerce_multicurrency_integration->hooks();
    }
}
