<?php
/**
 * Use a separate handler for ajax calls so they can be accessed from frontend also.
 *
 * @package monsterinsights-page-insights
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class MonsterInsights_Page_Insights_Ajax
 */
class MonsterInsights_Page_Insights_Ajax {

	/**
	 * MonsterInsights_Page_Insights_Ajax constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_monsterinsights_pageinsights_refresh_report', array( $this, 'refresh_reports_data' ) );
		add_action( 'wp_ajax_monsterinsights_pageinsights_check_background_progress', array( $this, 'check_background_progress' ) );

		add_action( 'wp_ajax_monsterinsights_pageinsights_meta_report', array( $this, 'get_reports_data' ) );
	}

	/**
	 * Refresh the reports data, similar to the core plugin.
	 */
	public function refresh_reports_data() {
		check_ajax_referer( 'mi-admin-nonce', 'security' );

		// Get variables.
		$start     = ! empty( $_REQUEST['start'] ) ? $_REQUEST['start'] : '';
		$end       = ! empty( $_REQUEST['end'] ) ? $_REQUEST['end'] : '';
		$name      = ! empty( $_REQUEST['report'] ) ? $_REQUEST['report'] : '';
		$isnetwork = ! empty( $_REQUEST['isnetwork'] ) ? $_REQUEST['isnetwork'] : '';
		$json      = ! empty( $_REQUEST['json'] ) ? $_REQUEST['json'] : false;

		if ( ! empty( $_REQUEST['isnetwork'] ) && $_REQUEST['isnetwork'] ) {
			define( 'WP_NETWORK_ADMIN', true );
		}

		// Only for Pro users, require a license key to be entered first so we can link to things.
		if ( monsterinsights_is_pro_version() ) {
			if ( ! MonsterInsights()->license->is_site_licensed() && ! MonsterInsights()->license->is_network_licensed() ) {
				wp_send_json_error( array( 'message' => __( 'You can\'t view MonsterInsights reports because you are not licensed.', 'monsterinsights-page-insights' ) ) );
			} else if ( MonsterInsights()->license->is_site_licensed() && ! MonsterInsights()->license->site_license_has_error() ) {
				// Good to go: site licensed.
			} else if ( MonsterInsights()->license->is_network_licensed() && ! MonsterInsights()->license->network_license_has_error() ) {
				// Good to go: network licensed.
			} else {
				wp_send_json_error( array( 'message' => __( 'You can\'t view MonsterInsights reports due to license key errors.', 'monsterinsights-page-insights' ) ) );
			}
		}

		// We do not have a current auth.
		$site_auth = MonsterInsights()->auth->get_viewname();
		$ms_auth   = is_multisite() && MonsterInsights()->auth->get_network_viewname();
		if ( ! $site_auth && ! $ms_auth ) {
			wp_send_json_error( array( 'message' => __( 'You must authenticate with MonsterInsights before you can view reports.', 'monsterinsights-page-insights' ) ) );
		}

		if ( empty( $name ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown report. Try refreshing and retrying. Contact support if this issue persists.', 'monsterinsights-page-insights' ) ) );
		}

		$report = new MonsterInsights_Report_Page_Insights();

		if ( empty( $report ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown report. Try refreshing and retrying. Contact support if this issue persists.', 'monsterinsights-page-insights' ) ) );
		}

		$args = array(
			'start' => $start,
			'end'   => $end,
		);
		if ( $isnetwork ) {
			$args['network'] = true;
		}

		$data = $report->get_data( $args );

		if ( $json ) {
			$data = $report->prepare_report_data( $data );

			if ( ! empty( $data['success'] ) && ! empty( $data['data'] ) ) {
				wp_send_json_success( $data['data'] );
			} else if ( isset( $data['success'] ) && false === $data['success'] && ! empty( $data['error'] ) ) {
				wp_send_json_error(
					array(
						'message' => $data['error'],
						'footer'  => isset( $data['data']['footer'] ) ? $data['data']['footer'] : '',
					)
				);
			}
		}

		if ( ! empty( $data['success'] ) ) {

			if ( ! empty( $data['more'] ) ) {
				$data  = '<p>';
				$data .= __( 'It looks like your site has a large number of pages, please wait while we process the data for you, this might take a couple minutes.', 'monsterinsights-page-insights' );
				$data .= '</p>';
				wp_send_json_success( array( 'more' => $data ) );

			} else {
				$data = $report->get_report_html( $data['data'] );
				wp_send_json_success( array( 'html' => $data ) );
			}
		} else {
			wp_send_json_error(
				array(
					'message' => $data['error'],
					'data'    => $data['data'],
				)
			);
		}
	}

	function check_background_progress() {
		check_ajax_referer( 'mi-admin-nonce', 'security' );

		$pulling_data = get_option( 'monsterinsights_pageinsights_pulling_data', false );
		$done         = true;

		if ( $pulling_data || time() - intval( $pulling_data ) < 10 * MINUTE_IN_SECONDS ) {
			$done = false;
		}

		wp_send_json_success(
			array(
				'done' => $done,
			)
		);

	}

	public function get_reports_data() {
		check_ajax_referer( 'mi-admin-nonce', 'security' );

		// Get variables.
		$start     = ! empty( $_REQUEST['start'] )     ? $_REQUEST['start'] : '';
		$end       = ! empty( $_REQUEST['end'] )       ? $_REQUEST['end'] : '';
		$name      = ! empty( $_REQUEST['report'] )    ? $_REQUEST['report'] : '';
		$isnetwork = ! empty( $_REQUEST['isnetwork'] ) ? $_REQUEST['isnetwork'] : '';

		if ( ! empty( $_REQUEST['isnetwork'] ) && $_REQUEST['isnetwork'] ) {
			define( 'WP_NETWORK_ADMIN', true );
		}

		// Only for Pro users, require a license key to be entered first so we can link to things.
		if ( monsterinsights_is_pro_version() ) {
			if ( ! MonsterInsights()->license->is_site_licensed() && ! MonsterInsights()->license->is_network_licensed() ) {
				wp_send_json_error( array( 'message' => __( 'You can\'t view MonsterInsights reports because you are not licensed.', 'monsterinsights-page-insights' ) ) );
			} else if ( MonsterInsights()->license->is_site_licensed() && ! MonsterInsights()->license->site_license_has_error() ) {
				// Good to go: site licensed.
			} else if ( MonsterInsights()->license->is_network_licensed() && ! MonsterInsights()->license->network_license_has_error() ) {
				// Good to go: network licensed.
			} else {
				wp_send_json_error( array( 'message' => __( 'You can\'t view MonsterInsights reports due to license key errors.', 'monsterinsights-page-insights' ) ) );
			}
		}

		// We do not have a current auth.
		$site_auth = MonsterInsights()->auth->get_viewname();
		$ms_auth   = is_multisite() && MonsterInsights()->auth->get_network_viewname();
		if ( ! $site_auth && ! $ms_auth ) {
			wp_send_json_error( array( 'message' => __( 'You must authenticate with MonsterInsights before you can view reports.', 'monsterinsights-page-insights' ) ) );
		}

		if ( empty( $name ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown report. Try refreshing and retrying. Contact support if this issue persists.', 'monsterinsights-page-insights' ) ) );
		}

		$report = new MonsterInsights_Report_Page_Insights();

		if ( empty( $report ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown report. Try refreshing and retrying. Contact support if this issue persists.', 'monsterinsights-page-insights' ) ) );
		}

		$args = array(
			'start' => $start,
			'end'   => $end,
		);
		if ( $isnetwork ) {
			$args['network'] = true;
		}

		$data = $report->get_data( $args );

		$report_data = $report->prepare_report_raw_data( $data['data'] );

		if ( ! empty( $data['success'] ) && ! empty( $report_data ) ) {
			wp_send_json_success( $report_data );
		} else if ( isset( $data['success'] ) && false === $data['success'] && ! empty( $data['error'] ) ) {
			wp_send_json_error(
				array(
					'message' => $data['error'],
					'footer'  => isset( $data['data']['footer'] ) ? $data['data']['footer'] : '',
				)
			);
		}
	}
}
