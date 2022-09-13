<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Summary_Stock_Planner')){
	
	class IC_Commerce_Ultimate_Woocommerce_Report_Summary_Stock_Planner  extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		
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
			
			if($report_name == "summary_stock_planner"){
				add_filter("ic_commerce_report_page_title", 					array($this, "ic_commerce_report_page_title"),						31,2);
				add_filter("ic_commerce_report_page_start_date", 				array($this, "ic_commerce_report_page_start_date"),					31,2);
				add_filter("ic_commerce_report_page_default_items", 			array($this, "ic_commerce_report_page_default_items"),				31,5);
				add_filter("ic_commerce_report_page_columns", 					array($this, "ic_commerce_report_page_columns"),					31,2);
				add_filter("ic_commerce_report_page_result_columns", 			array($this, "ic_commerce_report_page_result_columns"),				31,2);
				add_filter("ic_commerce_pdf_custom_column_right_alignment", 	array($this, "ic_commerce_pdf_custom_column_right_alignment"),		31,2);
				add_filter("ic_commerce_report_page_search_form_bottom", 		array($this, "ic_commerce_report_page_search_form_bottom"),			31,2);
			}
		}
		/**
		* ic_commerce_report_page_start_date
		*
		* Get start date
		*
		* @param date $start_date
		* @param string $report_name 
		* @return string   $start_date
		*/
		function ic_commerce_report_page_start_date($start_date = '', $report_name = ''){
			
			$today_date = isset($this->constants['today_date']) ? $this->constants['today_date'] : date_i18n("Y-m-d");
			$time_today = strtotime($today_date);
			$start_date = date("Y-m-d",strtotime("-15 day", strtotime($today_date)));
			
			return $start_date;
		}
		/**
		* ic_commerce_pdf_custom_column_right_alignment
		*
		* PDF custom columns right align
		*
		* @param array $custom_columns
		* @return array   $custom_columns
		*/
		function ic_commerce_pdf_custom_column_right_alignment($custom_columns = array()){
			$custom_columns['avg_sales_quantity'] 		= 'avg_sales_quantity';
			$custom_columns['current_stock_quantity'] 	= 'current_stock_quantity';
			$custom_columns['stock_valid_days'] 		= 'stock_valid_days';
			$custom_columns['stock_valid_date'] 		= 'stock_valid_date';
			return $custom_columns;
		}
		/**
		* ic_commerce_report_page_titles
		*
		* Display page title
		*
		* @param string $page_titles
		* @param string $report_name
		* @param string $plugin_options
		* @return string   $page_titles
		*/
		function ic_commerce_report_page_titles($page_titles = '',$report_name = '', $plugin_options = ''){
			//$page_titles['summary_stock_planner'] = __('Summary Stock Planner',	'icwoocommerce_textdomains');
			return $page_titles;
		}
		/**
		* ic_commerce_report_page_title
		*
		* Display page title
		*
		* @param string $page_titles
		* @param string $report_name
		* @param string $plugin_options
		* @return string   $page_titles
		*/
		function ic_commerce_report_page_title($page_title = '',$report_name = '', $plugin_options = ''){
			$page_title = __('Summary Stock Planner Based On Average Sales',	'icwoocommerce_textdomains');
			return $page_title;
		}
		

		/**
		* ic_commerce_report_page_columns
		*
		* Get columns by report name
		*
		* @param array $columns
		* @param string $report_name
		*
		* @return array   $columns
		*/
		function ic_commerce_report_page_columns($columns = array(), $report_name = ''){
			$columns 	= array();
			
			$product_type = $this->get_request('product_type','simple');
			
			switch($product_type){
				case "variation":
					$columns["product_sku"] 			= __("Product SKU", 							'icwoocommerce_textdomains');
					$columns["order_item_name"] 		= __("Product Name", 							'icwoocommerce_textdomains');
					$columns["variation_id"] 			= __("Variation ID", 							'icwoocommerce_textdomains');
					$columns["product_variation"] 		= __("Product Variation", 							'icwoocommerce_textdomains');
					$columns["avg_sales_quantity"] 		= __("Avg. Sales Qty.", 						'icwoocommerce_textdomains');
					$columns["current_stock_quantity"] 	= __("Current Stock Qty", 						'icwoocommerce_textdomains');
					$columns["stock_valid_days"] 		= __("Stock Valid Days", 						'icwoocommerce_textdomains');
					$columns["stock_valid_date"] 		= __("Till Date", 						        'icwoocommerce_textdomains');
					break;
				case "simple":
				default:
					$columns["product_sku"] 			= __("Product SKU", 							'icwoocommerce_textdomains');
					$columns["order_item_name"] 		= __("Product Name", 							'icwoocommerce_textdomains');
					$columns["avg_sales_quantity"] 		= __("Avg. Sales Qty.", 						'icwoocommerce_textdomains');
					$columns["current_stock_quantity"] 	= __("Current Stock Qty", 						'icwoocommerce_textdomains');
					$columns["stock_valid_days"] 		= __("Stock Valid Days", 						'icwoocommerce_textdomains');
					$columns["stock_valid_date"] 		= __("Till Date", 						        'icwoocommerce_textdomains');
					break;				
			}
			
			
			return $columns;
			
		}
		/**
		* ic_commerce_report_page_result_columns
		*
		* Get report total columns
		*
		* @param array $total_columns
		* @param string $report_name
		*
		* @return array   $total_columns
		*/
		function ic_commerce_report_page_result_columns($total_columns = array(), $report_name = ''){
			$total_columns = array();
			$total_columns["total_row_count"] 			= __("Product Count", 					'icwoocommerce_textdomains');
			//$total_columns["avg_sales_quantity"] 		= __("Avg. Sales Qty.(Last 15 Days)", 	'icwoocommerce_textdomains');
			$total_columns["current_stock_quantity"] 	= __("Current Stock Qty", 				'icwoocommerce_textdomains');
			return $total_columns;
		}
		
		
		/**
		* ic_commerce_report_page_search_form_bottom
		*
		* Create search form for toatl
		*
		*
		* @return void
		*/
		function ic_commerce_report_page_search_form_bottom(){
			?>
                <div class="form-group">
                    <div class="FormRow FirstRow">
                        <div class="label-text" style="padding-top:0px;"><label for="sort_by"><?php _e('Order By:','icwoocommerce_textdomains');?></label></div>
                        <div style="padding-top:0px;">
                             <?php
                                
                                $sorting_list = array();
								$sorting_list["order_item_name"] 		= __("Product Name",		'icwoocommerce_textdomains');
								$sorting_list["avg_sales_quantity"] 	= __("Avg. Quantity Sold",	'icwoocommerce_textdomains');
								$sorting_list['current_stock_quantity']	= __('Current Stock Qty',	'icwoocommerce_textdomains');
								$sorting_list["stock_valid_days"] 		= __("Stock Valid Days",	'icwoocommerce_textdomains');
								
								//$sorting_list["sales_quantity"] 		= __("Quantity Sold",		'icwoocommerce_textdomains');
								
								$sort_by		= "stock_valid_days";
								$sorting_list 	= apply_filters("ic_commerce_detail_page_sorting_list", $sorting_list);
                                $this->create_dropdown($sorting_list,"sort_by","sort_by",NULL,"sort_by",$sort_by, 'array');
                                
								$order_by		= "ASC";
                                $order_list = array("ASC" => __("Ascending",'icwoocommerce_textdomains'), "DESC" => __("Descending",'icwoocommerce_textdomains'));
                                $order_list = apply_filters("ic_commerce_detail_page_order_list", $order_list);
                                $this->create_dropdown($order_list,"order_by","order_by",NULL,"order_by",$order_by, 'array');
                            ?>
                        </div>
                        
                    </div>
                    
                    <div class="FormRow">
                        <div class="label-text"><label for="product_type"><?php _e('Product Type:','icwoocommerce_textdomains');?></label></div>
                        <div class="input-text">
                             <?php
                                $product_types = array('simple' => __('Simple Products'), 'variation' => __('Variation Products'));
								 $this->create_dropdown($product_types,"product_type","product_type",NULL,"product_type",$order_by, 'array');
                            ?>
                        </div>
                        
                    </div>
                </div>
            <?php 
		} 
		/**
		* ic_commerce_report_page_default_items
		*
		* Get default item
		*
		* @param array $rows
		* @param string $type
		* @param string $columns
		* @param string $report_name
		* @param string $that
		*
		* @return array  
		*/ 
		function ic_commerce_report_page_default_items($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){
			
			$product_type = $this->get_request('product_type','simple');
			
			switch($product_type){
				case "variation":
					return $this->ic_commerce_custom_all_summary_sales_report_variation_query($rows, $type, $columns, $report_name, $that);
					break;
				case "simple":
				default:
					return $this->ic_commerce_custom_all_summary_sales_report_query($rows, $type, $columns, $report_name, $that);
					break;				
			}
		}
		/**
		* ic_commerce_custom_all_summary_sales_report_variation_query
		*
		* 
		*
		* @param array $rows
		* @param string $type
		* @param string $columns
		* @param string $report_name
		* @param string $that
		*
		* @return array   
		*/
		function ic_commerce_custom_all_summary_sales_report_variation_query($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){
			
			global $wpdb;
			
			$request = $that->get_all_request();
			
			if(!isset($this->items_query)){
				
				extract($request);
				
				$date1 		= strtotime($start_date);
				
				$date2 		= strtotime($end_date);
				
				$datediff 	= $date2 - $date1;
				
				$difference = floor($datediff/(60*60*24));
				
				$order_status			= $that->get_string_multi_request('order_status',$order_status, "-1");
				
				$hide_order_status		= $that->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				
				$sql = "SELECT ";				
				
				$sql .= "  woocommerce_order_items.order_item_name 																	AS order_item_name";
				
				$sql .= ",  woocommerce_order_items.order_item_id 																	AS order_item_id";
				
				$sql .= ", woocommerce_order_itemmeta_product_id.meta_value 														AS product_id";
				
				$sql .= ", woocommerce_order_itemmeta_variation_id.meta_value 														AS variation_id";
				
				$sql .= ", SUM(woocommerce_order_itemmeta_qty.meta_value)															AS sales_quantity";
				
				$sql .= ", postmeta_stock.meta_value																				AS current_stock_quantity";
				
				$sql .= ", SUM(woocommerce_order_itemmeta_qty.meta_value)/$difference												AS avg_sales_quantity";
				
				$sql .= ", ROUND((postmeta_stock.meta_value/(SUM(woocommerce_order_itemmeta_qty.meta_value)/$difference)))			AS stock_valid_days";
				
				$sql .= ", postmeta_manage_stock.meta_value																			AS manage_stock";
				
				$sql .= " FROM {$wpdb->prefix}woocommerce_order_items 																AS woocommerce_order_items";
				
				$sql .= " LEFT JOIN {$wpdb->posts} AS posts ON posts.ID = woocommerce_order_items.order_id";
				
				$sql .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woocommerce_order_itemmeta_qty 			ON woocommerce_order_itemmeta_qty.order_item_id 			= woocommerce_order_items.order_item_id";
				
				$sql .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woocommerce_order_itemmeta_product_id 		ON woocommerce_order_itemmeta_product_id.order_item_id 		= woocommerce_order_items.order_item_id";
				
				$sql .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woocommerce_order_itemmeta_variation_id 	ON woocommerce_order_itemmeta_variation_id.order_item_id 	= woocommerce_order_items.order_item_id";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS postmeta_manage_stock 	ON postmeta_manage_stock.post_id 	= woocommerce_order_itemmeta_variation_id.meta_value";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS postmeta_stock 		ON postmeta_stock.post_id 			= woocommerce_order_itemmeta_variation_id.meta_value";
				
				$sql .= " WHERE posts.post_type 								= 'shop_order'";
				
				$sql .= " AND woocommerce_order_itemmeta_qty.meta_key 			= '_qty'";
				
				$sql .= " AND woocommerce_order_itemmeta_product_id.meta_key 	= '_product_id'";
				
				$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_key 	= '_variation_id'";
				
				$sql .= " AND postmeta_manage_stock.meta_key 					= '_manage_stock'";
				
				$sql .= " AND postmeta_stock.meta_key 							= '_stock'";
				
				$sql .= " AND postmeta_manage_stock.meta_value 					= 'yes'";
				
				$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_value > 0";
				
				//$sql .= " AND woocommerce_order_itemmeta_product_id.meta_value = 1422";
				
				$sql .= " AND postmeta_stock.meta_value > 0";
				
				$sql .= " AND LENGTH(postmeta_stock.meta_value) >= 0";
				
				
				
				$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
				if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}
				}
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
				
				if($order_status  && $order_status != '-1' and $order_status != "'-1'") $sql .= " AND posts.post_status IN (".$order_status.")";				
				
				$sql .= " GROUP BY woocommerce_order_itemmeta_variation_id.meta_value";
				
				$sql .= " ORDER BY {$sort_by} {$order_by}";
				
				
				
				$that->items_query = $sql;
				
			}else{
				$sql = $that->items_query;
			}
			
			$order_items = $that->get_query_items($type,$sql);
			
			if($type != 'total_row'){
				
				$group_by						= 'variation_id';
				$_REQUEST['show_variation'] 	= 'variable';
				$_REQUEST['variation_column'] 	= '0';
				$_REQUEST['group_by'] 			= 'variation_id';				
				
				$order_items = $this->get_grid_items_variation($columns,$order_items,$group_by);
			}
			
			if($type != 'total_row'){
				$today_date 	= $this->constants['today_date'];
				$_today_date 	= strtotime($today_date);
				foreach($order_items as $item_key => $order_item){
					
					$stock_valid_days 		= $order_item->stock_valid_days;
					$current_stock_quantity = $order_item->current_stock_quantity;
					$variation_id 			= $order_item->variation_id;
					$product_id 			= $order_item->product_id;
					
					$order_items[$item_key]->current_stock_quantity = $current_stock_quantity + 0;
					
					if($stock_valid_days > 0 and $stock_valid_days < 8000){//80008
						$order_items[$item_key]->stock_valid_date = date($date_format,strtotime(" + {$stock_valid_days} day", $_today_date));
					}else{
						$order_items[$item_key]->stock_valid_date = '';
						//$order_items[$item_key]->stock_valid_days = '';
					}
					
					$avg_sales_quantity = isset($order_item->avg_sales_quantity) ? $order_item->avg_sales_quantity : 0;
					if($avg_sales_quantity < 1){
						$order_items[$item_key]->avg_sales_quantity = number_format($avg_sales_quantity,3);
					}else{
						$order_items[$item_key]->avg_sales_quantity = number_format($avg_sales_quantity,2);
					}
					
					
					
					$order_items[$item_key]->variation_id = '#'.$variation_id;
				}
			};
			
			return $order_items;
		}
		/**
		* ic_commerce_custom_all_summary_sales_report_query
		*
		* 
		*
		* @param array $rows
		* @param string $type
		* @param string $columns
		* @param string $report_name
		* @param string $that
		*
		* @return array   
		*/
		function ic_commerce_custom_all_summary_sales_report_query($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){
			
			global $wpdb;
			
			$request = $that->get_all_request();
			
			if(!isset($this->items_query)){
				
				extract($request);
				
				$date1 		= strtotime($start_date);
				
				$date2 		= strtotime($end_date);
				
				$datediff 	= $date2 - $date1;
				
				$difference = floor($datediff/(60*60*24));
				
				$order_status			= $that->get_string_multi_request('order_status',$order_status, "-1");
				
				$hide_order_status		= $that->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				
				$sql = "SELECT ";				
				
				$sql .= "  woocommerce_order_items.order_item_name 																	AS order_item_name";
				
				$sql .= ", woocommerce_order_itemmeta_product_id.meta_value 														AS product_id";
				
				$sql .= ", SUM(woocommerce_order_itemmeta_qty.meta_value)															AS sales_quantity";
				
				$sql .= ", postmeta_stock.meta_value																				AS current_stock_quantity";
				
				$sql .= ", SUM(woocommerce_order_itemmeta_qty.meta_value)/$difference												AS avg_sales_quantity";
				
				$sql .= ", ROUND((postmeta_stock.meta_value/(SUM(woocommerce_order_itemmeta_qty.meta_value)/$difference)))			AS stock_valid_days";
				
				$sql .= ", postmeta_manage_stock.meta_value																			AS manage_stock";
				
				$sql .= " FROM {$wpdb->prefix}woocommerce_order_items 																AS woocommerce_order_items";
				
				$sql .= " LEFT JOIN {$wpdb->posts} AS posts ON posts.ID = woocommerce_order_items.order_id";
				
				$sql .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woocommerce_order_itemmeta_qty ON woocommerce_order_itemmeta_qty.order_item_id = woocommerce_order_items.order_item_id";
				
				$sql .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woocommerce_order_itemmeta_product_id ON woocommerce_order_itemmeta_product_id.order_item_id = woocommerce_order_items.order_item_id";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS postmeta_stock ON postmeta_stock.post_id = woocommerce_order_itemmeta_product_id.meta_value";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS postmeta_manage_stock ON postmeta_manage_stock.post_id = woocommerce_order_itemmeta_product_id.meta_value";
				
				$sql .= " WHERE posts.post_type 								= 'shop_order'";
				
				$sql .= " AND woocommerce_order_itemmeta_qty.meta_key 			= '_qty'";
				
				$sql .= " AND woocommerce_order_itemmeta_product_id.meta_key 	= '_product_id'";
				
				$sql .= " AND postmeta_manage_stock.meta_key 					= '_manage_stock'";
				
				$sql .= " AND postmeta_stock.meta_key 							= '_stock'";
				
				$sql .= " AND postmeta_manage_stock.meta_value 					= 'yes'";
				
				$sql .= " AND postmeta_stock.meta_value > 0";
				
				$sql .= " AND LENGTH(postmeta_stock.meta_value) >= 0";
				
				/*if ($start_date != NULL &&  $end_date !=NULL){
					$sql .= " AND DATE(posts.post_date) BETWEEN '".$start_date."' AND '". $end_date ."'";
				}*/
				$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
				if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}
				}
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
				
				if($order_status  && $order_status != '-1' and $order_status != "'-1'") $sql .= " AND posts.post_status IN (".$order_status.")";
				
				
				$sql .= " GROUP BY product_id";
				
				$sql .= " ORDER BY {$sort_by} {$order_by}";
				
				
				
				$that->items_query = $sql;
				
			}else{
				$sql = $that->items_query;
			}
			
			$order_items = $that->get_query_items($type,$sql);
			
			if($type != 'total_row'){
				$today_date 	= $this->constants['today_date'];
				$_today_date 	= strtotime($today_date);
				foreach($order_items as $item_key => $order_item){
					$stock_valid_days = $order_item->stock_valid_days;
					$current_stock_quantity = $order_item->current_stock_quantity;
					$order_items[$item_key]->current_stock_quantity = $current_stock_quantity + 0;
					
					if($stock_valid_days > 0 and $stock_valid_days < 8000){//80008
						$order_items[$item_key]->stock_valid_date = date($date_format,strtotime(" + {$stock_valid_days} day", $_today_date));
					}else{
						$order_items[$item_key]->stock_valid_date = '';
						//$order_items[$item_key]->stock_valid_days = '';
					}
					
					$avg_sales_quantity = isset($order_item->avg_sales_quantity) ? $order_item->avg_sales_quantity : 0;
					if($avg_sales_quantity < 1){
						$order_items[$item_key]->avg_sales_quantity = number_format($avg_sales_quantity,3);
					}else{
						$order_items[$item_key]->avg_sales_quantity = number_format($avg_sales_quantity,2);
					}
					
				}
			};
			
			return $order_items;
		}
		/**
		* get_product_stock
		*
		* 
		*
		* @param string $product_ids
		*
		* @return array  return_list 
		*/
		function get_product_stock($product_ids = ''){
			global $wpdb;
			$return_list = array();
			if($product_ids){
				$items = $wpdb->get_results("SELECT post_id AS product_id, meta_value AS stock FROM $wpdb->postmeta WHERE meta_key = '_stock' AND post_id IN ({$product_ids})");			
				//$this->print_array($items);
				foreach($items as $key => $value){
					$return_list[$value->product_id] = $value->stock + 0;
				}
				
				//$this->print_array($return_list);
			}
			
			return $return_list;
		}
		
	}//End Class
}//End 