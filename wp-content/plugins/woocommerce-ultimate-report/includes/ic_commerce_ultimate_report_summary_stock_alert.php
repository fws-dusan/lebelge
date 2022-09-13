<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Summary_Stock_Alert')){
		
	class IC_Commerce_Ultimate_Woocommerce_Report_Summary_Stock_Alert  extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
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
			
			if($report_name == "zero_level_stock_alert" || $report_name == "minimum_level_stock_alert"  || $report_name == "most_stocked"){
				add_filter("ic_commerce_report_page_default_items", 				array($this, "ic_commerce_report_page_default_items"),							31,5);
				add_filter("ic_commerce_report_page_columns", 						array($this, "ic_commerce_report_page_columns"),								31,2);
				add_filter("ic_commerce_report_page_result_columns", 				array($this, "ic_commerce_report_page_result_columns"),							31,2);
				add_filter("ic_commerce_pdf_custom_column_right_alignment", 		array($this, "ic_commerce_pdf_custom_column_right_alignment"),					31,2);				
				add_filter("ic_commerce_report_page_no_date_fields_tabs", 			array($this, "ic_commerce_report_page_no_date_fields_tabs"),  					31,1);
				add_filter("ic_commerce_report_page_search_form_below_date_fields", array($this, "ic_commerce_report_page_search_form_below_date_fields"),  		31,1);
				
				add_filter("ic_commerce_report_page_grid_columns", 						array($this, "ic_commerce_report_page_grid_columns"),  						31,1);
				add_filter("ic_commerce_report_page_data_grid_items_create_grid_items", array($this, "ic_commerce_report_page_data_grid_items_create_grid_items"),  31,1);
				
			}
		}
		/**
		* ic_commerce_report_page_search_form_below_date_fields
		*
		*
		* @return void 
		*/
		function ic_commerce_report_page_search_form_below_date_fields(){
			
			$report_name = isset($_REQUEST['report_name']) ? $_REQUEST['report_name'] : '';
			
			
			if($report_name == "most_stocked"){
				$most_stocked = isset($_REQUEST['most_stocked']) ? $_REQUEST['most_stocked'] : get_option('woocommerce_notify_low_stock_amount');
				//echo '<input type="text" value="'.$most_stocked.'" id="most_stocked" name="most_stocked" />';
				?>
					<div class="form-group">
						<div class="FormRow">
							<div class="label-text"><label for="most_stocked"><?php _e("Stock Greater Than:",'icwoocommerce_textdomains'); ?></label></div>
							<div class="input-text"><input type="text" value="<?php echo $most_stocked;?>" id="most_stocked" name="most_stocked" maxlength="10" class="numberonly" /></div>
						</div>
					</div>
				<?php
			}else{
				$stock_less_than = 0;
					
				if($report_name == "minimum_level_stock_alert"){
					$stock_less_than = get_option('woocommerce_notify_low_stock_amount');
				}
				
				$stock_less_than = isset($_REQUEST['stock_less_than']) ? $_REQUEST['stock_less_than'] : $stock_less_than;
				
				?>
					<div class="form-group">
						<div class="FormRow">
							<div class="label-text"><label for="stock_less_than"><?php _e("Stock Less Than:",'icwoocommerce_textdomains'); ?></label></div>
							<div class="input-text"><input type="text" value="<?php echo $stock_less_than;?>" id="stock_less_than" name="stock_less_than" maxlength="10" class="numberonly" /></div>
						</div>
					</div>
				<?php
			}
		}
		/**
		* ic_commerce_report_page_no_date_fields_tabs
		*
		* 
		* @param array $tabs
		*
		* @return array $tabs
		*/
		function ic_commerce_report_page_no_date_fields_tabs($tabs = array()){
			$tabs['zero_level_stock_alert'] 	= 'zero_level_stock_alert';
			$tabs['minimum_level_stock_alert'] 	= 'minimum_level_stock_alert';
			$tabs['most_stocked'] 				= 'most_stocked';
			return $tabs;
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
			$custom_columns['stock'] 	= 'stock';
			$custom_columns['actions'] 	= 'actions';
			return $custom_columns;
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
		function ic_commerce_report_page_columns($columns = array(), $report_name = ''){
			$columns 	= array(
				//"product_id"			=> __("Product ID", 		'icwoocommerce_textdomains')
				//,"variation_id"			=> __("Variation ID", 		'icwoocommerce_textdomains')
				"product_sku"			=> __("Product SKU", 				'icwoocommerce_textdomains')
				,"stock_product_name"	=> __("Product Name", 		'icwoocommerce_textdomains')
				,"order_date"			=> __("Last Sales Date", 	'icwoocommerce_textdomains')
				,"stock"				=> __("Stock Qty", 			'icwoocommerce_textdomains')
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
		* @return array $total_columns
		*/
		function ic_commerce_report_page_result_columns($total_columns = array(), $report_name = ''){
			$total_columns = array(
				"total_row_count"		=> __("Product Count", 		'icwoocommerce_textdomains')
				,"stock"				=> __("Total Stock", 		'icwoocommerce_textdomains')
				
			);
			return $total_columns;
		}
		/**
		* ic_commerce_report_page_grid_columns
		*
		* 
		* @param array $columns
		* @param string $report_name
		*
		* @return array $columns
		*/
		function ic_commerce_report_page_grid_columns($columns = '', $report_name = ''){
			$columns['actions'] = __("Actions", 'icwoocommerce_textdomains');
			return $columns;
		}
		/**
		* ic_commerce_report_page_data_grid_items_create_grid_items
		*
		* 
		* @param array $order_items
		*
		*
		* @return array $order_items
		*/
		function ic_commerce_report_page_data_grid_items_create_grid_items($order_items = array()){
			$edit_label = __("Edit Product", 			'icwoocommerce_textdomains');
			$view_label = __("View Product", 			'icwoocommerce_textdomains');
			$edit_link	= admin_url("post.php")."?action=edit&post=";
			foreach($order_items as $order_key => $order_item){
				$product_id 	= isset($order_item->product_id) ? $order_item->product_id : 0;
				if($product_id > 0){
					$view_link		= get_permalink($product_id);
					$edit_html_link = "<a href=\"{$edit_link}$product_id\" class=\"grid_link edit_product\" target=\"_blank\">{$edit_label}</a>";
					$view_html_link = "<a href=\"{$view_link}\" class=\"grid_link view_product\" target=\"_blank\">{$view_label}</a>";
					$order_items[$order_key]->actions = $edit_html_link.  " | ". $view_html_link;
				}
				
			}
			return $order_items;
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
		* @return array $columns
		*/
		function ic_commerce_report_page_default_items($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){
			return $this->ic_commerce_custom_all_summary_sales_report_query($rows, $type, $columns, $report_name, $that);
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
		* @return array $order_items
		*/
		function ic_commerce_custom_all_summary_sales_report_query($rows = '', $type = '', $columns = '', $report_name = '', $that = ''){
			
			global $wpdb;
			
			$request = $that->get_all_request();
			
			if(!isset($this->items_query)){
				extract($request);
				
				$stock_less_than 		=	isset($_REQUEST['stock_less_than']) ? $_REQUEST['stock_less_than'] : 0;
				
				$stock_less_than 		=	$stock_less_than +  0;
				
				$sql = "SELECT ";
				
				$sql .= " product.ID 										AS product_id";
				
				$sql .= ", product.post_parent								AS product_parent";
				
				$sql .= ", product.post_title 								AS stock_product_name";
				
				$sql .= ", product.post_type 								AS post_type";
				
				$sql .= ", manage_stock.meta_value 							AS manage_stock";
				
				$sql .= ", (stock.meta_value + 0) 							AS stock";
				
				$sql .= " FROM {$wpdb->posts} 						AS product";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS manage_stock ON manage_stock.post_id = product.ID AND manage_stock.meta_key = '_manage_stock'";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS stock ON stock.post_id = product.ID AND stock.meta_key = '_stock'";
				
				$sql .= " WHERE product.post_type IN ('product','product_variation')";
				
				$sql .= " AND product.post_status IN ('publish')";
				
				$sql .= " AND manage_stock.meta_value = 'yes'";
				
				$order = "ASC";
				if($report_name == "most_stocked"){					
					$sql .= " AND stock.meta_value > {$most_stocked}";
					$order = "DESC";
				}else if($report_name == "minimum_level_popup" || $report_name == "minimum_level_stock_alert"){
					if(strlen($stock_less_than) > 0){
						$sql .= " AND (stock.meta_value <= {$stock_less_than} AND stock.meta_value >= 1)";
					}else{
						$sql .= " AND (stock.meta_value <= 2 AND stock.meta_value >= 1)";
					}
				}else{
					if(strlen($stock_less_than) > 0){
						$sql .= " AND stock.meta_value <= {$stock_less_than}";
					}
				}
				
				$sql .= " GROUP BY product_id";
				
				//$sql .= " ORDER BY stock.meta_value ASC";
				
				//$sql .= " ORDER BY cast(stock.meta_value as unsigned) ASC";
				
				$sql .= " ORDER BY stock.meta_value *1 {$order}";
				
				$that->items_query = $sql;
				
			}else{
				$sql = $that->items_query;
			}
			
			$order_items = $that->get_query_items($type,$sql);
			
			if($type != 'total_row'){
				$order_items = $this->create_grid_data($order_items);
			}
			
			return $order_items;
		}
		/**
		* create_grid_data
		*
		* 
		* @param array $order_items
		*
		*
		* @return array $order_items
		*/
		function create_grid_data($order_items){
				$variation_products = array();
				
				foreach($order_items as $order_key => $order_item){
					$post_type = $order_item->post_type;
					//echo $order_item->product_id;
					//echo ", ";
					
					//echo $order_item->product_parent;
					//echo ", ";
					if($post_type == "product_variation"){
						$variation_products[] = $order_item->product_id;
						$order_items[$order_key]->variation_id 	= $order_item->product_id;
						$order_items[$order_key]->product_id 	= $order_item->product_parent;
					}else{
						$order_items[$order_key]->variation_id 	= 0;
					}
				}
				
				$product_ids 				= $this->get_items_id_list($order_items,'product_id','','string');				
				$product_last_order_dates 	= $this->get_product_last_sold_order_date($product_ids);
				
				//$this->print_array($product_last_order_dates);
				
				$product_id_order_dates 	= isset($product_last_order_dates['product_id']) ? $product_last_order_dates['product_id'] : array();
				$variation_id_order_dates 	= isset($product_last_order_dates['variation_id']) ? $product_last_order_dates['variation_id'] : array();
				
				//$this->print_array($product_last_order_dates);
				//$this->print_array($product_id_order_dates);
				//$this->print_array($variation_id_order_dates);
				//$this->print_array($variation_products);
				
				if(count($variation_products)>0){
					$product_variation_id = implode(",",$variation_products);				
					$product_variations = $this->get_product_variations($product_variation_id);
				}
				
				foreach($order_items as $order_key => $order_item){
					$post_type = $order_item->post_type;
					if($post_type == "product_variation"){
						$variation_id = $order_item->variation_id;
						$order_items[$order_key]->stock_product_name 	= isset($product_variations[$variation_id]) 		? $product_variations[$variation_id] 		: $order_item->stock_product_name;
						$order_items[$order_key]->order_date 			= isset($variation_id_order_dates[$variation_id]) 	? $variation_id_order_dates[$variation_id] 	: '';
					}else{
						$product_id = $order_item->product_id;
						$order_items[$order_key]->order_date 			= isset($product_id_order_dates[$product_id]) 		? $product_id_order_dates[$product_id] 	: '';
					}
				}
				
				
				
				return $order_items;
			
		}
		/**
		* get_product_variations
		*
		* 
		* @param string $product_variation_id
		*
		*
		* @return array $variation_attributes
		*/
		function get_product_variations($product_variation_id){
			global $wpdb;
			
			$sql = "SELECT ";
				
			$sql .= " product.ID 										AS product_id";
			
			$sql .= ", product_parent.post_title 						AS stock_product_name";
			
			$sql .= ", product_attributes.meta_key 						AS attribute_key";
			
			$sql .= ", product_attributes.meta_value 					AS attribute_value";
			
			$sql .= " FROM {$wpdb->posts} 						AS product";
			
			$sql .= " LEFT JOIN {$wpdb->postmeta} AS product_attributes ON product_attributes.post_id = product.ID AND product_attributes.meta_key LIKE 'attribute_%'";
			
			$sql .= " LEFT JOIN {$wpdb->posts} AS product_parent ON product_parent.ID = product.post_parent";
			
			$sql .= " WHERE product.post_type IN ('product_variation')";
			
			$sql .= " AND product_parent.post_type IN ('product')";			
			
			$sql .= " AND product.ID IN ($product_variation_id)";			
			
			$item_attributes = $wpdb->get_results($sql);
			
			$_variation_attributes =  array();
			
			$product_names =  array();
			
			foreach($item_attributes as $key => $value){
				
				$product_name 		= $value->stock_product_name;
				
				$product_id 		= $value->product_id;
				$attribute_key 		= $value->attribute_key;
				
				$attribute_key 		= str_replace("attribute_","",$attribute_key);
				$attribute_key 		= str_replace("pa_","",$attribute_key);
				$attribute_key 		= ucwords(str_replace("-"," ",$attribute_key));
				
				$attribute_value	= $value->attribute_value;
				$attribute_value 	= ucwords(str_replace("-"," ",$attribute_value));				
				$attribute_value 	= empty($attribute_value) ? __('Any')." ".$attribute_key : $attribute_value;
				
				$_variation_attributes[$product_id][$attribute_key] = $attribute_key.": ".$attribute_value;
				
				$product_names[$product_id] = $product_name;
				
			}
			
			$variation_attributes =  array();
			foreach($_variation_attributes as $product_id => $value){
				
				$product_name 		= $product_names[$product_id];
				
				$variation_attributes[$product_id] = $product_name . " (".implode(",",$value).")";
			}
			
			return $variation_attributes;
		}
		
		/**
		* get_product_last_sold_order_date
		*
		*
		*
		* @param string $product_ids
		*
		* @return array $order_date  
		*/
		function get_product_last_sold_order_date($product_ids = ''){
			global $wpdb;
			
			$sql = " SELECT ";
			$sql .= " woocommerce_order_itemmeta_product_id.meta_value 		AS product_id";
			$sql .= ", woocommerce_order_itemmeta_variation_id.meta_value 	AS variation_id";			
			$sql .= ", MAX(shop_order.post_date) 							AS order_date";
			//$sql .= ", MAX(woocommerce_order_items.order_id) 				AS order_id";
			
			$sql .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items";
			$sql .= " LEFT JOIN  {$wpdb->posts} as shop_order ON shop_order.id=woocommerce_order_items.order_id";
			$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_product_id ON woocommerce_order_itemmeta_product_id.order_item_id=woocommerce_order_items.order_item_id";
			$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_variation_id ON woocommerce_order_itemmeta_variation_id.order_item_id=woocommerce_order_items.order_item_id";
			$sql .= " WHERE 1*1";
			$sql .= " AND shop_order.post_type	= 'shop_order'";
			$sql .= " AND woocommerce_order_itemmeta_product_id.meta_key 	= '_product_id'";
			$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_key 	= '_variation_id'";
			
			if(!empty($product_ids)){
				$sql .= " AND woocommerce_order_itemmeta_product_id.meta_value IN ($product_ids)";
			}
			
			$sql .= " GROUP BY product_id,variation_id";
							
			$order_items = $wpdb->get_results($sql);
			
			if($wpdb->last_error){
				echo $wpdb->last_error;
				return array();
			}
			
			
			
			$order_date =  array();
			foreach($order_items as $order_key => $order_item){				
				$product_id 		= $order_item->product_id;
				$variation_id 		= $order_item->variation_id;
				if($variation_id > 0){
					$order_date['variation_id'][$variation_id] = $order_item->order_date;
				}
				
				$order_date['product_id'][$product_id] = $order_item->order_date;
			}
			
			//$this->print_array($order_date);
			
			return $order_date;
			
		}
		/**
		* get_product_stock_query
		*
		* 
		*
		* @param integer $stock_less_than
		* @param string $report_name 
		* @param integer $most_stocked 
		* @return string $sql 
		*/
		function get_product_stock_query($stock_less_than = 0,$report_name = 'zero_level', $most_stocked = 2){
			global $wpdb;
			
			$sql = "SELECT ";
				
			$sql .= " product.ID 										AS product_id";
			
			$sql .= ", product.post_parent								AS product_parent";
			
			$sql .= ", product.post_title 								AS stock_product_name";
			
			$sql .= ", product.post_type 								AS post_type";
			
			$sql .= ", manage_stock.meta_value 							AS manage_stock";
			
			$sql .= ", (stock.meta_value + 0) 							AS stock";
			
			$sql .= " FROM {$wpdb->posts} 						AS product";
			
			$sql .= " LEFT JOIN {$wpdb->postmeta} AS manage_stock ON manage_stock.post_id = product.ID AND manage_stock.meta_key = '_manage_stock'";
			
			$sql .= " LEFT JOIN {$wpdb->postmeta} AS stock ON stock.post_id = product.ID AND stock.meta_key = '_stock'";
			
			$sql .= " WHERE product.post_type IN ('product','product_variation')";
			
			$sql .= " AND product.post_status IN ('publish')";
			
			$sql .= " AND manage_stock.meta_value = 'yes'";
			
			$order = "ASC";
			
			if($report_name == "most_stocked"){					
				$sql .= " AND stock.meta_value > {$most_stocked}";
				$order = "DESC";
			}else if($report_name == "minimum_level_popup" || $report_name == "minimum_level_stock_alert"){
				if(strlen($stock_less_than) > 0){
					$sql .= " AND (stock.meta_value <= {$stock_less_than} AND stock.meta_value >= 1)";
				}else{
					$sql .= " AND (stock.meta_value <= 2 AND stock.meta_value >= 1)";
				}
			}else{
				if(strlen($stock_less_than) > 0){
					$sql .= " AND stock.meta_value <= {$stock_less_than}";
				}
			}
			
			$sql .= " GROUP BY product_id";
			
			$sql .= " ORDER BY stock.meta_value *1 {$order}";
			
			return $sql;
		}
		
		/**
		* create_grid
		*
		* 
		*
		* @param array $order_items
		* @param array $columns 
		* @param string $report_name 
		* @param string $output
		* 
		* @return string $output 
		*/
		function create_grid($order_items = array(), $columns = array(),$report_name ='',$output = ''){
			
			if(count($order_items)<=0) return '';
			$header_style = '';
			
			if($report_name == "email_template"){
				$header_style = 'padding:6px 10px; background:#BCD3E7; font-size:13px; margin:0px;';
			}
			$date_format = "F j, Y";
			if($report_name == "email_template"){
				$output .= "<table style=\"width:100%\" class=\"widefat\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width:700px; border:1px solid #0066CC; margin:0 auto;\">";	
			}else{
				$output .= "<table style=\"width:100%\" class=\"widefat\">";	
			}
			
				$output .= "<thead>";
					$output .= "<tr class=\"first\">";						
							$cells_status = array();							
							foreach($columns as $key => $value):
								$td_class = $key;
								$td_width = "";
								switch($key):
									case "stock":
										$td_class .= " amount";	
										$td_style = "text-align:right;";
										break;									
									case "order_date":	
										$date_format = get_option('date_format', $date_format);	
										$td_style = "text-align:left;";								
										break;							
									default;
										$td_style = "text-align:left;";	
										break;
								endswitch;
								$th_value = $value;
								$output .= "\n\t<th class=\"{$td_class}\" style=\"{$header_style}{$td_style}\">{$th_value}</th>";											
							endforeach;
					$output .= "</tr>";
				$output .= "</thead>";
				$output .= "<tbody>"; 
					if($report_name == "email_template"){
						foreach ( $order_items as $key => $order_item ) {								
							if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
								$default_td_style = 'font-family:Arial, Helvetica, sans-serif;font-size:12px; padding:3px 3px;';
								if($key%2 == 1){								
									$odd_even_td_style = 'background-color:#d7e3ee;';
								}else{										
									$odd_even_td_style = '';
								};
							$output .= "<tr class=\"{$alternate} row_{$key}\">";
									foreach($columns as $key => $value):
										$td_class = $key;
										$td_style = '';
										$td_value = "";
										switch($key):
											case "stock":
												$td_value = isset($order_item->$key) ? $order_item->$key : '';
												$td_style = "width:90px; text-align:right;";
												break;
											case "order_date":
												$order_date = isset($order_item->$key) ? $order_item->$key : '';
												$td_value = !empty($order_date) ?  date($date_format,strtotime($order_item->$key)) :"";
												$td_style = "width:110px;";
												break;
											case "stock":
												$td_value = isset($order_item->$key) ? $order_item->$key : '';
												$td_class .= " amount";	
												break;
											case "product_sku":
												$td_style = "width:75px;";
												$variation_id = isset($order_item->variation_id) ? $order_item->variation_id : '';
												if($variation_id > 0){												
													$td_value =  get_post_meta($variation_id,'_sku', true);
													break;
												}else{
													$product_id = isset($order_item->product_id) ? $order_item->product_id : '';
													$td_value =  get_post_meta($product_id,'_sku', true);
													break;
												}
												break;
											default:
												$td_value = isset($order_item->$key) ? $order_item->$key : '';
												break;
										endswitch;
										$output .= "<td class=\"{$td_class}\" style=\"{$default_td_style}{$td_style}{$odd_even_td_style}\">{$td_value}</td>\n";                                           
									endforeach;
							$output .= "</tr>";
						}//End Foreach
					}else{
						foreach ( $order_items as $key => $order_item ) {								
							if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};													
							$output .= "<tr class=\"{$alternate} row_{$key}\">";
									foreach($columns as $key => $value):
										$td_class = $key;
										//$td_style = $cells_status[$key];
										$td_value = "";
										switch($key):
											case "order_date":
												$order_date = isset($order_item->$key) ? $order_item->$key : '';
												$td_value = !empty($order_date) ?  date($date_format,strtotime($order_item->$key)) :"";
												break;
											case "stock":
												$td_value = isset($order_item->$key) ? $order_item->$key : '';
												$td_class .= " amount";	
												break;
											case "product_sku":
												$variation_id = isset($order_item->variation_id) ? $order_item->variation_id : '';
												if($variation_id > 0){												
													$td_value =  get_post_meta($variation_id,'_sku', true);
													break;
												}else{
													$product_id = isset($order_item->product_id) ? $order_item->product_id : '';
													$td_value =  get_post_meta($product_id,'_sku', true);
													break;
												}
												break;
											default:
												$td_value = isset($order_item->$key) ? $order_item->$key : '';
												break;
										endswitch;
										$output .= "<td class=\"{$td_class}\">{$td_value}</td>\n";                                           
									endforeach;
							$output .= "</tr>";
						}//End Foreach
					}//End If                        
					
				$output .= "</tbody>";
			$output .= "</table>";
			
			
			
			return $output;
		}
		
		/**
		* ajax
		*
		* 
		* @return void  
		*/
		function ajax(){
			global $wpdb;			
			$report_name = isset($_REQUEST['report_name']) ? $_REQUEST['report_name'] : '';
			$product_count = isset($_REQUEST['product_count']) ? $_REQUEST['product_count'] : '';
			if($report_name == "zero_level_popup"){
				$sql 			= $this->get_product_stock_query(0,$report_name);
				$sql			= $sql . " LIMIT 10";
				$order_items 	= $wpdb->get_results($sql);			
				$order_items 	= $this->create_grid_data($order_items);
				$columns 		= $this->ic_commerce_report_page_columns(array(),'dasbboard_popup');
				$output			= $this->create_grid($order_items, $columns);
				if($product_count > 10){
					$plugin_key = $this->constants['plugin_key'];
					$view_more_link = admin_url("admin.php")."?page={$plugin_key}_report_page&report_name=zero_level_stock_alert";
					$output			.= "<a href=\"{$view_more_link}\" target=\"_blank\">".__("View More")."</a>";
				}
				echo $output;
			}else if($report_name == "minimum_level_popup"){
				$stock_less_than = get_option('woocommerce_notify_low_stock_amount');
				$sql 			= $this->get_product_stock_query($stock_less_than,$report_name);
				$sql			= $sql . " LIMIT 10";
				$order_items 	= $wpdb->get_results($sql);			
				$order_items 	= $this->create_grid_data($order_items);
				$columns 		= $this->ic_commerce_report_page_columns(array(),'dasbboard_popup');
				$output			= $this->create_grid($order_items, $columns);
				if($product_count > 10){
					$plugin_key = $this->constants['plugin_key'];
					$view_more_link = admin_url("admin.php")."?page={$plugin_key}_report_page&report_name=minimum_level_stock_alert";
					$output			.= "<a href=\"{$view_more_link}\" target=\"_blank\">".__("View More")."</a>";
				}
				echo $output;
			}else if($report_name == "product_notification_count"){
				$return_json = array();
				$stock_less_than = get_option('woocommerce_notify_low_stock_amount');
				$sql 			= $this->get_product_stock_query($stock_less_than,'minimum_level_popup');
				$order_items 	= $wpdb->get_results($sql);
				$return_json['minimum_level_popup_button']  = count($order_items);
				
				$sql 			= $this->get_product_stock_query(0,'zero_level_popup');
				$order_items 	= $wpdb->get_results($sql);
				$return_json['zero_level_popup_button']  = count($order_items);
				
				echo json_encode($return_json);
				
				
			}
			die;
		}
		
	}//End Class
}//End 