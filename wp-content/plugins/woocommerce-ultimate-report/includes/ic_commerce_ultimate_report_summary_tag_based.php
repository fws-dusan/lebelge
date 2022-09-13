<?php  
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  
  
if(!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Tag_Based')){
	
	class IC_Commerce_Ultimate_Woocommerce_Report_Tag_Based  extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		/* variable declaration constants*/
		public $constants 			=	array();
		
		/*
		* Function Name __construct
		*
		* Initialize Class Default Settings, Assigned Variables, add filter
		*
		* @param array $constants
		*		 
		*/
		function __construct($constants = array(), $plugin_key = ""){
			$this->constants	= array_merge($this->constants, $constants);
			//add_action("ic_commerce_report_page_titles", 					array($this, "ic_commerce_report_page_titles"),31,2);
			
			$report_name = isset($_REQUEST['report_name']) ? $_REQUEST['report_name'] : '';
			if($report_name == "tag_page"){	
				add_action("ic_commerce_report_page_start_date", 				array($this, "ic_commerce_report_page_start_date"),				31,2);
				add_action("ic_commerce_report_page_default_items", 			array($this, "ic_commerce_report_page_default_items"),			31,5);
				add_action("ic_commerce_report_page_columns", 					array($this, "ic_commerce_report_page_columns"),				31,2);
				add_action("ic_commerce_report_page_result_columns", 			array($this, "ic_commerce_report_page_result_columns"),			31,2);
				add_filter('ic_commerce_report_page_items', 					array($this, 'get_ic_commerce_report_page_items'),				31,5);
				add_filter('ic_commerce_report_page_search_form_bottom', 		array($this, 'get_ic_commerce_report_page_search_form_bottom'), 31,2);
			}
		}
		
		/**
		* ic_commerce_report_page_start_date
		*
		* get start date
		*
		* @param string $start_date
		* @param string $report_name
		*  
		* @return date  start_date  
		*/
		function ic_commerce_report_page_start_date($start_date = '',$report_name = ''){
			$start_date = $this->constants['today_date'];
			return $start_date;
		}
		
		/**
		* ic_commerce_report_page_titles
		*
		* get report page title
		*
		* @param string $page_titles
		* @param string $report_name
		* @param string $plugin_options
		*  
		* @return string  $page_titles  
		*/
		function ic_commerce_report_page_titles($page_titles = '',$report_name = '', $plugin_options = ''){
			//$page_titles['tag_page'] = __('Tag',	'icwoocommerce_textdomains');
			return $page_titles;
		}
			
		/**
		* ic_commerce_report_page_default_items
		*
		* Not in use
		*
		* @param array $rows
		* @param string $type
		* @param array $columns
		* @param string $report_name
		* @param string $parent_this
		*  
		* @return void 
		*/
		function ic_commerce_report_page_default_items($rows = array(), $type = "limit_row", $columns = array(), $report_name = "", $parent_this = NULL){
			//$rows 		= $this->ic_commerce_custom_all_tag_based_query($type, $columns, $report_name, $parent_this);
			//return $rows;
		}		
		/**
		* ic_commerce_report_page_titles
		*
		* get report page title
		*
		* @param array $columns
		* @param string $report_name
	
		*  
		* @return array  $columns  
		*/
		function ic_commerce_report_page_columns($columns = array(), $report_name = ""){
			
			$columns 	= array(
				"tag_name" 										=> __("Tag Name", 			'icwoocommerce_textdomains')
				,"quantity"										=> __("Sales Quantity",		'icwoocommerce_textdomains')
				,"total_amount"									=> __("Amount", 			'icwoocommerce_textdomains')
			);
			
			return $columns;
		}
		/**
		* ic_commerce_report_page_result_columns
		*
		* get report columns
		*
		* @param array $columns
		* @param string $report_name
	
		*  
		* @return array  $columns  
		*/
		function ic_commerce_report_page_result_columns($columns = array(), $report_name = ""){							
			$columns = array(
				"total_row_count" 								=> __("Tag Count", 			'icwoocommerce_textdomains')
				,"quantity"										=> __("Sales Quantity", 	'icwoocommerce_textdomains')
				,"total_amount"									=> __("Amount", 			'icwoocommerce_textdomains')
			);
			return $columns;
		}
		
		/**
		* get_wordpress_terms
		*
		* get wordpress terms by product category
		*
		* @param array $columns
		* @param string $report_name
	
		*  
		* @return array  $product_tag  
		*/
		function get_wordpress_terms($taxonomy = 'product_tag'){
			global $wpdb;
			
			$sql = "SELECT terms.term_id AS id, terms.name as label
			FROM `{$wpdb->posts}` AS posts
			LEFT JOIN  {$wpdb->prefix}term_relationships	as term_relationships 	ON term_relationships.object_id	=	posts.ID
			LEFT JOIN  {$wpdb->prefix}term_taxonomy			as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id
			LEFT JOIN  {$wpdb->prefix}terms					as terms 				ON terms.term_id					=	term_taxonomy.term_id
			WHERE term_taxonomy.taxonomy = '{$taxonomy}'";
			$sql .= " GROUP BY terms.term_id";
			$sql .= " ORDER BY terms.term_id ASC, terms.term_id ASC";
			
			$product_tag = $wpdb->get_results($sql);			
			
			return $product_tag;
		}
		/**
		* get_ic_commerce_report_page_search_form_bottom
		*
		*
		* @return void  
		*/
		function get_ic_commerce_report_page_search_form_bottom(){
			$product_tags = $this->get_wordpress_terms();
			?>
            <div class="form-group">                
                <div class="FormRow FirstRow">
                    <div class="label-text"><label for="product_tag"><?php _e('Tags:','icwoocommerce_textdomains');?></label></div>
                    <div class="input-text">
                        <?php 
							$tag_id 		= $this->get_request('tag_id',"-1",true);
							$product_tag 	= $this->get_request('product_tag',$tag_id,true);
                            $this->create_dropdown($product_tags,"product_tag[]","product_tag","Select All","product_tag",$product_tag, 'object', true, 5);
                        ?>                                                        
                    </div>                    
                </div>
            </div>
            <?php
		}    
		
		/**
		* get_ic_commerce_report_page_items
		*
		*
		* @param array $rows
		* @param string $type
		* @param string $columns
		* @param string $report_name
		* @param string $that
		*  
		* @return array  $order_items  
		*/
		function get_ic_commerce_report_page_items($rows = array(), $type = '', $columns = '', $report_name = '', $that = ''){
			global $wpdb;			
			
			if(!isset($this->items_query)){
				$request 					= $that->get_all_request();extract($request);				
				$order_status				= $that->get_string_multi_request('order_status',$order_status, "-1");
				$hide_order_status			= $that->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				$category_product_id_string = $that->get_products_list_in_category($category_id,$product_id);
				$category_id 				= "-1";
				$product_tag 				= $this->get_request('product_tag','-1');
				
				$sql = " SELECT ";
				
				$sql .= "							
							woocommerce_order_items.order_item_name					AS 'product_name'
							,woocommerce_order_items.order_item_id					AS order_item_id
							,woocommerce_order_itemmeta_product_id.meta_value		AS product_id							
							,DATE(shop_order.post_date)								AS post_date
							,terms3.name											AS tag_name
							,terms3.term_id											AS tag_id
							";

				if($category_id  && $category_id != "-1") {					
					$sql .= "
							,terms.term_id											AS term_id
							,terms.name												AS term_name
							,term_taxonomy.parent									AS term_parent
						";						
				}
								
				$sql .= " ,SUM(woocommerce_order_itemmeta.meta_value) 				AS 'quantity'";
				$sql .= " ,SUM(woocommerce_order_itemmeta6.meta_value) 				AS 'total_amount'";
							
				$sql = apply_filters("ic_commerce_report_page_select_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= "
							FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items						
							LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
							LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta6 ON woocommerce_order_itemmeta6.order_item_id=woocommerce_order_items.order_item_id
							LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_product_id ON woocommerce_order_itemmeta_product_id.order_item_id=woocommerce_order_items.order_item_id						
							";
				
				if($category_id  && $category_id != "-1") {
						$sql .= " 	
							LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	woocommerce_order_itemmeta_product_id.meta_value 
							LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id
							LEFT JOIN  {$wpdb->prefix}terms 				as terms 				ON terms.term_id					=	term_taxonomy.term_id";
				}
				
				if($order_status_id  && $order_status_id != "-1") {
						$sql .= " 	
							LEFT JOIN  {$wpdb->prefix}term_relationships	as term_relationships2 	ON term_relationships2.object_id	=	woocommerce_order_items.order_id
							LEFT JOIN  {$wpdb->prefix}term_taxonomy			as term_taxonomy2 		ON term_taxonomy2.term_taxonomy_id	=	term_relationships2.term_taxonomy_id
							LEFT JOIN  {$wpdb->prefix}terms					as terms2 				ON terms2.term_id					=	term_taxonomy2.term_id";
				}
				
				
				$sql .= " 	
							LEFT JOIN  {$wpdb->prefix}term_relationships	as term_relationships3 	ON term_relationships3.object_id	=	woocommerce_order_itemmeta_product_id.meta_value 
							LEFT JOIN  {$wpdb->prefix}term_taxonomy			as term_taxonomy3 		ON term_taxonomy3.term_taxonomy_id	=	term_relationships3.term_taxonomy_id
							LEFT JOIN  {$wpdb->prefix}terms					as terms3 				ON terms3.term_id					=	term_taxonomy3.term_id";
				
				
				
				$sql .= " 
							LEFT JOIN  {$wpdb->posts} as shop_order ON shop_order.id=woocommerce_order_items.order_id";
				
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
							
				$sql .= "
							WHERE 1*1
							AND woocommerce_order_itemmeta.meta_key				= '_qty'
							AND woocommerce_order_itemmeta6.meta_key			= '_line_total' 
							AND woocommerce_order_itemmeta_product_id.meta_key 	= '_product_id'						
							AND shop_order.post_type							= 'shop_order'
							";
							
				$sql .= " AND term_taxonomy3.taxonomy = 'product_tag'";
				
				if($product_tag  && $product_tag != "-1") 
					$sql .= " AND terms3.term_id IN ({$product_tag})";
				
				/*if ($start_date != NULL &&  $end_date !=NULL){
					$sql .= " 
							AND (DATE(shop_order.post_date) BETWEEN '".$start_date."' AND '". $end_date ."')";
				}*/
				$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
				if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(shop_order.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}
				}
				
				if($product_id  && $product_id != "-1") 
					$sql .= "
							AND woocommerce_order_itemmeta_product_id.meta_value IN (".$product_id .")";
				
				if($category_id  && $category_id != "-1") 
					$sql .= "
							AND terms.term_id IN (".$category_id .")";	
				
				if($category_product_id_string  && $category_product_id_string != "-1") $sql .= " AND woocommerce_order_itemmeta_product_id.meta_value IN (".$category_product_id_string .")";//Added 20150219	
				
				if($order_status_id  && $order_status_id != "-1") 
					$sql .= " 
							AND terms2.term_id IN (".$order_status_id .")";
							
				
				if(strlen($publish_order)>0 && $publish_order != "-1" && $publish_order != "no" && $publish_order != "all"){
					$in_post_status		= str_replace(",","','",$publish_order);
					$sql .= " AND  shop_order.post_status IN ('{$in_post_status}')";
				}
				//echo $order_status;
				if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND shop_order.post_status IN (".$order_status.")";
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND shop_order.post_status NOT IN (".$hide_order_status.")";
				
				$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$group_sql = " GROUP BY  terms3.term_id";		
				
				$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);	
				
				$order_sql = " ORDER BY total_amount DESC, quantity DESC, tag_id ASC";
				
				$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);
					
				
				$that->items_query = $sql;
			}else{
				$sql = $that->items_query;
			}
			
			$order_items = $that->get_query_items($type,$sql);
			return $order_items;		
		}
		
	}//End Class
}//End 