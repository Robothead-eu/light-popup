<?php
/**
 * Plugin Name: Light Popup
 * Description: Lightweight, privacy-first WordPress popup plugin.
 * Version:     0.2.1
 * Author:      Robothead
 * Author URI:  https://lightpopup.com
 * Text Domain: light-popup
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LIGHT_POPUP_VERSION', '0.2.1' );
define( 'LIGHT_POPUP_FILE', __FILE__ );
define( 'LIGHT_POPUP_DIR', plugin_dir_path( __FILE__ ) );
define( 'LIGHT_POPUP_URL', plugin_dir_url( __FILE__ ) );

require_once LIGHT_POPUP_DIR . 'vendor/autoload.php';

( new \Robothead\LightPopup\Plugin() )->boot();
