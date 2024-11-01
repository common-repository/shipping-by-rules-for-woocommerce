/**
 * Ordernumber Admin JS
 */
jQuery( function ( $ ) {
	var loading_icon = '<span class="loading-icon"><img src="images/loading.gif"/></span>';

	// Add new ruleset via an AJAX call
	$( '#shipping_rules_rulesets' ).on( 'click', '.ruleset-add', function() {
		
		// Display loading icon
		$( '.shipping_rules_rulesets' ).append( loading_icon ).children( ':last' );
		
		// Find the largest existing ruleset ID number (might have been reordered!):
		var maxnr = 0;
		$('.shipping_rules_ruleset').map(function(){
			var value = parseFloat(this.getAttribute('data-nr')) || -Infinity;
			maxnr = (value>maxnr) ? value : maxnr;
		});

		var data = { 
			action: 'shipping_rules_add_ruleset',
			rulesetnr: maxnr+1
		};
		
		// Insert condition group
		$.post( ajaxurl, data, function( response ) {
			$( '.shipping_rules_rulesets .loading-icon' ).last().remove();
			var appended = $( '.shipping_rules_rulesets' ).append( response ).children( ':last' );
			$(appended).find( '.select2' ).select2();
			$(appended).hide().fadeIn( 'normal' );
		});
	});
	
	// Delete ruleset
	$( '#shipping_rules_rulesets' ).on( 'click', '.ruleset-remove', function() {
		$( this ).closest( '.shipping_rules_ruleset' ).fadeOut( 'normal', function() { $( this ).remove();	});
	});
	
	// Open/Close the ruleset boxes
	$( '#shipping_rules_rulesets' ).on( 'click', '.ruleset-box-header', function (event) {
		// If the user clicks on some form input inside the h3, like a select list (for variations), the box should not be toggled
		if ($(event.target).filter(':input, option').length) return;
		$( this ).closest( '.shipping_rules_ruleset' ).toggleClass('closed');
	});

});
