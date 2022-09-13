<?php

namespace FlexibleShippingImportExportVendor;

require_once __DIR__ . '/vendor/autoload.php';
if (!\class_exists('FlexibleShippingImportExportVendor\\WPDesk\\Notice\\AjaxHandler')) {
    require_once __DIR__ . '/src/WPDesk/Notice/AjaxHandler.php';
}
if (!\class_exists('FlexibleShippingImportExportVendor\\WPDesk\\Notice\\Notice')) {
    require_once __DIR__ . 'src/WPDesk/Notice/Notice.php';
}
if (!\class_exists('FlexibleShippingImportExportVendor\\WPDesk\\Notice\\PermanentDismissibleNotice')) {
    require_once __DIR__ . '/src/WPDesk/Notice/PermanentDismissibleNotice.php';
}
if (!\class_exists('FlexibleShippingImportExportVendor\\WPDesk\\Notice\\Factory')) {
    require_once __DIR__ . '/src/WPDesk/Notice/Factory.php';
}
require_once __DIR__ . '/src/WPDesk/notice-functions.php';
