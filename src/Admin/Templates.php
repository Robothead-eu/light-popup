<?php

namespace Robothead\LightPopup\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Templates {

	public function register(): void {
		add_action( 'admin_action_lp_create_from_template', [ $this, 'handle_create_from_template' ] );
		add_action( 'admin_action_lp_duplicate', [ $this, 'handle_duplicate' ] );
		add_action( 'admin_action_lp_export', [ $this, 'handle_export' ] );
		add_action( 'admin_action_lp_import', [ $this, 'handle_import' ] );
	}

	// -------------------------------------------------------
	// Template library
	// -------------------------------------------------------

	/**
	 * Returns all built-in sample templates.
	 *
	 * Each template is an array with keys:
	 *   id, name, description, content (HTML), meta (assoc array of _lp_* values)
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_templates(): array {
		return [
			[
				'id'          => 'newsletter',
				'name'        => 'Newsletter signup',
				'description' => 'Email capture popup with a clear CTA. Shows after 8 seconds, once per week.',
				'content'     => "<h2>Stay in the loop</h2>\n<p>Get the latest updates delivered straight to your inbox. No spam, ever.</p>\n<!-- wp:shortcode -->[contact-form-7 id=\"\" title=\"Newsletter\"]<!-- /wp:shortcode -->",
				'meta'        => [
					'_lp_enabled'               => '0',
					'_lp_trigger_type'           => 'time_delay',
					'_lp_trigger_value'          => '8',
					'_lp_trigger_secondary_type' => '',
					'_lp_trigger_secondary_value' => '',
					'_lp_targeting_type'         => 'all',
					'_lp_targeting_ids'          => '',
					'_lp_targeting_post_types'   => '',
					'_lp_frequency'              => 'week',
					'_lp_show_on_mobile'         => '1',
					'_lp_show_on_desktop'        => '1',
					'_lp_close_on_backdrop'      => '1',
					'_lp_gdpr_checkbox'          => '1',
					'_lp_gdpr_checkbox_label'    => 'I agree to receive email updates. I can unsubscribe at any time.',
					'_lp_custom_css'             => '',
				],
			],
			[
				'id'          => 'announcement',
				'name'        => 'Announcement banner',
				'description' => 'Simple announcement popup. Shows after 5 seconds, always visible.',
				'content'     => "<h2>Important announcement</h2>\n<p>We have something exciting to share. <a href=\"#\">Read more →</a></p>",
				'meta'        => [
					'_lp_enabled'               => '0',
					'_lp_trigger_type'           => 'time_delay',
					'_lp_trigger_value'          => '5',
					'_lp_trigger_secondary_type' => '',
					'_lp_trigger_secondary_value' => '',
					'_lp_targeting_type'         => 'all',
					'_lp_targeting_ids'          => '',
					'_lp_targeting_post_types'   => '',
					'_lp_frequency'              => 'session',
					'_lp_show_on_mobile'         => '1',
					'_lp_show_on_desktop'        => '1',
					'_lp_close_on_backdrop'      => '1',
					'_lp_gdpr_checkbox'          => '0',
					'_lp_gdpr_checkbox_label'    => '',
					'_lp_custom_css'             => '',
				],
			],
			[
				'id'          => 'exit_intent',
				'name'        => 'Exit intent offer',
				'description' => 'Catches visitors before they leave. Triggered on exit intent, shown once.',
				'content'     => "<h2>Wait — before you go!</h2>\n<p>Here's a special offer just for you.</p>\n<p><a href=\"#\" class=\"wp-block-button__link\">Claim your offer</a></p>",
				'meta'        => [
					'_lp_enabled'               => '0',
					'_lp_trigger_type'           => 'exit_intent',
					'_lp_trigger_value'          => '',
					'_lp_trigger_secondary_type' => '',
					'_lp_trigger_secondary_value' => '',
					'_lp_targeting_type'         => 'all',
					'_lp_targeting_ids'          => '',
					'_lp_targeting_post_types'   => '',
					'_lp_frequency'              => 'once',
					'_lp_show_on_mobile'         => '0',
					'_lp_show_on_desktop'        => '1',
					'_lp_close_on_backdrop'      => '1',
					'_lp_gdpr_checkbox'          => '0',
					'_lp_gdpr_checkbox_label'    => '',
					'_lp_custom_css'             => '',
				],
			],
			[
				'id'          => 'content_upgrade',
				'name'        => 'Content upgrade',
				'description' => 'Appears mid-read at 50% scroll on blog posts. Offers a related download.',
				'content'     => "<h2>Enjoying this article?</h2>\n<p>Download the free checklist that goes with it.</p>\n<!-- wp:shortcode -->[contact-form-7 id=\"\" title=\"Download\"]<!-- /wp:shortcode -->",
				'meta'        => [
					'_lp_enabled'               => '0',
					'_lp_trigger_type'           => 'scroll_depth',
					'_lp_trigger_value'          => '50',
					'_lp_trigger_secondary_type' => '',
					'_lp_trigger_secondary_value' => '',
					'_lp_targeting_type'         => 'post_types',
					'_lp_targeting_ids'          => '',
					'_lp_targeting_post_types'   => 'post',
					'_lp_frequency'              => 'week',
					'_lp_show_on_mobile'         => '1',
					'_lp_show_on_desktop'        => '1',
					'_lp_close_on_backdrop'      => '1',
					'_lp_gdpr_checkbox'          => '1',
					'_lp_gdpr_checkbox_label'    => 'I agree to receive the download and occasional related emails.',
					'_lp_custom_css'             => '',
				],
			],
			[
				'id'          => 'cookie_notice',
				'name'        => 'Cookie notice',
				'description' => 'Minimal cookie/GDPR notice. Triggered immediately, shown once ever.',
				'content'     => "<p>This website uses cookies to ensure you get the best experience. <a href=\"/privacy-policy\">Learn more</a></p>",
				'meta'        => [
					'_lp_enabled'               => '0',
					'_lp_trigger_type'           => 'time_delay',
					'_lp_trigger_value'          => '1',
					'_lp_trigger_secondary_type' => '',
					'_lp_trigger_secondary_value' => '',
					'_lp_targeting_type'         => 'all',
					'_lp_targeting_ids'          => '',
					'_lp_targeting_post_types'   => '',
					'_lp_frequency'              => 'once',
					'_lp_show_on_mobile'         => '1',
					'_lp_show_on_desktop'        => '1',
					'_lp_close_on_backdrop'      => '0',
					'_lp_gdpr_checkbox'          => '0',
					'_lp_gdpr_checkbox_label'    => '',
					'_lp_custom_css'             => '--lp-max-width: 720px; --lp-radius: 0;',
				],
			],
		];
	}

	// -------------------------------------------------------
	// Templates page renderer
	// -------------------------------------------------------

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'light-popup' ) );
		}
		?>
		<div class="wrap lp-templates-page">
			<h1><?php esc_html_e( 'Popup Templates', 'light-popup' ); ?></h1>
			<p class="lp-templates-page__intro"><?php esc_html_e( 'Start from a template to get set up quickly. All templates are created as inactive — review the settings before enabling.', 'light-popup' ); ?></p>

			<div class="lp-template-grid">
				<?php foreach ( self::get_templates() as $template ) : ?>
					<?php
					$nonce = wp_create_nonce( 'lp_create_from_template_' . $template['id'] );
					$url   = add_query_arg(
						[
							'action'      => 'lp_create_from_template',
							'template_id' => $template['id'],
							'_wpnonce'    => $nonce,
						],
						admin_url( 'admin.php' )
					);
					?>
					<div class="lp-template-card">
						<div class="lp-template-card__body">
							<h3 class="lp-template-card__title"><?php echo esc_html( $template['name'] ); ?></h3>
							<p class="lp-template-card__desc"><?php echo esc_html( $template['description'] ); ?></p>
						</div>
						<div class="lp-template-card__footer">
							<a href="<?php echo esc_url( $url ); ?>" class="button button-primary"><?php esc_html_e( 'Use template', 'light-popup' ); ?></a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<hr class="lp-templates-page__divider">

			<h2><?php esc_html_e( 'Import popup', 'light-popup' ); ?></h2>
			<p><?php esc_html_e( 'Import a popup from a previously exported JSON file.', 'light-popup' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
				<input type="hidden" name="action" value="lp_import">
				<?php wp_nonce_field( 'lp_import' ); ?>
				<input type="file" name="lp_import_file" accept=".json" required>
				<button type="submit" class="button button-secondary" style="margin-left:8px;"><?php esc_html_e( 'Import', 'light-popup' ); ?></button>
			</form>
		</div>
		<?php
	}

	// -------------------------------------------------------
	// Action handlers
	// -------------------------------------------------------

	public function handle_create_from_template(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'light-popup' ) );
		}

		$template_id = isset( $_GET['template_id'] ) ? sanitize_key( $_GET['template_id'] ) : '';
		check_admin_referer( 'lp_create_from_template_' . $template_id );

		$template = null;
		foreach ( self::get_templates() as $t ) {
			if ( $t['id'] === $template_id ) {
				$template = $t;
				break;
			}
		}

		if ( ! $template ) {
			wp_die( esc_html__( 'Template not found.', 'light-popup' ) );
		}

		$post_id = $this->create_popup( $template['name'], $template['content'], $template['meta'] );

		wp_safe_redirect( get_edit_post_link( $post_id, 'raw' ) );
		exit;
	}

	public function handle_duplicate(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'light-popup' ) );
		}

		$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
		if ( ! $post_id ) {
			wp_die( esc_html__( 'Invalid popup.', 'light-popup' ) );
		}

		check_admin_referer( 'lp_duplicate_' . $post_id );

		$post = get_post( $post_id );
		if ( ! $post || 'light_popup' !== $post->post_type ) {
			wp_die( esc_html__( 'Invalid popup.', 'light-popup' ) );
		}

		$meta_keys = [
			'_lp_enabled', '_lp_trigger_type', '_lp_trigger_value',
			'_lp_trigger_secondary_type', '_lp_trigger_secondary_value',
			'_lp_targeting_type', '_lp_targeting_ids', '_lp_targeting_post_types',
			'_lp_frequency', '_lp_show_on_mobile', '_lp_show_on_desktop',
			'_lp_close_on_backdrop', '_lp_gdpr_checkbox', '_lp_gdpr_checkbox_label',
			'_lp_custom_css',
		];

		$meta = [];
		foreach ( $meta_keys as $key ) {
			$meta[ $key ] = (string) get_post_meta( $post_id, $key, true );
		}

		// Duplicate starts inactive regardless of original state.
		$meta['_lp_enabled'] = '0';

		/* translators: %s: original popup title */
		$new_title = sprintf( __( '%s (copy)', 'light-popup' ), $post->post_title );
		$new_id    = $this->create_popup( $new_title, $post->post_content, $meta );

		wp_safe_redirect( get_edit_post_link( $new_id, 'raw' ) );
		exit;
	}

	public function handle_export(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'light-popup' ) );
		}

		$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
		if ( ! $post_id ) {
			wp_die( esc_html__( 'Invalid popup.', 'light-popup' ) );
		}

		check_admin_referer( 'lp_export_' . $post_id );

		$post = get_post( $post_id );
		if ( ! $post || 'light_popup' !== $post->post_type ) {
			wp_die( esc_html__( 'Invalid popup.', 'light-popup' ) );
		}

		$meta_keys = [
			'_lp_enabled', '_lp_trigger_type', '_lp_trigger_value',
			'_lp_trigger_secondary_type', '_lp_trigger_secondary_value',
			'_lp_targeting_type', '_lp_targeting_ids', '_lp_targeting_post_types',
			'_lp_frequency', '_lp_show_on_mobile', '_lp_show_on_desktop',
			'_lp_close_on_backdrop', '_lp_gdpr_checkbox', '_lp_gdpr_checkbox_label',
			'_lp_custom_css',
		];

		$meta = [];
		foreach ( $meta_keys as $key ) {
			$meta[ $key ] = (string) get_post_meta( $post_id, $key, true );
		}

		$export = [
			'_lp_export_version' => '1',
			'title'              => $post->post_title,
			'content'            => $post->post_content,
			'meta'               => $meta,
		];

		$filename = sanitize_file_name( $post->post_title ) . '-light-popup.json';

		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'X-Robots-Tag: noindex' );

		echo wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		exit;
	}

	public function handle_import(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'light-popup' ) );
		}

		check_admin_referer( 'lp_import' );

		if ( empty( $_FILES['lp_import_file']['tmp_name'] ) ) {
			wp_safe_redirect( add_query_arg( 'lp_error', 'no_file', admin_url( 'edit.php?post_type=light_popup&page=light-popup-templates' ) ) );
			exit;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$raw = file_get_contents( $_FILES['lp_import_file']['tmp_name'] );
		if ( false === $raw ) {
			wp_safe_redirect( add_query_arg( 'lp_error', 'read_failed', admin_url( 'edit.php?post_type=light_popup&page=light-popup-templates' ) ) );
			exit;
		}

		$data = json_decode( $raw, true );

		if ( ! is_array( $data ) || empty( $data['title'] ) || ! isset( $data['content'], $data['meta'] ) || '1' !== ( $data['_lp_export_version'] ?? '' ) ) {
			wp_safe_redirect( add_query_arg( 'lp_error', 'invalid', admin_url( 'edit.php?post_type=light_popup&page=light-popup-templates' ) ) );
			exit;
		}

		$allowed_meta_keys = [
			'_lp_enabled', '_lp_trigger_type', '_lp_trigger_value',
			'_lp_trigger_secondary_type', '_lp_trigger_secondary_value',
			'_lp_targeting_type', '_lp_targeting_ids', '_lp_targeting_post_types',
			'_lp_frequency', '_lp_show_on_mobile', '_lp_show_on_desktop',
			'_lp_close_on_backdrop', '_lp_gdpr_checkbox', '_lp_gdpr_checkbox_label',
			'_lp_custom_css',
		];

		$meta = [];
		foreach ( $allowed_meta_keys as $key ) {
			$meta[ $key ] = isset( $data['meta'][ $key ] ) ? sanitize_text_field( (string) $data['meta'][ $key ] ) : '';
		}

		// Imported popups always start inactive.
		$meta['_lp_enabled'] = '0';

		$new_id = $this->create_popup(
			sanitize_text_field( $data['title'] ),
			wp_kses_post( $data['content'] ),
			$meta
		);

		wp_safe_redirect( get_edit_post_link( $new_id, 'raw' ) );
		exit;
	}

	// -------------------------------------------------------
	// Shared helper
	// -------------------------------------------------------

	/**
	 * Creates a new light_popup post with the given content and meta.
	 *
	 * @param array<string, string> $meta
	 */
	private function create_popup( string $title, string $content, array $meta ): int {
		$post_id = wp_insert_post(
			[
				'post_title'   => $title,
				'post_content' => $content,
				'post_type'    => 'light_popup',
				'post_status'  => 'publish',
			],
			true
		);

		if ( is_wp_error( $post_id ) ) {
			wp_die( esc_html( $post_id->get_error_message() ) );
		}

		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		\Robothead\LightPopup\Domain\PopupRepository::flush_cache();

		return $post_id;
	}
}
