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
if (!class_exists('LegacyRulesShippingFrameworkWooCommerce')) {
	require_once( plugin_dir_path( __FILE__ ) . '/legacy-rules_shipping_framework_woocommerce.php');
}

class LegacyRulesShippingFrameworkWooCommerceAdvanced extends LegacyRulesShippingFrameworkWooCommerce {
	
	static function getHelper() {
		static $helper = null;
		if (!$helper) {
			$helper = new LegacyRulesShippingFrameworkWooCommerceAdvanced();
			$helper->setup();
		}
		return $helper;
    }
	function __construct() {
		parent::__construct();
	}
	function isAdvanced() {
		return true;
	}
	protected function getOrderAddress ($cart, $method) {
		$data = parent::getOrderAddress($cart, $method);
		$address = $cart['destination'];
		$zip = isset($address['postcode'])?trim($address['postcode']):'';
		if (isset($zip) && $zip!='') {
			$data = array_merge($data, $this->getAddressZIP($zip));
		}
		return $data;
	}
	
	protected function createMethodRule ($r, $countries, $ruleinfo) {
		return new ShippingRule_Advanced($this, $r, $countries, $ruleinfo);
	}
	
	protected function addCustomCartValues ($cart, $products, $method, &$values) {
		$values['coupons'] = $cart['applied_coupons'];
		return $values;
	}
	

}
