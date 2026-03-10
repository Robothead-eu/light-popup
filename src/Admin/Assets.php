<?php

namespace Robothead\LightPopup\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	public function register(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'admin_init', [ $this, 'register_editor_styles' ] );
	}

	public function enqueue( string $hook ): void {
		$screen = get_current_screen();

		if ( ! $screen || 'light_popup' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			'light-popup-admin',
			LIGHT_POPUP_URL . 'assets/css/admin.css',
			[],
			LIGHT_POPUP_VERSION
		);

		if ( in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			wp_enqueue_script(
				'light-popup-admin',
				LIGHT_POPUP_URL . 'assets/js/admin.js',
				[],
				LIGHT_POPUP_VERSION,
				true
			);
		}
	}

	/**
	 * Injects editor styles into the block editor iframe so the canvas
	 * gets the grey preview background.
	 */
	public function register_editor_styles(): void {
		$screen = get_current_screen();
		if ( ! $screen || 'light_popup' !== $screen->post_type ) {
			return;
		}
		add_editor_style( LIGHT_POPUP_URL . 'assets/css/editor.css' );
	}
}
