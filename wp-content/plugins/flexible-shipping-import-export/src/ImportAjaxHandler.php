<?php
/**
 * Class ImportAjaxActions
 *
 * @package WPDesk\FS\TableRate\ImportExport
 */

namespace WPDesk\FS\TableRate\ImportExport;

use FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use Throwable;
use WC_Shipping_Method;
use WC_Shipping_Zone;
use WPDesk\FS\TableRate\ImportExport\Conditions\ImportDataFactory;
use WPDesk\FS\TableRate\ImportExport\Conditions\ImportProcessor;
use WPDesk\FS\TableRate\ImportExport\Exception\InvalidImportFormatException;
use WPDesk\FS\TableRate\ImportExport\Exception\ShippingMethodNotFoundException;
use WPDesk\FS\TableRate\ImportExport\Tracker\TrackerDataCollector;
use WPDesk\FS\TableRate\Rule\Condition\ConditionsFactory;
use WPDesk\FS\TableRate\Rule\Rule;
use WPDesk\FS\TableRate\RulesSettingsField;
use WPDesk\FS\TableRate\ShippingMethod\CommonMethodSettings;
use WPDesk\FS\TableRate\ShippingMethodSingle;

/**
 * Can handle import AJAX actions.
 */
class ImportAjaxHandler implements Hookable {

	const AJAX_ACTION = 'flexible_shipping_import';
	const PARSE       = 'parse';
	const IMPORT      = 'import';

	/**
	 * .
	 */
	public function hooks() {
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'handle_ajax_action_import' ) );
	}

	/**
	 * .
	 */
	public function handle_ajax_action_import() {
		check_ajax_referer( self::AJAX_ACTION, 'security' );

		$stage   = sanitize_key( $this->get_post_value_as_string( 'stage' ) );
		$zone_id = (int) sanitize_key( $this->get_post_value_as_string( 'zone_id' ) );

		try {
			if ( self::PARSE === $stage ) {
				wp_send_json_success( $this->parse( $zone_id ) );
			}
			if ( self::IMPORT === $stage ) {
				wp_send_json_success( $this->import( $zone_id ) );
			}
		} catch ( Throwable $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}

		wp_send_json_error( array( 'message' => __( 'Missing or invalid "stage" parameter!', 'flexible-shipping-import-export' ) ) );
	}

	/**
	 * @param int $zone_id .
	 *
	 * @return array
	 * @throws InvalidImportFormatException .
	 */
	private function parse( $zone_id ) {
		$content_type = sanitize_text_field( $this->get_post_value_as_string( 'content_type' ) );
		$data         = $this->remove_bom( sanitize_textarea_field( $this->get_post_value_as_string( 'data' ) ) );

		return array(
			'import_shipping_methods' => array_values( $this->process_import_data( $data, $content_type ) ),
			'zone_shipping_methods'   => array_values( $this->get_zone_shipping_methods_for_zone_id( $zone_id ) ),
		);
	}

	/**
	 * @param string $str .
	 *
	 * @return string
	 */
	private function remove_bom( $str ) {
		$bom = pack( 'H *', 'EFBBBF' );

		$str = preg_replace( "/ ^ $bom /", '', $str );

		return $str ? $str : '';
	}

	/**
	 * @param string $key .
	 *
	 * @return string
	 */
	private function get_post_value_as_string( $key ) {
		$value = wp_unslash( $_POST[ $key ] ); // phpcs:ignore.
		return is_array( $value ) ? '' : $value;
	}

	/**
	 * @param int $zone_id .
	 *
	 * @return array
	 * @throws InvalidImportFormatException .
	 */
	private function import( $zone_id ) {
		$shipping_methods_to_import = json_decode( sanitize_textarea_field( $this->get_post_value_as_string( 'data' ) ), true );
		$all_methods                = (int) sanitize_text_field( $this->get_post_value_as_string( 'all_methods' ) );
		$zone                       = \WC_Shipping_Zones::get_zone( $zone_id );
		if ( ! $zone instanceof WC_Shipping_Zone ) {
			// Translators: zone id.
			throw new InvalidImportFormatException( sprintf( __( 'Invalid zone id: %1$s', 'flexible-shipping-import-export' ), $zone_id ) );
		}
		$conditions_preparing       = new ImportProcessor( ( new ImportDataFactory() )->create_prepares(), ( new ConditionsFactory() )->get_conditions() );

		$log = array(
			'updated_methods' => 0,
			'created_methods' => 0,
		);

		wc_transaction_query( 'start', true );
		foreach ( $shipping_methods_to_import as $shipping_method_data ) {
			$log = $this->import_single_shipping_method( $log, $zone, $shipping_method_data['settings'], $shipping_method_data['mapping'], $conditions_preparing );
		}

		$imported_methods = (int) ( $log['created_methods'] + $log['updated_methods'] );
		$skipped = $all_methods - $imported_methods;
		if ( $skipped ) {
			// Translators: skipped.
			$log['skipped_methods'] = sprintf( _n( '%1$s method skipped', '%1$s methods skipped', $skipped, 'flexible-shipping-import-export' ), $skipped );
		}
		// Translators: all.
		$log['all_methods'] = sprintf( _n( '%1$s method in import file', '%1$s methods in import file', $all_methods, 'flexible-shipping-import-export' ), $all_methods );
		if ( $log['created_methods'] ) {
			// Translators: created.
			$log['created_methods'] = sprintf( _n( '%1$s method added', '%1$s methods added', $log['created_methods'], 'flexible-shipping-import-export' ), $log['created_methods'] );
		} else {
			unset( $log['created_methods'] );
		}
		if ( $log['updated_methods'] ) {
			// Translators: updated.
			$log['updated_methods'] = sprintf( _n( '%1$s method updated', '%1$s methods updated', $log['updated_methods'], 'flexible-shipping-import-export' ), $log['updated_methods'] );
		} else {
			unset( $log['updated_methods'] );
		}

		( new TrackerDataCollector() )->update_import_data( $imported_methods );

		wc_transaction_query( 'commit', true );
		return array(
			'log' => $log,
		);
	}

	/**
	 * @param array            $log .
	 * @param WC_Shipping_Zone $zone .
	 * @param array            $shipping_method_settings .
	 * @param array            $mapping .
	 * @param ImportProcessor  $conditions_preparing .
	 *
	 * @return array
	 * @throws ShippingMethodNotFoundException .
	 */
	private function import_single_shipping_method( array $log, WC_Shipping_Zone $zone, array $shipping_method_settings, array $mapping, ImportProcessor $conditions_preparing ) {
		$action          = $mapping['action'];
		$shipping_method = $this->get_or_create_shipping_method( $action, $zone, empty( $mapping['enable_shipping_method'] ) ? false : $mapping['enable_shipping_method'] );

		$this->update_shipping_method_settings( $shipping_method, $shipping_method_settings, $conditions_preparing );

		if ( 'new' === $action ) {
			$log['created_methods']++;
		} else {
			$log['updated_methods']++;
		}

		return $log;
	}

	/**
	 * @param ShippingMethodSingle $shipping_method .
	 * @param array                $shipping_method_data .
	 * @param ImportProcessor      $conditions_preparing .
	 */
	private function update_shipping_method_settings( ShippingMethodSingle $shipping_method, array $shipping_method_data, ImportProcessor $conditions_preparing ) {
		foreach ( $shipping_method->instance_form_fields as $field_name => $settings ) {
			if ( isset( $settings['type'] ) && isset( $shipping_method_data[ $field_name ] ) ) {
				if ( RulesSettingsField::FIELD_TYPE === $settings['type'] ) {
					$shipping_method->update_instance_option( $field_name, (string) json_encode( $this->prepare_conditions_fields( $shipping_method_data[ $field_name ], $conditions_preparing ) ) );
				} else {
					$shipping_method->update_instance_option( $field_name, $shipping_method_data[ $field_name ] );
				}
			}
		}
	}

	/**
	 * @param array           $rules .
	 * @param ImportProcessor $conditions_preparing .
	 *
	 * @return array
	 */
	private function prepare_conditions_fields( array $rules, ImportProcessor $conditions_preparing ) {
		foreach ( $rules as $rule_id => $rule ) {
			$conditions = $rule[ Rule::CONDITIONS ];
			foreach ( $conditions as $condition_id => $condition ) {
				$conditions[ $condition_id ] = $conditions_preparing->process_condition( $condition, array() ); // TODO: mappings.
			}
			$rules[ $rule_id ][ Rule::CONDITIONS ] = $conditions;
		}

		return $rules;
	}

	/**
	 * @param string           $action .
	 * @param WC_Shipping_Zone $zone .
	 * @param bool             $enable_new_shipping_method .
	 *
	 * @return ShippingMethodSingle
	 * @throws ShippingMethodNotFoundException .
	 */
	private function get_or_create_shipping_method( $action, WC_Shipping_Zone $zone, $enable_new_shipping_method ) {
		if ( 'new' === $action ) {
			$instance_id             = $zone->add_shipping_method( ShippingMethodSingle::SHIPPING_METHOD_ID );
			if ( ! $enable_new_shipping_method ) {
				$shipping_method = \WC_Shipping_Zones::get_shipping_method( $instance_id );
				if ( ! is_bool( $shipping_method ) ) {
					$this->disable_shipping_method( $shipping_method, $zone );
				}
			}
		} else {
			$instance_id = (int) $action;
		}
		$shipping_method = \WC_Shipping_Zones::get_shipping_method( $instance_id );
		if ( is_bool( $shipping_method ) ) {
			// Translators: method title.
			throw new ShippingMethodNotFoundException( sprintf( __( 'Shipping method %1$s not found!', 'flexible-shipping-import-export' ), $instance_id ) );
		}

		if ( ! $shipping_method instanceof ShippingMethodSingle ) {
			// Translators: method id.
			throw new ShippingMethodNotFoundException( sprintf( __( 'Invalid shipping method type: %1$s', 'flexible-shipping-import-export' ), $shipping_method->id ) );
		}

		$shipping_method->init_instance_form_fields( true );

		return $shipping_method;
	}

	/**
	 * @param WC_Shipping_Method $shipping_method .
	 * @param WC_Shipping_Zone   $zone .
	 */
	private function disable_shipping_method( WC_Shipping_Method $shipping_method, WC_Shipping_Zone $zone ) {
		global $wpdb;

		if ( $wpdb->update( "{$wpdb->prefix}woocommerce_shipping_zone_methods", array( 'is_enabled' => 0 ), array( 'instance_id' => $shipping_method->instance_id ) ) ) {
			do_action( 'woocommerce_shipping_zone_method_status_toggled', $shipping_method->instance_id, $shipping_method->id, $zone->get_id(), 0 );
		}
	}

	/**
	 * @param string $data .
	 * @param string $content_type .
	 *
	 * @return array
	 * @throws InvalidImportFormatException .
	 */
	private function process_import_data( $data, $content_type ) {
		$data_parser_factory = new DataParserFactory();
		$parser              = $data_parser_factory->create_parser( $data, $content_type );

		$parsed_methods = $parser->parse();
		if ( 0 === count( $parsed_methods ) ) {
			throw new InvalidImportFormatException( __( 'The import file does not contain shipping methods', 'flexible-shipping-import-export' ) );
		}

		return array_map(
			function( ShippingMethodData $parsed_method ) {
				return array(
					'method_id'                  => $parsed_method->get_method_id(),
					'method_title'               => $parsed_method->get_method_title(),
					'settings'                   => $parsed_method->get_settings(),
					'can_be_imported'            => $parsed_method->is_can_be_imported(),
					'cannot_be_imported_message' => $parsed_method->prepare_cannot_be_imported_message(),
					'formatted_info'             => $parsed_method->get_formatted_info(),
				);
			},
			$parsed_methods
		);
	}

	/**
	 * Get zone shipping methods.
	 *
	 * @param int $zone_id .
	 *
	 * @return array
	 */
	private function get_zone_shipping_methods_for_zone_id( $zone_id ) {
		$shipping_methods = array();
		$zone             = \WC_Shipping_Zones::get_zone( $zone_id );
		if ( ! is_bool( $zone ) ) {
			$zone_shipping_methods = $zone->get_shipping_methods();
			foreach ( $zone_shipping_methods as $shipping_method ) {
				if ( $shipping_method instanceof ShippingMethodSingle ) {
					$shipping_methods[] = array(
						'instance_id'    => $shipping_method->get_instance_id(),
						'method_title'   => $shipping_method->get_instance_option( CommonMethodSettings::METHOD_TITLE ),
						'rules_count'    => count( $shipping_method->get_instance_option( CommonMethodSettings::METHOD_RULES, array() ) ),
						'formatted_info' => sprintf(
						// Translators: method title, new line, rules count.
							_n(
								'%1$s shipping method (ID: %2$s) will be overwritten. Shipping method contains %3$s rule.',
								'%1$s shipping method (ID: %2$s) will be overwritten. Shipping method contains %3$s rules.',
								count( $shipping_method->get_instance_option( CommonMethodSettings::METHOD_RULES, array() ) ),
								'flexible-shipping-import-export'
							),
							sprintf( '<strong>%1$s</strong>', $shipping_method->get_instance_option( CommonMethodSettings::METHOD_TITLE ) ),
							$shipping_method->get_instance_id(),
							count( $shipping_method->get_instance_option( CommonMethodSettings::METHOD_RULES, array() ) )
						),
					);
				}
			}
		}

		return $shipping_methods;
	}

}
