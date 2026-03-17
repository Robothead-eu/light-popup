<?php

namespace Robothead\LightPopup\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Menu {

	private Templates $templates;
	private Settings $settings;
	private About $about;

	public function __construct( Templates $templates, Settings $settings, About $about ) {
		$this->templates = $templates;
		$this->settings  = $settings;
		$this->about     = $about;
	}

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
	}

	public function add_menu(): void {
		add_menu_page(
			__( 'Light Popup', 'light-popup' ),
			__( 'Popups', 'light-popup' ),
			'manage_options',
			'edit.php?post_type=light_popup',
			'',
			'dashicons-media-interactive',
			58
		);

		add_submenu_page(
			'edit.php?post_type=light_popup',
			__( 'All Popups', 'light-popup' ),
			__( 'All Popups', 'light-popup' ),
			'manage_options',
			'edit.php?post_type=light_popup'
		);

		add_submenu_page(
			'edit.php?post_type=light_popup',
			__( 'Add New Popup', 'light-popup' ),
			__( 'Add New', 'light-popup' ),
			'manage_options',
			'post-new.php?post_type=light_popup'
		);

		add_submenu_page(
			'edit.php?post_type=light_popup',
			__( 'Templates', 'light-popup' ),
			__( 'Templates', 'light-popup' ),
			'manage_options',
			'light-popup-templates',
			[ $this->templates, 'render_page' ]
		);

		add_submenu_page(
			'edit.php?post_type=light_popup',
			__( 'Settings', 'light-popup' ),
			__( 'Settings', 'light-popup' ),
			'manage_options',
			'light-popup-settings',
			[ $this->settings, 'render_page' ]
		);

		add_submenu_page(
			'edit.php?post_type=light_popup',
			__( 'About', 'light-popup' ),
			__( 'About', 'light-popup' ),
			'manage_options',
			'light-popup-about',
			[ $this->about, 'render_page' ]
		);
	}
}
