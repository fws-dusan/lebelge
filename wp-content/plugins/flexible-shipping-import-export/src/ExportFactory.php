<?php
/**
 * Class ExportFactory
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport;

use Exception;
use WC_Shipping_Zone;
use WC_Shipping_Zones;
use WPDesk\FS\TableRate\ImportExport\Conditions\ExportDataFactory;
use WPDesk\FS\TableRate\ImportExport\Exception\InvalidExportFormatException;
use WPDesk\FS\TableRate\ImportExport\Exception\NoShippingMethodsToExportException;
use WPDesk\FS\TableRate\ImportExport\Export\CSV;
use WPDesk\FS\TableRate\ImportExport\Export\ExportInterface;
use WPDesk\FS\TableRate\Rule\Condition\ConditionsFactory;
use WPDesk\FS\TableRate\Rule\Cost\RuleAdditionalCostFactory;
use WPDesk\FS\TableRate\Rule\Cost\RuleAdditionalCostFieldsFactory;
use WPDesk\FS\TableRate\Rule\Cost\RuleCostFieldsFactory;
use WPDesk\FS\TableRate\Rule\SpecialAction\SpecialActionFactory;
use WPDesk\FS\TableRate\Rule\SpecialAction\SpecialActionFieldsFactory;
use WPDesk\FS\TableRate\RulesTableSettings;
use WPDesk\FS\TableRate\ShippingMethod\SingleMethodSettings;
use WPDesk\FS\TableRate\ShippingMethodSingle;

/**
 * Factory of export.
 */
class ExportFactory {
	/**
	 * @var string .
	 */
	private $type;

	/**
	 * @var int|null .
	 */
	private $zone_id;

	/**
	 * @var int[]
	 */
	private $instance_ids;

	/**
	 * ExportFactory constructor.
	 *
	 * @param string $type         .
	 * @param int    $zone_id      .
	 * @param int[]  $instance_ids .
	 */
	public function __construct( $type, $zone_id = null, $instance_ids = array() ) {
		$this->type         = $type;
		$this->zone_id      = $zone_id;
		$this->instance_ids = $instance_ids;
	}

	/**
	 * @return ExportInterface
	 * @throws InvalidExportFormatException .
	 */
	public function get_exporter() {
		$shipping_methods         = $this->get_shipping_methods();
		$conditions               = ( new ConditionsFactory() )->get_conditions();
		$export_preparing         = ( new ExportDataFactory() )->create_prepares();
		$shipping_method_settings = ( new SingleMethodSettings() )->get_settings_fields( array(), true );

		$rules_tables_settings        = new RulesTableSettings();
		$rule_costs_fields            = ( new RuleCostFieldsFactory() )->get_fields();
		$rule_additional_costs_fields = $rules_tables_settings->is_multiple_additional_costs_available() ? ( new RuleAdditionalCostFieldsFactory( ( new RuleAdditionalCostFactory() )->get_additional_costs() ) )->get_fields() : array();
		$special_action_fields        = $rules_tables_settings->is_special_actions_available() ? ( new SpecialActionFieldsFactory( ( new SpecialActionFactory() )->get_special_actions() ) )->get_fields() : array();

		switch ( $this->type ) {
			case 'csv':
				return new CSV(
					$shipping_methods,
					$conditions,
					$shipping_method_settings,
					$rule_costs_fields,
					$rule_additional_costs_fields,
					$special_action_fields,
					$export_preparing
				);
			default:
				throw new InvalidExportFormatException(
					sprintf(
					// Translators: Unsupported data format.
						__( 'Unsupported data format: %1$s', 'flexible-shipping-import-export' ),
						$this->type
					)
				);
		}
	}

	/**
	 * @return ShippingMethodSingle[]
	 * @throws NoShippingMethodsToExportException .
	 */
	private function get_shipping_methods() {
		$shipping_methods = array();

		if ( null !== $this->zone_id ) {
			$shipping_methods = $this->get_shipping_methods_by_zone_id( $this->zone_id );
		} elseif ( $this->instance_ids ) {
			$shipping_methods = $this->get_shipping_methods_by_instance_ids( $this->instance_ids );
		}

		if ( $shipping_methods ) {
			return $shipping_methods;
		}

		throw new NoShippingMethodsToExportException( __( 'Not found shipping methods to export', 'flexible-shipping-import-export' ) );
	}

	/**
	 * @param int[] $instance_ids .
	 *
	 * @return ShippingMethodSingle[]
	 */
	private function get_shipping_methods_by_instance_ids( $instance_ids ) {
		$shipping_methods = array();

		foreach ( $instance_ids as $instance_id ) {
			$shipping_methods[] = WC_Shipping_Zones::get_shipping_method( $instance_id );
		}

		$shipping_methods = $this->filter_shipping_methods( $shipping_methods );

		return $shipping_methods;
	}

	/**
	 * @param int $zone_id .
	 *
	 * @return ShippingMethodSingle[]
	 * @throws Exception .
	 */
	private function get_shipping_methods_by_zone_id( $zone_id ) {
		$zone = new WC_Shipping_Zone( absint( $zone_id ) );

		return $this->filter_shipping_methods( $zone->get_shipping_methods() );
	}

	/**
	 * @param mixed $shipping_methods .
	 *
	 * @return ShippingMethodSingle[]
	 */
	private function filter_shipping_methods( $shipping_methods ) {
		return array_filter(
			$shipping_methods,
			function ( $shipping_method ) {
				return $shipping_method instanceof ShippingMethodSingle;
			}
		);
	}
}
