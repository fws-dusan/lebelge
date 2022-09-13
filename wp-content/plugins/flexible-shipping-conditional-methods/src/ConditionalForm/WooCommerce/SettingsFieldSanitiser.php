<?php
/**
 * Class SettingsFieldSanitiser
 *
 * @package WPDesk\FS\ConditionalMethods\ConditionalForm\WooCommerce
 */

namespace WPDesk\FS\ConditionalMethods\ConditionalForm\WooCommerce;

use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Can sanitize field data.
 */
class SettingsFieldSanitiser implements Hookable {

	/**
	 * .
	 */
	public function hooks() {
		add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'sanitize_field_value' ), 10, 3 );
	}

	/**
	 * @param array[]|mixed $value .
	 * @param array[]       $option .
	 * @param array[]       $raw_value .
	 *
	 * @return array[]
	 * @internal
	 */
	public function sanitize_field_value( $value, $option, $raw_value ) {
		// @phpstan-ignore-next-line
		if ( is_array( $option ) && isset( $option['type'] ) && SettingsField::FIELD_TYPE === $option['type'] ) {
			if ( is_array( $value ) ) {
				$value = $this->sanitize_input_data( $value );
				$value = $this->remove_unwanted_or_conditions( $value );
			} else {
				$value = array();
			}
		}

		return $value;
	}

	/**
	 * @param array<string, string|array> $value .
	 *
	 * @return array<string, string|array>
	 */
	private function sanitize_input_data( array $value ) {
		foreach ( $value as $key => $data ) {
			if ( is_array( $data ) ) {
				$value[ $key ] = $this->sanitize_input_data( $data );
			} else {
				$value[ $key ] = sanitize_text_field( wp_unslash( $data ) );
			}
		}

		return $value;
	}

	/**
	 * @param array<string, array|string> $value .
	 *
	 * @return array<string, array|string>
	 */
	private function remove_unwanted_or_conditions( array $value ) {
		$previous_row_type = 'or';
		foreach ( $value as $key => $row ) {
			if ( is_array( $row ) && isset( $row['type'] ) ) {
				if ( 'or' === $row['type'] && 'or' === $previous_row_type ) {
					unset( $value[ $key ] );
				}
				$previous_row_type = $row['type'];
			} else {
				unset( $value[ $key ] );
			}
		}

		/** @var array<string, string> $row */
		$row = end( $value );
		$key = key( $value );
		if ( $row && 'or' === $row['type'] ) {
			unset( $value[ $key ] );
		}

		return $value;
	}

}
