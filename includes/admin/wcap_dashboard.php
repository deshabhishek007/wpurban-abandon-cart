<?php
/**
 * It will display the Dashboard data.
 * @author  Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Report
 * @since 5.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Dashboard' ) ) {
    /**
     * It will display the Dashboard data.
     * @since 5.0
     */
    class Wcap_Dashboard {
        /**
         * This function will display all the report of the Dashboard.
         * @since 5.0
         */
        public static function wcap_display_dashboard( ) { 
            $start_date = '';
            $end_date   = '';
            if( isset( $_GET['duration_select'] ) && '' != $_GET['duration_select'] ) {
                $selected_data_range = $_GET['duration_select'];
            } else {
                $selected_data_range = 'this_month';
            }
            if ( isset( $selected_data_range ) && 'other' == $selected_data_range ) {
                if ( isset( $_GET['wcap_start_date'] ) && '' != $_GET['wcap_start_date'] ){
                    $start_date = $_GET['wcap_start_date'];
                }
                if ( isset( $_GET['wcap_end_date'] ) && '' != $_GET['wcap_end_date'] ) {
                    $end_date   = $_GET['wcap_end_date'];
                }
            }

            $display_report = new Wcap_Dashboard_Report_Action();
            $display_report->wcap_get_all_reports( $selected_data_range, $start_date, $end_date );
        }
    }
}