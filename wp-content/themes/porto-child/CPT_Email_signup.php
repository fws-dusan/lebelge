<?php {
	/**
	 *  CUSTOM POST TYPE emails
	 */

	function CPTInit()
	{
		$plural = 'Email signups';
		$singular = 'Email signup';
		$slug = str_replace(' ', '_', strtolower($singular));

		$labels = [
			'name' => __($plural, 'lebelge'),
			'singular_name' => __($singular, 'lebelge'),
			'add_new' => _x('Add New', 'arrows', 'lebelge'),
			'add_new_item' => __('Add New ' . $singular, 'lebelge'),
			'edit' => __('Edit', 'lebelge'),
			'edit_item' => __('Edit ' . $singular, 'lebelge'),
			'new_item' => __('New ' . $singular, 'lebelge'),
			'view' => __('View ' . $singular, 'lebelge'),
			'view_item' => __('View ' . $singular, 'lebelge'),
			'search_term' => __('Search ' . $plural, 'lebelge'),
			'parent' => __('Parent ' . $singular, 'lebelge'),
			'not_found' => __('No ' . $plural . ' found', 'lebelge'),
			'not_found_in_trash' => __('No ' . $plural . ' in Trash', 'lebelge'),
		];

		$args = [
			'labels' => $labels,
			'hierarchical' => false,
			'public' => true,
			'publicly_queryable' => false,
			'query_var' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'has_archive' => false,
			'show_in_rest' => false,
			'rewrite' => ['slug' => $slug, 'with_front' => false],
			'menu_icon' => 'dashicons-admin-post',
			'supports' => ['title', 'add_media', 'thumbnail', 'excerpt', 'author'],
		];

		register_post_type($slug, $args);
	}

	// Actions
	add_action('init', 'CPTInit');
}
