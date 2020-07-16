(function(d, s, id){
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {return;}
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/" + wcap_fb_params.locale + "/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

window.fbAsyncInit = function() {
    FB.init({
        appId      : wcap_fb_params.aid,
        xfbml      : true,
        version    : 'v3.0'
    });

    FB.Event.subscribe('messenger_checkbox', function(e) {
        console.log("messenger_checkbox event");
        console.log(e);

        if (e.event == 'rendered') {
            console.log("Plugin was rendered");
        } else if (e.event == 'checkbox') {
            var checkboxState = e.state;

            if ( localStorage.getItem( 'wcap_checkbox_status' ) !== 'checked' ) {
                localStorage.setItem( 'wcap_checkbox_status', checkboxState );
                localStorage.setItem( 'wcap_user_ref', e.user_ref );
            }

            jQuery( '#wcap_checkbox_status' ).val( checkboxState );
            jQuery( '#wcap_user_ref' ).val( e.user_ref );
            //console.log("Checkbox state: " + checkboxState);
        } else if (e.event == 'not_you') {
            console.log("User clicked 'not you'");
        } else if (e.event == 'hidden') {
            console.log("Plugin was hidden");
        }
    });

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
}

jQuery(document).ready(function($){
    if ( $( '.fb-messenger-checkbox' ).length > 0 ) {
        $( document.body ).on( 'wcap_after_atc_load', function() {
            $( '.wcap_popup_button' ).after( '<div class="wcap_fb_consent" style="text-align:center;">' + wcap_fb_params.consent + '</div>' );
            $( '.wcap_fb_consent' ).after( $( '.fb-messenger-checkbox' ) );

            var old_user_ref = $( '.fb-messenger-checkbox' ).attr( 'user_ref' ),
                new_user_ref = 'wcap_'+Date.now(),
                plugin_query,
                iframe_src;

            $( '.fb-messenger-checkbox' ).attr( 'user_ref', new_user_ref );
            plugin_query = $( '.fb-messenger-checkbox' ).attr( 'fb-iframe-plugin-query' );
            if( plugin_query ) { 
                plugin_query = plugin_query.replace( 'user_ref='+old_user_ref, 'user_ref='+new_user_ref );
                $( '.fb-messenger-checkbox' ).attr( 'fb-iframe-plugin-query', plugin_query );
            }

            iframe_src = $( '[title="fb:messenger_checkbox Facebook Social Plugin"]' ).attr( 'src' );
            if( iframe_src ) { 
               iframe_src = iframe_src.replace( 'user_ref='+old_user_ref, 'user_ref='+new_user_ref );
                $( '[title="fb:messenger_checkbox Facebook Social Plugin"]' ).attr( 'src', iframe_src );
            }
        });
    }
});