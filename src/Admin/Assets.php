<?php

namespace Robothead\LightPopup\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	public function register(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	public function enqueue( string $hook ): void {
		$screen = get_current_screen();

		if ( ! $screen || 'light_popup' !== $screen->post_type ) {
			return;
		}

		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		wp_enqueue_style(
			'light-popup-admin',
			LIGHT_POPUP_URL . 'assets/css/admin.css',
			[],
			LIGHT_POPUP_VERSION
		);

		wp_enqueue_script(
			'light-popup-admin',
			LIGHT_POPUP_URL . 'assets/js/admin.js',
			[],
			LIGHT_POPUP_VERSION,
			true
		);
	}
}
