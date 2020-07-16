<?php
/**
 * It will fetch the abandoned cart data & generate and populate data in the modal.
 * @author  Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Popup-Modal/Cart-Detail
 * @since 5.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if ( !class_exists( 'Wcap_Abandoned_Cart_Details' ) ) {

    /**
     * It will fetch the abandoned cart data & generate and populate data in the modal.
     * @since 4.8
     */
    class Wcap_Abandoned_Cart_Details {

        /**
         * This function will fetch all the data and generate the HTML required for the cart details popup modal.
         * It will be displayed on the Abandoned carts & Send emails tab
         * @globals mixed $wpdb
         * @globals mixed $woocommerce
         * @param string $wcap_cart_id The abandoned cart id
         * @param string $wcap_email_address Email address if the customer
         * @param string $wcap_customer_details Details of the customer
         * @param int | string $wcap_cart_total Total of the cart
         * @param date $wcap_abandoned_date Date of the cart abandoned
         * @param string $wcap_abandoned_status Status of the cart
         * @param string $wcap_current_page Name of current page
         * @since 4.8
         */
        public static function wcap_get_cart_detail_view ( $wcap_cart_id = '', $wcap_email_address, $wcap_customer_details, $wcap_cart_total, $wcap_abandoned_date, $wcap_abandoned_status, $wcap_current_page ) {
            global $wpdb, $woocommerce;

            $wcap_selected_abandoned_cart = array();
            $wcap_show_customer_detail = '';
            $wcap_cart_content_var = '';
            $wcap_cart_user_id = '';
            $user_email        = '';
            $wcap_cart_email_sent  = $wcap_cart_email_clicked = $wcap_cart_email_open = '';
            $wcap_add_customer_details = '';
            
            $wc_shipping_charges = '';
            $shipping_charges_value = '';
            $wcap_Shipping_charges_text = '';
            $wc_Shipping_charges_text = '';
            $wc_shipping_charges_formatted = '';

            $wcap_show_customer_detail = '<br><a href="" id "wcap_customer_detail_modal" class ="wcap_customer_detail_modal"> Show Details </a>';
            $wcap_get_abandoned_cart_query  = "SELECT * FROM ". WCAP_ABANDONED_CART_HISTORY_TABLE . " WHERE `id` = %d";
            $wcap_get_abandoned_cart_result = $wpdb->get_results( $wpdb->prepare( $wcap_get_abandoned_cart_query, $wcap_cart_id ) );


            $user_role = '';
            if ( isset( $wcap_get_abandoned_cart_result[0] ) ) {
                if ( $wcap_get_abandoned_cart_result[0]->user_id == 0 ) {
                    $user_role = 'Guest';
                }
                elseif ( $wcap_get_abandoned_cart_result[0]->user_id >= 63000000 ) {
                    $user_role = 'Guest';
                }else{
                    $user_role = Wcap_Common::wcap_get_user_role ( $wcap_get_abandoned_cart_result[0]->user_id );
                }
            }
            
            $recovered_order = isset( $wcap_get_abandoned_cart_result[0]->recovered_cart ) && $wcap_get_abandoned_cart_result[0]->recovered_cart > 0 ? $wcap_get_abandoned_cart_result[0]->recovered_cart : 0;
            if ( 'send_email' == $wcap_current_page ) {

                if( $wcap_get_abandoned_cart_result[0]->unsubscribe_link == 1 ) {
                    $ac_status = __( "Unsubscribed", "woocommerce-ac" );
                    $wcap_abandoned_status = "<span id ='wcap_unsubscribe_link_modal' class = 'unsubscribe_link'  >" . $ac_status . "</span>";
                }else if( $wcap_get_abandoned_cart_result[0]->cart_ignored == 0 && $wcap_get_abandoned_cart_result[0]->recovered_cart == 0 ) {
                    $ac_status = __( "Abandoned", "woocommerce-ac" );
                    $wcap_abandoned_status = "<span id='wcap_status_modal_abandoned' class='wcap_status_modal_abandoned'  >" . $ac_status . "</span>";
                } elseif( $wcap_get_abandoned_cart_result[0]->cart_ignored == 1 && $wcap_get_abandoned_cart_result[0]->recovered_cart == 0 ) {
                    $ac_status = __( "Abandoned but new cart created after this", "woocommerce-ac" );
                    $wcap_abandoned_status = "<span id='wcap_status_modal_abandoned_new' class='wcap_status_modal_abandoned_new'  >" . $ac_status . "</span>";
                }


                $cart_update_time = $wcap_get_abandoned_cart_result[0]->abandoned_cart_time;

                if( $cart_update_time != "" && $cart_update_time != 0 ) {
                    $date_format = date_i18n( get_option( 'date_format' ), $cart_update_time );
                    $time_format = date_i18n( get_option( 'time_format' ), $cart_update_time );
                    $wcap_abandoned_date  = $date_format . ' ' . $time_format;

                }
            } else {
                $ac_status = __( $wcap_abandoned_status, "woocommerce-ac" );
                switch( $wcap_abandoned_status ) {
                    case 'Abandoned':
                        $wcap_abandoned_status = "<span id='wcap_status_modal_abandoned_new' class='wcap_status_modal_abandoned_new'  >" . $ac_status . "</span>";
                        break;
                    case 'Recovered':
                        $wcap_abandoned_status = "<span id='wcap_status_modal_abandoned' class='wcap_status_modal_abandoned'  >" . $ac_status . "</span>";
                        break;
                    case 'Unsubscribed':
                    $wcap_abandoned_status = "<span id ='wcap_unsubscribe_link_modal' class = 'unsubscribe_link'  >" . $ac_status . "</span>";
                        break;
                    case 'Abandoned - Order Unpaid':
                        $wcap_abandoned_status = "<span id ='wcap_status_unpaid_order' class = 'wcap_status_unpaid_order'  >" . $ac_status . "</span>";
                        break;
                    case 'Abandoned - Order Received':
                        $wcap_abandoned_status = "<span id ='wcap_status_received_order' class = 'wcap_status_received_order'  >" . $ac_status . "</span>";
                        break;
                    default:
                        $wcap_abandoned_status = "<span id='wcap_status_modal_abandoned_new' class='wcap_status_modal_abandoned_new'  >" . $ac_status . "</span>";
                        break;

                }
            }
            $wcap_get_abandoned_sent_query = "SELECT wcet.`template_name`, wsht.`sent_time`, wsht.`id`, wsht.`sent_email_id` FROM ". WCAP_EMAIL_SENT_HISTORY_TABLE . " as wsht LEFT JOIN ". WCAP_EMAIL_TEMPLATE_TABLE . " AS wcet ON wsht.template_id = wcet.id WHERE `abandoned_order_id` = %d";
            $wcap_get_abandoned_sent_result = $wpdb->get_results( $wpdb->prepare( $wcap_get_abandoned_sent_query, $wcap_cart_id ) );

            $shipping_charges        = 0;
            $currency_symbol         = get_woocommerce_currency_symbol();
            $biiling_field_display   = $email_field_display = $phone_field_display = $shipping_field_display = "block";
            $user_ip_address         = '';
            $user_ip_address_display = 'none';
            if ( isset( $wcap_get_abandoned_cart_result[0]->ip_address ) && '' != $wcap_get_abandoned_cart_result[0]->ip_address ) {
                $user_ip_address = $wcap_get_abandoned_cart_result[0]->ip_address;
                $user_ip_address_display = 'block';
            }

            $user_billing_company    = '';
            $user_billing_address_1  = '';
            $user_billing_address_2  = '';
            $user_billing_city       = '';
            $user_billing_postcode   = '';
            $user_billing_state      = '';
            $user_billing_country    = '';
            $user_shipping_company   = '';
            $user_shipping_address_1 = '';
            $user_shipping_address_2 = '';
            $user_shipping_city      = '';
            $user_shipping_postcode  = '';
            $user_shipping_state     = '';
            $user_shipping_country   = '';
            $biiling_field_display   = 'none';
            $shipping_field_display  = 'none';

            if ( isset( $wcap_get_abandoned_cart_result[0] ) && "GUEST" == $wcap_get_abandoned_cart_result[0]->user_type && "0" != $wcap_get_abandoned_cart_result[0]->user_id ) {
                $query_guest            = "SELECT * FROM `" . WCAP_GUEST_CART_HISTORY_TABLE . "` WHERE id = %d";
                $results_guest          = $wpdb->get_results( $wpdb->prepare( $query_guest, $wcap_get_abandoned_cart_result[0]->user_id ) );

                $user_first_name        =  ( isset( $results_guest[0]->billing_first_name ) && "" != $results_guest[0]->billing_first_name ) ? $results_guest[0]->billing_first_name : '';
                $user_last_name         = ( isset( $results_guest[0]->billing_last_name ) && "" != $results_guest[0]->billing_last_name ) ? $results_guest[0]->billing_last_name : '' ;
                $user_billing_postcode  = ( isset( $results_guest[0]->billing_zipcode ) && "" != $results_guest[0]->billing_zipcode ) ? $results_guest[0]->billing_zipcode : '' ;
                $user_shipping_postcode = ( isset( $results_guest[0]->shipping_zipcode ) && "" != $results_guest[0]->shipping_zipcode ) ? $results_guest[0]->shipping_zipcode : '';
                $shipping_charges       = ( isset( $results_guest[0]->shipping_charges ) && "" != $results_guest[0]->shipping_charges ) ? $results_guest[0]->shipping_charges : '' ;
                $user_billing_phone     = ( isset( $results_guest[0]->phone ) && "" != $results_guest[0]->phone ) ? $results_guest[0]->phone : '' ;

                if ( isset($user_billing_phone) && $user_billing_phone == '' ) {
                    $phone_field_display = "none";
                }
                
                $user_email = '';
                if ( isset( $results_guest[0]->email_id ) ) {
                    $user_email = $results_guest[0]->email_id;
                    if ( 'send_email' ==  $wcap_current_page ){
                        $wcap_email_address = $results_guest[0]->email_id;
    
    
                        $customer_information  = $user_first_name . " ".$user_last_name;
    
                        if( $user_billing_phone == '' ) {
                            $wcap_customer_details = $customer_information . "<br>" .  $user_role;
                        }else{
                            $wcap_customer_details = $customer_information . "<br>" . $user_billing_phone . "<br>" . $user_role;
                        }
                    }
                }
            } else if ( isset( $wcap_get_abandoned_cart_result[0] ) && $wcap_get_abandoned_cart_result[0]->user_type == "GUEST" && $wcap_get_abandoned_cart_result[0]->user_id == "0" ) {
                $user_email             = '';
                $user_first_name        = "Visitor";
                $user_last_name         = "";
                $user_billing_postcode  = '';
                $user_shipping_postcode = '';
                $shipping_charges       = '';
                $user_billing_phone     = '';
                $user_billing_company   = $user_billing_address_1 = $user_billing_address_2 = $user_billing_city = $user_billing_state = $user_billing_country  = "";
                $user_shipping_first_name =  $user_shipping_last_name = $user_shipping_company_temp = $user_shipping_company  = $user_shipping_address_1 = $user_shipping_address_2 = $user_shipping_city = $user_shipping_state = $user_shipping_country = "";
                $biiling_field_display  = $email_field_display = $phone_field_display = $shipping_field_display = "none";
            } else {

                $user_email      = '';
                if ( isset( $wcap_get_abandoned_cart_result[0] ) ){
                    $user_email_biiling      = get_user_meta( $wcap_get_abandoned_cart_result[0]->user_id, 'billing_email', true );
                }

                if ( isset( $user_email_biiling ) ) {
                    if( '' == $user_email_biiling ) {
                        if (isset( $wcap_get_abandoned_cart_result[0] )){
                            $current_user_data   = get_userdata( $wcap_get_abandoned_cart_result[0]->user_id );
                            $user_email  = $current_user_data->user_email;
                        }
                    } else {
                        $user_email  = $user_email_biiling;
                    }
                }
                if ( 'send_email' ==  $wcap_current_page ) {

                    $wcap_email_address   = $user_email    ;

                    $user_first_name_temp ='';
                    if ( isset( $wcap_get_abandoned_cart_result[0] )){
                        $user_first_name_temp = get_user_meta( $wcap_get_abandoned_cart_result[0]->user_id, 'billing_first_name', true );
                    }

                    if( isset( $user_first_name_temp ) && "" == $user_first_name_temp ) {
                        $user_data  = get_userdata( $wcap_get_abandoned_cart_result[0]->user_id );
                        $user_first_name = $user_data->first_name;
                    } else {
                        $user_first_name = $user_first_name_temp;
                    }

                    $user_last_name_temp = get_user_meta( $wcap_get_abandoned_cart_result[0]->user_id, 'billing_last_name', true );
                    if( isset( $user_last_name_temp ) && "" == $user_last_name_temp ) {
                        $user_data  = get_userdata( $wcap_get_abandoned_cart_result[0]->user_id );
                        $user_last_name = $user_data->last_name;
                    } else {
                        $user_last_name = $user_last_name_temp;
                    }

                    $user_phone_number = array();
                    if ( isset( $wcap_get_abandoned_cart_result[0] )){
                        $user_phone_number = get_user_meta( $wcap_get_abandoned_cart_result[0]->user_id, 'billing_phone' );
                    }
                    if( isset( $user_phone_number[0] ) ) {
                        $phone = $user_phone_number[0];
                    } else {
                        $phone = "";
                    }
                    $customer_information  = $user_first_name . " ".$user_last_name;

                    if( $phone == '' ) {
                        $wcap_customer_details = $customer_information . "<br>" .  $user_role;
                    }else{
                        $wcap_customer_details = $customer_information . "<br>" . $phone . "<br>" . $user_role;
                    }
                }
                if ( isset( $wcap_get_abandoned_cart_result[0] ) ){

                    $user_billing_details = self::wcap_get_billing_details( $wcap_get_abandoned_cart_result[0]->user_id );
                    
                    $user_billing_company = $user_billing_details[ 'billing_company' ];
                    $user_billing_address_1 = $user_billing_details[ 'billing_address_1' ];
                    $user_billing_address_2 = $user_billing_details[ 'billing_address_2' ];
                    $user_billing_city = $user_billing_details[ 'billing_city' ];
                    $user_billing_postcode = $user_billing_details[ 'billing_postcode' ] ;
                    $user_billing_country = $user_billing_details[ 'billing_country' ];
                    $user_billing_state = $user_billing_details[ 'billing_state' ];
                    
                    $user_billing_phone_temp = get_user_meta( $wcap_get_abandoned_cart_result[0]->user_id, 'billing_phone' );
                    $user_billing_phone = "";
                    if ( isset( $user_billing_phone_temp[0] ) ) {
                        $user_billing_phone = $user_billing_phone_temp[0];
                    }
                    $user_shipping_first_name   = get_user_meta( $wcap_get_abandoned_cart_result[0]->user_id, 'shipping_first_name' );
                    $user_shipping_last_name    = get_user_meta( $wcap_get_abandoned_cart_result[0]->user_id, 'shipping_last_name' );
                    $user_shipping_company_temp = get_user_meta( $wcap_get_abandoned_cart_result[0]->user_id, 'shipping_company' );
                    $user_shipping_company      = "";
                    if ( isset( $user_shipping_company_temp[0] ) ) {
                        $user_shipping_company = $user_shipping_company_temp[0];
                    }
                    $user_shipping_address_1_temp = get_user_meta( $wcap_get_abandoned_cart_result[0]->user_id, 'shipping_address_1' );
                    $user_shipping_address_1 = "";
                    if ( isset( $user_shipping_address_1_temp[0] ) ) {
                        $user_shipping_address_1 = $user_shipping_address_1_temp[0];
                    }
                    $user_shipping_address_2_temp = get_user_meta( $wcap_get_abandoned_cart_result[0]->user_id, 'shipping_address_2' );
                    $user_shipping_address_2 = "";
                    if ( isset( $user_shipping_address_2_temp[0] ) ) {
                        $user_shipping_address_2 = $user_shipping_address_2_temp[0];
                    }
                    $user_shipping_city_temp = get_user_meta( $wcap_get_abandoned_cart_result[0]->user_id, 'shipping_city' );
                    $user_shipping_city = "";
                    if ( isset( $user_shipping_city_temp[0] ) ) {
                        $user_shipping_city = $user_shipping_city_temp[0];
                    }
                    $user_shipping_postcode_temp = get_user_meta( $wcap_get_abandoned_cart_result[0]->user_id, 'shipping_postcode' );
                    $user_shipping_postcode = "";
                    if ( isset( $user_shipping_postcode_temp[0] ) ) {
                        $user_shipping_postcode = $user_shipping_postcode_temp[0];
                    }
                    $user_shipping_country_temp = get_user_meta( $wcap_get_abandoned_cart_result[0]->user_id, 'shipping_country' );
                    $user_shipping_country = "";
                    if ( isset( $user_shipping_country_temp[0] ) ) {
                        $user_shipping_country = $user_shipping_country_temp[0];
                        if ( isset( $woocommerce->countries->countries[ $user_shipping_country ] ) ) {
                            $user_shipping_country = $woocommerce->countries->countries[ $user_shipping_country ];    
                        }else {
                            $user_shipping_country = "";
                        }
                        
                    }
                    $user_shipping_state_temp = get_user_meta( $wcap_get_abandoned_cart_result[0]->user_id, 'shipping_state' );
                    $user_shipping_state = "";
                    if ( isset( $user_shipping_state_temp[0] ) ) {
                        $user_shipping_state = $user_shipping_state_temp[0];
                        if ( isset( $woocommerce->countries->states[ $user_shipping_country_temp[0] ][ $user_shipping_state ] ) ) {
                            # code...
                            $user_shipping_state = $woocommerce->countries->states[ $user_shipping_country_temp[0] ][ $user_shipping_state ];
                        }    
                    }
                    //get shipping charges
                    $cart_info = json_decode( stripslashes( $wcap_get_abandoned_cart_result[0]->abandoned_cart_info ) );
                    $wc_shipping_charges = isset( $cart_info->shipping_charges ) ? $cart_info->shipping_charges : 0;
                }
            }

            

            if ( '' == $user_billing_company && '' == $user_billing_address_1 && '' == $user_billing_address_2 &&
                 '' ==  $user_billing_city && '' == $user_billing_postcode && '' == $user_billing_state && '' == $user_billing_country ){

                $biiling_field_display = 'none';
            }

            $wcap_billing_address_text   = __( 'Billing Address:' , 'woocommerce-ac' );
            $wcap_create_billing_address = "<br>".$user_billing_company   . "</br>" .
                                                  $user_billing_address_1 . "</br>" .
                                                  $user_billing_address_2 . "</br>" .
                                                  $user_billing_city      . "</br>" .
                                                  $user_billing_postcode  ;

           $wcap_Shipping_address_text   = __( 'Shipping Address:' , 'woocommerce-ac' );

           if (  $user_shipping_company      == '' &&
                 $user_shipping_address_1   == '' &&
                 $user_shipping_address_2   == '' &&
                 $user_shipping_city        == '' &&
                 $user_shipping_postcode    == '' &&
                 $user_shipping_state       == '' &&
                 $user_shipping_country     == '' ) {

                $wcap_create_shipping_address = "Shipping Address same as Billing Address";
             } else {
              $wcap_create_shipping_address = "<br>". $user_shipping_company . "</br>" .

                   $user_shipping_address_1   . "</br>" .
                   $user_shipping_address_2   . "</br>" .
                   $user_shipping_city        . "</br>" .
                   $user_shipping_postcode    ;
            }

            $wcap_ip_address_text = __( 'IP Address:' , 'woocommerce-ac' );
            if ( $shipping_charges != '' ) { 
                 $wcap_Shipping_charges_text = __( 'Shipping Charges:', 'woocommerce-ac' );                       
                 $shipping_charges_value =  wc_price ( $shipping_charges ) ;
            }
             if ( $wc_shipping_charges != '' ) {
                $wc_Shipping_charges_text = __( 'Shipping Charges:', 'woocommerce-ac' ); 
                $wc_shipping_charges_formatted = wc_price( $wc_shipping_charges );
            }

            $wcap_add_customer_details = " <div class= 'wcap_modal_customer_all_details' >
                <span style = 'display: $user_ip_address_display' >
                    <strong>
                        $wcap_ip_address_text
                    </strong>
                     $user_ip_address
                     <br>
                     <strong>  $wcap_Shipping_charges_text </strong>
                   $shipping_charges_value
                </span>
                <span style = 'display:$biiling_field_display'>
                <strong>  $wcap_billing_address_text </strong>
                   $wcap_create_billing_address
                </span>
                <span style = 'display:$shipping_field_display'>
                <strong>  $wcap_Shipping_address_text </strong>
                   $wcap_create_shipping_address <br/>
                   <strong>  $wc_Shipping_charges_text </strong>
                   $wc_shipping_charges_formatted
                </span>

            </div>";

            if ( isset( $wcap_get_abandoned_cart_result[0] ) && !empty( $wcap_get_abandoned_cart_result ) ) {

                $wcap_cart_user_id = $wcap_get_abandoned_cart_result[0]->user_id;
                $wcap_cart_info    =  json_decode ( stripslashes( $wcap_get_abandoned_cart_result[0]->abandoned_cart_info ) );

                if( $wcap_cart_info == null ) {
                    $wcap_cart_info    =  json_decode ( $wcap_get_abandoned_cart_result[0]->abandoned_cart_info );
                }
               
                $wcap_cart_details = $wcap_cart_info->cart;

                // Currency selected
                $currency = isset( $wcap_cart_info->currency ) ? $wcap_cart_info->currency : '';

                $line_subtotal_tax = '';
                
                $display_cart_details = self::wcap_get_cart_details( $wcap_cart_details, $wcap_cart_id, $wcap_current_page, $currency, $wcap_cart_total );
                
                if ( 'send_email' ==  $wcap_current_page ) {
                    $wcap_quantity_total = ( $display_cart_details [ 'qty_total' ] > 0 ) ? $display_cart_details [ 'qty_total' ] : 0;
                    $wcap_cart_total     = ( $display_cart_details [ 'cart_total' ] > 0 ) ? $display_cart_details [ 'cart_total' ] : 0;
                
                    $line_subtotal_tax_total = ( $display_cart_details [ 'line_subtotal_tax_total' ] > 0 ) ? $display_cart_details [ 'line_subtotal_tax_total' ] : 0;
                }
                
                foreach( $wcap_cart_details as $k => $v ) {
                    $product_id = $display_cart_details[$k][ 'product_id' ];
                    $product_page_url = get_permalink( $product_id );
                    $product_name = $display_cart_details[$k][ 'product_name' ];
                    $item_total_display = $display_cart_details[$k][ 'item_total_formatted' ];
                    $quantity_total = $display_cart_details[$k][ 'qty' ];
                    $line_tax_total = $display_cart_details[$k][ 'line_tax' ];
                    
                    $qty_item_text = 'item';
                    if ( $quantity_total > 1 ) {
                        $qty_item_text = 'items';
                    }
                    
                    $wcap_cart_content_var .= '<tr>';
                    $wcap_cart_content_var .= '<td> <a href="' . $product_page_url . '"> ' . $product_name . '</a></td>';
                    $wcap_cart_content_var .= '<td> ' . $item_total_display . '</td>';
                    $wcap_cart_content_var .= '<td> ' . $quantity_total . ' '. $qty_item_text. '</td>';
                    $wcap_cart_content_var .= '</tr>';
                    
                }
                
                $wcap_include_tax  = get_option( 'woocommerce_prices_include_tax' );
                $wcap_include_tax_setting = get_option( 'woocommerce_calc_taxes' );
            }

            if ( 'send_email' ==  $wcap_current_page ) {
                //$wcap_cart_total = wc_price( $wcap_cart_total );

                $wcap_cart_total = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $wcap_cart_total, $currency ), $wcap_cart_id, $wcap_cart_total, 'wcap_ajax' );

                if( 1 == $wcap_quantity_total ) {
                    $item_disp = __( "item", "woocommerce-ac" );
                } else {
                    $item_disp = __( "items", "woocommerce-ac" );
                }

                $show_taxes = apply_filters('wcap_show_taxes', true);

                if( $show_taxes && isset($wcap_include_tax) && $wcap_include_tax == 'no' &&
                    isset($wcap_include_tax_setting) && $wcap_include_tax_setting == 'yes' ) {

                        //$line_subtotal_tax_total = wc_price( $line_subtotal_tax_total );

                        $line_subtotal_tax_total = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $line_subtotal_tax_total, $currency ), $wcap_cart_id, $line_subtotal_tax_total, 'wcap_ajax' );

                        $wcap_cart_total         = $wcap_cart_total . "<br>Tax: " . $line_subtotal_tax_total;
                }else if( isset($wcap_include_tax) && $wcap_include_tax == 'yes' &&
                    isset($wcap_include_tax_setting) && $wcap_include_tax_setting == 'yes' ) {
                        //$line_subtotal_tax_total = wc_price( $line_subtotal_tax_total );

                        $line_subtotal_tax_total = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $line_subtotal_tax_total, $currency ), $wcap_cart_id, $line_subtotal_tax_total, 'wcap_ajax' );
                        if ($show_taxes) {

                            $wcap_cart_total         = $wcap_cart_total . ' (includes Tax: '. $line_subtotal_tax_total . ')';
                        } else {
                            $wcap_cart_total         = $wcap_cart_total;
                        }
                }
                $wcap_cart_total = $wcap_cart_total . "<br>" . $wcap_quantity_total . ' '.  $item_disp;
            }

            if ( !empty( $wcap_get_abandoned_sent_result ) && count( $wcap_get_abandoned_sent_result ) > 0 ) {
                foreach( $wcap_get_abandoned_sent_result as $wcap_get_abandoned_sent_key => $wcap_get_abandoned_sent_value ) {

                    $wcap_email_sent_time  = strtotime( $wcap_get_abandoned_sent_value->sent_time );
                    $sent_date_format      = date_i18n( get_option( 'date_format' ), $wcap_email_sent_time );
                    $sent_time_format      = date_i18n( get_option( 'time_format' ), $wcap_email_sent_time );
                    $wcap_email_sent_time  = $sent_date_format . ' ' . $sent_time_format;
                    $email_address         = $wcap_get_abandoned_sent_value->sent_email_id;

                    $wcap_cart_email_sent .= '<tr>';
                    $wcap_cart_email_sent .= '<td>Email template <strong>' . $wcap_get_abandoned_sent_value->template_name . '</strong> was sent to <strong>' . $email_address . ' </strong> on '. $wcap_email_sent_time .' </td>';
                    $wcap_cart_email_sent .= '</tr>';

                    $wcap_get_abandoned_cart_clicked_query  = "SELECT `time_clicked` FROM ". WCAP_EMAIL_CLICKED_TABLE . " WHERE `email_sent_id` = %d ORDER BY `id` DESC LIMIT 1";
                    $wcap_get_abandoned_cart_clicked_result = $wpdb->get_results( $wpdb->prepare( $wcap_get_abandoned_cart_clicked_query, $wcap_get_abandoned_sent_value->id ) );

                    if ( !empty( $wcap_get_abandoned_cart_clicked_result ) && count( $wcap_get_abandoned_cart_clicked_result ) > 0 ) {
                        $wcap_email_time_clicked  = strtotime ( $wcap_get_abandoned_cart_clicked_result[0]->time_clicked );
                        $clicked_date_format      = date_i18n( get_option( 'date_format' ), $wcap_email_time_clicked );
                        $clicked_time_format      = date_i18n( get_option( 'time_format' ), $wcap_email_time_clicked );
                        $wcap_email_time_clicked  = $clicked_date_format . ' ' . $clicked_time_format;

                        $wcap_cart_email_clicked .= '<tr>';
                        $wcap_cart_email_clicked .= '<td>Email template <strong>' . $wcap_get_abandoned_sent_value->template_name . '</strong> was <strong> clicked </strong> on '. $wcap_email_time_clicked .' </td>';
                        $wcap_cart_email_clicked .= '</tr>';
                    }

                    $wcap_query_opens   = "SELECT time_opened FROM " . WCAP_EMAIL_OPENED_TABLE . " WHERE email_sent_id= %d ORDER BY `id` DESC LIMIT 1";
                    $wcap_results_opens = $wpdb->get_results( $wpdb->prepare( $wcap_query_opens, $wcap_get_abandoned_sent_value->id ) );

                    if ( !empty( $wcap_results_opens ) && count( $wcap_results_opens ) > 0 ) {
                        $wcap_opened_tmstmp = strtotime( $wcap_results_opens[0]->time_opened );
                        $opened_date_format      = date_i18n( get_option( 'date_format' ), $wcap_opened_tmstmp );
                        $opened_time_format      = date_i18n( get_option( 'time_format' ), $wcap_opened_tmstmp );
                        $wcap_email_opened  = $opened_date_format . ' ' . $opened_time_format;
                        $wcap_cart_email_open .= '<tr>';
                        $wcap_cart_email_open .= '<td>Email template <strong>' . $wcap_get_abandoned_sent_value->template_name . '</strong> was <strong> opened </strong> on '. $wcap_email_opened .' </td>';
                        $wcap_cart_email_open .= '</tr>';
                    }
                }
            }

        ?>

        <div class="wcap-modal__header">
            <h1><?php printf(__( "Cart #%s", 'woocommerce-ac' ), $wcap_cart_id ) ?></h1>
            <?php 
            echo stripslashes( $wcap_abandoned_status ); 
            if( $recovered_order > 0 ) {
                $order_post = get_post( $recovered_order );
                $recovered_stamp = strtotime ( $order_post->post_date );
                
                $order_date_format      = date_i18n( get_option( 'date_format' ), $recovered_stamp );
                $order_time_format      = date_i18n( get_option( 'time_format' ), $recovered_stamp );
                $recovered_date = "$order_date_format $order_time_format";

                $order_url = admin_url( "post.php?post=$recovered_order&action=edit" );
                
                printf( "<h1>" . __( 'Order', 'woocommerce-ac' ) . " <a href='%s' target='_blank'>#%s</a><h1>" . __( " <h5>recovered on %s</h5>" ) , $order_url, $recovered_order, $recovered_date );
            }
            ?>
        </div>

        <div class="wcap-modal__body">
            <div class="wcap-modal__body-inner">

                <table cellspacing="0" cellpadding="6" border="1" class="wcap-cart-table">
                    <thead>
                    <tr>
                        <th><?php _e( 'Email Address',    'woocommerce-ac' ); ?></th>
                        <th><?php _e( 'Customer Details', 'woocommerce-ac' ); ?></th>
                        <th><?php _e( 'Order Total',      'woocommerce-ac' ); ?></th>
                        <th><?php _e( 'Abandoned Date',   'woocommerce-ac' ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                         <tr>
                            <td> <?php echo $wcap_email_address; ?> </td>
                            <td> <?php echo $wcap_customer_details . $wcap_show_customer_detail . $wcap_add_customer_details; ?> </td>
                            <td> <?php echo stripslashes ( $wcap_cart_total ); ?> </td>
                            <td> <?php echo $wcap_abandoned_date; ?> </td>
                        </tr>
                    </tbody>
                </table>
                <table cellspacing="0" cellpadding="0" class="wcap-modal-cart-content">
                    <thead>
                    <tr>
                        <th><?php _e( 'Item Name',    'woocommerce-ac' ); ?></th>
                        <th><?php _e( 'Item Cost',   'woocommerce-ac' ); ?></th>
                        <th><?php _e( 'Item Quantity',   'woocommerce-ac' ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php echo $wcap_cart_content_var; ?>
                    </tbody>
                </table>
                <?php if ( !empty( $wcap_get_abandoned_sent_result ) && count( $wcap_get_abandoned_sent_result ) > 0 ) { ?>
                <table cellspacing="0" cellpadding="0" class="wcap-modal-email-content">
                    <tbody>
                        <?php echo $wcap_cart_email_sent; ?>
                        <?php echo $wcap_cart_email_open; ?>
                        <?php echo $wcap_cart_email_clicked; ?>
                    </tbody>
                </table>
                <?php } ?>
            </div>
        </div>

        <div class="wcap-modal__footer">
            <?php
	    
            if ( isset( $wcap_cart_user_id ) && $wcap_cart_user_id > 0  && '' != $user_email && $recovered_order == 0 && '0' === $wcap_get_abandoned_cart_result[0]->unsubscribe_link ) {
                $wcap_array = array(
                                'action'             => 'cart_recovery',
                                'section'            => 'emailtemplates',
                                'mode'               => 'wcap_manual_email',
                                'abandoned_order_id' => $wcap_cart_id
                            );
                $wcap_url   = add_query_arg(
                                $wcap_array , admin_url( 'admin.php?page=woocommerce_ac_page' )
                            );
                $wcap_initiate_recovery_text = __( 'Send Custom Email', 'woocommerce-ac' );

                $value =  '<a class="button button-primary" href="' . $wcap_url . '">' . $wcap_initiate_recovery_text . '</a>';

                echo $value;

                $unsubscribe = __( 'Unsubscribe', 'woocommerce-ac' );

                echo '<a class="button wcap_admin_unsubscribe" style="margin-left:10px;" data-wcap-cart-id="'.$wcap_cart_id.'" data-modal-type="ajax" >'.$unsubscribe.'</a>';
            }

            $wcap_footer_close_text = __( 'Close', 'woocommerce-ac' );
            $value_close            =  '<a class=" button wcap-icon-close-footer wcap-js-close-modal" >' . $wcap_footer_close_text . '</a>';
            echo $value_close;
            ?>
        </div>
        <?php
        }
        
        /**
         * Returns an array of customer billing information.
         * Should be called only for registered users.
         *
         * @param integer $user_id - User ID
         * @return array $billing_details - Contains Billing Address Details
         * @since 7.8
         */
        public static function wcap_get_billing_details( $user_id ) {
        
            $billing_details = array();
        
            $user_billing_company_temp = get_user_meta( $user_id, 'billing_company' );
            $user_billing_company = "";
            if ( isset( $user_billing_company_temp[0] ) ){
                $user_billing_company = $user_billing_company_temp[0];
            }
            $billing_details[ 'billing_company' ] = $user_billing_company;
        
            $user_billing_address_1_temp = get_user_meta( $user_id, 'billing_address_1' );
            $user_billing_address_1      = "";
            if ( isset( $user_billing_address_1_temp[0] ) ) {
                $user_billing_address_1 = $user_billing_address_1_temp[0];
            }
            $billing_details[ 'billing_address_1' ] = $user_billing_address_1;
        
            $user_billing_address_2_temp = get_user_meta( $user_id, 'billing_address_2' );
            $user_billing_address_2 = "";
            if ( isset( $user_billing_address_2_temp[0] ) ) {
                $user_billing_address_2 = $user_billing_address_2_temp[0];
            }
            $billing_details[ 'billing_address_2' ] = $user_billing_address_2;
        
            $user_billing_city_temp = get_user_meta( $user_id, 'billing_city' );
            $user_billing_city = "";
            if ( isset( $user_billing_city_temp[0] ) ) {
                $user_billing_city = $user_billing_city_temp[0];
            }
            $billing_details[ 'billing_city' ] = $user_billing_city;
        
            $user_billing_postcode_temp = get_user_meta( $user_id, 'billing_postcode' );
            $user_billing_postcode = "";
            if ( isset( $user_billing_postcode_temp[0] ) ) {
                $user_billing_postcode = $user_billing_postcode_temp[0];
            }
            $billing_details[ 'billing_postcode' ] = $user_billing_postcode;
        
            $user_billing_country_temp = get_user_meta( $user_id, 'billing_country' );
            $user_billing_country = "";
            if ( isset( $user_billing_country_temp[0] ) ) {
                $user_billing_country = $user_billing_country_temp[0];
                if ( isset( WC()->countries->countries[ $user_billing_country ] ) ) {
                    $user_billing_country = WC()->countries->countries[ $user_billing_country ];
                }else {
                    $user_billing_country = "";
                }
            }
            $billing_details[ 'billing_country' ] = $user_billing_country;
        
            $user_billing_state_temp = get_user_meta( $user_id, 'billing_state' );
            $user_billing_state = "";
            if ( isset( $user_billing_state_temp[0] ) ) {
                $user_billing_state = $user_billing_state_temp[0];
                if ( isset( WC()->countries->states[ $user_billing_country_temp[0] ][ $user_billing_state ] ) ) {
                    $user_billing_state = WC()->countries->states[ $user_billing_country_temp[0] ][ $user_billing_state ];
                }else {
                    $user_billing_state = "";
                }
        
            }
            $billing_details[ 'billing_state' ] = $user_billing_state;
        
            return $billing_details;
        }
        
        /**
         * Returns the Item Name, Qty and Total for any given product
         * in the WC Cart
         *
         * @param stdClass $v - Cart Information from WC()->cart;
         * @param integer $wcap_cart_id - Abandoned Cart ID
         * @param string $wcap_current_page - Current page where the data is needed
         * @param string $currency - Product Currency
         * @return array $item_details - Item Data
         * @since 7.8
         */
        public static function wcap_get_cart_details( $wcap_cart_details, $wcap_cart_id = '', $wcap_current_page = '', $currency = '', $wcap_cart_total = 0 ) {
        
            global $woocommerce;
             
            $cart_total        = $item_subtotal = $item_total = $line_subtotal_tax_display =  $after_item_subtotal = $after_item_subtotal_display =  $line_subtotal_tax_total= 0;

            $line_subtotal_tax = 0;
            $wcap_quantity_total =  0;
            
            $wcap_include_tax  = get_option( 'woocommerce_prices_include_tax' );
            $wcap_include_tax_setting = get_option( 'woocommerce_calc_taxes' );
            
            $item_details = array();

            foreach( $wcap_cart_details as $k => $v ) {
            
                $product_id        = $v->product_id;
                $prod_name         = get_post( $product_id );
                if ( count( get_object_vars( $prod_name ) ) > 0 && $prod_name->post_type === 'product' ) {
            
                    $quantity_total    = $v->quantity;
                    $wcap_quantity_total = $wcap_quantity_total + $v->quantity;
                    $item_name         = $prod_name->post_title;
                    $product_name      = apply_filters( 'wcap_product_name', $item_name, $v->product_id );
                    $wcap_product      = wc_get_product( $product_id );
                    if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {
                        $wcap_product_type = $wcap_product->get_type();
                    }else {
                        $wcap_product_type = $wcap_product->product_type;
                    }
                    $wcap_product_sku = apply_filters( 'wcap_product_sku', $wcap_product->get_sku(), $v->product_id );
                    if( false != $wcap_product_sku && '' != $wcap_product_sku ) {
                        if( $wcap_product_type == 'simple' && '' != $wcap_product->get_sku() ){
                            $wcap_sku = '<br> SKU: ' . $wcap_product->get_sku();
                        } else {
                            $wcap_sku = '';
                        }
                        $product_name = $product_name . $wcap_sku ;
                    } else {
                        $product_name = $product_name;
                    }
                    $product_name = apply_filters( 'wcap_after_product_name', $product_name, $v->product_id ); 
                    if( isset( $v->variation_id ) && '' != $v->variation_id ) {
                        $variation_id = $v->variation_id;
                        $variation    = wc_get_product( $variation_id );
            
                        if ( false !== $variation ) {
                            $name         = $variation->get_formatted_name() ;
                            $explode_all  = explode( "&ndash;", $name );
            
                            if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {
                                if( false != $wcap_product_sku && '' != $wcap_product_sku ) {
                                    $wcap_sku = '';
                                    if( $variation->get_sku() ) {
                                        $wcap_sku = "SKU: " . $variation->get_sku();
                                    }
                                    $wcap_get_formatted_variation  =  wc_get_formatted_variation( $variation, true );
            
                                    $add_product_name = $product_name . ' - <br>' . $wcap_sku . ' '.$wcap_get_formatted_variation;
                                } else {
                                    $wcap_get_formatted_variation = wc_get_formatted_variation( $variation, true );
            
                                    $add_product_name = $product_name . '<br>' . $wcap_get_formatted_variation;
                                }
            
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
                            $product_name = apply_filters( 'wcap_after_variable_product_name', $product_name_with_variable, $v->product_id );
                        }
                    }
            
                    // Item subtotal is calculated as product total including taxes
                    if( isset($wcap_include_tax) && $wcap_include_tax == 'no' &&
                        isset($wcap_include_tax_setting) && $wcap_include_tax_setting == 'yes' ) {
            
                            $item_subtotal        = $item_subtotal + $v->line_total;  // This is fix
                            $line_subtotal_tax   += $v->line_tax; // This is fix
                            $after_item_subtotal =  $item_subtotal;
                            /* on sent email we need this for first row */
                            $line_subtotal_tax_total += $line_subtotal_tax;
            
                        }else if ( isset($wcap_include_tax) && $wcap_include_tax == 'yes' &&
                            isset($wcap_include_tax_setting) && $wcap_include_tax_setting == 'yes' ) {
                                // Item subtotal is calculated as product total including taxes
                                if( is_numeric( $v->line_tax ) && $v->line_tax > 0 ) {
                                    $line_subtotal_tax_display += $v->line_tax;
            
                                    /* After copon code price */
                                    $after_item_subtotal = $item_subtotal + $v->line_total + $v->line_tax;
            
                                    /*Calculate the product price*/
                                    $item_subtotal = $item_subtotal + $v->line_subtotal + $v->line_subtotal_tax;
            
                                    /* on sent emial tab we need this for first row */
                                    $line_subtotal_tax_total += $line_subtotal_tax_display;
                                } else {
                                    $item_subtotal = $item_subtotal + $v->line_total;
                                    $line_subtotal_tax_display += $v->line_tax;
                                }
                            }else{
            
                                $item_subtotal = $item_subtotal + $v->line_total;
                                $after_item_subtotal = $v->line_total ;
                            }
            
                            //  Line total
                            $item_total                  = $item_subtotal;
                            $item_price                  = $item_subtotal / $quantity_total;
                            $after_item_subtotal_display = ( $item_subtotal - $after_item_subtotal ) +  $after_item_subtotal_display ;
                            ///$item_total_display          = wc_price( $item_total );
            
                            $item_total_display          = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $item_total, $currency ), $wcap_cart_id, $item_total, 'wcap_ajax' );
            
                            //$item_price                  = wc_price( $item_price );
            
                            $item_price                  = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $item_price, $currency ), $wcap_cart_id, $item_price, 'wcap_ajax' );
            
                            if( isset($wcap_include_tax) && $wcap_include_tax == 'no' &&
                                isset($wcap_include_tax_setting) && $wcap_include_tax_setting == 'yes' ) {
            
                                    //$line_subtotal_tax  = wc_price( $line_subtotal_tax );
                                    $line_subtotal_tax  = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $line_subtotal_tax, $currency ), $wcap_cart_id, $line_subtotal_tax, 'wcap_ajax' );
                                    $item_total_display = $item_total_display . '<br>'. __( "Tax: ", "woocommerce-ac" ) . $line_subtotal_tax;
                                }else if( isset($wcap_include_tax) && $wcap_include_tax == 'yes' &&
                                    isset($wcap_include_tax_setting) && $wcap_include_tax_setting == 'yes' ) {
                                        //$line_subtotal_tax_display = wc_price( $line_subtotal_tax_display );
            
                                        $line_subtotal_tax_display = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $line_subtotal_tax_display, $currency ), $wcap_cart_id, $line_subtotal_tax_display, 'wcap_ajax' );
            
            
                                        $item_total_display        = $item_total_display . ' (' . __( "includes Tax: " , "woocommerce-ac" ) . $line_subtotal_tax_display . ')';
                                    }
            
                                    if ( 'send_email' ==  $wcap_current_page ) {
                                        $wcap_cart_total = $after_item_subtotal + $wcap_cart_total ;
                                    }
                                    $product = wc_get_product( $product_id );
                                    // If bundled product, get the list of sub products
                                    if( isset( $product->bundle_data ) && is_array( $product->bundle_data ) && count( $product->bundle_data ) > 0) {
                                        foreach( $product->bundle_data as $b_key => $b_value ) {
                                            $bundle_child[] = $b_key;
                                        }
                                    }
                                    // check if the product is a part of the bundles product, if yes, set qty and totals to blanks
                                    if( isset( $bundle_child ) && count( $bundle_child ) > 0 ) {
                                        if( in_array( $product_id, $bundle_child ) ) {
                                            $item_subtotal = $item_total_display = $quantity_total = '';
                                        }
                                    }
            
                } else {
                    $product_name =  __( "Product has been deleted" , "woocommerce-ac" );
                    $item_total_display = $quantity_total = $qty_item_text = '';
                }
            
                $item_details[$k][ 'product_id' ] = $product_id;
                $item_details[$k][ 'product_name' ] = $product_name;
                $item_details[$k][ 'item_total_formatted' ] = $item_total_display;
                $item_details[$k][ 'item_total' ] = $item_total;
                $item_details[$k][ 'qty' ] = $quantity_total;
                $item_details[$k][ 'line_tax' ] = $line_subtotal_tax_total;
                                
                // reset the fields
                $item_subtotal = $item_total = 0;
            }
            
            $item_details[ 'qty_total' ]     = $wcap_quantity_total;
            $item_details[ 'cart_total']   = $wcap_cart_total;
            $item_details[ 'line_subtotal_tax_total' ] = $line_subtotal_tax_total;
            
            return $item_details;
        }
    }
}
