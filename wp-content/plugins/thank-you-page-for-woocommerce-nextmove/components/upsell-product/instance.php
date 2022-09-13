<?php
defined( 'ABSPATH' ) || exit;

class XLWCTY_Upsell_Products extends XLWCTY_Component {

	private static $instance = null;
	public $viewpath = '';
	public $upsell_product = array();
	public $grid_type = '2c';
	public $is_disable = true;

	public function __construct( $order = false ) {
		parent::__construct();
		add_action( 'woocommerce_add_to_cart', array( $this, 'woocommerce_add_to_cart' ), 10, 2 );
		add_action( 'xlwcty_after_component_data_setup_xlwcty_upsell_product', array( $this, 'setup_style' ) );
		add_action( 'xlwcty_after_components_loaded', array( $this, 'setup_fields' ) );
		$this->viewpath = __DIR__ . '/views/view.php';
	}

	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function setup_fields() {
		$this->fields = array(
			'heading'            => $this->get_slug() . '_heading',
			'heading_font_size'  => $this->get_slug() . '_heading_font_size',
			'heading_alignment'  => $this->get_slug() . '_heading_alignment',
			'desc'               => $this->get_slug() . '_desc',
			'desc_alignment'     => $this->get_slug() . '_desc_alignment',
			'layout'             => $this->get_slug() . '_layout',
			'grid_type'          => $this->get_slug() . '_grid_type',
			'display_count'      => $this->get_slug() . '_display_count',
			'display_rating'     => $this->get_slug() . '_display_rating',
			'border_style'       => $this->get_slug() . '_border_style',
			'border_width'       => $this->get_slug() . '_border_width',
			'border_color'       => $this->get_slug() . '_border_color',
			'component_bg_color' => $this->get_slug() . '_component_bg',
		);
	}

	public function prepare_out_put_data() {

		parent::prepare_out_put_data();
		if ( $this->data->layout == 'grid' ) {
			$this->data->grid_type = $this->data->grid_type;
		} else {
			$this->data->grid_type = $this->data->layout;
		}
	}

	public function woocommerce_add_to_cart( $cart_item_key, $product_id ) {
		if ( $product_id > 0 ) {
			global $product;
			if ( $product instanceof WC_product ) {

			} else {
				$product = wc_get_product( $product_id );
			}

			$xlwcty_upsell_product = WC()->session->get( 'xlwcty_upsell_product' );
			if ( ! is_array( $xlwcty_upsell_product ) ) {
				$xlwcty_upsell_product = array();
			}
			if ( ! is_array( $xlwcty_upsell_product ) ) {
				$xlwcty_upsell_product = array();
			}
			if ( version_compare( $this->wc_version(), '3.0', '<' ) ) {
				$upsell = $product->get_upsells();
			} else {
				$upsell = $product->get_upsell_ids();
			}
			if ( ! empty( $upsell ) && is_array( $upsell ) > 0 ) {
				$xlwcty_upsell_product = array_merge( $xlwcty_upsell_product, $upsell );
				$xlwcty_upsell_product = array_unique( $xlwcty_upsell_product );
				WC()->session->set( 'xlwcty_upsell_product', $xlwcty_upsell_product );
			}
		}
	}

	public function setup_style( $slug ) {
		if ( $this->is_enable() ) {
			if ( $this->data->layout === 'grid_native' && $this->data->display_rating == 'no' ) {
				$style['.xlwcty_product.upsell_product .star-rating']['display'] = 'none';
			}
			if ( $this->data->heading_font_size != '' ) {
				$style['.xlwcty_product.upsell_product .xlwcty_title']['font-size']   = $this->data->heading_font_size . 'px';
				$style['.xlwcty_product.upsell_product .xlwcty_title']['line-height'] = ( $this->data->heading_font_size + 4 ) . 'px';
			}
			if ( $this->data->heading_alignment != '' ) {
				$style['.xlwcty_product.upsell_product .xlwcty_title']['text-align'] = $this->data->heading_alignment;
			}
			if ( $this->data->border_style != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_product.upsell_product']['border-style'] = $this->data->border_style;
			}
			if ( (int) $this->data->border_width >= 0 ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_product.upsell_product']['border-width'] = (int) $this->data->border_width . 'px';
			}
			if ( $this->data->border_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_product.upsell_product']['border-color'] = $this->data->border_color;
			}
			if ( $this->data->component_bg_color != '' ) {
				$style['.xlwcty_wrap .xlwcty_Box.xlwcty_product.upsell_product']['background-color'] = $this->data->component_bg_color;
			}
			parent::push_css( $slug, $style );
		}
	}

	public function get_view_data( $key = 'order' ) {
		$this->upsell_product = $this->get_upsell_product();

		return parent::get_view_data();
	}

	public function get_upsell_product() {
		$xlwcty_upsell_product = WC()->session->get( 'xlwcty_upsell_product' );

		if ( ! XLWCTY_Core()->public->is_preview && is_array( $xlwcty_upsell_product ) && count( $xlwcty_upsell_product ) > 0 ) {
			return $xlwcty_upsell_product;
		}

		//handling for the case where we do not have any data set in the session
		$order                 = XLWCTY_Core()->data->get_order();
		$xlwcty_upsell_product = array();
		if ( $order instanceof WC_Order ) {

			$items = $order->get_items();
			foreach ( $items as $item ) {

				if ( isset( $item['variation_id'] ) && $item['variation_id'] !== '0' ) {
					$product = wc_get_product( $item['product_id'] );
				} else {
					$product = XLWCTY_Compatibility::get_product_from_item( $order, $item );
				}
				if ( $product === false ) {
					continue;
				}
				if ( version_compare( $this->wc_version(), '3.0', '<' ) ) {
					$upsell = $product->get_upsells();
				} else {
					$upsell = $product->get_upsell_ids();
				}
				if ( ! empty( $upsell ) && is_array( $upsell ) > 0 ) {
					$xlwcty_upsell_product = array_merge( $xlwcty_upsell_product, $upsell );
					$xlwcty_upsell_product = array_unique( $xlwcty_upsell_product );
				}
			}
		}

		return $xlwcty_upsell_product;
	}

}

return XLWCTY_Upsell_Products::get_instance();
