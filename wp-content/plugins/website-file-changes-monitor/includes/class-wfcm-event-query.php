<?php
/**
 * WFCM Events Query.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Events Query Class.
 */
class WFCM_Event_Query {

	/**
	 * Query variables.
	 *
	 * @var array
	 */
	protected $query_vars = array();

	/**
	 * Constructor.
	 *
	 * @param array $args - Array of query arguments.
	 */
	public function __construct( $args = array() ) {
		$this->query_vars = wp_parse_args( $args, $this->get_default_query_vars() );
		add_filter( 'posts_where', [$this, 'alter_posts_where_clause'], 10, 2 );
	}

	/**
	 * Changes the WHERE SQL clause to support search for posts with starting with a specific string.
	 *
	 * @param string $where
	 * @param WP_Query$query
	 *
	 * @return string
	 * @since 1.7.1
	 */
	public function alter_posts_where_clause( $where, $query ) {
		global $wpdb;
		$starts_with = esc_sql( $query->get( 'starts_with' ) );
		if ( $starts_with ) {
			$where .= " AND $wpdb->posts.post_title LIKE '$starts_with%'";
		}
		return $where;
	}

	/**
	 * Returns query arguments.
	 *
	 * @return array
	 */
	private function get_args() {
		return $this->query_vars;
	}

	/**
	 * Returns default arguments for quering events from WordPress.
	 *
	 * @return array
	 */
	private function get_default_query_vars() {
		return array(
			'post_status'    => array( 'draft', 'pending', 'private', 'publish' ),
			'post_type'      => 'wfcm_file_event',

			'posts_per_page' => -1,
			'paginate'       => false,

			'order'          => 'DESC',
			'orderby'        => 'date',

			'return'         => 'objects',

			'event_type'     => '', // Event type: added, modified, or deleted.
			'status'         => '', // Event status.
		);
	}

	/**
	 * Get events from WordPress.
	 *
	 * @return array|object
	 * @throws Exception
	 */
	public function get_events() {
		$args   = $this->get_args();
		return WFCM_Database_DB_Data_Store::get_file_events( $args );
	}
}
