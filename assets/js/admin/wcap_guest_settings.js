jQuery( function( $ ) {
	
	$(document).on('change', '#ac_disable_guest_cart_email', function() {
        $(this).closest('tbody').find('#ac_track_guest_cart_from_cart_page').prop('disabled', this.checked);
    });

    $('#ac_disable_guest_cart_email').click(function( event ){

		if ($(this).is(':checked')) {
			$.post( ajaxurl, {
				action    : 'wcap_is_atc_enable',
			}, function( wcap_is_atc_enable ) {
				if ( 'on' == wcap_is_atc_enable ) {
					$( "#wcap_atc_disable_msg" ).html( "The Enable Add to cart popup modal setting has been disabled. As the guest cart's are no longer captured !!" );
					$("#wcap_atc_disable_msg" ).css({ 'color': 'red' });

		            $( "#wcap_atc_disable_msg" ).fadeIn();
		            setTimeout( function(){$( "#wcap_atc_disable_msg" ).fadeOut();},3000);		
				}
			});
		}
	});

	$(document).on('change', '#ac_capture_email_from_forms', function() {
		$('#ac_email_forms_classes').prop("readonly", !this.checked);
	});

	$('#wcap_delete_coupons').click(function( event ) {
		var msg 	= "Are you sure you want delete the expired and used coupons created by Abandonment Cart Pro for WooCommerce Plugin?";
        var status 	= confirm( msg );
        if ( status == true ) {
        	// disable delete button and show loader
			$("#wcap_delete_coupons").attr( "disabled", true );
			$( ".wcap-spinner" ).removeAttr( "style" );

			$.post( ajaxurl, {
				action: 'wcap_delete_expired_used_coupon_code',
			}, function() {

			}).done(function( data ) {
		    	$( "#wcap_delete_coupons" ).attr( "disabled", false );
		    	$( ".wcap-spinner" ).hide();
		    	$( ".wcap-coupon-response-msg" ).html( data.data );
		    	$( ".wcap-coupon-response-msg" ).fadeOut(3000);
		  	}).fail(function( data ) {
			    $( "#wcap_delete_coupons" ).attr( "disabled", false );
			    $( ".wcap-spinner" ).hide();
			    $( ".wcap-coupon-response-msg" ).html( "Something went wrong. Please try deleting again." );
			    $( ".wcap-coupon-response-msg" ).fadeOut(3000);
			});
        }
	});
});