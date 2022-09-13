<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if( ! class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Dashboard')){
	require_once('ic_commerce_ultimate_report_cog_functions.php');
	class IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Dashboard extends IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Functions{
		
		/*Define constants variable*/
		public $constants 		=	array();
		/**
		* Declare class constructor
		* @param array $constants, set default constants 
		*/	
		public function __construct($constants = array()) {
			$this->constants	= array_merge($this->constants, $constants);
			add_action('ic_commerce_dashboard_page_above_recent_order', array($this, 'get_ic_commerce_dashboard_page_above_recent_order'));			
			add_action( 'ic_commerce_dashbaord_graph_items',				array($this, 'get_ic_commerce_dashbaord_graph_items'),30,7);
		}
		/**
		* get_ic_commerce_dashboard_page_above_recent_order
		* 
		* Get recent order
		*
		* @param array $constants
		* @return array 
		*/	
		function get_ic_commerce_dashboard_page_above_recent_order($constants = array()){
			global $wpdb;
			
			$this->define_constant();	
			$last_month_product_profit		= $this->get_setting('last_month_product_profit',	$this->constants['plugin_options'],8);
					
			$end_date					= $constants['end_date'];			
			$start_date					= $constants['start_date'];			
			$strtotime   				= strtotime($end_date);
			$end_date					= date("Y-m-t",$strtotime);			
			for($i=1; $i<=$last_month_product_profit; $i++){				
				$month 					= date('Y-m',$strtotime);
				$months[$month] 		= date_i18n('F',$strtotime);
				$strtotime 				= strtotime("- $i month");				
			}
			$start_date 				= date("Y-m-01",$strtotime);
			
			
			
			$shop_order_status 			= isset($constants['shop_order_status']) ? $constants['shop_order_status'] : array();
			$hide_order_status 			= isset($constants['hide_order_status']) ? $constants['hide_order_status'] : array();			
			//$order_totals 			= $this->get_order_total('total',$shop_order_status,$hide_order_status,$start_date,$end_date);
			$items						= $this->get_cost_of_goods_items('total',$shop_order_status,$hide_order_status,$start_date,$end_date);
			$new_items 					= array();
			$i 							= 0;
			
			//$this->print_array($items);
			//$this->print_array($months);
			
			foreach($months as $month_key =>  $month_name){
				$found_month = false;
				foreach($items as $item_key =>  $item){
					$item_month_key = isset($item->month_key) ? $item->month_key : '';
					if($item_month_key == $month_key){
						$found_month = $item;
					}
				}
				
				
				
				if($found_month){
					$new_items[$i] = $found_month;
					$new_items[$i]->quantity 				= $found_month->quantity;
					$new_items[$i]->total_cost_good_amount 	= $found_month->total_cost_good_amount;
					$new_items[$i]->margin_profit_amount 	= $found_month->margin_profit_amount;
					$new_items[$i]->total_amount 			= $found_month->total_amount;
					$new_items[$i]->month_name 				= $month_name;
					$new_items[$i]->month_key 				= $month_key;
					$i++;
				}else{
					$new_items[$i]							= new stdClass();
					$new_items[$i]->quantity 				= 0;
					$new_items[$i]->total_cost_good_amount 	= 0;
					$new_items[$i]->margin_profit_amount 	= 0;
					$new_items[$i]->total_amount 			= 0;
					$new_items[$i]->month_name 				= $month_name;
					$new_items[$i]->month_key 				= $month_key;
					$i++;
				}
			}
			
			
			
			$summary_page_url 					= admin_url('admin.php')."?page=".$this->constants['plugin_key']."_report_page";
			$view_all_label						= __('View All', 'icwoocommerce_textdomains');
			
			$show_sections						= $this->get_setting('show_sections',			$this->constants['plugin_options'], 0);			
			$show_monthly_profit_center			= $this->get_setting('show_monthly_profit_center',			$this->constants['plugin_options'], 0);
			$show_top_profit_products			= $this->get_setting('show_top_profit_products',		$this->constants['plugin_options'], 0);
			
			if($show_sections == 1){
				$output = '';				
				$output .= ' <div class="row">';
					if($show_monthly_profit_center == 1){
						
						$columns 	= array();
						$columns['month_name'] 				= __("Month Name",	'icwoocommerce_textdomains');
						$columns['quantity'] 				= __("Quantity Sold",	'icwoocommerce_textdomains');
						$columns['total_cost_good_amount'] 	= __("Total Cost of Goods",	'icwoocommerce_textdomains');
						$columns['margin_profit_amount'] 	= __("Margin/Profit",	'icwoocommerce_textdomains');
						//$columns['payment_fees'] 			= __("Payment Fees	",	'icwoocommerce_textdomains');				
						$columns['total_amount'] 			= __("Sales Amount	",	'icwoocommerce_textdomains');
						$cost_of_goods_items_grid			= $this->get_grids($new_items, $columns);
						
						$output .= ' <div class="icpostbox">';
							$output .= '  <h3>';
							$output .= '		<span class="title">';
							//$output .= '		<span class="title">'.__('Monthly Profit Center').'</span>';
							$output .= 				sprintf(__( 'Monthly Profit Center (Last %s Month)', 'icwoocommerce_textdomains'),$this->get_setting('last_month_product_profit',$this->constants['plugin_options'],5));					
							$output .= '		</span>';
							$output .= '		<span class="progress_status"></span>';
							$output .= '		<div class="Icons">';
							$output .= '			<a href="#" class="box_tab_report Table active" data-doreport="monthly_product_profit" 	data-content="table"		data-inside_id="monthly_profit_product">'. __( 'Monthly Profit Center', 'icwoocommerce_textdomains').'</a>';
							$output .= '			<a href="#" class="box_tab_report BarChart" 	data-doreport="monthly_profit_product" 	data-content="barchart"		data-inside_id="monthly_profit_product">'. __( 'Monthly Profit Center', 'icwoocommerce_textdomains').'</a>';
							$output .= '			<a href="#" class="box_tab_report PieChart" 	data-doreport="monthly_profit_product" 	data-content="piechart"		data-inside_id="monthly_profit_product">'. __( 'Monthly Profit Center', 'icwoocommerce_textdomains').'</a>';                                  
							$output .= '		</div>';
							$output .= '   	</h3>';						
							$output .= ' <div class="ic_inside Overflow" id="monthly_profit_product">';
								$output .= ' <div class="chart_parent">';
									$output .= ' <div class="chart" id="monthly_profit_product_chart"></div>';
								$output .= ' </div>';
								$output .= ' <div class="grid">'.$cost_of_goods_items_grid.'</div>';
								$output .= "<span class=\"ViewAll\"><a href=\"{$summary_page_url}&start_date={$start_date}&end_date={$end_date}&report_name=monthly_profit_product\">{$view_all_label}</a></span>";
							$output .= ' </div>';                               
						$output .= ' </div>';
					}
				$output .= '  </div>';
				
				$output .= ' <div class="row">';
					if($show_top_profit_products == 1){
						$columns 	= array();
						$columns['product_name'] 			= __("Product Name",	'icwoocommerce_textdomains');
						$columns['product_sku'] 			= __("SKU",	'icwoocommerce_textdomains');
						$columns['quantity'] 				= __("Quantity Sold",	'icwoocommerce_textdomains');
						$columns['total_cost_good_amount'] 	= __("Total Cost of Goods",	'icwoocommerce_textdomains');
						$columns['margin_profit_amount'] 	= __("Margin/Profit",	'icwoocommerce_textdomains');
						$columns['total_amount'] 			= __("Sales Amount	",	'icwoocommerce_textdomains');				
						$end_date							= $constants['end_date'];
						$start_date							= $constants['start_date'];
						$top_profit_products				= $this->get_top_profit_products('total',$shop_order_status,$hide_order_status,$start_date,$end_date);	
						$top_profit_products_grid			= $this->get_grids($top_profit_products, $columns);	
						
						$output .= ' <div class="icpostbox">';
							$output .= '  <h3>';
							$output .= '		<span class="title">';
							$output .= 				sprintf(__( 'Top %s Profit Product', 'icwoocommerce_textdomains'),$this->get_setting('top_profit_product',$this->constants['plugin_options'],5));					
							$output .= '		</span>';
							$output .= '		<span class="progress_status"></span>';
							$output .= '		<div class="Icons">';
							$output .= '			<a href="#" class="box_tab_report Table active" data-doreport="top_profit_product" 	data-content="table"		data-inside_id="top_profit_product">'. __( 'Top Profit Product', 'icwoocommerce_textdomains').'</a>';
							$output .= '			<a href="#" class="box_tab_report BarChart" 	data-doreport="top_profit_product" 	data-content="barchart"		data-inside_id="top_profit_product">'. __( 'Top Profit Product', 'icwoocommerce_textdomains').'</a>';
							$output .= '			<a href="#" class="box_tab_report PieChart" 	data-doreport="top_profit_product" 	data-content="piechart"		data-inside_id="top_profit_product">'. __( 'Top Profit Product', 'icwoocommerce_textdomains').'</a>';                                  
							$output .= '		</div>';
							$output .= '   	</h3>';						
							$output .= ' <div class="ic_inside Overflow" id="top_profit_product">';
								$output .= ' <div class="chart_parent">';
									$output .= ' <div class="chart" id="top_profit_product_chart"></div>';
								$output .= ' </div>';
								$output .= ' <div class="grid">'.$top_profit_products_grid.'</div>';
								$output .= "<span class=\"ViewAll\"><a href=\"{$summary_page_url}&start_date={$start_date}&end_date={$end_date}&report_name=cost_of_good_page\">{$view_all_label}</a></span>";
							$output .= ' </div>';                               
						$output .= ' </div>';
					}
				$output .= '  </div>';
			}else{
				
				$columns 	= array();
				$columns['month_name'] 				= __("Month Name",	'icwoocommerce_textdomains');
				$columns['quantity'] 				= __("Quantity Sold",	'icwoocommerce_textdomains');
				$columns['total_cost_good_amount'] 	= __("Total Cost of Goods",	'icwoocommerce_textdomains');
				$columns['margin_profit_amount'] 	= __("Margin/Profit",	'icwoocommerce_textdomains');
				//$columns['payment_fees'] 			= __("Payment Fees	",	'icwoocommerce_textdomains');				
				$columns['total_amount'] 			= __("Sales Amount	",	'icwoocommerce_textdomains');
				$cost_of_goods_items_grid			= $this->get_grids($new_items, $columns);
				
				$output = '';				
				$output .= ' <div class="row">';
					$output .= ' <div class="icpostbox">';
						$output .= '  <h3>';
						$output .= '		<span class="title">';
						//$output .= '		<span class="title">'.__('Monthly Profit Center').'</span>';
						$output .= 				sprintf(__( 'Monthly Profit Center (Last %s Month)', 'icwoocommerce_textdomains'),$this->get_setting('last_month_product_profit',$this->constants['plugin_options'],5));					
						$output .= '		</span>';
						$output .= '		<span class="progress_status"></span>';
						$output .= '		<div class="Icons">';
						$output .= '			<a href="#" class="box_tab_report Table active" data-doreport="monthly_product_profit" 	data-content="table"		data-inside_id="monthly_profit_product">'. __( 'Monthly Profit Center', 'icwoocommerce_textdomains').'</a>';
						$output .= '			<a href="#" class="box_tab_report BarChart" 	data-doreport="monthly_profit_product" 	data-content="barchart"		data-inside_id="monthly_profit_product">'. __( 'Monthly Profit Center', 'icwoocommerce_textdomains').'</a>';
						$output .= '			<a href="#" class="box_tab_report PieChart" 	data-doreport="monthly_profit_product" 	data-content="piechart"		data-inside_id="monthly_profit_product">'. __( 'Monthly Profit Center', 'icwoocommerce_textdomains').'</a>';                                  
						$output .= '		</div>';
						$output .= '   	</h3>';						
						$output .= ' <div class="ic_inside Overflow" id="monthly_profit_product">';
							$output .= ' <div class="chart_parent">';
								$output .= ' <div class="chart" id="monthly_profit_product_chart"></div>';
							$output .= ' </div>';
							$output .= ' <div class="grid">'.$cost_of_goods_items_grid.'</div>';
							$output .= "<span class=\"ViewAll\"><a href=\"{$summary_page_url}&start_date={$start_date}&end_date={$end_date}&report_name=monthly_profit_product\">{$view_all_label}</a></span>";
						$output .= ' </div>';                               
					$output .= ' </div>';
				$output .= '  </div>';
				
				$columns 	= array();
				$columns['product_name'] 			= __("Product Name",	'icwoocommerce_textdomains');
				$columns['product_sku'] 			= __("SKU",	'icwoocommerce_textdomains');
				$columns['quantity'] 				= __("Quantity Sold",	'icwoocommerce_textdomains');
				$columns['total_cost_good_amount'] 	= __("Total Cost of Goods",	'icwoocommerce_textdomains');
				$columns['margin_profit_amount'] 	= __("Margin/Profit",	'icwoocommerce_textdomains');
				$columns['total_amount'] 			= __("Sales Amount	",	'icwoocommerce_textdomains');				
				$end_date							= $constants['end_date'];
				$start_date							= $constants['start_date'];
				$top_profit_products				= $this->get_top_profit_products('total',$shop_order_status,$hide_order_status,$start_date,$end_date);	
				$top_profit_products_grid			= $this->get_grids($top_profit_products, $columns);	
				
				$output .= ' <div class="row">';
					$output .= ' <div class="icpostbox">';
						$output .= '  <h3>';
						$output .= '		<span class="title">';
						$output .= 				sprintf(__( 'Top %s Profit Product', 'icwoocommerce_textdomains'),$this->get_setting('top_profit_product',$this->constants['plugin_options'],5));					
						$output .= '		</span>';
						$output .= '		<span class="progress_status"></span>';
						$output .= '		<div class="Icons">';
						$output .= '			<a href="#" class="box_tab_report Table active" data-doreport="top_profit_product" 	data-content="table"		data-inside_id="top_profit_product">'. __( 'Top Profit Product', 'icwoocommerce_textdomains').'</a>';
						$output .= '			<a href="#" class="box_tab_report BarChart" 	data-doreport="top_profit_product" 	data-content="barchart"		data-inside_id="top_profit_product">'. __( 'Top Profit Product', 'icwoocommerce_textdomains').'</a>';
						$output .= '			<a href="#" class="box_tab_report PieChart" 	data-doreport="top_profit_product" 	data-content="piechart"		data-inside_id="top_profit_product">'. __( 'Top Profit Product', 'icwoocommerce_textdomains').'</a>';                                  
						$output .= '		</div>';
						$output .= '   	</h3>';						
						$output .= ' <div class="ic_inside Overflow" id="top_profit_product">';
							$output .= ' <div class="chart_parent">';
								$output .= ' <div class="chart" id="top_profit_product_chart"></div>';
							$output .= ' </div>';
							$output .= ' <div class="grid">'.$top_profit_products_grid.'</div>';
							$output .= "<span class=\"ViewAll\"><a href=\"{$summary_page_url}&start_date={$start_date}&end_date={$end_date}&report_name=cost_of_good_page\">{$view_all_label}</a></span>";
						$output .= ' </div>';                               
					$output .= ' </div>';
				$output .= '  </div>';
			}
			
			echo $output;
			
			
			
		}
		/**
		* get_grids
		* 
		* Display order grids
		*
		* @param array $items
		* @param array $columns
		* @param string $output
		* @return string 
		*/	
		function get_grids($items = array(), $columns = array(), $output = ""){
			
			$woocommerce_currency = $this->woocommerce_currency();
			
			$summary_report_url = admin_url("admin.php")."?page=".$this->constants['plugin_key']."_report_page";
			
			$output .= '<table style="width:100%" class="widefat">';
			$output .= '<thead>';
			$output .= '<tr class="first">';                                	
				$cells_status = array();
				foreach($columns as $key => $value):
					$td_class = $key;
					$td_width = "";
					switch($key):
						case "quantity":
						case "total_cost_good_amount":
						case "margin_profit_amount":
						case "total_amount":
						case "order_total":
							$td_class .= " amount";												
							break;							
						default;
							break;
					endswitch;
					$th_value 			= $value;
					$output 			.= "\n\t<th class=\"{$td_class}\">{$th_value}</th>";											
				endforeach;
			$output .= '</tr>';
			$output .= '</thead>';
			$output .= '<tbody>';
				foreach ( $items as $key => $order_item ) {
					$order_item->order_currency 				= isset($order_item->order_currency) 				? $order_item->order_currency : $woocommerce_currency;
					$zero_prize[$order_item->order_currency]	= isset($zero_prize[$order_item->order_currency]) 	? $zero_prize[$order_item->order_currency] : $this->price(0, array('currency' => $order_item->order_currency));
					if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
					$output .= "<tr class=\"$alternate row_{$key}\">";
						 foreach($columns as $key => $value):
								$td_class = $key;
								$td_value = "";
								switch($key):                                               
									case "product_name":
										$product_id 	= isset($order_item->product_id) ? $order_item->product_id : 0;
										$product_name 	= isset($order_item->product_name) ? $order_item->product_name : '';
										$td_value 		= "<a href=\"{$summary_report_url}&report_name=cost_of_good_page&product_id=$product_id\" target=\"_blank\">{$product_name}</a>";
										break;
									case "total_cost_good_amount":
									case "margin_profit_amount":
									case "total_amount":
										$td_value = isset($order_item->$key) ? $order_item->$key : 0;
										$td_value = $td_value == 0 ? $zero_prize[$order_item->order_currency] : $this->price($td_value, array('currency' => $order_item->order_currency));
										$td_class .= " amount";
										break;
									case "quantity":
										$td_value = isset($order_item->$key) ? $order_item->$key : 0;
										$td_class .= " amount";
										break;
									default:
										$td_value = isset($order_item->$key) ? $order_item->$key : '';
										break;
								endswitch;
								$output .= "<td class=\"{$td_class}\">{$td_value}</td>\n";                                           
							endforeach;  
					$output .= '</tr>';
				}
				
			$output .= '</tbody>';
			$output .= '</table>';			
			return $output;
			
		}
		/**
		* get_order_total
		* 
		* Get order total
		*
		* @param array $type
		* @param string $shop_order_status
		* @param array $hide_order_status
		* @param end_date $start_date
		* @param date $end_date
		* @return string 
		*/
		function get_order_total($type = 'total', $shop_order_status = array(),$hide_order_status = array(),$start_date = NULL,$end_date = NULL){
			global $wpdb;
			
			$sql = " SELECT ";
			
			$sql .= " COUNT(*)			AS order_count ";
			
			$sql .= ", MONTHNAME(posts.post_date) 			AS month_name ";
			
			$sql .= ", DATE_FORMAT(posts.post_date,'%Y-%m') AS month_key";
			
			$sql .= ", SUM(postmeta.meta_value) 			AS total_amount";
			
			$sql .= " FROM {$wpdb->posts} 					AS posts";
			
			$sql .= " LEFT JOIN  {$wpdb->postmeta} 	AS postmeta ON posts.ID = postmeta.post_id";
			
			$sql .= " WHERE posts.post_type = 'shop_order'";
			
			$sql .= " AND postmeta.meta_key ='_order_total'";
			
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
				}
			}else{
				if(count($shop_order_status)>0){
					$in_shop_order_status		= implode("', '",$shop_order_status);
					$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
				}
			}
			
			/*if ($start_date != NULL &&  $end_date !=NULL){
				$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
			}*/
			
			$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
			if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
				if ($start_date != NULL &&  $end_date !=NULL){
					$sql .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
				}
			}
			
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
			}
			
			$sql .= " GROUP BY YEAR(posts.post_date), MONTH(posts.post_date) ORDER BY posts.post_date ASC;";
			
			//echo $sql;
            
            $order_items = $wpdb->get_results($sql);
			
			if($wpdb->last_error){
				echo $wpdb->last_error;
			}
			
			$order_totals = array();
			$order_counts = array();
			//$order_totals['item_name'] = __('Sales Amount');
			foreach($order_items as $item_key => $item_values){
				$order_totals[$item_values->month_key] = $item_values->total_amount;
				$order_counts[$item_values->month_key] = $item_values->order_count;
			}
			
			$r['order_totals'] = $order_totals;
			$r['order_counts'] = $order_counts;
			
			return $r;
		}		
		/**
		* get_cost_of_goods_items
		* 
		* Get Cost Of Goods Items
		*
		* @param string $type
		* @param string $shop_order_status
		* @param string $hide_order_status
		* @param end_date $start_date
		* @param date $end_date
		* @return string 
		*/
		function get_cost_of_goods_items($type = "total", $shop_order_status,$hide_order_status,$start_date,$end_date){
				global $wpdb;
				
				$cogs_metakey_item_total		= $this->get_setting('cogs_metakey_item_total',	$this->constants['plugin_options'],'');
				$last_month_product_profit		= $this->get_setting('last_month_product_profit',	$this->constants['plugin_options'],8);
				
				$sql = " SELECT ";
				$sql .= "				
					SUM(woocommerce_order_itemmeta_qty.meta_value) 																					AS quantity							
					,SUM(woocommerce_order_itemmeta_total_cost_of_item.meta_value) 																	AS total_cost_good_amount
					,SUM(woocommerce_order_itemmeta_line_total.meta_value) - SUM(woocommerce_order_itemmeta_total_cost_of_item.meta_value)			AS margin_profit_amount							
					,SUM(woocommerce_order_itemmeta_line_total.meta_value) 																			AS total_amount	
					,MONTHNAME(shop_order.post_date) 																								AS month_name			
				";
				
				$sql .= ", DATE_FORMAT(shop_order.post_date,'%Y-%m') AS month_key";
				
				if($this->constants['post_order_status_found'] == 0 ){
					if(count($shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
				}	
				
				$sql .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_qty					ON woocommerce_order_itemmeta_qty.order_item_id					=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_qty.meta_key					= '_qty'";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_line_total 			ON woocommerce_order_itemmeta_line_total.order_item_id			=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_line_total.meta_key			= '_line_total'";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_line_subtotal			ON woocommerce_order_itemmeta_line_subtotal.order_item_id		=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_line_subtotal.meta_key		= '_line_subtotal'";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_total_cost_of_item 	ON woocommerce_order_itemmeta_total_cost_of_item.order_item_id	=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_total_cost_of_item.meta_key 	= '{$cogs_metakey_item_total}'";
				$sql .= " LEFT JOIN  {$wpdb->posts} as shop_order 															ON shop_order.id												=	woocommerce_order_items.order_id		AND shop_order.post_type									= 'shop_order'";
				
						
				$sql .= " WHERE 1*1 ";
				
				if($this->constants['post_order_status_found'] == 0 ){
					if(count($shop_order_status)>0){
						$in_shop_order_status = implode(",",$shop_order_status);
						$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					}
				}else{
					if(count($shop_order_status)>0){
						$in_shop_order_status		= implode("', '",$shop_order_status);
						$sql .= " AND  shop_order.post_status IN ('{$in_shop_order_status}')";
					}
				}
				
				/*if ($start_date != NULL &&  $end_date != NULL && $type != "today"){
					$sql .= " AND DATE(shop_order.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
				}*/
				
				$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
				if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(shop_order.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}
				}
				
				if(count($hide_order_status)>0){
					$in_hide_order_status		= implode("', '",$hide_order_status);
					$sql .= " AND  shop_order.post_status NOT IN ('{$in_hide_order_status}')";
				}
				
				$cogs_enable_set_item = $this->get_setting('cogs_enable_set_item',$this->constants['plugin_options'],0);
				
				if($cogs_enable_set_item == 1){				
					$sql .= " AND woocommerce_order_itemmeta_total_cost_of_item.meta_value > 0";
				}
				
				$sql .= " GROUP BY YEAR(shop_order.post_date), MONTH(shop_order.post_date) ORDER BY shop_order.post_date DESC LIMIT {$last_month_product_profit}";
								
				$order_items = $wpdb->get_results($sql);
				
				return $order_items;
		}
		/**
		* get_ic_commerce_dashbaord_graph_items
		* 
		* 
		*
		* @param string $order_items
		* @param string $do_action
		* @param string $shop_order_status
		* @param string $hide_order_status
		* @param end_date $start_date
		* @param date $end_date
		* @param array $constants
		* @return string 
		*/
		function get_ic_commerce_dashbaord_graph_items($order_items=array(), $do_action = '', $shop_order_status  = array(), $hide_order_status = array(), $start_date = NULL, $end_date = NULL, $constants = array()){
				
				//echo $do_action;
				
				if($do_action  == "monthly_profit_product"){
					
					$last_month_product_profit		= $this->get_setting('last_month_product_profit',	$this->constants['plugin_options'],8);
					
					$end_date					= $constants['end_date'];			
					$start_date					= $constants['start_date'];			
					$strtotime   				= strtotime($end_date);
					$end_date					= date("Y-m-t",$strtotime);			
					for($i=1; $i<=$last_month_product_profit; $i++){				
						$month 					= date('Y-m',$strtotime);
						$months[$month] 		= date_i18n('F',$strtotime);
						$strtotime 				= strtotime("- $i month");				
					}
					$start_date 				= date("Y-m-01",$strtotime);
					
					
					global $wpdb;
					$cogs_metakey_item_total		= $this->get_setting('cogs_metakey_item_total',	$this->constants['plugin_options'],'');
					$last_month_product_profit		= $this->get_setting('last_month_product_profit',	$this->constants['plugin_options'],5);
					//$this->constants['post_order_status_found'] = isset($this->constants['post_order_status_found']) ? $this->constants['post_order_status_found'] : 1;
					
					$cogs_metakey_item_total = "_ic_cogs_item_total";
					
					$sql = " SELECT ";
					$sql .= "				
						SUM(woocommerce_order_itemmeta_qty.meta_value) 																					AS quantity							
						,SUM(woocommerce_order_itemmeta_total_cost_of_item.meta_value) 																	AS total_cost_good_amount
						,SUM(woocommerce_order_itemmeta_line_total.meta_value) - SUM(woocommerce_order_itemmeta_total_cost_of_item.meta_value)			AS margin_profit_amount							
						,SUM(woocommerce_order_itemmeta_line_total.meta_value) 																			AS total_amount	
						,MONTHNAME(shop_order.post_date) 																								AS month_name			
					";
					
					$sql .= ", DATE_FORMAT(shop_order.post_date,'%Y-%m') AS month_key";
					
					if($this->constants['post_order_status_found'] == 0 ){
						if(count($shop_order_status)>0){
							$sql .= " 
							LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
							LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
						}
					}	
					
					$sql .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items";
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_qty					ON woocommerce_order_itemmeta_qty.order_item_id					=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_qty.meta_key					= '_qty'";
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_line_total 			ON woocommerce_order_itemmeta_line_total.order_item_id			=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_line_total.meta_key			= '_line_total'";
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_total_cost_of_item 	ON woocommerce_order_itemmeta_total_cost_of_item.order_item_id	=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_total_cost_of_item.meta_key 	= '{$cogs_metakey_item_total}'";
					$sql .= " LEFT JOIN  {$wpdb->posts} as shop_order 															ON shop_order.id												=	woocommerce_order_items.order_id		AND shop_order.post_type									= 'shop_order'";
					
							
					$sql .= " WHERE 1*1 ";
					
					if($this->constants['post_order_status_found'] == 0 ){
						if(count($shop_order_status)>0){
							$in_shop_order_status = implode(",",$shop_order_status);
							$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
						}
					}else{
						if(count($shop_order_status)>0){
							$in_shop_order_status		= implode("', '",$shop_order_status);
							$sql .= " AND  shop_order.post_status IN ('{$in_shop_order_status}')";
						}
					}
					
					/*if ($start_date != NULL &&  $end_date != NULL){
						$sql .= " AND DATE(shop_order.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
					}*/
					
					$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
					if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
						if ($start_date != NULL &&  $end_date !=NULL){
							$sql .= " AND DATE(shop_order.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
						}
					}
					
					if(count($hide_order_status)>0){
						$in_hide_order_status		= implode("', '",$hide_order_status);
						$sql .= " AND  shop_order.post_status NOT IN ('{$in_hide_order_status}')";
					}
					
					$cogs_enable_set_item = $this->get_setting('cogs_enable_set_item',$this->constants['plugin_options'],0);
					//$cogs_enable_set_item	= 1;
					
					if($cogs_enable_set_item == 1){				
						$sql .= " AND woocommerce_order_itemmeta_total_cost_of_item.meta_value > 0";
					}
					
					$sql .= " GROUP BY YEAR(shop_order.post_date), MONTH(shop_order.post_date)";
					
					$sql .= " ORDER BY shop_order.post_date DESC LIMIT {$last_month_product_profit}";
									
					$items = $wpdb->get_results($sql);
					
					$return_items				= array();
					$i							= 0;
					
					/*foreach($items as $item_key =>  $item){
						$return_items[$i]['Value']		= $item->month_key;
						$return_items[$i]['Label']		= $item->month_name;
						$i++;
					}*/
					
					
					$end_date					= $end_date;
					$strtotime   				= strtotime($end_date);
					$end_date					= date("Y-m-t",$strtotime);			
					
					for($i=1; $i<=$last_month_product_profit; $i++){
						$month 					= date('Y-m',$strtotime);
						$months[$month] 		= date_i18n('F',$strtotime);
						$strtotime 				= strtotime("- $i month");				
					}
					
					$months 					= array_reverse($months);
					$i							= 0;
					$start_date 				= date("Y-m-01",$strtotime);
					$new_items 					= array();
					$founded_lists				= array();
					
					
					
					if(count($items)>0){
						foreach($items as $item_key =>  $item){
							$item_month_key = isset($item->month_key) ? $item->month_key : '';
							$founded_lists[$item_month_key] = $item;
						}
					}
					
					foreach($months as $month_key =>  $month_name){
						$found_month = false;
						
						$return_items[$i]['Label']			= $month_name;
						
						if(isset($founded_lists[$month_key])){
							$found_month = $founded_lists[$month_key];
							$return_items[$i]['Value']		= $found_month->margin_profit_amount;
						}else{
							$return_items[$i]['Value']		= 0;
						}
						
						$i++;
					}
					
					return $return_items;
				}
				
				if($do_action  == "top_profit_product"){
					$top_profit_products 		= $this->get_top_profit_products('total',$shop_order_status,$hide_order_status,$start_date,$end_date);
					$return_items				= array();
					$i							= 0;
					foreach($top_profit_products as $month_key =>  $top_profit_product){
						$return_items[$i]['Label']		= $top_profit_product->product_name;
						$return_items[$i]['Value']		= $top_profit_product->margin_profit_amount;
						$i++;
					}
					
					return $return_items;
				}
				
				return array();
				
		}
		
		/**
		* get_top_profit_products
		* 
		* 
		*
		* @param string $type
		* @param string $shop_order_status
		* @param string $hide_order_status
		* @param end_date $start_date
		* @param date $end_date
		* @param array $constants
		* @return string 
		*/
		function get_top_profit_products($type = "total", $shop_order_status,$hide_order_status,$start_date,$end_date){
				global $wpdb;
				$cogs_metakey_item_total		= $this->get_setting('cogs_metakey_item_total',	$this->constants['plugin_options'],'');
				$top_profit_product				= $this->get_setting('top_profit_product',	$this->constants['plugin_options'],5);
				$sql = " SELECT ";
				$sql .= "				
					SUM(woocommerce_order_itemmeta_qty.meta_value) 																					AS quantity							
					,SUM(woocommerce_order_itemmeta_total_cost_of_item.meta_value) 																	AS total_cost_good_amount
					,SUM(woocommerce_order_itemmeta_line_total.meta_value) - SUM(woocommerce_order_itemmeta_total_cost_of_item.meta_value)			AS margin_profit_amount							
					,SUM(woocommerce_order_itemmeta_line_total.meta_value) 																			AS total_amount						
					,woocommerce_order_items.order_item_name 																						AS order_item_name
					,woocommerce_order_items.order_item_name 																						AS product_name
					,woocommerce_order_itemmeta_product_id.meta_value 																				AS product_id			
				";
				
				if($this->constants['post_order_status_found'] == 0 ){
					if(count($shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
				}	
				
				$sql .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_qty					ON woocommerce_order_itemmeta_qty.order_item_id					=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_qty.meta_key					= '_qty'";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_line_total 			ON woocommerce_order_itemmeta_line_total.order_item_id			=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_line_total.meta_key			= '_line_total'";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_total_cost_of_item 	ON woocommerce_order_itemmeta_total_cost_of_item.order_item_id	=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_total_cost_of_item.meta_key 	= '{$cogs_metakey_item_total}'";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_product_id				ON woocommerce_order_itemmeta_product_id.order_item_id			=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_product_id.meta_key			= '_product_id'";
				$sql .= " LEFT JOIN  {$wpdb->posts} as shop_order 															ON shop_order.id												=	woocommerce_order_items.order_id		AND shop_order.post_type									= 'shop_order'";
				
						
				$sql .= " WHERE 1*1 ";
				
				if($this->constants['post_order_status_found'] == 0 ){
					if(count($shop_order_status)>0){
						$in_shop_order_status = implode(",",$shop_order_status);
						$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					}
				}else{
					if(count($shop_order_status)>0){
						$in_shop_order_status		= implode("', '",$shop_order_status);
						$sql .= " AND  shop_order.post_status IN ('{$in_shop_order_status}')";
					}
				}
				
				/*if ($start_date != NULL &&  $end_date != NULL){
					$sql .= " AND DATE(shop_order.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
				}*/
				
				$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
				if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(shop_order.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}
				}
				
				if(count($hide_order_status)>0){
					$in_hide_order_status		= implode("', '",$hide_order_status);
					$sql .= " AND  shop_order.post_status NOT IN ('{$in_hide_order_status}')";
				}
				
				$cogs_enable_set_item = $this->get_setting('cogs_enable_set_item',$this->constants['plugin_options'],0);
				
				if($cogs_enable_set_item == 1){				
					$sql .= " AND woocommerce_order_itemmeta_total_cost_of_item.meta_value > 0";
				}
				
				
				$sql .= " GROUP BY woocommerce_order_itemmeta_product_id.meta_value";
				
				$sql .= " ORDER BY margin_profit_amount DESC LIMIT {$top_profit_product}";
								
				$items = $wpdb->get_results($sql);
				
				foreach($items as $key => $item){
					$items[$key]->product_sku = get_post_meta($item->product_id, '_sku', true);
					$product_name   = get_the_title($item->product_id);
					$items[$key]->product_name = $product_name;
				}
				
				//$this->print_sql($sql);
				
				//$this->print_array($items);
				
				return $items;
		}
		
		
		
		
	}//End Class
}//End Class