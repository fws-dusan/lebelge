<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms\Sanitizer;

use FSConditionalMethodsVendor\WPDesk\Forms\Sanitizer;
class NoSanitize implements \FSConditionalMethodsVendor\WPDesk\Forms\Sanitizer
{
    public function sanitize($value)
    {
        return $value;
    }
}
