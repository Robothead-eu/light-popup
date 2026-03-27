# LightPopup

Displays popups in WordPress. Content is built in the block editor. 
Triggers, targeting, and frequency are configured per popup. 
No external requests, no tracking, no jQuery.

https://lightpopup.com

## What it does

- Trigger types: time delay, scroll depth, exit intent, click on CSS selector
- Targeting: all pages, specific post/page IDs, or by post type
- Frequency control: always, once per session, once per day/week/month, once forever
- Device filtering: desktop, mobile, or both
- Frontend JS is loaded only on pages with an active popup — not sitewide
- Visual templates with customizable colors and dimensions (ships with "Heart" template)
- GDPR consent checkbox with custom label per popup
- Custom CSS per popup
- Shortcode `[light_popup id="123"]` for click-triggered or embedded popups
- Extensible via `light_popup_templates` filter for custom templates

## What it does not do

- No analytics, tracking, or conversion metrics
- No A/B testing
- No built-in form builder — use any form plugin or block
- No external dependencies, CDN calls, or third-party services
- Does not delete popup data on uninstall (preserved for reinstalls)
- Does not support Classic Editor without enabling `force_block_editor` in settings

## Requirements

- WordPress 6.0+
- PHP 8.1+
- Composer (for autoloading)

## Installation

1. Download or clone into `wp-content/plugins/light-popup`
2. Run `composer install` in the plugin directory
3. Activate in WordPress admin

## Storage

- Post type: `light_popup`
- Post meta keys: `_lp_enabled`, `_lp_trigger_type`, `_lp_trigger_value`, 
  `_lp_targeting_type`, `_lp_targeting_ids`, `_lp_targeting_post_types`, 
  `_lp_frequency`, `_lp_template`, `_lp_template_settings`, `_lp_custom_css`, 
  `_lp_gdpr_checkbox`, `_lp_gdpr_checkbox_label`, `_lp_show_on_mobile`, 
  `_lp_show_on_desktop`, `_lp_close_on_backdrop`
- Plugin options: `light_popup_settings` in `wp_options`
- Targeting cache: `light_popup_active_pages` transient (12h TTL)

## Extending

Register custom visual templates via filter:
```php
add_filter( 'light_popup_templates', function( $templates ) {
    $templates['my-template'] = [
        'name'        => 'My Template',
        'description' => 'Custom popup design.',
        'css_url'     => plugins_url( 'css/my-template.css', __FILE__ ),
    ];
    return $templates;
} );
```

---

Built by [Robothead](https://robothead.eu)