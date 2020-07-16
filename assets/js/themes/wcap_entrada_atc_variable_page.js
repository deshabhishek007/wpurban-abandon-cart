jQuery( function( $ ) {
	$(document).ready(function() {

		
		var wcap_last_check_date = localStorage.getItem( "wcap_popup_displayed_next_time" );
		if ( null != wcap_last_check_date ) {
			if ( (new Date()).getTime() > wcap_last_check_date ) {
				localStorage.removeItem( "wcap_popup_displayed_next_time" );
				localStorage.removeItem( "wcap_popup_displayed" );
			}
		}
	});

	var wcap_product_id 	   = "";
	var wcap_form_variation_id = "";	
	var wcap_variation_href    = "";
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

    $( document.body ).on( 'wc_fragments_refreshed', function( response_dat ) {
    		
		/* it detect that the product is added to the cart */
		var wcap_is_popup_displayed = localStorage.getItem("wcap_popup_displayed");
		var wcap_is_popup_time = localStorage.getItem("wcap_popup_displayed_next_time");
		
		if ( ( wcap_is_popup_time !== null && wcap_is_popup_displayed != "yes" )  ) {
			localStorage.setItem("wcap_popup_displayed", "yes");
			

	        var wcap_email_data = {
				wcap_atc_email       : localStorage.getItem("wcap_hidden_email_id"),
				wcap_atc_user_action : localStorage.getItem("wcap_atc_user_action"),
				action: 'wcap_atc_store_guest_email'
			}
			$.post( wc_cart_fragments_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'wcap_atc_store_guest_email' ), wcap_email_data, function(response_dat , status, xhr ) {
				
	    	} );
		}
    } );

	/**
	 * AddToCartHandler class.
	 */
	var wcap_single_simple_atc = function() {
		$( document )
			.on( 'click', '.middle', this.wcap_on_single_add_to_cart )
			.on( 'click', '.wcap_popup_button', this.wcap_add_to_cart_from_shop )
			.on( 'click', '.wcap_popup_non_mandatory_button', this.wcap_add_product_to_cart )
			.on( 'click', '.wcap_popup_close', this.wcap_add_product_to_cart );

	};

	/**
	* Handle the add to cart event.
	*/
	wcap_single_simple_atc.prototype.wcap_on_single_add_to_cart = function( e ) {
		
		var wcap_is_popup_displayed = localStorage.getItem("wcap_popup_displayed");
		if ( ( typeof wcap_is_popup_displayed === undefined ||  wcap_is_popup_displayed != "yes" ) && "" == wcap_atc_modal_param.wcap_populate_email ){
			e.preventDefault();
			
			wcap_variation_href = $(this).find("a").attr("href");

			wcap_open_atc_modal();
		}
		if ( "" != wcap_atc_modal_param.wcap_populate_email ) {
			localStorage.setItem( "wcap_hidden_email_id", wcap_atc_modal_param.wcap_populate_email );
			localStorage.setItem( "wcap_atc_user_action", "yes" );
		}
	};

	function  wcap_open_atc_modal (){

		$(document.body).addClass('wcap-atc-modal-open').append('<div class="wcap-modal-overlay"></div>');
        $(document.body).append('<div class="wcap-modal"><div class="wcap-modal__contents"> '+ wcap_atc_modal_param.wcap_atc_modal_data+ ' </div> </div>');
        wcap_atc_position();
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
	 * Handle the add to cart event.
	 */
	wcap_single_simple_atc.prototype.wcap_add_to_cart_from_shop = function( e ) {
		
		e.preventDefault();
		var wcap_get_email_address = $('#wcap_popup_input').val();	
		
		/* https://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
		var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);

		if ( ! pattern.test( wcap_get_email_address  ) ) {
			$('#wcap_placeholder_validated_msg').text(wcap_atc_modal_param.wcap_mandatory_email_text);
			$( "#wcap_placeholder_validated_msg" ).fadeIn();
            setTimeout( function(){$( "#wcap_placeholder_validated_msg" ).fadeOut();},3000);
		}else{

			wcap_get_client_email = $('#wcap_popup_input').val();
			localStorage.setItem("wcap_hidden_email_id", wcap_get_client_email);
			localStorage.setItem("wcap_atc_user_action", "yes" );
			wcap_add_product_to_cart_for_all();
		}
	};

	wcap_single_simple_atc.prototype.wcap_add_product_to_cart = function( e ) {
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
		var wcap_is_popup_displayed = localStorage.getItem("wcap_popup_displayed");

		if ( ( typeof wcap_is_popup_displayed === undefined ) || ( wcap_is_popup_displayed != "yes" ) ){
			wcap_get_client_email = $('#wcap_popup_input').val();
			localStorage.setItem("wcap_hidden_email_id", wcap_get_client_email);
			var wcap_next_date = new Date();
			wcap_next_date.setHours( wcap_next_date.getHours() + 24);
			localStorage.setItem("wcap_popup_displayed_next_time", wcap_next_date.getTime() );
		}

		window.location =  wcap_variation_href ;
	}

	new wcap_single_simple_atc();
});
