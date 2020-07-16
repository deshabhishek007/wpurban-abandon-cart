<?php
/**
 * It will capture the coupon codes and apply the coupon code directly when ac reminder emails have the coupon.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Frontend/Coupon
 * @since 5.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Coupon' ) ) {
    /**
     * It will capture the coupon codes and apply the coupon code directly when ac reminder emails have the coupon
     */
    class Wcap_Coupon{

        /**
         * It will captures the coupon code used by the customers.
         * It will store the coupon code for the specific abandoned cart.
         * @hook woocommerce_applied_coupon
         * @param string $valid Coupon code
         * @return string $valid Coupon code
         * @globals mixed $wpdb
         * @since 2.4.3
         */
        public static function wcap_capture_applied_coupon( $valid ) {

            global $wpdb;

            $coupon_code = wcap_get_cart_session( 'wcap_c' );

            $user_id = wcap_get_cart_session( 'wcap_user_id' );

            $user_id = $user_id != '' ? $user_id : get_current_user_id();

            if ( $coupon_code == '' && isset( $_POST['coupon_code'] ) ){
                $coupon_code = $_POST['coupon_code'];
            } else if ( isset( $valid ) ){
                $coupon_code = $valid;
            }

            if ( $valid != '' ) {
                if ( is_user_logged_in()){

                    $abandoned_cart_id_query   = "SELECT id FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE user_id = %d AND cart_ignored = '0' AND recovered_cart = '0'";
                    $abandoned_cart_id_results = $wpdb->get_results( $wpdb->prepare( $abandoned_cart_id_query, $user_id ) );
                }else if ( ! is_user_logged_in()){
                        $wcap_get_cookie_id        =  Wcap_Common::wcap_get_guest_session_key();
                        $abandoned_cart_id_query   = "SELECT id FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE user_id = %d AND cart_ignored = '0' AND recovered_cart = '0' ORDER BY id DESC LIMIT 1";
                        $abandoned_cart_id_results = $wpdb->get_results( $wpdb->prepare( $abandoned_cart_id_query, $user_id ) );
                }

                $abandoned_cart_id         = '0';
                if( isset( $abandoned_cart_id_results ) && !empty( $abandoned_cart_id_results ) ) {
                    $abandoned_cart_id = $abandoned_cart_id_results[0]->id;
                }
                $existing_coupon = ( get_user_meta( $user_id, '_woocommerce_ac_coupon', true ) );
                    if ( is_array( $existing_coupon ) && count( $existing_coupon ) > 0 ) {
                        foreach ( $existing_coupon as $key => $value ) {
                            if ( $existing_coupon[$key]['coupon_code'] != $coupon_code ) {
                                $existing_coupon[]      = array ( 'coupon_code' => $coupon_code, 'coupon_message' => __( 'Discount code applied successfully.', 'woocommerce-ac' ) );
                                $post_meta_coupon_array = array ( 'coupon_code' => $coupon_code, 'coupon_message' => __( 'Discount code applied successfully.', 'woocommerce-ac' ) );
                                update_user_meta( $user_id, '_woocommerce_ac_coupon', $existing_coupon );
                                if( $user_id > 0 || ! is_user_logged_in() ) {
                                    update_post_meta( $abandoned_cart_id, '_woocommerce_ac_coupon', $post_meta_coupon_array );
                                }
                            return $valid;
                        }
                    }
                } else {
                    $coupon_details[]       = array ( 'coupon_code' => $coupon_code, 'coupon_message' => __( 'Discount code applied successfully.', 'woocommerce-ac' ) );
                    $post_meta_coupon_array = array ( 'coupon_code' => $coupon_code, 'coupon_message' => __( 'Discount code applied successfully.', 'woocommerce-ac' ) );
                    update_user_meta( $user_id, '_woocommerce_ac_coupon', $coupon_details );

                    if( $user_id > 0  || ! is_user_logged_in() ) {
                        update_post_meta( $abandoned_cart_id, '_woocommerce_ac_coupon', $post_meta_coupon_array );
                    }
                    return $valid;
                }
            }

            return $valid;
        }

        /**
         * It will captures the coupon code errors specific to the abandoned carts.
         * @hook woocommerce_coupon_error
         * @param string $valid Error
         * @param string $new Error code
         * @globals mixed $wpdb
         * @return string $valid Error
         * @since 2.4.3
         */
        public static function wcap_capture_coupon_error( $valid, $new ) {

            global $wpdb;
            $coupon_code = wcap_get_cart_session( 'wcap_c' );

            $user_id = wcap_get_cart_session( 'wcap_user_id' );

            $user_id = $user_id != '' ? $user_id : get_current_user_id();

            $abandoned_cart_id_query   = "SELECT id FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE user_id = %d AND cart_ignored = '0' AND recovered_cart = '0' ORDER BY id DESC LIMIT 1";
            $abandoned_cart_id_results = $wpdb->get_results( $wpdb->prepare( $abandoned_cart_id_query, $user_id ) );
            $abandoned_cart_id         = '0';

            if( isset( $abandoned_cart_id_results ) && count( $abandoned_cart_id_results ) > 0 ) {
                $abandoned_cart_id = $abandoned_cart_id_results[0]->id;
            }
            
            if( $coupon_code == '' && isset( $_POST['coupon_code'] ) ) {
                $coupon_code = $_POST['coupon_code'];
            }

            if ( '' != $coupon_code ) {
                $existing_coupon        = get_user_meta( $user_id, '_woocommerce_ac_coupon', false );
                $existing_coupon[]      = array ( 'coupon_code' => $coupon_code, 'coupon_message' => $valid );
                $post_meta_coupon_array = array ( 'coupon_code' => $coupon_code, 'coupon_message' => $valid );
                if ( $user_id > 0 ) {
                    update_post_meta( $abandoned_cart_id, '_woocommerce_ac_coupon', $post_meta_coupon_array );
                }
                update_user_meta( $user_id, '_woocommerce_ac_coupon', $existing_coupon );
            }
            return $valid;
        }
        /**
         * It will directly apply the coupon code if the coupon code present in the abandoned cart reminder email link.
         * It will apply direct coupon on cart and checkout page.
         * @hook woocommerce_before_cart_table
         * @hook woocommerce_before_checkout_form
         * @param string $coupon_code Name of coupon
         * @since 2.4.3
         */
        public static function wcap_apply_direct_coupon_code( $coupon_code ) {

            remove_action( 'woocommerce_cart_updated', array( 'Wcap_Cart_Updated', 'wcap_store_cart_timestamp' ) );

            $wcap_language = wcap_get_cart_session( 'wcap_selected_language' );
            if ( $wcap_language != '' && function_exists( 'icl_register_string' ) ) {
                global $sitepress;
                $sitepress->switch_lang( $wcap_language );
            }

            $coupon_code = wcap_get_cart_session( 'wcap_c' );

            if ( $coupon_code != '' ) {
                global $woocommerce;

                // If coupon has been already been added remove it.
                if ( $woocommerce->cart->has_discount( sanitize_text_field( $coupon_code ) ) ) {
                    if ( !$woocommerce->cart->remove_coupons( sanitize_text_field( $coupon_code ) ) ) {
                        wc_print_notices( );
                    }
                }
                // Add coupon
                if ( !$woocommerce->cart->add_discount( sanitize_text_field( $coupon_code ) ) ) {
                    wc_print_notices( );
                } else {
                    wc_print_notices( );
                }
                // Manually recalculate totals.  If you do not do this, a refresh is required before user will see updated totals when discount is removed.
                $woocommerce->cart->calculate_totals();
                // need to clear the coupon code from session
                wcap_unset_cart_session( 'wcap_c' );
            }
       }
    }
}