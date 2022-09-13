<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Summary_Stock_Alert_Email')){
		
	class IC_Commerce_Ultimate_Woocommerce_Report_Summary_Stock_Alert_Email  extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		
		/* variable declaration constants*/
		public $constants 			=	array();
		/*
		* Function Name __construct
		*
		* Initialize Class Default Settings, Assigned Variables
		*
		* @param array $constants
		*		 
		*/
		function __construct($constants = array(), $plugin_key = ""){
			$this->constants	= array_merge($this->constants, $constants);
			
			$this->constants['plugin_key'] 			= isset($this->constants['plugin_key']) ? $this->constants['plugin_key'] : 'icwoocommerceultimatereport';
			$this->constants['schedule_hook_name'] 	= $this->constants['plugin_key'].'_schedule_sea_event';
			
			add_action('ic_commerce_ultimate_report_settting_field_after_email_report',array($this, 'settings'),		31,2);
			//add_action('ic_commerce_ultimate_report_settting_field_after_dashboard',	array($this, 'settings'),		9,2);
			add_action('ic_commerce_ultimate_report_settting_values',				array($this, 'save_settings'),	41,3);			
			add_action("ic_commerce_ultimate_report_ajax_action", 					array($this, 'ajax_action'),	41,2);			
			add_action('wp', 														 array($this, 'wp_next_scheduled'));
			add_action($this->constants['schedule_hook_name'], 					   array($this, 'event_action_cron'));			
			add_action('wp_loaded', 												  array($this, 'wp_loaded'),110);
			
		}
		
		/**
		* init
		*
		* send email
		*
		* @return void
		*/
		function wp_loaded(){
			if(isset($_REQUEST['action']) and $_REQUEST['action'] == "stock_status_notification"){
				$this->ic_woo_schedule_send_email();
				die;
			}
		}
		
		/**
		* event_action_cron
		*
		* send email
		*
		* @return void
		*/
		function event_action_cron(){
			$this->ic_woo_schedule_send_email();
		}
		/**
		* wp_next_scheduled
		*
		* scheduled next event
		*
		*
		* @return void 
		*/
		function wp_next_scheduled(){
			
			$original_args 					= array();
			$timestamp 						= time();			
			$this->constants['plugin_options'] = isset($this->constants['plugin_options']) ? $this->constants['plugin_options'] : get_option($this->constants['plugin_key']);;
			$options 						= $this->constants['plugin_options'];
			
			$schedule_activate				= isset($options['act_sea_reporting']) ? $options['act_sea_reporting'] : 0;
			$schedule_recurrence			= isset($options['sea_schedule']) ? $options['sea_schedule'] : 0;
			$schedule_hook_name				= $this->constants['schedule_hook_name'];
			
			if($schedule_activate == 1 and strlen($schedule_recurrence) > 2){
				if (!wp_next_scheduled($schedule_hook_name)){
					wp_schedule_event($timestamp, $schedule_recurrence, $schedule_hook_name);
				}
			}else{
				wp_unschedule_event( $timestamp, $schedule_hook_name, $original_args );
				wp_clear_scheduled_hook( $schedule_hook_name, $original_args );
			}
		}
		/**
		* settings
		*
		* Save settings
		*
		* @param string $that
		* @param string $option 
		* @return void 
		*/
		function settings($that = '', $option = ''){
			
			$cron_schedule 			= $this->get_cron_schedule();
			//$cs						= array("ten_minute" => "Once Ten Minute", "hourly" => "Once Hourly");
			//$cron_schedule			= array_merge($cs,$cron_schedule);
			
			add_settings_section('stock_email_alert',	__('Stock Email Alert:','icwoocommerce_textdomains'),			array( &$that, 'section_options_callback' )	,$option);
			add_settings_field('sea_zero_level',		__( 'Zero Level:', 'icwoocommerce_textdomains'),				array( &$that,'checkbox_element_callback' ), 	$option, 'stock_email_alert', array('menu'=> $option,	'label_for'=>'sea_zero_level',			'id'=> 'sea_zero_level','default'=>0));			
			add_settings_field('sea_minimum_level',		__( 'Minimum Level:', 'icwoocommerce_textdomains'),				array( &$that,'checkbox_element_callback' ), 	$option, 'stock_email_alert', array('menu'=> $option,	'label_for'=>'sea_minimum_level',		'id'=> 'sea_minimum_level','default'=>0));
			
			add_settings_field('sea_send_to',			__( 'Email Send To:', 'icwoocommerce_textdomains'),				array( &$that, 'text_element_callback' ), 		$option, 'stock_email_alert', array('menu'=> $option,	'size'=>50,	'class'=>'emailcharacters', 'maxlength'=>'500',	'label_for'=>'sea_send_to',		'id'=> 'sea_send_to',	'default'=>''));
			add_settings_field('sea_from_name',			__( 'From Name:', 'icwoocommerce_textdomains'),					array( &$that, 'text_element_callback' ), 		$option, 'stock_email_alert', array('menu'=> $option,	'size'=>50,	'class'=>'emailcharacters', 'maxlength'=>'100',	'label_for'=>'sea_from_name',	'id'=> 'sea_from_name',	'default'=>''));
			add_settings_field('sea_from_email',		__( 'From Email:', 'icwoocommerce_textdomains'),				array( &$that, 'text_element_callback' ), 		$option, 'stock_email_alert', array('menu'=> $option,	'size'=>50,	'class'=>'emailcharacters', 'maxlength'=>'100',	'label_for'=>'sea_from_email',	'id'=> 'sea_from_email','default'=>''));
			add_settings_field('sea_subject',			__( 'Subject:', 'icwoocommerce_textdomains'),					array( &$that, 'text_element_callback' ), 		$option, 'stock_email_alert', array('menu'=> $option,	'size'=>50,	'class'=>'emailcharacters', 'maxlength'=>'150',	'label_for'=>'sea_subject',		'id'=> 'sea_subject',	'default'=>''));
			
			add_settings_field('sea_schedule',			__( 'Email Schedule:', 'icwoocommerce_textdomains'),			array( &$that, 'select_element_callback' ), 	$option, 'stock_email_alert', array('menu'=> $option,	'label_for'=>'sea_schedule',		'id'=> 'sea_schedule','default'=>'daily','options'=>$cron_schedule,'label_none'=>__('Unschedule Event','icwoocommerce_textdomains')));
			add_settings_field('act_sea_reporting',		__( 'Activate Email Reporting:', 'icwoocommerce_textdomains' ),	array( &$that,'checkbox_element_callback' ), 	$option, 'stock_email_alert', array('menu'=> $option,	'size'=>25,	'label_for'=>'act_sea_reporting',		'id'=> 'act_sea_reporting',	'default'=>0));			
			
			$schedule_hook_name			= $this->constants['schedule_hook_name'];
			$wp_next_scheduled			= wp_next_scheduled($schedule_hook_name);
			
			if ($wp_next_scheduled){
				if(defined('DISABLE_WP_CRON') && DISABLE_WP_CRON){
				
				}else{
					$html = __("Active",'icwoocommerce_textdomains');					
					add_settings_field('sea_cron_job_status',	__( 'Schedule Status:', 'icwoocommerce_textdomains' ),	array( &$that, 'label_element_callback' ), 				$option, 'stock_email_alert', array('menu'=> $option,	'id'=> 'sea_cron_job_status',		'default'=>$html));
				}
			}else{
				$html = __("Schedule mailing stoped or not activated or unschedule event or try again",'icwoocommerce_textdomains');
				add_settings_field('sea_cron_job_status',	__( 'Schedule Status:', 'icwoocommerce_textdomains' ),	array( &$that, 'label_element_callback' ), 				$option, 'stock_email_alert', array('menu'=> $option,	'id'=> 'sea_cron_job_status',		'default'=>$html));		
			}
		
			if(defined('DISABLE_WP_CRON') && DISABLE_WP_CRON){
				
				if(!isset($this->constants['plugin_options'])){
					$this->constants['plugin_options'] = isset($this->constants['plugin_options']) ? $this->constants['plugin_options'] : get_option($this->constants['plugin_key']);
				}
				
				$disable_wp_cron 			= $this->get_setting('disable_wp_cron',$this->constants['plugin_options'], 0);//Added 20150721
				if($disable_wp_cron == 1){
					$html = __('Automatic Schedule mailing can not work, because "DISABLE_WP_CRON" is defined as "TRUE" on this plug-ins setting page in "Cron URL Settings" section.','icwoocommerce_textdomains');
				}else{
					$html = __('Schedule mailing can not work, because "DISABLE_WP_CRON" is defined as "TRUE" in wp-config.php file or somewhere else. To define this to "FALSE" open wp-config.php file in WordPress root folder and set it to "FALSE" or comment.','icwoocommerce_textdomains');
				}
				
				add_settings_field('disable_wp_cron_status',	__( 'WP CRON Status:', 'icwoocommerce_textdomains' ),	array( &$that, 'label_element_callback' ), 				$option, 'stock_email_alert', array('menu'=> $option,	'id'=> 'disable_wp_cron_status',		'default'=>$html));		
			}
			
			add_settings_field('stock_alert_cron_url',	__( 'Stock Alert CRON URL  :', 'icwoocommerce_textdomains' ),	array( &$this, 'sea_cron_url_callback' ), 				$option, 'stock_email_alert', array('menu'=> $option,	'id'=> 'sea_cron_url',		'default'=>''));		
			
			add_settings_field('sea_report_actions',	__( 'Action:', 'icwoocommerce_textdomains' ),			array( &$that, 'create_button_element_callback' ), 		$option, 'stock_email_alert', array('menu'=> $option,	'id'=> 'sea_report_actions',		'buttons'=>array('order_status_mail' =>array('value'=>__('Test Mail','icwoocommerce_textdomains'),'type'=>'button','id'=>'order_status_mail'))));
			
		}
		
		function sea_cron_url_callback(){			
			if(function_exists('site_url')){
				$site_url = site_url()."/admin-ajax.php?action=stock_status_notification";
				echo "<a href=\"{$site_url}\" target=\"_blank\">{$site_url}</a>";
			}
		}
		
		/**
		* save_settings
		*
		* 
		*
		* @param array $new_settings
		* @param string $that
		* @param array $old_settings 
		* @return array $new_settings 
		*/
		function save_settings($new_settings = array(), $that = NULL, $old_settings = array()){
			
			if(isset($new_settings['sea_schedule'])){
				
				$original_args 					= array();
				$timestamp 						= time();
									
				$schedule_activate_old			= isset($old_settings['act_sea_reporting']) 	? $old_settings['act_sea_reporting'] 	: 0;
				$schedule_recurrence_old		= isset($old_settings['sea_schedule']) 			? $old_settings['sea_schedule'] 			: 0;					
				
				$schedule_activate				= isset($new_settings['act_sea_reporting']) ? $new_settings['act_sea_reporting'] : 0;
				$schedule_recurrence			= isset($new_settings['sea_schedule']) ? $new_settings['sea_schedule'] : 0;
				$schedule_hook_name				= $this->constants['schedule_hook_name'];
				
				//wp_unschedule_event( $timestamp, $schedule_hook_name, $original_args );
				//wp_clear_scheduled_hook( $schedule_hook_name, $original_args );
				
				if(($schedule_activate_old != $schedule_activate) or ($schedule_recurrence_old != $schedule_recurrence)){
					//echo "action";
					wp_unschedule_event( $timestamp, $schedule_hook_name, $original_args );
					wp_clear_scheduled_hook( $schedule_hook_name, $original_args );
				}
				
				//else{echo "no action";}	
				if($schedule_activate == 1){
					if(strlen($schedule_recurrence) > 2){
						if (!wp_next_scheduled($schedule_hook_name)){
							wp_schedule_event($timestamp, $schedule_recurrence, $schedule_hook_name);
						}
					}
				}
			}
			return $new_settings;
		}
		/**
		* ajax_action
		*
		* 
		*
		* @param array $constants
		* @param string $do_action_type
		*
		* @return void
		*/
		function ajax_action($constants = '', $do_action_type = ''){
			if($do_action_type == "sea_report_actions_order_status_mail"){
				$this->constants	= array_merge($this->constants, $constants);
				$this->ajax_schedule_event();
				die;
			}
		}
		/**
		* ajax_schedule_event
		*
		* @return void
		*/
		function ajax_schedule_event(){
				$message = "";
				
				$this->constants['plugin_options'] = isset($this->constants['plugin_options']) ? $this->constants['plugin_options'] : get_option($this->constants['plugin_key']);;
				
				$schedule_activate	 		= $this->get_setting('act_sea_reporting',$this->constants['plugin_options'], 0);
				$schedule_recurrence		= $this->get_setting('sea_schedule',$this->constants['plugin_options'], 'daily');
				$flag						= true;
				$schedule_hook_name			= $this->constants['schedule_hook_name'];
				
				if(strlen($schedule_recurrence) <= 2){
					$message .= '<li>'.__("Please select Email Schedule.",'icwoocommerce_textdomains').'</li>';
					$flag	= false;
				}
				
				if($schedule_activate  == 0){
					$message .= '<li>'.__("Please select, Activate Email Reporting.",'icwoocommerce_textdomains').'</li>';
					$flag	= false;
				}
			
				if($schedule_activate == 1 and strlen($schedule_recurrence) > 2){
					if (!wp_next_scheduled($schedule_hook_name)){
						$message .= '<li>'.__("Somehow next schedule is not set, please try re-activating schedule settting.",'icwoocommerce_textdomains').'</li>';
						$flag	= false;
					}
				}
				
				if($flag){
					$this->constants['status_report_emailed'] = $this->ic_woo_schedule_send_email();
					if(isset($this->constants['status_report_emailed'])){
						if($this->constants['status_report_emailed']){
							$message .= '<li>'.__("Email has been sent successfully.",'icwoocommerce_textdomains').'</li>';
						}else{
							$message .= '<li>'.__("Getting problem while sending mail.",'icwoocommerce_textdomains').'</li>';
						}
						
					}
				}else{
					$message .= '<li>'.__('Click on "Save Changes" button for saving changes and try again.','icwoocommerce_textdomains').'</li>';
				}
				echo "<ul>";
				echo $message;
				echo "</ul>";
			}
			/**
			* ic_woo_schedule_send_email
			*
			* @return void
			*/
			public function ic_woo_schedule_send_email() {
				//error_log("function ic_woo_schedule_send_email");
				
				global $wpdb;
				
				if(!isset($this->constants['plugin_options'])){
					$this->constants['plugin_options'] = get_option($this->constants['plugin_key']);
				}
				
				$act_sea_reporting 			= $this->get_setting('act_sea_reporting',$this->constants['plugin_options'], 0);
				$sea_schedule 				= $this->get_setting('sea_schedule',$this->constants['plugin_options'], 'daily');
				$sea_zero_level 			= $this->get_setting('sea_zero_level',$this->constants['plugin_options'], 0);
				$sea_minimum_level 			= $this->get_setting('sea_minimum_level',$this->constants['plugin_options'], 0);
				
				$sea_time_limit 			= $this->get_setting('sea_time_limit',$this->constants['plugin_options'], 300);
				
				$activate_cron_url 			= $this->get_setting('activate_cron_url',$this->constants['plugin_options'], 0);
				
				@set_time_limit($sea_time_limit);
				
				$isset_doing_wp_cron 		= (isset($_REQUEST['doing_wp_cron']) and $activate_cron_url == 1) ? 1 : 0;
				
				if($sea_zero_level == 1 || $sea_minimum_level == 1):
					require_once('ic_commerce_ultimate_report_summary_stock_alert.php');
					$stock_alert_email_obj = new IC_Commerce_Ultimate_Woocommerce_Report_Summary_Stock_Alert($this->constants);					
				endif;	
				
				//error_log(print_r($this->constants['plugin_options'],true));			
				
				$filter_parameters = array(
					'report_type' 			=> "top",
					'start_date' 			=> "",
					'end_date' 				=> "",
					'title' 				=> "",
					'start' 				=> ""
				);
				
				//error_log("function 1");
				
				$email_data 	= "";					
				$email_data 	= apply_filters("ic_commerce_schedule_mailing_stock_emal_alert_top", $email_data, $filter_parameters);
				$page_titles	= array();
				
				if($sea_zero_level == 1):					
					$title				= __("Zero Level Stock Alert",'icwoocommerce_textdomains');
					$sql 			= $stock_alert_email_obj->get_product_stock_query(0,'zero_level_popup');
					$order_items 	= $wpdb->get_results($sql);			
					$order_items 	= $stock_alert_email_obj->create_grid_data($order_items);
					$columns 		= $stock_alert_email_obj->ic_commerce_report_page_columns(array(),'email_template');
					$output			= $stock_alert_email_obj->create_grid($order_items, $columns,'email_template');
					
					if(!empty($output)){
						$email_data .= '<div style="width:700px;">';
						$email_data 	.= "<h3 style=\"width:700px;font-family:Arial, Helvetica, sans-serif;margin:10px 0 0 0; font-size:15; color:#000;\">{$title}</h3>";
						$email_data 	.= $output;
						$email_data .='</div>';
					}
					
					$page_titles[] = $title;
					
					
					
				endif;
				
				//error_log("function 2");
				
				if($sea_zero_level == 1):					
					$title				= __("Minimum Level Stock Alert",'icwoocommerce_textdomains');
					$stock_less_than = get_option('woocommerce_notify_low_stock_amount');
					$sql 			= $stock_alert_email_obj->get_product_stock_query($stock_less_than,'minimum_level_popup');					
					$order_items 	= $wpdb->get_results($sql);
					$order_items 	= $stock_alert_email_obj->create_grid_data($order_items);
					$columns 		= $stock_alert_email_obj->ic_commerce_report_page_columns(array(),'email_template');
					$output			= $stock_alert_email_obj->create_grid($order_items, $columns,'email_template');
					
					if(!empty($output)){
						$email_data .= '<div style="width:700px;">';
						$email_data 	.= "<h3 style=\" width:700px;font-family:Arial, Helvetica, sans-serif;margin:10px 0 0 0; font-size:15; color:#000;\">{$title}</h3>";
						$email_data 	.= $output;
						$email_data .='</div>';
					}
					
					$page_titles[] = $title;
					
				endif;
				
				//error_log("function 3");
					
				$email_data = apply_filters("ic_commerce_schedule_mailing_stock_emal_alert_bottom", $email_data, $filter_parameters);
				
				//error_log("email_data $email_data");
				
				if($sea_zero_level 		==	1 || $sea_minimum_level	==	1):
					if(strlen($email_data)>0){
											
						$new ='<html>';
							$new .='<head>';
								$new .='<title>';								
								$new .= implode(",", $page_titles);								
								$new .='</title>';						
							$new .='</head>';
							$new .='<body>';
							$new .= $this->display_logo();
							$new .= '<div style="width:700px; margin:0 auto; font-family:Arial, Helvetica, sans-serif; font-size:12px;">';
							$new .= $email_data;
							$new .='</div>';
							$new .='</body>';
						$new .='</html>';
						$email_data = $new;
						
						if(isset($this->constants['force_email'])){
							//echo $email_data;
						}
						
						$sea_send_to 		= $this->get_setting('sea_send_to',		$this->constants['plugin_options'], '');
						$sea_from_name 		= $this->get_setting('sea_from_name',	$this->constants['plugin_options'], '');
						$sea_from_email 	= $this->get_setting('sea_from_email',	$this->constants['plugin_options'], '');
						$sea_subject 		= $this->get_setting('sea_subject',		$this->constants['plugin_options'], '');
						
						$sea_send_to 		= $this->get_email_string($sea_send_to);
						$sea_from_email 	= $this->get_email_string($sea_from_email);
						//echo $email_data;
						
						//error_log("function 4");
						
						if($sea_send_to || $sea_from_email){
							
							$subject = $sea_subject;
								
							$headers  = 'MIME-Version: 1.0' . "\r\n";
							$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
							$headers .= 'From: '.$sea_from_name.' <'.$sea_from_email.'>'. "\r\n";
														
							$email_data = str_replace("! ","",$email_data);
							$email_data = str_replace("!","",$email_data);
							
							$message = $email_data;
							$to		 = $sea_send_to;
							
							$result = wp_mail( $to, $subject, $message, $headers); 
							//error_log("function result");
							return $result;
						}
				
					}
				endif;
				return '';
			}
			/**
			* display_logo
			*
			* Display logo 
			*
			* @return string $body
			*/
			function display_logo(){
				$logo_image 			= $this->get_setting('logo_image',$this->constants['plugin_options'], '');				
				$body = "";
				if($logo_image){					
					$body .= '<div style="width:700px; margin:0 auto; background:#F0F8FF; border-radius:5px; font-family:Arial, Helvetica, sans-serif; font-size:12px;">';
					$body .= '<div style="padding-left:5px;">';
					$body .= '<table style="width:500px; border:1px solid #0066CC; margin:0 auto;">';
					$body .= '<tr>';
					$body .= '<td colspan="3">';
					$body .= '<img src="'.$logo_image.'" />';
					$body .= '</td>';
					$body .= '</tr>';
					$body .= '</table>';
					$body .= '</div>';
					$body .= '</div>';
					return $body;
				}
			}
			/**
			* check_email
			*
			* @param string $check
			*
			* @return bool 
			*/
			function check_email($check) {
				$expression = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$/";
				if (preg_match($expression, $check)) {
					return true;
				} else {
					return false;
				} 
			}
			/**
			* get_email_string
			*
			* @param string $emails
			*
			* @return bool 
			*/
			function get_email_string($emails){
				$emails = str_replace("|",",",$emails);
				$emails = str_replace(";",",",$emails);
				$emails = explode(",", $emails);
				
				$newemail = array();
				foreach($emails as $key => $value):
					$e = trim($value);
					if($this->check_email($e)){
						$newemail[] = $e;
					}				
				endforeach;
				
				if(count($newemail)>0){
					$newemail = array_unique($newemail);
					return implode(",",$newemail);
				}else
					return false;
			}
		
	}//End Class
}//End 