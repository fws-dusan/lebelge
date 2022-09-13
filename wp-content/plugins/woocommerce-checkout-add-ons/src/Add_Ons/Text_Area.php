<?php
/**
 * WooCommerce Checkout Add-Ons
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Checkout Add-Ons to newer
 * versions in the future. If you wish to customize WooCommerce Checkout Add-Ons for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-checkout-add-ons/ for more information.
 *
 * @author      SkyVerge
 * @copyright   Copyright (c) 2014-2021, SkyVerge, Inc. (info@skyverge.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\Checkout_Add_Ons\Add_Ons;

use SkyVerge\WooCommerce\PluginFramework\v5_10_6 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Text Area Add-On Class
 *
 * @since 2.0.0
 */
class Text_Area extends Add_On {


	/** @var string the add-on type */
	protected $add_on_type = 'textarea';


	/**
	 * Sets up the add-on.
	 *
	 * @since 2.0.0
	 *
	 * @param Add_On|array|int Add_On object, data array, or ID
	 * @throws Framework\SV_WC_Plugin_Exception
	 */
	public function __construct( $data = 0 ) {

		parent::__construct( $data );

		$this->add_on_type_name = _x( 'Text Area', 'Add-on type', 'woocommerce-checkout-add-ons' );
	}


	/**
	 * Normalizes the add-on value.
	 *
	 * Provided the value(s), looks up the proper label(s) and returns them
	 * as a comma-separated string or an array.
	 *
	 * @since 1.0
	 *
	 * @param string|array $value sanitized key or array of keys
	 * @param bool $implode whether to glue labels together with commas
	 * @return mixed string|array label or array of labels matching the value
	 */
	public function normalize_value( $value, $implode ) {

		return parent::normalize_value( wpautop( $value ), $implode );
	}


	/**
	 * Gets an array of the supported attributes for this add-on.
	 *
	 * Removes 'filterable' since text areas can't be sorted.
	 *
	 * @since 2.0.0
	 *
	 * @return string[]
	 */
	public static function get_supported_attributes() {

		$supported_attributes = array_flip( parent::get_supported_attributes() );

		unset( $supported_attributes['filterable'] );

		return array_keys( $supported_attributes );
	}


}
