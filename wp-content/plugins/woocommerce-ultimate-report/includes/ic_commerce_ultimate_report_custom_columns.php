<?php
if(!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Custom_Columns')){
	class IC_Commerce_Ultimate_Woocommerce_Report_Custom_Columns extends IC_Commerce_Ultimate_Woocommerce_Report_Functions{
		
		public $constants = array();
		
		function __construct($constants = array(), $admin_page = ''){
			$this->constants = $constants;
			
			add_action('ic_commerce_normal_view_extra_meta_keys',				array($this, 'ic_commerce_normal_view_extra_meta_keys'),31,3);
			add_action('ic_commerce_normal_view_columns',						array($this, 'ic_commerce_normal_view_columns'),		31,2);
			add_action('ic_commerce_normal_view_data_items',					 array($this, 'ic_commerce_normal_view_data_items'),31,3);
			
			add_action('ic_commerce_result_columns_details_page',				array($this, 'ic_commerce_result_columns_details_page'),31,2);
			add_action('ic_commerce_price_columns',						      array($this, 'ic_commerce_price_columns'),31,1);			
			
			
			add_action('ic_commerce_pdf_custom_column_right_alignment',		  array($this, 'ic_commerce_price_columns'),					31,2);
			
			
			add_action('ic_commerce_details_view_data_items',					 array($this, 'ic_commerce_details_view_data_items'),31,3);
			add_action('ic_commerce_details_view_extra_meta_keys',				array($this, 'ic_commerce_normal_view_extra_meta_keys'),31,3);
			add_action('ic_commerce_details_view_columns',						array($this, 'ic_commerce_details_view_columns'),31,2);	
			
		}
		
		function ic_commerce_normal_view_extra_meta_keys($extra_meta_keys = array(), $request = array(),$type = ''){
			$extra_meta_keys[] = 'Payment type';			
			$extra_meta_keys[] = 'PayPal Transaction Fee';
			$extra_meta_keys[] = 'PayPal Transaction Fee';
			$extra_meta_keys[] = 'Stripe Fee';
			$extra_meta_keys[] = 'stripe_fee';
			return $extra_meta_keys;
		}
		
		function ic_commerce_price_columns($price_columns = array()){
			$price_columns['paypal_transaction_fee'] = 'paypal_transaction_fee';
			$price_columns['transaction_fee'] = 'transaction_fee';
			$price_columns['stripe_fee'] = 'stripe_fee';
			return $price_columns;
		}
		
		function ic_commerce_normal_view_columns($columns = array(), $detail_column = ''){
			
			if($detail_column == 'normal_view'){
				$new_columns 	= $this->get_columns();				
				$position 	   = array_search('order_currency', array_keys($columns));					
				$columns 		= array_slice($columns, 0, $position, true) + $new_columns + array_slice($columns, $position, count($columns) - 1, true) ;
			}
			return $columns;
		}
		
		function ic_commerce_details_view_columns($columns = array(), $detail_column = ''){
			if($detail_column == 'order_columns'){
				$new_columns 	= $this->get_columns();				
				$columns = array_merge($columns,$new_columns);
			}
			return $columns;
		}
		
		function ic_commerce_result_columns_details_page($columns = array(), $detail_column = array()){			
			
			if($detail_column == 'no'){
				$new_columns 	= $this->get_columns();				
				$position 		= array_search('gross_amount', array_keys($columns));					
				$columns 		= array_slice($columns, 0, $position, true) + $new_columns + array_slice($columns, $position, count($columns) - 1, true) ;
			}
			
			
			if($detail_column == 'yes'){
				$new_columns 	= $this->get_columns();
				$position 		= array_search('product_rate', array_keys($columns));					
				$columns 		= array_slice($columns, 0, $position, true) + $new_columns + array_slice($columns, $position, count($columns) - 1, true) ;
			}
			
			return $columns;
		}
		
		function get_columns(){			
			$new_columns = array();			
			//$new_columns['payment_type'] 	 = __("Payment Type", 			'icwoocommerce_textdomains');
			$new_columns['transaction_fee']  = __("Transaction Fee", 			'icwoocommerce_textdomains');
			return $new_columns;
		}
		
		function ic_commerce_normal_view_data_items($order_items = '', $request = '', $type = ''){	
			//error_log($type)		;
			//if($type == 'limit_row' || $type == 'all_row'){
				foreach ( $order_items as $key => $order_item ) {
					
					$paypal_transaction_fee = isset($order_item->paypal_transaction_fee) ? $order_item->paypal_transaction_fee : 0;
					
					$stripe_fee = isset($order_item->stripe_fee) ? $order_item->stripe_fee : 0;				
					
					$order_items[$key]->transaction_fee = $paypal_transaction_fee + $stripe_fee;
				}
			//}
			return $order_items;
		}
		
		function ic_commerce_details_view_data_items($order_items = '', $request = '', $type = '', $page = '', $columns = array(),$total_columns = array()){				
			if($type == 'limit_row' || $type == 'all_row'){
				foreach ( $order_items as $key => $order_item ) {
					
					$order_id = $order_item->order_id;
					if(!isset($orders[$order_id])){
						$paypal_transaction_fee = isset($order_item->paypal_transaction_fee) ? $order_item->paypal_transaction_fee : 0;
						
						$stripe_fee = isset($order_item->stripe_fee) ? $order_item->stripe_fee : 0;				
						
						$order_items[$key]->transaction_fee = $paypal_transaction_fee + $stripe_fee;
						
						$orders[$order_id] = $order_id;
					}
				}
			}else{
				$extra_meta_keys 	= apply_filters('ic_commerce_details_view_extra_meta_keys', array(),$request, $type, $page, 'details_view', $columns);
				$post_ids 			= $this->get_items_id_list($order_items,'order_id');
				$postmeta_datas 	= $this->get_postmeta($post_ids, $columns,$extra_meta_keys);
				
				$orders = array();
				
				foreach ( $order_items as $key => $order_item ) {
						$order_id = $order_item->order_id;
						
						if(!isset($orders[$order_id])){
							$postmeta_data 	= isset($postmeta_datas[$order_id]) ? $postmeta_datas[$order_id] : array();
							
							foreach($postmeta_data as $postmeta_key => $postmeta_value){
								$order_items[$key]->{$postmeta_key}	= $postmeta_value;
							}
							
							$paypal_transaction_fee = isset($order_item->paypal_transaction_fee) ? $order_item->paypal_transaction_fee : 0;
						
							$stripe_fee = isset($order_item->stripe_fee) ? $order_item->stripe_fee : 0;				
							
							$order_items[$key]->transaction_fee = $paypal_transaction_fee + $stripe_fee;
							
							$orders[$order_id] = $order_id;
						}
				}
			}			
			return $order_items;
		}
		
		/*
		 * Print Array
		 *
		 * Beautifully display the array
		 *
		 * @param string $ar, array data
		 * @param bool $display 
		 * @return string
 		 */
		function print_array($ar = NULL,$display = true){
			if($ar){
				$output = "<pre>";
				$output .= print_r($ar,true);
				$output .= "</pre>";
				
				if($display){
					echo $output;
				}else{
					return $output;
				}
			}
		}
	}
}
?>