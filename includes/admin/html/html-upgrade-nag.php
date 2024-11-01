<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
		<div id="opentools-shippingbyrules-upgrade" class="postbox">
			<?php if (isset($settings['title'])) { ?><h3><?php echo esc_html($settings['title']); ?></h3><?php } ?>
			<div class="contents">
				<div class="logoleft"><a href="<?php echo esc_html($settings['link']); ?>"><img src="<?php echo plugins_url('../../../assets/images/advlogo100.png', __FILE__); ?>"></a></div>
				<!--p>Advanced features not included in the free plugin include:</p-->
				<ul>
					<li><b>Mathematical expressions</b> (depending on weight, dimensions, amount, etc.): <span style="font-size: smaller">shipping costs per kg, percentage shipping cost on the amount, etc.</span></li>
					<li>Conditions on <b>certain products, categories, tags or shipping classes</b></li>
					<li><b>Calculations for product subsets</b>: values (weight, quantity, amount, ...) only for particular products, categories, tags or shipping classes</li>
					<li>Support <b>UK and Canadian postcodes</b>, <b>coupon codes</b>, and many more features...</li>
				</ul>
				<p>More information and purchase: <a class="button-primary" href="<?php echo esc_html($settings['link']); ?>" target="_blank">Get Support and advanced features</a></p>
			</div>
		</div>
<?php
