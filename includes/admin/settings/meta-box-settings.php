<?php
/**
 * Shipping By Rules meta box settings.
 *
 * Display the shipping settings in the meta box.
 *
 * @author		Reinhold Kainhofer
 * @package		OpenTools Shipping by Rules for WooCommerce
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wp_nonce_field( 'rules_shipping_settings_meta_box', 'rules_shipping_settings_meta_box_nonce' );

global $post;
$settings = get_post_meta( $post->ID, '_rules_shipping_shipping_method', true );
$settings['shipping_title'] = ! empty( $settings['shipping_title'] ) ? $settings['shipping_title'] : '';

?><div class='rules_shipping rules_shipping_settings rules_shipping_meta_box rules_shipping_settings_meta_box'>

	<p class='rules_shipping-option'>

		<label for='shipping_title'><?php _e( 'Shipping title', 'woocommerce-advanced-free-shipping' ); ?></label>
		<input type='text' class='' id='shipping_title' name='_rules_shipping_shipping_method[shipping_title]'
			value='<?php echo esc_attr( $settings['shipping_title'] ); ?>' placeholder='<?php _e( 'e.g. Free Shipping', 'woocommerce-advanced-free-shipping' ); ?>'>

	</p>


</div>
