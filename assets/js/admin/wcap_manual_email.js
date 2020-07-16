jQuery( function( $ ) {
    
    $('#wcap_manual_template_name').change(
        function() {
            
            $('#wcap_manual_email_data_loading').show();
            var template_id = this.value;
            
            var data = {
                    wcap_template_id        : template_id,
                    action                  : 'wcap_change_manual_email_data'
                };
                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                $.post( ajaxurl, data, function( response ) {
                    var wcap_decode_template_data        = JSON.parse( response );

                    var wcap_subject                     = wcap_decode_template_data.subject;
                    var wcap_body                        = wcap_decode_template_data.body;
                    var wcap_wc_email_header             = wcap_decode_template_data.wc_email_header;
                    var wcap_is_wc_template              = wcap_decode_template_data.is_wc_template;
                    var wcap_coupon_code                 = wcap_decode_template_data.coupon_code;
                    var wcap_coupon_code_name            = wcap_decode_template_data.coupon_code_name;
                    var wcap_generate_unique_coupon_code = wcap_decode_template_data.generate_unique_coupon_code;
                    var wcap_wc_version                  = wcap_decode_template_data.wc_version;

                    /* Coupon Code Options */
                    var discount                         = wcap_decode_template_data.discount;
                    var discount_type                    = wcap_decode_template_data.discount_type;
                    var discount_shipping                = wcap_decode_template_data.discount_shipping;
                    var discount_expiry                  = wcap_decode_template_data.discount_expiry;
                    var discount_expiry_split            = discount_expiry.split("-");

                    $("#wcap_discount_type").val( discount_type );
                    $("#wcap_coupon_amount").val( discount );
                    $("#wcac_coupon_expiry").val( discount_expiry_split[0] );
                    $("#expiry_day_or_hour").val( discount_expiry_split[1] );

                    if ( "yes" == discount_shipping ){
                        $("#wcap_allow_free_shipping").prop("checked", true);
                    } else {
                        $("#wcap_allow_free_shipping").prop("checked", false);
                    }
                    
                    $("#woocommerce_ac_email_subject").val( wcap_subject );
                    $("#wcap_wc_email_header").val( wcap_wc_email_header );
                    
                    if ( 1 == wcap_is_wc_template ){
                        $("#is_wc_template").prop("checked", true);
                    }else{
                        $("#is_wc_template").prop("checked", false);
                    }
                    
                    if ( 1 == wcap_generate_unique_coupon_code ){
                        $("#unique_coupon").prop("checked", true);
                    }else{
                        $("#unique_coupon").prop("checked", false);
                    }
                    
                    if ( $("#wp-woocommerce_ac_email_body-wrap").hasClass( "tmce-active" ) ){
                        tinyMCE.activeEditor.setContent(wcap_body);
                    }else{
                        $("#woocommerce_ac_email_body").val(wcap_body);
                    }

                    if ( wcap_coupon_code == "" ) {
                        $('#coupon_ids').val(null).trigger('change');
                    }
                    
                    if ( wcap_coupon_code > 0 ) {
                        
                        if ( wcap_wc_version > '3.0' ){
                            $("#coupon_ids").select2("destroy");
                            $("#coupon_ids").append('<option value='+ wcap_coupon_code +'  selected >'+ wcap_coupon_code_name +'</option>').change();
                            $("#coupon_ids").select2();
                        }else{
                            $('#coupon_ids').select2('data', {id: wcap_coupon_code, text: wcap_coupon_code_name });
                        }
                    }else{
                        $('#coupon_ids').val(null).trigger('change');
                    }
                    
                    $('#wcap_manual_email_data_loading').hide();
                } );
        }
    );
});