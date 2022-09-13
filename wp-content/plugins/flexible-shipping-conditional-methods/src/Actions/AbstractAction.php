<?php
/**
 * Class AbstractAction
 *
 * @package WPDesk\FS\ConditionalMethods\Actions
 */

namespace WPDesk\FS\ConditionalMethods\Actions;

use WPDesk\FS\ConditionalMethods\ConditionalForm\AbstractOptionField;

/**
 * Abstract Action
 */
abstract class AbstractAction extends AbstractOptionField implements Action {

	/**
	 * @param array<string, string|array> $action_settings .
	 * @param array[]                     $package .
	 *
	 * @return array[]
	 */
	abstract public function execute_action( array $action_settings, array $package );

}
