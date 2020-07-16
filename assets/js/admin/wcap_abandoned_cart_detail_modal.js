/**
 * Abandoned cart detail Modal
 */

var Modal;
var wcap_clicked_cart_id;
var $wcap_get_email_address;
var $wcap_customer_details;
var $wcap_cart_total;
var $wcap_abandoned_date;
var $wcap_abandoned_status; 
var email_body;
var $wcap_cart_status;
var $wcap_show_customer_detail;

jQuery(function($) {

    Modal = {
        init: function(){

            $(document.body).on( 'click', '.wcap-js-close-modal', this.close );
            $(document.body).on( 'click', '.wcap-modal-overlay', this.close );
            $(document.body).on( 'click', '.wcap-js-open-modal', this.handle_link );
            $(document.body).on( 'click', '.wcap_customer_detail_modal', this.handle_customer_details );
            $(document.body).on( 'mousedown', '.wcap-js-open-modal', this.handle_link_mouse_middle_click );
            $(document.body).on( 'click', '.wcap_admin_unsubscribe', this.wcap_admin_unsubscribe );

            $(window).resize(function(){
                Modal.position();
            });

            $(document).keydown(function(e) {
                if (e.keyCode == 27) {
                    Modal.close();
                }
            });

        },
        handle_customer_details: function ( event ){
            event.preventDefault();
            var wcap_text_of_event = $(event.target).text();
            if ( wcap_text_of_event.indexOf ('Hide') == -1 ){
                $( ".wcap_modal_customer_all_details" ).fadeIn();
                Modal.position();
                $(event.target).text('Hide Details') ;
            }else{
                $( ".wcap_modal_customer_all_details" ).fadeOut();
                Modal.position();
                $(event.target).text('Show Details') ;
            }
        },
        handle_link_mouse_middle_click: function( e ){
            
           if( e.which == 2 ) {
                var wcap_get_currentpage = window.location.href;    
                this.href = wcap_get_currentpage;
                e.preventDefault();
                return false;
           }
        },
        handle_link: function( e ){
            e.preventDefault();

            var $a = $( this );
            var current_page   = ''; 
            var wcap_get_currentpage = window.location.href;
            var $wcap_get_email_address;
            var $wcap_break_email_text;
            var $email_text;
            var $wcap_row_data;
            
            if ( wcap_get_currentpage.indexOf('action=emailstats') == -1 ){ 
                $wcap_row_data = $a.closest("tr")[0];
                $email_text = $wcap_row_data.getElementsByTagName('td')[1].innerHTML;
                $wcap_break_email_text  = $email_text.split('<div');
                $wcap_get_email_address = $wcap_break_email_text[0];

                $wcap_customer_details = $wcap_row_data.getElementsByTagName('td')[2].innerHTML;
                $wcap_cart_total       = $wcap_row_data.getElementsByTagName('td')[3].innerHTML;
                $wcap_abandoned_date   = $wcap_row_data.getElementsByTagName('td')[4].innerHTML;
                $wcap_abandoned_status = $wcap_row_data.getElementsByClassName('status')[0].firstChild.innerText;
                
                $wcap_cart_status = $wcap_abandoned_status;
            }else{
                current_page            = 'send_email';
                $wcap_get_email_address = '';
                $wcap_customer_details  = '';
                $wcap_cart_total        = '';
                $wcap_abandoned_date    = '';
                $wcap_cart_status       = '';
            }

            $wcap_show_customer_detail = '<br><a href="#" id "wcap_customer_detail_modal"> Show Details </a>';
            
            email_body = '<div class="wcap-modal__body"> <div class="wcap-modal__body-inner"> <table cellspacing="0" cellpadding="6" border="1" class="wcap-cart-table"> <thead><th>Email Address</th><th>Customer Details</th><th>Order Total</th><th> Abandoned Date</th></tr></thead><tbody><tr><td>'+  $wcap_get_email_address+ '</td><td>'+ $wcap_customer_details + $wcap_show_customer_detail +'</td><td>'+ $wcap_cart_total+' </td><td>' +$wcap_abandoned_date+' </td></tr></tbody></table></div> </div>';

            var type = $a.data('modal-type');
            
            if ( type == 'ajax' )
            {
                wcap_clicked_cart_id     = $a.data('wcap-cart-id');
                Modal.open( 'type-ajax' );
                Modal.loading();
                var data = {
                    action                : 'wcap_abandoned_cart_info',
                    wcap_cart_id          : wcap_clicked_cart_id,
                    wcap_email_address    : $wcap_get_email_address,
                    wcap_customer_details : $wcap_customer_details,
                    wcap_cart_total       : $wcap_cart_total,
                    wcap_abandoned_date   : $wcap_abandoned_date,
                    wcap_abandoned_status : $wcap_cart_status,
                    wcap_current_page     : current_page
                }

                $.post( ajaxurl, data , function( response ){

                    Modal.contents( response ); 
                });
            }
        },

        open: function( classes ) {

            $(document.body).addClass('wcap-modal-open').append('<div class="wcap-modal-overlay"></div>');
            var modal_body = '<div class="wcap-modal ' + classes + '"><div class="wcap-modal__contents"> <div class="wcap-modal__header"><h1>Cart #'+wcap_clicked_cart_id+'</h1>'+$wcap_cart_status+'</div>'+ email_body +' <div class = "wcap-modal-cart-content-hide" id ="wcap_remove_class">  </div> </div>  <div class="wcap-icon-close wcap-js-close-modal"></div>    </div>';

            $(document.body).append( modal_body );

            this.position();
        },

        loading: function() {
            $(document.body).addClass('wcap-modal-loading');
        },

        contents: function ( contents ) {
            $(document.body).removeClass('wcap-modal-loading');

            contents = contents.replace(/\\(.)/mg, "$1");

            $('.wcap-modal__contents').html(contents);

            this.position();
        },

        close: function() {
            $(document.body).removeClass('wcap-modal-open wcap-modal-loading');
            
            $('.wcap-modal, .wcap-modal-overlay').remove();
        },

        position: function() {

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
        },

        wcap_admin_unsubscribe: function() {

            var $a                  = $( this );
            wcap_clicked_cart_id    = $a.data('wcap-cart-id');
            var data = {
                action          : 'wcap_admin_unsubscribe_cart',
                wcap_cart_id    : wcap_clicked_cart_id,
            }

            $a.text('Processing...');

            $.post( ajaxurl, data , function( response ){
                $a.text(response.data);
                
                setTimeout( function(){
                    $('.wcap-js-close-modal' ).click();
                    location.reload();    
                },500);
            });

            return false;
        }
    };
    Modal.init();
});