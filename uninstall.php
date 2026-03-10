<?php
/**
 * Uninstall Light Popup
 *
 * Runs when the plugin is deleted from wp-admin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove cached targeting map.
delete_transient( 'light_popup_active_pages' );

// Note: light_popup CPT posts and meta are intentionally left intact on uninstall.
// Site owners may reinstall the plugin and should not lose their popup configurations.
