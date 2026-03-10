<?php

namespace Robothead\LightPopup\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcode {

	private Loader $loader;

	public function __construct( Loader $loader ) {
		$this->loader = $loader;
	}

	public function register(): void {
		add_shortcode( 'light_popup', [ $this, 'render' ] );
	}

	/**
	 * @param array<string, string>|string $atts
	 * @param string|null                  $content
	 */
	public function render( $atts, ?string $content = null ): string {
		$atts = shortcode_atts(
			[
				'id'    => '',
				'tag'   => 'a',
				'class' => '',
			],
			$atts,
			'light_popup'
		);

		$popup_id = absint( $atts['id'] );
		if ( ! $popup_id ) {
			return '';
		}

		$post = get_post( $popup_id );
		if ( ! $post || 'light_popup' !== $post->post_type || 'publish' !== $post->post_status ) {
			return '';
		}

		// Force-load this popup's assets on the current page.
		$this->loader->force_load( $popup_id );

		$allowed_tags = [ 'a', 'button', 'span', 'div' ];
		$tag          = in_array( $atts['tag'], $allowed_tags, true ) ? $atts['tag'] : 'a';
		$extra_class  = $atts['class'] ? ' ' . sanitize_html_class( $atts['class'] ) : '';
		$inner        = $content ? do_shortcode( $content ) : esc_html( $post->post_title );

		return sprintf(
			'<%1$s class="lp-trigger lp-trigger--%2$d%3$s" data-lp-id="%2$d" role="button" tabindex="0">%4$s</%1$s>',
			esc_attr( $tag ),
			$popup_id,
			esc_attr( $extra_class ),
			wp_kses_post( $inner )
		);
	}
}
