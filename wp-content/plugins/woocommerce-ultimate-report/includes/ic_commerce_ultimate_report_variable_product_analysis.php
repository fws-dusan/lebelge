<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'IC_Commerce_Ultimate_Woocommerce_Report_Variable_Product_Analysis' ) ) {
	require_once('ic_commerce_ultimate_report_sales_analysis_functions.php');
	class IC_Commerce_Ultimate_Woocommerce_Report_Variable_Product_Analysis extends IC_Commerce_Ultimate_Woocommerce_Report_Sales_Analysis_Functions{
		
		/*Define Constant*/
		public $constants 	=	array();
		
		/**
		* Declare class constructor
		* @param array $constants, set default constants 
		*/
		public function __construct($constants) {
			global $options;			
			$this->constants		= $constants;	
		}
		
		/*
		* init
		* get all server side request
		* set request to constant and variable
		* create search form with filter option
		*/
		function init(){
		//$this->prepare_query();
			$start_date 				= $this->get_request ("start_date",date_i18n("Y-m-d"),true);
			$end_date 					= $this->get_request ("end_date",date_i18n("Y-m-d"),true);			
			$action						= $this->get_request('action',$this->constants['plugin_key'].'_wp_ajax_action',true);
			$do_action_type				= $this->get_request('do_action_type','variable_product_analysis',true);
			$report_name 				= $this->get_request ("report_name",'variable_product_analysis',true);
			$page 						= $this->get_request ("page",'-1',true);
			$product_id					= $this->get_request ("product_id",'-1',true);
			$admin_page					= $page;
			$onload_search				= apply_filters('ic_commerce_onload_search', 'yes');
			?>
			
			<div id="navigation" class="hide_for_print">
				<div class="collapsible" id="section1"><?php _e("Custom Search",'icwoocommerce_textdomains');?><span></span></div>
				<div class="container">
					<div class="content">
						<div class="search_report_form">
							<form name="frm_variable_product" id="frm_variable_product" method="post">
								<div class="form-group">
									<div class="FormRow firstrow">
										<div class="label-text"><label for="start_date"><?php _e("Start Date:",'icwoocommerce_textdomains');?></label></div>
										<div class="input-text"><input type="text" name="start_date" class="_date" id="start_date" value="<?php echo $start_date ; ?>" /></div>
									</div>
									<div class="FormRow secondrow">
										<div class="label-text"><label for="end_date"><?php _e("End Date:",'icwoocommerce_textdomains');?></label></div>
										<div class="input-text"><input type="text" name="end_date" 	 class="_date" id="end_date" value="<?php echo $end_date ; ?>"  /></div>
									</div>
								</div>
								<?php do_action('ic_commerce_variable_product_analysis_below_date_fields',$report_name,$this)?>
								<div class="form-group">
									<div class="FormRow firstrow">
										<div class="label-text"><label for="start_date"><?php _e("Product:",'icwoocommerce_textdomains');?></label></div>
										<div class="input-text">
											<?php											
												$product_data = $this->get_product("VARIABLE",NULL);
												if(count($product_data)>0){
													$this->create_dropdown($product_data,"product_id[]","product_id2","Select All","product_id2",$product_id, 'object', true, 5);
												}else{
													echo ('No Variable products found.');
												}												
											?>
										</div>
									</div>
									<div class="FormRow secondrow">
										<div class="label-text"><label for="end_date"><?php _e("Order By:",'icwoocommerce_textdomains');?></label></div>
										<div class="form-text">
											<select name="order" id="order" class="sort_by">
											  <option value="quantity"><?php _e("Quantity",'icwoocommerce_textdomains');?></option>
											  <option value="product_name"><?php _e("Product Name",'icwoocommerce_textdomains');?></option>
										  </select>
										  <select name="order_by" id="order_by" class="order_by">
											  <option value="desc"><?php _e("DESC",'icwoocommerce_textdomains');?></option>
											  <option value="asc"><?php _e("ASC",'icwoocommerce_textdomains');?></option>
										  </select>
										</div>
									</div>
								</div>
								<input type="hidden" name="action" id="action" value="<?php echo $this->constants['plugin_key'].'_wp_ajax_action';?>" />
								<input type="hidden" name="sub_action" id="sub_action" value="variable-analysis" />
								<input type="hidden" name="call" id="call" value="variable-product" />
								<input type="hidden" name="do_action_type" id="do_action_type" 	value="<?php echo $this->get_request('do_action_type','report_page',true);?>" /> 
								<input type="hidden" name="admin_page" id="admin_page" value="<?php echo $admin_page;?>" />
                                <input type="hidden" name="page" id="page" value="<?php echo $page;?>" />
                                <input type="hidden" name="report_name" id="report_name" value="<?php echo $report_name;?>" />                                  
								<div class="form-group">
									<div class="FormRow Fullwidth">
										<span class="submit_buttons">
											<input type="submit" class="onformprocess searchbtn" value="<?php _e("Search",'icwoocommerce_textdomains');?>" />
										</span>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="ajax_content search_report_content hide_for_print autoload_<?php echo $onload_search;?>">
				<?php if($onload_search == "no") {echo apply_filters('ic_commerce_onload_search_text', '');}?>
            </div>
			
			<?php
		}
		
		/*
		* prepare_query
		* Create report query
		* 
		* @return array $results  
		*/
		function prepare_query(){	
			
			global $wpdb;
			
			$start_date 			=  $this->get_request ("start_date",date_i18n("Y-m-d"),true);
			$end_date 				=  $this->get_request ("end_date",date_i18n("Y-m-d"),true);
			$product_id 			=  $this->get_request ("product_id",0,true);
			
			$order 					=  $this->get_request ("order",'quantity',true);
			$order_by 				=  $this->get_request ("order_by",'desc',true);
			
			$query					= "";
			if ($product_id!="-1")
				$products = $this->get_product("VARIABLE",$product_id,"ARRAY_A");
			else
				$products = $this->get_product("VARIABLE",NULL,"ARRAY_A");
				
		
			foreach($products  as $key=>$value):
				$product_id =$value["id"]; 
				if (strlen($query)==0) {
					$query .= "SELECT 
								qty.meta_value as qty, count(*) as order_count,
								(count(*) * line_total.meta_value) as line_total,
								order_items.order_item_name as order_item_name,
								order_items.order_item_id as order_item_id,
								product_id.meta_value  as product_id,
								variation_id.meta_value as variation_id
								 FROM  {$wpdb->posts} as posts ";
				
					$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as order_items ON order_items.order_id=posts.ID ";
					$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as product_id ON product_id.order_item_id=order_items.order_item_id ";
					$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as qty ON qty.order_item_id=order_items.order_item_id ";
				
					$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as line_total ON line_total.order_item_id=order_items.order_item_id ";
					$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as variation_id ON variation_id.order_item_id=order_items.order_item_id ";
					
					$query = apply_filters("ic_commerce_variable_product_analysis_page_join_query", $query, $product_id, $start_date, $end_date);
					
					$query .= " WHERE 1=1 ";
					$query .= " AND posts.post_type='shop_order'";
					$query .= " AND order_items.order_item_type='line_item'";
					$query .= " AND product_id.meta_key='_product_id'";
					$query .= " AND qty.meta_key='_qty'";
					$query .= " AND line_total.meta_key='_line_total'";
					$query .= " AND variation_id.meta_value>'0'";
					$query .= " AND variation_id.meta_key='_variation_id'";
					$query .= " AND product_id.meta_value={$product_id}";
				
					//$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}' ";
					$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
					if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
						if ($start_date != NULL &&  $end_date !=NULL){
							$query .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
						}
					}
					
					$query = apply_filters("ic_commerce_variable_product_analysis_where_query", $query, $product_id, $start_date, $end_date);
				
					$query .= " GROUP By qty.meta_value,variation_id.meta_value  ";
					//$query .= " order By CAST(variation_id.meta_value AS SIGNED) ";
				}
				else{
					$query .= " UNION ";
					$query .= "SELECT 
								qty.meta_value as qty, count(*) as order_count,
								(count(*) * line_total.meta_value) as line_total,
								order_items.order_item_name as order_item_name,
								order_items.order_item_id as order_item_id,
								product_id.meta_value  as product_id,
								variation_id.meta_value as variation_id
								 FROM  {$wpdb->posts} as posts ";
				
					$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as order_items ON order_items.order_id=posts.ID ";
					$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as product_id ON product_id.order_item_id=order_items.order_item_id ";
					$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as qty ON qty.order_item_id=order_items.order_item_id ";
				
					$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as line_total ON line_total.order_item_id=order_items.order_item_id ";
					$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as variation_id ON variation_id.order_item_id=order_items.order_item_id ";
					$query = apply_filters("ic_commerce_variable_product_analysis_page_join_query", $query, $product_id, $start_date, $end_date);
					
					$query .= " WHERE 1=1 ";
					$query .= " AND posts.post_type='shop_order'";
					$query .= " AND order_items.order_item_type='line_item'";
					$query .= " AND product_id.meta_key='_product_id'";
					$query .= " AND qty.meta_key='_qty'";
					$query .= " AND line_total.meta_key='_line_total'";
					$query .= " AND variation_id.meta_value>'0'";
					$query .= " AND variation_id.meta_key='_variation_id'";
					$query .= " AND product_id.meta_value={$product_id}";
				
					//$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}' ";
					if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
						if ($start_date != NULL &&  $end_date !=NULL){
							$query .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
						}
					}
					
					$query = apply_filters("ic_commerce_variable_product_analysis_where_query", $query, $product_id, $start_date, $end_date);
				
					$query .= " GROUP By qty.meta_value,variation_id.meta_value  ";
				}
			endforeach;
			
			if ($order == "quantity")
				$query .= " order By CAST(qty AS SIGNED) " . $order_by;
			if ($order == "product_name")
				$query .= " order By order_item_name " . $order_by;	
			
					
		
			
				
				$results = $wpdb->get_results( $query);	
				foreach(	$results as $key=>$value){
					$results[$key]->variation = $this->get_product_variation($value->order_item_id); 
				}
				//$this->print_array($results);
				
			return $results;		
		}
		
		/*
		* get_variable_product
		* Get variable product
		* 
		* @return void  
		*/
		function get_variable_product(){
			
			do_action('ic_commerce_variable_product_analysis_before_default_request');
			
			$results = $this->prepare_query();
			
			?>
			<div class="ic_overflow">
			<table class="widefat">
				<thead>
					<tr>
						<th style="display:none"><?php _e("ID",'icwoocommerce_textdomains'); ?></th>
						<th><?php _e("SKU",'icwoocommerce_textdomains'); ?></th>
						<th><?php _e("Category",'icwoocommerce_textdomains'); ?></th>
						<th><?php _e("Product",'icwoocommerce_textdomains'); ?></th>
						<th><?php _e("Variation",'icwoocommerce_textdomains'); ?></th>
						<th style="text-align:right;"><?php _e("No of Order",'icwoocommerce_textdomains'); ?></th>
						<th style="text-align:right;"><?php _e("Quantity",'icwoocommerce_textdomains'); ?></th>
						<th style="text-align:right;"><?php _e("Price",'icwoocommerce_textdomains'); ?></th>
						<th style="text-align:right;"><?php _e("Total",'icwoocommerce_textdomains'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($results as $key => $value): ?>
					<tr>
						<td style="display:none" ><?php echo $value->product_id; ?></td>
						<td><?php echo get_post_meta($value->variation_id, '_sku',true); ?></td>
						<td><?php echo $this->get_product_cat($value->product_id) ?></td>
						<td><?php echo $value->order_item_name;  ?></td>
						<td><?php echo $value->variation;  ?></td>
						<td style="text-align:right;"><?php echo $value->order_count; ?></td>
						<td style="text-align:right;"><?php echo $value->qty; ?></td>
						<td style="text-align:right;"><?php echo wc_price($value->line_total/ ( $value->order_count *  $value->qty)); ?></td>
						<td style="text-align:right;"><?php echo wc_price($value->line_total); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			 </table>
			 </div>
			<?php
			
			
		}
		
		/*
		* get_product_variation
		* Get variable product
		* @param integer $order_item_id
		* @return array $product_variation   
		*/
		function get_product_variation($order_item_id){
			global $wpdb;
			$all_variation = $this->get_all_variation(); 
			$product_variation = "";
			//$this->print_array($all_variation);
			$query = "";
			$query = " SELECT * FROM  {$wpdb->prefix}woocommerce_order_itemmeta as order_itemmeta "; 
			$query .= " WHERE 1=1 ";
			$query .= " AND order_itemmeta.order_item_id='{$order_item_id}'";
			$results = $wpdb->get_results( $query);	
			
			
			
			foreach($results as $key=>$value){
				//echo $value->meta_key;
				if (in_array($value->meta_key, $all_variation)){
					if (strlen($product_variation)==0)
					$product_variation = $value->meta_value;
					else
					$product_variation .= ", ".$value->meta_value;
				}
			}
			//$this->print_array($results);
			//	$product_variation;
			return $product_variation;	
		}
		
		/*
		* ajax
		* Handle all ajax request
		* 
		* @return array $product_variation   
		*/
		function ajax(){
			
		//echo json_encode($_REQUEST);
		//die;	
		 $call	= $this->get_request('call');
		 switch ($call) {
				case "variable-product":
					$this->get_variable_product();
					break;			
				case "green":
					echo "Your favorite color is green!";
					break;
				default:
					echo "Your favorite color is neither red, blue, nor green! AJAX";
			}
			die;
		}
	}	
}
?>