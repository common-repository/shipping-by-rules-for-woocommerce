<?php
/**
 * Shipping by Rules generic helper class (WP/WooCommerce-specific)
 * Reinhold Kainhofer, Open Tools, office@open-tools.net
 * @copyright (C) 2012-2016 - Reinhold Kainhofer
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

if ( !defined( 'ABSPATH' ) ) { 
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' );
}
require_once( plugin_dir_path( __FILE__ ) . '/../library/rules_shipping_framework.php');

class RulesShippingFrameworkWooCommerce extends RulesShippingFramework {
	protected static $_method_ordering	= 'woocommerce_shipping_rules_ordering';
	
	function __construct() {
		parent::__construct();
		load_plugin_textdomain('woocommerce-shipping-by-rules', false, basename( dirname( __FILE__ ) ) . '/languages' );
		$this->registerScopings(array(
			"categories"    => 'categories',
			"subcategories" => 'subcategories',
			"products"      => 'products',
			"skus"          => 'products',
			"vendors"       => 'vendors',
			"shippingclasses" => 'shippingclasses',
		));
	}
	static function getHelper() {
		static $helper = null;
		if (!$helper) {
			$helper = new RulesShippingFrameworkWooCommerce();
			$helper->setup();
		}
		return $helper;
    }
	function urlPath($type, $file) {
		return plugins_url('library/' . $type . '/' . $file, __FILE__);
    }
	
	function isAdvanced() {
		return false;
	}
	function getCustomFunctions() {
		// Let other plugins add custom functions! 
		// The opentools_shipping_by_rules_replacements filter is expected to return an array of the form:
		//   array ('functionname1' => 'function-to-be-called',
		//          'functionname2' => array($classobject, 'memberfunc')),
		//          ...);
		return apply_filters( 'opentools_shipping_by_rules_replacements', array());
	}
	
	public function printMessage($message, $type) {
		// Keep track of warning messages, so we don't print them twice:
		global $printed_messages;
		if (!isset($printed_messages))
			$printed_messages = array();
		if ($type == "debug") {
			if ( true === WP_DEBUG ) {
				if ( is_array( $message ) || is_object( $message ) ) {
					error_log( print_r( $message, true ) );
				} else {
					error_log( $message );
				}
			}
		} elseif (!in_array($message, $printed_messages)) {
			switch ($type) {
				case 'error':
				case 'warning':
					wc_add_notice( $message, 'error'); break;
				case 'message':
					wc_add_notice( $message, 'success'); break;
				case 'notice':
				default:
					wc_add_notice( $message, 'notice'); break;
			}
			$printed_messages[] = $message;
		}
	}

	/**
	 * HELPER FUNCTIONS, WooCommerce-specific
	 */
	public function __($string) {
		$args = func_get_args();
		$string = $this->readableString($string);
		$string = __($string, 'opentools-shippingrules');
		if (count($args)>1) {
			$args[0] = $string;
			return call_user_func_array("sprintf", $args);
		} else {
			return $string;
		}
	}

	protected function getCartProducts($package, $method) {
		return $package['contents'];
	}
	
	protected function getMethodId($method) {
		return $method->get_rate_id();
	}

	protected function getMethodName($method) {
		return $method->title;
	}

	// TODO: Legacy
	protected function parseMethodRules (&$method) {
		return $this->parseMethodRule(
			/* Rules */    isset($method->rules)?$method->rules:'',
			/* Countries */array(),
			/* Rule info */array(),
			/* Method */   $method);
	}

	/**
	 * Functions to calculate the cart variables:
	 *   - getOrderCounts($cart, $products, $method)
	 *   - getOrderDimensions
	 */
	/** Functions to calculate all the different variables for the given cart and given (sub)set of products in the cart */
	protected function getOrderCounts ($cart, $products, $method) {
		$counts = array(
			'articles'    => 0,
			'products'    => count($products),
			'quantity'    => 0,
			'minquantity' => 9999999999,
			'maxquantity' => 0,
		);

		foreach ($products as $product) {
			$counts['articles']   += $product['quantity'];
			$counts['maxquantity'] = max ($counts['maxquantity'], $product['quantity']);
			$counts['minquantity'] = min ($counts['minquantity'], $product['quantity']);
		}
		$counts['quantity'] = $counts['articles'];
		return $counts;
	}

	protected function getOrderDimensions ($cart, $products, $method) {
		/* Cache the value in a static variable and calculate it only once! */
		$dimensions=array(
			'volume' => 0,
			'maxvolume' => 0, 'minvolume' => 99999999,
			'maxlength' => 0, 'minlength' => 99999999, 'totallength' => 0,
			'maxwidth'  => 0, 'minwidth' => 99999999,  'totalwidth'  => 0,
			'maxheight' => 0, 'minheight' => 99999999, 'totalheight' => 0,
		);
		foreach ($products as $product) {
	
			$l = $product['data']->get_length();
			$w = $product['data']->get_width();
			$h = $product['data']->get_height();

			if (is_numeric($l) && is_numeric($w) && is_numeric($h)) {
				$volume = $l * $w * $h;
				$dimensions['volume'] += $volume * $product['quantity'];
				$dimensions['maxvolume'] = max ($dimensions['maxvolume'], $volume);
				$dimensions['minvolume'] = min ($dimensions['minvolume'], $volume);
			}
			if (is_numeric($l)) {
				$dimensions['totallength'] += $l * $product['quantity'];
				$dimensions['maxlength'] = max ($dimensions['maxlength'], $l);
				$dimensions['minlength'] = min ($dimensions['minlength'], $l);
			}
			if (is_numeric($w)) {
				$dimensions['totalwidth'] += $w * $product['quantity'];
				$dimensions['maxwidth'] = max ($dimensions['maxwidth'], $w);
				$dimensions['minwidth'] = min ($dimensions['minwidth'], $w);
			}
			if (is_numeric($h)) {
				$dimensions['totalheight'] += $h * $product['quantity'];
				$dimensions['maxheight'] = max ($dimensions['maxheight'], $h);
				$dimensions['minheight'] = min ($dimensions['minheight'], $h);
			}
		}

		return $dimensions;
	}
	
	protected function getOrderWeights ($cart, $products, $method) {
		$dimensions=array(
			'weight' => 0,
			'maxweight' => 0, 'minweight' => 9999999999,
		);
		foreach ($products as $product) {
			$w = $product['data']->get_weight();
			if (is_numeric($w)) {
				$dimensions['maxweight'] = max ($dimensions['maxweight'], $w);
				$dimensions['minweight'] = min ($dimensions['minweight'], $w);
				$dimensions['weight'] += $w * $product['quantity'];
			}
		}
		return $dimensions;
	}
	
	protected function getOrderListProperties ($cart, $products, $method) {
// $this->warning("<pre>Cart: ".print_r($cart,1)."</pre>");
		$categories = array();
		$skus = array();
		$tags = array();
		$shipping_classes = array();
		foreach ($products as $product) {
			$id = $product['data']->get_id();
			if ($product['data']->get_sku()) {
				$skus[] = $product['data']->get_sku();
			}
			foreach (wc_get_product_terms( $id, 'product_cat') as $c) {
				$categories[] = urldecode($c->slug);
			}
			foreach (wc_get_product_terms( $id, 'product_tag') as $c) {
				$tags[] = urldecode($c->slug);
			}
			$shipclass = $product['data']->get_shipping_class();
			if ($shipclass) {
				$shipping_classes[] = $shipclass;
			}
		}
		$skus = array_unique($skus);
		$categories = array_unique($categories);
		$tags = array_unique($tags);
		$shipping_classes = array_unique($shipping_classes);
		

		$data = array (
			'skus'       => $skus, 
			'categories' => $categories,
			'tags'       => $tags,
			'shippingclasses' => $shipping_classes,
		);
		
		// THIRD-PARTY SUPPORT
		// "WC Vendors"  support (vendors stored as post author)
		if (class_exists("WC_Vendors")) {
			$vendorids = array();
			foreach ($products as $product) {
				// Variations inherit the vendor from their parent product 
				if (!isset($product['variation_id'])) {
					// "Normal" product, not a variation
					$vendorids[] = $product['data']->post->post_author;
				} else {
					// A variation => load the parent product instead and use its vendor
					$vendorids[] = get_post_field('post_author', $product['product_id']);
				}
			}
			$data['vendorids'] = array_unique($vendorids);
			
			$vendors = array(); // Requires "WC Vendors" or "WooThemes Product Vendors" plugin
			$vendornames = array();
			foreach ($data['vendorids'] as $v) {
				$vnd = get_user_by('id', $v);  // Get user name by user id
				if (is_object($vnd)) {
					$vendornames[] = $vnd->display_name;
					$vendors[] = $vnd->user_login;
				}
			}
			$data['vendornames'] = array_unique($vendornames);
			$data['vendors'] = array_unique($vendors);
		}
		
		// "Dokan Marketplace"  support (vendors stored as post author)
		if (class_exists("WeDevs_Dokan")) {
			$vendorids = array();
			foreach ($products as $product) {
				// Variations inherit the vendor from their parent product 
				if (!isset($product['variation_id'])) {
					// "Normal" product, not a variation
					$vendorids[] = $product['data']->post->post_author;
				} else {
					// A variation => load the parent product instead and use its vendor
					$vendorids[] = get_post_field('post_author', $product['product_id']);
				}
			}
			$data['vendorids'] = array_unique($vendorids);
			
			$vendors = array(); // Requires "WC Vendors" or "WooThemes Product Vendors" plugin
			$vendornames = array();
			foreach ($data['vendorids'] as $v) {
				$vnd = get_user_by('id', $v);  // Get user name by user id
				if (is_object($vnd)) {
					$vendornames[] = $vnd->display_name;
					$vendors[] = $vnd->user_login;
				}
			}
			$data['vendornames'] = array_unique($vendornames);
			$data['vendors'] = array_unique($vendors);
		}
		
		// "WooThemes Vendor Products" <2.0 support (vendors stored in its own taxonomy)
		if (class_exists("WooCommerce_Product_Vendors") && function_exists("get_product_vendors")) {
			$vendors = array();
			$vendornames = array();
			$vendorids = array();
			// The plugin provides its own function to retrieve the vendor for a product
			foreach ($products as $product) {
				foreach (get_product_vendors($product['data']->get_id()) as $vendor) {
					$vendors[] = urldecode($vendor->slug);
					$vendornames[] = $vendor->title;
					$vendorids[] = $vendor->ID;
				}
			}
			$data['vendors'] = array_unique($vendors);
			$data['vendornames'] = array_unique($vendornames);
			$data['vendorids'] = array_unique($vendorids);
		}
		// "WooThemes Vendor Products" >=2.0 support (vendors stored in its own taxonomy)
		if (class_exists("WC_Product_Vendors") && method_exists("WC_Product_Vendors_Utils", "get_vendor_id_from_product")) {
			$vendors = array();
			$vendornames = array();
			$vendorids = array();
			// The plugin provides its own function to retrieve the vendor for a product
			foreach ($products as $product) {
				$vndid = WC_Product_Vendors_Utils::get_vendor_id_from_product($product['data']->get_id());
				if ($vndid>0) {
					$vendor = WC_Product_Vendors_Utils::get_vendor_data_by_id($vndid);
					$vendors[] = urldecode($vendor["slug"]);
					$vendornames[] = $vendor["name"];
					$vendorids[] = $vendor["term_id"];
				}
			}
			$data['vendors'] = array_unique($vendors);
			$data['vendornames'] = array_unique($vendornames);
			$data['vendorids'] = array_unique($vendorids);
		}
		
		// "YITH WooCommerce Multi Vendor" support (vendors stored in its own taxonomy)
		if (function_exists("yith_get_vendor")) {
			$vendors = array();
			$vendornames = array();
			$vendorids = array();
			// The plugin provides its own function to retrieve the vendor for a product
			foreach ($products as $product) {
				$vendor = yith_get_vendor($product['data']->get_id(), 'product');
// $this->printWarning("<pre>vendor: ".print_r($vendor,1)."</pre>");
				if ($vendor->is_valid()) {
					$vendors[] = urldecode($vendor->l);
					$vendornames[] = $vendor->name;
					$vendorids[] = $vendor->term_id;
				}
			}
			$data['vendors'] = array_unique($vendors);
			$data['vendornames'] = array_unique($vendornames);
			$data['vendorids'] = array_unique($vendorids);
		}
		
		
		// END THIRD-PARTY SUPPORT
		
		
		return $data;
	}
	
	protected function getOrderAddress ($cart, $method) {
		$address = $cart['destination'];
		$zip = isset($address['postcode'])?trim($address['postcode']):'';
		$data = array(
			'zip'      => $zip,
			'postcode' => $zip,
			'zip1'     => substr($zip,0,1),
			'zip2'     => substr($zip,0,2),
			'zip3'     => substr($zip,0,3),
			'zip4'     => substr($zip,0,4),
			'zip5'     => substr($zip,0,5),
			'zip6'     => substr($zip,0,6),
			'zipnumeric'  => preg_replace('/[^0-9]/', '', $zip),
			'zipalphanum' => preg_replace('/[^a-zA-Z0-9]/', '', $zip),
			'city'     => trim($address['city']),
			'country'  => trim($address['country']),
			'state'    => trim($address['state']),
			'address1' => trim($address['address']),
			'address2' => trim($address['address_2']),
		);
		/* Get the user from the package information and extract further information about the buyer */
		$user = $cart['user'];
		$data['userid'] = $user['ID'];
		$data['userroles'] = array();
		$data['username'] = '';
		$data['first_name'] = '';
		$data['last_name'] = '';
		$data['email'] = '';
		if ($user['ID']>0) {
			$userinfo = get_userdata($user['ID']);
			$data['userroles'] = $userinfo->roles;
			$data['username'] = $userinfo->user_login;
			
			$data['first_name'] = $userinfo->first_name;
			$data['last_name'] = $userinfo->last_name;
		
			$data['email'] = isset($address['email'])?$address['email']:'';
		// TODO: Extract more user fields!
/**		
		$data['company'] = isset($address['company'])?$address['company']:'';
		$data['title'] = isset($address['title'])?$address['title']:'';
		$data['middle_name'] = isset($address['middle_name'])?$address['middle_name']:'';
		$data['phone1'] = isset($address['phone_1'])?$address['phone_1']:'';
		$data['phone2'] = isset($address['phone_2'])?$address['phone_2']:'';
		$data['fax'] = isset($address['fax'])?$address['fax']:'';
*/
		}
		// The country check needs the countryid variable, so duplicate from country:
		$data['countryid'] = $data['country'];

		return $data;
	}
	
	protected function getOrderPrices ($cart, $products, /*$cart_prices, */$method) {
		$data = array(
			'total'       => 0,
			'subtotal'    => 0,
			'taxtotal'    => 0,
			'taxsubtotal' => 0,
			'cost'        => 0,
		);
		// Calculate the prices from the individual products!
		// Possible problems are discounts on the order total
		foreach ($products as $product) {
			$data['total']                     += $product['line_total'];
			$data['subtotal']                  += $product['line_subtotal'];
			$data['taxtotal']                  += $product['line_tax'];
			$data['taxsubtotal']               += $product['line_subtotal_tax'];
			$data['cost']                      += $product['line_total'] + $product['line_tax'];
		}
		$data['amount'] = $data['cost'];
		$data['amountwithtax'] = $data['cost'];
		return $data;
	}

	/** Allow child classes to add additional variables for the rules or modify existing one
	 */
	protected function addCustomCartValues ($cart, $products, $method, &$values) {
	}
	protected function addPluginCartValues($cart, $products, $method, &$values) {
		return apply_filters( 'opentools_shipping_by_rules_get_cart_values', array(&$values, $cart, $products, $method));
	}

	/** Filter the given array of products and return only those that belong to the categories, manufacturers, 
	*  vendors or products given in the $filter_conditions. The $filter_conditions is an array of the form:
	*     array( 'products'=>array(....), 'categories'=>array(1,2,3,42))
	*  Notice that giving an empty array for any of the keys means "no restriction" and is exactly the same 
	*  as leaving out the entry altogether
	*/
	public function filterProducts($products, $filter_conditions) {
		$result = array();
		
		// For the subcategories scoping we need all subcategories of the conditions:
		$subcategories = array();
		if (isset($filter_conditions['subcategories']) && !empty($filter_conditions['subcategories'])) {
			foreach ($filter_conditions['subcategories'] as $catslug) {
				// Get the term itself (we have only the slug!), DB stores slugs as URL-encoded!
				$cat = get_term_by('slug', urlencode($catslug), 'product_cat');
				if (empty($cat))
					continue;
				$subcategories[] = urldecode($cat->slug);
				// Get the list of all subcategories of the given categories
				$args=array('child_of' => $cat->term_id, 'hierarchical' => 1);
				foreach (get_terms( 'product_cat', $args) as $subcat) {
					$subcategories[] = urldecode($subcat->slug);
				}
			}
			$subcategories = array_unique($subcategories);
		}
		
		// Now filter out all products that do not match the conditions
		foreach ($products as $p) {
			$prodcategories = array();
			foreach (wc_get_product_terms( $p['data']->id, 'product_cat') as $cat) {
				$prodcategories[] = urldecode($cat->slug);
			}
			if (!empty($filter_conditions['products']) && !in_array($p['data']->get_sku(), $filter_conditions['products']))
				continue;
			if (!empty($filter_conditions['categories']) && count(array_intersect($filter_conditions['categories'], $prodcategories))==0)
				continue;
			if (!empty($filter_conditions['subcategories']) && count(array_intersect($subcategories, $prodcategories))==0)
				continue;
			
			if (!empty($filter_conditions['shippingclasses'])) {
				$shipclass = $p['data']->get_shipping_class();
				if ($shipclass) {
					if (!in_array($shipclass, $filter_conditions['shippingclasses'] ))
						continue;
				} else {
					// No shipping class set for product, but scoping has a valid shipping clase => filter out product
					continue;
				}
			}
			
			if (!empty($filter_conditions['vendors'])) {
				// Collect all vendors (ids and slug/login_name - PLUGIN-specific!)
				// for the current product. If any of them is in the vendor conditions
				// list, this product should not be filtered out!
				$vnd_props = array();
				
				// THIRD-PARTY SUPPORT
				// "WC Vendors" and "Dokan Marketplace" support (vendors stored as post author)
				if (class_exists("WC_Vendors") || class_exists("WeDevs_Dokan")) {
					$vendor = $p['data']->post->post_author;
					$vnd = get_user_by('id', $vendor);  // Get user name by user id
					$vnd_props[] = $vendor;
					$vnd_props[] = $vnd->user_login;
				}

				// "WooThemes Product Vendors"<2.0 support (vendors stored in its own taxonomy)
				if (class_exists("WooCommerce_Product_Vendors") && function_exists("get_product_vendors")) {
					foreach (get_product_vendors($p['data']->id) as $vendor) {
						$vnd_props[] = urldecode($vendor->slug);
					}
				}

				// "WooThemes Vendor Products">=2.0 support (vendors stored in its own taxonomy)
				if (class_exists("WC_Product_Vendors") && method_exists("WC_Product_Vendors_Utils", "get_vendor_id_from_product")) {
					$vndid = WC_Product_Vendors_Utils::get_vendor_id_from_product($p['data']->get_id());
					$vendor = WC_Product_Vendors_Utils::get_vendor_data_by_id($vndid);
					$vnd_props[] = urldecode($vendor["slug"]);
				}

				// "YITH WooCommerce Multi Vendor" support (vendors stored in its own taxonomy)
				if (function_exists("yith_get_vendor")) {
					$vendor = yith_get_vendor($p['data']->id, 'product');
					if ($vendor->is_valid()) {
						$vnd_props[] = urldecode($vendor->slug);
					}
				}
		
				// END THIRD-PARTY SUPPORT

				// Check if any of the vendor properties is matched by the conditions; If not => skip product
				if (count(array_intersect($vnd_props, $filter_conditions['vendors']))==0)
					continue;
			}
			$result[] = $p;
		}
		return $result;
	}
	
	protected function createMethodRule ($r, $countries, $ruleinfo) {
		return new ShippingRule($this, $r, $countries, $ruleinfo);
	}
	
	public function getUpgradeNagSettings() {
		$settings = array();
// 		if (!$this->isAdvanced()) {
			$settings['opentools_shippingbyrules_upgrade'] = array(
				'name' 		=> $this->__( 'Upgrade to the ADVANCED VERSION of the OpenTools Shipping by Rules plugin'),
				'type' 		=> 'opentools_shippingbyrules_upgrade',
				'link'		=> 'http://open-tools.net/woocommerce/advanced-shipping-by-rules-for-woocommerce.html',
			);
// 		}
		return $settings;
	}
	
	public function printUpgradeNagBox($settings) {
		include plugin_dir_path( __FILE__ ) . 'admin/html/html-upgrade-nag.php';
	}
	

}
