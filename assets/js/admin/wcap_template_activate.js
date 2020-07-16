jQuery(function( $ ) {

	$('.wcap-switch.wcap-toggle-template-status').click(function(){

		var $switch, state, new_state, template_id, id_name, template_type;

		$switch = $(this);

		if ( $switch.is('.wcap-loading') )
			return;

		state = $switch.attr( 'wcap-template-switch' );
		new_state = state === 'on' ? 'off' : 'on';

		$switch.addClass('wcap-loading');
		$switch.attr( 'wcap-template-switch', new_state );

		if( wcap_activate_params.template_type == 'sms' ) {
			template_id = $switch.attr( 'wcap-sms-id' );
			id_name = 'wcap-sms-id';
			template_type = wcap_activate_params.template_type;
		} else if( wcap_activate_params.template_type == 'emailtemplates' ) {
			template_id = $switch.attr( 'wcap-template-id' );
			id_name = 'wcap-template-id';
			template_type = wcap_activate_params.template_type;
		} else if ( wcap_activate_params.template_type == 'fb_templates' ) {
			template_id = $switch.attr( 'wcap-fb-id' );
			id_name = 'wcap-fb-id';
			template_type = 'fb';
		}
		
		$.post( ajaxurl, {
			action          : 'wcap_toggle_template_status',
			wcap_template_id: template_id,
			template_type 	: template_type, 
			current_state   : new_state
		}, function( wcap_template_response ) {
			if ( wcap_template_response.indexOf('wcap-template-updated') > -1){
				var wcap_template_response_array = wcap_template_response.split ( ':' );

				var wcap_deactivate_ids = wcap_template_response_array[1];
				var wcap_split_all_ids  = wcap_deactivate_ids.split ( ',' );

				for (i = 0; i < wcap_split_all_ids.length; i++) { 
					var selected_id = wcap_split_all_ids[i];
				
					var $list = document.querySelector('[' + id_name + '="'+ selected_id+'"]');
					$($list).attr('wcap-template-switch','off');
				}
				
			}
			$switch.removeClass('wcap-loading');
		});
	});

	/* Showing hiding discount options */
	$('#unique_coupon').click(function(){
		if ( $( "#unique_coupon" ).prop( "checked" ) == false ){
	        $('.wcap_discount_options_rows').hide();
	    } else {
	        $('.wcap_discount_options_rows').removeAttr( "style" );
	    }
	});
});