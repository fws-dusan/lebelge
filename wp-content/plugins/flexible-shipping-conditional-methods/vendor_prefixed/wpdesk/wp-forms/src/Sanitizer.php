<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms;

interface Sanitizer
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function sanitize($value);
}
