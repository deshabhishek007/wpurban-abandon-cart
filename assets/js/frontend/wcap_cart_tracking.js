jQuery( document ).ready( function() {
	
	// check if the button exists
	if( jQuery( '#wcap_tracking_yes' ).length > 0 ) {
		// if yes, add an onclick event
		jQuery( '#wcap_tracking_yes' ).on( 'click', function() {
			
			var data = {
					notice: 'yes',
					action: 'wcap_track_notice'
			};
			
			jQuery.post( wcap_ntc_parms.wc_ajax_url.toString().replace( '%%endpoint%%', 'wcap_track_notice' ), data, function( response ) {
				location.reload();
			});
		});
		
	} 
	
	
});