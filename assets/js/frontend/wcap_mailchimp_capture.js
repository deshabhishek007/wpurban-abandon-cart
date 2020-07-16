jQuery( document).ready( function( $ ) {

	setTimeout( function(){
		var wcap_get_email_address = '',
			pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i),
			wcap_record_added = false,
			wcap_next_date = new Date();

		jQuery( '.mc-layout__modalContent iframe' ).contents().find('#mc-EMAIL').on( 'change', function() {
			wcap_get_email_address = this.value;

			if ( pattern.test( wcap_get_email_address  ) ) {
				localStorage.setItem( "wcap_hidden_email_id", wcap_get_email_address );
				localStorage.setItem( "wcap_atc_user_action", "yes" );
				localStorage.setItem( "wcap_mailchimp_captured", "yes" );
			}
		});
	}, 6000);

	var timeout_var = 0;
	if (typeof nfi18n !== 'undefined') {
		timeout_var = 5000;
	}
	setTimeout( function(){
		var wcap_form_classes = wcap_mailchimp_setting.wcap_form_classes;

		wcap_form_classes = '.'+wcap_form_classes.replace(/,/g, ', .');
		if( !localStorage.getItem( "wcap_abandoned_id" ) && localStorage.getItem( "wcap_mailchimp_captured" ) !== 'yes' ){
			var wcap_get_email_address = '',
				pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);

			jQuery( wcap_form_classes ).on( 'change', function() {
				wcap_get_email_address = this.value;
				if ( pattern.test( wcap_get_email_address  ) ) {
					localStorage.setItem( "wcap_hidden_email_id", wcap_get_email_address );
					localStorage.setItem( "wcap_atc_user_action", "yes" );
					localStorage.setItem( "wcap_mailchimp_captured", "yes" );

					var data = {
						wcap_get_email_address,
						action: 'wcap_add_email_to_cart'
					}

					$.post( wcap_mailchimp_setting.wcap_ajax_url, data, function( response ) {
						wcap_record_added = true;
					});
				}
			});
		}
	}, timeout_var);

	if ( wcap_mailchimp_setting.wcap_popup_setting !== 'on' || wcap_mailchimp_setting.wcap_url_capture ) {
		$( document.body ).on( 'wc_fragments_refreshed', function(){
			if( !localStorage.getItem( "wcap_abandoned_id" ) && localStorage.getItem( "wcap_mailchimp_captured" ) === 'yes' ){
				var wcap_email_data = {
					wcap_atc_email       : localStorage.getItem("wcap_hidden_email_id"),
					wcap_atc_user_action : localStorage.getItem("wcap_atc_user_action"),
					action: 'wcap_atc_store_guest_email'
				}
				$.post( wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'wcap_atc_store_guest_email' ), wcap_email_data, function( response_dat, status, xhr ) {
					if ( status === 'success' && response_dat ) {
						localStorage.setItem( "wcap_abandoned_id", response_dat );
					}
				} );
			}
		});

		$( document.body ).on( 'added_to_cart', function( fragments, cart_hash, button ){
			if( !localStorage.getItem( "wcap_abandoned_id" ) && localStorage.getItem( "wcap_mailchimp_captured" ) === 'yes' ){
				var wcap_email_data = {
					wcap_atc_email       : localStorage.getItem("wcap_hidden_email_id"),
					wcap_atc_user_action : localStorage.getItem("wcap_atc_user_action"),
					action: 'wcap_atc_store_guest_email'
				}
				$.post( wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'wcap_atc_store_guest_email' ), wcap_email_data, function( response_dat, status, xhr ) {
					if ( status === 'success' && response_dat ) {
						localStorage.setItem( "wcap_abandoned_id", response_dat );
					}
				} );
			}
		});
	}

	if ( wcap_mailchimp_setting.wcap_url_capture ) {
		var query = window.location.search.substring(1);
		if ( query ) {
			query = query.replace( '%20', '+' );
			var uriArray = decodeURIComponent(query);
			if ( uriArray.indexOf( wcap_mailchimp_setting.wcap_url_capture ) !== -1 ) {
				uriArray = uriArray.split('&');
				uriArray.forEach(element => {
					var emailArray = element.split('=');
					if ( emailArray[0] === wcap_mailchimp_setting.wcap_url_capture ){
						var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);

						if ( pattern.test( emailArray[1] ) ) {
							localStorage.setItem( "wcap_hidden_email_id", emailArray[1] );
							localStorage.setItem( "wcap_atc_user_action", "yes" );
							localStorage.setItem( "wcap_mailchimp_captured", "yes" );
							localStorage.setItem( "wcap_popup_displayed", "yes" );
							return false;
						}
					}
				});
			}
		}
	}
});