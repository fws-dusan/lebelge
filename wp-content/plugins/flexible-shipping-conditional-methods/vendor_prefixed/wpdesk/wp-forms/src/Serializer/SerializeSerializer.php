<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms\Serializer;

use FSConditionalMethodsVendor\WPDesk\Forms\Serializer;
class SerializeSerializer implements \FSConditionalMethodsVendor\WPDesk\Forms\Serializer
{
    public function serialize($value)
    {
        return \serialize($value);
    }
    public function unserialize($value)
    {
        return \unserialize($value);
    }
}
