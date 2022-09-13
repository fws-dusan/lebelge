<?php
/**
 * Class CustomPostType
 *
 * @package WPDesk\FS\ConditionalMethods
 */

namespace WPDesk\FS\ConditionalMethods;

use FSConditionalMethodsVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Can create custom post type.
 */
class CustomPostType implements Hookable {

	const POST_TYPE = 'fs_cm_ruleset';

	/**
	 * .
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * @return \WP_Post_Type|\WP_Error
	 *
	 * @internal
	 */
	public function register_post_type() {
		return register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name' => __( 'Conditional Methods', 'flexible-shipping-conditional-methods' ),
				),
				'public'              => false,
				'show_ui'             => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => false,
				'show_in_menu'        => false,
			)
		);
	}

}
