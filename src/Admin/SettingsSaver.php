<?php

namespace Robothead\LightPopup\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Robothead\LightPopup\Domain\PopupRepository;
use Robothead\LightPopup\Domain\TemplateRegistry;

class SettingsSaver {

	public function register(): void {
		add_action( 'save_post_light_popup', [ $this, 'save' ], 10, 2 );
		add_action( 'delete_post', [ $this, 'flush_cache_on_delete' ] );
	}

	public function save( int $post_id, \WP_Post $post ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['light_popup_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['light_popup_nonce'] ) ), 'light_popup_settings_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->save_meta( $post_id, '_lp_enabled', isset( $_POST['lp_enabled'] ) ? '1' : '0' );

		$allowed_trigger_types = [ 'time_delay', 'scroll_depth', 'exit_intent', 'click', 'url_param' ];

		$trigger_type = isset( $_POST['lp_trigger_type'] ) ? sanitize_text_field( wp_unslash( $_POST['lp_trigger_type'] ) ) : 'time_delay';
		if ( ! in_array( $trigger_type, $allowed_trigger_types, true ) ) {
			$trigger_type = 'time_delay';
		}
		$this->save_meta( $post_id, '_lp_trigger_type', $trigger_type );
		$this->save_meta( $post_id, '_lp_trigger_value', isset( $_POST['lp_trigger_value'] ) ? sanitize_text_field( wp_unslash( $_POST['lp_trigger_value'] ) ) : '' );

		$trigger_2_type = isset( $_POST['lp_trigger_secondary_type'] ) ? sanitize_text_field( wp_unslash( $_POST['lp_trigger_secondary_type'] ) ) : '';
		if ( '' !== $trigger_2_type && ! in_array( $trigger_2_type, $allowed_trigger_types, true ) ) {
			$trigger_2_type = '';
		}
		$this->save_meta( $post_id, '_lp_trigger_secondary_type', $trigger_2_type );
		$this->save_meta( $post_id, '_lp_trigger_secondary_value', isset( $_POST['lp_trigger_secondary_value'] ) ? sanitize_text_field( wp_unslash( $_POST['lp_trigger_secondary_value'] ) ) : '' );

		$allowed_targeting_types = [ 'all', 'page_ids', 'post_types' ];
		$targeting_type = isset( $_POST['lp_targeting_type'] ) ? sanitize_text_field( wp_unslash( $_POST['lp_targeting_type'] ) ) : 'all';
		if ( ! in_array( $targeting_type, $allowed_targeting_types, true ) ) {
			$targeting_type = 'all';
		}
		$this->save_meta( $post_id, '_lp_targeting_type', $targeting_type );

		// Targeting IDs: strip to integers only.
		$targeting_ids_raw = isset( $_POST['lp_targeting_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['lp_targeting_ids'] ) ) : '';
		$targeting_ids     = implode( ',', array_filter( array_map( 'absint', explode( ',', $targeting_ids_raw ) ) ) );
		$this->save_meta( $post_id, '_lp_targeting_ids', $targeting_ids );

		// Targeting post types: validate against registered post types.
		$public_post_types   = array_keys( get_post_types( [ 'public' => true ] ) );
		$submitted_types     = isset( $_POST['lp_targeting_post_types'] ) && is_array( $_POST['lp_targeting_post_types'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['lp_targeting_post_types'] ) )
			: [];
		$valid_types         = array_intersect( $submitted_types, $public_post_types );
		$this->save_meta( $post_id, '_lp_targeting_post_types', implode( ',', $valid_types ) );

		$allowed_frequencies = [ 'always', 'session', 'day', 'week', 'month', 'once' ];
		$frequency = isset( $_POST['lp_frequency'] ) ? sanitize_text_field( wp_unslash( $_POST['lp_frequency'] ) ) : 'week';
		if ( ! in_array( $frequency, $allowed_frequencies, true ) ) {
			$frequency = 'week';
		}
		$this->save_meta( $post_id, '_lp_frequency', $frequency );

		$this->save_meta( $post_id, '_lp_show_on_mobile', isset( $_POST['lp_show_on_mobile'] ) ? '1' : '0' );
		$this->save_meta( $post_id, '_lp_show_on_desktop', isset( $_POST['lp_show_on_desktop'] ) ? '1' : '0' );
		$this->save_meta( $post_id, '_lp_close_on_backdrop', isset( $_POST['lp_close_on_backdrop'] ) ? '1' : '0' );
		$this->save_meta( $post_id, '_lp_gdpr_checkbox', isset( $_POST['lp_gdpr_checkbox'] ) ? '1' : '0' );
		$this->save_meta( $post_id, '_lp_gdpr_checkbox_label', isset( $_POST['lp_gdpr_checkbox_label'] ) ? sanitize_text_field( wp_unslash( $_POST['lp_gdpr_checkbox_label'] ) ) : '' );

		// Custom CSS: strip HTML tags to prevent injection; keep valid CSS characters.
		$custom_css = isset( $_POST['lp_custom_css'] ) ? wp_strip_all_tags( wp_unslash( $_POST['lp_custom_css'] ) ) : '';
		$this->save_meta( $post_id, '_lp_custom_css', $custom_css );

		// Template: validate against registered templates.
		$template = isset( $_POST['lp_template'] ) ? sanitize_text_field( wp_unslash( $_POST['lp_template'] ) ) : '';
		if ( ! TemplateRegistry::is_valid( $template ) ) {
			$template = '';
		}
		$this->save_meta( $post_id, '_lp_template', $template );

		// Template settings: validate and sanitize based on schema.
		$template_settings = [];
		if ( ! empty( $template ) ) {
			$schema = TemplateRegistry::get_settings_schema( $template );
			$submitted_settings = isset( $_POST['lp_template_settings'] ) && is_array( $_POST['lp_template_settings'] )
				? wp_unslash( $_POST['lp_template_settings'] )
				: [];

			foreach ( $schema as $key => $setting ) {
				if ( isset( $submitted_settings[ $key ] ) ) {
					$value = sanitize_text_field( $submitted_settings[ $key ] );
					// For color type, validate hex format.
					if ( 'color' === $setting['type'] ) {
						$value = preg_match( '/^#[a-fA-F0-9]{6}$/', $value ) ? $value : $setting['default'];
					}
					$template_settings[ $key ] = $value;
				}
			}
		}
		update_post_meta( $post_id, '_lp_template_settings', $template_settings );

		PopupRepository::flush_cache();
	}

	public function flush_cache_on_delete( int $post_id ): void {
		if ( 'light_popup' === get_post_type( $post_id ) ) {
			PopupRepository::flush_cache();
		}
	}

	private function save_meta( int $post_id, string $key, string $value ): void {
		update_post_meta( $post_id, $key, $value );
	}
}
