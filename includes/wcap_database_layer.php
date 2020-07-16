<?php
/**
 * It will have all the common function needed all over the plugin.
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Database-Layer
 * @since 7.7
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if ( !class_exists('WCAP_DB_Layer' ) ) {

    /**
     * Database access layer for performing DB related activities
     */
    class WCAP_DB_Layer {
        
        /**
         * Function ton insert data in database
         * 
         * @param int $user_id User ID
         * @param string $cart_info Cart Info Object encoded as string
         * @param int $abandoned_time Time
         * @param string $ignored If Cart Ignored
         * @param int $recovered Recovered Order Number
         * @param string $unsubscribe If unsubscribed
         * @param string $user_type User Type
         * @param string $language Current Language
         * @param string $session_id Session ID
         * @param string $ip_address IP Address
         * @param string $manual_email Manual Email ID
         * @param string $wcap_trash If trashed
         * 
         * @globals mixed $wpdb Global Variable
         * 
         * @since 7.7
         * 
         * @return int Inserted ID
         */
        public static function insert_cart_history( $user_id, $cart_info, $abandoned_time, $ignored, $recovered, $unsubscribe = '0', $user_type, $language, $session_id, $ip_address, $manual_email, $wcap_trash ) {

            if ( !Wcap_Common::wcap_validate_cart( $cart_info ) ) {
                return false;
            }

            global $wpdb;

            if ( function_exists( 'icl_object_id' ) ) {
                $cart_info = self::add_wcml_currency( $cart_info );
            }

            $cart_info = apply_filters( 'wcap_cart_info_before_insert', $cart_info );

            $wpdb->insert( 
                WCAP_ABANDONED_CART_HISTORY_TABLE,
                array( 
                    'user_id'               => $user_id,
                    'abandoned_cart_info'   => $cart_info,
                    'abandoned_cart_time'   => $abandoned_time, 
                    'cart_ignored'          => $ignored, 
                    'recovered_cart'        => $recovered,
                    'unsubscribe_link'      => '0',
                    'user_type'             => $user_type, 
                    'language'              => $language,
                    'session_id'            => $session_id,
                    'ip_address'            => $ip_address,
                    'manual_email'          => $manual_email,
                    'wcap_trash'            => $wcap_trash ),
                array( '%d', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ) );
            
            $insert_id = $wpdb->insert_id;
            
            do_action( 'wcap_after_insert_cart_details', $insert_id );

            $reminder_types = array();

            if( 'on' == get_option( 'wcap_enable_sms_reminders' ) ) {
                array_push( $reminder_types, 'type = "sms"' );
            }

            $reminder_types = apply_filters( 'wcap_add_meta_reminder_types', $reminder_types );

            if ( count( $reminder_types ) > 0 ) {
                wcap_common::wcap_insert_cart_id( $insert_id, $reminder_types );
            }

            return $insert_id;
        }

        /**
         * Add Currency to cart info object with WPML active and currency switcher present
         * 
         * @param string $cart_info Cart Info object as string
         * 
         * @return string cart_info object with currency added
         * 
         * @since 7.7
         * 
         * @globals mixed Global woocommerce wpml object
         */
        public static function add_wcml_currency( $cart_info ) {
            global $woocommerce_wpml;

            $cart_info = stripslashes($cart_info);

            if ( isset( $woocommerce_wpml->settings[ 'enable_multi_currency' ] ) && 
                $woocommerce_wpml->settings[ 'enable_multi_currency' ] == '2' ) {

                $client_currency = function_exists( 'WC' ) ? WC()->session->get( 'client_currency' ) : $woocommerce->session->get( 'client_currency' );

                $cart_info = json_decode( $cart_info, true );

                if ( !empty( $cart_info ) && isset( $cart_info['cart'] ) && !empty( $cart_info['cart'] ) &&
                     isset( $client_currency ) && $client_currency !== '' ) {

                    $cart_info['currency'] = $client_currency;
                }

                $cart_info = json_encode($cart_info);
            }

            return ( $cart_info );
            //return addslashes( $cart_info );
        }

        /**
         * Delete Abandoned Cart Record
         * 
         * @param array $value Key => Value pair to be deleted.
         * 
         * @since 7.10.0
         * 
         * @globals mixed Global $wpdb object
         */
        public static function wcap_delete_abandoned_order( $value = array() ) {
            global $wpdb;

            $wpdb->delete( WCAP_ABANDONED_CART_HISTORY_TABLE, $value );
        }
    }
}

return new WCAP_DB_Layer;