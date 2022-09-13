<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Main;

class Astra extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'ASTRA_THEME_VERSION' );
	}

	public function run() {
		$this->remove_astra_scripts();
	}

	public function remove_scripts( array $scripts ): array {
		// This prevents basically all Astra Add-on scripts from loading
		$scripts['astra-addon-js'] = 'astra-addon-js';

		return $scripts;
	}

	public function remove_astra_scripts() {
		if ( cfw_is_checkout() ) {
			remove_all_actions( 'astra_get_js_files' );
		}
	}
}