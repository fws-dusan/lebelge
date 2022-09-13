<?php
/**
 * WFCM Abstract Update Wrapper
 *
 * This is an abstract class to ensure all update routines follow the same
 * format and to give provide the basic functions needed.
 *
 * @package wfcm
 */

/**
 * Abstract class to provide a base for update routines in the plugin.
 *
 * @since 1.4.0
 */
abstract class WFCM_AbstractUpdateWrapper implements WFCM_UpdateWrapperInterface {

	/**
	 * The key/ID of this update method.
	 *
	 * @var string
	 */
	public $key = 'update_routine';

	/**
	 * A version string representing the old/previous plugin version.
	 *
	 * @var string
	 */
	public $old_version;

	/**
	 * A version string representing the new/current plugin version.
	 *
	 * @var string
	 */
	public $new_version;

	/**
	 * The options key that holds the current plugin version.
	 *
	 * @var string
	 */
	public $version_key;

	/**
	 * A min version that this update may be applied to.
	 *
	 * This is the min version that the updates should be applied FROM. Use as
	 * an indicator of when this update routine was added.
	 *
	 * @var string
	 */
	public $min_version = '0.0.0';

	/**
	 * A max version that this update may be applied to.
	 *
	 * This is the max version that the updates should be applied TO and is an
	 * early indicator of when you expect this update routine will no longer be
	 * valid and to consider removing after that.
	 *
	 * @var string
	 */
	public $max_version = '999';

	/**
	 * Indicator if the run has finished.
	 *
	 * When the work is completed and verified this should be flipped to true.
	 *
	 * @var bool
	 */
	protected $finished = false;

	public function __construct( $old_version, $new_version ) {
		$this->old_version = $old_version;
		$this->new_version = $new_version;
		if ( $this->check() ) {
			$this->register();
		}
	}

	/**
	 * A boolean function to check versions against the various different
	 * conditions for including this classes update method in the pool to run.
	 *
	 * NOTE: All parameters are optional.
	 *
	 * @method check
	 * @since  1.4.0
	 * @param  string $compare     A compare operator. Default is less than.
	 * @param  string $old_version Last knows version string. If not passed this var in the method is cast to the class prop with same name.
	 * @param  string $new_version Current/new version string. If not passed this var in the method is cast to the class prop with same name.
	 * @return bool
	 */
	public function check( $compare = '<', $old_version = '', $new_version = '' ) {
		// allow passing in custom old/new version strings, otherwise use props.
		$old_version = ( '' !== $old_version ) ? $old_version : $this->old_version;
		$new_version = ( '' !== $new_version ) ? $new_version : $this->new_version;
		$passed      = false;

		/*
		 * If the old version is 0.0.0 then state it as current min to satisfy
		 * checks.
		 */
		$old_version = ( '0.0.0' === $old_version ) ? $this->min_version : $old_version;

		/*
		 * Compare version conditions to determine if this check is true/false.
		 */
		if (
			\version_compare( $old_version, $new_version, $compare ) &&
			$old_version >= $this->min_version &&
			$new_version <= $this->max_version
		) {
			// all conditions passed so the return would be true.
			$passed = true;
		}
		return $passed;
	}

	/**
	 * Adds this class to the array that gets run on the update action/filter.
	 *
	 * @method register
	 * @since  1.4.0
	 */
	public function register() {
		add_filter(
			'wfcm_register_update_routine',
			function( $update_routines ) {
				$update_routines[ $this->key ] = $this;
				return $update_routines;
			}
		);
	}

	/**
	 * This is the main body of the update class and is the method that does
	 * the actual work to handle this update routine.
	 *
	 * @method run
	 * @since  1.4.0
	 */
	abstract public function run();

	/**
	 * This is the completion indicator, it should return a bool of success or
	 * failure.
	 *
	 * @method finish
	 * @since  1.4.0
	 * @return bool
	 */
	public function finish() {
		return $this->finished;
	}
}
