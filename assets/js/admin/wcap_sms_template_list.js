jQuery( document ).ready( function() {
	// Load  text area when the message link is clicked
	jQuery( '[id^="sms_txt_"]' ).on( 'click', function() {
		// <a> id
		var id = this.id;
		var split_id = id.split( '_' );
		
		// row ID
		var trid = split_id[2];
		
		// prepare the textarea HTML
		var full_msg = jQuery( 'tr#sms_' + trid + " td.full_txt_msg" ).html();
		var editable = '<td class="txt_msg column-txt_msg"><textarea class="msg" rows="4">' + full_msg + '</textarea></td>'
		// Use replaceWith and open the editable text area
		jQuery( 'tr#sms_' + trid + ' td.txt_msg' ).replaceWith( editable );

		// mark the row as updated
		jQuery( 'tr#sms_' + trid + ' td.updated' ).html('1');
	});
	
	// Make sure that the numeric value available for frequency is setup correctly when any change is made in the Minutes/Hours or Days dropdown 
	jQuery( '.sms_template_ids' ).on( 'change', '[id^="freq_value_"]', function() {
		// <select> id
		var id = this.id;
		var split_id = id.split( '_' );
		
		// row ID
		var trid = split_id[2];
		
		var freq_value = jQuery( '#' + id ).val();
		
		switch ( freq_value ) {
		case 'hours':
			var count = 23;
			break;
		case 'minutes':
			var count = 59;
			break;
		case 'days':
			var count = 90;
			break;
		}
		var frequency = jQuery( '#freq_count_' + trid ).val();
		var select_text = "<select id='freq_count_" + trid + "'>";
		
		for( var i=1; i <= count; i++ ) {
			select_text += "<option value='" + i + "'>" + i + "</option>";
		}
		select_text += "</select>";
		
		jQuery( '#freq_count_' + trid ).replaceWith( select_text );
		// preselect the existing frequency
		if( frequency <= count ) {
			jQuery( "#freq_count_" + trid ).val( frequency );
		}
		// mark the row as updated
		jQuery( 'tr#sms_' + trid + ' td.updated' ).html('1');
	});

	// Onclick for delete SMS Template
	jQuery( '.sms_template_ids' ).on( 'click', '.delete_sms', function() {
	
		var id = this.id;
		
		var id_params =  id.split( '_' );
		var sms_template_id = id_params[1];
		
		var data = {
				template_id: sms_template_id,
				action: 'wcap_delete_sms_template'
		}
		jQuery.post( wcap_sms_params.ajax_url, data, function( response ) {
			// Remove the row from the table
			jQuery( '#sms_' + sms_template_id ).remove();
		}); 
	});
	
	// Onclick of Save SMS Template
	jQuery( document ).on( 'click', '.sms_bulk_save', function() {
		
		var sms_templates = {};
		// loop through each row 
		jQuery( '.sms_template_ids tr[id^="sms_"]' ).each(function (i, row) {
			// check if it has been updated
			var id = this.id;
			
			var updated = jQuery( this ).find( '.updated' ).html();
	        
        	// text message
        	var msg = jQuery( this ).find( 'textarea[class="msg"]' ).val();
        	if( msg == undefined ) {
        		msg = jQuery( this ).find( '.full_txt_msg' ).html();
        	}

			// if updated & text msg is not blanks, then capture the data
	        if( updated == '1' && msg != '' ) {
    	  
	        	// id
	        	var split_id = id.split( '_' );
	        	id = split_id[1];
	        	
	        	// frequency
	        	var frequency = jQuery( this ).find( 'select[id^="freq_count"]' ).val();
	        	frequency += ' ' + jQuery( this ).find( 'select[id^="freq_value"]' ).val();
	        	
	        	// active
	        	var active = jQuery( this ).find( '.wcap-switch.wcap-toggle-template-status' ).attr( 'wcap-template-switch' );
	       
	        	// coupon code
	        	var coupon_code = jQuery( this ).find( 'select[id^="coupon_ids"]' ).val();
	        	
	        	sms_templates[ id ] = msg + '|~|' + frequency + '|~|' + active + '|~|' + coupon_code;
	        }
			
			
		});
		var data = {
				template_data: JSON.stringify( sms_templates ),
				action: 'wcap_save_bulk_sms_template'
		};
		jQuery.post( wcap_sms_params.ajax_url, data, function( response ) {
			location.reload();
		}); 
	});
	
	// When the Add New Text Message button is clicked, append a new row
	jQuery( '#new_sms' ).on( 'click', function() {
		
		var new_id = jQuery( '#new_template_id' ).val();
		
		row = jQuery("<tr id=sms_" + new_id + "></tr>");
		   col1 = jQuery( jQuery( '#cb_default' ).val() );
		   col2 = jQuery( jQuery( '#id_default' ).val() );
		   col3 = jQuery( jQuery( '#updated_default' ).val() );
		   col4 = jQuery( jQuery( '#txt_msg_default' ).val() );
		   col5 = jQuery( jQuery( '#full_txt_msg_default' ).val() );
		   col6 = jQuery( jQuery( '#coupon_code_default' ).val() );
		   col7 = jQuery( jQuery( '#sent_time_default' ).val() );
		   col8 = jQuery( jQuery( '#sms_sent_default' ).val() );
		   col9 = jQuery( jQuery( '#activate_default' ).val() );
		   col10 = jQuery( jQuery( '#actions_default' ).val() );
		   
		   row.append(col1, col2, col3, col4, col5, col6, col7, col8, col9 ).prependTo( ".sms_template_ids" );
		   
		   // Update the New SMS ID by 1
		   var existing_id = jQuery( '#new_template_id' ).val();
		   existing_id++;
		   jQuery( '#new_template_id' ).val( existing_id );
	});
	
	// mark row as updated if coupon code has been modified
	jQuery( "[id^='coupon_ids_']" ).on( 'change', function(){
	
		// <a> id
		var id = this.id;
		var split_id = id.split( '_' );
		
		// row ID
		var trid = split_id[2];
		
		jQuery( 'tr#sms_' + trid + ' td.updated' ).html('1');
	});
	
	// mark row as updated if frequency count has been updated 
	jQuery( '.sms_template_ids' ).on( 'change', '[id^="freq_count_"]', function() {
		
		var id = this.id;
		var split_id = id.split( '_' );
		
		// row ID
		var trid = split_id[2];
		
		jQuery( 'tr#sms_' + trid + ' td.updated' ).html('1');
	});
});