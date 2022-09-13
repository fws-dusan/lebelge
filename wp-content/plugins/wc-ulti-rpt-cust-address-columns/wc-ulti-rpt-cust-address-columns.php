<?php 
/**
Plugin Name: Woocommerce Ultimate Reports Customization
Author: Deepak Shah
Description: Added custom columns billing and shipping address on detail report
Version: 1.0
Author URI: http://woochamps.com/

Last Update Date: 06 September 2018

Text Domain: icwoocommerce_textdomains
Domain Path: /languages/

Copyright: (c) 2018 WooPro (info@woochamps.com)
Tested up to: 4.9.8
WC requires at least: 3.4.5
WC tested up to: 3.4.5

**/   

if(!class_exists('woocommerce_ultimate_report_customization')){
	class woocommerce_ultimate_report_customization{
		
		/* variable declaration*/
		public $constants 	=	array();
		
		public function __construct($constants = array()) {
			global $options;
			
			$this->constants		= $constants;
			$admin_page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';	
			if($admin_page == 'icwoocommerceultimatereport_report_page' || $admin_page == 'icwoocommerceultimatereport_details_page'){
				add_filter('ic_commerce_ultimate_report_page_init', array($this, 'ic_commerce_ultimate_report_page_init'),101,2);
				add_filter('ic_commerce_onload_search', array($this, 'ic_commerce_onload_search'),101);
			}
			
			if($admin_page == 'icwoocommerceultimatereport_details_page'){
				add_filter('ic_commerce_normal_view_columns', array($this, 'ic_commerce_normal_view_columns'),101);
				//add_filter('ic_commerce_normal_view_data_grid_after_create_grid_items', array($this, 'ic_commerce_normal_view_data_grid_after_create_grid_items'),101);
				add_filter('ic_commerce_normal_view_extra_meta_keys', array($this, 'ic_commerce_normal_view_extra_meta_keys'),101);
				
			}
		}
		
		
		function ic_commerce_ultimate_report_page_init($constants = array(), $admin_page = ''){
			$this->constants = $constants;
			$report_name = isset($_REQUEST['report_name']) ? $_REQUEST['report_name'] : '';
			if($admin_page == 'icwoocommerceultimatereport_report_page' and $report_name == 'billing_state_page'){
				add_filter('ic_commerce_report_page_data_grid_items_create_grid_items', array($this, 'ic_commerce_report_page_data_grid_items_create_grid_items'),101,4);
				
			}
		}
		
		function ic_commerce_onload_search($onload_search = 'yes'){
			$onload_search = isset($_REQUEST['onload_search']) ? $_REQUEST['onload_search'] : $onload_search;
			return $onload_search;
		}
		
		function ic_commerce_report_page_data_grid_items_create_grid_items($order_items = '', $columns = '', $report_name = '', $request = '', $type = '', $zero = ''){
			
			$start_date = isset($request['start_date']) ? $request['start_date'] : '';
			$end_date   = isset($request['end_date']) ? $request['end_date'] : '';			
			$admin_url  = admin_url("admin.php").'?page=icwoocommerceultimatereport_details_page&detail_view=no&onload_search=yes&start_date='.$start_date.'&end_date='.$end_date;
			foreach($order_items as $key => $order_item){
				
				$order_count 		   = $order_item->order_count;
				$billing_country_name  = $order_item->billing_country_name;
				$billing_state_name 	= $order_item->billing_state_name;				
				$billing_country  	   = $order_item->billing_country;
				$billing_state_code 	= $order_item->billing_state_code;
				
				if($billing_state_code){
					$admin_url2 = $admin_url."&state_code=".$billing_state_code."&country_code=".$billing_country;				
					$order_items[$key]->billing_state_name = '<a href="'.$admin_url2.'">'.$billing_state_name.'</a>';
					$order_items[$key]->order_count = '<a href="'.$admin_url2.'">'.$order_count.'</a>';
				}
			}
			
			return $order_items;
		}
		
		
		function ic_commerce_normal_view_columns($columns = array(), $detail_column = array()){
			
			$billing_columns = array(
					"billing_address_index"  =>	__("Billing Address",		'icwoocommerce_textdomains')
					,"billing_address_1"	 =>	__("Billing Address 1",	  'icwoocommerce_textdomains')
					,"billing_address_2"	 =>	__("Billing Address 2",	  'icwoocommerce_textdomains')
					,"billing_postcode"	  =>	__("Billing Zip",	   	    'icwoocommerce_textdomains')
					,"billing_state"		 =>	__("Billing State",		  'icwoocommerce_textdomains')
					//,"billing_country"	   =>	__("Billing Country",		'icwoocommerce_textdomains')
					,"shipping_address_index" =>	__("Shipping Address",	  'icwoocommerce_textdomains')
					,"shipping_address_1"	=>	__("Shipping Address 1",	'icwoocommerce_textdomains')
					,"shipping_address_2"	=>	__("Shipping Address 2",	'icwoocommerce_textdomains')
					,"shipping_postcode"	 =>	__("Shipping Zip",	      'icwoocommerce_textdomains')
					,"shipping_state"		=>	__("Shipping State",		'icwoocommerce_textdomains')
					///,"shipping_country"	  =>	__("Shipping Country",	  'icwoocommerce_textdomains')
			);
			
			$new_columns = array();			
			foreach($columns as $column_name => $column_label){
				$new_columns[$column_name] = $column_label;
				if($column_name == 'billing_email'){
					foreach($billing_columns as $column_name2 => $column_label2){
						$new_columns[$column_name2] = $column_label2;
					}
				}
			}
			return $new_columns;
		}
		
		function ic_commerce_normal_view_extra_meta_keys($meta_keys = array()){
			$meta_keys[] = 'billing_country';
			$meta_keys[] = 'shipping_country';
			return $meta_keys;
		}
		
		function ic_commerce_normal_view_data_grid_after_create_grid_items($order_items = '', $columns = '', $zero = '', $type = '',$total_columns = ''){
			
			/*foreach($order_items as $key => $order_item){
				$shipping_state 		   = isset($order_item->shipping_state) ? $order_item->shipping_state : '';
				$shipping_state 		   = isset($order_item->shipping_state) ? $order_item->shipping_state : '';
				if($shipping_state){
					
				}
			}*/
			
			return $order_items;
		}
		
	}/*End Class*/
	
	$obj = new woocommerce_ultimate_report_customization();
}