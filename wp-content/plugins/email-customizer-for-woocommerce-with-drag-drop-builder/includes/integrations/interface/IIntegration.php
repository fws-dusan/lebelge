<?php

if (!defined('ABSPATH')) {
    exit;
}

interface IWooMailIntegration
{
    /*
    * name of integrated plugin
    */
    public function getName();


    /*
    * collect data regarding integration
    */
    public function collectData();
}