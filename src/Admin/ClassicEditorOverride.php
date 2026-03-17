<?php

namespace Robothead\LightPopup\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Robothead\LightPopup\Domain\Options;

/**
 * Handles Classic Editor compatibility.
 *
 * When the "Force block editor" option is enabled, this class
 * ensures the block editor is used for light_popup post type
 * even when the Classic Editor plugin is active.
 */
class ClassicEditorOverride {

	public function register(): void {
		// Only act if Classic Editor plugin is active.
		if ( ! $this->is_classic_editor_active() ) {
			return;
		}

		// Only act if force block editor is enabled.
		if ( ! Options::should_force_block_editor() ) {
			return;
		}

		// Force block editor for light_popup.
		add_filter( 'use_block_editor_for_post_type', [ $this, 'force_block_editor' ], 100, 2 );
		add_filter( 'use_block_editor_for_post', [ $this, 'force_block_editor_for_post' ], 100, 2 );
	}

	/**
	 * Check if Classic Editor plugin is active.
	 *
	 * @return bool
	 */
	private function is_classic_editor_active(): bool {
		return class_exists( 'Classic_Editor' ) || function_exists( 'classic_editor_init_actions' );
	}

	/**
	 * Force block editor for light_popup post type.
	 *
	 * @param bool   $use_block_editor Whether to use block editor.
	 * @param string $post_type        Post type.
	 * @return bool
	 */
	public function force_block_editor( bool $use_block_editor, string $post_type ): bool {
		if ( 'light_popup' === $post_type ) {
			return true;
		}
		return $use_block_editor;
	}

	/**
	 * Force block editor for specific light_popup post.
	 *
	 * @param bool     $use_block_editor Whether to use block editor.
	 * @param \WP_Post $post             Post object.
	 * @return bool
	 */
	public function force_block_editor_for_post( bool $use_block_editor, \WP_Post $post ): bool {
		if ( 'light_popup' === $post->post_type ) {
			return true;
		}
		return $use_block_editor;
	}
}
