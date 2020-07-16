var $thisbutton;
var $wcap_button;
var wcap_product_cart_link;
var wcap_add_to_cart_id;
var wcap_add_to_cart_pro_id;
var wcap_add_to_cart_variation_id;
jQuery( function( $ ) {

	$(document).ready(function() {
		var wcap_last_check_date = localStorage.getItem( "wcap_popup_displayed_next_time" );
		if ( null != wcap_last_check_date ){
			if ( (new Date()).getTime() > wcap_last_check_date ){
				localStorage.removeItem( "wcap_popup_displayed_next_time" );
				localStorage.removeItem( "wcap_popup_displayed" );
			}
		}
	});

	var wcap_get_client_email = '';
	var wcap_atc_modal_data = {
        wcap_heading_section_text_email: wcap_atc_modal_param.wcap_atc_head,
        wcap_text_section_text_field:    wcap_atc_modal_param.wcap_atc_text,
        wcap_email_placeholder_section_input_text: wcap_atc_modal_param.wcap_atc_email_place,
        wcap_button_section_input_text : wcap_atc_modal_param.wcap_atc_button,
        wcap_button_bg_color : wcap_atc_modal_param.wcap_atc_button_bg_color,
        wcap_button_text_color : wcap_atc_modal_param.wcap_atc_button_text_color,
        wcap_popup_text_color : wcap_atc_modal_param.wcap_atc_popup_text_color,
        wcap_popup_heading_color : wcap_atc_modal_param.wcap_atc_popup_heading_color,
        wcap_non_mandatory_modal_input_text : wcap_atc_modal_param.wcap_atc_non_mandatory_input_text,
        wcap_atc_button: {
            backgroundColor: wcap_atc_modal_param.wcap_atc_button_bg_color,
            color          : wcap_atc_modal_param.wcap_atc_button_text_color  
        },
        wcap_atc_popup_text:{
            color          : wcap_atc_modal_param.wcap_atc_popup_text_color,  
        },
        wcap_atc_popup_heading:{
            color          : wcap_atc_modal_param.wcap_atc_popup_heading_color,   
        }
    };

    $( document.body ).on( 'wc_fragments_refreshed', function( response_data ) {
    		
    		var wcap_response = response_data.target.innerHTML;
    		/* it detect that the product is added to the cart */
    		if ( wcap_response.indexOf('button wc-forward') >= 0 ) {
    			
    			var wcap_is_popup_displayed = localStorage.getItem("wcap_popup_displayed");

				if ( ( typeof wcap_is_popup_displayed === undefined ) || ( wcap_is_popup_displayed != "yes" ) ){
					localStorage.setItem("wcap_popup_displayed", "yes");
					var wcap_next_date = new Date();
					wcap_next_date.setHours( wcap_next_date.getHours() + 24 );
					//wcap_next_date.setMinutes( wcap_next_date.getMinutes() + 4);
					localStorage.setItem("wcap_popup_displayed_next_time", wcap_next_date.getTime() );

	    	        var wcap_email_data = {
						wcap_atc_email       : localStorage.getItem("wcap_hidden_email_id"),
						wcap_atc_user_action : localStorage.getItem("wcap_atc_user_action"),
						action: 'wcap_atc_store_guest_email'
					}
					$.post( wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'wcap_atc_store_guest_email' ), wcap_email_data, function(response_dat, status, xhr ) {
						if ( status === 'success' && response_dat ) {
							localStorage.setItem( "wcap_abandoned_id", response_dat );
						}
			    	} );
				}
    		}
    } );
    
    /**
	 * AddToCartHandler class.
	 */
	var wcap_add_to_cart_modal = function() {
		$( document )
			.on( 'click', '.add_to_cart_button', this.wcap_on_shop_add_to_cart )
			.on( 'click', '.single_add_to_cart_button', this.wcap_on_shop_add_to_cart )
			.on( 'added_to_cart', this.updateButton )
			.on( 'added_to_cart', this.updateCartPage )
			.on( 'added_to_cart', this.updateFragments )
			.on( 'click', '.wcap_popup_button', this.wcap_add_to_cart_from_shop )
			.on( 'click', '.wcap_popup_non_mandatory_button', this.wcap_add_product_to_cart )
			.on( 'click', '.wcap_popup_close', close );

			$(document).keydown(function(e) {
	            if (e.keyCode == 27) {
	                close();
	            }
	        });
	};

	/**
	* Handle the add to cart event.
	*/
	wcap_add_to_cart_modal.prototype.wcap_on_shop_add_to_cart = function( e ) {
		var wcap_get_class_name = this.getAttribute('class');

		if ( wcap_get_class_name.indexOf ('product_type_variable') == -1 && wcap_get_class_name.indexOf ('product_type_booking') == -1 && wcap_get_class_name.indexOf ('product_type_bundle_input_required') == -1) {
			e.preventDefault();
			$thisbutton = $( this );

			$wcap_button = $( this );
			wcap_product_cart_link = this.getAttribute('href');
			var wcap_is_popup_displayed = localStorage.getItem("wcap_popup_displayed");

			if ( localStorage.getItem( 'wcap_mailchimp_captured' ) === 'yes' ) {
				wcap_add_product_to_cart_for_all();
			}else if ( ( ( typeof wcap_is_popup_displayed === undefined || wcap_is_popup_displayed != "yes" ) ) && "" == wcap_atc_modal_param.wcap_populate_email ) {
				wcap_open_atc_modal();
			}else{
				if ( "" != wcap_atc_modal_param.wcap_populate_email ) {
					localStorage.setItem( "wcap_hidden_email_id", wcap_atc_modal_param.wcap_populate_email );
					localStorage.setItem( "wcap_atc_user_action", "yes" );
				}
				wcap_add_product_to_cart_for_all();	
			}
		}		
	};

	/**
	 * Handle the add to cart event.
	 */
	wcap_add_to_cart_modal.prototype.wcap_add_to_cart_from_shop = function( e ) {
		
		e.preventDefault();
		var wcap_get_email_address = $('#wcap_popup_input').val();	
		
		/* https://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
		var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);

		if ( ! pattern.test( wcap_get_email_address  ) ) {
			$('#wcap_placeholder_validated_msg').text(wcap_atc_modal_param.wcap_mandatory_email_text);
			$( "#wcap_placeholder_validated_msg" ).fadeIn();
            setTimeout( function(){$( "#wcap_placeholder_validated_msg" ).fadeOut();},3000);
		}else{
			if( $( '#wcap_coupon_auto_applied' ).length > 0 ) {
				$( '#wcap_coupon_auto_applied' ).text(wcap_atc_modal_param.wcap_atc_coupon_applied_msg);
				$( '#wcap_coupon_auto_applied' ).fadeIn();
				setTimeout( function() {
					$( '#wcap_coupon_auto_applied' ).fadeOut();
					wcap_get_client_email = $('#wcap_popup_input').val();
					localStorage.setItem("wcap_hidden_email_id", wcap_get_client_email);
					localStorage.setItem("wcap_atc_user_action", "yes" );
					wcap_add_product_to_cart_for_all();
				}, 3000 );
			} else {
				wcap_get_client_email = $('#wcap_popup_input').val();
				localStorage.setItem("wcap_hidden_email_id", wcap_get_client_email);
				localStorage.setItem("wcap_atc_user_action", "yes" );
				wcap_add_product_to_cart_for_all();
			}
		}
	};

	/**
	 * Update cart page elements after add to cart events.
	 */
	wcap_add_to_cart_modal.prototype.updateButton = function( e, fragments, cart_hash, $button ) {
		$button = typeof $button === 'undefined' ? false : $button;
		if ( $button ) {
			$button.removeClass( 'loading' );
			$button.addClass( 'added' );

			// View cart text.
			if ( ! wc_add_to_cart_params.is_cart && $button.parent().find( '.added_to_cart' ).length === 0 ) {
				$button.after( ' <a href="' + wc_add_to_cart_params.cart_url + '" class="added_to_cart wc-forward" title="' +
					wc_add_to_cart_params.i18n_view_cart + '">' + wc_add_to_cart_params.i18n_view_cart + '</a>' );

				var wcap_is_popup_displayed = localStorage.getItem("wcap_popup_displayed");
				if ( ( typeof wcap_is_popup_displayed === undefined ) || ( wcap_is_popup_displayed != "yes" ) ){
					localStorage.setItem("wcap_popup_displayed", "yes");
					
					var wcap_next_date = new Date();
					wcap_next_date.setHours( wcap_next_date.getHours() + 24 );
					//wcap_next_date.setMinutes( wcap_next_date.getMinutes() + 4);
					localStorage.setItem("wcap_popup_displayed_next_time", wcap_next_date.getTime() );

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
			}
			$( document.body ).trigger( 'wc_cart_button_updated', [ $button ] );
		}
	};

	/**
	 * Update cart page elements after add to cart events.
	 */
	wcap_add_to_cart_modal.prototype.updateCartPage = function() {
		var page = window.location.toString().replace( 'add-to-cart', 'added-to-cart' );
			$( '.shop_table.cart' ).load( page + ' .shop_table.cart:eq(0) > *', function() {
			$( '.shop_table.cart' ).stop( true ).css( 'opacity', '1' ).unblock();
			$( document.body ).trigger( 'cart_page_refreshed' );
		});
		$( '.cart_totals' ).load( page + ' .cart_totals:eq(0) > *', function() {
			$( '.cart_totals' ).stop( true ).css( 'opacity', '1' ).unblock();
			$( document.body ).trigger( 'cart_totals_refreshed' );
		});
	};

	/**
	 * Update fragments after add to cart events.
	 */
	wcap_add_to_cart_modal.prototype.updateFragments = function( e, fragments ) {
		if ( fragments ) {
			$.each( fragments, function( key ) {
				$( key )
					.addClass( 'updating' )
					.fadeTo( '400', '0.6' )
					.block({
						message: null,
						overlayCSS: {
							opacity: 0.6
						}
					});
			});

			$.each( fragments, function( key, value ) {
				$( key ).replaceWith( value );
				$( key ).stop( true ).css( 'opacity', '1' ).unblock();
			});

			$( document.body ).trigger( 'wc_fragments_loaded' );
		}
	};
	
	wcap_add_to_cart_modal.prototype.wcap_add_product_to_cart = function( e ) {

		if ( "off" == wcap_atc_modal_param.wcap_atc_mandatory_email ) {
			e.preventDefault();
			localStorage.setItem("wcap_atc_user_action", "no" );
			wcap_add_product_to_cart_for_all();	
		} else {
			e.preventDefault();

			var wcap_get_email_address = $('#wcap_popup_input').val();
			var wcap_validate_text = wcap_atc_modal_param.wcap_mandatory_text;
			if ( wcap_get_email_address ) {
				wcap_validate_text = wcap_atc_modal_param.wcap_mandatory_email_text;
			}
			$('#wcap_placeholder_validated_msg').text( wcap_validate_text );

			$( "#wcap_placeholder_validated_msg" ).fadeIn();
        	
			setTimeout( function(){
				$( "#wcap_placeholder_validated_msg" ).fadeOut();
				//close();
			},3000);
		}
		
	}	
	
	function wcap_add_product_to_cart_for_all (){

		var $product_id = $thisbutton.attr( 'data-product_id' );
		if (typeof $thisbutton !== 'undefined'){ 
			if ( $thisbutton.is( '.ajax_add_to_cart' ) || $thisbutton.is( '.ajax' ) ) {
				if ( ! $product_id ) {
					var link_element = $thisbutton.attr( 'href' ),
						href_params = '',
						url_params = '';

					if ( link_element !== undefined ) {
						href_params = link_element;
					}else{
						link_element = $thisbutton.find( 'a' );
						if ( link_element.length > 0 ) {
							href_params = $( link_element[0] ).attr( 'href' );
						}else{
							return true;
						}
					}

					if ( href_params !== undefined && href_params !== '' ) {
						href_params = href_params.split('?');
						url_params = href_params[1].split( '&' );
						for( var url_subparams in url_params ){
							if ( url_params[url_subparams].indexOf( 'add-to-cart' ) !== -1 ) {
								$product_id = url_params[url_subparams].replace( 'add-to-cart=', '' );
								break;
							}
						}
					}else{
						return true;
					}
				}
				$thisbutton.removeClass( 'added' );
				$thisbutton.addClass( 'loading' );
				var data = {};
				if ( $thisbutton.attr( 'data-product_id' ) ) {
					$.each( $thisbutton.data(), function( key, value ) {
						data[ key ] = value;
					});
				}else {
					data['product_id'] = $product_id;
					data['quantity'] = '1';
				}

				if ( $( '#wcap_checkbox_status' ).length > 0 && $( '#wcap_user_ref' ).length > 0 ) {
					data[ 'wcap_checkbox_status' ] = $( '#wcap_checkbox_status' ).val();
					data[ 'wcap_user_ref' ] = $( '#wcap_user_ref' ).val();
				}

				// Trigger event.
				$( document.body ).trigger( 'adding_to_cart', [ $thisbutton, data ] );

				// Ajax action.
				$.post( wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'add_to_cart' ), data, function( response ) {
					if ( ! response ) {
						return;
					}
					if ( response.error && response.product_url ) {
						window.location = response.product_url;
						return;
					}
					close();
					// Redirect to cart option

					if ( wc_add_to_cart_params.cart_redirect_after_add === 'yes'  ) {

						if ( wc_add_to_cart_params.cart_url === null ) {
							var cart_url = '/cart';
						}else{
							var cart_url = wc_add_to_cart_params.cart_url;
						}

						window.location = cart_url;
						return;
					}
					// redirect to the cart page.
					if ( 'yes' == wcap_atc_modal_param.wcap_ajax_add ){
						// Trigger event so themes can refresh other areas.
					    $( document.body ).trigger( 'added_to_cart', [ response.fragments, response.cart_hash, $thisbutton ] );
						$thisbutton.removeClass( 'loading' );
						$thisbutton.addClass( 'added' );
						localStorage.setItem("wcap_popup_displayed", "yes");

						var wcap_next_date = new Date();
						wcap_next_date.setHours( wcap_next_date.getHours() + 24 );
						//wcap_next_date.setMinutes( wcap_next_date.getMinutes() + 4);
						localStorage.setItem("wcap_popup_displayed_next_time", wcap_next_date.getTime() );
					}

					if ( 'no' == wcap_atc_modal_param.wcap_ajax_add ){
						var wcap_is_popup_displayed = localStorage.getItem("wcap_popup_displayed");
						if ( ( typeof wcap_is_popup_displayed === undefined ) || ( wcap_is_popup_displayed != "yes" ) ){
							localStorage.setItem("wcap_popup_displayed", "no");
						}
						window.location = window.location.pathname + "?add-to-cart=" + $thisbutton.attr( $product_id );
						return;
					}

					if ( localStorage.getItem( 'wcap_checkbox_status' ) === 'checked' ) {
						console.log('Here');
						console.log(localStorage);
						FB.AppEvents.logEvent('MessengerCheckboxUserConfirmation', null, {
							'app_id': wcap_fb_params.aid,
							'page_id': wcap_fb_params.pid,
							'ref':'',
							'user_ref': localStorage.getItem( 'wcap_user_ref' )
						});
					}

					if ( $thisbutton.is( '.single_add_to_cart_button' ) ) {
						localStorage.setItem("wcap_popup_displayed", "no");
						$( document.body ).trigger( 'wc_fragments_refreshed' );
					}
				});
			}
		}
	}

	function wcap_open_atc_modal () {

		$(document.body).addClass('wcap-atc-modal-open').append('<div class="wcap-modal-overlay"></div>');
        $(document.body).append('<div class="wcap-modal" style="overflow-y:auto; max-height:90%;"><div class="wcap-modal__contents"> '+ wcap_atc_modal_param.wcap_atc_modal_data+ ' </div> </div>');
        wcap_atc_position();

        $( document.body ).trigger( 'wcap_after_atc_load' );

        var myViewModel = new Vue({
        	el: '#wcap_popup_main_div',
        	data: wcap_atc_modal_data,
        });

        $(".wcap_popup_button").prop("disabled", true);

        $("#wcap_popup_input").on("input", function(e) {
		    var wcap_get_email_address = $('#wcap_popup_input').val();
		    var is_button_disabled = $(".wcap_popup_button").is(":disabled");
		    if ( wcap_get_email_address.length > 0 && is_button_disabled == true ) {
				$(".wcap_popup_button").prop("disabled", false);		    	
		    } else if ( wcap_get_email_address.length == 0 && is_button_disabled == false ){
		    	$(".wcap_popup_button").prop("disabled", true );
		    }
		});
        
	}

	function close () {
        $(document.body).removeClass('wcap-atc-modal-open wcap-modal-loading');
        $('.wcap-modal, .wcap-modal-overlay').remove();
    }

	function wcap_atc_position() {
		$('.wcap-modal__body').removeProp('style');
		var modal_header_height = $('.wcap-modal__header').outerHeight();
        var modal_height = $('.wcap-modal').height();
        var modal_width = $('.wcap-modal').width();
        var modal_body_height = $('.wcap-modal__body').outerHeight();
        var modal_contents_height = modal_body_height + modal_header_height;

        $('.wcap-modal').css({
            'margin-left': -modal_width / 2,
            'margin-top': -modal_height / 2
        });

        if ( modal_height < modal_contents_height - 5 ) {
            $('.wcap-modal__body').height( modal_height - modal_header_height );
        }
    }
	/**
	 * Init AddToCartHandler.
	 */
	new wcap_add_to_cart_modal();
});
