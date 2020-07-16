jQuery(function( $ ) {

	$('.wcap-import-yes').click(function( event ){
		event.preventDefault();
		
		$('.wcap_import_checkboxes').fadeIn();
		$('.wcap_import_yes_no').fadeOut();
		
		//console.log($('#wcap_abandoned_cart_import').is(":checked"));
		// var wcap_import_abandoned_carts = $('#wcap_abandoned_cart_import').is(":checked");
		// var wcap_import_settings = $('#wcap_settings_import').is(":checked"); 
		// var wcap_import_template = $('#wcap_email_template_import').is(":checked"); 
		// $.post( ajaxurl, {
		// 	action    : 'wcap_import_lite_data',
		// 	wcap_import_ac_cart : wcap_import_abandoned_carts,
		// 	wcap_import_settings: wcap_import_settings,
		// 	wcap_import_template: wcap_import_template
		// }, function( wcap_import_lite_data_response ) {
		// 	//console.log( wcap_import_lite_data_response );	
		// });
		// window.location = 'admin.php?page=woocommerce_ac_page&action=wcap_dashboard';
	});

	$('.wcap-import-no').click(function( event ){
		event.preventDefault();
		
		$.post( ajaxurl, {
			action    : 'wcap_do_not_import_lite_data',
			
		}, function( wcap_do_not_import_lite_data_response ) {
			window.location = 'admin.php?page=woocommerce_ac_page&action=wcap_dashboard';
		});
	});

	$('.wcap-import-now').click(function( event ){
		event.preventDefault();
		
		///$('.wcap_import_checkboxes').fadeIn();
		//console.log($('#wcap_abandoned_cart_import').is(":checked"));
		var wcap_import_abandoned_carts = $('#wcap_abandoned_cart_import').is(":checked");
		var wcap_import_settings = $('#wcap_settings_import').is(":checked"); 
		var wcap_import_template = $('#wcap_email_template_import').is(":checked"); 
		$.post( ajaxurl, {
			action    : 'wcap_import_lite_data',
			wcap_import_ac_cart : wcap_import_abandoned_carts,
			wcap_import_settings: wcap_import_settings,
			wcap_import_template: wcap_import_template
		}, function( wcap_import_lite_data_response ) {
			//console.log( wcap_import_lite_data_response );	
		});
		window.location = 'admin.php?page=woocommerce_ac_page&action=wcap_dashboard&wcap_lite_import=YES';
	});

	$('#wcap_plugin_page_import').click(function( event ){
		event.preventDefault();
		
		///$('.wcap_import_checkboxes').fadeIn();
		//console.log($('#wcap_abandoned_cart_import').is(":checked"));
		
		window.location = 'admin.php?page=wcap-update&wcap_plugin_link=wcap-update';
	});
});