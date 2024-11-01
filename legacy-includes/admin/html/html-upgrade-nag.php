<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
		<div id="opentools-shippingbyrules-upgrade" class="rules_shipping rules_shipping_upgrade rules_shipping_meta_box rules_shipping_settings_meta_box postbox">
			<?php if (isset($settings['title'])) { ?><h3><?php echo esc_html($settings['title']); ?></h3><?php } ?>
			<div class="contents">
				<div class="logoleft"><a href="<?php echo esc_html($settings['link']); ?>"><img src="<?php echo plugins_url('../../../assets/images/advlogo100.png', __FILE__); ?>"></a></div>
				<!--p>Advanced features not included in the free plugin include:</p-->
				<ul>
					<li><b>Mathematical expressions</b>: shipping costs calculated from mathematical expressions (depending on weight, dimensions, amount, etc.):
					<ul style="font-size: smaller">
						<li>Shipping costs per kg</li>
						<li>Percentage shipping cost on the amount, etc.</li>
					</ul>
					<li><b>List functions</b>: conditions on certain products, categories, tags or shipping classes</li>
					<li><b>Conditions for product subsets</b>: Conditions and shipping costs depending on values evaluated only for particular products, categories, tags or shipping classes
					<li><b>Alphanumeric Postcodes</b>: Conditions on UK and Canadian postcodes</li>
					<li><b>Coupon Codes</b>: conditions on coupon codes</li>
					<li>...</li>
				</ul>
				<p>More information and purchase: <a class="button-primary" href="<?php echo esc_html($settings['link']); ?>" target="_blank">Get Support and advanced features</a></p>
			</div>
		</div>
<?php
