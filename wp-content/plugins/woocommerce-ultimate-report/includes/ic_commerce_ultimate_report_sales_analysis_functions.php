<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if (!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Sales_Analysis_Functions')) {
	/*
	 * Class Name IC_Commerce_Ultimate_Woocommerce_Report_Sales_Analysis_Functions
	 *
	 * Class is used for returning Sales Analysis Functions.
	 *	 
	*/
	class IC_Commerce_Ultimate_Woocommerce_Report_Sales_Analysis_Functions{
		/**
		* __construct
		*/
		public function __construct(){
		
		}
		
		/**
		* get_request
		* @param string $name
		* @param string $default
		* @param string $set
		*/
		public function get_request($name,$default = NULL,$set = false){
		if(isset($_REQUEST[$name])){
			$newRequest = $_REQUEST[$name];
			
			if(is_array($newRequest)){
				$newRequest = implode(",", $newRequest);
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
		* print_array
		* @param string $ar
		* @param string $display
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
		* get_product
		* This Function is used to get Products.
		* @param string $type
		* @param string $product_id
		* @param string $r
		* @return string
		*/
		function get_product($type="VARIABLE",$product_id=NULL,$r="OBJECT"){
			global $wpdb;
			$query = " 	SELECT 
						order_items.order_item_name as label  
						,product_id.meta_value as id
			
				FROM {$wpdb->prefix}woocommerce_order_items as order_items " ;	
			$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as product_id ON product_id.order_item_id=order_items.order_item_id ";
			if ($type=="SIMPLE" || $type=="VARIABLE"):
				$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as variation_id ON variation_id.order_item_id=order_items.order_item_id ";
			endif;
			$query .= " WHERE 1=1 ";
			if ($product_id)
			$query .= " AND product_id.meta_value IN ({$product_id})";
			$query .= " AND order_items.order_item_type='line_item'";
			$query .= " AND product_id.meta_key='_product_id'";
			
			if ($type=="SIMPLE"):
				$query .= " AND variation_id.meta_value='0'";
				$query .= " AND variation_id.meta_key='_variation_id'";
			endif;
			if ($type=="VARIABLE"):
				$query .= " AND variation_id.meta_value>'0'";
				$query .= " AND variation_id.meta_key='_variation_id'";
			endif;
			$query .= " GROUP BY  product_id.meta_value ";
			$query .= " order By order_items.order_item_name ";
			
			if ($r=="ARRAY_A"){
				$results = $wpdb->get_results( $query,ARRAY_A);
			}
			else{
				$results = $wpdb->get_results( $query);
			}
			return $results;
			
		
		}
		
		/**
		* get_all_variation
		* This Function is used to get All Variations.
		* @return string
		*/
		function get_all_variation(){
		global $wpdb;
		$variation =array();
		
		$query = " SELECT postmeta.meta_key as variation_name, postmeta.meta_value as variation_value FROM  {$wpdb->postmeta} as postmeta ";
		$query .= " WHERE 1=1 ";
		$query .= " AND ( postmeta.meta_key LIKE 'attribute_%' OR postmeta.meta_key LIKE 'pa_%')";
		$results = $wpdb->get_results( $query);	
		foreach($results as $key => $value){
			 $variation_name = str_replace("attribute_","",$value->variation_name) ;
			// $variation_name = str_replace("pa_","", $variation_name ) ;
			 $variation[$value->variation_value] =   $variation_name ;
		}
		
		
		
			
			//$this->print_array($variation);
			 
			return $variation;
		}
		
		/**
		* get_product_cat
		* This Function is used to get All Product Categories.
		* @param string $product_id 
		* @return string
		*/
		function get_product_cat($product_id =NULL){
			$product_cat_name = "";
			$product_cats = wp_get_post_terms( $product_id, 'product_cat' );
			
			foreach($product_cats as $k=>$v){
				if (strlen($product_cat_name)==0) 
					$product_cat_name = $v->name;
				else
					$product_cat_name .= ",".$v->name;
			}
			return $product_cat_name;
		}
		
		/**
		* create_dropdown
		* This Function is used to Create Dropdowns.
		* @param string $data
		* @param string $name 
		* @param string $id 
		* @param string $show_option_none 
		* @param string $class 
		* @param string $default 
		* @param string $type 
		* @param string $multiple 
		* @param string $size 
		* @param string $d  
		* @param string $display 
		* @return string
		*/
		function create_dropdown($data = NULL, $name = "",$id='', $show_option_none="Select One", $class='', $default ="-1", $type = "array", $multiple = false, $size = 0, $d = "-1", $display = true){
			$count 				= count($data);
			$dropdown_multiple 	= '';
			$dropdown_size 		= '';
			
			$selected =  explode(",",$default);
			
			if($count<=0) return '';
			
			if($multiple == true and $size >= 0){
				//$this->print_array($data);
				
				if($count < $size) $size = $count + 1;
				$dropdown_multiple 	= ' multiple="multiple"';
				//echo $count;
				$dropdown_size 		= ' size="'.$size.'"  data-size="'.$size.'"';
			}
			$output = "";
			$output .= '<select name="'.$name.'" id="'.$id.'" class="'.$class.'"'.$dropdown_multiple.$dropdown_size.'>';
			
			//if(!$dropdown_multiple)
			
			//$output .= '<option value="-1">'.$show_option_none.'</option>';
			
			if($show_option_none){
				if($default == "all"){
					$output .= '<option value="'.$d.'" selected="selected">'.$show_option_none.'</option>';
				}else{
					$output .= '<option value="'.$d.'">'.$show_option_none.'</option>';
				}
			}
			
			if($type == "object"){
				foreach($data as $key => $value):
					$s = '';
					
					if(in_array($value->id,$selected)) $s = ' selected="selected"';					
					//if($value->id == $default ) $s = ' selected="selected"';
					
					$c = (isset($value->counts) and $value->counts > 0) ? " (".$value->counts.")" : '';
					
					$output .= "\n<option value=\"".$value->id."\"{$s}>".$value->label.$c."</option>";
				endforeach;
			}else if($type == "array"){
				foreach($data as $key => $value):
					$s = '';
					if(in_array($key,$selected)) $s = ' selected="selected"';
					//if($key== $default ) $s = ' selected="selected"';
					
					$output .= "\n".'<option value="'.$key.'"'.$s.'>'.$value.'</option>';
				endforeach;
			}else{
				foreach($data as $key => $value):
					$s = '';
					if(in_array($key,$selected)) $s = ' selected="selected"';
					//if($key== $default ) $s = ' selected="selected"';
					$output .= "\n".'<option value="'.$key.'"'.$s.'>'.$value.'</option>';
				endforeach;
			}
						
			$output .= '</select>';
			if($display){
				echo $output;
			}else{
				return  $output;
			}
		
		}
	}
}
?>