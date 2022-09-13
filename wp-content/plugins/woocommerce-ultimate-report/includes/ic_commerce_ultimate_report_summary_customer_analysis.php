<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Summary_Customer_Analysis')){
	/*
	 * Class Name IC_Commerce_Ultimate_Woocommerce_Report_Summary_Customer_Analysis
	 *
	 * Class is used for Summary of Customer Analysis.
	 *	 
	*/
	class IC_Commerce_Ultimate_Woocommerce_Report_Summary_Customer_Analysis  extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		
		public $constants 			=	array();
		
		/**
		* __construct
		* @param string $constants
		* @param string $plugin_key
		*/
		function __construct($constants = array(), $plugin_key = ""){
			$this->constants	= array_merge($this->constants, $constants);
			//add_filter("ic_commerce_report_page_titles", 					array($this, "ic_commerce_report_page_titles"),31,2);
			
			$report_name = isset($_REQUEST['report_name']) ? $_REQUEST['report_name'] : '';
			
			if($report_name == "customer_analysis"){
				add_filter("ic_commerce_report_page_title", 					array($this, "ic_commerce_report_page_title"),						31,2);
				add_filter("ic_commerce_report_page_search_form_hidden_fields", array($this, "ic_commerce_report_page_search_form_hidden_fields"),  31,1);				
				add_filter("ic_commerce_report_page_start_date", 				array($this, "ic_commerce_report_page_start_date"),					31,2);
				add_filter("ic_commerce_report_page_end_date", 					array($this, "ic_commerce_report_page_end_date"),					31,2);
				add_filter("ic_commerce_report_page_default_items", 			array($this, "ic_commerce_report_page_default_items"),				31,5);
				add_filter("ic_commerce_report_page_columns", 					array($this, "ic_commerce_report_page_columns"),					31,2);
				add_filter("ic_commerce_report_page_result_columns", 			array($this, "ic_commerce_report_page_result_columns"),				31,2);
				add_filter("ic_commerce_pdf_custom_column_right_alignment", 	array($this, "ic_commerce_pdf_custom_column_right_alignment"),		31,2);
				add_filter("ic_commerce_report_page_grid_price_columns", 		array($this, "ic_commerce_report_page_grid_price_columns"),			31,2);
				add_filter("ic_commerce_report_page_export_pdf_price_columns", 	array($this, "ic_commerce_report_page_grid_price_columns"),			31,2);
				add_action("ic_commerce_report_page_footer_area", 				array($this, "ic_commerce_report_page_footer_area"),  				31,1);
				
			}
		}
		
		/**
		* ic_commerce_report_page_search_form_hidden_fields
		* @param string $hidden_fields
		* @param string $page
		* @param string $report_name
		* @return array
		*/
		function ic_commerce_report_page_search_form_hidden_fields($hidden_fields = '', $page = '', $report_name = ''){
			$hidden_fields['report_type'] 		= isset($_REQUEST['report_type']) ? $_REQUEST['report_type'] : 'parent';
			if($hidden_fields['report_type'] == 'parent'){
				$hidden_fields['limit'] 		= 99999999;
			}
			return $hidden_fields;
		}
		
		/**
		* ic_commerce_report_page_start_date
		* @param string $start_date
		* @param string $report_name
		* @return date
		*/
		function ic_commerce_report_page_start_date($start_date = '', $report_name = ''){
			
			$today_date = isset($this->constants['today_date']) ? $this->constants['today_date'] : date_i18n("Y-m-d");
			$time_today = strtotime($today_date);
			$start_date = date("Y-m-01",strtotime("-2 month", strtotime($today_date)));
			
			return $start_date;
		}
		
		/**
		* ic_commerce_report_page_end_date
		* @param string $end_date
		* @param string $report_name
		* @return date
		*/
		function ic_commerce_report_page_end_date($end_date = '', $report_name = ''){
			
			$today_date = isset($this->constants['today_date']) ? $this->constants['today_date'] : date_i18n("Y-m-d");
			$time_today = strtotime($today_date);
			$end_date = date("Y-m-t",strtotime($today_date));
			
			return $end_date;
		}
		
		/**
		* ic_commerce_report_page_grid_price_columns
		* @param array $custom_columns
		* @return array
		*/
		function ic_commerce_report_page_grid_price_columns($custom_columns = array()){
			$custom_columns['total_sales_amount'] 			= 'total_sales_amount';
			$custom_columns['new_customer_sales_amount'] 	= 'new_customer_sales_amount';
			$custom_columns['repeat_customer_sales_amount'] = 'repeat_customer_sales_amount';
			return $custom_columns;
		}
		
		/**
		* ic_commerce_pdf_custom_column_right_alignment
		* @param array $custom_columns
		* @return array
		*/
		function ic_commerce_pdf_custom_column_right_alignment($custom_columns = array()){
			$custom_columns['total_sales_amount'] 			= 'total_sales_amount';
			$custom_columns['new_customer_sales_amount'] 	= 'new_customer_sales_amount';
			$custom_columns['repeat_customer_sales_amount'] = 'repeat_customer_sales_amount';			
			$custom_columns['new_customer_count'] 			= 'new_customer_count';
			$custom_columns['repeat_customer_count'] 		= 'repeat_customer_count';
			$custom_columns['total_order_count'] 			= 'total_order_count';
			return $custom_columns;
		}
		
		/**
		* ic_commerce_report_page_titles
		* @param string $page_titles
		* @param string $report_name
		* @param string $plugin_options
		* @return string
		*/
		function ic_commerce_report_page_titles($page_titles = '',$report_name = '', $plugin_options = ''){
			//$page_titles['summary_stock_planner'] = __('Summary Stock Planner',	'icwoocommerce_textdomains');
			return $page_titles;
		}
		
		/**
		* ic_commerce_report_page_title
		* @param string $page_titles
		* @param string $report_name
		* @param string $plugin_options
		* @return string
		*/
		function ic_commerce_report_page_title($page_title = '',$report_name = '', $plugin_options = ''){
			$page_title = __('New Customer/Repeat Customer Analysis',	'icwoocommerce_textdomains');
			return $page_title;
		}
		
		/**
		* ic_commerce_report_page_columns
		* @param array $columns
		* @param string $report_name
		* @return array
		*/
		function ic_commerce_report_page_columns($columns = array(), $report_name = ''){
			$columns 	= array();
			$columns["month_name"] 						= __("Months", 							'icwoocommerce_textdomains');
			$columns["total_sales_amount"] 				= __("Total Sales Amt.", 				'icwoocommerce_textdomains');
			$columns["total_order_count"] 				= __("Total Order Count", 				'icwoocommerce_textdomains');
			$columns["new_customer_count"] 				= __("New Customer Count", 				'icwoocommerce_textdomains');
			$columns["repeat_customer_count"] 			= __("Repeat Customer Count", 			'icwoocommerce_textdomains');
			$columns["new_customer_sales_amount"] 		= __("New Customer Sales Amt.", 		'icwoocommerce_textdomains');
			$columns["repeat_customer_sales_amount"] 	= __("Repeat Customer Sales Amt.", 		'icwoocommerce_textdomains');
			return $columns;
			
		}
		
		/**
		* ic_commerce_report_page_result_columns
		* This Function is used for Result page Columns.
		* @param array $total_columns
		* @param string $report_name
		* @return array
		*/
		function ic_commerce_report_page_result_columns($total_columns = array(), $report_name = ''){
			$columns = array();
			$columns["total_row_count"] 				= __("Months Count", 					'icwoocommerce_textdomains');
			$columns["total_sales_amount"] 				= __("Total Sales Amt.", 				'icwoocommerce_textdomains');
			$columns["total_order_count"] 				= __("Total Order Count", 				'icwoocommerce_textdomains');
			$columns["new_customer_count"] 				= __("New Customer Count", 				'icwoocommerce_textdomains');
			$columns["repeat_customer_count"] 			= __("Repeat Customer Count", 			'icwoocommerce_textdomains');
			$columns["new_customer_sales_amount"] 		= __("New Customer Sales Amt.", 		'icwoocommerce_textdomains');
			$columns["repeat_customer_sales_amount"] 	= __("Repeat Customer Sales Amt.", 		'icwoocommerce_textdomains');
			return $columns;
		}
		
		/**
		* ic_commerce_report_page_default_items
		* This Function is used for page Default Page Items.
		* @param string $rows
		* @param string $type
		* @param string $columns
		* @param string $report_name
		* @param string $that
		* @return array
		*/
		function ic_commerce_report_page_default_items($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){
			return $this->ic_commerce_custom_all_summary_sales_report_query($rows, $type, $columns, $report_name, $that);
		}
		
		/**
		* ic_commerce_custom_all_summary_sales_report_query
		* @param string $rows
		* @param string $type
		* @param string $columns
		* @param string $report_name
		* @param string $that
		* @return array
		*/
		function ic_commerce_custom_all_summary_sales_report_query($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){
			
			$order_items = $this->get_total_sales_amount($rows, $type, $columns, $report_name, $that);
			
			return $order_items;
			
		}
		
		/**
		* get_total_sales_amount
		* @param string $rows
		* @param string $type
		* @param string $columns
		* @param string $report_name
		* @param string $that
		* @return array
		*/
		function get_total_sales_amount($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){
			global $wpdb;
			
			$request = $that->get_all_request();
			
			if(!isset($this->items_query)){
				
				extract($request);
				
				$order_status			= $that->get_string_multi_request('order_status',$order_status, "-1");
				
				$hide_order_status		= $that->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				
				$sql = "SELECT ";				
				
				$sql .= " SUM(order_total.meta_value)					AS order_total";
				
				$sql .= ", COUNT(posts.ID) 								AS order_count";
				
				$sql .= ", DATE_FORMAT(posts.post_date,'%Y%m') 			AS month_key";
				
				$sql .= ", DATE_FORMAT(posts.post_date,'%M, %Y') 		AS month_name";
				
				$sql .= ", billing_email.meta_value						AS billing_email";
				
				//$sql .= ", MONTHNAME(posts.post_date) 				AS month_name";
				
				$sql .= " FROM {$wpdb->posts} 					AS posts";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS order_total ON order_total.post_id = posts.ID";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS billing_email ON billing_email.post_id = posts.ID";
				
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= " WHERE posts.post_type		= 'shop_order'";
				
				$sql .= " AND order_total.meta_key 	= '_order_total'";
				
				$sql .= " AND billing_email.meta_key 	= '_billing_email'";
				
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
				
				$sql .= " GROUP BY month_key, billing_email";
				
				$sql .= " ORDER BY month_key ASC";
				
				$that->items_query = $sql;
				
			}else{
				$sql = $that->items_query;
			}
			
			$order_items = $that->get_query_items($type,$sql);
			
			
			
			if($type != "total_row"){
				$array_match 			= array();
				$result_list 			= array();
				$total_customers		= array();
				$group_customers		= $this->get_old_customer_emails($request, $that);
				$repeat_customers		= array();
				$repeat_total_amt		= array();
				$new_total_amt			= array();
				$montsh_array			= array();
				
				//$this->print_array($group_customers);
				
				//$group_customers['vidhi@egmail.com'] = 'vidhi@egmail.com';
				
				foreach($order_items as $order_key => $order_item){
					$order_total 	= $order_item->order_total;
					$order_count 	= $order_item->order_count;
					$month_key 		= $order_item->month_key;
					$billing_email 	= $order_item->billing_email;
					$month_name 	= $order_item->month_name;
					if(isset($result_list[$month_key])){
						$result_list[$month_key]['order_total'] 	= $result_list[$month_key]['order_total'] + $order_total;
						$result_list[$month_key]['order_count'] 	= $result_list[$month_key]['order_count'] + $order_count;
					}else{
						$result_list[$month_key]['order_total'] 	= $order_total;
						$result_list[$month_key]['order_count'] 	= $order_count;
						$result_list[$month_key]['month_key'] 		= $month_key;
						$result_list[$month_key]['month_name'] 		= $month_name;
					}
					
					if(isset($group_customers[$billing_email])){						
						$repeat_customers[$month_key][$billing_email] = $billing_email;
						$repeat_total_amt[$month_key] = isset($repeat_total_amt[$month_key]) ? ($repeat_total_amt[$month_key] + $order_total) : $order_total;
					}else{
						if($order_count > 1){
							 $repeat_customers[$month_key][$billing_email] = $billing_email;
							 $repeat_total_amt[$month_key] = isset($repeat_total_amt[$month_key]) ? ($repeat_total_amt[$month_key] + $order_total) : $order_total;
						}
						
						if(!isset($total_customers[$month_key][$billing_email])){
							$total_customers[$month_key][$billing_email] = $billing_email;
							$new_total_amt[$month_key] = isset($new_total_amt[$month_key]) ? ($new_total_amt[$month_key] + $order_total) : $order_total;
						}
					}
					
					$result_list[$month_key]['repeat_customer'] 	= isset($repeat_customers[$month_key]) ? count($repeat_customers[$month_key]) : 0;
					$result_list[$month_key]['new_customer'] 		= isset($total_customers[$month_key]) ? count($total_customers[$month_key]) :0 ;
					
					$result_list[$month_key]['new_total'] 		= isset($new_total_amt[$month_key]) ? $new_total_amt[$month_key] : 0;
					$result_list[$month_key]['repeat_total'] 	= isset($repeat_total_amt[$month_key]) ? $repeat_total_amt[$month_key] : 0;
					
					$group_customers[$billing_email] = $billing_email;
					
					
					
				}//End Foreach
				
				
				$order_items = array();
				$x			= count($result_list);
				$i			= 0;
				
				
				foreach($result_list as $month_key => $list){
					$montsh_array[]	= $list['month_key'];
				}
				
				//$this->print_array($montsh_array);
				
				
				for($x;$x>0;$x--){
					$key = $montsh_array[$x-1];
					$order_items[$i] = new stdClass();
					$order_items[$i]->month_key 					= $result_list[$key]['month_key'];
					$order_items[$i]->month_name 					= $result_list[$key]['month_name'];
					$order_items[$i]->total_sales_amount 			= $result_list[$key]['order_total'];
					$order_items[$i]->total_order_count 			= $result_list[$key]['order_count'];
					$order_items[$i]->repeat_customer_count 		= $result_list[$key]['repeat_customer'];
					$order_items[$i]->new_customer_count 			= $result_list[$key]['new_customer'];
					$order_items[$i]->new_customer_sales_amount 	= $result_list[$key]['new_total'];
					$order_items[$i]->repeat_customer_sales_amount 	= $result_list[$key]['repeat_total'];
					$i++;
				}
				/*foreach($result_list as $month_key => $list){
					$order_items[$i] = new stdClass();
					$order_items[$i]->month_key 					= $list['month_key'];
					$order_items[$i]->month_name 					= $list['month_name'];
					$order_items[$i]->total_sales_amount 			= $list['order_total'];
					$order_items[$i]->total_order_count 			= $list['order_count'];
					$order_items[$i]->repeat_customer_count 		= $list['repeat_customer'];
					$order_items[$i]->new_customer_count 			= $list['new_customer'];
					$order_items[$i]->new_customer_sales_amount 	= $list['new_total'];
					$order_items[$i]->repeat_customer_sales_amount 	= $list['repeat_total'];
					$i++;
				}*/
				
				
				
				$that->all_row_result = $order_items;
			}else{
				
				$order_items = $that->get_query_items($type,$sql);
				
			}
			
			return $order_items;
		}
		
		/**
		* get_old_customer_emails
		* This Function is used to get Old Customer Emals.
		* @param string $request
		* @param string $that
		* @return array
		*/
		function get_old_customer_emails($request, $that){
				global $wpdb;
				extract($request);
				
				$order_status			= $that->get_string_multi_request('order_status',$order_status, "-1");
				
				$hide_order_status		= $that->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				
				$sql = "SELECT ";				
				
				$sql .= " billing_email.meta_value						AS billing_email";
				
				$sql .= " FROM {$wpdb->posts} 					AS posts";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS billing_email ON billing_email.post_id = posts.ID";
				
				$sql .= " WHERE posts.post_type		= 'shop_order'";
				
				$sql .= " AND billing_email.meta_key 	= '_billing_email'";
				
				if ($start_date != NULL){
					$sql .= " AND DATE(posts.post_date) < '".$start_date."'";
				}
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
				
				if($order_status  && $order_status != '-1' and $order_status != "'-1'") $sql .= " AND posts.post_status IN (".$order_status.")";
				
				$sql .= " GROUP BY billing_email";
				
				$sql .= " ORDER BY billing_email ASC";
				
				$order_items = $wpdb->get_results($sql);
				$old_customers = array();
				foreach($order_items as $order_key => $order_item){
					$old_customers[$order_item->billing_email] = $order_item->billing_email;
				}				
				return $old_customers;
		}
		
		/**
		* ic_commerce_report_page_footer_area
		* This Function is used to get Page Footer Area.
		* @return array
		*/
		function ic_commerce_report_page_footer_area(){
			?>
            	<script type="text/javascript">
					var last_dates = new Array();
					function LastDayOfMonth(Year, Month){
						var date1 = new Date((new Date(Year, Month+1,1))-1);
						var last_date = date1.getDate();
						return last_date;
					}
                	jQuery(document).ready(function($) {
                        $('#start_date').datepicker({
								dateFormat : 'yy-mm-dd',								
								changeMonth: true,
								changeYear: true,
								maxDate:ic_commerce_vars['max_date_start_date'],
							   	beforeShowDay: function (date) {
									if (date.getDate() == 1) {
										return [true, ''];
									}
									return [false, ''];
								},								
								onClose: function( selectedDate ) {
									$( "#end_date" ).datepicker( "option", "minDate", selectedDate );
								}
						});
						
						$('#end_date').datepicker({
								dateFormat : 'yy-mm-dd',
								changeMonth: true,
								changeYear: true,
							   	beforeShowDay: function (date) {
									var current_date = date.getDate();
									if(current_date == 28 || current_date == 29 || current_date == 30 || current_date == 31){
										var last_date = LastDayOfMonth(date.getFullYear(),date.getMonth())
										if (current_date == last_date) {
											return [true, ''];
										}
									}
									return [false, ''];
								},								
								onClose: function( selectedDate ) {
									$( "#start_date" ).datepicker( "option", "maxDate", selectedDate );
								}
						});
                    });
                </script>
            <?php
		}
		
	}//End Class
}//End 