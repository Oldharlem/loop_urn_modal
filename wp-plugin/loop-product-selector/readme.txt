=== Loop Product Selector ===
Contributors: loopbiotech
Tags: popup, mobile, product selector, ecommerce, urn selector
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A mobile-only popup that displays product selection options with an easy-to-use admin interface.

== Description ==

Loop Product Selector displays a beautiful, mobile-only popup that helps visitors choose between different products. Perfect for e-commerce sites with multiple product variants or categories.

= Features =

* **Mobile-Only Display** - Automatically detects mobile devices
* **One-Time Display** - Shows once per visitor using localStorage
* **Fully Configurable** - Easy admin interface, no coding required
* **Multiple Products** - Support for 1 or more products
* **Image Upload** - Built-in WordPress media uploader
* **Responsive Design** - Adapts to all mobile screen sizes
* **Loading Animation** - Professional loading spinner
* **Accessible** - Keyboard and screen reader support

= Perfect For =

* URN selection (pet vs. human)
* Product variant selection (size, color, type)
* Category navigation
* Service selection
* Any mobile product decision flow

= How It Works =

1. Visitor opens your site on a mobile device
2. Popup appears with your configured products
3. Visitor clicks on their choice
4. They're taken to the product page
5. Popup won't show again for that visitor

== Installation ==

= Via WordPress Admin =

1. Go to Plugins → Add New
2. Click Upload Plugin
3. Choose the zip file
4. Click Install Now
5. Activate the plugin
6. Go to Settings → Product Selector to configure

= Via FTP =

1. Upload `loop-product-selector` folder to `/wp-content/plugins/`
2. Activate through the 'Plugins' menu
3. Go to Settings → Product Selector to configure

== Frequently Asked Questions ==

= Does this work on desktop? =

No, it's designed to show only on mobile devices (screen width ≤ 768px by default). You can adjust this in settings.

= How do I test it? =

Use browser DevTools device emulation (F12 → Ctrl+Shift+M) and select a mobile device, then refresh your page.

= Can I show it again? =

Yes, either change the "Storage Key" in settings, or clear browser localStorage, or use incognito mode.

= How many products can I add? =

As many as you want! Works best with 2-4 products for mobile screens.

= Can I customize the styling? =

Yes, add custom CSS in Appearance → Customize → Additional CSS.

= Does it slow down my site? =

No, the popup is lightweight and only loads on mobile devices.

== Screenshots ==

1. Admin settings page with product configuration
2. Popup displayed on mobile device with two products
3. Product selection with image tiles
4. Loading animation when product is clicked

== Changelog ==

= 1.0.0 =
* Initial release
* Mobile-only product selector popup
* Admin configuration interface
* Multiple product support
* Image uploader integration
* Preview functionality
* Loading spinner animation
* Accessible design

== Upgrade Notice ==

= 1.0.0 =
Initial release of Loop Product Selector.
