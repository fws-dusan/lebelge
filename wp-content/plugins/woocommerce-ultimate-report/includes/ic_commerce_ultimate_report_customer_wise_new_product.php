<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if (!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Customer_Wise_New_Product')) {
	include_once("ic_commerce_ultimate_report_functions.php");
	class IC_Commerce_Ultimate_Woocommerce_Report_Customer_Wise_New_Product  extends  IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		
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
			
			//$this->get_daily_sales_report();
			$start_date  = $this->get_request("start_date",date_i18n("Y-m-d"),true);
			$end_date  = $this->get_request("end_date",date_i18n("Y-m-d"),true);
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
                           	
								
								<input type="<?php echo $input_type; ?>" name="do_action_type" value="customer_product" />
                                <input type="<?php echo $input_type; ?>" name="action" value="<?php echo $this->constants['plugin_key'].'_wp_ajax_action';?>" />
                                <input type="<?php echo $input_type; ?>" name="limit" value="<?php echo $this->get_request("limit",'5',true);?>"/>
								
                                
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
		}
		
		/*
			* Function Name ajax
		*/
		function ajax(){
			//$input_type  = "text";
			$input_type  = "hidden";
			
			$row 	    = $this->get_query();
			$columns   	= $this->get_columns();
			$limit     	= $this->get_request("limit",2,true);
			
			$start_date    = $this->get_request("start_date");
			$end_date      = $this->get_request("end_date");
			$total_row     = $this->get_request("total_row",0);
			
			if($total_row == 0){
				$total_row =  $this->get_query("count");
			}
			
			?>
            <?php if (count($row)>0): ?> 
                <form name="frm_export_customer_wise_new_product" id="frm_export_customer_wise_new_product">
                    <input type="<?php echo $input_type; ?>"  name="p" value="1"/>
                    <input type="<?php echo $input_type; ?>"  name="limit" value="<?php echo $this->get_request("limit",'5',true);?>"/>
                    <input type="<?php echo $input_type; ?>"  name="total_row" value="<?php echo $total_row; ?>"/>
                    <input type="<?php echo $input_type; ?>"  name="start_date" id="start_date" value="<?php echo $start_date ; ?>" />
                    <input type="<?php echo $input_type; ?>"  name="end_date" id="end_date"  value="<?php echo $end_date ; ?>" />
                    <input type="<?php echo $input_type; ?>"  name="export_file_format" id="export_file_format"  value="csv" />
                    
                    
                 
               		<input type="<?php echo $input_type; ?>" name="do_action_type" value="customer_product" />
                    <input type="<?php echo $input_type; ?>" name="action" value="<?php echo $this->constants['plugin_key'].'_wp_ajax_action';?>" />
				
				<div class="top_buttons">
                	<div class="RegisterDetailExport">
                    	<input type="submit" name="<?php echo $this->constants['plugin_key'].'_customer_wise_new_product_export_csv';?>" id="export_customer_product" value="<?php _e('Export','icwoocommerce_textdomains'); ?>" class="onformprocess" />  
                    </div>
                	
                </div>
                    
                                
                </form>
            <?php endif; ?>
            <table style="width:100%" class="widefat widefat_normal_table" cellpadding="0" cellspacing="0">
            	<thead>
            	<tr>
                <?php foreach($columns as $key=>$value): ?>
                	<th><?php echo $value; ?></th>
				<?php endforeach; ?>
                </tr>
                </thead>
                 <tbody>
                <?php if (count($row)>0): ?>
                	<?php foreach($row as $key=>$value): ?>
                     <tr>
                        <td><?php echo $value->product_name; ?></td>
                        <td><?php echo $value->billing_email; ?></td>
                        <td><?php echo $value->qty; ?></td>
                        <td><?php echo $this->get_woo_price($value->line_total); ?></td>
                    </tr>
				<?php endforeach; ?>
                <?php else: ?>
					<tr>
                    	<td colspan="<?php echo count($columns); ?>"> <?php _e('No record found','icwoocommerce_textdomains'); ?></td>
                    </tr>                
                <?php endif; ?>
                
               </tbody> 
            </table>
             <form name="search_order_pagination" id="search_order_pagination">
                <input type="<?php echo $input_type; ?>"  name="p" value="1"/>
                <input type="<?php echo $input_type; ?>" name="limit" value="<?php echo $this->get_request("limit",'5',true);?>"/>
                <input type="<?php echo $input_type; ?>"  name="total_row" value="<?php echo $total_row; ?>"/>
                <input type="<?php echo $input_type; ?>"  name="start_date" id="start_date" value="<?php echo $start_date ; ?>" />
                <input type="<?php echo $input_type; ?>"  name="end_date" id="end_date"  value="<?php echo $end_date ; ?>" />
               
               <input type="<?php echo $input_type; ?>" name="do_action_type" value="customer_product" />
               <input type="<?php echo $input_type; ?>" name="action" value="<?php echo $this->constants['plugin_key'].'_wp_ajax_action';?>" />           
            </form>
            <?php
			echo $this->get_pagination($total_row,$limit);
			//$this->print_array($row);
		}
		
		/*
			* Function Name get_query
			*
			* @param string $type
			*
			* return $row
		*/
		function get_query($type="row"){
			global $wpdb;
			
			
			$start_date  = $this->get_request("start_date",date_i18n("Y-m-d"),true);
			$end_date  = $this->get_request("end_date",date_i18n("Y-m-d"),true);
			
			$p 			 = $this->get_request("p",1,true);
		    $limit 		 = $this->get_request("limit",2,true);
			
			$start = (($p-1) * $limit);
			
			$query = " SELECT ";
		
			//$query .= " * ";
			if ($type =="count"){
				$query .= " count(*) as count "; 
			}else{
				$query .= " order_items.order_item_name as product_name";
				$query .= " ,product_id.meta_value as product_id";
				$query .= " ,variation_id.meta_value as variation_id";
				//$query .= " ,ROUND(SUM(line_total.meta_value),2) as line_total";
				$query .= " ,SUM(line_total.meta_value) as line_total";
				$query .= " ,SUM(qty.meta_value) as qty";
				
				$query .= " , billing_email.meta_value as  billing_email ";
			
			}
			
		
			
			$query .= " FROM  {$wpdb->posts} as posts";
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id";
			
			
			$query .= " LEFT JOIN {$wpdb->postmeta} AS billing_email ON posts.ID = billing_email.post_id";
			
			
			
			$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 	as product_id 	ON product_id.order_item_id 		= order_items.order_item_id";
			
			$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 	as variation_id 	ON variation_id.order_item_id 		= order_items.order_item_id";
			
			
	
			$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 	as line_total 	ON line_total.order_item_id 		= order_items.order_item_id";
			$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta 	as qty 	ON qty.order_item_id 		= order_items.order_item_id";
					
			
			
			$query .= " WHERE 1=1 ";
			$query .= " AND posts.post_type IN('shop_order')";
			
			$query .= " AND order_items.order_item_type IN('line_item')";
			
			$query .= " AND billing_email.meta_key IN('_billing_email')";
			
			
			$query .= " AND product_id.meta_key IN('_product_id')";
			$query .= " AND variation_id.meta_key IN('_variation_id')";
			$query .= " AND line_total.meta_key IN('_line_total')";
			$query .= " AND qty.meta_key IN('_qty')";
			//$query .= " AND variation_id.meta_value > 0 ";
			
			
			if ($start_date && $end_date){
				$query .= " AND  date_format(posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}' ";
			}
			
			if ($type =="count"){
			
			   	$query .= " GROUP BY billing_email.post_id, variation_id.meta_value, product_id.meta_value  ";
				//$query .= "  ORDER BY product_name ";
			    $row = $wpdb->get_results($query);
				$row = count($row);
				//$this->print_sql($query);
				
				//$this->print_array($wpdb);
				//$this->print_array($row);
			} 
			elseif ($type  =="export") {
			  	$query .= " GROUP BY billing_email.post_id, variation_id.meta_value, product_id.meta_value  ";
				$query .= "  ORDER BY product_name ";
				$row = $wpdb->get_results($query);	
			}else{
				$query .= " GROUP BY billing_email.post_id, variation_id.meta_value, product_id.meta_value  ";
				$query .= "  ORDER BY product_name ";
				$query .= " LIMIT {$start}, {$limit} ";
				$row = $wpdb->get_results($query);
			}
			
			return $row;
		}
		
		/*
			* Function Name get_columns
			*
			* return $columns
		*/
		function get_columns(){
			$columns["product_name"]  	= __("Product Name",	"textdomain_icar");
			$columns["billing_email"] 	= __("Billing Email",	"textdomain_icar");
			$columns["qty"] 		   	= __("Quantity",		"textdomain_icar");
			//$columns["product_id"] 	= __("Order Count",		"textdomain_icar");
			//$columns["variation_id"]  = __("Total Qty",		"textdomain_icar");
			$columns["line_total"] 		= __("Line Total",		"textdomain_icar");
			
			return $columns;
		}
		
		/*
			* Function Name export_customer_product
		*/
		function export_customer_product(){
			$columns = $this->get_columns();
			$rows =  $this->get_query("export") ; 
			
			//$this->print_array($rows);
			//die;
			
			$i = 0;
			$export_rows = array();
			foreach ( $rows as $rkey => $rvalue ):					
					foreach($columns as $key => $value):
						
						switch ($key) {
							case "order_item_name":
							case "order_item_name":
							case "order_item_name":
									//$export_rows[$i][$key] =  isset($country->countries[$this_order->$key]) ? $country->countries[$this_order->$key]: $this_order->$key;
								break;
							default:
								$export_rows[$i][$key] = isset($rvalue->$key) ? $rvalue->$key : '';
								break;
	
						}
					endforeach;
					$i++;
			endforeach;
			
			
			/*$today = date_i18n("Y-m-d-H-i-s");				
			$FileName = "customer-wise-new-product"."-".$today.".csv";	
			$this->ExportToCsv($FileName ,$export_rows,$columns,"csv");
		 	die;*/
			
			//$export_file_name 	= $this->get_request('export_file_name',"no");
			$export_file_format = $this->get_request('export_file_format',"no");	
			$date_format		= get_option( 'date_format' );
			
			$export_file_name 		= "customer-wise-new-product";
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
		/*End Daily Sales*/
	}
}
?>