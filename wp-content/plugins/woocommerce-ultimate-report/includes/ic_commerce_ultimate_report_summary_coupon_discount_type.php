<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Coupon_Discount_Type')){
	/*
	 * Class Name IC_Commerce_Ultimate_Woocommerce_Report_Coupon_Discount_Type
	 *
	 * Class is used for Coupon Discount Type.
	 *	 
	*/
	class IC_Commerce_Ultimate_Woocommerce_Report_Coupon_Discount_Type  extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		
		public $constants 			=	array();
		
		/**
		* __construct
		* @param array $constants 
		* @param string $plugin_key 
		*/
		function __construct($constants = array(), $plugin_key = ""){
			$this->constants	= array_merge($this->constants, $constants);
			//add_action("ic_commerce_report_page_titles", 					array($this, "ic_commerce_report_page_titles"),31,2);
			
			$report_name = isset($_REQUEST['report_name']) ? $_REQUEST['report_name'] : '';
			
			if($report_name == "coupon_discount_type"){				
				add_action("ic_commerce_report_page_coupon_code_field_tabs", 	array($this, "ic_commerce_report_page_coupon_code_field_tabs"),31,2);
				add_action("ic_commerce_report_page_reset_button_field_tabs", 	array($this, "ic_commerce_report_page_coupon_code_field_tabs"),31,2);			
				add_action("ic_commerce_report_page_default_items", 			array($this, "ic_commerce_report_page_default_items"),31,5);
				add_action("ic_commerce_report_page_columns", 					array($this, "ic_commerce_report_page_columns"),31,2);
				add_action("ic_commerce_report_page_result_columns", 			array($this, "ic_commerce_report_page_result_columns"),31,2);
			}
		}
		
		/**
		* ic_commerce_report_page_titles
		* @param string $page_titles 
		* @param string $report_name 
		* @param string $plugin_options
		* @return string 
		*/
		function ic_commerce_report_page_titles($page_titles = '',$report_name = '', $plugin_options = ''){
			//$page_titles['coupon_discount_type'] = __('Coupon Discount Type Wise Reporting',	'icwoocommerce_textdomains');
			return $page_titles;
		}
		
		/**
		* ic_commerce_report_page_coupon_code_field_tabs
		* @param array $tabs 
		* @param string $report_name
		* @return array 
		*/
		function ic_commerce_report_page_coupon_code_field_tabs($tabs = array(), $report_name = ''){
			$tabs['coupon_discount_type'] = 'coupon_discount_type';
			return $tabs;
		}
		
		/**
		* ic_commerce_report_page_columns
		* @param array $columns 
		* @param string $report_name
		* @return array 
		*/
		function ic_commerce_report_page_columns($columns = array(), $report_name = ''){
			$columns 	= array(
				"prduct_name"			   => __("Product Name", 		   'icwoocommerce_textdomains')				
				,"coupon_code_label"		=> __("Coupon Code", 		    'icwoocommerce_textdomains')
				,"discount_type_label"	  => __("Discount Type", 		  'icwoocommerce_textdomains')
				,"quantity"				 => __("Qty.", 				   'icwoocommerce_textdomains')
				,"total_amount"			 => __("Discount Amount", 	    'icwoocommerce_textdomains')
			);
			return $columns;
			
		}
		
		/**
		* ic_commerce_report_page_result_columns
		* This Function is used for Result page Coulmns.
		* @param array $total_columns 
		* @param string $report_name
		* @return array 
		*/
		function ic_commerce_report_page_result_columns($total_columns = array(), $report_name = ''){
			$total_columns = array(
				"total_row_count"		  => __("Result Count", 		'icwoocommerce_textdomains')
				,"quantity"				=> __("Product Qty.", 		'icwoocommerce_textdomains')
				,"total_amount"			=> __("Discount Amount", 	'icwoocommerce_textdomains')
			);
			return $total_columns;
		}
		
		/**
		* ic_commerce_report_page_default_items
		* This Function is used for Result page Default Items.
		* @param string $rows 
		* @param string $type
		* @param string $columns 
		* @param string $report_name
		* @param string $$that
		* @return string 
		*/
		function ic_commerce_report_page_default_items($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){
			return $this->ic_commerce_custom_all_coupon_discount_type_query($rows, $type, $columns, $report_name, $that);
			return $rows;
		}
		
		/**
		* ic_commerce_custom_all_coupon_discount_type_query
		* This Function is used for Result page Default Items.
		* @param string $rows 
		* @param string $type
		* @param string $columns 
		* @param string $report_name
		* @param string $$that
		* @return array 
		*/
		function ic_commerce_custom_all_coupon_discount_type_query($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){
			
			global $wpdb;
			
			$request = $that->get_all_request();
			
			if(!isset($this->items_query)){
				extract($request);
				
				$coupon_codes 			= $this->get_request('coupon_codes','-1');
				$coupon_code  			= $this->get_request('coupon_code','-1');
				$order_status			= $that->get_string_multi_request('order_status',$order_status, "-1");
				$hide_order_status		= $that->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				$coupon_codes			= $that->get_string_multi_request('coupon_codes',$coupon_codes, "-1");
				$coupon_discount_types	= $that->get_string_multi_request('coupon_discount_types',$coupon_discount_types, "-1");
				$country_code			= $that->get_string_multi_request('country_code',$country_code, "-1");
									
									
										
				$sql = " SELECT ";
				$sql .= "
				woocommerce_order_items.order_item_name				AS		'coupon_code_label',
				woocommerce_order_items.order_item_name				AS		'prduct_name',
				woocommerce_order_items.order_item_name				AS		'coupon_code'
				";
				
				if($report_name == "coupon_discount_type"){
					$sql .= "";
					
				}else{
					$sql .= ", SUM(woocommerce_order_itemmeta.meta_value) 	AS		'total_amount'
					,woocommerce_order_itemmeta.meta_value 					AS 		'coupon_amount'
					,COUNT(*) 												AS 		'coupon_count'";
				}
				
				if($report_name == "coupon_couontry_page"){
					$sql .= ", billing_country.meta_value 			AS billing_country";
					switch($sort_by){
						case "coupon_country":
							$sql .= ", CONCAT(woocommerce_order_items.order_item_name, ' ', billing_country.meta_value) 			AS coupon_country";
							break;
						case "country_coupon":
							$sql .= ", CONCAT(billing_country.meta_value, ' ', woocommerce_order_items.order_item_name) 			AS country_coupon";
							break;
						
					}
				}
				
				if($report_name == "coupon_discount_type"){
					$sql .= ", coupon_discount_type.meta_value 							AS discount_type";
					//$sql .= ", SUM(woocommerce_order_itemmeta.meta_value)				AS discount_amount";
					$sql .= ", woocommerce_order_items_product.order_item_name			AS 'prduct_name'";
					$sql .= ", SUM(woocommerce_order_itemmeta_product_qty.meta_value)	AS 'quantity'";										
					$sql .= ", CONCAT(woocommerce_order_items_product.order_item_name, '-', coupon_discount_type.meta_value, '-' ,woocommerce_order_items.order_item_name) 	AS order_by";
					
					$sql .= ", SUM(woocommerce_order_itemmeta_line_subtotal.meta_value)						AS line_subtotal";
					$sql .= ", SUM(woocommerce_order_itemmeta_line_total.meta_value)						AS line_total";
					$sql .= ", (SUM(woocommerce_order_itemmeta_line_subtotal.meta_value) - SUM(woocommerce_order_itemmeta_line_total.meta_value)) AS total_amount";
				}
				
				$sql = apply_filters("ic_commerce_report_page_select_query", $sql, $request, $type, $page, $report_name, $columns);				
				
				$sql .= "
					FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items 
					LEFT JOIN	{$wpdb->posts}	as posts ON posts.ID = woocommerce_order_items.order_id
					
				";
				
				if($report_name == "coupon_discount_type"){
					
					$sql .= " LEFT JOIN	{$wpdb->posts}	as coupons ON coupons.post_title = woocommerce_order_items.order_item_name";
					$sql .= " LEFT JOIN	{$wpdb->postmeta}	as coupon_discount_type ON coupon_discount_type.post_id = coupons.ID";
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items_product ON woocommerce_order_items_product.order_id=woocommerce_order_items.order_id";
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_product_qty ON woocommerce_order_itemmeta_product_qty.order_item_id=woocommerce_order_items_product.order_item_id";
					//$sql .= " LEFT JOIN  {$wpdb->postmeta} as postmeta_order_total ON postmeta_order_total.post_id = woocommerce_order_items_product.order_id";
					
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_line_subtotal ON woocommerce_order_itemmeta_line_subtotal.order_item_id=woocommerce_order_items_product.order_item_id AND woocommerce_order_itemmeta_line_subtotal.meta_key = '_line_subtotal'";
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_line_total ON woocommerce_order_itemmeta_line_total.order_item_id=woocommerce_order_items_product.order_item_id AND woocommerce_order_itemmeta_line_total.meta_key = '_line_total'";
				}else{
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id";
					
					if($coupon_discount_types && $coupon_discount_types != "-1"){
						$sql .= " LEFT JOIN	{$wpdb->posts}	as coupons ON coupons.post_title = woocommerce_order_items.order_item_name";
						$sql .= " LEFT JOIN	{$wpdb->postmeta}	as coupon_discount_type ON coupon_discount_type.post_id = coupons.ID";
					}
				}
				
				if($report_name == "coupon_couontry_page"){
					$sql .= " LEFT JOIN	{$wpdb->postmeta}	as billing_country ON billing_country.post_id = woocommerce_order_items.order_id AND billing_country.meta_key = '_billing_country'";
				}
				
				if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
				
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= "
				WHERE 
				posts.post_type 								=	'shop_order'
				AND woocommerce_order_items.order_item_type		=	'coupon' 
				
				";
				
				if($report_name == "coupon_discount_type"){
					$sql .= " ";
				}else{
					$sql .= " AND woocommerce_order_itemmeta.meta_key			=	'discount_amount'";
				}
				
				
				if($report_name == "coupon_discount_type"){
					$sql .= " AND coupons.post_type 								=	'shop_coupon'";
					$sql .= " AND coupon_discount_type.meta_key						=	'discount_type'";
					$sql .= " AND woocommerce_order_items_product.order_item_type	=	'line_item'";
					$sql .= " AND woocommerce_order_itemmeta_product_qty.meta_key	=	'_qty'";
					//$sql .= " AND postmeta_order_total.meta_key						=	'_order_total'";
				}else{
					if($coupon_discount_types && $coupon_discount_types != "-1"){
						$sql .= " AND coupons.post_type 				=	'shop_coupon'";
						$sql .= " AND coupon_discount_type.meta_key		=	'discount_type'";
					}
				}
				
				/*if ($start_date != NULL &&  $end_date !=NULL){
					$sql .= " AND DATE(posts.post_date) BETWEEN '".$start_date."' AND '". $end_date ."'";
				}*/
				
				if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}
				}
				
				if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
					$sql .= " AND  term_taxonomy.term_id IN ({$order_status_id})";
				}
				
				if(strlen($publish_order)>0 && $publish_order != "-1" && $publish_order != "no" && $publish_order != "all"){
					$in_post_status		= str_replace(",","','",$publish_order);
					$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
				}
				
				if($coupon_code && $coupon_code != "-1"){
					$sql .= " AND (woocommerce_order_items.order_item_name IN ('{$coupon_code}') OR woocommerce_order_items.order_item_name LIKE '%{$coupon_code}%')";
				}
				
				if($coupon_codes && $coupon_codes != "-1"){
					$sql .= " AND woocommerce_order_items.order_item_name IN ({$coupon_codes})";
				}
				
				if($coupon_discount_types && $coupon_discount_types != "-1"){
					$sql .= " AND coupon_discount_type.meta_value IN ({$coupon_discount_types})";
				}
				
				if($country_code && $country_code != "-1"){
					$sql .= " AND billing_country.meta_value IN ({$country_code})";
				}
				
				if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND posts.post_status IN (".$order_status.")";
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
				
				$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
				if($request['report_name'] == "coupon_couontry_page"){
					
					$group_sql = " Group BY billing_country.meta_value, woocommerce_order_items.order_item_name";
					
					$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);	
					
					$order_sql = " ORDER BY {$sort_by} {$order_by}";
					
					$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);	
					
				}else if($request['report_name'] == "coupon_discount_type"){
					
					$group_sql = " Group BY order_by";
					
					$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);	
					
					$order_sql = "";
					
					if(($sort_by != "" and $sort_by != "-1") || ($sort_by != "" and $sort_by != "-1")){
						$order_sql = " ORDER BY {$sort_by} {$order_by}";
					}
					
					$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);	
					
				
					
				}else{
					
					$group_sql = " Group BY woocommerce_order_items.order_item_name";
					
					$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);	
					
					$order_sql = " ORDER BY total_amount DESC";
					
					$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);	
				}				
				
				$that->items_query = $sql;
				
				
				
			}else{
				$sql = $that->items_query;
			}
			
			$order_items = $that->get_query_items($type,$sql);
			
			if($report_name == "coupon_discount_type"){
				if($type != "total_row"){
					$coupon_discount_type_data = $this->get_coupon_types();
					foreach($order_items as $item_key => $order_item):
						$discount_type 			= isset($order_item->discount_type) ? $order_item->discount_type : '';
						$discount_type_label	= isset($coupon_discount_type_data[$discount_type]) ? $coupon_discount_type_data[$discount_type] : '';
						$order_items[$item_key]->discount_type_label	= $discount_type_label;
					endforeach;
				}
			}
			
			return $order_items;
		}
		
	}//End Class
}//End 