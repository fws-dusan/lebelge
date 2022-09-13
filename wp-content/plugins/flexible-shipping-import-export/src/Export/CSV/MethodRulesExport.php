<?php
/**
 * Class MethodRulesExport
 *
 * @package WPDesk\FS\TableRate\ImportExport\Export\CSV
 */

namespace WPDesk\FS\TableRate\ImportExport\Export\CSV;

use FSVendor\WPDesk\Forms\Field;
use WPDesk\FS\TableRate\ImportExport\Conditions\ExportData;
use WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels\RuleLabelsTrait;
use WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels\SpecialActionsTrait;
use WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels\ConditionLabelsTrait;
use WPDesk\FS\TableRate\ImportExport\Export\CSV\Labels\AdditionalCostsLabelsTrait;
use WPDesk\FS\TableRate\Rule\Condition\Condition;

/**
 * Exporter of method rules.
 */
class MethodRulesExport extends CSVAbstract {
	use RuleLabelsTrait;
	use SpecialActionsTrait;
	use ConditionLabelsTrait;
	use AdditionalCostsLabelsTrait;

	/**
	 * @var Condition[] .
	 */
	private $conditions;

	/**
	 * @var array .
	 */
	private $method_rules;

	/**
	 * @var array .
	 */
	private $rule_costs_fields;

	/**
	 * @var array .
	 */
	private $rule_additional_costs_fields;

	/**
	 * @var array .
	 */
	private $special_action_fields;

	/**
	 * @var ExportData[] .
	 */
	private $export_preparing;

	/**
	 * MethodRulesExporter constructor.
	 *
	 * @param array        $method_rules                 .
	 * @param Condition[]  $conditions                   .
	 * @param Field[]      $rule_costs_fields            .
	 * @param Field[]      $rule_additional_costs_fields .
	 * @param Field[]      $special_action_fields        .
	 * @param ExportData[] $export_preparing             .
	 */
	public function __construct(
		array $method_rules,
		array $conditions,
		array $rule_costs_fields,
		array $rule_additional_costs_fields,
		array $special_action_fields,
		array $export_preparing
	) {
		$this->method_rules                 = $method_rules;
		$this->conditions                   = $conditions;
		$this->rule_costs_fields            = $rule_costs_fields;
		$this->rule_additional_costs_fields = $rule_additional_costs_fields;
		$this->special_action_fields        = $special_action_fields;
		$this->export_preparing             = $export_preparing;
	}

	/**
	 * @param bool $with_header .
	 *
	 * @return array
	 */
	public function get_data( $with_header = true ) {
		$header = $this->get_header_fields();
		$fields = $this->get_condition_fields();

		$data = array();

		if ( $with_header ) {
			$data[] = $header;
		}

		$method_rule_row_empty = $this->get_row_default_fields( $header );

		foreach ( $this->prepare_method_rules_data( $fields ) as $rule_number => $method_rule ) {
			$method_rule_rows = max( count( $method_rule['conditions'] ), count( $method_rule['additional_costs'] ) );

			for ( $i = 1; $i <= $method_rule_rows; $i ++ ) {
				$data[] = $this->prepare_data_row( $rule_number, $method_rule, $method_rule_row_empty, $i );
			}
		}

		return $data;
	}

	/**
	 * @param int   $rule_number           .
	 * @param array $method_rule           .
	 * @param array $method_rule_row_empty .
	 * @param int   $iteration             .
	 *
	 * @return array
	 */
	private function prepare_data_row( $rule_number, $method_rule, $method_rule_row_empty, $iteration ) {
		$data_row = $method_rule_row_empty;

		$data_row[ $this->get_label_rule_number() ] = $rule_number;

		if ( isset( $method_rule['conditions'][ $iteration ] ) ) {
			$data_row = array_merge( $data_row, $method_rule['conditions'][ $iteration ] );
		}

		if ( isset( $method_rule['additional_costs'][ $iteration ] ) ) {
			$data_row = array_merge( $data_row, $method_rule['additional_costs'][ $iteration ] );
		}

		if ( 1 === $iteration ) {
			$data_row = array_merge( $data_row, $this->get_shipping_method_settings_fields( $method_rule ) );
		}

		return $data_row;
	}

	/**
	 * @param array $method_rule .
	 *
	 * @return array
	 */
	private function get_shipping_method_settings_fields( $method_rule ) {
		$data_row = array();

		foreach ( $this->rule_costs_fields as $field ) {
			$field_name = $field->get_name();

			$data_row[ $this->get_label_rule_costs_field( $field_name ) ] = isset( $method_rule['data'][ $field_name ] ) ? $method_rule['data'][ $field_name ] : '';
		}

		foreach ( $this->special_action_fields as $field ) {
			$field_name = $field->get_name();

			$data_row[ $this->get_label_special_action_field( $field_name ) ] = isset( $method_rule['data'][ $field_name ] ) ? $method_rule['data'][ $field_name ] : '';
		}

		return $data_row;
	}

	/**
	 * @param array $fields .
	 *
	 * @return array
	 */
	private function prepare_method_rules_data( $fields ) {
		$data = array();

		$rule_number = 1;
		foreach ( $this->method_rules as $method_rule_id => $method_rule ) {
			$data[ $rule_number ] = array(
				'data' => $method_rule,
			);

			$conditions                         = isset( $method_rule['conditions'] ) ? $method_rule['conditions'] : array();
			$data[ $rule_number ]['conditions'] = $this->prepare_method_rules_data_conditions( $conditions, $fields );

			if ( count( $this->rule_additional_costs_fields ) ) {
				$additional_costs                         = isset( $method_rule['additional_costs'] ) ? $method_rule['additional_costs'] : array();
				$data[ $rule_number ]['additional_costs'] = $this->prepare_method_rules_additional_costs_fields( $additional_costs );
			}

			$rule_number ++;
		}

		return $data;
	}

	/**
	 * @param array $additional_costs .
	 *
	 * @return array
	 */
	private function prepare_method_rules_additional_costs_fields( $additional_costs ) {
		$data = array();

		$additional_costs_number = 1;
		foreach ( $additional_costs as $additional_cost ) {
			$additional_cost_fields = array(
				$this->get_label_additional_cost_number() => $additional_costs_number,
			);

			foreach ( $this->rule_additional_costs_fields as $field ) {
				$field_name = $field->get_name();

				$additional_cost_fields[ $this->get_label_additional_cost_field( $field_name ) ] = isset( $additional_cost[ $field_name ] ) ? $additional_cost[ $field_name ] : '';
			}

			$data[ $additional_costs_number ] = $additional_cost_fields;

			$additional_costs_number ++;
		}

		return $data;
	}

	/**
	 * @param array $conditions .
	 * @param array $fields     .
	 *
	 * @return array
	 */
	private function prepare_method_rules_data_conditions( $conditions, $fields ) {
		$data = array();

		$condition_number = 1;
		foreach ( $conditions as $condition ) {
			$condition_row = array(
				$this->get_label_condition_number() => $condition_number,
				$this->get_label_condition_id()     => isset( $condition['condition_id'] ) ? $condition['condition_id'] : '',
			);

			if ( isset( $fields[ $condition['condition_id'] ] ) && is_array( $fields[ $condition['condition_id'] ] ) ) {
				foreach ( $fields[ $condition['condition_id'] ] as $field ) {
					$condition_row[ $this->get_label_condition_field( $field ) ] = $this->get_field_value( isset( $condition[ $field ] ) ? $condition[ $field ] : '', $condition, $field );
				}
			}

			$data[ $condition_number ] = $condition_row;

			$condition_number ++;
		}

		return $data;
	}

	/**
	 * @param mixed  $value      .
	 * @param array  $condition  .
	 * @param string $field_name .
	 *
	 * @return string
	 */
	private function get_field_value( $value, $condition, $field_name ) {
		$condition_id = $condition['condition_id'];
		$value        = isset( $this->export_preparing[ $condition_id ] ) ? $this->export_preparing[ $condition_id ]->prepare_data( $value, $field_name ) : $value;

		return is_array( $value ) ? implode( ',', $value ) : maybe_serialize( $value );
	}

	/**
	 * @return array
	 */
	public function get_header_fields() {
		$header = array(
			$this->get_label_rule_number(),
		);

		// Conditions.
		$header = array_merge( $header, $this->get_columns_conditions() );

		// Rule costs.
		$header = array_merge( $header, $this->get_columns_rule_costs() );

		if ( count( $this->rule_additional_costs_fields ) ) {
			$header = array_merge( $header, $this->get_columns_additional_costs() );
		}

		if ( count( $this->special_action_fields ) ) {
			$header = array_merge( $header, $this->get_columns_special_actions() );
		}

		return array_values( array_unique( $header ) );
	}

	/**
	 * @return array
	 */
	private function get_condition_fields() {
		$fields = array();

		foreach ( $this->conditions as $condition ) {
			$condition_fields = $condition->get_fields();

			$fields[ $condition->get_condition_id() ] = array();

			foreach ( $condition_fields as $field ) {
				$fields[ $condition->get_condition_id() ][] = $field->get_name();
			}
		}

		return $fields;
	}

	/**
	 * @return array
	 */
	private function get_columns_additional_costs() {
		$columns = array( $this->get_label_additional_cost_number() );

		foreach ( $this->rule_additional_costs_fields as $field ) {
			$columns[] = $this->get_label_additional_cost_field( $field->get_name() );
		}

		return $columns;
	}

	/**
	 * @return array
	 */
	private function get_columns_rule_costs() {
		$columns = array();

		foreach ( $this->rule_costs_fields as $field ) {
			$columns[] = $this->get_label_rule_costs_field( $field->get_name() );
		}

		return $columns;
	}

	/**
	 * @return array
	 */
	private function get_columns_special_actions() {
		$columns = array();

		foreach ( $this->special_action_fields as $field ) {
			$columns[] = $this->get_label_special_action_field( $field->get_name() );
		}

		return $columns;
	}

	/**
	 * @return array
	 */
	private function get_columns_conditions() {
		$columns = array(
			$this->get_label_condition_number(),
			$this->get_label_condition_id(),
		);

		// Condition fields.
		foreach ( $this->conditions as $condition ) {
			foreach ( $condition->get_fields() as $field ) {
				$columns[] = $this->get_label_condition_field( $field->get_name() );
			}
		}

		return $columns;
	}
}
