<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms\Field;

class Header extends \FSConditionalMethodsVendor\WPDesk\Forms\Field\NoValueField
{
    public function __construct()
    {
        parent::__construct();
        $this->meta['header_size'] = '';
    }
    public function get_template_name()
    {
        return 'header';
    }
    public function should_override_form_template()
    {
        return \true;
    }
    public function set_header_size($value)
    {
        $this->meta['header_size'] = $value;
        return $this;
    }
}
