<?php
/**
 * Send an email to the admin when the abandoned cart is recovered.
 * @author  Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Recover
 * @since 5.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Admin_Recovery' ) ) {
    /**
     * Send an email to the admin when the abandoned cart is recovered.
     */
    class Wcap_Admin_Recovery{

        /**
         * This function will send the email to the store admin when any abandoned cart email recovered.
         * @hook woocommerce_order_status_changed
         * @param int | string  $order_id Order id
         * @param string $wcap_old_status Old status of the order
         * @param string $wcap_new_status New status of the order    
         * @globals mixed $woocommerce
         * @since 1.0
         */
        public static function wcap_email_admin_recovery ( $order_id, $wcap_old_status, $wcap_new_status ) {
           global $woocommerce;

           if (    ( 'pending' == $wcap_old_status && 'processing' == $wcap_new_status )
                || ( 'pending' == $wcap_old_status && 'completed'  == $wcap_new_status )
                || ( 'pending' == $wcap_old_status && 'on-hold'    == $wcap_new_status )
                || ( 'failed'  == $wcap_old_status && 'completed'  == $wcap_new_status )
                || ( 'failed'  == $wcap_old_status && 'processing' == $wcap_new_status )
            ) {
               $user_id                 = get_current_user_id();
               $ac_email_admin_recovery = get_option( 'ac_email_admin_on_recovery' );
               $order                   = new WC_Order( $order_id );
               if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {
                    $user_id              = $order->get_user_id();
                } else {
                    $user_id              = $order->user_id;
                }
                if( $ac_email_admin_recovery == 'on' ) {
                   $recovered_email_sent          = get_post_meta( $order_id, 'wcap_recovered_email_sent', true );
                   $wcap_check_order_is_recovered = self::wcap_check_order_is_recovered( $order_id );

                   if ( 'yes' != $recovered_email_sent && true === $wcap_check_order_is_recovered ) { // indicates cart is abandoned
                       $order          = new WC_Order( $order_id );
                       $email_heading  = __( 'New Customer Order - Recovered', 'woocommerce' );
                       $blogname       = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
                       $email_subject  = __( 'New Customer Order - Recovered', 'woocommerce' );
                       $user_email     = get_option( 'admin_email' );
                       $headers[]      = "From: Admin <".$user_email.">";
                       $headers[]      = "Content-Type: text/html";
                       // Buffer
                       ob_start();
                       // Get mail template
                       wc_get_template( 'emails/admin-new-order.php', array(
                             'order'         => $order,
                             'email_heading' => $email_heading,
                             'sent_to_admin' => false,
                             'plain_text'    => false,
                             'email'         => true
                            )
                        );
                       // Get contents
                       $email_body = ob_get_clean();
                       wc_mail( $user_email, $email_subject, $email_body, $headers );

                       update_post_meta( $order_id, 'wcap_recovered_email_sent', 'yes' );
                   }
               }
           }
       }

		/**
		 * This function will check if the order is recovered or not.
		 * @param int | string $wcap_order_id Order Id
		 * @globals mixed $wpdb
		 * @return boolean true | false
		 * @since 5.0
		 */
		public static function wcap_check_order_is_recovered( $wcap_order_id ) {
			global $wpdb;

			$wcap_recover_order_query        = "SELECT `recovered_cart` FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` WHERE recovered_cart = %d";
			$wcap_recover_order_query_result = $wpdb->get_results( $wpdb->prepare( $wcap_recover_order_query, $wcap_order_id ) );

			if ( count( $wcap_recover_order_query_result ) > 0 ){
				return true;
			}
			return false;
		}
	}
}
