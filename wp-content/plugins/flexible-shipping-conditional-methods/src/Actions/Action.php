<?php
/**
 * Interface Action
 *
 * @package WPDesk\FS\ConditionalMethods\Actions
 */

namespace WPDesk\FS\ConditionalMethods\Actions;

/**
 * Action
 */
interface Action {

	/**
	 * @param array<string, string|array> $action_settings .
	 * @param array[]                     $package .
	 *
	 * @return array[]
	 */
	public function execute_action( array $action_settings, array $package );

}
