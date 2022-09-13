<?php

namespace UpsProVendor\WPDesk\PluginBuilder\Storage;

class StorageFactory
{
    /**
     * @return PluginStorage
     */
    public function create_storage()
    {
        return new \UpsProVendor\WPDesk\PluginBuilder\Storage\WordpressFilterStorage();
    }
}
