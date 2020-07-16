jQuery(function( $ ) {

	$('.wcap-enable-atc-modal.wcap-toggle-atc-modal-enable-status').click(function(){

		var $switch, state, new_state;

		$switch = $(this);

		if ( $switch.is('.wcap-loading') ){
			return;
		}

		state = $switch.attr( 'wcap-atc-switch-modal-enable' );
		new_state = state === 'on' ? 'off' : 'on';

		$switch.addClass('wcap-loading');
		$switch.attr( 'wcap-atc-switch-modal-enable', new_state );

		

		if ( 'off' == new_state ){
			$(".wcap_atc_all_fields_container :input").attr("disabled", true);
			$(".wcap_atc_all_fields_container :submit").attr("disabled", true);
			$("select").attr("disabled", true);
		}else if ( 'on' == new_state ){
			$(".wcap_atc_all_fields_container :input").attr("disabled", false);
			$(".wcap_atc_all_fields_container :submit").attr("disabled", false);
			$("select").attr("disabled", false);
		}

		$.post( ajaxurl, {
			action    : 'wcap_toggle_atc_enable_status',
			new_state : new_state
		}, function( wcap_atc_enable_response ) {
			
			$switch.removeClass('wcap-loading');
		});
	});
});