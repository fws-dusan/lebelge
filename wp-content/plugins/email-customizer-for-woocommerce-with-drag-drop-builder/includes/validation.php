<?php

if (!defined('ABSPATH')) {
    exit;
}

 class EC_Validation
{
  public static function checkNullOrEmpty($param,$paramName)
  {
    if (empty($param) || is_null($param)) {
      throw new Exception($paramName.' cannot be null or empty');
    }
  }
}
