jQuery( document ).ready( function() {
	jQuery( '#wcap_test_sms' ).on( 'click', function() {
		
		var data = {
			number: jQuery( '#test_number' ).val(),
			msg: jQuery( '#test_msg' ).val(),
			action: 'wcap_test_sms' 
		};
		jQuery.post( wcap_advance.ajax_url, data, function( response ) {
			var message = JSON.parse( response );
			jQuery( '#status_msg' ).html( message );
			jQuery( '#status_msg' ).css( 'display', 'block' );
			jQuery( '#status_msg' ).fadeOut( 8000 );
			
		});
		
	});
});