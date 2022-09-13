<?php
/**
 * Class UnknownConditionException
 *
 * @package WPDesk\FS\TableRate\ImportExport\Exception
 */

namespace WPDesk\FS\TableRate\ImportExport\Exception;

use RuntimeException;
use Throwable;

/**
 * Unknown Condition exception.
 */
class UnknownConditionException extends RuntimeException {

	/**
	 * @var string
	 */
	private $condition_id;

	/**
	 * UnknownConditionException constructor.
	 *
	 * @param string $message .
	 * @param string $condition_id .
	 */
	public function __construct( $message, $condition_id ) {
		parent::__construct( $message );
		$this->condition_id = $condition_id;
	}

	/**
	 * @return string
	 */
	public function get_condition_id() {
		return $this->condition_id;
	}

}
