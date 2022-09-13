<?php
/**
 * Class ShippingMethodData
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport;

use WPDesk\FS\TableRate\ImportExport\Exception\InvalidImportDataException;
use WPDesk\FS\TableRate\ShippingMethod\CommonMethodSettings;

/**
 * Stores parsed shipping method data.
 */
class ShippingMethodData {

	const METHOD_ID = 'method_id';
	const METHOD_TITLE = 'method_title';

	/**
	 * @var array
	 */
	private $settings = array();

	/**
	 * @var bool
	 */
	private $can_be_imported = true;

	/**
	 * @var array
	 */
	private $unknown_conditions = array();

	/**
	 * @var array
	 */
	private $data_validation = array();

	/**
	 * @var string
	 */
	private $formatted_info;

	/**
	 * @var array
	 */
	private $invalid_rows = array();

	/**
	 * ShippingMethodData constructor.
	 *
	 * @param int $method_id .
	 */
	public function __construct( $method_id ) {
		$this->update_setting( self::METHOD_ID, $method_id );
	}

	/**
	 * @return int
	 */
	public function get_method_id() {
		$method_id = $this->get_setting( self::METHOD_ID );

		return is_array( $method_id ) ? 0 : (int) $method_id;
	}

	/**
	 * @return string
	 */
	public function get_method_title() {
		$method_title = $this->get_setting( self::METHOD_TITLE );

		return is_string( $method_title ) ? $method_title : '';
	}

	/**
	 * @param string           $field .
	 * @param string|array|int $value .
	 */
	public function update_setting( $field, $value ) {
		$this->settings[ $field ] = $value;
	}

	/**
	 * @return array
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * @param string                $field   .
	 * @param int|string|array|null $default .
	 *
	 * @return int|string|array|null
	 */
	public function get_setting( $field, $default = null ) {
		return isset( $this->settings[ $field ] ) ? $this->settings[ $field ] : $default;
	}

	/**
	 * @return array
	 */
	public function get_method_rules() {
		$method_rules = $this->get_setting( CommonMethodSettings::METHOD_RULES, array() );

		return is_array( $method_rules ) ? $method_rules : array();
	}

	/**
	 * @return bool
	 */
	public function is_can_be_imported() {
		return $this->can_be_imported;
	}

	/**
	 * @return array
	 */
	public function get_unknown_conditions() {
		return $this->unknown_conditions;
	}

	/**
	 * @param int    $row_number        .
	 * @param string $unknown_condition .
	 */
	public function add_unknown_condition( $row_number, $unknown_condition ) {
		$this->unknown_conditions[ $unknown_condition ] = isset( $this->unknown_conditions[ $unknown_condition ] ) ? $this->unknown_conditions[ $unknown_condition ] ++ : 1;
		$this->invalid_rows[]                           = $row_number;
		$this->can_be_imported                          = false;
	}

	/**
	 * @param int    $row_number .
	 * @param string $message .
	 */
	public function add_data_validation_error( $row_number, $message ) {
		$this->data_validation[ $row_number ]   = isset( $this->data_validation[ $row_number ] ) ? $this->data_validation[ $row_number ] : array();
		$this->data_validation[ $row_number ][] = $message;
		$this->can_be_imported                  = false;
	}

	/**
	 * @return string
	 */
	public function prepare_cannot_be_imported_message() {
		$message = '';
		if ( count( $this->unknown_conditions ) ) {
			$message .= $this->prepare_cannot_be_imported_message_for_unknown_conditions();
		}

		if ( count( $this->data_validation ) ) {
			$message = sprintf( '%1$s%2$s', $message . ( $message ? '<br/><br/>' : '' ), $this->prepare_data_validation_messages() );
		}

		return $message;
	}

	/**
	 * @return string
	 */
	private function prepare_data_validation_messages() {
		$data_validation_message = '';
		foreach ( $this->data_validation as $row_number => $messages ) {
			$row_message = '';
			foreach ( $messages as $message ) {
				$row_message .= sprintf( '%1$s%2$s', $message, '<br/>' );
			}
			// Translators: row number.
			$data_validation_message .= sprintf( __( '%1$sRow %2$s: %3$s', 'flexible-shipping-import-export' ), '<br/>', $row_number, $row_message );
		}

		return sprintf(
			// Translators: unknown conditions rows.
			__( 'The shipping methods contains invalid data: %1$s', 'flexible-shipping-import-export' ),
			$data_validation_message
		);
	}

	/**
	 * @return string
	 */
	private function prepare_cannot_be_imported_message_for_unknown_conditions() {
		$pl                              = get_locale() === 'pl_PL';
		$flexible_shipping_pro_url       = $pl ? 'https://www.wpdesk.pl/sklep/flexible-shipping-pro-woocommerce/?utm_source=import-fs&utm_medium=link&utm_campaign=cross-fsie' : 'https://flexibleshipping.com/products/flexible-shipping-pro-woocommerce/?utm_source=import-fs&utm_medium=link&utm_campaign=cross-fsie';
		$flexible_shipping_locations_url = $pl ? ' https://www.wpdesk.pl/sklep/flexible-shipping-lokalizacje-woocommerce/?utm_source=import-loc&utm_medium=link&utm_campaign=cross-fsie' : 'https://flexibleshipping.com/products/flexible-shipping-locations-woocommerce/?utm_source=import-loc&utm_medium=link&utm_campaign=cross-fsie';

		return sprintf(
		// Translators: unknown conditions rows.
			__( 'The shipping methods from the selected CSV file contain the rules configured with %1$sFlexible Shipping PRO%2$s, %3$sFlexible Shipping Locations%4$s, in a different way or the CSV file is invalid. In order to import it, please install and/or activate the plugins you used to configure it originally. If the uploaded CSV file was modified manually, please ensure it doesn\'t contain any typos or syntax errors (%5$sline: %6$s%7$s) and try again.', 'flexible-shipping-import-export' ),
			'<a href="' . $flexible_shipping_pro_url . '" target="_blank">',
			'</a>',
			'<a href="' . $flexible_shipping_locations_url . '" target="_blank">',
			'</a>',
			'<strong>',
			implode( ', ', $this->invalid_rows ),
			'</strong>'
		);
	}

	/**
	 * @return string
	 */
	public function get_formatted_info() {
		return $this->formatted_info;
	}

	/**
	 * @param string $formatted_info .
	 */
	public function set_formatted_info( $formatted_info ) {
		$this->formatted_info = $formatted_info;
	}
}
