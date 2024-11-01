<?php
/**
 * Shipping By Rules meta box Help.
 *
 * Display the help message / user guide for the Shipping by Rules Method.
 *
 * @author		Reinhold Kainhofer
 * @package		OpenTools Shipping by Rules for WooCommerce
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?><div id="opentools-shippingbyrules-help" class='closed postbox'>
	<?php if (isset($settings['title'])) { ?>
	<div class="opentools_shippingbyrules_title">
		<div class="openclose" title="Click to toggle"><br></div>
		<h3><?php echo esc_html($settings['title']); ?> <span style="font-size: smaller; font-weight: normal;">(click to open/close this section)</span></h3>
	</div><?php 
	} ?>
	<div class="content opentools_shippingbyrules_contents">
		<p>See also the the <a href='http://open-tools.net/documentation/advanced-shipping-by-rules-for-woocommerce.html'>Plugin's documentation</a> and <a href='http://open-tools.net/documentation/shipping-by-rules-plugins-for-virtuemart/rules-examples.html'>Rules Examples</a>.</p>
		<ul class='otsr_list'>
			<li>One rule per line, rule parts separated by Semicolon (;)</li>
			<li>Rules are evaluated sequentially, first matching rule is offered</li>
			<li>Typical form of a rule:
			<blockquote>
			Name="Rule name"; Amount&lt;50; 1000&lt;=ZIP&lt;2000; Shipping=123
			</blockquote></li>
			<li>Setting costs to NoShipping prevents method from offering any shipping</li>
			<li>Conditions can contain <strong>comparison operators (&lt;, &lt;=, =&lt;, ==, !=, &lt;&gt;, &gt;=, =&gt;, &gt;)</strong>, variables and functions.</li>
			<li>Some <strong>variables (case-insensitive)</strong>: 
				<ul>
					<li><tt>Amount</tt>, <tt>Weight</tt>, <tt>ZIP</tt>, <tt>Products</tt> (number of products), <tt>Articles</tt> (counted with quantity), <tt>Volume</tt> (total volume of the order), <tt>Min/MaxVolume</tt>, <tt>Min/MaxLength</tt>, <tt>Min/MaxWidth</tt>, <tt>Min/MaxHeight</tt>, ....</li>
				</ul>
			</li>
			<!--li>All rule parts of the form <strong><tt>[VARIABLE]=VALUE</tt> are assignments</strong>; allowed variables are 
				<ul>
					<li><strong><tt>Name</tt></strong> (of the rule), </li>
					<li><strong><tt>Shipping</tt></strong>, </li>
					<li><strong><tt>ShippingWithTax</tt></strong>, </li>
					<li><strong><tt>ExtraShippingCharge</tt></strong>, </li>
					<li><strong><tt>ExtraShippingMultiplier</tt></strong>, </li>
					<li><strong><tt>Condition</tt></strong>, </li>
					<li><strong><tt>Variable</tt></strong>, </li>
					<li><strong><tt>Value</tt></strong>, </li>
					<li><strong><tt>Comment</tt></strong>.</li>
				</ul>
				The '<tt>Shipping='</tt> can be left out.</li-->
			<li>In the <a href="http://open-tools.net/woocommerce/advanced-shipping-by-rules-for-woocommerce.html">advanced version</a>, all expressions may contain <strong>arbitrary basic arithmetic expressions (+, -, *, /, %, ^ and parentheses)</strong> of the above variables and functions like max(..), min(..), round(..), ceil(..), floor(..), day(), month(), etc..</li>
			<li><strong>Alphanumeric postal codes</strong>: variables <tt>UK_Outward, UK_Area, UK_District, UK_Subdistrict, Canada_FSA, Canada_Area, Canada_Urban, Canada_Subarea, ZIP1, ZIP2, ZIP3, ZIP4, ZIP5</tt> and <tt>ZIP6</tt> in the <a href="http://open-tools.net/woocommerce/advanced-shipping-by-rules-for-woocommerce.html">advanced version</a>.</li>
		</ul>
		<p>EXAMPLE: A rule named 'Europe' that sets shipping costs of 5€ for orders weighing less than 5kg, 10€ for heavier orders, and free shipping from 100€ on would be:</p>
		<blockquote><tt>Name=Free Shipping; 100&lt;= Amount; 0<br/>Name=Europe; Weight&lt;5; Shipping=5<br/>Name=Europe; Weight>=5; Shipping=10</tt></blockquote>
		
		<?php if (!isset($settings['isAdvanced']) || !$settings['isAdvanced']) { ?>
		<div id='opentools-shippingbyrules-upgrade'>
			<h3><?php echo esc_html(__( 'Upgrade to the ADVANCED VERSION of the OpenTools Shipping by Rules plugin', 'opentools-shippingrules')); ?></h3>
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
		<?php } ?>
	</div>

</div>
<?php 
