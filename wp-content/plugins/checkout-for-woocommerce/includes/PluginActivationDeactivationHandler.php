<?php

namespace Objectiv\Plugins\Checkout;

use Objectiv\Plugins\Checkout\Interfaces\RunsOnPluginActivationInterface;
use Objectiv\Plugins\Checkout\Interfaces\RunsOnPluginDeactivationInterface;

class PluginActivationDeactivationHandler {
	/**
	 * @var RunsOnPluginActivationInterface[]
	 */
	protected $activation_runners = array();

	/**
	 * @var RunsOnPluginDeactivationInterface[]
	 */
	protected $deactivation_runners = array();

	public function add_activation_handler( RunsOnPluginActivationInterface $runner ) {
		$this->activation_runners[] = $runner;
	}


	public function add_deactivation_handler( RunsOnPluginDeactivationInterface $runner ) {
		$this->deactivation_runners[] = $runner;
	}


	public function activate() {
		foreach ( $this->activation_runners as $runner ) {
			$runner->run_on_plugin_activation();
		}
	}

	public function deactivate() {
		foreach ( $this->deactivation_runners as $runner ) {
			$runner->run_on_plugin_deactivation();
		}
	}
}
