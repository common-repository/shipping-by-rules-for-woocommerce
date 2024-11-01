<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$methods = array();

?>
<tr valign="top">
	<th colspan="2" scope="row" class="titledesc"><?php echo $this->helper->__( 'Configured Shipping By Rules Methods in all shipping zones' ) ?></th>
</tr>
<tr valign="top">
	<td colspan="2" class="forminp" id="<?php echo $this->id; ?>_rules_shipping_methods"><?php echo sprintf($this->helper->__( 'This table shows <a href="%s">all shipping zones</a> and for each zone the shipping methods provided by this plugin.' ), admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ); ?>

		<table class="rules_shipping_methods wc-shipping-zones widefat striped" cellspacing="0">
			<thead>
				<tr>
					<th class="name"    ><?php echo $this->helper->__( 'Zone name' ); ?></th>
					<th class="methods" ><?php echo $this->helper->__( 'Configured Shipping by Rules methods' ); ?></th>
					<th class="methods" ><?php echo $this->helper->__( 'Other methods' ); ?></th>
				</tr>
			</thead>
			<tbody class="wc-shipping-zone-rows">
				<?php
				$zones = WC_Shipping_Zones::get_zones();
				// get_zones does NOT include the global (fallback) zone => add it manually!
				$globalshippingzone = new WC_Shipping_Zone(0);
				$globalzone                            = $globalshippingzone->get_data();
				$globalzone['formatted_zone_location'] = $globalshippingzone->get_formatted_location();
				$globalzone['shipping_methods']        = $globalshippingzone->get_shipping_methods();
				$zones[] = $globalzone;
				foreach ($zones as $zone) {
					$zoneid = isset($zone['zone_id'])?$zone['zone_id']:$zone['id'];
				?>
				<tr>
					<td class="name"><a href="<?php echo admin_url(sprintf('admin.php?page=wc-settings&tab=shipping&zone_id=%d', $zoneid )); ?>"><?php echo $zone['zone_name']; ?> (<?php echo $zone['formatted_zone_location']; ?>)</a></td>
					<td class="methods wc-shipping-zone-methods ">
						<ul>
						<?php
						foreach ($zone['shipping_methods'] as $method) {
							if ($method->id == 'shipping_by_rules') {
								$methodclass = ($method->enabled=='no')?'method_disabled':'method_enabled';
								$methodurl = admin_url(sprintf('admin.php?page=wc-settings&tab=shipping&instance_id=%d', $method->instance_id));
								?>
								<li class="<?php echo $methodclass;?>"><a href="<?php echo $methodurl;?>"><?php echo $method->title; ?></a></li>
								<?php
							}
// 							print_r($method);
						} ?>
						</ul>
					</td>
					<td class="methods wc-shipping-zone-methods ">
						<ul>
						<?php
						foreach ($zone['shipping_methods'] as $method) {
							if ($method->id != 'shipping_by_rules') {
								$methodclass = ($method->enabled=='no')?'method_disabled':'method_enabled';
								?>
								<li class="<?php echo $methodclass;?>"><?php echo $method->title; ?></li>
								<?php
							}
						} ?>
						</ul>
					</td>
				</tr>
				<?php 
				} ?>
			</tbody>
		</table>
	</td>
</tr>

<?php
