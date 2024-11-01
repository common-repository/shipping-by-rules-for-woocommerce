/**
 * Ordernumber Admin JS
 */
jQuery( function ( $ ) {
	// Open/Close the help boxes
	$( '#opentools-shippingbyrules-help' ).on( 'click', '.opentools_shippingbyrules_title', function (event) {
		$( this ).closest( '#opentools-shippingbyrules-help' ).toggleClass('closed');
	});

});
