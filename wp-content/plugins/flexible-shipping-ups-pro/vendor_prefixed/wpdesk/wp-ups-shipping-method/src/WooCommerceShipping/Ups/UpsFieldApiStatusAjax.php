<?php

/**
 * Ajax status handler.
 *
 * @package WPDesk\WooCommerceShipping\Ups
 */
namespace UpsProVendor\WPDesk\WooCommerceShipping\Ups;

use UpsProVendor\WPDesk\UpsShippingService\UpsApi\ConnectionChecker;
use UpsProVendor\WPDesk\WooCommerceShipping\CustomFields\ApiStatus\FieldApiStatusAjax;
/**
 * Can handle api status ajax request.
 */
class UpsFieldApiStatusAjax extends \UpsProVendor\WPDesk\WooCommerceShipping\CustomFields\ApiStatus\FieldApiStatusAjax
{
    /**
     * Check connection error.
     *
     * @return string|false
     */
    protected function check_connection_error()
    {
        try {
            $this->ping();
            return \false;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    /**
     * Ping api.
     *
     * @throws \Exception
     */
    private function ping()
    {
        $connection_checker = new \UpsProVendor\WPDesk\UpsShippingService\UpsApi\ConnectionChecker($this->get_shipping_service(), $this->get_settings(), $this->get_logger());
        $connection_checker->check_connection();
    }
}
