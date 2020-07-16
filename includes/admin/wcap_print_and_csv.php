<?php
/**
 * It will generate and display the data for the print and csv.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Report
 * @since 5.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Print_And_CSV' ) ) {
    /**
     * It will generate and display the data for the print and csv
     */
    class Wcap_Print_And_CSV{

        /**
         * It is used to print the abandoned cart data.
         * @since  3.8
         */
        public static function wcap_print_data() {
            if ( ( isset( $_GET['action'] ) && 'listcart' == $_GET['action'] ) && ( isset( $_GET['wcap_download'] ) && 'wcap.print' == $_GET['wcap_download'] ) ) {
                Wcap_Print_And_CSV::wcap_generate_print_report();
            }
        }

        /**
         * This function used to generate the csv file.
         * @since 3.8
         */
        public static function wcap_generate_csv_report() {
            $wcap_report  = Wcap_Print_And_CSV::wcap_generate_data( );
            $wcap_csv     = Wcap_Print_And_CSV::wcap_generate_csv( $wcap_report );
            return  $wcap_csv;
        }
        /**
         * This function used to generate the Print data.
         * @since 3.8
         */
        public static function wcap_generate_print_report() {
            $wcap_report       = Wcap_Print_And_CSV::wcap_generate_data( );
            $wcap_print_report = Wcap_Print_And_CSV::wcap_generate_print_data( $wcap_report );
            echo $wcap_print_report;
            exit();
        }

        /**
         * It will generate the abandoned cart data for print and csv.
         * @globals mixed $wpdb
         * @globals mixed $woocommerce
         * @return object | array $return_abandoned_orders
         * @since  3.8
         */
        public static function wcap_generate_data() {
            global $wpdb, $woocommerce;
            $return_abandoned_orders = array();
            $per_page                = 30;
            $results                 = array();
            $blank_cart_info         = '{"cart":[]}';
            $blank_cart_info_guest   = '[]';
            $ac_cutoff_time          = get_option( 'ac_cart_abandoned_time' );
            $cut_off_time            = $ac_cutoff_time * 60;
            $current_time            = current_time( 'timestamp' );
            $compare_time            = $current_time - $cut_off_time;
            $ac_cutoff_time_guest    = get_option( 'ac_cart_abandoned_time_guest' );
            $cut_off_time_guest      = $ac_cutoff_time_guest * 60;
            $current_time            = current_time ('timestamp');
            $compare_time_guest      = $current_time - $cut_off_time_guest;
            $get_section_of_page     = Wcap_Common::wcap_get_current_section ();
            $wcap_class     = new Woocommerce_Abandon_Cart();
            $duration_range = "";
            if ( isset( $_POST['duration_select'] ) ) {
                $duration_range = $_POST['duration_select'];
            }
            if( "" == $duration_range ) {
                if ( isset( $_GET['duration_select'] ) && '' != $_GET['duration_select'] ) {
                    $duration_range = $_GET['duration_select'];
                }
            }
            if ( isset( $_SESSION ['duration'] ) && '' != $_SESSION ['duration'] ) {
                $duration_range     = $_SESSION ['duration'];
            }

            if ( "" == $duration_range ) {
                $duration_range = "last_seven";
            }
            $start_date_range = "";
            if ( isset( $_POST['start_date'] ) && '' != $_POST['start_date'] ){
                $start_date_range = $_POST['start_date'];
            }
            if ( isset( $_SESSION ['start_date'] ) &&  '' != $_SESSION ['start_date'] ) {
                $start_date_range = $_SESSION ['start_date'];
            }
            if ( "" == $start_date_range ) {
               $start_date_range = $wcap_class->start_end_dates[$duration_range]['start_date'];
            }
            $end_date_range = "";
            if ( isset( $_POST['end_date'] ) && '' != $_POST['end_date'] ){
                $end_date_range = $_POST['end_date'];
            }

            if ( isset($_SESSION ['end_date'] ) && '' != $_SESSION ['end_date'] ){
                $end_date_range = $_SESSION ['end_date'];
            }

            if ( "" == $end_date_range ) {
                $end_date_range = $wcap_class->start_end_dates[$duration_range]['end_date'];
            }

            $start_date              = strtotime( $start_date_range." 00:01:01" );
            $end_date                = strtotime( $end_date_range." 23:59:59" );

            $status_filter = "( ( wpac.cart_ignored <> '1' AND wpac.recovered_cart = 0) OR ( wpac.cart_ignored = '1' AND wpac.recovered_cart > 0 ) ) ";
            if( isset( $_GET[ 'cart_status' ] ) && '' != $_GET[ 'cart_status' ] ) {
                switch( $_GET[ 'cart_status' ] ) {
                    case 'abandoned':
                        $status_filter = "( wpac.cart_ignored = '0' AND wpac.recovered_cart = 0 ) ";
                        break;
                    case 'recovered':
                        $status_filter = "( wpac.cart_ignored = '1' AND wpac.recovered_cart > 0 ) ";
                        break;
                    case 'unpaid':
                        $status_filter = "( wpac.cart_ignored = '2' AND wpac.recovered_cart = 0 ) ";
                        break;
                    case 'received':
                        $status_filter = "( wpac.cart_ignored = '3' AND wpac.recovered_cart = 0 ) ";
                        break;
                    default:
                        $status_filter = "( ( wpac.cart_ignored <> '1' AND wpac.recovered_cart = 0) OR ( wpac.cart_ignored = '1' AND wpac.recovered_cart > 0 ) ) ";
                        break;
                }
            }
            switch ( $get_section_of_page ) {
              case 'wcap_all_abandoned':
                # code...
                if( is_multisite() ) {
                    $main_prefix = $wpdb->get_blog_prefix(1);
                    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` AS wpac LEFT JOIN ".$main_prefix."users AS wpu ON wpac.user_id = wpu.id
                    WHERE ( user_type = 'REGISTERED' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND $status_filter ) OR ( user_type = 'GUEST' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND $status_filter ) ORDER BY wpac.abandoned_cart_time DESC";
                    $results = $wpdb->get_results($query);
                } else {
                    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` AS wpac LEFT JOIN ".$wpdb->prefix."users AS wpu ON wpac.user_id = wpu.id
                    WHERE ( user_type = 'REGISTERED' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND $status_filter ) OR ( user_type = 'GUEST' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND $status_filter ) ORDER BY wpac.abandoned_cart_time DESC";
                }    
                break;

              case 'wcap_all_registered':
                # code...
                if( is_multisite() ) {
                    $main_prefix = $wpdb->get_blog_prefix(1);
                    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` AS wpac LEFT JOIN ".$main_prefix."users AS wpu ON wpac.user_id = wpu.id
                    WHERE user_type = 'REGISTERED' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND $status_filter ORDER BY wpac.abandoned_cart_time DESC";
                    $results = $wpdb->get_results($query);
                } else {
                    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` AS wpac LEFT JOIN ".$wpdb->prefix."users AS wpu ON wpac.user_id = wpu.id
                    WHERE user_type = 'REGISTERED' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND $status_filter ORDER BY wpac.abandoned_cart_time DESC";
                }
                break;

              case 'wcap_all_guest':
              # code...
                if( is_multisite() ) {
                    $main_prefix = $wpdb->get_blog_prefix(1);
                    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` AS wpac LEFT JOIN ".$main_prefix."users AS wpu ON wpac.user_id = wpu.id
                    WHERE user_type = 'GUEST' AND user_id >= '63000000' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND $status_filter ORDER BY wpac.abandoned_cart_time DESC";
                    $results = $wpdb->get_results($query);
                } else {
                    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` AS wpac LEFT JOIN ".$wpdb->prefix."users AS wpu ON wpac.user_id = wpu.id
                    WHERE user_type = 'GUEST' AND user_id >= '63000000' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND $status_filter ORDER BY wpac.abandoned_cart_time DESC";
                }
                break;

                case 'wcap_all_visitor':
                # code...
                if( is_multisite() ) {
                    $main_prefix = $wpdb->get_blog_prefix(1);
                    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` AS wpac LEFT JOIN ".$main_prefix."users AS wpu ON wpac.user_id = wpu.id
                    WHERE user_type = 'GUEST' AND user_id = '0' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND $status_filter ORDER BY wpac.abandoned_cart_time DESC";
                    $results = $wpdb->get_results($query);
                } else {
                    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` AS wpac LEFT JOIN ".$wpdb->prefix."users AS wpu ON wpac.user_id = wpu.id
                    WHERE user_type = 'GUEST' AND user_id = '0' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND $status_filter ORDER BY wpac.abandoned_cart_time DESC";
                }
                break;

                case 'wcap_trash_abandoned':
                    if( is_multisite() ) {
                        $main_prefix = $wpdb->get_blog_prefix(1);
                        $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` AS wpac LEFT JOIN ".$main_prefix."users AS wpu ON wpac.user_id = wpu.id 
                        WHERE ( user_type = 'REGISTERED' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time' AND wcap_trash = '1' AND $status_filter ) OR ( user_type = 'GUEST' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '1' AND $status_filter ) ORDER BY wpac.abandoned_cart_time DESC";
                        $results = $wpdb->get_results($query);
                    } else {
                        $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` AS wpac LEFT JOIN ".$wpdb->prefix."users AS wpu ON wpac.user_id = wpu.id
                        WHERE ( user_type = 'REGISTERED' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time' AND wcap_trash = '1' AND $status_filter ) OR ( user_type = 'GUEST' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '1' AND $status_filter ) ORDER BY wpac.abandoned_cart_time DESC";
                    }
                break;
                case 'wcap_all_unsubscribe_carts':
                    if( is_multisite() ) {
                        $main_prefix = $wpdb->get_blog_prefix(1);
                        $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` AS wpac LEFT JOIN ".$main_prefix."users AS wpu ON wpac.user_id = wpu.id 
                        WHERE ( user_type = 'REGISTERED' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time' AND unsubscribe_link = '1' AND $status_filter ) OR ( user_type = 'GUEST' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time_guest' AND unsubscribe_link = '1' AND $status_filter ) ORDER BY wpac.abandoned_cart_time DESC";
                        $results = $wpdb->get_results($query);
                    } else {
                        $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` AS wpac LEFT JOIN ".$wpdb->prefix."users AS wpu ON wpac.user_id = wpu.id
                        WHERE ( user_type = 'REGISTERED' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time' AND unsubscribe_link = '1' AND $status_filter ) OR ( user_type = 'GUEST' AND wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_time <= '$compare_time_guest' AND unsubscribe_link = '1' AND $status_filter ) ORDER BY wpac.abandoned_cart_time DESC";
                    }
                break;
                default:
                # code...
                break;
             }

            $results = $wpdb->get_results($query);

            $i = 0;
            $display_tracked_coupons  = get_option( 'ac_track_coupons' );
            $wp_date_format           = get_option( 'date_format' );
            $wp_time_format           = get_option( 'time_format' );
            $ac_cutoff_time           = get_option( 'ac_cart_abandoned_time' );
            $current_time             = current_time( 'timestamp' );
            $wcap_include_tax         = get_option( 'woocommerce_prices_include_tax' );
            $wcap_include_tax_setting = get_option( 'woocommerce_calc_taxes' );

            foreach ( $results as $key => $value ) {
                if ( $value->user_type == "GUEST" ) {
                    $query_guest   = "SELECT * from `" . WCAP_GUEST_CART_HISTORY_TABLE . "` WHERE id = %d";
                    $results_guest = $wpdb->get_results( $wpdb->prepare( $query_guest, $value->user_id ) );
                }
                $abandoned_order_id = $value->id;
                $user_id            = $value->user_id;
                $user_login         = $value->user_login;
                if ( $value->user_type == "GUEST" ) {

                    $user_email = '';
                    if ( isset( $results_guest[0]->email_id ) ) {
                        $user_email = $results_guest[0]->email_id;
                    } 
                    
                    if ( isset( $results_guest[0]->billing_first_name ) ) {
                        $user_first_name = $results_guest[0]->billing_first_name;
                    } else if( $value->user_id == "0" ) {
                        $user_first_name = "Visitor";
                    } else {
                        $user_first_name = "";
                    }

                    $user_last_name = "";
                    if ( isset( $results_guest[0]->billing_last_name ) ) {
                        $user_last_name = $results_guest[0]->billing_last_name;
                    } 

                    $phone = '';
                    if ( isset( $results_guest[0]->phone ) ) {
                        $phone = $results_guest[0]->phone;
                    } 

                } else {
                    $user_email_biiling = get_user_meta( $user_id, 'billing_email', true );
                    $user_email = __( "User Deleted" , "woocommerce-ac" );
                    if( isset( $user_email_biiling ) && "" == $user_email_biiling ) {
                        $user_data  = get_userdata( $user_id );
                        if( isset( $user_data->user_email ) && "" != $user_data->user_email ) {
                            $user_email = $user_data->user_email;
                        } 
                    } else if ( '' != $user_email_biiling ) {
                        $user_email = $user_email_biiling;
                    } 
                    $user_first_name_temp = get_user_meta( $user_id, 'billing_first_name', true );
                    if ( isset( $user_first_name_temp ) && "" == $user_first_name_temp ) {
                        $user_first_name = '';
                        if ( isset($user_data->first_name) && "" == $user_data->first_name ){
                          $user_first_name = $user_data->first_name;
                        }
                    } else {
                        $user_first_name = $user_first_name_temp;
                    }
                    $user_last_name_temp = get_user_meta( $user_id, 'billing_last_name', true );
                    if ( isset( $user_last_name_temp ) && "" == $user_last_name_temp ) {
                        
                        $user_last_name = '';
                        if ( isset($user_data->last_name) && "" == $user_data->last_name ){
                          $user_last_name = $user_data->last_name;
                        }
                    } else {
                        $user_last_name = $user_last_name_temp;
                    }
                    $user_phone_number = get_user_meta( $value->user_id, 'billing_phone' );
                    if( isset( $user_phone_number[0] ) ) {
                        $phone = $user_phone_number[0];
                    } else {
                        $phone = "";
                    }
              }
              $cart_info        = json_decode( stripslashes( $value->abandoned_cart_info ) );
              $order_date       = "";
              $cart_update_time = $value->abandoned_cart_time;
              if( $cart_update_time != "" && $cart_update_time != 0 ) {
                  $date_format = date_i18n( $wp_date_format, $cart_update_time );
                  $time_format = date_i18n( $wp_time_format, $cart_update_time );
                  $order_date  = $date_format . ' ' . $time_format;
              }
              $cut_off_time   = $ac_cutoff_time * 60;
              $compare_time   = $current_time - $cart_update_time;
              $cart_details   = new stdClass();
              $line_total     = 0;
              $cart_total     = $item_subtotal = $item_total = $line_subtotal_tax_display =  $after_item_subtotal = $after_item_subtotal_display = 0;
              $line_subtotal_tax = 0;

              if( isset( $cart_info->cart ) ) {
                  $cart_details = $cart_info->cart;
              }

              // Currency selected
              $currency = isset( $cart_info->currency ) ? $cart_info->currency : '';

                $prod_name = '';
                if( isset( $cart_details ) && is_object( $cart_details ) && count( get_object_vars( $cart_details ) ) > 0 ) {
                    foreach( $cart_details as $k => $v ) {
                        $prod_name      .= "<br>" . apply_filters( 'wcap_product_name', get_the_title( $v->product_id ), $v->product_id ) . "</br>";
                        $wcap_product      = wc_get_product($v->product_id );
                        $wcap_product_type = "";
                        $wcap_sku = '';
                        if ( false !== $wcap_product ) {
                            $wcap_product_type = $wcap_product->get_type();
                            if ( $wcap_product_type == 'simple' && '' != $wcap_product->get_sku() ){
                                $wcap_sku = '<br> SKU: ' . apply_filters( 'wcap_product_sku', $wcap_product->get_sku(), $v->product_id );
                            }
                        }
                        
                        $prod_name         = $prod_name . $wcap_sku ;
                        $prod_name = apply_filters( 'wcap_after_product_name', $prod_name, $v->product_id ); 

                        if( isset( $v->variation_id ) && '' != $v->variation_id ) {
                          $variation_id = $v->variation_id;
                          $variation    = wc_get_product( $variation_id );
                          if ( false !== $variation ) { 
                            $name         = $variation->get_formatted_name() ;
                            $explode_all  = explode( "&ndash;", $name );
                            
                            if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {
                                $wcap_sku = '';
                                if ( $variation->get_sku() ) {
                                    $wcap_sku = "SKU: " . $variation->get_sku() . "<br>";
                                }
                                $wcap_get_formatted_variation  =  wc_get_formatted_variation( $variation, true );

                                $add_product_name = $prod_name . ' - ' . $wcap_sku . $wcap_get_formatted_variation;
                                        
                                $pro_name_variation = (array) $add_product_name;
                            }else{
                                $pro_name_variation = array_slice( $explode_all, 1, -1 );
                            }
                            $product_name_with_variable = '';
                            $explode_many_varaition     = array();
                            foreach( $pro_name_variation as $pro_name_variation_key => $pro_name_variation_value ) {
                                $explode_many_varaition = explode ( ",", $pro_name_variation_value );
                                if( !empty( $explode_many_varaition ) ) {
                                    foreach( $explode_many_varaition as $explode_many_varaition_key => $explode_many_varaition_value ) {
                                        $product_name_with_variable = $product_name_with_variable .  html_entity_decode ( $explode_many_varaition_value ) . "<br>";
                                    }
                                } else {
                                    $product_name_with_variable = $product_name_with_variable .  html_entity_decode ( $explode_many_varaition_value ) . "<br>";
                                }
                            }
                            $prod_name = apply_filters( 'wcap_after_variable_product_name', $product_name_with_variable, $v->product_id );
                        }
                      }

                      if( isset($wcap_include_tax) && $wcap_include_tax == 'no' &&
                        isset($wcap_include_tax_setting) && $wcap_include_tax_setting == 'yes' ) {
                            $line_total    = $line_total + $v->line_total;
                            $line_subtotal_tax += $v->line_tax; // This is fix
                        }else if( isset($wcap_include_tax) && $wcap_include_tax == 'yes' &&
                        isset($wcap_include_tax_setting) && $wcap_include_tax_setting == 'yes' ){
                            // Item subtotal is calculated as product total including taxes
                            if( $v->line_tax != 0 && $v->line_tax > 0 ) {

                                $line_subtotal_tax_display += $v->line_tax;

                                /* After copon code price */
                                $after_item_subtotal = $item_subtotal + $v->line_total + $v->line_tax;
                                /*Calculate the product price*/
                                $item_subtotal = $item_subtotal + $v->line_subtotal + $v->line_subtotal_tax;
                                $line_total    = $line_total +   $v->line_subtotal + $v->line_subtotal_tax;
                            } else {
                                $item_subtotal = $item_subtotal + $v->line_total;
                                $line_total    = $line_total +  $v->line_total;
                                $line_subtotal_tax_display += $v->line_tax;
                            }
                        }else{
                        $line_total = $line_total + $v->line_total;
                      }
                    }
                }
                $line_total     =  $line_total ;

                if( isset($wcap_include_tax) && $wcap_include_tax == 'no' &&
                    isset($wcap_include_tax_setting) && $wcap_include_tax_setting == 'yes' ) {

                    $line_subtotal_tax =  $line_subtotal_tax ;
                }else if( isset($wcap_include_tax) && $wcap_include_tax == 'yes' ) {
                    $line_subtotal_tax = $line_subtotal_tax_display ;
                }

                $quantity_total = 0;
                if( isset( $cart_details ) && is_object( $cart_details ) && count( get_object_vars( $cart_details ) ) > 0 ) {
                  foreach( $cart_details as $k => $v ) {
                    $quantity_total = $quantity_total + $v->quantity;
                  }
                }
                if( 1 == $quantity_total ) {
                   $item_disp = __( "item", "woocommerce-ac" );
                } else {
                   $item_disp = __( "items", "woocommerce-ac" );
                }
               $coupon_details          = get_user_meta( $value->user_id, '_woocommerce_ac_coupon', true );
               $coupon_detail_post_meta = get_post_meta( $value->id, '_woocommerce_ac_coupon');

                if( $value->unsubscribe_link == 1 ) {
                    $ac_status = __( "Unsubscribed", "woocommerce-ac" );
                }elseif( $value->cart_ignored == 0 && $value->recovered_cart == 0 ) {
                   $ac_status = __( "Abandoned", "woocommerce-ac" );
                } elseif( $value->cart_ignored == 1 && $value->recovered_cart == 0 ) {
                   $ac_status = __( "Abandoned but new cart created after this", "woocommerce-ac" );
                } elseif ( $value->cart_ignored == 2 && $value->recovered_cart == 0 ) {
		          $ac_status = __( "Abandoned - Order Unpaid", "woocommerce-ac" );
                } elseif ( $value->cart_ignored == 3 && $value->recovered_cart == 0 ) {
                    $ac_status = __( "Abandoned - Order Received", "woocommerce-ac" );
                } elseif( $value->cart_ignored == 1 && $value->recovered_cart > 0 ) {
                    $ac_status = __( "Recovered", "woocommerce-ac" );
                } else {
                  $ac_status = "";
                }

                $ip_address = '';
                if ( isset( $value->ip_address ) ) {
                    $ip_address = $value->ip_address;
                }

               $coupon_code_used = $coupon_code_message = "";
               if ( $compare_time > $cut_off_time && $ac_status != "" ) {
                   $return_abandoned_orders[$i] = new stdClass();
                   if( $quantity_total > 0 ) {
                      $user_role = '';
                      if( isset( $user_id ) ) {
                          if ( $user_id == 0 ) {
                             $user_role = 'Guest';
                          } elseif ( $user_id >= 63000000 ) {
                              $user_role = 'Guest';
                          } else {
                              $user_role = Wcap_Common::wcap_get_user_role ( $user_id );
                          }
                      }
                      $abandoned_order_id                           = $abandoned_order_id;
                      $customer_information                         = $user_first_name . " ".$user_last_name;
                      $return_abandoned_orders[ $i ]->id            = $abandoned_order_id;
                      $return_abandoned_orders[ $i ]->email         = $user_email;
                      if( $phone == '' ) {
                          $return_abandoned_orders[ $i ]->customer      = $customer_information . "<br>" . $user_role;
                      } else {
                          $return_abandoned_orders[ $i ]->customer      = $customer_information . "<br>" . $phone . "<br>" . $user_role;
                      }
                      $return_abandoned_orders[ $i ]->order_total   = $line_total;
                      $return_abandoned_orders[ $i ]->quantity      = $quantity_total . " " . $item_disp;
                      $return_abandoned_orders[ $i ]->date          = $order_date;
                      $return_abandoned_orders[ $i ]->status        = $ac_status;
                      $return_abandoned_orders[ $i ]->user_id       = $user_id;
                      $return_abandoned_orders[ $i ]->product_names = $prod_name;
                      $return_abandoned_orders[ $i ]->tax_type      = $wcap_include_tax == 'yes' ? 'inc' : 'exc';
                      
                      $return_abandoned_orders[ $i ]->tax_setting   = $wcap_include_tax_setting;
                      $return_abandoned_orders[ $i ]->tax_amount    = $line_subtotal_tax;
                      $return_abandoned_orders[ $i ]->user_ip_address    = $ip_address;
                      if ( $currency !== '' ) {
                          $return_abandoned_orders[ $i ]->currency  = $currency;
                      }

                      // Recovered Order ID
                        if( $value->recovered_cart > 0 ) {
                            $return_abandoned_orders[$i]->recovered_id = $value->recovered_cart;
                        }
                      if( $display_tracked_coupons == 'on' ) {
                          if( $coupon_detail_post_meta != '' ) {
                              foreach( $coupon_detail_post_meta as $key => $value ) {
                                  if( $coupon_detail_post_meta[$key]['coupon_code'] != '' ) {
                                      $coupon_code_used .= $coupon_detail_post_meta[$key]['coupon_code'] . "</br>";
                                  }
                              }
                              $return_abandoned_orders[ $i ]->coupon_code_used = $coupon_code_used;
                          }
                          if ( $coupon_detail_post_meta != '' && $coupon_code_used !== '' ) {
                              foreach( $coupon_detail_post_meta as $key => $value ) {
                                  $coupon_code_message .= $coupon_detail_post_meta[$key]['coupon_message'] . "</br>";
                              }
                              $return_abandoned_orders[ $i ]->coupon_code_status = $coupon_code_message;
                          }
                       }

                   } else {
                    $abandoned_order_id                    = $abandoned_order_id;
                    $return_abandoned_orders[ $i ]->id     = $abandoned_order_id;
                    $return_abandoned_orders[ $i ]->date   = $order_date;
                    $return_abandoned_orders[ $i ]->status = $ac_status;
                    //$return_abandoned_orders[ $i ]->ip_address    = $ip_address;
                    }
                    $i++;
                }
            }
            // sort for order date
            if( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'date' ) {
                if( isset( $_GET['order'] ) && $_GET['order'] == 'asc' ) {
                  usort( $return_abandoned_orders, array( __CLASS__ , "wcap_class_order_date_asc" ) );
                } else {
                  usort( $return_abandoned_orders, array( __CLASS__ , "wcap_class_order_date_dsc" ) );
                }
            } else if( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'status' ) { // sort for customer name
                if ( isset( $_GET['order'] ) && $_GET['order'] == 'asc' ) {
                  usort( $return_abandoned_orders, array( __CLASS__ , "wcap_class_status_asc" ) );
                } else {
                  usort( $return_abandoned_orders, array( __CLASS__ , "wcap_class_status_dsc" ) );
                }
            }
            return $return_abandoned_orders;
        }

        /**
         * It will prepare the data for the csv.
         * @param array $report All abandoned cart information
         * @return string $csv Prepared csv format data
         */
        public static function wcap_generate_csv( $report ) {

            // tracking coupons
            $display_tracked_coupons =  get_option( 'ac_track_coupons' );
            // Column Names
            if ( $display_tracked_coupons == 'on' ) {
                $csv   = 'ID, Email Address, Customer, Products, Order Total, Quantity, Abandoned Date, Coupon Code Used, Coupon Status, Email Captured By, Status of cart, Order ID (Recovered), IP Address';
                $csv  .= "\n";
            } else {
                $csv  = 'ID, Email Address, Customer, Products, Order Total, Quantity, Abandoned Date, Email Captured By, Status of cart, Order ID (Recovered), IP Address';
                $csv .= "\n";
            }
            
            foreach ( $report as $key => $value ) {
                $woocommerce_currency = isset( $value->currency ) ? $value->currency : get_woocommerce_currency();

                $currencey       = apply_filters( 'acfac_get_cart_currency', $woocommerce_currency, $value->id );
                $currency_symbol = get_woocommerce_currency_symbol( $currencey );

                // Order ID
                $order_id = '';
                if ( isset( $value->id ) ){
                    $order_id = $value->id;
                } 

                $email_id = '';
                if ( isset( $value->email ) ) {
                    $email_id = $value->email;
                }

                $name = '';
                if( isset( $value->customer ) ) {
                    $name = $value->customer;
                    $name = str_replace ( '<br>', "\n", $name );
                }

                $product_name = '';
                if ( isset( $value->product_names ) ) {
                    $product_name = strip_tags ( $value->product_names );
                }

                $product_name   = str_replace( '</br>', "\n", $product_name );

                $order_total = '';
                if ( isset( $value->order_total ) ) {
                    $order_total = $value->order_total;
                }
                
                $final_order_total = strip_tags( html_entity_decode( wc_price( $order_total, array( 'currency' => $woocommerce_currency ) ) ) );

                if ( isset( $value->tax_setting ) && 'yes' == $value->tax_setting &&

                    isset( $value->tax_type ) && 'inc' == $value->tax_type ) {

                    //$line_subtotal_tax = $currency_symbol . $value->tax_amount;

                    $final_subtotal_tax = strip_tags( html_entity_decode( wc_price( $value->tax_amount, array( 'currency' => $woocommerce_currency ) ) ) );    

                    $final_order_total       = $final_order_total . ' (includes Tax: '. $final_subtotal_tax . ')';
                } else if ( isset( $value->tax_setting ) && 'yes' == $value->tax_setting &&
                            isset( $value->tax_type ) && 'exc' == $value->tax_type ) {
                    //$line_subtotal_tax = $currency_symbol . $value->tax_amount;

                    $final_subtotal_tax = strip_tags( html_entity_decode( wc_price( $value->tax_amount, array( 'currency' => $woocommerce_currency ) ) ) );

                    $final_order_total       = $final_order_total . "\n". "Tax: ". $final_subtotal_tax ;
                }
                
                $quantity = '';
                if ( isset( $value->quantity ) ) {
                    $quantity = $value->quantity;
                }

                $abandoned_date = '';
                if ( isset( $value->date ) ) {
                    $abandoned_date = $value->date;
                }

                $abandoned_status = '';
                if ( isset( $value->status ) ) {
                    $abandoned_status = $value->status;
                }
                
                $wcap_email_captured_by = '';
                if( isset( $value->user_id ) ) {
                  if ( $value->user_id > 0 ){
                    $wcap_cart_popup_data = get_post_meta ( $value->id, 'wcap_atc_report' );
                    //if ( ) $wcap_cart_popup_data[0]['wcap_atc_action']
                    if ( count( $wcap_cart_popup_data ) > 0 ) {
                      $wcap_user_selected_action = $wcap_cart_popup_data[0]['wcap_atc_action'];
                      if ( 'yes' == $wcap_user_selected_action ){
                        $wcap_email_captured_by = __( 'Cart Popup', 'woocommerce-ac' );
                      }else if ( 'no' == $wcap_user_selected_action ){
                        $wcap_email_captured_by = __( 'Checkout page', 'woocommerce-ac' );
                      }
                    }else{
                      if ( $value->user_id >= 63000000 ){
                        $wcap_email_captured_by = __( 'Checkout page', 'woocommerce-ac' );
                      }else if ( $value->user_id > 0 &&  $value->user_id < 63000000 ){
                        $wcap_email_captured_by = __( 'User Profile', 'woocommerce-ac' );
                      }
                      
                    }
                  }
                }
                
                $user_ip_address = '';
                if ( isset( $value->user_ip_address ) ) {
                    $user_ip_address = $value->user_ip_address;
                }

                $recovered_order_id = isset( $value->recovered_id ) && $value->recovered_id > 0 ? $value->recovered_id : '';

                if ( $display_tracked_coupons == 'on' ) {
                    if ( isset( $value->coupon_code_used ) ) {
                        $coupon_used = $value->coupon_code_used;
                    } else {
                        $coupon_used = '';
                    }
                    $coupon_used   = str_replace( '</br>', "\n", $coupon_used );
                    $coupon_status = '';
                    if ( isset( $value->coupon_code_status ) && '' != $value->coupon_code_status ) {
                        $coupon_status = $value->coupon_code_status;
                        $coupon_status = str_replace ('</br>', "\n", $coupon_status );
                    }
                    /**
                     * When any string which contain comma in the csv we need to escape that. We need to wrap that sting in double quotes.
                     * So it will display string with comma.
                     */
                    // Create the data row
                    $csv .= $order_id . ',' . $email_id . ','. "\" $name \"" . ',' . "\"  $product_name \"" . ',' .  "\" $final_order_total \"" . ',' . $quantity . ',' . "\" $abandoned_date\"". ',' . "\" $coupon_used \"" . ','. "\" $coupon_status \"" . ',' . "\" $wcap_email_captured_by \"" . ',' . $abandoned_status . ',' . $recovered_order_id . ',' . $user_ip_address;
                    $csv             .= "\n";
                } else {
                    // Create the data row
                    $csv .= $order_id . ',' . $email_id . ','. "\" $name \"" . ',' . "\"  $product_name \"" . ',' . "\" $final_order_total \"". ',' . $quantity . ',' . "\" $abandoned_date\"". ',' . "\" $wcap_email_captured_by\"". ',' . $abandoned_status . ',' . $recovered_order_id . ',' . $user_ip_address;
                    $csv             .= "\n";
                }
            }
            return $csv;
        }

        /**
         * It will prepare the data for the print.
         * @param array $report All abandoned cart information
         * @return string $print_data Prepared print format data
         */
        public static function wcap_generate_print_data( $report ) {
            // tracking coupons
            $display_tracked_coupons = get_option( 'ac_track_coupons' );
            $print_data_columns  = "
                                    <tr>
                                        <th style='border:1px solid black;padding:5px;'>".__( 'ID', 'woocommerce-ac' )."</th>
                                        <th style='border:1px solid black;padding:5px;'>".__( 'Email Address', 'woocommerce-ac' )."</th>
                                        <th style='border:1px solid black;padding:5px;'>".__( 'Customer Details', 'woocommerce-ac' )."</th>
                                        <th style='border:1px solid black;padding:5px;'>".__( 'Products', 'woocommerce-ac' )."</th>
                                        <th style='border:1px solid black;padding:5px;'>".__( 'Order Total', 'woocommerce-ac' )."</th>
                                        <th style='border:1px solid black;padding:5px;'>".__( 'Quantity', 'woocommerce-ac' )."</th>
                                        <th style='border:1px solid black;padding:5px;'>".__( 'Abandoned Date', 'woocommerce-ac' )."</th>";

            if ( $display_tracked_coupons == 'on' ) {
                
                $print_data_columns .= "<th style='border:1px solid black;padding:5px;'>".__( 'Coupon Code Used', 'woocommerce-ac' )."</th>
                                        <th style='border:1px solid black;padding:5px;'>".__( 'Coupon Status', 'woocommerce-ac' )."</th>";
            }

            $print_data_columns .= "
                                        <th style='border:1px solid black;padding:5px;'>".__( 'Email Captured By', 'woocommerce-ac' )."</th>
                                        <th style='border:1px solid black;padding:5px;'>".__( 'Status of cart', 'woocommerce-ac' )."</th>
                                        <th style='border:1px solid black;padding:5px;'>".__( 'Order ID (Recovered)', 'woocommerce-ac' )."</th>
                                        <th style='border:1px solid black;padding:5px;'>".__( 'IP Address', 'woocommerce-ac' )."</th>
                                    </tr>";
            
            
            $print_data_row_data = '';

            foreach ( $report as $key => $value ) {

                $woocommerce_currency = isset( $value->currency ) ? $value->currency : get_woocommerce_currency();

                $wcap_currency  = apply_filters( 'acfac_get_cart_currency', $woocommerce_currency, $value->id );
                $currency       = get_woocommerce_currency_symbol( $wcap_currency );

                $abandoned_id = '';
                if ( isset( $value->id ) ) {
                    $abandoned_id = $value->id;
                }

                $customer_email = '';
                if ( isset( $value->email ) ) {
                    $customer_email = $value->email;
                }
                
                $customer_name = '';
                if ( isset( $value->customer ) ) {
                    $customer_name = $value->customer;
                }
                
                $product_names = '';
                if ( isset( $value->product_names ) ) {
                    $product_names = $value->product_names;
                }
                
                if ( isset( $value->order_total ) ) {
                    $order_total = $value->order_total;
                } else {
                    $order_total = '';
                    $currency    = '';
                }

                 $final_order_total = strip_tags( html_entity_decode( wc_price( $order_total, array( 'currency' => $woocommerce_currency ) ) ) );

                if (  isset( $value->tax_setting ) && 'yes' == $value->tax_setting &&

                    isset( $value->tax_type ) && 'inc' == $value->tax_type ) {

                    //$line_subtotal_tax = $currency . $value->tax_amount;

                 $final_subtotal_tax = strip_tags( html_entity_decode( wc_price( $value->tax_amount, array( 'currency' => $woocommerce_currency ) ) ) );
                    $final_order_total       = $final_order_total . ' (includes Tax: '. $final_subtotal_tax . ')';
                } else if (  isset( $value->tax_setting ) && 'yes' == $value->tax_setting &&

                    isset( $value->tax_type ) && 'exc' == $value->tax_type ) {

                    //$line_subtotal_tax = $currency . $value->tax_amount;
                    $final_subtotal_tax = strip_tags( html_entity_decode( wc_price( $value->tax_amount, array( 'currency' => $woocommerce_currency ) ) ) );
                    $final_order_total       = $final_order_total . "<br> Tax: ". $final_subtotal_tax ;
                }

                $order_quantity = '';
                if ( isset( $value->quantity ) ) {
                    $order_quantity = $value->quantity;
                }

                $coupon_code_used = '';
                if ( isset( $value->coupon_code_used ) ) {
                    $coupon_code_used = $value->coupon_code_used;
                }

                $coupon_code_status = '';
                if ( isset( $value->coupon_code_status ) ) {
                    $coupon_code_status = $value->coupon_code_status;
                }

                $abandoned_date = '';
                if ( isset( $value->date ) ) {
                    $abandoned_date = $value->date;
                }

                $abandoned_status = '';
                if ( isset( $value->status ) ) {
                    $abandoned_status = $value->status;
                }
                $user_ip_address = '';
                if ( isset( $value->user_ip_address ) ) {
                    $user_ip_address = $value->user_ip_address;
                }

                $wcap_email_captured_by = '';
                if( isset( $value->user_id ) ) {
                  if ( $value->user_id > 0 ){
                    $wcap_cart_popup_data = get_post_meta ( $value->id, 'wcap_atc_report' );
                    //if ( ) $wcap_cart_popup_data[0]['wcap_atc_action']
                    if ( count( $wcap_cart_popup_data ) > 0 ){
                      $wcap_user_selected_action = $wcap_cart_popup_data[0]['wcap_atc_action'];
                      if ( 'yes' == $wcap_user_selected_action ){
                        $wcap_email_captured_by = __( 'Cart Popup', 'woocommerce-ac' );
                      }else if ( 'no' == $wcap_user_selected_action ){
                        $wcap_email_captured_by = __( 'Checkout page', 'woocommerce-ac' );
                      }
                    }else{
                      if ( $value->user_id >= 63000000 ){
                        $wcap_email_captured_by = __( 'Checkout page', 'woocommerce-ac' );
                      }else if ( $value->user_id > 0 &&  $value->user_id < 63000000 ){
                        $wcap_email_captured_by = __( 'User Profile', 'woocommerce-ac' );
                      }
                      
                    }
                  }
                }

                $recovered_order_id = ( isset( $value->recovered_id ) && $value->recovered_id > 0 ) ? $value->recovered_id : ''; 

                if ( $display_tracked_coupons == 'on' ) {
                    $print_data_row_data .= "<tr>
                                        <td style='border:1px solid black;padding:5px;'>".$abandoned_id."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$customer_email."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$customer_name."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$product_names."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$final_order_total."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$order_quantity."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$abandoned_date."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$coupon_code_used."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$coupon_code_status."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$wcap_email_captured_by."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$abandoned_status."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$recovered_order_id."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$user_ip_address."</td>
                                        </tr>";
                } else {
                    $print_data_row_data .= "<tr>
                                        <td style='border:1px solid black;padding:5px;'>".$abandoned_id."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$customer_email."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$customer_name."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$product_names."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$final_order_total."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$order_quantity."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$abandoned_date."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$wcap_email_captured_by."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$abandoned_status."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$recovered_order_id."</td>
                                        <td style='border:1px solid black;padding:5px;'>".$user_ip_address."</td>
                                        </tr>";
                }
            }
            $print_data_columns  = $print_data_columns;
            $print_data_row_data = $print_data_row_data;
            $print_data          = "<table style='border:1px solid black;border-collapse:collapse;'>" . $print_data_columns . $print_data_row_data . "</table>";
            return $print_data;
        }
    }
}