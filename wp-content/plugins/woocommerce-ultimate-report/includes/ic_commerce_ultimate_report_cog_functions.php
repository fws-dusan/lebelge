<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if( ! class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Functions')){
	/*
	 * Class Name IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Functions
	 *
	 * Class is used for Cost Of Goods Functions
	 *	 
	*/
	class IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Functions{
		
		public $constants 		=	array();
		public $cogs_constants 	=	array();
		/**
		* Declare class constructor
		* @param array $constants, set default constants 
		*/
		public function __construct($constants = array(),$plugin_key = '') {			
			$this->constants	= array_merge($this->constants, $constants);
			$this->constants['plugin_key']	= isset($this->constants['plugin_key']) ? $this->constants['plugin_key'] : $plugin_key;			
		}
		/**
		* define_constant
		* Defind all COG constant and set into cogs_constants variable
		*/
		function define_constant(){
			
				if(isset($this->cogs_constants['cogs_metakey_simple'])){
					$this->constants['cog'] = $this->cogs_constants;
					return $this->cogs_constants;
				}
				
				$this->cogs_constants['default_cogs_metakey']					 = '_ic_cogs_cost';
				$this->cogs_constants['default_cogs_metakey_simple']			  = '_ic_cogs_cost_simple';
				$this->cogs_constants['default_cogs_metakey_variable']			= '_ic_cogs_cost';
				
				$this->cogs_constants['default_cogs_metakey_order_total']		 = '_ic_cogs_order_total';
				$this->cogs_constants['default_cogs_metakey_item']				= '_ic_cogs_item';
				$this->cogs_constants['default_cogs_metakey_item_total']		  = '_ic_cogs_item_total';
				
				$this->constants['plugin_options'] 								= isset($this->constants['plugin_options']) ? $this->constants['plugin_options'] : get_option($this->constants['plugin_key']);
				
				$this->cogs_constants['cogs_metakey']							= $this->get_setting('cogs_metakey',					$this->constants['plugin_options'],$this->cogs_constants['default_cogs_metakey']);
				$this->cogs_constants['cogs_metakey_simple']					= $this->get_setting('cogs_metakey_simple',				$this->constants['plugin_options'],$this->cogs_constants['default_cogs_metakey_simple']);
				$this->cogs_constants['cogs_metakey_variable']					= $this->get_setting('cogs_metakey_variable',			$this->constants['plugin_options'],$this->cogs_constants['default_cogs_metakey_variable']);
				
				$this->cogs_constants['cogs_metakey_order_total']				= $this->get_setting('cogs_metakey_order_total',		$this->constants['plugin_options'],$this->cogs_constants['default_cogs_metakey_order_total']);
				$this->cogs_constants['cogs_metakey_item']						= $this->get_setting('cogs_metakey_item',				$this->constants['plugin_options'],$this->cogs_constants['default_cogs_metakey_item']);
				$this->cogs_constants['cogs_metakey_item_total']				= $this->get_setting('cogs_metakey_item_total',			$this->constants['plugin_options'],$this->cogs_constants['default_cogs_metakey_item_total']);
				
				return $this->cogs_constants;
		}
		
		/**
		* print_array
		* 
		* Beautifully print the array
		*
		* @param array $ar
		* @param bool $display
		* @return string 
		*/
		function print_array($ar = NULL,$display = true){
			if($ar){
				$output = "<pre>";
				$output .= print_r($ar,true);
				$output .= "</pre>";
				
				if($display){
					echo $output;
				}else{
					return $output;
				}
			}
		}
		
		
		/**
		* print_sql
		* 
		* Beautifully print SQL Query
		*
		* @param string $string, SQL Query string
		*
		* @return string 
		*/
		function print_sql($string){			
			
			$string = str_replace("\t", "",$string);
			$string = str_replace("\r\n", "",$string);
			$string = str_replace("\n", "",$string);
			
			$string = str_replace("SELECT ", "\n\tSELECT \n\t",$string);
			//$string = str_replace(",", "\n\t,",$string);
			
			$string = str_replace("FROM", "\n\nFROM",$string);
			$string = str_replace("LEFT", "\n\tLEFT",$string);
			
			$string = str_replace("AND", "\r\n\tAND",$string);			
			$string = str_replace("WHERE", "\n\nWHERE",$string);
			
			$string = str_replace("LIMIT", "\nLIMIT",$string);
			$string = str_replace("ORDER", "\nORDER",$string);
			$string = str_replace("GROUP", "\nGROUP",$string);
			
			$new_str = "<pre>";
				$new_str .= $string;
			$new_str .= "</pre>";
			
			echo $new_str;
		}
		
		
		/**
		* get_setting
		* 
		* 
		*
		* @param string $id
		* @param array $data
		* @param string $defalut
		* @return string 
		*/
		function get_setting($id, $data, $defalut = NULL){
			if(isset($data[$id]))
				return $data[$id];
			else
				return $defalut;
		}
		/*
		 * get_items_id_list
		 * 
		 * @param array $order_items 
		 * @param array  $field_key 
		 * @param integer  $return_default  
		 * @param string $return_formate
		 * @return string
		 */
		function get_items_id_list($order_items = array(),$field_key = 'order_id', $return_default = '-1' , $return_formate = 'string'){
				$list 	= array();
				$string = $return_default;
				if(count($order_items) > 0){
					foreach ($order_items as $key => $order_item) {
						if(!empty($order_item->$field_key)){
							$list[] = $order_item->$field_key;
						}
					}
					
					$list = array_unique($list);
					
					if($return_formate == "string"){
						$string = implode(",",$list);
					}else{
						$string = $list;
					}
				}
				return $string;
		}	
		/**
		* Get all server request 
		* @param string $name
		* @param string $default 
		* @param string $set 
		* @return decimal $default 
		*/
		public function get_request($name,$default = NULL,$set = false){
			if(isset($_REQUEST[$name])){
				$newRequest = $_REQUEST[$name];
				
				if(is_array($newRequest)){
					$newRequest2 = array();
					foreach($newRequest as $akey => $avalue):
						$newRequest2[] = is_array($avalue) ? implode(",", $avalue) : $avalue;
					endforeach;
					$newRequest = implode(",", $newRequest2);					
				}else{
					$newRequest = trim($newRequest);
				}
				
				if($set) $_REQUEST[$name] = $newRequest;
				
				return $newRequest;
			}else{
				if($set) 	$_REQUEST[$name] = $default;
				return $default;
			}
		}
		/**
		* Get woocommerce price with or without currency
		* @param object $value
		* @param array $args 
		* @return decimal $v 
		*/
		function price($value, $args = array()){
			
			$currency        = isset( $args['currency'] ) ? $args['currency'] : '';
			
			if (!$currency ) {
				if(!isset($this->constants['woocommerce_currency'])){
					$this->constants['woocommerce_currency'] =  $currency = (function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : "USD");
				}else{
					$currency  = $this->constants['woocommerce_currency'];
				}
			}
			
			$args['currency'] 	= $currency;
			$value 				= trim($value);
			$withoutdecimal 	= str_replace(".","d",$value);
						
			if(!isset($this->constants['price_format'][$currency][$withoutdecimal])){
				if(function_exists('wc_price')){
					$v = wc_price($value, $args);
				}elseif(function_exists('woocommerce_price')){
					$v = woocommerce_price($value, $args);
				}else{
					if(!isset($this->constants['currency_symbol'])){
						$this->constants['currency_symbol'] =  $currency_symbol 	= apply_filters( 'ic_commerce_currency_symbol', '&#36;', 'USD');
					}else{
						$currency_symbol  = $this->constants['currency_symbol'];
					}					
					$value				= strlen(trim($value)) > 0 ? $value : 0;
					$v 					= $currency_symbol."".number_format($value, 2, '.', ' ');
					$v					= "<span class=\"amount\">{$v}</span>";
				}
				$this->constants['price_format'][$currency][$withoutdecimal] = $v;
			}else{
				$v = $this->constants['price_format'][$currency][$withoutdecimal];				
			}
			
			
			return $v;
		}
		
		/**
		* Get woocommerce default currency 
		* @return string $currency 
		*/
		function woocommerce_currency(){
			if(!isset($this->constants['woocommerce_currency'])){
				$this->constants['woocommerce_currency'] =  $currency = (function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : "USD");
			}else{
				$currency  = $this->constants['woocommerce_currency'];
			}			
			return $currency;
		}
		
	}//End Class
}//End Class