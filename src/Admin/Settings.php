<?php

namespace Robothead\LightPopup\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Robothead\LightPopup\Domain\Options;

/**
 * Plugin settings page.
 */
class Settings {

	public function register(): void {
		add_action( 'admin_init', [ $this, 'handle_save' ] );
	}

	/**
	 * Handle settings form submission.
	 */
	public function handle_save(): void {
		if ( ! isset( $_POST['lp_settings_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lp_settings_nonce'] ) ), 'lp_save_settings' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = [
			'force_block_editor' => isset( $_POST['lp_force_block_editor'] ),
		];

		Options::save( $options );

		// Redirect to avoid form resubmission.
		wp_safe_redirect( add_query_arg( 'settings-updated', 'true', wp_get_referer() ) );
		exit;
	}

	/**
	 * Render the settings page.
	 */
	public function render_page(): void {
		$options = Options::get_all();
		$updated = isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag set by our own redirect, no action taken
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Light Popup Settings', 'light-popup' ); ?></h1>

			<?php if ( $updated ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved.', 'light-popup' ); ?></p>
				</div>
			<?php endif; ?>

			<form method="post" action="">
				<?php wp_nonce_field( 'lp_save_settings', 'lp_settings_nonce' ); ?>

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'Editor', 'light-popup' ); ?></th>
							<td>
								<fieldset>
									<legend class="screen-reader-text"><span><?php esc_html_e( 'Editor', 'light-popup' ); ?></span></legend>
									<label for="lp_force_block_editor">
										<input type="checkbox" name="lp_force_block_editor" id="lp_force_block_editor" value="1" <?php checked( $options['force_block_editor'] ); ?>>
										<?php esc_html_e( 'Force block editor for popups', 'light-popup' ); ?>
									</label>
									<p class="description">
										<?php esc_html_e( 'Enable this to use the block editor for popups even when the Classic Editor plugin is active. Leave unchecked to respect the Classic Editor settings.', 'light-popup' ); ?>
									</p>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
