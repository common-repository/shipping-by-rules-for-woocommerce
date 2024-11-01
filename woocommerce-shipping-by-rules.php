<?php
/**
 * Plugin Name: WooCommerce Shipping By Rules
 * Plugin URI: http://open-tools.net/woocommerce/advanced-shipping-by-rules-for-woocommerce.html
 * Description: Define Shipping cost by very general and flexible (text-based) rules.
 * Version: 2.0.6
 * Author: Open Tools, Reinhold Kainhofer
 * Author URI: http://open-tools.net
 * Text Domain: woocommerce-shipping-by-rules
 * Domain Path: 
 * License: GPL2+
 * WC requires at least: 2.2
 * WC tested up to: 3.3.3
 

 * Copyright (C) 2015 Reinhold Kainhofer
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
 * Class WooCommerce_Shipping_By_Rules.
 *
 * Main Shipping by Rules class, add filters and handling all other files.
 *
 * @class       WooCommerce_Shipping_By_Rules
 * @author      Reinhold Kainhofer
 */
class WooCommerce_Shipping_By_Rules {
	/**
	 * Version.
	 *
	 * @since 1.0.0
	 * @var string $version Plugin version number.
	 */
	public $version = '2.0.5';


	/**
	 * Instance of WooCommerce_Shipping_By_Rules.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var object $instance The instance of WooCommerce_Shipping_By_Rules.
	 */
	private static $instance;


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( ! function_exists( 'is_plugin_active_for_network' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		// Check if WooCommerce is active
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :
			if ( ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) :
				add_action( 'admin_notices', array('WooCommerce_Shipping_By_Rules', 'otsr_woocommerce_inactive_admin_notice') );
				return;
			endif;
		endif;
		$this->init();

	}


	public static function otsr_woocommerce_inactive_admin_notice() { 
	?>
		<div class="error">
			<p><?php _e( 'Open Tools <b>Advanced Shipping by Rules</b> for WooCommerce is enabled, but <b>WooCommerce</b> is not installed or enabled.', 'opentools-shippingrules' ); ?></p>
		</div>
	<?php
	}


	/**
	 * Instance.
	 *
	 * An global instance of the class. Used to retrieve the instance
	 * to use on other files/plugins/themes.
	 *
	 * @since 1.0.0
	 *
	 * @return object Instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Init.
	 *
	 * Initialize plugin parts.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->hooks();
		$this->load_textdomain();
		$this->update();

		if (!class_exists("RulesShippingFrameworkWooCommerce")) {
			require_once( plugin_dir_path( __FILE__ ) . 'includes/rules_shipping_framework_woocommerce.php');
		}
		$this->helper = RulesShippingFrameworkWooCommerce::getHelper();
		
		// Links to docs and config in the plugin page
		add_filter( 'plugin_row_meta', array( &$this, 'shippingbyrules_row_meta' ), 30, 2 );
		add_filter( 'plugin_action_links_'.plugin_basename(__FILE__),		array( &$this, 'shippingbyrules_add_settings_link' ) );
	}


	public function shippingbyrules_row_meta($links, $file ) {
		if ($file==plugin_basename(__FILE__)) {
			$links['docs'] = '<a href="' . esc_url(  'http://open-tools.net/documentation/advanced-shipping-by-rules-for-woocommerce.html' ) . '" title="' . esc_attr( $this->helper->__( 'Plugin Documentation' ) ) . '">' . $this->helper->__( 'Plugin Documentation' ) . '</a>';
			$links['support'] = '<a href="' . esc_url( 'http://open-tools.net/support-forum/shiping-by-rules-for-woocommerce.html' ) . '" title="' . esc_attr( $this->helper->__( 'Support Forum' ) ) . '">' . $this->helper->__( 'Support Forum' ) . '</a>';
			// ONLY IN BASIC VERSION: Purchase link
			$links['advanced'] = '<a href="' . esc_url( 'http://open-tools.net/woocommerce/advanced-shipping-by-rules-for-woocommerce.html' ) . '" title="' . esc_attr( $this->helper->__('Purchase Advanced Version')) . '">' . $this->helper->__('Purchase Advanced Version') . '</a>';
		}
		return (array)$links;
	}
	/**
	 * Add settings link to plugins page
	 */
	public function shippingbyrules_add_settings_link( $links ) {
		$link = '<a href="admin.php?page=wc-settings&tab=shipping&section=shipping_by_rules">'. $this->helper->__( 'Settings' ) . '</a>';
		array_unshift( $links, $link );
		return $links;
	}
	
	
	/**
	 * Update.
	 *
	 * Runs when the plugin is updated and checks if there should be
	 * any data updated to be compatible for the new version.
	 *
	 * @since 1.0.0
	 */
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


	/**
	 * Hooks.
	 *
	 * Initialize all class hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		// Add the check for the advanced version (disable the basic version in that case)
		add_action( 'plugins_loaded', array($this, 'check_advanced_deactivate'), 99 );

		// Initialize shipping method class
		add_action( 'woocommerce_shipping_init', array( $this, 'shipping_by_rules_init' ) );

		// Add shipping method
		add_action( 'woocommerce_shipping_methods', array( $this, 'shipping_by_rules_add_shipping_method' ) );

		// Enqueue scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'shipping_by_rules_admin_enqueue_scripts' ) );

	}


	/**
	 * Textdomain.
	 *
	 * Load the textdomain based on WP language.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {

		// Load textdomain
		load_plugin_textdomain('opentools-shippingrules', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}


	/**
	 * Shipping method.
	 *
	 * Include the WooCommerce shipping method class.
	 *
	 * @since 1.0.0
	 */
	public function shipping_by_rules_init() {
		if (!class_exists('Shipping_by_Rules')) {
			require_once plugin_dir_path( __FILE__ ) . 'includes/rules-shipping-method.php';
		}
		$this->rule_shipping_method = new Shipping_by_Rules();
	}


	/**
	 * Add shipping method.
	 *
	 * Add shipping method to WooCommerce.
	 *
	 * @since 1.0.0
	 */
	public function shipping_by_rules_add_shipping_method( $methods ) {
		if ( class_exists( 'Shipping_by_Rules' ) ) :
			$methods['shipping_by_rules'] = 'Shipping_by_Rules';
		endif;
		// TODO: Figure out a way to add each shipping by rules method as a
		// separate WooCommerce shipping method (ie. their order can be
		// defined in the WC configuration and not only in the Shipping by
		// Rules Method configuration screen.
		return $methods;
	}
	
	/**
	 * Check if the advanced version is installed an activated
	 */
	function check_advanced_deactivate() {
		if (defined ('OPENTOOLS_ADVANCED_SHIPPINGRULES')) {
			$hook = is_multisite() ? 'network_' : '';
			add_action( "{$hook}admin_notices", array($this, 'print_basic_admin_notice'));
		}
	}
	/**
	 * Disable the basic plugin and print a corresponding message if the advanced version is installed.
	 */
	function print_basic_admin_notice() { 
		deactivate_plugins( plugin_basename( __FILE__ ) );
		?>
		<div class="error">
			<p><?php _e( 'The <b>OpenTools Advanced Shipping by Rules</b> plugin is <b>installed</b> and activated, the <b>basic Shipping by Rules plugin</b> with similar, but limited functionality will be <b>deactivated</b>.', 'opentools-shipping-rules' ); ?></p>
		</div>
		<?php
	}
	


	/**
	 * Enqueue scripts.
	 *
	 * Enqueue javascript and stylesheets to the admin area.
	 *
	 * @since 1.0.0
	 */
	public function shipping_by_rules_admin_enqueue_scripts() {
		// For some strange reason, WC does not define the select2.css style...
		$assets_path          = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';
		wp_enqueue_style( 'select2', $assets_path . 'css/select2.css' );

		wp_enqueue_style( 'shipping_by_rules-style', plugins_url( 'assets/css/admin-styles.css', __FILE__ ), array('select2'), $this->version );
		wp_enqueue_script( 'shipping-by-rules-config', plugins_url( 'assets/js/shipping-by-rules-config.js', __FILE__ ), array( 'jquery', 'jquery-ui-sortable', 'select2', 'wc-enhanced-select' ), $this->version, true );

	}


}

WooCommerce_Shipping_By_Rules::instance();

// Load the legacy plugin version IF REQUIRED!
$legacymethods = get_posts (array ('posts_per_page' => '-1', 'post_type' => 'shipping_rules'));
if (count($legacymethods)>0) {
	require_once( plugin_dir_path( __FILE__ ) . 'legacy-woocommerce-shipping-by-rules.php');
}
