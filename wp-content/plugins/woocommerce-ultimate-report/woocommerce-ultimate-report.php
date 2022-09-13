<?php 
/**
Plugin Name: Woocommerce Ultimate Reports
Author: WooPro
Description: The latest release of our WooCommerce Report Plug-in has all features of Gold version plus new features like Projected Vs Actual Sales, Comprehensive Tax based Reporting, Improvised Dashboard, Filters by Variation Attributes, Sales summary by Map View, Graphs and much more.
Version: 3.2.1
Author URI: http://woochamps.com/

Text Domain: icwoocommerce_textdomains
Domain Path: /languages/

Copyright: (c) 2021 WooPro (info@woochamps.com)

Tested up to: 5.7.x
WC requires at least: 3.5.x
WC tested up to: 5.2.x

Last Update Date: October 31, 2020
Last Update Date: March 17, 2021
Last Update Date: April 28, 2021

Removed country join query from tax report, working with after setting

**/   

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!function_exists('init_icwoocommerceultimatereport')){
	
	if(!function_exists('ic_woocommerce_my_account_my_orders_actions')){
		function ic_woocommerce_my_account_my_orders_actions( $actions, $order ) {
			if ( $order->has_status( 'completed' ) ) {
				$actions['order-again'] = array(
					'url'  => wp_nonce_url( add_query_arg( 'order_again', $order->id ) , 'woocommerce-order_again' ),
					'name' => __( 'Order Again', 'woocommerce' )
				);
			}
			return $actions;
		}
		//add_filter( 'woocommerce_my_account_my_orders_actions', 'ic_woocommerce_my_account_my_orders_actions', 50, 2 );
	}
	
	if(!function_exists('ic_commerce_onload_search')){
		function ic_commerce_onload_search($onload_search = ''){
			return 'no';
		}
	}
	
	if(!function_exists('ic_commerce_onload_search_text')){
		function ic_commerce_onload_search_text($onload_search_text = ''){		
			$onload_search_text = "<div class=\"order_not_found\">".__("In order to view the results please hit \"<strong>Search</strong>\" button.",'icwoocommerce_textdomains')."</div>";
			return $onload_search_text;
		}	
	}
	
	if(!function_exists('gettext_icwoocommerceultimatereport')){
		function gettext_icwoocommerceultimatereport($translated_text = '', $text = '', $domain = ''){
			if($domain == 'icwoocommerce_textdomains'){
				//return '['.$translated_text.']';
			}		
			return $translated_text;
		}
		//add_filter('gettext','gettext_icwoocommerceultimatereport',20,3);
	}
	
	function init_icwoocommerceultimatereport() {
		global $woocommerce_ultimate_report, $woocommerce_ultimate_report_constant;
		
		$constants = array(
				"version" 				=> "3.2.1"
				,"product_id" 			=> "1583"
				,"plugin_key" 			=> "icwoocommerceultimatereport"
				,"plugin_main_class" 	=> "Woocommerce_Ultimate_Report"
				,"plugin_instance" 		=> "woocommerce_ultimate_report"
				,"plugin_dir" 			=> 'woocommerce-ultimate-report'
				,"plugin_file" 			=> __FILE__
				,"__FILE__" 			=> __FILE__
				,"plugin_role" 			=> apply_filters('ic_commerce_ultimate_report_plugin_role','manage_woocommerce')//'read'
				,"per_page_default"		=> 5
				,"plugin_parent_active" => false
				,"color_code" 			=> '#77aedb'
				,"plugin_parent" 		=> array(
					"plugin_name"		=> "WooCommerce"
					,"plugin_slug"		=> "woocommerce/woocommerce.php"
					,"plugin_file_name"	=> "woocommerce.php"
					,"plugin_folder"	=> "woocommerce"
					,"order_detail_url"	=> "post.php?&action=edit&post="
				)			
		);
		
		if(!defined('WC_VERSION')){
			define('WC_VERSION',0);
		}
		
		$constants['is_wc_ge_27'] 		= version_compare( WC_VERSION, '2.7', '<' );
		$constants['is_wc_ge_3_0'] 		= version_compare( WC_VERSION, '3.0', '>=' );
		$constants['is_wc_ge_3_0_5'] 	= version_compare( WC_VERSION, '3.0.5', '>=' );
		
		add_filter('ic_commerce_onload_search',		'ic_commerce_onload_search');
		add_filter('ic_commerce_onload_search_text','ic_commerce_onload_search_text');
		
		require_once('includes/ic_commerce_ultimate_report_functions.php');
		
		load_plugin_textdomain('icwoocommerce_textdomains', WP_PLUGIN_DIR.'/'.$constants['plugin_dir'].'/languages',$constants['plugin_dir'].'/languages');
		$constants['plugin_name'] 		= __('Ultimate Woocommerce Report', 	'icwoocommerce_textdomains');
		$constants['plugin_menu_name'] 	= __('Ultimate Report',								'icwoocommerce_textdomains');
		$constants['admin_page'] 		= isset($_REQUEST['page']) ? $_REQUEST['page'] : "";
		$constants['is_admin'] 			= is_admin();
		
		$constants = apply_filters('ic_commerce_ultimate_report_init_constants', $constants, $constants['plugin_key']);
		do_action('ic_commerce_ultimate_report_textdomain_loaded',$constants, $constants['plugin_key']);
		
		$woocommerce_ultimate_report_constant = $constants;
		
		require_once('includes/ic_commerce_ultimate_report_add_actions.php');
		
		if(!defined('DOMPDF_FONT_DIR_URL')){
			define("DOMPDF_FONT_DIR_URL", plugins_url("",__FILE__). "/dompdf-master/lib/fonts/");
		}
		
		if ($constants['is_admin']) {
				
				require_once('includes/ic_commerce_ultimate_report_init.php');
								
				if(!class_exists('Woocommerce_Ultimate_Report')){class Woocommerce_Ultimate_Report extends IC_Commerce_Ultimate_Woocommerce_Report_Init{}}
				
				$woocommerce_ultimate_report 			= new Woocommerce_Ultimate_Report( __FILE__, $woocommerce_ultimate_report_constant);
		}
	}
}

add_action('init','init_icwoocommerceultimatereport', 100);

if(!function_exists('get_ic_commerce_ultimate_report_textdomain_loaded')){	
	function get_ic_commerce_ultimate_report_textdomain_loaded($constants = array(), $plugin_key = ""){
				
		$path = WP_PLUGIN_DIR."/woocommerce-ultimate-report/includes/";
		
		$path.'ic_commerce_ultimate_report_cog_init.php';
		if(file_exists($path.'ic_commerce_ultimate_report_cog_init.php')){
			require_once('includes/ic_commerce_ultimate_report_cog_init.php');
			$IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Init = new IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Init($constants, $plugin_key);
		}
		
	}
	add_action('ic_commerce_ultimate_report_textdomain_loaded','get_ic_commerce_ultimate_report_textdomain_loaded', 100,2);
}

if(!function_exists('ic_commerce_ultimate_report_woocommerce_hidden_order_itemmeta')){
	function ic_commerce_ultimate_report_woocommerce_hidden_order_itemmeta($hidden_meta = array()){
		if(isset($_REQUEST['post']) and $_REQUEST['post'] > 0){
			if(!is_admin()){
				$hidden_meta[] = '_ic_cogs_item';
				$hidden_meta[] = '_ic_cogs_item_total';
			}
		}else{
			if(!isset($_REQUEST['order_id'])){
				$hidden_meta[] = '_ic_cogs_item';
				$hidden_meta[] = '_ic_cogs_item_total';
			}
		}
		return $hidden_meta;
	}
	add_action( 'woocommerce_hidden_order_itemmeta', 'ic_commerce_ultimate_report_woocommerce_hidden_order_itemmeta');
}


if(!function_exists('custom_ic_commerce_ultimate_report_plugin_role')){
	function custom_ic_commerce_ultimate_report_plugin_role($plugin_role = 'manage_woocommerce'){
		$saved_enabled_pages = get_option('icwoocommerceultimatereport_page_enabled_pages',array());
		if(count($saved_enabled_pages) > 0){
			$plugin_role = 'read';
		}
		return $plugin_role;
	}	
	add_filter('ic_commerce_ultimate_report_plugin_role','custom_ic_commerce_ultimate_report_plugin_role',11);
}

