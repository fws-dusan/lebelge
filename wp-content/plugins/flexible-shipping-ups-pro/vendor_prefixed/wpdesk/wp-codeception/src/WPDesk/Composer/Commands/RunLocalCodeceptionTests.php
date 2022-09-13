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
class RunLocalCodeceptionTests extends \UpsProVendor\WPDesk\Composer\Codeception\Commands\RunCodeceptionTests
{
    use LocalCodeceptionTrait;
    /**
     * Configure command.
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('run-local-codeception-tests')->setDescription('Run local codeception tests.')->setDefinition(array(new \UpsProVendor\Symfony\Component\Console\Input\InputArgument(self::SINGLE, \UpsProVendor\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Name of Single test to run.', ' ')));
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
        $this->prepareWpConfig($output, $configuration);
        $singleTest = $input->getArgument(self::SINGLE);
        $sep = \DIRECTORY_SEPARATOR;
        $codecept = "vendor{$sep}bin{$sep}codecept";
        $cleanOutput = $codecept . ' clean';
        $this->execAndOutput($cleanOutput, $output);
        $runLocalTests = $codecept . ' run -f --steps --html --verbose acceptance ' . $singleTest;
        $this->execAndOutput($runLocalTests, $output);
    }
}
