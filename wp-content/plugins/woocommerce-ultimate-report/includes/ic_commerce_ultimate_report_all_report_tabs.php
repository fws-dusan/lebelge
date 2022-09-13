<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'IC_Commerce_Ultimate_Woocommerce_Report_All_Report_Tabs')){
	
	/*
	 * Class Name IC_Commerce_Ultimate_Woocommerce_Report_All_Report_Tabs
	 *
	 * Class is used for returning all reports tabs
	 *	 
	*/
	
	class IC_Commerce_Ultimate_Woocommerce_Report_All_Report_Tabs extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		
		/* variable declaration*/
		public $constants 	=	array();
		
		/*
		 * Function Name __construct
		 *
		 * Initialize Class Default Settings, Assigned Variables
		 *
		 * @constants (array) settings
		*/
		public function __construct($constants = array()) {
			$this->constants		= $constants;
		}
		
		/*
		 * Function Name get_parent_tabs
		 *
		 * Here we are creating defualt parent tabs
		 *
		 * @report_name (string) sub tab name
		 *
		 * @return (array) return parent tabs of all report page
		*/
		function get_parent_tabs($report_name = ''){
				$parent_tabs 									= array();
				$parent_tabs['product_summary']					= __('Product Summary', 						'icwoocommerce_textdomains');
				$parent_tabs['order_summary']					= __('Order Summary', 							'icwoocommerce_textdomains');
				$parent_tabs['customer_summary']				= __('Customer Summary',						'icwoocommerce_textdomains');
				$parent_tabs['stock_report']					= __('Stock Report', 							'icwoocommerce_textdomains');
				$parent_tabs['profit_report']					= __('Profit/Cost of Goods', 					'icwoocommerce_textdomains');
				$parent_tabs['other_summary']					= __('Other Summary', 							'icwoocommerce_textdomains');
				$parent_tabs 									= apply_filters('ic_commerce_report_page_parent_tabs',$parent_tabs);
				return $parent_tabs;
		}
		
		
		/*
		 * Function Name get_child_tabs
		 *
		 * Here we are creating defualt child tabs
		 *
		 * @parent_tab (string) parent tab name
		 *
		 * @report_name (string) sub tab name
		 *
		 * @return (array) return child tabs of all report page
		*/
		function get_child_tabs($parent_tab = '', $report_name = ''){
			$child_tabs = array();
			
			switch($parent_tab){
				case "product_summary":					
					$child_tabs['product_page']					= __('Product',									'icwoocommerce_textdomains');
					$child_tabs['category_page']				= __('Category',								'icwoocommerce_textdomains');
					$child_tabs['tag_page'] 					= __('Tag',										'icwoocommerce_textdomains');	
					break;
				case "order_summary":	
					$child_tabs['recent_order']					= __('Recent Order',							'icwoocommerce_textdomains');
					$child_tabs['payment_gateway_page']			= __('Payment Gateway',							'icwoocommerce_textdomains');
					$child_tabs['coupon_page']					= __('Coupon',									'icwoocommerce_textdomains');					
					$child_tabs['order_status']					= __('Order Status',							'icwoocommerce_textdomains');
					$child_tabs['manual_refund_detail_page']	= __('Refund Details',							'icwoocommerce_textdomains');
					$child_tabs['tax_page'] 					= __('Tax Report',								'icwoocommerce_textdomains');
					$child_tabs['summary_sales_report'] 		= __('Monthly Sales Report',					'icwoocommerce_textdomains');
					break;
				case "customer_summary":					
					$child_tabs['customer_page']				= __('Customer',								'icwoocommerce_textdomains');
					$child_tabs['billing_country_page']			= __('Billing Country',							'icwoocommerce_textdomains');	
					$child_tabs['billing_state_page']			= __('Billing State',							'icwoocommerce_textdomains');	
					$child_tabs['billing_city_page']			= __('Billing City',							'icwoocommerce_textdomains');
					$child_tabs['customer_buy_products_page']	= __('Customer Wise Products',					'icwoocommerce_textdomains');
					break;
				case "stock_report":
					$child_tabs['zero_level_stock_alert'] 		= __('Zero Level Stock',						'icwoocommerce_textdomains');
					$child_tabs['minimum_level_stock_alert'] 	= __('Minimum Level Stock',						'icwoocommerce_textdomains');
					$child_tabs['most_stocked'] 				= __('Most Stocked',							'icwoocommerce_textdomains');
					$child_tabs['summary_stock_planner'] 		= __('Summary Stock Planner',					'icwoocommerce_textdomains');
					$child_tabs['summary_stock_planner'] 		= __('Summary Stock Planner',					'icwoocommerce_textdomains');	
					$child_tabs['valuation_page'] 				= __('Stock Valuation',							'icwoocommerce_textdomains');				
					break;
				case "profit_report":	
					$child_tabs['cost_of_good_page'] 			= __('Product Cost of Goods',					'icwoocommerce_textdomains');				
					$child_tabs['monthly_profit_product'] 		= __('Monthly Profit Center',					'icwoocommerce_textdomains');
					break;
				case "other_summary":					
					$child_tabs['coupon_discount_type'] 		= __('Coupon Discount Type Wise Reporting',		'icwoocommerce_textdomains');
					$child_tabs['customer_analysis'] 			= __('New/Repeat Customer Analysis',			'icwoocommerce_textdomains');
					$child_tabs['frequently_order_customers'] 	= __('Frequently Order Customer',				'icwoocommerce_textdomains');
					$child_tabs['customer_in_price_point'] 		= __('Customer In Price Point',					'icwoocommerce_textdomains');
					$child_tabs['customer_not_purchased'] 		= __('Customer/Non Purchase',					'icwoocommerce_textdomains');	
					break;
				case "other_reports":					
					$child_tabs 								= isset($this->constants['other_reports']) ? $this->constants['other_reports'] : array();					
					break;
				default:
					break;
			}
								
			$child_tabs 										= apply_filters('ic_commerce_report_page_child_tabs',$child_tabs, $parent_tab, $report_name);
			
			return $child_tabs;
		}
		
		
		/*
		 * Function Name get_tabs_data
		 *
		 * Here we are creating tab html
		 *
		 * @report_name (string) sub tab name
		 *
		 * @return (sting) return tabs html sting
		*/
		function get_tabs_data($report_name = ''){
			$parent_tab  	= $this->get_request('parent_tab','product_summary');
			$report_name  	= $this->get_request('report_name',$report_name);
			$page  		 	= $this->get_request('page','');
			$parent_tabs 	= $this->get_parent_tabs($report_name);
			$other_reports	= $this->get_other_reports($report_name);
			$admin_url 		= admin_url('admin.php').'?page='.$page;
			$page_titles	= $this->get_child_tabs($parent_tab,$report_name);
			
			if(count($other_reports)>0){
				$parent_tabs['other_reports'] = __('Other Reports',	'icwoocommerce_textdomains');
			}
			//Rename Report Title
			$page_titles['coupon_discount_type'] = __('Coupon Discount Reporting',		'icwoocommerce_textdomains');
			
			$page_title 	= isset($page_titles[$report_name]) ? $page_titles[$report_name] : '';
			$page_title 	= apply_filters('ic_commerce_report_page_title',$page_title, $report_name);			
			$parent_tabs 	= apply_filters('ic_commerce_report_page_parent_tabs',$parent_tabs, $report_name);
			
			$ouptput = '';
			$ouptput .= '<div class="details-tabs hide_for_print">';
				$ouptput .= '<div class="responsive-tabs-default">';
					$ouptput .= '<ul class="responsive-tabs">';
							foreach($parent_tabs as $parent_tab_key => $parent_tab_label){
								$ouptput .= '<li id="button_'.$parent_tab_key.'">';
								$ouptput .= "<a href=\"#tab_{$parent_tab_key}\" id=\"{$parent_tab_key}\" data-tab=\"#tab_{$parent_tab_key}\" target=\"_self\">{$parent_tab_label}</a>";
								$ouptput .= '</li>';
							}
					$ouptput .= ' </ul>';
					$ouptput .= '<div class="clearfix"></div>';
					$ouptput .= '<div class="responsive-tabs-content tabs-content-remove-space">';
						foreach($parent_tabs as $parent_tab_key => $parent_tab_label){
							$ouptput .= '<div id="tab_'.$parent_tab_key.'" class="responsive-tabs-panel" style="padding: 0 15px;display:none;">';
							$ouptput .= '	<div class="row">';
							$ouptput .= '		<div>';
							$ouptput .= '			<div class="PluginMenu menu-wrap" style="padding-top:15px;">'."\n";
							$ouptput .= '					<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';
							$ouptput .= '						<div class="responsive-menu"><a href="#" id="menu-icon"></a></div>';
																$child_tabs = $this->get_child_tabs($parent_tab_key, $report_name);
																foreach ( $child_tabs as $key => $value ) {
																	$ouptput .= '<a href="'.$admin_url.'&parent_tab='.$parent_tab_key.'&report_name='.urlencode($key).'" data-parent_tab="'.$parent_tab_key.'" class="nav-tab ';
																	if ( $report_name == $key ) {
																		$ouptput .= 'nav-tab-active';
																	}
																	$ouptput .= '">' . esc_html( $value ) . '</a>';
															   }
							$ouptput .= '					</h2>';
							$ouptput .= '				</div>';
							$ouptput .= '			</div>';
							$ouptput .= '		<div class="clearfix"></div>';
							$ouptput .= '	</div>';
							$ouptput .= '</div>';
						}
					$ouptput .= '</div>';
				$ouptput .= '</div>';
			$ouptput .= '</div>';
			
			$return['report_title'] = '<h2 class="hide_for_print">'.$page_title.'</h2>';
			$return['parent_tabs'] 	= $parent_tabs;
			$return['page_title'] 	= $page_title;
			$return['report_name'] 	= $report_name;
			$return['ouptput'] 		= $ouptput;
			return $return;
		}
		
		
		/*
		 * Function Name get_other_reports
		 *
		 * Here we are assigning other tabs
		 *
		 * @report_name (string) sub tab name
		 *
		 * @return (array) return other tabs html sting
		*/
		function get_other_reports($report_name = ""){
			$page_titles 						= array();			
			$other_reports 						= apply_filters('ic_commerce_report_page_titles',$page_titles,$report_name);
			$this->constants['other_reports'] 	= $other_reports;
			return $other_reports;	
		}
	}//END class 
}//END Clas check