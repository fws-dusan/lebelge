<?php
/**
 * Plugin Update Checker Library 4.4
 * http://w-shadow.com/
 *
 * Copyright 2017 Janis Elsts
 * Released under the MIT license. See license.txt for details.
 */

require dirname(__FILE__) . '/Puc/v4p4/Factory.php';
require dirname(__FILE__) . '/Puc/v4/Factory.php';
require dirname(__FILE__) . '/Puc/Autoloader.php';
new WooMail_Puc_v4p4_Autoloader();


WooMail_Puc_v4_Factory::addVersion('Plugin_UpdateChecker', 'WooMail_Puc_v4p4_Plugin_UpdateChecker', '4.4');