jQuery(function( $ ) {

	function wcap_show_tooltip_amount( x, y, contents ) {
		$( '<div class="chart-tooltip">' + contents + '</div>' ).css( {
			top: y - 16,
	   		left: x + 20
	   		
		}).appendTo( 'body' ).fadeIn( 200 );
	}
	
	function wcap_show_tooltip_cart_number( x, y, contents ) {
		$( '<div class="chart-tooltip">' + contents + '</div>' ).css( {
			top: y - 16,
	   		left: x + 20
		}).appendTo( 'body' ).fadeIn( 200 );
	}
	
	function wcap_total_orders_vs_abandoned_orders_number_tooltip( x, y, contents ) {
		$( '<div class="chart-tooltip">' + contents + '</div>' ).css( {
			top: y - 16,
	   		left: x + 20
		}).appendTo( 'body' ).fadeIn( 200 );
	}
	
	function wcap_abandoned_email_sent_tooltip( x, y, contents ) {
		$( '<div class="chart-tooltip">' + contents + '</div>' ).css( {
			top: y - 16,
	   		left: x + 20
		}).appendTo( 'body' ).fadeIn( 200 );
	}
	
	function wcap_abandoned_email_opened_tooltip( x, y, contents ) {
		$( '<div class="chart-tooltip">' + contents + '</div>' ).css( {
			top: y - 16,
	   		left: x + 20
		}).appendTo( 'body' ).fadeIn( 200 );
	}
	
	function wcap_abandoned_email_clicked_tooltip( x, y, contents ) {
		$( '<div class="chart-tooltip">' + contents + '</div>' ).css( {
			top: y - 16,
	   		left: x + 20
		}).appendTo( 'body' ).fadeIn( 200 );
	}
	
	

	var prev_data_index = null;
	var prev_series_index = null;

	$( '.chart-placeholder.abandoned_vs_recovered_amount').bind("plothover", 		   wcap_pie_hover_amount );
	$( '.chart-placeholder.abandoned_vs_recovered_cart_number').bind("plothover", 	   wcap_pie_hover_cart_number );
	$( '.chart-placeholder.total_orders_vs_abandoned_orders_number').bind("plothover", wcap_total_orders_vs_abandoned_orders_number );
	$( '.chart-placeholder-email.wcap_abandoned_email_sent').bind("plothover", 		   wcap_abandoned_email_sent );
	$( '.chart-placeholder-email.wcap_abandoned_email_opened').bind("plothover", 	   wcap_abandoned_email_opened );
	$( '.chart-placeholder-email.wcap_abandoned_email_clicked').bind("plothover", 	   wcap_abandoned_email_clicked );
	
	function wcap_pie_hover_amount( event, pos, item ) 
	{
		
		if ( item ) {
			if ( prev_data_index !== item.dataIndex || prev_series_index !== item.seriesIndex ) {
				prev_data_index   = item.dataIndex;
				prev_series_index = item.seriesIndex;

				$( '.chart-tooltip' ).remove();

				if ( item.series.points.show || item.series.enable_tooltip ) {

					var y = item.series.data[item.dataIndex][1],
						tooltip_content = '';

					if ( item.series.prepend_label ) {
						tooltip_content = ( tooltip_content + item.series.label + ': ');
					}

					if ( item.series.prepend_tooltip ) {
						tooltip_content = ( tooltip_content + item.series.prepend_tooltip );
					}

					tooltip_content = tooltip_content + ( y.toFixed(wcap_dashboard_report_params.wc_round_value) );
					
					if ( item.series.append_tooltip ) {

						wcap_total = accounting.formatMoney( tooltip_content, {
							symbol:    wcap_dashboard_report_params.currency_symbol,
							decimal:   wcap_dashboard_report_params.currency_format_decimal_sep,
							thousand:  wcap_dashboard_report_params.currency_format_thousand_sep,
							precision: wcap_dashboard_report_params.wc_round_value,
							format:    wcap_dashboard_report_params.currency_format
						});

						if ( "Total Revenue" == item.series.label ){
							tooltip_content = "Total Sales:" + " " + wcap_total;
						}else{
							tooltip_content = "Recovered Sales:" + " " + wcap_total;
						}
					}

					if ( item.series.pie.show ) {
						wcap_show_tooltip_amount( pos.pageX, pos.pageY, tooltip_content );
						
					} else {
						
						wcap_show_tooltip_amount( item.pageX, item.pageY, tooltip_content );
					}
				}
			}
		} else {
			jQuery( '.chart-tooltip' ).remove();
			prev_data_index = null;
		}
	}
	
	function wcap_pie_hover_cart_number( event, pos, item ) 
	{
		
		if ( item ) {
			if ( prev_data_index !== item.dataIndex || prev_series_index !== item.seriesIndex ) {
				prev_data_index   = item.dataIndex;
				prev_series_index = item.seriesIndex;

				$( '.chart-tooltip' ).remove();

				if ( item.series.points.show || item.series.enable_tooltip ) {

					var y = item.series.data[item.dataIndex][1],
						tooltip_content = '';

					if ( item.series.prepend_label ) {
						tooltip_content = tooltip_content + item.series.label + ': ';
					}

					if ( item.series.prepend_tooltip ) {
						tooltip_content = tooltip_content + item.series.prepend_tooltip;
					}

					tooltip_content = tooltip_content + y;

					if ( item.series.append_tooltip ) {
						
						if ( "Abandoned Carts" == item.series.label ){
							tooltip_content = "Abandoned Carts:"+" "+tooltip_content;
						}else{
							tooltip_content = "Recovered Carts:"+" "+ tooltip_content;
						}
					}

					if ( item.series.pie.show ) {
						wcap_show_tooltip_cart_number( pos.pageX, pos.pageY, tooltip_content );
						
					} else {
						
						wcap_show_tooltip_cart_number( item.pageX, item.pageY, tooltip_content );
					}
				}
			}
		} else {
			jQuery( '.chart-tooltip' ).remove();
			prev_data_index = null;
		}
	}
	
	function wcap_total_orders_vs_abandoned_orders_number( event, pos, item ) 
	{
		
		if ( item ) {
			if ( prev_data_index !== item.dataIndex || prev_series_index !== item.seriesIndex ) {
				prev_data_index   = item.dataIndex;
				prev_series_index = item.seriesIndex;

				$( '.chart-tooltip' ).remove();

				if ( item.series.points.show || item.series.enable_tooltip ) {

					var y = item.series.data[item.dataIndex][1],
						tooltip_content = '';

					if ( item.series.prepend_label ) {
						tooltip_content = tooltip_content + item.series.label + ': ';
					}

					if ( item.series.prepend_tooltip ) {
						tooltip_content = tooltip_content + item.series.prepend_tooltip;
					}

					tooltip_content = tooltip_content + y;

					if ( item.series.append_tooltip ) {
						
						if ( "Total Carts" == item.series.label ){
							tooltip_content = "Total Carts:"+" "+tooltip_content;
						}else{
							tooltip_content = "Abandoned Carts:"+" "+tooltip_content;
						}
					}

					if ( item.series.pie.show ) {
						wcap_total_orders_vs_abandoned_orders_number_tooltip( pos.pageX, pos.pageY, tooltip_content );
					} else {
						wcap_total_orders_vs_abandoned_orders_number_tooltip( item.pageX, item.pageY, tooltip_content );
					}
				}
			}
		} else {
			jQuery( '.chart-tooltip' ).remove();
			prev_data_index = null;
		}
	}
	
	function wcap_abandoned_email_sent( event, pos, item ) {
		
		if ( item ) {
			if ( prev_data_index !== item.dataIndex || prev_series_index !== item.seriesIndex ) {
				prev_data_index   = item.dataIndex;
				prev_series_index = item.seriesIndex;

				$( '.chart-tooltip' ).remove();

				if ( item.series.points.show || item.series.enable_tooltip ) {

					var y = item.series.data[item.dataIndex][1],
						tooltip_content = '';

					if ( item.series.prepend_label ) {
						tooltip_content = tooltip_content + item.series.label + ': ';
					}

					if ( item.series.prepend_tooltip ) {
						tooltip_content = tooltip_content + item.series.prepend_tooltip;
					}

					tooltip_content = tooltip_content + y;

					if ( item.series.append_tooltip ) {
						
						tooltip_content = "Emails Sent:"+ " "+tooltip_content;
						
					}

					if ( item.series.pie.show ) {
						wcap_abandoned_email_sent_tooltip( pos.pageX, pos.pageY, tooltip_content );
					} else {
						wcap_abandoned_email_sent_tooltip( item.pageX, item.pageY, tooltip_content );
					}
				}
			}
		} else {
			jQuery( '.chart-tooltip' ).remove();
			prev_data_index = null;
		}
	}
	
	function wcap_abandoned_email_opened( event, pos, item ) {
		
		if ( item ) {
			if ( prev_data_index !== item.dataIndex || prev_series_index !== item.seriesIndex ) {
				prev_data_index   = item.dataIndex;
				prev_series_index = item.seriesIndex;

				$( '.chart-tooltip' ).remove();

				if ( item.series.points.show || item.series.enable_tooltip ) {

					var y = item.series.data[item.dataIndex][1],
						tooltip_content = '';

					if ( item.series.prepend_label ) {
						tooltip_content = tooltip_content + item.series.label + ': ';
					}

					if ( item.series.prepend_tooltip ) {
						tooltip_content = tooltip_content + item.series.prepend_tooltip;
					}

					tooltip_content = tooltip_content + y;

					if ( item.series.append_tooltip ) {
						
						if ( "Emails Opened" == item.series.label ){
							tooltip_content = "Emails Opened:"+" "+ tooltip_content;
						}else{
							tooltip_content = "Emails not Opened:"+ " "+tooltip_content;
						}
					}

					if ( item.series.pie.show ) {
						wcap_abandoned_email_opened_tooltip( pos.pageX, pos.pageY, tooltip_content );
					} else {
						wcap_abandoned_email_opened_tooltip( item.pageX, item.pageY, tooltip_content );
					}
				}
			}
		} else {
			jQuery( '.chart-tooltip' ).remove();
			prev_data_index = null;
		}
	}
	
	function wcap_abandoned_email_clicked( event, pos, item ) {
		
		if ( item ) {
			if ( prev_data_index !== item.dataIndex || prev_series_index !== item.seriesIndex ) {
				prev_data_index   = item.dataIndex;
				prev_series_index = item.seriesIndex;

				$( '.chart-tooltip' ).remove();

				
				if ( item.series.points.show || item.series.enable_tooltip ) {

					var y = item.series.data[item.dataIndex][1],
						tooltip_content = '';

					if ( item.series.prepend_label ) {
						tooltip_content = tooltip_content + item.series.label + ': ';
					}

					if ( item.series.prepend_tooltip ) {
						tooltip_content = tooltip_content + item.series.prepend_tooltip;
					}

					tooltip_content = tooltip_content + y;

					if ( item.series.append_tooltip ) {
						
						if ( "Emails Opened" == item.series.label ){
							tooltip_content = "Emails Opened:"+ " "+tooltip_content;
						}else{
							tooltip_content = "Emails Clicked:"+ " " +tooltip_content;
						}
					}

					if ( item.series.pie.show ) {
						wcap_abandoned_email_clicked_tooltip( pos.pageX, pos.pageY, tooltip_content );
					} else {
						wcap_abandoned_email_clicked_tooltip( item.pageX, item.pageY, tooltip_content );
					}
				}
			}
		} else {
			jQuery( '.chart-tooltip' ).remove();
			prev_data_index = null;
		}
	}
	
});


