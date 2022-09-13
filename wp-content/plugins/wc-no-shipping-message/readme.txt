=== WooCommerce No Shipping Message ===
Contributors: dangoodman
Tags: woocommerce, shipping, woocommerce shipping, woocommerce no shipping, woocommerce no shipping options were found
Requires PHP: 7.0
Requires at least: 4.0
Tested up to: 5.8
WC requires at least: 3.1
WC tested up to: 5.5
Stable tag: trunk

Replaces "No shipping options were found", "There are no shipping options available" and "No shipping method has been selected" messages on the cart and checkout pages with the provided text.


== Description ==

The plugins allows one to quickly customize the messages WooCommerce shows to the customer when shipping is not available:
1. "No shipping options were found" on the cart page ([screenshot](https://ps.w.org/wc-no-shipping-message/assets/screenshot-3.png))
2. "There are no shipping options available" on the checkout page ([screenshot](https://ps.w.org/wc-no-shipping-message/assets/screenshot-4.png))
3. "No shipping method has been selected" notice appearing after a checkout attempt ([screenshot](https://ps.w.org/wc-no-shipping-message/assets/screenshot-5.png))


&nbsp;
Customers see a no-shipping-options message when there are no delivery options configured for their order because they choose a destination you don't ship to, or have too many items in the cart, or meet another edge condition. Providing a custom message may be useful to explain why shipping is not available and what to do next. You can also provide an actual link to your contact page.


&nbsp;
Message examples:

- "You order exceeds 10 kg. Please [contact us](#) to get a quote."
- "Shipping to the selected country for orders under $100 is not available. Please add more items to your order or [contact us](#)."
- "[Product] is not available for shipping to the selected destination."
- "Unfortunately, we can't deliver your order. Please [contact us](#) for details."


== Changelog ==

= 2.0.11 =
* Tested with WordPress 5.8, WooCommerce 5.5.

= 2.0.10 =
* Tested with WooCommerce 5.3.

= 2.0.9 =
* Tested with WooCommerce 5.2.

= 2.0.8 =
* Tested with WordPress 5.7, WooCommerce 5.1.

= 2.0.7 =
* Tested with WooCommerce 5.0.

= 2.0.6 =
* Tested with WordPress 5.6, WooCommerce 4.8.

= 2.0.5 =
* Tested with WooCommerce 4.7.

= 2.0.4 =
* Tested with WooCommerce 4.6.

= 2.0.3 =
* Tested with WooCommerce 4.5.

= 2.0.2 =
* Tested with WooCommerce 4.4.

= 2.0.1 =
* Tested with WooCommerce 4.3.2.
* Tested with WordPress 5.5.

= 2.0.0 =
* Added an option to change the notice shown to the customer on a checkout without a shipping option selected.
* Allow setting no-shipping-available messages separately for the cart and checkout pages.
* Minimum required PHP version raised to 7.0.
* Minimum required WooCommerce version raised to 3.1.

= 1.3.2.6 =
* Tested with WooCommerce 4.1.

= 1.3.2.5 =
* Tested with WooCommerce 4.0, WordPress 5.4.

= 1.3.2.4 =
* Tested with WooCommerce 3.9.

= 1.3.2.3 =
* Update supported WordPress and WooCommerce versions.

= 1.3.2.2 =
* Update supported WordPress and WooCommerce versions.

= 1.3.2.1 =
* Update supported WordPress version to 5.1.

= 1.3.2 =
* Update supported WooCommerce and WordPress versions.

= 1.3.1 =
* Update supported WooCommerce version.

= 1.3.0 =
* Add an option to choose message type: Plain text or HTML.

= 1.2.0 =
* Fix 'Edit message' link to point to the correct settings page on Woocommerce 2.6+.

= 1.1.0 =
* Added 'Edit Message' link under the plugin name on the plugin listing.

= 1.0.0 =
* Initial version.


== Screenshots ==

1. Plugin settings
2. How that could look to the customer
3. Default message on the cart page
4. Default message on the checkout page
5. Default notice appearing after a checkout attempt
