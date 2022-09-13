<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if (!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Product_Wise_New_Customer')) {
	include_once("ic_commerce_ultimate_report_functions.php");
	class IC_Commerce_Ultimate_Woocommerce_Report_Product_Wise_New_Customer  extends  IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		
		/* variable declaration*/
		public $constants 	=	array();
		
		/* variable declaration*/
		public $request		=	array();
		
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
			
		}
		
		/*
			* Function Name init
			*
			* Creates Search form, assigning values to variables.
			*		 
		*/
		function init(){
		
			$input_type  = "hidden";
			
			if ( !current_user_can( $this->constants['plugin_role'] ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ,'icwoocommerce_textdomains' ) );
			}
			
			$onload_search				= apply_filters('ic_commerce_onload_search', 'no', '');
			
			
			$start_date  = $this->get_request("start_date",date_i18n("Y-m-d"),true);
			$end_date  = $this->get_request("end_date",date_i18n("Y-m-d"),true);
			$customer_type =	isset($_REQUEST["customer_type"])?$_REQUEST["customer_type"] : 'new_customer';
			
			$page =	isset($_REQUEST["page"]) ? $_REQUEST["page"] : '';
			$page_titles = array(
					'new_customer'			=> __('New Customer',		'icwoocommerce_textdomains')
					,'repeat_customer'		=> __('Repeat Customer',  	'icwoocommerce_textdomains')				
				);
		?>
		
		<div id="navigation" class="hide_for_print">
         	<div class="collapsible collapse-open" id="section1"><?php _e('Custom Search','icwoocommerce_textdomains'); ?><span></span></div>
           	<div class="container">
            	<div class="content">
                	 <div class="search_report_form">
                     	  <div class="form_process"></div>
                           <div class="form-table">
                           		<form id="search_order_report" name="search_order_report">
                                <div class="form-group">
                                    <div class="FormRow FirstRow">
                                        <div class="label-text"><label for="start_date"><?php _e('From Date:','icwoocommerce_textdomains');?></label></div>
                                        <div class="input-text">
                                        <input type="text" name="start_date" id="start_date"  class="_date" value="<?php echo $start_date; ?>" readonly maxlength="10" />
                                        </div>
                                    </div>
                                    <div class="FormRow">
                                        <div class="label-text"><label for="end_date"><?php _e('To Date:','icwoocommerce_textdomains');?></label></div>
                                        <div class="input-text">
                                        <input type="text" name="end_date"   id="end_date" 	class="_date" value="<?php echo $end_date; ?>" readonly maxlength="10" />
                                        </div>
                                    </div>
                                </div>
                           	
                              	
								<input type="<?php echo $input_type; ?>" name="do_action_type" value="product_new_repeat_customer" />
                                <input type="<?php echo $input_type; ?>" name="action" value="<?php echo $this->constants['plugin_key'].'_wp_ajax_action';?>" />
                                <input type="<?php echo $input_type; ?>" name="limit" value="<?php echo $this->get_request("limit",'5',true);?>"/>
								
								<input type="<?php echo $input_type; ?>" name="customer_type" value="<?php echo $customer_type ;  ?>" />
								
                                
                                <input type="<?php echo $input_type; ?>" name="page" value="<?php echo $this->get_request('page','',true);?>" />
                                <input type="<?php echo $input_type; ?>" name="admin_page" value="<?php echo $this->get_request('page','',true);?>" />
                                
                                  <span class="submit_buttons">
                                	<input type="submit" value="<?php _e('Search','icwoocommerce_textdomains');?>" name="btnSearch" id="SearchOrder" class="onformprocess"/>
                                </span>
								
                                 
                            </form>	
                          </div>
                     </div>
                </div>
            </div>
         </div>
         
       
        <div class="search_report_content hide_for_print autoload_<?php echo $onload_search;?>">
			<?php if($onload_search == "no") {echo apply_filters('ic_commerce_onload_search_text', '');}?>
        </div>
		<div class="_ajax_data"></div>
        <?php	
		}/*old Customer*/
		
		/*
			* Function Name get_old_customer_billing_email
			*
			* @param string $start_date
			*
			* @param string $end_date
			*
			* return old_customer_billing_email
		*/
		function get_old_customer_billing_email($start_date = '',$end_date = ''){
			global $wpdb;
			
			if(!isset($this->constants['old_customer_billing_email'])){
				$sql = "SELECT billing_email.meta_value AS billing_email";
				
				$sql .= ", DATE(shop_order.post_date) 					AS post_date ";
				
				$sql .= " FROM $wpdb->posts AS shop_order";
				$sql .= " LEFT JOIN $wpdb->postmeta AS billing_email ON billing_email.post_id = shop_order.ID";
				$sql .= " WHERE 1*1";
				$sql .= " AND shop_order.post_type = 'shop_order'";
				$sql .= " AND billing_email.meta_key = '_billing_email'";
				if($start_date != "" || $start_date != "-1"){
					$sql .= " AND date_format(shop_order.post_date, '%Y-%m-%d') < '".$start_date."'";
				}
				$sql .= " GROUP BY billing_email.meta_value";
				
				$order_items = $wpdb->get_results($sql);
				
				$billing_emails = $this->get_items_id_list($order_items,'billing_email');
				
				$this->constants['old_customer_billing_email'] = $billing_emails;
			}
			
			return $this->constants['old_customer_billing_email'];
		}
		
		
		/*
			* Function Name get_new_customer_billing_email
			*
			* @param string $start_date
			*
			* @param string $end_date
			*
			* @param string $old_customer_billing_email
			*
			* return new_customer_billing_email
		*/
		function get_new_customer_billing_email($start_date = '',$end_date = '', $old_customer_billing_email = ''){
			global $wpdb;
			
			if(!isset($this->constants['new_customer_billing_email'])){
			
				$sql = "SELECT billing_email.meta_value AS billing_email";
				
				$sql .= ", DATE(shop_order.post_date) 					AS post_date ";
				
				$sql .= " FROM $wpdb->posts AS shop_order";
				
				$sql .= " LEFT JOIN $wpdb->postmeta AS billing_email ON billing_email.post_id = shop_order.ID";
				
				$sql .= " WHERE 1*1";
				
				$sql .= " AND shop_order.post_type = 'shop_order'";
				
				$sql .= " AND billing_email.meta_key = '_billing_email'";
				
				if($start_date != "" || $start_date != "-1"){
					//$sql .= " AND DATE(shop_order.post_date) BETWEEN '".$start_date."' AND '". $end_date ."'";
					
					$sql .= " AND  date_format(shop_order.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}' ";
				}
				
				if($old_customer_billing_email != "" and $old_customer_billing_email != "-1"){
					$old_customer_billing_email = str_replace(",","', '",$old_customer_billing_email);
					$sql .= " AND billing_email.meta_value NOT IN('{$old_customer_billing_email}')";
				}
				
			    $sql .= " GROUP BY billing_email.meta_value";
				
				$order_items = $wpdb->get_results($sql);
				
				$billing_emails 			= $this->get_items_id_list($order_items,'billing_email');
				
				$this->constants['new_customer_billing_email'] = $billing_emails;
			}
			
			return $this->constants['new_customer_billing_email'];
		}
		
		/*
			* Function Name ajax
		*/
		function ajax(){
			$input_type  = "hidden";
			
			$customer_type 	= $this->get_request("customer_type","new_repeat_customer",true);
			$start_date  	= $this->get_request("start_date",date_i18n("Y-m-d"),true);
			$end_date  	 	= $this->get_request("end_date",date_i18n("Y-m-d"),true);
			$customer_row  	= array();
			$customer_count = 0;
			$columns       	= $this->get_columns();
			$limit 			= $this->get_request("limit",2,true);
			
			$old_customer_billing_email 		= $this->get_old_customer_billing_email($start_date,$end_date);
			
			if ($customer_type == "new_customer"){
				$new_customer_billing_email 		= $this->get_new_customer_billing_email($start_date,$end_date, $old_customer_billing_email);
				if (strlen($new_customer_billing_email )>0 && $new_customer_billing_email !='' && $new_customer_billing_email != "-1"){
					$customer_row = $this->get_customer($new_customer_billing_email, $start_date  ,$end_date  );
					$customer_count = $this->get_customer($new_customer_billing_email, $start_date  ,$end_date,"count");
				}
			}
			if ($customer_type == "repeat_customer"){
				if (strlen($old_customer_billing_email )>0 && $old_customer_billing_email !='' && $old_customer_billing_email != "-1"){
					$customer_row =  $this->get_customer($old_customer_billing_email ,$start_date  ,$end_date );
					$customer_count = $this->get_customer($old_customer_billing_email, $start_date  ,$end_date, "count");
					
				}
			}
			$count = $customer_count;
			?>
            <?php if ($count>0): ?> 
                
				<form name="frm_export_product_wise_new_customer" id="frm_export_product_wise_new_customer">
					
					<input type="<?php echo $input_type; ?>"  name="p" value="1"/>
                  	<input type="<?php echo $input_type; ?>" name="limit" value="<?php echo $this->get_request("limit",'5',true);?>"/>
                    <input type="<?php echo $input_type; ?>"  name="total_row" value="<?php echo $count; ?>"/>
                    <input type="<?php echo $input_type; ?>"  name="start_date" id="start_date" value="<?php echo $start_date ; ?>" />
                    <input type="<?php echo $input_type; ?>"  name="end_date" id="end_date"  value="<?php echo $end_date ; ?>" />
					<input type="<?php echo $input_type; ?>"  name="customer_type" value="<?php echo $customer_type ;  ?>" />
                    <input type="<?php echo $input_type; ?>"  name="export_file_format" id="export_file_format"  value="csv" />
                 
               		<input type="<?php echo $input_type; ?>" name="do_action_type" value="product_new_repeat_customer" />
                    <input type="<?php echo $input_type; ?>" name="action" value="<?php echo $this->constants['plugin_key'].'_wp_ajax_action';?>" />
				
				<div class="top_buttons">
                	<div class="RegisterDetailExport">
                    	<input type="submit" name="<?php echo $this->constants['plugin_key'].'_product_wise_new_customer_export_csv';?>" id="export_product_new_repeat_customer" value="<?php _e('Export','icwoocommerce_textdomains'); ?>" class="onformprocess" />  
                    </div>
                	
                </div>
                    
                
                </form>
            <?php endif; ?>
            <style type="text/css">
            	th.total_amount{ text-align:right;}
            </style>
            <table style="width:100%" class="widefat widefat_normal_table" cellpadding="0" cellspacing="0">
            	<thead>
            	<tr>
                <?php foreach($columns as $key=>$value): ?>
                	<th class="<?php echo $key;?>"><?php echo $value; ?></th>
				<?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php if (count($customer_row)>0): ?>
                 <?php 	if($key%2 == 1){$alternate = "alternate ";}else{$alternate = "";}; ?>
                	<?php foreach($customer_row as $key=>$value): ?>
                      <tr class="<?php echo $alternate."row_".$key;?>">
                        <td><?php echo $value->order_item_name; ?></td>
                        <td><?php echo $value->billing_first_name; ?></td>
                        <td><?php echo $value->billing_last_name; ?></td>
                        <td><?php echo $value->billing_email; ?></td>
                        <td style="text-align:right"><?php echo $this->get_woo_price( $value->total_amount); ?></td>
                    </tr>
				<?php endforeach; ?>
                <?php else: ?>
					<tr>
                    	<td colspan="<?php echo count($columns); ?>"> <?php _e('No record found','icwoocommerce_textdomains'); ?></td>
                    </tr>                
                <?php endif; ?>
                
              </tbody>  
            </table>
			
			<form name="search_order_pagination" id="search_order_pagination" method="post">
                <input type="<?php echo $input_type; ?>"  name="p" value="1"/>
                <input type="<?php echo $input_type; ?>"  name="total_row" value="<?php echo $count; ?>"/>
                <input type="<?php echo $input_type; ?>"  name="start_date" id="start_date" value="<?php echo $start_date ; ?>" />
                <input type="<?php echo $input_type; ?>"  name="end_date" id="end_date"  value="<?php echo $end_date ; ?>" />
				<input type="<?php echo $input_type; ?>" name="customer_type" value="<?php echo $customer_type ;  ?>" />
               
                <input type="<?php echo $input_type; ?>" name="do_action_type" value="product_new_repeat_customer" />
                <input type="<?php echo $input_type; ?>" name="action" value="<?php echo $this->constants['plugin_key'].'_wp_ajax_action';?>" />
               	<input type="<?php echo $input_type; ?>" name="limit" value="<?php echo $this->get_request("limit",'5',true);?>"/>
                
                
            </form>
            <?php
			//die;
			echo $this->get_pagination($count,$limit);
			
		}
		
		/*
			* Function Name get_customer
			*
			* @param string $billing_email
			*
			* @param string $start_date
			*
			* @param string $end_date
			*
			* @param string $type
			*
			* return $row
		*/
		function get_customer($billing_email ='', $start_date  ,$end_date ,$type = "row"){
			global $wpdb;
			
			$p 			 = $this->get_request("p",1,true);
		    $limit 		 = $this->get_request("limit",2,true);
			
			$start = (($p-1) * $limit);
			
			
			$query = "";
			$query = " SELECT ";
			
			if ($type =="count"){
				
				$query .= " COUNT(*) 													AS 'order_count'";
			}else{
				$query .= " COUNT(*) 													AS 'order_count'";
				$query .= " ,woocommerce_order_items.order_item_name 					AS 'order_item_name'";
				$query .= " ,woocommerce_order_itemmeta_product_id.meta_value 			AS 'product_id'";
				$query .= " ,woocommerce_order_itemmeta_variation_id.meta_value 		AS 'variation_id'";
				$query .= " ,SUM(woocommerce_order_itemmeta_line_total.meta_value) 		AS 'total_amount'";
				$query .= " ,SUM(woocommerce_order_itemmeta_qty.meta_value) 			AS 'quantity'";
				
				$query .= " ,billing_email.meta_value  as billing_email ";
				
				
				$query .= " ,CONCAT(woocommerce_order_itemmeta_product_id.meta_value,'-',woocommerce_order_itemmeta_variation_id.meta_value,'-',billing_email.meta_value) 	AS 'group_key'";
				
				$query .= " ,billing_first_name.meta_value  as billing_first_name ";
				$query .= " ,billing_last_name.meta_value  as billing_last_name ";
			}
			
			$query .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items";
			
			$query .= " LEFT JOIN  {$wpdb->posts} as shop_order ON shop_order.id=woocommerce_order_items.order_id";
			
			$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_line_total 	ON woocommerce_order_itemmeta_line_total.order_item_id	= woocommerce_order_items.order_item_id";
			$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_qty 			ON woocommerce_order_itemmeta_qty.order_item_id			= woocommerce_order_items.order_item_id";				
			$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_product_id 	ON woocommerce_order_itemmeta_product_id.order_item_id	= woocommerce_order_items.order_item_id";
			$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_variation_id ON woocommerce_order_itemmeta_variation_id.order_item_id= woocommerce_order_items.order_item_id";
			$query .= " LEFT JOIN $wpdb->postmeta AS billing_email ON billing_email.post_id = woocommerce_order_items.order_id";
			
			
			$query .= " LEFT JOIN $wpdb->postmeta AS billing_first_name ON billing_first_name.post_id = woocommerce_order_items.order_id";
			$query .= " LEFT JOIN $wpdb->postmeta AS billing_last_name ON billing_last_name.post_id = woocommerce_order_items.order_id";
			
			$query .= " WHERE 1*1";
			$query .= " AND woocommerce_order_itemmeta_qty.meta_key			= '_qty'";
			$query .= " AND woocommerce_order_itemmeta_line_total.meta_key	= '_line_total'";
			$query .= " AND woocommerce_order_itemmeta_product_id.meta_key 	= '_product_id'";
			$query .= " AND woocommerce_order_itemmeta_variation_id.meta_key 	= '_variation_id'";
			$query .= " AND shop_order.post_type								= 'shop_order'";
			
			$query .= " AND   billing_email.meta_key ='_billing_email'";
			$query .= " AND   billing_first_name.meta_key ='_billing_first_name'";
			$query .= " AND   billing_last_name.meta_key ='_billing_last_name'";
			
			if($billing_email != "" and $billing_email != "-1"){
				$billing_email = str_replace(",","', '",$billing_email);
				$query .= " AND billing_email.meta_value  IN('{$billing_email}')";
			}
			
			if ($start_date && $end_date){
				$query .= " AND  date_format(shop_order.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}' ";
			}
			
			if ($type =="count"){
				$row = $wpdb->get_var($query);
			} 
			elseif ($type  =="export") {
				$query .= " GROUP BY group_key";	
				$row = $wpdb->get_results($query);	
			}else{
				$query .= " GROUP BY group_key";	
				$query .= " LIMIT {$start}, {$limit} ";
				$row = $wpdb->get_results($query);
			}
			
			return $row;
			
		}
		
		/*
			* Function Name get_product_variations
			*
			* @param string $order_id_string
			*
			* return $product_variation
		*/
		function get_product_variations($order_id_string = array()){			
			global $wpdb;
			
			if(is_array($order_id_string)){
				$order_id_string = implode(",",$order_id_string);
			}
				
			$sql = "SELECT meta_key, REPLACE(REPLACE(meta_key, 'attribute_', ''),'pa_','') AS attributes, meta_value, post_id as variation_id
					FROM  {$wpdb->postmeta} as postmeta WHERE 
					meta_key LIKE '%attribute_%' AND meta_key NOT IN ('_default_attributes')";
			
			if(strlen($order_id_string) > 0){
				$sql .= " AND post_id IN ({$order_id_string})";
			}
			
			$order_items 		= $wpdb->get_results($sql);
			
			$product_variation  = array(); 
			if(count($order_items)>0){
				foreach ( $order_items as $key => $order_item ) {
					$variation_label	=	ucfirst($order_item->meta_value);
					$variation_key		=	$order_item->attributes;
					$variation_id		=	$order_item->variation_id;
					$product_variation[$variation_id][$variation_key] =  $variation_label;
				}
			}
			return $product_variation;
		}
		
		/*
			* Function Name get_columns
			*
			* return $columns
		*/
		function get_columns(){
			$columns["order_item_name"] 	= __("Product Name", 		"textdomain_icar");
			$columns["billing_first_name"] 	= __("Billing First Name", 	"textdomain_icar");
			$columns["billing_last_name"]  	= __("Billing Last Name",  	"textdomain_icar");
			$columns["billing_email"] 	  	= __("Billing Email",	   	"textdomain_icar");
			$columns["total_amount"] 		= __("Total Amt.",		 	"textdomain_icar");
			return $columns;
		}
		
		/*
			* Function Name export_customer
		*/
		function  export_customer(){
			$customer_type = $this->get_request("customer_type","new_repeat_customer",true);
			$start_date  	= $this->get_request("start_date",date_i18n("Y-m-d"),true);
			$end_date  	  = $this->get_request("end_date",date_i18n("Y-m-d"),true);
			$old_customer_billing_email 		= $this->get_old_customer_billing_email($start_date,$end_date);
			
			
			if ($customer_type == "new_customer"){
				$new_customer_billing_email 		= $this->get_new_customer_billing_email($start_date,$end_date, $old_customer_billing_email);
				if (strlen($new_customer_billing_email )>0 && $new_customer_billing_email !='' && $new_customer_billing_email != "-1"){
					$customer_row = $this->get_customer($new_customer_billing_email, $start_date  ,$end_date ,"export" );
				}
			}
			if ($customer_type == "repeat_customer"){
				if (strlen($old_customer_billing_email )>0 && $old_customer_billing_email !='' && $old_customer_billing_email != "-1"){
					$customer_row =  $this->get_customer($old_customer_billing_email ,$start_date  ,$end_date,"export" );
				}
			}
			
			
			
			$columns = $this->get_columns();
			$rows = $customer_row ; 
			
			$i = 0;
			$export_rows = array();
			foreach ( $rows as $rkey => $rvalue ):					
					foreach($columns as $key => $value):
						
						switch ($key) {
							default:
								$export_rows[$i][$key] = isset($rvalue->$key) ? $rvalue->$key : '';
								break;
	
						}
					endforeach;
					$i++;
			endforeach;
			
			
			/*$today = date_i18n("Y-m-d-H-i-s");				
			$FileName = "product-wise-new-customer"."-".$today.".csv";	
			$this->ExportToCsv($FileName ,$export_rows,$columns,"csv");
		 	die;*/
			
			//$export_file_name 	= $this->get_request('export_file_name',"no");
			$export_file_format = $this->get_request('export_file_format',"no");	
			$date_format		= get_option( 'date_format' );
			
			$export_file_name 		= "product-wise-new-customer";
			$report_name 			= $this->get_request('report_name','');
			$report_name 			= str_replace("_page","_list",$report_name);
			
			$today_date 		= date_i18n("Y-m-d-H-i-s");				
			$export_filename 	= $export_file_name."-".$today_date.".".$export_file_format;
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
			//echo $report_title;
			//echo "\n";
			echo $out;
			exit;
		}
		/*Export End*/
	}
}
?>