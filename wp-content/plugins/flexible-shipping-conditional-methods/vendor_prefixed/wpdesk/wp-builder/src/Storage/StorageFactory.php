<?php

namespace FSConditionalMethodsVendor\WPDesk\PluginBuilder\Storage;

class StorageFactory
{
    /**
     * @return PluginStorage
     */
    public function create_storage()
    {
        return new \FSConditionalMethodsVendor\WPDesk\PluginBuilder\Storage\WordpressFilterStorage();
    }
}
