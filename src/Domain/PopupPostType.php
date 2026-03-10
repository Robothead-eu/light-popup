<?php

namespace Robothead\LightPopup\Domain;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PopupPostType {

	public function register(): void {
		add_action( 'init', [ $this, 'register_post_type' ] );
	}

	public function register_post_type(): void {
		$labels = [
			'name'               => __( 'Popups', 'light-popup' ),
			'singular_name'      => __( 'Popup', 'light-popup' ),
			'add_new'            => __( 'Add New', 'light-popup' ),
			'add_new_item'       => __( 'Add New Popup', 'light-popup' ),
			'edit_item'          => __( 'Edit Popup', 'light-popup' ),
			'new_item'           => __( 'New Popup', 'light-popup' ),
			'view_item'          => __( 'View Popup', 'light-popup' ),
			'search_items'       => __( 'Search Popups', 'light-popup' ),
			'not_found'          => __( 'No popups found.', 'light-popup' ),
			'not_found_in_trash' => __( 'No popups found in Trash.', 'light-popup' ),
			'all_items'          => __( 'All Popups', 'light-popup' ),
			'menu_name'          => __( 'Popups', 'light-popup' ),
		];

		register_post_type(
			'light_popup',
			[
				'labels'              => $labels,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_rest'        => true,
				'supports'            => [ 'title', 'editor' ],
				'capability_type'     => 'post',
				'capabilities'        => [
					'edit_post'          => 'manage_options',
					'read_post'          => 'manage_options',
					'delete_post'        => 'manage_options',
					'edit_posts'         => 'manage_options',
					'edit_others_posts'  => 'manage_options',
					'publish_posts'      => 'manage_options',
					'read_private_posts' => 'manage_options',
					'delete_posts'       => 'manage_options',
					'delete_others_posts' => 'manage_options',
				],
				'map_meta_cap'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'has_archive'         => false,
				'exclude_from_search' => true,
			]
		);
	}
}
