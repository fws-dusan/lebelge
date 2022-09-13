<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//require_once('ic_commerce_ultimate_report_functions.php');
if ( ! class_exists( 'IC_Commerce_Ultimate_Woocommerce_Report_Map' ) ) {

	/*
	 * Class Name IC_Commerce_Ultimate_Woocommerce_Report_Map
	 *
	 * Class is used for returning map data
	 *	 
	*/


	//class IC_Commerce_Ultimate_Woocommerce_Report_Map extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
	class IC_Commerce_Ultimate_Woocommerce_Report_Map{
		
		/* variable declaration*/
		public $constants 	=	array();
		
		/* variable declaration*/
		public $parameters 	=	array();
		
		/*
			* Function Name __construct
			*
			* This function is used for Assigned Variables , Parameter and Call Filters
			* 
			* @param array $constants
			*		 
		*/
		
		public function __construct($constants = array(), $parameters = array('shop_order_status'=>array(),'hide_order_status'=>array(),'start_date'=>NULL,'end_date'=>NULL)) {
			global $plugin_options;
			
			$this->constants	= $constants;
			$this->parameters	= $parameters;
			$plugin_options 	= $this->constants['plugin_options'];
		}
		
		
		/*
			* Function Name init
			*
			* This function is used for Define Function, Setting Variables, Call Filters and print array
			* 
			* @param array $_REQUEST
			*		 
		*/
		
		function init(){
			$this->print_array($_REQUEST);		
		}
		
		
		/*
			* Function Name get_country_list
			*
			* Get Country List
			*
			* @param string $shop_order_status
			*
			* @param string $hide_order_status
			*
			* @param string $start_date
			*
			* @param string $end_date
		 	*
		 	* @return $order_items
			*			
		*/
		
		function get_country_list($shop_order_status,$hide_order_status,$start_date,$end_date){
			
			$json_encode = $this->get_request('json_encode',0);
			global $wpdb;
			$sql = " SELECT ";
			$sql .= " SUM(ROUND(order_total.meta_value, 2)) AS 'order_total' ";
			$sql .= ", billing_country.meta_value AS 'billing_country'";
			$sql .= ", COUNT(*) AS 'order_count'";
			
			$sql .= " FROM {$wpdb->posts} as posts";
			$sql .= " LEFT JOIN  {$wpdb->postmeta} as order_total ON order_total.post_id=posts.ID";
			$sql .= " LEFT JOIN  {$wpdb->postmeta} as billing_country ON billing_country.post_id=posts.ID";
			
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$sql .= " 
					LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
					LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
			}
			
			$sql .= " WHERE 1*1";
			
			$sql .= "  AND post_type IN ('shop_order')";
				
			$sql .= " AND order_total.meta_key	=	'_order_total' ";
			
			$sql .= " AND billing_country.meta_key	=	'_billing_country'";
			
			$url_shop_order_status	= "";
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					
					$url_shop_order_status	= "&order_status_id=".$in_shop_order_status;
				}
			}else{
				if(count($shop_order_status)>0){
					$in_shop_order_status		= implode("', '",$shop_order_status);
					$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
					
					$url_shop_order_status	= implode(",",$shop_order_status);
					$url_shop_order_status	= "&order_status=".$url_shop_order_status;
				}
			}
				
			if ($start_date != NULL &&  $end_date !=NULL){
				$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			
			$url_hide_order_status = "";
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
				
				$url_hide_order_status	= implode(",",$hide_order_status);
				$url_hide_order_status = "&hide_order_status=".$url_hide_order_status;
			}
			
			$sql .= " GROUP BY  billing_country.meta_value ";
			$sql .= " Order By order_total DESC";
			
			
			
			$order_items = $wpdb->get_results($sql);
			$country_list = array();
			$country_list_order_total = array();
			$country_list_order_count = array();
			
			$refunds = $this->get_refund_country_list($shop_order_status,$hide_order_status,$start_date,$end_date);			
			$refund_order_total = 0;
			foreach($order_items as $key => $value){
				$billing_country = $value->billing_country;
				$refund_order_total = isset($refunds['total'][$billing_country]) ? $refunds['total'][$billing_country] : 0;
				$country_list_order_total[$value->billing_country] = number_format($value->order_total + $refund_order_total, 2, '.', '');
			}
			
			$country_list = array();
			foreach($order_items as $key => $value){
				$country_list_order_count[$value->billing_country] = $value->order_count;
			}
			
			$country_list['total'] = $country_list_order_total;
			$country_list['count'] = $country_list_order_count;
			
			//error_log(print_r($country_list,true));
			
			if($json_encode == 1){
				echo json_encode($country_list);
				die;
			}else{
				//echo json_encode($country_list);
				
			}
			
			
			//return $order_items ;
		}
		
		
		/*
			* Function Name get_country_list
			*
			* Get Country List
			*
			* @param string $shop_order_status
			*
			* @param string $hide_order_status
			*
			* @param string $start_date
			*
			* @param string $end_date
		 	*
		 	* @return $order_items
			*			
		*/
		
		function get_refund_country_list($shop_order_status,$hide_order_status,$start_date,$end_date){
			
			$json_encode = $this->get_request('json_encode',0);
			global $wpdb;
			$sql = " SELECT ";
			$sql .= " SUM(ROUND(order_total.meta_value, 2)) AS 'order_total' ";
			$sql .= ", billing_country.meta_value AS 'billing_country'";
			$sql .= ", COUNT(*) AS 'order_count'";
			
			$sql .= " FROM {$wpdb->posts} as shop_order_refund";
			$sql .= " LEFT JOIN  {$wpdb->postmeta} as order_total ON order_total.post_id=shop_order_refund.ID";
			
			
			$sql .= " LEFT JOIN  {$wpdb->posts} as shop_order ON shop_order.ID=shop_order_refund.post_parent";
			
			$sql .= " LEFT JOIN  {$wpdb->postmeta} as billing_country ON billing_country.post_id = shop_order.ID";
			
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$sql .= " 
					LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
					LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
			}
			
			$sql .= " WHERE 1*1";			
			$sql .= "  AND shop_order_refund.post_type IN ('shop_order_refund')";
			$sql .= "  AND shop_order.post_type IN ('shop_order')";
							
			$sql .= " AND order_total.meta_key	=	'_order_total'";
			$sql .= " AND billing_country.meta_key	=	'_billing_country'";
			
			$url_shop_order_status	= "";
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					
					$url_shop_order_status	= "&order_status_id=".$in_shop_order_status;
				}
			}else{
				if(count($shop_order_status)>0){
					$in_shop_order_status		= implode("', '",$shop_order_status);
					$sql .= " AND  shop_order.post_status IN ('{$in_shop_order_status}')";
					
					$url_shop_order_status	= implode(",",$shop_order_status);
					$url_shop_order_status	= "&order_status=".$url_shop_order_status;
				}
			}
				
			if ($start_date != NULL &&  $end_date !=NULL){
				$sql .= " AND DATE(shop_order_refund.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			
			$url_hide_order_status = "";
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
				
				$url_hide_order_status	= implode(",",$hide_order_status);
				$url_hide_order_status = "&hide_order_status=".$url_hide_order_status;
			}
			$sql .= " GROUP BY  billing_country.meta_value ";
			$sql .= " Order By order_total DESC";
			
			$order_items = $wpdb->get_results($sql);
			$country_list = array();
			$country_list_order_total = array();
			$country_list_order_count = array();
			
			foreach($order_items as $key => $value){
				$country_list_order_total[$value->billing_country] = number_format($value->order_total, 2, '.', '');
			}
			
			$country_list = array();
			foreach($order_items as $key => $value){
				$country_list_order_count[$value->billing_country] = $value->order_count;
			}
			
			$country_list['total'] = $country_list_order_total;
			$country_list['count'] = $country_list_order_count;
			
			//error_log(print_r($country_list,true));
			
			return $country_list;
			
		}
		
		/**
		* get_request
		* This function is used for new request
		* @param string $name 
		* @param string $default 
		* @return boolean
		*/
		
		public function get_request($name,$default = NULL,$set = false){
			if(isset($_REQUEST[$name])){
				$newRequest = $_REQUEST[$name];
				
				if(is_array($newRequest)){
					$newRequest = implode(",", $newRequest);
				}else{
					$newRequest = trim($newRequest);
				}
				
				if($set) $_REQUEST[$name] = $newRequest;
				
				return $newRequest;
			}else{
				if($set) 	$_REQUEST[$name] = $default;
				return $default;
			}
		}
	}
}
