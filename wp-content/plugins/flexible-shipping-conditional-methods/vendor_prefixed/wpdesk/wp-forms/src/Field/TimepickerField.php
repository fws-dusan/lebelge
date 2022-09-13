<?php

namespace FSConditionalMethodsVendor\WPDesk\Forms\Field;

class TimepickerField extends \FSConditionalMethodsVendor\WPDesk\Forms\Field\BasicField
{
    /**
     * @inheritDoc
     */
    public function get_template_name()
    {
        return 'timepicker';
    }
}
