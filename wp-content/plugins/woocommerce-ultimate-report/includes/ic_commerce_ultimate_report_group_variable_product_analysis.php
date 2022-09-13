<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'IC_Commerce_Ultimate_Woocommerce_Report_Group_Variable_Product_Analysis' ) ) {
	require_once('ic_commerce_ultimate_report_sales_analysis_functions.php');
	/*
	 * Class Name IC_Commerce_Ultimate_Woocommerce_Report_Group_Variable_Product_Analysis
	 *
	 * Class is used for returning Group Report of Variable Product Analysis.
	 *	 
	*/
	class IC_Commerce_Ultimate_Woocommerce_Report_Group_Variable_Product_Analysis extends IC_Commerce_Ultimate_Woocommerce_Report_Sales_Analysis_Functions{
	
		public $constants 	=	array();
		
		var $all_variable = array();
		
		/**
		* __construct
		* @param string $constants 
		*/
		public function __construct($constants) {
			global $options;			
			$this->constants		= $constants;
			
			$this->all_variable  =$this->get_all_variation();		
		}
		
		/**
		* init
		* This function is used for Define Function, Setting Variables, Call Filters
		*/
		function init(){
			$start_date 			=  $this->get_request ("start_date",date_i18n("Y-m-d"),true);
			$end_date 				=  $this->get_request ("end_date",date_i18n("Y-m-d"),true);			
			$action					= $this->get_request('action',$this->constants['plugin_key'].'_wp_ajax_action',true);
			$do_action_type			= $this->get_request('do_action_type','group_variable_product_analysis',true);
			$report_name 			= $this->get_request ("report_name",'group_variable_product_analysis',true);
			$page 					= $this->get_request ("page",'-1',true);
			$product_id				= $this->get_request ("product_id",'-1',true);
			$onload_search				= apply_filters('ic_commerce_onload_search', 'yes');
			$admin_page				= $page;
			?>
            			
			<div id="navigation" class="hide_for_print">
				<div class="collapsible" id="section1"><?php _e("Custom Search",'icwoocommerce_textdomains');?><span></span></div>
				<div class="container">
					<div class="content">
						<div class="search_report_form">
							<div class="form_process"></div>
							
							<form name="frm_group_product" id="frm_group_product" method="post">
								<div class="form-table">
									<div class="form-group">
										<div class="FormRow FirstRow">
											<div class="label-text"><label for="start_date"><?php _e("Start Date:",'icwoocommerce_textdomains');?></label></div>
											<div class="input-text"><input type="text" value="<?php echo $start_date;?>" id="start_date" name="start_date" /></div>
										</div>
										<div class="FormRow">
											<div class="label-text"><label for="end_date"><?php _e("End Date:",'icwoocommerce_textdomains');?></label></div>
											<div class="input-text"><input type="text" name="end_date" 	 class="_date" id="end_date" value="<?php echo $end_date ; ?>"  /></div>
										</div>
									</div>
									<?php do_action('ic_commerce_group_variable_product_analysis_below_date_fields',$report_name,$this)?>
									<div class="form-group">
										<div class="FormRow FirstRow">
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
									
									<!--   Product:	-->
									<?php //$data=  $this->get_product("SIMPLE",NULL)?>
									<?php //$this->create_dropdown($data,"product_id[]","product_id","Select All","product_id",'-1', 'object', true, 5);?>	
									
									<input type="hidden" name="action" 			id="action" 		value="<?php echo $this->constants['plugin_key'].'_wp_ajax_action';?>" />									
									<input type="hidden" name="sub_action" 		id="sub_action" 	value="group_variable_product_analysis" />
									<input type="hidden" name="call" 			id="call" 			value="group_variable_product" />
									<input type="hidden" name="page" 			id="page" 			value="<?php echo $_REQUEST['page']; ?>" />
									<input type="hidden" name="do_action_type" 	id="do_action_type" value="<?php echo $this->get_request('do_action_type','report_page',true);?>" /> 
									<input type="hidden" name="admin_page" 		id="admin_page" 	value="<?php echo $admin_page;?>" />
                                    <input type="hidden" name="page" 			id="page" 			value="<?php echo $page;?>" />
                                    <input type="hidden" name="report_name" 	id="report_name" 	value="<?php echo $report_name;?>"
					  
									<div class="ic_form-group">
										<div class="ic_FormRow ic_Fullwidth">
											<span class="ic_submit_buttons">
												<input type="submit" class="onformprocess searchbtn" value="<?php _e("Search",'icwoocommerce_textdomains');?>" />
											</span>
										</div>
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
		
		/**
		* query
		* @return array
		*/
		function query(){
			global $wpdb;
			$query = "";
			$start_date 			=  $this->get_request ("start_date",date_i18n("Y-m-d"),true);
			$end_date 				=  $this->get_request ("end_date",date_i18n("Y-m-d"),true);
			
			$query = " SELECT COUNT(*) as product_count ,posts.ID  FROM  {$wpdb->posts} as posts ";
			$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items ON woocommerce_order_items.order_id=posts.ID ";
			
			$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as variation_id ON variation_id.order_item_id=woocommerce_order_items.order_item_id ";
			
			$query = apply_filters("ic_commerce_group_variable_product_analysis_page_join_query", $query, 0, $start_date, $end_date);
			
			$query .=" WHERE 1=1 ";
			$query .= " AND posts.post_type='shop_order'";
			//$query .= " AND date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}' ";
			
			
			
			$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
			if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
				if ($start_date != NULL &&  $end_date !=NULL){
					$query .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
				}
			}
						
			$query .= " AND woocommerce_order_items.order_item_type='line_item'";			
			//$query .= " AND variation_id.meta_value>'0'";
			$query .= " AND variation_id.meta_key='_variation_id'";
			
			$query = apply_filters("ic_commerce_group_variable_product_analysis_where_query", $query, 0, $start_date, $end_date);
			
			$query .= " GROUP By posts.ID HAVING product_count >= 1 ";
			
			$results = $wpdb->get_results( $query);	
			//$this->print_array($results);
			foreach ($results as $k=>$v) {
				$results[$k]->product =$this->get_order_product($v->ID);
				//$results[$k]->variation_product =$this->get_order_product($v->ID);	
			}
			//$this->print_array($results);
			
			return $results ;
		}
		
		/**
		* get_order_product
		* This function is used to get Order Product.
		* @param string $order_id 
		* @return string
		*/
		function get_order_product($order_id=NULL){
				global $wpdb;
				$colors  = array("orange","purple","green2","green3","light-green","red","skyblue-light",
				"pink",
				"blue-light",
				"brown","pink",
				"orange",
				"purple",
				"lime-300",
				"green3",
				"light-green",
				"red",
				"skyblue-light",
				"green-300",
				"blue-300",
				"cyan-300");
				
				$products= "";
				$query = "";
				$query = " SELECT * FROM  {$wpdb->posts} as posts ";
				$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as order_items ON order_items.order_id=posts.ID ";
				
				
				$query .=" WHERE 1=1 ";
				$query .= " AND posts.post_type='shop_order'";
				$query .= " AND order_items.order_item_type='line_item'";
				$query .= " AND posts.ID='{$order_id}'";
				
				$results = $wpdb->get_results( $query);	
				
				/*
					$order_count = array();
				foreach ($product_combination as $key => $row){
					$order_count[$key] =  $row["order_count"];
				}
				*/
				
				$products_name = array();
				foreach ($results as $key => $row){
					$products_name[$key] =  $row->order_item_name;
				}
				array_multisort($products_name, SORT_ASC, $results);
				
				
				//$this->print_array($results);
				
				$order_product_variable = "";
				foreach ($results as $k=>$v) {
				//	echo $v->order_item_id; 
					if (strlen($products)==0) {
						
						$order_product_variable = $this->get_order_product_variable($v->order_item_id);
						if (strlen($order_product_variable)==0) {
							shuffle($colors);
							$color = array_rand($colors, 1);
							$products = " <span class=\"{$colors[$color]} white-font\">". $v->order_item_name . "</span>";
						}
						else{
							shuffle($colors);
							$color = array_rand($colors, 1);
							$products = " <span class=\"{$colors[$color]} white-font\">". $v->order_item_name ."-". $order_product_variable  . "</span>";
						}
						//$products = $v->order_item_name ;
						
					}
					else{
						$order_product_variable = $this->get_order_product_variable($v->order_item_id);
						if (strlen($order_product_variable)==0) {
							shuffle($colors);
							$color = array_rand($colors, 1);
							$products .= " # <span class=\"{$colors[$color]} white-font\">".$v->order_item_name. "</span>";
						}
						else{
							shuffle($colors);
						$color = array_rand($colors, 1);
						$products .= " # <span class=\"{$colors[$color]} white-font\">".$v->order_item_name ."-". $this->get_order_product_variable($v->order_item_id) . "</span>";
						}
						
						
						
						//$products .= " # ".$v->order_item_name ."-". $this->get_order_product_variable($v->order_item_id);
					
					}
					
				}
				//$this->print_array($products);
				//$products = explode(",", $products);
				//sort($products);
				//echo $products ;
				//echo "<br/>" ;
				return  $products;
				
				//$this->print_array($results);
		}
		
		/**
		* get_order_product_variable
		* This function is used to get Order Product Variable.
		* @param string $order_item_id 
		* @return string
		*/
		function get_order_product_variable($order_item_id=NULL){
			
				global $wpdb;
				$variations = "";
				$all_variation= 	$this->all_variable;
				//$this->print_array($all_variation);
				
				$products= "";
				
				$query = "";
				$query = " SELECT * FROM  {$wpdb->prefix}woocommerce_order_itemmeta as order_itemmeta ";
	
				$query .=" WHERE 1=1 ";
				$query .= " AND order_itemmeta.order_item_id='{$order_item_id}'";
				
				$results = $wpdb->get_results( $query);	
				foreach ($results as $k=>$v) {
					//echo $v->meta_key;
						//			echo $v->meta_value;
					if (in_array( $v->meta_key, $all_variation)){
						if(strlen($variations)==0)
							$variations .=  $v->meta_value;
						else
							$variations .=  ",". $v->meta_value;
						
					}
				}
				$variations = explode(",", $variations);
				sort($variations);
				//$this->print_array($variations);
				
				/*Sort The Variable String */
				$str_variations  = "";
				foreach($variations as $k=>$v){
					if(strlen($str_variations)==0)
						$str_variations .=  $v;
					else
						$str_variations .=  ",". $v;
				}
				//echo $str_variations;
				return $str_variations;
		}
		
		/**
		* prepare_product_combination
		* This function is used to Prepare Product Combinations.
		* @return array
		*/
		function prepare_product_combination(){
			global $wpdb;
			$duplicate_data =array();
			$original_data =  $this->query();
			$duplicate_data  = $original_data;
			$product_combination = array();
			
			
			foreach($original_data as $key=>$value):
				$i=1;
				foreach($duplicate_data as $dkey=>$dvalue):
				
						//$products1 = $dvalue->product ;
						//$products2 = $value->product;
					
						//$products1 = explode(" # ", $dvalue->product);
						//$products2 = explode(" # ", $value->product);
						
						//sort($products1);
						//sort($products2);
					if ($dvalue->product == $value->product){
						//echo " SAM ";
						$product_combination[$dvalue->product]["product_combination"]= $value->product;
						$product_combination[$dvalue->product]["order_count"] =$i;
						$product_combination[$dvalue->product]["product_count"] =$value->product_count;
						$i++;
					}
				endforeach;	
			endforeach;
			
			//$this->print_array($original_data );
			//$this->print_array($product_combination );
			return $product_combination;
			
		}
		
		/**
		* get_group_variable_product
		* This function is used to Group Variable Product.
		*/
		function get_group_variable_product(){
			
			do_action('ic_commerce_group_variable_product_analysis_before_default_request');
			
			$product_combination = 	$this->prepare_product_combination();
			//$product_combination = 	$this->prepare_product_combination();
			//$this->print_array($product_combination);
			 $order 				=  $this->get_request ("order",'order_count',true);
			 $order_by 				=  $this->get_request ("order_by",'desc',true);
			
			
			if ($order == "order_count") {
			
				$order_count = array();
				foreach ($product_combination as $key => $row){
					$order_count[$key] =  $row["order_count"];
				}
				if ($order_by=="desc")
					array_multisort($order_count, SORT_DESC, $product_combination);
				if ($order_by=="asc")
					array_multisort($order_count, SORT_ASC, $product_combination);
			
			}
			
			if ($order == "product_count") {
			
				$product_count = array();
				foreach ($product_combination as $key => $row){
					$product_count[$key] =  $row["product_count"];
				}
				if ($order_by=="desc")
					array_multisort($product_count, SORT_DESC, $product_combination);
				if ($order_by=="asc")
					array_multisort($product_count, SORT_ASC, $product_combination);
			
			}
			
			?>
			<div class="ic_overflow">
				<table class="widefat">
					<thead>
						<tr>
							<th><?php _e("Product Combination",'icwoocommerce_textdomains'); ?></th>
							<th><?php _e("Order Count",'icwoocommerce_textdomains'); ?></th>
							<th><?php _e("Product Count",'icwoocommerce_textdomains'); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($product_combination as $key => $value): ?>
						<tr>
							<td><?php echo $value["product_combination"]; ?></td>
							<td><?php echo $value["order_count"];  ?></td>
							<td><?php echo $value["product_count"];  ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				 </table>
			</div>
			<?php
		}
		
		/**
		* ajax
		*/
		function ajax(){
			
		//echo json_encode($_REQUEST);
			
		 $call	= $this->get_request('call');
		 switch ($call) {
				case "group_variable_product":
					$this->get_group_variable_product();
					break;			
				case "green":
					echo "Your favorite color is green!";
					break;
				default:
					echo "Your favorite color is neither red, blue, nor green! AJAX 1";
			}
			die;
		}	
	
	}
}
?>