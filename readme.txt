=== Royal Carrybee ===
Contributors: royaltechnologies
Tags: woocommerce, shipping, carrybee, courier, bangladesh, delivery, free
Requires at least: 5.6
Tested up to: 6.7
Requires PHP: 7.4
WC requires at least: 5.0
WC tested up to: 9.0
Stable tag: 26.07.11
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WooCommerce shipping integration with Carrybee courier service for Bangladesh. 100% Free and Open Source.

== Description ==

Royal Carrybee is a **100% FREE and Open Source** WooCommerce shipping integration plugin for [Carrybee](https://carrybee.com) courier service in Bangladesh.

There are **no hidden fees, no subscriptions, and no locked features**. Every merchant receives full, unlimited access to all capabilities.

= Key Features =

* **100% Free Forever** - Full access to all features without subscriptions or paywalls.
* **One-Click Order Dispatch** - "Sent to Carrybee" button in Orders table for instant booking.
* **Smart Address Auto-Fill** - Intelligent city and zone matching from customer checkout address.
* **WooCommerce Shipping Method** - Adds Carrybee as a shipping option at checkout.
* **Cascading Location Selection** - City, Zone, Area cascading dropdowns.
* **Automatic Order Creation** - Creates Carrybee parcel when order status changes.
* **Bulk Actions** - Create or Sync multiple Carrybee orders at once from WooCommerce.
* **Real-time Tracking** - Order status updates via webhooks.
* **Multi-Store Support** - Manage multiple pickup locations.
* **Sandbox Mode** - Safe testing environment with sandbox credentials.
* **Automatic Updates** - One-click GitHub auto-updates directly inside WordPress admin.

= Requirements =

* WordPress 5.6+
* WooCommerce 5.0+
* PHP 7.4+
* Carrybee API credentials

== Installation ==

1. Upload `royal-carrybee` folder to `/wp-content/plugins/` (or install `royal-carrybee.zip` via Plugins > Add New).
2. Activate through the 'Plugins' menu in WordPress.
3. Go to WooCommerce → Carrybee to configure your API credentials.
4. Add Carrybee to your shipping zone under WooCommerce → Settings → Shipping.

== Frequently Asked Questions ==

= Is Royal Carrybee completely free? =

Yes! Royal Carrybee is 100% free and open-source under the GPLv2 license. Every single feature—including 1-click dispatch, automatic address auto-fill, real-time webhook tracking, and bulk actions—is 100% free with no monthly fee, no activation charge, and no locked features.

= How do I get API credentials? =

Contact Carrybee at info@carrybee.com or call 09613 555 111 to obtain your merchant API credentials.

= Can I test before going live? =

Yes! Use sandbox mode with the provided test credentials in the plugin settings.

= Does it support COD? =

Yes, COD (Cash on Delivery) is fully supported.

= How do automatic updates work? =

Royal Carrybee checks GitHub for new releases. Whenever an update is available, you will receive an update notification right inside your WordPress admin Plugins screen, with support for WordPress automatic background updates.

== Screenshots ==

1. Settings page with API configuration
2. Checkout with location dropdowns
3. Order tracking meta box with smart auto-fill
4. 1-Click Sent to Carrybee quick action button

== Changelog ==

= 26.07.11 =
* Improved: Smart auto-matching uses WooCommerce State for Carrybee City, and WooCommerce City for Carrybee Zone to avoid spelling mistakes.
* Added: Address details lookup API fallback for cases when city/zone is completely missing.
* Added: Integrated GitHub Auto-Updater and WordPress native auto-updates support.
* Added: Official plugin banner and icon assets.
* Fixed: "Loading cities..." spinner getting stuck on connection failure in admin dashboard.

= 26.07.10 =
* Added: One-Click "Sent to Carrybee" quick action and button on WooCommerce Orders Table and Order panel.
* Added: Automatic server-side and client-side address matching for City & Zone.
* Added: Order address summary preview card and auto-filled state in admin metabox.

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 26.07.11 =
Added GitHub auto-updater, official assets, improved smart address matching, and address lookup fallback.
