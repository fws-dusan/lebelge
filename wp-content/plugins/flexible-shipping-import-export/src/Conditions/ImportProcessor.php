<?php
/**
 * Class ImportProcessor
 *
 * @package WPDesk\FS\TableRate\ImportExport\Conditions
 */

namespace WPDesk\FS\TableRate\ImportExport\Conditions;

use FSVendor\WPDesk\Forms\Field;
use WPDesk\FS\TableRate\Rule\Condition\Condition;
use WPDesk\FS\TableRate\Rule\Rule;

/**
 * Can process import data.
 */
class ImportProcessor {

	/**
	 * @var ImportData[]
	 */
	private $import_data;

	/**
	 * @var Condition[]
	 */
	private $available_conditions;

	/**
	 * ImportProcessor constructor.
	 *
	 * @param ImportData[] $import_data .
	 * @param Condition[]  $available_conditions .
	 */
	public function __construct( array $import_data, array $available_conditions ) {
		$this->import_data          = $import_data;
		$this->available_conditions = $available_conditions;
	}

	/**
	 * @param array $condition .
	 * @param array $parameters_mapping .
	 *
	 * @return array
	 */
	public function process_condition( array $condition, $parameters_mapping ) {
		$condition_id = $condition[ Rule::CONDITION_ID ];
		if ( isset( $this->import_data[ $condition_id ] ) ) {
			$fields = $this->available_conditions[ $condition_id ]->get_fields();
			foreach ( $fields as $field ) {
				if ( isset( $condition[ $field->get_name() ] ) ) {
					$condition[ $field->get_name() ] = $this->import_data[ $condition_id ]->prepare_data( $condition[ $field->get_name() ], $field->get_name(), array() ); // TODO: mappings.
				}
			}
		} else {
			$condition = $this->process_condition_select_fields( $condition );
		}

		return $condition;
	}

	/**
	 * @param array $condition .
	 *
	 * @return array
	 */
	private function process_condition_select_fields( array $condition ) {
		$condition_id = $condition[ Rule::CONDITION_ID ];
		$fields = $this->available_conditions[ $condition_id ]->get_fields();
		foreach ( $fields as $field ) {
			if ( $field instanceof Field\WooSelect && $field->is_multiple() ) {
				$condition[ $field->get_name() ] = explode( ',', $condition[ $field->get_name() ] );
			}
		}

		return $condition;
	}

}
