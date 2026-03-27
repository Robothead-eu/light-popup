<?php

namespace Robothead\LightPopup\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Robothead\LightPopup\Domain\TemplateRegistry;

class SettingsMetaBox {

	public function register(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
	}

	public function add_meta_box(): void {
		add_meta_box(
			'light_popup_settings',
			__( 'Popup Settings', 'light-popup' ),
			[ $this, 'render' ],
			'light_popup',
			'side',
			'high'
		);
	}

	public function render( \WP_Post $post ): void {
		wp_nonce_field( 'light_popup_settings_save', 'light_popup_nonce' );

		$enabled               = get_post_meta( $post->ID, '_lp_enabled', true );
		$trigger_type          = get_post_meta( $post->ID, '_lp_trigger_type', true ) ?: 'time_delay';
		$trigger_value         = get_post_meta( $post->ID, '_lp_trigger_value', true ) ?: '8';
		$trigger_2_type        = get_post_meta( $post->ID, '_lp_trigger_secondary_type', true ) ?: '';
		$trigger_2_value       = get_post_meta( $post->ID, '_lp_trigger_secondary_value', true ) ?: '';
		$targeting_type        = get_post_meta( $post->ID, '_lp_targeting_type', true ) ?: 'all';
		$targeting_ids         = get_post_meta( $post->ID, '_lp_targeting_ids', true ) ?: '';
		$targeting_post_types  = get_post_meta( $post->ID, '_lp_targeting_post_types', true ) ?: '';
		$frequency             = get_post_meta( $post->ID, '_lp_frequency', true ) ?: 'week';
		$show_on_mobile        = get_post_meta( $post->ID, '_lp_show_on_mobile', true );
		$show_on_desktop       = get_post_meta( $post->ID, '_lp_show_on_desktop', true );
		$close_on_backdrop     = get_post_meta( $post->ID, '_lp_close_on_backdrop', true );
		$gdpr_checkbox         = get_post_meta( $post->ID, '_lp_gdpr_checkbox', true );
		$gdpr_label            = get_post_meta( $post->ID, '_lp_gdpr_checkbox_label', true ) ?: '';
		$custom_css            = get_post_meta( $post->ID, '_lp_custom_css', true ) ?: '';
		$template              = get_post_meta( $post->ID, '_lp_template', true ) ?: '';
		$template_settings     = get_post_meta( $post->ID, '_lp_template_settings', true );
		$template_settings     = is_array( $template_settings ) ? $template_settings : [];

		// Default to checked (1) for new posts.
		if ( '' === $show_on_mobile ) {
			$show_on_mobile = '1';
		}
		if ( '' === $show_on_desktop ) {
			$show_on_desktop = '1';
		}
		if ( '' === $close_on_backdrop ) {
			$close_on_backdrop = '1';
		}

		$public_post_types = get_post_types( [ 'public' => true ], 'objects' );
		unset( $public_post_types['attachment'] );

		$selected_post_types = array_filter( array_map( 'trim', explode( ',', $targeting_post_types ) ) );
		?>
		<div class="lp-settings">

			<?php /* Status */ ?>
			<div class="lp-settings__section">
				<label class="lp-settings__label lp-settings__label--inline">
					<input type="checkbox" name="lp_enabled" value="1" <?php checked( '1', $enabled ); ?>>
					<?php esc_html_e( 'Enable this popup', 'light-popup' ); ?>
				</label>
			</div>

			<hr>

			<?php /* Trigger */ ?>
			<div class="lp-settings__section">
				<p class="lp-settings__heading"><?php esc_html_e( 'Trigger', 'light-popup' ); ?></p>

				<label class="lp-settings__label"><?php esc_html_e( 'Primary trigger', 'light-popup' ); ?></label>
				<select name="lp_trigger_type" id="lp_trigger_type" class="lp-trigger-select">
					<?php $this->render_trigger_options( $trigger_type ); ?>
				</select>

				<div id="lp_trigger_value_wrap" class="lp-settings__field">
					<label class="lp-settings__label" id="lp_trigger_value_label"><?php esc_html_e( 'Value', 'light-popup' ); ?></label>
					<input type="text" name="lp_trigger_value" id="lp_trigger_value" value="<?php echo esc_attr( $trigger_value ); ?>" class="widefat">
					<p class="description" id="lp_trigger_value_hint"></p>
				</div>

				<label class="lp-settings__label lp-settings__label--mt"><?php esc_html_e( 'Secondary trigger (OR)', 'light-popup' ); ?></label>
				<select name="lp_trigger_secondary_type" id="lp_trigger_secondary_type" class="lp-trigger-select">
					<option value=""><?php esc_html_e( '— None —', 'light-popup' ); ?></option>
					<?php $this->render_trigger_options( $trigger_2_type ); ?>
				</select>

				<div id="lp_trigger_2_value_wrap" class="lp-settings__field" style="display:none;">
					<label class="lp-settings__label"><?php esc_html_e( 'Value', 'light-popup' ); ?></label>
					<input type="text" name="lp_trigger_secondary_value" id="lp_trigger_secondary_value" value="<?php echo esc_attr( $trigger_2_value ); ?>" class="widefat">
					<p class="description" id="lp_trigger_2_value_hint"></p>
				</div>
			</div>

			<hr>

			<?php /* Targeting */ ?>
			<div class="lp-settings__section">
				<p class="lp-settings__heading"><?php esc_html_e( 'Targeting', 'light-popup' ); ?></p>

				<label class="lp-settings__label lp-settings__label--inline">
					<input type="radio" name="lp_targeting_type" value="all" <?php checked( 'all', $targeting_type ); ?>>
					<?php esc_html_e( 'All pages', 'light-popup' ); ?>
				</label>
				<label class="lp-settings__label lp-settings__label--inline">
					<input type="radio" name="lp_targeting_type" value="page_ids" <?php checked( 'page_ids', $targeting_type ); ?>>
					<?php esc_html_e( 'Specific pages / posts', 'light-popup' ); ?>
				</label>
				<label class="lp-settings__label lp-settings__label--inline">
					<input type="radio" name="lp_targeting_type" value="post_types" <?php checked( 'post_types', $targeting_type ); ?>>
					<?php esc_html_e( 'Post types', 'light-popup' ); ?>
				</label>

				<div id="lp_targeting_ids_wrap" class="lp-settings__field" style="display:none;">
					<label class="lp-settings__label"><?php esc_html_e( 'Page / Post IDs (comma-separated)', 'light-popup' ); ?></label>
					<input type="text" name="lp_targeting_ids" value="<?php echo esc_attr( $targeting_ids ); ?>" class="widefat" placeholder="42, 57, 100">
				</div>

				<div id="lp_targeting_post_types_wrap" class="lp-settings__field" style="display:none;">
					<label class="lp-settings__label"><?php esc_html_e( 'Post types', 'light-popup' ); ?></label>
					<?php foreach ( $public_post_types as $pt ) : ?>
						<label class="lp-settings__label lp-settings__label--inline">
							<input type="checkbox" name="lp_targeting_post_types[]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( in_array( $pt->name, $selected_post_types, true ) ); ?>>
							<?php echo esc_html( $pt->label ); ?>
						</label>
					<?php endforeach; ?>
				</div>
			</div>

			<hr>

			<?php /* Frequency */ ?>
			<div class="lp-settings__section">
				<p class="lp-settings__heading"><?php esc_html_e( 'Frequency', 'light-popup' ); ?></p>
				<select name="lp_frequency" class="widefat">
					<option value="always" <?php selected( 'always', $frequency ); ?>><?php esc_html_e( 'Always', 'light-popup' ); ?></option>
					<option value="session" <?php selected( 'session', $frequency ); ?>><?php esc_html_e( 'Once per session', 'light-popup' ); ?></option>
					<option value="day" <?php selected( 'day', $frequency ); ?>><?php esc_html_e( 'Once per day', 'light-popup' ); ?></option>
					<option value="week" <?php selected( 'week', $frequency ); ?>><?php esc_html_e( 'Once per week', 'light-popup' ); ?></option>
					<option value="month" <?php selected( 'month', $frequency ); ?>><?php esc_html_e( 'Once per month', 'light-popup' ); ?></option>
					<option value="once" <?php selected( 'once', $frequency ); ?>><?php esc_html_e( 'Once (never again)', 'light-popup' ); ?></option>
				</select>
			</div>

			<hr>

			<?php /* Devices */ ?>
			<div class="lp-settings__section">
				<p class="lp-settings__heading"><?php esc_html_e( 'Devices', 'light-popup' ); ?></p>
				<label class="lp-settings__label lp-settings__label--inline">
					<input type="checkbox" name="lp_show_on_desktop" value="1" <?php checked( '1', $show_on_desktop ); ?>>
					<?php esc_html_e( 'Show on desktop', 'light-popup' ); ?>
				</label>
				<label class="lp-settings__label lp-settings__label--inline">
					<input type="checkbox" name="lp_show_on_mobile" value="1" <?php checked( '1', $show_on_mobile ); ?>>
					<?php esc_html_e( 'Show on mobile', 'light-popup' ); ?>
				</label>
			</div>

			<hr>

			<?php /* Close behavior */ ?>
			<div class="lp-settings__section">
				<p class="lp-settings__heading"><?php esc_html_e( 'Close behavior', 'light-popup' ); ?></p>
				<label class="lp-settings__label lp-settings__label--inline">
					<input type="checkbox" name="lp_close_on_backdrop" value="1" <?php checked( '1', $close_on_backdrop ); ?>>
					<?php esc_html_e( 'Close when clicking outside', 'light-popup' ); ?>
				</label>
			</div>

			<hr>

			<?php /* GDPR */ ?>
			<div class="lp-settings__section">
				<p class="lp-settings__heading"><?php esc_html_e( 'GDPR', 'light-popup' ); ?></p>
				<label class="lp-settings__label lp-settings__label--inline">
					<input type="checkbox" name="lp_gdpr_checkbox" id="lp_gdpr_checkbox" value="1" <?php checked( '1', $gdpr_checkbox ); ?>>
					<?php esc_html_e( 'Show consent checkbox', 'light-popup' ); ?>
				</label>
				<div id="lp_gdpr_label_wrap" class="lp-settings__field" style="display:none;">
					<label class="lp-settings__label"><?php esc_html_e( 'Checkbox label', 'light-popup' ); ?></label>
					<input type="text" name="lp_gdpr_checkbox_label" value="<?php echo esc_attr( $gdpr_label ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'I agree to the privacy policy.', 'light-popup' ); ?>">
				</div>
			</div>

			<hr>

			<?php /* Template */ ?>
			<div class="lp-settings__section">
				<p class="lp-settings__heading"><?php esc_html_e( 'Template', 'light-popup' ); ?></p>
				<select name="lp_template" id="lp_template" class="widefat">
					<?php foreach ( TemplateRegistry::get_choices() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $template ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>

				<?php // Render settings for each template (shown/hidden via JS). ?>
				<?php foreach ( TemplateRegistry::get_templates() as $tpl_id => $tpl_data ) :
					$schema = $tpl_data['settings'] ?? [];
					if ( empty( $schema ) ) {
						continue;
					}
					$is_current = ( $template === $tpl_id );
				?>
				<div id="lp_template_settings_<?php echo esc_attr( $tpl_id ); ?>" class="lp-template-settings" style="<?php echo $is_current ? '' : 'display:none;'; ?>">
					<?php foreach ( $schema as $setting_key => $setting ) :
						$saved_value = $template_settings[ $setting_key ] ?? $setting['default'];
					?>
					<div class="lp-settings__field">
						<label class="lp-settings__label"><?php echo esc_html( $setting['label'] ); ?></label>
						<?php if ( 'color' === $setting['type'] ) : ?>
							<input type="text" name="lp_template_settings[<?php echo esc_attr( $setting_key ); ?>]" value="<?php echo esc_attr( $saved_value ); ?>" class="lp-color-picker" data-default-color="<?php echo esc_attr( $setting['default'] ); ?>">
						<?php else : ?>
							<input type="text" name="lp_template_settings[<?php echo esc_attr( $setting_key ); ?>]" value="<?php echo esc_attr( $saved_value ); ?>" class="widefat">
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endforeach; ?>
			</div>

			<hr>

			<?php /* Custom CSS */ ?>
			<div class="lp-settings__section">
				<p class="lp-settings__heading"><?php esc_html_e( 'Custom CSS', 'light-popup' ); ?></p>
				<p class="description"><?php esc_html_e( 'Styles are scoped to this popup only.', 'light-popup' ); ?></p>
				<textarea name="lp_custom_css" rows="6" class="widefat code" placeholder=".lp-popup__inner { background: #f9f9f9; }"><?php echo esc_textarea( $custom_css ); ?></textarea>
			</div>

		</div>
		<?php
	}

	/**
	 * @param string $selected Currently selected value.
	 */
	private function render_trigger_options( string $selected ): void {
		$options = [
			'time_delay'   => __( 'Time delay', 'light-popup' ),
			'scroll_depth' => __( 'Scroll depth', 'light-popup' ),
			'exit_intent'  => __( 'Exit intent', 'light-popup' ),
			'click'        => __( 'Click on element', 'light-popup' ),
			'url_param'    => __( 'URL parameter', 'light-popup' ),
		];
		foreach ( $options as $value => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $value ),
				selected( $selected, $value, false ),
				esc_html( $label )
			);
		}
	}
}
