/*global wc_add_to_cart_variation_params, wc_cart_fragments_params */
;(function ( $, window, document, undefined ) {
	
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
        wcap_heading_section_text_email: wcap_atc_modal_param_variation.wcap_atc_head,
        wcap_text_section_text_field:    wcap_atc_modal_param_variation.wcap_atc_text,
        wcap_email_placeholder_section_input_text: wcap_atc_modal_param_variation.wcap_atc_email_place,
        wcap_button_section_input_text : wcap_atc_modal_param_variation.wcap_atc_button,
        wcap_button_bg_color : wcap_atc_modal_param_variation.wcap_atc_button_bg_color,
        wcap_button_text_color : wcap_atc_modal_param_variation.wcap_atc_button_text_color,
        wcap_popup_text_color : wcap_atc_modal_param_variation.wcap_atc_popup_text_color,
        wcap_popup_heading_color : wcap_atc_modal_param_variation.wcap_atc_popup_heading_color,
        wcap_non_mandatory_modal_input_text : wcap_atc_modal_param_variation.wcap_atc_non_mandatory_input_text,
        wcap_atc_button: {
            backgroundColor: wcap_atc_modal_param_variation.wcap_atc_button_bg_color,
            color          : wcap_atc_modal_param_variation.wcap_atc_button_text_color  
        },
        wcap_atc_popup_text:{
            color          : wcap_atc_modal_param_variation.wcap_atc_popup_text_color,  
        },
        wcap_atc_popup_heading:{
            color          : wcap_atc_modal_param_variation.wcap_atc_popup_heading_color,   
        }
    };

    $(document).keydown(function(e) {
        if (e.keyCode == 27) {
            close();
        }
    });

    $( document.body ).on( 'wc_fragments_refreshed', function( response_dat ) {
    		
		var wcap_response = response_dat.target.innerHTML;
		/* it detect that the product is added to the cart */
		if ( wcap_response.indexOf('button wc-forward') >= 0 ){
			
			var wcap_is_popup_displayed = localStorage.getItem("wcap_popup_displayed");
			if ( ( typeof wcap_is_popup_displayed === undefined ) || ( wcap_is_popup_displayed != "yes" ) ){
				localStorage.setItem("wcap_popup_displayed", "yes");
				//localStorage.setItem("wcap_popup_displayed_time", ( new Date() ).getTime() );
				
				var wcap_next_date = new Date();
				wcap_next_date.setHours( wcap_next_date.getHours() + 24 );
				localStorage.setItem("wcap_popup_displayed_next_time", wcap_next_date.getTime() );
    	        
    	        var wcap_email_data = {
					wcap_atc_email       : localStorage.getItem("wcap_hidden_email_id"),
					wcap_atc_user_action : localStorage.getItem("wcap_atc_user_action"),
					action: 'wcap_atc_store_guest_email'
				}
				$.post( wc_cart_fragments_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'wcap_atc_store_guest_email' ), wcap_email_data, function(response_dat , status, xhr ) {
					if ( status === 'success' && response_dat ) {
						localStorage.setItem( "wcap_abandoned_id", response_dat );
					}
		    	} );
			}
		}
    } );

	/**
	 * VariationForm class which handles variation forms and attributes.
	 */

	var VariationForm = function( $form ) {

		this.$form                = $form;
		this.$attributeFields     = $form.find( '.variations select' );
		this.$singleVariation     = $form.find( '.single_variation' ),
		this.$singleVariationWrap = $form.find( '.single_variation_wrap' );
		this.$resetVariations     = $form.find( '.reset_variations' );
		this.$product             = $form.closest( '.product' );
		this.variationData        = $form.data( 'product_variations' );
		this.useAjax              = false === this.variationData;
		this.xhr                  = false;

		// Initial state.
		this.$singleVariationWrap.show();
		this.$form.off( '.wc-variation-form' );

		// Methods.
		this.getChosenAttributes    = this.getChosenAttributes.bind( this );
		this.findMatchingVariations = this.findMatchingVariations.bind( this );
		this.isMatch                = this.isMatch.bind( this );
		this.toggleResetLink        = this.toggleResetLink.bind( this );

		// Events.
		$form.on( 'click.wc-variation-form', '.reset_variations', { variationForm: this }, this.onReset );
		$form.on( 'reload_product_variations', { variationForm: this }, this.onReload );
		$form.on( 'hide_variation', { variationForm: this }, this.onHide );
		$form.on( 'show_variation', { variationForm: this }, this.onShow );
		$form.on( 'click', '.single_add_to_cart_button', { variationForm: this }, this.onAddToCart );
		$form.on( 'reset_data', { variationForm: this }, this.onResetDisplayedVariation );
		$form.on( 'reset_image', { variationForm: this }, this.onResetImage );
		$form.on( 'change.wc-variation-form', '.variations select', { variationForm: this }, this.onChange );
		$form.on( 'found_variation.wc-variation-form', { variationForm: this }, this.onFoundVariation );
		$form.on( 'check_variations.wc-variation-form', { variationForm: this }, this.onFindVariation );
		$form.on( 'update_variation_values.wc-variation-form', { variationForm: this }, this.onUpdateAttributes );

		// Init after gallery.
		setTimeout( function() {
			$form.trigger( 'check_variations' );
			$form.trigger( 'wc_variation_form' );
			self.loading = false;
		}, 100 );

		$( document )
			.on( 'added_to_cart', this.updateButton )
			.on( 'click', '.wcap_popup_button', this.wcap_add_to_cart_from_shop )
			.on( 'click', '.wcap_popup_non_mandatory_button', this.wcap_add_product_to_cart )
			.on( 'click', '.wcap_popup_close', close );

		if( $('.woo-variation-swatches').length > 0 ) {
			this.init($form);	
		}
	};

	/** Compatibility with WooCommerce Variation Swatches plugin */
    VariationForm.prototype.afterGalleryInit = function ($form) {
        setTimeout(function () {
            // $form.trigger('check_variations');
            $form.trigger('wc_variation_form');
            $form.loading = false;
        }, 100);
    };

    // Variation form events
    VariationForm.prototype.init = function ($form) {
        var _this = this;

        var product_id = $form.data('product_id');
        if (this.useAjax) {
            wp.ajax.send('wvs_get_available_variations', {
                data: {
                    product_id: product_id
                },
                success: function success(data) {
                    $form.data('product_variations', data);
                    _this.useAjax = false;

                    // Init after gallery.
                    _this.afterGalleryInit($form);
                },
                error: function error(e) {
                    // Init after gallery.
                    _this.afterGalleryInit($form);
                    //console.error('Variation not available on variation id ' + product_id + '.');
                }
            });
        } else {
            // Init after gallery.
            this.afterGalleryInit($form);
        }
    };
	/** End Compatibility with WooCommerce Variation Swatches plugin */


	/**
	 * Reset all fields.
	 */
	VariationForm.prototype.onReset = function( event ) {
		event.preventDefault();
		event.data.variationForm.$attributeFields.val( '' ).change();
		event.data.variationForm.$form.trigger( 'reset_data' );
	};

	/**
	 * Reload variation data from the DOM.
	 */
	VariationForm.prototype.onReload = function( event ) {

		var form           = event.data.variationForm;
		form.variationData = form.$form.data( 'product_variations' );
		form.useAjax       = false === form.variationData;
		form.$form.trigger( 'check_variations' );
	};

	/**
	 * When a variation is hidden.
	 */
	VariationForm.prototype.onHide = function( event ) {
		event.preventDefault();
		event.data.variationForm.$form.find( '.single_add_to_cart_button' ).removeClass( 'wc-variation-is-unavailable' ).addClass( 'disabled wc-variation-selection-needed' );
		event.data.variationForm.$form.find( '.woocommerce-variation-add-to-cart' ).removeClass( 'woocommerce-variation-add-to-cart-enabled' ).addClass( 'woocommerce-variation-add-to-cart-disabled' );
	};

	/**
	 * When a variation is shown.
	 */
	VariationForm.prototype.onShow = function( event, variation, purchasable ) {
		event.preventDefault();
		if ( purchasable ) {
			event.data.variationForm.$form.find( '.single_add_to_cart_button' ).removeClass( 'disabled wc-variation-selection-needed wc-variation-is-unavailable' );
			event.data.variationForm.$form.find( '.woocommerce-variation-add-to-cart' ).removeClass( 'woocommerce-variation-add-to-cart-disabled' ).addClass( 'woocommerce-variation-add-to-cart-enabled' );
		} else {
			event.data.variationForm.$form.find( '.single_add_to_cart_button' ).removeClass( 'wc-variation-selection-needed' ).addClass( 'disabled wc-variation-is-unavailable' );
			event.data.variationForm.$form.find( '.woocommerce-variation-add-to-cart' ).removeClass( 'woocommerce-variation-add-to-cart-enabled' ).addClass( 'woocommerce-variation-add-to-cart-disabled' );
		}
	};

	/**
	 * When the cart button is pressed.
	 */
	VariationForm.prototype.onAddToCart = function( event ) {
		
		if ( $( this ).is('.disabled') ) {
			event.preventDefault();

			if ( $( this ).is('.wc-variation-is-unavailable') ) {
				window.alert( wc_add_to_cart_variation_params.i18n_unavailable_text );
			} else if ( $( this ).is('.wc-variation-selection-needed') ) {
				window.alert( wc_add_to_cart_variation_params.i18n_make_a_selection_text );
			}
		}else{
			/* We will set this to local storage, so again we will not disply popup modal.
			*  Once we have closed the browser it will remove all stored data.
			*/
			var wcap_is_popup_displayed = localStorage.getItem("wcap_popup_displayed");
			if( ( typeof wcap_is_popup_displayed === undefined ||  wcap_is_popup_displayed != "yes" ) && 
				"" == wcap_atc_modal_param_variation.wcap_populate_email &&
				'yes' !== localStorage.getItem( 'wcap_mailchimp_captured' ) ){

				event.preventDefault();
				event.stopImmediatePropagation();
				wcap_open_atc_modal();
			}

			if ( "" != wcap_atc_modal_param_variation.wcap_populate_email ) {
				localStorage.setItem( "wcap_hidden_email_id", wcap_atc_modal_param_variation.wcap_populate_email );	
				localStorage.setItem( "wcap_atc_user_action", "yes" );
			}
		}
	};

	/**
	 * Handle the add to cart event.
	 */
	VariationForm.prototype.wcap_add_to_cart_from_shop = function( e ) {
		
		e.preventDefault();
		var wcap_get_email_address = $('#wcap_popup_input').val();	
		
		/* https://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
		var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
		if ( ! pattern.test( wcap_get_email_address  ) ) {
			$('#wcap_placeholder_validated_msg').text(wcap_atc_modal_param_variation.wcap_mandatory_email_text);
			$( "#wcap_placeholder_validated_msg" ).fadeIn();
            setTimeout( function(){$( "#wcap_placeholder_validated_msg" ).fadeOut();},3000);
		}else{
			if( $( '#wcap_coupon_auto_applied' ).length > 0 ) {
				$( '#wcap_coupon_auto_applied' ).text(wcap_atc_modal_param_variation.wcap_atc_coupon_applied_msg);
				$( '#wcap_coupon_auto_applied' ).fadeIn();
				setTimeout( function() {
					$( '#wcap_coupon_auto_applied' ).fadeOut();
					wcap_get_client_email = $('#wcap_popup_input').val();
					localStorage.setItem("wcap_hidden_email_id", wcap_get_client_email);
					
					var wcap_next_date = new Date();
					wcap_next_date.setHours( wcap_next_date.getHours() + 24 );
					localStorage.setItem("wcap_popup_displayed_next_time", wcap_next_date.getTime() );

					localStorage.setItem("wcap_atc_user_action", "yes" );
					$(".variations_form").submit();
				}, 3000 );
			} else {
				wcap_get_client_email = $('#wcap_popup_input').val();
				localStorage.setItem("wcap_hidden_email_id", wcap_get_client_email);
				
				var wcap_next_date = new Date();
				wcap_next_date.setHours( wcap_next_date.getHours() + 24 );
				localStorage.setItem("wcap_popup_displayed_next_time", wcap_next_date.getTime() );

				localStorage.setItem("wcap_atc_user_action", "yes" );
				$(".variations_form").submit();
			}
		}
	};

	/**
	 * Update cart page elements after add to cart events.
	 */
	VariationForm.prototype.updateButton = function( e, fragments, cart_hash, $button ) {
		$button = typeof $button === 'undefined' ? false : $button;
		if ( $button ) {

			// View cart text.
			if ( ! wc_add_to_cart_params.is_cart /*&& $button.parent().find( '.added_to_cart' ).length === 0*/ ) {

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
							close();
						}
		        	} );
				}
			}
			$( document.body ).trigger( 'wc_cart_button_updated', [ $button ] );
		}
	};

	VariationForm.prototype.wcap_add_product_to_cart = function( e ) {
		
		if ( "off" == wcap_atc_modal_param_variation.wcap_atc_mandatory_email ) {
			e.preventDefault();
			localStorage.setItem("wcap_atc_user_action", "no" );
			wcap_add_product_to_cart_for_all();	
		} else {
			e.preventDefault();
			//close();
			var wcap_get_email_address = $('#wcap_popup_input').val();
			var wcap_validate_text = wcap_atc_modal_param_variation.wcap_mandatory_text;
			if ( wcap_get_email_address ) {
				wcap_validate_text = wcap_atc_modal_param_variation.wcap_mandatory_email_text;
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

		close();
		$(".variations_form").submit();
	}

	function  wcap_open_atc_modal (){

		$(document.body).addClass('wcap-atc-modal-open').append('<div class="wcap-modal-overlay"></div>');
        $(document.body).append('<div class="wcap-modal" style="overflow-y:auto; max-height:90%;"><div class="wcap-modal__contents"> '+ wcap_atc_modal_param_variation.wcap_atc_modal_data+ ' </div> </div>');
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
	 * When displayed variation data is reset.
	 */
	VariationForm.prototype.onResetDisplayedVariation = function( event ) {
		var form = event.data.variationForm;
		form.$product.find( '.product_meta' ).find( '.sku' ).wc_reset_content();
		form.$product.find( '.product_weight' ).wc_reset_content();
		form.$product.find( '.product_dimensions' ).wc_reset_content();
		form.$form.trigger( 'reset_image' );
		form.$singleVariation.slideUp( 200 ).trigger( 'hide_variation' );
	};

	/**
	 * When the product image is reset.
	 */
	VariationForm.prototype.onResetImage = function( event ) {
		event.data.variationForm.$form.wc_variations_image_update( false );
	};

	/**
	 * Looks for matching variations for current selected attributes.
	 */
	VariationForm.prototype.onFindVariation = function( event ) {
		var form              = event.data.variationForm,
			attributes        = form.getChosenAttributes(),
			currentAttributes = attributes.data;

		if ( attributes.count === attributes.chosenCount ) {
			if ( form.useAjax ) {
				if ( form.xhr ) {
					form.xhr.abort();
				}
				form.$form.block( { message: null, overlayCSS: { background: '#fff', opacity: 0.6 } } );
				currentAttributes.product_id  = parseInt( form.$form.data( 'product_id' ), 10 );
				currentAttributes.custom_data = form.$form.data( 'custom_data' );
				form.xhr                      = $.ajax( {
					url: wc_cart_fragments_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'get_variation' ),
					type: 'POST',
					data: currentAttributes,
					success: function( variation ) {
						if ( variation ) {
							form.$form.trigger( 'found_variation', [ variation ] );
						} else {
							form.$form.trigger( 'reset_data' );
							form.$form.find( '.single_variation' ).after( '<p class="wc-no-matching-variations woocommerce-info">' + wc_add_to_cart_variation_params.i18n_no_matching_variations_text + '</p>' );
							form.$form.find( '.wc-no-matching-variations' ).slideDown( 200 );
						}
					},
					complete: function() {
						form.$form.unblock();
					}
				} );
			} else {
				form.$form.trigger( 'update_variation_values' );

				var matching_variations = form.findMatchingVariations( form.variationData, currentAttributes ),
					variation           = matching_variations.shift();

				if ( variation ) {
					form.$form.trigger( 'found_variation', [ variation ] );
				} else {
					form.$form.trigger( 'reset_data' );
					form.$form.find( '.single_variation' ).after( '<p class="wc-no-matching-variations woocommerce-info">' + wc_add_to_cart_variation_params.i18n_no_matching_variations_text + '</p>' );
					form.$form.find( '.wc-no-matching-variations' ).slideDown( 200 );
				}
			}
		} else {
			form.$form.trigger( 'update_variation_values' );
			form.$form.trigger( 'reset_data' );
		}

		// Show reset link.
		form.toggleResetLink( attributes.chosenCount > 0 );
	};

	/**
	 * Triggered when a variation has been found which matches all attributes.
	 */
	VariationForm.prototype.onFoundVariation = function( event, variation ) {
		var form           = event.data.variationForm,
			$sku           = form.$product.find( '.product_meta' ).find( '.sku' ),
			$weight        = form.$product.find( '.product_weight' ),
			$dimensions    = form.$product.find( '.product_dimensions' ),
			$qty           = form.$singleVariationWrap.find( '.quantity' ),
			purchasable    = true,
			variation_id   = '',
			template       = false,
			$template_html = '';

		if ( variation.sku ) {
			$sku.wc_set_content( variation.sku );
		} else {
			$sku.wc_reset_content();
		}

		if ( variation.weight ) {
			$weight.wc_set_content( variation.weight_html );
		} else {
			$weight.wc_reset_content();
		}

		if ( variation.dimensions ) {
			$dimensions.wc_set_content( variation.dimensions_html );
		} else {
			$dimensions.wc_reset_content();
		}

		form.$form.wc_variations_image_update( variation );

		if ( ! variation.variation_is_visible ) {
			template = wp.template( 'unavailable-variation-template' );
		} else {
			template     = wp.template( 'variation-template' );
			variation_id = variation.variation_id;
		}

		$template_html = template( {
			variation: variation
		} );
		$template_html = $template_html.replace( '/*<![CDATA[*/', '' );
		$template_html = $template_html.replace( '/*]]>*/', '' );

		form.$singleVariation.html( $template_html );
		form.$form.find( 'input[name="variation_id"], input.variation_id' ).val( variation.variation_id ).change();

		// Hide or show qty input
		if ( variation.is_sold_individually === 'yes' ) {
			$qty.find( 'input.qty' ).val( '1' ).attr( 'min', '1' ).attr( 'max', '' );
			$qty.hide();
		} else {
			$qty.find( 'input.qty' ).attr( 'min', variation.min_qty ).attr( 'max', variation.max_qty );
			$qty.show();
		}

		// Enable or disable the add to cart button
		if ( ! variation.is_purchasable || ! variation.is_in_stock || ! variation.variation_is_visible ) {
			purchasable = false;
		}

		// Reveal
		if ( $.trim( form.$singleVariation.text() ) ) {
			form.$singleVariation.slideDown( 200 ).trigger( 'show_variation', [ variation, purchasable ] );
		} else {
			form.$singleVariation.show().trigger( 'show_variation', [ variation, purchasable ] );
		}
	};

	/**
	 * Triggered when an attribute field changes.
	 */
	VariationForm.prototype.onChange = function( event ) {
		var form = event.data.variationForm;

		form.$form.find( 'input[name="variation_id"], input.variation_id' ).val( '' ).change();
		form.$form.find( '.wc-no-matching-variations' ).remove();

		if ( form.useAjax ) {
			
			form.$form.trigger( 'check_variations' );
		} else {
			
			form.$form.trigger( 'woocommerce_variation_select_change' );
			form.$form.trigger( 'check_variations' );
			$( this ).blur();
		}

		// Custom event for when variation selection has been changed
		form.$form.trigger( 'woocommerce_variation_has_changed' );
	};

	/**
	 * Escape quotes in a string.
	 * @param {string} string
	 * @return {string}
	 */
	VariationForm.prototype.addSlashes = function( string ) {
		string = string.replace( /'/g, '\\\'' );
		string = string.replace( /"/g, '\\\"' );
		return string;
	};

	/**
	 * Updates attributes in the DOM to show valid values.
	 */
	VariationForm.prototype.onUpdateAttributes = function( event ) {
		var form              = event.data.variationForm,
			attributes        = form.getChosenAttributes(),
			currentAttributes = attributes.data;

		if ( form.useAjax ) {
			return;
		}

		// Loop through selects and disable/enable options based on selections.
		form.$attributeFields.each( function( index, el ) {
			var current_attr_select     = $( el ),
				current_attr_name       = current_attr_select.data( 'attribute_name' ) || current_attr_select.attr( 'name' ),
				show_option_none        = $( el ).data( 'show_option_none' ),
				option_gt_filter        = ':gt(0)',
				attached_options_count  = 0,
				new_attr_select         = $( '<select/>' ),
				selected_attr_val       = current_attr_select.val() || '',
				selected_attr_val_valid = true;

			// Reference options set at first.
			if ( ! current_attr_select.data( 'attribute_html' ) ) {
				var refSelect = current_attr_select.clone();

				refSelect.find( 'option' ).removeAttr( 'disabled attached' ).removeAttr( 'selected' );

				current_attr_select.data( 'attribute_options', refSelect.find( 'option' + option_gt_filter ).get() ); // Legacy data attribute.
				current_attr_select.data( 'attribute_html', refSelect.html() );
			}

			new_attr_select.html( current_attr_select.data( 'attribute_html' ) );

			// The attribute of this select field should not be taken into account when calculating its matching variations:
			// The constraints of this attribute are shaped by the values of the other attributes.
			var checkAttributes = $.extend( true, {}, currentAttributes );

			checkAttributes[ current_attr_name ] = '';

			var variations = form.findMatchingVariations( form.variationData, checkAttributes );

			// Loop through variations.
			for ( var num in variations ) {
				if ( typeof( variations[ num ] ) !== 'undefined' ) {
					var variationAttributes = variations[ num ].attributes;

					for ( var attr_name in variationAttributes ) {
						if ( variationAttributes.hasOwnProperty( attr_name ) ) {
							var attr_val         = variationAttributes[ attr_name ],
								variation_active = '';

							if ( attr_name === current_attr_name ) {
								if ( variations[ num ].variation_is_active ) {
									variation_active = 'enabled';
								}

								if ( attr_val ) {
									// Decode entities and add slashes.
									attr_val = $( '<div/>' ).html( attr_val ).text();

									// Attach.
									new_attr_select.find( 'option[value="' + form.addSlashes( attr_val ) + '"]' ).addClass( 'attached ' + variation_active );
								} else {
									// Attach all apart from placeholder.
									new_attr_select.find( 'option:gt(0)' ).addClass( 'attached ' + variation_active );
								}
							}
						}
					}
				}
			}

			// Count available options.
			attached_options_count = new_attr_select.find( 'option.attached' ).length;

			// Check if current selection is in attached options.
			if ( selected_attr_val && ( attached_options_count === 0 || new_attr_select.find( 'option.attached.enabled[value="' + form.addSlashes( selected_attr_val ) + '"]' ).length === 0 ) ) {
				selected_attr_val_valid = false;
			}

			// Detach the placeholder if:
			// - Valid options exist.
			// - The current selection is non-empty.
			// - The current selection is valid.
			// - Placeholders are not set to be permanently visible.
			if ( attached_options_count > 0 && selected_attr_val && selected_attr_val_valid && ( 'no' === show_option_none ) ) {
				new_attr_select.find( 'option:first' ).remove();
				option_gt_filter = '';
			}

			// Detach unattached.
			new_attr_select.find( 'option' + option_gt_filter + ':not(.attached)' ).remove();

			// Finally, copy to DOM and set value.
			current_attr_select.html( new_attr_select.html() );
			current_attr_select.find( 'option' + option_gt_filter + ':not(.enabled)' ).prop( 'disabled', true );

			// Choose selected value.
			if ( selected_attr_val ) {
				// If the previously selected value is no longer available, fall back to the placeholder (it's going to be there).
				if ( selected_attr_val_valid ) {
					current_attr_select.val( selected_attr_val );
				} else {
					current_attr_select.val( '' ).change();
				}
			} else {
				current_attr_select.val( '' ); // No change event to prevent infinite loop.
			}
		});

		// Custom event for when variations have been updated.
		form.$form.trigger( 'woocommerce_update_variation_values' );
	};

	/**
	 * Get chosen attributes from form.
	 * @return array
	 */
	VariationForm.prototype.getChosenAttributes = function() {
		var data   = {};
		var count  = 0;
		var chosen = 0;

		this.$attributeFields.each( function() {
			var attribute_name = $( this ).data( 'attribute_name' ) || $( this ).attr( 'name' );
			var value          = $( this ).val() || '';

			if ( value.length > 0 ) {
				chosen ++;
			}

			count ++;
			data[ attribute_name ] = value;
		});

		return {
			'count'      : count,
			'chosenCount': chosen,
			'data'       : data
		};
	};

	/**
	 * Find matching variations for attributes.
	 */
	VariationForm.prototype.findMatchingVariations = function( variations, attributes ) {
		var matching = [];
		for ( var i = 0; i < variations.length; i++ ) {
			var variation = variations[i];

			if ( this.isMatch( variation.attributes, attributes ) ) {
				matching.push( variation );
			}
		}
		return matching;
	};

	/**
	 * See if attributes match.
	 * @return {Boolean}
	 */
	VariationForm.prototype.isMatch = function( variation_attributes, attributes ) {
		var match = true;
		for ( var attr_name in variation_attributes ) {
			if ( variation_attributes.hasOwnProperty( attr_name ) ) {
				var val1 = variation_attributes[ attr_name ];
				var val2 = attributes[ attr_name ];
				if ( val1 !== undefined && val2 !== undefined && val1.length !== 0 && val2.length !== 0 && val1 !== val2 ) {
					match = false;
				}
			}
		}
		return match;
	};

	/**
	 * Show or hide the reset link.
	 */
	VariationForm.prototype.toggleResetLink = function( on ) {
		if ( on ) {
			if ( this.$resetVariations.css( 'visibility' ) === 'hidden' ) {
				this.$resetVariations.css( 'visibility', 'visible' ).hide().fadeIn();
			}
		} else {
			this.$resetVariations.css( 'visibility', 'hidden' );
		}
	};

	/**
	 * Function to call wc_variation_form on jquery selector.
	 */
	$.fn.wc_variation_form = function() {
		new VariationForm( this );
		return this;
	};

	/**
	 * Stores the default text for an element so it can be reset later
	 */
	$.fn.wc_set_content = function( content ) {
		if ( undefined === this.attr( 'data-o_content' ) ) {
			this.attr( 'data-o_content', this.text() );
		}
		this.text( content );
	};

	/**
	 * Stores the default text for an element so it can be reset later
	 */
	$.fn.wc_reset_content = function() {
		if ( undefined !== this.attr( 'data-o_content' ) ) {
			this.text( this.attr( 'data-o_content' ) );
		}
	};

	/**
	 * Stores a default attribute for an element so it can be reset later
	 */
	$.fn.wc_set_variation_attr = function( attr, value ) {
		if ( undefined === this.attr( 'data-o_' + attr ) ) {
			this.attr( 'data-o_' + attr, ( ! this.attr( attr ) ) ? '' : this.attr( attr ) );
		}
		if ( false === value ) {
			this.removeAttr( attr );
		} else {
			this.attr( attr, value );
		}
	};

	/**
	 * Reset a default attribute for an element so it can be reset later
	 */
	$.fn.wc_reset_variation_attr = function( attr ) {
		if ( undefined !== this.attr( 'data-o_' + attr ) ) {
			this.attr( attr, this.attr( 'data-o_' + attr ) );
		}
	};

	/**
	 * Reset the slide position if the variation has a different image than the current one
	 */
	$.fn.wc_maybe_trigger_slide_position_reset = function( variation ) {
		var $form                = $( this ),
			$product             = $form.closest( '.product' ),
			$product_gallery     = $product.find( '.images' ),
			reset_slide_position = false,
			new_image_id = ( variation && variation.image_id ) ? variation.image_id : '';

		if ( $form.attr( 'current-image' ) !== new_image_id ) {
			reset_slide_position = true;
		}

		$form.attr( 'current-image', new_image_id );

		if ( reset_slide_position ) {
			$product_gallery.trigger( 'woocommerce_gallery_reset_slide_position' );
		}
	};

	/**
	 * Sets product images for the chosen variation
	 */
	$.fn.wc_variations_image_update = function( variation ) {
		var $form             = this,
			$product          = $form.closest( '.product' ),
			$product_gallery  = $product.find( '.images' ),
			$gallery_img      = $product.find( '.flex-control-nav li:eq(0) img' ),
			$product_img_wrap = $product_gallery.find( '.woocommerce-product-gallery__image, .woocommerce-product-gallery__image--placeholder' ).eq( 0 ),
			$product_img      = $product_img_wrap.find( '.wp-post-image' ),
			$product_link     = $product_img_wrap.find( 'a' ).eq( 0 );

		if ( variation && variation.image && variation.image.src && variation.image.src.length > 1 ) {
			if ( $( '.flex-control-nav li img[src="' + variation.image.thumb_src + '"]' ).length > 0 ) {
				$gallery_img = $( '.flex-control-nav li img[src="' + variation.image.thumb_src + '"]' );
				$gallery_img.trigger( 'click' );
				$form.attr( 'current-image', variation.image_id );
				return;
			} else {
				$product_img.wc_set_variation_attr( 'src', variation.image.src );
				$product_img.wc_set_variation_attr( 'height', variation.image.src_h );
				$product_img.wc_set_variation_attr( 'width', variation.image.src_w );
				$product_img.wc_set_variation_attr( 'srcset', variation.image.srcset );
				$product_img.wc_set_variation_attr( 'sizes', variation.image.sizes );
				$product_img.wc_set_variation_attr( 'title', variation.image.title );
				$product_img.wc_set_variation_attr( 'alt', variation.image.alt );
				$product_img.wc_set_variation_attr( 'data-src', variation.image.full_src );
				$product_img.wc_set_variation_attr( 'data-large_image', variation.image.full_src );
				$product_img.wc_set_variation_attr( 'data-large_image_width', variation.image.full_src_w );
				$product_img.wc_set_variation_attr( 'data-large_image_height', variation.image.full_src_h );
				$product_img_wrap.wc_set_variation_attr( 'data-thumb', variation.image.src );
				$gallery_img.wc_set_variation_attr( 'src', variation.image.thumb_src );
				$product_link.wc_set_variation_attr( 'href', variation.image.full_src );
			}
		} else {
			$product_img.wc_reset_variation_attr( 'src' );
			$product_img.wc_reset_variation_attr( 'width' );
			$product_img.wc_reset_variation_attr( 'height' );
			$product_img.wc_reset_variation_attr( 'srcset' );
			$product_img.wc_reset_variation_attr( 'sizes' );
			$product_img.wc_reset_variation_attr( 'title' );
			$product_img.wc_reset_variation_attr( 'alt' );
			$product_img.wc_reset_variation_attr( 'data-src' );
			$product_img.wc_reset_variation_attr( 'data-large_image' );
			$product_img.wc_reset_variation_attr( 'data-large_image_width' );
			$product_img.wc_reset_variation_attr( 'data-large_image_height' );
			$product_img_wrap.wc_reset_variation_attr( 'data-thumb' );
			$gallery_img.wc_reset_variation_attr( 'src' );
			$product_link.wc_reset_variation_attr( 'href' );
		}

		window.setTimeout( function() {
			$( window ).trigger( 'resize' );
			$form.wc_maybe_trigger_slide_position_reset( variation );
			$product_gallery.trigger( 'woocommerce_gallery_init_zoom' );
		}, 20 );
	};

	$(function() {
		if ( typeof wc_add_to_cart_variation_params !== 'undefined' ) {
			$( '.variations_form' ).each( function() {
				$( this ).wc_variation_form();
			});
		}
	});

	/**
	 * Matches inline variation objects to chosen attributes
	 * @deprecated 2.6.9
	 * @type {Object}
	 */
	var wc_variation_form_matcher = {
		find_matching_variations: function( product_variations, settings ) {
			var matching = [];
			for ( var i = 0; i < product_variations.length; i++ ) {
				var variation    = product_variations[i];

				if ( wc_variation_form_matcher.variations_match( variation.attributes, settings ) ) {
					matching.push( variation );
				}
			}
			return matching;
		},
		variations_match: function( attrs1, attrs2 ) {
			var match = true;
			for ( var attr_name in attrs1 ) {
				if ( attrs1.hasOwnProperty( attr_name ) ) {
					var val1 = attrs1[ attr_name ];
					var val2 = attrs2[ attr_name ];
					if ( val1 !== undefined && val2 !== undefined && val1.length !== 0 && val2.length !== 0 && val1 !== val2 ) {
						match = false;
					}
				}
			}
			return match;
		}
	};

})( jQuery, window, document );
