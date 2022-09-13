<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Init')){
	/*
	 * Class Name IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Init
	 *
	 * Class is used for returning all reports data
	 *	  
	*/
	class IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Init{
		
		/* variable declaration*/
		public $constants 			=	array();
		
		/* variable declaration*/
		public $cogs_constants 		=	array();
		
		/* variable declaration*/
		public $cost_of_goods 		=	NULL;
		
		/**
		* __construct
		*/
		function __construct($constants = array(), $plugin_key = ""){
			$this->constants	= array_merge($this->constants, $constants);
			
			
			add_action( "ic_commerce_ultimate_report_init", 			array($this, "get_cog_ic_commerce_init"),101,2);
			if($this->constants['is_wc_ge_3_0_5']){
				add_action( 'woocommerce_new_order_item',  				array($this, 'set_cog_woocommerce_add_order_item_meta'), 101, 3 );
			}else{
				add_action( 'woocommerce_add_order_item_meta',  		array($this, 'set_cog_woocommerce_add_order_item_meta'), 101, 3 );
			}
			add_action( 'woocommerce_checkout_update_order_meta', 		array($this, 'set_cog_woocommerce_checkout_update_order_meta'), 101 );
			add_action( 'woocommerce_save_product_variation',			array($this, 'set_woocommerce_save_product_variation'),30,2);
			add_action( "ic_commerce_ultimate_report_page_init", 		 array($this, "get_ic_commerce_ultimate_report_page_init"),101,2);			
			
			add_action( 'admin_init', 									array($this, 'admin_init'));
			
			//add_action( 'woocommerce_hidden_order_itemmeta', 			 array( $this, 'woocommerce_hidden_order_itemmeta' ) );
			
		}
		
		/*
		 * Function Name woocommerce_hidden_order_itemmeta
		 *
		 * Initialize Class Default Settings, Assigned Variables
		 *
		 * @return array new default value on setting save
		*/
		function woocommerce_hidden_order_itemmeta($hidden_meta = array()){	
			if(isset($_REQUEST['post']) and $_REQUEST['post'] > 0){
				//$hidden_meta[] = '_ic_cogs_item';
				//$hidden_meta[] = '_ic_cogs_item_total';
			}else{
				$hidden_meta[] = '_ic_cogs_item';
				$hidden_meta[] = '_ic_cogs_item_total';
			}
			/*print("<pre>");
			print_r($_REQUEST);
			print("<pre>");*/
			return $hidden_meta;
		}
		
		function admin_init(){
			if(isset($_GET['post_type']) and $_GET['post_type'] == 'product'){
				$this->get_cog_ic_commerce_init();
				if($this->cost_of_goods->get_cogs_enabled()){
					add_filter( 'manage_edit-product_columns', 			   array($this, "show_cost_of_goods"),20);			
					add_action( 'manage_product_posts_custom_column', 		array($this, "show_cost_of_goods_value"),20);
					add_filter( 'manage_edit-product_sortable_columns',	  array($this, "show_cost_of_goods_sortable"),20);
					add_action( 'load-edit.php', 							 array($this, 'sort_cost_of_goods_load'));
					add_action( 'admin_footer', 							 array($this, 'admin_footer'));
				}
			}
		}
				
		
		/**
		 * show_cost_of_goods.
		 *
		 * @param string[] $columns
		 * @return string[] $new_columns
		 */
		function show_cost_of_goods( $columns ) {		
			$new_columns = array();		
			foreach ( $columns as $column_name => $column_info ) {
				$new_columns[ $column_name ] = $column_info;		
				if ( 'price' === $column_name ) {
					$new_columns['ic_cog'] = __('Cost of Goods', 'icwoocommerce_textdomains');	
				}
			}
			
			if(!isset($new_columns['ic_cog'])){
				$new_columns['ic_cog'] = __('Cost of Goods', 'icwoocommerce_textdomains');	
			}
		
			return $new_columns;
		}
		
		function show_cost_of_goods_value($column = ''){
			global $post;

			if ( 'ic_cog' === $column ) {	
				$post_id = $post->ID;
				
				
				$args = array(
					'post_parent' => $post_id,
					'post_type'   => 'product_variation', 
					'numberposts' => -1
					
				);
				$children = get_children( $args );
				if(count($children)>0){
					$p = array();
					$i = 0;
					foreach($children as $key => $chil){
						$child_id = $chil->ID;
						$cog = get_post_meta( $child_id, '_ic_cogs_cost', true );
						if(!empty($cog)){
							$p[$child_id] =$cog;
							$i++;
						}
					}
					if($i > 0){
						if($i > 1){
							sort($p);
							$min = isset($p[0]) ? $p[0] : '';
							$max = isset($p[$i-1]) ? $p[$i-1] : '';
							
							echo wc_price($min);
							echo " - ";
							echo wc_price($max);
						}else{
							$min = isset($p[0]) ? $p[0] : '';
							echo wc_price($min);
						}
					}
					
				}else{
					$value    = get_post_meta( $post_id, '_ic_cogs_cost', true );
					if($value > 0){
						echo wc_price($value);
					}
				}
				
			}
		}
		
		function show_cost_of_goods_sortable( $columns ) {
			$columns['ic_cog'] = '_ic_cogs_cost';		
			return $columns;
		}
		
		
		function sort_cost_of_goods_load() {
			add_filter( 'request', array($this, 'sort_cost_of_goods'));
		}
		
		/* Sorts the cost_of_goods. */
		function sort_cost_of_goods( $vars ) {
		
			/* Check if we're viewing the 'product' post type. */
			if ( isset( $vars['post_type'] ) && 'product' == $vars['post_type'] ) {
		
				/* Check if 'orderby' is set to 'duration'. */
				if ( isset( $vars['orderby'] ) && '_ic_cogs_cost' == $vars['orderby'] ) {
		
					/* Merge the query vars with our custom variables. */
					$vars = array_merge(
						$vars,
						array(
							'meta_key' => '_ic_cogs_cost',
							'orderby' => 'meta_value_num'
						)
					);
				}
			}
		
			return $vars;
		}
		
		function admin_footer(){
		?>
        	<style type="text/css">
            	table.wp-list-table .column-ic_cog{width: 10ch;}
            </style>
        <?php
		}
		
		/**
		* get_ic_commerce_ultimate_report_page_init
		* @param array $constants 
		* @param string $admin_page 
		* @return void
		*/
		function get_ic_commerce_ultimate_report_page_init($constants = array(), $admin_page = ""){
			if($admin_page == "icwoocommerceultimatereport_cross_tab_page"){
				require_once("ic_commerce_ultimate_report_cog_crosstab.php");
				$new = new IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Crosstab($constants);
			}
		}
		/**
		* set_woocommerce_save_product_variation
		* @param integer $variation_id 
		* @param string $i 
		* @return void
		*/
		function set_woocommerce_save_product_variation( $variation_id = 0, $i = 0 ){
			$this->get_cost_of_goods_class($this->constants,$this->constants['plugin_key']);
			return $this->cost_of_goods->set_woocommerce_save_product_variation($variation_id, $i);
		}
		/**
		* get_cost_of_goods_class
		* @param array $constants 
		* @param string $plugin_key 
		* @return object
		*/
		function get_cost_of_goods_class($constants = array(), $plugin_key = ""){			
			if($this->cost_of_goods == NULL){
				require_once('ic_commerce_ultimate_report_cog.php');
				$this->cost_of_goods = new IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods($constants,$plugin_key);
				$this->cost_of_goods->define_constant();
			}
			return $this->cost_of_goods;
		}
		/**
		* get_cog_ic_commerce_init
		* @param array $constants 
		* @param string $plugin_key 
		* @return object
		*/
		function get_cog_ic_commerce_init($constants = array(), $plugin_key = ""){			
			$this->get_cost_of_goods_class($constants,$plugin_key);
			$this->cost_of_goods->admin_init();
			
			$admin_page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
			$plugin_key	= isset($this->constants['plugin_key']) ? $this->constants['plugin_key'] : '';
			if($admin_page == $plugin_key."_page"){
				require_once('ic_commerce_ultimate_report_cog_dashboard.php');
				$dashboard = new IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Dashboard($constants);
			}
			
			
		}
		/**
		* set_cog_woocommerce_add_order_item_meta
		* @param integer $item_id 
		* @param string $values 
		* @param integer $cart_item_key 
		* @return void
		*/
		function set_cog_woocommerce_add_order_item_meta($item_id = 0, $values, $cart_item_key = 0){
			$this->get_cost_of_goods_class($this->constants,$this->constants['plugin_key']);
			
			if(!$this->cost_of_goods->get_cogs_enabled()) return $item_id;
			$this->cost_of_goods->woocommerce_add_order_item_meta($item_id, $values, $cart_item_key);
		
		}
		/**
		* set_cog_woocommerce_checkout_update_order_meta
		* @param integer $order_id 
		*
		* @return void
		*/
		function set_cog_woocommerce_checkout_update_order_meta($order_id){
			$this->get_cost_of_goods_class($this->constants,$this->constants['plugin_key']);
			if(!$this->cost_of_goods->get_cogs_enabled()) return false;
			$this->cost_of_goods->woocommerce_checkout_update_order_meta($order_id);
		}
		
		
		
		
	}//End IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Init
}//End IC_Commerce_Ultimate_Woocommerce_Report_Cost_of_Goods_Init