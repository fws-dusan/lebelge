<?php

/**
 * Handling fees.
 *
 * @package WPDesk\WooCommerceShipping\CustomFields
 */
namespace UpsProVendor\WPDesk\WooCommerceShipping\CustomFields;

use UpsProVendor\WPDesk\WooCommerceShipping\HandlingFees\PriceAdjustmentFixed;
use UpsProVendor\WPDesk\WooCommerceShipping\HandlingFees\PriceAdjustmentNone;
use UpsProVendor\WPDesk\WooCommerceShipping\HandlingFees\PriceAdjustmentPercentage;
/**
 * Can handle handling fees.
 *
 * @TODO: this is not a field. Need to be moved or refactored to placeholder injection.
 */
class FieldHandlingFees
{
    const FIELD_TYPE = 'handling_fees';
    const OPTION_PRICE_ADJUSTMENT_TYPE = 'price_adjustment_type';
    const OPTION_PRICE_ADJUSTMENT_VALUE = 'price_adjustment_value';
    /**
     * Add to settings.
     *
     * @param array $settings_fields
     * @param array $field
     *
     * @return array
     */
    public function add_to_settings(array $settings_fields, array $field)
    {
        $settings_fields[self::OPTION_PRICE_ADJUSTMENT_TYPE] = array('title' => \__('Handling Fees', 'flexible-shipping-ups-pro'), 'type' => 'select', 'options' => array(\UpsProVendor\WPDesk\WooCommerceShipping\HandlingFees\PriceAdjustmentNone::ADJUSTMENT_TYPE => \__('None', 'flexible-shipping-ups-pro'), \UpsProVendor\WPDesk\WooCommerceShipping\HandlingFees\PriceAdjustmentFixed::ADJUSTMENT_TYPE => \__('Fixed value', 'flexible-shipping-ups-pro'), \UpsProVendor\WPDesk\WooCommerceShipping\HandlingFees\PriceAdjustmentPercentage::ADJUSTMENT_TYPE => \__('Percentage', 'flexible-shipping-ups-pro')), 'description' => \__('If you need to add a handling fee to the rates select one of the handling fees types. This can be a percentage or a fixed value.', 'flexible-shipping-ups-pro'), 'desc_tip' => \true, 'default' => \UpsProVendor\WPDesk\WooCommerceShipping\HandlingFees\PriceAdjustmentNone::ADJUSTMENT_TYPE, 'class' => isset($field['class']) ? $field['class'] : '');
        $settings_fields[self::OPTION_PRICE_ADJUSTMENT_VALUE] = array('title' => \__('Fee value', 'flexible-shipping-ups-pro'), 'type' => 'decimal', 'description' => \__('Positive Number=Surcharge, Negative Number=Discount. If you use the currency switcher the Handling fee will be added and converted to the currently active currency at your shop. The rates will also include the taxes based on your current Tax options.', 'flexible-shipping-ups-pro'), 'desc_tip' => \true, 'default' => '', 'class' => isset($field['class']) ? $field['class'] : '');
        return $settings_fields;
    }
}
