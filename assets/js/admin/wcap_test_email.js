jQuery( function( $ ) {
	
	$( '.wcap_email_filter' ).select2();
    $( "table#addedit_template input#preview_email" ).click( function() {
        $( '.ajax_img' ).show();
        var email_body            = '';
        if ( $("#wp-woocommerce_ac_email_body-wrap").hasClass( "tmce-active" ) ){
            email_body =  tinyMCE.get('woocommerce_ac_email_body').getContent();
        }else{
            email_body =  jQuery('#woocommerce_ac_email_body').val();
        }

        var subject_email_preview   = $( '#woocommerce_ac_email_subject' ).val();
        var body_email_preview      = email_body;
        var send_email_id           = $( '#send_test_email' ).val();
        var is_wc_template          = document.getElementById("is_wc_template").checked;
        var wc_template_header      = $( '#wcap_wc_email_header' ).val() != '' ? $( '#wcap_wc_email_header' ).val() : 'Abandoned cart reminder';
        var data = {

            subject_email_preview   : subject_email_preview,
            body_email_preview      : body_email_preview,
            send_email_id           : send_email_id,
            is_wc_template          : is_wc_template,
            wc_template_header      : wc_template_header,
            action                  : 'wcap_preview_email_sent'
        };

        var wcap_image_url = wcap_test_email_params.wcap_test_email_sent_image_path;

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post( ajaxurl, data, function( response ) {
            $( '.ajax_img' ).hide();
            $( "#preview_email_sent_msg" ).html( "<img src="+ wcap_image_url +">&nbsp;Email has been sent successfully." );
            $( "#preview_email_sent_msg" ).fadeIn();
            setTimeout( function(){$( "#preview_email_sent_msg" ).fadeOut();},3000);
        } );
    } );
	
});