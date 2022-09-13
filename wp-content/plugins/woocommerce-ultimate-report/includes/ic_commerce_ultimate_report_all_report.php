<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'IC_Commerce_Ultimate_Woocommerce_Report_All_Report')){
	
	/*
	 * Class Name IC_Commerce_Ultimate_Woocommerce_Report_All_Report
	 *
	 * Class is used for returning all reports data
	 *	 
	*/
	class IC_Commerce_Ultimate_Woocommerce_Report_All_Report extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		
		/* variable declaration*/
		public $per_page = 0;	
		
		/* variable declaration*/
		public $per_page_default = 10;
		
		/* variable declaration*/
		public $request_data =	array();
		
		/* variable declaration*/
		public $constants 	=	array();
		
		/* variable declaration*/
		public $request		=	array();
		
		/* variable declaration*/
		public $today 		=	'';
		
		
		/*
			* Function Name __construct
			*
			* Initialize Class Default Settings, Assigned Variables
			*
			* @param array $constants
			*		 
		*/
		public function __construct($constants) {
			global $options;
			
			$this->constants		= $constants;			
			$options				= $this->constants['plugin_options'];			
			$this->per_page			= $this->constants['per_page_default'];
			$this->per_page_default	= $this->constants['per_page_default'];			
			$per_page 				= (isset($options['per_apge']) and strlen($options['per_apge']) > 0)? $options['per_apge'] : $this->per_page_default;
			$this->per_page 		= is_numeric($per_page) ? $per_page : $this->per_page_default;
			$this->today			= $this->constants['today_date'];			
		}
		
		
		/*
			* Function Name init
			*
			* Creates Search form, assigning values to variables.
			*
		*/
		function init(){
				global $back_day,$report_title, $wpdb;
				
				if(!isset($_REQUEST['page'])){return false;}
				
				if ( !current_user_can( $this->constants['plugin_role'] ) )  {
					wp_die( __( 'You do not have sufficient permissions to access this page.' ,'icwoocommerce_textdomains' ) );
				}
				
				$shop_order_status			= $this->get_set_status_ids();	
				$hide_order_status			= $this->constants['hide_order_status'];
				$hide_order_status			= implode(",",$hide_order_status);
				
				$order_status_id 			= "";
				$order_status 				= "";
				
				if($this->constants['post_order_status_found'] == 0 ){
					$order_status_id 		= implode(",",$shop_order_status);
				}else{
					$order_status_id 		= "";
					$order_status 			= implode(",",$shop_order_status);
				}
				
				$order_status				= strlen($order_status) > 0 		?  $order_status 		: '-1';
				$order_status_id			= strlen($order_status_id) > 0 		?  $order_status_id 	: '-1';
				$hide_order_status			= strlen($hide_order_status) > 0 	?  $hide_order_status 	: '-1';
				
				
				$product_status 			= $this->get_setting('product_status',$this->constants['plugin_options'], array());				
				$product_status				= implode("', '",$product_status);				
				$product_status				= strlen($product_status) > 0 ?  $product_status 	: '-1';
				
				$default_tab 				= apply_filters('ic_commerce_report_page_default_tab', 	'product_page');
				$report_name 				= $this->get_request('report_name',$default_tab,true);
				$start_date 				= apply_filters('ic_commerce_report_page_start_date',	$this->constants['start_date'],$report_name);
				$end_date 					= apply_filters('ic_commerce_report_page_end_date',		$this->constants['end_date'],$report_name);
				$order_status				= apply_filters('ic_commerce_report_page_selected_order_status', $order_status,$report_name);
				$product_status				= apply_filters('ic_commerce_report_page_selected_product_status', $product_status,$report_name);
				$onload_search				= apply_filters('ic_commerce_report_page_onload_search', "yes", $report_name);
				$onload_search				= apply_filters('ic_commerce_onload_search', $onload_search, $report_name);
				
				$publish_order				= "no";
				
				$page						= $this->get_request('page',NULL);
				$optionsid					= "per_row_all_report_page";
				$per_page 					= $this->get_number_only($optionsid,$this->per_page_default);
				$admin_page					= $this->get_request('admin_page',$page,true);
				$adjacents					= $this->get_request('adjacents','3',true);
				$p							= $this->get_request('p','1',true);
				$limit						= $this->get_request('limit',$per_page,true);
				
				$end_date						= $this->get_request('end_date',$end_date);
				$start_date					= $this->get_request('start_date',$start_date);
				
				$category_id				= $this->get_request('category_id','-1',true);
				$order_status_id			= $this->get_request('order_status_id',$order_status_id,true);
				$order_status				= $this->get_request('order_status',$order_status,true);
				$publish_order				= $this->get_request('publish_order',$publish_order,true);
				$hide_order_status			= $this->get_request('hide_order_status',$hide_order_status,true);
				$product_id					= $this->get_request('product_id','-1',true);
				
				$action						= $this->get_request('action',$this->constants['plugin_key'].'_wp_ajax_action',true);
				$do_action_type				= $this->get_request('do_action_type','report_page',true);
				$count_generated			= $this->get_request('count_generated',0,true);				
				$country_code				= $this->get_request('country_code','-1',true);
				$product_status				= $this->get_request('product_status',$product_status,true);
				
				$first_date 				= $this->constants['first_order_date'];
				
				if(!$end_date){$end_date 		= date_i18n('Y-m-d');}
				if(!$start_date){$start_date 	= $first_date;}
				
				$_REQUEST['end_date']		= $end_date;
				$_REQUEST['start_date'] 	  = $start_date;
				$_REQUEST['page_name'] 	   = 'all_detail';
				$_REQUEST['page_title'] 	  = "";
				
				$billing_or_shipping		= $this->get_setting('billing_or_shipping',$this->constants['plugin_options'], 'billing');
				
				$page_title 				= '';
				$report_tabts				= $this->get_all_report_tabts($this->constants,$report_name);
				$page_title					= isset($report_tabts['page_title']) ? $report_tabts['page_title'] : '';
				$_REQUEST['page_title'] 	= $page_title;
				$child_categories_count 	= 0;
				$parent_categories_count	= 0;				
					echo $report_tabts['report_title'];				
					echo $report_tabts['ouptput'];
				?>
                <div style="border-bottom:5px solid #2980B9; background-color:#FFFFFF; padding:0 15px 25px 15px; box-shadow: 2px 2px 3px #ccc;">
                <br>
                <?php if($report_name != 'coupon_page_archive'):?>
                <div id="navigation" class="hide_for_print">
                    <div class="collapsible" id="section1"><?php _e('Custom Search','icwoocommerce_textdomains');?><span></span></div>
                    <div class="container">
                        <div class="content">
                            <div class="search_report_form">
                                <div class="form_process"></div>
                                <form action="" name="Report" id="search_order_report" method="post">
                                    <div class="form-table">
                                         <?php
                                            $no_date_fields_tabs = apply_filters('ic_commerce_report_page_no_date_fields_tabs',array(),$report_name);
                                            if(!in_array($report_name,$no_date_fields_tabs)):
                            ?>
                                        <div class="form-group">
                                            <div class="FormRow FirstRow">
                                                <div class="label-text"><label for="start_date"><?php _e("From Date:",'icwoocommerce_textdomains'); ?></label></div>
                                                <div class="input-text"><input type="text" value="<?php echo $start_date;?>" id="start_date" name="start_date" readonly maxlength="10" /></div>
                                            </div>
                                            <div class="FormRow">
                                                <div class="label-text"><label for="end_date"><?php _e("To Date:",'icwoocommerce_textdomains'); ?></label></div>
                                                <div class="input-text"><input type="text" value="<?php echo $end_date;?>" id="end_date" name="end_date" readonly maxlength="10" /></div>
                                            </div>
                                        </div>
                                        <?php endif;?>
                                        <?php  do_action("ic_commerce_report_page_search_form_below_date_fields", $report_name, $this );?>
                                        <?php 											
                                        $category_product_fields_tabs = apply_filters('ic_commerce_report_page_category_product_fields_tabs',array("product_page"),$report_name);
                                        if(in_array($report_name,$category_product_fields_tabs)):?>
                                        <div class="form-group">
                                            <div class="FormRow FirstRow">
                                                <div class="label-text"><label for="category_id2"><?php _e("Category:",'icwoocommerce_textdomains'); ?></label></div>
                                                <div class="input-text">
                                                    <?php 
                                                        $category_data = $this->get_category_data();
                                                        $this->create_dropdown($category_data,"category_id[]","category_id2","Select All","category_id2",$category_id, 'object', true, 5);
                                                    ?>                                                        
                                                </div>                                                    
                                            </div>
                                            <div class="FormRow">
                                                <div class="label-text"><label for="product_id"><?php _e("Product Name:",'icwoocommerce_textdomains'); ?></label></div>
                                                <div class="input-text">
                                                    	<?php $id 	= '';if($product_id != "-1"){$id = $product_id;}?>
                                                    	<input id="ic_search_product" name="product_id" class="product_id ic_autocomplete" type="text" value="<?php echo $id;?>" data-id="<?php echo $id;?>" data-search_type="products" />
                                                        <?php 
                                                        //$product_data = $this->get_product_data('all');
                                                        //$this->create_dropdown($product_data,"product_id[]","product_id2","Select All","product_id2",$product_id, 'object', true, 5);
                                                    ?>
                                                </div>                                                    
                                            </div>                                                
                                        </div>                                  
                                        <?php endif;?>
                                         <?php
                                            $status_field_tabs = apply_filters('ic_commerce_report_page_status_field_tabs',array("product_page"),$report_name);
                                            if(in_array($report_name,$status_field_tabs)):
                                         ?>
                                         <div class="form-group">
                                            <div class="FormRow FirstRow">
                                                <div class="label-text"><label for="order_status_id2"><?php _e("Status:",'icwoocommerce_textdomains'); ?></label></div>
                                                <div class="input-text">
                                                    <?php
                                                        if($this->constants['post_order_status_found'] == 0 ){
                                                            $data = $this->ic_get_order_statuses_slug_id('shop_order_status');
                                                            $this->create_dropdown($data,"order_status_id[]","order_status_id","Select All","product_id",$order_status_id, 'object', true, 5);
                                                            
                                                            echo '<input type="hidden" name="order_status[]" id="order_status" value="'.$order_status.'">';
                                                        }else{
                                                            $order_statuses = $this->ic_get_order_statuses();
                                                            if(in_array('trash',$this->constants['hide_order_status'])){
                                                                unset($order_statuses['trash']);
                                                            }
                                                            $this->create_dropdown($order_statuses,"order_status[]","order_status","Select All","product_id",$order_status, 'array', true, 5);
                                                            
                                                            echo '<input type="hidden" name="order_status_id[]" id="order_status_id" value="'.$order_status_id.'">';
                                                        }
                                                    ?>
                                                </div>
                                            </div>
                                            <?php do_action("ic_commerce_report_page_search_form_right_side_status",$report_name, $this );?>
                                         </div>                                             
                                         <?php
                                            else:
                                                $not_status_field_tabs = apply_filters('ic_commerce_report_page_not_status_field_tabs',array(),$report_name);
                                                if(!in_array($report_name,$not_status_field_tabs)):
                                                    echo '<input type="hidden" name="order_status_id[]" id="order_status_id" value="'.$order_status_id.'">';
                                                    echo '<input type="hidden" name="order_status[]" id="order_status" value="'.$order_status.'">';
                                                endif;													
                                            endif;
                                         ?>
                                         
                                        <?php if($report_name == 'category_page'):
                                            $parent_categories = $this->get_sold_product_parent_category_data();
                                            if(count($parent_categories)>0){
                                            ?>   
                                            <div class="form-group">                                                
                                                <div class="FormRow FirstRow">
                                                    <div class="label-text"><label for="parent_category_id"><?php _e("Parent Category:",'icwoocommerce_textdomains'); ?></label></div>
                                                    <div class="input-text">
                                                        <?php
                                                            $this->create_dropdown($parent_categories,"parent_category_id[]","parent_category_id","Select All","parent_category_id",'-1', 'object', TRUE, 5);
                                                        ?>
                                                    </div>
                                                </div>                                          
                                            </div>
                                            <?php }?>  
                                        <?php endif;?>
                                        
                                        <?php if($report_name == 'manual_refund_detail_page'):?>
                                            <div class="form-group">                                                	
                                                <div class="FormRow">
                                                    <div class="label-text"><label for="refund_status_type"><?php _e("Refund Type:",'icwoocommerce_textdomains');?></label></div>
                                                    <div class="input-text">
                                                        <?php 
                                                            $refund_status_type = 'status_refunded';
                                                            $groups = array(
                                                                "part_refunded" 	=> __("Part Refund - Order status not refunded",'icwoocommerce_textdomains'),
                                                                "status_refunded" 	=> __("Order Status Refunded",'icwoocommerce_textdomains')
                                                            );
                                                            $this->create_dropdown($groups,"refund_status_type[]","refund_status_type","","refund_status_type",$refund_status_type, 'array', false, 5);
                                                        ?>                                                        
                                                    </div>                                                    
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <div class="FormRow checkbox FirstRow">
                                                    <div class="label-text"><label for="show_refund_note"><?php _e("Show Refund Note:",'icwoocommerce_textdomains');?></label></div>
                                                    <div style="padding-top:5px;"><input type="checkbox" id="show_refund_note" name="show_refund_note"  value="yes" <?php if($this->get_request('show_refund_note','no',true) == "yes"){ echo ' checked="checked"';}?> /></div>
                                                </div>
                                                
                                                <div class="FormRow">
                                                    <div class="label-text"><label for="group_by"><?php _e("Group By:",'icwoocommerce_textdomains');?></label></div>
                                                    <div class="input-text">
                                                        <?php 
                                                            $group_by = 'refund_id';
                                                            $groups = array(
                                                                    "refund_id" 		=> __("Refund ID",	'icwoocommerce_textdomains'),
                                                                    "order_id" 			=> __("Order ID",	'icwoocommerce_textdomains'),
                                                                    "refunded" 			=> __("Refunded",	'icwoocommerce_textdomains'),
                                                                    "daily" 			=> __("Daily",		'icwoocommerce_textdomains'),
                                                                    "monthly" 			=> __("Monthly",	'icwoocommerce_textdomains'),
                                                                    "yearly" 			=> __("Yearly",		'icwoocommerce_textdomains')
                                                            );
                                                            $this->create_dropdown($groups,"group_by[]","group_by","","group_by",$group_by, 'array', false, 5);
                                                        ?>                                                        
                                                    </div>                                                    
                                                </div>
                                            </div>
                                        <?php endif;?>
                                         <?php 
                                         if(in_array($report_name,apply_filters('ic_commerce_report_page_coupon_code_field_tabs',array("coupon_page"),$report_name))):
                                         ?>
                                            <div class="form-group">
                                                <div class="FormRow checkbox FirstRow">
                                                    <div class="label-text"><label for="coupon_codes"><?php _e("Coupon Codes:",'icwoocommerce_textdomains');?></label></div>
                                                    <div class="input-text">
                                                        <?php
                                                            $coupon_code_data = $this->get_coupon_codes();
                                                            $this->create_dropdown($coupon_code_data,"coupon_codes[]","coupon_codes","Select All","coupon_codes",'-1', 'object', true, 5);
                                                        ?>  
                                                    </div>
                                                </div>
                                                <div class="FormRow checkbox">
                                                    <div class="label-text"><label for="coupon_discount_types"><?php _e("Discount Type:",'icwoocommerce_textdomains');?></label></div>
                                                    <div class="input-text">
                                                        <?php
                                                            $coupon_discount_type_data = $this->get_coupon_types();
                                                            $this->create_dropdown($coupon_discount_type_data,"coupon_discount_types[]","coupon_discount_types","Select One","coupon_discount_types",'-1', 'array', false, 5);
                                                        ?>  
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif;?>
                                        
                                        <?php do_action("ic_commerce_report_page_search_form_bottom",$report_name, $this );?>
                                        
                                        <div class="form-group">
                                            <div class="FormRow " style="width:100%">
                                                    <?php
                                                        $hidden_fields = array();
                                                        $hidden_fields['hide_order_status'] 	=  $hide_order_status;
                                                        $hidden_fields['publish_order'] 		=  $publish_order;
                                                        $hidden_fields['report_name'] 	 		=  $report_name;
                                                        $hidden_fields['page'] 		 			=  $page;
                                                        $hidden_fields['admin_page'] 		 	=  $admin_page;
                                                        $hidden_fields['p'] 	 				=  $p;
                                                        $hidden_fields['adjacents'] 	 		=  $adjacents;
                                                        $hidden_fields['limit'] 				=  $limit;
                                                        $hidden_fields['page_title'] 			=  $page_title;
                                                        $hidden_fields['count_generated'] 		=  $count_generated;
                                                        $hidden_fields['product_status'] 		=  $product_status;
                                                        $hidden_fields['page_title'] 			=  $page_title;
                                                        $hidden_fields['billing_or_shipping'] 	=  $billing_or_shipping;
                                                        $hidden_fields['count_generated'] 		=  0;
                                                        $hidden_fields['total_row_count'] 		=  0;
                                                        $hidden_fields['do_action_type'] 		=  $this->get_request('do_action_type','report_page',true);
                                                        $hidden_fields['page_name'] 			=  $this->get_request('page_name','all_detail',true);
                                                        $hidden_fields['date_format'] 			=  $this->get_request('date_format',get_option('date_format'),true);
                                                        $hidden_fields['onload_search'] 		=  $this->get_request('onload_search',$onload_search,true);
                                                        $hidden_fields['breakup'] 				=  $this->get_request('breakup',0,true);
                                                        $hidden_fields['order_date_field_key'] 	=  $this->get_request('order_date_field_key','post_date',true);
                                                        $hidden_fields['action'] 	 			=  $this->constants['plugin_key'].'_wp_ajax_action';
                                                        
                                                                                                                    
                                                        $hidden_fields = apply_filters('ic_commerce_report_page_search_form_hidden_fields', $hidden_fields, $page, $report_name);
                                                        echo $this->create_search_form_hidden_fields($hidden_fields);
                                                    ?> 
                                                    <span class="submit_buttons">
                                                        <?php if(in_array($report_name,apply_filters('ic_commerce_report_page_reset_button_field_tabs',array('product_page','coupon_page','manual_refund_detail_page'),$report_name))):?>
                                                            <input name="ResetForm" id="ResetForm" class="onformprocess" value="<?php _e("Reset",'icwoocommerce_textdomains');?>" type="reset">
                                                        <?php endif;?>
                                                        <input name="SearchOrder" id="SearchOrder" class="onformprocess searchbtn" value="<?php _e("Search",'icwoocommerce_textdomains');?>" type="submit"> &nbsp; &nbsp; &nbsp; <span class="ajax_progress"></span>
                                                    </span>
                                            </div>
                                        </div>                                                
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <?php else:?>
                    <form action="" name="Report" id="search_order_report" method="post">dfsfdasfdsafds
                            
                            <input type="hidden" value="<?php echo $start_date;?>"			id="start_date" 		name="start_date" 		maxlength="10" />
                            <input type="hidden" value="<?php echo $end_date;?>"				id="end_date" 			name="end_date" 		maxlength="10" />                        
                            <input type="hidden" value="<?php echo $order_status_id;?>"		id="order_status_id" 	name="order_status_id" 	maxlength="1000" />
                            <input type="hidden" value="<?php echo $order_status;?>" 		id="order_status" 		name="order_status" 	maxlength="1000" />
                            <input type="hidden" name="hide_order_status" 					id="hide_order_status" 	value="<?php echo $hide_order_status;?>" />
                            
                        <div class="FormRow " style="width:100%">	                    	
                            <input type="hidden" name="publish_order" 			id="publish_order" 		value="<?php echo $publish_order;?>" />
                            <input type="hidden" name="action" 					id="action" 			value="<?php echo $this->constants['plugin_key'].'_wp_ajax_action';?>" />
                            <input type="hidden" name="limit"  					id="limit" 				value="<?php echo $limit;?>" />
                            <input type="hidden" name="p"  						id="p" 					value="<?php echo $p;?>" />
                            <input type="hidden" name="admin_page"  			id="admin_page" 		value="<?php echo $admin_page;?>" />
                            <input type="hidden" name="page"  					id="page" 				value="<?php echo $page;?>" />
                            <input type="hidden" name="adjacents"  				id="adjacents" 			value="<?php echo $adjacents;?>" />
                            <input type="hidden" name="report_name"  			id="report_name" 		value="<?php echo $report_name;?>" />
                            <input type="hidden" name="do_action_type"  		id="do_action_type" 	value="<?php echo $this->get_request('do_action_type','report_page',true);?>" /> 
                            <input type="hidden" name="page_title"  			id="page_title" 		value="<?php echo $page_title;?>" />
                            <input type="hidden" name="page_name"  				id="page_name" 			value="<?php echo $this->get_request('page_name','all_detail',true);?>" />
                            <input type="hidden" name="count_generated"  		id="count_generated" 	value="<?php echo $count_generated;?>" />
                            <input type="hidden" name="date_format" 			id="date_format" 		value="<?php echo $this->get_request('date_format',get_option('date_format'),true);?>" />
                            <input type="hidden" name="onload_search" 			id="onload_search" 		value="<?php echo $this->get_request('onload_search',$onload_search,true);?>" />
                            <input type="hidden" name="breakup" 				id="breakup" 			value="<?php echo $this->get_request('breakup',0,true);?>" />
                            <input type="hidden" name="product_status" 			id="product_status"		value="<?php echo $product_status;?>" />
                            <span class="submit_buttons">                    
                                <input name="SearchOrder" id="SearchOrder" class="onformprocess searchbtn" value="Search" type="submit" style="display:none">
                            </span>
                        </div>
                    </form>
                <?php endif;?>
                
                <div class="search_report_content hide_for_print autoload_<?php echo $onload_search;?>">
					<?php if($onload_search == "no") {echo apply_filters('ic_commerce_onload_search_text', '');}?>
                </div>
                
                <div id="search_for_print_block" class="search_for_print_block"></div>
                <?php
						$page_title				= $this->get_request('page_title',NULL,true);	

						$admin_page 			= $this->get_request('admin_page');
						
						$admin_page_url 		= $this->constants['admin_page_url'];
						$mngpg 					= $admin_page_url.'?page='.$admin_page ;
						$billing_information 	= $this->get_setting('billing_information',$this->constants['plugin_options'], 0);
						$shipping_information 	= $this->get_setting('shipping_information',$this->constants['plugin_options'], 0);
						$logo_image 			= $this->get_setting('logo_image',$this->constants['plugin_options'], '');
						$report_title 			= $this->get_setting('report_title',$this->constants['plugin_options'], '');
						$company_name 			= $this->get_setting('company_name',$this->constants['plugin_options'], '');
						$page_title				= $this->get_request('page_title',NULL,true);
						
						$set_report_title		= $report_title;							
						if($page_title) $page_title = " (".$page_title.")";							
						$report_title = $report_title.$page_title;
					?>
					<div id="export_csv_popup" class="popup_box">
						<h4><?php _e("Export to CSV - Additional Information",'icwoocommerce_textdomains');?></h4>
						<a class="popup_close" title="Close popup"></a>
						<div class="popup_content">                        	
						<form id="<?php echo $admin_page ;?>_csv_popup_form" class="<?php echo $admin_page ;?>_csv_popup_form" action="<?php echo $mngpg;?>" method="post">
							<div class="popup_csv_hidden_fields popup_hidden_fields"></div>
							
							 <table class="popup_form_table">
								<tr>
									<th><label for="billing_information"><?php _e("Billing Information:",'icwoocommerce_textdomains');?></label></th>
									<td><input id="billing_information" name="billing_information" value="1" type="checkbox"<?php if($billing_information == 1) echo ' checked="checked"';?> /></td>
								</tr>
								<tr>
									<th><label for="shipping_information"><?php _e("Shipping Information:",'icwoocommerce_textdomains');?></label></th>
									<td><input id="shipping_information" name="shipping_information" value="1" type="checkbox"<?php if($shipping_information == 1) echo ' checked="checked"';?>></td>
								</tr>
							   <?php do_action('ic_commerce_export_csv_popup_extra_option',$page);?>
								<tr>
									<td colspan="2"><input type="submit" name="<?php echo $admin_page ;?>_export_csv" class="onformprocess button_popup_close" value="<?php _e("Export to CSV",'icwoocommerce_textdomains');?>" /></td>
								</tr>                                
							</table>
						</form>
						<div class="clear"></div>
						</div>
					</div>
                    
                    <div id="export_print_popup" class="popup_box export_pdf_popup export_print_popup">
                            <a class="popup_close" title="Close popup"></a>
                            <h4>Export to PDF</h4>
                            <div class="popup_content">
                            <form id="<?php echo $admin_page ;?>_print_popup_form" class="<?php echo $admin_page ;?>_pdf_popup_form" action="<?php echo $mngpg;?>" method="post">
                                <div class="popup_print_hidden_fields popup_hidden_fields2"></div>
                                 <table class="form-table">
                                    <tr>
                                        <th><label for="company_name_print"><?php _e("Company Name:",'icwoocommerce_textdomains');?></label></th>
                                        <td><input id="company_name_print" name="company_name" value="<?php echo $company_name;?>" type="text" class="textbox"></td>
                                    </tr>
                                    <tr>
                                        <th><label for="report_title_print"><?php _e("Report Title:",'icwoocommerce_textdomains');?></label></th>
                                        <td><input id="report_title_print" name="report_title" value="<?php echo $report_title;?>" data-report_title="<?php echo $set_report_title;?>" type="text" class="textbox"></td>
                                    </tr>
                                    <?php if($logo_image):?>
                                    <tr>
                                        <th><label for="display_logo_print"><?php _e("Print Logo:",'icwoocommerce_textdomains');?></label></th>
                                        <td class="inputfield"><input id="display_logo_print" name="display_logo" value="1" type="checkbox"<?php if($logo_image) echo ' checked="checked"';?>></td>
                                    </tr>
                                    <?php endif;?>
                                     <tr>
                                        <th><label for="display_date_print"><?php _e("Print Date:",'icwoocommerce_textdomains');?></label></th>
                                        <td class="inputfield"><input id="display_date_print" name="display_date" value="1" type="checkbox" checked="checked"></td>
                                    </tr>
                                    <?php do_action('ic_commerce_export_print_popup_extra_option',$page);?>
                                    <tr>
                                        <td colspan="2"><input type="button" name="<?php echo $admin_page ;?>_export_print" class="onformprocess button_popup_close search_for_print" value="<?php _e("Print",'icwoocommerce_textdomains');?>" data-form="popup"  data-do_action_type="all_report_page_for_print" /></td>
                                    </tr>                                
                                </table>
                                <input type="hidden" name="display_center" value="1" />
                            </form>
                            <div class="clear"></div>
                            </div>
                        </div>
                        
					<div id="export_pdf_popup" class="popup_box export_pdf_popup">
                            <a class="popup_close" title="Close popup"></a>
                            <h4><?php _e("Export to PDF",'icwoocommerce_textdomains');?></h4>
                            <div class="popup_content">
                            <form id="<?php echo $admin_page ;?>_pdf_popup_form" class="<?php echo $admin_page ;?>_pdf_popup_form" action="<?php echo $mngpg;?>" method="post">
                                <div class="popup_pdf_hidden_fields popup_hidden_fields"></div>
                                 <table class="form-table">
                                    <tr>
                                        <th><label for="company_name_pdf"><?php _e("Company Name:",'icwoocommerce_textdomains');?></label></th>
                                        <td><input id="company_name_pdf" name="company_name" value="<?php echo $company_name;?>" type="text" class="textbox"></td>
                                    </tr>
                                    <tr>
                                        <th><label for="report_title_pdf"><?php _e("Report Title:",'icwoocommerce_textdomains');?></label></th>
                                        <td><input id="report_title_pdf" name="report_title" value="<?php echo $report_title;?>" data-report_title="<?php echo $set_report_title;?>" type="text" class="textbox"></td>
                                    </tr>
                                    <?php if($logo_image):?>
                                    <tr>
                                        <th><label for="display_logo_pdf"><?php _e("Show Logo:",'icwoocommerce_textdomains');?></label></th>
                                        <td class="inputfield"><input id="display_logo_pdf" name="display_logo" value="1" type="checkbox"<?php if($logo_image) echo ' checked="checked"';?>></td>
                                    </tr>
                                    <?php endif;?>
                                     <tr>
                                        <th><label for="display_date_pdf"><?php _e("Show Date:",'icwoocommerce_textdomains');?></label></th>
                                        <td class="inputfield"><input id="display_date_pdf" name="display_date" value="1" type="checkbox" checked="checked"></td>
                                    </tr>
                                    <?php do_action('ic_commerce_export_pdf_popup_extra_option',$page);?>
                                    <tr>
									<th><label for="orientation_portrait_pdf"><?php _e("PDF Orientation:",'icwoocommerce_textdomains');?></label></th>
									<td class="inputfield">
                                    <label for="orientation_portrait_pdf"><input id="orientation_portrait_pdf" name="orientation_pdf" value="portrait" type="radio"> <?php _e("Portrait",'icwoocommerce_textdomains');?></label>
                                    <label for="orientation_landscape_pdf"><input id="orientation_landscape_pdf" name="orientation_pdf" value="landscape" type="radio" checked="checked"> <?php _e("Landscape",'icwoocommerce_textdomains');?></label>
                                    
                                    </td>
								</tr>
                                	<tr>
									<th><label for="paper_size_pdf"><?php _e("Paper Size:",'icwoocommerce_textdomains');?></label></th>
									<td class="inputfield">
                                    <?php
										$paper_sizes = $this->get_pdf_paper_size();
										$this->create_dropdown($paper_sizes,"paper_size","paper_size2","","paper_size2",'letter', 'array', false, 5);
									?>                                    
								</tr>
                                    <tr>
                                        <td colspan="2">                                                                                
                                        <input type="submit" name="<?php echo $admin_page ;?>_export_pdf" class="onformprocess button_popup_close" value="<?php _e("Export to PDF:",'icwoocommerce_textdomains');?>" /></td>
                                    </tr>                                
                                </table>
                                <input type="hidden" name="display_center" value="center_header" />
                                <input type="hidden" name="pdf_keywords" value="" />
                                <input type="hidden" name="pdf_description" value="" />
                            </form>
                            <div class="clear"></div>
                            </div>
                        </div>
					
					</div>
					<div class="popup_mask"></div>
                    <?php 	
					do_action("ic_commerce_report_page_footer_area",$page);
					echo '<style type="text/css">';
					$report_name	= $this->get_request('report_name','');
					$columns 		= $this->get_columns($report_name);
					echo $this->get_pdf_style_align($columns,'right',' ','.iccommercepluginwrap ', $report_name);
					echo '</style>';
							
		}
		
		/*
			* Function Name ic_commerce_report_ajax_request
			*
			* AJAX Request
			*		 
		*/
		function ic_commerce_report_ajax_request($type = 'limit_row'){			
			$report_name 	= $this->get_request('report_name','product_page',true);
			$columns 		= $this->get_columns($report_name);
			$total_columns	= $this->result_columns($report_name);			
			$this->get_grid($type, $report_name,$columns,$total_columns);
		}
		
		
		/*
			* Function Name get_columns
			*
			* Returns columns of all reports
			*
			* @param string $report_name
			*
			* @return array|object $columns
			*		 
		*/
		function get_columns($report_name = 'product_page'){
			$grid_column 	= $this->get_grid_columns();
			$columns 		=  $grid_column->grid_columns_all_reports($report_name);			
			return $columns;
		}
		
		/*
			* Function Name result_columns
			*
			* Returns total columns of all reports
			*
			* @param string $report_name
			*
			* @return array|object $total_columns
			*		 
		*/
		function result_columns($report_name = ''){
			$grid_column 	= $this->get_grid_columns();
			$total_columns 	= $grid_column->result_columns_all_reports($report_name);
			return $total_columns;
		}
		
		
		
		/*
			* Function Name get_items
			*
			* Returns resulting rows of all reports
			*
			* @param string $type
			*
			* @param string $report_name
			*
			* @param array $columns
			*
			* @param array $total_columns
			*
			* @return array|object $rows
			*		 
		*/
		function get_items($type = "limit_row",$report_name, $columns = array(), $total_columns = array()){
			$report_name = $this->get_request('report_name','product_page',true);
			$rows = array();
			switch ($report_name ) {
				case "product_page":
					$rows 		= $this->ic_commerce_custom_all_product_query($type, $columns, $report_name, $total_columns);
					break;
				case "customer_page":
					$rows 		= $this->ic_commerce_custom_all_customer_query($type, $columns, $report_name, $total_columns);					
					break;				
				case "payment_gateway_page":
					$rows 		= $this->ic_commerce_custom_all_payment_gateway_query($type, $columns, $report_name, $total_columns);
					break;
				case "order_status":
					$rows 		= $this->ic_commerce_custom_all_order_status_query($type, $columns, $report_name, $total_columns);
					break;
				case "recent_order":
					$rows 		= $this->ic_commerce_custom_all_recent_order_query($type, $columns, $report_name, $total_columns);
					break;
				case "coupon_page":
					$rows 		= $this->ic_commerce_custom_all_coupon_query($type, $columns, $report_name, $total_columns);
					break;
				case "billing_country_page":
					$rows 		= $this->ic_commerce_custom_all_billing_country_query($type, $columns, $report_name, $total_columns);
					break;
				case "advance_product_page":
					$rows 		= $this->ic_commerce_custom_all_product_advance_query($type, $columns, $report_name, $total_columns);
					break;
				case "manual_refund_detail_page":
					$rows 		= $this->ic_commerce_custom_all_manual_refund_detail_query($type, $columns, $report_name, $total_columns);
					break;
				default:
					$rows 		= apply_filters('ic_commerce_report_page_default_items',$rows, $type, $columns, $report_name, $this, $total_columns);
					break;
				
			}
						
			$rows 		= apply_filters('ic_commerce_report_page_items',$rows, $type, $columns, $report_name, $this, $total_columns);
			
			return $rows;	
		}
		
		
		/*
			* Function Name create_grid_items
			*
			* Returns created grid items for Recent Order, Customer Report, Product Report, Billing Country and manual refund detail page.
			*
			* @param array $order_items
			*
			* @param array $columns
			*
			* @param string $report_name
			*
			* @param array $request
			*
			* @param string $type
			*
			* @return array|object $order_items
			*
		*/
		function create_grid_items($order_items = array(), $columns = array(), $report_name = "", $request = array(), $type = "limit_row"){
			switch($report_name){
				case "recent_order":
				case "customer_page":
				case "billing_country_page":					
				case "product_page":
				case "manual_refund_detail_page":
					$grid_object		= $this->get_grid_object();
					$order_items		= $grid_object->create_grid_items($columns,$order_items, $report_name);
					break;
				default:
					$order_items 		= apply_filters('ic_commerce_report_page_create_grid_items',$order_items, $columns, $request, $type);
					break;
				
			}
			
			return $order_items;
		}
		
		/*
			* Function Name get_detail_page_url
			*
			* Returns Admin URL
			*
			* @param string $report_name
			*
			* @param array $request
			*
			* @return array|object $admin_url
			*
		*/
		function get_detail_page_url($report_name = '', $request = array()){			
			$admin_url 	= admin_url("admin.php?page=".$this->constants['plugin_key']."_details_page");			
			switch ($report_name ) {
				case "product_page":
					$form_input_fields = array('start_date','end_date','order_status');
					break;
				case "customer_page":
				case "billing_country_page":
				case "billing_state_page":
				case "payment_gateway_page":
				case "order_status":
				case "recent_order":
				case "tax_page":
				case "customer_buy_products_page":
					$form_input_fields = array('start_date','end_date');
					break;
				case "manual_refund_detail_page":

					$form_input_fields = array('start_date','end_date');
					break;	
				case "coupon_page":

					$form_input_fields = array('start_date','end_date');
					break;
				default:
					$form_input_fields = array();					
					break;
			}
			
			$form_input_fields = apply_filters('ic_commerce_report_page_form_inputs', $form_input_fields, $report_name, $request);
			
			$paras = array();
			foreach($form_input_fields as $key => $field_name){
				if(!empty($request[$field_name]) and $request[$field_name] != '-1'){
					 $paras[] = $field_name ."=".$request[$field_name];
				}
			}
			
			if(count($paras)>0){
				$admin_url .= "&".implode("&",$paras);
			}
			
			return $admin_url;
		}
		
		
		/*
			* Function Name get_grid
			*
			* Returns resulting columns for all reports
			*
			* @param string $type
			*
			* @param string $report_name
			*
			* @param array $columns
			*
			* @param array $total_columns
			*
		*/
		function get_grid($type = 'limit_row', $report_name = "", $columns = array(), $total_columns = array()){
				global $wpdb;
				$columns 								= $this->get_columns($report_name);
				$order_items 							= $this->get_items($type,$report_name,$columns,$total_columns);
				
				if(count($order_items) > 0):
							$report_name 				= $this->get_request('report_name','product_page',true);
							$Totalorder_count 			= 0;
							$TotalAmount 				= 0;
							$TotalShipping 				= 0;
							$request 					= $this->get_all_request();extract($request);
							
							$summary 					= $this->get_items('total_row',$report_name,$columns,$total_columns);
							$total_row_amount			= isset($summary['total_row_amount']) ? $summary['total_row_amount'] : 0;
							$total_row_count			= isset($summary['total_row_count']) ? $summary['total_row_count'] : 0;
							$zero						= $this->price(0);
							$end_date 					= $this->today;
							$request['plugin_key']		= $this->constants['plugin_key'];
							
							$user_url					= admin_url("user-edit.php")."?user_id=";
							
							$admin_url 					= $this->get_detail_page_url($report_name, $request);
							$product_url				= admin_url("post.php?action=edit");
							
							$link_para_order_status 	= "";
							
							if($order_status_id != "-1" and strlen($order_status_id)>2){
								$link_para_order_status = "&order_status_id=".$order_status_id;
							}else if($order_status != "-1" and strlen($order_status)>2){
								$link_para_order_status = "&order_status=".$order_status;
							}
							
							$columns					= apply_filters("ic_commerce_report_page_grid_columns",$columns, $report_name);
							$order_items				= apply_filters("ic_commerce_report_page_data_grid_items",$order_items,$columns, $report_name, $request, $type, $zero);
							
							$order_items				= $this->create_grid_items($order_items, $columns, $report_name, $request, $type);
							
							$order_items				= apply_filters("ic_commerce_report_page_data_grid_items_create_grid_items",$order_items,$columns, $report_name, $request, $type, $zero);
							
							if(count($order_items)<=0){
								_e("Some thing going wrong on creating result data in \"add_filter\" or \"create_grid_items\"", "icwoocommerce_textdomains");
								return;
							}
							
							$price_columns				= apply_filters("ic_commerce_report_page_grid_price_columns",array(), $report_name);
							$date_format				= get_option( 'date_format' );
							$this->print_header($type);	
						?>
                		<?php if($type != 'all_row'):?>
                        <div class="top_buttons"><?php $this->export_to_csv_button('top',$summary);?><div class="clearfix"></div></div>
                        <?php else: $this->back_print_botton('top',$summary)?>
						<?php endif;?>
						<table style="width:100%" class="widefat widefat_normal_table" cellpadding="0" cellspacing="0">
							<thead>
								<tr class="first">
                                	<?php 
										$cells_status = array();
										$output = "";
										foreach($columns as $key => $value):
											$td_class = $key;
											$td_width = "";
											switch($key):
												case "order_shipping":
												case "order_shipping_tax":
												case "order_tax":
												case "gross_amount":
												case "order_discount":
												case "cart_discount":
												case "total_discount":
												case "total_tax":
												case "order_total":
												case "item_count":
												case "transaction_id":
												case "order_item_count":
												case "customer_id":
												case "quantity":
												case "product_stock":
												case "total_amount":
												case "order_count":
												case "coupon_amount":
												case "Count":
												case "coupon_count":
												case "refund_amount":
												case "refund_count":
												case "order_refund_amount":
												case "part_order_refund_amount":												
												case "quantity":
													$td_class .= " amount";												
													break;							
												default;
													break;
											endswitch;
											$th_value 			= $value;
											$output 			.= "\n\t<th class=\"{$td_class}\">{$th_value}</th>";											
										endforeach;
										echo $output ;
										?>
								</tr>
							</thead>
							<tbody>
								<?php					
									foreach ( $order_items as $key => $order_item ) {
										$TotalShipping 	= isset($order_item->order_shipping) ? $TotalShipping + $order_item->order_shipping : 0;
										$TotalAmount 	= isset($order_item->total_amount) ? ($TotalAmount + $order_item->total_amount) : 0;
										$Totalorder_count++;									
										if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";};
										?>
										<tr class="<?php echo $alternate."row_".$key;?>">
											<?php
												foreach($columns as $key => $value):
													$td_class = $key;
													$td_style = '';
													$td_value = "";
													switch($key):
														
														case "product_stock":
															$order_item_id = isset($order_item->order_item_id) ? $order_item->order_item_id : 0;
															$td_value = $this->get_stock_($order_item_id, $order_item->product_id);
															$td_class .= " amount";
															break;
														case "product_sku":
															$order_item_id = isset($order_item->order_item_id) ? $order_item->order_item_id : 0;
															$td_value = $this->get_sku($order_item_id, $order_item->product_id);
															break;													
														case "sku":
															$td_value = $this->get_stock($order_item->$key);
															break;
														case "stock":													
															$td_value = $this->get_stock($order_item->$key);
															$td_class .= " amount";														
															break;
														case "product_name":													
															$td_value = " <a href=\"{$admin_url}&detail_view=yes&product_id={$order_item->product_id}\" target=\"_blank\">{$order_item->product_name}</a>";														
															break;
														case "coupon_code":
															$td_value = '<a href="'.$admin_url."&coupon_code=".$order_item->coupon_code.$link_para_order_status.'&detail_view=no" target="'.$order_item->coupon_code.'_blank">' . $order_item->coupon_code  . '</a>';
															break;
														case "quantity":														
															$td_value = $order_item->quantity;	
															$td_class .= " amount";													
															break;
														case "order_id":
															$td_value = '<a href="'.$admin_url."&order_id=".$order_item->order_id.$link_para_order_status.'&detail_view=no" target="'.$order_item->order_id.'_blank">' . $order_item->order_id  . '</a>';
															break;														
														case "billing_name":
															$td_value = ucwords(stripslashes_deep($order_item->billing_name));
															break;
														case "billing_email":
															$td_value = $this->emailLlink($order_item->billing_email,false);
															break;													
														case "status":
															$td_value = '<span class="order-status order-status-'.sanitize_title($order_item->status_id).'">'.ucwords(__($order_item->status, 'icwoocommerce_textdomains')).'</span>';
															break;
														case 'billing_country_name':
														case 'shipping_country_name':														
														case 'billing_country':
														case 'shipping_country':
															$country      	= $this->get_wc_countries();														
															$td_value = isset($country->countries[$order_item->$key]) ? $country->countries[$order_item->$key]: $order_item->$key;
															break;
														case "item_count":
														case "transaction_id":
														case "order_item_count":														
														case "item_count":
														case "order_count":
														case "refund_count":
														case "Count":
														case "coupon_count":
														case "_order_count":
															$td_value = isset($order_item->$key) ? $order_item->$key : '';
															$td_class .= " amount";
															break;
														
														case "order_date":
														case "post_date":
														case "post_modified":
														case "refund_date":
														case "group_date":
														case "start_date":
														case "end_date":
														case "first_date":
														case "last_date":														
														case "completed_date":
														case "delivery_date":
															$order_date = isset($order_item->$key) 	? trim($order_item->$key) 					: '';
															$td_value 	= !empty($order_date) 		? date($date_format,strtotime($order_date)) : '';
															break;
														case "order_tax_rate":
															$td_value = isset($order_item->$key) ? $order_item->$key : 0;
															$td_value = sprintf("%.2f%%",$td_value);
															$td_class .= " amount";
															break;
														case "order_shipping":
														case "order_shipping_tax":
														case "order_tax":
														case "total_tax":
														case "gross_amount":
														case "order_discount":
														case "cart_discount":
														case "total_discount":
														case "order_total":
														case "total_amount":
														
														case "product_rate_exculude_tax":
														case "product_vat":
														case "product_vat_total":
														case "product_vat_par_item":
														case "product_shipping":
														case "total_price_exculude_tax":
														
														case "cost_of_good_amount":
														case "total_cost_good_amount":
														case "margin_profit_amount":
														case "sales_rate_amount":
														
														
														case "_order_shipping_amount":
														case "_order_amount":
														case "order_total_amount":
														case "_shipping_tax_amount":
														case "_order_tax":
														case "_total_tax":
														case "order_refund_amount":
														case "part_order_refund_amount":
														case "product_rate":
														case "min_product_price":
														case "max_product_price":
														
														case "product_sold_rate":
														case "product_total":
														case "product_subtotal":
														case "product_discount":														
															$td_value = isset($order_item->$key) ? $order_item->$key : 0;
															$td_value = $td_value == 0 ? $zero : $this->price($td_value);
															$td_class .= " amount";
															break;
														case "coupon_amount":
															$td_value = isset($order_item->$key) ? $order_item->$key : 0;
															$discount_type = isset($order_item->discount_type) ? $order_item->discount_type : '';
															switch($discount_type){
																case "percent":
																case "percent_product":
																	$td_value = sprintf("%.2f%%",$td_value);
																	break;
																case "fixed_cart":
																case "fixed_product":
																default:
																	$td_value = $td_value == 0 ? $zero : $this->price($td_value);
																	break;
															}
															$td_class .= " amount";
															break;
														case "order_status":
														case "order_status_name":
														case "refund_status":
															$td_value = isset($order_item->$key) ? $order_item->$key : '';
															$td_value = '<span class="order-status order-status-'.sanitize_title($td_value).'">'.ucwords(__($td_value, 'icwoocommerce_textdomains')).'</span>';
															break;
														case "product_edit":
															$td_class .= " amount";
															$td_value = "<a href=\"{$product_url}&post={$order_item->product_id}\" target=\"_blank\">Edit</a>";
															break;
															
														case "billing_first_name2":														
															$td_value = isset($order_item->$key) ? $order_item->$key : '';
															
															break;	
														
														case "user_name":														
															$td_value = isset($order_item->$key) ? $order_item->$key : '';
															
															break;
														case "profit_percentage":
															$td_class .= " amount";
															$td_value = isset($order_item->$key) ? $order_item->$key : 0;
															$td_class .= $td_value > 0 ? " up_class" : ($td_value < 0 ? " down_class" : " equal_class");
															
															$td_value = sprintf("%.2f%%",$td_value);
															break;
														default:
															if(in_array($key, $price_columns)){
																$td_value = isset($order_item->$key) ? $order_item->$key : 0;
																$td_value = $td_value == 0 ? $zero : $this->price($td_value);
																$td_class .= " amount";
															}else{
																$td_value = isset($order_item->$key) ? $order_item->$key : '';
															}															
															break;
													endswitch;
													$td_content = "<td class=\"{$td_class}\"{$td_style}>{$td_value}</td>\n";
													echo $td_content;
												endforeach;                                        	
											?>
										</tr>
										<?php 
									}
								?>
							</tbody>          
						</table>
						 <?php 
						 	if($type != 'all_row') $this->total_count($Totalorder_count, $TotalAmount, $summary, $TotalShipping);  else $this->back_print_botton('bottom',$summary);
                        	echo $this->result_grid($report_name,$summary,$zero,$total_columns, $price_columns);
							echo apply_filters("ic_commerce_report_page_bottom_of_report",'',$columns, $total_columns, $price_columns);//
							
						 ?>
				<?php else:?>        
						<div class="order_not_found"><?php _e('No Orders found','icwoocommerce_textdomains'); ?></div>
				<?php endif;?>
			<?php
		}
		
		var $items_query = NULL;
		
		/*
			* Function Name ic_commerce_custom_all_product_query
			*
			* Returns all products list
			*
			* @param string $type
			*
			* @param array $columns
			*
			* @param string $report_name
			*
			* @return array|object $order_items
			*			
		*/
		function ic_commerce_custom_all_product_query($type = 'limit_row', $columns = array(), $report_name = ""){
			global $wpdb;			
			
			if(!isset($this->items_query)){
				$request 					= $this->get_all_request();extract($request);				
				$order_status				= $this->get_string_multi_request('order_status',$order_status, "-1");
				$hide_order_status			= $this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				$category_product_id_string = $this->get_products_list_in_category($category_id,$product_id);
				$category_id 				= "-1";
				
				$sql = " SELECT ";
				
				$sql .= "							
							woocommerce_order_items.order_item_name		AS 'product_name'
							,woocommerce_order_items.order_item_id		AS order_item_id
							,woocommerce_order_itemmeta_product_id.meta_value		AS product_id							
							,woocommerce_order_itemmeta_variation_id.meta_value		AS variation_id							
							,DATE(shop_order.post_date)					AS post_date
							";

				if($category_id  && $category_id != "-1") {
					
					$sql .= "
							,terms.term_id								AS term_id
							,terms.name									AS term_name
							,term_taxonomy.parent						AS term_parent
						";						
				}
								
				
				$sql .= " ,SUM(woocommerce_order_itemmeta.meta_value) AS 'quantity' ,SUM(woocommerce_order_itemmeta6.meta_value) AS 'total_amount'";
							
				$sql = apply_filters("ic_commerce_report_page_select_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= "
							FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items						
							LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
							LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta6 ON woocommerce_order_itemmeta6.order_item_id=woocommerce_order_items.order_item_id
							LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_product_id ON woocommerce_order_itemmeta_product_id.order_item_id=woocommerce_order_items.order_item_id						
							LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_variation_id ON woocommerce_order_itemmeta_variation_id.order_item_id=woocommerce_order_items.order_item_id						
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
							LEFT JOIN  {$wpdb->posts} as shop_order ON shop_order.id=woocommerce_order_items.order_id";
				
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
							
				$sql .= "
							WHERE 1*1
							AND woocommerce_order_itemmeta.meta_key	= '_qty'
							AND woocommerce_order_itemmeta6.meta_key	= '_line_total' 
							AND woocommerce_order_itemmeta_product_id.meta_key 	= '_product_id'						
							AND woocommerce_order_itemmeta_variation_id.meta_key 	= '_variation_id'						
							AND shop_order.post_type					= 'shop_order'
							";
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
				
				if($category_product_id_string  && $category_product_id_string != "-1") $sql .= " AND woocommerce_order_itemmeta_product_id.meta_value IN (".$category_product_id_string .")";	
				
				if($order_status_id  && $order_status_id != "-1") 
					$sql .= " 
							AND terms2.term_id IN (".$order_status_id .")";
							
				
				if(strlen($publish_order)>0 && $publish_order != "-1" && $publish_order != "no" && $publish_order != "all"){
					$in_post_status		= str_replace(",","','",$publish_order);
					$sql .= " AND  shop_order.post_status IN ('{$in_post_status}')";
				}
				
				if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND shop_order.post_status IN (".$order_status.")";
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND shop_order.post_status NOT IN (".$hide_order_status.")";
				
				$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$group_sql = " GROUP BY  woocommerce_order_itemmeta_product_id.meta_value";		
				
				$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);	
				
				$order_sql = " ORDER BY total_amount DESC";
				
				$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);	
				
				$this->items_query = $sql;
			}else{
				$sql = $this->items_query;
			}
			
			$order_items = $this->get_query_items($type,$sql);
			
			if($type != "total_row"){
				foreach($order_items as $key => $order_item){
					if($order_item->variation_id > 0){
						$order_items[$key]->product_name = get_the_title($order_item->product_id);
					}
					$order_items[$key]->product__sku = get_post_meta($order_item->product_id,'_sku',true);
				}
			}
			
			return $order_items;
		}
				
		
		/*
			* Function Name get_stock
			*
			* Returns Stocks
			*
			* @param string $stock_count
			*
			* @return string $stock_count
			*			
		*/
		function get_stock($stock_count){
			if(strlen($stock_count)<=0){
				return  __("Not Set",'icwoocommerce_textdomains');
			}else{
				return $stock_count;
			}
		}
		
		
		/*
			* Function Name ic_commerce_custom_all_customer_query
			*
			* Returns all customers list
			*
			* @param string $type
			*
			* @param array $columns
			*
			* @param string $report_name
			*
			* @return array|object $order_items
			*			
		*/
		function ic_commerce_custom_all_customer_query($type = 'limit_row', $columns = array(), $report_name = "")
		{
				global $wpdb;
				if(!isset($this->items_query)){
					$request = $this->get_all_request();extract($request);
					
					$order_status	= $this->get_string_multi_request('order_status',$order_status, "-1");
					$hide_order_status	= $this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
											
					$sql = " SELECT ";
					$sql .= " SUM(ROUND(order_total.meta_value,2)) 	AS 'total_amount' ";					
					$sql .= " ,COUNT(billing_email.meta_value) 		AS 'order_count'";
					$sql .= " ,billing_email.meta_value 			AS 'billing_email'";
					$sql .= " ,billing_first_name.meta_value 		AS 'billing_first_name'";					
					$sql .= " ,billing_last_name.meta_value 		AS  billing_last_name";
					$sql .= " ,customer_user.meta_value 			AS  customer_id";
					$sql .= " ,customer_user.meta_value 			AS  customer_user";
					
					$sql .= " ,CONCAT(billing_first_name.meta_value, ' ',billing_last_name.meta_value) AS billing_name";
					
					$sql = apply_filters("ic_commerce_report_page_select_query", $sql, $request, $type, $page, $report_name, $columns);
					
					$sql .= " FROM {$wpdb->posts} as posts";
					$sql .= " LEFT JOIN  {$wpdb->postmeta} as order_total ON order_total.post_id=posts.ID";
					
					$sql .= " LEFT JOIN  {$wpdb->postmeta} as billing_email ON billing_email.post_id=posts.ID";					
					$sql .= " LEFT JOIN  {$wpdb->postmeta} as customer_user ON customer_user.post_id=posts.ID";					
					
					$sql .= " LEFT JOIN  {$wpdb->postmeta} as billing_first_name ON billing_first_name.post_id=posts.ID";					
					$sql .= " LEFT JOIN  {$wpdb->postmeta} as billing_last_name ON billing_last_name.post_id=posts.ID";
					
					
					if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
							$sql .= " 
							LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
							LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
					
					
					$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
					
					
					$sql .= " WHERE  1*1";
					$sql .= " AND posts.post_type='shop_order'";
					$sql .= " AND order_total.meta_key='_order_total'";
					
					$sql .= " AND billing_email.meta_key='_billing_email'";					
					$sql .= " AND customer_user.meta_key='_customer_user'";
					
					$sql .= " AND billing_first_name.meta_key='_billing_first_name'";					
					$sql .= " AND billing_last_name.meta_key='_billing_last_name'";
					
					
					if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
						$sql .= " AND  term_taxonomy.term_id IN ({$order_status_id})";
					}
					
					
					$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
					if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
						if ($start_date != NULL &&  $end_date !=NULL){
							$sql .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
						}
					}
					
					if(strlen($publish_order)>0 && $publish_order != "-1" && $publish_order != "no" && $publish_order != "all"){
						$in_post_status		= str_replace(",","','",$publish_order);
						$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
					}
					
					if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND posts.post_status IN (".$order_status.")";
					if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
										
					$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
					$group_sql = " GROUP BY  billing_email.meta_value ";
					
					$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);	
					
					$order_sql = " Order By total_amount DESC";
					
					$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);	
					
					$this->items_query = $sql;
				}else{
					$sql = $this->items_query;
				}
				
				$order_items = $this->get_query_items($type,$sql);
				
				if($type != 'total_row'){
					$billing_company = $this->get_billing_company();
					foreach($order_items as $key => $order_item){
						$billing_email = $order_item->billing_email;						
						$order_items[$key]->billing_company =  isset($billing_company[$billing_email]) ? $billing_company[$billing_email] : '';
					}
				};
				return $order_items;
		}
		
		function get_billing_company(){
			global $wpdb;
			
			$request = $this->get_all_request();extract($request);
			
			$sql = " SELECT billing_email.meta_value AS billing_email, ";
			$sql .= " billing_company.meta_value AS billing_company ";
			$sql .= " FROM  {$wpdb->posts} AS shop_order";
			$sql .= " LEFT JOIN  {$wpdb->postmeta} as billing_company ON billing_company.post_id=shop_order.ID";
			$sql .= " LEFT JOIN  {$wpdb->postmeta} as billing_email ON billing_email.post_id=shop_order.ID";
			$sql .= " WHERE 1*1";
			$sql .= " AND shop_order.post_type = 'shop_order'";
			$sql .= " AND billing_company.meta_key = '_billing_company'";
			$sql .= " AND billing_email.meta_key='_billing_email'";
			
			
			$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
			if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
				if ($start_date != NULL &&  $end_date !=NULL){
					$sql .= " AND DATE(shop_order.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
				}
			}
			
			$sql .= " GROUP BY billing_email";
			
			$items = $wpdb->get_results($sql);
			$billing_company = array();
			foreach($items as $key => $item){
				$billing_company[$item->billing_email] = $item->billing_company;
			}
			
			return $billing_company;
		}
		
		
		/*
			* Function Name ic_commerce_custom_all_billing_country_query
			*
			* Returns all billing countries list
			*
			* @param string $type
			*
			* @param array $columns
			*
			* @param string $report_name
			*
			* @return array|object $order_items
			*			
		*/
		function ic_commerce_custom_all_billing_country_query($type = 'limit_row', $columns = array(), $report_name = "")
		{
			global $wpdb;
			if(!isset($this->items_query)){
				$request = $this->get_all_request();extract($request);
				
				$order_status			= $this->get_string_multi_request('order_status',$order_status, "-1");
				$hide_order_status		= $this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				$billing_or_shipping	= $this->get_setting('billing_or_shipping',$this->constants['plugin_options'], 'billing');
				
				$sql = "
				SELECT SUM(postmeta1.meta_value) AS 'total_amount' 
				,postmeta2.meta_value AS 'billing_country'
				,Count(*) AS 'order_count'";
				$sql = apply_filters("ic_commerce_report_page_select_query", $sql, $request, $type, $page, $report_name, $columns);				
				$sql .= "
				FROM {$wpdb->posts} as posts
				LEFT JOIN  {$wpdb->postmeta} as postmeta1 ON postmeta1.post_id=posts.ID
				LEFT JOIN  {$wpdb->postmeta} as postmeta2 ON postmeta2.post_id=posts.ID";
				if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
				
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= "
				WHERE
				posts.post_type			=	'shop_order'  
				AND postmeta1.meta_key	=	'_order_total' 
				AND postmeta2.meta_key	=	'_{$billing_or_shipping}_country'";
				
				if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
					$sql .= " AND  term_taxonomy.term_id IN ({$order_status_id})";
				}
				
				$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
				if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}
				}
					
					
				if(strlen($publish_order)>0 && $publish_order != "-1" && $publish_order != "no" && $publish_order != "all"){
					$in_post_status		= str_replace(",","','",$publish_order);
					$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
				}
				
				if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND posts.post_status IN (".$order_status.")";
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
				
				$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$group_sql = " GROUP BY  postmeta2.meta_value  ";
				
				$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);	
				
				$order_sql = " Order By total_amount DESC ";
				
				$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);	
				
				$this->items_query = $sql;
				
			}else{
				$sql = $this->items_query;
			}
				
			$order_items = $this->get_query_items($type,$sql);
			return $order_items;
		}
		
		
		/*
			* Function Name ic_commerce_custom_all_payment_gateway_query
			*
			* Returns all payment gateways list
			*
			* @param string $type
			*
			* @param array $columns
			*
			* @param string $report_name
			*
			* @return array|object $order_items
			*			
		*/
		function ic_commerce_custom_all_payment_gateway_query($type = 'limit_row', $columns = array(), $report_name = "")
		{
		 	
			global $wpdb;
			if(!isset($this->items_query)){
				$request = $this->get_all_request();extract($request);
				
				$order_status	= $this->get_string_multi_request('order_status',$order_status, "-1");
				$hide_order_status	= $this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
										
				$sql = " SELECT ";
				$sql .= "
				postmeta2.meta_value AS 'payment_method_title' 
				,SUM(postmeta1.meta_value) AS 'total_amount'
				,COUNT(postmeta1.meta_value) As 'order_count'";
				$sql = apply_filters("ic_commerce_report_page_select_query", $sql, $request, $type, $page, $report_name, $columns);				
				$sql .= "
				
				FROM {$wpdb->posts} as posts
				LEFT JOIN  {$wpdb->postmeta} as postmeta1 ON postmeta1.post_id=posts.ID
				LEFT JOIN  {$wpdb->postmeta} as postmeta2 ON postmeta2.post_id=posts.ID";
				if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
				
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= "
				WHERE
				posts.post_type='shop_order'  
				AND postmeta1.meta_key='_order_total' 
				AND postmeta2.meta_key='_payment_method_title'
				";
				if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
					$sql .= " AND  term_taxonomy.term_id IN ({$order_status_id})";
				}
				
				$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
				if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}
				}
				
				if(strlen($publish_order)>0 && $publish_order != "-1" && $publish_order != "no" && $publish_order != "all"){
					$in_post_status		= str_replace(",","','",$publish_order);
					$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
				}
				
				if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND posts.post_status IN (".$order_status.")";
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
				
				
				
				$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$group_sql = " GROUP BY postmeta2.meta_value ";
				
				$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);	
				
				$order_sql = " 	Order BY total_amount DESC ";
				
				$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);	
				
				
				$this->items_query = $sql;
			}else{
				$sql = $this->items_query;
			}
					
			$order_items = $this->get_query_items($type,$sql);
			return $order_items;
		}
		
	
		/*
			* Function Name ic_commerce_custom_all_order_status_query
			*
			* Returns all order status list
			*
			* @param string $type
			*
			* @param array $columns
			*
			* @param string $report_name
			*
			* @return array|object $order_items
			*			
		*/
		function ic_commerce_custom_all_order_status_query($type = 'limit_row', $columns = array(), $report_name = "")
		{
		    global $wpdb;
			if(!isset($this->items_query)){
				$request = $this->get_all_request();extract($request);
				
				$order_status	= $this->get_string_multi_request('order_status',$order_status, "-1");
				$hide_order_status	= $this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
										
				$sql = " SELECT ";
				$sql .= "
				COUNT(postmeta.meta_value) AS 'order_count'
				,SUM(postmeta.meta_value) AS 'total_amount'";
				
				if($this->constants['post_order_status_found'] == 0 ){
					$sql .= "  ,terms.name As 'order_status', term_taxonomy.term_id AS 'StatusID'";
					

					$sql = apply_filters("ic_commerce_report_page_select_query", $sql, $request, $type, $page, $report_name, $columns);				

					
					$sql .= "  FROM {$wpdb->posts} as posts";
					
					$sql .= "
					LEFT JOIN  {$wpdb->prefix}term_relationships as term_relationships ON term_relationships.object_id=posts.ID
					LEFT JOIN  {$wpdb->prefix}term_taxonomy as term_taxonomy ON term_taxonomy.term_taxonomy_id=term_relationships.term_taxonomy_id
					LEFT JOIN  {$wpdb->prefix}terms as terms ON terms.term_id=term_taxonomy.term_id";
				}else{
					$sql .= "  , posts.post_status As 'order_status' ,posts.post_status As 'StatusID'";
					$sql = apply_filters("ic_commerce_report_page_select_query", $sql, $request, $type, $page, $report_name, $columns);				
					
					$sql .= "  FROM {$wpdb->posts} as posts";
				}
				
				$sql .= "  
				LEFT JOIN  {$wpdb->postmeta} as postmeta ON postmeta.post_id=posts.ID";
				
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= "  
				WHERE postmeta.meta_key = '_order_total'
				AND posts.post_type='shop_order'";	
			
				$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
				if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}
				}
				
				if(strlen($publish_order)>0 && $publish_order != "-1" && $publish_order != "no" && $publish_order != "all"){
					$in_post_status		= str_replace(",","','",$publish_order);
					$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
				}
				
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
				
				
				
				$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$group_sql = " Group BY order_status";
				
				$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);	
				
				$order_sql = "  ORDER BY total_amount DESC";
				
				$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);

				
				$this->items_query = $sql;
			}else{
				$sql = $this->items_query;
			}
						
			$order_items = $this->get_query_items($type,$sql);
			if($type == 'limit_row' || $type == 'all_row' or $type == 'all_row_total'){
				if($this->constants['post_order_status_found'] == 1 ){
				
					$order_statuses = $this->ic_get_order_statuses();
					
					foreach($order_items as $key  => $value){
						$order_items[$key]->order_status = isset($order_statuses[$value->order_status]) ? $order_statuses[$value->order_status] : '';
					}
				}
			}
			
			return $order_items;
		}
		
		
		var $recent_order_query	 	= NULL;
		var $all_row_result 		= NULL;
		var $order_meta				= array();
		
		/*
			* Function Name ic_commerce_custom_all_recent_order_query
			*
			* Returns all recent orders list
			*
			* @param string $type
			*
			* @param array $columns
			*
			* @param string $report_name
			*
			* @param array $total_columns
			*
			* @return array|object $order_items
			*			
		*/
		function ic_commerce_custom_all_recent_order_query($type = 'limit_row', $columns = array(), $report_name = "", $total_columns = array()){			
				global $wpdb;				
				$request = $this->get_all_request();extract($request);
				
				if($type == 'total_row'){	
					$columns_sql = "SELECT ";					
					$columns_sql .= " 	  
						posts.ID AS order_id, 
						DATE_FORMAT(posts.post_date,'%m/%d/%Y') AS order_date
						,postmeta3.meta_value As 'total_amount'
						,posts.post_status As order_status
						,customer_user.meta_value AS customer_user
					";			
				}else{
					$columns_sql = "SELECT ";					
					$columns_sql .= " 	  
						posts.ID AS order_id, 
						DATE_FORMAT(posts.post_date,'%m/%d/%Y') AS order_date
						,postmeta3.meta_value As 'total_amount'
						,posts.post_status As order_status
						,customer_user.meta_value AS customer_user
					";
				}
				
				$columns_sql .= ", posts.post_status As post_status";
				
				$columns_sql = apply_filters("ic_commerce_report_page_select_query", $columns_sql, $request, $type, $page, $report_name, $columns);				
				
				if(!$this->recent_order_query){	
						$order_status	= $this->get_string_multi_request('order_status',$order_status, "-1");
						$hide_order_status	= $this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
									
						$sql = " FROM {$wpdb->posts} as posts 
						LEFT JOIN  {$wpdb->postmeta} as postmeta3 ON postmeta3.post_id=posts.ID";
						
						if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
								$sql .= " 
								LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
								LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
						}
						
						$sql .= " LEFT JOIN  {$wpdb->postmeta} as customer_user 	ON customer_user.post_id		=	posts.ID";
						
						$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
						
						$sql .= " WHERE  posts.post_type='shop_order'
						AND postmeta3.meta_key='_order_total'";
						
						$sql .= " AND customer_user.meta_key = '_customer_user'";
						
						if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
							$sql .= " AND  term_taxonomy.term_id IN ({$order_status_id})";
						}
					
						$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
						if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
							if ($start_date != NULL &&  $end_date !=NULL){
								$sql .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
							}
						}
						
						if(strlen($publish_order)>0 && $publish_order != "-1" && $publish_order != "no" && $publish_order != "all"){
							$in_post_status		= str_replace(",","','",$publish_order);
							$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
						}
						
						
						if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND posts.post_status IN (".$order_status.")";
						
						if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
						
						$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
						$this->recent_order_query = $sql;						
						
						$sql = "";
				}else{
					$sql = $this->recent_order_query;
				}
				
				$sql = $columns_sql;		
				$sql .= $this->recent_order_query;
				
				
				
				$group_sql = " GROUP BY posts.ID";
				
				$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);
				
				
				$wpdb->flush(); 				
				$wpdb->query("SET SQL_BIG_SELECTS=1");					
			
				if($type == 'limit_row'){
					$order_sql = " Order By posts.post_date DESC ";
					$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);
					
					$sql .= " LIMIT $start, $limit";				
					$order_items = $wpdb->get_results($sql);
					
				}
							
				if($type == 'all_row'){
					$order_sql = " Order By posts.post_date DESC ";
					$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);
					$order_items = $wpdb->get_results($sql);
					$this->all_row_result = $order_items;
					
				}
				
				if($type == 'limit_row' || $type == 'all_row' or $type == 'all_row_total'){
						if(count($order_items)>0){
							$extra_meta_keys 	= apply_filters('ic_commerce_report_page_extra_meta_keys', array('order_total','order_shipping','cart_discount','order_discount','total_discount','order_tax','order_shipping_tax','total_tax','billing_first_name','billing_last_name','order_currency'),$request, $type, $page, $report_name, $columns);
							$post_ids 			= $this->get_items_id_list($order_items,'order_id');
							$postmeta_datas 	= $this->get_postmeta($post_ids, $columns,$extra_meta_keys);
							
							foreach ( $order_items as $key => $order_item ) {
									$order_id								= $order_item->order_id;
									
									$postmeta_data 	= isset($postmeta_datas[$order_id]) ? $postmeta_datas[$order_id] : array();
									
									foreach($postmeta_data as $postmeta_key => $postmeta_value){
										$order_items[$key]->{$postmeta_key}	= $postmeta_value;
									}
									
									$order_items[$key]->order_total			= isset($order_items[$key]->order_total)		? $order_items[$key]->order_total 		: 0;
									$order_items[$key]->order_shipping		= isset($order_items[$key]->order_shipping)		? $order_items[$key]->order_shipping 	: 0;
									
									$order_items[$key]->cart_discount		= isset($order_items[$key]->cart_discount)		? $order_items[$key]->cart_discount 	: 0;
									$order_items[$key]->order_discount		= isset($order_items[$key]->order_discount)		? $order_items[$key]->order_discount 	: 0;
									$order_items[$key]->total_discount 		= ($order_items[$key]->cart_discount + $order_items[$key]->order_discount);
									
									$order_items[$key]->order_tax 			= isset($order_items[$key]->order_tax)			? $order_items[$key]->order_tax : 0;
									$order_items[$key]->order_shipping_tax 	= isset($order_items[$key]->order_shipping_tax)	? $order_items[$key]->order_shipping_tax : 0;
									$order_items[$key]->total_tax 			= ($order_items[$key]->order_tax + $order_items[$key]->order_shipping_tax);
									
									$order_items[$key]->gross_amount 		= ($order_items[$key]->order_total + $order_items[$key]->total_discount) - ($order_items[$key]->order_shipping +  $order_items[$key]->order_shipping_tax + $order_items[$key]->order_tax );
									
									$order_items[$key]->billing_first_name	= isset($order_items[$key]->billing_first_name)	? $order_items[$key]->billing_first_name 	: '';
									$order_items[$key]->billing_last_name	= isset($order_items[$key]->billing_last_name)	? $order_items[$key]->billing_last_name 	: '';
									$order_items[$key]->billing_name		= $order_items[$key]->billing_first_name.' '.$order_items[$key]->billing_last_name;
							}
						}
						
						$order_items 		= apply_filters("ic_commerce_all_report_recent_order_data_items", $order_items, $report_name);
						$order_items 	= apply_filters("ic_commerce_report_page_data_items",  $order_items, $request, $type, $page, $report_name);
				}
				
				if($type == 'total_row'){
					if($this->all_row_result){
						if($count_generated == 1){
							$order_items = $this->create_summary($request);
						}else{
							$order_items = $this->all_row_result;
							$summary = $this->get_count_total($order_items,'total_amount');				
							$order_items = $summary;
						}
						
					}else{					
						if($count_generated == 1 || ($p > 1)){
							$order_items = $this->create_summary($request);
							
						}else{
							$order_items = $wpdb->get_results($sql);
							if(count($order_items)>0){
								$extra_meta_keys 	= apply_filters('ic_commerce_report_page_extra_meta_keys', array('order_total','order_shipping','cart_discount','order_discount','order_tax','order_shipping_tax','total_tax'),$request, $type, $page, $report_name, $columns);
								$post_ids 			= $this->get_items_id_list($order_items,'order_id');
								$postmeta_datas 	= $this->get_postmeta($post_ids, $total_columns,$extra_meta_keys,'total');
								
								foreach ( $order_items as $key => $order_item ) {
										$order_id								= $order_item->order_id;
										
										$postmeta_data 	= isset($postmeta_datas[$order_id]) ? $postmeta_datas[$order_id] : array();
										
										foreach($postmeta_data as $postmeta_key => $postmeta_value){
											$order_items[$key]->{$postmeta_key}	= $postmeta_value;
										}
										
										$order_items[$key]->order_total			= isset($order_items[$key]->order_total)		? $order_items[$key]->order_total 		: 0;
										$order_items[$key]->order_shipping		= isset($order_items[$key]->order_shipping)		? $order_items[$key]->order_shipping 	: 0;
										
										$order_items[$key]->cart_discount		= isset($order_items[$key]->cart_discount)		? $order_items[$key]->cart_discount 	: 0;
										$order_items[$key]->order_discount		= isset($order_items[$key]->order_discount)		? $order_items[$key]->order_discount 	: 0;
										$order_items[$key]->total_discount 		= ($order_items[$key]->cart_discount + $order_items[$key]->order_discount);
										
										$order_items[$key]->order_tax 			= isset($order_items[$key]->order_tax)			? $order_items[$key]->order_tax : 0;
										$order_items[$key]->order_shipping_tax 	= isset($order_items[$key]->order_shipping_tax)	? $order_items[$key]->order_shipping_tax : 0;
										$order_items[$key]->total_tax 			= ($order_items[$key]->order_tax + $order_items[$key]->order_shipping_tax);
										
										$order_items[$key]->gross_amount 		= ($order_items[$key]->order_total + $order_items[$key]->total_discount) - ($order_items[$key]->order_shipping +  $order_items[$key]->order_shipping_tax + $order_items[$key]->order_tax );
								}
							}
							
							$report_name 		= $this->get_request('report_name');
							$total_columns 		= $this->result_columns($report_name);
							
							$order_items 		= apply_filters("ic_commerce_report_page_data_items",  $order_items, $request, $type, $page, $report_name);
							
							$grid_object		= $this->get_grid_object();
							$order_items		= $grid_object->create_grid_items($total_columns,$order_items);
							
							
							
							
							$summary = $this->get_count_total($order_items,'total_amount');				
							$order_items = $summary;
							
						}	
						
					}
					
					return $order_items;
				}
				
				return $order_items;		
		}
		
	
		/*
			* Function Name ic_commerce_custom_all_coupon_query
			*
			* Returns all coupons list
			*
			* @param string $type
			*
			* @param array $columns
			*
			* @param string $report_name
			*
			* @return array|object $order_items
			*			
		*/
		function ic_commerce_custom_all_coupon_query($type = 'limit_row', $columns = array(), $report_name = ""){
			global $wpdb;
			
			$request = $this->get_all_request();
			
			if(!isset($this->items_query)){
				extract($request);
				
				$order_status			= $this->get_string_multi_request('order_status',$order_status, "-1");
				$hide_order_status		= $this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				$coupon_codes			= $this->get_string_multi_request('coupon_codes',$coupon_codes, "-1");
				$coupon_discount_types	= $this->get_string_multi_request('coupon_discount_types',$coupon_discount_types, "-1");
				$country_code			= $this->get_string_multi_request('country_code',$country_code, "-1");
									
									
										
				$sql = " SELECT ";
				$sql .= "
				woocommerce_order_items.order_item_name				AS		'order_item_name',
				woocommerce_order_items.order_item_name				AS		'coupon_code', 
				SUM(woocommerce_order_itemmeta.meta_value) 			AS		'total_amount', 
				woocommerce_order_itemmeta.meta_value 				AS 		'coupon_amount' , 
				Count(*) 											AS 		'coupon_count'";
				
				if($report_name == "coupon_couontry_page"){
					$sql .= ", billing_country.meta_value 			AS billing_country";
					switch($sort_by){
						case "coupon_country":
							$sql .= ", CONCAT(woocommerce_order_items.order_item_name, ' ', billing_country.meta_value) 			AS coupon_country";
							break;
						case "country_coupon":
							$sql .= ", CONCAT(billing_country.meta_value, ' ', woocommerce_order_items.order_item_name) 			AS country_coupon";
							break;
						
					}
				}
				
				$sql = apply_filters("ic_commerce_report_page_select_query", $sql, $request, $type, $page, $report_name, $columns);				
				
				$sql .= "
				FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items 
				LEFT JOIN	{$wpdb->posts}	as posts ON posts.ID = woocommerce_order_items.order_id
				LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
				";
				
				if($report_name == "coupon_couontry_page"){
					$sql .= " LEFT JOIN	{$wpdb->postmeta}	as billing_country ON billing_country.post_id = woocommerce_order_items.order_id AND billing_country.meta_key = '_billing_country'";
				}
				
				if($coupon_discount_types && $coupon_discount_types != "-1"){
					$sql .= " LEFT JOIN	{$wpdb->posts}	as coupons ON coupons.post_title = woocommerce_order_items.order_item_name";
					$sql .= " LEFT JOIN	{$wpdb->postmeta}	as coupon_discount_type ON coupon_discount_type.post_id = coupons.ID";
				}
				
				if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
				
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= "
				WHERE 
				posts.post_type 								=	'shop_order'
				AND woocommerce_order_items.order_item_type		=	'coupon' 
				AND woocommerce_order_itemmeta.meta_key			=	'discount_amount'";
				
				if($coupon_discount_types && $coupon_discount_types != "-1"){
					$sql .= " AND coupons.post_type 				=	'shop_coupon'";
					$sql .= " AND coupon_discount_type.meta_key		=	'discount_type'";
				}
				
				$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
				if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}
				}
				
				if(strlen($order_status_id)>0 && $order_status_id != "-1" && $order_status_id != "no" && $order_status_id != "all"){
					$sql .= " AND  term_taxonomy.term_id IN ({$order_status_id})";
				}
				
				if(strlen($publish_order)>0 && $publish_order != "-1" && $publish_order != "no" && $publish_order != "all"){
					$in_post_status		= str_replace(",","','",$publish_order);
					$sql .= " AND  posts.post_status IN ('{$in_post_status}')";
				}
				
				if($coupon_code && $coupon_code != "-1"){
					$sql .= " AND (woocommerce_order_items.order_item_name IN ('{$coupon_code}') OR woocommerce_order_items.order_item_name LIKE '%{$coupon_code}%')";
				}
				
				if($coupon_codes && $coupon_codes != "-1"){
					$sql .= " AND woocommerce_order_items.order_item_name IN ({$coupon_codes})";
				}
				
				if($coupon_discount_types && $coupon_discount_types != "-1"){
					$sql .= " AND coupon_discount_type.meta_value IN ({$coupon_discount_types})";
				}
				
				if($country_code && $country_code != "-1"){
					$sql .= " AND billing_country.meta_value IN ({$country_code})";
				}
				
				if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND posts.post_status IN (".$order_status.")";
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
				
				$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
				if($request['report_name'] == "coupon_couontry_page"){
					
					$group_sql = " Group BY billing_country.meta_value, woocommerce_order_items.order_item_name";
					
					$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);	
					
					$order_sql = " ORDER BY {$sort_by} {$order_by}";
					
					$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);	
					
				}else{
					
					$group_sql = " Group BY woocommerce_order_items.order_item_name";
					
					$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);	
					
					$order_sql = " ORDER BY total_amount DESC";
					
					$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);	
				}				
				
				$this->items_query = $sql;
				
			}else{
				$sql = $this->items_query;
			}
			
			$order_items = $this->get_query_items($type,$sql);
			
			if(count($order_items) > 0){
				if($request['report_name'] == "coupon_couontry_page"){
					if($type == "limit_row" or $type == 'all_row' or $type == 'all_row_total'){
						$countries		= 	$this->get_wc_countries();
						
						foreach($order_items as $orderitem_key => $orderitem){
							$billing_country_code = isset($orderitem->billing_country) ? $orderitem->billing_country : '';
							
							if($billing_country_code == "US"){
								$billing_country_name = isset($countries->countries[$billing_country_code]) ? $countries->countries[$billing_country_code] : $billing_country_code;	
							}else{
								$billing_country_name = isset($countries->countries[$billing_country_code]) ? $countries->countries[$billing_country_code]." ({$billing_country_code})" : $billing_country_code;	
							}
							$order_items[$orderitem_key]->billing_country = $billing_country_name;
						}						
					}
				}
			}
			
			return $order_items;
		}
		
		/*
			* Function Name ic_commerce_custom_all_product_advance_query
			*
			* Returns all advanced products list
			*
			* @param string $type
			*
			* @param array $columns
			*
			* @param string $report_name
			*
			* @return array|object $order_items
			*			
		*/
		function ic_commerce_custom_all_product_advance_query($type = 'limit_row', $columns = array(), $report_name = ""){
			global $wpdb;
			
			if(!isset($this->items_query)){
				$request = $this->get_all_request();extract($request);
				
				$order_status	= $this->get_string_multi_request('order_status',$order_status, "-1");
				$hide_order_status	= $this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				
				$sql = " SELECT ";
				$sql .= "	
							woocommerce_order_items.order_item_name 		AS 'product_name'
							,woocommerce_order_items.order_item_id
							,SUM(woocommerce_order_itemmeta.meta_value) 	AS 'quantity'
							,SUM(woocommerce_order_itemmeta6.meta_value) 	AS 'total_amount'
							,SUM(woocommerce_order_itemmeta8.meta_value) 	AS 'product_vat_total'
							,SUM(woocommerce_order_itemmeta8.meta_value)/SUM(woocommerce_order_itemmeta.meta_value)  	AS 'product_vat_par_item'
							,SUM(woocommerce_order_itemmeta6.meta_value)/SUM(woocommerce_order_itemmeta.meta_value) 	AS 'product_rate_exculude_tax'
							,woocommerce_order_itemmeta7.meta_value 		AS product_id						
							,SUM(woocommerce_order_itemmeta6.meta_value) 	AS 'total_price_exculude_tax'						
							,(SUM(woocommerce_order_itemmeta8.meta_value) + SUM(woocommerce_order_itemmeta6.meta_value) )	AS 'total_amount'						
							,DATE(shop_order.post_date) 					AS post_date 
							
							";
				$sql = apply_filters("ic_commerce_report_page_select_query", $sql, $request, $type, $page, $report_name, $columns);				
				
				$sql .= "
							FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items						
							LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
							LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta6 ON woocommerce_order_itemmeta6.order_item_id=woocommerce_order_items.order_item_id
							LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta7 ON woocommerce_order_itemmeta7.order_item_id=woocommerce_order_items.order_item_id
							LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta8 ON woocommerce_order_itemmeta8.order_item_id=woocommerce_order_items.order_item_id
							";
				
				if($category_id  && $category_id != "-1") {
						$sql .= " 	
							LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	woocommerce_order_itemmeta7.meta_value 
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
							LEFT JOIN  {$wpdb->posts} as shop_order ON shop_order.id=woocommerce_order_items.order_id";
				
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
							
				$sql .= "
							WHERE woocommerce_order_itemmeta.meta_key	= '_qty'
							AND woocommerce_order_itemmeta6.meta_key	= '_line_total' 
							AND woocommerce_order_itemmeta7.meta_key 	= '_product_id'
							AND woocommerce_order_itemmeta8.meta_key 	= '_line_tax'
							AND shop_order.post_type					= 'shop_order'
							";
				
				$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
				if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(shop_order.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}
				}
				
				if($product_id  && $product_id != "-1") 
					$sql .= "
							AND woocommerce_order_itemmeta7.meta_value IN (".$product_id .")";	
				
				if($category_id  && $category_id != "-1") 
					$sql .= "
							AND terms.term_id IN (".$category_id .")";	
				
				if($order_status_id  && $order_status_id != "-1") 
					$sql .= " 
							AND terms2.term_id IN (".$order_status_id .")";
				
			
				if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND shop_order.post_status IN (".$order_status.")";//changed 20141013
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND shop_order.post_status NOT IN (".$hide_order_status.")";//changed 20141013
				
				$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$group_sql = " GROUP BY  woocommerce_order_itemmeta7.meta_value";			
				
				$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);	
				
				$order_sql = " ORDER BY total_amount DESC";
				
				$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);	
				
				$this->items_query = $sql;
				
			}else{
				$sql = $this->items_query;
			}
			$order_items = $this->get_query_items($type,$sql);
			return $order_items;
		}
		
		
		/*
			* Function Name ic_commerce_custom_all_manual_refund_detail_query
			*
			* Returns rows of manual refund summary
			*
			* @param string $type
			*
			* @param array $columns
			*
			* @param string $report_name
			*
			* @return array|object $order_items
			*			
		*/
		function ic_commerce_custom_all_manual_refund_detail_query($type = 'limit_row', $columns = array(), $report_name = ""){
			global $wpdb;			
			
			if(!isset($this->items_query)){
					$request 				= $this->get_all_request();extract($request);
					$sql 					= "";				
					$order_status			= $this->get_string_multi_request('order_status',$order_status, "-1");
					$hide_order_status		= $this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				_status_type;
					if($refund_status_type == "part_refunded"){
						$sql = "SELECT 
						
						posts.ID 						as refund_id
						
						,posts.post_status 				as refund_status
						,posts.post_date 				as refund_date				
						,posts.post_excerpt 			as refund_note				
						,posts.post_author				as customer_user
						
						,postmeta.meta_value 			as refund_amount
						,SUM(postmeta.meta_value) 		as total_amount
						
						,shop_order.ID 					as order_id
						,shop_order.ID 					as order_id_number
						,shop_order.post_status 		as order_status
						,shop_order.post_date 			as order_date
						,COUNT(posts.ID) 				as refund_count";
										
						
						$group_sql = "";
						switch($group_by){
							case "refund_id":
								$group_sql .= ", posts.ID as group_column";
								$group_sql .= ", posts.ID as order_column";
								break;
							case "order_id":
								$group_sql .= ", shop_order.ID as group_column";
								$group_sql .= ", shop_order.post_author as order_column";
								break;
							case "refunded":
								$group_sql .= ", posts.post_author as group_column";
								$group_sql .= ", posts.post_author as order_column";
								break;
							case "daily":
								$group_sql .= ", DATE(posts.post_date) as group_column";
								$group_sql .= ", DATE(posts.post_date) as group_date";
								$group_sql .= ", DATE(posts.post_date) as order_column";
								break;
							case "monthly":
								$group_sql .= ", CONCAT(MONTHNAME(posts.post_date) , ' ',YEAR(posts.post_date)) as group_column";
								$group_sql .= ", DATE(posts.post_date) as order_column";
								break;
							case "yearly":
								$group_sql .= ", YEAR(posts.post_date)as group_column";
								$group_sql .= ", DATE(posts.post_date) as order_column";
								break;
							default:
								$group_sql .= ", posts.ID as group_column";
								$group_sql .= ", posts.ID as order_column";
								break;
							
						}
					
						$sql .= $group_sql;				
						$sql = apply_filters("ic_commerce_report_page_select_query", $sql, $request, $type, $page, $report_name, $columns);				
						$sql .= "
						
						FROM {$wpdb->posts} as posts
										
						LEFT JOIN  {$wpdb->postmeta} as postmeta ON postmeta.post_id	=	posts.ID";
						
						$sql .= " LEFT JOIN  {$wpdb->posts} as shop_order ON shop_order.ID	=	posts.post_parent";

					}else{
						
						$sql = "SELECT 						
						SUM(postmeta.meta_value) 		as total_amount
						,shop_order.post_author			as customer_user
						,shop_order.ID 					as order_id
						,shop_order.ID 					as order_id_number
						,shop_order.post_status 		as order_status
						,shop_order.post_modified		as order_date
						,COUNT(shop_order.ID) 			as refund_count
						";
						
						$group_sql = "";
						switch($group_by){
							case "order_id":
								$group_sql .= ", shop_order.ID as group_column";
								$group_sql .= ", shop_order.ID as order_column";
								break;
							case "refunded":
								$group_sql .= ", shop_order.post_author as group_column";
								$group_sql .= ", shop_order.post_author as order_column";
								break;
							case "daily":
								$group_sql .= ", DATE(shop_order.post_modified) as group_column";
								$group_sql .= ", DATE(shop_order.post_modified) as group_date";
								$group_sql .= ", DATE(shop_order.post_modified) as order_column";
								break;
							case "monthly":
								$group_sql .= ", CONCAT(MONTHNAME(shop_order.post_modified) , ' ',YEAR(shop_order.post_modified)) as group_column";
								$group_sql .= ", DATE(shop_order.post_modified) as order_column";
								break;
							case "yearly":
								$group_sql .= ", YEAR(shop_order.post_modified)as group_column";
								$group_sql .= ", DATE(shop_order.post_modified) as order_column";
								break;
							default:
								$group_sql .= ", shop_order.ID as group_column";
								$group_sql .= ", shop_order.ID as order_column";
								break;
							
						}
						
						$sql .= $group_sql;
						$sql = apply_filters("ic_commerce_report_page_select_query", $sql, $request, $type, $page, $report_name, $columns);				
						$sql .= "						
								FROM {$wpdb->posts} as shop_order
								LEFT JOIN  {$wpdb->postmeta} as postmeta ON postmeta.post_id	=	shop_order.ID";
							
					}
						
						
						
					if($order_status_id  && $order_status_id != "-1") {
						$sql .= " 	
							LEFT JOIN  {$wpdb->prefix}term_relationships	as term_relationships2 	ON term_relationships2.object_id	=	shop_order.ID
							LEFT JOIN  {$wpdb->prefix}term_taxonomy			as term_taxonomy2 		ON term_taxonomy2.term_taxonomy_id	=	term_relationships2.term_taxonomy_id
							LEFT JOIN  {$wpdb->prefix}terms					as terms2 				ON terms2.term_id					=	term_taxonomy2.term_id";
					}
					
					if($refund_status_type == "part_refunded"){
						
						$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
						
						$sql .= " WHERE posts.post_type = 'shop_order_refund' AND  postmeta.meta_key='_refund_amount'";						
						
						$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
						if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
							if ($start_date != NULL &&  $end_date !=NULL){
								$sql .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
							}
						}
						
						if($order_status_id  && $order_status_id != "-1"){
							$refunded_id 	= $this->get_old_order_status(array('refunded'), array('wc-refunded'));
							$refunded_id    = implode(",".$refunded_id);
							$sql .= " AND terms2.term_id NOT IN (".$refunded_id .")";
							
							if($order_status_id  && $order_status_id != "-1"){
								$sql .= " AND terms2.term_id IN (".$order_status_id .")";
							}
						}else{
							$sql .= " AND shop_order.post_status NOT IN ('wc-refunded')";
							if($order_status  && $order_status != '-1' and $order_status != "'-1'"){
								$sql .= " AND shop_order.post_status IN (".$order_status.")";
							}
						}
					}else{
						$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
						
						$sql .= " WHERE shop_order.post_type = 'shop_order' AND  postmeta.meta_key='_order_total'";						
					
						$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
						if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
							if ($start_date != NULL &&  $end_date !=NULL){
								$sql .= " AND DATE(shop_order.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
							}
						}
						
						if($order_status_id  && $order_status_id != "-1"){
							$refunded_id 	= $this->get_old_order_status(array('refunded'), array('wc-refunded'));
							$refunded_id    = implode(",",$refunded_id);
							$sql .= " AND terms2.term_id IN (".$refunded_id .")";
						}else{
							$sql .= " AND shop_order.post_status IN ('wc-refunded')";
						}
					}					
					
					if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND shop_order.post_status NOT IN (".$hide_order_status.")";
					
					$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
					$group_sql = " GROUP BY  group_column";			
					
					$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);	
					
					$order_sql = " ORDER BY order_column DESC";
					
					$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);
									
				$this->items_query = $sql;
				
			}else{
				$sql = $this->items_query;
			}
			
			$order_items = $this->get_query_items($type,$sql,"total_amount");
			
			
			return $order_items;
		}
		
		/*
			* Function Name ic_commerce_custom_all_part_refund_detail_query
			*
			* Returns rows of partial refund summary
			*
			* @param string $type
			*
			* @param array $columns
			*
			* @param string $report_name
			*
			* @return array|object $order_items
			*			
		*/
		function ic_commerce_custom_all_part_refund_detail_query($type = 'limit_row', $columns = array(), $report_name = ""){
			global $wpdb;
			$request 				= $this->get_all_request();extract($request);
			
			$sql 					= "";	
						
			$order_status			= $this->get_string_multi_request('order_status',$order_status, "-1");
			
			$hide_order_status		= $this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
			
			
			
			$sql = "SELECT 
				
				posts.ID 						as refund_id
				
				,posts.post_status 				as refund_status
				,posts.post_date 				as refund_date				
				,posts.post_excerpt 			as refund_note				
				,posts.post_author				as customer_user
				
				,postmeta.meta_value 			as refund_amount
				,SUM(postmeta.meta_value) 		as total_amount
				
				,shop_order.ID 					as order_id
				,shop_order.ID 					as order_id_number
				,shop_order.post_status 		as order_status
				,shop_order.post_date 			as order_date
				,COUNT(posts.ID) 				as refund_count";
								
				
				$group_sql = "";
				switch($group_by){
					case "refund_id":
						$group_sql .= ", posts.ID as group_column";
						break;
					case "order_id":
						$group_sql .= ", shop_order.ID as group_column";
						break;
					case "refunded":
						$group_sql .= ", posts.post_author as group_column";
						break;
					case "daily":
						$group_sql .= ", DATE(posts.post_date) as group_column";
						$group_sql .= ", DATE(posts.post_date) as group_date";
						break;
					case "monthly":
						$group_sql .= ", CONCAT(MONTHNAME(posts.post_date) , ' ',YEAR(posts.post_date)) as group_column";
						break;
					case "yearly":
						$group_sql .= ", YEAR(posts.post_date)as group_column";
						break;
					default:
						$group_sql .= ", posts.ID as group_column";
						break;
					
				}
				$sql .= $group_sql;				
				
				$sql = apply_filters("ic_commerce_report_page_select_query", $sql, $request, $type, $page, $report_name, $columns);				
				
				$sql .= "
				
				FROM {$wpdb->posts} as posts
								
				LEFT JOIN  {$wpdb->postmeta} as postmeta ON postmeta.post_id	=	posts.ID";
				
				$sql .= " LEFT JOIN  {$wpdb->posts} as shop_order ON shop_order.ID	=	posts.post_parent";
			
				
				if($order_status_id  && $order_status_id != "-1") {
					$sql .= " 	
						LEFT JOIN  {$wpdb->prefix}term_relationships	as term_relationships2 	ON term_relationships2.object_id	=	shop_order.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy			as term_taxonomy2 		ON term_taxonomy2.term_taxonomy_id	=	term_relationships2.term_taxonomy_id
						LEFT JOIN  {$wpdb->prefix}terms					as terms2 				ON terms2.term_id					=	term_taxonomy2.term_id";
				}
				
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= " WHERE posts.post_type = 'shop_order_refund' AND  postmeta.meta_key='_refund_amount'";
				
				$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
				if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(posts.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}
				}
				
				if($order_status_id  && $order_status_id != "-1"){
					$refunded_id 	= $this->get_old_order_status(array('refunded'), array('wc-refunded'));
					$refunded_id    = implode(",".$refunded_id);
					$sql .= " AND terms2.term_id NOT IN (".$refunded_id .")";
					
					if($order_status_id  && $order_status_id != "-1"){
						$sql .= " AND terms2.term_id IN (".$order_status_id .")";
					}
				}else{
					$sql .= " AND shop_order.post_status NOT IN ('wc-refunded')";
					if($order_status  && $order_status != '-1' and $order_status != "'-1'"){
						$sql .= " AND shop_order.post_status IN (".$order_status.")";
					}
				}
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND shop_order.post_status NOT IN (".$hide_order_status.")";//changed 20141013
			
			
				$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$group_sql = " GROUP BY  group_column";			
				
				$sql .= apply_filters("ic_commerce_report_page_group_query", $group_sql, $request, $type, $page, $report_name, $columns);	
				
				$order_sql = " ORDER BY order_date DESC";
				
				$sql .= apply_filters("ic_commerce_report_page_order_query", $order_sql, $request, $type, $page, $report_name, $columns);	
				
				
				$order_items = $this->get_query_items($type,$sql,"total_amount");
					
				return $order_items;
		}
		
		/*
			* Function Name get_final_order_items
			*
			* Returns extra columns of Tax Report page and Customer Buy Product page
			*
			* @param string $type
			*
			* @param array $order_items
			*
			* @param string $report_name
			*
			* @return array|object $order_items
			*			
		*/
		function get_final_order_items($type,$order_items,$report_name){
			
			if($report_name == "tax_page"){
				foreach ( $order_items as $key => $order_item ) {
					$order_items[$key]->_total_tax = $order_item->_shipping_tax_amount + $order_item->_order_tax;
					
					$order_item->_order_amount = $this->get_percentage($order_item->_order_tax,$order_item->order_tax_rate);//Added 20150206
					$order_items[$key]->tax_rate_name = isset($order_item->tax_rate_name) ? trim($order_item->tax_rate_name) : '';
					$order_items[$key]->tax_rate_name = strlen($order_item->tax_rate_name)<=0 ? $order_item->tax_rate_code : $order_item->tax_rate_name;				
					$order_items[$key]->billing_state = isset($order_item->billing_state) ? $order_item->billing_state : '';
					
					$order_items[$key]->total_amount = $order_items[$key]->_total_tax;
				}
			}
			
			if($report_name == "customer_buy_products_page"){
				if($type == 'total_row') return $order_items;
				$users = array();
				foreach ( $order_items as $key => $order_item ) {
					$users[] = $order_item->customer_id;
				}				
				$users 		= array_unique($users);
				$users_list = $this->get_customer_details($users);
				if(count($users_list)>0){
					foreach ( $order_items as $key => $order_item) {
						$customer_id = $order_item->customer_id;
						if($customer_id > 0){
							if(isset($users_list[$customer_id])){
								$order_items[$key]->billing_email = $users_list[$customer_id]->billing_email;
								$order_items[$key]->billing_name = $users_list[$customer_id]->billing_name;
							}
						}
					}
				}
			}
			return $order_items;			
		}
		
		/*
			* Function Name get_query_items
			*
			* Returns created query items.
			*
			* @param string $type
			*
			* @param string $sql
			*
			* @param string $total_amount
			*
			* @return array|object $order_items
			*			
		*/
		function get_query_items($type,$sql,$total_amount = 'total_amount'){
			global  $wpdb;
			$request = $this->get_all_request();extract($request);
			$wpdb->flush(); 				
			$wpdb->query("SET SQL_BIG_SELECTS=1");
			if($type == 'total_row'){
				
				if($this->all_row_result){
					if($count_generated == 1){
						$order_items = $this->create_summary($request);
						
						
					}else{
						$order_items = $this->all_row_result;
						$summary = $this->get_count_total($order_items,$total_amount);				
						$order_items = $summary;
						
					}
					
				}else{					
					if($count_generated == 1 || ($p > 1)){
						$order_items = $this->create_summary($request);
						
					}else{
						$order_items = $wpdb->get_results($sql);
						if($wpdb->last_error){
							echo $wpdb->last_error;
						}
						$order_items = $this->get_final_order_items($type,$order_items,$report_name);
						
						$order_items 	= apply_filters("ic_commerce_report_page_data_items",  $order_items, $request, $type, $page, $report_name);
						
						
						$summary = $this->get_count_total($order_items,$total_amount);				
						$order_items = $summary;
						
						
					}					
				}
				return $order_items;
			}
			
			if($type == 'limit_row'){					
				$sql .= " LIMIT $start, $limit";
				$order_items = $wpdb->get_results($sql);
				if($wpdb->last_error){
					echo $wpdb->last_error;
				}
				$order_items = $this->get_final_order_items($type,$order_items,$report_name);				
				$wpdb->flush(); 
			}
			
			if($type == 'all_row' or $type == 'all_row_total'){
				$order_items = $wpdb->get_results($sql);
				if($wpdb->last_error){
					echo $wpdb->last_error;
				}
				$order_items = $this->get_final_order_items($type,$order_items,$report_name);
				$this->all_row_result = $order_items;
				$wpdb->flush(); 
			}
			
			$order_items 	= apply_filters("ic_commerce_report_page_data_items",  $order_items, $request, $type, $page, $report_name);
			
			return $order_items;
		}
		
		/*
			* Function Name get_count_total
			*
			* Returns total count summary
			*
			* @param string $data
			*
			* @param string $amt
			*
			* @return array|object $return
			*			
		*/
		function get_count_total($data,$amt = 'total_amount'){
			$total = 0;
			$return = array();
			$report_name 		= $this->get_request('report_name');
			$total_columns 		= $this->result_columns($report_name);
			
			$order_status		= array();
			if(count($total_columns) > 0){
				
				
				foreach($data as $key => $value){
					$total = $total + (isset($value->$amt) ? $value->$amt : 0);

					foreach($total_columns as $ckey => $label):
						$v = isset($value->$ckey) ? trim($value->$ckey) : 0;
						$v = empty($v) ? 0 : $v;						
						$return[$ckey] 	= isset($return[$ckey])	? ($return[$ckey] + $v): $v;
					endforeach;
					
				}
			}else{
				foreach($data as $key => $value){
					$total = $total + (isset($value->$amt) ? $value->$amt : 0);
				}
			}
			
			$return['total_row_amount'] = $total;
			$return['total_row_count'] = count($data);
			
			return $return;
		}
		
		/*
			* Function Name total_count
			*
			* Returns created total count summary HTML
			*
			* @param string $Totalorder_count
			*
			* @param string $TotalAmount
			*
			* @param array $summary
			*
			* @param string $TotalShipping
			*			
		*/
		function total_count($Totalorder_count = 0, $TotalAmount=0, $summary = array(), $TotalShipping=0){
			global $wpdb;
			
			$admin_page 		= $this->get_request('page');
			$limit	 			= $this->get_request('limit',15, true);
			$adjacents			= $this->get_request('adjacents',3);
			$detail_view		= $this->get_request('detail_view',"no");
			$targetpage 		= "admin.php?page=".$admin_page;
		    $request 			= $this->get_all_request();extract($request);
			$total_pages		= isset($summary['total_row_count']) ? $summary['total_row_count'] : 0;
			$create_pagination 	= $this->get_pagination($total_pages,$limit,$adjacents,$targetpage,$request);
			
			$total_row_amount	= isset($summary['total_row_amount']) ? $summary['total_row_amount'] : 0;
			$total_row_count	= isset($summary['total_row_count']) ? $summary['total_row_count'] : 0;
			
			$output 			= "";
			$categories_count 	= $this->get_request('parent_categories_count',0,true);
				
			?>
				
				<table style="width:100%" class="detail_summary">
					<tr>
						<td valign="middle" class="grid_bottom_total">
                        	<?php
								if($report_name == "recent_order"){
									$formated_total_amount		= $this->price($TotalAmount);
									$formated_total_shippint	= $this->price($TotalShipping);
									$output = "<table><tr>";
									$output .= "<tr><td>Result:		</td><td>	<strong>{$Totalorder_count}/{$total_pages}</strong></td></tr>";
									$output .= "<tr><td>Amount:		</td><td> 	<strong>{$formated_total_amount}</strong></td></tr>";
									$output .= "<tr><td>Shipping:	</td><td> 	<strong>{$formated_total_shippint}</strong></td></tr>";
									$output .= "</tr></table>";
								}else{
									
									if($categories_count <= 0){
										$output = sprintf(__("Result: <strong>%s/%s</strong>",'icwoocommerce_textdomains'),$Totalorder_count,$total_pages);
										if($TotalAmount >0){
											$formated_total_amount		= $this->price($TotalAmount);
											$output .= sprintf(__(", Amount: <strong>%s</strong><br />",'icwoocommerce_textdomains'),$formated_total_amount);
										}
									}
									
									
								}
								
								echo $output;
								
							?>
						</td>
						<td>					
							<?php echo $create_pagination;?>
                        	<div class="clearfix"></div>
                            <div>
                        	<?php
								$this->export_to_csv_button('bottom', $summary);
								$this->back_button();
							?>
                            </div>
                            <div class="clearfix"></div>
                        </td>
					</tr>
				</table>
                <script type="text/javascript">
                	jQuery(document).ready(function($) {$('.pagination a').removeAttr('href');});
                </script>
			<?php
		}
		
		/*
			* Function Name export_to_csv_button
			*
			* Returns created Export to CSV, EXCEL, PDF and PRINT buttons
			*
			* @param string $position
			*
			* @param array $summary
			*			
		*/
		function export_to_csv_button($position = 'bottom', $summary = array()){
			global $request;
			$admin_page			= $this->get_request('page',NULL);
			$admin_page			= $this->get_request('admin_page',$admin_page,true);
			
			$admin_page_url 		= $this->constants['admin_page_url'];
			$mngpg 				= 	$admin_page_url.'?page='.$admin_page;
			$request			=	$request = $this->get_all_request();
			
			$request['count_generated']		=	1;
			
			foreach($summary as $key => $value):

				$request[$key]		=	$value;
			endforeach;
			
			$request_			=	$request;
			
			unset($request['action']);
			
			unset($request['p']);
			
			$logo_image 			= $this->get_setting('logo_image',$this->constants['plugin_options'], '');
			$report_title 			= $this->get_setting('report_title',$this->constants['plugin_options'], '');
			$company_name 			= $this->get_setting('company_name',$this->constants['plugin_options'], '');
							
			?>
            <div id="<?php echo $admin_page ;?>Export" class="RegisterDetailExport">
                <form id="<?php echo $admin_page."_".$position ;?>_form" class="<?php echo $admin_page ;?>_form ic_export_<?php echo $position ;?>_form" action="<?php echo $mngpg;?>" method="post">
                   <?php echo $this->create_hidden_fields($request);?>
                    <input type="hidden" name="export_file_name" value="<?php echo $admin_page;?>" />
                    <input type="hidden" name="export_file_format" value="csv" />
                 	
                    <input type="submit" name="<?php echo $admin_page ;?>_export_csv" class="onformprocess csvicon" value="<?php _e("Export to CSV",'icwoocommerce_textdomains');?>" data-format="csv" data-popupid="export_csv_popup" data-hiddenbox="popup_csv_hidden_fields" data-popupbutton="<?php _e("Export to CSV",'icwoocommerce_textdomains');?>" data-title="<?php _e("Export to CSV - Additional Information",'icwoocommerce_textdomains');?>" />
                    <input type="submit" name="<?php echo $admin_page ;?>_export_xls" class="onformprocess excelicon" value="<?php _e("Export to Excel",'icwoocommerce_textdomains');?>" data-format="xls" data-popupid="export_csv_popup" data-hiddenbox="popup_csv_hidden_fields" data-popupbutton="<?php _e("Export to Excel",'icwoocommerce_textdomains');?>" data-title="<?php _e("Export to Excel - Additional Information",'icwoocommerce_textdomains');?>" />
                    <input type="button" name="<?php echo $admin_page ;?>_export_pdf" class="onformprocess open_popup pdficon" value="<?php _e("Export to PDF",'icwoocommerce_textdomains');?>" data-format="pdf" data-popupid="export_pdf_popup" data-hiddenbox="popup_pdf_hidden_fields" data-popupbutton="<?php _e("Export to PDF",'icwoocommerce_textdomains');?>" data-title="<?php _e("Export to PDF",'icwoocommerce_textdomains');?>" />
                    <input type="button" name="<?php echo $admin_page ;?>_export_print" class="onformprocess open_popup printicon" value="<?php _e("Print",'icwoocommerce_textdomains');?>"  data-format="print" data-popupid="export_print_popup" data-hiddenbox="popup_print_hidden_fields" data-popupbutton="<?php _e("Print",'icwoocommerce_textdomains');?>" data-title="<?php _e("Print",'icwoocommerce_textdomains');?>" data-form="form" />
                    
                </form>
                <?php if($position == "bottom"):?>
                <form id="search_order_pagination" class="search_order_pagination" action="<?php echo $mngpg;?>" method="post">
                    <?php echo $this->create_hidden_fields($request_);?>
                </form>
                <?php endif;?>
               </div>
            <?php
		}
		
		/*
			* Function Name back_button
			*
			* Returns created Back to Previous button HTML
			*			
		*/
		function back_button(){
			$url = "#";
			if(isset($_SERVER['HTTP_REFERER']))
				$url = $_SERVER['HTTP_REFERER'];
			
			?>	<div class="backtoprevious">
            		<!--<a href="<?php echo $url;?>" class="backtoprevious" onclick="back_to_previous();">Back to Previous</a>-->
                    <input type="button" name="backtoprevious" value="<?php _e("Back to Previous",'icwoocommerce_textdomains');?>"  class="backtoprevious onformprocess" onClick="back_to_previous();" />
                </div>
            <?php
		}
		
		/*
			* Function Name back_print_botton
			*
			* Returns created PRINT button at bottom
			*
			* @param string $position
			*
			* @param array $summary
			*			
		*/
		function back_print_botton($position  = "bottom",$summary = array()){
			
			if($position  == "bottom"){
				$total_row_amount	= $summary['total_row_amount'];
				$total_row_count	= $summary['total_row_count'];
				
			?>
            	<div class="print_summary_bottom">
                	Total Result: <strong><?php echo $total_row_count ?></strong>, Amount: <strong><?php echo $this->price($total_row_amount); ?></strong><br />
                </div>
            <?php }?>
                <div class="back_print_botton noPrint">
            		<input type="button" name="backtoprevious" value="<?php _e("Back to Previous",'icwoocommerce_textdomains');?>"  class="onformprocess" onClick="back_to_detail();" />
                    <input type="button" name="backtoprevious" value="<?php _e("Print",'icwoocommerce_textdomains');?>"  class="onformprocess" onClick="print_report();" />
                </div>
            <?php   
		} 
		
		/*
			* Function Name ic_commerce_custom_admin_report_ajax_request
			*
			* Returns AJAX Request
			*
			* @param string $type 
			*			
		*/
		function ic_commerce_custom_admin_report_ajax_request($type) {
			
			if (!empty( $_POST['action'] ) ) {
				$detail_view 	= isset($_REQUEST['detail_view']) ? $_REQUEST['detail_view'] : 'no';
				if($detail_view == "yes"){
					$this->ic_commerce_custom_report_detail($type);
				}else{
					$this->ic_commerce_custom_report_normal($type);
				}
			}else{
				echo __("Something going wrong, contact to developer",'icwoocommerce_textdomains');
			}
			die();
		}
		
		/*
			* Function Name product_by_category_ajax_request
			*
			* Returns AJAX Request of Products by Category
			*			
		*/
		function product_by_category_ajax_request() {
			$output_array 					= array();	
			$output_array['error_output'] 	= '';
			$output_array['error'] 			= 'false';
			$output_array['success'] 		= 'false';
			$output_array['success_output'] = '';
			$message = "";	
			
			if (!empty( $_POST['action'] ) ) {
						global $wpdb;				
						$products = $this->get_product_data('all');
						if(count($products) <= 0){					
							$products = array(array("ID" => "-1","title" => __("Purchased product not found in selected categroy",'icwoocommerce_textdomains')));
						}
						
				$output_array['success'] = 'true';
				$output_array['success_output'] = $products;	

				
			}else{
				$output_array['error'] = 'true';
				$output_array['error_output'] = __("Some thing going wrong, contact to developer",'icwoocommerce_textdomains');
			}
			
			echo json_encode($output_array);
			die();
		}
		
		/*
			* Function Name ic_commerce_custom_report_page_export_csv
			*
			* Creates data to export into CSV or EXCEL
			*
			* @param string $export_file_format
			*
			* @return array|object $out
			*			
		*/
		function ic_commerce_custom_report_page_export_csv($export_file_format='csv'){
			global $wpdb, $table_prefix;
			
			$report_name	= $this->get_request('report_name',"no");			
			$columns 		= $this->get_columns($report_name);
			$total_columns	= $this->result_columns($report_name);
			$order_items	= $this->get_items('all_row',$report_name,$columns,$total_columns);
			$summary 		= $this->get_items('total_row',$report_name,$columns,$total_columns);
			
			
			if(isset($columns['product_edit']))	unset($columns['product_edit']);
			
			$columns 			= apply_filters("ic_commerce_report_page_export_csv_columns", $columns);			
			$order_items		= apply_filters("ic_commerce_report_page_export_csv_excel_data",$order_items,$columns, $export_file_format, $report_name);
			
			$order_items		= $this->create_grid_items($order_items, $columns, $report_name, array(), "all_row");
			
			$order_items		= apply_filters("ic_commerce_report_page_export_csv_excel_data_after_get_grid_object",$order_items,$columns, $export_file_format, $report_name);
			
			$price_columns		= apply_filters("ic_commerce_report_page_export_csv_excel_price_columns",array("total_amount"), $report_name);
			
			$export_rows	= array();
			$country      	= $this->get_wc_countries();
			$i 				= 0;
			$date_format	= get_option( 'date_format' );			
			
			$num_decimals   = get_option( 'woocommerce_price_num_decimals'	,	0		);
			$decimal_sep    = get_option( 'woocommerce_price_decimal_sep'	,	'.'		);
			$thousand_sep   = get_option( 'woocommerce_price_thousand_sep'	,	','		);			
			$zero			= number_format(0, $num_decimals,$decimal_sep,$thousand_sep);
			
			foreach ( $order_items as $rkey => $rvalue ):	
				$order_item = $rvalue;			
				foreach($columns as $key => $value):					
					switch ($key) {
							case "cost_of_good_amount":
							case "total_cost_good_amount":
							case "sales_rate_amount":
							case "total_amount":
							case "margin_profit_amount":						
							case "coupon_amount":						
							case "order_shipping":
							case "order_shipping_tax":
							case "order_tax":
							case "total_tax":
							case "gross_amount":
							case "order_discount":
							case "cart_discount":
							case "total_discount":
							case "order_total":
							case "total_amount":						
							case "product_rate":
							case "total_price":	
							case "order_refund_amount":
							case "part_order_refund_amount":
							case "profit_percentage":
							case "min_product_price":
							case "max_product_price":
							
							case "product_sold_rate":
							case "product_total":
							case "product_subtotal":
							case "product_discount":
							
								$td_value 	=  isset($rvalue->$key) ? $rvalue->$key : 0;
								$td_value 	=  strlen($td_value) != 0 ? $td_value : 0;
								$export_rows[$i][$key]	=  $td_value != 0 ? number_format($td_value, $num_decimals,$decimal_sep,$thousand_sep) : $zero;
								break;
							case 'billing_country':
							case 'billing_country':
							case 'shipping_country':
								$export_rows[$i][$key] =  isset($country->countries[$rvalue->$key]) ? $country->countries[$rvalue->$key]: $rvalue->$key;
								break;							
							case "sku":
							case "stock":
								$export_rows[$i][$key] =  $this->get_stock($rvalue->stock);
								break;
							case "product_stock":
								$order_item_id = isset($order_item->order_item_id) ? $order_item->order_item_id : 0;
								$export_rows[$i][$key] =  $this->get_stock_($order_item_id, $order_item->product_id);
								break;
							case "product_sku":
								$order_item_id = isset($order_item->order_item_id) ? $order_item->order_item_id : 0;
								$export_rows[$i][$key] =  $this->get_sku($order_item_id, $order_item->product_id);
								break;
							case "order_date":
							case "post_date":
							case "post_modified":
							case "refund_date":
							case "group_date":
							case "start_date":
							case "end_date":
							case "first_date":
							case "last_date":														
							case "completed_date":
							case "delivery_date":
								$td_value = isset($order_item->$key) ? trim($order_item->$key) : '';
								$td_value = empty($td_value) ? '' : date($date_format,strtotime($td_value));
								$export_rows[$i][$key] = $td_value;
								break;
							case "order_status":
							case "order_status_name":
							case "refund_status":
								$td_value = isset($order_item->$key) ? $order_item->$key : '';
								$export_rows[$i][$key] = ucwords($td_value);
								break;
							default:								
								if(in_array($key, $price_columns)){
									$td_value 	=  isset($rvalue->$key) ? $rvalue->$key : 0;
									$td_value 	=  strlen($td_value) != 0 ? $td_value : 0;
									$export_rows[$i][$key]	=  $td_value != 0 ? number_format($td_value, $num_decimals,$decimal_sep,$thousand_sep) : $zero;
								}else{
									$export_rows[$i][$key] = isset($rvalue->$key) ? $rvalue->$key : '';
								}
								break;
						}
				endforeach;
				$i++;
			endforeach;
			
			$total_columns = $this->result_columns($report_name);
			if(count($total_columns) > 0);{			
				$total_label_flag = false;
				foreach($columns as $key => $value):					
					switch ($key) {
							case "cost_of_good_amount":
							case "total_cost_good_amount":
							case "sales_rate_amount":
							case "total_amount":
							case "margin_profit_amount":						
							case "coupon_amount":						
							case "order_shipping":
							case "order_shipping_tax":
							case "order_tax":
							case "total_tax":
							case "gross_amount":
							case "order_discount":
							case "cart_discount":
							case "total_discount":
							case "order_total":
							case "total_amount":						
							case "product_rate":
							case "total_price":
							
							case "product_sold_rate":
							case "product_total":
							case "product_subtotal":
							case "product_discount":
							case "stock":
							
																		
								$td_value 	=  isset($summary[$key]) ? $summary[$key] : '';
								$td_value 	=  strlen($td_value) !=  0 ? $td_value : 0;
								$export_rows[$i][$key]	=  $td_value != 0 ? number_format($td_value, $num_decimals,$decimal_sep,$thousand_sep) : $zero;
								break;						
							case "ic_commerce_order_item_count":
							case "total_row_count":
							case "quantity":
							case "product_quantity":
								$export_rows[$i][$key] = isset($summary[$key]) ? $summary[$key] : '';
								break;							
							case "product_sku":
							case "billing_first_name":						
							case "payment_method_title":
							case "order_status":
							case "order_id":
							case "billing_first_name":
							case "billing_country":
							case "order_item_name":
								if($total_label_flag)
									$export_rows[$i][$key] = "";
								else{
									$export_rows[$i][$key] = "Total";
									$total_label_flag = true;
								}
								break;						
							case 'product_name':
							case 'order_status':
							case 'ic_commerce_order_billing_name':
							case 'billing_email':
							case 'order_date':
							case 'billing_country':
							case 'shipping_country':								
							case "sku":
							case "stock":
							case 'ic_commerce_order_billing_name':
							case 'ic_commerce_order_tax_name':
							case 'ic_commerce_order_coupon_codes':
							case 'ic_commerce_order_item_count':
							case "ic_commerce_order_status_name":
							case "product_stock":
							case "product_sku":
							case "order_date":
								$export_rows[$i][$key] = '';
								break;
							case "profit_percentage":
								$total_cost_good_amount 	= isset($summary['total_cost_good_amount']) 	? $summary['total_cost_good_amount'] 	: 0;
								$margin_profit_amount 		= isset($summary['margin_profit_amount']) 		? $summary['margin_profit_amount'] 		: 0;
								$profit_percentage 			= isset($summary['profit_percentage']) 			? $summary['profit_percentage'] 		: 0;
								
								if($total_cost_good_amount != 0 and $margin_profit_amount != 0){
									$profit_percentage = ($margin_profit_amount/$total_cost_good_amount)*100;
								}
								
								$export_rows[$i][$key] = sprintf("%.2f",$profit_percentage);
								break;
							default:
								if(in_array($key, $price_columns)){
									$td_value 	=  isset($summary[$key]) ? $summary[$key] : '';
									$td_value 	=  strlen($td_value) != 0 ? $td_value : 0;
									$export_rows[$i][$key]	=  $td_value != 0 ? number_format($td_value, $num_decimals,$decimal_sep,$thousand_sep) : $zero;
								}else{
									$export_rows[$i][$key] = isset($summary[$key]) ? $summary[$key] : '';
								}
								break;
						}
				endforeach;
				$i++;
			}
			
			$export_file_name 		= $this->get_request('export_file_name',"no");
			$report_name 			= $this->get_request('report_name','product_page');
			$report_name 			= str_replace("_page","_list",$report_name);
			
			
			$today_date 		= date_i18n("Y-m-d-H-i-s");				
			$export_filename 	= $export_file_name."-".$report_name."-".$today_date.".".$export_file_format;
			$export_filename 	= apply_filters('ic_commerce_export_csv_excel_format_file_name',$export_filename,$report_name,$today_date,$export_file_name,$export_file_format);
			do_action("ic_commerce_export_csv_excel_format",$export_filename,$export_rows,$columns,$export_file_format,$report_name);
			$out = $this->ExportToCsv($export_filename,$export_rows,$columns,$export_file_format,$report_name);
			
			
			$format		= $export_file_format;
			$filename	= $export_filename;
			
			if($format=="csv"){
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Length: " . strlen($out));	
				header("Content-type: text/x-csv");
				header("Content-type: text/csv");
				header("Content-type: application/csv");
				header("Content-Disposition: attachment; filename=$filename");
			}elseif($format=="xls"){
				
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Length: " . strlen($out));
				header("Content-type: application/octet-stream");
				header("Content-Disposition: attachment; filename=$filename");
				header("Pragma: no-cache");
				header("Expires: 0");
			}
			
			echo $out;
			exit;
		}
		
		/*
			* Function Name ExportToCsv
			*
			* Export to CSV
			*
			* @param string $filename
			*
			* @param string $rows
			*
			* @param string $columns
			*
			* @param string $format
			*
			* @return array|object $out
			*			
		*/
		function ExportToCsv($filename = 'export.csv',$rows,$columns,$format="csv"){				
			global $wpdb;
			$csv_terminated = "\n";
			$csv_separator = ",";
			$csv_enclosed = '"';
			$csv_escaped = "\\";
			$fields_cnt = count($columns); 
			$schema_insert = '';
			
			if($format=="xls"){
				$csv_terminated = "\r\n";
				$csv_separator = "\t";
			}
				
			foreach($columns as $key => $value):
				$l = $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $value) . $csv_enclosed;
				$schema_insert .= $l;
				$schema_insert .= $csv_separator;
			endforeach;
		 
		   $out = trim(substr($schema_insert, 0, -1));
		   $out .= $csv_terminated;
			
			
			
			for($i =0;$i<count($rows);$i++){   
				
				
				$j = 0;
				$schema_insert = '';
				foreach($columns as $key => $value){
						
						
						 if ($rows[$i][$key] == '0' || $rows[$i][$key] != ''){
							if ($csv_enclosed == '')
							{
								$schema_insert .= $rows[$i][$key];
							} else
							{
								$schema_insert .= $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $rows[$i][$key]) . $csv_enclosed;
							}
						 }else{
							$schema_insert .= '';
						 }
						
						if ($j < $fields_cnt - 1)
						{
							$schema_insert .= $csv_separator;
						}
						$j++;
				}
				$out .= $schema_insert;
				$out .= $csv_terminated;
			}
			
			return $out;			
		 
		}
		
		
		/*
			* Function Name ic_commerce_custom_report_page_export_pdf
			*
			* Creates data to export into PDF
			*
			* @param string $export_file_format
			*			
		*/
		function ic_commerce_custom_report_page_export_pdf($export_file_format = "pdf"){
			global $wpdb, $table_prefix;			
			$report_name	= $this->get_request('report_name',"no");
			$columns 		= $this->get_columns($report_name);
			$total_columns	= $this->result_columns($report_name);
			$order_items	= $this->get_items('all_row',$report_name,$columns,$total_columns);
			$summary 		= $this->get_items('total_row',$report_name,$columns,$total_columns);
			
			if(isset($columns['product_edit']))	unset($columns['product_edit']);		
			
			$columns 			= apply_filters("ic_commerce_report_page_export_pdf_columns", $columns);			
			$order_items		= apply_filters("ic_commerce_report_page_export_pdf_data",$order_items,$columns, $export_file_format, $report_name);
			
			$order_items		= $this->create_grid_items($order_items, $columns, $report_name, array(), "all_row");
			
			$order_items		= apply_filters("ic_commerce_report_page_export_pdf_data_after_get_grid_object",$order_items,$columns, $export_file_format, $report_name);
			
			$price_columns		= apply_filters("ic_commerce_report_page_export_pdf_price_columns",array("total_amount"), $report_name);
			
			$export_rows 	= array();
			$country      	= $this->get_wc_countries();
			$i				= 0;
			$zero			= $this->price(0);
			$date_format	= get_option( 'date_format' );
			
			foreach ( $order_items as $rkey => $rvalue ):
					$order_item = $rvalue;
					foreach($columns as $key => $value):
						switch ($key) {
							case 'amount':
							case 'payment_amount_total':
							case 'total_amount':

							case 'Total':
							
							case 'gross_amount':
							case 'discount_value':
							case 'total_amount':
							case 'product_rate':
							case 'total_price':
							
							case 'regular_price':
							case 'sale_price':
							
							case "product_rate_exculude_tax":
							case "product_vat":
							case "product_vat_total":
							case "product_vat_par_item":
							case "product_shipping":
							case "total_price_exculude_tax":
							
							case "cost_of_good_amount":
							case "total_cost_good_amount":
							case "margin_profit_amount":
							case "sales_rate_amount":
							
							case "_order_shipping_amount":
							case "_order_amount":
							case "order_total_amount":
							case "_shipping_tax_amount":
							case "_order_tax":
							case "_total_tax":
							
							case "order_shipping_tax":
							case "order_shipping":
							case "order_tax":
							
							case "order_discount":
							case "cart_discount":
							case "total_discount":
							case "total_tax":
							case "order_total":
							case "order_refund_amount":
							case "part_order_refund_amount":
							case "min_product_price":
							case "max_product_price":
							
							case "product_sold_rate":
							case "product_total":
							case "product_subtotal":
							case "product_discount":
							
								$td_value 				=	isset($rvalue->$key) ? $rvalue->$key : 0;
								$export_rows[$i][$key]	=	$td_value == 0 ? $zero : $this->price($td_value);
								break;
							case 'billing_country':
							case 'billing_country':
							case 'shipping_country':
								$export_rows[$i][$key] =  isset($country->countries[$rvalue->$key]) ? $country->countries[$rvalue->$key]: $rvalue->$key;
								break;							
							case "sku":
							case "stock":
								$export_rows[$i][$key] =  $this->get_stock($rvalue->stock);
								break;
							case "product_stock":
								$order_item_id = isset($order_item->order_item_id) ? $order_item->order_item_id : 0;
								$export_rows[$i][$key] =  $this->get_stock_($order_item_id, $order_item->product_id);
								break;
							case "product_sku":
								$order_item_id = isset($order_item->order_item_id) ? $order_item->order_item_id : 0;
								$export_rows[$i][$key] =  $this->get_sku($order_item_id, $order_item->product_id);
								break;
							case "order_date":
							case "post_date":
							case "post_modified":
							case "refund_date":
							case "group_date":
							case "start_date":
							case "end_date":
							case "first_date":
							case "last_date":														
							case "completed_date":
							case "delivery_date":
								$td_value = isset($order_item->$key) ? trim($order_item->$key) : '';
								$td_value = empty($td_value) ? '' : date($date_format,strtotime($td_value));
								$export_rows[$i][$key] = $td_value;
								break;
							case "order_tax_rate":
								$td_value = isset($order_item->$key) ? $order_item->$key : 0;
								$export_rows[$i][$key] = sprintf("%.2f%%",$td_value);
								break;
							case "profit_percentage":
								$td_value = isset($order_item->$key) ? $order_item->$key : 0;
								$td_value = sprintf("%.2f%%",$td_value);
								$export_rows[$i][$key] =	$td_value;
								break;
							default:
								if(in_array($key, $price_columns)){
									$td_value 				=	isset($rvalue->$key) ? $rvalue->$key : 0;
									$export_rows[$i][$key]	=	$td_value == 0 ? $zero : $this->price($td_value);
								}else{
									$export_rows[$i][$key] = isset($rvalue->$key) ? $rvalue->$key : '';
								}								
								
								break;
						}
					endforeach;				
				$i++;
			endforeach;
			
			$output = $this->get_export_pdf_content($export_rows,$columns,$summary, $price_columns,$total_columns);			
			$this->export_to_pdf($export_rows,$output);
		}
		
		/*
			* Function Name GetDataGrid
			*
			* Get data grid
			*
			* @param array $rows
			*
			* @param array $columns
			*
			* @param array $summary
			*
			* @param array $price_columns
			*
			* @param array $total_columns
			*
			* @return array|object $out
			*			
		*/
		function GetDataGrid($rows=array(),$columns=array(),$summary=array(),$price_columns=array(),$total_columns = array()){
			global $wpdb;
			$csv_terminated = "\n";
			$csv_separator = ",";
			$csv_enclosed = '"';
			$csv_escaped = "\\";
			$fields_cnt = count($columns); 
			$schema_insert = '';
			
			$th_open = '<th class="#class#">';
			$th_close = '</th>';
			
			$td_open = '<td class="#class#">';
			$td_close = '</td>';
			
			$tr_open = '<tr>';
			$tr_close = '</tr>';
			
			
			
			$total_row_amount	= $summary['total_row_amount'];
			$total_row_count	= $summary['total_row_count'];
			
			foreach($columns as $key => $value):
				$l = str_replace("#class#",$key,$th_open) . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $value) . $th_close;
				$schema_insert .= $l;				
			endforeach;

			$company_name	= $this->get_request('company_name','');
			$report_title	= $this->get_request('report_title','');
			$display_logo	= $this->get_request('display_logo','');
			$display_date	= $this->get_request('display_date','');
			$display_center	= $this->get_request('display_center','');
			$report_name	= $this->get_request('report_name',"no");
			$zero			= $this->price(0);
			
			$keywords		= $this->get_request('pdf_keywords','');
			$description	= $this->get_request('pdf_description','');
			$column_align_style = $this->get_pdf_style_align($columns,'right','','', $report_name);
			$date_format 	= get_option( 'date_format' );
			
			$out ='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head>
					<title>'.$report_title.'</title>
						<meta name="description" content="'.$description.'" />
						<meta name="keywords" content="'.$keywords.'" />
						<meta name="author" content="'.$company_name.'" />
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
						<style type="text/css"><!--
						*{ font-family: DejaVu Sans !important;}-- 
						.header {position: fixed; top: -40px; text-align:center;}
						.header h2{font-size:16px;}
						  .footer { position: fixed; bottom: 0px; text-align:center;}
						  .pagenum:before { content: counter(page); }
					/*.Container{width:750px; margin:0 auto; border:1px solid black;}*/
					body{font-family: "Source Sans Pro", sans-serif; font-size:10px;}
					span{font-weight:bold;}
					.Clear{clear:both; margin-bottom:10px;}
					/*label{width:100px; float:left; }*/
					.sTable3{border:1px solid #DFDFDF; }
					.sTable3 th{
						padding:10px 10px 7px 10px;
						background:#eee url(../images/thead.png) repeat-x top left;
						/*border-bottom:1px solid #DFDFDF;*/
						text-align:left;
						}
					.Form{padding:1% 1% 11% 1%; margin:5px 5px 5px 5px;}
					.myclass{border:1px solid black;}
						
					.sTable3 tbody tr td{padding:8px 10px; background:#fff; border-top:1px solid #DFDFDF; border-right:1px solid #DFDFDF;}
					.sTable3 tbody tr.AltRow td{background:#FBFBFB;}
					.print_header_logo.center_header, .header.center_header{margin:auto;  text-align:center;}
					.print_summary_bottom{ margin-top:10px;font-size:14px;}
					.print_summary_bottom strong{ font-size:15px;}
					td span.amount{ text-align:right; margin-right:0}
					label.report_title{font-size:12px;font-weight:bold}					
					.print_summary_bottom2 .sTable3{ width:auto; text-align:left; margin-left:0;}
					'.$column_align_style.'--></style>
					</head>
					<body>';
			
			
			
			$logo_html		=	"";
			
			if(strlen($display_logo) > 0){
				$company_logo	=	$logo_image 			= $this->get_setting('logo_image',$this->constants['plugin_options'], '');
				$upload_dir 	= wp_upload_dir(); // Array of key => value pairs
				$company_logo	= str_replace($upload_dir['baseurl'],$upload_dir['basedir'],$company_logo);
				
				$logo_html 		= "<div class='Clear  print_header_logo ".$display_center."'><img src='".$company_logo."' alt='' /></div>";
			}else{
				
			}
			if(strlen($company_name) > 0)	$out .="<div class='header ".$display_center."'><h2>".stripslashes($company_name)."</h2></div>";			
			$out .="<div class='footer'>Page: <span class='pagenum'></span></div>";
			$out .= "<div class='Container1'>";
			$out .= "<div class='Form1'>";
			$out .= $logo_html;
			
			if(strlen($company_name) > 0 || strlen($display_logo) > 0)
			$out .= "<hr class='myclass1'>";
			
			if(strlen($report_title) > 0)	$out .= "<div class='Clear'><label>Report Title: </label><label>".stripslashes($report_title)."</label></div>";
			$out .= "<div class='Clear'></div>";
			if($display_date) $out .= "<div class='Clear'><label>".__( 'Date:', 'icwoocommerce_textdomains' )." </label><label>".date_i18n($date_format)."</label></div>";
			
			$out .= "<div class='Clear'></div>";
			$out .= "<div class='Clear'>";			
			$out .= "<table class='sTable3' cellpadding='0' cellspacing='0' width='100%'>";
			$out .= "<thead>";
			$out .= $tr_open;			
			$out .= trim(substr($schema_insert, 0, -1));
			$out .= $tr_close;
			$out .= "</thead>";			
			$out .= "<tbody>";			
			$out .= $csv_terminated;
			
			$last_order_id = 0;
			$alt_order_id = 0; 
			for($i =0;$i<count($rows);$i++){   			
				$j = 0;
				$schema_insert = '';
				foreach($columns as $key => $value){
						 if ($rows[$i][$key] == '0' || $rows[$i][$key] != ''){
							if ($csv_enclosed == '')
							{
								$schema_insert .= $rows[$i][$key];
							} else
							{
								$schema_insert .= str_replace("#class#",$key,$td_open) . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $rows[$i][$key]) . $td_close;
								
							}
							
						 }else{
							$schema_insert .= $td_open.''.$td_close;;
						 }
						$j++;
				}				
				$out .= $tr_open;
				$out .= $schema_insert;
				$out .= $tr_close;			
			}

			$out .= "</tbody>";
			$out .= "</table>";	
			$out .= "</div>";
			
			$summary_data = $this->result_grid($report_name,$summary,$zero, $total_columns, $price_columns);
			if(!empty($summary_data)){
				$out .= "<div class=\"print_summary_bottom\">";
				
				$out .= __("Summary Total:",'icwoocommerce_textdomains');
				$out .= "</div>";
				$out .= "<div class=\"print_summary_bottom2\">";
				$out .= "<br />";
				$out .= $summary_data;
				$out .= "</div>";
			}
			$out .= "</div></div></body>";			
			$out .="</html>";
			return $out;
		}
		
		/*
			* Function Name get_product_name
			*
			* Get product name
			*
			* @param string $id
			*
			* @param string $by
			*
			* @return array|object $first_order_date
			*			
		*/
		function get_product_name($id, $by = 'ID'){
			global $wpdb;
			$sql = "SELECT post_title  FROM {$wpdb->posts}  AS posts	WHERE posts.ID='{$id}' LIMIT 1";
								
			return $first_order_date = $wpdb->get_var($sql );
		}
		
		/*
			* Function Name page_title
			*
			* Get page title
			*
			* @param string $title
			*
			* @return array|object $title
			*			
		*/
		function page_title($title){
			$title = str_replace("_"," ",$title);
			$title = str_replace("-"," ",$title);
			//$title = Ucwords($title);
			return $title;
		}
		
		/*
			* Function Name get_all_request
			*
			* Get all variable's values
			*
			* @return array|object $request
			*			
		*/
		function get_all_request(){
			global $request, $back_day;
			if(!$this->request){
				$request 			= array();
				$start				= 0;
				
				do_action("ic_commerce_report_page_before_default_request");
				
				$limit 				= $this->get_request('limit',3,true);
				$p 					= $this->get_request('p',1,true);				
				$page				= $this->get_request('page',NULL);				
				$report_name		= $this->get_request('report_name',"product_page",true);
				$order_status		= $this->get_request('order_status',"-1",true);
				$category_id		= $this->get_request('category_id','-1',true);
				$product_id			= $this->get_request('product_id','-1',true);
				$order_status_id	= $this->get_request('order_status_id','-1',true);	
				$parent_category_id	= $this->get_request('parent_category_id','-1',true);
				$child_category_id	= $this->get_request('child_category_id','-1',true);
				$group_by_parent_cat= $this->get_request('group_by_parent_cat',0,true);
				$paid_customer		= $this->get_request('paid_customer','-1',true);
				$cost_of_goods_only	= $this->get_request('cost_of_goods_only','no',true);
				$country_code		= $this->get_request('country_code','-1',true);
				$state_code 		= $this->get_request('state_code','-1',true);
				$tax_group_by 		= $this->get_request('tax_group_by','-1',true);
				$order_by 			= $this->get_request('list_parent_category',NULL,true);
				$order_by 			= $this->get_request('cost_of_goods_only',"no",true);
				$product_status 	= $this->get_request('product_status',"-1",true);
				$product_type 		= $this->get_request('product_type',"-1",true);
				
				$start_date  		= $this->get_request('start_date  ','', true);
				$end_date 			= $this->get_request('end_date ','',true);
				
				if($report_name == "manual_refund_detail_page"){
					$group_by 			= $this->get_request('group_by','refund_id',true);
					$refund_status_type = $this->get_request('refund_status_type','part_refunded',true);
					if($refund_status_type == "part_refunded"){
						if($group_by == "order_id"){
							//$_REQUEST['group_by'] = 'refund_id';
						}
					}else{
						if($group_by == "refund_id"){
							$_REQUEST['group_by'] = 'order_id';
						}
					}
				}
				
				if($report_name == "coupon_page" || $report_name == "coupon_couontry_page"){
					$coupon_code		= $this->get_request('coupon_code','-1',true);	
					$coupon_codes		= $this->get_request('coupon_codes','-1',true);	
					$discount_types		= $this->get_request('coupon_discount_types','-1',true);	
					$country_code		= $this->get_request('country_code','-1',true);	
					
					$sort_by			= $this->get_request('sort_by','total_amount',true);
				}
				
				$sort_by 			= $this->get_request('sort_by','-1',true);
				$order_by 			= $this->get_request('order_by','DESC',true);
				
				$this->common_request_form();
				
				
				
				if($p > 1){	$start = ($p - 1) * $limit;}				
				$_REQUEST['start']= $start;
				
				if(isset($_REQUEST)){
					$REQUEST = $_REQUEST;
					$REQUEST = apply_filters("ic_commerce_before_request_creation", $REQUEST);
					foreach($REQUEST as $key => $value ):						
						$request[$key] =  $this->get_request($key,NULL);
					endforeach;
					$request = apply_filters("ic_commerce_after_request_creation", $request);
				}
				$this->request = $request;				
			}else{				
				$request = $this->request;
			}
			
			return $request;
		}
		
		/*
			* Function Name _get_string_multi_request
			*
			* Get multiple values comma separated in one string
			*
			* @param string $string
			*
			* @param string $default
			*
			* @return array|object $string
			*			
		*/
		function _get_string_multi_request($string, $default = NULL){
			if($string == "'-1'" || $string == "\'-1\'"  || $string == "-1" ||$string == "''" || strlen($string) <= 0)$string = $default;
			if(strlen($string) > 0 and $string != $default){ $string  		= "'".str_replace(",","','",$string)."'";}
			return $string;
		}
		
		/*
			* Function Name _get_string_multi_request
			*
			* Get multiple values comma separated in one string
			*
			* @param string $id
			*
			* @param string $string
			*
			* @param string $default
			*
			* @return array|object $string
			*			
		*/
		var $request_string = array();
		function get_string_multi_request($id=1,$string, $default = NULL){
			
			if(isset($this->request_string[$id])){
				$string = $this->request_string[$id];
			}else{
				if($string == "'-1'" || $string == "\'-1\'"  || $string == "-1" ||$string == "''" || strlen($string) <= 0)$string = $default;
				if(strlen($string) > 0 and $string != $default){ $string  		= "'".str_replace(",","','",$string)."'";}
				$this->request_string[$id] = $string;			
			}
			
			return $string;
		}
		
		/*
			* Function Name get_paying_state
			*
			* Get list of States
			*
			* @param string $state_key
			*
			* @param string $country_key
			*
			* @param string $deliter
			*
			* @return array|object $results
			*			
		*/
		function get_paying_state($state_key = 'billing_state',$country_key = false, $deliter = "-"){
			global $wpdb;
			if($country_key){
				
				$sql = "SELECT billing_by.meta_value as id, billing_by.meta_value as label, billing_country.meta_value as billing_country ";
			}else
				$sql = "SELECT billing_by.meta_value as id, billing_by.meta_value as label ";
			
			$sql .= "
				FROM `{$wpdb->posts}` AS posts
				LEFT JOIN {$wpdb->postmeta} as billing_by ON billing_by.post_id=posts.ID";
			if($country_key)
				$sql .= " 
				LEFT JOIN {$wpdb->postmeta} as billing_country ON billing_country.post_id=posts.ID";
			$sql .= "
				WHERE billing_by.meta_key='_{$state_key}' AND posts.post_type='shop_order'
			";
			
			if($country_key)
				$sql .= "
				AND billing_country.meta_key='_{$country_key}'";
			
			$sql .= " 
			GROUP BY billing_by.meta_value
			ORDER BY billing_by.meta_value ASC";
			
			$results	= $wpdb->get_results($sql);
			$country    = $this->get_wc_countries();
			
			if($country_key){
				foreach($results as $key => $value):
						$v = $this->get_state($value->billing_country, $value->label);
						$v = trim($v);
						if(strlen($v)>0)
							$results[$key]->label = $v ." (".$value->billing_country.")";
						else
							unset($results[$key]);
				endforeach;
			}else{
				
				foreach($results as $key => $value):
						$v = isset($country->countries[$value->label]) ? $country->countries[$value->label]: $value->label;
						$v = trim($v);
						if(strlen($v)>0)
							$results[$key]->label = $v;
						else
							unset($results[$key]);
				endforeach;
			}
			return $results; 
		}
		
		/*
			* Function Name get_state
			*
			* Get list of States
			*
			* @param string $cc
			*
			* @param string $st
			*
			* @return array|object $state_code
			*			
		*/
		function get_state($cc = NULL,$st = NULL){
			global $woocommerce;
			$state_code = $st;
			
			if(!$cc) return $state_code;
			
			$states 			= $this->get_wc_states($cc);

			if(is_array($states)){
				foreach($states as $key => $value){

					if($key == $state_code)
						return $value;
				}
			}else if(empty($states)){
				return $state_code;
			}			
			return $state_code;
		}
		
		/*
			* Function Name _get_setting
			*
			* Get setting values
			*
			* @param string $id
			*
			* @param string $data
			*
			* @param string $defalut
			*
			* @return array|object $data
			*
			* @return array|object $defalut
			*			
		*/
		function _get_setting($id, $data, $defalut = NULL){
			if(isset($data[$id]))
				return $data[$id];
			else
				return $defalut;
		}
		
		/*
			* Function Name print_header
			*
			* Print header in PDF
			*
			* @param string $type
			*
			* @param string $report_title
			*
			* @return array|object $out
			*			
		*/
		function print_header($type = NULL, $report_title = NULL){
			$out = "";
			
			if($type == 'all_row'){
				
				$company_name	= $this->get_request('company_name','');
				$report_title	= $this->get_request('report_title','');
				$display_logo	= $this->get_request('display_logo','');
				$display_date	= $this->get_request('display_date','');
				$display_center	= $this->get_request('display_center','');
				$date_format	= $this->get_request('date_format','jS F Y');
				
				$print_header_logo = "print_header_logo";				
				if($display_center) $print_header_logo .= " center_header";
				
				$out .= "<div class=\"print_header\">";
				if($company_name or $display_logo){
					$out .= "	<div class=\"".$print_header_logo."\">";
					if(strlen($company_name) > 0)	$out .= "<div class='header'><h2>".stripslashes($company_name)."</h2></div>";
					if(strlen($display_logo) > 0 and $display_logo == 1){
						$logo_image = $this->get_setting('logo_image',$this->constants['plugin_options'], '');
						$out 		.= "<div class='clear'><img src='".$logo_image."' alt='' /></div>";
					}				
					$out .= "	</div>";
				}
				if(strlen($report_title) > 0)	$out .= "<div class='clear'><label class=\"report_title\">".stripslashes($report_title)."</label></div>";
				if(strlen($display_date) > 0)	$out .= "<div class='Clear'><label>Report Date: </label> <label>".date_i18n($date_format)."</label></div>";
				$out .= "</div>";
			}
			
			echo $out;
		}
		
		/*
			* Function Name get_sold_product_parent_category_data
			*
			* Get sold products parent category data
			*
			* @return array|object $category_items
			*			
		*/
		function get_sold_product_parent_category_data(){
			global $wpdb;

			$request = $this->get_all_request();extract($request);
			
			$hide_order_status	= $this->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
			
			$sql ="";
			$sql .= " SELECT ";
			$sql .= " term_taxonomy_product_id.parent AS id";
			$sql .= " ,terms_parent_product_id.name AS label";
			
			$sql .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items";
			
			
			$sql .= " LEFT JOIN  {$wpdb->posts} as posts ON posts.id=woocommerce_order_items.order_id";
			
			$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_product_id ON woocommerce_order_itemmeta_product_id.order_item_id=woocommerce_order_items.order_item_id";
			
			$sql .= " 	LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships_product_id 	ON term_relationships_product_id.object_id		=	woocommerce_order_itemmeta_product_id.meta_value 
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy_product_id 		ON term_taxonomy_product_id.term_taxonomy_id	=	term_relationships_product_id.term_taxonomy_id
						LEFT JOIN  {$wpdb->prefix}terms 				as terms_product_id 				ON terms_product_id.term_id						=	term_taxonomy_product_id.term_id";
			
			$sql .= " 	LEFT JOIN  {$wpdb->prefix}terms 				as terms_parent_product_id 				ON terms_parent_product_id.term_id						=	term_taxonomy_product_id.parent";
			
			$sql .= " WHERE 1*1 ";
			$sql .= " AND woocommerce_order_items.order_item_type 	= 'line_item'";
			$sql .= " AND woocommerce_order_itemmeta_product_id.meta_key 	= '_product_id'";
			$sql .= " AND term_taxonomy_product_id.taxonomy 	= 'product_cat'";
			$sql .= " AND term_taxonomy_product_id.parent > 0";
			
			
			$sql .= " AND posts.post_type 											= 'shop_order'";				
			
			if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND posts.post_status NOT IN (".$hide_order_status.")";
			
			$sql .= " GROUP BY term_taxonomy_product_id.parent";
			
			$sql .= " ORDER BY terms_parent_product_id.name ASC";
			
			$category_items = $wpdb->get_results($sql);
			
			if($wpdb->last_error){
				echo $wpdb->last_error;
			}
			return $category_items;
		}
		
		/*
			* Function Name get_sold_product_child_category_data
			*
			* Get sold products child category data
			*
			* @return array|object $category_items
			*			
		*/
		function get_sold_product_child_category_data(){
			global $wpdb;
			$sql ="";
			$sql .= " SELECT ";
			
			$sql .= " terms_product_id.term_id AS id";
			$sql .= " ,terms_product_id.name AS label";
			$sql .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items";
			$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_product_id ON woocommerce_order_itemmeta_product_id.order_item_id=woocommerce_order_items.order_item_id";
			
			$sql .= " 	LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships_product_id 	ON term_relationships_product_id.object_id		=	woocommerce_order_itemmeta_product_id.meta_value 
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy_product_id 		ON term_taxonomy_product_id.term_taxonomy_id	=	term_relationships_product_id.term_taxonomy_id
						LEFT JOIN  {$wpdb->prefix}terms 				as terms_product_id 				ON terms_product_id.term_id						=	term_taxonomy_product_id.term_id";
			
			$sql .= " 	LEFT JOIN  {$wpdb->prefix}terms 				as terms_parent_product_id 				ON terms_parent_product_id.term_id						=	term_taxonomy_product_id.parent";
			
			$sql .= " LEFT JOIN  {$wpdb->posts} as posts ON posts.id=woocommerce_order_items.order_id";
			
			$sql .= " WHERE 1*1 ";
			$sql .= " AND woocommerce_order_items.order_item_type 					= 'line_item'";
			$sql .= " AND woocommerce_order_itemmeta_product_id.meta_key 			= '_product_id'";
			$sql .= " AND term_taxonomy_product_id.taxonomy 						= 'product_cat'";
			$sql .= " AND posts.post_type 											= 'shop_order'";
			
			$sql .= " AND term_taxonomy_product_id.parent > 0";
			
			$sql .= " GROUP BY label";
			
			$sql .= "  Order By label ASC";
			
			$category_items = $wpdb->get_results($sql);
			
			if($wpdb->last_error){
				echo $wpdb->last_error;
			}
			return $category_items;
		}
		
		/*
			* Function Name get_customer_details
			*
			* Get customer details data
			*
			* @return array|object $users_array
			*			
		*/
		function get_customer_details($users=array()){
			global $wpdb;
				$sql = " SELECT ";
				$sql .= "	users.ID											AS user_id
							,postmeta_billing_billing_email.meta_value			AS billing_email
							,CONCAT(postmeta_billing_first_name.meta_value,' ',postmeta_billing_last_name.meta_value)		AS billing_name							
							";
				$sql .= "	FROM {$wpdb->users} as users";
							
				
				$sql .= " 	LEFT JOIN  {$wpdb->usermeta} as postmeta_billing_first_name ON postmeta_billing_first_name.user_id		=	users.ID";
				$sql .= " 	LEFT JOIN  {$wpdb->usermeta} as postmeta_billing_last_name ON postmeta_billing_last_name.user_id			=	users.ID";
				$sql .= " 	LEFT JOIN  {$wpdb->usermeta} as postmeta_billing_billing_email ON postmeta_billing_billing_email.user_id	=	users.ID";
				
							
				$sql .= "
							WHERE 
							postmeta_billing_first_name.meta_key		= 'billing_first_name'
							AND postmeta_billing_last_name.meta_key		= 'billing_last_name'
							AND postmeta_billing_billing_email.meta_key	= 'billing_email'
							
							AND LENGTH(TRIM(CONCAT(postmeta_billing_first_name.meta_value,' ',postmeta_billing_last_name.meta_value)))>0
							
							";
				if(count($users)>0) {
					$users_string = implode(",",$users);
					$sql .= "AND users.ID IN ({$users_string})";
				}
						
				$users_list 	= $wpdb->get_results($sql);
				$users_array	= array();
				if($wpdb->last_error){
					echo $wpdb->last_error;
				}else{
					if(count($users_list) > 0){
						foreach($users_list as $key => $value){
							$users_array[$value->user_id] = $value;
						}
					}
				}				
				
				return $users_array;
		}
		
		/*
			* Function Name get_order_customer2
			*
			* Get billing information of customer
			*
			* @param string $post_type
			*
			* @param string $post_status
			*
			* @return array|object $products_category
			*			
		*/
		function get_order_customer2($post_type = 'shop_order',$post_status = 'no'){
				global $wpdb;
				
				
				$sql = "SELECT 
				billing_email.meta_value AS id, 
				concat(billing_first_name.meta_value, ' ',billing_last_name.meta_value) AS label
					FROM `{$wpdb->posts}` AS posts
					LEFT JOIN  {$wpdb->postmeta} as customer_user ON customer_user.post_id=posts.ID
					LEFT JOIN  {$wpdb->postmeta} as billing_first_name ON billing_first_name.post_id=posts.ID
					LEFT JOIN  {$wpdb->postmeta} as billing_last_name ON billing_last_name.post_id=posts.ID
					LEFT JOIN  {$wpdb->postmeta} as billing_email ON billing_email.post_id=posts.ID
				";
				$sql .= " WHERE 
					post_type='{$post_type}' 
				AND customer_user.meta_key = '_customer_user'
				AND billing_first_name.meta_key = '_billing_first_name'
				AND billing_last_name.meta_key = '_billing_last_name'
				AND billing_email.meta_key = '_billing_email'
				";
				$sql .= " 
				GROUP BY billing_email.meta_value
				ORDER BY label  ASC";
				
				$products_category = $wpdb->get_results($sql);
				return $products_category; 
		}
		
		/*
			* Function Name get_category_name_by_product_id
			*
			* Get category name by product ID
			*
			* @param string $id
			*
			* @param string $taxonomy
			*
			* @param string $termkey
			*
			* @return array|object $term_name
			*			
		*/
		var $terms_by = array();
		function get_category_name_by_product_id($id, $taxonomy = 'product_cat', $termkey = 'name'){
			$term_name ="";			
			if(!isset($this->terms_by[$taxonomy][$id])){
				$id			= (integer)$id;
				$terms		= get_the_terms($id, $taxonomy);
				$termlist	= array();
				if($terms and count($terms)>0){
					foreach ( $terms as $term ) {
							$termlist[] = $term->$termkey;
					}
					if(count($termlist)>0){
						$term_name =  implode( ', ', $termlist );
					}
				}
				$this->terms_by[$taxonomy][$id] = $term_name;				
			}else{				
				$term_name = $this->terms_by[$taxonomy][$id];
			}					
			return $term_name;
		}
		
		/*
			* Function Name create_search_form_hidden_fields
			*
			* Create hidden fields
			*
			* @param string $request
			*
			* @param string $type
			*
			* @return array|object $output_fields
			*			
		*/
		function create_search_form_hidden_fields($request = array(), $type = "hidden"){
			$output_fields = "";
			foreach($request as $key => $value):
				$output_fields .=  "\n<input type=\"{$type}\" name=\"{$key}\" id=\"{$key}\" value=\"{$value}\" />";
			endforeach;
			return $output_fields;
		}
		
		/*
			* Function Name create_summary
			*
			* Create summary
			*
			* @param string $request
			*
			* @return array|object $summary
			*
		*/
		function create_summary($request = array()){
			$report_name 		= $this->get_request('report_name');
			$total_columns 		= $this->result_columns($report_name);
			$summary 			= array();
			$summary['total_row_amount'] 		= isset($request['total_row_amount']) 		? $request['total_row_amount'] : '';
			$summary['total_row_count'] 		= isset($request['total_row_count']) 		? $request['total_row_count'] : '';
			
			if(count($total_columns) > 0){
				foreach($total_columns as $key => $label):
					$summary[$key] 	= isset($request[$key]) 	? $request[$key] : 0;
				endforeach;
			}
			return $summary;						
		}
	
	}//END class 
}//END Clas check