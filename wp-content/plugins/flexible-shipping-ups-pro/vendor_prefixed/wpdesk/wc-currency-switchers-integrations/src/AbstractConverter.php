<?php

/**
 * Abstract converter.
 *
 * @package WPDesk\WooCommerce\CurrencySwitchers
 */
namespace UpsProVendor\WPDesk\WooCommerce\CurrencySwitchers;

/**
 * Abstract class for converters.
 */
abstract class AbstractConverter implements \UpsProVendor\WPDesk\WooCommerce\CurrencySwitchers\SwitcherConverter
{
    /**
     * @inheritDoc
     */
    abstract function convert($value);
    /**
     * @inheritDoc
     */
    public function convert_array($values)
    {
        foreach ($values as $key => $value) {
            if ($value) {
                $values[$key] = $this->convert($value);
            }
        }
        return $values;
    }
}
