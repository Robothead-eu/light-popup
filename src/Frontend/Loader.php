<?php

namespace Robothead\LightPopup\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Robothead\LightPopup\Domain\PopupRepository;

class Loader {

	/** @var int[] Popup IDs to render on this page. */
	private array $popup_ids = [];

	/** @var bool Whether assets have been enqueued. */
	private bool $enqueued = false;

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

		// Inline the JS config.
		wp_add_inline_script(
			'light-popup',
			'var LightPopupConfig = ' . wp_json_encode( $config ) . ';',
			'before'
		);
	}

	private function render_popup_html( \WP_Post $post ): void {
		$popup_id    = $post->ID;
		$custom_css  = get_post_meta( $popup_id, '_lp_custom_css', true );
		$gdpr        = get_post_meta( $popup_id, '_lp_gdpr_checkbox', true );
		$gdpr_label  = get_post_meta( $popup_id, '_lp_gdpr_checkbox_label', true );

		// Render content through the block editor and shortcode pipeline.
		$content = apply_filters( 'the_content', $post->post_content );
		$content = do_shortcode( $content );

		if ( ! empty( $custom_css ) ) {
			printf(
				'<style>#lp-popup-%d { %s }</style>' . "\n",
				$popup_id,
				wp_strip_all_tags( $custom_css ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- stripped of all tags
			);
		}
		?>
		<dialog class="lp-popup" id="lp-popup-<?php echo esc_attr( (string) $popup_id ); ?>" aria-label="<?php echo esc_attr( $post->post_title ); ?>">
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
