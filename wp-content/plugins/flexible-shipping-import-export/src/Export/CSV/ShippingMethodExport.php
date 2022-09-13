<?php
/**
 * Class ShippingMethodExport
 *
 * @package WPDesk\FS\TableRate\ImportExport\Export\CSV
 */

namespace WPDesk\FS\TableRate\ImportExport\Export\CSV;

use WC_Shipping_Method;
use WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels\ShippingMethodLabelsTrait;
use WPDesk\FS\TableRate\ShippingMethodSingle;

/**
 * Exporter of shipping method.
 */
class ShippingMethodExport extends CSVAbstract {
	use ShippingMethodLabelsTrait;

	/**
	 * @var ShippingMethodSingle .
	 */
	private $shipping_method;

	/**
	 * @var MethodRulesExport .
	 */
	private $method_rules_exporter;

	/**
	 * @var array
	 */
	private $shipping_method_settings;

	/**
	 * ShippingMethodExporter constructor.
	 *
	 * @param ShippingMethodSingle $shipping_method          .
	 * @param MethodRulesExport    $method_rules_exporter    .
	 * @param array                $shipping_method_settings .
	 */
	public function __construct( ShippingMethodSingle $shipping_method, MethodRulesExport $method_rules_exporter, $shipping_method_settings ) {
		$this->shipping_method          = $shipping_method;
		$this->method_rules_exporter    = $method_rules_exporter;
		$this->shipping_method_settings = $shipping_method_settings;
	}

	/**
	 * @param bool $with_header .
	 *
	 * @return array
	 */
	public function get_data( $with_header = true ) {
		$method_rules_data = $this->method_rules_exporter->get_data();
		$header            = array_merge( $this->get_header_fields(), $method_rules_data[0] );

		$data = array();

		if ( $with_header ) {
			$data[] = $header;
		}

		unset( $method_rules_data[0] );

		$row_empty = $this->get_row_default_fields( $header );

		$i = 1;
		foreach ( $method_rules_data as $method_rule_key => $method_rules_value ) {
			$row_data = array_merge( $row_empty, $method_rules_value );
			$row_data = array_merge( $row_data, $this->get_method_data( $i ++ ) );

			$data[] = $row_data;
		}

		return $data;
	}

	/**
	 * @param int $row_number .
	 *
	 * @return array
	 */
	private function get_method_data( $row_number ) {
		$data = array();

		$this->shipping_method->init_instance_form_fields( true );
		$data[ $this->get_label_shipping_method_id() ] = $this->shipping_method->get_instance_id();

		if ( 1 === $row_number ) {
			foreach ( $this->get_shipping_method_settings() as $key => $details ) {
				$data[ $this->get_label_shipping_method_field( $key ) ] = $this->shipping_method->get_instance_option( $key );
			}
		}

		return $data;
	}

	/**
	 * @return array
	 */
	public function get_header_fields() {
		$fields = array();

		$fields[] = $this->get_label_shipping_method_id();

		foreach ( $this->get_shipping_method_settings() as $field_name => $field_details ) {
			$fields[] = $this->get_label_shipping_method_field( $field_name );
		}

		return $fields;
	}

	/**
	 * @return array
	 */
	private function get_shipping_method_settings() {
		return array_filter(
			$this->shipping_method_settings,
			function ( $value ) {
				return ! in_array( $value['type'], array( 'title', 'shipping_rules' ) );
			}
		);
	}
}
