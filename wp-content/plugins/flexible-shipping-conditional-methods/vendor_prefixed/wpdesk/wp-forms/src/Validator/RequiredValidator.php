<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms\Validator;

use FSConditionalMethodsVendor\WPDesk\Forms\Validator;
class RequiredValidator implements \FSConditionalMethodsVendor\WPDesk\Forms\Validator
{
    public function is_valid($value)
    {
        return $value !== null;
    }
    public function get_messages()
    {
        return [];
    }
}
