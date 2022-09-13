<?php

namespace UpsProVendor\WPDesk\Composer\Codeception;

use UpsProVendor\WPDesk\Composer\Codeception\Commands\CreateCodeceptionTests;
use UpsProVendor\WPDesk\Composer\Codeception\Commands\PrepareCodeceptionDb;
use UpsProVendor\WPDesk\Composer\Codeception\Commands\PrepareLocalCodeceptionTests;
use UpsProVendor\WPDesk\Composer\Codeception\Commands\PrepareParallelCodeceptionTests;
use UpsProVendor\WPDesk\Composer\Codeception\Commands\PrepareWordpressForCodeception;
use UpsProVendor\WPDesk\Composer\Codeception\Commands\RunCodeceptionTests;
use UpsProVendor\WPDesk\Composer\Codeception\Commands\RunLocalCodeceptionTests;
/**
 * Links plugin commands handlers to composer.
 */
class CommandProvider implements \UpsProVendor\Composer\Plugin\Capability\CommandProvider
{
    public function getCommands()
    {
        return [new \UpsProVendor\WPDesk\Composer\Codeception\Commands\CreateCodeceptionTests(), new \UpsProVendor\WPDesk\Composer\Codeception\Commands\RunCodeceptionTests(), new \UpsProVendor\WPDesk\Composer\Codeception\Commands\RunLocalCodeceptionTests(), new \UpsProVendor\WPDesk\Composer\Codeception\Commands\PrepareCodeceptionDb(), new \UpsProVendor\WPDesk\Composer\Codeception\Commands\PrepareWordpressForCodeception(), new \UpsProVendor\WPDesk\Composer\Codeception\Commands\PrepareLocalCodeceptionTests(), new \UpsProVendor\WPDesk\Composer\Codeception\Commands\PrepareParallelCodeceptionTests()];
    }
}
