<?php

if (!defined('ABSPATH')) {
    exit;
}
require_once(EC_WOO_BUILDER_PATH . '/includes/validation.php');
require_once(EC_WOO_BUILDER_PATH . '/includes/helper/init.php');
require_once(EC_WOO_BUILDER_PATH . '/includes/ajax.php');
require_once(EC_WOO_BUILDER_PATH . '/includes/compatibilityChecking.php');
require_once(EC_WOO_BUILDER_PATH . '/includes/helper.php');
require_once(EC_WOO_BUILDER_PATH . '/includes/generalSettings.php');
require_once(EC_WOO_BUILDER_PATH . '/includes/customShortcode.php');
require_once(EC_WOO_BUILDER_PATH . '/includes/integrations/init.php');
//TODO:Remove email-core
// require_once(EC_WOO_BUILDER_PATH . '/includes/email-core.class.php');
require_once(EC_WOO_BUILDER_PATH . '/includes/emailCore.php');
require_once(EC_WOO_BUILDER_PATH . '/includes/registerPost.php');
require_once(EC_WOO_BUILDER_PATH . '/includes/registerWpFilters.php');
require_once(EC_WOO_BUILDER_PATH . '/includes/registerWpActions.php');