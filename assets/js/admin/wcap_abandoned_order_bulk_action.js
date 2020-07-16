jQuery(function( $ ) {

	$ ( '#doaction' ).on( 'click', function( e ) {
		
		if ( $( '#bulk-action-selector-top' ).val() == 'wcap_abandoned_trash' ) {

			var checkboxes = document.getElementsByName('abandoned_order_id[]');
			var wcap_selected_id = [];
		  	for (var i = 0; i < checkboxes.length; i++) {
		     
		     	if ( checkboxes[i].checked ) {
		        	wcap_selected_id.push( checkboxes[i].value );
		    	}
		  	}

		  	if ( wcap_selected_id.length == 0 ){

		  		var display_message       = 'Please select atleast 1 Abandoned order to Move to Trash.';
				$( ".wcap_ac_bulk_message_p" ).html( display_message );
	            $( "#wcap_ac_bulk_message" ).fadeIn();
	            setTimeout( function(){
	            	$( "#wcap_ac_bulk_message" ).fadeOut();
	            },3000);
		  		e.preventDefault();	
		  	}
		}

		if ( $( '#bulk-action-selector-top' ).val() == 'emailtemplates&mode=wcap_manual_email' ) {

			var checkboxes = document.getElementsByName('abandoned_order_id[]');
			var wcap_selected_id = [];
			var wcap_parent      = [];
		  	for (var i = 0; i < checkboxes.length; i++) {
		     
		     	if ( checkboxes[i].checked ) {
		     		var email_check = $( checkboxes[i] ).parent().parent().find('.email').text();
		     		var initiate_recovery = email_check.indexOf("Send Custom Email");
		     		wcap_parent [ checkboxes[i].value ] =  initiate_recovery ;
					wcap_selected_id.push( checkboxes[i].value );
		        }
		  	}

			if ( wcap_selected_id.length == 0 ){
		  		var display_message       = 'Please select atleast 1 Abandoned order to Send Custom Email.';
				$( ".wcap_ac_bulk_message_p" ).html( display_message );
	            $( "#wcap_ac_bulk_message" ).fadeIn();
	            setTimeout( function(){
	            	$( "#wcap_ac_bulk_message" ).fadeOut();
	            },3000);
		  		e.preventDefault();	
		  	}

		  	var allow = 'no';
			if ( wcap_parent.length > 0 ){
				for ( var key in wcap_parent ) {
				  	//console.log("key " + key + " has value " + wcap_parent[key]);
				  	if ( wcap_parent[key] > 0 ){
				  		allow = 'yes';
				  	}else{
				  		var visitor = document.querySelectorAll("input[ value = '"+ key +"']");
				  		visitor[0].checked = false;
				  	}
				}
				
				if ( 'no' == allow ){
					var display_message       = 'Send Custom Email cannot be applied.';
					$( ".wcap_ac_bulk_message_p" ).html( display_message );
		            $( "#wcap_ac_bulk_message" ).fadeIn();
		            setTimeout( function(){
		            	$( "#wcap_ac_bulk_message" ).fadeOut();
		            },3000);
			  		e.preventDefault();	
			  	}
			}
		}
		
	});

	$ ( '#doaction2' ).on( 'click', function( e ) {
		if ( $( '#bulk-action-selector-bottom' ).val() == '-1' ) {
			e.preventDefault();
		}

		if ( $( '#bulk-action-selector-bottom' ).val() == 'wcap_abandoned_trash' ) {
			var checkboxes = document.getElementsByName('abandoned_order_id[]');
			var wcap_selected_id = [];
		  	for (var i = 0; i < checkboxes.length; i++) {
		     
		     	if ( checkboxes[i].checked ) {
		        	wcap_selected_id.push( checkboxes[i].value );
		    	}
		  	}

		  	if ( wcap_selected_id.length == 0 ){
		  		var display_message       = 'Please select atleast 1 Abandoned order to Move to Trash.';
				$( ".wcap_ac_bulk_message_p" ).html( display_message );
	            $( "#wcap_ac_bulk_message" ).fadeIn();
	            setTimeout( function(){
	            	$( "#wcap_ac_bulk_message" ).fadeOut();
	            },3000);
		  		e.preventDefault();	
		  	}
		}
		if ( $( '#bulk-action-selector-bottom' ).val() == 'emailtemplates&mode=wcap_manual_email' ) {

			var checkboxes = document.getElementsByName('abandoned_order_id[]');
			var wcap_selected_id = [];
			var wcap_parent      = [];
		  	for (var i = 0; i < checkboxes.length; i++) {
		     
		     	if ( checkboxes[i].checked ) {
		     		var email_check = $( checkboxes[i] ).parent().parent().find('.email').text();
		     		var initiate_recovery = email_check.indexOf("Send Custom Email");
		     		wcap_parent [ checkboxes[i].value ] =  initiate_recovery ;
		        	wcap_selected_id.push( checkboxes[i].value );
		    	}

		    	var allow = 'no';
				if ( wcap_parent.length > 0 ){
					for ( var key in wcap_parent ) {
					  //console.log("key " + key + " has value " + wcap_parent[key]);
					  if ( wcap_parent[key] > 0 ){
					  	allow = 'yes';
					  }else{
					  	var visitor = document.querySelectorAll("input[ value = '"+ key +"']");
					  	visitor[0].checked = false;
					  }
					}
					
					if ( 'no' == allow ){
						var display_message       = 'Send Custom Email cannot be applied.';
						$( ".wcap_ac_bulk_message_p" ).html( display_message );
			            $( "#wcap_ac_bulk_message" ).fadeIn();
			            setTimeout( function(){
			            	$( "#wcap_ac_bulk_message" ).fadeOut();
			            },3000);
						e.preventDefault();
					}
				} 
		  	}

		  	if ( wcap_selected_id.length == 0 ){
		  		var display_message       = 'Please select atleast 1 Abandoned order to Send Custom Email.';
				$( ".wcap_ac_bulk_message_p" ).html( display_message );
	            $( "#wcap_ac_bulk_message" ).fadeIn();
	            setTimeout( function(){
	            	$( "#wcap_ac_bulk_message" ).fadeOut();
	            },3000);
		  		e.preventDefault();	
		  	}
		}
	});
});