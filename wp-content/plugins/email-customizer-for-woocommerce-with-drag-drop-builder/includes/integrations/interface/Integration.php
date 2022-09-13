<?php

abstract class WooMailIntegration implements IWooMailIntegration
{
    protected  $wpDateFormat;
    protected  $wpTimeFormat;
    protected  $wpDateTimeFormat;
    protected  $order;

    public function __construct($order)
    {
      EC_Validation::checkNullOrEmpty($order,'$order');
      $this->order=$order;
      $this->wpDateFormat=get_option('date_format','M d, Y');
      $this->wpTimeFormat=get_option('time_format','H:i:s');
      $this->wpDateTimeFormat=$this->wpDateFormat.' '.$this->wpTimeFormat;
    }

    abstract public function getName();
    abstract public function collectData();

    public function get_order_number()
    {
        return method_exists($this->order, 'get_order_number') ? $this->order->get_order_number() : $this->order->id;
    }
    public function get_template_override($template){
        $template = locate_template(
            array(
                trailingslashit( dirname(EC_WOO_BUILDER_PLUGIN_SLUG) ) . $template,
                $template,
            )
        );

        return $template;
    }
}