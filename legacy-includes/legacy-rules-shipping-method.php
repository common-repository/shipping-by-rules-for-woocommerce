<?php
/**
 * Shipping By Rules Shipping Method
 * http://open-tools.net/woocommerce/
 * Define Shipping cost by very general and flexible (text-based) rules.
 * Ver. 1.0.0
 * Author: Open Tools, Reinhold Kainhofer
 * License: GPLv3+
*/

class Legacy_Shipping_by_Rules extends WC_Shipping_Method {
	protected $helper = null;
	/**
	 * Constructor for your shipping class
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		if (class_exists("LegacyRulesShippingFrameworkWooCommerceAdvanced")) {
			$this->helper = LegacyRulesShippingFrameworkWooCommerceAdvanced::getHelper();
		} else {
			if (!class_exists("LegacyRulesShippingFrameworkWooCommerce")) {
				require_once( plugin_dir_path( __FILE__ ) . 'legacy-rules_shipping_framework_woocommerce.php');
			}
			$this->helper = LegacyRulesShippingFrameworkWooCommerce::getHelper();
		}
		$this->id						= 'legacy-shipping_by_rules'; // Id for your shipping method. Should be unique.
		$this->title  					= $this->helper->__( 'Shipping By Rules (legacy)');
		$this->method_title				= $this->helper->__( 'Shipping by Rules (legacy)' );  // Title shown in admin
		$this->method_description		= sprintf($this->helper->__('<strong>This method is deprecated in WooCommerce 2.6, where shipping zones are introduced, and will be removed in future versions.</strong> Please use WooCommerce\'s shipping zones for country restrictions and set up methods of type "Shipping by Rules" inside each shipping zone. Your existing rules and this legacy shipping method will continue to work until you manually transfer all rules to the <a href="%s">Shipping Zones</a>. and remove all Shipping by Rules methods here.'), admin_url( 'admin.php?page=wc-settings&tab=shipping' ) );
		$this->init();
	}
	
	public function setHelper($helper) {
		$this->helper = $helper;
	}

	/**
	 * Init your settings
	 *
	 * @access public
	 * @return void
	 */
	function init() {
		$this->init_form_fields();
		$this->init_settings();

		$this->enabled            = $this->get_option ('enabled');

		// Save settings in admin if you have any defined
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_shipping_rules_sorting' ) );
	}

	/**
	* Initialise Settings Form Fields
	*
	* @access public
	* @return void
	*/
	public function init_form_fields() {
		add_filter( 'woocommerce_admin_field_opentools_shippingbyrules_upgrade', array( &$this, 'admin_field_opentools_shippingbyrules_upgrade') );
		$fields = array_merge(
			$this->helper->getUpgradeNagSettings(),
			
			array(
				'enabled' => array(
					'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
					'type' 			=> 'checkbox',
					'label' 		=> $this->helper->__( 'Enable Shipping By Rules'),
					'default' 		=> 'yes'
				),
				'methods' => array(
					'type' => 'legacy_rules_shipping_methods',
				),
			)
		);
		$this->form_fields = $fields;
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
	
	/**
	* generate_rules_shipping_methods_html function.
	*
	* @access public
	* @return string
	*/
	public function generate_legacy_rules_shipping_methods_html() {
		ob_start();
		include plugin_dir_path( __FILE__ ) . 'admin/html/html-shipping-methods.php';
		return ob_get_clean();
	}

	
	/**
	* validate_rules_shipping_methods_field function.
	* Table does not need validation, so always return false.
	*
	* @access public
	* @param mixed $key
	* @return bool
	*/
	public function validate_legacy_rules_shipping_methods_field( $key ) {
		return false;
	}
	
	/**
	* generate_rules_shipping_methods_html function.
	*
	* @access public
	* @return string
	*/
	public function generate_opentools_shippingbyrules_upgrade_html($id, $settings) {
		if (isset($settings['name'])) {
			$settings['title'] = $settings['name'];
		}
		ob_start();
		?>
		<tr valign="top">
			<td colspan="2">
				<?php $this->helper->printUpgradeNagBox($settings); ?>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}


	/** 
	 * Handle shipping rules settings
	 */
	public function process_shipping_rules_sorting() {
		$ordering = isset($_POST[ 'rules_method_order']) ? array_map( 'wc_clean', $_POST[ 'rules_method_order'] ) : array();
		$this->helper->set_method_ordering($ordering);
	}

 
	/**
	 * calculate_shipping function.
	 *
	 * @access public
	 * @param mixed $package
	 * @return void
	 */
	public function calculate_shipping( $package = array()) {
		$methods = $this->helper->get_rule_shipping_methods();
		foreach ($methods as $method) {

			$rates = $this->helper->getCosts($package, $method);
			foreach ($rates as $r) {
				$label = $r['name'];
				if (isset($r['rulename']) && !empty($r['rulename'])) {
					$label .= ' (' . $r['rulename'] . ')';
				}
				$rate = array(
					'id' => $this->id . $r['method'],
					'label' => $label,
					'package' => $package,
					'cost' => $r['cost'],
// 					'calc_tax' => 'per_item',
				);
				// Register the rate
				$this->add_rate( $rate );
			}
		}
	}
}
