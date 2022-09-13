<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'IC_Commerce_Ultimate_Woocommerce_Report_Group_Simple_Product_Analysis' ) ) {
	require_once('ic_commerce_ultimate_report_sales_analysis_functions.php');
	/*
	 * Class Name IC_Commerce_Ultimate_Woocommerce_Report_Group_Simple_Product_Analysis
	 *
	 * Class is used for returning Group Report of Simple Product Analysis.
	 *
	*/
	class IC_Commerce_Ultimate_Woocommerce_Report_Group_Simple_Product_Analysis extends IC_Commerce_Ultimate_Woocommerce_Report_Sales_Analysis_Functions{
	
		public $constants 	=	array();
		
		/**
		* __construct
		* @param string $constants 
		*/
		public function __construct($constants) {
			global $options;			
			$this->constants		= $constants;	
		}
		
		/**
		* init
		* This function is used for Define Function, Setting Variables, Call Filters
		*/
		function init(){
		//$this->prepare_query();
			$start_date 			=  $this->get_request ("start_date",date_i18n("Y-m-d"),true);
			$end_date 				=  $this->get_request ("end_date",date_i18n("Y-m-d"),true);
			
			$action					= $this->get_request('action',$this->constants['plugin_key'].'_wp_ajax_action',true);
			$do_action_type			= $this->get_request('do_action_type','group_simple_product_analysis',true);
			$report_name 			= $this->get_request("report_name",'group_simple_product_analysis',true);
			$page 					= $this->get_request("page",'-1',true);
			$product_id				= $this->get_request("product_id",'-1',true);
			$admin_page				= $page;
			$onload_search				= apply_filters('ic_commerce_onload_search', 'yes');
			
			?>
			<div id="navigation" class="hide_for_print">
				<div class="collapsible" id="section1"><?php _e("Custom Search",'icwoocommerce_textdomains');?><span></span></div>
				<div class="container">
					<div class="content">
						<div class="search_report_form">
							<div class="form_process"></div>
							<form name="frm_group_simple_product" id="frm_group_simple_product" method="post">
								<div class="form-group">
									<div class="FormRow firstrow">
										<div class="label-text"><label for="start_date">Start Date:</label></div>
										<div class="input-text"><input type="text" name="start_date" class="_date" id="start_date" value="<?php echo $start_date ; ?>" /></div>
									</div>
									<div class="FormRow secondrow">
										<div class="label-text"><label for="end_date">End Date:</label></div>
										<div class="input-text"><input type="text" name="end_date" 	 class="_date" id="end_date" value="<?php echo $end_date ; ?>"  /></div>
									</div>
								</div>
                                <?php do_action('ic_commerce_group_simple_product_analysis_below_date_fields',$report_name,$this)?>
								<div class="form-group">
									<div class="FormRow firstrow">
										<div class="label-text"><label for="start_date">Order By:</label></div>
										<div class="form-text">
											<select name="order" id="order" class="sort_by">
												<option value="order_count">Order Count</option>
												<option value="product_count">Product Count</option>
											</select>
											<select name="order_by" id="order_by" class="sort_by">
												<option value="desc">DESC</option>
												<option value="asc">ASC</option>
											</select>
										</div>
									</div>
								</div>
								<!--   Product		:	-->
									<?php //$data=  $this->get_product("SIMPLE",NULL)?>
									<?php //$this->create_dropdown($data,"product_id[]","product_id","Select All","product_id",'-1', 'object', true, 5);?>
								<input type="hidden" name="action" id="action" value="<?php echo $this->constants['plugin_key'].'_wp_ajax_action';?>" />	
								<input type="hidden" name="action" id="action" value="ic_sales_analysis_ajax" />
								<input type="hidden" name="sub_action" id="sub_action" value="group_simple_product_analysis" />
								<input type="hidden" name="call" id="call" value="group_simple_product" />
								<input type="hidden" name="do_action_type" id="do_action_type" 	value="<?php echo $this->get_request('do_action_type','report_page',true);?>" />
								<input type="hidden" name="admin_page" id="admin_page" value="<?php echo $admin_page;?>" />
                                <input type="hidden" name="page" id="page" value="<?php echo $page;?>" />
                                <input type="hidden" name="report_name" id="report_name" value="<?php echo $report_name;?>" />                                  
								  <div class="form-group">
									<div class="FormRow Fullwidth">
										<span class="submit_busttons">
											<input type="submit" class="onformprocess searchbtn" value="Search" />
										</span>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
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
			$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as order_items ON order_items.order_id=posts.ID ";
			
			$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as variation_id ON variation_id.order_item_id=order_items.order_item_id ";
			
			$query = apply_filters("ic_commerce_group_simple_product_analysis_page_join_query", $query, 0, $start_date, $end_date);
			
			$query .=" WHERE 1=1 ";
			$query .= " AND posts.post_type='shop_order'";
			//$query .= " AND date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}' ";
			
			$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
			if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
				if ($start_date != NULL &&  $end_date !=NULL){
					$query .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
				}
			}
			$query .= " AND order_items.order_item_type='line_item'";
			
			$query .= " AND variation_id.meta_value='0'";
			$query .= " AND variation_id.meta_key='_variation_id'";
			
			$query = apply_filters("ic_commerce_group_simple_product_analysis_where_query", $query, 0, $start_date, $end_date);
			
			$query .= " GROUP By posts.ID HAVING product_count >= 2 ";
			
			$results = $wpdb->get_results( $query);	
			foreach ($results as $k=>$v) {
				$results[$k]->product =$this->get_order_product($v->ID);	
			}
			//$this->print_array($results);
			
			return $results ;
		}
		
		/**
		* get_order_product
		* This Function is used to get Order Product.
		* @param string $order_id 
		* @return string
		*/
		function get_order_product($order_id=NULL){
				global $wpdb;
				$products= "";
				$query = "";
				$query = " SELECT * FROM  {$wpdb->posts} as posts ";
				$query .= "	LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as order_items ON order_items.order_id=posts.ID ";
				
				$query .=" WHERE 1=1 ";
				$query .= " AND posts.post_type='shop_order'";
				$query .= " AND order_items.order_item_type='line_item'";
				$query .= " AND posts.ID='{$order_id}'";
				
				$results = $wpdb->get_results( $query);	
				foreach ($results as $k=>$v) {
					//echo $v->order_item_name; 
					if (strlen($products)==0) {
						$products = $v->order_item_name;
					}
					else{
						$products .= ",".$v->order_item_name;
					}
					
				}
				return  $products;
				//echo $products ;
				//$this->print_array($results);
		}
		
		/**
		* prepare_product_combination
		* This Function is used to Prepare Product Combinations.
		* @return array
		*/
		function prepare_product_combination(){
			$products_new = array(); 
			$products = $this->query();	
			$products_new = $products ;
			$product_combination  =array(); 
			
			//$products = $this->query();	
			//$this->print_array($products);
			//$this->print_array($products_new);
			
			/*For Sort Array for same combination 1,2 or 2,1*/
			$product_name = array();
			foreach ($products as $key => $row){
				$product_name[$key] = $row->product;
			}
			array_multisort($product_name, SORT_ASC, $products);
			
			$product_name_new = array();
			foreach ($products as $key => $row){
				$product_name_new[$key] = $row->product;
			}
			array_multisort($product_name_new, SORT_ASC, $products_new);
			
			/*End Sort Array*/
			
			foreach ($products as $k=>$v) :
				//echo $v->product_count;
				$i=1;
				foreach ($products_new as $k_new=>$v_new) :
					if ($v->product_count == $v_new->product_count){
						//$product_combination[$v->product_count] = $v->product;
						$products1 = explode(",",  $v->product);
						$products2 = explode(",",  $v_new->product);
						
						sort($products1);
						sort($products2);
						$comman_product = "";
						foreach($products2 as $key1=>$value1){
							//echo $value1;
							//echo "<br>";
							if (strlen($comman_product)==0){
								$comman_product .=  $value1;
							}else{
								$comman_product .= ",". $value1;
							}
						}
						//$this->print_array($products1);
						//$this->print_array($products2);
						if ($products1 == $products2) {
							$product_combination[$comman_product]["product_combination"]= $v->product;
							$product_combination[$comman_product]["order_count"] =$i;
							$product_combination[$comman_product]["product_count"] =$v->product_count;
							$i++;
						}
						
					}
				endforeach;	
			endforeach;
			//$this->print_array($product_combination);
			return $product_combination;	
		}
		
		/**
		* get_group_simple_product
		* This Function is used to Group Simple Product.
		*/
		function get_group_simple_product(){
			
			do_action('ic_commerce_group_simple_product_analysis_before_default_request');
			
			$product_combination = 	$this->prepare_product_combination();
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
						<th>Product Combination</th>
						<th>Order Count</th>
						<th>Product Count</th>
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
			
		
		 $call	= $this->get_request('call');
		 switch ($call) {
				case "group_simple_product":
					$this->get_group_simple_product();
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