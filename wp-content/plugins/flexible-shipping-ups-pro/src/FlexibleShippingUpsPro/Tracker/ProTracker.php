<?php
/**
 * Tracker
 *
 * @package WPDesk\FlexibleShippingUpsPro\Tracker
 */

namespace WPDesk\FlexibleShippingUpsPro\Tracker;

use UpsProVendor\WPDesk\AbstractShipping\Settings\SettingsDecorators\BlackoutLeadDaysSettingsDefinitionDecoratorFactory;
use UpsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use UpsProVendor\WPDesk\UpsProShippingService\CollectionPointFlatRate\CollectionPointFlatRateSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\CutoffTime\CutoffTimeSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\DestinationAddressTypeSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\DestinationAddressType\EstimatedDeliverySettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\LeadTime\LeadTimeSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\MaximumTransitTime\MaximumTransitTimeSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsProShippingService\PickupType\PickupTypeSettingsDefinitionDecorator;
use UpsProVendor\WPDesk\UpsShippingService\UpsSettingsDefinition;
use UpsProVendor\WPDesk\WooCommerceShipping\HandlingFees\PriceAdjustmentNone;
use UpsProVendor\WPDesk\WooCommerceShippingPro\Packer\PackerSettings;
use WPDesk\FlexibleShippingUpsPro\ShippingMethod\UpsProShippingMethod;

/**
 * Handles tracker actions.
 *
 * TODO: refactor this class.
 */
class ProTracker implements Hookable {

	/**
	 * Priority filter
	 * Must fires after UPS free
	 *
	 * @var int
	 */
	const PRIORITY_AFTER_FREE_TRACKER = 20;

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_filter( 'wpdesk_tracker_data', array( $this, 'wpdesk_tracker_data' ), self::PRIORITY_AFTER_FREE_TRACKER );
		add_filter( 'wpdesk_tracker_notice_screens', array( $this, 'wpdesk_tracker_notice_screens' ) );
	}

	/**
	 * Append data for shipping method.
	 *
	 * @param array                $plugin_data Plugin data.
	 * @param UpsProShippingMethod $shipping_method Shipping method.
	 *
	 * @return array
	 */
	private function append_data_for_shipping_method(
		array $plugin_data,
		UpsProShippingMethod $shipping_method
	) {

		$instance_fields = $shipping_method->get_instance_form_fields();

		$access_point_option = $shipping_method->get_instance_option( 'access_point', UpsSettingsDefinition::DO_NOT_ADD_ACCESS_POINTS_TO_RATES );
		if ( CollectionPointFlatRateSettingsDefinitionDecorator::OPTION_ACCESS_POINT_FLAT_RATE === $access_point_option ) {
			$plugin_data['access_point_flat_rate']++;
		}

		$plugin_data = $this->price_adjustment_data( $shipping_method, $instance_fields, $plugin_data );
		$plugin_data = $this->delivery_dates_data( $shipping_method, $instance_fields, $plugin_data );
		$plugin_data = $this->delivery_packing_method( $shipping_method, $instance_fields, $plugin_data );
		$plugin_data = $this->delivery_packing_boxes( $shipping_method, $instance_fields, $plugin_data );
		$plugin_data = $this->destination_address_type( $shipping_method, $instance_fields, $plugin_data );
		$plugin_data = $this->pickup_type( $shipping_method, $instance_fields, $plugin_data );
		$plugin_data = $this->maximum_transit_time( $shipping_method, $instance_fields, $plugin_data );
		$plugin_data = $this->lead_time( $shipping_method, $instance_fields, $plugin_data );
		$plugin_data = $this->cutoff_time( $shipping_method, $instance_fields, $plugin_data );
		$plugin_data = $this->lead_blackout_days( $shipping_method, $instance_fields, $plugin_data );

		return $plugin_data;
	}

	/**
	 * Count price adjustment option.
	 *
	 * @param UpsProShippingMethod $shipping_method Shipping method.
	 * @param array                $instance_fields List of fields.
	 * @param array                $plugin_data     Plugin data.
	 *
	 * @return array
	 */
	private function price_adjustment_data( UpsProShippingMethod $shipping_method, array $instance_fields, array $plugin_data ) {
		foreach ( $instance_fields['price_adjustment_type']['options'] as $price_id => $price_name ) {
			if ( empty( $plugin_data[ 'price_adjustment_type_' . $price_id ] ) ) {
				$plugin_data[ 'price_adjustment_type_' . $price_id ] = 0;
			}
			if ( $price_id === $shipping_method->get_instance_option( 'price_adjustment_type', PriceAdjustmentNone::ADJUSTMENT_TYPE ) ) {
				$plugin_data[ 'price_adjustment_type_' . $price_id ]++;
			}
		}
		return $plugin_data;
	}

	/**
	 * Count delivery dates option.
	 *
	 * @param UpsProShippingMethod $shipping_method Shipping method.
	 * @param array                $instance_fields List of fields.
	 * @param array                $plugin_data     Plugin data.
	 *
	 * @return array
	 */
	private function delivery_dates_data( UpsProShippingMethod $shipping_method, array $instance_fields, array $plugin_data ) {
		foreach ( $instance_fields['delivery_dates']['options'] as $delivery_dates_id => $delivery_name ) {
			if ( empty( $plugin_data[ 'delivery_dates_' . $delivery_dates_id ] ) ) {
				$plugin_data[ 'delivery_dates_' . $delivery_dates_id ] = 0;
			}
			if ( $delivery_dates_id === $shipping_method->get_instance_option( 'delivery_dates', EstimatedDeliverySettingsDefinitionDecorator::OPTION_NONE ) ) {
				$plugin_data[ 'delivery_dates_' . $delivery_dates_id ]++;
			}
		}
		return $plugin_data;
	}

	/**
	 * Count delivery packing method option.
	 *
	 * @param UpsProShippingMethod $shipping_method Shipping method.
	 * @param array                $instance_fields List of fields.
	 * @param array                $plugin_data     Plugin data.
	 *
	 * @return array
	 */
	private function delivery_packing_method( UpsProShippingMethod $shipping_method, array $instance_fields, array $plugin_data ) {
		if ( isset( $instance_fields['packing_method'] ) ) {
			foreach ( $instance_fields['packing_method']['options'] as $packing_method_id => $packing_name ) {
				if ( empty( $plugin_data[ 'packing_method_' . $packing_method_id ] ) ) {
					$plugin_data[ 'packing_method_' . $packing_method_id ] = 0;
				}
				if ( $packing_method_id === $shipping_method->get_option( 'packing_method', PackerSettings::PACKING_METHOD_WEIGHT ) ) {
					$plugin_data[ 'packing_method_' . $packing_method_id ] ++;
				}
			}
		}
		return $plugin_data;
	}

	/**
	 * Count delivery packaging boxes option.
	 *
	 * @param UpsProShippingMethod $shipping_method Shipping method.
	 * @param array                $instance_fields List of fields.
	 * @param array                $plugin_data     Plugin data.
	 *
	 * @return array
	 */
	private function delivery_packing_boxes( UpsProShippingMethod $shipping_method, array $instance_fields, array $plugin_data ) {
		if ( PackerSettings::PACKING_METHOD_BOX === $shipping_method->get_option( 'packing_method', PackerSettings::PACKING_METHOD_BOX ) ) {
			$boxes = json_decode( $shipping_method->get_option( PackerSettings::OPTION_SHIPPING_BOXES, '[]' ), true );
			foreach ( $boxes as $box_setting ) {
				$tracker_data_key = 'packing_boxes_' . $box_setting['code'];
				if ( empty( $plugin_data[ $tracker_data_key ] ) ) {
					$plugin_data[ $tracker_data_key ] = 0;
				}
				$plugin_data[ $tracker_data_key ]++;
			}
		}
		return $plugin_data;
	}

	/**
	 * Count destination address type.
	 *
	 * @param UpsProShippingMethod $shipping_method Shipping method.
	 * @param array                $instance_fields List of fields.
	 * @param array                $plugin_data     Plugin data.
	 *
	 * @return array
	 */
	private function destination_address_type( UpsProShippingMethod $shipping_method, array $instance_fields, array $plugin_data ) {
		$setting_field = DestinationAddressTypeSettingsDefinitionDecorator::OPTION_DESTINATION_ADDRESS_TYPE;
		$default_value = DestinationAddressTypeSettingsDefinitionDecorator::DESTINATION_ADDRESS_TYPE_COMMERCIAL;
		return $this->count_select_options( $shipping_method, $instance_fields, $plugin_data, $setting_field, $default_value );
	}

	/**
	 * Count pickup type.
	 *
	 * @param UpsProShippingMethod $shipping_method Shipping method.
	 * @param array                $instance_fields List of fields.
	 * @param array                $plugin_data     Plugin data.
	 *
	 * @return array
	 */
	private function pickup_type( UpsProShippingMethod $shipping_method, array $instance_fields, array $plugin_data ) {
		$setting_field = PickupTypeSettingsDefinitionDecorator::PICKUP_TYPE;
		$default_value = PickupTypeSettingsDefinitionDecorator::DEFAULT_PICKUP_TYPE;
		return $this->count_select_options( $shipping_method, $instance_fields, $plugin_data, $setting_field, $default_value );
	}

	/**
	 * Count select options.
	 *
	 * @param UpsProShippingMethod $shipping_method .
	 * @param array                $instance_fields .
	 * @param array                $plugin_data .
	 * @param string               $setting_field .
	 * @param string               $default_value .
	 *
	 * @return array
	 */
	private function count_select_options(
		UpsProShippingMethod $shipping_method,
		array $instance_fields,
		array $plugin_data,
		$setting_field,
		$default_value
	) {
		foreach ( $instance_fields[ $setting_field ]['options'] as $option_value => $option_label ) {
			if ( empty( $plugin_data[ $setting_field . '_' . $option_value ] ) ) {
				$plugin_data[ $setting_field . '_' . $option_value ] = 0;
			}
			if ( $option_value === $shipping_method->get_instance_option( $setting_field, $default_value ) ) {
				$plugin_data[ $setting_field . '_' . $option_value ]++;
			}
		}
		return $plugin_data;
	}

	/**
	 * Is delivery dates option enabled for shipping method?
	 *
	 * @param UpsProShippingMethod $shipping_method .
	 *
	 * @return bool
	 */
	private function is_delivery_dates_enabled( UpsProShippingMethod $shipping_method ) {
		return $shipping_method->get_instance_option( EstimatedDeliverySettingsDefinitionDecorator::OPTION_DELIVERY_DATES, EstimatedDeliverySettingsDefinitionDecorator::OPTION_NONE )
			!== EstimatedDeliverySettingsDefinitionDecorator::OPTION_NONE;
	}

	/**
	 * Has blackout days?
	 *
	 * @param UpsProShippingMethod $shipping_method .
	 *
	 * @return bool
	 */
	private function has_blackout_days( UpsProShippingMethod $shipping_method ) {
		$blackout_days_settings = $shipping_method->get_instance_option( BlackoutLeadDaysSettingsDefinitionDecoratorFactory::OPTION_ID, array() );
		$blackout_days_settings = is_array( $blackout_days_settings ) ? $blackout_days_settings : array();
		return count( $blackout_days_settings ) > 0;
	}

	/**
	 * Count maximum time in transit.
	 *
	 * @param UpsProShippingMethod $shipping_method Shipping method.
	 * @param array                $instance_fields List of fields.
	 * @param array                $plugin_data     Plugin data.
	 *
	 * @return array
	 */
	private function maximum_transit_time( UpsProShippingMethod $shipping_method, array $instance_fields, array $plugin_data ) {
		$setting_field = MaximumTransitTimeSettingsDefinitionDecorator::OPTION_MAXIMUM_TRANSIT_TIME;
		if ( empty( $plugin_data[ $setting_field ] ) ) {
			$plugin_data[ $setting_field ] = 0;
		}
		if ( $this->is_delivery_dates_enabled( $shipping_method ) ) {
			if ( '' !== $shipping_method->get_instance_option( $setting_field, '' ) ) {
				$plugin_data[ $setting_field ]++;
			}
		}
		return $plugin_data;
	}

	/**
	 * Count lead time.
	 *
	 * @param UpsProShippingMethod $shipping_method Shipping method.
	 * @param array                $instance_fields List of fields.
	 * @param array                $plugin_data     Plugin data.
	 *
	 * @return array
	 */
	private function lead_time( UpsProShippingMethod $shipping_method, array $instance_fields, array $plugin_data ) {
		$setting_field = LeadTimeSettingsDefinitionDecorator::OPTION_LEAD_TIME;
		if ( empty( $plugin_data[ $setting_field ] ) ) {
			$plugin_data[ $setting_field ] = 0;
		}
		if ( $this->is_delivery_dates_enabled( $shipping_method ) ) {
			if ( 0 !== intval( $shipping_method->get_instance_option( $setting_field, '0' ) ) ) {
				$plugin_data[ $setting_field ]++;
			}
		}
		return $plugin_data;
	}

	/**
	 * Count lead blackout days.
	 *
	 * @param UpsProShippingMethod $shipping_method Shipping method.
	 * @param array                $instance_fields List of fields.
	 * @param array                $plugin_data     Plugin data.
	 *
	 * @return array
	 */
	private function lead_blackout_days( UpsProShippingMethod $shipping_method, array $instance_fields, array $plugin_data ) {
		$setting_field = BlackoutLeadDaysSettingsDefinitionDecoratorFactory::OPTION_ID;
		if ( empty( $plugin_data[ $setting_field ] ) ) {
			$plugin_data[ $setting_field ] = 0;
		}
		if ( $this->has_blackout_days( $shipping_method ) ) {
			$plugin_data[ $setting_field ]++;
		}

		return $plugin_data;
	}

	/**
	 * Count cutoff time.
	 *
	 * @param UpsProShippingMethod $shipping_method Shipping method.
	 * @param array                $instance_fields List of fields.
	 * @param array                $plugin_data     Plugin data.
	 *
	 * @return array
	 */
	private function cutoff_time( UpsProShippingMethod $shipping_method, array $instance_fields, array $plugin_data ) {
		$setting_field = CutoffTimeSettingsDefinitionDecorator::OPTION_CUTOFF_TIME;
		if ( empty( $plugin_data[ $setting_field ] ) ) {
			$plugin_data[ $setting_field ] = 0;
		}
		if ( $this->is_delivery_dates_enabled( $shipping_method ) ) {
			if ( '' !== intval( $shipping_method->get_instance_option( $setting_field, '' ) ) ) {
				$plugin_data[ $setting_field ]++;
			}
		}
		return $plugin_data;
	}

	/**
	 * Add plugin data tracker.
	 *
	 * @param array $data Data.
	 *
	 * @return array
	 */
	public function wpdesk_tracker_data( array $data ) {
		$shipping_methods = WC()->shipping()->get_shipping_methods();

		if ( isset( $shipping_methods['flexible_shipping_ups'] ) ) {
			/**
			 * IDE type hint.
			 *
			 * @var UpsProShippingMethod $flexible_shipping_ups
			 */

			$plugin_data = array();

			$shipping_zones    = \WC_Shipping_Zones::get_zones();
			$shipping_zones[0] = array( 'zone_id' => 0 );
			/**
			 * IDE type hint.
			 *
			 * @var \WC_Shipping_Zone $zone
			 */
			foreach ( $shipping_zones as $zone_data ) {
				$zone             = new \WC_Shipping_Zone( $zone_data['zone_id'] );
				$shipping_methods = $zone->get_shipping_methods( true );
				/**
				 * IDE type hint.
				 *
				 * @var UpsProShippingMethod $shipping_method
				 */
				foreach ( $shipping_methods as $shipping_method ) {
					if ( 'flexible_shipping_ups' === $shipping_method->id ) {
						$plugin_data = $this->append_data_for_shipping_method( $plugin_data, $shipping_method );
					}
				}
			}

			$data['flexible_shipping_ups'] = array_merge( $data['flexible_shipping_ups'], $plugin_data );
		}

		return $data;
	}

	/**
	 * Notice screens.
	 *
	 * @param array $screens .
	 *
	 * @return array
	 */
	public function wpdesk_tracker_notice_screens( $screens ) {
		$current_screen = get_current_screen();
		if ( 'woocommerce_page_wc-settings' === $current_screen->id ) {
			if ( isset( $_GET['tab'] ) && 'shipping' === $_GET['tab'] && isset( $_GET['section'] ) && 'flexible_shipping_ups' === $_GET['section'] ) {
				$screens[] = $current_screen->id;
			}
		}

		return $screens;
	}

}
