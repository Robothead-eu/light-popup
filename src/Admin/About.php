<?php

namespace Robothead\LightPopup\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * About page for the plugin.
 */
class About {

	/**
	 * Render the about page.
	 */
	public function render_page(): void {
		?>
		<div class="wrap lp-about-page">
			<h1><?php esc_html_e( 'About Light Popup', 'light-popup' ); ?></h1>

			<div class="lp-about-page__content">
				<div class="lp-about-page__section">
					<h2><?php esc_html_e( 'A lightweight popup plugin', 'light-popup' ); ?></h2>
					<p>
						<?php esc_html_e( 'Light Popup is a minimal, performance-focused popup plugin built with the WordPress block editor. It gives you full control over popup design using blocks, without bloated page builders or complex interfaces.', 'light-popup' ); ?>
					</p>
				</div>

				<div class="lp-about-page__section">
					<h2><?php esc_html_e( 'Features', 'light-popup' ); ?></h2>
					<ul>
						<li><?php esc_html_e( 'Block editor - Design popups using any blocks', 'light-popup' ); ?></li>
						<li><?php esc_html_e( 'Multiple triggers - Time delay, scroll depth, exit intent, click', 'light-popup' ); ?></li>
						<li><?php esc_html_e( 'Smart targeting - All pages, specific pages, or post types', 'light-popup' ); ?></li>
						<li><?php esc_html_e( 'Frequency control - Once per session, day, week, month, or forever', 'light-popup' ); ?></li>
						<li><?php esc_html_e( 'Visual templates - Ready-to-use designs like Heart', 'light-popup' ); ?></li>
						<li><?php esc_html_e( 'GDPR checkbox - Built-in consent option', 'light-popup' ); ?></li>
						<li><?php esc_html_e( 'Custom CSS - Per-popup styling', 'light-popup' ); ?></li>
						<li><?php esc_html_e( 'Shortcode support - Place popups anywhere', 'light-popup' ); ?></li>
						<li><?php esc_html_e( 'Device control - Show on desktop, mobile, or both', 'light-popup' ); ?></li>
					</ul>
				</div>

				<div class="lp-about-page__section">
					<h2><?php esc_html_e( 'For Developers', 'light-popup' ); ?></h2>
					<p>
						<?php esc_html_e( 'Light Popup is extensible via WordPress filters. Third-party developers can register custom visual templates:', 'light-popup' ); ?>
					</p>
					<pre><code>add_filter( 'light_popup_templates', function( $templates ) {
    $templates['my-template'] = [
        'name'        => 'My Custom Template',
        'description' => 'A custom popup design.',
        'css_url'     => plugins_url( 'css/my-template.css', __FILE__ ),
        'settings'    => [
            'bg_color' => [
                'type'    => 'color',
                'label'   => 'Background color',
                'default' => '#ffffff',
                'css_var' => '--my-bg-color',
            ],
            'padding' => [
                'type'    => 'text',
                'label'   => 'Padding (px)',
                'default' => '24',
                'css_var' => '--my-padding',
                'suffix'  => 'px', // Appended to value
            ],
        ],
    ];
    return $templates;
} );</code></pre>
				</div>

				<div class="lp-about-page__section lp-about-page__section--meta">
					<p>
						<strong><?php esc_html_e( 'Version:', 'light-popup' ); ?></strong>
						<?php echo esc_html( LIGHT_POPUP_VERSION ); ?>
					</p>
					<p>
						<strong><?php esc_html_e( 'Author:', 'light-popup' ); ?></strong>
						<a href="https://robothead.ee" target="_blank" rel="noopener">Robothead</a>
					</p>
				</div>
			</div>
		</div>
		<?php
	}
}
