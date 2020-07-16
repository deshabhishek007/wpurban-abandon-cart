jQuery( function( $ ) {
	
	var data = {
        action: 'wcap_dashboard_widget_report'
     };

     // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
     $.get( ajaxurl, data, function( response ) {
        $('#abandoned_dashboard_carts .inside').html( response );
     } );
	
});