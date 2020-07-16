
jQuery( function( $ ) {

	var wcap_session_value = sessionStorage.getItem( 'wcap_email_is_email_captured' );	
    $( document.body ).on( 'wc_fragments_loaded', function( response_data ) {   		
    		if ( Cookies.get( 'woocommerce_items_in_cart' ) > 0 ) {			
				var wcap_is_popup_displayed = localStorage.getItem("wcap_popup_displayed");
				if ( "on" == wcap_capture_url_email_param.wcap_is_atc_enabled ) {
					localStorage.setItem("wcap_popup_displayed", "yes");
				}
				if ( "yes" != wcap_session_value && ( typeof wcap_is_popup_displayed === undefined || wcap_is_popup_displayed != "yes" ) ) {
	    	        var wcap_email_data = {
						wcap_atc_email       : wcap_capture_url_email_param.wcap_populate_email,
						wcap_atc_user_action : "yes",
						action: 'wcap_atc_store_guest_email'
					}
					
					$.post( wcap_capture_url_email_param.wc_ajax_url.toString().replace( '%%endpoint%%', 'wcap_atc_store_guest_email' ), wcap_email_data, function(response_dat , status, xhr ) {
						sessionStorage.setItem( 'wcap_email_is_email_captured', "yes" );
						if ( status === 'success' && response_dat ) {
							localStorage.setItem( "wcap_abandoned_id", response_dat );
						}
			    	} );
				}
			}
    } );

    $( document.body ).on( 'wc_fragments_refreshed', function( response_dat ) {
		var wcap_is_popup_displayed = localStorage.getItem("wcap_popup_displayed");
		if ( Cookies.get( 'woocommerce_items_in_cart' ) > 0 ) {	
			if ( "on" == wcap_capture_url_email_param.wcap_is_atc_enabled ) {
				localStorage.setItem("wcap_popup_displayed", "yes");
			}
			if ( "yes" != wcap_session_value && ( typeof wcap_is_popup_displayed === undefined ||  wcap_is_popup_displayed != "yes" ) ) {
		        var wcap_email_data = {
					wcap_atc_email       : wcap_capture_url_email_param.wcap_populate_email,
					wcap_atc_user_action : "yes",
					action: 'wcap_atc_store_guest_email'
				}

				$.post( wcap_capture_url_email_param.wc_ajax_url.toString().replace( '%%endpoint%%', 'wcap_atc_store_guest_email' ), wcap_email_data, function(response_dat , status, xhr ) {
					sessionStorage.setItem( 'wcap_email_is_email_captured', "yes" );
					if ( status === 'success' && response_dat ) {
						localStorage.setItem( "wcap_abandoned_id", response_dat );
					}
		    	} );
			}
		}
    } );
});
