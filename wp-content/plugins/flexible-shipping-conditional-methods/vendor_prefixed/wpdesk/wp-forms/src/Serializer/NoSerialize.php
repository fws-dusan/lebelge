<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms\Serializer;

use FSConditionalMethodsVendor\WPDesk\Forms\Serializer;
class NoSerialize implements \FSConditionalMethodsVendor\WPDesk\Forms\Serializer
{
    public function serialize($value)
    {
        return $value;
    }
    public function unserialize($value)
    {
        return $value;
    }
}
