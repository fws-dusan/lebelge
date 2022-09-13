<?php
/**
 * Class CSVDataParser
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport;

use FlexibleShippingImportExportVendor\League\Csv\Reader;
use FSVendor\WPDesk\Forms\Field;
use WPDesk\FS\TableRate\ImportExport\Conditions\ImportData;
use WPDesk\FS\TableRate\ImportExport\Exception\InvalidImportDataException;
use WPDesk\FS\TableRate\ImportExport\Exception\InvalidImportFormatException;
use WPDesk\FS\TableRate\ImportExport\Exception\MissingImportFieldException;
use WPDesk\FS\TableRate\ImportExport\Exception\UnknownConditionException;
use WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels\AdditionalCostsLabelsTrait;
use WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels\ConditionLabelsTrait;
use WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels\RuleLabelsTrait;
use WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels\ShippingMethodLabelsTrait;
use WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels\SpecialActionsTrait;
use WPDesk\FS\TableRate\Rule\Condition\Condition;
use WPDesk\FS\TableRate\Rule\Cost\RuleCostFieldsFactory;
use WPDesk\FS\TableRate\Rule\Rule;
use WPDesk\FS\TableRate\RulesSettingsField;
use WPDesk\FS\TableRate\ShippingMethod\CommonMethodSettings;
use WPDesk\FS\TableRate\ShippingMethod\MethodSettings;

/**
 * Can parse CSV data.
 */
class CSVDataParser implements DataParser {

	use ShippingMethodLabelsTrait;
	use RuleLabelsTrait;
	use SpecialActionsTrait;
	use AdditionalCostsLabelsTrait;
	use ConditionLabelsTrait;

	/**
	 * @var string
	 */
	private $raw_data;

	/**
	 * @var ImportData[]
	 */
	private $import_data;

	/**
	 * @var MethodSettings
	 */
	private $method_settings;

	/**
	 * @var Condition[]
	 */
	private $available_conditions;

	/**
	 * @var Field[]
	 */
	private $rule_costs_fields;

	/**
	 * @var Field[]
	 */
	private $rule_additional_costs_fields;

	/**
	 * @var Field[]
	 */
	private $special_action_fields;

	/**
	 * CSVDataParser constructor.
	 *
	 * @param string         $raw_data                     .
	 * @param ImportData[]   $import_data                  .
	 * @param MethodSettings $method_settings              .
	 * @param Condition[]    $available_conditions         .
	 * @param Field[]        $rule_costs_fields            .
	 * @param Field[]        $rule_additional_costs_fields .
	 * @param Field[]        $special_action_fields        .
	 */
	public function __construct( $raw_data, array $import_data, MethodSettings $method_settings, array $available_conditions, array $rule_costs_fields, array $rule_additional_costs_fields, array $special_action_fields ) {
		$this->raw_data                     = $raw_data;
		$this->import_data                  = $import_data;
		$this->method_settings              = $method_settings;
		$this->available_conditions         = $available_conditions;
		$this->rule_costs_fields            = $rule_costs_fields;
		$this->rule_additional_costs_fields = $rule_additional_costs_fields;
		$this->special_action_fields        = $special_action_fields;
	}

	/**
	 * .
	 *
	 * @throws InvalidImportFormatException .
	 */
	public function parse() {
		$csv = Reader::createFromString( $this->raw_data );
		$csv->setDelimiter( $this->detectDelimiter( $this->raw_data ) );
		$csv->setHeaderOffset( 0 );

		$shipping_methods_data = array();

		foreach ( $csv->getIterator() as $row_id => $single_row ) {
			$row_number = $row_id + 1;

			try {
				$shipping_method_data = $this->prepare_single_shipping_method_data( $shipping_methods_data, $single_row );
				$shipping_method_data = $this->process_row_data( $row_number, $single_row, $shipping_method_data );
			} catch ( \Throwable $e ) {
				// Translators: error message.
				throw new InvalidImportFormatException( sprintf( __( 'Row %1$s: %2$s', 'flexible-shipping-import-export' ), $row_number, $e->getMessage() ) );
			}

			$method_rules = $shipping_method_data->get_method_rules();
			$shipping_method_data->set_formatted_info(
				sprintf(
				// Translators: method id, rules count.
					_n( 'Method ID: %1$s, contains %2$s rule.', 'Method ID: %1$s, contains %2$s rules.', count( $method_rules ), 'flexible-shipping-import-export' ),
					$shipping_method_data->get_method_id(),
					count( $method_rules )
				)
			);
			$shipping_methods_data[ $shipping_method_data->get_method_id() ] = $shipping_method_data;
		}

		return $this->normalize_shipping_methods_data( $shipping_methods_data );
	}

	/**
	 * @param string $raw_data .
	 *
	 * @return string
	 */
	private function detectDelimiter( $raw_data ) {
		return false !== strpos( $raw_data, 'Shipping Method ID;' ) ? ';' : ',';
	}

	/**
	 * @param ShippingMethodData[] $shipping_methods_data .
	 *
	 * @return array
	 */
	private function normalize_shipping_methods_data( array $shipping_methods_data ) {
		foreach ( $shipping_methods_data as $shipping_method_data ) {
			$method_rules = $shipping_method_data->get_method_rules();
			foreach ( $method_rules as $rule_id => $rule_settings ) {
				$rule_settings            = $this->normalize_rules_data( $rule_settings, Rule::ADDITIONAL_COSTS );
				$rule_settings            = $this->normalize_rules_data( $rule_settings, Rule::CONDITIONS );
				$method_rules[ $rule_id ] = $rule_settings;
			}

			ksort( $method_rules );

			$shipping_method_data->update_setting( CommonMethodSettings::METHOD_RULES, array_values( $method_rules ) );
		}

		return $shipping_methods_data;
	}

	/**
	 * @param array  $method_rules .
	 * @param string $field        .
	 *
	 * @return array
	 */
	private function normalize_rules_data( array $method_rules, $field ) {
		$method_rules[ $field ] = array_values( $method_rules[ $field ] );

		return $method_rules;
	}

	/**
	 * .
	 *
	 * @param ShippingMethodData[] $shipping_methods_data .
	 * @param array                $single_row            .
	 *
	 * @return ShippingMethodData
	 * @throws InvalidImportFormatException .
	 */
	private function prepare_single_shipping_method_data( array $shipping_methods_data, array $single_row ) {
		if ( ! isset( $single_row[ $this->get_label_shipping_method_id() ] ) || ! is_numeric( $single_row[ $this->get_label_shipping_method_id() ] ) ) {
			// Translators: method id.
			throw new InvalidImportFormatException( sprintf( __( 'missing %1$s', 'flexible-shipping-import-export' ), $this->get_label_shipping_method_id() ) );
		} else {
			$method_id = (int) $single_row[ $this->get_label_shipping_method_id() ];

			return isset( $shipping_methods_data[ $method_id ] ) ? $shipping_methods_data[ $method_id ] : new ShippingMethodData( $method_id );
		}
	}

	/**
	 * .
	 *
	 * @param int                $row_number           .
	 * @param array              $single_row           .
	 * @param ShippingMethodData $shipping_method_data .
	 *
	 * @return ShippingMethodData
	 */
	private function process_row_data( $row_number, array $single_row, ShippingMethodData $shipping_method_data ) {
		$shipping_method_data = $this->process_method_settings( $single_row, $shipping_method_data );

		try {
			$method_rules = $this->process_rules_settings(
				$single_row,
				$shipping_method_data->get_method_rules()
			);
			$shipping_method_data->update_setting( CommonMethodSettings::METHOD_RULES, $method_rules );
		} catch ( UnknownConditionException $e ) {
			$shipping_method_data->add_unknown_condition( $row_number, $e->get_condition_id() );
		} catch ( InvalidImportDataException $e ) {
			$shipping_method_data->add_data_validation_error( $row_number, $e->getMessage() );
		}

		return $shipping_method_data;
	}

	/**
	 * @param array              $single_row           .
	 * @param ShippingMethodData $shipping_method_data .
	 *
	 * @return ShippingMethodData
	 */
	private function process_method_settings( array $single_row, ShippingMethodData $shipping_method_data ) {
		foreach ( $this->method_settings->get_settings_fields( array(), true ) as $field_name => $field_settings ) {
			if ( RulesSettingsField::FIELD_TYPE === $field_settings['type'] ) {
				continue;
			}
			$csv_row_field_name = $this->get_label_shipping_method_field( $field_name );
			if ( empty( $single_row[ $csv_row_field_name ] ) ) {
				continue;
			}
			$value = trim( $single_row[ $csv_row_field_name ] );
			if ( ! empty( $value ) ) {
				$shipping_method_data->update_setting( $field_name, $value );
			}
		}

		return $shipping_method_data;
	}

	/**
	 * @param array $single_row   .
	 * @param array $method_rules .
	 *
	 * @return array
	 * @throws InvalidImportFormatException .
	 * @throws UnknownConditionException .
	 */
	private function process_rules_settings( array $single_row, array $method_rules ) {

		$rule_number = $this->get_number( $this->get_label_rule_number(), $single_row );

		$rule = $this->get_rule( $method_rules, $single_row, $rule_number );

		$rule = $this->process_rule_costs( $rule, $single_row );

		$rule = $this->process_rule_special_action( $rule, $single_row );

		$rule = $this->process_rule_additional_costs( $rule, $single_row );

		$rule = $this->process_rule_conditions( $rule, $single_row );

		$method_rules[ $rule_number ] = $rule;

		ksort( $method_rules );

		return $method_rules;
	}

	/**
	 * @param string $csv_field  .
	 * @param array  $single_row .
	 *
	 * @return int
	 * @throws InvalidImportFormatException .
	 * @throws MissingImportFieldException .
	 */
	private function get_number( $csv_field, array $single_row ) {
		if ( ! empty( $single_row[ $csv_field ] ) ) {
			if ( is_numeric( $single_row[ $csv_field ] ) ) {
				return (int) $single_row[ $csv_field ];
			} else {
				// Translators: field.
				throw new InvalidImportFormatException( sprintf( __( 'invalid %1$s', 'flexible-shipping-import-export' ), $csv_field ) );
			}
		} else {
			// Translators: field.
			throw new MissingImportFieldException( sprintf( __( 'missing %1$s', 'flexible-shipping-import-export' ), $csv_field ) );
		}
	}

	/**
	 * @param array $method_rules .
	 * @param array $single_row   .
	 * @param int   $rule_number  .
	 *
	 * @return array|mixed
	 */
	private function get_rule( array $method_rules, array $single_row, $rule_number ) {
		if ( isset( $method_rules[ $rule_number ] ) ) {
			$rule = $method_rules[ $rule_number ];
		} else {
			$rule = array(
				RuleCostFieldsFactory::COST_PER_ORDER => '',
				Rule::CONDITIONS                      => array(),
				Rule::ADDITIONAL_COSTS                => array(),
			);
		}

		return $rule;
	}

	/**
	 * @param array $rule       .
	 * @param array $single_row .
	 *
	 * @return array
	 */
	private function process_rule_costs( array $rule, array $single_row ) {
		return $this->process_fields( $rule, $this->rule_costs_fields, array( $this, 'get_label_rule_costs_field' ), $single_row );
	}

	/**
	 * @param array $rule       .
	 * @param array $single_row .
	 *
	 * @return array
	 */
	private function process_rule_special_action( array $rule, array $single_row ) {
		foreach ( $this->special_action_fields as $field ) {
			$csv_field_name = $this->get_label_special_action_field( $field->get_name() );
			if ( ! empty( $single_row[ $csv_field_name ] ) ) {
				$rule[ $field->get_id() ] = $single_row[ $csv_field_name ];
			}
		}

		return $rule;
	}

	/**
	 * @param array $rule       .
	 * @param array $single_row .
	 *
	 * @return array
	 * @throws InvalidImportFormatException .
	 */
	private function process_rule_additional_costs( array $rule, array $single_row ) {
		$additional_costs = $rule[ Rule::ADDITIONAL_COSTS ];
		try {
			$additional_costs_number = $this->get_number( $this->get_label_additional_cost_number(), $single_row );
		} catch ( MissingImportFieldException $e ) {
			$additional_costs_number = false;
		}
		if ( $additional_costs_number && count( $this->rule_additional_costs_fields ) ) {
			if ( isset( $additional_costs[ $additional_costs_number ] ) ) {
				// Translators: cost number.
				throw new InvalidImportFormatException( sprintf( __( 'duplicated %1$s', 'flexible-shipping-import-export' ), $this->get_label_additional_cost_number() ) );
			}
			$additional_costs[ $additional_costs_number ] = $this->process_fields( array(), $this->rule_additional_costs_fields, array( $this, 'get_label_additional_cost_field' ), $single_row );
		}

		ksort( $additional_costs );

		$rule[ Rule::ADDITIONAL_COSTS ] = $additional_costs;

		return $rule;
	}

	/**
	 * @param array $rule       .
	 * @param array $single_row .
	 *
	 * @return array
	 * @throws InvalidImportFormatException .
	 * @throws UnknownConditionException .
	 */
	private function process_rule_conditions( array $rule, array $single_row ) {
		$conditions = $rule[ Rule::CONDITIONS ];
		try {
			$condition_number = (int) $single_row[ $this->get_label_condition_number() ];
		} catch ( MissingImportFieldException $e ) {
			$condition_number = false;
		}
		if ( $condition_number ) {
			if ( isset( $conditions[ $condition_number ] ) ) {
				// Translators: condition number.
				throw new InvalidImportFormatException( sprintf( __( 'duplicated %1$s', 'flexible-shipping-import-export' ), $this->get_label_condition_number() ) );
			}
			if ( ! empty( $single_row[ $this->get_label_condition_id() ] ) ) {
				$condition_id = $single_row[ $this->get_label_condition_id() ];
				if ( ! empty( $this->available_conditions[ $condition_id ] ) ) {
					$condition_fields                = $this->available_conditions[ $condition_id ]->get_fields();
					$conditions[ $condition_number ] = $this->process_fields( array( Rule::CONDITION_ID => $condition_id ), $condition_fields, array( $this, 'get_label_condition_field' ), $single_row );
					if ( isset( $this->import_data[ $condition_id ] ) ) {
						$this->verify_condition_fields( $condition_fields, $conditions[ $condition_number ], $this->import_data[ $condition_id ] );
					}
				} else {
					// Translators: condition ID.
					throw new UnknownConditionException( sprintf( __( 'invalid %1$s: %2$s', 'flexible-shipping-import-export' ), $this->get_label_condition_id(), $condition_id ), $condition_id );
				}
			} else {
				// Translators: condition ID.
				throw new InvalidImportFormatException( sprintf( __( 'invalid %1$s', 'flexible-shipping-import-export' ), $this->get_label_condition_id() ) );
			}
		}

		ksort( $conditions );

		$rule[ Rule::CONDITIONS ] = $conditions;

		return $rule;
	}

	/**
	 * @param array      $conditions_fields .
	 * @param array      $condition .
	 * @param ImportData $import_data .
	 *
	 * @throws InvalidImportDataException .
	 */
	private function verify_condition_fields( array $conditions_fields, array $condition, ImportData $import_data ) {
		foreach ( $conditions_fields as $field ) {
			$import_data->verify_data( isset( $condition[ $field->get_name() ] ) ? $condition[ $field->get_name() ] : null, $field->get_name() );
		}
	}

	/**
	 * @param array    $to_array            .
	 * @param Field[]  $fields              .
	 * @param callable $field_name_resolver .
	 * @param array    $single_row          .
	 *
	 * @return array
	 * @throws InvalidImportFormatException .
	 */
	private function process_fields( array $to_array, array $fields, $field_name_resolver, array $single_row ) {

		foreach ( $fields as $field ) {
			$field_name      = $field->get_name();
			$csv_field_name  = $field_name_resolver( $field_name );
			$csv_field_value = isset( $single_row[ $csv_field_name ] ) ? $single_row[ $csv_field_name ] : null;
			if ( ! empty( $csv_field_value ) || '0' === (string) $csv_field_value ) {
				if ( $field instanceof Field\InputNumberField ) {
					$csv_field_value = $this->prepare_number( $csv_field_value );
					if ( is_numeric( $csv_field_value ) ) {
						$to_array[ $field_name ] = $csv_field_value;
					} else {
						// Translators: field name.
						throw new InvalidImportFormatException( sprintf( __( 'invalid %1$s', 'flexible-shipping-import-export' ), $csv_field_name ) );
					}
				} else {
					$to_array[ $field_name ] = $csv_field_value;
				}
			}
		}

		return $to_array;
	}

	/**
	 * @param string $csv_field_value .
	 *
	 * @return string
	 */
	private function prepare_number( $csv_field_value ) {
		return str_replace( ',', '.', $csv_field_value );
	}

}
