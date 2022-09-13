<?php
/**
 * Sensor: File Changes Detection
 *
 * Sensor file for detecting file changes.
 *
 * @since 3.2
 * @package Wsal
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class: File Change Detection Sensor
 *
 * @package Wsal
 */

if ( ! class_exists( 'WSAL_Sensors_FileChanges' ) ) {
	class WSAL_Sensors_FileChanges extends WSAL_AbstractSensor {

		/**
		 * WP Root Path.
		 *
		 * @var string
		 */
		private $root_path = '';

		/**
		 * Method: Constructor.
		 *
		 * @param WpSecurityAuditLog $plugin - Instance of WpSecurityAuditLog.
		 */
		public function __construct( WpSecurityAuditLog $plugin ) {
			// Call to parent constructor.
			parent::__construct( $plugin );

			// Set root path.
			$this->root_path = trailingslashit( ABSPATH );
		}

		/**
		 * Listening to events using WP hooks.
		 */
		public function HookEvents() {
			add_action( 'wfcm_wsal_file_modified', array( $this, 'detect_wfcm_file_modified' ) );
			add_action( 'wfcm_wsal_file_added', array( $this, 'detect_wfcm_file_added' ) );
			add_action( 'wfcm_wsal_file_deleted', array( $this, 'detect_wfcm_file_deleted' ) );
			add_action( 'wfcm_wsal_file_size_exceeded', array( $this, 'detect_wfcm_file_size_exceeded' ), 10, 1 );
			add_action( 'wfcm_wsal_file_scan_started', array( $this, 'detect_wfcm_file_scan_started' ) );
			add_action( 'wfcm_wsal_file_scan_stopped', array( $this, 'detect_wfcm_file_scan_stopped' ) );
			add_action( 'wfcm_wsal_file_limit_exceeded', array( $this, 'detect_wfcm_file_limit_exceeded' ), 10, 1 );
		}

		public function detect_wfcm_file_modified() {
			if ( ! $this->was_triggered_recently( 6028 ) ) {
				$this->plugin->alerts->Trigger(
					6028
				);
			}
		}

		public function detect_wfcm_file_added() {
			if ( ! $this->was_triggered_recently( 6029 ) ) {
				$this->plugin->alerts->Trigger(
					6029
				);
			}
		}

		public function detect_wfcm_file_deleted() {
			if ( ! $this->was_triggered_recently( 6030 ) ) {
				$this->plugin->alerts->Trigger(
					6030
				);
			}
		}

		public function detect_wfcm_file_size_exceeded( $files_over_limit ) {
			foreach ( $files_over_limit as $file ) {
				$variables = array(
					'File'         => basename( $file ),
					'FileLocation' => $file,
				);
				$this->plugin->alerts->Trigger(
					6031,
					$variables
				);
			}
		}

		public function detect_wfcm_file_scan_started() {
			$variables = array(
				'ScanStatus' => __( 'started', 'website-file-changes-montior' ),
				'EventType'  => __( 'started', 'website-file-changes-montior' ),
			);
			$this->plugin->alerts->Trigger(
				6033,
				$variables
			);
		}

		public function detect_wfcm_file_scan_stopped() {
			$variables = array(
				'ScanStatus' => __( 'stopped', 'website-file-changes-montior' ),
				'EventType'  => __( 'stopped', 'website-file-changes-montior' ),
			);
			$this->plugin->alerts->Trigger(
				6033,
				$variables
			);
		}

		public function detect_wfcm_file_limit_exceeded( $path_to_scan ) {
			$variables = array(
				'ScanStatus' => __( 'stopped', 'website-file-changes-montior' ),
			);
			$this->plugin->alerts->Trigger(
				6032,
				$variables
			);
		}

		/**
		 * Check if the alert was triggered recently.
		 *
		 * Checks last 5 events if they occured less than 20 seconds ago.
		 *
		 * @param integer|array $alert_id - Alert code.
		 * @return boolean
		 */
		private function was_triggered_recently( $alert_id ) {
			// if we have already checked this don't check again.
			if ( isset( $this->cached_alert_checks ) && array_key_exists( $alert_id, $this->cached_alert_checks ) && $this->cached_alert_checks[$alert_id] ) {
				return true;
			}
			$query = new WSAL_Models_OccurrenceQuery();
			$query->addOrderBy( 'created_on', true );
			$query->setLimit( 100 );
			$last_occurences  = $query->getAdapter()->Execute( $query );
			$known_to_trigger = false;
			foreach ( $last_occurences as $last_occurence ) {
				if ( $known_to_trigger ) {
					break;
				}
				if ( ! empty( $last_occurence ) && ( $last_occurence->created_on + 20 ) > time() ) {
					if ( ! is_array( $alert_id ) && $last_occurence->alert_id === $alert_id ) {
						$known_to_trigger = true;
					} elseif ( is_array( $alert_id ) && in_array( $last_occurence[0]->alert_id, $alert_id, true ) ) {
						$known_to_trigger = true;
					}
				}
			}
			// once we know the answer to this don't check again to avoid queries.
			$this->cached_alert_checks[ $alert_id ] = $known_to_trigger;
			return $known_to_trigger;
		}
	}
}
