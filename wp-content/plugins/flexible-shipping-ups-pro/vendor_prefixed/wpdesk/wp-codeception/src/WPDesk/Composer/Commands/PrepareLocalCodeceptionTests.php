<?php

namespace UpsProVendor\WPDesk\Composer\Codeception\Commands;

use UpsProVendor\Symfony\Component\Console\Input\InputArgument;
use UpsProVendor\Symfony\Component\Console\Input\InputInterface;
use UpsProVendor\Symfony\Component\Console\Output\OutputInterface;
use UpsProVendor\Symfony\Component\Yaml\Exception\ParseException;
use UpsProVendor\Symfony\Component\Yaml\Yaml;
/**
 * Codeception tests run command.
 *
 * @package WPDesk\Composer\Codeception\Commands
 */
class PrepareLocalCodeceptionTests extends \UpsProVendor\WPDesk\Composer\Codeception\Commands\RunCodeceptionTests
{
    use LocalCodeceptionTrait;
    /**
     * Configure command.
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('prepare-local-codeception-tests')->setDescription('Prepare local codeception tests.');
    }
    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(\UpsProVendor\Symfony\Component\Console\Input\InputInterface $input, \UpsProVendor\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $configuration = $this->getWpDeskConfiguration();
        $this->installPlugin($configuration->getPluginSlug(), $output, $configuration);
        $this->activatePlugins($output, $configuration);
        $this->prepareWpConfig($output, $configuration);
        $sep = \DIRECTORY_SEPARATOR;
        $codecept = "vendor{$sep}bin{$sep}codecept";
        $cleanOutput = $codecept . ' clean';
        $this->execAndOutput($cleanOutput, $output);
    }
}
