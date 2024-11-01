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
require_once( plugin_dir_path( __FILE__ ) . '/../includes/rules_shipping_framework_woocommerce.php');

class LegacyRulesShippingFrameworkWooCommerce extends RulesShippingFrameworkWooCommerce {
	protected static $_method_ordering	= 'woocommerce_shipping_rules_ordering';
	
	static function getHelper() {
		static $helper = null;
		if (!$helper) {
			$helper = new LegacyRulesShippingFrameworkWooCommerce();
			$helper->setup();
		}
		return $helper;
    }

	protected function getMethodId($method) {
		return $method->ID;
	}

	protected function getMethodName($method) {
		return $method->post_title;
	}

	protected function parseMethodRules (&$method) {
		$rulesets = Legacy_Shipping_Rules_post_type::get_rulesets($method);
		foreach ($rulesets as $ruleset) {
			$this->parseMethodRule(
				/* Rules */    isset($ruleset['rules'])?$ruleset['rules']:'', 
				/* Countries */isset($ruleset['countries'])?$ruleset['countries']:array(), 
				/* Rule info */array(), 
				/* Method */   $method);
		}
	}

	/**
	* get_shipping_rules_methods function.
	*/
	static public function get_rule_shipping_methods() {
		$unsortedmethods = get_posts (array ('posts_per_page' => '-1', 'post_type' => 'shipping_rules'));
		$ordering = get_option( self::$_method_ordering, array() );
		$methods = array();
		foreach ($ordering as $o) {
			foreach ($unsortedmethods as $key=>$m) {
				if ($m->ID == $o) {
					$methods[$o] = $m;
					unset($unsortedmethods[$key]);
					break;
				}
			}
		}
		$methods = $methods + $unsortedmethods;
		return $methods;
	}
	
	static public function set_method_ordering($ordering) {
		update_option( self::$_method_ordering, $ordering );
	}

	protected function createMethodRule ($r, $countries, $ruleinfo) {
		return new ShippingRule($this, $r, $countries, $ruleinfo);
	}
	
	public function getUpgradeNagSettings() {
		$settings = array();
		if (!$this->isAdvanced()) {
			$settings['opentools_shippingbyrules_upgrade'] = array(
				'name' 		=> $this->__( 'Upgrade to the ADVANCED VERSION of the OpenTools Shipping by Rules plugin'),
				'type' 		=> 'opentools_shippingbyrules_upgrade',
				'link'		=> 'http://open-tools.net/woocommerce/advanced-shipping-by-rules-for-woocommerce.html',
			);
		}
		return $settings;
	}
	
	public function printUpgradeNagBox($settings) {
		include plugin_dir_path( __FILE__ ) . 'admin/html/html-upgrade-nag.php';
	}
	

}
