<?php

namespace UpsProVendor\WPDesk\Composer\Codeception\Commands;

use UpsProVendor\Symfony\Component\Console\Input\InputArgument;
use UpsProVendor\Symfony\Component\Console\Input\InputInterface;
use UpsProVendor\Symfony\Component\Console\Output\OutputInterface;
use UpsProVendor\Symfony\Component\Yaml\Exception\ParseException;
use UpsProVendor\Symfony\Component\Yaml\Yaml;
/**
 * Prepare Database for Codeception tests command.
 *
 * @package WPDesk\Composer\Codeception\Commands
 */
class PrepareCodeceptionDb extends \UpsProVendor\WPDesk\Composer\Codeception\Commands\BaseCommand
{
    use LocalCodeceptionTrait;
    /**
     * Configure command.
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('prepare-codeception-db')->setDescription('Prepare codeception database.');
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
        $this->prepareCommonWpWcConfiguration($configuration, $output);
        $this->prepareWpConfig($output, $configuration);
        $this->executeWpCliAndOutput('plugin activate ' . $configuration->getPluginSlug(), $output, $configuration->getApacheDocumentRoot());
        $this->activatePlugins($output, $configuration);
        $this->executeWpCliAndOutput('db export ' . \getcwd() . '/tests/codeception/tests/_data/db.sql', $output, $configuration->getApacheDocumentRoot());
    }
}
