<?php

if (!defined('ABSPATH')) {
    exit;
}

class WMIntegrationRelatedItems extends WooMailIntegration
{
  private $name="Related Items";
  private $shortcodes;
  private $isPreview;

  public function __construct($order,$isPreview)
  {
    //EC_Validation::checkNullOrEmpty($isPreview,'WMIntegrationRelatedItems->$isPreview');
    if (!$isPreview) {
      $this->isPreview=false;
    }else {
      $this->isPreview=$isPreview;
    }
    parent::__construct($order);

  }

  public function getName()
  {
    return $this->name;
  }

  public function collectData()
  {
    $this->shortcodes=array();
    $this->shortcodes['related_items'] = $this->get_related_products_grid($this->order);
    return $this->shortcodes;
  }
  private function get_related_products_grid($order, $params = array())
  {
      $params['is_preview']=$this->isPreview;
      //TODO: find common way for loading template
      $template = $this->get_template_override('ec-woo-mail-helper/products-related-grid.php');
      $path = EC_WOO_BUILDER_PATH . '/templates/ec-woo-mail-helper/products-related-grid.php';
      if($template){
          $path = $template;
      }
      ob_start();
      include($path);
      $html = ob_get_contents();
      ob_end_clean();
      return $html;
  }
}