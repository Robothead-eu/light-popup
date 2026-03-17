<?php

namespace Robothead\LightPopup\Domain;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Centralized access to plugin options.
 */
class Options {

	/** @var string Option key in wp_options table. */
	private const OPTION_KEY = 'light_popup_settings';

	/**
	 * Get all plugin options with defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_all(): array {
		$defaults = self::get_defaults();
		$saved    = get_option( self::OPTION_KEY, [] );

		return wp_parse_args( $saved, $defaults );
	}

	/**
	 * Get a single option value.
	 *
	 * @param string $key     Option key.
	 * @param mixed  $default Default value if not set.
	 * @return mixed
	 */
	public static function get( string $key, $default = null ) {
		$options = self::get_all();
		return $options[ $key ] ?? $default;
	}

	/**
	 * Save all plugin options.
	 *
	 * @param array<string, mixed> $options Options to save.
	 */
	public static function save( array $options ): void {
		$sanitized = self::sanitize( $options );
		update_option( self::OPTION_KEY, $sanitized );
	}

	/**
	 * Get default option values.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_defaults(): array {
		return [
			'force_block_editor' => false,
		];
	}

	/**
	 * Sanitize options before saving.
	 *
	 * @param array<string, mixed> $options Raw options.
	 * @return array<string, mixed>
	 */
	private static function sanitize( array $options ): array {
		$defaults  = self::get_defaults();
		$sanitized = [];

		// Force block editor: boolean.
		$sanitized['force_block_editor'] = ! empty( $options['force_block_editor'] );

		return $sanitized;
	}

	/**
	 * Check if block editor should be forced for light_popup.
	 *
	 * @return bool
	 */
	public static function should_force_block_editor(): bool {
		return (bool) self::get( 'force_block_editor', false );
	}
}
