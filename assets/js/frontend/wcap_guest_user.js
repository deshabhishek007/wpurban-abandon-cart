jQuery( function( $ ) {

    if( localStorage.wcap_hidden_email_id && !$( '#billing_email' ).val() ){
        $( '#billing_email' ).val( localStorage.wcap_hidden_email_id );
    }

    var wcap_checkout_fields = {
            billing_first_name: '',
            billing_last_name : '',
            billing_phone     : '',
            billing_email     : ''
        },
        timer                = 0,
        wcap_record_added    = false;

    var gdpr_consent = true;

    var email_field = 'billing_email';
    if ( !$( 'input#billing_email' ).length && $('input#shipping_email').length ) {
        email_field = 'shipping_email';
    }

    var phone_field = 'billing_phone';
    if ( !$( 'input#billing_phone' ).length && $('input#shipping_phone').length ) {
        phone_field = 'shipping_phone';
    }
    
    $( 'input#' + email_field + ', input#' + phone_field + ', input#billing_first_name, input#billing_last_name' ).on( 'change', function() {

        if ( this.id && this.id !== email_field ) {
            timer = 3000;
        }

        setTimeout( function(){
            var $wcap_is_valid_field_value = $(this).closest(".form-row"),
                wcap_validated             = true,
                wcap_validate_required     = $wcap_is_valid_field_value.is( '.validate-required' ),
                wcap_validate_email        = $wcap_is_valid_field_value.is( '.validate-email' ),
                $wcap_this                 = $( this );

            if ( wcap_validate_email ) {
                if ( $wcap_this.val() ) {
                    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);

                    if ( ! pattern.test( $wcap_this.val()  ) ) {
                        wcap_validated = false;
                    }
                }
            }

            if ( wcap_validate_required ) {
                if ( $wcap_this.val() === '' ) {
                    wcap_validated = false;
                }
            }

            var message_data = wcap_capture_guest_user_params._show_gdpr_message ? wcap_capture_guest_user_params._show_gdpr_message : true;

            if ( wcap_validated &&
                 ( wcap_checkout_fields.billing_first_name !== $( '#billing_first_name' ).val() ||
                   wcap_checkout_fields.billing_last_name  !== $( '#billing_last_name' ).val() || 
                   wcap_checkout_fields.billing_phone      !== $( '#' + phone_field ).val() || 
                   wcap_checkout_fields.billing_email      !== $( '#' + email_field ).val() ) && 
                   ( gdpr_consent && message_data ) ) {

                var data = {
                    billing_first_name  : $( '#billing_first_name' ).val(),
                    billing_last_name   : $( '#billing_last_name' ).val(),
                    billing_company     : $( '#billing_company' ).val(),
                    billing_address_1   : $( '#billing_address_1' ).val(),
                    billing_address_2   : $( '#billing_address_2' ).val(),
                    billing_city        : $( '#billing_city' ).val(),
                    billing_state       : $( '#billing_state' ).val(),
                    billing_postcode    : $( '#billing_postcode' ).val(),
                    billing_country     : $( '#billing_country' ).val(),
                    billing_phone       : $( '#' + phone_field ).val(),
                    billing_email       : $( '#' + email_field ).val(),
                    order_notes         : $( '#order_comments' ).val(),
                    shipping_first_name : $( '#shipping_first_name' ).val(),
                    shipping_last_name  : $( '#shipping_last_name' ).val(),
                    shipping_company    : $( '#shipping_company' ).val(),
                    shipping_address_1  : $( '#shipping_address_1' ).val(),
                    shipping_address_2  : $( '#shipping_address_2' ).val(),
                    shipping_city       : $( '#shipping_city' ).val(),
                    shipping_state      : $( '#shipping_state' ).val(),
                    shipping_postcode   : $( '#shipping_postcode' ).val(),
                    shipping_country    : $( '#shipping_country' ).val(),
                    ship_to_billing     : $( '#shiptobilling-checkbox' ).val(),
                    action              : 'wcap_save_guest_data'
                };

                if ( localStorage.wcap_abandoned_id ) {
                    data.wcap_abandoned_id = localStorage.wcap_abandoned_id;
                }

                if ( localStorage.wcap_atc_user_action && localStorage.wcap_atc_user_action === 'yes' ) {
                    wcap_record_added = true;
                }

                data.wcap_record_added = wcap_record_added;

                wcap_checkout_fields.billing_first_name = data.billing_first_name;
                wcap_checkout_fields.billing_last_name  = data.billing_last_name;
                wcap_checkout_fields.billing_phone      = data.billing_phone;
                wcap_checkout_fields.billing_email      = data.billing_email;

                $.post( wcap_capture_guest_user_params.ajax_url, data, function( response ) {

                    wcap_record_added = true;
                } );
            }
        }, timer );
    } );

    $( document ).ready( function() {

	    if ( wcap_capture_guest_user_params._show_gdpr_message && ! $( "#wcap_gdpr_message_block" ).length && gdpr_consent ) {
	        $("#" + email_field ).after("<span id='wcap_gdpr_message_block'> <span style='font-size: small'> "+ wcap_capture_guest_user_params._gdpr_message +" <a style='cursor: pointer' id='wcap_gdpr_no_thanks'> "+ wcap_capture_guest_user_params._gdpr_nothanks_msg +" </a></span></span>");
	    }

	    $("#wcap_gdpr_no_thanks").click( function () {
	        wcap_capture_guest_user_params._show_gdpr_message = false;
	        
	        gdpr_consent = false;        
	        
	        // run an ajax call and save the data that user did not give consent
	        var data = {
	            action : 'wcap_gdpr_refused'
	        };
	        $.post( wcap_capture_guest_user_params.ajax_url, data, function() {
	            $("#wcap_gdpr_message_block").empty().append("<span style='font-size: small'>" + 
	            wcap_capture_guest_user_params._gdpr_after_no_thanks_msg + "</span>").delay(5000).fadeOut();
	        });
	        
	    });
	});
});