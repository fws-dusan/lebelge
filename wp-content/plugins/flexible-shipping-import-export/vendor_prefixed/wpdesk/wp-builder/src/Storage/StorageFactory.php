<?php

namespace FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Storage;

class StorageFactory
{
    /**
     * @return PluginStorage
     */
    public function create_storage()
    {
        return new \FlexibleShippingImportExportVendor\WPDesk\PluginBuilder\Storage\WordpressFilterStorage();
    }
}
