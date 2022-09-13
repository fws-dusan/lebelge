<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms\Sanitizer;

use FSConditionalMethodsVendor\WPDesk\Forms\Sanitizer;
class CallableSanitizer implements \FSConditionalMethodsVendor\WPDesk\Forms\Sanitizer
{
    private $callable;
    public function __construct($callable)
    {
        $this->callable = $callable;
    }
    public function sanitize($value)
    {
        return \call_user_func($this->callable, $value);
    }
}
