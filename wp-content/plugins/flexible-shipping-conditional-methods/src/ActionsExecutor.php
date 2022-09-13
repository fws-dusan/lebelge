<?php
/**
 * Class ActionsExecutor
 *
 * @package WPDesk\FS\ConditionalMethods
 */

namespace WPDesk\FS\ConditionalMethods;

use WPDesk\FS\ConditionalMethods\Actions\Action;
use WPDesk\FS\ConditionalMethods\Actions\Exception\UnknownActionException;

/**
 * Can execute actions.
 */
class ActionsExecutor {

	/**
	 * @var Action[]
	 */
	private $available_actions;

	/**
	 * ActionsExecutor constructor.
	 *
	 * @param Action[] $available_actions .
	 */
	public function __construct( array $available_actions ) {
		$this->available_actions = $available_actions;
	}

	/**
	 * @param array[] $actions_settings .
	 * @param array[] $package .
	 *
	 * @return array[]
	 */
	public function execute_actions( array $actions_settings, array $package ) {
		foreach ( $actions_settings as $single_action_setting ) {
			$action = $this->get_action_from_available_actions( $single_action_setting['option_id'] );
			$package = $action->execute_action( $single_action_setting, $package );
		}

		return $package;
	}

	/**
	 * @param int $action_id .
	 *
	 * @return Action
	 * @throws UnknownActionException .
	 */
	private function get_action_from_available_actions( $action_id ) {
		if ( isset( $this->available_actions[ $action_id ] ) ) {
			return $this->available_actions[ $action_id ];
		}

		throw new UnknownActionException( $action_id );
	}

}
