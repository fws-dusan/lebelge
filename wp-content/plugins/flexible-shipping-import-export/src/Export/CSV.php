<?php
/**
 * Class CSV
 *
 * @package WPDesk\FS\TableRate\ImportExport\Export
 */

namespace WPDesk\FS\TableRate\ImportExport\Export;

use FlexibleShippingImportExportVendor\League\Csv\Writer;
use FSVendor\WPDesk\Forms\Field;
use WPDesk\FS\TableRate\ImportExport\Export\CSV\MethodRulesExport;
use WPDesk\FS\TableRate\ImportExport\Export\CSV\ShippingMethodExport;
use WPDesk\FS\TableRate\ImportExport\Conditions\ExportData;
use WPDesk\FS\TableRate\Rule\Condition\Condition;
use WPDesk\FS\TableRate\ShippingMethodSingle;

/**
 * CSV Exporter
 */
class CSV implements ExportInterface {

	const CSV_DELIMITER = ',';

	/**
	 * @var Condition[] .
	 */
	private $conditions;

	/**
	 * @var ShippingMethodSingle[] .
	 */
	private $shipping_methods;

	/**
	 * @var array .
	 */
	private $shipping_method_settings;

	/**
	 * @var Field[] .
	 */
	private $rule_costs_fields;

	/**
	 * @var Field[] .
	 */
	private $rule_additional_costs_fields;

	/**
	 * @var Field[] .
	 */
	private $special_action_fields;

	/**
	 * @var ExportData[] .
	 */
	private $export_preparing;

	/**
	 * CSV constructor.
	 *
	 * @param ShippingMethodSingle[] $shipping_methods             .
	 * @param Condition[]            $conditions                   .
	 * @param array                  $shipping_method_settings     .
	 * @param Field[]                $rule_costs_fields            .
	 * @param Field[]                $rule_additional_costs_fields .
	 * @param Field[]                $special_action_fields        .
	 * @param ExportData[]           $export_preparing             .
	 */
	public function __construct(
		array $shipping_methods,
		array $conditions,
		array $shipping_method_settings,
		array $rule_costs_fields,
		array $rule_additional_costs_fields,
		array $special_action_fields,
		array $export_preparing
	) {
		$this->shipping_methods             = $shipping_methods;
		$this->conditions                   = $conditions;
		$this->shipping_method_settings     = $shipping_method_settings;
		$this->rule_costs_fields            = $rule_costs_fields;
		$this->rule_additional_costs_fields = $rule_additional_costs_fields;
		$this->special_action_fields        = $special_action_fields;
		$this->export_preparing             = $export_preparing;
	}

	/**
	 * Download File.
	 */
	public function download() {
		$this->prepare_csv_writer()->output( $this->get_filename() );
	}

	/**
	 * @return string
	 */
	public function get_raw() {
		return $this->prepare_csv_writer()->getContent();
	}

	/**
	 * @return Writer
	 */
	private function prepare_csv_writer() {
		$csv = Writer::createFromString();
		$csv->insertAll( $this->get_data() );
		$csv->setDelimiter( self::CSV_DELIMITER );
		$csv->setOutputBOM( Writer::BOM_UTF8 );
		$csv->addStreamFilter( 'convert.iconv.ISO-8859-15/UTF-8' );

		return $csv;
	}

	/**
	 * @return int
	 */
	public function get_elements_count() {
		return count( $this->shipping_methods );
	}

	/**
	 * @return string
	 */
	public function get_mime_type() {
		return 'text/csv';
	}

	/**
	 * @return array
	 */
	private function get_data() {
		$data = array();

		$i = 1;
		foreach ( $this->shipping_methods as $shipping_method ) {
			$method_rules_exporter = new MethodRulesExport(
				$shipping_method->get_method_rules(),
				$this->conditions,
				$this->rule_costs_fields,
				$this->rule_additional_costs_fields,
				$this->special_action_fields,
				$this->export_preparing
			);

			$exporter_shipping_method = new ShippingMethodExport( $shipping_method, $method_rules_exporter, $this->shipping_method_settings );

			$data = array_merge( $data, $exporter_shipping_method->get_data( 1 === $i ++ ) );
		}

		return $data;
	}

	/**
	 * @return string
	 */
	public function get_filename() {
		return 'flexible-shipping.csv';
	}
}
