WP App Store for Product Integration
====================================

The purpose of the code in this repo is to integrate the WP App Store into your theme or plugin and enable you to sell addons for that theme or plugin. This particular code was built specifically for integration with Modern Tribe's Events Calendar plugin. However, it can easily be adapted to fit into your theme or plugin as described below.

Adapting for Your Theme or Plugin
---------------------------------

1. Open `wp-app-store-integration.php`
1. Replace the value of `$parent_sku` with the SKU of the product you are integrating this into
1. Replace the value of `$affiliate_id` with your affiliate ID
1. Replace the value of `$parent_slug` with the slug of the top level menu item you want to add this to
1. Replace the value of `$addons_menu_title` with the title of the page
1. Replace the value of `$this->slug` with your own prefix instead of `tribe-`
1. Replace instances of `Tribe_` and `tribe_` with your own prefix
1. Open `classes/wp-app-store.php`
1. Replace instances of `Tribe_` and `tribe_` with your own prefix

Integration
-----------

1. Copy all of the files in this repo to your theme or plugin directory
1. Follow the instructions above to adapt the settings to your own theme or plugin if you haven't already
1. Add `include "/path/to/wp-app-store-integration.php"` to your theme's `functions.php` file or for a plugin, your primary plugin file

Change Log
----------

### 0.1 (2012-07-18)
 * Initial release
