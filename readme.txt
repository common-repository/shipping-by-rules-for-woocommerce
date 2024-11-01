=== Shipping by Rules for WooCommerce ===
Contributors: opentools
Tags: WooCommerce, Shipment, Shipping, Rules shipping
Requires at least: 4.0
Tested up to: 4.9.4
Stable tag: 2.0.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl.html

Describe (even complex) shipping costs with simple general rules on the order properties (amount, postcode, weight, #products and/or articles etc.).


== Description ==
Determine shipping costs according to general conditions (bounds on the order properties). Shipping costs can depend on e.g.:

* Total amount of the order
* Total weight of the order
* Number of articles or different products in the order
* Volume or minimal and maximal extensions of the products
* Postal code of the delivery address (the Advanced version also supports alphanumeric postal codes from the UK, Canana and Netherlands)
* Coupon Code (Advanced version only)

The plugin exists in two different versions, this free version, which supports fixed bounds for all properties, and a paid version, which allows all conditions and shipping rules to contain arbitrary mathematical expressions (like an OR operator, multiplication, addition, subtraction, functions like rounding etc.). See the documentation for all the differences. 

Each rule is described as one line of text with an easy structure (semicolons separate the parts of the rule). For example:

`Name=Free Shipping; 100<=Amount; 0
Name=Domestic Small; Articles<5; Amount<100; Shipping=1.50
Name=Domestic Standard; Amount<100; Shipping=3.50`

This set of rules describes three shipping costs: Orders of 100€ and more are free, otherwise orders with less than five articles have shipping costs of 1.5€, all others 3.50€.

= Limitations and common misconceptions =

* The plugin is not designed to calculte shipping costs on a per-product level (e.g. it is not possible that Article A has shipping costs of 3€, Article B 5€ and if you order both you pay 8€ shipping)
* The plugin allows only limited support for category-based shipping. You can only find out if an article from a particular category is in the order, but NOT how many articles from a given category (e.g. it is not possible to have all articles from Category X ship for 3€ and all articles from Category Y ship for 5€)
* The plugin does NOT sum the results of all rules, but uses the FIRST matching rule it finds.
* The plugin does NOT use the lowest result of all rules, but it uses the FIRST matching rule it finds. (In particular, if you want to provide free shipping under certain conditions, you usually need to place the rule for free shipping FIRST rather than last, because the last rule will only be used if none of the other rules matches).

For the full documentation of the Shipping by Rules plugin for WooCommerce see:
http://open-tools.net/documentation/advanced-shipping-by-rules-for-woocommerce.html



== Installation ==

1. To install the plugin, either:
	1. use WordPress' plugin manager to find it in the WordPress plugin directory and directly install it from the WP plugin manager, or
	1. use WordPress' plugin manager to upload the plugin's zip file.
1. After installation, activate the plugin through the 'Plugins' menu in WordPress
1. Go to WooCommerce's shipment configuration page, open the "Shipping by Rules" method and set up a your rule-based methods there



== Frequently Asked Questions ==

= Where can I get further rule examples or help? =

Please see our support forum at http://open-tools.net/forum/. It might also be a good idea to check the support forum of the Shipping by Rules plugin for VirtueMart. The basic concepts of these plugins are identical, so most solutions for VirtueMart will also work in the WooCommerce shipping plugin.


== Screenshots ==

1. The shipping method configuration page. Here you can set up multiple shipping methods (think carriers) using the Shipping by Rules plugin. Each method can have an arbitrary number of rulesets and rules. Each method can independently offer a shipping rate.
2. The shipping method edit page. Give the shipping method a title that will be shown in the cart and the invoice. You can add an arbitrary number of rulesets, each applying to different countries. Each ruleset can have an arbitrary number of rules of the form 'Name="Name to be displayed to the user"; Amount<14; Weight>5; Shipping=9.9'. Do not forget to press "Update" to save your changes. The rulesets can be reordered by simply dragging them.
3. An example of two shipping methods offered in the cart. Notice that the displayed shipping name has the form "Shipping Method name (Optional Rule name)".
4. If a matching rule has NoShipping set as shipping costs, its name will be displayed as a warning message to the user and the method will not offer any shipping.
5. The plugin's entry in  WordPress' plugin management page contains direct links to the configuration page, to the documentation and to the support forum.

== Changelog ==

= 2.0.6 =
* Add function evaluate_for_shippingclasses 

= 2.0.5 =
* Basic support for Dokan Marketplace (vendors lists are properly filled)

= 2.0.4 =
* Compatibility with WC vendors in combination with product variations
* Fix PHP warnings

= 2.0.3 = 
* Fix warning when products have no length/width/height assigned (use 0 as default)
* Fix tiny incompatibility with PHP 7

= 2.0.2 =
* Support for WooCommerce Product Vendors >=2.0
* Support UK postcodes if second part is left out by the user altogether

= 2.0.1 = 
* No changes (installation package was missing files, so a version increase was required)

= 2.0 =
* Transition to WooCommerce's Shipping Zones. Existing methods (not using zones) are preserved and legacy mode is enabled.
* Update for full compatibility with WooCommerce 3.0
* Fix non-latin category SLUGs

= 1.2.8 =
* Fix issues with UK_* variables when no address is entered yet

= 1.2.7 =
* Add debug messages to the update system (disabled by default)

= 1.2.6 =
* Add message functionality (Error=..., Warning=..., Message=... rule parts)

= 1.2.5 =
* Add variables username, first_name, last_name, email
* Add list variable userroles (advanced version only)
* Fix issue with debug_variables

= 1.2.4 =
* Fix incompatibility with Cash on Delivery (returned id should be prefixed with the method ID)
* Fix opentools_shipping_by_rules_get_cart_values filter using indefined argument

= 1.2.3 =
* Fix update credentials input 
* Fix PHP error when both versions of the plugin are enabled
* Fix fatal error:  Call to a member function isNoShipping() on a non-object...

= 1.2.2 =
* Fix warning about function signature mismatch
* Fix Coupons variable not being available (advanced version)

= 1.2.1 =
* Fix for warning when a rule contained only spaces

= 1.2 =
* Add support for "WC Vendors" and for "WooThemes Product Vendors" (new variable "Vendors", new function "evaluate_for_vendors")

= 1.1.1 =
* Fix for PHP 5.3
* Fix for evaluate_for_XXX functions (advanced version)

= 1.1 =
* Add time variables, Quantity/MaxQuantity/MinQuantity

= 1.0 =
* Initial release

== Upgrade Notice ==

To install the Shipping by Rules plugin for WooCommerce, proceed as described in the Installation section.
Upgrades to new versions are automatically offered in the WordPress plugin page.
