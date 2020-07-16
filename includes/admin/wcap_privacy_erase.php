<?php
/**
 * Export Abandoned Carts data in 
 * Dashboard->Tools->Erase Personal Data
 * 
 * @since 7.8
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Personal_Data_Eraser' ) ) {

    /**
     * Export Abandoned Carts data in
     * Dashboard->Tools->Erase Personal Data
     */
    class Wcap_Personal_Data_Eraser {
    
        /**
         * Construct
         * @since 7.8
         */
        public function __construct() {
            // Hook into the WP erase process
            add_filter( 'wp_privacy_personal_data_erasers', array( &$this, 'wcap_eraser_array' ), 6 );
        }
    
        /**
         * Add our eraser and it's callback function
         *
         * @param array $erasers - Any erasers that need to be added by 3rd party plugins
         * @param array $erasers - Erasers list containing our plugin details
         *
         * @since 7.8
         */
        public static function wcap_eraser_array( $erasers = array() ) {
            
            $eraser_list = array();
            // Add our eraser and it's callback function
            $eraser_list[ 'wcap_carts' ] = array( 
                'eraser_friendly_name' => __( 'Abandoned & Recovered Carts', 'woocommerce-ac' ),
                'callback'               => array( 'Wcap_Personal_Data_Eraser', 'wcap_data_eraser' )
            );
             
            $erasers = array_merge( $erasers, $eraser_list );

            return $erasers;
            
        }
        
        /**
         * Erases personal data for abandoned carts.
         *
         * @param string $email_address - EMail Address for which personal data is being exported
         * @param integer $page - The Eraser page number
         * @return array $reponse - Whether the process was successful or no
         *
         * @hook wp_privacy_personal_data_erasers
         * @global $wpdb
         * @since 7.8
         */
        static function wcap_data_eraser( $email_address, $page ) {

            global $wpdb;
            
            $page            = (int) $page;
            $user            = get_user_by( 'email', $email_address ); // Check if user has an ID in the DB to load stored personal data.
            $erasure_enabled = wc_string_to_bool( get_option( 'woocommerce_erasure_request_removes_order_data', 'no' ) );
            $response        = array(
                'items_removed'  => false,
                'items_retained' => false,
                'messages'       => array(),
                'done'           => true,
            );
        
            $user_id = $user ? (int) $user->ID : 0;
            
            if( $user_id > 0 ) { // registered user
                
                $cart_query = "SELECT id FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "`
                                WHERE user_id = %d AND
                                user_type = 'REGISTERED'";
                
                $cart_ids = $wpdb->get_results( $wpdb->prepare( $cart_query, $user_id ) );
            } else { // guest carts
                $guest_query = "SELECT id FROM `" . WCAP_GUEST_CART_HISTORY_TABLE . "`
                                WHERE email_id = %s";
                
                $guest_user_ids = $wpdb->get_results( $wpdb->prepare( $guest_query, $email_address ) );
                
                if( count( $guest_user_ids ) == 0 ) 
                    return array( 'messages' => array( __( 'No personal data found for any abandoned carts.', 'woocommerce-ac' ) ),
                            'items_removed' => false,
                            'items_retained' => true,       
                            'done' => true
                    );
                
                $cart_ids = array();
                
                foreach( $guest_user_ids as $ids ) {
                    // get the cart data
                    $cart_query = "SELECT id FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "`
                                    WHERE user_id = %d AND
                                    user_type = 'GUEST'";
                    
                    $cart_data = $wpdb->get_results( $wpdb->prepare( $cart_query, $ids->id ) );
                    
                    $cart_ids = array_merge( $cart_ids, $cart_data );
                }
            }
            
            if ( 0 < count( $cart_ids ) ) {
                $cart_chunks = array_chunk( $cart_ids, 10, true );
                
                $cart_export = isset( $cart_chunks[ $page - 1 ] ) ? $cart_chunks[ $page - 1 ] : array();
                if( count( $cart_export ) > 0 ) {
                    foreach ( $cart_export as $abandoned_ids ) {
                        $cart_id = $abandoned_ids->id;
                        
                        if ( apply_filters( 'wcap_privacy_erase_cart_personal_data', $erasure_enabled, $cart_id ) ) {
                            self::remove_cart_personal_data( $cart_id );
                        
                            /* Translators: %s Abandoned Cart ID. */
                            $response['messages'][]    = sprintf( __( 'Removed personal data from cart %s.', 'woocommerce-ac' ), $cart_id );
                            $response['items_removed'] = true;
                        } else {
                            /* Translators: %s Abandoned Cart ID. */
                            $response['messages'][]     = sprintf( __( 'Personal data within cart %s has been retained.', 'woocommerce-ac' ), $cart_id );
                            $response['items_retained'] = true;
                        }
                        
                    }
                    $response['done'] = $page > count( $cart_chunks );
                } else {
                    $response['done'] = true;
                }
            } else {
                $response['done'] = true;
            }
            
            return $response;
    
        }
        
        /**
         * Erases the personal data for each abandoned cart
         *
         * @param integer $abandoned_id - Abandoned Cart ID
         * @global $wpdb
         * @since 7.8
         */
        static function remove_cart_personal_data( $abandoned_id ) {
            global $wpdb;
            
            $anonymized_cart = array();
            $anonymized_guest = array();
            
    		do_action( 'wcap_privacy_before_remove_cart_personal_data', $abandoned_id );

            // list the props we'll be anonymizing for cart history table
            $props_to_remove_cart = apply_filters( 'wcap_privacy_remove_cart_personal_data_props', array(
                'ip_address'          => 'ip',
                'session_id'          => 'numeric_id',
                ), 
                $abandoned_id 
            );
            
            // list the props we'll be anonymizing for guest cart history table
            $props_to_remove_guest = apply_filters( 'wcap_privacy_remove_cart_personal_data_props_guest', array( 
                'billing_first_name'  => 'text',
                'billing_last_name'   => 'text',
                'phone'               => 'phone',
                'email_id'            => 'email' ), $abandoned_id );
                

            if ( ! empty( $props_to_remove_cart ) && is_array( $props_to_remove_cart ) ) {
                
                // get the data from cart history 
                $cart_query = "SELECT ip_address, session_id, user_type, user_id FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "`
                                WHERE id = %d";
                $cart_details = $wpdb->get_results( $wpdb->prepare( $cart_query, $abandoned_id ) );
                
                if( count( $cart_details ) > 0 ) {
                    $cart_details = $cart_details[0];
                } else {
                    return;
                }

                $user_id = $cart_details->user_id;
                $user_type = $cart_details->user_type;
                
                foreach ( $props_to_remove_cart as $prop => $data_type ) {
    				
                    $value = $cart_details->$prop;
                    
                    if ( empty( $value ) || empty( $data_type ) ) {
    					continue;
    				}
                    
                    if ( function_exists( 'wp_privacy_anonymize_data' ) ) {
                        $anon_value = wp_privacy_anonymize_data( $data_type, $value );
                    } else {
                    	$anon_value = '';
                    }

                    $anonymized_cart[ $prop ] = apply_filters( 'wcap_privacy_remove_cart_personal_data_prop_value', $anon_value, $prop, $value, $data_type, $abandoned_id );
                }
                $anonymized_cart[ 'user_type' ] = __( 'ANONYMIZED', 'woocommerce-ac' );
                // update the DB
                $wpdb->update( WCAP_ABANDONED_CART_HISTORY_TABLE, $anonymized_cart, array( 'id' => $abandoned_id ) );
            }
            
            // check whether it's a guest user
            if( $user_type == 'GUEST' && ! empty( $props_to_remove_guest ) && is_array( $props_to_remove_guest ) ) {

                // get the data from guest cart history
                $guest_query = "SELECT billing_first_name, billing_last_name, phone, email_id FROM `" . WCAP_GUEST_CART_HISTORY_TABLE . "`
                                WHERE id = %d";
                $guest_details = $wpdb->get_results( $wpdb->prepare( $guest_query, $user_id ) );
                
                if( count( $guest_details ) > 0 ) {
                    $guest_details = $guest_details[0];
                } else {
                    return;
                }
                
                foreach ( $props_to_remove_guest as $prop => $data_type ) {
                
                    $value = $guest_details->$prop;
                
                    if ( empty( $value ) || empty( $data_type ) ) {
                        continue;
                    }
                
                    if ( function_exists( 'wp_privacy_anonymize_data' ) ) {
                        $anon_value = wp_privacy_anonymize_data( $data_type, $value );
                    } else {
                        $anon_value = '';
                    }
                
                    $anonymized_guest[ $prop ] = apply_filters( 'wcap_privacy_remove_cart_personal_data_prop_value_guest', $anon_value, $prop, $value, $data_type, $abandoned_id );
                }
                
                // update the DB
                $wpdb->update( WCAP_GUEST_CART_HISTORY_TABLE, $anonymized_guest, array( 'id' => $user_id ) );
                
            }
                       
        }
        
    } // end of class
    $Wcap_Personal_Data_Eraser = new Wcap_Personal_Data_Eraser();
} // end if
?>