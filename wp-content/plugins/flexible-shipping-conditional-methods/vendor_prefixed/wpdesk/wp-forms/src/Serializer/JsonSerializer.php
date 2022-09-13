<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms\Serializer;

use FSConditionalMethodsVendor\WPDesk\Forms\Serializer;
class JsonSerializer implements \FSConditionalMethodsVendor\WPDesk\Forms\Serializer
{
    public function serialize($value)
    {
        return \json_encode($value);
    }
    public function unserialize($value)
    {
        return \json_decode($value, \true);
    }
}
