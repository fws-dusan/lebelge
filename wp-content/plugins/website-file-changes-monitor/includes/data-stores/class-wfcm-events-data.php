<?php

/**
 * Special object to handle our data.
 */
class WFCM_Database_Events_Data {

	public  $events;
	public  $total;
	public  $max_num_pages;

	/**
	 * Setters.
	 */

	public function set_events( $events ) {
		$this->events = $events;
	}

	public function set_total( $total ) {
		$this->total = $total;
	}

	public function set_max_num_pages( $max_num_pages ) {
		$this->max_num_pages = $max_num_pages;
	}

	/**
	 * Getters.
	 */
	public function get_events() {
		return $this->events;
	}

	public function get_total() {
		return $this->total;
	}
	
	public function get_max_num_pages() {
		return $this->max_num_pages;
	}
}