<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * It will show Abandoned Carts data on Dashboard widget page.
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @category Classes
 * @since    5.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Heartbeart Class. 
 *
 * Hooks into the WP heartbeat API to update various parts of the dashboard as new sales are made. Dashboard components that are effect on Dashboard Summary Widget.
 *
 * @since 2.8
 */
class WCAP_Dashboard_Widget_Heartbeat {

	/**
	 * Get things started
	 *
	 * @since 2.8
	 * @return void
	 */
	public static function wcap_init() {
		add_filter( 'heartbeat_received',     array( 'WCAP_Dashboard_Widget_Heartbeat' , 'wcap_heartbeat' ), 10, 2 );
		add_action( 'admin_enqueue_scripts',  array( 'WCAP_Dashboard_Widget_Heartbeat' , 'wcap_enqueue_scripts' ) );
	}

	/**
	 * Tie into the heartbeat and append our stats
	 *
	 * @hook heartbeat_received
	 *
	 * @param array $response The Heartbeat response.
	 * @param array $data     The $_POST data sent.
	 * @return array $response
	 * @access public
	 * @since 2.8
	 */
	public static function wcap_heartbeat( $response, $data ) {
		if( ! current_user_can( 'manage_woocommerce' ) ) {
			return $response; // Only modify heartbeat if current user can view show reports
		}
		// Make sure we only run our query if the edd_heartbeat key is present
		if( ( isset( $data['wcap_widget_heartbeat'] ) ) && ( $data['wcap_widget_heartbeat'] == 'wcap_widget_summary' ) ) {
			// Instantiate the stats class
			$reports = new Wcap_Dashboard_Widget_Report();
            // Send back the number of complete payments
			$response['wcap-total-recover']   = $reports->get_total_reports( 'recover' );
			$response['wcap-total-abandoned'] = $reports->get_total_reports( 'abandoned' );
			$response['wcap-month-recover']   = $reports->get_this_month_reports( 'recover' );
			$response['wcap-month-abandoned'] = $reports->get_this_month_reports( 'abandoned' );
			$response['wcap-today-recover']   = $reports->get_today_reports( 'recover' );
			$response['wcap-today-abandoned'] = $reports->get_today_reports( 'abandoned' );
			$today_ratio                      = $reports->get_today_reports( 'ratio' );
			$response['wcap-today-ratio']     = round ( $today_ratio, wc_get_price_decimals() ).'%';
			$month_ratio                      = $reports->get_this_month_reports( 'ratio' );
			$response['wcap-month-ratio']     = round ( $month_ratio, wc_get_price_decimals() ).'%';
			$total_ratio                      = $reports->get_total_reports( 'ratio' );
			$response['wcap-total-ratio']     = round ( $total_ratio, wc_get_price_decimals() ).'%';
		}
        return $response;
    }

	/**
	 * Load the heartbeat scripts.
	 *
	 * @hook admin_enqueue_scripts
	 *
	 * @access public
	 * @since 2.8
	 */
	public static function wcap_enqueue_scripts() {
		if( ! current_user_can( 'manage_woocommerce' ) ) {
			return; // Only load heartbeat if current user can view show reports
		}
		// Make sure the JS part of the Heartbeat API is loaded.
		wp_enqueue_script( 'heartbeat' );
		add_action( 'admin_print_footer_scripts', array( 'WCAP_Dashboard_Widget_Heartbeat' , 'wcap_footer_js' ), 20 );
	}

	/**
	 * Inject our Java Script into the admin footer.
	 *
	 * @hook admin_print_footer_scripts
	 *
	 * @globals mixed $pagenow
	 * @access public
	 * @since 2.8
	 */
	public static function wcap_footer_js() {
		global $pagenow;
		// Only proceed if on the dashboard
		if( 'index.php' != $pagenow ) {
			return;
		}

		if( ! current_user_can( 'manage_woocommerce' ) ) {
			return; // Only load heartbeat if current user can view show reports
		}
		?>
		<script>
			(function($){
				// Hook into the heartbeat-send
				$(document).on('heartbeat-send', function(e, data) {
					data['wcap_widget_heartbeat'] = 'wcap_widget_summary';
				});

				// Listen for the custom event "heartbeat-tick" on $(document).
				$(document).on( 'heartbeat-tick', function(e, data) {

					// Only proceed if our WCAP data is present
					if ( ! data['wcap-total-abandoned'] )
						return;

					<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) : ?>
					//console.log('tick');
					<?php endif; ?>

					// Update sale count and bold it to provide a highlight
					wcap_update_heartbeat( '.wcap_dashboard_report_widget .table_totals .wcap.b-earnings',      data['wcap-total-abandoned'] );
					wcap_update_heartbeat( '.wcap_dashboard_report_widget .table_totals .wcap.b-sales',         data['wcap-total-recover'] );
					wcap_update_heartbeat( '.wcap_dashboard_report_widget .table_today .wcap.b-earnings',       data['wcap-today-abandoned'] );
					wcap_update_heartbeat( '.wcap_dashboard_report_widget .table_today .wcap.b-sales',          data['wcap-today-recover'] );
					wcap_update_heartbeat( '.wcap_dashboard_report_widget .table_current_month .b-earnings',    data['wcap-month-abandoned'] );
					wcap_update_heartbeat( '.wcap_dashboard_report_widget .table_current_month .b-sales',       data['wcap-month-recover'] );

					wcap_update_heartbeat( '.wcap_dashboard_report_widget .table_current_month .b-sales-ratio', data['wcap-month-ratio'] );
					wcap_update_heartbeat( '.wcap_dashboard_report_widget .table_today .wcap.b-sales-ratio',    data['wcap-today-ratio'] );
					wcap_update_heartbeat( '.wcap_dashboard_report_widget .table_totals .wcap.b-sales-ratio',   data['wcap-total-ratio'] );

					// Return font-weight to normal after 2 seconds
					setTimeout(function(){
						$('.wcap_dashboard_report_widget .wcap.b-sales,.wcap_dashboard_report_widget .wcap.b-earnings').css( 'font-weight', 'normal' );
						$('.wcap_dashboard_report_widget .table_current_month .wcap.b-earnings,.wcap_dashboard_report_widget .table_current_month .wcap.b-sales, .wcap_dashboard_report_widget .table_current_month .wcap.b-sales-ratio, .wcap_dashboard_report_widget .table_today .wcap.b-sales-ratio, .wcap_dashboard_report_widget .table_totals .wcap.b-sales-ratio').css( 'font-weight', 'normal' );
					}, 2000);

				});

				function wcap_update_heartbeat( selector, new_value ) {
					var current_value       = $(selector).text();
					current_value           = current_value.replace(/ /g, '');

				    var new_value_to_string = new_value.toString();
					new_value_to_string     = new_value_to_string.replace(/ /g, '');

					$(selector).text( new_value_to_string );

				    if ( current_value !== new_value_to_string ) {
						$(selector).css( 'font-weight', 'bold' );
					}
				}
			}(jQuery));
		</script>
		<?php
	}
}
//add_action( 'plugins_loaded', array( 'WCAP_Dashboard_Widget_Heartbeat' , 'wcap_init' ) );
