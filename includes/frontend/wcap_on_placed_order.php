<?php
/**
 * It will delete the abandoned cart if order is placed before the cutoff time.
 * It will also, create the post meta for the abandoned cart. It will create after the cutoff time.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Frontend/Place-Order
 * @since 5.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if ( !class_exists('WCAP_On_Placed_Order' ) ) {
    /**
     * It will delete the abandoned cart if order is placed before the cutoff time.
     * It will also, create the post meta for the abandoned cart. It will create after the cutoff time.
     * Also, when order status is changes it will check if abandoned cart needs to delete or keep it.
     */
  class WCAP_On_Placed_Order {
    /**
     * It will delete the abandoned cart if order is placed before the cutoff time.
     * It will also, create the post meta for the abandoned cart. It will create after the cutoff time.
     * This post meta contain the abandoned cart id and if the email is sent to that cart then the email sent id.
     * @hook woocommerce_checkout_order_processed
     * @param int | string $order_id Order id
     * @globals mixed $wpdb
     * @since 5.0
     */
    public static function wcap_order_placed( $order_id ) {

        global $wpdb;
        $email_sent_id         = wcap_get_cart_session( 'wcap_email_sent_id' );
        $abandoned_order_id    = wcap_get_cart_session( 'wcap_abandoned_id' );
        $wcap_user_id_of_guest = wcap_get_cart_session( 'wcap_user_id' );

        // if user becomes the registered user
        $guest_turned_registered = false;
        if ( isset( $_POST['account_password'] ) && $_POST['account_password'] != '' ) { // guest logged in on the Checkout page
            $guest_turned_registered = true;
        } else if( isset( $_POST['createaccount'] ) && $_POST['createaccount'] != '' ) { // guest created an account at Checkout
            $guest_turned_registered = true;
        } else if( !isset( $_POST[ 'createaccount' ] ) && 'no' == get_option( 'woocommerce_enable_guest_checkout', 'no' ) ) { // Guest Checkouts are not allowed, user registration is forced (Subscriptions)
            $guest_turned_registered = true;
        }
        
        if( $email_sent_id != '' && $email_sent_id > 0 ) { // recovered cart
        
            if( $abandoned_order_id == '' || $abandoned_order_id == false ) {
            
                $get_ac_id_query    = "SELECT abandoned_order_id FROM `" . WCAP_EMAIL_SENT_HISTORY_TABLE ."` WHERE id = %d";
                $get_ac_id_results  = $wpdb->get_results( $wpdb->prepare( $get_ac_id_query, $email_sent_id ) );
            
                $abandoned_order_id = $get_ac_id_results[0]->abandoned_order_id;
            }

            add_post_meta( $order_id , 'wcap_recover_order_placed_sent_id', $email_sent_id );
            add_post_meta( $order_id , 'wcap_recover_order_placed', $abandoned_order_id );
            
        }

        if ( $abandoned_order_id != '' && $guest_turned_registered && $wcap_user_id_of_guest != '' ) {

            if( version_compare( WC()->version, '3.7.0', '>=' ) ) {
                $update_id = $wpdb->update(
                    WCAP_ABANDONED_CART_HISTORY_TABLE,
                    array(
                        'user_id'   => get_current_user_id(),
                        'user_type' => 'REGISTERED',
                    ),
                    array( 'user_id' => $wcap_user_id_of_guest )
                );
            } else {
                $update_id = $wpdb->update(
                    WCAP_ABANDONED_CART_HISTORY_TABLE,
                    array(
                        'user_id'   => get_current_user_id(),
                        'user_type' => 'REGISTERED',
                    ),
                    array( 'id' => $abandoned_order_id )
                );
            }

            $wpdb->delete( WCAP_GUEST_CART_HISTORY_TABLE ,     array( 'id' => $wcap_user_id_of_guest ) );
    
        }

        add_post_meta( $order_id, 'wcap_abandoned_cart_id', $abandoned_order_id );
    }
    
    /**
     * Deletes Abandoned Cart records once order payment is completed
     * 
     * @param integer $order_id - WC Order ID
     * @param string $wc_old_status - Old WC Order Status
     * @param string $wc_new_status - New WC Order Status
     * 
     * @since 7.11.0
     * @hook woocommerce_order_status_changed
     */
    public static function wcap_cart_details_update( $order_id, $wc_old_status, $wc_new_status ) {
    
        if( 'pending' != $wc_new_status &&
            'failed' != $wc_new_status &&
            'cancelled' != $wc_new_status &&
            'trash' != $wc_new_status ) {
    
            global $wpdb;

            if( $order_id > 0 ) {
                $get_abandoned_id_of_order  = get_post_meta( $order_id, 'wcap_recover_order_placed', true );
                $abandoned_id               = $get_abandoned_id_of_order;

                if( $get_abandoned_id_of_order > 0 || wcap_get_cart_session( 'wcap_email_sent_id' ) != '' ) {
                    // recovered order
                    $get_sent_email_id_of_order = get_post_meta( $order_id, 'wcap_recover_order_placed_sent_id', true );

                    // Order Status passed in the function is either 'processing' or 'complete' and may or may not reflect the actual order status.
                    // Hence, always use the status fetched from the order object.

                    $order = new WC_Order( $order_id );

                    $order_status = ( $order ) ? $order->get_status() : '';

                    if ( 'pending' !== $order_status &&
                        'failed' !== $order_status &&
                        'cancelled' !== $order_status &&
                        'trash' !== $order_status ) {

                        // Mark as recovered 
                        if ( isset( $get_sent_email_id_of_order ) && '' != $get_sent_email_id_of_order ) {
                            wcap_common::wcap_updated_recovered_cart( $get_abandoned_id_of_order, $order_id, $get_sent_email_id_of_order, $order );
                        }
                    }
                } else {

                    $wcap_abandoned_id = get_post_meta( $order_id, 'wcap_abandoned_cart_id', true );
                    $abandoned_id      = $wcap_abandoned_id;

                    // check if it's a guest cart
                    $query_cart_data = "SELECT user_id, user_type FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "`
                                        WHERE id = %d";
                    $get_cart_data = $wpdb->get_results( $wpdb->prepare( $query_cart_data, $wcap_abandoned_id ) );

                    if( is_array( $get_cart_data ) && count( $get_cart_data ) > 0 ) {
                        $user_type = $get_cart_data[0]->user_type;
                        $user_id = $get_cart_data[0]->user_id;

                        if( 'GUEST' === $user_type && $user_id >= 63000000 ) {
                            $wpdb->delete( WCAP_GUEST_CART_HISTORY_TABLE, array( 'id' => $user_id ) );
                            $wpdb->delete( WCAP_ABANDONED_CART_HISTORY_TABLE, array( 'user_id' => $user_id ) );
                        }
                    }
                    $wpdb->delete( WCAP_ABANDONED_CART_HISTORY_TABLE, array( 'id' => $wcap_abandoned_id ) );

                    // remove the cart ID from the list to which SMS reminders will be sent
                    Wcap_Common::wcap_delete_cart_notification( $wcap_abandoned_id );
                }

                // Add the ATC coupon details.
                if ( $order_id > 0 && $abandoned_id > 0 ) {
                    $coupon_details = get_post_meta( $abandoned_id, '_woocommerce_ac_coupon', true );
    
                    $details_added = isset( $coupon_details['order_atc_details_added'] ) && $coupon_details['order_atc_details_added'] ? true : false;
    
                    if ( is_array( $coupon_details ) && isset( $coupon_details['atc_coupon_code'] ) && '' !== $coupon_details['atc_coupon_code'] && ! $details_added ) {
                        $coupon_code = $coupon_details['atc_coupon_code']; // Get the coupon name.
                        $order = wc_get_order( $order_id ); // WC Order.
                        
                        // Add post meta record & Note.
                        update_post_meta( $order_id, 'wcap_atc_coupon', $coupon_code );
                        $order->add_order_note( "ATC Coupon $coupon_code was used.", 'woocommerce-ac' );
                        $coupon_details['order_atc_details_added'] = true;
                        update_post_meta( $abandoned_id, '_woocommerce_ac_coupon', $coupon_details );
                    }
                }
            }
        }

    }

    /**
     * When an order status is changed we check the order status, if the status is pending or falied then we consider that cart as an abandoned.
     * Apart from the pending and failed we delete the abandoned cart.
     * @hook woocommerce_payment_complete_order_status
     * @param string $woo_order_status New order status
     * @param int | string $order_id Order id
     * @return string $woo_order_status
     * @globals mixed $wpdb
     * @since 5.0
     * 
     */
    public static function wcap_order_complete_action( $woo_order_status, $order_id ) {

        $order = new WC_Order( $order_id );

        $get_abandoned_id_of_order  = get_post_meta( $order_id, 'wcap_recover_order_placed', true );
        $get_sent_email_id_of_order = get_post_meta( $order_id, 'wcap_recover_order_placed_sent_id', true );

        // Order Status passed in the function is either 'processing' or 'complete' and may or may not reflect the actual order status.
        // Hence, always use the status fetched from the order object.
        
        $order_status = ( $order ) ? $order->get_status() : '';

        if ( 'pending' !== $order_status &&
             'failed' !== $order_status &&
             'cancelled' !== $order_status &&
             'trash' !== $order_status ) {

            // Mark as recovered 
            if ( isset( $get_sent_email_id_of_order ) && '' != $get_sent_email_id_of_order ) {
                wcap_common::wcap_updated_recovered_cart( $get_abandoned_id_of_order, $order_id, $get_sent_email_id_of_order, $order );
            }
        }

        return $woo_order_status;
    }
    
    /**
     * Updates cart status to 'Abandoned - Order Unpaid'
     * when the order is cancelled by WooCommerce once
     * Hold Stock Limit is reached.
     *
     * @param string $created_via - From where the order has been created.
     * @param WC_order $order - Order Object
     * @return string $created_via
     * @global mixed $wpdb
     * 
     * @since 7.7
     * @hook woocommerce_cancel_unpaid_order
     */
    static function wcap_update_cart_status( $created_via, $order ) {
        global $wpdb;
    
        $order_id = ( $order ) ? $order->get_id() : 0;
    
        if( isset( $order_id ) && $order_id > 0 ) {
            $abandoned_id  = get_post_meta( $order_id, 'wcap_abandoned_cart_id', true );
    
            if( isset( $abandoned_id ) && $abandoned_id > 0 ) {
                $update_data = array( 'cart_ignored' => '2' );
    
                $wpdb->update( WCAP_ABANDONED_CART_HISTORY_TABLE, $update_data, array( 'id' => $abandoned_id ) );
            }
        }
        return $created_via;
    }
  }
}