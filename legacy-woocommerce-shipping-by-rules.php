<?php
/**
 * Copyright (C) 2016 Reinhold Kainhofer
 *
 *     This file is part of WooCommerce Shipping By Rules,
 *     a plugin for WordPress and WooCommerce.
 *
 *     WooCommerce Shipping By Rules is free software:
 *     You can redistribute it and/or modify it under the terms of the
 *     GNU General Public License as published by the Free Software
 *     Foundation, either version 3 of the License, or (at your option)
 *     any later version.
 *
 *     This software is distributed in the hope that
 *     it will be useful, but WITHOUT ANY WARRANTY; without even the
 *     implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 *     PURPOSE. See the GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with WordPress. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class Legacy_WooCommerce_Shipping_By_Rules.
 *
 * Main Shipping by Rules class (legacy version), add filters and handling all other files.
 *
 * @class       Legacy_WooCommerce_Shipping_By_Rules
 * @author      Reinhold Kainhofer
 */
class Legacy_WooCommerce_Shipping_By_Rules {
	public $version = '2.0.3';
	private static $instance;

	public function __construct() {
		if ( ! function_exists( 'is_plugin_active_for_network' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		// Check if WooCommerce is active
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :
			if ( ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) :
				return;
			endif;
		endif;
		$this->init();
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init() {
		$this->hooks();
		$this->load_textdomain();
		$this->update();

		if (!class_exists("LegacyRulesShippingFrameworkWooCommerce")) {
			require_once( plugin_dir_path( __FILE__ ) . 'legacy-includes/legacy-rules_shipping_framework_woocommerce.php');
		}
		$this->helper = LegacyRulesShippingFrameworkWooCommerce::getHelper();
		
		// Shipping method post type definition:
		if (!class_exists("Legacy_Shipping_Rules_post_type")) {
			require_once plugin_dir_path( __FILE__ ) . 'legacy-includes/legacy-rules-shipping-post-type.php';
		}
		$this->post_type = new Legacy_Shipping_Rules_post_type($this->helper);
		add_filter( 'plugin_action_links_' . dirname(plugin_basename( __FILE__ )) . '/woocommerce-shipping-by-rules.php',		array( &$this, 'shippingbyrules_add_settings_link' ) );
	}

	/**
	 * Add settings link to plugins page
	 */
	public function shippingbyrules_add_settings_link( $links ) {
		$link = '<a href="admin.php?page=wc-settings&tab=shipping&section=legacy-shipping_by_rules">'. $this->helper->__( 'Settings (legacy)' ) . '</a>';
		array_unshift( $links, $link );
		return $links;
	}
	

	public function update() {
		$db_version = get_option( 'shipping_by_rules_plugin_version', $this->version );
		// Stop current version is up to date
		if ( $db_version >= $this->version ) :
			return;
		endif;
		// Update functions come here:
		// From version 1.x.x to 1.x.y
		// From version 1.x.y to 1.x.z
		update_option( 'shipping_by_rules_plugin_version', $this->version );
	}

	public function hooks() {
		add_action( 'woocommerce_shipping_init', array( $this, 'shipping_by_rules_init' ) );
		add_action( 'woocommerce_shipping_methods', array( $this, 'shipping_by_rules_add_shipping_method' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'shipping_by_rules_admin_enqueue_scripts' ) );
	}

	public function load_textdomain() {
		load_plugin_textdomain('opentools-shippingrules', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	public function shipping_by_rules_init() {
		if (!class_exists('Legacy_Shipping_by_Rules')) {
			require_once plugin_dir_path( __FILE__ ) . 'legacy-includes/legacy-rules-shipping-method.php';
		}
		$this->rule_shipping_method = new Legacy_Shipping_by_Rules();
	}

	public function shipping_by_rules_add_shipping_method( $methods ) {
		if ( class_exists( 'Legacy_Shipping_by_Rules' ) ) :
			$methods['legacy-shipping_by_rules'] = 'Legacy_Shipping_by_Rules';
		endif;
		// TODO: Figure out a way to add each shipping by rules method as a
		// separate WooCommerce shipping method (ie. their order can be
		// defined in the WC configuration and not only in the Shipping by
		// Rules Method configuration screen.
		return $methods;
	}

	public function shipping_by_rules_admin_enqueue_scripts() {
		// For some strange reason, WC does not define the select2.css style...
		$assets_path          = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';
		wp_enqueue_style( 'select2', $assets_path . 'css/select2.css' );

		wp_enqueue_style( 'legacy_shipping_by_rules-style', plugins_url( 'assets/css/legacy-admin-styles.css', __FILE__ ), array('select2'), $this->version );
		wp_enqueue_style( 'shipping_by_rules-style', plugins_url( 'assets/css/admin-styles.css', __FILE__ ), array('select2'), $this->version );
		wp_enqueue_script( 'legacy-shipping-by-rules-config', plugins_url( 'assets/js/legacy-shipping-by-rules-config.js', __FILE__ ), array( 'jquery', 'jquery-ui-sortable', 'select2', 'wc-enhanced-select' ), $this->version, true );

	}


}

Legacy_WooCommerce_Shipping_By_Rules::instance();
