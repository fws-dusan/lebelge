<?php

namespace UpsProVendor\WPDesk\WooCommerceShippingPro\CustomFields;

use UpsProVendor\WPDesk\Packer\BoxFactory\BoxesWithUnit;
use UpsProVendor\WpDesk\WooCommerce\ShippingMethod\Assets;
use UpsProVendor\WpDesk\WooCommerce\ShippingMethod\Labels;
use UpsProVendor\WPDesk\WooCommerceShipping\CustomFields\CustomField;
use UpsProVendor\WpDesk\WooCommerce\ShippingMethod\SettingsField;
use UpsProVendor\WPDesk\WooCommerceShippingPro\Packer\PackerSettings;
/**
 * Custom field
 *
 * @package WPDesk\WooCommerceShippingPro\CustomFields
 */
class ShippingBoxes implements \UpsProVendor\WPDesk\WooCommerceShipping\CustomFields\CustomField
{
    // TODO: not sure what is that field.
    const OPTION_PACKAGING_BOXES = 'packaging_boxes';
    /** @var \WC_Shipping_Method */
    private $method;
    /** @var BoxesWithUnit */
    private $boxes;
    public function __construct(\WC_Shipping_Method $method, \UpsProVendor\WPDesk\Packer\BoxFactory\BoxesWithUnit $boxes)
    {
        $this->method = $method;
        $this->boxes = $boxes;
    }
    /**
     * Unique field name.
     *
     * @return string .
     */
    public static function get_type_name()
    {
        return 'shipping_boxes';
    }
    /**
     * Must be applied in admin_enqueue_scripts so the js would work.
     *
     * @param string $plugin_assets_url
     */
    public static function enqueue_scripts($plugin_assets_url)
    {
        $current_screen = \get_current_screen();
        if ($current_screen instanceof \WP_Screen && 'woocommerce_page_wc-settings' === $current_screen->id) {
            $suffix = \defined('SCRIPT_DEBUG') && \SCRIPT_DEBUG ? '' : '.min';
            $shipping_boxes_assets = new \UpsProVendor\WpDesk\WooCommerce\ShippingMethod\Assets();
            $shipping_boxes_assets->enqueue($plugin_assets_url . '/../../vendor_prefixed/wpdesk/wp-settings-field-boxes', $suffix, 6);
        }
    }
    /**
     * Can sanitize data so it can be saved into DB.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function sanitize(array $data = null)
    {
        $shipping_boxes_field = new \UpsProVendor\WpDesk\WooCommerce\ShippingMethod\SettingsField('whatever');
        return $shipping_boxes_field->get_field_posted_value_as_json($data);
    }
    /**
     * Render view.
     *
     * @param array|null $params Params.
     *
     * @return mixed
     */
    public function render(array $params = null)
    {
        if ($this->boxes->is_metric()) {
            $weight_unit = '[kg]';
            $dimensions_unit = '[cm]';
        } else {
            $weight_unit = '[lbs]';
            $dimensions_unit = '[in]';
        }
        $labels = new \UpsProVendor\WpDesk\WooCommerce\ShippingMethod\Labels();
        $labels->set_labels(
            \__('Type', 'flexible-shipping-ups-pro'),
            // Translators: units.
            \sprintf(\__('Length %1$s', 'flexible-shipping-ups-pro'), $dimensions_unit),
            // Translators: units.
            \sprintf(\__('Width %1$s', 'flexible-shipping-ups-pro'), $dimensions_unit),
            // Translators: units.
            \sprintf(\__('Height %1$s', 'flexible-shipping-ups-pro'), $dimensions_unit),
            // Translators: units.
            \sprintf(\__('Max Weight %1$s', 'flexible-shipping-ups-pro'), $weight_unit),
            // Translators: units.
            \sprintf(\__('Padding %1$s', 'flexible-shipping-ups-pro'), $dimensions_unit),
            // Translators: units.
            \sprintf(\__('Box Weight %1$s', 'flexible-shipping-ups-pro'), $weight_unit),
            \__('Delete', 'flexible-shipping-ups-pro'),
            \__('Add', 'flexible-shipping-ups-pro')
        );
        $shipping_boxes_field = new \UpsProVendor\WpDesk\WooCommerce\ShippingMethod\SettingsField($params['field_key']);
        \ob_start();
        $shipping_boxes_field->render($params['title'], $this->method->get_tooltip_html($params), $params['value'], $this->boxes->get_boxes(), $labels, !empty($params['description']) ? $params['description'] : '');
        return \ob_get_clean();
    }
    public function render_footer($key)
    {
        \ob_start();
        $prefix = "woocommerce_{$this->method->id}_";
        $packaging_method_field = $prefix . \UpsProVendor\WPDesk\WooCommerceShippingPro\Packer\PackerSettings::OPTION_PACKAGING_METHOD;
        $packaging_boxes_field = $prefix . self::OPTION_PACKAGING_BOXES;
        $shipping_boxes_field = $prefix . \UpsProVendor\WPDesk\WooCommerceShippingPro\Packer\PackerSettings::OPTION_SHIPPING_BOXES;
        $packaging_method_box = \UpsProVendor\WPDesk\WooCommerceShippingPro\Packer\PackerSettings::PACKING_METHOD_BOX;
        include __DIR__ . '/views/settings-script.php';
        return \ob_get_clean();
    }
}
