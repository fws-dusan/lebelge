<?php

/**
 * Background process class handling batch event deletion.
 */
class WFCM_Background_Event_Deletion extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'wfcm_delete_events';

	/**
	 * @param int[] $item Queue item to iterate over. Represents a list event (post) IDs.
	 *
	 * @return bool
	 */
	protected function task( $item ) {
		if ( is_array( $item ) && ! empty( $item ) ) {
			foreach ( $item as $event_id ) {
				WFCM_REST_API::processEventDeletion( intval( $event_id ) );
			}
		}

		return false;
	}
}
