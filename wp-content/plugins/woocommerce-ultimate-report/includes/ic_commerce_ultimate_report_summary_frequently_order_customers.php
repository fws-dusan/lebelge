<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Summary_Frequently_Order_Customer')){
	
	class IC_Commerce_Ultimate_Woocommerce_Report_Summary_Frequently_Order_Customer  extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		
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
			
			$this->constants['report_name'] = "frequently_order_customers";
			if($report_name == $this->constants['report_name']){
				add_filter("ic_commerce_report_page_title", 					array($this, "ic_commerce_report_page_title"),						31,2);
				add_filter("ic_commerce_report_page_default_items", 			array($this, "ic_commerce_report_page_default_items"),				31,5);
				add_filter("ic_commerce_report_page_columns", 					array($this, "ic_commerce_report_page_columns"),					31,2);
				add_filter("ic_commerce_report_page_result_columns", 			array($this, "ic_commerce_report_page_result_columns"),				31,2);
				add_filter("ic_commerce_pdf_custom_column_right_alignment", 	array($this, "ic_commerce_pdf_custom_column_right_alignment"),		31,2);
				add_filter("ic_commerce_report_page_start_date", 				array($this, "ic_commerce_report_page_start_date"),					31,2);
				add_filter("ic_commerce_report_page_end_date", 					array($this, "ic_commerce_report_page_end_date"),					31,2);
				add_filter("ic_commerce_report_page_bottom_of_report", 			array($this, "ic_commerce_report_page_bottom_of_report"),			31,1);
				add_filter("ic_commerce_report_page_search_form_hidden_fields", array($this, "ic_commerce_report_page_search_form_hidden_fields"),  31,1);				
				add_action("ic_commerce_report_page_search_form_bottom", 		array($this, "ic_commerce_report_page_search_form_bottom"),  		31,1);				
				add_action("ic_commerce_report_page_footer_area", 				array($this, "ic_commerce_report_page_footer_area"),  				31,1);
			}
		}
		/**
		* ic_commerce_report_page_footer_area
		*
		*
		*
		* @return void
		*/
		function ic_commerce_report_page_footer_area(){
			$report_type 		= isset($_REQUEST['report_type']) ? $_REQUEST['report_type'] : 'parent';
			if($report_type == "parent"):
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
			endif;
		}
		/**
		* ic_commerce_report_page_search_form_bottom
		*
		*
		*
		* @return void
		*/
		function ic_commerce_report_page_search_form_bottom(){
			$report_name 		= isset($_REQUEST['report_name']) ? $_REQUEST['report_name'] : '';
			if($report_name == 'frequently_order_customers'){
				$min_order_count 		= isset($_REQUEST['min_order_count']) ? $_REQUEST['min_order_count'] : 1;
			?>
            	<div class="form-group">
                    <div class="FormRow checkbox FirstRow">
                        <div class="label-text"><label for="min_order_count"><?php _e("Min Order Count:",'icwoocommerce_textdomains');?></label></div>
                        <div class="input-text">
                           <input type="text" name="min_order_count" id="min_order_count" value="<?php echo $min_order_count;?>" class="numberonly" maxlength="5" />
                        </div>
                    </div>
                </div>
            <?php  
			} 
		}
		/**
		* ic_commerce_report_page_search_form_hidden_fields
		*
		*
		* @param string $hidden_fields 
		* @param string $page
		* @param string $report_name
		*
		* @return array $hidden_fields
		*/
		function ic_commerce_report_page_search_form_hidden_fields($hidden_fields = '', $page = '', $report_name = ''){
			$hidden_fields['report_type'] 		= isset($_REQUEST['report_type']) ? $_REQUEST['report_type'] : 'parent';
			if($hidden_fields['report_type'] == 'parent'){
				$hidden_fields['limit'] 		= 99999999;
			}
			return $hidden_fields;
		}
		
		/**
		* ic_commerce_report_page_titles
		*
		*
		* @param string $page_titles
		* @param string $report_name
		* @param string $plugin_options
		*
		* @return array $page_titles
		*/
		function ic_commerce_report_page_titles($page_titles = '',$report_name = '', $plugin_options = ''){
			
			$page_titles[$this->constants['report_name']] = __('Frequently Order Customer',	'icwoocommerce_textdomains');
			
			/*$report_type = isset($_REQUEST['report_type']) ? $_REQUEST['report_type'] : '';
			
			if($report_type == "customer_count"){
				$page_titles[$this->constants['report_name']."_2"] = __('Frequently Order Customer',	'icwoocommerce_textdomains');
			}else{
				$page_titles[$this->constants['report_name']] = __('Frequently Order Customer',	'icwoocommerce_textdomains');
			}*/
			
			/*
			if($report_name == $this->constants['report_name']){
				$page_titles[$this->constants['report_name']] = __('Frequently Order Customer',	'icwoocommerce_textdomains');				
			}else{
				$page_titles[$this->constants['report_name']."_2"] = __('Frequently Order Customer',	'icwoocommerce_textdomains');
			};
			*/
			return $page_titles;
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
		function ic_commerce_report_page_title($page_title = '',$report_name = '', $plugin_options = ''){
			$report_type = isset($_REQUEST['report_type']) ? $_REQUEST['report_type'] : '';
			$page_title = __('Top n Customer Report Who Orders Frequently',	'icwoocommerce_textdomains');
			return $page_title;
		}
		/**
		* ic_commerce_report_page_bottom_of_report
		*
		* @param string $output
		*
		* @return string $output
		*/	
		function ic_commerce_report_page_bottom_of_report($output = ''){//return $output;
			$report_type = isset($_REQUEST['report_type']) ? $_REQUEST['report_type'] : '';
			if($report_type != "customer_count"){
				$months_columns = $this->get_months_columns();
				$output .= '<style type="text/css">';
				
				foreach($months_columns as $months_column_key => $months_column){
					$custom_columns[] 	= ".iccommercepluginwrap th.".$months_column_key;
					$custom_columns[] 	= ".iccommercepluginwrap td.".$months_column_key;
				}
				
				$output .= implode(", ", $custom_columns);
				$output .= "{text-align:right;}";
				$output .= '</style>';
			}
			
			return $output;
		}
		/**
		* ic_commerce_report_page_start_date
		*
		* @param date $start_date
		* @param string $report_name
		*
		* @return date $start_date
		*/
		function ic_commerce_report_page_start_date($start_date = '', $report_name = ''){
			$report_type = isset($_REQUEST['report_type']) ? $_REQUEST['report_type'] : '';			
			if($report_type != "customer_count"){
				$today_date = isset($this->constants['today_date']) ? $this->constants['today_date'] : date_i18n("Y-m-d");
				$time_today = strtotime($today_date);
				$start_date = date("Y-m-01",strtotime("-2 month", strtotime($today_date)));
			}
			
			return $start_date;
		}
		/**
		* ic_commerce_report_page_end_date
		*
		* @param date $start_date
		* @param string $report_name
		*
		* @return date $start_date
		*/
		function ic_commerce_report_page_end_date($end_date = '', $report_name = ''){
			$report_type = isset($_REQUEST['report_type']) ? $_REQUEST['report_type'] : '';			
			if($report_type != "customer_count"){
				$today_date = isset($this->constants['today_date']) ? $this->constants['today_date'] : date_i18n("Y-m-t");
				$end_date = date("Y-m-t",strtotime($today_date));
			}
			
			return $end_date;
		}
		/**
		* get_months_columns
		*
		*
		* @return array $constants
		*/
		function get_months_columns(){
			if(!isset($this->constants['months_columns'])){
				$today_date = isset($this->constants['today_date']) ? $this->constants['today_date'] : date_i18n("Y-m-d");
				$time_today = strtotime($today_date);			
				$m 			= 0;
				$months 	= array();
				$columns 	= array();
				
				
				$start_date = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : '';
				$end_date = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : '';
				
				$start_date_time = strtotime($start_date);
				$end_date_time = strtotime($end_date);
				
				$current_date_time = $start_date_time;
				$test = array();
				while($current_date_time <= $end_date_time){
					
					
					
					$month_name = date("F",$current_date_time);
					$month_key = date("Ym",$current_date_time);
					$columns["month_".$month_key] = $month_name;
					
					$current_date_time = strtotime("1 month",$current_date_time);
				}
				
				/*$this->print_array($test);
				
				for($m; $m<5; $m++){
					$month = strtotime(" - ".$m . "month",$time_today);
					$months[] = $month;
				}
				
				$months = array_reverse($months);				
				
				foreach($months as $month){
					$month_name = date("F",$month);
					$month_key = date("Ym",$month);
					$columns["month_".$month_key] = $month_name;
				}*/
				
				$this->constants['months_columns'] = $columns;
			}
			
			return $this->constants['months_columns'];
		}
		/**
		* ic_commerce_report_page_columns
		*
		*
		* @param array $columns
		* @param string $report_name
		*
		*
		* @return string $page_titles 
		*/
		function ic_commerce_report_page_columns($columns = array(), $report_name = ''){
			$columns 	= array();
			$report_type = isset($_REQUEST['report_type']) ? $_REQUEST['report_type'] : '';
							
			if($report_type == "customer_count"){
				
				$columns['billing_first_name'] 		= __('Billing First Name',	'icwoocommerce_textdomains');
				$columns['billing_last_name'] 		= __('Billing Last Name',	'icwoocommerce_textdomains');				
				//$columns['billing_name'] 			= __('Billing Name',		'icwoocommerce_textdomains');
				$columns['billing_email'] 			= __('Billing Email',		'icwoocommerce_textdomains');
				$columns['order_count'] 			= __('Order Count',			'icwoocommerce_textdomains');
				$columns['total_amount'] 			= __('Sales Amount',		'icwoocommerce_textdomains');
			}else{
				$columns['report_label'] 			= __('Report Item',			'icwoocommerce_textdomains');
				$months_columns = $this->get_months_columns();
				$columns = array_merge($columns, $months_columns);			
			}
			
			return $columns;
			
		}
		/**
		* ic_commerce_pdf_custom_column_right_alignment
		*
		*
		* @param array $custom_columns
		*
		*
		* @return string $page_titles 
		*/
		function ic_commerce_pdf_custom_column_right_alignment($custom_columns = array()){
			$report_type = isset($_REQUEST['report_type']) ? $_REQUEST['report_type'] : '';
			if($report_type != "customer_count"){
				$months_columns = $this->get_months_columns();			
				foreach($months_columns as $months_column_key => $months_column){
					$custom_columns[$months_column_key] 		= $months_column_key;
				}				
			}
			$custom_columns['total_amount'] 			= 'total_amount';
			$custom_columns['order_count'] 				= 'order_count';
			$custom_columns['avg_customer_count'] 		= 'avg_customer_count';
			return $custom_columns;
		}
		
		
		/**
		* ic_commerce_report_page_result_columns
		*
		*
		* @param array $total_columns
		* @param string $report_name
		*
		*
		* @return string $total_columns 
		*/
		function ic_commerce_report_page_result_columns($total_columns = array(), $report_name = ''){
			$report_type = isset($_REQUEST['report_type']) ? $_REQUEST['report_type'] : '';
			if($report_type == "customer_count"){
				$total_columns = array();
				$total_columns["total_row_count"] 	= __("Customer Count", 		'icwoocommerce_textdomains');
				$total_columns["order_count"] 		= __("Order Count", 		'icwoocommerce_textdomains');
				$total_columns["total_amount"] 		= __("Sales Amount", 		'icwoocommerce_textdomains');
			}else{
				$total_columns = array();
				$total_columns["avg_customer_count"]= __("Avg. Customer Count", 'icwoocommerce_textdomains');
				$total_columns["order_count"] 		= __("Order Count", 		'icwoocommerce_textdomains');
				$total_columns["total_amount"] 		= __("Sales Amount", 		'icwoocommerce_textdomains');
			}
			return $total_columns;
		}
		/**
		* ic_commerce_report_page_default_items
		*
		*
		* @param array $rows
		* @param string $type
		* @param string $columns
		* @param string $report_name
		* @param string $that
		*
		*
		* @return string $rows 
		*/	
		function ic_commerce_report_page_default_items($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){
			$report_type = isset($_REQUEST['report_type']) ? $_REQUEST['report_type'] : '';
			if($report_type == "customer_count"){
				return $this->ic_commerce_custom_all_customer_report_report_query($rows, $type, $columns, $report_name, $that);
			}else{
				return $this->ic_commerce_custom_all_summary_sales_report_query($rows, $type, $columns, $report_name, $that);
			}
			return $rows;	
		}
		/**
		* ic_commerce_custom_all_summary_sales_report_query
		*
		*
		* @param array $rows
		* @param string $type
		* @param string $columns
		* @param string $report_name
		* @param string $that
		*
		*
		* @return string $return_items 
		*/
		function ic_commerce_custom_all_summary_sales_report_query($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){			
			global $wpdb;
			
			if($type == "total_row"){
				//return array();
			}
			
			$request = $that->get_all_request();extract($request);
			$min_order_count 	= !empty($_REQUEST['min_order_count']) ? $_REQUEST['min_order_count'] : 1;
			
			
			if(!isset($this->items_query)){				
				$order_status			= $that->get_string_multi_request('order_status',$order_status, "-1");
				$hide_order_status		= $that->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				
				$sql = "SELECT ";				
				$sql .= " COUNT(DISTINCT posts.ID)									AS order_count";
				$sql .= ", SUM(order_total.meta_value) 								AS total_amount";				
				//$sql .= ", MONTHNAME(posts.post_date) 							AS order_month";
				//$sql .= ", YEAR(posts.post_date) 									AS order_year";
				$sql .= ", COUNT(DISTINCT postmeta_billing_email.meta_value)		AS customer_count";
				$sql .= ", DATE_FORMAT(posts.post_date,'month_%Y%m') 				AS month_key";
				//$sql .= ", MIN(DATE_FORMAT(posts.post_date,'%Y-%m-%d'))				AS min_date";
				//$sql .= ", MAX(DATE_FORMAT(posts.post_date,'%Y-%m-%d'))				AS max_date";
				
				$sql .= ", DATE_FORMAT(posts.post_date,'%Y-%m-01')					AS min_date";
				$sql .= ", DATE_FORMAT(LAST_DAY(posts.post_date),'%Y-%m-%d')		AS max_date";
				$sql .= ", postmeta_billing_email.meta_value						AS billing_email";
				$sql .= ", CONCAT(DATE_FORMAT(posts.post_date,'month_%Y%m'),'-',postmeta_billing_email.meta_value)		AS group_column";
				
				$sql .= " FROM {$wpdb->posts} AS posts";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS order_total ON order_total.post_id = posts.ID AND order_total.meta_key = '_order_total'";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS postmeta_billing_email ON postmeta_billing_email.post_id = posts.ID AND postmeta_billing_email.meta_key = '_billing_email'";
				
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
				
				$sql .= " GROUP BY group_column";
				
				$sql .= " ORDER BY posts.post_date DESC";
				
				$that->items_query = $sql;
				
				$_REQUEST['parent_categories_count'] = 1;	
							
			}else{
				$sql = $that->items_query;
			}
			
			if($type != "total_row"){
				
				$order_items = $that->get_query_items($type,$sql);
				
				if($type == "limit_row"){
					$customer_list_url = admin_url("admin.php")."?page={$admin_page}&report_name={$report_name}&report_type=customer_count&min_order_count={$min_order_count}";
				}
				
				$reports = array(
					"customer_count" 	=> "Customer Count"
					,"order_count" 		=> "Order Count"
					,"total_amount" 	=> "Sales Amount"
				);
				
				$i 								= 0;				
				$months 						= array();
				$months2 						= array();
				foreach($reports as $report_name => $report_label){
					$return_items[$i]				= new stdClass();
					$return_items[$i]->report_label	= $report_label;
					$return_items[$i]->report_name 	= $report_name;
					foreach($order_items as $order_key => $order_item){
							$month_key 					  	= $order_item->month_key;
							$order_count				 	= $order_item->order_count;
							$group_key						= $report_name."-".$month_key;
							
							if($order_count > $min_order_count){
								if(isset($months[$group_key])){
									$months[$group_key] 			= $months[$group_key] + $order_item->$report_name;
									$return_items[$i]->$month_key 	= $months[$group_key];
								}else{
									$months[$group_key] 			= $order_item->$report_name;
									$return_items[$i]->$month_key 	= $months[$group_key];
								}
								$months2[$month_key] = $order_item->min_date;
								$max_date[$month_key] = $order_item->max_date;
							}
							
					}
					$i++;
				}
				
				//die;
				//$this->print_array($return_items);
				
				if($type == "limit_row"){					 
					foreach($return_items as $order_key => $order_item){
					
						if($order_item->report_name == "total_amount" and $type == "limit_row"){
							foreach($months2 as $months2_key => $months2_item){
								$column_value								= $order_item->$months2_key;
								$column_value								= $this->price($column_value);
								$start_date									= $months2_item;
								$end_date									= $max_date[$months2_key];
								$column_value 								= "<a href=\"{$customer_list_url}&start_date={$start_date}&end_date={$end_date}\" target=\"customer_count_blank_{$months2_key}\">$column_value</a>";
								$return_items[$order_key]->$months2_key 	= $column_value;
							}
							
						}
						/*
						else if($order_item->report_name == "customer_count" and $type == "limit_row"){							
							foreach($months2 as $months2_key => $months2_item){
								$column_value								= $order_item->$months2_key;
								$start_date									= $months2_item;
								$end_date									= $max_date[$months2_key];
								$column_value 								= "<a href=\"{$customer_list_url}&start_date={$start_date}&end_date={$end_date}\" target=\"customer_count_blank_{$months2_key}\">$column_value</a>";
								$return_items[$order_key]->$months2_key 	= $column_value;
							}
						}
						*/
					}
					
					
					
				}
				
				if(isset($_REQUEST['icwoocommerceultimatereport_report_page_export_pdf'])){
					foreach($return_items as $order_key => $order_item){					
						if($order_item->report_name == "total_amount"){
							foreach($months2 as $months2_key => $months2_item){
								$return_items[$order_key]->$months2_key 	= $this->price($order_item->$months2_key);
							}
						}
					}
				}
				
				$that->all_row_result = $return_items;
				return $return_items;
				
			}else{
				
				////////////////////
				if(isset($this->all_row_result) and $this->all_row_result){
						if($count_generated == 1){
							$order_items = $that->create_summary($request);
						}else{
							$order_items = $this->all_row_result;
							$order_items = $that->get_count_total($order_items,'total_amount');
						}
						
					}else{					
						if($count_generated == 1 || ($p > 1)){
							$order_items = $that->create_summary($request);							
						}else{
							$order_items = $wpdb->get_results($sql);
							$months = array();
							if($order_items){
								foreach($order_items as $order_key => $order_item){							
									$order_count				 	= $order_item->order_count;
									if($order_count <= $min_order_count){
										unset($order_items[$order_key]);
									}else{
										$month_key	= $order_item->month_key;
										$months[$month_key] = 1;
									}
								}	
								if($order_items){
									$order_items = $that->get_count_total($order_items,'total_amount');								
									$order_items['avg_customer_count'] = $order_items['total_row_count']/count($months);
								}
							}
							
						}
					}	
					//$order_items = $that->get_query_items($type,$sql);				
					return $order_items;
				
			}
			
			return $return_items;
		}
		/**
		* ic_commerce_custom_all_customer_report_report_query
		*
		*
		* @param array $rows
		* @param string $type
		* @param string $columns
		* @param string $report_name
		* @param string $that
		*
		*
		* @return string $return_items 
		*/
		function ic_commerce_custom_all_customer_report_report_query($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){			
			global $wpdb;			
			$request = $that->get_all_request();extract($request);			
			$min_order_count 	= !empty($_REQUEST['min_order_count']) ? $_REQUEST['min_order_count'] : 1;
			
			if(!isset($this->items_query)){
				$order_status			= $that->get_string_multi_request('order_status',$order_status, "-1");
				$hide_order_status		= $that->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				
				$sql = "SELECT ";				
				
				$sql .= " COUNT(posts.ID)											AS order_count";
				
				$sql .= ", SUM(order_total.meta_value) 								AS total_amount";				
				
				$sql .= ", postmeta_billing_email.meta_value						AS billing_email";
				
				$sql .= ", postmeta_billing_first_name.meta_value					AS billing_first_name";
				
				$sql .= ", postmeta_billing_last_name.meta_value					AS billing_last_name";
				
				$sql .= "	,CONCAT(postmeta_billing_first_name.meta_value, ' ',postmeta_billing_last_name.meta_value) AS billing_name";
				
				$sql .= " FROM {$wpdb->posts} AS posts";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS order_total ON order_total.post_id = posts.ID AND order_total.meta_key = '_order_total'";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS postmeta_billing_email ON postmeta_billing_email.post_id = posts.ID AND postmeta_billing_email.meta_key = '_billing_email'";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS postmeta_billing_first_name ON postmeta_billing_first_name.post_id = posts.ID AND postmeta_billing_first_name.meta_key = '_billing_first_name'";
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS postmeta_billing_last_name ON postmeta_billing_last_name.post_id = posts.ID AND postmeta_billing_last_name.meta_key = '_billing_last_name'";
				
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= " WHERE posts.post_type = 'shop_order'";
				
				if ($start_date != NULL &&  $end_date !=NULL){
					$sql .= " AND DATE(posts.post_date) BETWEEN '".$start_date."' AND '". $end_date ."'";
				}
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
				
				if($order_status  && $order_status != '-1' and $order_status != "'-1'") $sql .= " AND posts.post_status IN (".$order_status.")";
				
				//$sql .= "  AND COUNT(posts.ID) > 1";
				
				$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= " GROUP BY billing_email";
				
				if($min_order_count > 0) $sql .= "  HAVING COUNT(posts.ID) > {$min_order_count}";
				
				$sql .= " ORDER BY total_amount DESC";
				
				$that->items_query = $sql;
			}else{
				$sql = $that->items_query;
			}
			
			$order_items = $that->get_query_items($type,$sql);
			
			
			
			return $order_items;
		}
		
	}//End Class
}//End 