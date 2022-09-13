<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms\Field;

use FSConditionalMethodsVendor\WPDesk\Forms\Sanitizer\TextFieldSanitizer;
class InputNumberField extends \FSConditionalMethodsVendor\WPDesk\Forms\Field\BasicField
{
    public function __construct()
    {
        parent::__construct();
        $this->set_default_value('');
        $this->set_attribute('type', 'number');
    }
    public function get_sanitizer()
    {
        return new \FSConditionalMethodsVendor\WPDesk\Forms\Sanitizer\TextFieldSanitizer();
    }
    public function get_template_name()
    {
        return 'input-number';
    }
}
