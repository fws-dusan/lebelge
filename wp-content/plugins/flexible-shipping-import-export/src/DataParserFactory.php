<?php
/**
 * Class DataParserFactory
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport;

use WPDesk\FS\TableRate\ImportExport\Conditions\ImportDataFactory;
use WPDesk\FS\TableRate\ImportExport\CSVDataParser;
use WPDesk\FS\TableRate\ImportExport\DataParser;
use WPDesk\FS\TableRate\ImportExport\Exception\InvalidImportFormatException;
use WPDesk\FS\TableRate\Rule\Condition\Condition;
use WPDesk\FS\TableRate\Rule\Condition\ConditionsFactory;
use WPDesk\FS\TableRate\Rule\Cost\RuleAdditionalCostFactory;
use WPDesk\FS\TableRate\Rule\Cost\RuleAdditionalCostFieldsFactory;
use WPDesk\FS\TableRate\Rule\Cost\RuleCostFieldsFactory;
use WPDesk\FS\TableRate\Rule\SpecialAction\SpecialActionFactory;
use WPDesk\FS\TableRate\Rule\SpecialAction\SpecialActionFieldsFactory;
use WPDesk\FS\TableRate\RulesTableSettings;
use WPDesk\FS\TableRate\ShippingMethod\MethodSettings;
use WPDesk\FS\TableRate\ShippingMethod\SingleMethodSettings;

/**
 * Can create Data Parser.
 */
class DataParserFactory {

	/**
	 * @param string $raw_data .
	 * @param string $content_type .
	 *
	 * @return DataParser
	 * @throws InvalidImportFormatException .
	 */
	public function create_parser( $raw_data, $content_type ) {
		$import_data                  = ( new ImportDataFactory() )->create_prepares();
		$method_settings              = new SingleMethodSettings();
		$available_conditions         = ( new ConditionsFactory() )->get_conditions();
		$rule_costs_fields            = ( new RuleCostFieldsFactory() )->get_fields();
		$rules_tables_settings        = new RulesTableSettings();
		$rule_additional_costs_fields = $rules_tables_settings->is_multiple_additional_costs_available() ? ( new RuleAdditionalCostFieldsFactory( ( new RuleAdditionalCostFactory() )->get_additional_costs() ) )->get_fields() : array();
		$special_action_fields        = $rules_tables_settings->is_special_actions_available() ? ( new SpecialActionFieldsFactory( ( new SpecialActionFactory() )->get_special_actions() ) )->get_fields() : array();
		if ( 'text/csv' === $content_type ) {
			return new CSVDataParser( $raw_data, $import_data, $method_settings, $available_conditions, $rule_costs_fields, $rule_additional_costs_fields, $special_action_fields );
		} else {
			// Translators: content type.
			throw new InvalidImportFormatException( sprintf( __( 'Not supported data format: %1$s', 'flexible-shipping-import-export' ), $content_type ) );
		}
	}

}
