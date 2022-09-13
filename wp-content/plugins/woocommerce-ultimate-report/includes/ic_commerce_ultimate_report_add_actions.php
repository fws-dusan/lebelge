<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*Schedule Mailings*/
require_once('ic_commerce_ultimate_report_schedule_mailing.php');
$IC_Commerce_Ultimate_Woocommerce_Report_Schedule_Mailing = new IC_Commerce_Ultimate_Woocommerce_Report_Schedule_Mailing(__FILE__,'icwoocommerceultimatereport');

/*Stock Alert*/
require_once('ic_commerce_ultimate_report_summary_stock_alert_email.php');
$IC_Commerce_Ultimate_Woocommerce_Report_Summary_Stock_Alert_Email = new IC_Commerce_Ultimate_Woocommerce_Report_Summary_Stock_Alert_Email(array(), 'icwoocommerceultimatereport');
				
if(!function_exists('ic_commerce_ultimate_report_page_init')){
	
	/*
	 * Function Name ic_commerce_ultimate_report_page_init
	 *
	 * Initialize More Reports
	 *
	 * @constants (array) settings
	 * @admin_page (string) admin pagenave
	*/
	
	function ic_commerce_ultimate_report_page_init($constants = array(), $admin_page = ""){
		
			if(	
				$admin_page == "icwoocommerceultimatereport_details_page"
			|| 	$admin_page == "icwoocommerceultimatereport_variation_page"
			|| 	$admin_page == "icwoocommerceultimatereport_report_page"
			
			){
				
				global $IC_Commerce_Ultimate_Woocommerce_Report_Advance_Variation;
				$path = WP_PLUGIN_DIR."/woocommerce-ultimate-report/includes/";
				
				if(file_exists($path.'ic_commerce_ultimate_report_customization.php')){
					require_once($path.'ic_commerce_ultimate_report_customization.php');
					$IC_Commerce_Ultimate_Woocommerce_Report_Customization = new IC_Commerce_Ultimate_Woocommerce_Report_Customization($constants, $admin_page);
				}
				
			}
			
			if($admin_page == "icwoocommerceultimatereport_details_page"){
				require_once($path.'ic_commerce_ultimate_report_custom_columns.php');
				$IC_Commerce_Ultimate_Woocommerce_Report_Custom_Columns = new IC_Commerce_Ultimate_Woocommerce_Report_Custom_Columns($constants, $admin_page);
			}
			
			if($admin_page == "icwoocommerceultimatereport_report_page"){
				$path = WP_PLUGIN_DIR."/woocommerce-ultimate-report/includes/";
				$report_name = isset($_REQUEST['report_name']) ? $_REQUEST['report_name'] : '';
				
				
				
				switch($report_name){
					case "coupon_discount_type":
						if(file_exists($path.'ic_commerce_ultimate_report_summary_coupon_discount_type.php')){
							require_once($path.'ic_commerce_ultimate_report_summary_coupon_discount_type.php');
							$IC_Commerce_Ultimate_Woocommerce_Report_Coupon_Discount_Type = new IC_Commerce_Ultimate_Woocommerce_Report_Coupon_Discount_Type($constants, $admin_page);
						}
						break;
					case "customer_not_purchased":
						if(file_exists($path.'ic_commerce_ultimate_report_summary_customer_not_purchased.php')){
							require_once($path.'ic_commerce_ultimate_report_summary_customer_not_purchased.php');
							$IC_Commerce_Ultimate_Woocommerce_Report_Customer_Not_Purcahsed = new IC_Commerce_Ultimate_Woocommerce_Report_Customer_Not_Purcahsed($constants, $admin_page);
						}
						break;
					case "tag_page":
						if(file_exists($path.'ic_commerce_ultimate_report_summary_tag_based.php')){
							require_once($path.'ic_commerce_ultimate_report_summary_tag_based.php');
							$IC_Commerce_Ultimate_Woocommerce_Report_Tag_Based = new IC_Commerce_Ultimate_Woocommerce_Report_Tag_Based($constants, $admin_page);
						}
						break;
					case "summary_sales_report":
						if(file_exists($path.'ic_commerce_ultimate_report_summary_sales_reports.php')){
							require_once($path.'ic_commerce_ultimate_report_summary_sales_reports.php');
							$IC_Commerce_Ultimate_Woocommerce_Report_Summary_Sales_Reports = new IC_Commerce_Ultimate_Woocommerce_Report_Summary_Sales_Reports($constants, $admin_page);
						}
						break;
					case "customer_in_price_point":
						if(file_exists($path.'ic_commerce_ultimate_report_customer_in_price_point.php')){
							require_once($path.'ic_commerce_ultimate_report_customer_in_price_point.php');
							$IC_Commerce_Ultimate_Woocommerce_Report_Customer_In_Price_Point = new IC_Commerce_Ultimate_Woocommerce_Report_Customer_In_Price_Point($constants, $admin_page);
						}
						break;
					case "summary_stock_planner":
						if(file_exists($path.'ic_commerce_ultimate_report_summary_stock_planner.php')){
							require_once($path.'ic_commerce_ultimate_report_summary_stock_planner.php');
							$IC_Commerce_Ultimate_Woocommerce_Report_Summary_Stock_Planner = new IC_Commerce_Ultimate_Woocommerce_Report_Summary_Stock_Planner($constants, $admin_page);
						}
						break;
					case "frequently_order_customers":
						if(file_exists($path.'ic_commerce_ultimate_report_summary_frequently_order_customers.php')){
							require_once($path.'ic_commerce_ultimate_report_summary_frequently_order_customers.php');
							$IC_Commerce_Ultimate_Woocommerce_Report_Summary_Frequently_Order_Customer = new IC_Commerce_Ultimate_Woocommerce_Report_Summary_Frequently_Order_Customer($constants, $admin_page);
						}
						break;
					case "customer_analysis":
						if(file_exists($path.'ic_commerce_ultimate_report_summary_customer_analysis.php')){
							require_once($path.'ic_commerce_ultimate_report_summary_customer_analysis.php');
							$IC_Commerce_Ultimate_Woocommerce_Report_Summary_Customer_Analysis = new IC_Commerce_Ultimate_Woocommerce_Report_Summary_Customer_Analysis($constants, $admin_page);
						}
						break;
					case "zero_level_stock_alert":
					case "minimum_level_stock_alert":
					case "most_stocked":
						if(file_exists($path.'ic_commerce_ultimate_report_summary_stock_alert.php')){
							require_once($path.'ic_commerce_ultimate_report_summary_stock_alert.php');
							$IC_Commerce_Ultimate_Woocommerce_Report_Summary_Stock_Alert = new IC_Commerce_Ultimate_Woocommerce_Report_Summary_Stock_Alert($constants, $admin_page);
						}
						break;
					default:
						break;
				}
			}
	}	
	add_action("ic_commerce_ultimate_report_page_init","ic_commerce_ultimate_report_page_init", 10, 2);	
}


