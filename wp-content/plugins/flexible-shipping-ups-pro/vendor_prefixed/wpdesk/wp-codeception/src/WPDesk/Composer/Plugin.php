<?php

namespace UpsProVendor\WPDesk\Composer\Codeception;

use UpsProVendor\Composer\Composer;
use UpsProVendor\Composer\IO\IOInterface;
use UpsProVendor\Composer\Plugin\Capable;
use UpsProVendor\Composer\Plugin\PluginInterface;
/**
 * Composer plugin.
 *
 * @package WPDesk\Composer\Codeception
 */
class Plugin implements \UpsProVendor\Composer\Plugin\PluginInterface, \UpsProVendor\Composer\Plugin\Capable
{
    /**
     * @var Composer
     */
    private $composer;
    /**
     * @var IOInterface
     */
    private $io;
    public function activate(\UpsProVendor\Composer\Composer $composer, \UpsProVendor\Composer\IO\IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }
    /**
     * @inheritDoc
     */
    public function deactivate(\UpsProVendor\Composer\Composer $composer, \UpsProVendor\Composer\IO\IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }
    /**
     * @inheritDoc
     */
    public function uninstall(\UpsProVendor\Composer\Composer $composer, \UpsProVendor\Composer\IO\IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }
    public function getCapabilities()
    {
        return [\UpsProVendor\Composer\Plugin\Capability\CommandProvider::class => \UpsProVendor\WPDesk\Composer\Codeception\CommandProvider::class];
    }
}
