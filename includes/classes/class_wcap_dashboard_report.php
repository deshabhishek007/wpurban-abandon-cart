<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * It shows the states on Dashboard tab.
 *
 * @author      Tyche Softwares
 * @package     Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @category    Classes
 * @since       3.5
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}    
/**
 * Shows the report on dashboard tab.
 * 
 * @since 3.5
 */
class Wcap_Dashboard_Report {

    /** 
     * Select the start date range 
     * 
     * @param string $selected_data_range Selected Date Range
     * @param string $user_start_date Start Date
     * @return string $begin_date Start Date
     * @since 3.5
     */
    function get_begin_of_month( $selected_data_range, $user_start_date ) {

        $begin_date   = '';
        switch ( $selected_data_range ){
            case 'this_month':
                $begin_date = mktime(00, 01, 01, date("n"), 1);
                break;
        
            case 'last_month':
                $begin_date = mktime(00, 01, 01, date("n") - 1, 1);
                break;
                
            case 'this_quarter':
                
                $current_month = date('m');
                $current_year = date('Y');
                
                if($current_month>=1 && $current_month<=3) {
                    $begin_date = strtotime('1-January-'.$current_year. '00:01:01' );  // timestamp or 1-Januray 12:00:00 AM
                }
                else  if($current_month>=4 && $current_month<=6) {
                    $begin_date = strtotime('1-April-'.$current_year. '00:01:01' );  // timestamp or 1-April 12:00:00 AM
                }
                else  if($current_month>=7 && $current_month<=9) {
                    $begin_date = strtotime('1-July-'.$current_year. '00:01:01' );  // timestamp or 1-July 12:00:00 AM
                }
                else  if($current_month>=10 && $current_month<=12) {
                    $begin_date = strtotime('1-October-'.$current_year. '00:01:01' );  // timestamp or 1-October 12:00:00 AM
                }
                
                break;
                
            case 'last_quarter':
                
                $current_month = date('m');
                $current_year = date('Y');
                
                if( $current_month >= 1 && $current_month <= 3 ) {
                    $begin_date = strtotime( '1-October-'.($current_year-1). '00:01:01' );  // timestamp or 1-October Last Year 12:00:00 AM
                }
                else if( $current_month >= 4 && $current_month <= 6 ) {
                    $begin_date = strtotime( '1-January-'.$current_year . '00:01:01' );  // timestamp or 1-Januray 12:00:00 AM
                }
                else if( $current_month >= 7 && $current_month <= 9 ) {
                    $begin_date = strtotime( '1-April-'.$current_year. '00:01:01' );  // timestamp or 1-April 12:00:00 AM
                }
                else if( $current_month >= 10 && $current_month <= 12 ) {
                    $begin_date = strtotime( '1-July-'.$current_year. '00:01:01' );  // timestamp or 1-July 12:00:00 AM
                }
                break;

            case 'this_year':
                $begin_date =  mktime( 00, 01, 01, 1, 1, date('Y') );;
                break;
                
            case 'last_year':
                $begin_date = mktime( 00, 01, 01, 1, 1, date('Y')-1 );
                break;
            
            case 'other':
                $explode_start_date = explode ( "-", $user_start_date );
                $month              = $explode_start_date[1];
                $date               = $explode_start_date[2];
                $year               = $explode_start_date[0];
                $begin_date         = mktime( 00, 01, 01, $month, $date, $year );
                break;

            default:
                $begin_date = time();
        }
        return $begin_date;
    }
    /** 
     * Select the end date range 
     * 
     * @param  string $selected_data_range Selected Date Range
     * @param  string $user_start_date Start Date
     * @return $end_date End date
     * @since  3.5
     */
    function get_end_of_month ( $selected_data_range, $user_end_date ){
    
        $end_date   = '';
        switch ( $selected_data_range ){
            case 'this_month':
                $end_date = mktime( 23, 59, 59, date("n"), date("t") );
                break;
    
            case 'last_month':
                $end_date = mktime( 23, 59, 59, date("n") - 1, date("t") - 1 );
                break;
    
            case 'this_quarter':
                $current_month = date('m');
                $current_year = date('Y');
                
                if( $current_month >= 1 && $current_month <= 3) {
                    $end_date = strtotime( '31-March-'.$current_year.'23:59:59' );  // timestamp or 1-April 12:00:00 AM means end of 31 March
                }
                else if( $current_month >= 4 && $current_month <=6 ) {
                    $end_date = strtotime( '30-July-'.$current_year.'23:59:59' );  // timestamp or 1-July 12:00:00 AM means end of 30 June
                }
                else if( $current_month >= 7 && $current_month <= 9 ) {
                    $end_date = strtotime( '30-September-'.$current_year.'23:59:59' );  // timestamp or 1-October 12:00:00 AM means end of 30 September
                }
                else if( $current_month >= 10 && $current_month <= 12) {
                    $end_date = strtotime( '1-January-'.( $current_year + 1 ).'23:59:59');  // timestamp or 1-January Next year 12:00:00 AM means end of 31 December this year
                }
                
                break;
    
            case 'last_quarter':
                
                $current_month = date('m');
                $current_year = date('Y');
                
                if( $current_month >= 1 && $current_month <= 3 ) {
                    $end_date = strtotime( '1-January-'.$current_year.'23:59:59' );  // // timestamp or 1-January  12:00:00 AM means end of 31 December Last year
                }
                else if( $current_month >= 4 && $current_month <= 6 ) {
                    $end_date = strtotime( '31-March-'.$current_year.'23:59:59' );  // timestamp or 1-April 12:00:00 AM means end of 31 March
                }
                else if( $current_month >= 7 && $current_month <= 9 ) {
                    $end_date = strtotime( '30-June-'.$current_year.'23:59:59' );  // timestamp or 1-July 12:00:00 AM means end of 30 June
                }
                else if( $current_month >= 10 && $current_month <= 12 ) {
                    $end_date = strtotime( '30-September-'.$current_year.'23:59:59' );  // timestamp or 1-October 12:00:00 AM means end of 30 September
                }
                break;
    
            case 'this_year':
                
                $end_date =  mktime( 23, 59, 59, date('m'), date('d'), date('y') ); // it will restrict date from todays date. so Jan 1st to the current date
                break;
    
            case 'last_year':
                $end_date = mktime( 23, 59, 59, 12, 31, date('Y')-1 );
                break;
                
            case 'other':
                $explode_end_date = explode ( "-", $user_end_date );
                $month            = $explode_end_date[1];
                $date             = $explode_end_date[2];
                $year             = $explode_end_date[0];
                $end_date         = mktime( 23, 59, 59, $month, $date, $year );
                break;

            default:
                $end_date = time();
        }
        return $end_date;
    }
    /** 
     *Get the report of recover orders, total sales of current month.
     * 
     * @param  string $type Recovere & total sales
     * @param  string $selected_data_range Selected Date range
     * @param  string $start_date start date
     * @param  string $end_date end date
     * @return int $count_month Recover Amount and total sales
     * @since  3.5
     */
    function get_this_month_amount_reports( $type, $selected_data_range, $start_date, $end_date ) {
    
        $count_month = 0;
        $begin_of_month = $this->get_begin_of_month ( $selected_data_range, $start_date ); //mktime(0, 0, 0, date("n"), 1);
        $end_of_month   = $this->get_end_of_month   ( $selected_data_range, $end_date ); //mktime(23, 59, 0, date("n"), date("t"));
    
        switch ( $type ){
            case 'recover':
                $count_month = $this->get_current_month_recovered_amount ( $begin_of_month, $end_of_month);
                break;
    
            case 'wc_total_sales':
                $count_month = $this->get_wc_total_sales ( $begin_of_month, $end_of_month);
                break;
        }
        return $count_month;
    }
    
    /** 
     * Get the report of abandoned carts, recovered Orders of current month.
     * 
     * @param  string $type Recovere & total sales
     * @param  string $selected_data_range Selected Date range
     * @param  string $start_date start date
     * @param  string $end_date end date
     * @return int $count Abandoned Amount & Recover Amount
     * @since  3.5
     */
    function get_this_month_number_reports ( $type, $selected_data_range, $start_date, $end_date ) {
        $count = 0;
        $begin_of_month = $this->get_begin_of_month ( $selected_data_range, $start_date ); //mktime(0, 0, 0, date("n"), 1);
        $end_of_month   = $this->get_end_of_month   ( $selected_data_range, $end_date ); //mktime(23, 59, 0, date("n"), date("t"));
    
        switch ( $type ) {
            case 'abandoned':
                $count = $this->get_current_month_abandoned_count ( $begin_of_month, $end_of_month);
                break;
    
            case 'recover':
                $count = $this->get_current_month_recovered_count ( $begin_of_month, $end_of_month);
                break;
        }
        return $count;
    }
    /** 
     * Get the report of abandoned carts, recovered Orders of current month.
     * 
     * @param  string $type Abandoned carts & total orders
     * @param  string $selected_data_range Selected Date range
     * @param  string $start_date start date
     * @param  string $end_date end date
     * @return int $count Abandoned carts & total orders
     * @since  3.5
     */
    function get_this_month_total_vs_abandoned_order( $type, $selected_data_range, $start_date, $end_date ) {
        $count          = 0;
        $begin_of_month = $this->get_begin_of_month ( $selected_data_range, $start_date );
        $end_of_month   = $this->get_end_of_month   ( $selected_data_range, $end_date );
    
        switch ( $type ) {
    
            case 'abandoned':
                $count = $this->get_current_month_abandoned_count( $begin_of_month, $end_of_month );
                break;
    
            case 'wc_total_orders':
                $count = $this->get_this_month_wc_total_order_count( $begin_of_month, $end_of_month );
                break;
        }
        return $count;
    }
    /** 
     * Get the report of Email Sent, opened emails & Email Clicked of current month.
     * 
     * @param  string $type Email Sent, opened emails & Email Clicked 
     * @param  string $selected_data_range Selected Date range
     * @param  string $start_date start date
     * @param  string $end_date end date
     * @return int $count total count of Email Sent, opened emails & Email Clicked 
     * @since  3.5
     */
    function wcap_get_email_report ( $type, $selected_data_range, $user_start_date, $user_end_date ){
        $count = 0;
        $begin_date             = $this->get_begin_of_month ( $selected_data_range, $user_start_date );
        $end_date               = $this->get_end_of_month   ( $selected_data_range, $user_end_date );
        
        $start_date_db          = date( 'Y-m-d H:i:s', $begin_date );
        $end_date_db            = date( 'Y-m-d H:i:s', $end_date );
        switch ( $type ) {
            case 'total_sent':
                $count = $this->wcap_get_total_email_sent_count ( $start_date_db, $end_date_db);
                break;
        
            case 'total_opened':
                $count = $this->wcap_get_total_emails_opened ( $start_date_db, $end_date_db);
                break;
                
            case 'total_clicked':
                $count = $this->wcap_get_total_emails_clicked ( $start_date_db, $end_date_db);
                break;
        }
        return $count;
    }

    /** 
     * Get total count of Email Sent.
     * 
     * @param  string $start_date start date
     * @param  string $end_date end date
     * @return int $count total count of Email Sent
     * @globals mixed $wpdb
     * @since  3.5
     */
    function wcap_get_total_email_sent_count ( $start_date_db, $end_date_db ) {
        global $wpdb; 
        $query_ac_sent          = "SELECT COUNT(wpsh.id) FROM " . WCAP_EMAIL_SENT_HISTORY_TABLE . " as wpsh 
                                    WHERE wpsh.sent_time >= '$start_date_db' AND 
                                    wpsh.sent_time <= '$end_date_db' ";
        $total_sent_email_count = $wpdb->get_var( $query_ac_sent );        
        return $total_sent_email_count;
    }
     /** 
      * Get the total count of email opened.
      * 
      * @param  string $start_date start date
      * @param  string $end_date end date
      * @return int $wcap_opened_emails total count of Email opened.
      * @globals mixed $wpdb
      * @since  3.5
      */
    function wcap_get_total_emails_opened ( $start_date_db, $end_date_db ) {
        global $wpdb;
        $query_ac_opened        = "SELECT COUNT(DISTINCT wpoe.email_sent_id) FROM " . WCAP_EMAIL_OPENED_TABLE . " as wpoe 
                                    LEFT JOIN ". WCAP_EMAIL_SENT_HISTORY_TABLE." AS wpsh ON wpsh.id = wpoe.email_sent_id 
                                    WHERE 
                                    time_opened >= '" . $start_date_db . "' 
                                    AND 
                                    time_opened <= '" . $end_date_db . "' 
                                    AND 
                                    wpsh.id = wpoe.email_sent_id ";
        $wcap_opened_emails     = $wpdb->get_var( $query_ac_opened );
        return $wcap_opened_emails;
    }
     /** 
      * Get the total count of email clicked.
      * 
      * @param  string $start_date start date
      * @param  string $end_date end date
      * @return int $wcap_opened_emails total count of Email opened.
      * @globals mixed $wpdb
      * @since  3.5
      */
    function wcap_get_total_emails_clicked( $start_date_db, $end_date_db ) {
        global $wpdb;
        $query_ac_clicked       = "SELECT COUNT(DISTINCT wplc.email_sent_id) FROM " . WCAP_EMAIL_CLICKED_TABLE . " as wplc 
                                    LEFT JOIN ".WCAP_EMAIL_SENT_HISTORY_TABLE." AS wpsh ON wplc.email_sent_id = wpsh.id 
                                    WHERE wplc.time_clicked >= '" . $start_date_db . "' 
                                    AND 
                                    wplc.time_clicked <= '" . $end_date_db . "' 
                                    ORDER BY wplc.id DESC ";

        $wcap_opened_clicked    = $wpdb->get_var( $query_ac_clicked );
    
        return $wcap_opened_clicked;
    }
    /** 
     * Get the total count of Sales of store.
     * 
     * @param  string $begin_of_month Start date of month
     * @param  string $end_of_month  end date of month
     * @return int $count_month total count of Sales.
     * @globals mixed $wpdb
     * @since  3.5
     */
    function get_wc_total_sales( $begin_of_month, $end_of_month ) {
        global $wpdb; 
        $count_month         = 0;
        $begin_date_of_month = date( 'Y-m-d H:i:s', $begin_of_month );
        $end_date_of_month   = date( 'Y-m-d H:i:s', $end_of_month );
        
        $order_totals = $wpdb->get_row( "
        
            SELECT SUM(meta.meta_value) AS total_sales, COUNT(posts.ID) AS total_orders FROM {$wpdb->posts} AS posts
        
            LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
        
            WHERE meta.meta_key = '_order_total'
        
            AND posts.post_type = 'shop_order'
        
            AND posts.post_date >= '$begin_date_of_month'
        
            AND posts.post_date <= '$end_date_of_month'
        
            AND posts.post_status IN ( '" . implode( "','", array( 'wc-completed', 'wc-processing', 'wc-on-hold' ) ) . "' )
        
            " ) ;
        
            $count_month = $order_totals->total_sales == null ? 0 : $order_totals->total_sales;       
        return $count_month;
    }
    
    /** 
     * Get the Amount of Recover Orders
     * 
     * @param  string $begin_of_month Start date of month
     * @param  string $end_of_month end date of month
     * @return int $count_month total amount of recover orders.
     * @globals mixed $wpdb
     * @since  3.5
     */
    function get_current_month_recovered_amount ( $begin_of_month, $end_of_month ){
        global $wpdb;
        $blank_cart_info            =  '{"cart":[]}';
        $blank_cart_info_guest      =  '[]';
        $ac_results                 = array();
        $query_ac                   = "SELECT id, recovered_cart FROM " . WCAP_ABANDONED_CART_HISTORY_TABLE . " 
                                        WHERE abandoned_cart_info NOT LIKE %s 
                                        AND 
                                        abandoned_cart_info NOT LIKE %s 
                                        AND 
                                        abandoned_cart_time >= %d 
                                        AND 
                                        abandoned_cart_time <= %d 
                                        AND 
                                        recovered_cart > 0 
                                        AND 
                                        wcap_trash = '' 
                                        ORDER BY recovered_cart desc";
        $ac_results                 = $wpdb->get_results( $wpdb->prepare( $query_ac, $blank_cart_info, $blank_cart_info_guest, $begin_of_month, $end_of_month ) );

        $recovered_order_total      = 0;
        $wc_get_price_decimals      = wc_get_price_decimals();
        $this->total_recover_amount = round( $recovered_order_total, $wc_get_price_decimals )  ;
        $count_month                = 0;
        $table_data                 = "";
        foreach ( $ac_results as $key => $value ) {
            if( 0 != $value->recovered_cart ) {
                $abandoned_order_id = $value->id;
                $recovered_id       = $value->recovered_cart;
                $rec_order          = get_post_meta( $recovered_id, '_order_total' );

                $recovered_order_total        = 0;
                if ( isset( $rec_order[0] ) ) {
                    $wcap_recovered_amount = $rec_order[0];
                    $recovered_order_total = $wcap_recovered_amount;
                }
                $count_month = round( ( $recovered_order_total + $count_month ) , $wc_get_price_decimals );
            }
        }
        return $count_month;
    }
    /** 
     * Get the Amount of Abandoned Orders.
     * 
     * @param  string $begin_of_month Start date of month
     * @param  string $end_of_month  end date of month
     * @return int $count_month total amount of Abandoned Orders.
     * @globals mixed $wpdb
     * @since  3.5
     */
    function get_current_month_abandoned_amount( $begin_of_month, $end_of_month ) {
        global $wpdb;
        $count_month             = 0;
        $start_date              = $begin_of_month;
        $end_date                = $end_of_month;
        $blank_cart_info         = '{"cart":[]}';
        $blank_cart_info_guest   = '[]';
        $query_ac_carts          = "SELECT abandoned_cart_info FROM " . WCAP_ABANDONED_CART_HISTORY_TABLE . " 
                                    WHERE abandoned_cart_info NOT LIKE %s 
                                    AND 
                                    abandoned_cart_info NOT LIKE %s 
                                    AND 
                                    abandoned_cart_time >= %d 
                                    AND 
                                    abandoned_cart_time <= %d 
                                    AND 
                                    wcap_trash = '' AND cart_ignored <> '1'";
        $ac_carts_results        = $wpdb->get_results( $wpdb->prepare( $query_ac_carts, $blank_cart_info, $blank_cart_info_guest, $start_date, $end_date ) );
        $count_carts = $total_value = 0;
        
        foreach ( $ac_carts_results as $key => $value ) {
            $count_carts += 1;
            $cart_detail = json_decode( stripslashes( $value->abandoned_cart_info ) );
            $product_details = array();
            if( isset( $cart_detail->cart ) ){
                $product_details = $cart_detail->cart;
            }
            $line_total = 0;
            if ( isset( $product_details ) && count( $product_details ) > 0 && $product_details != false ) {
                foreach ( $product_details as $k => $v ) {
                    if( $v->line_subtotal_tax != 0 && $v->line_subtotal_tax > 0 ) {
                        $line_total = $line_total + $v->line_total + $v->line_subtotal_tax;
                    } else {
                        $line_total = $line_total + $v->line_total;
                    }
                }
            }
            $total_value += $line_total;
        }
        $count_month = round( $total_value, wc_get_price_decimals() );
        return $count_month;
    }
    /** 
     * Get the total count of Sales of Abandoned Orders.
     * 
     * @param  string $begin_of_month Start date of month
     * @param  string $end_of_month end date of month
     * @return int $count_month total count of Abandoned Orders.
     * @globals mixed $wpdb
     * @since  3.5
     */
    function get_current_month_abandoned_count ( $begin_of_month, $end_of_month ) {
        global $wpdb;
        $count_month             = 0;
        $blank_cart_info         = '{"cart":[]}';
        $blank_cart_info_guest   = '[]';
        
        $ac_cutoff_time = get_option( 'ac_cart_abandoned_time' );
        $cut_off_time   = $ac_cutoff_time * 60;        
        $current_time   = current_time( 'timestamp' );
        $compare_time   = $current_time - $cut_off_time;
        
        
        $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
        $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
        $current_time          = current_time ('timestamp');
        $compare_time_guest    = $current_time - $cut_off_time_guest;
        
    
        $query_abandoned  = "SELECT COUNT(id) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` 
                            WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >=  $begin_of_month AND abandoned_cart_time <= $end_of_month AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND cart_ignored <> '1') 
                            OR ( user_type = 'GUEST' AND abandoned_cart_time >=  $begin_of_month AND abandoned_cart_time <= $end_of_month AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND cart_ignored <> '1' )
                            ";
        $count_month = $wpdb->get_var( $query_abandoned );
        return $count_month;
    }

    function get_adv_stats( $selected_data_range, $start_date, $end_date ) {
        global $wpdb;

        $begin_of_month = $this->get_begin_of_month ( $selected_data_range, $start_date ); //mktime(0, 0, 0, date("n"), 1);
        $end_of_month   = $this->get_end_of_month   ( $selected_data_range, $end_date ); //mktime(23, 59, 0, date("n"), date("t"));

        $count_month             = 0;
        $blank_cart_info         = '{"cart":[]}';
        $blank_cart_info_guest   = '[]';
        
        $ac_cutoff_time = get_option( 'ac_cart_abandoned_time' );
        $cut_off_time   = $ac_cutoff_time * 60;        
        $current_time   = current_time( 'timestamp' );
        $compare_time   = $current_time - $cut_off_time;
        
        
        $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
        $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
        $current_time          = current_time ('timestamp');
        $compare_time_guest    = $current_time - $cut_off_time_guest;
    
        $query_abandoned  = "SELECT abandoned_cart_info, recovered_cart FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` 
                            WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >=  $begin_of_month AND abandoned_cart_time <= $end_of_month AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND ( cart_ignored <> '1' OR recovered_cart <> '0' ) ) 
                            OR ( user_type = 'GUEST' AND abandoned_cart_time >=  $begin_of_month AND abandoned_cart_time <= $end_of_month AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND ( cart_ignored <> '1' OR recovered_cart <> '0' ) )
                            ";

        $count_month = $wpdb->get_results( $query_abandoned );

        $abandoned_count = $recovered_count = $abandoned_amount = $recovered_amount = 0;

        foreach ( $count_month as $cart_value ) {

            $abandoned_count++;

            if ( $cart_value->recovered_cart !== '0' ) {
                $recovered_count++;
            }

            $cart_info = json_decode( stripslashes( $cart_value->abandoned_cart_info ) );
            if ( isset( $cart_info->cart ) ) {
                foreach ( $cart_info->cart as $cart ) {
                    $abandoned_amount += isset( $cart->line_total ) ? $cart->line_total : 0;

                    if ( $cart_value->recovered_cart !== '0' ) {
                        $recovered_amount += isset( $cart->line_total ) ? $cart->line_total : 0;
                    }

                }
            }
        }

        return array(
            'abandoned_count' => $abandoned_count,
            'recovered_count' => $recovered_count,
            'abandoned_amount' => $abandoned_amount,
            'recovered_amount' => $recovered_amount
        );
    }
    
    /** 
     * Get the total count of Recovered Orders.
     * 
     * @param  string $begin_of_month Start date of month
     * @param  string $end_of_month end date of month
     * @return int $count_month total count of Recovered Orders.
     * @globals mixed $wpdb
     * @since  3.5
     */
    function get_current_month_recovered_count( $begin_of_month, $end_of_month ) {
        global $wpdb;
        $count_month             = 0;
        $blank_cart_info         = '{"cart":[]}';
        $blank_cart_info_guest   = '[]';
    
        $query_recover  = "SELECT COUNT(wach.recovered_cart) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` as wach 
                            LEFT JOIN ".$wpdb->prefix."posts AS wposts ON wach.recovered_cart = wposts.ID 
                            WHERE 
                            wach.abandoned_cart_time >=  $begin_of_month 
                            AND 
                            wach.abandoned_cart_time <= $end_of_month 
                            AND 
                            wach.recovered_cart != 0 
                            AND 
                            wach.abandoned_cart_info NOT LIKE '%$blank_cart_info%' 
                            AND 
                            wach.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' 
                            AND 
                            wach.wcap_trash = '' 
                            AND 
                            wach.recovered_cart = wposts.ID ";
        $count_month    = $wpdb->get_var( $query_recover );
        return $count_month;
    }

    /** 
     * Get the total count of sales of store.
     * 
     * @param  string $begin_of_month Start date of month
     * @param  string $end_of_month  end date of month
     * @return int $count_month total count of Sales.
     * @globals mixed $wpdb
     * @since  3.5
     */
    
    function get_this_month_wc_total_order_count( $begin_of_month, $end_of_month ) {
        global $wpdb;
        $count_month         = 0;
        $begin_date_of_month = date( 'Y-m-d H:i:s', $begin_of_month );
        $end_date_of_month   = date( 'Y-m-d H:i:s', $end_of_month );
        $sales_query =  "SELECT COUNT(ID) FROM {$wpdb->posts} as posts
                            WHERE posts.post_type     = 'shop_order'
                            AND   
                            posts.post_date >= '$begin_date_of_month'
                            AND   
                            posts.post_date <= '$end_date_of_month' 
                            AND   
                            posts.post_status IN ( '" . implode( "','", array( 'wc-completed', 'wc-processing', 'wc-on-hold' ) ) . "' ) ";
        $count_month = $wpdb->get_var( $sales_query );
        return $count_month;
    }
    
   /** 
    * Get the total Email Sents.
    * 
    * @param  int $wcap_template_id Email Template ID
    * @param  string $selected_data_range Selected Date Range
    * @param  string $start_date Start date
    * @param  string $end_date  end date
    * @return int $wcap_emails_sent_count total count of Email Sent.
    * @globals mixed $wpdb
    * @since  3.9
    */
    function wcap_get_total_email_sent_for_template( $wcap_template_id, $selected_data_range, $start_date, $end_date ) {
        global $wpdb;
        $begin_of_month      = $this->get_begin_of_month ( $selected_data_range, $start_date ); //mktime(0, 0, 0, date("n"), 1);
        $end_of_month        = $this->get_end_of_month   ( $selected_data_range, $end_date );
        $begin_date_of_month = date( 'Y-m-d H:i:s', $begin_of_month );
        $end_date_of_month   = date( 'Y-m-d H:i:s', $end_of_month );
    
        $query_no_emails        = "SELECT COUNT(id) FROM " . WCAP_EMAIL_SENT_HISTORY_TABLE . " 
                                    WHERE template_id = $wcap_template_id  
                                    AND 
                                    sent_time >=  '$begin_date_of_month' 
                                    AND 
                                    sent_time <= '$end_date_of_month'";
        $wcap_emails_sent_count = $wpdb->get_var( $query_no_emails );
        return $wcap_emails_sent_count;
    }
     /** 
      * Get the total count of Email Opened.
      * 
      * @param  int $wcap_template_id Email Template ID
      * @param  string $selected_data_range Selected Date Range
      * @param  string $start_date Start date
      * @param  string $end_date  end date
      * @return int $wcap_emails_opened_count total count of Email Opened.
      * @globals mixed $wpdb
      * @since  3.9
      */
    function wcap_get_total_email_open_for_template( $wcap_template_id, $selected_data_range, $start_date, $end_date ){
        global $wpdb;
        $begin_of_month      = $this->get_begin_of_month ( $selected_data_range, $start_date ); //mktime(0, 0, 0, date("n"), 1);
        $end_of_month        = $this->get_end_of_month   ( $selected_data_range, $end_date );
        $begin_date_of_month = date( 'Y-m-d H:i:s', $begin_of_month );
        $end_date_of_month   = date( 'Y-m-d H:i:s', $end_of_month );
        $query_ac_opened     = "SELECT COUNT( DISTINCT wpoe.email_sent_id )
                                FROM " . WCAP_EMAIL_OPENED_TABLE . " AS wpoe
                                LEFT JOIN ".WCAP_EMAIL_SENT_HISTORY_TABLE." AS wpsh ON wpsh.id = wpoe.email_sent_id
                                WHERE time_opened >=  '$begin_date_of_month'
                                AND time_opened <=  '$end_date_of_month'
                                AND wpsh.id = wpoe.email_sent_id
                                AND wpsh.template_id = '$wcap_template_id' ";
        $wcap_emails_opened_count = $wpdb->get_var( $query_ac_opened );
        return $wcap_emails_opened_count;
    }
    /** 
     * Get the total count of Email link clicked.
     * 
     * @param  int $wcap_template_id Email Template ID
     * @param  string $selected_data_range Selected Date Range
     * @param  string $start_date Start date
     * @param  string $end_date  end date
     * @return int $wcap_emails_clicked_count total count of Email link clicked.
     * @globals mixed $wpdb
     * @since  3.9
     */
    function wcap_get_total_email_click_for_template ( $wcap_template_id, $selected_data_range, $start_date, $end_date ){
        global $wpdb;
        $begin_of_month      = $this->get_begin_of_month ( $selected_data_range, $start_date ); //mktime(0, 0, 0, date("n"), 1);
        $end_of_month        = $this->get_end_of_month   ( $selected_data_range, $end_date );
        $begin_date_of_month = date( 'Y-m-d H:i:s', $begin_of_month );
        $end_date_of_month   = date( 'Y-m-d H:i:s', $end_of_month );
    
        $query_ac_clicked    = "SELECT COUNT( DISTINCT wplc.email_sent_id) FROM " . WCAP_EMAIL_CLICKED_TABLE . " as wplc 
                                LEFT JOIN ".WCAP_EMAIL_SENT_HISTORY_TABLE." AS wpsh ON wpsh.id = wplc.email_sent_id 
                                WHERE wpsh.template_id = $wcap_template_id 
                                AND 
                                wpsh.sent_time >= '$begin_date_of_month' 
                                AND 
                                wpsh.sent_time <= '$end_date_of_month' 
                                AND wplc.email_sent_id IN ( SELECT email_sent_id FROM " . WCAP_EMAIL_OPENED_TABLE . " )
                                ";
        $wcap_emails_clicked_count = $wpdb->get_var( $query_ac_clicked );
        return $wcap_emails_clicked_count;
    }
    /** 
     * Get the Recovery Rate for template.
     * 
     * @param  int $wcap_template_id Email Template ID
     * @param  string $selected_data_range Selected Date Range
     * @param  string $start_date Start date
     * @param  string $end_date  end date
     * @return int $number_order_recovered_count Recovery Rate.
     * @globals mixed $wpdb
     * @since  3.9
     */
    function wcap_get_total_email_recover_for_template( $wcap_template_id, $selected_data_range, $start_date, $end_date ) {
        global $wpdb;
        $begin_of_month      = $this->get_begin_of_month ( $selected_data_range, $start_date ); //mktime(0, 0, 0, date("n"), 1);
        $end_of_month        = $this->get_end_of_month   ( $selected_data_range, $end_date );
        $begin_date_of_month = date( 'Y-m-d H:i:s', $begin_of_month );
        $end_date_of_month   = date( 'Y-m-d H:i:s', $end_of_month );
    
         $query_recovered_orders = "SELECT COUNT(wpsh.id) FROM " . WCAP_EMAIL_SENT_HISTORY_TABLE ." AS wpsh
                                    LEFT JOIN ". WCAP_ABANDONED_CART_HISTORY_TABLE." AS wpac
                                    ON wpsh.abandoned_order_id = wpac.id WHERE wpsh.abandoned_order_id = wpac.id AND wpsh.template_id = $wcap_template_id 
                                    AND 
                                    wpsh.recovered_order != '0' 
                                    AND 
                                    wpsh.sent_time >=  '$begin_date_of_month' 
                                    AND 
                                    wpsh.sent_time <= '$end_date_of_month' 
                                    ";
        $number_order_recovered_count   = $wpdb->get_var( $query_recovered_orders );
        return $number_order_recovered_count;
    }
    /** 
     * Get the Add to Cart Popup Modal Data.
     *
     * @param  string $selected_data_range Selected Date Range
     * @param  string $start_date Start date
     * @param  string $end_date  end date
     * @return int $wcap_post_ac_atc_stats Add To Cart Popup States.
     * @globals mixed $wpdb
     * @since  6.0
     */
    function wcap_get_atc_data_of_range( $selected_data_range, $start_date, $end_date ) {
        global $wpdb;
        $begin_of_month      = $this->get_begin_of_month ( $selected_data_range, $start_date );
        $end_of_month        = $this->get_end_of_month   ( $selected_data_range, $end_date );
        $blank_cart_info        = '{"cart":[]}';
        $blank_cart_info_guest  = '[]';
        $wcap_post_ac_atc_stats = array(
            "wcap_atc_open" => 0, 
            "wcap_has_email" => 0, 
            "wcap_not_has_email" => 0
        );

        $ac_cutoff_time = get_option( 'ac_cart_abandoned_time' );
        $cut_off_time   = $ac_cutoff_time * 60;        
        $current_time   = current_time( 'timestamp' );
        $compare_time   = $current_time - $cut_off_time;
        
        $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
        $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
        $current_time          = current_time ('timestamp');
        $compare_time_guest    = $current_time - $cut_off_time_guest;
        
        $query_abandoned  = "SELECT * FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >=  $begin_of_month AND abandoned_cart_time <= $end_of_month AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND cart_ignored <> '1') OR ( user_type = 'GUEST' AND abandoned_cart_time >=  $begin_of_month AND abandoned_cart_time <= $end_of_month AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND cart_ignored <> '1') ORDER BY id asc";
        $ac_carts_results = $wpdb->get_results($query_abandoned);  

        if ( count( $ac_carts_results ) > 0  ){
            $wcap_start_ac_id = reset( $ac_carts_results );
            $wcap_end_ac_id   = end  ( $ac_carts_results );
            
            $wcap_post_ac_carts_stats   = "SELECT post_id, meta_value FROM " . $wpdb->prefix . "postmeta WHERE meta_key = 'wcap_atc_report' AND post_id >= %d AND post_id <= %d ";
            $wcap_post_ac_carts_stats_results = $wpdb->get_results( $wpdb->prepare( $wcap_post_ac_carts_stats, $wcap_start_ac_id->id , $wcap_end_ac_id->id ) );

            $wcap_atc_open_count            = 0;
            $wcap_atc_email_given_count     = 0;
            $wcap_atc_not_email_given_count = 0;

            foreach( $wcap_post_ac_carts_stats_results as $wcap_post_ac_carts_stats_results_key => $wcap_post_ac_carts_stats_results_value ) {
                $wcap_ac_atc_data        = $wcap_post_ac_carts_stats_results_value->meta_value;
                $wcap_ac_atc_data_decode = unserialize( $wcap_ac_atc_data );
                if( 'yes' == $wcap_ac_atc_data_decode['wcap_atc_open'] ) {
                    $wcap_atc_open_count++;
                }
                if( 'yes' == $wcap_ac_atc_data_decode['wcap_atc_action'] ) {
                    $wcap_atc_email_given_count++;
                } else if ( 'no' == $wcap_ac_atc_data_decode['wcap_atc_action'] ) {
                    $wcap_atc_not_email_given_count++;
                }
            }
            $wcap_post_ac_atc_stats = array( 
                "wcap_atc_open" => $wcap_atc_open_count, 
                "wcap_has_email" => $wcap_atc_email_given_count, 
                "wcap_not_has_email" => $wcap_atc_not_email_given_count ); 
        }
        return $wcap_post_ac_atc_stats;
    }

    /**
     * Get Graph Data
     * 
     */
    function get_abandoned_data( $selected_data_range, $begin_of_month, $end_of_month ) {
        global $wpdb;

        /*$begin_of_month = $this->get_begin_of_month ( $selected_data_range, $begin_of_month ); //mktime(0, 0, 0, date("n"), 1);
        $end_of_month   = $this->get_end_of_month   ( $selected_data_range, $end_of_month ); //mktime(23, 59, 0, date("n"), date("t"));
        $current_time   = current_time('timestamp');
        $end_key        = $end_of_month;

        $data = array();

        if ( $current_time < $end_of_month ) {
            $end_key = $current_time;
        }

        $days = round( ( $end_key - $begin_of_month )  / (60 * 60 * 24) );*/

        $begin_of_month = current_time('timestamp');
        $days = 8;

        $data[date('Y-m-d', $begin_of_month)] = array(
            'abandoned_amount' => 0,
            'recovered_amount' => 0
        );
        for( $day = 1; $day < $days; $day++ ){
            $data[date('Y-m-d', strtotime( "-$day day", $begin_of_month))] = array(
                'abandoned_amount' => 0,
                'recovered_amount' => 0
            );

            $end_date = strtotime( "-$day day", $begin_of_month );
        }

        $count_month             = 0;
        $start_date              = $end_of_month;
        $end_date                = $begin_of_month;
        $blank_cart_info         = '{"cart":[]}';
        $blank_cart_info_guest   = '[]';
        $query_ac_carts          = "SELECT abandoned_cart_time, recovered_cart, abandoned_cart_info FROM " . WCAP_ABANDONED_CART_HISTORY_TABLE . " 
                                    WHERE abandoned_cart_info NOT LIKE %s AND 
                                    abandoned_cart_info NOT LIKE %s AND 
                                    abandoned_cart_time >= %d AND 
                                    abandoned_cart_time <= %d AND 
                                    wcap_trash = '' AND ( cart_ignored <> '1' OR recovered_cart <> '0' )";

        $ac_carts_results        = $wpdb->get_results( $wpdb->prepare( $query_ac_carts, $blank_cart_info, $blank_cart_info_guest, $start_date, $end_date ) );

        foreach ( $ac_carts_results as $cart_value ) {
            $selected_date = date('Y-m-d', $cart_value->abandoned_cart_time);

            if ( isset( $data[$selected_date] ) ) {
                $cart_info = json_decode( stripslashes( $cart_value->abandoned_cart_info ) );
                if( isset( $cart_info ) && false != $cart_info && count( get_object_vars( $cart_info ) ) > 0 && isset( $cart_info->cart ) ) {
                    foreach ( $cart_info->cart as $cart ) {
                        $data[$selected_date]['abandoned_amount'] += isset( $cart->line_total ) ? $cart->line_total : 0;

                        if ( $cart_value->recovered_cart != 0 ) {
                            $data[$selected_date]['recovered_amount'] += isset( $cart->line_total ) ? $cart->line_total : 0;
                        }
                    }
                }
            }
        }
        return array_reverse( $data );
    }

}
