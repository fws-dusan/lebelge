<?php

namespace UpsProVendor\WPDesk\Composer\Codeception\Commands;

use UpsProVendor\Composer\Command\BaseCommand as CodeceptionBaseCommand;
use UpsProVendor\Symfony\Component\Console\Output\OutputInterface;
/**
 * Base for commands - declares common methods.
 *
 * @package WPDesk\Composer\Codeception\Commands
 */
abstract class BaseCommand extends \UpsProVendor\Composer\Command\BaseCommand
{
    /**
     * @param string $command
     * @param OutputInterface $output
     */
    protected function execAndOutput($command, \UpsProVendor\Symfony\Component\Console\Output\OutputInterface $output)
    {
        \passthru($command);
    }
}
