jQuery(function(){
	var data = [
	            { label: wcap_dashboard_create_report_params.total_revenue ,  data: wcap_dashboard_create_report_params.this_month_total_orders_amount , color: "#1abc9c"},
	            { label: wcap_dashboard_create_report_params.recovered_revenue,  data: wcap_dashboard_create_report_params.this_month_recovered_cart_amount, color: "#8fdece"}
	            
	        ];
	jQuery.plot(
		jQuery('.chart-placeholder.abandoned_vs_recovered_amount'),
		data,
	{
		grid: {
			hoverable: true,
		},
		series: {
			pie: {
				show: true,
				radius: 1,
				innerRadius: 0.6,
				label: {
					show: false,
                    radius: 2/3,
                    threshold: 0.1
                }
			},
			enable_tooltip: true,
			append_tooltip: wcap_dashboard_create_report_params.recovered,
			},
			legend: {
				show: true
			}
		}
	);
	
    jQuery('.chart-placeholder.abandoned_vs_recovered_amount').resize();
});
    
jQuery(function(){
	var data = [
	            { label: wcap_dashboard_create_report_params.abandoned_carts,  data: wcap_dashboard_create_report_params.this_month_abandoned_cart_count , color: "#1abc9c"},
	            { label: wcap_dashboard_create_report_params.recovered_carts,  data: wcap_dashboard_create_report_params.this_month_recovered_cart_count, color: "#8fdece"}
	            
	        ];
	jQuery.plot(
		jQuery('.chart-placeholder.abandoned_vs_recovered_cart_number'),
		data,
	{
		grid: {
			hoverable: true,
		},
		series: {
			pie: {
				show: true,
				radius: 1,
				innerRadius: 0.6,
				label: {
					show: false,
                    radius: 2/3,
                    threshold: 0.1
                }
			},
			enable_tooltip: true,
			append_tooltip: wcap_dashboard_create_report_params.recovered,
			},
			legend: {
				show: true
			}
		}
	);
	
    jQuery('.chart-placeholder.abandoned_vs_recovered_cart_number').resize();
});

jQuery(function(){
	var data = [
	            { label: wcap_dashboard_create_report_params.total_orders ,  data: wcap_dashboard_create_report_params.this_month_wc_orders, color: "#1abc9c"},
	            { label: wcap_dashboard_create_report_params.abandoned_orders,  data: wcap_dashboard_create_report_params.this_month_abandoned_cart_count, color: "#8fdece"}
	            
	        ];
	jQuery.plot(
		jQuery('.chart-placeholder.total_orders_vs_abandoned_orders_number'),
		data,
	{
		grid: {
			hoverable: true,
		},
		series: {
			pie: {
				show: true,
				radius: 1,
				innerRadius: 0.6,
				label: {
					show: false,
                    radius: 2/3,
                    threshold: 0.1
                }
			},
			enable_tooltip: true,
			append_tooltip: wcap_dashboard_create_report_params.recovered,
			},
			legend: {
				show: true
			}
		}
	);
	
    jQuery('.chart-placeholder.total_orders_vs_abandoned_orders_number').resize();
});

jQuery( document ).ready( function() {
    jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "en-GB" ] );
    jQuery( "#wcap_start_date" ).datepicker({
    	onSelect: function( date ) {
    		 //var date = jQuery('#wcap_start_date').datepicker('getDate');
    		 jQuery('#wcap_end_date').datepicker('option', 'minDate', date);
    		 setTimeout(function(){
            	jQuery( "#wcap_end_date" ).datepicker('show');
            }, 16);     
        },
         maxDate: '0',
         changeMonth: true,
         changeYear: true,
         dateFormat: "yy-mm-dd" 
    } );
} );

jQuery( document ).ready( function() {
    jQuery( "#wcap_end_date" ).datepicker( {
         maxDate: '0',
         changeMonth: true,
         changeYear: true,
         dateFormat: "yy-mm-dd" } );

} );

 jQuery( '#duration_select' ).change( function() {

	var group_name  = jQuery( '#duration_select' ).val();
    if ( jQuery(this).val() == "other") {
    	document.getElementById("wcap_start_end_date_div").style.display = "block";
    }
    if ( jQuery(this).val() != "other" ) {
    	document.getElementById("wcap_start_end_date_div").style.display = "none";
    }
});
 
 jQuery(function(){
		var data = [
		            { label: wcap_dashboard_create_report_params.email_sent ,  data: wcap_dashboard_create_report_params.wcap_email_sent_count, color: "#1abc9c"}
		        ];
		jQuery.plot(
			jQuery('.chart-placeholder-email.wcap_abandoned_email_sent'),
			data,
		{
			grid: {
				hoverable: true,
			},
			series: {
				pie: {
					show: true,
					radius: 1,
					innerRadius: 0.6,
					label: {
						show: false,
	                    radius: 2/3,
	                    threshold: 0.1
	                }
				},
				enable_tooltip: true,
				append_tooltip: wcap_dashboard_create_report_params.recovered,
				},
				legend: {
					show: true
				}
			}
		);
		
	    jQuery('.chart-placeholder-email.wcap_abandoned_email_sent').resize();
	});
 
 jQuery(function(){
		var data = [

	            	{ label: wcap_dashboard_create_report_params.email_opened ,    data: wcap_dashboard_create_report_params.wcap_email_opened, color: "#1abc9c"},
		            { label: wcap_dashboard_create_report_params.email_not_opened, data: ( wcap_dashboard_create_report_params.wcap_email_sent_count - wcap_dashboard_create_report_params.wcap_email_opened ) ,     color: "#8fdece"}
	            	
		        ];
		jQuery.plot(
			jQuery('.chart-placeholder-email.wcap_abandoned_email_opened'),
			data,
		{
			grid: {
				hoverable: true,
			},
			series: {
				pie: {
					show: true,
					radius: 1,
					innerRadius: 0.6,
					label: {
						show: false,
	                    radius: 2/3,
	                    threshold: 0.1
	                }
				},
				enable_tooltip: true,
				append_tooltip: wcap_dashboard_create_report_params.recovered,
				},
				legend: {
					show: true
				}
			}
		);
		
	    jQuery('.chart-placeholder-email.wcap_abandoned_email_opened').resize();
	});
 
 jQuery(function(){
		var data = [
	            	
		            { label: wcap_dashboard_create_report_params.email_opened ,  data: wcap_dashboard_create_report_params.wcap_email_opened,  color: "#1abc9c"},
		            { label: wcap_dashboard_create_report_params.click_rate,     data: wcap_dashboard_create_report_params.wcap_email_clicked, color: "#8fdece"}
		            
		        ];
		jQuery.plot(
			jQuery('.chart-placeholder-email.wcap_abandoned_email_clicked'),
			data,
		{
			grid: {
				hoverable: true,
			},
			series: {
				pie: {
					show: true,
					radius: 1,
					innerRadius: 0.6,
					label: {
						show: false,
	                    radius: 2/3,
	                    threshold: 0.1
	                }
				},
				enable_tooltip: true,
				append_tooltip: wcap_dashboard_create_report_params.recovered,
				},
				legend: {
					show: true
				}
			}
		);
		
	    jQuery('.chart-placeholder-email.wcap_abandoned_email_clicked').resize();
	});
