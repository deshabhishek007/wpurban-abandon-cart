jQuery( document ).ready( function() {
	
	if( jQuery( '#wcap_timer' ).length > 0 ) {
    	var countDownDate = new Date(wcap_atc_coupon_countdown_params._wcap_coupon_expires).getTime();

    	const server_offset = wcap_atc_coupon_countdown_params._wcap_server_offset * 1000;
	    // Update the count down every 1 second
		var x = setInterval(function() {

			// Get today's date and time
			var now = new Date().getTime();
			var offset = (-1) * new Date().getTimezoneOffset() * 60000;
			var gmt = Math.round(new Date(now - offset).getTime() );

			var server_time = Math.round(new Date(gmt + server_offset).getTime() );
			
			// Find the distance between now and the count down date
			var distance = countDownDate - server_time;
			// Time calculations for days, hours, minutes and seconds
			//	  var days = Math.floor(distance / (1000 * 60 * 60 * 24));
			var hours = Math.floor(distance / (1000 * 60 * 60));
			var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
			var seconds = Math.floor((distance % (1000 * 60)) / 1000);

			var countdown_timer = '';
			if( hours > 0 ) {
				countdown_timer += hours + "h ";
			}

			if( minutes > 0 ) {
				countdown_timer += minutes + "m ";
			}

			if( seconds > 0 ) {
				countdown_timer += seconds + "s ";
			}
			// Display the result in the element with id="wcap_timer"
			document.getElementById("wcap_timer").innerHTML = countdown_timer;

			// If the count down is finished, write some text
			if (distance < 0) {
				clearInterval(x);
				document.getElementById("wcap_float").innerHTML = wcap_atc_coupon_countdown_params._wcap_expiry_msg;
			}
		}, 1000);

		jQuery( '#wcap_countdown_dismiss' ).on( 'click', function() {

			var data = {
				action: 'wcap_coupon_countdown_dismissed'
			};

			jQuery.post( wcap_atc_coupon_countdown_params.ajax_url, data, function( response ) {
				jQuery( '#wcap_primary' ).hide();
			});
		});
	}
});