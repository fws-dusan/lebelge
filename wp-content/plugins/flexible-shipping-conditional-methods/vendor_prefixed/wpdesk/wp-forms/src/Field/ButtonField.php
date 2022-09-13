<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms\Field;

class ButtonField extends \FSConditionalMethodsVendor\WPDesk\Forms\Field\NoValueField
{
    public function get_template_name()
    {
        return 'button';
    }
    public function get_type()
    {
        return 'button';
    }
}
