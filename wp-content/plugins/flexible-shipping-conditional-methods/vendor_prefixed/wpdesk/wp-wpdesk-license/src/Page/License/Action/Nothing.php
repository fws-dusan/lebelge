<?php

namespace FSConditionalMethodsVendor\WPDesk\License\Page\License\Action;

use FSConditionalMethodsVendor\WPDesk\License\Page\Action;
/**
 * Do nothing.
 *
 * @package WPDesk\License\Page\License\Action
 */
class Nothing implements \FSConditionalMethodsVendor\WPDesk\License\Page\Action
{
    public function execute(array $plugin)
    {
        // NOOP
    }
}
