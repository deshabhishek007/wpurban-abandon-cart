"use strict";

jQuery( '.ac-insert-template' ).on( 'click', function(){
	Vue.component('wcap-thumbnail', {
		props: [ 'value', 'templates', 'key', 'showThumbnail' ],
		data: function () {
			return {
				count: 0
			}
		},
		template: `
			<div class="col-sm-12 col-md-3 col-lg-3 wcap-container text-center">
				<label for="{{key}}" class="wcap-image-label">
					<input 
						type="radio" 
						name="wcap-template-select" 
						:id="key" 
						:value="key" 
						class="wcap-radio" 
						v-model="wcap_template"
					>
					<img src="{{templates.url}}" class="wcap-image">
					Template {{key}}
				<button class="wcap-preview-btn" v-on:click="clickMethodPreview"><span class="dashicons dashicons-search"></span></button>
				</label>
			</div>`,
		methods: {
			clickMethodPreview: function (event) {
				this.$root.showThumbnail = false;
				this.$root.label = this.templates.url;
				this.$root.wcap_template_selected = this.templates.html;
			}
		},
		computed: {
			wcap_template: {
				get: function(){
					return this.value;
				},
				set: function(){
					this.$emit( 'checked', this.templates.html );
				}
			}
		}
	});

	new Vue({
		el: '#wcap-preview-modal',
		data: function() {
			return { 
				showThumbnail: true,
				label: '',
				templateList: wcap_template_params, // JS Global Variable Localized
				wcap_template_selected: null
			}
		},
		methods: {
			clickMethod: function (item) {
				this.showThumbnail = false;
				this.label = item.text;
			},
			on_select: function(value) {
				this.wcap_template_selected = value;
			},
			wcap_view_back: function() {
				this.showThumbnail = true;
				this.wcap_template_selected = null;
			},
			wcap_insert_html: function() {
				if ( this.wcap_template_selected ) {
					this.$http.get(this.wcap_template_selected).then( function( response ){
						jQuery( '#wcap-preview-modal' ).modal('hide');
						tinymce.get('woocommerce_ac_email_body').setContent( response.body );
					}).catch(function( error ){
						console.log( error );
					});
				}
			}
		}
	});
});

jQuery( '.ac-import-template' ).on( 'click', function(){

	var frame;

	// If the media frame already exists, reopen it.
	if ( frame ) {
		frame.open();
		return;
	}
	
	// Create a new media frame
	frame = wp.media({
		className: 'media-frame',
		library: {
			type: 'text',
			subtype: 'html'
		},
		menu: 'default',
		view: {
			EmbedUrl: true
		},
		editing: true,
		//states: states,
		title: 'Select or Upload HTML Files',
		button: {
			text: 'Import this file'
		},
		multiple: false  // Set to true to allow multiple files to be selected
	});

	// When an image is selected in the media frame...
	frame.on( 'select', function() {

		// Get media attachment details from the frame state
		var attachment = frame.state().get('selection').first().toJSON();

		Vue.http.get( attachment.url ).then( function( response ) {
			jQuery( '#wcap-preview-modal' ).modal( 'hide' );
			tinymce.get( 'woocommerce_ac_email_body' ).setContent( response.body );
		}).catch( function( error ) {
			console.log(error);
		});
	});

	// Finally, open the modal on click
	frame.open();
});