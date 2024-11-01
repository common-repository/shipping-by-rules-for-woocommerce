<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$methods = get_posts( array( 'posts_per_page' => '-1', 'post_type' => 'shipping_rules', 'post_status' => array( 'draft', 'publish' ) ) );

?>
<tr valign="top">
	<th scope="row" class="titledesc"><?php echo $this->helper->__( 'Shipping By Rules Methods' ) ?></th>
	<td class="forminp" id="<?php echo $this->id; ?>_rules_shipping_methods">

		<table class="rules_shipping_methods widefat striped" cellspacing="0">
			<thead>
				<tr>
					<th class="name"    ><?php echo $this->helper->__( 'Name' ); ?></th>
					<th class="id"      ><?php echo $this->helper->__( 'ID' ); ?></th>
					<th class="status"  ><?php echo $this->helper->__( 'Status' ); ?></th>
					<th class="methods" ><?php echo $this->helper->__( 'Configured Rulesets' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$i = 0;
				foreach ( $methods as $method ) {
					$alt = '';
// 					$alt 			= ( $i++ ) % 2 == 0 ? 'alternate' : '';
					?>
					<tr>
						<td class="name <?php echo $alt; ?>">
							<input type="hidden" name="rules_method_order[]" value="<?php echo esc_attr($method->ID); ?>" />
							<?php echo empty($method->post_title) ? $this->helper->__('Untitled Method') : esc_html($method->post_title); ?>
							<div class='row-actions'>
								<span class='edit'>
									<a href='<?php echo get_edit_post_link( $method->ID ); ?>' title='<?php echo $this->helper->__( 'Edit Method'); ?>'>
										<?php echo $this->helper->__('Edit'); ?>
									</a>
									 |
								</span>
								<span class='trash'>
									<a href='<?php echo get_delete_post_link( $method->ID ); ?>' title='<?php echo $this->helper->__('Delete Method'); ?>'><?php
										echo $this->helper->__('Delete');
									?></a>
								</span>
							</div>
					</td>
					<td class="id">
						<?php echo esc_html($method->ID); ?>
					</td>
					<td class="status">

						<?php 
						if ( $method->post_status == 'publish' ) { ?>
						<span class="status-enabled tips" data-tip="<?php echo __ ( 'Enabled', 'woocommerce' ); ?>"><?php echo __ ( 'Enabled', 'woocommerce' ); ?></span>
						<?php
						} else {
							echo '-';
						}
						?>

					</td>
					<td>
						<?php
							$rulesetnames = array();
							$rulesets = get_post_meta( $method->ID, '_rules_shipping_ruleset', true );
							if (!is_array($rulesets)) { 
								$rulesets = array();
							}
							foreach ($rulesets as $ruleset) {
								if (isset($ruleset['name']) && !empty($ruleset['name'])) {
									$rulesetnames[] = $ruleset['name'];
								} else {
									$rulesetnames[] = $this->helper->__('<Unnamed>');
								}
							}
							if (!empty($rulesetnames)) {
								echo esc_html(join(", ", $rulesetnames));
							} else {
								echo $this->helper->__('<em>No rulesets defined</em>');
							}
						?>
					</td>
				</tr>
					<?php 
				}
				
				if ( empty( $methods ) ) :

					?><tr>
						<td colspan='4'><?php echo $this->helper->__( 'There are no Shipping By Rules methods configured yet.' ); ?></td>
					</tr><?php
					endif;

			?></tbody>
			<tfoot>
				<tr>
					<th colspan='1' style='padding-left: 10px;'>
						<a href='<?php echo admin_url( 'post-new.php?post_type=shipping_rules' ); ?>' class='add button'><?php echo $this->helper->__( 'Add Shipping By Rules method' ); ?></a>
					</th>
					<th colspan="3">
						<span class="description"><?php _e( 'Drag and drop the above shipping methods to control their display order.', 'woocommerce' ); ?></span>
					</th>
				</tr>
			</tfoot>
		</table>
		<script type="text/javascript">
			jQuery(function() {

				// Sorting
				jQuery('table.rules_shipping_methods tbody').sortable({
					items:'tr',
					cursor:'move',
					axis:'y',
					handle: 'td',
					scrollSensitivity:40,
					helper:function(e,ui){
						ui.children().each(function(){
							jQuery(this).width(jQuery(this).width());
						});
// 						ui.css('left', '0');
						return ui;
					},
					start:function(event,ui){
						ui.item.css('background-color','#f6f6f6');
					},
					stop:function(event,ui){
						ui.item.removeAttr('style');
					}
				});
			});
		</script>
	</td>
</tr>
<?php
