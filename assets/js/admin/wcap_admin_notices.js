jQuery(document).ready(function() {

	jQuery( '#wcap_cron_notice' ).on( 'click', '.notice-dismiss', function() {
		var data = {
			notice: 'wcap_scheduler_update_dismiss',
			action: "wcap_dismiss_admin_notice"
		};

		var admin_url = wcap_dismiss_params.ajax_url;
			jQuery.post( admin_url, data, function( response ) {
		});

	});
});