<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Class rules_shipping_post_type.
 *
 * Initialize the Shipping by Rules post type (to configure the .
 *
 * @class       Legacy_Shipping_Rules_post_type
 * @author      Reinhold Kainhofer
 * @package		WooCommerce (Advanced) Shipping By Rules
 * @version		1.0.0
 * @license		GPL v3+
 * @copyright	(c) 2015 Reinhold Kainhofer, Open Tools
 * Based in part on WAFS_post_type (WooCommerce Advanced Free Shipping plugin)
 * (C) Jeroen Sormani, Licensed under the GPL V3+
 */

class Legacy_Shipping_Rules_post_type {
	protected $helper = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct($helper) {
		$this->helper = $helper;
		
		// Register post type
		add_action( 'init', array( $this, 'shipping_rules_register_post_type' ) );

		// Add/save meta boxes
		add_action( 'add_meta_boxes_shipping_rules', array( $this, 'shipping_rules_post_type_meta_box' ) );
		add_action( 'save_post_shipping_rules', array( $this, 'shipping_rules_save_meta' ) );
		add_action( 'save_post_shipping_rules', array( $this, 'shipping_rules_save_rulesets_meta' ) );

 		// Edit user messages
		add_filter( 'post_updated_messages', array( $this, 'shipping_rules_custom_post_type_messages' ) );

		// Redirect after delete
		add_action('load-edit.php', array( $this, 'shipping_rules_redirect_after_trash' ) );
		
		// AJAX handling for the edit page:
		add_action( 'wp_ajax_shipping_rules_add_ruleset', array( $this, 'shipping_rules_add_ruleset' ) );

	 }


	/**
	 * Post type.
	 *
	 * Register 'shipping_rules' post type.
	 *
	 * @since 1.0.0
	 */
	public function shipping_rules_register_post_type() {

		$labels = array(
			'name' 					=> $this->helper->__( 'Shipping By Rules Methods' ),
			'singular_name' 		=> $this->helper->__( 'Shipping By Rules method' ),
			'add_new' 				=> $this->helper->__( 'Add New' ),
			'add_new_item' 			=> $this->helper->__( 'Add New Shipping by Rules method' ),
			'edit_item' 			=> $this->helper->__( 'Edit Shipping by Rules method' ),
			'new_item' 				=> $this->helper->__( 'New Shipping by Rules method' ),
			'view_item' 			=> $this->helper->__( 'View Shipping by Rules method' ),
			'search_items' 			=> $this->helper->__( 'Search Shipping by Rules methods' ),
			'not_found' 			=> $this->helper->__( 'No Shipping by Rules methods' ),
			'not_found_in_trash'	=> $this->helper->__( 'No Shipping by Rules methods found in Trash' ),
		);

		register_post_type( 'shipping_rules', array(
			'label' 				=> 'shipping_rules',
			'show_ui' 				=> true,
			'show_in_menu' 			=> false,
			'public' 				=> false,
			'publicly_queryable'	=> false,
			'capability_type' 		=> 'post',
			'map_meta_cap' 			=> true,
			'rewrite' 				=> false,
			'_builtin' 				=> false,
			'query_var' 			=> true,
			'supports' 				=> array( 'title' ),
			'labels' 				=> $labels,
		) );

	}


	/**
	 * Messages.
	 *
	 * Modify the notice messages text for the 'shipping_rules' post type.
	 *
	 * @since 1.0.0
	 *
	 * @param 	array $messages Existing list of messages.
	 * @return 	array			Modified list of messages.
	 */
	function shipping_rules_custom_post_type_messages( $messages ) {

		$post 				= get_post();
		$post_type			= get_post_type( $post );
		$post_type_object	= get_post_type_object( $post_type );

		$messages['shipping_rules'] = array(
			0  => '',
			1  => $this->helper->__( 'Shipping by Rules method updated.' ),
			2  => $this->helper->__( 'Custom field updated.' ),
			3  => $this->helper->__( 'Custom field deleted.' ),
			4  => $this->helper->__( 'Shipping by Rules method updated.' ),
			5  => isset( $_GET['revision'] ) ?
				sprintf( $this->helper->__( 'Shipping by Rules method restored to revision from %s' ), wp_post_revision_title( (int) $_GET['revision'], false ) )
				: false,
			6  => $this->helper->__( 'Shipping by Rules method published.' ),
			7  => $this->helper->__( 'Shipping by Rules method saved.' ),
			8  => $this->helper->__( 'Shipping by Rules method submitted.' ),
			9  => sprintf(
				$this->helper->__( 'Shipping by Rules method scheduled for: <strong>%1$s</strong>.' ),
				date_i18n( $this->helper->__( 'M j, Y @ G:i' ), strtotime( $post->post_date ) )
			),
			10 => $this->helper->__( 'Shipping by Rules method draft updated.' ),
		);

		if ( 'shipping_rules' == $post_type ) :
			$overview_link = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipping_by_rules' );

			$overview = sprintf( ' <a href="%s">%s</a>', esc_url( $overview_link ), $this->helper->__( 'Return to overview.' ) );
			$messages[ $post_type ][1] .= $overview;
			$messages[ $post_type ][6] .= $overview;
			$messages[ $post_type ][9] .= $overview;
			$messages[ $post_type ][8]  .= $overview;
			$messages[ $post_type ][10] .= $overview;

		endif;

		return $messages;

	}
	
	public static function get_rulesets($post) {
		$rulesets = get_post_meta( $post->ID, '_rules_shipping_ruleset', true );
		if (!is_array($rulesets)) {
			$rulesets = array();
		}
		return $rulesets;
	}


	/**
	 * Add meta boxes.
	 *
	 * Add two meta boxes to the shipping_by_rules post type  with rulesets and settings.
	 *
	 * @since 1.0.0
	 */
	public function shipping_rules_post_type_meta_box($post) {
// my_add_notice( "shipping_rules_post_type_meta_box called", 'info');
		if (!$this->helper->isAdvanced()) {
// 			add_meta_box( 'shipping_rules_upgrade', $this->helper->__( 'Upgrade to the ADVANCED VERSION of the OpenTools Shipping by Rules plugin' ), array( $this, 'render_shipping_upgrade' ), 'shipping_rules', 'normal' );
		}
// 		add_meta_box( 'shipping_rules_settings', $this->helper->__( 'Shipping settings' ), array( $this, 'render_shipping_rules_settings' ), 'shipping_rules', 'normal' );

		add_meta_box( 
			/* ID */      'shipping_rules_rulesets', 
			/* Title */   $this->helper->__( 'Rulesets' ), 
			/* Callback */array( $this, 'render_shipping_rulesets' ), 
			/* Screen */  'shipping_rules', 
			/* Context */ 'advanced');

		add_meta_box( 'shipping_rules_help', $this->helper->__( 'Overview of the Rules Syntax' ), array( $this, 'render_shipping_rules_help' ), 'shipping_rules', 'side' );
	}


	/**
	 * Render meta box.
	 *
	 * Render and display the rulesets meta box contents.
	 *
	 * @since 1.0.0
	 */
	public function render_shipping_upgrade($post, $metabox) {
		$nag_settings = $this->helper->getUpgradeNagSettings();
		$this->helper->printUpgradeNagBox($nag_settings['opentools_shippingbyrules_upgrade']);
	}
	
	public function render_shipping_rulesets($post, $metabox) {
		$rulesets = $this->get_rulesets($post);
		require_once plugin_dir_path( __FILE__ ) . 'admin/settings/meta-box-rulesets.php';
		render_meta_box_shipping_rules_rulesets ($post, $rulesets);
	}
	
	/**
	 * Adding a rule set in the edit page via AJAX => simply return the block generated
	 */
	public function shipping_rules_add_ruleset() {
		require_once plugin_dir_path( __FILE__ ) . 'admin/settings/meta-box-field-ruleset.php';
		$nr = absint($_POST['rulesetnr']);
		render_meta_box_shipping_rules_single_ruleset(null, $nr, null);
		die();
	}


	/**
	 * Render meta box.
	 *
	 * Render and display the settings meta box.
	 *
	 * @since 1.0.0
	 */
	public function render_shipping_rules_settings() {

		require_once plugin_dir_path( __FILE__ ) . 'admin/settings/meta-box-settings.php';
	}

	/**
	 * Render meta box.
	 *
	 * Render and display the help meta box.
	 *
	 * @since 1.0.0
	 */
	public function render_shipping_rules_help() {
		require_once plugin_dir_path( __FILE__ ) . 'admin/settings/meta-box-help.php';
	}


	/**
	 * Save rulesets meta box.
	 *
	 * Validate and save post meta from rulesets meta box.
	 *
	 * @since 1.0.0
	 */
	public function shipping_rules_save_rulesets_meta( $post_id ) {
		if ( ! isset( $_POST['shipping_rules_rulesets_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['shipping_rules_rulesets_meta_box_nonce'], 'shipping_rules_rulesets_meta_box' ) ) :
			return $post_id;
		endif;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) :
			return $post_id;
		endif;

		if ( ! current_user_can( 'manage_woocommerce' ) ) :
			return $post_id;
		endif;

		// Updating the method ordering should only be done when we are on the overview page and have all methods submitted as form controls.
		// Otherwise, the update_post_meta would erase all methods!
		if (!isset($_POST['_rules_shipping_ruleset']) or !isset($_POST['_rules_shipping_ordering'])) {
			return $post_id;
		}

		$shipping_method_rulesets = $_POST['_rules_shipping_ruleset'];
		$shipping_method_ordering = $_POST['_rules_shipping_ordering'];

		$rulesets = array();
		foreach ($shipping_method_ordering as $o) {
			$rulesets[] = $shipping_method_rulesets[$o];
		}
		update_post_meta( $post_id, '_rules_shipping_ruleset', $rulesets );

	}


	/**
	 * Save settings meta box.
	 *
	 * Validate and save post meta from settings meta box.
	 *
	 * @since 1.0.0
	 */
	public function shipping_rules_save_meta( $post_id ) {

		if ( ! isset( $_POST['shipping_rules_settings_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['shipping_rules_settings_meta_box_nonce'], 'shipping_rules_settings_meta_box' ) ) :
			return $post_id;
		endif;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) :
			return $post_id;
		endif;

		if ( ! current_user_can( 'manage_woocommerce' ) ) :
			return $post_id;
		endif;
		
		// Store general settings

// 		$shipping_method = array_map( 'sanitize_text_field', $_POST['_shipping_rules_shipping_method'] );
// 		update_post_meta( $post_id, '_shipping_rules_shipping_method', $shipping_method );

	}


	/**
	 * Redirect trash.
	 *
	 * Redirect user after trashing a shipping_by_rules post.
	 *
	 * @since 1.0.0
	 */
	public function shipping_rules_redirect_after_trash() {
		$screen = get_current_screen();
		if ('edit-shipping_rules' == $screen->id ) {
			if (isset( $_GET['trashed'] ) &&  intval( $_GET['trashed'] ) > 0 ) {
				wp_redirect( admin_url( '/admin.php?page=wc-settings&tab=shipping&section=shipping_by_rules' ) );
				exit();
			}
		}
	}


}


