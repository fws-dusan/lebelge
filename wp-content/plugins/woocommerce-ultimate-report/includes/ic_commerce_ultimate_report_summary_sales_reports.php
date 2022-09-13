<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Summary_Sales_Reports')){
	
	class IC_Commerce_Ultimate_Woocommerce_Report_Summary_Sales_Reports  extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		
		/* variable declaration constants*/
		public $constants 			=	array();
		
		/*
		* Function Name __construct
		*
		* Initialize Class Default Settings, Assigned Variables
		*
		* @param array $constants
		*		 
		*/
		function __construct($constants = array(), $plugin_key = ""){
			$this->constants	= array_merge($this->constants, $constants);
			//add_filter("ic_commerce_report_page_titles", 					array($this, "ic_commerce_report_page_titles"),31,2);
			
			$report_name = isset($_REQUEST['report_name']) ? $_REQUEST['report_name'] : '';
			
			if($report_name == "summary_sales_report"){
				add_filter("ic_commerce_report_page_default_items", 			array($this, "ic_commerce_report_page_default_items"),				31,5);
				add_filter("ic_commerce_report_page_columns", 					array($this, "ic_commerce_report_page_columns"),					31,2);
				add_filter("ic_commerce_report_page_result_columns", 			array($this, "ic_commerce_report_page_result_columns"),				31,2);
				add_filter("ic_commerce_pdf_custom_column_right_alignment", 	array($this, "ic_commerce_pdf_custom_column_right_alignment"),		31,2);
			}
		}
		/**
		* ic_commerce_pdf_custom_column_right_alignment
		*
		*
		* @param array $custom_columns
		*
		* @return array $custom_columns 
		*/
		function ic_commerce_pdf_custom_column_right_alignment($custom_columns = array()){
			$custom_columns['order_year'] = 'order_year';
			return $custom_columns;
		}
		/**
		* ic_commerce_report_page_titles
		*
		*
		* @param string $page_titles
		* @param string $report_name
		* @param string $plugin_options
		*
		* @return string $page_titles 
		*/
		function ic_commerce_report_page_titles($page_titles = '',$report_name = '', $plugin_options = ''){
			//$page_titles['summary_sales_report'] = __('Monthly Sales Report',	'icwoocommerce_textdomains');
			return $page_titles;
		}
		/**
		* ic_commerce_report_page_coupon_code_field_tabs
		*
		*
		* @param array $tabs
		*
		*
		* @return array $tabs 
		*/
		function ic_commerce_report_page_coupon_code_field_tabs($tabs = array(), $report_name = ''){
			$tabs['summary_sales_report'] = 'summary_sales_report';
			return $tabs;
		}
		/**
		* ic_commerce_report_page_columns
		*
		*
		* @param array $columns
		* @param string $report_name
		*
		*
		* @return array $columns 
		*/
		function ic_commerce_report_page_columns($columns = array(), $report_name = ''){
			$columns 	= array(
				"order_month"			=> __("Month Name", 		'icwoocommerce_textdomains')
				,"order_year"			=> __("Order Year", 		'icwoocommerce_textdomains')
				,"order_count"			=> __("Order Count", 		'icwoocommerce_textdomains')
				,"total_amount"			=> __("Sales Amount", 		'icwoocommerce_textdomains')
			);
			return $columns;
			
		}
		/**
		* ic_commerce_report_page_result_columns
		*
		*
		* @param array $total_columns
		* @param string $report_name
		*
		*
		* @return array $total_columns 
		*/
		function ic_commerce_report_page_result_columns($total_columns = array(), $report_name = ''){
			$total_columns = array(
				"total_row_count"		=> __("Month Count", 		'icwoocommerce_textdomains')
				,"order_count"			=> __("Order Count", 		'icwoocommerce_textdomains')
				,"total_amount"			=> __("Sales Amount", 		'icwoocommerce_textdomains')
			);
			return $total_columns;
		}
		/**
		* ic_commerce_report_page_default_items
		*
		*
		* @param string $rows
		* @param string $type
		* @param string $columns
		* @param string $report_name
		* @param string $that
		*
		* @return array $total_columns 
		*/
		function ic_commerce_report_page_default_items($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){
			return $this->ic_commerce_custom_all_summary_sales_report_query($rows, $type, $columns, $report_name, $that);
		}
		/**
		* ic_commerce_custom_all_summary_sales_report_query
		*
		*
		* @param string $rows
		* @param string $type
		* @param string $columns
		* @param string $report_name
		* @param string $that
		*
		* @return array $total_columns 
		*/
		function ic_commerce_custom_all_summary_sales_report_query($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){
			
			global $wpdb;
			
			$request = $that->get_all_request();
			
			if(!isset($this->items_query)){
				extract($request);
				
				$order_status			= $that->get_string_multi_request('order_status',$order_status, "-1");
				$hide_order_status		= $that->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				
				$sql = "SELECT ";				
				$sql .= " COUNT(*) 										AS order_count";				
				$sql .= ", SUM(order_total.meta_value) 					AS total_amount";				
				$sql .= ", MONTHNAME(posts.post_date) 					AS order_month";
				$sql .= ", YEAR(posts.post_date) 						AS order_year";
				$sql .= ", DATE_FORMAT(posts.post_date,'%Y-%m') 		AS month_key";
				
				$sql .= " FROM {$wpdb->posts} AS posts";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS order_total ON order_total.post_id = posts.ID AND order_total.meta_key = '_order_total'";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS customer_user ON customer_user.post_id = posts.ID AND customer_user.meta_key = '_customer_user'";
				
				$sql .= " LEFT JOIN {$wpdb->users} AS users ON users.ID = posts.ID AND order_total.meta_key = '_order_total'";
				
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= " WHERE posts.post_type = 'shop_order'";
				
				/*if ($start_date != NULL &&  $end_date !=NULL){
					$sql .= " AND DATE(posts.post_date) BETWEEN '".$start_date."' AND '". $end_date ."'";
				}*/
				
				if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}
				}
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
				
				if($order_status  && $order_status != '-1' and $order_status != "'-1'") $sql .= " AND posts.post_status IN (".$order_status.")";
				
				$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= " GROUP BY month_key";
				
				$sql .= " ORDER BY posts.post_date DESC";
				
				$that->items_query = $sql;
				
			}else{
				$sql = $that->items_query;
			}
			
			$order_items = $that->get_query_items($type,$sql);			
			
			return $order_items;
		}
		
	}//End Class
}//End 