<?php

namespace FlexibleShippingImportExportVendor\WPDesk\License\Page;

use FlexibleShippingImportExportVendor\WPDesk\License\Page\License\Action\LicenseActivation;
use FlexibleShippingImportExportVendor\WPDesk\License\Page\License\Action\LicenseDeactivation;
use FlexibleShippingImportExportVendor\WPDesk\License\Page\License\Action\Nothing;
/**
 * Action factory.
 *
 * @package WPDesk\License\Page\License
 */
class LicensePageActions
{
    /**
     * Creates action object according to given param
     *
     * @param string $action
     *
     * @return Action
     */
    public function create_action($action)
    {
        if ($action === 'activate') {
            return new \FlexibleShippingImportExportVendor\WPDesk\License\Page\License\Action\LicenseActivation();
        }
        if ($action === 'deactivate') {
            return new \FlexibleShippingImportExportVendor\WPDesk\License\Page\License\Action\LicenseDeactivation();
        }
        return new \FlexibleShippingImportExportVendor\WPDesk\License\Page\License\Action\Nothing();
    }
}
