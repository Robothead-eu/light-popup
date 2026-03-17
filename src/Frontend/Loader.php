<?php

namespace Robothead\LightPopup\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Robothead\LightPopup\Domain\PopupRepository;
use Robothead\LightPopup\Domain\TemplateRegistry;

class Loader {

	/** @var int[] Popup IDs to render on this page. */
	private array $popup_ids = [];

	/** @var bool Whether assets have been enqueued. */
	private bool $enqueued = false;

	/** @var string[] Template IDs that need CSS loaded. */
	private array $templates_used = [];

	public function register(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'maybe_enqueue' ] );
		add_action( 'wp_footer', [ $this, 'render_popups' ] );
	}

	/**
	 * Called by the shortcode to force-load a specific popup on this page.
	 */
	public function force_load( int $popup_id ): void {
		if ( ! in_array( $popup_id, $this->popup_ids, true ) ) {
			$this->popup_ids[] = $popup_id;
		}
		if ( ! $this->enqueued ) {
			$this->enqueue_assets();
		}
	}

	public function maybe_enqueue(): void {
		if ( ! is_singular() && ! is_home() && ! is_archive() ) {
			return;
		}

		$post_id   = (int) get_queried_object_id();
		$post_type = get_post_type( $post_id ) ?: get_query_var( 'post_type', 'post' );

		$ids = PopupRepository::get_active_popup_ids_for_page( $post_id, (string) $post_type );

		if ( empty( $ids ) ) {
			return;
		}

		$this->popup_ids = $ids;
		$this->enqueue_assets();
	}

	private function enqueue_assets(): void {
		$this->enqueued = true;

		wp_enqueue_style(
			'light-popup',
			LIGHT_POPUP_URL . 'assets/css/popup.css',
			[],
			LIGHT_POPUP_VERSION
		);

		wp_enqueue_script(
			'light-popup',
			LIGHT_POPUP_URL . 'assets/js/popup.js',
			[],
			LIGHT_POPUP_VERSION,
			true
		);
	}

	public function render_popups(): void {
		if ( empty( $this->popup_ids ) ) {
			return;
		}

		// Pass config to JS after we know all popup IDs (including shortcode-forced ones).
		$config = [ 'popups' => [] ];

		foreach ( $this->popup_ids as $popup_id ) {
			$post = get_post( $popup_id );
			if ( ! $post || 'publish' !== $post->post_status ) {
				continue;
			}

			$trigger_type    = get_post_meta( $popup_id, '_lp_trigger_type', true ) ?: 'time_delay';
			$trigger_value   = get_post_meta( $popup_id, '_lp_trigger_value', true ) ?: '8';
			$trigger_2_type  = get_post_meta( $popup_id, '_lp_trigger_secondary_type', true ) ?: '';
			$trigger_2_value = get_post_meta( $popup_id, '_lp_trigger_secondary_value', true ) ?: '';
			$frequency       = get_post_meta( $popup_id, '_lp_frequency', true ) ?: 'week';
			$show_mobile     = '0' !== get_post_meta( $popup_id, '_lp_show_on_mobile', true );
			$show_desktop    = '0' !== get_post_meta( $popup_id, '_lp_show_on_desktop', true );
			$close_backdrop  = '0' !== get_post_meta( $popup_id, '_lp_close_on_backdrop', true );

			// Track template for CSS loading.
			$template = get_post_meta( $popup_id, '_lp_template', true ) ?: '';
			if ( '' !== $template && ! in_array( $template, $this->templates_used, true ) ) {
				$this->templates_used[] = $template;
			}

			$popup_config = [
				'id'              => $popup_id,
				'trigger'         => [ 'type' => $trigger_type, 'value' => $trigger_value ],
				'frequency'       => $frequency,
				'closeOnBackdrop' => $close_backdrop,
				'showOnMobile'    => $show_mobile,
				'showOnDesktop'   => $show_desktop,
			];

			if ( '' !== $trigger_2_type ) {
				$popup_config['secondaryTrigger'] = [ 'type' => $trigger_2_type, 'value' => $trigger_2_value ];
			}

			$config['popups'][] = $popup_config;

			// Render the dialog HTML.
			$this->render_popup_html( $post );
		}

		// Enqueue template-specific CSS.
		$this->enqueue_template_css();

		// Inline the JS config.
		wp_add_inline_script(
			'light-popup',
			'var LightPopupConfig = ' . wp_json_encode( $config ) . ';',
			'before'
		);
	}

	/**
	 * Enqueue CSS for templates used on this page.
	 */
	private function enqueue_template_css(): void {
		foreach ( $this->templates_used as $template_id ) {
			$css_url = TemplateRegistry::get_css_url( $template_id );
			if ( $css_url ) {
				wp_enqueue_style(
					'light-popup-template-' . $template_id,
					$css_url,
					[ 'light-popup' ],
					LIGHT_POPUP_VERSION
				);
			}
		}
	}

	private function render_popup_html( \WP_Post $post ): void {
		$popup_id           = $post->ID;
		$custom_css         = get_post_meta( $popup_id, '_lp_custom_css', true );
		$gdpr               = get_post_meta( $popup_id, '_lp_gdpr_checkbox', true );
		$gdpr_label         = get_post_meta( $popup_id, '_lp_gdpr_checkbox_label', true );
		$template           = get_post_meta( $popup_id, '_lp_template', true );
		$template_settings  = get_post_meta( $popup_id, '_lp_template_settings', true );
		$template_settings  = is_array( $template_settings ) ? $template_settings : [];

		// Build class list.
		$classes = [ 'lp-popup' ];
		if ( ! empty( $template ) ) {
			$classes[] = 'lp-popup--' . sanitize_html_class( $template );
		}

		// Render content through the block editor and shortcode pipeline.
		$content = apply_filters( 'the_content', $post->post_content );
		$content = do_shortcode( $content );

		// Build inline styles from template settings.
		$inline_styles = '';
		if ( ! empty( $template ) && ! empty( $template_settings ) ) {
			$css_vars = TemplateRegistry::generate_settings_css( $template, $template_settings );
			if ( $css_vars ) {
				$inline_styles .= '#lp-popup-' . $popup_id . ' { ' . $css_vars . '; }' . "\n";
			}

			// Special handling for heart template: regenerate SVG with custom color.
			if ( 'heart' === $template && ! empty( $template_settings['heart_color'] ) ) {
				$color = $template_settings['heart_color'];
				// URL-encode the color (# becomes %23).
				$svg_color = str_replace( '#', '%23', $color );
				$inline_styles .= '#lp-popup-' . $popup_id . ' .lp-popup__inner::before { background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'-200 30 800 600\'%3E%3Cpath fill=\'' . esc_attr( $svg_color ) . '\' d=\'M208.8,592.8c0,0,233.4-143.4,304.8-281.9c0.7-1.4,1.5-2.9,2.3-4.3c7.4-12.2,52.2-94.1-12.1-185.4c0,0-110-154.4-303.8-24.8c0,0-106-94.9-239.4-27.8c-21.1,10.6-39.9,25.5-54.8,43.9c-31.1,38.7-71.9,115.3-12.5,208.7c0.8,1.3,1.6,2.6,2.4,3.8c6.6,11.3,53.6,87.4,173.7,181.7l117.8,84c3.3,2.4,7.2,4,11.3,4.3C201.7,595.4,205.4,595,208.8,592.8z\'/%3E%3C/svg%3E"); }' . "\n";
			}
		}

		if ( ! empty( $custom_css ) ) {
			$inline_styles .= '#lp-popup-' . $popup_id . ' { ' . wp_strip_all_tags( $custom_css ) . ' }' . "\n";
		}

		if ( ! empty( $inline_styles ) ) {
			echo '<style>' . $inline_styles . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
		<dialog class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" id="lp-popup-<?php echo esc_attr( (string) $popup_id ); ?>" aria-label="<?php echo esc_attr( $post->post_title ); ?>">
			<div class="lp-popup__inner">
				<button class="lp-popup__close" aria-label="<?php esc_attr_e( 'Close popup', 'light-popup' ); ?>">&times;</button>
				<div class="lp-popup__content">
					<?php echo wp_kses_post( $content ); ?>
				</div>
				<?php if ( '1' === $gdpr ) : ?>
				<label class="lp-popup__gdpr">
					<input type="checkbox" name="lp_gdpr_<?php echo esc_attr( (string) $popup_id ); ?>">
					<?php echo esc_html( $gdpr_label ); ?>
				</label>
				<?php endif; ?>
			</div>
		</dialog>
		<?php
	}
}
