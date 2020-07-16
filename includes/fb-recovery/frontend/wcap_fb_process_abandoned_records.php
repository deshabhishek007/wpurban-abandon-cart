<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * Process Abandoned records for FB messenger
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/FB-Messenger
 * @category Modules
 * @since    7.10.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;   //Exit if accessed directly.
}

if ( !class_exists( 'WCAP_FB_Frontend_Processing' ) ) {

    /**
     * Front end processing of abandoned orders
     */
    class WCAP_FB_Frontend_Processing {
        
        function __construct() {
            
            add_filter( 'wcap_cart_info_before_insert', array( &$this, 'wcap_fb_add_user_ref' ) );

            add_filter( 'wcap_add_meta_reminder_types', array( &$this, 'wcap_fb_add_meta_type' ) );

            add_action( 'woocommerce_add_to_cart', array( &$this, 'wcap_fb_after_atc' ) );
        }

        public function wcap_fb_after_atc() {

            if ( isset( $_POST['wcap_checkbox_status'] ) && $_POST['wcap_checkbox_status'] === 'checked' ) {
                $wcap_user_ref = ( isset( $_POST['wcap_user_ref'] ) && $_POST['wcap_user_ref'] != '' ) ? $_POST['wcap_user_ref'] : '';

                wcap_set_cart_session( 'wcap_user_ref', $wcap_user_ref );
            }
        }

        function wcap_fb_add_user_ref( $cart_info ){

            $wcap_user_ref = wcap_get_cart_session( 'wcap_user_ref' );

            if ( $wcap_user_ref != '' ) {
                $cart_info = json_decode( stripslashes( $cart_info ), true );

                if ( !empty( $cart_info ) && isset( $cart_info['cart'] ) && !empty( $cart_info['cart'] ) ) {

                    $cart_info['wcap_user_ref'] = $wcap_user_ref;
                }

                $cart_info = json_encode($cart_info);
            }

            return $cart_info;
        }

        function wcap_fb_add_meta_type( $reminder_type ) {

            $wcap_user_ref = wcap_get_cart_session( 'wcap_user_ref' );

            if ( get_option( 'wcap_enable_fb_reminders' ) === 'on' && $wcap_user_ref != '' ) {
                array_push( $reminder_type, 'type= "fb"' );
            }

            return $reminder_type;
        }
    }
}

return new WCAP_FB_Frontend_Processing();