<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if (!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Permission_Settings')) {
	include_once("ic_commerce_ultimate_report_functions.php");
	class IC_Commerce_Ultimate_Woocommerce_Report_Permission_Settings  extends  IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		
		/* variable declaration*/
		var $constants = array();
		
		/*
			* Function Name __construct
			*
			* Initialize Class Default Settings, Assigned Variables
			*
			* @param array $constants
			*		 
		*/
		function __construct($constants = array()){
			$this->constants = $constants;
			//add_filter('ic_commerce_ultimate_report_plugin_role', array($this, 'ic_commerce_ultimate_report_plugin_role'),11);
			add_filter('ic_commerce_permission_page_roles', array($this, 'ic_commerce_permission_page_roles'),11);
		}
		
		function custom_ic_commerce_ultimate_report_plugin_role($plugin_role = 'manage_woocommerce'){
			$saved_enabled_pages = get_option('icwoocommerceultimatereport_page_enabled_pages',array());
			if(count($saved_enabled_pages) > 0){
				$plugin_role = 'read';
			}
			return $plugin_role;
		}	
				
		/*
			* Function Name admin_menu
		*/
		function admin_menu(){
			global $submenu, $menu;
			
			$plugin_key 			= isset($this->constants['plugin_key']) ? $this->constants['plugin_key'] : '';
			$parent_menu  			= $plugin_key.'_page';
			
			$saved_enabled_pages	= get_option($parent_menu.'_enabled_pages',array());
			
			$submenu_list 		   = isset($submenu[$parent_menu]) ? $submenu[$parent_menu] : array();
			
			if(count($submenu_list)>0){			
				$current_user 		= wp_get_current_user();
				$roles			   = isset($current_user->roles) ? $current_user->roles : array();				
				$page 				= isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
				$delete_permission	= isset($_REQUEST['delete_permission']) ? $_REQUEST['delete_permission'] : '';
				if($delete_permission == "yes"){
					$access_delete = false;
					foreach($roles as $user_role_key => $user_role){
						if(in_array($user_role, array('administrator'))){
							$access_delete = true;
						}
					}
					if($access_delete){
						$saved_enabled_pages = array();
						delete_option($parent_menu.'_enabled_pages');
					}
				}
				
				if($page == 'icwoocommerceultimatereport_permission'){
					foreach($roles as $user_role_key => $user_role){
						if(in_array($user_role, array('administrator'))){
							$report_pages = array();
							foreach($submenu_list as $key => $menu_list){
								$page_slug = isset($menu_list[2]) ? $menu_list[2] : '';
								$page_title = isset($menu_list[0]) ? $menu_list[0] : '';
								$report_pages[$page_slug] = $page_title;
							}					
							update_option($parent_menu,$report_pages);
						}
					}
				}				
				
				$checked = false;
				$removed_menu = array();
				$activated_pages = array();
				
				$comman_enabled_pages = array();
				/*Creating list of selected/unselected pages*/
				foreach($roles as $key => $user_role){
					$enabled_pages	= isset($saved_enabled_pages[$user_role]) ? $saved_enabled_pages[$user_role] : array();
					foreach($enabled_pages as $admin_page => $enabled){						
						$comman_enabled_pages[$admin_page] = $enabled;						
					}
				}
				
				/*Updating list of selected pages*/
				foreach($roles as $key => $user_role){
					$enabled_pages	= isset($saved_enabled_pages[$user_role]) ? $saved_enabled_pages[$user_role] : array();
					foreach($enabled_pages as $admin_page => $enabled){
						if($enabled == 'yes'){
							$comman_enabled_pages[$admin_page] = $enabled;
						}
					}
				}
				
				foreach($roles as $key => $user_role){
					if(!$checked){
						$checked = true;
						//$enabled_pages	= isset($saved_enabled_pages[$user_role]) ? $saved_enabled_pages[$user_role] : array();
						$enabled_pages	= $comman_enabled_pages;
						if(count($enabled_pages)>0){
							$checked = true;						
							foreach($enabled_pages as $admin_page => $enabled){							
								if($enabled == 'no'){								
									foreach($submenu_list as $key => $menu_list){
										$page_slug = isset($menu_list[2]) ? $menu_list[2] : '';
										if($page_slug == $admin_page){
											unset($submenu[$parent_menu][$key]);
											$removed_menu[$admin_page] = $admin_page;
										}
									}
								}else{
									if($enabled == 'yes'){
										$activated_pages[$admin_page] = $admin_page;
									}
								}
							}							
						}else{
							if(!$checked){
								if(!in_array($user_role, array('administrator'))){
									foreach($submenu_list as $key => $menu_list){
										$page_slug = isset($menu_list[2]) ? $menu_list[2] : '';
										unset($submenu[$parent_menu][$key]);
										$removed_menu[$page_slug] = $page_slug;
									}
									foreach($menu as $position => $menu_list){
										$page_slug = isset($menu_list[2]) ? $menu_list[2] : '';
										if($page_slug == $parent_menu){
											unset($menu[$position]);
										}
									}
								}
							}
							
						}
					}
				}
				
				
				
				if(count($removed_menu)>0){
					$current_page	= isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
					if(in_array($current_page,$removed_menu)){
						wp_die( __( 'You do not have sufficient permissions to access this page.','icwoocommerce_textdomains' ) );
					}
				}
				
			}
		}
		
		/*
			* Function Name get_plugin_sub_menu
			*
			* @param string $parent_menu
			*
			* return $report_pages
		*/
		function get_plugin_sub_menu($parent_menu){
			$report_pages = get_option($parent_menu);			
			return $report_pages;
		}
		
		/*
			* Function Name get_plugin_sub_menu
			*
			* @param string $report_pages
			*
			* @param string $parent_menu
			*
			* @param string $plugin_key
			*
			* return $pages
		*/
		function get_plugin_report_pages($report_pages = array(),$parent_menu = array(),$plugin_key = ''){
			$user_role = isset($_REQUEST['user_role']) ? $_REQUEST['user_role'] : '';
			switch($parent_menu){
				case "icwoocommerceultimatereport_page":
					$pages = array();
					$pages[$parent_menu] 								      = isset($report_pages[$parent_menu]) 					? $report_pages[$parent_menu] 					: '';
					$pages[$plugin_key.'_details_page'] 				       = isset($report_pages[$plugin_key.'_details_page']) 	? $report_pages[$plugin_key.'_details_page'] 	: '';
					$pages[$plugin_key.'_report_page'] 					    = isset($report_pages[$plugin_key.'_report_page']) 		? $report_pages[$plugin_key.'_report_page'] 	: '';
					$pages[$plugin_key.'_cross_tab_page'] 				     = isset($report_pages[$plugin_key.'_cross_tab_page']) 	? $report_pages[$plugin_key.'_cross_tab_page'] 	: '';
					$pages[$plugin_key.'_variation_page'] 				     = isset($report_pages[$plugin_key.'_variation_page']) 	? $report_pages[$plugin_key.'_variation_page'] 	: '';
					$pages[$plugin_key.'_stock_list_page'] 				    = isset($report_pages[$plugin_key.'_stock_list_page']) 	? $report_pages[$plugin_key.'_stock_list_page'] : '';
					$pages[$plugin_key.'_variation_stock_page'] 		       = isset($report_pages[$plugin_key.'_variation_stock_page']) ? $report_pages[$plugin_key.'_variation_stock_page'] : '';
					$pages[$plugin_key.'_projected_actual_sales_page']	    = isset($report_pages[$plugin_key.'_projected_actual_sales_page']) ? $report_pages[$plugin_key.'_projected_actual_sales_page'] : '';
					$pages[$plugin_key.'_tax_report_page'] 					= isset($report_pages[$plugin_key.'_tax_report_page']) 				? $report_pages[$plugin_key.'_tax_report_page'] : '';
					$pages[$plugin_key.'_daily_sales_report'] 				 = isset($report_pages[$plugin_key.'_daily_sales_report']) 			? $report_pages[$plugin_key.'_daily_sales_report'] : '';
					$pages[$plugin_key.'_product_wise_new_customer'] 		  = isset($report_pages[$plugin_key.'_product_wise_new_customer']) 	? $report_pages[$plugin_key.'_product_wise_new_customer'] : '';
					$pages[$plugin_key.'_customer_wise_new_product'] 		  = isset($report_pages[$plugin_key.'_customer_wise_new_product']) 	? $report_pages[$plugin_key.'_customer_wise_new_product'] : '';
					$pages[$plugin_key.'_new_repeat_customer'] 				= isset($report_pages[$plugin_key.'_new_repeat_customer']) 			? $report_pages[$plugin_key.'_new_repeat_customer'] : '';
					$pages[$plugin_key.'_product_analysis'] 				   = isset($report_pages[$plugin_key.'_product_analysis'])				? $report_pages[$plugin_key.'_product_analysis'] : '';
					$pages[$plugin_key.'_variable_product_analysis'] 		  = isset($report_pages[$plugin_key.'_variable_product_analysis']) 	? $report_pages[$plugin_key.'_variable_product_analysis'] : '';
					$pages[$plugin_key.'_group_variable_product_analysis']	= isset($report_pages[$plugin_key.'_group_variable_product_analysis']) 		? $report_pages[$plugin_key.'_group_variable_product_analysis'] : '';
					
					if(!in_array($user_role, array('administrator'))){
						$pages[$plugin_key.'_permission']						 = isset($report_pages[$plugin_key.'_permission']) 								? $report_pages[$plugin_key.'_permission'] : '';
					}
					
					$pages[$plugin_key.'_options_page'] 					   = isset($report_pages[$plugin_key.'_options_page']) 						? $report_pages[$plugin_key.'_options_page'] 	: '';
					break;
				default:
					$pages = array();
					break;
				
			}
			return $pages;
		}
		
		function ic_commerce_permission_page_roles($roles = array()){
			if(isset($roles['subscriber'])){
				unset($roles['subscriber']);
			}			
			if(isset($roles['customer'])){
				unset($roles['customer']);
			}
			return $roles;
		}
		
		/*
			* Function Name init
			*
			* Creates Search form, assigning values to variables.
			*		 
		*/
		function init(){
			
					
					$output 		= "";
					$plugin_key 	= $this->constants['plugin_key'];
					$parent_menu  	= $plugin_key.'_page';
					$user_role		= isset($_REQUEST['user_role']) ? $_REQUEST['user_role'] : '';
					$roles 			= $this->get_user_role();
					$drop_down_label= __('Select Role: ','icwoocommerce_textdomains');
					
					//delete_option($parent_menu.'_enabled_pages');
					
					//$settings = get_option($parent_menu.'_enabled_pages');
					//$this->print_array($settings);
					
					$roles = apply_filters('ic_commerce_permission_page_roles',$roles,$roles);
					
					$output .= "<div class=\"ic_block_content\">";
					
					$output .= '<form method="post" name="frm_save_permission" id="frm_save_permission">';
					
					
					$output .= "<br /><label for=\"user_role\">".__('User Role: ','icwoocommerce_textdomains')."</label> ";
					
					$output .= $this->create_dropdown($roles,"user_role","user_role",$drop_down_label,"user_role",'-1', 'array', false, 5, "-1", false);
					
					$output .= '	<input type="hidden" name="action" value="icwoocommerceultimatereport_wp_ajax_action" />';
					$output .= '	<input type="hidden" name="do_action_type" value="save_report_pages" />';
					$output .= '	<input type="hidden" name="plugin_key" value="'.$plugin_key.'" />';
					$output .= '   <input type="hidden" name="parent_menu" value="'.$parent_menu.'" />';
					
					$output .= "<br /><br /><div class=\"ic_report_pages\">";
					$output .= $this->get_list_setting_pages();
					$output .= "</div>";
					
					$output .= " <p class=\"submit\">";
					$output .= "	<input type=\"submit\" value=\"".__('Save','icwoocommerce_textdomains')."\" class=\"button button-primary btn_save_permission\">";
					$output .= "	<input type=\"button\" value=\"".__('Check All','icwoocommerce_textdomains')."\" class=\"button button-primary check_all\" style=\"display:none\">";
					$output .= " </p>";
					$output .= '</form>';
					
					$output .= "</div>";
										
					echo $output;
		}
		
		/*
			* Function Name ajax
		*/
		function ajax(){
			$do_action_type = isset($_REQUEST['do_action_type']) ? $_REQUEST['do_action_type'] : '';
			
			
			if($do_action_type == 'save_report_pages'){
			
				$this->constants['plugin_key'] = isset($_REQUEST['plugin_key']) ? $_REQUEST['plugin_key'] : '';				
				
				$enabled_pages 			= isset($_REQUEST['enabled_pages']) ? $_REQUEST['enabled_pages'] : array();
				$user_role				= isset($_REQUEST['user_role']) ? $_REQUEST['user_role'] : '';
				$parent_menu			= isset($_REQUEST['parent_menu']) ? $_REQUEST['parent_menu'] : '';				
				$plugin_key 			= $this->constants['plugin_key'];
												
				$report_pages 			= $this->get_plugin_sub_menu($parent_menu);
				
				$pages 					= $this->get_plugin_report_pages($report_pages,$parent_menu,$plugin_key);
				
				$saved_enabled_pages	= get_option($parent_menu.'_enabled_pages',array());
				
				if(in_array($user_role, array('administrator'))){
					foreach($pages as $key => $label){					
						$saved_enabled_pages[$user_role][$key] = isset($enabled_pages[$key]) ? $enabled_pages[$key] : 'no';
					}
				}else{
					/*
					foreach($pages as $key => $label){					
						$saved_enabled_pages[$user_role][$key] = isset($enabled_pages[$key]) ? $enabled_pages[$key] : 'no';
					}
					*/
					$at_least_one_checkbox_checked = false;
					$check_submenu = array();
					foreach($pages as $key => $label){
						$enabled = isset($enabled_pages[$key]) ? $enabled_pages[$key] : 'no';
						$saved_enabled_pages[$user_role][$key] = $enabled;
						if($enabled == 'yes'){
							$at_least_one_checkbox_checked = true;
						}
					}					
					if(!$at_least_one_checkbox_checked){
						unset($saved_enabled_pages[$user_role]);
					}
					
				}
								
				update_option($parent_menu.'_enabled_pages',$saved_enabled_pages);				
				echo $this->get_list_setting_pages();
				die;
			}
			
			if($do_action_type == 'permission_setting_pages'){
				$this->constants['plugin_key'] = isset($_REQUEST['plugin_key']) ? $_REQUEST['plugin_key'] : '';
				echo $this->get_list_setting_pages();
				die;
			}
		}
		
		/*
			* Function Name get_list_setting_pages
			*
			* return $output
		*/
		function get_list_setting_pages(){
			
			$user_role		= isset($_REQUEST['user_role']) ? $_REQUEST['user_role'] : '';
			$plugin_key 	   = $this->constants['plugin_key'];
			$parent_menu  	  = $plugin_key.'_page';
			$report_pages 	 = $this->get_plugin_sub_menu($parent_menu);
			$output 		   = "";
			
			if(!empty($user_role) and $user_role != '-1'){
				
				
				
				$pages 					= $this->get_plugin_report_pages($report_pages,$parent_menu,$plugin_key);
				$saved_enabled_pages	  = get_option($parent_menu.'_enabled_pages',array());
				$enabled_pages			= isset($saved_enabled_pages[$user_role]) ? $saved_enabled_pages[$user_role] : array();
				
				$default = "no";
				if(in_array($user_role, array('administrator'))){
					$default = "";
				}
								
				$output .= "<ul id=\"sortable\">";
				foreach($pages as $admin_page_key => $label){
					$output .= "\n\t<li data-admin_page_key=\"{$admin_page_key}\">";
					
					$output .= "<div style=\"float:left\">";
						$output .= "{$label}";
					$output .= "</div>";
					//$output .= "<input type=\"text\" name=\"setting_columns[$admin_page_key]\" value=\"{$label}\">";
					$output .= "<div style=\"float:right; margin-bottom:3px;\">";
							$output .= "<label class=\"switch\">";
							
							$enable = isset($enabled_pages[$admin_page_key]) ? $enabled_pages[$admin_page_key] : $default;
							
							if($enable == 'yes'){
								$output .= "<input type=\"checkbox\" name=\"enabled_pages[$admin_page_key]\" value=\"yes\" checked=\"checked\">";
							}else if($enable == 'no'){
								$output .= "<input type=\"checkbox\"  name=\"enabled_pages[$admin_page_key]\" value=\"yes\">";
							}else{
								$output .= "<input type=\"checkbox\" name=\"enabled_pages[$admin_page_key]\" value=\"yes\" checked=\"checked\">";
							}
						 	$output .= "<div class=\"slider round\"></div>";
						$output .= "</label>";
					$output .= "</div>";
					
					$output .= "<div class=\"clearfix\"></div>";
					$output .= "</li>";
				}	
				$output .= "</ul>";
			}
			
			return $output;
		}
		
		/*
			* Function Name get_user_role
			*
			* return $roles
		*/
		function get_user_role(){ 
			global $wp_roles;
			$roles = $wp_roles->get_names();			
			return $roles;
		}//End
		
	}
}