<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	require_once('ic_commerce_ultimate_report_functions.php');

if(!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Init')){
	/*
	 * Class Name IC_Commerce_Ultimate_Woocommerce_Report_Init
	 *
	*/
	class IC_Commerce_Ultimate_Woocommerce_Report_Init extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
			
			/* variable declaration constants*/
			public $constants 				= array();			
			public $plugin_parent			= NULL;
			
			/**
			* __construct
			* @param string $file 
			* @param string $constants 
			*/
			public function __construct($file, $constants) {
				global $icpgpluginkey, $icperpagedefault, $iccurrent_page, $wp_version;
				if(is_admin()){
					
					add_action( 'admin_notices', array( $this, 'admin_notices'));
					
					$this->file 								= $file;					
					$this->constants 							= $constants;					
					$icpgpluginkey 								= $this->constants['plugin_key'];
					$icperpagedefault 							= $this->constants['per_page_default'];
					$this->constants['plugin_options'] 			= get_option($this->constants['plugin_key']);
					$ic_commercepro_pages						= $this->get_report_pages($icpgpluginkey);
					$ic_current_page							= $this->get_request('page',NULL,false);
					
					$this->check_parent_plugin();					
					$this->define_constant();
					
					do_action('ic_commerce_ultimate_report_init', $this->constants, $ic_current_page);
					
					add_action( 'admin_init', 					array( $this, 'export_csv'));
					add_action( 'admin_init', 					array( $this, 'export_pdf'));
					add_action( 'admin_init', 					array( $this, 'export_print'));
					add_action( 'admin_init', 					array( $this, 'pdf_invoice'));
					
					add_action( 'wp_loaded', 					array( $this, 'setting_page'));//Change to init to wp_loaded 20150721
					
					add_action( 'ic_commerce_autocomplete_product_types', 	array( $this, 'ic_commerce_autocomplete_product_types'),101,3);
					
					
					add_action('wp_ajax_'.$this->constants['plugin_key'].'_wp_ajax_action', array($this, 'wp_ajax_action'));
					
					if(in_array($ic_current_page, $ic_commercepro_pages)){
						$this->constants						= apply_filters('ic_commerce_ultimate_report_constants', $this->constants);
						do_action('ic_commerce_ultimate_report_page_init', $this->constants, $ic_current_page);
						add_action('admin_enqueue_scripts', 	array($this, 'wp_localize_script'));
						add_action('admin_init', 				array($this, 'admin_head'));
						add_action('admin_footer',  			array($this, 'admin_footer'),9);
					}
					
					add_action('admin_menu',					array( &$this, 'admin_menu' ) );					
					add_action('activated_plugin',				array($this->constants['plugin_instance'],							'activated_plugin'));
					
					register_activation_hook(	$this->constants['plugin_file'],	array('IC_Commerce_Ultimate_Woocommerce_Report_Init',	'activate'));
					register_deactivation_hook(	$this->constants['plugin_file'], 	array('IC_Commerce_Ultimate_Woocommerce_Report_Init',	'deactivation'));
					register_uninstall_hook(	$this->constants['plugin_file'], 	array('IC_Commerce_Ultimate_Woocommerce_Report_Init',	'uninstall'));
					
					add_filter( 'plugin_action_links_'.$this->constants['plugin_slug'], array( $this, 'plugin_action_links' ), 9, 2 );
					
					if ( version_compare( $wp_version, '2.8alpha', '>' ) )
						add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );
						
					
					if ( version_compare( $wp_version, '3.3', '>' ) )
						add_action('admin_bar_menu', array( $this, 'admin_bar_menu'), 1000);
						
					add_action('admin_menu', 				array($this, 'permission_settings'),1000);
					
					add_action('woocommerce_debug_tools', 	array($this, 'woocommerce_debug_tools'),1000);
				}
			}
			
			

			
			function get_text($translated_text, $text, $domain){
				if($domain == 'icwoocommerce_textdomains'){
					//return '['.$translated_text.']';
				}		
				return $translated_text;
			}
			
			function permission_settings(){
				if($this->constants['plugin_key'] == 'icwoocommerceultimatereport'){
					require_once('ic_commerce_ultimate_report_permission.php');
					$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Permission_Settings($this->constants, $this->constants['plugin_key']);
					$intence->admin_menu();
				}
			}
			
			/**
			* get_report_pages
			* This function is used to get Report Pages.
			* @param string $icpgpluginkey 
			* @param array $ic_commercepro_pages 
			* @return array
			*/
			function get_report_pages($icpgpluginkey, $ic_commercepro_pages = array()){
				
				$ic_commercepro_pages[]	= $icpgpluginkey;
				$ic_commercepro_pages[]	= $icpgpluginkey.'_page';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_report_page';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_options_page';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_details_page';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_customer_page';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_cross_tab_page';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_variation_page';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_tax_report_page';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_stock_list_page';				
				$ic_commercepro_pages[]	= $icpgpluginkey.'_product_analysis';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_variation_stock_page';
				//$ic_commercepro_pages[]	= $icpgpluginkey.'_google_analytics_page';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_variable_product_analysis';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_projected_actual_sales_page';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_group_variable_product_analysis';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_daily_sales_report';		
				$ic_commercepro_pages[]	= $icpgpluginkey.'_product_wise_new_customer';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_customer_wise_new_product';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_new_repeat_customer';
				$ic_commercepro_pages[]	= $icpgpluginkey.'_permission';
				$ic_commercepro_pages 	= apply_filters('ic_commerce_ultimate_report_pages', $ic_commercepro_pages, $icpgpluginkey);
				
				return $ic_commercepro_pages;
			}
		
			/**
			* define_constant
			*/
			function define_constant(){
				global $icpgpluginkey, $icperpagedefault, $iccurrent_page, $wp_version;
				
				//New Change ID 20140918
				$this->constants['detault_stauts_slug'] 	= array("completed","on-hold","processing");
				$this->constants['detault_order_status'] 	= array("wc-completed","wc-on-hold","wc-processing");
				$this->constants['hide_order_status'] 		= array();
				
				$this->constants['sub_version'] 			= '20200710';
				$this->constants['last_updated'] 			= '20200710';
				$this->constants['customized'] 				= 'no';
				$this->constants['customized_date'] 		= '20200710';
				
				$this->constants['first_order_date'] 		= $this->first_order_date($this->constants['plugin_key']);
				$this->constants['total_shop_day'] 			= $this->get_total_shop_day($this->constants['plugin_key']);
				$this->constants['today_date'] 				= date_i18n("Y-m-d");
				
				$this->constants['post_status']				= $this->get_setting2('post_status',$this->constants['plugin_options'],array());
				$this->constants['hide_order_status']		= $this->get_setting2('hide_order_status',$this->constants['plugin_options'],$this->constants['hide_order_status']);
				$this->constants['start_date']				= $this->get_setting('start_date',$this->constants['plugin_options'],date_i18n("Y-m-01"));
				$this->constants['end_date']				= $this->get_setting('end_date',$this->constants['plugin_options'],$this->constants['today_date']);
				
				$this->constants['wp_version'] 				= $wp_version;
				
				$file 										= $this->constants['plugin_file'];
				$this->constants['plugin_slug'] 			= plugin_basename( $file );
				$this->constants['plugin_file_name'] 		= basename($this->constants['plugin_slug']);
				$this->constants['plugin_file_id'] 			= basename($this->constants['plugin_slug'], ".php" );
				$this->constants['plugin_folder']			= dirname($this->constants['plugin_slug']);
				$this->constants['plugin_url'] 				= plugins_url("", $file);
				$this->constants['plugin_dir'] 				= WP_PLUGIN_DIR ."/". $this->constants['plugin_folder'];				
				$this->constants['http_user_agent'] 		= isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
				$this->constants['siteurl'] 				= site_url();
				$this->constants['admin_page_url']			= $this->constants['siteurl'].'/wp-admin/admin.php';				
				$this->constants['post_order_status_found']	= isset($this->constants['post_order_status_found']) ? $this->constants['post_order_status_found'] : 0;//Added 20150225
				$this->constants['classes_path'] 			= $this->constants['plugin_dir'].'/includes/';

				
			}
			
			/**
			* activate
			* This Function is used when Plugin is Activated.
			*/
			public static function activate(){
				global $icpgpluginkey, $icperpagedefault;
				
				$icpgpluginkey 			= "icwoocommerceultimatereport";
				$icperpagedefault 		= 5;
				
				$blog_title 			= get_bloginfo('name');
				$email_send_to			= get_option( 'admin_email' );
				$strtotime				= strtotime('this month');
				$cross_tab_start_date 	= date_i18n('Y-01-01',$strtotime);
				$cross_tab_end_date 	= date_i18n('Y-12-31',$strtotime);
				$cross_tab_start_date 	= trim($cross_tab_start_date);
				$cross_tab_end_date 	= trim($cross_tab_end_date);				
				$current_year			= date('Y',$strtotime);
				
				$default = array(
					'recent_order_per_page'				=> $icperpagedefault
					,'top_product_per_page'				=> $icperpagedefault
					,'top_customer_per_page'			=> $icperpagedefault
					,'top_billing_country_per_page'		=> $icperpagedefault
					,'top_payment_gateway_per_page'		=> $icperpagedefault
					,'top_coupon_per_page'				=> $icperpagedefault
					,'per_row_customer_page'			=> $icperpagedefault
					,'per_row_details_page'				=> $icperpagedefault
					,'per_row_stock_page'				=> $icperpagedefault
					,'per_row_all_report_page'			=> $icperpagedefault
					,'per_row_cross_tab_page'			=> $icperpagedefault
					
					
					,'email_daily_report' 				=> 1
					,'email_yesterday_report' 			=> 1
					,'email_weekly_report' 				=> 1
					,'email_last_week_report' 			=> 1
					,'email_monthly_report' 			=> 1
					,'email_last_month_report' 			=> 1
					,'email_this_year_report' 			=> 1
					,'email_last_year_report' 			=> 1
					,'email_till_today_report' 			=> 1
					
					,'email_send_to'					=> $email_send_to
					,'email_from_name'					=> $blog_title
					,'email_from_email'					=> $email_send_to
					,'email_subject'					=> "Sales Summary " . $blog_title
					,'email_schedule'					=> 'daily'
					,'act_email_reporting'				=> 0
					//20150217
					,'logo_image'							=> ''
					,'company_name'							=> $blog_title
					,'show_dasbboard_summary_box'			=> 1
					,'show_dasbboard_order_summary'			=> 1
					,'show_dasbboard_sales_order_status'	=> 1			
					,'show_dasbboard_sales_order_status'	=> 1
					,'show_dasbboard_top_products'			=> 1
					,'show_dasbboard_top_billing_country'	=> 1
					,'show_dasbboard_top_payment_gateway'	=> 1
					,'show_dasbboard_top_recent_orders'		=> 1
					,'show_dasbboard_top_customer'			=> 1
					,'show_dasbboard_top_coupons'			=> 1
					
					,'show_dasbboard_graph_ss'				=> 1
					,'show_dasbboard_graph_ao'				=> 1
					,'hide_order_status'					=> 'trash'
					
					,'cogs_enable_adding'					=> 0
					,'cogs_enable_reporting'					=> 0
					,'cogs_metakey'							=> '_ic_cogs_cost'
					
					
					,'cur_projected_sales_year'				=> $current_year
					,'projected_sales_year' 				=> $current_year
					
					
					
					,'cross_tab_start_date'					=> $cross_tab_start_date
					,'cross_tab_end_date'					=> $cross_tab_end_date
					
					,'company_name'							=> $blog_title
					,'pdf_invoice_company_name'				=> $blog_title
					,'report_title'							=> $blog_title ." report"
					,'theme_color'							=> '#77aedb'//$this->constants['color_code']
					
					//New Graph Settings 20150407
					,'tick_angle'							=> 0
					,'tick_font_size'						=> 9
					,'tick_char_length'						=> 15
					,'tick_char_suffix'						=> "..."
					,'graph_height'							=> 300
				);
				
				$projected_sales_year_option 					= $icpgpluginkey.'_projected_amount_'.$current_year;
				$projected_amounts_old 							= get_option($projected_sales_year_option,false);
				
				if(!$projected_amounts_old){
					$total_projected_amount	= 0;
					$projected_amounts		= array();
					$projected_month_list	= array("January","February","March","April","May","June","July","August","September","October","November","December");
					for($m = 0;$m<=11;$m++){
						$l 										= $projected_month_list[$m];				
						$l 										= $projected_month_list[$m];
						$projected_sales_month_current			= rand(1111,2222);
						$projected_amounts[$l] 					= $projected_sales_month_current;					
						$default["projected_sales_month_{$l}"] 	= $projected_sales_month_current;						
						$total_projected_amount 				= $total_projected_amount + $projected_sales_month_current;
					}
					
					$total_projected_amount_option 	= $icpgpluginkey.'_total_projected_amount_'.$current_year;
					update_option($projected_sales_year_option,$projected_amounts);
					update_option($total_projected_amount_option,$total_projected_amount);
					
					
				}
				
				
				
				//Added 20150217
				$o = get_option($icpgpluginkey,false);
				if(!$o){
					delete_option( $icpgpluginkey);
					add_option( $icpgpluginkey, $default );			
					add_option( $icpgpluginkey.'_per_page_default', $icperpagedefault);
				}
								
				//echo $icpgpluginkey;
				//add_option( $icpgpluginkey, $default );			
				//add_option( $icpgpluginkey.'_per_page_default', $icperpagedefault);
				
				//echo $icpgpluginkey." - ".$icperpagedefault;
				//exit;
			}
			
			/**
			* deactivation
			* This Function is used when Plugin is Deactivated.
			*/
			public static function deactivation(){
				global $icpgpluginkey;
				$icpgpluginkey 			= "icwoocommerceultimatereport";
				delete_option( $icpgpluginkey.'_admin_notice_error');
			}
			
			/**
			* uninstall
			* This Function is used when Plugin is Unistall.
			*/
			public static function uninstall(){
				global $icpgpluginkey, $wpdb;
				$icpgpluginkey 			= "icwoocommerceultimatereport";				
				$wpdb->query("DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '{$icpgpluginkey}%'");
				$wpdb->query("DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE 'ic_commerce_%'");
			}
			
			/**
			* activated_plugin
			* This Function is used when Plugin is already activated.
			*/
			public static function activated_plugin(){
				global $icpgpluginkey;
				$icpgpluginkey 			= "icwoocommerceultimatereport";
				update_option($icpgpluginkey.'_activated_plugin_error',  ob_get_contents());
				//delete_option('icwoocommerceultimatereport_page_enabled_pages');
			}
			
			/**
			* plugin_action_links
			* @param string $plugin_links
			* @param string $file
			* @return array
			*/
			function plugin_action_links($plugin_links, $file){
				if ( ! current_user_can( $this->constants['plugin_role'] ) ) return;
				if ( $file == $this->constants['plugin_slug']) {
					$settings_link = array();
					$settings_link[] = '<a href="'.admin_url('admin.php?page='.$this->constants['plugin_key'].'_page').'" 			title="'.__($this->constants['plugin_name'].' Dashboard', 	'icwoocommerce_textdomains').'">'.__('Dashboard', 	'icwoocommerce_textdomains').'</a>';

						$settings_link[] = '<a href="'.admin_url('admin.php?page='.$this->constants['plugin_key'].'_details_page').'" 	title="'.__($this->constants['plugin_name'].' Reports', 	'icwoocommerce_textdomains').'">'.__('Detail', 		'icwoocommerce_textdomains').'</a>';
						$settings_link[] = '<a href="'.admin_url('admin.php?page='.$this->constants['plugin_key'].'_options_page').'" 	title="'.__($this->constants['plugin_name'].' Settings', 	'icwoocommerce_textdomains').'">'.__('Settings', 	'icwoocommerce_textdomains').'</a>';

					
					return array_merge( $plugin_links, $settings_link );
				}		
				return $plugin_links;
			}
			
			/**
			* plugin_row_meta
			* @param string $plugin_meta
			* @param string $plugin_file
			* @param string $plugin_data
			* @param string $status
			* @return array
			*/
			function plugin_row_meta($plugin_meta, $plugin_file, $plugin_data, $status ){
				if ( $plugin_file == $this->constants['plugin_slug']) {
					$settings_link = array();
					$settings_link[] = '<a href="'.admin_url('admin.php?page='.$this->constants['plugin_key'].'_page').'" 			title="'.__($this->constants['plugin_name'].' Dashboard', 	'icwoocommerce_textdomains').'">'.__('Dashboard', 	'icwoocommerce_textdomains').'</a>';
					
						$settings_link[] = '<a href="'.admin_url('admin.php?page='.$this->constants['plugin_key'].'_details_page').'" 	title="'.__($this->constants['plugin_name'].' Reports', 	'icwoocommerce_textdomains').'">'.__('Detail', 		'icwoocommerce_textdomains').'</a>';
						$settings_link[] = '<a href="'.admin_url('admin.php?page='.$this->constants['plugin_key'].'_options_page').'" 	title="'.__($this->constants['plugin_name'].' Settings', 	'icwoocommerce_textdomains').'">'.__('Settings', 	'icwoocommerce_textdomains').'</a>';
					return array_merge( $plugin_meta, $settings_link );
				}		
				return $plugin_meta;
			}
			
			/*
				* Function Name admin_menu
				*
				* this function work when wordpress hook 'plugins_loaded' call, and add admin pages	
			*/
			function admin_menu(){
				
				add_menu_page($this->constants['plugin_name'], $this->constants['plugin_menu_name'], $this->constants['plugin_role'], $this->constants['plugin_key'].'_page', array($this, 'add_page'), plugins_url( '/assets/images/menu_icons.png',$this->constants['plugin_file']), '58.95' );
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Dashboard', 	'icwoocommerce_textdomains'),	__( 'Dashboard',	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_page',				array( $this, 'add_page' ));
				
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Details', 	'icwoocommerce_textdomains'),	__( 'Details',		'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_details_page',		array( $this, 'add_page' ));
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' All Details', 'icwoocommerce_textdomains'),	__( 'All Details',	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_report_page',			array( $this, 'add_page' ));
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Crosstab', 	'icwoocommerce_textdomains'),	__( 'Crosstab',		'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_cross_tab_page',		array( $this, 'add_page' ));
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Variation', 	'icwoocommerce_textdomains'),	__( 'Variation',	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_variation_page',		array( $this, 'add_page' ));
				//add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Google Analytics', 	'icwoocommerce_textdomains'),	__( 'Google Analytics',	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_google_analytics_page',		array( $this, 'add_page' ));
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Stock List', 	'icwoocommerce_textdomains'),	__( 'Stock List', 	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_stock_list_page',		array( $this, 'add_page' ));
				
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Variation Stock List', 	'icwoocommerce_textdomains'),	__( 'Variation Stock', 	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_variation_stock_page',		array( $this, 'add_page' ));
				
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Projected Vs Actual Sales', 	'icwoocommerce_textdomains'),	__( 'Projected Vs Actual Sales', 	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_projected_actual_sales_page',		array( $this, 'add_page' ));
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Tax Report', 	'icwoocommerce_textdomains'),	__( 'Tax Reports',	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_tax_report_page',	array( $this, 'add_page' ));
				
				/*New reports Jul-20-2017*/
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Daily Sales Report', 	'icwoocommerce_textdomains'),	__( 'Daily Sales Report',	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_daily_sales_report',	array( $this, 'add_page' ));
				
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Product wise New Customer', 	'icwoocommerce_textdomains'),	__( 'Product wise New Customer',	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_product_wise_new_customer',	array( $this, 'add_page' ));
				
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Customer wise New Product', 	'icwoocommerce_textdomains'),	__( 'Customer wise New Product',	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_customer_wise_new_product',	array( $this, 'add_page' ));
				
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' New / Repeat Customer', 	'icwoocommerce_textdomains'),	__( 'New / Repeat Customer',	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_new_repeat_customer',	array( $this, 'add_page' ));
				
				
				
				/*END*/
				
				
				/*Sales Analysis Report*/
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Order Qty. Analysis Simple', 		'icwoocommerce_textdomains'),	__( 'Order Qty. Analysis Simple',	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_product_analysis',	array( $this, 'add_page' ));
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Order Qty. Analysis Variable', 	'icwoocommerce_textdomains'),	__( 'Order Qty. Analysis Variable',	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_variable_product_analysis',	array( $this, 'add_page' ));
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Product Group Sales Analysis', 	'icwoocommerce_textdomains'),	__( 'Product Group Sales Analysis',	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_group_variable_product_analysis',	array( $this, 'add_page' ));
				/*END*/
				
				do_action('ic_commerce_ultimate_report_admin_menu', $this->constants);
				
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Reports page Permission', 	'icwoocommerce_textdomains'),	__( 'Reports page Permission',	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_permission',	array( $this, 'add_page' ));
				
				add_submenu_page($this->constants['plugin_key'].'_page',__( $this->constants['plugin_name'].' Settings', 	'icwoocommerce_textdomains'),	__( 'Settings', 	'icwoocommerce_textdomains'),$this->constants['plugin_role'],$this->constants['plugin_key'].'_options_page',		array( $this, 'add_page' ));
				
			}
			
			/**
			* admin_bar_menu
			*/
			function admin_bar_menu(){
				global $wp_admin_bar;
				
				if ( ! current_user_can( $this->constants['plugin_role'] ) ) return;
				

				$wp_admin_bar->add_menu(
					array(	'id' => $this->constants['plugin_key'],
							'title' => __($this->constants['plugin_menu_name'], 'icwoocommerce_textdomains'),
							'href' => admin_url('admin.php?page='.$this->constants['plugin_key'].'_page')
					)
				);
				
				$wp_admin_bar->add_menu(
					array(	'parent' => $this->constants['plugin_key'],
							'id' => $this->constants['plugin_key'].'_page',
							'title' => __('Dashboard', 'icwoocommerce_textdomains'),
							'href' => admin_url('admin.php?page='.$this->constants['plugin_key'].'_page')
					)
				);
				
				$wp_admin_bar->add_menu(
					array(	'parent' => $this->constants['plugin_key'],
							'id' => $this->constants['plugin_key'].'_details_page',
							'title' => __('Details', 'icwoocommerce_textdomains'),
							'href' => admin_url('admin.php?page='.$this->constants['plugin_key'].'_details_page')
					)
				);
				
				$wp_admin_bar->add_menu(
					array(	'parent' => $this->constants['plugin_key'],
							'id' => $this->constants['plugin_key'].'_options_page',
							'title' => __('Settings', 'icwoocommerce_textdomains'),
							'href' => admin_url('admin.php?page='.$this->constants['plugin_key'].'_options_page')
					)
				);
			}
			
			/**
			* admin_bar_menu
			* This Function is used when add new page in plugin.
			*/
			function add_page(){
				global $setting_intence, $activate_golden_intence;
				$current_page	= $this->get_request('page',NULL,false);
				$c				= $this->constants;
				$title			= NULL;
				$intence		= NULL;
				
				if ( ! current_user_can($this->constants['plugin_role']) ) return;
				//echo 	$current_page;
				//die;
				
				switch($current_page){
					case $this->constants['plugin_key'].'_page':	
						$title = __('Ultimate WooCommerce Report Dashboard','icwoocommerce_textdomains');
						include_once($this->constants['plugin_dir'].'/includes/ic_commerce_ultimate_report_dashboard.php');
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Dashboard($c);
						break;
					case $this->constants['plugin_key'].'_details_page':
						$title = NULL;
						include_once('ic_commerce_ultimate_report_custom_report.php');
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Detail_report($c);
						break;
					case $this->constants['plugin_key'].'_report_page':
						$title = __("All Page",'icwoocommerce_textdomains');
						$title = NULL;
						include_once('ic_commerce_ultimate_report_all_report.php');
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_All_Report($c);
						break;
					case $this->constants['plugin_key'].'_cross_tab_page':
						$title = __("Crosstab",'icwoocommerce_textdomains');
						$title = NULL;
						include_once('ic_commerce_ultimate_report_cross_tab.php');
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Cross_Tab($c);
						break;
					case $this->constants['plugin_key'].'_variation_page':
						$title = __("Variation",'icwoocommerce_textdomains');
						$title = NULL;
						include_once('ic_commerce_ultimate_report_variation.php');
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Variation($c);
						break;							
					/*case $this->constants['plugin_key'].'_google_analytics_page':
						$title = __("Google Analytics",'icwoocommerce_textdomains');
						$title = NULL;
						include_once('ic_commerce_ultimate_report_google_analytics.php');
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Google_Analytics($c);
						break;*/
						
					case $this->constants['plugin_key'].'_stock_list_page':
						$title = __('Stock List','icwoocommerce_textdomains');
						include_once('ic_commerce_ultimate_report_stock_list.php' );
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Stock_List_report($c);
						break;	
					case $this->constants['plugin_key'].'_variation_stock_page':
						$title = __('Variation Stock List','icwoocommerce_textdomains');
						include_once('ic_commerce_ultimate_report_variation_stock_list.php' );
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Variation_Stock_List_report($c);
						break;
					case $this->constants['plugin_key'].'_options_page':
						$title = __('Settings','icwoocommerce_textdomains');
						$intence = $setting_intence;
						break;
					
					case $this->constants['plugin_key'].'_projected_actual_sales_page':
						$title = __("Projectd Vs Actual Sales",'icwoocommerce_textdomains');
						$title = NULL;
						include_once('ic_commerce_ultimate_report_projected_actual_sales.php');
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Projected_Actual_Sales($c);
						break;
					case $this->constants['plugin_key'].'_tax_report_page':
						$title = __('Tax Reports','icwoocommerce_textdomains');
						include_once('ic_commerce_ultimate_report_tax_report.php' );				
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Tax_report($c);
						break;
					case $this->constants['plugin_key'].'_daily_sales_report':
						$title = __('Daily Sales Report','icwoocommerce_textdomains');
						include_once('ic_commerce_ultimate_report_daily_sales_report.php' );				
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Daily_Sales_Report($c);
						break;
					case $this->constants['plugin_key'].'_product_wise_new_customer':
						$title = __('Product wise New Customer','icwoocommerce_textdomains');
						include_once('ic_commerce_ultimate_report_product_wise_new_customer.php' );				
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Product_Wise_New_Customer($c);
						break;
					case $this->constants['plugin_key'].'_customer_wise_new_product':
						$title = __('Customer wise New Product','icwoocommerce_textdomains');
						include_once('ic_commerce_ultimate_report_customer_wise_new_product.php' );				
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Customer_Wise_New_Product($c);
						break;
					case $this->constants['plugin_key'].'_new_repeat_customer':
						$title = __('New / Repeat Customer','icwoocommerce_textdomains');
						include_once('ic_commerce_ultimate_report_new_repeat_customer.php' );				
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_New_Repeat_Customer($c);
						break;
					case $this->constants['plugin_key'].'_permission':
						$title = __('Reports page Permission','icwoocommerce_textdomains');
						include_once('ic_commerce_ultimate_report_permission.php' );				
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Permission_Settings($c);
						break;						
					case $this->constants['plugin_key'].'_variable_product_analysis':
						$title = __('Order Qty. Analysis Variable','icwoocommerce_textdomains');
						include_once('ic_commerce_ultimate_report_variable_product_analysis.php' );				
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Variable_Product_Analysis($c);
						break;
					case $this->constants['plugin_key'].'_product_analysis':
						$title = __('Order Qty. Analysis Simple','icwoocommerce_textdomains');
						include_once('ic_commerce_ultimate_report_product_analysis.php' );				
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Product_Analysis($c);
						break;
					case $this->constants['plugin_key'].'_group_variable_product_analysis':
						$title = __('Product Group Sales Analysis','icwoocommerce_textdomains');
						include_once('ic_commerce_ultimate_report_group_variable_product_analysis.php' );				
						$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Group_Variable_Product_Analysis($c);
						break;		
					default:
						//include_once('ic_commerce_ultimate_report_dashboard.php');
						//$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Dashboard($c);
						break;
					break;			
				}
				//add_action('admin_footer',  array( &$this, 'admin_footer'),9);
				//$this->print_array($this->constants);
				?>
                	<div class="wrap <?php echo $this->constants['plugin_key']?>_wrap iccommercepluginwrap">
                    	<div class="icon32" id="icon-options-general"><br /></div>
                    	<?php  if($title):?>
                            <h2><?php _e($title,'icwoocommerce_textdomains');?></h2>
                        <?php endif; ?>
						<?php if($intence) $intence->init(); else echo "Class not found."?>			
                    </div> 
                <?php      
				//add_action( 'admin_footer', array( $this, 'admin_footer_css'),100); 
			}
			    
			/**  
			* setting_page
			* This Function is used for plugin Setting Page.
			*/
			function setting_page(){
				global $setting_intence;
				$current_page	= $this->get_request('page',NULL,false);
				$option_page	= $this->get_request('option_page',NULL,false);
				
				if($current_page == $this->constants['plugin_key'].'_options_page' || $option_page == $this->constants['plugin_key']){				
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_plugin_settings.php');	
					$setting_intence = new IC_Commerce_Ultimate_Woocommerce_Report_Settings($c);				
				}
				return $setting_intence;
			}
			
			/**
			* export_csv
			* This Function is used for plugin data Export.
			*/
			function export_csv(){
				
				
				//exit;
				
				if(isset($_REQUEST['export_file_format']) and ($_REQUEST['export_file_format'] == "csv" || $_REQUEST['export_file_format'] == "xls" )){
					$time_limit = apply_filters("ic_commerce_maximum_execution_time",300,$_REQUEST['export_file_format']);
					set_time_limit($time_limit);//set_time_limit — Limits the maximum execution time
					$out2 = ob_get_contents();
					if(!empty($out2))ob_end_clean();
				}else{
					return '';
				}
								
				do_action('ic_commerce_ultimate_report_export_csv_xls',$this->constants, $_REQUEST['export_file_format']);
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_details_page_export_csv'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_custom_report.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Detail_report($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_csv();
					exit;
				}
				if(isset($_REQUEST[$this->constants['plugin_key'].'_customer_page_export_csv'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_customer_report.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Golden_Customer_report($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_csv();
					exit;
				}	
				if(isset($_REQUEST[$this->constants['plugin_key'].'_customer_page_export_xls'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_customer_report.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Golden_Customer_report($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_csv('xls');
					exit;
				}				
				
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_report_page_export_csv'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_all_report.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_All_Report($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_csv('csv');
					exit;
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_report_page_export_xls'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_all_report.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_All_Report($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_csv('xls');
					exit;
				}		
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_cross_tab_page_export_csv'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_cross_tab.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Cross_Tab($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_csv('csv');
					exit;
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_stock_list_page_export_csv'])
				|| isset($_REQUEST[$this->constants['plugin_key'].'_email_alert_simple_products_export_csv'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_stock_list.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Stock_List_report($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_csv();
					exit;
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_variation_stock_page_export_csv'])
				|| isset($_REQUEST[$this->constants['plugin_key'].'_email_alert_variation_products_export_csv'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_variation_stock_list.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Variation_Stock_List_report($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_csv('csv');
					exit;
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_cross_tab_page_export_xls'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_cross_tab.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Cross_Tab($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_csv('xls');
					exit;
				}
				
				/*if(isset($_REQUEST[$this->constants['plugin_key'].'_google_analytics_page_export_csv'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_google_analytics.php');
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Google_Analytics($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_csv('csv');
					exit;s
				}*/
				
				/*if(isset($_REQUEST[$this->constants['plugin_key'].'_google_analytics_page_export_xls'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_google_analytics.php');
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Google_Analytics($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_csv('xls');
					exit;
				}*/
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_variation_page_export_csv'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_variation.php');
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Variation($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_csv('csv');
					exit;
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_variation_page_export_xls'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_variation.php');
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Variation($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_csv('xls');
					exit;
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_tax_report_page_export_csv'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_tax_report.php' );
					$IC_Commerce_Tax_Pro_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Tax_report($c);
					$IC_Commerce_Tax_Pro_Detail_report->ic_commerce_custom_report_page_export_csv('csv');
					exit;
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_tax_report_page_export_xls'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_tax_report.php' );
					$IC_Commerce_Tax_Pro_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Tax_report($c);
					$IC_Commerce_Tax_Pro_Detail_report->ic_commerce_custom_report_page_export_csv('xls');
					exit;
				}
				
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_daily_sales_report_export_csv'])){
					$c				= $this->constants;
					require_once('ic_commerce_ultimate_report_daily_sales_report.php');
					$obj = new IC_Commerce_Ultimate_Woocommerce_Report_Daily_Sales_Report($c);
					$obj->export_daily_sales();
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_product_wise_new_customer_export_csv'])){
					$c				= $this->constants;
					require_once('ic_commerce_ultimate_report_product_wise_new_customer.php');
					$obj = new IC_Commerce_Ultimate_Woocommerce_Report_Product_Wise_New_Customer($c);
					$obj->export_customer();
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_customer_wise_new_product_export_csv'])){
					$c				= $this->constants;
					require_once('ic_commerce_ultimate_report_customer_wise_new_product.php');
					$obj = new IC_Commerce_Ultimate_Woocommerce_Report_Customer_Wise_New_Product($c);
					$obj->export_customer_product();
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_new_repeat_customer_export_csv'])){
					$c				= $this->constants;
					require_once('ic_commerce_ultimate_report_new_repeat_customer.php');
					$obj = new IC_Commerce_Ultimate_Woocommerce_Report_New_Repeat_Customer($c);
					$obj->export_customer();
				}
				
				
			}
			
			/**
			* export_pdf
			* This Function is used for plugin data Export to PDF.
			*/
			function export_pdf(){
				
				
				//exit;
				
				if(isset($_REQUEST['export_file_format']) and $_REQUEST['export_file_format'] == "pdf"){
					$time_limit = apply_filters("ic_commerce_maximum_execution_time",300,$_REQUEST['export_file_format']);
					set_time_limit($time_limit);//set_time_limit — Limits the maximum execution time
					$out2 = ob_get_contents();
					if(!empty($out2))ob_end_clean();
				}else{
					return '';
				}
				
				do_action('ic_commerce_ultimate_report_export_pdf',$this->constants, $_REQUEST['export_file_format']);
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_details_page_export_pdf'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_custom_report.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Detail_report($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_pdf();
					exit;
				}
				if(isset($_REQUEST[$this->constants['plugin_key'].'_customer_page_export_pdf'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_customer_report.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Golden_Customer_report($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_pdf();
					exit;
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_stock_list_page_export_pdf'])
				|| isset($_REQUEST[$this->constants['plugin_key'].'_email_alert_simple_products_export_pdf'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_stock_list.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Stock_List_report($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_pdf();
					exit;
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_variation_stock_page_export_pdf'])				
				|| isset($_REQUEST[$this->constants['plugin_key'].'_email_alert_variation_products_export_pdf'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_variation_stock_list.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Variation_Stock_List_report($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_pdf();
					exit;
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_report_page_export_pdf'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_all_report.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_All_Report($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_pdf();
					exit;
				}
				
				
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_cross_tab_page_export_pdf'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_cross_tab.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Cross_Tab($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_pdf();
					exit;
				}
				
				/*if(isset($_REQUEST[$this->constants['plugin_key'].'_google_analytics_page_export_pdf'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_google_analytics.php');
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Google_Analytics($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_pdf();
					exit;
				}*/
				
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_variation_page_export_pdf'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_variation.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Variation($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_report_page_export_pdf();
					exit;
				}
				
				if(isset($_REQUEST[$this->constants['plugin_key'].'_tax_report_page_export_pdf'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_tax_report.php' );
					$IC_Commerce_Tax_Pro_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Tax_report($c);
					$IC_Commerce_Tax_Pro_Detail_report->ic_commerce_custom_report_page_export_pdf();
					exit;
				}
				
				
			}
			
			/**
			* pdf_invoice
			* This Function is used for plugin data Export to PDF Invoice.
			*/
			function pdf_invoice(){
				
				$bulk_action = $this->get_request('bulk_action','0');					
				if($bulk_action == 'pdf_invoice_download' || $bulk_action == "pdf_invoice_print" || $bulk_action == $this->constants['plugin_key']."_pdf_invoice_download"){//Modified 20150205
					$time_limit = apply_filters("ic_commerce_maximum_execution_time",3000,'pdf_invoice');
					set_time_limit($time_limit);//set_time_limit — Limits the maximum execution time
					
					$out2 = ob_get_contents();
					if(!empty($out2))ob_end_clean();
					
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_export_invoice.php' );
					$i = new IC_Commerce_Ultimate_Woocommerce_Report_Export_Invoice($c);
					$i->invoice_action();
					exit;
					die;
				}
			}
			
			/**
			* export_print
			* This Function is used for plugin data Export Print.
			*/
			function export_print(){
				if(isset($_REQUEST[$this->constants['plugin_key'].'_details_page_export_print'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_custom_report.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Ultimate_Woocommerce_Report_Detail_report($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_admin_report_iframe_request('all_row');
					exit;
				}
				if(isset($_REQUEST[$this->constants['plugin_key'].'_customer_page_export_print'])){
					$c				= $this->constants;
					include_once('ic_commerce_ultimate_report_customer_report.php' );
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report = new IC_Commerce_Golden_Customer_report($c);
					$IC_Commerce_Ultimate_Woocommerce_Report_Detail_report->ic_commerce_custom_admin_report_iframe_request('all_row');
					exit;
				}
			}
			
			/**
			* admin_head
			*/
			function admin_head() {
				
				$ver = "";
				//$ver = date("YmdHis");
				wp_enqueue_style(  $this->constants['plugin_key'].'_admin_styles', 								$this->constants['plugin_url'].'/assets/css/admin.css',array(),$ver);
				wp_enqueue_style(  $this->constants['plugin_key'].'_font-awesome', 								$this->constants['plugin_url'].'/assets/css/font-awesome.min.css' );
				wp_enqueue_style(  $this->constants['plugin_key'].'_responsive-tabs', 							$this->constants['plugin_url'].'/assets/css/responsive-tabs.css' );	
			}
			
			/**
			* admin_footer
			*/
			function admin_footer() {
				global $wp_scripts;
				$current_page	= $this->get_request('page',NULL,false);
				$jquery_ui_core = false;
				
				if($current_page == $this->constants['plugin_key'].'_page'){
					//New Change ID 20150107					
					wp_enqueue_style(  $this->constants['plugin_key'].'_admin_map_ic_00',			$this->constants['plugin_url'].'/assets/map_lib/jquery-jvectormap.css');					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_01', 			$this->constants['plugin_url'].'/assets/map_lib/jquery-jvectormap.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_02', 			$this->constants['plugin_url'].'/assets/map_lib/lib/jquery-mousewheel.js');					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_03', 			$this->constants['plugin_url'].'/assets/map_lib/src/jvectormap.js');					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_04', 			$this->constants['plugin_url'].'/assets/map_lib/src/abstract-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_05', 			$this->constants['plugin_url'].'/assets/map_lib/src/abstract-canvas-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_06', 			$this->constants['plugin_url'].'/assets/map_lib/src/abstract-shape-element.js');					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_07', 			$this->constants['plugin_url'].'/assets/map_lib/src/svg-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_08', 			$this->constants['plugin_url'].'/assets/map_lib/src/svg-group-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_09', 			$this->constants['plugin_url'].'/assets/map_lib/src/svg-canvas-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_10', 			$this->constants['plugin_url'].'/assets/map_lib/src/svg-shape-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_11', 			$this->constants['plugin_url'].'/assets/map_lib/src/svg-path-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_12', 			$this->constants['plugin_url'].'/assets/map_lib/src/svg-circle-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_13', 			$this->constants['plugin_url'].'/assets/map_lib/src/svg-image-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_14', 			$this->constants['plugin_url'].'/assets/map_lib/src/svg-text-element.js');					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_15', 			$this->constants['plugin_url'].'/assets/map_lib/src/vml-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_16', 			$this->constants['plugin_url'].'/assets/map_lib/src/vml-group-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_17', 			$this->constants['plugin_url'].'/assets/map_lib/src/vml-canvas-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_18', 			$this->constants['plugin_url'].'/assets/map_lib/src/vml-shape-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_19', 			$this->constants['plugin_url'].'/assets/map_lib/src/vml-path-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_20', 			$this->constants['plugin_url'].'/assets/map_lib/src/vml-circle-element.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_21', 			$this->constants['plugin_url'].'/assets/map_lib/src/vml-image-element.js');					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_22', 			$this->constants['plugin_url'].'/assets/map_lib/src/map-object.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_23', 			$this->constants['plugin_url'].'/assets/map_lib/src/region.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_24', 			$this->constants['plugin_url'].'/assets/map_lib/src/marker.js');					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_25', 			$this->constants['plugin_url'].'/assets/map_lib/src/vector-canvas.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_26', 			$this->constants['plugin_url'].'/assets/map_lib/src/simple-scale.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_27', 			$this->constants['plugin_url'].'/assets/map_lib/src/ordinal-scale.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_28', 			$this->constants['plugin_url'].'/assets/map_lib/src/numeric-scale.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_29', 			$this->constants['plugin_url'].'/assets/map_lib/src/color-scale.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_30', 			$this->constants['plugin_url'].'/assets/map_lib/src/legend.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_31', 			$this->constants['plugin_url'].'/assets/map_lib/src/data-series.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_32', 			$this->constants['plugin_url'].'/assets/map_lib/src/proj.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_33', 			$this->constants['plugin_url'].'/assets/map_lib/src/map.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_34', 			$this->constants['plugin_url'].'/assets/map_lib/assets/jquery-jvectormap-world-mill-en.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_map_ic_35', 			$this->constants['plugin_url'].'/assets/js/jquery.map.scripts.js');
					
					/*Start: AM Charts*/
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_amcharts_amcharts', 	$this->constants['plugin_url'].'/assets/amcharts/amcharts.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_amcharts_serial', 	  $this->constants['plugin_url'].'/assets/amcharts/serial.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_amcharts_pe', 		  $this->constants['plugin_url'].'/assets/amcharts/pie.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_amcharts_script', 	  $this->constants['plugin_url'].'/assets/js/amcharts.scripts.js');
					
					/*End: AM Charts*/
					
					
					//New Change ID 20141119
					wp_enqueue_script('jquery-ui-datepicker');
					
					$jquery_ui_core = true;
					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_dashboard_summary', 					$this->constants['plugin_url'].'/assets/js/dashboard_summary.js', true);					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_dashboard_summary_stock_alert',		$this->constants['plugin_url'].'/assets/js/dashboard_summary_stock_alert.js', true);
					
					if(isset($this->constants['language'])){
						$language = $this->constants['language'];
						$languages = array('az','bg','de','es','fi','fo','fr','hr','hu','id','is','it','ja','lt','lv','mk','mn','mt','nl','no','pl','pt','ro','ru','rw','sk','so','th','tr','zh');
						if(in_array($language,$languages)){
							wp_enqueue_script( $this->constants['plugin_key'].'_admin_amcharts_lang',$this->constants['plugin_url'].'/assets/amcharts/lang/'.$language.'.js',array(),false,true);						
						}else{
							add_action('admin_print_footer_scripts', array($this, 'print_amchart_language'),1000);
						}
					}
				}
				
				$pages 		= array();				
				$pages[]	=	$this->constants['plugin_key'].'_details_page';
				$pages[]	=	$this->constants['plugin_key'].'_stock_list_page';
				$pages[]	=	$this->constants['plugin_key'].'_report_page';
				$pages[]	=	$this->constants['plugin_key'].'_variation_stock_page';
				
				$pages 	= apply_filters('ic_commerce_ultimate_autocomplete_script', $pages, $this->constants['plugin_key']);
				
				if(in_array($current_page,$pages)){
				
					wp_enqueue_script( 'jquery-ui-autocomplete' );
					
					wp_enqueue_script( $this->constants['plugin_key'].'_autocomplete_multiselect', 			$this->constants['plugin_url'].'/assets/js/autocomplete.multiselect.js');
					wp_enqueue_script( $this->constants['plugin_key'].'_autocomplete_multiselect_script', 			$this->constants['plugin_url'].'/assets/js/autocomplete.multiselect_scripts.js');
					wp_enqueue_style(  $this->constants['plugin_key'].'_autocomplete_multiselect_style',			$this->constants['plugin_url'].'/assets/css/autocomplete.multiselect.css');	
					
					$jquery_ui_core = true;
				}
				
				
				wp_enqueue_script( $this->constants['plugin_key'].'_responsive-tabs_script', 					$this->constants['plugin_url'].'/assets/js/responsive-tabs.js');
				
				$pages 		= array();				
				$pages[]	=	$this->constants['plugin_key'].'_details_page';
				$pages[]	=	$this->constants['plugin_key'].'_stock_list_page';
				$pages[]	=	$this->constants['plugin_key'].'_variation_stock_page';
				$pages[]	=	$this->constants['plugin_key'].'_report_page';
				$pages[]	=	$this->constants['plugin_key'].'_cross_tab_page';
				$pages[]	=	$this->constants['plugin_key'].'_options_page';
				//$pages[]	=	$this->constants['plugin_key'].'_google_analytics_page';
				$pages[]	=	$this->constants['plugin_key'].'_variation_page';
				$pages[]	=	$this->constants['plugin_key'].'_customer_page';
				$pages[]	=	$this->constants['plugin_key'].'_projected_actual_sales_page';
				$pages[]	=	$this->constants['plugin_key'].'_tax_report_page';
				$pages[]	=	$this->constants['plugin_key'].'_product_analysis';
				$pages[]	=	$this->constants['plugin_key'].'_variable_product_analysis';
				$pages[]	=	$this->constants['plugin_key'].'_daily_sales_report';
				$pages[]	=	$this->constants['plugin_key'].'_product_wise_new_customer';
				$pages[]	=	$this->constants['plugin_key'].'_customer_wise_new_product';
				$pages[]	=	$this->constants['plugin_key'].'_new_repeat_customer';
				$pages[]	=	$this->constants['plugin_key'].'_group_variable_product_analysis';
				
				$pages 	= apply_filters('ic_commerce_tax_pro_pages_script', $pages, $this->constants['plugin_key']);
				$pages 	= apply_filters('ic_commerce_ultimate_report_script', $pages, $this->constants['plugin_key']);
				
				
				if(in_array($current_page,$pages)){
					
					wp_enqueue_script('jquery-ui-datepicker');
					
					wp_enqueue_script( $this->constants['plugin_key'].'_jquery_collapsible', 			$this->constants['plugin_url'].'/assets/js/jquery.collapsible.js', true);
					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_details_page', 			$this->constants['plugin_url'].'/assets/js/details_page.js', true);
					
					$jquery_ui_core = true;
				}				
				
				//New Change ID 20141119
				if($current_page == $this->constants['plugin_key'].'_options_page'){					
					wp_enqueue_script( $this->constants['plugin_key'].'_admin_setting_page', 				$this->constants['plugin_url'].'/assets/js/setting_page.js', true);
				}

				if($current_page == $this->constants['plugin_key'].'_product_analysis'){
					wp_enqueue_script($this->constants['plugin_key'].'_product_analysis',					$this->constants['plugin_url'].'/assets/js/ic_single_product.js',true);					
				}
				
				if($current_page == $this->constants['plugin_key'].'_variable_product_analysis'){
					wp_enqueue_script($this->constants['plugin_key'].'_variable_product_analysis',			$this->constants['plugin_url'].'/assets/js/ic_variable_product.js',true);					
				}
				
				if($current_page == $this->constants['plugin_key'].'_group_simple_product_analysis'){
					wp_enqueue_script($this->constants['plugin_key'].'_group_simple_product_analysis',		$this->constants['plugin_url'].'/assets/js/ic_group_simple_product_analysis.js',true);					
				}
				
				if($current_page == $this->constants['plugin_key'].'_group_variable_product_analysis'){					
					wp_enqueue_script($this->constants['plugin_key'].'_group_variable_product_analysis',	$this->constants['plugin_url'].'/assets/js/ic_group_variable_product_analysis.js',true);					
				}
				
				
				if($jquery_ui_core){
					$ui = $wp_scripts->query('jquery-ui-core');
					$protocol = is_ssl() ? 'https' : 'http';
					$url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
					wp_register_style('jquery-ui-smoothness', $url, false, null);
					wp_enqueue_style( 'jquery-ui-smoothness');		
				}
				
				
				
				
			}
			
			
			/**
			* check_parent_plugin
			*/
			public function check_parent_plugin(){
				if(!isset($this->constants['plugin_parent'])) return '';
				$message 				= "";
				$msg 					= false;
				$this->plugin_parent 	= $this->constants['plugin_parent'];
				$action = "";
				
				
				$this->constants['plugin_parent_active'] 		=  false;
				$this->constants['plugin_parent_installed'] 	=  false;
				
				$active_plugins = (array) get_option( 'active_plugins', array() );
				
				$active_plugins  = apply_filters( 'active_plugins', $active_plugins );
				
				if ( is_multisite() ) {
					$active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
				}
				
				if(in_array( $this->plugin_parent['plugin_slug'],$active_plugins)){
										
					$this->constants['plugin_parent_active'] 		=  true;
					$this->constants['plugin_parent_installed'] 	=  true;
					
					//New Change ID 20140918
					$this->constants['parent_plugin_version']	= get_option('woocommerce_version',0);
					$this->constants['parent_plugin_db_version']= get_option('woocommerce_db_version',0);
					
					/*if(!defined('WOO_VERSION'))
					if(defined('WC_VERSION')) define('WOO_VERSION', WC_VERSION);else define('WOO_VERSION', '');
					
					if ( version_compare( $this->constants['parent_plugin_db_version'], '2.2.0', '>=' ) || WOO_VERSION == '2.2-bleeding' ) {
						if ( version_compare( $this->constants['parent_plugin_db_version'], '2.2.0', '<' ) || WOO_VERSION == '2.2-bleeding' ) {
							$this->constants['post_order_status_found']	= 0;
						}else{
							$this->constants['post_order_status_found']	= 1;
						}
					}else{
						$this->constants['post_order_status_found']	= 0;
					}*/
					
					/*Added 2017-08-04*/
					$this->constants['post_order_status_found']	= apply_filters('ic_commerce_latest_woocommerce_version',1);
		
					return $message;
				}else{
					$this->constants['plugin_parent_active'] =  false;
					if(is_dir(WP_PLUGIN_DIR.'/'.$this->plugin_parent['plugin_folder'] ) ) {
						$message = $this->constants['plugin_parent_installed'] =  true;
					}else{
						$message = $this->constants['plugin_parent_installed'] =  false;
					}
					return  $message;
				}
			}
			
			/**
			* admin_notices
			*/
			public function admin_notices(){
				$message 				= NULL;				
				if(!$this->constants['plugin_parent_active']){
					if($this->constants['plugin_parent_installed']){
						$action = esc_url(wp_nonce_url(admin_url('plugins.php?action=activate&plugin='.$this->plugin_parent['plugin_slug'].'&plugin_status=active&paged=1'), 'activate-plugin_'.$this->plugin_parent['plugin_slug']));						
						$msg = '<span>' . sprintf( __($this->constants['plugin_name'].' depends on <a href="%s">'.$this->plugin_parent['plugin_name'].'</a> to work! so please <a href="%s">activate</a> it.' , 'icwoocommerce_textdomains'), $action, $action ) . '</span>';
					}else{
						$action = admin_url( 'plugin-install.php?tab=plugin-information&plugin='.$this->plugin_parent['plugin_folder'].'&TB_iframe=true&width=640&height=800');
						$msg = '<span>' . sprintf( __($this->constants['plugin_name'].' depends on <a href="%s" target="_blank" class="thickbox onclick" title="'.$this->plugin_parent['plugin_name'].'">'.$this->plugin_parent['plugin_name'].'</a> to work!' , 'icwoocommerce_textdomains'),$action) . '</span>';					
					}					
					$message .= '<div class="error">';
					$message .= '<p>'.$msg.'</p>';
					$message .= '</div>';
				}
				echo $message;
			}			
			
			/**
			* wp_localize_script
			* @param string $hook 
			*/
			function wp_localize_script($hook) {
				$current_page				= $this->get_request('page');
				$localize_script_data		= array(
													'ajaxurl' 			=> admin_url( 'admin-ajax.php' )
													,'ic_ajax_action' 	=> $this->constants['plugin_key'].'_wp_ajax_action'
													,'first_order_date' => $this->constants['first_order_date']
													,'current_date' 	=> date("Y-m-d")
													,'total_shop_day' 	=> $this->constants['total_shop_day']
													,'defaultOpen' 		=> 'section1'
													,'color_code' 		=> $this->constants['color_code']
													,'admin_page' 		=> $current_page
												);
				
				$localize_script_data['please_wait'] 	= __("Please Wait!",'icwoocommerce_textdomains');
				
				if($this->constants['plugin_key'].'_details_page' == $current_page){
					$localize_script_data['customize_column'] 	= __("Customize Column",'icwoocommerce_textdomains');
					$localize_script_data['hide_column_view'] 	= __("Hide column view",'icwoocommerce_textdomains');
				}
				
				if($this->constants['plugin_key'].'_page' == $current_page){
					
					$locale = get_locale();
					$locales = explode("_",$locale);
					$language	= isset($locales[0]) ? $locales[0] : 'en';
					
					$this->constants['language'] = $language;
					
					if(function_exists('get_woocommerce_currency_symbol')){
						$currency_symbol	=	get_woocommerce_currency_symbol();
					}else{
						$currency_symbol	=	"$";
					}
					$localize_script_data['currency_symbol'] 	= $currency_symbol;
					$localize_script_data['num_decimals'] 		= get_option( 'woocommerce_price_num_decimals'	,	0		);
					$localize_script_data['currency_pos'] 		= get_option( 'woocommerce_currency_pos'		,	'left'	);
					$localize_script_data['decimal_sep'] 		= get_option( 'woocommerce_price_decimal_sep'	,	'.'		);
					$localize_script_data['thousand_sep'] 		= get_option( 'woocommerce_price_thousand_sep'	,	','		);
					
					//New Graph Settings 20150407
					$localize_script_data['tick_angle'] 		= $this->get_setting('tick_angle',			$this->constants['plugin_options'],0);
					$localize_script_data['tick_font_size'] 	= $this->get_setting('tick_font_size',		$this->constants['plugin_options'],9);
					$localize_script_data['tick_char_length'] 	= $this->get_setting('tick_char_length',	$this->constants['plugin_options'],15);
					$localize_script_data['tick_char_suffix'] 	= $this->get_setting('tick_char_suffix',	$this->constants['plugin_options'],"...");
					$localize_script_data['graph_height'] 		= $this->get_setting('graph_height',		$this->constants['plugin_options'],300);
					$localize_script_data['language'] 			= $language;
					
					$localize_script_data['country'] 		= __("Country",'icwoocommerce_textdomains');
					$localize_script_data['order_total'] 	= __("Order Total",'icwoocommerce_textdomains');
					$localize_script_data['order_count'] 	= __("Order Count",'icwoocommerce_textdomains');
					
					
				}
				
				wp_enqueue_script( $this->constants['plugin_key'].'_ajax-script', $this->constants['plugin_url'].'/assets/js/scripts.js', true);
				
				wp_localize_script($this->constants['plugin_key'].'_ajax-script', 'ic_ajax_object', $localize_script_data); // setting ajaxurl
				
				if($this->constants['plugin_key'].'_options_page' == $current_page){
					wp_enqueue_media();
					wp_enqueue_script('custom-background');
					//wp_enqueue_style('wp-color-picker');
				}
				
				if($current_page == "icwoocommerceultimatereport_permission"){
					//echo $plugins_url = plugins_url('/assets/');
					$plugin_key 	= $this->constants['plugin_key'];					
					$parent_menu  	= $plugin_key.'_page';
					
					
					//wp_enqueue_script('scripts_ajax_script', $this->constants['plugin_url'].'/assets/js/permission-script.js');
					
					wp_enqueue_script( $this->constants['plugin_key'].'_scripts_ajax_script', $this->constants['plugin_url'].'/assets/js/permission-script.js', true);
					
					$localize_script_data		= array(
						'ajaxurl' 				=> admin_url( 'admin-ajax.php' )
						,'ic_ajax_action' 		=> 'ic_report_permission_ajax'
						,'admin_page' 			=> $current_page
						,'plugin_key' 			=> $plugin_key
						,'parent_menu' 			=> $parent_menu
					);
					
					wp_localize_script('scripts_ajax_script', 'ic_ajax_object', $localize_script_data);
					
					//wp_enqueue_script('', $plugins_url.'js/jquery.blockUI.js');		
					wp_enqueue_script( $this->constants['plugin_key'].'_jquery_blockUI', $this->constants['plugin_url'].'/assets/js/jquery.blockUI.js', true);		
					wp_register_style('ic_style',  		$this->constants['plugin_url'].'/assets/css/ic-admin.css');
					wp_enqueue_style('ic_style');
				}
				
			}
			
			function print_amchart_language(){
				echo $this->get_amchart_language();
			}
			
			function get_amchart_language(){
				
				if(!isset($this->constants['language'])){					
					$locale = get_locale();
					$locales = explode("_",$locale);
					$language	= isset($locales[0]) ? $locales[0] : 'en';
					$this->constants['language'] = $language;
				}				
				$language	= $this->constants['language'];
			
				$months = array();
				$months[1] = __("January",'icwoocommerce_textdomains');
				$months[2] = __("February",'icwoocommerce_textdomains');
				$months[3] = __("March",'icwoocommerce_textdomains');
				$months[4] = __("April",'icwoocommerce_textdomains');
				$months[5] = __("May",'icwoocommerce_textdomains');
				$months[6] = __("June",'icwoocommerce_textdomains');
				$months[7] = __("July",'icwoocommerce_textdomains');
				$months[8] = __("August",'icwoocommerce_textdomains');
				$months[9] = __("September",'icwoocommerce_textdomains');
				$months[10] = __("October",'icwoocommerce_textdomains');
				$months[11] = __("November",'icwoocommerce_textdomains');
				$months[12] = __("December",'icwoocommerce_textdomains');
				
				$shortMonthNames = array();
				$shortMonthNames[1] = __("Jan.",'icwoocommerce_textdomains');
				$shortMonthNames[2] = __("Feb.",'icwoocommerce_textdomains');
				$shortMonthNames[3] = __("March",'icwoocommerce_textdomains');
				$shortMonthNames[4] = __("April",'icwoocommerce_textdomains');
				$shortMonthNames[5] = __("May.",'icwoocommerce_textdomains');
				$shortMonthNames[6] = __("Jun.",'icwoocommerce_textdomains');
				$shortMonthNames[7] = __("Jul.",'icwoocommerce_textdomains');
				$shortMonthNames[8] = __("Aug.",'icwoocommerce_textdomains');
				$shortMonthNames[9] = __("Sep.",'icwoocommerce_textdomains');
				$shortMonthNames[10] = __("Oct.",'icwoocommerce_textdomains');
				$shortMonthNames[11] = __("Nov.",'icwoocommerce_textdomains');
				$shortMonthNames[12] = __("Dec.",'icwoocommerce_textdomains');
				
				$dayNames = array();
				$dayNames[] = __("Monday",'icwoocommerce_textdomains');
				$dayNames[] = __("Tuesday",'icwoocommerce_textdomains');
				$dayNames[] = __("Wednesday",'icwoocommerce_textdomains');
				$dayNames[] = __("Thursday",'icwoocommerce_textdomains');
				$dayNames[] = __("Friday",'icwoocommerce_textdomains');
				$dayNames[] = __("Saturday",'icwoocommerce_textdomains');
				$dayNames[] = __("Sunday",'icwoocommerce_textdomains');
				
				$shortDayNames = array();
				$shortDayNames[] = __("Mond.",'icwoocommerce_textdomains');
				$shortDayNames[] = __("Tue.",'icwoocommerce_textdomains');
				$shortDayNames[] = __("Wed.",'icwoocommerce_textdomains');
				$shortDayNames[] = __("Thu.",'icwoocommerce_textdomains');
				$shortDayNames[] = __("Fri.",'icwoocommerce_textdomains');
				$shortDayNames[] = __("Sat.",'icwoocommerce_textdomains');
				$shortDayNames[] = __("Sun.",'icwoocommerce_textdomains');
				
				
				
				$output = "<script type=\"text/javascript\">";
				$output .= "AmCharts.translations.{$language} = {";
				$output .= '"monthNames":["'.implode('","',$months).'"],';
				$output .= '"shortMonthNames":["'.implode('","',$shortMonthNames).'"],';
				$output .= '"dayNames":["'.implode('","',$dayNames).'"],';
				$output .= '"shortDayNames":["'.implode('","',$shortDayNames).'"],';
				$output .= '"zoomOutText":"'. __("Zoom Out",'icwoocommerce_textdomains').'"';
				$output .= '};</script>';
				return $output;
			}
			
			/**
			* ic_commerce_save_normal_column
			* @param string $name 
			*/
			function ic_commerce_save_normal_column($name){
				$key = $this->get_column_key($name);
				do_action('ic_commerce_ultimate_report_save_column',$_POST, $key);
				unset($_POST['do_action_type']);
				unset($_POST['action']);
				unset($_POST['ic_admin_page']);
				update_option($key,$_POST);
				die();
				exit;
			}
			
			/**
			* get_column_key
			* @param string $name 
			*/
			function get_column_key($name){
				$page			= $this->get_request('ic_admin_page','report');				
				return $key 	= $page.'_'.$name;
			}
			
			/**
			* projected_sales_year
			*/
			function projected_sales_year(){
				$projected_sales_year			= $this->get_request('projected_sales_year','2000');				
				$projected_sales_year_option 	= $this->constants['plugin_key'].'_projected_amount_'.$projected_sales_year;
				
				$projected_amounts = get_option($projected_sales_year_option,array());
				$return['success'] = 'false';
				$return['projected_amounts'] = array();
				$return['projected_sales_year'] = $projected_sales_year;
				if($projected_amounts){
					$return['success'] 				= 'true';					
					$return['projected_amounts'] 	= $projected_amounts;
				}
				
				echo json_encode($return);
				die;
			}
			
			
			function ic_commerce_autocomplete_product_types($product_types = array(),$admin_page = '',$report_name = ''){
				
				$report_names	= apply_filters('ic_commerce_autocomplete_report_name',array('valuation_page'),$admin_page,$report_name);
				
				if(in_array($report_name,$report_names)){
					$product_types[] = 'product_variation';
				}
				return $product_types;
			}
			
			function autocomplete(){
				global $wpdb;
				$search_type	= $this->get_request('search_type',NULL,false);
				if($search_type == 'products'){
					
					$data = array();
					
					$product_status	= $this->get_request('product_status','publish');
					
					$term			= $this->get_request('term','');
					
					$sql = "SELECT posts.ID AS id, posts.post_title AS label";
				
					$sql .= " FROM `{$wpdb->posts}` AS posts";
					
					$sql .= " WHERE 1*1";
					
					
					$report_name	= $this->get_request('report_name');
					$admin_page		= $this->get_request('page');
					
					$product_types	= apply_filters('ic_commerce_autocomplete_product_types',array('product'),$admin_page,$report_name);
					
					if(count($product_types) > 0){
						$_product_types = implode("', '",$product_types);
						$sql .= " AND posts.post_type IN ('$_product_types')";
					}
					
					$sql .= " AND posts.post_status IN ('publish')";
					
					if($product_status){
						$sql .= " AND posts.post_status IN ('{$product_status}')";
					}
					
					if($term != ""){
						$sql .= " AND posts.post_title LIKE '%$term%'";
					}
					
					$sql .= " ORDER BY posts.post_title ASC";
					
					$products = $wpdb->get_results($sql);
					
					echo json_encode($products);
					
					die;
				}
			}
			
			function woocommerce_debug_tools($tools = array()){
				return $tools;
				$tools['reset_ult_report_pages_permission'] = array(
					'name' => __( 'Reset Reports page Permission', 'icwoocommerce_textdomains' ),
					'button' => __( 'Reset Permission', 'icwoocommerce_textdomains' ),
					'desc'   => sprintf(
						'<strong class="red">%1$s</strong> %2$s',
						__( 'Note:', 'woocommerce' ),
						__( 'This tool will reset the Ultimate Report Pages Permission. After the Administrator role login can see the reports only.', 'icwoocommerce_textdomains' )
					),
				
				);
				return $tools;
			}
			
			/**
			* wp_ajax_action
			*/
			function wp_ajax_action() {
				$action	= $this->get_request('action',NULL,false);
				//$this->print_array($_REQUEST);
				if($action ==  $this->constants['plugin_key'].'_wp_ajax_action'){
				
					if(isset($_REQUEST['do_action_type'])){
						$time_limit = apply_filters("ic_commerce_maximum_execution_time",300,$_REQUEST['do_action_type']);
						set_time_limit($time_limit);//set_time_limit — Limits the maximum execution time
					}
										
					$do_action_type	= $this->get_request('do_action_type',NULL,false);
					
					//
					
					if($do_action_type){
						$this->define_constant();
						$c	= $this->constants;
						
						do_action('ic_commerce_ultimate_report_ajax_action',$this->constants, $do_action_type);
						
						
						if($do_action_type == "ic_autocomplete"){
							$this->autocomplete();
							die;
						}
						
						//$this->print_array($_REQUEST);
						
						if($do_action_type == "email_report_actions_order_status_mail"){
							require_once('ic_commerce_ultimate_report_schedule_mailing_sales_status.php');
							$ic_commerce 									= new IC_Commerce_Ultimate_Woocommerce_Report_Schedule_Mailing_Sales_Status( $this->constants['plugin_file'], $c);
							$ic_commerce->ajax_schedule_event();
							die;
						}
						
						if($do_action_type == "email_report_actions_order_dashboard_email"){
							require_once('ic_commerce_ultimate_report_schedule_mailing_dashboard_report.php');
							$ic_commerce 									= new IC_Commerce_Ultimate_Woocommerce_Report_Schedule_Mailing_Dashboard_Report( $this->constants['plugin_file'], $c);
							$ic_commerce->ajax_schedule_event();
							die;
						}
						
						
						
						if($do_action_type == "projected_actual_sales_page"){	
							include_once('ic_commerce_ultimate_report_projected_actual_sales.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Projected_Actual_Sales($c);
							$intence->ic_commerce_ajax_request('limit_row');
						}
						
						if($do_action_type == "tax_report_page" || $do_action_type == "tax_report_page_for_print"){
							include_once('ic_commerce_ultimate_report_tax_report.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Tax_report($c);
							
							if($do_action_type == "tax_report_page")
								$intence->ic_commerce_ajax_request('limit_row');
							else if($do_action_type == "tax_report_page_for_print"){
								$intence->ic_commerce_ajax_request('all_row');
							}
						}
						
						if($do_action_type == "projected_sales_year"){	
							$this->projected_sales_year();
						}
						
						if($do_action_type == "map_details"){
							
							
							
							$start_date = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : $this->constants['start_date'];
							$end_date = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : $this->constants['end_date'];
														
							$shop_order_status		= apply_filters('ic_commerce_dashboard_page_default_order_status',$this->get_set_status_ids(),$this->constants);	
							$hide_order_status 		= apply_filters('ic_commerce_dashboard_page_default_hide_order_status',$this->constants['hide_order_status'],$this->constants);
							$start_date 			= apply_filters('ic_commerce_dashboard_page_default_start_date',$start_date,$this->constants);
							$end_date 				= apply_filters('ic_commerce_dashboard_page_default_end_date',$end_date,$this->constants);
							include_once( 'ic_commerce_ultimate_report_map.php');
							$class_object = new IC_Commerce_Ultimate_Woocommerce_Report_Map($c);
							$class_object->get_country_list($shop_order_status,$hide_order_status,$start_date,$end_date);
						}
						
						if($do_action_type == "save_normal_column" || $do_action_type == "save_detail_column"){
							$this->ic_commerce_save_normal_column($do_action_type);
							die;
						}
						
						if($do_action_type == "graph"){
							include_once( 'ic_commerce_ultimate_report_ajax_graph.php');
							$IC_Commerce_Ultimate_Woocommerce_Report_Ajax_Graph = new IC_Commerce_Ultimate_Woocommerce_Report_Ajax_Graph($c);
						}
						
						if($do_action_type == "stock_page"){
							include_once('ic_commerce_ultimate_report_stock_list.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Stock_List_report($c);
							$intence->get_product_list();
							die;
						}
						
						if($do_action_type == "variation_stock_page"){
							include_once('ic_commerce_ultimate_report_variation_stock_list.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Variation_Stock_List_report($c);
							$intence->get_product_list();
							die;
						}						
						
						if($do_action_type == "report_page" || $do_action_type == "all_report_page_for_print"){
							
							include_once('ic_commerce_ultimate_report_all_report.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_All_Report($c);
							if($do_action_type == "report_page")
								$intence->ic_commerce_report_ajax_request('limit_row');
							else if($do_action_type == "all_report_page_for_print")
								$intence->ic_commerce_report_ajax_request('all_row');
						}
						
						if($do_action_type == "variation_page" || $do_action_type == "variation_page_for_print"){
							
							include_once('ic_commerce_ultimate_report_variation.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Variation($c);
							if($do_action_type == "variation_page")
								$intence->ic_commerce_report_ajax_request('limit_row');
							else if($do_action_type == "variation_page_for_print")
								$intence->ic_commerce_report_ajax_request('all_row');
						}						
						
						if($do_action_type == "cross_tab_page"
							|| $do_action_type == "cross_tab_for_print"){	
							include_once('ic_commerce_ultimate_report_cross_tab.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Cross_Tab($c);
							if($do_action_type == "cross_tab_page")
								$intence->ic_commerce_ajax_request('limit_row');
							else if($do_action_type == "cross_tab_for_print")
								$intence->ic_commerce_ajax_request('all_row');
						}
						
						/*if($do_action_type == "google_analytics" || $do_action_type == "google_analytics_for_print"){	
							include_once('ic_commerce_ultimate_report_google_analytics.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Google_Analytics($c);
							
							if($do_action_type == "google_analytics")
								$intence->ic_commerce_ajax_request('limit_row');
							else if($do_action_type == "google_analytics_for_print")
								$intence->ic_commerce_ajax_request('all_row');
						}*/
						
						if(
								$do_action_type == "save_normal_column" 
							|| $do_action_type == "save_detail_column" 
							|| $do_action_type == "product" 
							|| $do_action_type == "detail_page"
							|| $do_action_type == "customer_page"
							|| $do_action_type == "customer_page_for_print"
							|| $do_action_type == "detail_page_for_print"){
								
							
							include_once('ic_commerce_ultimate_report_custom_report.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Detail_report($c);
							
							if($do_action_type == "save_normal_column" || $do_action_type == "save_detail_column")
								$intence->ic_commerce_save_normal_column($do_action_type);
							else if($do_action_type == "product")
								$intence->product_by_category_ajax_request();
							else if($do_action_type == "detail_page")
								$intence->ic_commerce_custom_admin_report_ajax_request('limit_row');								
							else if($do_action_type == "detail_page_for_print")
								$intence->ic_commerce_custom_admin_report_ajax_request('all_row');
						}
						
						
						if($do_action_type == "check_cogs_exits"){
							include_once('ic_commerce_ultimate_report_plugin_settings.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Settings($c);
							$intence->check_cogs_exits();
							die;
						}
						
						if($do_action_type == "product_analysis"){
							include_once('ic_commerce_ultimate_report_product_analysis.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Product_Analysis($c);
							$intence->ajax();
							die;
						}
						
						if($do_action_type == "variable_product_analysis"){
							include_once('ic_commerce_ultimate_report_variable_product_analysis.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Variable_Product_Analysis($c);
							$intence->ajax();
							die;
						}
						
						if($do_action_type == "group_simple_product_analysis"){
							include_once('ic_commerce_ultimate_report_group_simple_product_analysis.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Group_Simple_Product_Analysis($c);
							$intence->ajax();
							die;
						}
						
						if($do_action_type == "group_variable_product_analysis"){
							include_once('ic_commerce_ultimate_report_group_variable_product_analysis.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Group_Variable_Product_Analysis($c);
							$intence->ajax();
							die;
						}
						
						if($do_action_type == "product_stock_alert"){							
							require_once('ic_commerce_ultimate_report_summary_stock_alert.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Summary_Stock_Alert($c, $this->constants['plugin_key']);
							$intence->ajax();
							die;
						}
						
						if($do_action_type == "daily_sales"){							
							require_once('ic_commerce_ultimate_report_daily_sales_report.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Daily_Sales_Report($c, $this->constants['plugin_key']);
							$intence->ajax();
							die;
						}
						
						if($do_action_type == "product_new_repeat_customer"){
							require_once('ic_commerce_ultimate_report_product_wise_new_customer.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Product_Wise_New_Customer($c, $this->constants['plugin_key']);
							$intence->ajax();
							die;
						}
						
						if($do_action_type == "customer_product"){
							require_once('ic_commerce_ultimate_report_customer_wise_new_product.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Customer_Wise_New_Product($c, $this->constants['plugin_key']);
							$intence->ajax();
							die;
						}
						
						if($do_action_type == "new_repeat_customer"){
							require_once('ic_commerce_ultimate_report_new_repeat_customer.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_New_Repeat_Customer($c, $this->constants['plugin_key']);
							$intence->ajax();
							die;
						}
						
						if($do_action_type == "permission_setting_pages" || $do_action_type == 'save_report_pages'){
							require_once('ic_commerce_ultimate_report_permission.php');
							$intence = new IC_Commerce_Ultimate_Woocommerce_Report_Permission_Settings($c, $this->constants['plugin_key']);
							$intence->ajax();
							die;
						}
						
					}
				}
				die(); // this is required to return a proper result
				exit;
			}
			
	}
}