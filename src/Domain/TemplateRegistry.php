<?php
/**
 * Template Registry
 *
 * Handles registration and retrieval of popup visual templates.
 * Templates can be registered by the core plugin or by third-party addons
 * using the 'light_popup_templates' filter.
 *
 * @package LightPopup
 */

namespace Robothead\LightPopup\Domain;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateRegistry {

	/**
	 * Cached templates array.
	 *
	 * @var array<string, array>|null
	 */
	private static ?array $templates = null;

	/**
	 * Get all registered visual templates.
	 *
	 * Templates are keyed by their ID (slug) and contain:
	 * - name: Human-readable name
	 * - description: Short description
	 * - css_file: Path to CSS file relative to plugin root (optional, for external templates)
	 * - css_url: Full URL to CSS file (optional, for external templates)
	 *
	 * Core templates use CSS files from assets/css/templates/{id}.css
	 * External templates should provide css_url.
	 *
	 * @return array<string, array>
	 */
	public static function get_templates(): array {
		if ( null !== self::$templates ) {
			return self::$templates;
		}

		// Core templates - CSS files live in assets/css/templates/
		$templates = [
			'heart' => [
				'name'        => __( 'Heart', 'light-popup' ),
				'description' => __( 'Big heart-shaped popup. Perfect for love-themed promotions.', 'light-popup' ),
				'settings'    => [
					'heart_color' => [
						'type'    => 'color',
						'label'   => __( 'Heart color', 'light-popup' ),
						'default' => '#22c55e',
						'css_var' => '--lp-heart-color',
					],
					'close_bg_color' => [
						'type'    => 'color',
						'label'   => __( 'Close button background', 'light-popup' ),
						'default' => '#ffffff',
						'css_var' => '--lp-close-bg',
					],
					'close_icon_color' => [
						'type'    => 'color',
						'label'   => __( 'Close button icon color', 'light-popup' ),
						'default' => '#333333',
						'css_var' => '--lp-close-icon',
					],
					'content_width' => [
						'type'    => 'text',
						'label'   => __( 'Content width (px)', 'light-popup' ),
						'default' => '320',
						'css_var' => '--lp-content-width',
						'suffix'  => 'px',
					],
					'inner_padding' => [
						'type'    => 'text',
						'label'   => __( 'Inner padding (px)', 'light-popup' ),
						'default' => '48',
						'css_var' => '--lp-inner-padding',
						'suffix'  => 'px',
					],
				],
			],
		];

		/**
		 * Filter to register additional visual templates.
		 *
		 * Third-party plugins can add their own templates:
		 *
		 * add_filter( 'light_popup_templates', function( $templates ) {
		 *     $templates['my-template'] = [
		 *         'name'        => 'My Custom Template',
		 *         'description' => 'A custom popup template.',
		 *         'css_url'     => plugins_url( 'css/my-template.css', __FILE__ ),
		 *     ];
		 *     return $templates;
		 * } );
		 *
		 * @param array<string, array> $templates Registered templates.
		 */
		self::$templates = apply_filters( 'light_popup_templates', $templates );

		return self::$templates;
	}

	/**
	 * Check if a template ID is valid.
	 *
	 * @param string $template_id Template ID to check.
	 * @return bool
	 */
	public static function is_valid( string $template_id ): bool {
		if ( '' === $template_id ) {
			return true; // Empty = default template, always valid.
		}
		$templates = self::get_templates();
		return isset( $templates[ $template_id ] );
	}

	/**
	 * Get a single template by ID.
	 *
	 * @param string $template_id Template ID.
	 * @return array|null Template data or null if not found.
	 */
	public static function get( string $template_id ): ?array {
		$templates = self::get_templates();
		return $templates[ $template_id ] ?? null;
	}

	/**
	 * Get the CSS URL for a template.
	 *
	 * @param string $template_id Template ID.
	 * @return string|null CSS URL or null if not found.
	 */
	public static function get_css_url( string $template_id ): ?string {
		$template = self::get( $template_id );
		if ( ! $template ) {
			return null;
		}

		// External templates provide their own URL.
		if ( ! empty( $template['css_url'] ) ) {
			return $template['css_url'];
		}

		// Core templates use the standard location.
		$css_file = LIGHT_POPUP_DIR . 'assets/css/templates/' . $template_id . '.css';
		if ( file_exists( $css_file ) ) {
			return LIGHT_POPUP_URL . 'assets/css/templates/' . $template_id . '.css';
		}

		return null;
	}

	/**
	 * Get template choices for select dropdowns.
	 *
	 * @return array<string, string> Template ID => Name pairs.
	 */
	public static function get_choices(): array {
		$choices   = [ '' => __( 'Default (white box)', 'light-popup' ) ];
		$templates = self::get_templates();

		foreach ( $templates as $id => $template ) {
			$choices[ $id ] = $template['name'];
		}

		return $choices;
	}

	/**
	 * Get settings schema for a template.
	 *
	 * @param string $template_id Template ID.
	 * @return array<string, array> Settings schema or empty array.
	 */
	public static function get_settings_schema( string $template_id ): array {
		$template = self::get( $template_id );
		return $template['settings'] ?? [];
	}

	/**
	 * Get default values for a template's settings.
	 *
	 * @param string $template_id Template ID.
	 * @return array<string, string> Setting key => default value pairs.
	 */
	public static function get_settings_defaults( string $template_id ): array {
		$schema   = self::get_settings_schema( $template_id );
		$defaults = [];

		foreach ( $schema as $key => $setting ) {
			$defaults[ $key ] = $setting['default'] ?? '';
		}

		return $defaults;
	}

	/**
	 * Generate inline CSS variables from template settings.
	 *
	 * @param string $template_id Template ID.
	 * @param array  $settings    Saved settings values.
	 * @return string CSS variable declarations.
	 */
	public static function generate_settings_css( string $template_id, array $settings ): string {
		$schema = self::get_settings_schema( $template_id );
		if ( empty( $schema ) ) {
			return '';
		}

		$css_vars = [];
		foreach ( $schema as $key => $setting ) {
			if ( empty( $setting['css_var'] ) ) {
				continue;
			}

			$value = $settings[ $key ] ?? $setting['default'] ?? '';
			if ( '' !== $value ) {
				// Append suffix if defined (e.g., 'px' for dimensions).
				$suffix = $setting['suffix'] ?? '';
				$css_vars[] = esc_attr( $setting['css_var'] ) . ': ' . esc_attr( $value . $suffix );
			}
		}

		return implode( '; ', $css_vars );
	}

	/**
	 * Reset cached templates (useful for testing).
	 */
	public static function reset(): void {
		self::$templates = null;
	}
}
