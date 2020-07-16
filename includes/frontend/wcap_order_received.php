<?php
/**
 * It will update the abadoned carts to recovered cart when customer reached the order received page.
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @since 5.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if ( !class_exists('Wcap_Order_Received' ) ) {

    /**
     * It will update the abadoned carts to recovered cart when customer reached the order received page.
     */
    class Wcap_Order_Received {
        /**
         * It will update the abadoned carts to recovered cart when customer reached the order received page.
         * If customer had placed the order after cut off time and reached the order recived page then it will also delete the abandoned cart if the order status is not pending or failed.
         * @hook woocommerce_order_details_after_order_table
         * @param object | array $order Order details
         * @since 5.0
         */
        public static function  wcap_action_after_delivery_session( $order ) {

            $order_id= Wcap_Common::wcap_get_ordrer_id( $order );

            $wcap_order = new WC_Order( $order_id );
            $wcap_get_order_status = $wcap_order->get_status();
            
            $get_abandoned_id_of_order  = get_post_meta( $order_id, 'wcap_recover_order_placed', true );
            $get_sent_email_id_of_order = get_post_meta( $order_id, 'wcap_recover_order_placed_sent_id', true );


            if ( isset( $get_sent_email_id_of_order ) && '' != $get_sent_email_id_of_order ) {
                /* When Placed order button is clicked, we create post meta for that order.
                If that meta is found then update our plugin table for recovered cart */
                wcap_common::wcap_updated_recovered_cart( $get_abandoned_id_of_order, $order_id, $get_sent_email_id_of_order, $order );
            } else if ( '' != $get_abandoned_id_of_order && isset( $get_abandoned_id_of_order )  ){
                /* if order status is not pending or failed then we  will delete the abandoned cart record.
                   post meta will be created only if the cut off time has been reached.
                */
                Wcap_Order_Received::wcap_delete_abanadoned_data_on_order_status( $order_id , $get_abandoned_id_of_order, $wcap_get_order_status );
            }

            if ( wcap_get_cart_session( 'wcap_selected_language' ) !='' && function_exists('icl_register_string' ) ) {
                wcap_unset_cart_session( 'wcap_selected_language' );
            }

            if ( wcap_get_cart_session( 'wcap_email_sent_id' ) != ''  ) {
                wcap_unset_cart_session( 'wcap_email_sent_id' );
            }

            // Irrespective of the status, add the ATC coupon details.
            if ( $order_id > 0 && $get_abandoned_id_of_order > 0 ) {
                $coupon_details = get_post_meta( $get_abandoned_id_of_order, '_woocommerce_ac_coupon', true );

                $details_added = isset( $coupon_details['order_atc_details_added'] ) && $coupon_details['order_atc_details_added'] ? true : false;

                if ( is_array( $coupon_details ) && isset( $coupon_details['atc_coupon_code'] ) && '' !== $coupon_details['atc_coupon_code'] && ! $details_added ) {
                    $coupon_code = $coupon_details['atc_coupon_code']; // Get the coupon name.
                    $order = wc_get_order( $order_id ); // WC Order.
                    
                    // Add post meta record & Note.
                    update_post_meta( $order_id, 'wcap_atc_coupon', $coupon_code );
                    $order->add_order_note( "ATC Coupon $coupon_code was used.", 'woocommerce-ac' );
                    $coupon_details['order_atc_details_added'] = true;
                    update_post_meta( $get_abandoned_id_of_order, '_woocommerce_ac_coupon', $coupon_details );
                }
            }
        }

        /**
         * If customer had placed the order after cut off time and reached the order recived page then it will also delete the abandoned cart if the order status is not pending or failed.
         * @param int | string $order_id Order id
         * @param int | string $get_abandoned_id_of_order Abandoned cart id
         * @param string $wcap_get_order_status Order status
         * @globals mixed $wpdb
         * @globals mixed $woocommerce
         * @since 5.0
         */
        public static function wcap_delete_abanadoned_data_on_order_status( $order_id, $get_abandoned_id_of_order, $wcap_get_order_status ) {

            global $wpdb, $woocommerce;
            

            if ( 'pending' != $wcap_get_order_status || 'failed' != $wcap_get_order_status ) {
                if ( isset( $get_abandoned_id_of_order ) && '' != $get_abandoned_id_of_order ){
                  
                  $get_abandoned_cart_user_id_query   = "SELECT user_id FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE id = %d ";
                  $get_abandoned_cart_user_id_results = $wpdb->get_results( $wpdb->prepare( $get_abandoned_cart_user_id_query, $get_abandoned_id_of_order ) );

                  if ( count( $get_abandoned_cart_user_id_results ) > 0 ){
                    $wcap_user_id = $get_abandoned_cart_user_id_results[0]->user_id;

                    if ( $wcap_user_id >= 63000000 ){
                      $wpdb->delete( WCAP_GUEST_CART_HISTORY_TABLE ,   array( 'id' => $wcap_user_id ) );
                    }

                    $wpdb->delete( WCAP_ABANDONED_CART_HISTORY_TABLE , array( 'id' => $get_abandoned_id_of_order ) );
                    delete_post_meta( $order_id,  'wcap_recover_order_placed', $get_abandoned_id_of_order );
                  }
                }
                // delete the cart ID from the list of carts to which SMS reminders will be sent.
                Wcap_Common::wcap_delete_cart_notification( $get_abandoned_id_of_order );
            }
        }
    }
}
