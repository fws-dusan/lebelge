<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * It will show list of Active and deactive email templates on Emails Templates tab.
 *
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @category Classes
 * @since    5.0
 */

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * Show Email templates list on Email Templates tab.
 *
 * @since 2.0
 */
class Wcap_ATC_Templates_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 2.0
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 2.0
	 */
	public $base_url;

	/**
	 * Total number of templates
	 *
	 * @var int
	 * @since 2.0
	 */
	public $total_count;

	/**
	 * It will add the bulk action and other variable needed for the class.
	 *
	 * @since 2.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {

		// Set parent defaults.
		parent::__construct(
			array(
				'singular' => __( 'template_id', 'woocommerce-ac' ), // singular name of the listed records.
				'plural'   => __( 'template_ids', 'woocommerce-ac' ), // plural name of the listed records.
				'ajax'     => false,                        // Does this table support ajax?
			)
		);
		$this->wcap_get_templates_count();
		$this->process_bulk_action();
		$this->base_url = admin_url( 'admin.php?page=woocommerce_ac_page&action=emailsettings&section=wcap_atc_settings' );
	}
	/**
	 * It will prepare the list of the Email Templates, columns, pagination, sortable column and other data.
	 *
	 * @since 2.0
	 */
	public function wcap_templates_prepare_items() {

		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns.
		$sortable              = $this->wcap_templates_get_sortable_columns();
		$data                  = $this->wcap_templates_data();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$total_items           = $this->total_count;
		$this->items           = $data;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,                      // WE have to calculate the total number of items.
				'per_page'    => $this->per_page,                       // WE have to determine how many items to show on a page.
				'total_pages' => ceil( $total_items / $this->per_page ),   // WE have to calculate the total number of pages.
			)
		);
	}
	/**
	 * It will add the columns for Email Templates Tab.
	 *
	 * @return array $columns All columns name.
	 * @since  2.0
	 */
	public function get_columns() {

		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'sr'             => __( 'Sr', 'woocommerce-ac' ),
			'template_name'  => __( 'Name Of Template', 'woocommerce-ac' ),
			'rules'          => __( 'Rules', 'woocommerce-ac' ),
			'email_captured' => __( 'Email Captured', 'woocommerce-ac' ),
			'viewed'         => __( 'Viewed', 'woocommerce-ac' ),
			'no_thanks'      => __( 'No Thanks', 'woocommerce-ac' ),
			'activate'       => __( 'Enable/Disable', 'woocommerce-ac' ),
		);
		return apply_filters( 'wcap_atc_templates_columns', $columns );
	}

	/**
	 * It is used to add the check box for the items
	 *
	 * @param object $item - Display Data.
	 * @return object
	 * @since 2.0
	 */
	public function column_cb( $item ) {

		$template_id = '';
		if ( isset( $item->id ) && '' !== $item->id ) {
			$template_id = $item->id;
		}
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'template_id',
			$template_id
		);
	}
	/**
	 * We can mention on which column we need the sorting. Here we are sorting Name of the Template & Sent After Set Time.
	 *
	 * @return array $columns Name of the column
	 * @since  2.0
	 */
	public function wcap_templates_get_sortable_columns() {
		$columns = array(
			'template_name' => array( 'template_name', false ),
		);
		return apply_filters( 'wcap_atc_templates_sortable_columns', $columns );
	}

	/**
	 * Render the Name of the Template. This function used for individual delete of row, It is for hover effect delete.
	 *
	 * @access public
	 * @since 2.4.9
	 * @param array $template_row_info - Contains all the data of the template row.
	 * @return string Data shown in the column.
	 */
	public function column_template_name( $template_row_info ) {
		$row_actions = array();
		$value       = '';
		$template_id = 0;
		if ( isset( $template_row_info->template_name ) ) {
			$template_id         = $template_row_info->id;
			$row_actions['edit'] = '<a href="' . wp_nonce_url(
				add_query_arg(
					array(
						'action'       => 'emailsettings',
						'wcap_section' => 'wcap_atc_settings',
						'mode'         => 'edittemplate',
						'id'           => $template_row_info->id,
					),
					$this->base_url
				),
				'abandoned_order_nonce'
			) . '">' . __( 'Edit', 'woocommerce-ac' ) . '</a>';

			$row_actions['delete'] = '<a href="' . wp_nonce_url(
				add_query_arg(
					array(
						'action'       => 'emailsettings',
						'wcap_section' => 'wcap_atc_settings',
						'mode'         => 'deleteatctemplate',
						'id'           => $template_row_info->id,
					),
					$this->base_url
				),
				'abandoned_order_nonce'
			) . '">' . __( 'Delete', 'woocommerce-ac' ) . '</a>';
			$email                 = $template_row_info->template_name;
			$value                 = $email . $this->row_actions( $row_actions );
		}
		return apply_filters( 'wcap_atc_template_single_column', $value, $template_id, 'atc' );
	}

	/**
	 * This function is used for email templates count.
	 *
	 * @globals mixed $wpdb
	 * @since   2.0
	 */
	public function wcap_get_templates_count() {
		global $wpdb;
		$this->total_count = $wpdb->get_var( 'SELECT COUNT(`id`) FROM `' . WCAP_ATC_RULES_TABLE . '`' ); // phpcs:ignore
	}
	/**
	 * It will manage for the Email Template list.
	 *
	 * @globals mixed $wpdb
	 * @return array $return_templates_display Key and value of all the columns
	 * @since 2.0
	 */
	public function wcap_templates_data() {
		global $wpdb;
		$return_templates_data = array();
		$per_page              = $this->per_page;
		$results               = array();
		$wcap_get_decimal      = wc_get_price_decimals();

		$results = $wpdb->get_results( // phpcs:ignore
			'SELECT id, is_active, rules, name FROM `' . WCAP_ATC_RULES_TABLE . '` ORDER BY id asc' // phpcs:ignore
		);

		$i = 0;

		$rule_data = array(
			'custom_pages' => __( 'Custom Page', 'woocommerce-ac' ),
			'product_cat'  => __( 'Product Category', 'woocommerce-ac' ),
			'products'     => __( 'Products', 'woocommerce-ac' ),
		);
		foreach ( $results as $key => $value ) {
			$return_templates_data[ $i ] = new stdClass();

			$id        = $value->id;
			$is_active = $value->is_active;

			// Viewed.
			$viewed = $wpdb->get_var( // phpcs:ignore
				$wpdb->prepare(
					'SELECT count(id) FROM ' . WCAP_AC_STATS . ' where template_type = %s AND event = %s AND template_id = %d', // phpcs:ignore
					'atc',
					'0',
					absint( $id )
				)
			);

			// No Thanks.
			$no_thanks = $wpdb->get_var( // phpcs:ignore
				$wpdb->prepare(
					'SELECT count(id) FROM ' . WCAP_AC_STATS . ' where template_type = %s AND event = %s AND template_id = %d', // phpcs:ignore
					'atc',
					'2',
					absint( $id )
				)
			);

			// Email Captured.
			$email_captured = $wpdb->get_var( // phpcs:ignore
				$wpdb->prepare(
					'SELECT count(id) FROM ' . WCAP_AC_STATS . ' where template_type = %s AND event = %s AND template_id = %d', // phpcs:ignore
					'atc',
					'1',
					absint( $id )
				)
			);

			$rules        = json_decode( $value->rules );
			$rule_display = '';
			if ( is_array( $rules ) && count( $rules ) > 0 ) {
				foreach ( $rules as $rule_list ) {
					if ( '' !== $rule_list->rule_type ) {
						$rule_type     = $rule_list->rule_type;
						$rule_display .= $rule_data[ $rule_type ] . ', ';
					}
				}
				$rule_display = rtrim( $rule_display, ', ' );
			}
			$return_templates_data[ $i ]->sr             = $i + 1;
			$return_templates_data[ $i ]->id             = $id;
			$return_templates_data[ $i ]->template_name  = $value->name;
			$return_templates_data[ $i ]->rules          = $rule_display;
			$return_templates_data[ $i ]->is_active      = $is_active;
			$return_templates_data[ $i ]->viewed         = $viewed;
			$return_templates_data[ $i ]->no_thanks      = $no_thanks;
			$return_templates_data[ $i ]->email_captured = $email_captured;

			$i++;
		}

		// Sort for order date.
		if ( isset( $_GET['orderby'] ) && 'template_name' === $_GET['orderby'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_GET['order'] ) && 'asc' === $_GET['order'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				usort( $return_templates_data, array( __CLASS__, 'wcap_class_template_name_asc' ) );
			} else {
				usort( $return_templates_data, array( __CLASS__, 'wcap_class_template_name_dsc' ) );
			}
		}

		if ( isset( $_GET['paged'] ) && $_GET['paged'] > 1 ) { // phpcs:ignore WordPress.Security.NonceVerification
			$page_number = absint( sanitize_text_field( wp_unslash( $_GET['paged'] ) ) ) - 1; // phpcs:ignore WordPress.Security.NonceVerification
			$k           = $per_page * $page_number;
		} else {
			$k = 0;
		}

		$return_templates_display = array();
		for ( $j = $k; $j < ( $k + $per_page ); $j++ ) {
			if ( isset( $return_templates_data[ $j ] ) ) {
				$return_templates_display[ $j ] = $return_templates_data[ $j ];
			} else {
				break;
			}
		}
		return apply_filters( 'wcap_atc_templates_table_data', $return_templates_display );
	}
	/**
	 * It will sort the alphabetically ascending on Name Of Template column.
	 *
	 * @param  array | object $value1 All data of the list.
	 * @param  array | object $value2 All data of the list.
	 * @return sorted array
	 * @since  3.4
	 */
	public function wcap_class_template_name_asc( $value1, $value2 ) {
		return strcasecmp( $value1->template_name, $value2->template_name );
	}

	/**
	 * It will sort the alphabetically descending on Name Of Template column.
	 *
	 * @param  array | object $value1 All data of the list.
	 * @param  array | object $value2 All data of the list.
	 * @return sorted array
	 * @since  3.4
	 */
	public function wcap_class_template_name_dsc( $value1, $value2 ) {
		return strcasecmp( $value2->template_name, $value1->template_name );
	}
	/**
	 * It will display the data for the ATC Templates.
	 *
	 * @param  array | object $wcap_atc_templates All data of the list.
	 * @param  stirng         $column_name Name of the column.
	 * @return string $value Data of the column.
	 * @since  2.0
	 */
	public function column_default( $wcap_atc_templates, $column_name ) {
		$value = '';
		switch ( $column_name ) {

			case 'activate':
				if ( isset( $wcap_atc_templates->is_active ) ) {
					$id        = $wcap_atc_templates->id;
					$is_active = $wcap_atc_templates->is_active;
					$active    = '1' === $is_active ? 'on' : 'off';

					$active_text = __( $active, 'woocommerce-ac' ); // phpcs:ignore
					$value       = '<button type="button" class="wcap-switch wcap-atc-template-status wcap-enable-atc-modal" '
					. 'wcap-template-id="' . $id . '" '
					. 'wcap-atc-switch-modal-enable="' . ( $active ) . '" onClick="wcap_atc_template_status( this )">'
					. $active_text . '</button>';
				}
				break;
			default:
				$value = isset( $wcap_atc_templates->$column_name ) ? $wcap_atc_templates->$column_name : '';
				break;
		}
		return apply_filters( 'wcap_atc_template_column_default', $value, $wcap_atc_templates, $column_name );
	}
	/**
	 * It will add the bulk action for delete the email template list.
	 *
	 * @return array $wcap_abandoned_bulk_actions bulk action
	 * @since  2.4.9
	 */
	public function get_bulk_actions() {
		return array(
			'wcap_atc_delete_template' => __( 'Delete', 'woocommerce-ac' ),
		);
	}
}
