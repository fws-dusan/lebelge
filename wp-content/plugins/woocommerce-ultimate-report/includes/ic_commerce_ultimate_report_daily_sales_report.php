<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if (!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Daily_Sales_Report')) {
	include_once("ic_commerce_ultimate_report_functions.php");
	class IC_Commerce_Ultimate_Woocommerce_Report_Daily_Sales_Report  extends  IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		
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
			
			$onload_search	= apply_filters('ic_commerce_onload_search', 'no', '');
			
			$start_date  	= $this->get_request("start_date",date_i18n("Y-m-d"),true);
			$end_date  		= $this->get_request("end_date",date_i18n("Y-m-d"),true);
		?>
		<!--<h3>Daily Sales Report</h3>-->
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
                                
                                <div class="form-group">
                                    <div class="FormRow FirstRow">
                                        <div class="label-text"><label for="start_date"><?php _e('Delivery Date:','icwoocommerce_textdomains');?></label></div>
                                        <div class="input-text">
                                        	
                                            	
                                            	<?php 
												// $date_format = get_option('date_format');
												$days = array();
												$new_start_date = strtotime("2019-06-03");
												for($i = 0; $i<6;$i++){
													
													$new_start_date = strtotime(" + 7 day", $new_start_date);
													$days[] = date("l F j, Y",$new_start_date);
												}
												
												//$this->print_array($days);
												
												//foreach($)?>
                                               <select>
                                               	<?php foreach($days as $day):?>
                                            	<option><?php echo $day;?></option>
                                                <?php endforeach;?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                </div>
                           	
                              
                                <input type="<?php echo $input_type; ?>" name="do_action_type" value="daily_sales" />
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
        <?php	
		}
		
		/*
			* Function Name ajax
		*/
		function ajax(){
			$start_date  = $this->get_request("start_date",date_i18n("Y-m-d"),true);
			$end_date    = $this->get_request("end_date",date_i18n("Y-m-d"),true);
			$row 	     = $this->get_daily_query("row");
			$count 	   	 = $this->get_daily_query("count");
			$columns  	 = $this->get_columns();
			$limit       = $this->get_request("limit",2,true);
			//$input_type  = "text";
			$input_type  = "hidden";
			
			?>
              <?php if (count($row)>0): ?> 
                <form name="frm_export_daily_sales" id="frm_export_daily_sales">
                    <input type="<?php echo $input_type; ?>"  name="p" value="1"/>
                    <input type="<?php echo $input_type; ?>"  name="limit" value="<?php echo $this->get_request("limit",'5',true);?>"/>
                    <input type="<?php echo $input_type; ?>"  name="total_row" value="<?php echo $count; ?>"/>
                    <input type="<?php echo $input_type; ?>"  name="start_date" id="start_date" value="<?php echo $start_date ; ?>" />
                    <input type="<?php echo $input_type; ?>"  name="end_date" id="end_date"  value="<?php echo $end_date ; ?>" />
                    <input type="<?php echo $input_type; ?>"  name="export_file_format" id="export_file_format"  value="csv" />
                    
                    
                <div class="top_buttons">
                	<div class="RegisterDetailExport">
                    	<input type="submit" name="<?php echo $this->constants['plugin_key'].'_daily_sales_report_export_csv';?>" id="export_daily_sales" value="<?php _e('Export','icwoocommerce_textdomains'); ?>" class="onformprocess" />  
                    </div>
                	
                </div>
                                 
                </form>
            <?php endif; ?>
            <table style="width:100%" class="widefat widefat_normal_table" cellpadding="0" cellspacing="0">
            	<thead>
            	<tr class="first">
                <?php 
					$td_content = "";
					foreach($columns as $column_key => $column_label):
							$td_class = $column_key;
							$td_style = "";
							$td_value = $column_label;
							switch($column_key){
								case "order_count":
								case "total_qty":
								case "discount_amount":								
								case "refund_amount":
								case "order_total":
								case "vat_20":
								case "vat_10":
								case "vat_5_5":
								case "other_tax":
								case "total_tax":
									$td_class .= " amount";
									break;
							}
							$td_content .= "<th class=\"{$td_class}\"{$td_style}>{$td_value}</th>\n";
						endforeach;
					echo $td_content;
					?>
                </tr>
                </thead>
                <tbody>
                <?php if (count($row)>0):
						$td_content = "";
                	 	foreach($row as $key=>$value):
							$alternate = $key%2 == 1 ? "alternate " : "";							
							$td_content .= "<tr class=\"{$alternate} row_{$key}\">\n";
							foreach($columns as $column_key => $column_label):
								$td_class = $column_key;
								$td_style = "";
								$td_value = isset($value->{$column_key}) ? $value->{$column_key} : '';
								switch($column_key){
									
									case "order_count":
									case "total_qty":
										$td_class .= " amount";
										$td_value = isset($value->{$column_key}) ? $value->{$column_key} : 0;
										break;
									case "discount_amount":
									case "refund_amount":
									case "order_total":
									case "vat_20":
									case "vat_10":
									case "vat_5_5":
									case "other_tax":
									case "total_tax":
									
										$td_class .= " amount";
										$td_value = isset($value->{$column_key}) ? $value->{$column_key} : 0;
										$td_value = $this->price($td_value);
										break;
									
								}
								$td_content .= "<td class=\"{$td_class}\"{$td_style}>{$td_value}</td>\n";
							endforeach;	
							$td_content .= "</tr>\n";
						endforeach;
						echo $td_content;
					else: ?>
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
               
                <input type="<?php echo $input_type; ?>" name="do_action_type" value="daily_sales" />
                <input type="<?php echo $input_type; ?>" name="action" value="<?php echo $this->constants['plugin_key'].'_wp_ajax_action';?>" />
                <input type="<?php echo $input_type; ?>"  name="limit" value="<?php echo $this->get_request("limit",'5',true);?>"/>
                
                <input type="<?php echo $input_type; ?>" name="page" value="<?php echo $this->get_request('page','',true);?>" />
                <input type="<?php echo $input_type; ?>" name="admin_page" value="<?php echo $this->get_request('page','',true);?>" />
                
            </form>
            <?php
			echo $this->get_pagination($count,$limit);
			//$this->get_coupon( );
		}
		
		/*
			* Function Name get_daily_qty_query
			*
			* @param string $type
			*
			* return $row
		*/
		
		function get_daily_qty_query($type="row"){
			global $wpdb;
			
			$start_date  = $this->get_request("start_date",date_i18n("Y-m-d"),true);
			$end_date  = $this->get_request("end_date",date_i18n("Y-m-d"),true);
			
			$query = "";
			
			$query = " SELECT ";
			
			if ($type =="count"){
				$query .= " * ";
			}else{
				$query .= " date_format(posts.post_date, '%Y-%m-%d') as order_date ";
				$query .= " ,SUM(qty.meta_value) as total_qty ";
			}
			
			$query .= " FROM $wpdb->posts AS posts ";
									
			$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_items AS order_items ON order_items.order_id = posts.ID ";			
			
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS qty ON qty.order_item_id = order_items.order_item_id";
			
			$query .= " WHERE 1=1";
			
			$query .= " AND posts.post_type = 'shop_order'";			
			
			$query .= " AND order_items.order_item_type = 'line_item'";
			
			$query .= " AND qty.meta_key = '_qty'";
			
			$query .= " AND posts.post_status IN ('wc-completed','wc-processing','wc-on-hold')";
			
			if ($start_date && $end_date){
				$query .= " AND  date_format(posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}' ";
			}
			
			$query .= " GROUP BY date_format(posts.post_date, '%Y-%m-%d')";
			
			$row = $wpdb->get_results($query);
			
			$new_data = array();
			
			foreach($row as $k=>$v){
				$new_data[$v->order_date] = $v->total_qty;
			}
			
			return $new_data;
		}
		
		/*
			* Function Name get_daily_query
			*
			* @param string $type
			*
			* return $row
		*/
		
		function get_daily_query($type="row"){
			$this->get_daily_refund_query();
			global $wpdb;
			
			$start_date  = $this->get_request("start_date",date_i18n("Y-m-d"),true);
			$end_date  = $this->get_request("end_date",date_i18n("Y-m-d"),true);
			
			
			$p 			 = $this->get_request("p",1,true);
		    $limit 		 = $this->get_request("limit",2,true);
			
			$start = (($p-1) * $limit);
			
			$query = "";
			
			$query = " SELECT ";
			//$query .= " * ";
			if ($type =="count"){
				$query .= " * ";
			}else{
				$query .= " date_format(posts.post_date, '%Y-%m-%d') as order_date ";
				$query .= " ,SUM(order_total.meta_value) as order_total ";
				$query .= " ,COUNT(*) as order_count ";
			}
			
			$query .= " FROM $wpdb->posts AS posts ";
			
			$query .= " LEFT JOIN $wpdb->postmeta AS order_total ON order_total.post_id = posts.ID";
			
			$query .= " WHERE 1=1";
			
			$query .= " AND posts.post_type = 'shop_order'";
			
			$query .= " AND order_total.meta_key ='_order_total'";
			
			$query .= " AND posts.post_status IN ('wc-completed','wc-processing','wc-on-hold')";
			
			if ($start_date && $end_date){
				$query .= " AND  date_format(posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}' ";
			}

			if ($type =="count"){
			
			    $query .= " GROUP BY date_format(posts.post_date, '%Y-%m-%d')";
			    $row = $wpdb->get_results($query);
				$row = count($row);
			} 
			elseif ($type  =="export") {
			    $query .= " GROUP BY date_format(posts.post_date, '%Y-%m-%d')";
		    	$query .= " ORDER BY  date_format(posts.post_date, '%Y-%m-%d') DESC ";
				$row = $wpdb->get_results($query);	
			}else{
				$query .= " GROUP BY date_format(posts.post_date, '%Y-%m-%d')";
		    	$query .= " ORDER BY  date_format(posts.post_date, '%Y-%m-%d') DESC ";
				$query .= " LIMIT {$start}, {$limit} ";
				$row = $wpdb->get_results($query);
			}
			
			
			
			if ($type =="export" || $type =="row"){
				
				$coupon = $this->get_coupon();
				$daily_qty = $this->get_daily_qty_query();
				$daily_refund = $this->get_daily_refund_query();
				
				foreach($row as $key=>$value){
					$row[$key]->total_qty  = isset($daily_qty [$value->order_date])?$daily_qty [$value->order_date]:0;
				}
				
				foreach($row as $key=>$value){
					$row[$key]->discount_amount  = isset($coupon [$value->order_date])?$coupon [$value->order_date]:0;
				}
				
				foreach($row as $key=>$value){
					$row[$key]->refund_amount  = isset($daily_refund [$value->order_date])?$daily_refund [$value->order_date]:0;
				}
			}
			
			return $row;
		}
		
		/*
			* Function Name get_daily_refund_query
			*
			* @param string $type
			*
			* return $row
		*/
		
		function get_daily_refund_query($type="row"){
			
			global $wpdb;
			
			$start_date  = $this->get_request("start_date",date_i18n("Y-m-d"),true);
			$end_date  = $this->get_request("end_date",date_i18n("Y-m-d"),true);
			
			
			$p 			 = $this->get_request("p",1,true);
		    $limit 		 = $this->get_request("limit",2,true);
			
			$start = (($p-1) * $limit);
			
			$query = "";
			
			$query = " SELECT ";
			//$query .= " * ";
			if ($type =="count"){
				$query .= " * ";
			}else{
				$query .= " date_format(posts.post_date, '%Y-%m-%d') as order_date ";
				$query .= " ,SUM(order_total.meta_value) as order_total ";
				$query .= " ,COUNT(*) as order_count ";
			}
			
			$query .= " FROM $wpdb->posts AS posts ";
			
			$query .= " LEFT JOIN $wpdb->postmeta AS order_total ON order_total.post_id = posts.ID";
			
			$query .= " WHERE 1=1";
			
			$query .= " AND posts.post_type = 'shop_order_refund'";
			
			$query .= " AND order_total.meta_key ='_order_total'";
			
			$query .= " AND posts.post_status IN ('wc-completed','wc-processing','wc-on-hold')";
			
			if ($start_date && $end_date){
				$query .= " AND  date_format(posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}' ";
			}
			
			$query .= " GROUP BY date_format(posts.post_date, '%Y-%m-%d')";
			
			$row = $wpdb->get_results($query);
			
			$new_data = array();
			
			foreach($row as $k=>$v){
				$new_data[$v->order_date] = $v->order_total;
			}
			
			return $new_data;
		}
		
		/*
			* Function Name get_columns
			*
			* return $columns
		*/
		function get_columns(){
			$columns["order_date"]  = __("Order Date","textdomain_icar");
			$columns["order_count"] = __("Order Count","textdomain_icar");
			$columns["total_qty"]   = __("Total Qty","textdomain_icar");
			$columns["order_total"] = __("Order Total","textdomain_icar");
			$columns["refund_amount"] = __("Refund Amount","textdomain_icar");
			$columns["discount_amount"] = __("Discount Amount","textdomain_icar");
			
			$columns["vat_20"] = __("Vat 20%","textdomain_icar");
			$columns["vat_10"] = __("Vat 10%","textdomain_icar");
			$columns["vat_5_5"] = __("Vat 5.5%","textdomain_icar");
			$columns["other_tax"] = __("Other Tax","textdomain_icar");
			$columns["total_tax"] = __("Total Tax","textdomain_icar");
			
			return $columns;
		}
		
		
		/*
			* Function Name export_daily_sales
		*/
		function export_daily_sales(){
			$columns = $this->get_columns();
			$rows =  $this->get_daily_query("export") ; 
			
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
			
			
			$today = date_i18n("Y-m-d-H-i-s");				
			$FileName = "daily-sales-report"."-".$today.".csv";	
			$this->ExportToCsv($FileName ,$export_rows,$columns,"csv");
		 	die;
		
		}
		
		
		/*
			* Function Name get_coupon
			*
			* return $new_coupon
		*/
		function get_coupon( ){
			global $wpdb;
			
			$start_date  = $this->get_request("start_date",date_i18n("Y-m-d"),true);
		    $end_date  = $this->get_request("end_date",date_i18n("Y-m-d"),true);
			
			$query = " SELECT ";
			$query .= " date_format(posts.post_date, '%Y-%m-%d') as order_date ";
			$query .= ", SUM( discount_amount.meta_value) as discount_amount ";
			$query .= " FROM $wpdb->posts AS posts ";
			
			$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_items AS coupon ON coupon.order_id = posts.ID ";
			
			$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta AS discount_amount ON discount_amount.order_item_id = coupon.order_item_id ";
			
			
			$query .= " WHERE 1=1";
			$query .= " AND posts.post_type = 'shop_order'";
			
			$query .= " AND coupon.order_item_type = 'coupon'";
			
			$query .= " AND discount_amount.meta_key = 'discount_amount'";
			
			$query .= " AND posts.post_status IN ('wc-completed','wc-processing','wc-on-hold')";
			
			if ($start_date && $end_date){
				$query .= " AND  date_format(posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}' ";
			}
			$query .= " GROUP BY date_format(posts.post_date, '%Y-%m-%d')";
			
			$row = $wpdb->get_results($query);	
			
			$new_coupon =array();
			foreach($row  as $key=>$value){
				$new_coupon[$value->order_date] = $value->discount_amount;	
			}
			
			return $new_coupon;
		}
		
		
	}
}