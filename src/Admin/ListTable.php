<?php

namespace Robothead\LightPopup\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ListTable {

	public function register(): void {
		add_filter( 'manage_light_popup_posts_columns', [ $this, 'add_columns' ] );
		add_action( 'manage_light_popup_posts_custom_column', [ $this, 'render_column' ], 10, 2 );
		add_filter( 'post_row_actions', [ $this, 'add_row_actions' ], 10, 2 );
		add_action( 'admin_action_lp_toggle_enabled', [ $this, 'handle_toggle' ] );
		add_action( 'all_admin_notices', [ $this, 'render_list_header' ] );
		add_action( 'admin_footer', [ $this, 'render_list_footer' ] );
	}

	/**
	 * @param array<string, string> $columns
	 * @return array<string, string>
	 */
	public function add_columns( array $columns ): array {
		$new = [];
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['lp_status']    = __( 'Status', 'light-popup' );
				$new['lp_trigger']   = __( 'Trigger', 'light-popup' );
				$new['lp_frequency'] = __( 'Frequency', 'light-popup' );
				$new['lp_shortcode'] = __( 'Shortcode', 'light-popup' );
			}
		}
		return $new;
	}

	public function render_column( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'lp_status':
				$enabled = get_post_meta( $post_id, '_lp_enabled', true );
				$nonce   = wp_create_nonce( 'lp_toggle_' . $post_id );
				$url     = add_query_arg(
					[
						'action'   => 'lp_toggle_enabled',
						'post_id'  => $post_id,
						'_wpnonce' => $nonce,
					],
					admin_url( 'admin.php' )
				);
				if ( '1' === $enabled ) {
					printf(
						'<a href="%s" class="lp-toggle lp-toggle--active" title="%s"><span class="lp-toggle__dot"></span>%s</a>',
						esc_url( $url ),
						esc_attr__( 'Click to disable', 'light-popup' ),
						esc_html__( 'Active', 'light-popup' )
					);
				} else {
					printf(
						'<a href="%s" class="lp-toggle lp-toggle--inactive" title="%s"><span class="lp-toggle__dot"></span>%s</a>',
						esc_url( $url ),
						esc_attr__( 'Click to enable', 'light-popup' ),
						esc_html__( 'Inactive', 'light-popup' )
					);
				}
				break;

			case 'lp_trigger':
				$type   = get_post_meta( $post_id, '_lp_trigger_type', true );
				$value  = get_post_meta( $post_id, '_lp_trigger_value', true );
				$labels = [
					'time_delay'   => __( 'Time delay', 'light-popup' ),
					'scroll_depth' => __( 'Scroll depth', 'light-popup' ),
					'exit_intent'  => __( 'Exit intent', 'light-popup' ),
					'click'        => __( 'Click', 'light-popup' ),
				];
				$label = $labels[ $type ] ?? esc_html( (string) $type );
				if ( $value && 'exit_intent' !== $type ) {
					echo esc_html( $label . ': ' . $value );
				} else {
					echo esc_html( $label );
				}
				break;

			case 'lp_frequency':
				$freq   = get_post_meta( $post_id, '_lp_frequency', true );
				$labels = [
					'always'  => __( 'Always', 'light-popup' ),
					'session' => __( 'Per session', 'light-popup' ),
					'day'     => __( 'Daily', 'light-popup' ),
					'week'    => __( 'Weekly', 'light-popup' ),
					'month'   => __( 'Monthly', 'light-popup' ),
					'once'    => __( 'Once', 'light-popup' ),
				];
				echo esc_html( $labels[ $freq ] ?? (string) $freq );
				break;

			case 'lp_shortcode':
				printf(
					'<code>[light_popup id="%d"]...[/light_popup]</code>',
					intval($post_id)
				);
				break;
		}
	}

	/**
	 * @param array<string, string> $actions
	 */
	public function add_row_actions( array $actions, \WP_Post $post ): array {
		if ( 'light_popup' !== $post->post_type ) {
			return $actions;
		}

		$duplicate_url = add_query_arg(
			[
				'action'   => 'lp_duplicate',
				'post_id'  => $post->ID,
				'_wpnonce' => wp_create_nonce( 'lp_duplicate_' . $post->ID ),
			],
			admin_url( 'admin.php' )
		);

		$export_url = add_query_arg(
			[
				'action'   => 'lp_export',
				'post_id'  => $post->ID,
				'_wpnonce' => wp_create_nonce( 'lp_export_' . $post->ID ),
			],
			admin_url( 'admin.php' )
		);

		$actions['lp_duplicate'] = '<a href="' . esc_url( $duplicate_url ) . '">' . esc_html__( 'Duplicate', 'light-popup' ) . '</a>';
		$actions['lp_export']    = '<a href="' . esc_url( $export_url ) . '">' . esc_html__( 'Export', 'light-popup' ) . '</a>';

		return $actions;
	}

	public function handle_toggle(): void {
		$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
		if ( ! $post_id ) {
			wp_die( esc_html__( 'Invalid popup.', 'light-popup' ) );
		}

		check_admin_referer( 'lp_toggle_' . $post_id );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'light-popup' ) );
		}

		$current = get_post_meta( $post_id, '_lp_enabled', true );
		update_post_meta( $post_id, '_lp_enabled', '1' === $current ? '0' : '1' );

		\Robothead\LightPopup\Domain\PopupRepository::flush_cache();

		wp_safe_redirect( admin_url( 'edit.php?post_type=light_popup' ) );
		exit;
	}

	public function render_list_header(): void {
	}

	public function render_list_footer(): void {
		$screen = get_current_screen();
		if ( ! $screen || 'light_popup' !== $screen->post_type || 'edit' !== $screen->base ) {
			return;
		}
		?>
		<div class="lp-list-footer">
			<?php
			printf(
				/* translators: %s: Robothead link */
				esc_html__( 'Built by %s', 'light-popup' ),
				'<a href="https://robothead.eu" target="_blank" rel="noopener">Robothead</a>'
			);
			?>
		</div>
		<?php
	}
}
