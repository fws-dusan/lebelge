<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms\Sanitizer;

use FSConditionalMethodsVendor\WPDesk\Forms\Sanitizer;
class TextFieldSanitizer implements \FSConditionalMethodsVendor\WPDesk\Forms\Sanitizer
{
    public function sanitize($value)
    {
        return \sanitize_text_field($value);
    }
}
