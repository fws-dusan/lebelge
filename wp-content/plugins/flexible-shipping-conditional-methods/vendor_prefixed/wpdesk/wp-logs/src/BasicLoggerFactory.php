<?php

namespace FSConditionalMethodsVendor\WPDesk\Logger;

use FSConditionalMethodsVendor\Monolog\Handler\HandlerInterface;
use FSConditionalMethodsVendor\Monolog\Logger;
use FSConditionalMethodsVendor\Monolog\Registry;
/**
 * Manages and facilitates creation of logger
 *
 * @package WPDesk\Logger
 */
class BasicLoggerFactory implements \FSConditionalMethodsVendor\WPDesk\Logger\LoggerFactory
{
    /** @var string Last created logger name/channel */
    private static $lastLoggerChannel;
    /**
     * Creates logger for plugin
     *
     * @param string $name The logging channel/name of logger
     * @param HandlerInterface[] $handlers Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[] $processors Optional array of processors
     * @return Logger
     */
    public function createLogger($name, $handlers = array(), array $processors = array())
    {
        if (\FSConditionalMethodsVendor\Monolog\Registry::hasLogger($name)) {
            return \FSConditionalMethodsVendor\Monolog\Registry::getInstance($name);
        }
        self::$lastLoggerChannel = $name;
        $logger = new \FSConditionalMethodsVendor\Monolog\Logger($name, $handlers, $processors);
        \FSConditionalMethodsVendor\Monolog\Registry::addLogger($logger);
        return $logger;
    }
    /**
     * Returns created Logger by name or last created logger
     *
     * @param string $name Name of the logger
     *
     * @return Logger
     */
    public function getLogger($name = null)
    {
        if ($name === null) {
            $name = self::$lastLoggerChannel;
        }
        return \FSConditionalMethodsVendor\Monolog\Registry::getInstance($name);
    }
}
