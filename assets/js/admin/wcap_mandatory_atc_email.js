jQuery(function( $ ) {

	$('.wcap-switch-atc-modal-mandatory.wcap-toggle-atc-modal-mandatory').click(function(){

		var $switch_mandatory, state_mandatory, new_state_mandatory;

		$switch_mandatory = $(this);

		if ( $switch_mandatory.is('.wcap-loading') ){
			return;
		}

		state_mandatory = $switch_mandatory.attr( 'wcap-atc-switch-modal-mandatory' );

		new_state_mandatory = state_mandatory === 'on' ? 'off' : 'on';
		$switch_mandatory.addClass('wcap-loading');
		$switch_mandatory.attr( 'wcap-atc-switch-modal-mandatory', new_state_mandatory );

		if ( 'off' == new_state_mandatory ){
			$(".wcap_non_mandatory_modal_section_fields_div :input").attr("disabled", false);
		}else if ( 'on' == new_state_mandatory ){
			$(".wcap_non_mandatory_modal_section_fields_div :input").attr("disabled", true);
		}

		$.post( ajaxurl, {
			action    : 'wcap_toggle_atc_mandatory_status',
			new_state : new_state_mandatory
		}, function( wcap_atc_enable_response ) {
			$switch_mandatory.removeClass('wcap-loading');
		});
	});

	$('.wcap-auto-apply-coupons-atc.wcap-toggle-auto-apply-coupons-status').click(function(){

		var $switch_auto_coupon, state_auto, new_state_auto;

		$switch_auto_coupon = $(this);

		if ( $switch_auto_coupon.is('.wcap-loading') ){
			return;
		}

		state_auto = $switch_auto_coupon.attr( 'wcap-atc-switch-coupon-enable' );

		new_state_auto = state_auto === 'on' ? 'off' : 'on';
		$switch_auto_coupon.addClass('wcap-loading');
		$switch_auto_coupon.attr( 'wcap-atc-switch-coupon-enable', new_state_auto );

		if ( 'off' == new_state_auto ){
			$(".wcap_coupon_settings_div_table").attr("disabled", false);
		}else if ( 'on' == new_state_auto ){
			$(".wcap_coupon_settings_div_table").attr("disabled", true);
		}

		$.post( ajaxurl, {
			action    : 'wcap_toggle_atc_auto_coupon_settings',
			new_state : new_state_auto,
			update_record_key : 'wcap_atc_auto_apply_coupon_enabled'
		}, function( wcap_atc_enable_response ) {
			$switch_auto_coupon.removeClass('wcap-loading');
		});
	});

	$('.wcap-countdown-timer-cart.wcap-toggle-countdown-timer-cart').click(function(){

		var $switch_auto_coupon, state_auto, new_state_auto;

		$switch_auto_coupon = $(this);

		if ( $switch_auto_coupon.is('.wcap-loading') ){
			return;
		}

		state_auto = $switch_auto_coupon.attr( 'wcap-atc-countdown-timer-cart-enable' );

		new_state_auto = state_auto === 'on' ? 'off' : 'on';
		$switch_auto_coupon.addClass('wcap-loading');
		$switch_auto_coupon.attr( 'wcap-atc-countdown-timer-cart-enable', new_state_auto );

		if ( 'off' == new_state_auto ){
			$(".wcap_coupon_settings_div_table").attr("disabled", false);
		}else if ( 'on' == new_state_auto ){
			$(".wcap_coupon_settings_div_table").attr("disabled", true);
		}

		$.post( ajaxurl, {
			action    : 'wcap_toggle_atc_auto_coupon_settings',
			new_state : new_state_auto,
			update_record_key : 'wcap_countdown_cart'
		}, function( wcap_atc_enable_response ) {
			$switch_auto_coupon.removeClass('wcap-loading');
		});
	});

	$('#wcap_atc_coupon_type').on( 'change', function() {
		var coupon_type = $('#wcap_atc_coupon_type').val();
		if ( 'unique' == coupon_type ) {
			$('.wcap_atc_pre_selected').hide();
			$('.wcap_atc_unique').show();
		} else if ( 'pre-selected' == coupon_type ) {
			$('.wcap_atc_unique').hide();
			$('.wcap_atc_pre_selected').show();
		}
	});

	$( document ).ready(function() {
		var coupon_type = $('#wcap_atc_coupon_type').val();
		if ( 'unique' == coupon_type ) {
			$('.wcap_atc_pre_selected').hide();
			$('.wcap_atc_unique').show();
		} else if ( 'pre-selected' == coupon_type ) {
			$('.wcap_atc_unique').hide();
			$('.wcap_atc_pre_selected').show();
		}
	});
});