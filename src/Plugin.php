<?php

namespace Robothead\LightPopup;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Robothead\LightPopup\Admin\About;
use Robothead\LightPopup\Admin\Assets;
use Robothead\LightPopup\Admin\ClassicEditorOverride;
use Robothead\LightPopup\Admin\ListTable;
use Robothead\LightPopup\Admin\Menu;
use Robothead\LightPopup\Admin\Settings;
use Robothead\LightPopup\Admin\Templates;
use Robothead\LightPopup\Admin\SettingsMetaBox;
use Robothead\LightPopup\Admin\SettingsSaver;
use Robothead\LightPopup\Domain\PopupPostType;
use Robothead\LightPopup\Frontend\Loader;
use Robothead\LightPopup\Frontend\Shortcode;

class Plugin {

	public function boot(): void {
		( new PopupPostType() )->register();

		if ( is_admin() ) {
			$templates = new Templates();
			$settings  = new Settings();
			$about     = new About();

			$templates->register();
			$settings->register();
			( new Menu( $templates, $settings, $about ) )->register();
			( new SettingsMetaBox() )->register();
			( new SettingsSaver() )->register();
			( new Assets() )->register();
			( new ListTable() )->register();
			( new ClassicEditorOverride() )->register();
		}

		if ( ! is_admin() ) {
			$loader = new Loader();
			$loader->register();
			( new Shortcode( $loader ) )->register();
		}
	}
}
