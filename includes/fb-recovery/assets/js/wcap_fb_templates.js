// Global vars ajaxurl
'use strict';
jQuery(document).ready(function($){

    // Delete FB Template
    $( '.delete_fb' ).on( 'click', function() {

        var req = confirm( 'Are you sure you want to delete?' );

        if ( req ) {
            var id = this.id,
                id_params,
                fb_template_id,
                data;

            id_params =  id.split( '_' );
            fb_template_id = id_params[1];

            var data = {
                template_id: fb_template_id,
                action: 'wcap_delete_sms_template'
            }

            $.post( ajaxurl, data, function( response ) {
                // Remove the row from the table
                $( '#fb_' + fb_template_id ).remove();
            });
        }
    });

    var vm,
        template_id = '',
        template_data = '';

    $('#wcap-fb-modal').on('shown.bs.modal', function( event ) {
        var frame,
            
            parent_button;

        parent_button = $( event.relatedTarget );

        template_id = parent_button.hasClass( 'edit_fb' ) ? parent_button.data( 'wcap-template-id' ) : '';
        template_data = template_id ? parent_button.data( 'wcap-template' ) : '';

        if ( vm && template_id ) {
            vm.wcap_load_data( template_id, template_data );
        }else if ( vm && !template_id ) {
            vm.wcap_clear_data();
        }

        // when modal was opened once, the instance of vm already exists and therefore shall not be recreated
        if ( !vm ) {
            vm = new Vue({
                el: '#wcap-fb-modal',
                data: function() {
                    return{
                        wcap_send_num: '1',
                        wcap_send_freq: 'minutes',
                        wcap_subject: '',
                        wcap_header: '',
                        wcap_subheader: '',
                        wcap_image_url: wcap_fb_params.wcap_fb_header_image,
                        wcap_checkout: '',
                        wcap_unsubscribe_text: '',
                        wcap_active_status: 0,
                        wcap_default_image: wcap_fb_params.wcap_fb_header_image
                    }
                },
                methods: {
                    wcap_select_image: function(event) {
                        event.preventDefault();
            
                        // If the media frame already exists, reopen it.
                        if ( frame ) {
                            frame.open();
                            return;
                        }
                        
                        // Create a new media frame
                        frame = wp.media({
                            title: 'Select or Upload an Image',
                            button: {
                                text: 'Use this media'
                            },
                            multiple: false  // Set to true to allow multiple files to be selected
                        });

                        
                        // When an image is selected in the media frame...
                        frame.on( 'select', function() {

                            // Get media attachment details from the frame state
                            var attachment = frame.state().get('selection').first().toJSON();

                            $( '#wcap_header_image' ).attr( 'src', attachment.url );
                            $( '#wcap_header_image_preview' ).attr( 'src', attachment.url );

                            vm.wcap_image_url = attachment.url;
                        });

                        // Finally, open the modal on click
                        frame.open();
                    },
                    wcap_load_data: function( template_id, db_data ){

                        var template_data = db_data,
                            sent_time = [],
                            template_body = '';

                        sent_time = template_data.sent_time.split(' ');
                        template_body = JSON.parse( template_data.body );
                        this.wcap_send_num = sent_time[0];
                        this.wcap_send_freq = sent_time[1];
                        this.wcap_subject = template_data.template_subject ? template_data.template_subject : '';
                        this.wcap_header = template_body.header ? template_body.header : '';
                        this.wcap_subheader = template_body.subheader ? template_body.subheader : '';
                        this.wcap_image_url = template_body.header_image ? template_body.header_image : this.wcap_image_url;
                        this.wcap_checkout = template_body.checkout_text ? template_body.checkout_text : '';
                        this.wcap_unsubscribe_text = template_body.unsubscribe_text ? template_body.unsubscribe_text : '';
                        this.wcap_active_status = template_data.active === 'on' ? 1 : 0;

                        /*var data = {
                                action      : 'wcap_fb_get_template',
                                template_id : template_id
                            },
                            config = {
                                'emulateJSON' : true
                            };

                        this.$http.post( ajaxurl, data, config ).then( function( response ){

                        });*/
                    },
                    wcap_clear_data: function(){

                        this.wcap_send_num = '1';
                        this.wcap_send_freq = 'minutes';
                        this.wcap_subject = '';
                        this.wcap_header = '';
                        this.wcap_subheader = '';
                        this.wcap_image_url = this.wcap_default_image;
                        this.wcap_checkout = '';
                        this.wcap_unsubscribe_text = '';
                        this.wcap_active_status = 0;
                    },
                    wcap_save_template: function(){

                        var data = {},
                            config = {
                                'emulateJSON' : true
                            };

                        data['action'] = 'wcap_fb_save_template';
                        data['subject'] = this.wcap_subject;
                        data['sent_time'] = this.wcap_send_num + ' ' + this.wcap_send_freq;
                        data['body'] = JSON.stringify({
                            'header': this.wcap_header,
                            'subheader': this.wcap_subheader,
                            'header_image': this.wcap_image_url,
                            'checkout_text': this.wcap_checkout,
                            'unsubscribe_text': this.wcap_unsubscribe_text
                        });
                        data['active'] = this.wcap_active_status;

                        if( template_id ){
                            data['template_id'] = template_id;
                        }

                        this.$http.post( ajaxurl, data, config ).then( function( response ){
                            window.location.reload();
                        });
                    },
                    wcap_destroy_template: function(){
                        //this.wcap_clear_data();
                    }
                },
                created: function(){

                    if ( template_id ) {
                        this.wcap_load_data( template_id, template_data );
                    }else {
                        this.wcap_clear_data();
                    }
                }
            });
        }
    }); // End Modal Event close
});