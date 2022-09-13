<?php
/**
 * Class UnknownActionException
 *
 * @package WPDesk\FS\ConditionalMethods\Actions\Exception
 */

namespace WPDesk\FS\ConditionalMethods\Actions\Exception;

use Throwable;

/**
 * UnknownActionException
 */
class UnknownActionException extends \RuntimeException {

	/**
	 * UnknownActionException constructor.
	 *
	 * @param int $action_id .
	 */
	public function __construct( $action_id ) {
		// Translators: %1$s action_id.
		parent::__construct( sprintf( __( 'Unknown action: %1$s', 'flexible-shipping-conditional-methods' ), $action_id ) );
	}

}
