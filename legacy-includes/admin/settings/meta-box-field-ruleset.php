<?php
/**
 * Shipping By Rules meta box field showing one ruleset.
 *
 * Display one ruleset in a meta box.
 *
 * @author		Reinhold Kainhofer, Open Tools
 * @package		WooCommerce Shipping Bby Rules
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function render_meta_box_shipping_rules_single_ruleset ($post, $nr, $ruleset) {
	$id = '_rules_shipping_ruleset['.absint($nr).']';

?><div class='shipping_rules shipping_rules_ruleset shipping_rules_meta_box shipping_rules_ruleset_meta_box_field' data-nr='<?php echo absint($nr); ?>'>
	<div class="ruleset-box-header">
		<div class="ruleset_openclose" title="Click to toggle"><br></div>
		<h3 class="ruleset_name_label ui-sortable-handle">Ruleset: </h3>
		<div class="input-wrap"><input type='text' class='ruleset_name' name='<?php echo $id; ?>[name]' placeholder='<?php _e('Name of the Ruleset', 'opentools-shippingrules'); ?>' value='<?php echo isset($ruleset['name'])?esc_attr($ruleset['name']):""; ?>'></div>
	</div>
	<div class="inside">
		<input type='hidden' name='_rules_shipping_ordering[]' value='<?php echo absint($nr);?>'>
		<table class='form-table'>
			<tbody>
<?php

				/** Country restrictions: */
				$selections = isset($ruleset['countries'])?$ruleset['countries']:array();
				$value = array(
// 					'options' => WC()->countries->countries,
					'id' =>		$id.'[countries]',
					'title' =>	__('Restrict to Countries', 'opentools-shippingrules'),
					'type' =>	'multi_select_countries',
				);
				$description = '';
				$tooltip_html = '';

				if ( ! empty( $value['options'] ) ) {
					$countries = $value['options'];
				} else {
					$countries = WC()->countries->countries;
				}

				asort( $countries );
				?><tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
						<?php echo $tooltip_html; ?>
					</th>
					<td class="forminp">
						<select multiple="multiple" name="<?php echo esc_attr( $value['id'] ); ?>[]" data-placeholder="<?php _e( 'Choose countries (no selection means no restriction)&hellip;', 'woocommerce' ); ?>" title="<?php _e( 'Country', 'woocommerce' ) ?>" class="select2">
							<?php
								if ( $countries ) {
									foreach ( $countries as $key => $val ) {
										echo '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( $key, $selections ), true, false ).'>' . $val . '</option>';
									}
								}
							?>
						</select> <?php echo ( $description ) ? $description : ''; ?>
					</td>
				</tr><?php



				/** Rules */
				$option_value = isset($ruleset['rules'])?$ruleset['rules']:'';
				$nrrules = count(explode("\n", $option_value));
				$value = array(
					'id' =>		$id.'[rules]',
					'title' =>	__('Rules:', 'opentools-shippingrules'),
					'type' =>	'textarea',
					'css' =>	'',
					'class' =>	'shipping_rules_rule_textarea',
					'placeholder'	=> __('Rules of the form: Name="Rule name"; Amount>100; [...Conditions...]; Shipping=3', 'opentools-shippingrules'),
				);
				$custom_attributes = array( 
					'rows="'.absint(max($nrrules,5)).'"',
					'cols="35"',
				);
					

				?><tr valign="top">
					<td class="forminp shipping_rules_rulescell forminp-<?php echo sanitize_title( $value['type'] ) ?>" colspan="2">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><b><?php echo esc_html( $value['title'] ); ?></b></label>
						<?php echo $tooltip_html; ?>
						<?php echo $description; ?>
						<br/>

						<textarea
							name="<?php echo esc_attr( $value['id'] ); ?>"
							id="<?php echo esc_attr( $value['id'] ); ?>"
							style="<?php echo esc_attr( $value['css'] ); ?>"
							class="<?php echo esc_attr( $value['class'] ); ?>"
							placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); ?>
							><?php echo esc_textarea( $option_value );  ?></textarea>
					</td>
				</tr><?php

?>
			</tbody>
		</table>
	
		<p class="shipping_rules_remove"><a class='button ruleset-remove' href='javascript:void(0);'><?php _e( 'Remove this ruleset', 'opentools-shippingrules' ); ?></a></p>

	</div>
	
</div>


<?php
}
