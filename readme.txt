=== Light Popup ===
Contributors: robothead, dans-art
Tags: popup, modal, overlay, block editor
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 0.3.1
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight popup plugin for WordPress. Build content with the block editor. No external requests, no tracking, no jQuery.

== Description ==

Displays popups in WordPress. Content is built in the block editor. Triggers, targeting, and frequency are configured per popup.

**Features:**

* Trigger types: time delay, scroll depth, exit intent, click on CSS selector, URL parameter
* Targeting: all pages, specific post/page IDs, or by post type
* Frequency control: always, once per session, once per day/week/month, once forever
* Device filtering: desktop, mobile, or both
* Frontend JS is loaded only on pages with an active popup — not sitewide
* Visual templates with customizable colors and dimensions
* GDPR consent checkbox with custom label per popup
* Custom CSS per popup
* Shortcode `[light_popup id="123"]` for click-triggered or embedded popups
* Extensible via `light_popup_templates` filter for custom templates

== Installation ==

1. Upload the plugin folder to `wp-content/plugins/`
2. Activate the plugin in WordPress admin

== Changelog ==

= 0.3.1 =
* Fix: sanitize template settings input on save
* Fix: plugin check code quality issues
