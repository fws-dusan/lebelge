<?php

namespace UpsProVendor\WPDesk\PluginBuilder\Storage;

use UpsProVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
/**
 * Can store plugin instances in static variable
 *
 * @package WPDesk\PluginBuilder\Storage
 */
class StaticStorage implements \UpsProVendor\WPDesk\PluginBuilder\Storage\PluginStorage
{
    protected static $instances = [];
    /**
     * @param string $class
     * @param AbstractPlugin $object
     */
    public function add_to_storage($class, $object)
    {
        if (isset(self::$instances[$class])) {
            throw new \UpsProVendor\WPDesk\PluginBuilder\Storage\Exception\ClassAlreadyExists("Class {$class} already exists");
        }
        self::$instances[$class] = $object;
    }
    /**
     * @param string $class
     *
     * @return AbstractPlugin
     */
    public function get_from_storage($class)
    {
        if (isset(self::$instances[$class])) {
            return self::$instances[$class];
        }
        throw new \UpsProVendor\WPDesk\PluginBuilder\Storage\Exception\ClassNotExists("Class {$class} not exists in storage");
    }
}
