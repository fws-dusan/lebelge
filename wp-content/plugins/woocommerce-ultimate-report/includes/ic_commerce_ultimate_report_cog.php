<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if( ! class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods')){
	require_once('ic_commerce_ultimate_report_cog_functions.php');
	
	/*
	 * Class Name IC_Commerce_Ultimate_Woocommerce_Report_All_Report_Tabs
	 *
	 * Class is used for Cost of Goods related functions
	 *	 
	*/
	
	class IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods extends IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Functions{
		
		/* variable declaration*/
		public $constants 		=	array();
		
		/* variable declaration*/
		public $cogs_constants 	=	array();
		
		
		/*
		 * Function Name __construct
		 *
		 * Initialize Class Default Settings, Assigned Variables
		 *
		 * @param $constants (array) settings
		*/
		public function __construct($constants = array()) {			
			$this->constants	= array_merge($this->constants, $constants);
			$this->constants['plugin_key']	= isset($this->constants['plugin_key']) ? $this->constants['plugin_key'] : $plugin_key;			
		}
		
		/*
		 * Function Name init
		 *
		 * Initialize Class Default Settings, Assigned Variables, call woocommerce hooks for add  and update order item meta
		 *		 
		*/
		function init(){						
			$cogs_enable_adding = $this->get_setting('cogs_enable_adding',$this->constants['plugin_options'],0);			
			if($cogs_enable_adding == 1){
				if($this->constants['is_wc_ge_3_0_5']){
					add_action( 'woocommerce_new_order_item', array( $this, 'woocommerce_add_order_item_meta' ), 101, 3 );
				}else{
					add_action( 'woocommerce_add_order_item_meta', array( $this, 'woocommerce_add_order_item_meta' ), 101, 3 );
				}
				add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'woocommerce_checkout_update_order_meta' ) );	
			}
			
			
			
		}
		
		/*
		 * Function Name admin_init
		 *
		 * Initialize Class Default Settings, Assigned Variables, call woocommerce hooks
		 *		 
		*/
		function admin_init(){
			
			
			
			$this->constants['plugin_options'] 								= isset($this->constants['plugin_options']) ? $this->constants['plugin_options'] : get_option($this->constants['plugin_key']);
			$cogs_enable_adding = $this->get_setting('cogs_enable_adding',$this->constants['plugin_options'],0);
			
			if($cogs_enable_adding == 1){
				add_filter('woocommerce_attribute_label', 							array($this, 'woocommerce_attribute_label'));				
				add_action( 'woocommerce_product_options_pricing', 					array($this, 'woocommerce_general_product_data_custom_field_simple' ) );
				add_action( 'woocommerce_process_product_meta', 					array(&$this,'woocommerce_process_product_meta_fields_save') );
				add_action( 'woocommerce_variation_options', 						array(&$this,'variable_fields'), 10, 3 );
				add_action( 'woocommerce_product_after_variable_attributes_js',		array(&$this,'variable_fields_js' ));
				add_action( 'woocommerce_process_product_meta_variable',  			array(&$this,'save_variable_fields'), 10, 1 );
				add_action( 'woocommerce_variable_product_bulk_edit_actions',  		array(&$this,'woocommerce_variable_product_bulk_edit_actions'), 10, 1 );
			}
			
			$cogs_enable_reporting  = $this->get_setting('cogs_enable_reporting',$this->constants['plugin_options'],0);
			$admin_page				= isset($this->constants['admin_page']) ? $this->constants['admin_page'] : "";
			$report_name			= isset($_REQUEST['report_name']) 		? $_REQUEST['report_name'] : "";
			
			if($cogs_enable_reporting == 1 and $admin_page == "icwoocommerceultimatereport_report_page"){
				
				if($report_name == "cost_of_good_page" || $report_name == "valuation_page" || $report_name == "monthly_profit_product"){
				
					add_action("ic_commerce_report_page_search_form_bottom",			array($this, "get_ic_commerce_report_page_search_form_bottom"),30,2);
					
					add_filter("ic_commerce_report_page_reset_button_field_tabs",		array($this, "get_ic_commerce_report_page_reset_button_field_tabs"),30,1);
					
					add_filter("ic_commerce_report_page_category_product_fields_tabs",	array($this, "get_ic_commerce_report_page_reset_button_field_tabs"),30,1);				
					
					add_filter("ic_commerce_report_page_default_items",					array($this, "get_ic_commerce_report_page_default_items"),30,5);
					
					add_filter("ic_commerce_report_page_result_columns",				array($this, "get_ic_commerce_report_page_result_columns"),30,2);
					
					add_filter("ic_commerce_report_page_no_date_fields_tabs",			array($this, "get_ic_commerce_report_page_no_date_fields_tabs"),30,2);
				
					add_filter("ic_commerce_report_page_columns",						array($this, "get_ic_commerce_report_page_grid_columns"),30,2);
					
					add_filter("ic_commerce_pdf_custom_column_right_alignment",			array($this, "get_ic_commerce_pdf_custom_column_right_alignment"),20,3);
					
					add_filter("ic_commerce_report_page_grid_price_columns",			array($this, "get_ic_commerce_report_page_grid_price_columns"),30,2);
					
					add_filter("ic_commerce_report_page_export_csv_excel_price_columns",array($this, "get_ic_commerce_report_page_grid_price_columns"),30,2);
					
					add_filter("ic_commerce_report_page_export_pdf_price_columns",		array($this, "get_ic_commerce_report_page_grid_price_columns"),30,2);			
					
					add_filter("ic_commerce_report_page_data_items",					array($this, "get_ic_commerce_report_page_data_items"),30,5);
					
					add_filter("ic_commerce_report_page_data_grid_items_create_grid_items",	array($this, "get_ic_commerce_report_page_data_grid_items_create_grid_items"),50,3);
					//echo $report_name;
					
					add_filter("ic_commerce_autocomplete_product_types",				array($this, 'ic_commerce_autocomplete_product_types'));
				}
			}
									
			add_filter('ic_commerce_ultimate_report_settting_values',				array($this, 'ic_commerce_ultimate_report_settting_values'),10, 2);
			
			//Settings
			add_action('ic_commerce_ultimate_report_settting_field_after_dashboard',	array($this, 'ic_commerce_ultimate_report_settting_field_after_dashboard'),10,2);
			
			//Add/Edit Order
			//add_action( 'woocommerce_checkout_update_order_meta', 				array( $this, 'woocommerce_checkout_update_order_meta' ) );
			
			add_action('ic_commerce_ultimate_report_dashboard_below_summary_section',array($this, 'ic_commerce_ultimate_report_dashboard_below_summary_section'),10);
			
			
		}
		
		/*
		 * Function Name woocommerce_hidden_order_itemmeta
		 *
		 * Initialize Class Default Settings, Assigned Variables
		 *
		 * @return array new default value on setting save
		*/
		function woocommerce_hidden_order_itemmeta($hidden_meta = array()){			
			$hidden_meta[] = $this->cogs_constants['cogs_metakey'];
			$hidden_meta[] = $this->cogs_constants['cogs_metakey_simple'];
			$hidden_meta[] = $this->cogs_constants['cogs_metakey_variable'];
			$hidden_meta[] = $this->cogs_constants['cogs_metakey_item'];
			$hidden_meta[] = $this->cogs_constants['cogs_metakey_item_total'];
			return $hidden_meta;//_ic_cogs_cost_simple
		}
		
		/*
		 * Function Name woocommerce_attribute_label
		 *
		 * return the label
		 *
		 * @return string label		 	 
		*/
		function woocommerce_attribute_label($attribute_label = ''){
			
			switch($attribute_label){
				case $this->cogs_constants['cogs_metakey']:
				case $this->cogs_constants['cogs_metakey_simple']:
				case $this->cogs_constants['cogs_metakey_variable']:
				case $this->cogs_constants['cogs_metakey_item']:
					$attribute_label = __('Cost Of Goods' , 'icwoocommerce_textdomains' );
					break;
				case $this->cogs_constants['cogs_metakey_item_total']:
					$attribute_label = __('Total Cost Of Goods' , 'icwoocommerce_textdomains' );
					break;
				default:
					$attribute_label = $attribute_label;
					break;
			}
			
			return $attribute_label;
		}
		
		/*
		 * Function Name get_ic_commerce_report_page_no_date_fields_tabs
		 *
		 * return the custom tabs for all report page
		 *
		 * @return array tabs
		*/
		function get_ic_commerce_report_page_no_date_fields_tabs($tabs = array(), $report_name = NULL){
			switch($report_name){
				case "valuation_page":
					$tabs[] = 'valuation_page';
					break;
			}
			return $tabs;
		}	
		
		/*
		 * Function Name woocommerce_admin_order_item_headers
		 *
		 * add more columns for cost of goods 
		 *
		 * @return sting return output cost of goods column
		*/
		function woocommerce_admin_order_item_headers(){
			?>
            <th class="item_cog sortable" data-sort="float"><?php _e( 'COGs', 'icwoocommerce_textdomains' ); ?></th>
            <th class="item_total_cog sortable" data-sort="float"><?php _e( 'Total COGs', 'icwoocommerce_textdomains' ); ?></th>
            <?php
		}
		
		/*
		 * Function Name woocommerce_admin_order_item_values
		 *
		 * add more columns value for cost of goods 
		 *
		 * @return (sting) return output cost of goods column value
		*/
		function woocommerce_admin_order_item_values( $_product, $item = array(), $item_id = 0){
			
			$cost_of_goods 			 = 0;
			$total_cost_of_goods 	 = 0;
			
			if($_product){
				$variation_id 	= isset($item['variation_id']) ? $item['variation_id'] : 0;
			
				
				$cogs_metakey 				= isset($this->cogs_constants['cogs_metakey']) 				? ltrim($this->cogs_constants['cogs_metakey'],"_") : "";
				$cogs_metakey_variable 		= isset($this->cogs_constants['cogs_metakey_variable']) 	? ltrim($this->cogs_constants['cogs_metakey_variable'],"_") : "";
				$cogs_metakey_simple 		= isset($this->cogs_constants['cogs_metakey_simple']) 		? ltrim($this->cogs_constants['cogs_metakey_simple'],"_") : "";
				$cogs_metakey_item 			= isset($this->cogs_constants['cogs_metakey_item']) 		? ltrim($this->cogs_constants['cogs_metakey_item'],"_") : "";
				$cogs_metakey_item_total 	= isset($this->cogs_constants['cogs_metakey_item_total']) 	? ltrim($this->cogs_constants['cogs_metakey_item_total'],"_") : "";
				
				if($variation_id > 0){
					$cost_of_goods 	 = isset($item[$cogs_metakey_item]) ? $item[$cogs_metakey_item] : 0;	
				}else{
					$cost_of_goods	 = isset($item[$cogs_metakey_item]) ? $item[$cogs_metakey_item] : 0;
				}
				
				$total_cost_of_goods = isset($item[$cogs_metakey_item_total]) ? $item[$cogs_metakey_item_total] : 0;
			}
			
			?>
				<td class="item_cog" width="1%" data-sort-value="<?php echo $cost_of_goods; ?>">
					<div class="view">
						<?php if($_product)echo $this->price($cost_of_goods);?>
					</div>
				</td>
				
				<td class="item_total_cog" width="1%" data-sort-value="<?php echo $total_cost_of_goods; ?>">
					<div class="view">
						<?php if($_product)echo $this->price($total_cost_of_goods);?>
					</div>
				</td>
				<?php
			
			
		}
		
		
		/*
		 * Function Name woocommerce_general_product_data_custom_field_simple
		 *
		 * genrate textbox to input cost of goods value for simple product
		 *
		 * @return (sting) return output cost of goods taxtbox
		*/
		function woocommerce_general_product_data_custom_field_simple(){
			 //Simple Product
			 $cogs_metakey 					= isset($this->cogs_constants['cogs_metakey']) ? $this->cogs_constants['cogs_metakey'] : "";
			 echo '<div class="options_group">';		 
			 woocommerce_wp_text_input( 
					array( 
						'id'                => 	$cogs_metakey, 
						'label'       		=> __('Cost Of Goods' , 'icwoocommerce_textdomains' ) .' (' . get_woocommerce_currency_symbol() . ')', 
						'placeholder'       => __('Enter Cost Of Goods', 'icwoocommerce_textdomains' ), 
						'description'       => __('Enter the custom value here.', 'icwoocommerce_textdomains' ),
						'type'              => 'text', 
						'data_type'         => 'price',
						'class' 			=> 'wc_input_price short',
						'custom_attributes' => array(
								'step' 	=> 'any',
								'min'	=> '0'
							) 
					)
				);
			 echo '</div>';
		}
		
		/*
		 * Function Name woocommerce_general_product_data_custom_field_variable
		 *
		 * genrate textbox to input cost of goods value for variable product
		 *
		 * @return (sting) return output cost of goods taxtbox
		*/
		function woocommerce_general_product_data_custom_field_variable(){
			 //Simple Product
			 $cogs_metakey_variable			= isset($this->cogs_constants['cogs_metakey_variable']) ? $this->cogs_constants['cogs_metakey_variable'] : "";
			 echo '<div class="options_group">';		 
			 woocommerce_wp_text_input( 
					array( 
						'id'                => 	$cogs_metakey_variable, 
						'label'       		=> __('Default Cost Of Goods' , 'icwoocommerce_textdomains' ) .' (' . get_woocommerce_currency_symbol() . ')', 
						'placeholder'       => __('Enter default cost of goods', 'icwoocommerce_textdomains' ), 
						'description'       => __('Enter default the custom value here.', 'icwoocommerce_textdomains' ),
						'type'              => 'text', 
						'data_type'         => 'price',
						'class' 			=> 'wc_input_price short',
						'wrapper_class'     => 'show_if_variable',
						'custom_attributes' => array(
								'step' 	=> 'any',
								'min'	=> '0'
							) 
					)
				);
			 echo '</div>';
		}
		
		
		/*
		 * Function Name variable_fields
		 *
		 * genrate textbox to input cost of goods value for variation product
		 *
		 * @return (sting) return output cost of goods taxtbox
		*/
		function variable_fields($loop = "", $variation_data = NULL, $variation = NULL){
			global $thepostid;
			$cogs_metakey    			= isset($this->cogs_constants['cogs_metakey']) ? $this->cogs_constants['cogs_metakey'] : "";
		 	echo '<div class="options_group">';
			$variation_id = isset($variation->ID) ? $variation->ID : 0;
			$value = get_post_meta($variation_id, $cogs_metakey , true);
			$thepostid = $variation_id;
			woocommerce_wp_text_input( 
				array( 
					'id'          			=> '_variable_cots_of_goods['.$loop.']', 
					'label'       			=> __('Cost Of Goods', 'icwoocommerce_textdomains' ), 
						'placeholder' 		=> __('Enter Cost Of Goods', 'icwoocommerce_textdomains' ), 
						'description' 		=> __('Enter the Cost Of Goods.', 'icwoocommerce_textdomains' ),
						'desc_tip'    		=> 'true',
						'type'              => 'text', 
						'class' 			=> 'wc_input_price',
						'value' 			=> isset($value) ? $value : '0',
						'custom_attributes' => array(
								'step' 	=> 'any',
								'min'	=> '0'
						)
				)
			);
		 echo '</div>';
		 
		}
		
		/*
		 * Function Name variable_fields
		 *
		 * genrate textbox to input cost of goods value for variation product
		 *
		 * @return (sting) return output cost of goods taxtbox dynamically
		*/
		function variable_fields_js(){
			
			$cogs_metakey 					= $this->cogs_constants['cogs_metakey'];
			echo '<div class="options_group">';
				$variation_id = isset($variation->ID) ? $variation->ID : 0;
				$value = get_post_meta($variation_id, $cogs_metakey , true);
				woocommerce_wp_text_input( 
					array( 
						'id'          		=> '_variable_cots_of_goods[ + loop + ]', 
						'label'       		=> __('Cost Of Goods', 'icwoocommerce_textdomains' ), 
						'placeholder' 		=> __('Enter Cost Of Goods', 'icwoocommerce_textdomains' ), 
						'description' 		=> __('Enter the Cost Of Goods.', 'icwoocommerce_textdomains' ),
						'desc_tip'    		=> 'true',
						'type'              => 'text', 
						'class' 			=> 'wc_input_price',
						'value' 			=> isset($value) ? $value : '0',
						'custom_attributes' => array(
								'step' 	=> 'any',
								'min'	=> '0'
						) 
					)
				);
			echo '</div>';
		}
		
		/*
		 * Function Name woocommerce_process_product_meta_fields_save
		 *
		 * save and update the simpe and variable product cost of goods value
		 *
		*/
		function woocommerce_process_product_meta_fields_save( $post_id = 0 ){
				
				if(isset($_POST['post_type']) and $_POST['post_type'] == "product"){
					$product_type = isset($_POST['product-type']) 	? sanitize_title(stripslashes($_POST['product-type'])) : 'simple';
					$product_type = empty($product_type) 			? 'simple' : $product_type;
					
					if ($product_type == 'simple' || $product_type == 'external' || $product_type == 'subscription') {
						$cogs_metakey 			= $this->cogs_constants['cogs_metakey'];
						if(isset($_POST[$cogs_metakey])){
							$woo_cots_of_goods = isset($_POST[$cogs_metakey]) ?  $_POST[$cogs_metakey] : '0';
							update_post_meta( $post_id, $cogs_metakey, $woo_cots_of_goods );
						}
					}else if ($product_type == 'variable' || $product_type == 'variable-subscription') {
						$cogs_metakey_variable 	= $this->cogs_constants['cogs_metakey_variable'];
						if(isset($_POST[$cogs_metakey_variable])){
							$woo_cots_of_goods = isset($_POST[$cogs_metakey_variable]) ?  $_POST[$cogs_metakey_variable] : '0';
							update_post_meta( $post_id, $cogs_metakey_variable, $woo_cots_of_goods );
						}
					}else{
						/*subscription|variable-subscription*/
						$cogs_metakey 			= $this->cogs_constants['cogs_metakey'];
						if(isset($_POST[$cogs_metakey])){
							$woo_cots_of_goods = isset($_POST[$cogs_metakey]) ?  $_POST[$cogs_metakey] : '0';
							update_post_meta( $post_id, $cogs_metakey, $woo_cots_of_goods );
						}
					}
				}
				
				return $post_id;
		}
		
		/*
		 * Function Name woocommerce_process_product_meta_fields_save
		 *
		 * save and update  the variation product cost of goods value
		 *
		*/
		function save_variable_fields( $post_id =0 ){ 
			if (isset( $_POST['variable_sku'] ) ) :	
				$cogs_metakey    		= $this->cogs_constants['cogs_metakey'];
				$variable_sku          = $_POST['variable_sku'];
				$variable_post_id      = $_POST['variable_post_id'];			
	
				$_text_field = $_POST['_variable_cots_of_goods'];
				
				for ( $i = 0; $i < sizeof( $variable_sku ); $i++ ) :
					$variation_id = (int) $variable_post_id[$i];
					
					if ( isset( $_text_field[$i] ) ) {
						update_post_meta( $variation_id, $cogs_metakey, stripslashes( $_text_field[$i] ) );
					}
				endfor;			
			endif;
			return $post_id;
		}
		
		/*
		 * Function Name set_woocommerce_save_product_variation
		 *
		 * save and update  the variation product cost of goods value
		 *
		*/
		function set_woocommerce_save_product_variation( $variation_id = 0, $i = 0 ){
			$cogs_metakey_variable 				= $this->cogs_constants['cogs_metakey_variable'];
			if(isset( $_POST['_variable_cots_of_goods'])){
				$_variable_cots_of_goods          	= isset( $_POST['_variable_cots_of_goods'] ) ? $_POST['_variable_cots_of_goods'] : array();
				update_post_meta( $variation_id, $cogs_metakey_variable,(isset($_variable_cots_of_goods[$i]) ? $_variable_cots_of_goods[$i] : '') );
			}
			return $variation_id;
		}
		
		/*
		 * Function Name woocommerce_variable_product_bulk_edit_actions
		 *
		 * add one more option for woocommerce variable product bulk edit actions
		 *
		*/
		function woocommerce_variable_product_bulk_edit_actions(){
			echo '<option value="variable_cots_of_goods">'. __( 'Cost Of Goods', 'icwoocommerce_textdomains' ).'</option>';
			add_action('admin_footer',array($this, 'woocommerce_variable_product_bulk_edit_actions_js'));
		}
		
		/*
		 * Function Name woocommerce_variable_product_bulk_edit_actions
		 *
		 * add dynamic scripts for bulk edit
		 *
		*/
		function woocommerce_variable_product_bulk_edit_actions_js(){
			?>
            	<script type="text/javascript">                	
                	jQuery('.wc-metaboxes-wrapper').on('click', 'a.bulk_edit', function(event){
						var field_to_edit = jQuery('select#field_to_edit').val();		//	alert(field_to_edit)			
						if ( field_to_edit == 'variable_cots_of_goods' ) {							
							var input_tag = jQuery('select#field_to_edit :selected').attr('rel') ? jQuery('select#field_to_edit :selected').attr('rel') : 'input';			
							var value = prompt("<?php echo esc_js( __( 'Enter Cost Of Goods', 'icwoocommerce_textdomains' ) ); ?>");
							jQuery(input_tag + '[name^="_' + field_to_edit + '["]').val( value ).change();
							return false;
						}
					});
                </script>
            <?php
		} 
		
		/*
		 * WooCommerce Order Item Meta API - Add term meta.
		 *
		 * function use for save order item meta value on add to cart
		 *
		 * @param int $item_id
		 *
		 * @param mixed $meta_value
		 
		*/
		public function woocommerce_add_order_item_meta( $item_id = 0, $values = array() ) {
			
			//error_log($this->print_array($values,false));

			// get product ID
			
			$cogs_enable_adding = $this->get_setting('cogs_enable_adding',$this->constants['plugin_options'],0);
			
			if($cogs_enable_adding == 0) return true;
			
			$product_id		= (!empty( $values['product_id'])) ? $values['product_id'] : 0;
			
			if($product_id > 0){
				
				$variation_id		= (!empty( $values['variation_id'])) ? $values['variation_id'] : 0;
				
				$product_id 		= $variation_id > 0 ? $variation_id : $product_id;
				
				$item_cost 			= $this->get_product_cost($product_id, $variation_id);
				
				$quantity 			= isset($values['quantity']) ? $values['quantity'] : 0;
				
				$total_item_cost 	= $item_cost * $quantity;
				
				$item_cost 			= $this->wc_format_decimal($item_cost, 5);
				
				$total_item_cost	= $this->wc_format_decimal($total_item_cost, 5);
				
				$cogs_metakey_item = isset($this->cogs_constants['cogs_metakey_item']) ? $this->cogs_constants['cogs_metakey_item'] : '';
				wc_add_order_item_meta( $item_id, $cogs_metakey_item, $item_cost);
				
				$cogs_metakey_item_total = isset($this->cogs_constants['cogs_metakey_item_total']) ? $this->cogs_constants['cogs_metakey_item_total'] : '';
				wc_add_order_item_meta( $item_id, $cogs_metakey_item_total,  $total_item_cost);
			}
		}
		
		
		/*
		 * WooCommerce Order Order Meta API - Add Order meta.
		 *
		 * We are saving order meta data
		 *
		 * @param int $order_id
		 *
		*/
		public function woocommerce_checkout_update_order_meta( $order_id = 0) {
			
			$order_cost_total = 0;
	
			foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
	
				$variation_id	= (!empty( $values['variation_id'])) ? $values['variation_id'] : 0;
			
				$product_id 	= (!empty( $values['variation_id'])) ? $values['variation_id'] : (isset($values['product_id']) ? $values['product_id'] : 0);
	
				$item_cost 		= $this->get_product_cost($product_id, $variation_id);
	
				// sum the individual line item cost totals
				$order_cost_total += ( $item_cost * $values['quantity'] );
			}
			
			$cogs_metakey_order_total = isset($this->cogs_constants['cogs_metakey_order_total']) ? $this->cogs_constants['cogs_metakey_order_total'] : '';
			
			$order_cost_total = $this->wc_format_decimal($order_cost_total, 5);
			
			update_post_meta( $order_id, $cogs_metakey_order_total, $order_cost_total);
		}
		
		
		/*
		 * Function Name Get Product Cost
		 *
		 * return product cost of product and varition product
		 *
		 * @param int $product_id
		 *
		 * @param int $variation_id
		 *
		 * @return mixed product cost
		*/
		function get_product_cost($product_id = 0, $variation_id = 0){
			
			$product_id 	= trim($product_id);
			
			if($variation_id > 0){;
				$cogs_metakey = isset($this->cogs_constants['cogs_metakey']) ? $this->cogs_constants['cogs_metakey'] : '';
				$cost 			= get_post_meta( $variation_id, $cogs_metakey, true );
			}else{
				$cogs_metakey = isset($this->cogs_constants['cogs_metakey']) ? $this->cogs_constants['cogs_metakey'] : '';
				$cost 			= get_post_meta( $product_id, $cogs_metakey, true );
			}
			
			if (empty($cost)) {
				$cost = 0;
			}
			
			return $cost;
		}
		
		/*
		 * Function Name wc_format_decimal
		 *
		 * format value
		 *
		 * @param int $product_id
		 *
		 * @param int $variation_id
		 *
		 * @return mixed product cost
		*/
		function wc_format_decimal($order_cost_total = 0, $dip = 5){
			if(function_exists('wc_format_decimal')){
				$order_cost_total = wc_format_decimal($order_cost_total, $dip);
			}
			
			return $order_cost_total;
		}
		
		/*
		 * Function Name get_product
		 *
		 * return product object
		 *
		 * @param int $product_id
		 *
		 * @param mixed $args
		 *
		 * @return WC_Product|null
		*/
		function get_product($product_id = 0, $args = array()){
			if(function_exists('wc_get_product')){
				$product = wc_get_product( $product_id, $args);
			}else if(function_exists('get_product')){
				$product = get_product( $product_id, $args);
			}			
			
			return $product;
		}
		
		/*
		 * Function Name ic_commerce_ultimate_report_settting_field_after_dashboard
		 *
		 * add more settings for cost of goods
		 *
		 * @param int $settting_this
		 *
		 * @param mixed $settting_option
		 *		 
		*/
		function ic_commerce_ultimate_report_settting_field_after_dashboard($settting_this = NULL, $settting_option = ""){			
			add_settings_section('cogs_settings',			__(	'Cost of Goods:', 				'icwoocommerce_textdomains'),	array( &$settting_this, 'section_options_callback' 	),	$settting_option);
			add_settings_field('cogs_enable_adding',		__( 'Enable Cost of Goods:', 		'icwoocommerce_textdomains'),	array( &$settting_this,	'checkbox_element_callback' ),	$settting_option, 'cogs_settings', array('menu'=> $settting_option,	'label_for'=>'cogs_enable_adding',		'id'=> 'cogs_enable_adding',		'default'=>0));
			add_settings_field('cogs_enable_reporting',		__( 'Enable Reporting:', 			'icwoocommerce_textdomains'),	array( &$settting_this,	'checkbox_element_callback' ),	$settting_option, 'cogs_settings', array('menu'=> $settting_option,	'label_for'=>'cogs_enable_reporting',	'id'=> 'cogs_enable_reporting',		'default'=>0));
			add_settings_field('cogs_enable_dashsummary',	__( 'Enable Dashboard Summary:', 	'icwoocommerce_textdomains'),	array( &$settting_this,	'checkbox_element_callback' ),	$settting_option, 'cogs_settings', array('menu'=> $settting_option,	'label_for'=>'cogs_enable_dashsummary',	'id'=> 'cogs_enable_dashsummary',	'default'=>0));
			add_settings_field('cogs_enable_set_item',		__( 'Enable Reporting Set Cogs:', 	'icwoocommerce_textdomains'),	array( &$settting_this,	'checkbox_element_callback' ),	$settting_option, 'cogs_settings', array('menu'=> $settting_option,	'label_for'=>'cogs_enable_set_item',	'id'=> 'cogs_enable_set_item',		'default'=>0, 'description' => __("Show items with Cost of Goods Only.",'icwoocommerce_textdomains')));
			add_settings_field('last_month_product_profit',	__( 'Dashboard Monthly Profit:', 	'icwoocommerce_textdomains'),	array( &$settting_this, 'text_element_callback' 	), 	$settting_option, 'cogs_settings', array('menu'=> $settting_option, 'class' => 'numberonly',				'size'=>15,							'maxlength'=>'5',	'label_for'=>'last_month_product_profit',			'id'=> 'last_month_product_profit',				'default'=>8));
			add_settings_field('top_profit_product',		__( 'Dashboard Top Profit Product:', 'icwoocommerce_textdomains'),	array( &$settting_this, 'text_element_callback' 	), 	$settting_option, 'cogs_settings', array('menu'=> $settting_option, 'class' => 'numberonly',				'size'=>15,							'maxlength'=>'5',	'label_for'=>'top_profit_product',			'id'=> 'top_profit_product',				'default'=>5));
		}
		
		/*
		 * Function Name ic_commerce_ultimate_report_settting_values
		 *
		 * save cost of goods setting on save, check fields and save default
		 *
		 * @param array $post
		 *
		 * @param object $settting_this
		 *
		 * @return array $post
		 *		 
		*/
		function ic_commerce_ultimate_report_settting_values($post = array(), $settting_this = NULL){				
			$post['cogs_enable_adding']			= isset($post['cogs_enable_adding']) 		? $post['cogs_enable_adding'] 			: 0;
			$post['cogs_enable_reporting']		= isset($post['cogs_enable_reporting']) 	? $post['cogs_enable_reporting'] 		: 0;
			$post['cogs_enable_dashsummary']	= isset($post['cogs_enable_dashsummary']) 	? $post['cogs_enable_dashsummary'] 		: 0;
			$post['cogs_enable_set_item']		= isset($post['cogs_enable_set_item']) 		? $post['cogs_enable_set_item'] 		: 0;
			$post['last_month_product_profit']	= isset($post['last_month_product_profit']) ? $post['last_month_product_profit'] 	: 8;
			$post['top_profit_product']			= isset($post['top_profit_product']) 		? $post['top_profit_product'] 			: 8;
			
			$post['cogs_metakey']				= $this->get_default_cogs_key($post,'cogs_metakey',					'default_cogs_metakey');
			$post['cogs_metakey_variable']		= $this->get_default_cogs_key($post,'cogs_metakey_variable',		'default_cogs_metakey_variable');
			$post['cogs_metakey_order_total']	= $this->get_default_cogs_key($post,'cogs_metakey_order_total',		'default_cogs_metakey_order_total');
			$post['cogs_metakey_item']			= $this->get_default_cogs_key($post,'cogs_metakey_item',			'default_cogs_metakey_item');
			$post['cogs_metakey_item_total']	= $this->get_default_cogs_key($post,'cogs_metakey_item_total',		'default_cogs_metakey_item_total');
			
			return $post;
		}
		
		/*
			* Function Name ic_commerce_ultimate_report_settting_values
			*
			* get cost of goods items
			*
			* @param string $type
			*
			* @param array $shop_order_status
			*
			* @param array $hide_order_status
			*
			* @param string $start_date
			*
			* @param string $end_date
			*
			* @return array $post
			*		 
		*/
		function get_cost_of_goods_items($type = "total", $shop_order_status,$hide_order_status,$start_date,$end_date){
				global $wpdb;
				$cogs_metakey_item_total		= $this->get_setting('cogs_metakey_item_total',	$this->constants['plugin_options'],'');
				$page	= $this->get_request('page','');
				$sql = " SELECT ";
				$sql .= "				
					SUM(woocommerce_order_itemmeta_qty.meta_value) 																					AS quantity							
					,SUM(woocommerce_order_itemmeta_total_cost_of_item.meta_value) 																	AS total_cost_good_amount
					,SUM(woocommerce_order_itemmeta_line_total.meta_value) - SUM(woocommerce_order_itemmeta_total_cost_of_item.meta_value)			AS margin_profit_amount							
					,SUM(woocommerce_order_itemmeta_line_total.meta_value) 																			AS total_amount				
				";	
				
				if($this->constants['post_order_status_found'] == 0 ){
					if(count($shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
				}	
				
				$sql .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_qty					ON woocommerce_order_itemmeta_qty.order_item_id					=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_qty.meta_key					= '_qty'";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_line_total 			ON woocommerce_order_itemmeta_line_total.order_item_id			=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_line_total.meta_key			= '_line_total'";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_line_subtotal			ON woocommerce_order_itemmeta_line_subtotal.order_item_id		=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_line_subtotal.meta_key		= '_line_subtotal'";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_total_cost_of_item 	ON woocommerce_order_itemmeta_total_cost_of_item.order_item_id	=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_total_cost_of_item.meta_key 	= '{$cogs_metakey_item_total}'";
				$sql .= " LEFT JOIN  {$wpdb->posts} as shop_order 															ON shop_order.id												=	woocommerce_order_items.order_id		AND shop_order.post_type									= 'shop_order'";
				
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, array(), $type, '', 'cost_of_good_page', array());
						
				$sql .= " WHERE 1*1 ";
				
				if($this->constants['post_order_status_found'] == 0 ){
					if(count($shop_order_status)>0){
						$in_shop_order_status = implode(",",$shop_order_status);
						$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					}
				}else{
					if(count($shop_order_status)>0){
						$in_shop_order_status		= implode("', '",$shop_order_status);
						$sql .= " AND  shop_order.post_status IN ('{$in_shop_order_status}')";
					}
				}
				
				/*if ($start_date != NULL &&  $end_date != NULL && $type != "today"){
					$sql .= " AND DATE(shop_order.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
				}*/
				
				$order_date_field_key = isset($_REQUEST['order_date_field_key']) ? $_REQUEST['order_date_field_key'] : 'post_date';
				if($order_date_field_key == "post_date" || $order_date_field_key == "post_modified"){
					if ($start_date != NULL &&  $end_date !=NULL){
						$sql .= " AND DATE(shop_order.{$order_date_field_key}) BETWEEN '".$start_date."' AND '". $end_date ."'";
					}
				}
				
				if(count($hide_order_status)>0){
					$in_hide_order_status		= implode("', '",$hide_order_status);
					$sql .= " AND  shop_order.post_status NOT IN ('{$in_hide_order_status}')";
				}
				
				$cogs_enable_set_item = $this->get_setting('cogs_enable_set_item',$this->constants['plugin_options'],0);
				
				if($cogs_enable_set_item == 1){				
					$sql .= " AND woocommerce_order_itemmeta_total_cost_of_item.meta_value > 0";
				}
				
				$sql = apply_filters("ic_commerce_report_page_where_query", $sql,array(), $type, $page, 'cost_of_good_page', array());
								
				$order_items = $wpdb->get_row($sql);
						
				return $order_items;
		}
		
		/*
			* Function Name ic_commerce_ultimate_report_dashboard_below_summary_section
			*
			* create summary boxes on dashboard
			*
			* @param array $constants
			*
			* create summary boxes on dashboard
			*		 
		*/
		function ic_commerce_ultimate_report_dashboard_below_summary_section($constants = array()){
			
			$cogs_enable_dash_summary = $this->get_setting('cogs_enable_dashsummary',$this->constants['plugin_options'],0);
			if($cogs_enable_dash_summary == 0) return false;
			
			
			$plugin_url			= isset($this->constants['plugin_url']) 	? $this->constants['plugin_url'] : array();
			
			$shop_order_status	= isset($constants['shop_order_status']) 	? $constants['shop_order_status'] : array();
			$hide_order_status 	= isset($constants['hide_order_status']) 	? $constants['hide_order_status'] : array();
			$start_date 		= isset($constants['start_date']) 			? $constants['start_date'] : array();
			$end_date 			= isset($constants['end_date']) 			? $constants['end_date'] : array();
			$output 			= "";
			$order_items 		= $this->get_cost_of_goods_items($type = "total", $shop_order_status,$hide_order_status,$start_date,$end_date);
			
			
			
			$quantity 				= isset($order_items->quantity) 						? $order_items->quantity : 0;
			$total_cost_good_amount = isset($order_items->total_cost_good_amount) 			? $order_items->total_cost_good_amount : 0;
			$margin_profit_amount 	= isset($order_items->margin_profit_amount) 			? $order_items->margin_profit_amount : 0;
			$total_amount 			= isset($order_items->total_amount) 					? $order_items->total_amount : 0;
			
			
			if($total_cost_good_amount != 0 and $margin_profit_amount != 0){
				$profit_percentage 			= ($margin_profit_amount/$total_cost_good_amount)*100;
			}else{
				$margin_profit_amount 		= 0;
				$profit_percentage 			= 0;
			}
			
			$dashoard['summary']['total_cogs_amount']['label'] 				= __('Total Cost Of Goods', 'icwoocommerce_textdomains');
			$dashoard['summary']['total_cogs_amount']['first_value'] 		= $this->price($total_cost_good_amount);
			//$dashoard['summary']['total_cogs_amount']['second_value'] 	= $quantity;
			//$dashoard['summary']['total_cogs_amount']['prefix'] 			= "#";
			$dashoard['summary']['total_cogs_amount']['background'] 		= '#b82b31';
			$dashoard['summary']['total_cogs_amount']['icon_path'] 			= $plugin_url."/assets/images/icons/sales-icon6.png";
			
			$dashoard['summary']['total_salse_amount']['label'] 			= __('Sales Amount', 'icwoocommerce_textdomains');
			$dashoard['summary']['total_salse_amount']['first_value'] 		= $this->price($total_amount);
			$dashoard['summary']['total_salse_amount']['second_value'] 		= '';
			$dashoard['summary']['total_salse_amount']['background'] 		= '#3f9ce7';
			$dashoard['summary']['total_salse_amount']['icon_path'] 		= $plugin_url."/assets/images/icons/sales-icon7.png";
			
			
			$dashoard['summary']['margin_profit_amount']['label'] 			= __('Total Profit/Margin', 'icwoocommerce_textdomains');
			$dashoard['summary']['margin_profit_amount']['first_value'] 	= $this->price($margin_profit_amount);
			$dashoard['summary']['margin_profit_amount']['second_value'] 	= '';
			$dashoard['summary']['margin_profit_amount']['background'] 		= '#cd5f3b';
			$dashoard['summary']['margin_profit_amount']['icon_path'] 		= $plugin_url."/assets/images/icons/profit-icon.png";
			
			$dashoard['summary']['profit_percentage']['label'] 				= __('Profit Percentage', 'icwoocommerce_textdomains');
			$dashoard['summary']['profit_percentage']['first_value'] 		= sprintf("%.2f%%",$profit_percentage);
			$dashoard['summary']['profit_percentage']['second_value'] 		= '';
			$dashoard['summary']['profit_percentage']['background'] 		= '#818306';
			$dashoard['summary']['profit_percentage']['icon_path'] 			= $plugin_url."/assets/images/icons/profit-icon1.png";
			
			echo '<div class="clearfix"></div>';
			
			echo '<div class="SubTitle"><span>'.__('Profit Summary').'</span></div>';
			
			echo $this->summary_box($dashoard['summary']);
		}
		
		
		/*
			* Function Name get_cogs_enabled
			*
			* check cost of goods enable or disable
			*
			* @return boolen true|false
			*		 
		*/
		function get_cogs_enabled(){
			$this->constants['plugin_options'] 								= isset($this->constants['plugin_options']) ? $this->constants['plugin_options'] : get_option($this->constants['plugin_key']);			
			$cogs_enable_adding = $this->get_setting('cogs_enable_adding',$this->constants['plugin_options'],0);
			if($cogs_enable_adding == 0) return false;			
			return true;
		}
		
		
		/*
			* Function Name get_default_cogs_key
			*
			* check cost of goods enable or desbale
			*
			* @param array $post
			*
			* @param string $post_key
			*
			* @return string $cogs_metakey
			*		 
		*/
		function get_default_cogs_key($post = array(), $post_key = 'cogs_metakey', $cog_defaulty_key = 'default_cogs_metakey'){
			$default_value 			= isset($this->cogs_constants[$cog_defaulty_key]) ? $this->cogs_constants[$cog_defaulty_key] : '';
			$cogs_metakey			= isset($post[$post_key]) ? str_replace(" ","_",strtolower(trim($post[$post_key]))) : $default_value;
			return strlen($cogs_metakey)>0 ? $cogs_metakey : $default_value;
		}
		
		
		/*
			* Function Name summary_box
			*
			* create summary boxes
			*
			* @param array $dashoard
			*
			* @param array $row_per_column
			*
			* @param array $output
			*
			* @return string $output
			*		 
		*/
		function summary_box($dashoard = array(), $row_per_column = 4, $output = "\n"){
			$output		= "";	
			if(count($dashoard)>0){				
				foreach($dashoard as $key => $values){
					$output .= $this->get_dashboard_box_html($values);
				}
			}			
			return $output;
		}
		
		
		/*
			* Function Name get_dashboard_box_html
			*
			* create summary box
			*
			* @param array $values		
			*
			* @return string $output
			*		 
		*/
		function get_dashboard_box_html($values = array()){
			
			$count  = isset($values['second_value']) ? $values['second_value'] : '';
			$prefix  = isset($values['prefix']) ? $values['prefix'] : "";
			
			if ( $count === 0 )
				$count  = __( '0'); 
				
			
			$output = "";
			$output .= "<div class=\"col-md-3\">";
			$output .= "<div class=\"ic_block\" style=\"background-color:{$values['background']}\">";
			$output .= "	<div class=\"ic_block-content\">";
			$output .= "		<h2>{$values['label']}</h2>";
			$output .= "		<div class=\"ic_stat_content\">";
			$output .= "			<p class=\"ic_stat\">";
			$output .= 				$values['first_value'];
			$output .= "			<span class=\"ic_count\">{$prefix}{$count}</span>";			
			$output .= "			</p>";
			$output .= "			<img src=\"{$values['icon_path']}\" alt=\"\" />";
			$output .= "		</div>";
			$output .= "	</div>";
			$output .= "</div>";
			$output .= "</div>";
			return $output;
		}
		
		/*
			* Function Name get_dashboard_box_html
			*
			* create summary box
			*
			* @param array $value		
			*
			* @param string $args default list of currency details
			*
			* @return string $v (wc_price)
			*		 
		*/
		function price($value = 0, $args = array()){
			
			$currency        = isset( $args['currency'] ) ? $args['currency'] : '';
			
			if (!$currency ) {
				if(!isset($this->constants['woocommerce_currency'])){
					$this->constants['woocommerce_currency'] =  $currency = (function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : "USD");
				}else{
					$currency  = $this->constants['woocommerce_currency'];
				}
			}
			
			$args['currency'] 	= $currency;
			$value 				= trim($value);
			$withoutdecimal 	= str_replace(".","d",$value);
						
			if(!isset($this->constants['price_format'][$currency][$withoutdecimal])){
				if(function_exists('wc_price')){
					$v = wc_price($value, $args);
				}elseif(function_exists('woocommerce_price')){
					$v = woocommerce_price($value, $args);
				}else{
					if(!isset($this->constants['currency_symbol'])){
						$this->constants['currency_symbol'] =  $currency_symbol 	= apply_filters( 'ic_commerce_currency_symbol', '&#36;', 'USD');
					}else{
						$currency_symbol  = $this->constants['currency_symbol'];
					}					
					$value				= strlen(trim($value)) > 0 ? $value : 0;
					$v 					= $currency_symbol."".number_format($value, 2, '.', ' ');
					$v					= "<span class=\"amount\">{$v}</span>";
				}
				$this->constants['price_format'][$currency][$withoutdecimal] = $v;
			}else{
				$v = $this->constants['price_format'][$currency][$withoutdecimal];				
			}
			
			
			return $v;
		}
		
		/*
			* Function Name get_ic_commerce_report_page_reset_button_field_tabs
			*
			* create form reset buttons
			*
			* @param array $tabs
			*
			* @param string $report_name		
			*
			* @return string $tabs
			*		 
		*/
		function get_ic_commerce_report_page_reset_button_field_tabs($tabs = array(), $report_name = ""){
			$tabs[] = "cost_of_good_page";
			$tabs[] = "valuation_page";
			$tabs[] = "monthly_profit_product";
			return $tabs;
		}
		
		/*
			* Function Name get_ic_commerce_report_page_search_form_bottom
			*
			* create form reset buttons
			*
			* @param string $report_name		
			*
			* @param object $that			
			*		 
		*/
		function get_ic_commerce_report_page_search_form_bottom($report_name = "", $that = NULL){
			$cogs_enable_set_item = $this->get_setting('cogs_enable_set_item',$this->constants['plugin_options'],0);
			$cost_of_goods_only = ($cogs_enable_set_item == 1) ? 'yes' : 'no';
			if($report_name == "cost_of_good_page"){
				?>
					<div class="form-group">                                                
						<div class="FormRow FirstRow">
							<div class="label-text"><label for="product_type"><?php _e("Product Type:",'icwoocommerce_textdomains'); ?></label></div>
							<div class="input-text">
								<?php
									$product_types = array(
										'all' 		=> __("All",'icwoocommerce_textdomains')
										,'simple' 	=> __("Simple Product",'icwoocommerce_textdomains')
										,'variable' 	=> __("Variable Product",'icwoocommerce_textdomains')
										,'variation'	=> __("Variation Product",'icwoocommerce_textdomains')
									);
									$that->create_dropdown($product_types,"product_type[]","product_type",'',"product_type",'all', 'array', false);
								?>
							</div>
						</div>
					   
					   <div class="FormRow">
								<div class="label-text" style="padding-top:0px;"><label for="sort_by"><?php _e("Order By:",'icwoocommerce_textdomains');?></label></div>
									<div style="padding-top:0px;">
									 <?php
										$data = array(																		
											"total_amount" 				=>	__("Sales Amount",				'icwoocommerce_textdomains'),
											"total_cost_good_amount" 	=>	__("Total Cost of Goods",				'icwoocommerce_textdomains'),
											"margin_profit_amount" 		=>	__("Margin/Profit",				'icwoocommerce_textdomains'),
										);
										
										$that->create_dropdown($data,"sort_by","sort_by",NULL,"sort_by",'total_amount', 'array');
										
										$data = array("ASC" => __("Ascending",'icwoocommerce_textdomains'), "DESC" => __("Descending",'icwoocommerce_textdomains'));
										$that->create_dropdown($data,"order_by","order_by",NULL,"order_by",'ASC', 'array');
									?>
									</div>                                                        
								</div> 
																											   
					</div>
                   
					<div class="form-group">
						<div class="FormRow checkbox">
							<div class="label-text"><label for="cost_of_goods_only"><?php _e("Show items with Cost of Goods Only:",'icwoocommerce_textdomains');?></label></div>
							<div style="padding-top:5px;"><input type="checkbox" id="cost_of_goods_only" name="cost_of_goods_only"  value="yes" <?php if($this->get_request('cost_of_goods_only',$cost_of_goods_only,true) == "yes"){ echo ' checked="checked"';}?> /><span> <label for="cost_of_goods_only"><strong><?php //_e("Set item cost of goods is greater than 0",'icwoocommerce_textdomains');?></strong></label></span></div>
						</div>
					</div>
				<?php
			}
			
			if($report_name == "monthly_profit_product"){
				?>
                	<div class="form-group">
                    	<div class="FormRow checkbox FirstRow">
                            <div class="label-text"><label for="cost_of_goods_only"><?php _e("Show items with Cost of Goods Only:",'icwoocommerce_textdomains');?></label></div>
                            <div style="padding-top:5px;"><input type="checkbox" id="cost_of_goods_only" name="cost_of_goods_only"  value="yes" <?php if($this->get_request('cost_of_goods_only',$cost_of_goods_only,true) == "yes"){ echo ' checked="checked"';}?> /><span> <label for="cost_of_goods_only"><strong><?php //_e("Set item cost of goods is greater than 0",'icwoocommerce_textdomains');?></strong></label></span></div>
                        </div>
                        <div class="FormRow ">
                            <div class="label-text" style="padding-top:0px;"><label for="sort_by"><?php _e("Order By:",'icwoocommerce_textdomains');?></label></div>
                                <div style="padding-top:0px;">
                                 <?php
                                    $data = array(																		
                                        "total_amount" 				=>	__("Sales Amount",				'icwoocommerce_textdomains'),
                                        "total_cost_good_amount" 	=>	__("Total Cost of Goods",				'icwoocommerce_textdomains'),
                                        "margin_profit_amount" 		=>	__("Margin/Profit",				'icwoocommerce_textdomains'),
										 "order_date" 				=>	__("Month Name",				'icwoocommerce_textdomains'),
                                    );
                                    
                                    $that->create_dropdown($data,"sort_by","sort_by",NULL,"sort_by",'order_date', 'array');
                                    
                                    $data = array("ASC" => __("Ascending",'icwoocommerce_textdomains'), "DESC" => __("Descending",'icwoocommerce_textdomains'));
                                    $that->create_dropdown($data,"order_by","order_by",NULL,"order_by",'DESC', 'array');
                                ?>
                                </div>                                                        
                            </div>
                            
                            
                    </div>
                <?php
			}
			
			if($report_name == "valuation_page"){
				?>
				<div class="form-group">
                    <div class="FormRow checkbox">
                        <div class="label-text"><label for="cost_of_goods_only"><?php _e("Show items with Cost of Goods Only:",'icwoocommerce_textdomains');?></label></div>
                        <div style="padding-top:5px;"><input type="checkbox" id="cost_of_goods_only" name="cost_of_goods_only"  value="yes" <?php if($this->get_request('cost_of_goods_only',$cost_of_goods_only,true) == "yes"){ echo ' checked="checked"';}?> /><span> <label for="cost_of_goods_only"><strong><?php //_e("Set item cost of goods is greater than 0",'icwoocommerce_textdomains');?></strong></label></span></div>
                    </div>
                </div>
				<?php
			}
		} 
		
		
		/*
			* Function Name get_ic_commerce_report_page_search_form_bottom
			*
			* create form reset buttons
			*
			* @param array|object $rows		
			*
			* @param string $type			
			*
			* @param array $columns		
			*
			* @param string $report_name		
			*
			* @param object $this_filter		
			*
			* @return array|object $rows		
			*		 
		*/
		function get_ic_commerce_report_page_default_items($rows = array(), $type = "", $columns = array(), $report_name = "", $this_filter = NULL){
			switch($report_name){
				case "cost_of_good_page":
					$rows 		= $this->get_ic_commerce_custom_all_cost_of_goods_query(			$type, $columns, $report_name, $this_filter);break;
				case "valuation_page":
					$rows 		= $this->get_ic_commerce_custom_all_valuation_query(				$type, $columns, $report_name, $this_filter);break;
				case "monthly_profit_product":
					$rows 		= $this->get_ic_commerce_custom_all_monthly_profit_product_query(	$type, $columns, $report_name, $this_filter);break;				
			}
			return $rows;
		}
		
		/*
			* Function Name get_ic_commerce_custom_all_cost_of_goods_query
			*
			* create order items
			*
			* @param array|object $rows		
			*
			* @param string $type			
			*
			* @param array $columns		
			*
			* @param string $report_name		
			*
			* @param object $this_filter		
			*
			* @return array|object $rows		
			*		 
		*/
		function get_ic_commerce_custom_all_cost_of_goods_query(			$type = 'limit_row', $columns = array(), $report_name = "", $this_filter = NULL)
		{
			global $wpdb;			
			if(!isset($this_filter->items_query)){
				
				$request = $this_filter->get_all_request();extract($request);
				
				if($type == 'limit_row'){
					
				}
				
				$order_status					= $this_filter->get_string_multi_request('order_status',$order_status, "-1");
				$hide_order_status				= $this_filter->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
				$category_product_id_string 	= $this_filter->get_products_list_in_category($category_id);
				$category_id 					= "-1";
				
				$cogs_metakey_variable 			= isset($this->cogs_constants['cogs_metakey_variable']) 	? $this->cogs_constants['cogs_metakey_variable'] 	: "";
				$cogs_metakey_simple 			= isset($this->cogs_constants['cogs_metakey_simple']) 		? $this->cogs_constants['cogs_metakey_simple'] 		: "";
				$cogs_metakey_item_total 		= isset($this->cogs_constants['cogs_metakey_item_total']) 	? $this->cogs_constants['cogs_metakey_item_total'] 	: "";
				
				$this->constants['cog'] 		= $this->cogs_constants;
				$cogs_metakey_item				= $this_filter->get_setting('cogs_metakey_item',		$this->constants['cog'],'');
				$cogs_metakey_item_total		= $this_filter->get_setting('cogs_metakey_item_total',	$this->constants['cog'],'');
				
				$product_id_string 				=  NULL;
				if($category_product_id_string  && $category_product_id_string != "-1"){
					$product_id_string 	= strlen($product_id_string) > 0 ? ",".$category_product_id_string : $category_product_id_string;$category_product_id_string = "-1";
				}
							
				$sql = " SELECT ";
				
				$sql .= "
							woocommerce_order_itemmeta_product_id.meta_value 																				AS product_id
							,woocommerce_order_items.order_item_name 																						AS product_name
							,SUM(woocommerce_order_itemmeta_qty.meta_value) 																				AS quantity							
							,SUM(woocommerce_order_itemmeta_line_total.meta_value)/SUM(woocommerce_order_itemmeta_qty.meta_value)							AS sales_rate_amount							
							,SUM(woocommerce_order_itemmeta_line_subtotal.meta_value)/SUM(woocommerce_order_itemmeta_qty.meta_value)						AS product_rate
							,SUM(woocommerce_order_itemmeta_total_cost_of_item.meta_value)/SUM(woocommerce_order_itemmeta_qty.meta_value)					AS cost_of_good_amount
							,SUM(woocommerce_order_itemmeta_total_cost_of_item.meta_value) 																	AS total_cost_good_amount
							,SUM(woocommerce_order_itemmeta_line_total.meta_value) - SUM(woocommerce_order_itemmeta_total_cost_of_item.meta_value)			AS margin_profit_amount
							
							,SUM(woocommerce_order_itemmeta_line_total.meta_value) 																			AS total_amount
							,DATE(shop_order.post_date) 																									AS order_date
							,woocommerce_order_items.order_item_id																							AS order_item_id
				";
				
				$sql .= ", woocommerce_order_itemmeta_variation_id.meta_value 																			AS variation_id";
				
				if($product_type and $product_type == "variation"){
					//$sql .= ", woocommerce_order_itemmeta_variation_id.meta_value 																			AS variation_id";
				}
				
				$sql .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_qty					ON woocommerce_order_itemmeta_qty.order_item_id					=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_qty.meta_key					= '_qty'";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_line_total 			ON woocommerce_order_itemmeta_line_total.order_item_id			=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_line_total.meta_key			= '_line_total'";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_line_subtotal			ON woocommerce_order_itemmeta_line_subtotal.order_item_id		=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_line_subtotal.meta_key		= '_line_subtotal'";
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_product_id 			ON woocommerce_order_itemmeta_product_id.order_item_id			=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_product_id.meta_key 			= '_product_id'";
				
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_total_cost_of_item 	ON woocommerce_order_itemmeta_total_cost_of_item.order_item_id	=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_total_cost_of_item.meta_key 	= '{$cogs_metakey_item_total}'";
				$sql .= " LEFT JOIN  {$wpdb->posts} as shop_order 															ON shop_order.id												=	woocommerce_order_items.order_id		AND shop_order.post_type									= 'shop_order'";
				
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
				
				if($product_type and $product_type != "-1" and $product_type != "all" and $product_type != "variation"){
					$sql .= " 	
							LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships_product_type 	ON term_relationships_product_type.object_id		=	woocommerce_order_itemmeta_product_id.meta_value 
							LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy_product_type 		ON term_taxonomy_product_type.term_taxonomy_id		=	term_relationships_product_type.term_taxonomy_id
							LEFT JOIN  {$wpdb->prefix}terms 				as terms_product_type 				ON terms_product_type.term_id						=	term_taxonomy_product_type.term_id";
				}
				
				$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_variation_id 			ON woocommerce_order_itemmeta_variation_id.order_item_id			=	woocommerce_order_items.order_item_id";
				
				if($product_type and $product_type == "variation"){
					//$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_variation_id 			ON woocommerce_order_itemmeta_variation_id.order_item_id			=	woocommerce_order_items.order_item_id";
				}
					
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
							
				$sql .= " WHERE 1*1 ";
				
				$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_key 			= '_variation_id'";
				
				if($product_type and $product_type == "variation"){					
					$sql .= " AND woocommerce_order_itemmeta_variation_id.meta_value > 0";
				}
				
				if ($start_date != NULL &&  $end_date !=NULL){
					$sql .= " AND (DATE(shop_order.post_date) BETWEEN '".$start_date."' AND '". $end_date ."')";
				}
				
				if($product_id  && $product_id != "-1") 
					$sql .= " AND woocommerce_order_itemmeta_product_id.meta_value IN (".$product_id .")";	
					
				if($product_id_string  && $product_id_string != "-1") 
					$sql .= " AND woocommerce_order_itemmeta_product_id.meta_value IN (".$product_id_string .")";	
				
				if($category_id  && $category_id != "-1"){
					$sql .= " AND terms.term_id IN (".$category_id .")";
				}
				
				if($order_status_id  && $order_status_id != "-1") 
					$sql .= " AND terms2.term_id IN (".$order_status_id .")";
							
				
				if(strlen($publish_order)>0 && $publish_order != "-1" && $publish_order != "no" && $publish_order != "all"){
					$in_post_status		= str_replace(",","','",$publish_order);
					$sql .= " AND  shop_order.post_status IN ('{$in_post_status}')";
				}
				
				if($cost_of_goods_only == "yes"){
					$sql .= " AND woocommerce_order_itemmeta_total_cost_of_item.meta_value > 0";
				}
				
				
				if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND shop_order.post_status IN (".$order_status.")";
				
				if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND shop_order.post_status NOT IN (".$hide_order_status.")";
				
				if($product_type and $product_type != "-1" and $product_type != "all" and $product_type != "variation")	$sql .= " AND terms_product_type.name IN ('{$product_type}')";
				
				$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= " GROUP BY woocommerce_order_itemmeta_product_id.meta_value";
				
				
				if($product_type and $product_type == "variation"){
					///$sql .= ", woocommerce_order_itemmeta_variation_id.meta_value";					
				}
				
				$sql .= " ORDER BY {$sort_by} {$order_by}";
				
				$this->items_query = $sql;
				
				
				
			}else{
				$sql = $this->items_query;
			}
			
			
			$order_items = $this_filter->get_query_items($type,$sql);
			
			
			if($type == 'limit_row' or $type == 'all_row' or $type == 'all_row_total'){
				
				if($type == 'limit_row'){					
					
				}
				
				$varation_string = array();				
				if($product_type and $product_type == "variation"){
					$variation_ids 		= $this_filter->get_items_id_list($order_items,'variation_id','','string');
					$attributes 		= $this_filter->get_variaiton_attributes('variation_id',$variation_ids);
					$varation_string 	= isset($attributes['varation_string']) ? $attributes['varation_string'] : array();
				}
				
				
				
				foreach($order_items as $result_key => $result_data){
					$total_cost_good_amount 	= isset($result_data->total_cost_good_amount) 	? $result_data->total_cost_good_amount 	: 0;
					$margin_profit_amount 		= isset($result_data->margin_profit_amount) 	? $result_data->margin_profit_amount 	: 0;
					$profit_percentage 			= isset($result_data->profit_percentage) 		? $result_data->profit_percentage 		: 0;
					$variation_id 				= isset($result_data->variation_id) 			? $result_data->variation_id 			: 0;
					$product_id 				= isset($result_data->product_id) 				? $result_data->product_id 				: 0;
					$order_item_id 				= isset($result_data->order_item_id) 			? $result_data->order_item_id 			: 0;
					
					if($total_cost_good_amount != 0 and $margin_profit_amount != 0){
						$profit_percentage = ($margin_profit_amount/$total_cost_good_amount)*100;
						$order_items[$result_key]->profit_percentage 			= $profit_percentage;
					}else{
						$order_items[$result_key]->margin_profit_amount 		= 0;
						$order_items[$result_key]->profit_percentage 			= 0;
					}
					
					
					if($variation_id > 0){
						$order_items[$result_key]->variation = isset($varation_string[$variation_id]['varation_string']) ? $varation_string[$variation_id]['varation_string'] : '';
					}
					
					if($variation_id > 0){
						$order_items[$result_key]->product_name = get_the_title($product_id);
					}
					
					$order_items[$result_key]->product__sku = get_post_meta($product_id,'_sku',true);
				}
				
				//$this->print_array($order_items);
			}
			return $order_items;
		}
		
		/*
			* Function Name get_ic_commerce_custom_all_valuation_query
			*
			* create order items
			*
			* @param array|object $rows		
			*
			* @param string $type			
			*
			* @param array $columns		
			*
			* @param string $report_name		
			*
			* @param object $this_filter		
			*
			* @return array|object $rows		
			*		 
		*/
		function get_ic_commerce_custom_all_valuation_query($type = 'limit_row', $columns = array(), $report_name = "", $this_filter = NULL)
		{
			global $wpdb;
			if(!isset($this_filter->items_query)){
				
				$request = $this_filter->get_all_request();extract($request);
				
				$category_product_id_string 	= $this_filter->get_products_list_in_category($category_id, $product_id);//Added 20150219
				$category_id 					= "-1";//Added 20150219
				
				$product_id_string 				=  '';
				if($category_product_id_string  && $category_product_id_string != "-1"){
					
					if($product_id != '-1' and $product_id != ""){
						$product_id_string 	= $product_id.",".$category_product_id_string;
					}else{
						$product_id_string 	= $category_product_id_string;
					}
					$category_product_id_string = "-1";
				}else{
					if($product_id != '-1' and $product_id != ""){
						$product_id_string 	= $product_id;
					}
				}
				
				if($product_id_string != '-1' and $product_id_string != ""){
					$variation_id = $this->get_variation_product_id($product_id_string);
					if($variation_id != '-1' and $variation_id != ""){
						$product_id_string 	= $product_id_string.",".$variation_id;
					}
				}
				
				$cogs_metakey = isset($this->cogs_constants['cogs_metakey']) ? ltrim($this->cogs_constants['cogs_metakey'],"_") : "";
				
				$sql = "SELECT posts.ID  AS product_id";
				
				$sql .= " ,posts.post_title 		AS valuation_product_name";
				
				$sql .= " ,postmeta_cog.meta_value 	AS cost_of_goods";
				
				$sql .= " ,sum(stock.meta_value) AS productstock";
				
				$sql .= " ,sum(stock.meta_value * postmeta_cog.meta_value) AS valuation";
				
				$sql .= " , sum(stock.meta_value * price.meta_value) AS return_amount";
				
				$sql .= " FROM {$wpdb->posts} 		AS posts";
				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS postmeta_cog ON posts.ID = postmeta_cog.post_id";				
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS stock ON posts.ID = stock.post_id";
				$sql .= " LEFT JOIN {$wpdb->postmeta} AS price ON posts.ID = price.post_id";
				
				$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
				
				$sql .= " WHERE 1*1";
				
				$sql .= " AND posts.post_type IN ( 'product', 'product_variation' )";
				
				$sql .= " AND posts.post_status = 'publish'";
				
				$sql .= " AND postmeta_cog.meta_key IN ('_{$cogs_metakey}')";
				
				if($cost_of_goods_only == "yes"){
					$sql .= " AND postmeta_cog.meta_value > 0";
					$sql .= " AND CAST(postmeta_cog.meta_value AS DECIMAL(10,2)) > 0";
				}
				
				$sql .= " AND stock.meta_key = '_stock' ";				
				
				$sql .= " AND CAST(stock.meta_value AS DECIMAL(10,2)) > 0";
				
				$sql .= " AND price.meta_key = '_price' ";				
				
				$sql .= " AND CAST(price.meta_value AS DECIMAL(10,2)) > 0";
				
				$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
				
				if($product_id_string  && $product_id_string != "-1") {
					$sql .= " AND posts.ID IN ({$product_id_string})";				
				}
				
				$sql .= " GROUP BY posts.ID";
					
				$sql .= " ORDER BY posts.post_title ASC";	
				
				//$this->create_wp_log($sql);
				
				$this->items_query = $sql;
				
				//$this->print_sql($sql);
				
			}else{
				$sql = $this->items_query;
			}			
			
			$order_items = $this_filter->get_query_items($type,$sql);
			
			return $order_items;
		}
		
		/* get_variation_product_id 
		 * 
		 * Get all post meta 
		 *
		 * @param string  product_ids 
		 * @return string
		 */	
		function get_variation_product_id($product_ids = ''){
			global $wpdb;
			
			$query = "SELECT";
			$query .= " posts.ID AS variation_id";	
			$query .= " FROM $wpdb->posts AS posts";
			$query .= " WHERE 1*1";
			$query .= " AND posts.post_type IN ('product_variation')";	
			
			if($product_ids != '-1' and $product_ids != ""){
				$query .= " AND posts.post_parent IN ($product_ids)";	
			}
			
			$query .= " GROUP BY posts.ID";
			$items = $wpdb->get_results($query);
			
			$variation_ids 		= $this->get_items_id_list($items,'variation_id','','string');
			
			return $variation_ids;
		}
		
		/*
			* Function Name get_ic_commerce_custom_all_monthly_profit_product_query
			*
			* create order items
			*
			* @param array|object $rows		
			*
			* @param string $type			
			*
			* @param array $columns		
			*
			* @param string $report_name		
			*
			* @param object $this_filter		
			*
			* @return array|object $rows		
			*		 
		*/
		function get_ic_commerce_custom_all_monthly_profit_product_query(	$type = 'limit_row', $columns = array(), $report_name = "", $this_filter = NULL)
		{
				global $wpdb;
				if(!isset($this_filter->items_query)){
					
					$request 						= $this_filter->get_all_request();extract($request);
					
					$cogs_metakey_item_total		= $this->get_setting('cogs_metakey_item_total',	$this->constants['plugin_options'],'');
					$order_status					= $this_filter->get_string_multi_request('order_status',$order_status, "-1");
					$hide_order_status				= $this_filter->get_string_multi_request('hide_order_status',$hide_order_status, "-1");
					$category_product_id_string 	= $this_filter->get_products_list_in_category($category_id);//Added 20150219
					$category_id 					= "-1";//Added 20150219
					
					$sql = " SELECT ";
					$sql .= "				
						SUM(woocommerce_order_itemmeta_qty.meta_value) 																					AS quantity							
						,SUM(woocommerce_order_itemmeta_total_cost_of_item.meta_value) 																	AS total_cost_good_amount
						,SUM(woocommerce_order_itemmeta_line_total.meta_value) - SUM(woocommerce_order_itemmeta_total_cost_of_item.meta_value)			AS margin_profit_amount							
						,SUM(woocommerce_order_itemmeta_line_total.meta_value) 																			AS total_amount	
						,MONTHNAME(shop_order.post_date) 																								AS month_name
						,YEAR(shop_order.post_date) 																									AS order_year
					";
					
					$sql .= ", DATE_FORMAT(shop_order.post_date,'%Y-%m') 																				AS month_key";
					$sql .= ", shop_order.post_date																										AS order_date";
					$sql .= ", MIN(DATE_FORMAT(shop_order.post_date,'%Y-%m-%d')) 																		AS start_date";
					$sql .= ", MAX(DATE_FORMAT(shop_order.post_date,'%Y-%m-%d')) 																		AS end_date";
					
					$sql .= " FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items";
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_qty					ON woocommerce_order_itemmeta_qty.order_item_id					=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_qty.meta_key					= '_qty'";
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_line_total 			ON woocommerce_order_itemmeta_line_total.order_item_id			=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_line_total.meta_key			= '_line_total'";
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_line_subtotal			ON woocommerce_order_itemmeta_line_subtotal.order_item_id		=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_line_subtotal.meta_key		= '_line_subtotal'";
					$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_total_cost_of_item 	ON woocommerce_order_itemmeta_total_cost_of_item.order_item_id	=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_total_cost_of_item.meta_key 	= '{$cogs_metakey_item_total}'";
					$sql .= " LEFT JOIN  {$wpdb->posts} as shop_order 															ON shop_order.id												=	woocommerce_order_items.order_id		AND shop_order.post_type									= 'shop_order'";
					
					if($category_product_id_string != -1 || $product_id != -1)
						$sql .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_product_id 			ON woocommerce_order_itemmeta_product_id.order_item_id			=	woocommerce_order_items.order_item_id	AND woocommerce_order_itemmeta_product_id.meta_key 			= '_product_id'";
										
					if($this->constants['post_order_status_found'] == 0 ){
						if(count($shop_order_status)>0){
							$sql .= " 
							LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
							LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
						}
					}	
					
					$sql = apply_filters("ic_commerce_report_page_join_query", $sql, $request, $type, $page, $report_name, $columns);
					
					$sql .= " WHERE 1*1 ";
					
					if ($start_date != NULL &&  $end_date != NULL){
						$sql .= " AND DATE(shop_order.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
					}
					
					if($order_status  && $order_status != '-1' and $order_status != "'-1'")$sql .= " AND shop_order.post_status IN (".$order_status.")";
				
					if($hide_order_status  && $hide_order_status != '-1' and $hide_order_status != "'-1'")$sql .= " AND shop_order.post_status NOT IN (".$hide_order_status.")";
					
					if($cost_of_goods_only == "yes"){
						$sql .= " AND woocommerce_order_itemmeta_total_cost_of_item.meta_value > 0";
					}
					
					if($category_product_id_string != -1 and $category_product_id_string != ""){
						$sql .= " AND woocommerce_order_itemmeta_product_id.meta_value IN ($category_product_id_string)";
					}
						
					if($product_id != -1 and $product_id != ""){
						$sql .= " AND woocommerce_order_itemmeta_product_id.meta_value IN ($product_id)";
					}
					
					$sql = apply_filters("ic_commerce_report_page_where_query", $sql, $request, $type, $page, $report_name, $columns);
					
					
					$sql .= " GROUP BY month_key ORDER BY {$sort_by} {$order_by}";
					
					
					$this->items_query = $sql;
								
				}else{
					$sql = $this->items_query;
				}			
				
				$order_items = $this_filter->get_query_items($type,$sql);
				
				
				
				return $order_items;
		}
		
		/*
			* Function Name get_ic_commerce_report_page_data_items
			*
			* create order items other data
			*
			* @param array|object $order_items
			*
			* @return array|object $order_items		
			*		 
		*/
		function get_ic_commerce_report_page_data_items($order_items = array()){
			$report_name			= isset($_REQUEST['report_name']) 		? $_REQUEST['report_name'] : "";
			if($report_name == "valuation_page"){
				$product_ids 			= $this->get_items_id_list($order_items,'product_id');				
				$products_stock 		= $this->get_postmeta_list($product_ids,'_stock');				
				$products_regular_price = $this->get_postmeta_list($product_ids,'_regular_price');				
				$products_sale_price 	= $this->get_postmeta_list($product_ids,'_sale_price');				
				
				foreach($order_items as $item_key => $item_value){
					
					$cost_of_goods 	= isset($item_value->cost_of_goods)				? $item_value->cost_of_goods : 0;
					$product_id 	= isset($item_value->product_id)				? $item_value->product_id : 0;
					$product_stock	= isset($products_stock[$product_id]) 			? trim($products_stock[$product_id]) : 0;
					$regular_price	= isset($products_regular_price[$product_id]) 	? trim($products_regular_price[$product_id]) : 0;
					$sale_price		= isset($products_sale_price[$product_id]) 		? trim($products_sale_price[$product_id]) : '';
					$sale_price		= $sale_price === 0								? $sale_price : ($sale_price === '' ? $regular_price : $sale_price);
					$product_stock	= $product_stock + 0;
					
					$order_items[$item_key]->sales_rate 		= $sale_price;
					$order_items[$item_key]->productstock 		= $product_stock + 0;
					$order_items[$item_key]->valuation 			= $product_stock * $cost_of_goods;
					$order_items[$item_key]->return_amount 		= $product_stock * $sale_price;
				}
			}
			
			return $order_items;
		}
		
		/*
			* Function Name get_postmeta_list
			*
			* create item postmeta list
			*
			* @param string $product_ids
			*
			* @return array $product_stocks		
			*		 
		*/
		function get_postmeta_list($product_ids, $meta_key = ""){
			global $wpdb;
			$sql = "SELECT  meta_key, meta_value, post_id FROM {$wpdb->postmeta} AS postmeta WHERE postmeta.meta_key = '{$meta_key}' AND postmeta.post_id IN ($product_ids)";
			$items = $wpdb->get_results($sql);
			$product_stocks = array();
			foreach($items as $key => $value){
					$product_stocks[$value->post_id] = $value->meta_value;
			}
			return $product_stocks;
		}
		
		/*
			* Function Name get_ic_commerce_report_page_grid_columns
			*
			* create grid columns
			*
			* @param array $columns
			*
			* @param sring $report_name
			*
			* @return array $columns		
			*		 
		*/
		function get_ic_commerce_report_page_grid_columns($columns = array(), $report_name = ""){
			switch($report_name){
				case "cost_of_good_page":
					$columns 	= array(
						"product__sku"								=> __("Product SKU", 			'icwoocommerce_textdomains')
						,"product_name"								=> __("Product Name", 			'icwoocommerce_textdomains')
						,"varation"									=> __("Varation", 				'icwoocommerce_textdomains')
						,"order_date"								=> __("order date", 			'icwoocommerce_textdomains')
						,"quantity"									=> __("Sales Qty", 				'icwoocommerce_textdomains')
						,"product_rate"								=> __("Avg. Prod. Rate", 		'icwoocommerce_textdomains')
						,"cost_of_good_amount"						=> __("Avg. COGs", 				'icwoocommerce_textdomains')						
						,"sales_rate_amount"						=> __("Avg. Sales Rate", 		'icwoocommerce_textdomains')
						,"total_cost_good_amount"					=> __("Total Cost of Goods", 	'icwoocommerce_textdomains')
						,"total_amount"								=> __("Sales Amount", 			'icwoocommerce_textdomains')
						,"margin_profit_amount"						=> __("Margin/Profit", 			'icwoocommerce_textdomains')
						,"profit_percentage"						=> __("Profit %", 				'icwoocommerce_textdomains')
						,"product_stock"							=> __("Current Stock", 			'icwoocommerce_textdomains')
						,"product_edit"								=> __("Edit", 					'icwoocommerce_textdomains')
						
					);
					
					$product_type = $this->get_request('product_type');
					if($product_type != 'variation') unset($columns['varation']);
					
					unset($columns['order_date']);
					
					break;
				case "valuation_page":
					$columns 	= array(
						"valuation_product_name"					=> __("Product Name", 			'icwoocommerce_textdomains')
						,"productstock"								=> __("In Stock", 					'icwoocommerce_textdomains')
						,"cost_of_goods"							=> __("Cost of Good", 					'icwoocommerce_textdomains')
						,"sales_rate"								=> __("Sales Price", 					'icwoocommerce_textdomains')
						,"valuation"								=> __("Valuation at Cost", 		'icwoocommerce_textdomains')
						,"return_amount"							=> __("Valuation at Retail", 	'icwoocommerce_textdomains')
						
					);
					break;
				case "monthly_profit_product":
					$columns 	= array();
					$columns['month_name'] 				= __("Month Name",	'icwoocommerce_textdomains');
					$columns['order_year'] 				= __("Year",	'icwoocommerce_textdomains');
					$columns['quantity'] 				= __("Quantity Sold",	'icwoocommerce_textdomains');
					$columns['total_cost_good_amount'] 	= __("Total Cost of Goods",	'icwoocommerce_textdomains');
					$columns['total_amount'] 			= __("Sales Amount	",	'icwoocommerce_textdomains');	
					$columns['margin_profit_amount'] 	= __("Margin/Profit",	'icwoocommerce_textdomains');		
					break;
			}
			
			return $columns;
		}
		
		
		/*
			* Function Name get_ic_commerce_report_page_result_columns
			*
			* create grid columns
			*
			* @param array $total_columns
			*
			* @param sring $report_name
			*
			* @return array $columns		
			*		 
		*/
		function get_ic_commerce_report_page_result_columns($total_columns = array(), $report_name = ""){
			
			switch($report_name){
				case "cost_of_good_page":
					$total_columns = array(
						"total_row_count"							=> __("Product Count", 			'icwoocommerce_textdomains')
						,"quantity"									=> __("Sales Qty", 				'icwoocommerce_textdomains')
						,"total_cost_good_amount"					=> __("Total Cost of Goods", 	'icwoocommerce_textdomains')
						,"total_amount"								=> __("Sales Amount", 			'icwoocommerce_textdomains')
						,"margin_profit_amount"						=> __("Margin/Profit", 			'icwoocommerce_textdomains')
						,"profit_percentage"						=> __("Profit %", 				'icwoocommerce_textdomains')						
					);
					break;
				case "valuation_page":
					$total_columns 	= array(						
						"productstock"								=> __("Stock", 					'icwoocommerce_textdomains')
						,"valuation"								=> __("Valuation at Cost", 				'icwoocommerce_textdomains')
						,"return_amount"							=> __("Valuation at Retail", 				'icwoocommerce_textdomains')
						
					);
					break;
				case "monthly_profit_product":
					$total_columns 	= array();
					$total_columns['quantity'] 				= __("Quantity Sold",	'icwoocommerce_textdomains');
					$total_columns['total_cost_good_amount'] 	= __("Total Cost of Goods",	'icwoocommerce_textdomains');
					$total_columns['margin_profit_amount'] 	= __("Margin/Profit",	'icwoocommerce_textdomains');
					$total_columns['total_amount'] 			= __("Sales Amount	",	'icwoocommerce_textdomains');			
					break;
			}
			return $total_columns;
		}
		
		/*
			* Function Name get_ic_commerce_report_page_result_columns
			*
			* create grid columns alignment
			*
			* @param array $column
			*
			* @param sring $report_name
			*
			* @return array $custom_columns		
			*		 
		*/
		function get_ic_commerce_pdf_custom_column_right_alignment($custom_columns = array(),$column = array(), $report_name = NULL){
			//echo $report_name."fdsafdasfdasf";
			switch($report_name){
				case "cost_of_good_page":
					$custom_columns = array(
						"total_row_count"			=> ""				
						,"quantity"					=> ""
						,"total_cost_good_amount"	=> ""
						,"total_amount"				=> ""
						,"margin_profit_amount"		=> ""
						,"profit_percentage"		=> ""
						,"product_rate"				=> ""
						,"cost_of_good_amount"		=> ""
					);
					break;
				case "valuation_page":
					$custom_columns = array(
						"productstock"				=> ""
						,"cost_of_goods"			=> ""						
						,"sales_rate"				=> ""
						,"valuation"				=> ""
						,"return_amount"			=> ""
					);
					break;
				case "monthly_profit_product":
					$custom_columns = array(
						"order_year"				=> ""
						,"quantity"					=> ""
						,"total_cost_good_amount"	=> ""						
						,"margin_profit_amount"		=> ""
						,"total_amount"				=> ""
					);
					break;
			}
			return $custom_columns;
		}
		
		/*
			* Function Name get_ic_commerce_report_page_grid_price_columns
			*
			* add more price column
			*
			* @param array $price_columns
			*
			* @param sring $report_name
			*
			* @return array $price_columns		
			*		 
		*/
		function get_ic_commerce_report_page_grid_price_columns($price_columns = array(), $report_name = NULL){
			
			switch($report_name){
				case "valuation_page":
					$price_columns[] = 'cost_of_goods';
					$price_columns[] = 'sales_rate';
					$price_columns[] = 'valuation';
					$price_columns[] = 'return_amount';
					break;
				case "monthly_profit_product":
					$price_columns[] = 'total_cost_good_amount';
					$price_columns[] = 'margin_profit_amount';
					$price_columns[] = 'total_amount';
					break;
			}
			return $price_columns;
		
		}
		
		/*
			* Function Name get_ic_commerce_report_page_data_grid_items_create_grid_items
			*
			* add more value in order items
			*
			* @param array|object $order_items		
			*
			* @param array $columns		
			*
			* @param string $report_name		
			*
			* @param string $type			
			*
			* @param string $zero		
			*
			* @return array|object $order_items		
			*		 
		*/
		function get_ic_commerce_report_page_data_grid_items_create_grid_items($order_items = array(), $columns = '', $report_name = '', $request = '', $type = '', $zero = ''){
			return $order_items;
			$start_date = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : '';
			$end_date = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : '';
			//echo "<br>";
			$admin_url = admin_url("admin.php")."?page=".$this->constants['plugin_key']."_details_page&detail_view=no";
			if($report_name == "monthly_profit_product"){
				foreach($order_items as $item_key => $item_value){
					
					$month_name = isset($item_value->month_name) ? $item_value->month_name : '';
					$start_date = isset($item_value->start_date) ? $item_value->start_date : 0;
					$end_date 	= isset($item_value->end_date) ? $item_value->end_date : 0;
					$order_items[$item_key]->month_name = "<a href=\"{$admin_url}&start_date={$start_date}&end_date={$end_date}\" target=\"_blank\">{$month_name}</a>";
				}
			}			
			return $order_items;
			
		}//End Method
		
		
		/*
			* Function Name create_wp_log
			*
			* Create Log
			*
			* @param string $content		
			*
			* @param clear boolen		
			*					 
		*/
		function create_wp_log($content = '', $clear = false){
			$error_folder  = ABSPATH.'wp-logs/';			
			$new_line	   = "\n";
			
			if(!isset($this->constants['log_created'])){
				
				if (!file_exists($error_folder)) {
					@mkdir($error_folder, 0777, true);
				}
				
				$this->constants['log_created'] 	= date_i18n("Y-m-d H:i:s");
				$today_date 						= date_i18n("Y-m-d");
				$this->constants['log_file_name'] 	= $error_folder . "/wc_ultimate_log_{$today_date}.log";				
				//$new_line	   						= "";
			}
			
			$clear 			= $clear == true ? "w" : "a";			
			$date 			= $this->constants['log_created'];			
			$fp 			= fopen($this->constants['log_file_name'],$clear);
			
			fwrite($fp,"{$new_line}{$date}:\t $content");
			
			fclose($fp);
		}
		
		
		
	}//End Class
}//End Class