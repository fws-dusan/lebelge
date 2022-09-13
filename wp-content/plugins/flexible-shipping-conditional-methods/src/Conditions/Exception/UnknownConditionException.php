<?php
/**
 * Class UnknownConditionException
 *
 * @package WPDesk\FS\ConditionalMethods\Conditions\Exception
 */

namespace WPDesk\FS\ConditionalMethods\Conditions\Exception;

/**
 * Unknown condition exception.
 */
class UnknownConditionException extends \RuntimeException {

	/**
	 * UnknownConditionException constructor.
	 *
	 * @param int $condition_id .
	 */
	public function __construct( $condition_id ) {
		// Translators: %1$s condition id.
		parent::__construct( sprintf( __( 'Unknown condition: %1$s', 'flexible-shipping-conditional-methods' ), $condition_id ) );
	}

}
