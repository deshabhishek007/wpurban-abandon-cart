jQuery( document ).ready( function() {

    jQuery("#wcap_gdpr_no_thanks").click(  function () {
        
        // run an ajax call and save the data that user did not give consent
        var data = {
            action          : 'wcap_gdpr_refused'
        };
        jQuery.post( wcap_registered_capture_params.ajax_url, data, function() {
            jQuery("#wcap_gdpr_message_block").empty().append("<span style='font-size: small'>" + 
            wcap_registered_capture_params._gdpr_after_no_thanks_msg + "</span>").delay(5000).fadeOut();
        });
        
    } );

});
