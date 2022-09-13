<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Main;

class Atelier extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'sf_custom_styles' );
	}

	public function run() {
		$this->wp();
	}

	function wp() {
		if ( cfw_is_checkout() ) {
			remove_action( 'wp_head', 'sf_custom_styles' );
		}
	}
}