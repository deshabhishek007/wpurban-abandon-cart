jQuery( function( $ ) {
	$(document).ready(function() {
		var wcap_last_check_date = localStorage.getItem( "wcap_popup_displayed_next_time" );
		if ( null != wcap_last_check_date ){
			if ( (new Date()).getTime() > wcap_last_check_date ){
				localStorage.removeItem( "wcap_popup_displayed_next_time" );
				localStorage.removeItem( "wcap_popup_displayed" );
			}
		}
	});
	
	$( document.body ).on( 'wc_fragments_refreshed', function( response_data ) {
    		
		var wcap_response = response_data.target.innerHTML;
		/* it detect that the product is added to the cart */
		if ( wcap_response.indexOf('button wc-forward') >= 0 ){
			
			var wcap_is_popup_displayed = localStorage.getItem("wcap_popup_displayed");
			if ( ( typeof wcap_is_popup_displayed === undefined ) || ( wcap_is_popup_displayed != "yes" ) ){
				localStorage.setItem("wcap_popup_displayed", "yes");
				var wcap_next_date = new Date();
				wcap_next_date.setHours( wcap_next_date.getHours() + 24 );
				localStorage.setItem("wcap_popup_displayed_next_time", wcap_next_date.getTime() );
    	        var wcap_email_data = {
					wcap_atc_email       : localStorage.getItem("wcap_hidden_email_id"),
					wcap_atc_user_action : localStorage.getItem("wcap_atc_user_action"),
					action: 'wcap_atc_store_guest_email'
				}

				$.post( wc_cart_fragments_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'wcap_atc_store_guest_email' ), wcap_email_data, function(response_dat , status, xhr ) {
					if ( status === 'success' && response_dat ) {
						localStorage.setItem( "wcap_abandoned_id", response_dat );
					}
		    	} );
			}
		}
    } );
});
