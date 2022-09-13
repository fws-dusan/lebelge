<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Crosstab')){
	require_once('ic_commerce_ultimate_report_cog_functions.php');
	/*
	 * Class Name IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Crosstab
	 *
	 * Class is used for Cost of Goods Crosstab
	 *	 
	*/
	class IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Crosstab extends IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Functions{
		
		/* variable declaration*/
		public $constants 			=	array();
		
		/* variable declaration*/
		public $cogs_constants 		=	array();
		
		/* variable declaration*/
		public $cost_of_goods 		=	NULL;
		
		
		/*
		 * Function Name __construct
		 *
		 * Initialize Class Default Settings, Assigned Variables, Hooks
		 *
		 * @param $constants (array) settings
		*/
		function __construct($constants = array(), $plugin_key = ""){
			$this->constants	= array_merge($this->constants, $constants);
			
			$report_name = isset($_REQUEST['report_name']) ? $_REQUEST['report_name'] : '';
			add_filter("ic_commerce_crosstab_page_titles", array($this, 'get_ic_commerce_crosstab_page_titles'));
			if($report_name == "product_profit_crosstab"){				
				add_filter("ic_commerce_crosstab_page_end_limit", 				array($this, 'get_ic_commerce_crosstab_page_end_limit'),		31,2);
				add_filter("ic_commerce_crosstab_page_items", 					array($this, 'get_ic_commerce_crosstab_page_items'),			31,5);
				add_filter("ic_commerce_crosstab_page_end_limit", 				array($this, 'get_ic_commerce_crosstab_page_end_limit'),		31,2);
				add_filter("ic_commerce_crosstab_page_amount_quantity",			array($this, 'get_ic_commerce_crosstab_page_amount_quantity'),	31,4);
				add_filter("ic_commerce_crosstab_page_columns",					array($this, 'get_ic_commerce_crosstab_page_columns'),			31,3);
				add_filter("ic_commerce_crosstab_page_crosstab_columns",		array($this, 'get_ic_commerce_crosstab_page_crosstab_columns'),	31,4);
				//add_action( "ic_commerce_crosstab_page_clickable_price", 		array($this, "get_ic_commerce_crosstab_page_clickable_price"),	31,7);
				//add_action( "ic_commerce_crosstab_page_total_clickable_price", array($this, "get_ic_commerce_crosstab_page_clickable_price"),	31,7);
			}
		}
		
		
		/*
		 * Function Name get_ic_commerce_crosstab_page_titles
		 *
		 * add aditional tab
		 *
		 * @param array $page_titles
		 *
		 * @return array $page_titles
		*/
		function get_ic_commerce_crosstab_page_titles($page_titles = array()){
			$page_titles['product_profit_crosstab'] = __('Product Profit/Month','icwoocommerce_textdomains');
			return $page_titles;
		}
		
		
		/*
		 * Function Name get_ic_commerce_crosstab_page_titles
		 *
		 * return crosstab report items
		 *
		 * @param array $items
		 *
		 * @param string $type
		 *
		 * @param boolen $items_only
		 *
		 * @param int $id
		 *
		 * @param object $that
		 *
		 * @return array|object $items
		*/
		function get_ic_commerce_crosstab_page_items($items = '', $type = '', $items_only = '', $id = '', $that = ''){
			$report_name = $this->get_request('report_name');
			if($report_name == "product_profit_crosstab"){	
				$items = $this->get_product_items($type,$items_only,$id, $that);
			}
			return $items;
		}
		
		/*
		 * Function Name get_ic_commerce_crosstab_page_end_limit
		 *
		 * return total number of product counts
		 *
		 * @param array $end_limit
		 *
		 * @param string $report_name
		 *
		 * @return string $end_limit
		*/
		function get_ic_commerce_crosstab_page_end_limit($end_limit = 0, $report_name = ''){
			if($report_name == "product_profit_crosstab"){	
				global $wpdb;
				$sql 		= "SELECT COUNT(*) FROM {$wpdb->posts} AS posts WHERE posts.post_type = 'product'";
				$end_limit 	= $wpdb->get_var($sql);
			}
			return $end_limit;
		}
		
		
		/*
		 * Function Name get_ic_commerce_crosstab_page_amount_quantity
		 *
		 * return items
		 *
		 * @param array $items
		  *
		 * @param array $month_key
		 *
		 * @param string $report_name
		  *
		 * @param object $that
		 *
		 * @return array|object $items
		 *
		*/
		function get_ic_commerce_crosstab_page_amount_quantity($items = '', $month_key = '', $report_name = '', $that = ''){
			if($report_name == "product_profit_crosstab"){	
				$items = $that->get_amount_quantity2($items,$month_key);
			}
			return $items;
		}
		
		/*
			* Function Name get_ic_commerce_crosstab_page_columns
			*
			* Get crosstab report page columns
			*
			* @param string $columns
			*
			* @param string $report_name
			*
			* @param string $that
		 	*
		 	* @return array|object $columns
			*			
		*/
		function get_ic_commerce_crosstab_page_columns($columns = '', $report_name = '', $that = ''){
			if($report_name == "product_profit_crosstab"){	
				$columns = array("product_sku"=>__("Product SKU",'icwoocommerce_textdomains'),"product_name"=>__("Product Name",'icwoocommerce_textdomains'));
				
			}
			return $columns;
		}
		
		/*
			* Function Name get_ic_commerce_crosstab_page_crosstab_columns
			*
			* Get crosstab report page month list
			*
			* @param string $items
			*
			* @param string $amount_column
			*
			* @param string $report_name
			*
			* @param string $that
		 	*
		 	* @return array|object $items
			*			
		*/
		function get_ic_commerce_crosstab_page_crosstab_columns($items = '', $amount_column, $report_name = '',  $that = ''){
			if($report_name == "product_profit_crosstab"){	
				$items = $that->get_months_list($amount_column);
			}
			return $items;
		}
		
		/*
			* Function Name get_ic_commerce_crosstab_page_clickable_price
			*
			* Get price
			*
			* @param string $price
			*
			* @param string $month_key
			*
			* @param string $request_key
			*
			* @param string $order_item
			*
			* @param string $item_href
			*
			* @param string $start_date
			*
			* @param string $end_date
		 	*
		 	* @return array|object $price
			*			
		*/
		function get_ic_commerce_crosstab_page_clickable_price($price = '', $month_key = '',$request_key = '',$order_item = '',$item_href = '',$start_date = '',$end_date = ''){
			if($request_key == "product_profit_crosstab"){
				$href = $item_href."&product_id=".$order_item->id."&month_key=".$month_key."&detail_view=yes";
				$price = "<a href=\"{$href}\" target=\"_blank\">{$price}</a>";
			}
			
			return $price;
		}
		
		/*
			* Function Name get_product_items
			*
			* Get product items
			*
			* @param string $type
			*
			* @param string $items_only
			*
			* @param string $id
			*
			* @param string $that
		 	*
		 	* @return array|object $order_items
			*			
		*/
		var $products_list_in_category = NULL;	
		public function get_product_items($type = 'limit_row', $items_only = true, $id = '-1', $that){
						
				global $wpdb;
				$request					= $that->get_all_request();extract($request);
				if($type == 'total_row'){
					$summary = $that->get_query_items($type, '', $request);							
					if($summary) return $summary;
				}
				
				$order_status				= $that->get_string_multi_request('order_status',$order_status, "-1");
				$hide_order_status			= $that->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				$product_status				= $that->get_string_multi_request('product_status',$product_status, "-1");
				
				
				if(!isset($that->products_list_in_category[$category_id])){
					$that->products_list_in_category[$category_id] = $that->get_products_list_in_category($category_id,$product_id);
				}						
				$category_product_id_string = $that->products_list_in_category[$category_id];
				$category_id 				= "-1";
				
				$cogs_metakey_item_total		= $that->get_setting('cogs_metakey_item_total',	$this->constants['plugin_options'],'');
				
				//$this->print_array($this->constants);
				
				$sql = " 
				SELECT
				woocommerce_order_itemmeta_product.meta_value 				as id
				,woocommerce_order_items.order_item_name 					as product_name
				,woocommerce_order_items.order_item_name 					as item_name
				,woocommerce_order_items.order_item_id 						as order_item_id
				,woocommerce_order_itemmeta_product.meta_value 				as product_id						
				
				,SUM(woocommerce_order_itemmeta_product_qty.meta_value) 	as quantity
				,MONTH(shop_order.post_date) 								as month_number
				,DATE_FORMAT(shop_order.post_date, '%Y-%m')					as month_key
				,COUNT(woocommerce_order_itemmeta_product.meta_value)		as item_count
				,SUM(woocommerce_order_itemmeta_line_total.meta_value) - SUM(woocommerce_order_itemmeta_total_cost_of_item.meta_value)			AS total
			
				FROM {$wpdb->prefix}woocommerce_order_items 				as woocommerce_order_items
				LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 		as woocommerce_order_itemmeta_product 			ON woocommerce_order_itemmeta_product.order_item_id=woocommerce_order_items.order_item_id
				LEFT JOIN  {$wpdb->posts} 							as shop_order 									ON shop_order.id								=	woocommerce_order_items.order_id
				
				LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 		as woocommerce_order_itemmeta_product_total 	ON woocommerce_order_itemmeta_product_total.order_item_id=woocommerce_order_items.order_item_id
				LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 		as woocommerce_order_itemmeta_product_qty		ON woocommerce_order_itemmeta_product_qty.order_item_id		=	woocommerce_order_items.order_item_id";
				
			
			if($category_id != NULL  && $category_id != "-1"){
				$sql .= " 
				LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	woocommerce_order_itemmeta_product.meta_value
				LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id
				LEFT JOIN  {$wpdb->prefix}terms 				as terms 				ON terms.term_id					=	term_taxonomy.term_id";
			}
			
			$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_line_total 			ON woocommerce_order_itemmeta_line_total.order_item_id			=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_line_total.meta_key			= '_line_total'";
			$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_total_cost_of_item 	ON woocommerce_order_itemmeta_total_cost_of_item.order_item_id	=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_total_cost_of_item.meta_key 	= '{$cogs_metakey_item_total}'";
			
			if($order_status_id != NULL  && $order_status_id != '-1'){
				$sql .= " 
				LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships2 	ON term_relationships2.object_id	=	woocommerce_order_items.order_id
				LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy2 		ON term_taxonomy2.term_taxonomy_id	=	term_relationships2.term_taxonomy_id
				LEFT JOIN  {$wpdb->prefix}terms 				as terms2 				ON terms2.term_id					=	term_taxonomy2.term_id";
			}
			
			if($product_status != NULL  && $product_status != '-1'){
				$sql .= " LEFT JOIN {$wpdb->posts} AS products ON products.ID = woocommerce_order_itemmeta_product.meta_value";
			}
			
				$sql .= " 
				WHERE
				woocommerce_order_itemmeta_product.meta_key		=	'_product_id'
				AND woocommerce_order_items.order_item_type		=	'line_item'
				AND shop_order.post_type						=	'shop_order'
	
				AND woocommerce_order_itemmeta_product_total.meta_key		='_line_total'
				AND woocommerce_order_itemmeta_product_qty.meta_key			=	'_qty'";
			
			if($id != NULL  && $id != '-1'){
				$sql .= " AND woocommerce_order_itemmeta_product.meta_value = {$id} ";					
			}
			
			//if ($start_date != NULL &&  $end_date !=NULL)	$sql .= " AND DATE_FORMAT(shop_order.post_date, '%Y-%m') BETWEEN '".$cross_tab_start_date."' AND '". $cross_tab_end_date ."'";
			
			$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
			if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
				if ($start_date != NULL &&  $end_date !=NULL){
					$sql .= " AND DATE(shop_order.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
				}
			}
			
			
			if($category_id  != NULL && $category_id != "-1"){
				
				$sql .= " 
				AND term_taxonomy.taxonomy LIKE('product_cat')
				AND terms.term_id IN (".$category_id .")";
			}
			
			if($category_product_id_string  && $category_product_id_string != "-1") $sql .= " AND woocommerce_order_itemmeta_product.meta_value IN (".$category_product_id_string .")";
			
			if($order_status_id != NULL  && $order_status_id != '-1'){
				$sql .= "
				AND term_taxonomy2.taxonomy LIKE('shop_order_status')
				AND terms2.term_id IN (".$order_status_id .")";
			}
			
			if($product_id != NULL  && $product_id != '-1'){
				$sql .= "
				AND woocommerce_order_itemmeta_product.meta_value IN ($product_id)";
			}
			
			if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND shop_order.post_status IN (".$order_status.")";
			
			if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND shop_order.post_status NOT IN (".$hide_order_status.")";
			
			if($product_status != NULL  && $product_status != '-1'){
				$sql .= " AND products.post_type IN ('product')";
				$sql .= " AND products.post_status IN ({$product_status})";
			}
			
			$cost_of_goods_only = 'yes' ;
			 
			if($cost_of_goods_only == "yes"){
				$sql .= " AND woocommerce_order_itemmeta_total_cost_of_item.meta_value > 0";
			}
			
			if($items_only){
				$sql .= " group by woocommerce_order_itemmeta_product.meta_value ORDER BY total DESC";						
			}else
				$sql .= " group by month_number ORDER BY month_number";
				
			
			$wpdb->flush(); 				
			$wpdb->query("SET SQL_BIG_SELECTS=1");
			
			if($type == 'limit_row'){
				if($items_only) $sql .= " LIMIT $start, $limit";			
				$order_items = $wpdb->get_results($sql);			
			}else if($type == 'all_row'){
				$order_items = $wpdb->get_results($sql);
			}else if($type == 'total_row'){
				$order_items = $that->get_query_items($type, $sql, $request);
			}
			
			if(strlen($wpdb->last_error) > 0){
				echo $wpdb->last_error;
			}
			
			//echo $type;
			
			return $order_items;
		}
		
		
	}//End Class
}