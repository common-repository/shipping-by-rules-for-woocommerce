<?php
/**
 * Shipping By Rules meta box rulesets.
 *
 * Display the shipping rulesets in the meta box.
 *
 * @author		Reinhold Kainhofer, Open Tools
 * @package		WooCommerce Shipping Bby Rules
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly




function render_meta_box_shipping_rules_rulesets ($post, $rulesets) {

wp_nonce_field( 'shipping_rules_rulesets_meta_box', 'shipping_rules_rulesets_meta_box_nonce' );

?><div class='shipping_rules shipping_rules_rulesets shipping_rules_meta_box shipping_rules_rulesets_meta_box ui-sortable'>
<pre><?php //print_r($rulesets); ?></pre>
	<?php 
	require_once plugin_dir_path( __FILE__ ) . 'meta-box-field-ruleset.php';
	$nr=0;
	foreach ($rulesets as $ruleset) {
		render_meta_box_shipping_rules_single_ruleset ($post, ++$nr, $ruleset);
	}
	?>

</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
// 		$( '.shipping_rules .chzn-select' ).chosen();
		$('.shipping_rules .select2').select2();
		$('.shipping_rules_rulesets_meta_box.ui-sortable').sortable();
	});
</script>
		
<p class="shipping_rules_add"><a class='button ruleset-add' href='javascript:void(0);'><?php _e( 'Add a new ruleset', 'opentools-shippingrules' ); ?></a></p>


<?php
}

