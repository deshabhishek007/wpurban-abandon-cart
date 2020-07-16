<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * It will send custom email to selected abandoned cart's users. The admin of the store will be able to send the abandoned cart reminder to specific abandoned cart(s). Also, the admin can edit the existing email template for sending the email.
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @category Classes
 * @since    5.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once( WP_PLUGIN_DIR . '/woocommerce-abandon-cart-pro/cron/wcap_send_email_using_cron.php' );
/**
 * This class is used to start Initiate Recovery. 
 * 
 * @since 4.2
 */
class Wcap_Send_Manual_Email {

    /**
     * It will replace the merge tags and send the email to customer(s).
     * @global mixed $wpdb
     * @global mixed $woocommerce 
     */
    public static function wcap_create_and_send_manual_email (){

        global $wpdb;
        global $woocommerce;

        $abandoned_cart_ids             = explode ( ",", $_POST ['abandoned_cart_id'] );
        $selected_template_id           = $_POST ['wcap_manual_template_name'];
        $get_template_data              = "SELECT * FROM " . WCAP_EMAIL_TEMPLATE_TABLE . " WHERE id =". $selected_template_id;
        $results_of_get_template_data   = $wpdb->get_results ( $get_template_data );
        $default_template               =  $results_of_get_template_data[0]->default_template;
        $discount_amount                =  $results_of_get_template_data[0]->discount;
        $wcap_current_time              = current_time( 'timestamp' );

        foreach ( $abandoned_cart_ids as $abandoned_cart_ids_key => $abandoned_cart_ids_value ){

            $email_body_template        = isset( $_POST['woocommerce_ac_email_body'] ) ? $_POST['woocommerce_ac_email_body'] : '';
            $email_body_template        = stripslashes( $email_body_template );
            $email_body_template        = convert_smilies( $email_body_template );
            $template_email_subject     = isset( $_POST['woocommerce_ac_email_subject'] ) ? $_POST['woocommerce_ac_email_subject'] : '';
            $template_email_subject     = convert_smilies( $template_email_subject );
            $wcap_from_name             = get_option ( 'wcap_from_name' );
            $wcap_from_email            = get_option ( 'wcap_from_email' );
            $wcap_reply_email           = get_option ( 'wcap_reply_email' );

            $headers                    = "From: " . $wcap_from_name . " <" . $wcap_from_email . ">" . "\r\n";
            $headers                    .= "Content-Type: text/html"."\r\n";
            $headers                    .= "Reply-To:  " . $wcap_reply_email . " " . "\r\n";
            $coupon_id                  = isset( $_POST['coupon_ids'] ) ? $_POST['coupon_ids'][0] : '';

            $coupon_code = '';
            if ( '' !== $coupon_id ) {
                $coupon_to_apply = get_post( $coupon_id, ARRAY_A );
                $coupon_code     = $coupon_to_apply['post_title'];
            }

            $generate_unique_code       = isset( $_POST['unique_coupon'] ) ? $_POST['unique_coupon'] : '' ;
            $is_wc_template             = isset( $_POST['is_wc_template'] ) ? $_POST['is_wc_template'] : '' ;
            $wc_template_header_t       = $_POST['wcap_wc_email_header'] != '' ? $_POST['wcap_wc_email_header'] : __( 'Abandoned cart reminder', 'woocommerce-ac');
            $coupon_code_to_apply       = $email_subject = '';

            $get_abandoned_cart         = "SELECT * FROM " . WCAP_ABANDONED_CART_HISTORY_TABLE . " WHERE id =". $abandoned_cart_ids_value;
            $results_of_abandoned_cart  = $wpdb->get_results ( $get_abandoned_cart );

            $value = new stdClass();
            $value->user_type           = isset( $results_of_abandoned_cart[0]->user_type ) ? $results_of_abandoned_cart[0]->user_type : '';
            $value->user_id             = isset( $results_of_abandoned_cart[0]->user_id ) ? $results_of_abandoned_cart[0]->user_id : '';
            $value->abandoned_cart_info = isset( $results_of_abandoned_cart[0]->abandoned_cart_info ) ? $results_of_abandoned_cart[0]->abandoned_cart_info : '';
            $value->abandoned_cart_time = isset( $results_of_abandoned_cart[0]->abandoned_cart_time ) ? $results_of_abandoned_cart[0]->abandoned_cart_time : '';
            $value->language            = isset( $results_of_abandoned_cart[0]->language ) ? $results_of_abandoned_cart[0]->language : '';
            $wcap_guest_session_id      = isset( $results_of_abandoned_cart[0]->session_id ) ? $results_of_abandoned_cart[0]->session_id : 0;;
            $value->ac_id               = isset( $results_of_abandoned_cart[0]->id ) ? $results_of_abandoned_cart[0]->id : '';
            $wcap_used_coupon           = '';

            $selected_lanaguage = '';
            if ( $value->user_type == "GUEST" && $value->user_id != '0' ) {
                $value->user_login  = "";
                $query_guest        = "SELECT billing_first_name, billing_last_name, email_id FROM `" . WCAP_GUEST_CART_HISTORY_TABLE . "` WHERE id = %d";
                $results_guest      = $wpdb->get_results( $wpdb->prepare( $query_guest, $value->user_id ) );
                if (count ($results_guest) > 0 ){
                    $value->user_email  = $results_guest[0]->email_id;
                }

                $query_guest_session   = "SELECT session_value FROM `" . $wpdb->prefix . "woocommerce_sessions` WHERE session_key = %s";
                $results_guest_session = $wpdb->get_results( $wpdb->prepare( $query_guest_session, $wcap_guest_session_id  ) );

                if ( count( $results_guest_session ) > 0 ){
                    $wcap_result_session  = unserialize ( $results_guest_session[0]->session_value );
                    $wcap_coupon_sesson   = unserialize ( $wcap_result_session['applied_coupons'] );
                    $wcap_used_coupon     = '';
                    $coupon_code_to_apply = '';
                    if ( count( $wcap_coupon_sesson ) > 0 && isset( $wcap_coupon_sesson[0]) ) {
                        $wcap_used_coupon       = $wcap_coupon_sesson[0];
                        $coupon_code_to_apply   = $wcap_used_coupon;
                    }
                }

            } else {
                $user_id            = $value->user_id;
                $user_email_biiling = get_user_meta( $user_id, 'billing_email', true );

                if ( isset( $user_email_biiling ) && "" == $user_email_biiling ) {
                    $user_data = get_userdata( $user_id );
                    
                    if ( isset( $user_data->user_email ) && "" != $user_data->user_email ) {
                        $value->user_email = $user_data->user_email;
                    } 
                } else if ( '' != $user_email_biiling ) {
                    $value->user_email = $user_email_biiling;
                }

                $logged_in_user_key = get_post_meta ( $value->ac_id , '_woocommerce_ac_coupon');
                if ( count( $logged_in_user_key ) > 0 ){

                    $wcap_used_coupon =  $logged_in_user_key[0]['coupon_code'];
                    $coupon_code_to_apply = $wcap_used_coupon;
                }
            }
            $cart = new stdClass();
            $cart_info_db_field = json_decode( stripslashes( $value->abandoned_cart_info ) );
            if( !empty( $cart_info_db_field->cart ) ) {
                $cart           = $cart_info_db_field->cart;
            }

            // Currency selected
            $currency = isset( $cart_info_db_field->currency ) ? $cart_info_db_field->currency : '';

            $validate_email_format = Wcap_Send_Manual_Email::wcap_validate_email_format ( $value->user_email );

            if( count( get_object_vars( $cart ) ) > 0 && $value->user_id != '0' && $validate_email_format === 1 ) {
                $cart_update_time   = $value->abandoned_cart_time;
                $selected_lanaguage = $value->language;
                $wc_template_header = $wc_template_header_t;
                $cart_info_db       = $value->abandoned_cart_info;
                $email_body         = $email_body_template;
                $email_body         .= '{{email_open_tracker}}';

                if ( $value->user_type == "GUEST" ) {
                    if ( isset( $results_guest[0]->billing_first_name ) ) {
                        $email_body    = str_ireplace( "{{customer.firstname}}", $results_guest[0]->billing_first_name, $email_body );
                        $email_subject = str_ireplace( "{{customer.firstname}}", $results_guest[0]->billing_first_name, $template_email_subject );
                    }

                    if ( isset( $results_guest[0]->billing_last_name ) ) {
                        $email_body = str_ireplace( "{{customer.lastname}}", $results_guest[0]->billing_last_name, $email_body );
                    }

                    if ( isset( $results_guest[0]->billing_first_name ) && isset( $results_guest[0]->billing_last_name ) ) {
                        $email_body = str_ireplace( "{{customer.fullname}}", $results_guest[0]->billing_first_name . " " . $results_guest[0]->billing_last_name, $email_body );
                    }
                    else if ( isset( $results_guest[0]->billing_first_name ) ){
                        $email_body = str_ireplace( "{{customer.fullname}}", $results_guest[0]->billing_first_name, $email_body );
                    }
                    else if ( isset( $results_guest[0]->billing_last_name) ){
                        $email_body = str_ireplace( "{{customer.fullname}}", $results_guest[0]->billing_last_name, $email_body );
                    }
                } else {
                    $user_first_name_temp = get_user_meta( $value->user_id, 'billing_first_name', true );
                    if( isset( $user_first_name_temp ) && "" == $user_first_name_temp ) {
                        $user_data  = get_userdata( $user_id );
                        $user_first_name = $user_data->first_name;
                    } else {
                        $user_first_name = $user_first_name_temp;
                    }

                    $user_last_name_temp = get_user_meta( $value->user_id, 'billing_last_name', true );
                    if( isset( $user_last_name_temp ) && "" == $user_last_name_temp ) {
                        $user_data  = get_userdata( $user_id );
                        $user_last_name = $user_data->last_name;
                    } else {
                        $user_last_name = $user_last_name_temp;
                    }
                    $email_body    = str_ireplace( "{{customer.firstname}}", $user_first_name, $email_body );
                    $email_subject = str_ireplace( "{{customer.firstname}}", $user_first_name, $template_email_subject );
                    $email_body    = str_ireplace( "{{customer.lastname}}", $user_last_name, $email_body );
                    $email_body    = str_ireplace( "{{customer.fullname}}", $user_first_name . " " . $user_last_name, $email_body );
                }

                $wcap_get_customers_email = Wcap_Send_Manual_Email::wcap_get_customers_email( $value->user_id, $value->user_type );
                $email_body = str_ireplace( "{{customer.email}}", $wcap_get_customers_email, $email_body );

                $wcap_get_customers_phone = Wcap_Send_Manual_Email::wcap_get_customers_phone( $value->user_id, $value->user_type );
                $email_body = str_ireplace( "{{customer.phone}}", $wcap_get_customers_phone, $email_body );
                $order_date = "";

                if ( $cart_update_time != "" && $cart_update_time != 0 ) {
                    $date_format   = date_i18n( get_option( 'date_format' ), $cart_update_time );
                    $time_format   = date_i18n( get_option( 'time_format' ), $cart_update_time );
                    $order_date    = $date_format . ' ' . $time_format;
                }

                if ( preg_match( "{{coupon.code}}", $email_body, $matched ) ) {

                    // Calculating the expiry time of the coupon.
                    $expiry_date_extend = '';
                    if ( isset( $_POST['wcac_coupon_expiry'] ) && "" !== $_POST['wcac_coupon_expiry'] && '0' !== $_POST['wcac_coupon_expiry'] ) {
                        
                        $wcac_coupon_expiry = isset( $_POST['wcac_coupon_expiry'] ) ? $_POST['wcac_coupon_expiry'] : "7" ;
                        $expiry_day_or_hour = isset( $_POST['expiry_day_or_hour'] ) ? $_POST['expiry_day_or_hour'] : "days" ;
                        $discount_expiry    = " +".$wcac_coupon_expiry." ".$expiry_day_or_hour;
                        $expiry_date_extend = strtotime( $discount_expiry );
                    }

                    $coupon_post_meta   = '';
                    $amount             = isset( $_POST['wcap_coupon_amount'] ) ? $_POST['wcap_coupon_amount'] : "0";
                    $discount_type      = isset( $_POST['wcap_discount_type'] ) ? $_POST['wcap_discount_type'] : "percent";

                    $discount_shipping  = isset( $_POST['wcap_allow_free_shipping'] ) ? "yes" : "no";
                    $expiry_date        = apply_filters( 'wcap_coupon_expiry_date', $expiry_date_extend );

                    if ( '1' == $default_template && $coupon_code == '' ) {
                        $coupon_code_to_apply   = '';
                        if ( $amount > '0' ) {
                            $coupon_code_to_apply = Wcap_Send_Manual_Email::wp_coupon_code( $amount, $discount_type, $expiry_date, $discount_shipping, $coupon_post_meta );
                        }
                    } else if ( $coupon_code != '' && $generate_unique_code == 'on' ) {
                        $coupon_post_meta         = get_post_meta( $coupon_id );
                        $get_current_date         = date( get_option( 'date_format' ) );                       
                        $coupon_expiry_timestamp  = $coupon_post_meta['date_expires'][0];                       
                        $current_date_timestamp   = strtotime( $get_current_date ); 
                        $discount_type            = $coupon_post_meta['discount_type'][0];
                        $amount                   = $coupon_post_meta['coupon_amount'][0];
                        if ( isset( $coupon_post_meta['date_expires'][0] ) && $coupon_expiry_timestamp < $current_date_timestamp && $coupon_post_meta['date_expires'][0] != '' ) {
                            $expiry_date = $coupon_post_meta['date_expires'][0];
                        }

                        if ( isset( $coupon_post_meta['date_expires'][0] ) && $coupon_expiry_timestamp >= $wcap_current_time && $coupon_post_meta['date_expires'][0] != '' ) {
                            $expiry_date = $coupon_post_meta['date_expires'][0];
                        }
                        $coupon_code_to_apply = Wcap_Send_Manual_Email::wp_coupon_code( $amount, $discount_type, $expiry_date, $discount_shipping, $coupon_post_meta );
                    } else if ( 'on' === $generate_unique_code && '' === $coupon_code && '0' == $default_template ) {
                        $coupon_code_to_apply   = '';
                        if ( $amount > '0' ) {
                            $coupon_code_to_apply = Wcap_Send_Manual_Email::wp_coupon_code( $amount, $discount_type, $expiry_date, $discount_shipping, $coupon_post_meta );
                        }
                    } else {
                        $coupon_code_to_apply = $coupon_code;
                    }

                    $email_body = str_ireplace( "{{coupon.code}}", $coupon_code_to_apply, $email_body );
                }

                $email_body  = str_ireplace( "{{cart.abandoned_date}}", $order_date, $email_body );
                $email_body  = str_ireplace( "{{shop.name}}", get_option( 'blogname' ), $email_body );
                $email_body  = str_ireplace( "{{shop.url}}", get_option( 'siteurl' ), $email_body );
                if( version_compare( WOOCOMMERCE_VERSION, '3.2.0', ">=" ) ) {
                    $store_address = Wcap_Common::wcap_get_wc_address();
                    $email_body  = str_ireplace( "{{store.address}}", $store_address, $email_body );
                }
                $uid         = get_current_user_id();
                $admin_phone = get_user_meta( $uid, 'billing_phone', true );
                $email_body  = str_ireplace( '{{admin.phone}}', $admin_phone, $email_body );

                if ( $woocommerce->version < '2.3' ) {
                    $checkout_page_link = $woocommerce->cart->get_checkout_url();
                } else {
                    $checkout_page_id   = wc_get_page_id( 'checkout' );
                    $checkout_page_link = '';
                    if( $checkout_page_id ) {
                        // Get the checkout URL
                        $checkout_page_link = get_permalink( $checkout_page_id );

                        if( function_exists( 'icl_register_string' ) ) {
                            if( 'en' == $selected_lanaguage  ) {
                                $checkout_page_link = $checkout_page_link;
                            } else {
                                $checkout_page_link = apply_filters( 'wpml_permalink', $checkout_page_link, $selected_lanaguage );
                            }
                        }
                        // Force SSL if needed
                        if ( is_ssl() || 'yes' === get_option( 'woocommerce_force_ssl_checkout' ) ) {
                            $checkout_page_link = str_ireplace( 'http:', 'https:', $checkout_page_link );
                        }
                    }
                }

                $query_sent = "INSERT INTO `" . WCAP_EMAIL_SENT_HISTORY_TABLE . "` ( template_id, abandoned_order_id, sent_time, sent_email_id )
                                      VALUES ( '" . $selected_template_id . "', '" . $abandoned_cart_ids_value . "', '" . current_time('mysql') . "', '" . $value->user_email . "' )";
                $wpdb->query( $query_sent );
                $query_id          = "SELECT * FROM `" . WCAP_EMAIL_SENT_HISTORY_TABLE . "` WHERE template_id= %d AND abandoned_order_id= %d ORDER BY id DESC LIMIT 1 ";
                $results_sent      = $wpdb->get_results ( $wpdb->prepare( $query_id, $selected_template_id, $abandoned_cart_ids_value ) );
                $email_sent_id     = $results_sent[0]->id;

                $encoding_checkout = $email_sent_id . '&url=' . $checkout_page_link;
                $validate_checkout = Wcap_Common::encrypt_validate( $encoding_checkout );

                if( isset( $coupon_code_to_apply ) && $coupon_code_to_apply != '' ) {
                    $encypted_coupon_code = Wcap_Common::encrypt_validate( $coupon_code_to_apply );
                    $checkout_link_track  = get_option( 'siteurl' ) . '/?wacp_action=track_links&validate=' . $validate_checkout . '&c='.$encypted_coupon_code;
                } else {
                    $checkout_link_track = get_option( 'siteurl' ) . '/?wacp_action=track_links&validate=' . $validate_checkout;
                }

                // Populate the product name if its present in the email subject line
                $sub_line_prod_name = '';
                $cart_details       = $cart_info_db_field->cart;
                foreach ( $cart_details as $k => $v ) {
                    $sub_line_prod_name = get_the_title( $v->product_id );
                    break;
                }
                $email_subject = str_ireplace( "{{product.name}}", $sub_line_prod_name, $email_subject );
                // Populate the products.cart shortcode if it exists
                $wcap_product_image_height = get_option( 'wcap_product_image_height' );
                $wcap_product_image_width  = get_option( 'wcap_product_image_width' );

                if ( preg_match( "{{item.image}}", $email_body, $matched ) || preg_match( "{{item.name}}", $email_body, $matched ) || preg_match( "{{item.price}}", $email_body, $matched ) || preg_match( "{{item.quantity}}", $email_body, $matched ) || preg_match( "{{item.subtotal}}", $email_body, $matched ) || preg_match( "{{cart.total}}", $email_body, $matched ) ) {
                    $replace_html   = '';
                    $cart_details   = $cart_info_db_field->cart;
                    $cart_total     = $item_subtotal = $item_total =  $line_subtotal_tax_display = $after_item_subtotal = $after_item_subtotal_display = 0;
                    $line_subtotal_tax = 0;

                    $wcap_all_product_names = '';
                    $wcap_all_product_images = '';
                    $wcap_all_product_price = '';
                    $wcap_all_product_qty = '';
                    $wcap_all_product_sub_total = '';
                    $custom_table = false;
                    $custom_replace = '';

                    $wcap_include_tax         = get_option( 'woocommerce_prices_include_tax' );
                    $wcap_include_tax_setting = get_option( 'woocommerce_calc_taxes' );
                    // This array will be used to house the columns in the hierarchy they appear
                    $position_array = array();
                    $start_position = $end_position = $image_start_position = $name_start_position = 0;
                    

                    // Complete populating the array
                    ksort( $position_array );

                    $tr_array   = explode( "<tr", $email_body );
                    $check_html = $style = '';
                    foreach ( $tr_array as $tr_key => $tr_value ) {
                        if( (preg_match( "{{item.image}}", $tr_value, $matched ) || preg_match( "{{item.name}}", $tr_value, $matched) || preg_match( "{{item.price}}", $tr_value, $matched ) || preg_match( "{{item.quantity}}", $tr_value, $matched) || preg_match( "{{item.subtotal}}", $tr_value, $matched)) && ! preg_match( "{{cart.total}}", $tr_value, $matched ) && stripos( $tr_value, 'wcap_custom_table' ) === false && stripos(  $tr_value, 'cart.link' ) === false ) {

                            if ( count( get_object_vars( $cart_details ) ) > 0 ) {
                                $style_start  = stripos( $tr_value, 'style' );
                                $style_end    = stripos( $tr_value, '>', $style_start );
                                $style_end    = $style_end - $style_start;
                                $style        = substr( $tr_value, $style_start, $style_end );
                                $tr_value     = "<tr" . $tr_value;
                                $end_position = stripos( $tr_value, '</tr>' );
                                $end_position = $end_position + 5;
                                $check_html   = substr( $tr_value, 0, $end_position );
                            }

                            //check which columns are present
                            if ( preg_match( "{{item.image}}", $email_body, $matched ) ) {
                                $image_start_position = stripos( $email_body, '{{item.image}}' );
                                $position_array[ $image_start_position ] = 'image';
                            }
                            if ( preg_match( "{{item.name}}", $email_body, $matched ) ) {
                                $name_start_position = stripos( $email_body,'{{item.name}}' );
                                $position_array[ $name_start_position ] = 'name';
                            }
                            if ( preg_match( "{{item.price}}", $email_body, $matched ) ) {
                                $price_start_position = stripos( $email_body, '{{item.price}}' );
                                $position_array[ $price_start_position ] = 'price';
                            }
                            if ( preg_match( "{{item.quantity}}", $email_body, $matched ) ) {
                                $quantity_start_position = stripos( $email_body, '{{item.quantity}}' );
                                $position_array[ $quantity_start_position ] = 'quantity';
                            }
                            if ( preg_match( "{{item.subtotal}}", $email_body, $matched ) ) {
                                $subtotal_start_position = stripos( $email_body,'{{item.subtotal}}' );
                                $position_array[ $subtotal_start_position ] = 'subtotal';
                            }
                        } else if ( stripos( $tr_value, 'wcap_custom_table' ) !== false ) {
                            $tr_value       = "<tr" . $tr_value;
                            $end_position   = stripos( $tr_value, '</tr>' );
                            $end_position   = $end_position + 5;
                            $custom_tr      = substr( $tr_value, 0, $end_position );
                            $custom_table   = true;
                        }
                    }

                    $i = 1;
                    $image_url = '';
                    $display_cart_total = 0;
                    $bundle_child = array();
                    foreach ( $cart_details as $k => $v ) {
                        // Product image
                        if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {
                          $wcap_product   = wc_get_product($v->product_id );
                          $product        = wc_get_product($v->product_id );
                        }else {
                            $product      = get_product( $v->product_id );
                            $wcap_product = get_product($v->product_id );
                        }
                            
                        if ( false !== $product ) {
                            $image_size   = array( $wcap_product_image_width, $wcap_product_image_height, '1' );

                            $image_id = isset( $v->variation_id ) && $v->variation_id != '' && $v->variation_id > 0 ? $v->variation_id : $v->product_id;
                            $image_url    = Wcap_Common::wcap_get_product_image( $image_id, $image_size );

                            // Populate the name variable
                            $product_name = get_post( $v->product_id );
                            $item_name    = $product_name->post_title;
                            $prod_name    = apply_filters( 'wcap_product_name', $item_name, $v->product_id );                                                      
                            // Populate qty
                            $quantity  = $v->quantity;

                           
                            if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {
                                $wcap_product_type = $wcap_product->get_type();
                            }else {
                                $wcap_product_type = $wcap_product->product_type;
                            }
                            $wcap_product_sku = apply_filters( 'wcap_product_sku', $product->get_sku(), $v->product_id );
                            if( false != $wcap_product_sku && '' != $wcap_product_sku ) {
                                if( $wcap_product_type == 'simple' && '' != $wcap_product->get_sku() ){
                                    $wcap_sku = '<br> SKU: ' . $wcap_product->get_sku();        
                                } else {
                                    $wcap_sku = '';    
                                }
                                $prod_name    = $prod_name . $wcap_sku;
                            } else {
                                $prod_name    = $prod_name;
                            }  

                            $prod_name = apply_filters( 'wcap_after_product_name', $prod_name, $v->product_id );  
                            // Show variation
                            if( isset( $v->variation_id ) && '' != $v->variation_id ){
                                $variation_id = $v->variation_id;
                                $variation    = wc_get_product( $variation_id );

                                if ( false !== $variation ) {
                                    $name         = $variation->get_formatted_name() ;
                                    $explode_all  = explode( "&ndash;", $name );
                                    if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {
                                        if( false != $wcap_product_sku && '' != $wcap_product_sku ) {
                                            $wcap_sku = '';
                                            if ( $variation->get_sku() ) {
                                                $wcap_sku = "SKU: " . $variation->get_sku() . "<br>";
                                            }
                                            $wcap_get_formatted_variation  =  wc_get_formatted_variation( $variation, true );

                                            $add_product_name = $prod_name . ' - ' . $wcap_sku . ' ' .$wcap_get_formatted_variation;
                                        } else {
                                            $wcap_get_formatted_variation  =  wc_get_formatted_variation( $variation, true );
                                            $add_product_name = $prod_name . '<br>' . $wcap_get_formatted_variation;
                                        }                                          
                                        $pro_name_variation = (array) $add_product_name;
                                    } else {
                                        $pro_name_variation = array_slice( $explode_all, 1, -1 );
                                    }
                                    $product_name_with_variable = '';
                                    $explode_many_varaition     = array();

                                    foreach ( $pro_name_variation as $pro_name_variation_key => $pro_name_variation_value ){
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

                            if( isset( $wcap_include_tax ) && $wcap_include_tax == 'no' &&
                            isset( $wcap_include_tax_setting ) && $wcap_include_tax_setting == 'yes' ) {
                                $item_subtotal       =  $v->line_subtotal;
                                $after_item_subtotal =  $v->line_total ;
                                $line_subtotal_tax   += $v->line_tax;
                                $display_cart_total  = $v->line_total +  $v->line_tax + $display_cart_total;
                            } elseif ( isset( $wcap_include_tax ) && $wcap_include_tax == 'yes' &&
                            isset( $wcap_include_tax_setting ) && $wcap_include_tax_setting == 'yes' ) {
                                // Item subtotal is calculated as product total including taxes
                                if( $v->line_tax != 0 && $v->line_tax > 0 ) {

                                    $line_subtotal_tax_display += $v->line_tax;

                                    // After copon code price
                                    $after_item_subtotal = $item_subtotal + $v->line_total + $v->line_tax;

                                    // Calculate the product price
                                    $item_subtotal = $item_subtotal + $v->line_subtotal + $v->line_subtotal_tax;
                                } else {
                                    $item_subtotal = $item_subtotal + $v->line_total;
                                    $line_subtotal_tax_display += $v->line_tax;
                                    $after_item_subtotal = $item_subtotal + $v->line_tax;
                                }
                            } else {

                                if( $v->line_subtotal_tax != 0 && $v->line_subtotal_tax > 0 ) {
                                    $after_item_subtotal = $v->line_total + $v->line_subtotal_tax;
                                    $item_subtotal = $item_subtotal + $v->line_subtotal + $v->line_subtotal_tax;
                                } else {
                                    $after_item_subtotal = $v->line_total;
                                    $item_subtotal = $item_subtotal + $v->line_total;
                                }
                            }

                            //  Line total
                            $item_total                  = $item_subtotal;
                            $item_price                  = $item_subtotal / $quantity;
                            $after_item_subtotal_display = ( $item_subtotal - $after_item_subtotal ) +  $after_item_subtotal_display ;
                            $item_subtotal_display       = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $item_total, $currency ), $abandoned_cart_ids_value, $item_total, 'wcap_manual_mail' ); 
                            $item_price                  =  apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $item_price, $currency ), $abandoned_cart_ids_value, $item_price, 'wcap_manual_mail' ); 
                            $cart_total                 += $after_item_subtotal;
                            $item_subtotal               = $item_total = 0;

                            /*if( $i % 2 == 0 ) {
                                $replace_html .= '<tr>';
                            } else {*/
                                $replace_html .= '<tr ' . $style . '>';
                            //}

                            // If bundled product, get the list of sub products
                            if( isset( $v->product_type ) && 'bundle' == $v->product_type && isset( $product->bundle_data ) && is_array( $product->bundle_data ) && count( $product->bundle_data ) > 0 ) {
                                foreach ( $product->bundle_data as $b_key => $b_value ) {
                                    $bundle_child[] = $b_key;
                                }
                            }
                            // Check if the product is a part of the bundles product, if yes, set qty and totals to blanks
                            if( isset( $bundle_child ) && count( $bundle_child ) > 0 ) {
                                if ( in_array( $v->product_id, $bundle_child ) ) {
                                    $item_subtotal_display = $item_price = $quantity = '';
                                }
                            }

                            $wcap_all_product_names = $prod_name . ", " . $wcap_all_product_names ;

                            $wcap_all_product_images = $image_url . ", " . $wcap_all_product_images ;

                            $wcap_all_product_price  = $item_price . ", " . $wcap_all_product_price ;
                            
                            $wcap_all_product_qty    = $quantity . ", " . $wcap_all_product_qty ;

                            $wcap_all_product_sub_total    = $item_subtotal_display . ", " . $wcap_all_product_sub_total ;

                            if( $custom_table === true ){
                                $custom_replace .= $custom_tr;
                                $custom_replace = str_ireplace( "{{item.name}}", $prod_name, $custom_replace );
                                $custom_replace = str_ireplace( "{{item.image}}", $image_url, $custom_replace );
                                $custom_replace = str_ireplace( "{{item.price}}", $item_price, $custom_replace );
                                $custom_replace = str_ireplace( "{{item.quantity}}", $quantity, $custom_replace );
                                $custom_replace = str_ireplace( "{{item.subtotal}}", $item_subtotal_display, $custom_replace );
                            }
                            
                            foreach( $position_array as $k => $v ) {
                                switch( $v ) {
                                    case 'image':
                                        $replace_html .= '<td style="text-align:center;"> <a href="' . $checkout_link_track . '">' . $image_url . '</a> </td>';
                                        break;
                                    case 'name':
                                        $replace_html .= '<td style="text-align:center;"> <a href="' . $checkout_link_track . '">' . $prod_name . '</a> </td>';
                                        break;
                                    case 'price':
                                        if ( $item_price == '' ) {
                                            $replace_html .= '<td></td>';
                                        }
                                        else {
                                            $replace_html .= '<td style="text-align:center;">' . $item_price . '</td>';
                                        }
                                        break;
                                    case 'quantity':
                                        $replace_html .= '<td style="text-align:center;">' . $quantity . '</td>';
                                        break;
                                    case 'subtotal':
                                        if ( $item_subtotal_display == '' ) {
                                            $replace_html .= '<td></td>';
                                        }
                                        else {
                                            $replace_html .= '<td style="text-align:center;">' . $item_subtotal_display . '</td>';
                                        }
                                        break;
                                    default:
                                        $replace_html .= '<td></td>';
                                }
                            }
                            $replace_html .= '</tr>';
                        } else {
                            $replace_html .= '<tr > <td colspan="5"> Product you had added to cart is currently unavailable. Please choose another product from <a href="'.get_option( 'siteurl' ).'">'.get_option( 'blogname' ).'</a> </td> </tr>';
                            $after_item_subtotal_display = $wcap_line_subtotal_tax = '';
                        }

                        $i++;
                    }

                    $count_columns = count( $position_array ) - 2;

                    if( isset($after_item_subtotal_display) && $after_item_subtotal_display > 0 ) {
                        $after_item_subtotal_display = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $after_item_subtotal_display, $currency ), $abandoned_cart_ids_value, $after_item_subtotal_display, 'wcap_manual_mail' );
            
                        $replace_html .= '<tr>';
                        if ( count( $position_array ) > 2 ) {
                            for( $count_c = 1; $count_c <= $count_columns; $count_c++ ){
                                $replace_html .= '<td></td>';
                            }

                        }
                        $replace_html .= '<td>'.__( "<strong>Coupon: $wcap_used_coupon</strong>", "woocommerce-ac" ).'</td>
                                        <td> - '. $after_item_subtotal_display .'</td>
                                    </tr>';
                    }
                    $show_taxes = apply_filters('wcap_show_taxes', true);

                    if( $show_taxes && isset($wcap_include_tax) && $wcap_include_tax == 'no' &&
                        isset($wcap_include_tax_setting) && $wcap_include_tax_setting == 'yes' && 
                        ( preg_match( "{{cart.total}}", $email_body, $matched ) ) ) {

                        $wcap_line_subtotal_tax = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $line_subtotal_tax, $currency ), $abandoned_cart_ids_value, $line_subtotal_tax, 'wcap_cron' );

                        $replace_html .= '<tr>';
                        if ( count( $position_array ) > 2 ) {
                            for( $count_c = 1; $count_c <= $count_columns; $count_c++ ){
                                $replace_html .= '<td></td>';
                            }

                        }
                        $replace_html .= '<td>'.__( "<strong>Tax:</strong>", "woocommerce-ac" ).'</td>
                            <td> '. $wcap_line_subtotal_tax .'</td>
                        </tr>';

                        /*$line_subtotal_tax =  apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $line_subtotal_tax, $currency ), $abandoned_cart_ids_value, $line_subtotal_tax, 'wcap_manual_mail' );

                        $replace_html .= '<tr>
                                            <td> </td>
                                            <td> </td>
                                            <td> </td>
                                            <td>' . __( "<strong>Tax:</strong>", "woocommerce-ac" ) . '</td>
                                            <td>' . $line_subtotal_tax . '</td>
                                        </tr>';*/
                    }
                    // Calculate the cart total
                     if( isset( $wcap_include_tax ) && $wcap_include_tax == 'yes' &&
                        isset( $wcap_include_tax_setting ) && 'yes' == $wcap_include_tax_setting ) {
                        $cart_total = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $cart_total, $currency ), $abandoned_cart_ids_value, $cart_total, 'wcap_manual_mail' );
                        $line_subtotal_tax_display = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $line_subtotal_tax_display, $currency ), $abandoned_cart_ids_value, $line_subtotal_tax_display, 'wcap_manual_mail' ); 
                        if ($show_taxes) { 

                        $cart_total =  $cart_total . ' (' . __( "includes Tax: " , "woocommerce-ac" ) . $line_subtotal_tax_display . ')';
                        } else {
                            $cart_total =  $cart_total; 
                        }
                    } elseif( isset( $wcap_include_tax ) && $wcap_include_tax == 'no' &&
                        isset( $wcap_include_tax_setting ) && 'yes' == $wcap_include_tax_setting ) {

                        $display_cart_total = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $display_cart_total, $currency ), $abandoned_cart_ids_value, $display_cart_total, 'wcap_manual_mail' );
                        $cart_total = $display_cart_total;
                    } else {

                        $cart_total = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $cart_total, $currency ), $abandoned_cart_ids_value, $cart_total, 'wcap_manual_mail' );
                    }

                    $wcap_all_product_names = substr( $wcap_all_product_names, 0, -2 );

                    $wcap_all_product_images = substr( $wcap_all_product_images, 0, -2 );

                    $wcap_all_product_price = substr( $wcap_all_product_price, 0, -2 );

                    $wcap_all_product_qty = substr( $wcap_all_product_qty, 0, -2 );

                    $wcap_all_product_sub_total = substr( $wcap_all_product_sub_total, 0, -2 );

                    // Populate/Add the product rows
                    $email_body     = str_ireplace( $check_html, $replace_html, $email_body );

                    if ( $custom_table === true ) {
                        $email_body = str_ireplace( $custom_tr, $custom_replace, $email_body );
                    }

                    // Populate the cart total
                    $email_body     = str_ireplace( "{{cart.total}}", $cart_total, $email_body );
                    $email_body     = str_ireplace( "{{item.name}}", $wcap_all_product_names, $email_body );
                    $replace_image  = $image_url;
                    $email_body     = str_ireplace( "{{item.image}}", $wcap_all_product_images, $email_body );
                    $email_body     = str_ireplace( "{{item.price}}", $wcap_all_product_price, $email_body );
                    $email_body     = str_ireplace( "{{item.quantity}}", $wcap_all_product_qty, $email_body );
                    $email_body     = str_ireplace( "{{item.subtotal}}", $wcap_all_product_sub_total, $email_body );
                }
                // Up Sell and Cross Sell Data.
                $email_settings = array(
                    'image_height'  => $wcap_product_image_height,
                    'image_width'   => $wcap_product_image_width,
                    'site_url'      => get_option( 'siteurl' ),
                    'email_sent_id' => $email_sent_id
                );
                $email_body = Wpuac_Send_Email_Using_Cron::wcap_replace_upsell_data( $email_body, $cart_details, $email_settings );
                $email_body = Wpuac_Send_Email_Using_Cron::wcap_replace_crosssell_data( $email_body, $cart_details, $email_settings );

                if( $woocommerce->version < '2.3' ) {
                    $cart_page_link = $woocommerce->cart->get_cart_url();
                } else {
                    $cart_page_id   = wc_get_page_id( 'cart' );
                    $cart_page_link = $cart_page_id ? get_permalink( $cart_page_id ) : '';
                }

                if( function_exists( 'icl_register_string' ) ) {
                    if( 'en' == $selected_lanaguage ) {
                        $cart_page_link = $cart_page_link;
                    } else {
                        $cart_page_link = apply_filters( 'wpml_permalink', $cart_page_link, $selected_lanaguage );
                    }
                }
                $email_body    = str_ireplace( "{{checkout.link}}", $checkout_link_track, $email_body );
                $encoding_cart = $email_sent_id . '&url=' . $cart_page_link ;
                $validate_cart = Wcap_Common::encrypt_validate( $encoding_cart );

                if( isset( $coupon_code_to_apply ) && $coupon_code_to_apply != '' ) {
                    $encypted_coupon_code = Wcap_Common::encrypt_validate( $coupon_code_to_apply );
                    $cart_link_track = get_option( 'siteurl' ) . '/?wacp_action=track_links&validate=' . $validate_cart . '&c=' . $encypted_coupon_code;
                } else {
                    $cart_link_track = get_option( 'siteurl' ) . '/?wacp_action=track_links&validate=' . $validate_cart;
                }
                $email_body                    = str_ireplace( "{{cart.link}}", $cart_link_track, $email_body );
                $validate_unsubscribe          = Wcap_Common::encrypt_validate( $email_sent_id );
                $email_sent_id_address         = $results_sent[0]->sent_email_id;;
                $encrypt_email_sent_id_address = hash( 'sha256', $email_sent_id_address );
                $plugins_url                   = get_option( 'siteurl' ) . "/?wcap_track_unsubscribe=wcap_unsubscribe&validate=" . $validate_unsubscribe . "&track_email_id=" . $encrypt_email_sent_id_address;
                $unsubscribe_link_track        = $plugins_url;

                $email_body                    = str_ireplace( "{{cart.unsubscribe}}" , $unsubscribe_link_track , $email_body );
                $plugins_url_track_image       = get_option( 'siteurl' ) . '/?wcap_track_email_opens=wcap_email_open&email_id=';
                $hidden_image                  = '<img style="border:0px; height: 1px; width:1px; position:absolute; visibility:hidden;" alt="" src="' . $plugins_url_track_image . $email_sent_id . '" >';
                $email_body                    = str_ireplace( "{{email_open_tracker}}" , $hidden_image , $email_body );
                $user_email                    = $value->user_email;

                $email_body = str_ireplace ( "My document title", "", $email_body );

                if( isset( $is_wc_template ) && "on" == $is_wc_template ){

                    ob_start();

                    wc_get_template( 'emails/email-header.php', array( 'email_heading' => $wc_template_header ) );
                    $email_body_template_header = ob_get_clean();

                    ob_start();

                    wc_get_template( 'emails/email-footer.php' );
                    $email_body_template_footer = ob_get_clean();
                    $email_body_template_footer = str_ireplace( '{site_title}', get_option( 'blogname' ), $email_body_template_footer );
                    $final_email_body =  $email_body_template_header . $email_body . $email_body_template_footer;

                    Wcap_Common::wcap_add_wc_mail_header();
                    wc_mail( $user_email, stripslashes( $email_subject ), stripslashes( $final_email_body ) , $headers );
                    Wcap_Common::wcap_remove_wc_mail_header();
                } else {
                    Wcap_Common::wcap_add_wp_mail_header();
                    wp_mail( $user_email, stripslashes( $email_subject ), stripslashes( $email_body ), $headers );
                    Wcap_Common::wcap_remove_wc_mail_header();
                }

                $update_manual_data = "UPDATE `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` SET manual_email = 'YES' WHERE id = '".$abandoned_cart_ids_value."' ";
                $wpdb->query( $update_manual_data );
            }
        }

        wp_safe_redirect( admin_url( '/admin.php?page=woocommerce_ac_page&action=listcart&wcap_manual_email_sent=YES' ) );
    }

    /**
     * This function will return the email address of the customer for the cart. As we have given the choice to the admin that he can choose who will receive the email template.
     *
     * @param int $wcap_user_id user ID
     * @param String $wcap_user_type User Type
     * @return String $wcap_email_address Email Address
     * @since 4.2
     */
    public static function wcap_get_customers_email( $wcap_user_id, $wcap_user_type ) {
        global $wpdb;

        $wcap_email_address = '';
        if( "GUEST" == $wcap_user_type && '0' != $wcap_user_id ) {
                
            $query_guest        = "SELECT billing_first_name, billing_last_name, email_id FROM `" . WCAP_GUEST_CART_HISTORY_TABLE . "` WHERE id = %d";
            $results_guest      = $wpdb->get_results( $wpdb->prepare( $query_guest, $wcap_user_id ) );
            if( count( $results_guest ) > 0 ) {
                $wcap_email_address = $results_guest[0]->email_id;
            }
        } else if( "GUEST" != $wcap_user_type && '0' != $wcap_user_id ) { 
            $key                = 'billing_email';
            $single             = true;
            $user_billing_email = get_user_meta( $wcap_user_id, $key, $single );
            if( isset( $user_billing_email ) && $user_billing_email != '' ) {
               $wcap_email_address = $user_billing_email;
           } else {
                $user_data  = get_userdata( $wcap_user_id );
                if ( isset( $user_data->user_email ) && '' != $user_data->user_email ) {
                    $wcap_email_address = $user_data->user_email;
                }
            }
        }
        return $wcap_email_address;
    }

    /**
     * This function will return the phone number of the customer for the cart.
     *
     * @param int $wcap_user_id user ID
     * @param String $wcap_user_type User Type
     * @return Int|String $wcap_customer_phone Phone number
     * @since  4.2
     */
    public static function wcap_get_customers_phone( $wcap_user_id, $wcap_user_type ) {
        global $wpdb;

        $wcap_customer_phone = '';
        if( "GUEST" == $wcap_user_type && '0' != $wcap_user_id ) {
                
            $get_phone_query_guest = "SELECT phone FROM `" . WCAP_GUEST_CART_HISTORY_TABLE . "` WHERE id = %d";
            $results_guest         = $wpdb->get_results( $wpdb->prepare( $get_phone_query_guest, $wcap_user_id ) );
            if( count( $results_guest ) > 0 ) {
                $wcap_customer_phone = $results_guest[0]->phone;
            }
        } else if( "GUEST" != $wcap_user_type && '0' != $wcap_user_id ) { 
            $user_phone_number = get_user_meta( $wcap_user_id, 'billing_phone' );
            if( isset( $user_phone_number[0] ) ) {
                $wcap_customer_phone = $user_phone_number[0];
            }
        }
        return $wcap_customer_phone;
    }

    /**
     * As we have given the choice to admin, that he can send email template to any one. So we need to check each email address is in correct format or not. If any one of the email address is correct then send the email. We are having the multiple email address in the comma seprated so we need to check validate email format.
     *
     * @param String $wcap_email_address Email Address of abandoned cart's user
     * @return String $validated_value Validate email address
     * @since  4.2
     */
    public static function wcap_validate_email_format( $wcap_email_address ) {

        if ( version_compare( phpversion(), '5.2.0', '>=') ) {
            $validated_value = filter_var( $wcap_email_address, FILTER_VALIDATE_EMAIL );

            if ( $validated_value == $wcap_email_address ){
                $validated_value = 1;
            }else{
                $validated_value = 0;
            }
        } else{
            $pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';
            $validated_value = preg_match( $pattern, $wcap_email_address ) ;
        }
        return $validated_value;
    }
    /**
     * If the email tempalte will have the merge tag {{coupon.code}} and parent coupon code then our plugin will generate the coupon code. This function is used for getting the functionality of parent coupon code for the unique generated coupon code. Also, it will update coupon details in post_meta table.
     *
     * @param int $discount_amt coupon amount for default email template
     * @param string $get_discount_type Discount type
     * @param string $get_expiry_date Coupon expiry date
     * @param string $coupon_post_meta Retrieve post meta field
     * @return String $final_string Coupon code
     * @since 4.2
     */
    public static function wp_coupon_code( $discount_amt, $get_discount_type, $get_expiry_date, $discount_shipping = 'no', $coupon_post_meta ) {
        $ten_random_string = Wcap_Send_Manual_Email::wp_random_string();
        $first_two_digit   = rand( 0, 99 );
        $final_string      = $first_two_digit.$ten_random_string;
        $datetime          = $get_expiry_date ; //date( "Y-m-d", strtotime( date( 'Y-m-d' )." +7 days" ) );
        $coupon_code       = $final_string;

        $coupon_product_categories         = isset( $coupon_post_meta['product_categories'] [ 0 ] ) && $coupon_post_meta['product_categories'] [ 0 ] != '' ? unserialize( $coupon_post_meta['product_categories'] [ 0 ] )  : array();
        $coupon_exculde_product_categories = isset( $coupon_post_meta['exclude_product_categories'] [ 0 ] ) && $coupon_post_meta['exclude_product_categories'] [ 0 ] != '' ? unserialize ( $coupon_post_meta['exclude_product_categories'] [ 0 ] ) : array();
        $coupon_product_ids                = isset( $coupon_post_meta['product_ids'] [ 0 ] ) && $coupon_post_meta['product_ids'] [ 0 ] != '' ? $coupon_post_meta['product_ids'] [ 0 ] : '';
        $coupon_exclude_product_ids        = isset( $coupon_post_meta['exclude_product_ids'] [ 0 ] ) && $coupon_post_meta['exclude_product_ids'] [ 0 ] != '' ? $coupon_post_meta['exclude_product_ids'] [ 0 ] : '';
        $individual_use                    = 'yes'; // we always want to create coupons that can be used only once
        $coupon_free_shipping              = isset( $coupon_post_meta['free_shipping'] [ 0 ] ) && $coupon_post_meta['free_shipping'] [ 0 ] != '' ? $coupon_post_meta['free_shipping'] [ 0 ] : $discount_shipping;
        $coupon_minimum_amount             = isset( $coupon_post_meta['minimum_amount'] [ 0 ] ) && $coupon_post_meta['minimum_amount'] [ 0 ] != '' ? $coupon_post_meta['minimum_amount'] [ 0 ] : '';
        $coupon_maximum_amount             = isset( $coupon_post_meta['maximum_amount'] [ 0 ] ) && $coupon_post_meta['maximum_amount'] [ 0 ] != '' ? $coupon_post_meta['maximum_amount'] [ 0 ] : '';
        $coupon_exclude_sale_items         = isset( $coupon_post_meta['exclude_sale_items'] [ 0 ] ) && $coupon_post_meta['exclude_sale_items'] [ 0 ] != '' ? $coupon_post_meta['exclude_sale_items'] [ 0 ] : 'no';
        $use_limit                         = isset( $coupon_post_meta['usage_limit'] [ 0 ] ) && $coupon_post_meta['usage_limit'] [ 0 ] != '' ? $coupon_post_meta['usage_limit'] [ 0 ] : '';
        $use_limit_user                    = isset( $coupon_post_meta['usage_limit_per_user'] [ 0 ] ) && $coupon_post_meta['usage_limit_per_user'] [ 0 ] != '' ? $coupon_post_meta['usage_limit_per_user'] [ 0 ] : '';

        if( class_exists( 'WC_Free_Gift_Coupons' ) ) {
            $free_gift_coupon              = isset( $coupon_post_meta['gift_ids'] [ 0 ] ) && $coupon_post_meta['gift_ids'] [ 0 ] != '' ? $coupon_post_meta['gift_ids'] [ 0 ] : '';
            $free_gift_shipping            = isset( $coupon_post_meta['free_gift_shipping'] [ 0 ] ) && $coupon_post_meta['free_gift_shipping'] [ 0 ] != '' ? $coupon_post_meta['free_gift_shipping'] [ 0 ] : 'no';
        }
        if( is_plugin_active( 'yith-woocommerce-brands-add-on/init.php' ) ) {
            $coupon_brand = isset( $coupon_post_meta['brand'] [ 0 ] ) && $coupon_post_meta['brand'] [ 0 ] != '' ? unserialize ( $coupon_post_meta['brand'] [ 0 ] ) : array();
        }
        $amount        = $discount_amt;
        $discount_type = $get_discount_type; // Type: fixed_cart, percent, fixed_product, percent_product
        $coupon        = apply_filters( 'wcap_manual_before_shop_coupon_create',
                                        array(
                                            'post_title'   => $coupon_code,
                                            'post_content' => 'This coupon provides 5% discount on cart price.',
                                            'post_status'  => 'publish',
                                            'post_author'  => 1,
                                            'post_type'    => 'shop_coupon',
                                            'post_expiry_date' => $datetime,
                                        )
                                    );
        $new_coupon_id = wp_insert_post( $coupon );
        // Add meta
        update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
        update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
        update_post_meta( $new_coupon_id, 'minimum_amount', $coupon_minimum_amount );
        update_post_meta( $new_coupon_id, 'maximum_amount', $coupon_maximum_amount );
        update_post_meta( $new_coupon_id, 'individual_use', $individual_use );
        update_post_meta( $new_coupon_id, 'free_shipping', $coupon_free_shipping );
        update_post_meta( $new_coupon_id, 'product_ids', '' );
        update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
        update_post_meta( $new_coupon_id, 'usage_limit', $use_limit );
        update_post_meta( $new_coupon_id, 'usage_limit_per_user', $use_limit_user );
        update_post_meta( $new_coupon_id, 'date_expires', $datetime );
        update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
        update_post_meta( $new_coupon_id, 'product_ids', $coupon_product_ids );
        update_post_meta( $new_coupon_id, 'exclude_sale_items', $coupon_exclude_sale_items );
        update_post_meta( $new_coupon_id, 'exclude_product_ids', $coupon_exclude_product_ids );
        update_post_meta( $new_coupon_id, 'product_categories', $coupon_product_categories );
        update_post_meta( $new_coupon_id, 'exclude_product_categories', $coupon_exculde_product_categories );
        update_post_meta( $new_coupon_id, 'wcap_created_by', "wcap" );

        if ( class_exists( 'WC_Free_Gift_Coupons' ) ) {
            update_post_meta( $new_coupon_id, 'gift_ids', $free_gift_coupon );
            update_post_meta( $new_coupon_id, 'free_gift_shipping', $free_gift_shipping );
        }
        if ( is_plugin_active( 'yith-woocommerce-brands-add-on/init.php' ) ) {
            update_post_meta( $new_coupon_id, 'brand', $coupon_brand );
        }

        return $final_string;
    }
    /**
     * It is used for generating random coupon code which will send in the abandoned cart reminder email.
     *
     * @return array $temp_array Coupon code
     * @since 4.2
     */
    public static function wp_random_string() {
        $character_set_array   = array();
        $character_set_array[] = array( 'count' => 5, 'characters' => 'abcdefghijklmnopqrstuvwxyz' );
        $character_set_array[] = array( 'count' => 5, 'characters' => '0123456789' );
        $temp_array            = array();
        foreach ( $character_set_array as $character_set ) {
            for ( $i = 0; $i < $character_set['count']; $i++ ) {
                $temp_array[] = $character_set['characters'][ rand( 0, strlen( $character_set['characters'] ) - 1 ) ];
            }
        }
        shuffle( $temp_array );
        return implode( '', $temp_array );
    }
}
