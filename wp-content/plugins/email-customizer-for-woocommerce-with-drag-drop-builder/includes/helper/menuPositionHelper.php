<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper class
 */
class Helper_Menu_Position
{
    private $option_name;
    private $default_value;

    public function __construct()
    {
        $this->option_name='ec_woo_settings_menu';
        $this->default_value='left';
    }
    /*
    * Get menu position
    */
    public function get()
    {
        return get_option($this->option_name, '');
    }
    /*
    * Update menu positoin
    */
    public function update($value)
    {
        if (isset($value)) {
            update_option($this->option_name, $value);
            return 0;
        } else {
            return -1;
        }
    }
    /*
    * Add menu positoin if it is empty
    */
    public function add()
    {
        $option = $this->get();
        if ($option == '') {
            add_option($this->option_name, $this->default_value);
        }
    }
}