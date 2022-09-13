<?php    
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
if(!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Customer_In_Price_Point')){
	
	class IC_Commerce_Ultimate_Woocommerce_Report_Customer_In_Price_Point  extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		/*Declare constant variable*/
		public $constants 			=	array();
		/**
		* Declare class constructor
		* @param array $constants, set default constants 
		*/
		function __construct($constants = array(), $plugin_key = ""){
			$this->constants	= array_merge($this->constants, $constants);
			//add_action("ic_commerce_report_page_titles", 					array($this, "ic_commerce_report_page_titles"),31,2);
			
			$report_name = isset($_REQUEST['report_name']) ? $_REQUEST['report_name'] : '';
			if($report_name == "customer_in_price_point"){	
				add_action("ic_commerce_report_page_customer_in_price_point_field_tabs", 	array($this, "ic_commerce_report_page_customer_in_price_point_field_tabs"),31,2);
				add_action("ic_commerce_report_page_start_date", 							array($this, "ic_commerce_report_page_start_date"),			31,2);
				add_action("ic_commerce_report_page_default_items", 						array($this, "ic_commerce_report_page_default_items"),		31,5);
				add_action("ic_commerce_report_page_columns", 								array($this, "ic_commerce_report_page_columns"),			31,2);
				add_action("ic_commerce_report_page_result_columns", 						array($this, "ic_commerce_report_page_result_columns"),		31,2);
				add_action("ic_commerce_pdf_custom_column_right_alignment",					array($this, 'ic_commerce_column_right_alignment'),			31,3);						
			}
		}
		
		/**
		* ic_commerce_report_page_start_date
		*
		*
		* @param string $start_date 
		* @param string $report_name 
		*
		* @return date
		*/
		function ic_commerce_report_page_start_date($start_date = '',$report_name = ''){
			$start_date = $this->constants['today_date'];
			return $start_date;
		}
		
		/**
		* ic_commerce_report_page_titles
		*
		*
		* @param string $page_titles 
		* @param string $report_name 
		* @param string $plugin_options 
		*
		* @return date
		*/
		function ic_commerce_report_page_titles($page_titles = '',$report_name = '', $plugin_options = ''){
			//$page_titles['customer_in_price_point'] = __('Customer In Price Point',	'icwoocommerce_textdomains');
			return $page_titles;
		}
		/**
		* ic_commerce_report_page_customer_in_price_point_field_tabs
		*
		*
		* @param array $tabs 
		* @param string $report_name 
	    *
		*
		* @return date
		*/
		function ic_commerce_report_page_customer_in_price_point_field_tabs($tabs = array(), $report_name = ''){
			$tabs['customer_in_price_point'] = 'customer_in_price_point';
			return $tabs;
		}
		/**
		* ic_commerce_report_page_default_items
		*
		*
		* @param array $rows 
		* @param string $type 
		* @param array $columns 
		* @param string $report_name 
		* @param object $parent_this 
	    *
		*
		* @return array
		*/
		function ic_commerce_report_page_default_items($rows = array(), $type = "limit_row", $columns = array(), $report_name = "", $parent_this = NULL){
			$rows 		= $this->ic_commerce_custom_all_customer_in_price_range_query($type, $columns, $report_name, $parent_this);
			return $rows;
		}
		/**
		* ic_commerce_report_page_columns
		*
		*
		* @param array $columns 
		* @param string $report_name 
		*
		*
		* @return array
		*/
		function ic_commerce_report_page_columns($columns = array(), $report_name = ""){
			
			$columns 	= array(
				"billing_first_name"	=> __("Billing First Name", 	'icwoocommerce_textdomains')
				,"billing_last_name"	=> __("Billing Last Name", 		'icwoocommerce_textdomains')
				,"billing_email"		=> __("Billing Email", 			'icwoocommerce_textdomains')
				,"min_product_price"	=> __("Min Price", 				'icwoocommerce_textdomains')
				,"max_product_price"	=> __("Max Price", 				'icwoocommerce_textdomains')
				,"order_count"			=> __("Order Count", 			'icwoocommerce_textdomains')
				//,"total_amount"			=> __("Amount", 				'icwoocommerce_textdomains')
			);
			
			return $columns;
		}
		/**
		* ic_commerce_report_page_result_columns
		*
		*
		* @param array $columns 
		* @param string $report_name 
		*
		*
		* @return array
		*/
		function ic_commerce_report_page_result_columns($columns = array(), $report_name = ""){							
			$columns = array(
				"total_row_count"			=> __("Customer Count", 	'icwoocommerce_textdomains')
				,"order_count"				=> __("Order Count", 		'icwoocommerce_textdomains')
				,"total_amount"				=> __("Total Amount", 		'icwoocommerce_textdomains')
			);
			return $columns;
		}
		/**
		* ic_commerce_column_right_alignment
		*
		*
		* @param array $columns 
		* @param string $report_name 
		*
		*
		* @return array
		*/
		function ic_commerce_column_right_alignment($custom_columns = array()){
			
			
					$custom_columns['min_product_price']= 'min_product_price';
					$custom_columns['max_product_price']= 'max_product_price';
					$custom_columns['order_count'] 		= 'order_count';
					$custom_columns['total_amount'] 	= 'total_amount';
					
					
			return $custom_columns;
		}
		/**
		* ic_commerce_custom_all_customer_in_price_range_query
		*
		*
		* @param string $type 
		* @param array $columns 
		* @param string $report_name 
		* @param object $parent_this 
		*
		* @return array
		*/
		/*Customers in Price Range*/		
		function ic_commerce_custom_all_customer_in_price_range_query($type = 'limit_row', $columns = array(), $report_name = "", $parent_this = NULL){
				global $wpdb;				
				if(!isset($parent_this->items_query)){
					$request 			= $parent_this->get_all_request();extract($request);					
					$order_status		= $parent_this->get_string_multi_request('order_status',$order_status, "-1");
					$hide_order_status	= $parent_this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
											
					$sql = " SELECT ";
					$sql .= " SUM(postmeta1.meta_value) 		AS 'total_amount'";					
					$sql .= ", postmeta2.meta_value 			AS 'billing_email'";					
					$sql .= ", postmeta3.meta_value 			AS 'billing_first_name'";					
					$sql .= ", COUNT(postmeta2.meta_value) 		AS 'order_count'";					
					$sql .= ", postmeta4.meta_value 			AS  customer_id";
					$sql .= ", postmeta5.meta_value 			AS  billing_last_name";
					$sql .= ", MAX(posts.post_date)				AS  order_date";
					$sql .= ", CONCAT(postmeta3.meta_value, ' ',postmeta5.meta_value) AS billing_name";
					
					$sql .= ", MAX((woocommerce_order_itemmeta_ttl.meta_value/woocommerce_order_itemmeta_qty.meta_value)) AS max_product_price";
					$sql .= ", MIN((woocommerce_order_itemmeta_ttl.meta_value/woocommerce_order_itemmeta_qty.meta_value)) AS min_product_price";
					
					$sql = apply_filters("ic_commerce_report_page_select_query", $sql, $request, $type, $page, $report_name);
					
					$sql .= " FROM {$wpdb->posts} as posts
					LEFT JOIN  {$wpdb->postmeta} as postmeta1 ON postmeta1.post_id=posts.ID
					LEFT JOIN  {$wpdb->postmeta} as postmeta2 ON postmeta2.post_id=posts.ID
					LEFT JOIN  {$wpdb->postmeta} as postmeta3 ON postmeta3.post_id=posts.ID";
					
					$sql .= " LEFT JOIN  {$wpdb->postmeta} as postmeta4 ON postmeta4.post_id=posts.ID";
					
					$sql .= " LEFT JOIN  {$wpdb->postmeta} as postmeta5 ON postmeta5.post_id=posts.ID";
					
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items ON woocommerce_order_items.order_id = posts.ID";
					
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_ttl ON woocommerce_order_itemmeta_ttl.order_item_id=woocommerce_order_items.order_item_id";
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_qty ON woocommerce_order_itemmeta_qty.order_item_id=woocommerce_order_items.order_item_id";
					
					if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
							$sql .= " 
							LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
							LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
					
					$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name);
					
					$sql .= " WHERE 1*1";
					$sql .= " AND posts.post_type							= 'shop_order' ";
					$sql .= " AND postmeta1.meta_key						= '_order_total' ";
					$sql .= " AND postmeta2.meta_key						= '_billing_email'";
					$sql .= " AND postmeta3.meta_key						= '_billing_first_name'";					
					$sql .= " AND postmeta4.meta_key						= '_customer_user'";
					$sql .= " AND postmeta5.meta_key						= '_billing_last_name'";					
					$sql .= " AND woocommerce_order_items.order_item_type	= 'line_item'";
					$sql .= " AND woocommerce_order_itemmeta_ttl.meta_key	= '_line_total'";
					$sql .= " AND woocommerce_order_itemmeta_qty.meta_key	= '_qty'";
					
					$min_price = isset($_REQUEST['min_price']) ? $_REQUEST['min_price'] : '';
					if(strlen($min_price) > 0 and $min_price >= 0){
						if(is_numeric($min_price)) $sql .= " AND (woocommerce_order_itemmeta_ttl.meta_value/woocommerce_order_itemmeta_qty.meta_value) > $min_price";
					}
					
					$max_price = isset($_REQUEST['max_price']) ? $_REQUEST['max_price'] : '';
					if(strlen($max_price) > 0 and $max_price >= 0){
						if(is_numeric($max_price)) $sql .= " AND (woocommerce_order_itemmeta_ttl.meta_value/woocommerce_order_itemmeta_qty.meta_value) < $max_price";
					}
					
					/*if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(posts.post_date) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}*/
					
					$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
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
					
					//Added 20141013
					if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND posts.post_status IN (".$order_status.")";
					if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
					
					$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name);
					
					//$sql .= " GROUP BY  postmeta2.meta_value Order By billing_first_name ASC, billing_last_name ASC";
					
					
					$group_sql = " GROUP BY  postmeta2.meta_value Order By billing_last_name ASC, billing_first_name ASC";
					
					$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name);
					
					$order_sql = "";
				
					$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name);	
				
					
					$parent_this->items_query = $sql;
				}else{
					$sql = $parent_this->items_query;
				}
				
				$order_items = $parent_this->get_query_items($type,$sql);
				
				return $order_items;
		}
	}//End Class
}//End 