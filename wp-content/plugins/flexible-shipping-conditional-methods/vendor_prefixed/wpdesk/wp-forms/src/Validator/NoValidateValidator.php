<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms\Validator;

use FSConditionalMethodsVendor\WPDesk\Forms\Validator;
class NoValidateValidator implements \FSConditionalMethodsVendor\WPDesk\Forms\Validator
{
    public function is_valid($value)
    {
        return \true;
    }
    public function get_messages()
    {
        return [];
    }
}
