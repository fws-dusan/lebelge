<?php
/**
 * Handle the custom caching for the Page Insights report.
 *
 * @package monsterinsights-page-insights
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MonsterInsights_Page_Insights_Cache
 */
class MonsterInsights_Page_Insights_Cache {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var MonsterInsights_Page_Insights_Cache
	 */
	public static $instance;

	/**
	 * The db interface.
	 *
	 * @var wpdb
	 */
	private $db;

	/**
	 * The name of the table where the data is stored.
	 *
	 * @var string
	 */
	private $table;

	/**
	 * MonsterInsights_Page_Insights_Cache constructor.
	 */
	private function __construct() {

		global $wpdb;
		$this->db    = $wpdb;
		$this->table = $this->db->prefix . 'monsterinsights_pageinsights_cache';

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return MonsterInsights_Page_Insights_Cache
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof MonsterInsights_Page_Insights_Cache ) ) {
			self::$instance = new MonsterInsights_Page_Insights_Cache();
		}

		return self::$instance;
	}

	/**
	 * Save data to the cache.
	 *
	 * @param string $path The page path for which the data is stored.
	 * @param array  $data The actual data which will be stored.
	 * @param int    $expiration When should this data expire.
	 *
	 * @return int|bool False if the data was not inserted.
	 */
	public function set( $path, $data, $expiration = 0 ) {

		if ( 0 === $expiration ) {
			// By default, set the expiration for next day in the website's timezone.
			$expiration = strtotime( ' Tomorrow 1am ' ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		}

		if ( empty( $path ) || empty( $data ) ) {
			return false;
		}

		$data = wp_json_encode( $data );

		return $this->db->insert( $this->table, array(
			'path'   => $path,
			'value'  => $data,
			'expiry' => date( 'Y-m-d H:i:s', $expiration ),
		) );

	}

	/**
	 * Grab the data from the cache.
	 *
	 * @param string $path The path of the page for the data is loaded.
	 *
	 * @return array|bool The stored data or false if not valid/expired.
	 */
	public function get( $path ) {

		$query = $this->db->prepare( "SELECT `value`, `expiry` FROM  $this->table WHERE `path` = %s", $path );
		$data  = $this->db->get_row( $query );

		if ( ! empty( $data->expiry ) && ! empty( $data->value ) ) {
			if ( strtotime( $data->expiry ) < time() ) {
				// The content expired.
				// Empty the table so we can replace it with fresh data.
				$this->clear_cache();

				return false;
			}

			return json_decode( $data->value, true );
		}

		return false;

	}

	/**
	 * Empty the cache table.
	 */
	public function clear_cache() {

		update_option( 'monsterinsights_pageinsights_next_fetch', 0 );
		$this->db->query( "TRUNCATE table $this->table" );

	}

	/**
	 * Destroy the current instance.
	 */
	public static function destroy() {

		self::$instance = null;
	}

}
