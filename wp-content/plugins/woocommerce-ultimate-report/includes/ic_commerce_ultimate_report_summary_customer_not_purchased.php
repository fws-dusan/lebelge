<?php  
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
if(!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Customer_Not_Purcahsed')){
	
	class IC_Commerce_Ultimate_Woocommerce_Report_Customer_Not_Purcahsed  extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
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
			//add_action("ic_commerce_report_page_titles", 					array($this, "ic_commerce_report_page_titles"),31,2);
			
			$report_name = isset($_REQUEST['report_name']) ? $_REQUEST['report_name'] : '';
			if($report_name == "customer_not_purchased"){	
				add_action("ic_commerce_report_page_start_date", 					array($this, "ic_commerce_report_page_start_date"),						31,2);
				add_action("ic_commerce_report_page_default_items", 				array($this, "ic_commerce_report_page_default_items"),					31,5);
				add_action("ic_commerce_report_page_columns", 						array($this, "ic_commerce_report_page_columns"),						31,2);
				add_action("ic_commerce_report_page_result_columns", 				array($this, "ic_commerce_report_page_result_columns"),					31,2);
				add_action("ic_commerce_report_page_no_date_fields_tabs", 			array($this, "ic_commerce_report_page_no_date_fields_tabs"),			31,2);				
				add_action("ic_commerce_report_page_search_form_below_date_fields", array($this, "ic_commerce_report_page_search_form_below_date_fields"),	31,2);
				add_filter("ic_commerce_pdf_custom_column_right_alignment", 		array($this, "ic_commerce_pdf_custom_column_right_alignment"),			31,2);
				add_filter("ic_commerce_report_page_title", 						array($this, "ic_commerce_report_page_title"),							31,2);
			}
		}
		
		
		/**
		* ic_commerce_report_page_title
		*
		*
		* @param string $page_title
		* @param string $report_name
		* @param string $plugin_options
		*
		* @return string $page_title
		*/
		function ic_commerce_report_page_title($page_title = '',$report_name = '', $plugin_options = ''){
			$page_title = __('Customer Who Has Not Purchased',	'icwoocommerce_textdomains');
			return $page_title;
		}
		
		/**
		* ic_commerce_report_page_titles
		*
		*
		* @param string $page_title
		* @param string $report_name
		* @param string $plugin_options
		*
		* @return string $page_title
		*/
		function ic_commerce_report_page_titles($page_titles = '',$report_name = '', $plugin_options = ''){
			//$page_titles['customer_not_purchased'] = __('Customer Who Has Not Purchased',	'icwoocommerce_textdomains');
			return $page_titles;
		}
		
		/**
		* ic_commerce_report_page_no_date_fields_tabs
		*
		*
		* @param array $tabs
		* @param string $report_name
		*
		*
		* @return array $tabs
		*/
		function ic_commerce_report_page_no_date_fields_tabs($tabs = array(),$report_name = ''){
			$tabs['customer_not_purchased'] = 'customer_not_purchased';
			return $tabs;	
		}
		
		
		/**
		* ic_commerce_report_page_search_form_below_date_fields
		*
		*
		*
		* @return void
		*/
		function ic_commerce_report_page_search_form_below_date_fields(){
			$start_date = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : '';
			$end_date 	= isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : '';
			
			$billing_name 	= isset($_REQUEST['billing_name']) ? $_REQUEST['billing_name'] : '';
			$billing_email 	= isset($_REQUEST['billing_email']) ? $_REQUEST['billing_email'] : '';
			
			?>
            	 <div class="form-group">
					<div class="FormRow FirstRow">
						<div class="label-text"><label for="start_date"><?php _e("Avg. Calc From Date:",'icwoocommerce_textdomains'); ?></label></div>
						<div class="input-text"><input type="text" value="<?php echo $start_date;?>" id="start_date" name="start_date" readonly maxlength="10" /></div>
					</div>
					<div class="FormRow">
						<div class="label-text"><label for="end_date"><?php _e("Avg. Calc To Date:",'icwoocommerce_textdomains'); ?></label></div>
						<div class="input-text"><input type="text" value="<?php echo $end_date;?>" id="end_date" name="end_date" readonly maxlength="10" /></div>
					</div>
				</div>
                    
				 <div class="form-group">
					<div class="FormRow FirstRow">
						<div class="label-text"><label for="billing_name"><?php _e("Billing Name:",'icwoocommerce_textdomains'); ?></label></div>
						<div class="input-text"><input type="text" value="<?php echo $billing_name;?>" id="billing_name" name="billing_name" maxlength="100" /></div>
					</div>
					<div class="FormRow">
						<div class="label-text"><label for="billing_email"><?php _e("Billing Email:",'icwoocommerce_textdomains'); ?></label></div>
						<div class="input-text"><input type="text" value="<?php echo $billing_email;?>" id="billing_email" name="billing_email" maxlength="200" /></div>
					</div>
				</div>
            <?php 
		}
		/**
		* ic_commerce_report_page_start_date
		*
		*
		* @param string $start_date
		* @param string $report_name
		*
		* @return date $start_date
		*/
		function ic_commerce_report_page_start_date($start_date = '',$report_name = ''){
			$start_date = $this->constants['today_date'];
			return $start_date;
		}
		/**
		* ic_commerce_report_page_default_items
		*
		*
		* @param array $rows
		* @param string $type
		* @param array $columns
		* @param string $report_name
		* @param string $parent_this
		*
		* @return array $rows
		*/
		function ic_commerce_report_page_default_items($rows = array(), $type = "limit_row", $columns = array(), $report_name = "", $parent_this = NULL){
			$rows 		= $this->ic_commerce_custom_all_customer_not_purchased_query($type, $columns, $report_name, $parent_this);
			return $rows;
		}
		/**
		* ic_commerce_report_page_columns
		*
		*
		* @param array $columns
		* @param string $report_name
		*
		* @return array $columns
		*/
		function ic_commerce_report_page_columns($columns = array(), $report_name = ""){
			
			$columns 	= array(
				"billing_first_name"	=> __("Billing First Name", 	'icwoocommerce_textdomains')
				,"billing_last_name"	=> __("Billing Last Name", 		'icwoocommerce_textdomains')
				,"billing_email"		=> __("Billing Email", 			'icwoocommerce_textdomains')
				,"order_count"			=> __("Order Count", 			'icwoocommerce_textdomains')
				,"total_amount"			=> __("Amount", 				'icwoocommerce_textdomains')
				,"order_date"			=> __("Last Order Date", 		'icwoocommerce_textdomains')
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
		* @return array $columns
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
		* ic_commerce_pdf_custom_column_right_alignment
		*
		*
		* @param array $custom_columns
		*
		* @return array $custom_columns
		*/
		function ic_commerce_pdf_custom_column_right_alignment($custom_columns = array()){
			$custom_columns['order_date'] 			= 'order_date';
			return $custom_columns;
		}
		/**
		* ic_commerce_custom_all_customer_not_purchased_query
		*
		*
		* @param string $type
		* @param array $columns
		* @param string $report_name
		* @param string $parent_this
		*
		* @return array $order_items
		*/
		/*Customers not purchased*/		
		function ic_commerce_custom_all_customer_not_purchased_query($type = 'limit_row', $columns = array(), $report_name = "", $parent_this = NULL){
				global $wpdb;
				if(!isset($parent_this->items_query)){
					$request = $parent_this->get_all_request();extract($request);
					
					$order_status			= $parent_this->get_string_multi_request('order_status',$order_status, "-1");
					$hide_order_status		= $parent_this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
					
					$customers 				= $this->ic_commerce_custom_all_customer_purchased_query($parent_this);
					
					$customer_ids = array();
					$customer_emails = array();
					foreach($customers as $key => $values){
						$customer_ids[] = $values->customer_id;
						$customer_emails[] = $values->billing_email;
					}
					
					
											
					$sql = " SELECT ";
					$sql .= " SUM(postmeta1.meta_value) 		AS 'total_amount'";					
					$sql .= ", postmeta2.meta_value 			AS 'billing_email'";					
					$sql .= ", postmeta3.meta_value 			AS 'billing_first_name'";					
					$sql .= ", COUNT(postmeta2.meta_value) 		AS 'order_count'";					
					$sql .= ", postmeta4.meta_value 			AS  customer_id";
					$sql .= ", postmeta5.meta_value 			AS  billing_last_name";
					$sql .= ", MAX(posts.post_date)				AS  order_date";
					$sql .= ", CONCAT(postmeta3.meta_value, ' ',postmeta5.meta_value) AS billing_name";
					
					$sql .= " FROM {$wpdb->posts} as posts
					LEFT JOIN  {$wpdb->postmeta} as postmeta1 ON postmeta1.post_id=posts.ID
					LEFT JOIN  {$wpdb->postmeta} as postmeta2 ON postmeta2.post_id=posts.ID
					LEFT JOIN  {$wpdb->postmeta} as postmeta3 ON postmeta3.post_id=posts.ID";
					
					$sql .= " LEFT JOIN  {$wpdb->postmeta} as postmeta4 ON postmeta4.post_id=posts.ID";
					
					$sql .= " LEFT JOIN  {$wpdb->postmeta} as postmeta5 ON postmeta5.post_id=posts.ID";
					
					if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
							$sql .= " 
							LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
							LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
					
					$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
					
					$sql .= " WHERE 1*1";
					$sql .= " AND posts.post_type		= 'shop_order' ";
					$sql .= " AND postmeta1.meta_key	= '_order_total' ";
					$sql .= " AND postmeta2.meta_key	= '_billing_email'";
					$sql .= " AND postmeta3.meta_key	= '_billing_first_name'";					
					$sql .= " AND postmeta4.meta_key	= '_customer_user'";
					$sql .= " AND postmeta5.meta_key	= '_billing_last_name'";
					
					if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
						$sql .= " AND  term_taxonomy.term_id IN ({$order_status_id})";
					}
					
					if(strlen($publish_order)>0 && $publish_order != "-1" && $publish_order != "no" && $publish_order != "all"){
						$in_post_status		= str_replace(",","','",$publish_order);
						$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
					}
					
					if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND posts.post_status IN (".$order_status.")";
					
					if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
					
					if(count($customer_emails)>0){
						$in_customer_emails		= implode("','",$customer_emails);
						$sql .= " AND  postmeta2.meta_value NOT IN ('{$in_customer_emails}')";
					}
					
					if(count($billing_email)>0){
						$sql .= " AND  postmeta2.meta_value LIKE '%{$billing_email}%'";
					}
					
					if($billing_name and $billing_name != '-1'){
						$sql .= " AND (lower(concat_ws(' ', postmeta3.meta_value, postmeta5.meta_value)) like lower('%".$billing_name."%') OR lower(concat_ws(' ', postmeta5.meta_value, postmeta3.meta_value)) like lower('%".$billing_name."%'))";
					}
					
					$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
					
					$sql .= " GROUP BY  postmeta2.meta_value Order By billing_first_name ASC, billing_last_name ASC";
					
					
					
					$parent_this->items_query = $sql;
				}else{
					$sql = $parent_this->items_query;
				}
				
				$order_items = $parent_this->get_query_items($type,$sql);
				return $order_items;
		}
		/**
		* ic_commerce_custom_all_customer_purchased_query
		*
		*
		* @param string $parent_this
		*
		*
		* @return array $customer
		*/
		function ic_commerce_custom_all_customer_purchased_query($parent_this = NULL){
			global $wpdb;
			
			$request = $parent_this->get_all_request();extract($request);
			
			$order_status			= $parent_this->get_string_multi_request('order_status',$order_status, "-1");
			
			$hide_order_status		= $parent_this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
									
			$sql = " SELECT ";
			$sql .= " postmeta2.meta_value 		AS 	billing_email";
			$sql .= ", postmeta4.meta_value 	AS  customer_id";
			
			
			
			$sql .= " FROM {$wpdb->posts} as posts					
			LEFT JOIN  {$wpdb->postmeta} as postmeta2 ON postmeta2.post_id=posts.ID";
			
			$sql .= " LEFT JOIN  {$wpdb->postmeta} as postmeta4 ON postmeta4.post_id=posts.ID";
			if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
					$sql .= " 
					LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
					LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
			}
			
			$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
			
			$sql .= "
			
			WHERE  
			posts.post_type='shop_order'  
			AND postmeta2.meta_key='_billing_email'";
			
			$sql .= " AND postmeta4.meta_key='_customer_user'";
			
			
			if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
				$sql .= " AND  term_taxonomy.term_id IN ({$order_status_id})";
			}
			
			if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
				if ($start_date != NULL &&  $end_date !=NULL){
					$sql .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
				}
			}
			
			/*if ($start_date != NULL &&  $end_date !=NULL){
				$sql .= " AND DATE(posts.post_date) BETWEEN '".$start_date."' AND '". $end_date ."'";
			}*/
			if(strlen($publish_order)>0 && $publish_order != "-1" && $publish_order != "no" && $publish_order != "all"){
				$in_post_status		= str_replace(",","','",$publish_order);
				$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
			}
								
			if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND posts.post_status IN (".$order_status.")";
			
			if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
			
			$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
			
			$sql .= "  GROUP BY  postmeta2.meta_value Order By billing_email ASC";
			
			$customer = $wpdb->get_results($sql);
			
			//$this->print_array($wpdb);
			
			return $customer;
		}
	}//End Class
}//End 