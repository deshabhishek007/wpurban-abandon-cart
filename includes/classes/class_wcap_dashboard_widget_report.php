<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * It will get the record of Abandoned Carts, Recovered Orders and products from database and show it on Dashboard Widget page.
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @category Classes
 * @since    5.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 *  This class will get the Abandoned Carts, Recovered Orders and products data from database.
 *
 * @since 2.8
 */
class Wcap_Dashboard_Widget_Report {
    /**
     * Get the report of Today.
     *
     *
     * @param String $type Types of case-abandoned, recover, ratio
     * @return int $count Count data
     * @globals mixed $wpdb
     * @access public
     * @since 2.8
     */  
    function get_today_reports( $type ) {
        global $wpdb;
        
        $count = 0;
        $blank_cart_info       = '{"cart":[]}';
        $blank_cart_info_guest = '[]';
        
        $ac_cutoff_time        = get_option( 'ac_cart_abandoned_time' );
        $cut_off_time          = $ac_cutoff_time * 60;
        $current_time          = current_time ('timestamp');
        $compare_time          = $current_time - $cut_off_time;
        
        $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
        $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
        $compare_time_guest    = $current_time - $cut_off_time_guest;
        
        $beginOfDay = strtotime( "midnight", $current_time );
        $endOfDay   = strtotime( "tomorrow", $current_time ) - 1;
        
        switch( $type ){
            
            case 'abandoned':
                
                $query_abandoned   = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >= $beginOfDay AND abandoned_cart_time <= $endOfDay AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND cart_ignored != '1' ) OR ( user_type = 'GUEST' AND abandoned_cart_time >= $beginOfDay AND abandoned_cart_time <= $endOfDay AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND cart_ignored != '1' ) ";

                $count = $wpdb->get_var( $query_abandoned );
                break;
            
            case 'recover':

                $query_recover   = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE abandoned_cart_time >=  $beginOfDay AND abandoned_cart_time <= $endOfDay AND recovered_cart != 0 AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' ";
                $count = $wpdb->get_var( $query_recover );
                       
                break;
            
            case 'ratio':
                
                $count_recover = $count_abandoned = '0';
                
                $query_recover  = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE abandoned_cart_time >=  $beginOfDay AND abandoned_cart_time <= $endOfDay AND recovered_cart != 0 AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' ";
                $count_recover = $wpdb->get_var( $query_recover );
                
                
                $query_abandoned   = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >= $beginOfDay AND abandoned_cart_time <= $endOfDay AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND cart_ignored != '1' ) OR ( user_type = 'GUEST' AND abandoned_cart_time >= $beginOfDay AND abandoned_cart_time <= $endOfDay AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND cart_ignored != '1' ) ";
                $count_abandoned = $wpdb->get_var( $query_abandoned );
                
                if ( $count_recover > 0 ){ 
                    $count =  ( $count_recover /  $count_abandoned ) * 100 ;
                }
                
            break;
        }
        return $count;
    }
    /**
     * Get the report of this month.
     *
     *
     * @param String $type Types of case-abandoned, recover, ratio
     * @return int $count_month Count data
     * @globals mixed $wpdb
     * @access public
     * @since 2.8
     */
    function get_this_month_reports( $type ) {
        global $wpdb;
    
        $count_month = 0;
    
        $blank_cart_info       = '{"cart":[]}';
        $blank_cart_info_guest = '[]';
    
        $ac_cutoff_time        = get_option( 'ac_cart_abandoned_time' );
        $cut_off_time          = $ac_cutoff_time * 60;
        $current_time          = current_time ('timestamp');
        $compare_time          = $current_time - $cut_off_time;
        
        $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
        $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
        $compare_time_guest    = $current_time - $cut_off_time_guest;
    
        $begin_of_month        = mktime(0, 0, 0, date("n"), 1);
        $end_of_month          = mktime(23, 59, 0, date("n"), date("t"));
    
        switch ( $type ){
    
            case 'abandoned':
    
                $query_abandoned   = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >= $begin_of_month AND abandoned_cart_time <= $end_of_month AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND cart_ignored != '1' ) OR ( user_type = 'GUEST' AND abandoned_cart_time >=  $begin_of_month AND abandoned_cart_time <= $end_of_month AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND cart_ignored != '1'  ) ";
                $count_month = $wpdb->get_var($query_abandoned);
                    
                break;
    
            case 'recover':
    
                $query_recover  = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE abandoned_cart_time >= $begin_of_month AND abandoned_cart_time <= $end_of_month AND recovered_cart != 0 AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' ";
                $count_month = $wpdb->get_var( $query_recover );
      
                break;
    
            case 'ratio':
    
                $count_recover = $count_abandoned = '0';
    
                $query_recover  = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE abandoned_cart_time >= $begin_of_month AND abandoned_cart_time <= $end_of_month AND recovered_cart != 0 AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' ";
                $count_recover = $wpdb->get_var( $query_recover );
    
    
                $query_abandoned  = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >= $begin_of_month AND abandoned_cart_time <= $end_of_month AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND cart_ignored != '1' ) OR ( user_type = 'GUEST' AND abandoned_cart_time >=  $begin_of_month AND abandoned_cart_time <= $end_of_month AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND cart_ignored != '1'  ) ";
                $count_abandoned = $wpdb->get_var( $query_abandoned );
    
                if ( $count_recover > 0 ){
                    $count_month =  ( $count_recover /  $count_abandoned ) * 100 ;
                }
    
                break;
        }
        return $count_month;
    }
    /**
     * Get the report of Last Month.
     *
     *
     * @param String $type Types of case-abandoned, recover, ratio
     * @return int $count_last_month Count data
     * @globals mixed $wpdb
     * @access public
     * @since 2.8
     */
    function get_last_month_reports( $type ) {
        global $wpdb;
    
        $count_last_month      = 0;
        $blank_cart_info       = '{"cart":[]}';
        $blank_cart_info_guest = '[]';
    
        $current_time = current_time ('timestamp');
    
        $last_month_of_begin = mktime(0, 0, 0, date("n")- 1, 1);
        $last_month_of_end   = mktime(23, 59, 0, date("n") - 1 , date("t") - 1 );
    
        switch ( $type ){
    
            case 'abandoned':
    
                $query_abandoned  = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE abandoned_cart_time >=  $last_month_of_begin AND abandoned_cart_time <= $last_month_of_end AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' AND cart_ignored != '1' ";
                $count_last_month = $wpdb->get_var( $query_abandoned );
                break;
    
            case 'recover':
    
                $query_recover  = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE abandoned_cart_time >=  $last_month_of_begin AND abandoned_cart_time <= $last_month_of_end AND recovered_cart != 0 AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' ";
                $count_last_month = $wpdb->get_var( $query_recover );
                break;
    
            case 'ratio':
    
                $count_recover = $count_abandoned = '0';
    
                $query_recover  = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE abandoned_cart_time >=  $last_month_of_begin AND abandoned_cart_time <= $last_month_of_end AND recovered_cart != 0 AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' ";
                $count_recover = $wpdb->get_var( $query_recover );
    
                $query_abandoned  = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE abandoned_cart_time >=  $last_month_of_begin AND abandoned_cart_time <= $last_month_of_end AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' AND cart_ignored != '1' ";
                $count_abandoned = $wpdb->get_var($query_abandoned);
    
                if ( $count_recover > 0 && $count_abandoned > 0 ) {
                    $count_last_month = ( $count_recover / $count_abandoned ) * 100;
                }
    
                break;
        }  
        return $count_last_month;
    }
    /**
     * Get the total reports.
     *
     *
     * @param String $type Types of case-abandoned, recover, ratio
     * @return int $count_last_month Count data
     * @globals mixed $wpdb
     * @access public
     * @since 2.8
     */
    function get_total_reports( $type ) {
        global $wpdb;
    
        $count_last_month      = 0;
        $blank_cart_info       = '{"cart":[]}';
        $blank_cart_info_guest = '[]';
        
        $ac_cutoff_time        = get_option( 'ac_cart_abandoned_time' );
        $cut_off_time          = $ac_cutoff_time * 60;
        $current_time          = current_time ('timestamp');
        $compare_time          = $current_time - $cut_off_time;
        
        $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
        $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
        $compare_time_guest    = $current_time - $cut_off_time_guest;
        
        switch ( $type ){
    
            case 'abandoned':
    
                $query_abandoned   = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND cart_ignored != '1' ) OR ( user_type = 'GUEST' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND cart_ignored != '1' ) ";
                $count_last_month = $wpdb->get_var($query_abandoned);
     
                break;
    
            case 'recover':
    
                $query_recover  = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE recovered_cart != 0 AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' ";
                $count_last_month = $wpdb->get_var( $query_recover );
    
                break;
    
            case 'ratio':
    
                $count_recover = $count_abandoned = '0';
                $query_recover  = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE recovered_cart != 0 AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' ";
                $count_recover = $wpdb->get_var( $query_recover );
    
                $query_abandoned  = "SELECT COUNT(`id`) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND cart_ignored != '1' ) OR ( user_type = 'GUEST' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND cart_ignored != '1' ) ";
                $count_abandoned = $wpdb->get_var( $query_abandoned );
                if ( $count_recover > 0 ) {
                    $count_last_month =  ( $count_recover /  $count_abandoned ) * 100 ;
                }
                break;
        }
        return $count_last_month;
    }
    /**
     * Get Abandoned and Recovered Products to show Top Abandoned Product & Top Recovered Product on Dashboard Widget page.
     *
     *
     * @param String $type Types of case-abandoned, recover.
     * @return int $$product_id Product ID
     * @globals mixed $wpdb
     * @access public
     * @since 2.8
     */  
    function get_product( $type ) {
        global $wpdb;
    
        $product_id            = 0;
        $blank_cart_info       = '{"cart":[]}';
        $blank_cart_info_guest = '[]';
    
        switch( $type ) {
            case 'abandoned':
    
                $query_abandoned  = "SELECT abandoned_cart_info FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' ";
                $results_abandoned = $wpdb->get_results($query_abandoned);
                $products_id_array = array();
                $cart_info = new stdClass();
                
                if ( count ( $results_abandoned ) > 0 ){
                    foreach ($results_abandoned as $results_abandoned_key => $results_abandoned_value) {
                        $cart_info =  json_decode ($results_abandoned_value->abandoned_cart_info) ;
                        if ( is_object( $cart_info ) && false != $cart_info && count( get_object_vars( $cart_info ) ) > 0 ) {
                            foreach ( $cart_info as $cart_info_key => $cart_info_value){
                                if ( $cart_info_key === 'cart' ) {
                                    foreach ($cart_info_value as $cart_info_value_key => $cart_info_value_of_value ){

                                        $wcap_product_id  = $cart_info_value_of_value->product_id ;
                                        if ( isset( $products_id_array [ $wcap_product_id ] ) ) {
                                            $products_id_array [ $wcap_product_id ] = $products_id_array [ $wcap_product_id ] + 1;
                                        } else {
                                            $products_id_array [ $wcap_product_id ] = 1;
                                        }
                                    }
                                }
                            }
                        }
                    }   
                }
                if ( count ( $products_id_array ) > 0 ) {
                    /**
                     * Sort the array with retaining its key.
                     */
                    arsort( $products_id_array ); 
                    /**
                     * Make the pointer to first element
                     */
                    reset ( $products_id_array ) ;

                    $product_id = key( $products_id_array );
                    $products_id_values = array_count_values ( $products_id_array );
                }               
                break;
    
            case 'recover':
    
                $query_recover  = "SELECT abandoned_cart_info FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE recovered_cart != 0 AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' ";
                $results_recover = $wpdb->get_results( $query_recover );
    
                $products_id_array = array();
    
                if ( count ( $results_recover ) > 0 ){
                    foreach ($results_recover as $results_recover_key => $results_recover_value) {
                        $cart_info =  json_decode (stripslashes( $results_recover_value->abandoned_cart_info) );

                        if( isset( $cart_info ) && false != $cart_info && count( get_object_vars( $cart_info ) ) > 0 ) {
                            foreach ( $cart_info as $cart_info_key => $cart_info_value){

                                if ( $cart_info_key === 'cart' ) {
                                    foreach ($cart_info_value as $cart_info_value_key => $cart_info_value_of_value ){
                                        $wcap_product_id  = $cart_info_value_of_value->product_id ;
                                        if ( isset( $products_id_array [ $wcap_product_id ] ) ) {
                                            $products_id_array [ $wcap_product_id ] = $products_id_array [ $wcap_product_id ] + 1;
                                        } else {
                                            $products_id_array [ $wcap_product_id ] = 1;
                                        }
                                    }
                                }
                            }
                        }
                    }   
                }
                if ( count ( $products_id_array ) > 0 ) {
                    /**
                     * Sort the array with retaining its key.
                     */
                    arsort( $products_id_array ); 
                    /**
                     * Make the pointer to first element
                     */
                    reset ( $products_id_array ) ;

                    $product_id = key( $products_id_array );
                }
            break;
        }
        return $product_id;
    }
}