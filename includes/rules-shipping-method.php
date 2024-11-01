<?php
/**
 * Shipping By Rules Shipping Method
 * http://open-tools.net/woocommerce/
 * Define Shipping cost by very general and flexible (text-based) rules.
 * Ver. 1.0.0
 * Author: Open Tools, Reinhold Kainhofer
 * License: GPLv3+
*/

if (!defined('ABSPATH')) {
	exit;
}

class Shipping_by_Rules extends WC_Shipping_Method {
	protected $helper = null;
	/**
	 * Constructor for your shipping class
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($instance_id = 0) {
		if (class_exists("RulesShippingFrameworkWooCommerceAdvanced")) {
			$this->helper = RulesShippingFrameworkWooCommerceAdvanced::getHelper();
		} else {
			if (!class_exists("RulesShippingFrameworkWooCommerce")) {
				require_once( plugin_dir_path( __FILE__ ) . 'includes/rules_shipping_framework_woocommerce.php');
			}
			$this->helper = RulesShippingFrameworkWooCommerce::getHelper();
		}
		$this->id						= 'shipping_by_rules'; // Id for your shipping method. Should be unique.
		$this->instance_id				= absint( $instance_id );
		$this->method_title				= $this->helper->__( 'Shipping by Rules' );  // Title shown in admin
		$this->method_description		= $this->helper->__( 'Define shipping costs by general, text-based rules.' ); // Description shown in admin
		$this->supports					= array(
			'settings', // Global settings (not instance-specific)
			'shipping-zones',
			'instance-settings',
		);

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
		$this->instance_form_fields		= $this->define_instance_form_fields();
		$this->form_fields				= $this->define_global_form_fields();
		$this->title  					= $this->get_option('title');
		$this->tax_status				= $this->get_option('tax_status');
		$this->rules					= $this->get_option('rules');
		
	
		$this->init_form_fields();
		$this->init_settings();

		// Save settings in admin if you have any defined
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		add_filter( 'woocommerce_admin_field_opentools_shippingbyrules_help', array( &$this, 'admin_field_opentools_shippingbyrules_help') );
	}
	
	function define_global_form_fields() {
		return array_merge(
			$this->helper->getUpgradeNagSettings(),
			array(
/*				'debug' => array(
					'title'   => $this->helper->__('Enable debug messages'),
					'type'    => 'checkbox',
					'label'   => $this->helper->__('If enabled and WP_DEBUG is set, all defined variables and their values are printed into the WordPress debug log.' ),
					'default' => 'no'
				),
				'global_rules' => array(
					'title'			=> $this->helper->__('Global definitions:'),
					'type'			=> 'textarea',
					'css'			=> '',
					'description'			=> $this->helper->__('Global definitions are prepended to all rule sets and will be available in all Shipping by Rules methods of all shipping zones. Do NOT include a rule that sets Shipping=... or NoShipping, as this will override all other rules!'),
					'class'			=> 'shipping_rules_rule_textarea',
					'placeholder'	=> $this->helper->__('Definitions of the form: Variable=MyVar; Amount>100; [...Conditions...]; Value=123'),
				),*/
				'methods' => array(
					'type' => 'rules_shipping_methods',
				),
				
				
			)
		);
	}
	
	function define_instance_form_fields() {

		return array_merge(
// 			$this->helper->getUpgradeNagSettings(),
			
			array(
				'title' => array(
					'title' 		=> __( 'Method Title', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					'default'		=> __( 'Shipping by Rules', 'woocommerce' ),
					'desc_tip'		=> true
				),
				'tax_status' => array(
					'title' 		=> __( 'Tax Status', 'woocommerce' ),
					'type' 			=> 'select',
					'class'         => 'wc-enhanced-select',
					'default' 		=> 'taxable',
					'options'		=> array(
						'taxable' 	=> __( 'Taxable', 'woocommerce' ),
						'none' 		=> _x( 'None', 'Tax status', 'woocommerce' )
					)
				),
				'rules' => array(
					'title'			=> $this->helper->__('Rules:'),
					'type'			=> 'textarea',
					'css'			=> '',
					'class'			=> 'shipping_rules_rule_textarea',
					'placeholder'	=> $this->helper->__('Rules of the form: Name="Rule name"; Amount>100; [...Conditions...]; Shipping=3'),
				),
				'ruleshelp' => array(
					'name'			=> $this->helper->__('Help on rules syntax'),
					'type' 			=> 'opentools_shippingbyrules_help',
					'link'			=> 'http://open-tools.net/woocommerce/advanced-shipping-by-rules-for-woocommerce.html',
					'isAdvanced'	=> $this->helper->isAdvanced(),
				),
			)
// 			$this->helper->getUpgradeNagSettings()
		);
	}
	
	/**
	 * Validate rules Textarea Field.
	 *
	 * Custom validation of rules textarea is needed, as we need to preserve
	 * all text verbatim (in particular the comparison operators < and > as well
	 * as quotes. Do NOT try to strip html, as the rules are interpreted as 
	 * text anyway and no malicious code can be inserted)
	 *
	 * @param  string $key
	 * @param  string|null $value Posted Value
	 * @return string
	 */
	public function validate_rules_field( $key, $value ) {
		$value = is_null( $value ) ? '' : $value;
		return trim( stripslashes( $value ) );
	}
	
	/**
	 * Generate Textarea HTML. 
	 * Overridden from WC_Settings_API to allow for dynamic textarea heights 
	 * (WC_Settings_API hardcodes 3 lines of text).
	 * We resize the textarea to #lines+1, with a maximum of 25 lines
	 *
	 * @param  mixed $key
	 * @param  mixed $data
	 * @since  1.0.0
	 * @return string
	 */
	public function generate_textarea_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);
		$value = $this->get_option( $key );
		$linecount = min(25, substr_count($value, PHP_EOL) + 2);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php echo $this->get_tooltip_html( $data ); ?>
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<textarea rows="<?php echo $linecount; ?>" cols="20" class="input-text wide-input <?php echo esc_attr( $data['class'] ); ?>" type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?>><?php echo esc_textarea( $value ); ?></textarea>
					<?php echo $this->get_description_html( $data ); ?>
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	/**
	* generate_rules_shipping_methods_html function.
	*
	* @access public
	* @return string
	*/
	public function generate_rules_shipping_methods_html() {
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
	public function validate_rules_shipping_methods_field( $key ) {
		return false;
	}
	
	
	/**
	* generate_opentools_shippingbyrules_upgrade_html function.
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
	* generate_opentools_shippingbyrules_help_html function.
	*
	* @access public
	* @return string
	*/
	public function generate_opentools_shippingbyrules_help_html($id, $settings) {
		if (isset($settings['name'])) {
			$settings['title'] = $settings['name'];
		}
		ob_start();
		?>
		<tr valign="top">
			<td colspan="2">
				<?php include plugin_dir_path( __FILE__ ) . 'admin/html/html-rulesyntax-help.php'; ?>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}


	/**
	 * calculate_shipping function.
	 *
	 * @access public
	 * @param mixed $package
	 * @return void
	 */
	public function calculate_shipping( $package = array()) {
		$rate = array(
			'id'		=> $this->get_rate_id(),
			'label'		=> $this->title,
			'cost'		=> 0,
			'package'	=> $package,
		);

		$rates = $this->helper->getCosts($package, $this);
		foreach ($rates as $r) {
			if (isset($r['rulename']) && !empty($r['rulename'])) {
				$rate['label'] = $this->title . ' (' . $r['rulename'] . ')';
			}
			$rate['cost'] = $r['cost'];
			$this->add_rate( $rate );
		}

	}
}
