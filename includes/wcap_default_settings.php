<?php
/**
 * It will add the default setting and the email templates.
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Setting
 * @since 2.3.5
 * 
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if ( !class_exists('Wcap_Default_Settings' ) ) {
    /**
     * It will add the default setting and the email templates.
     */
    class Wcap_Default_Settings {

        /** 
         * This function will load default settings.
         * @since 2.3.5
         */
        function wcap_create_default_settings( $blog_id ) {
            if( $blog_id === 0 ) {
                add_option( 'ac_enable_cart_emails'         , 'on' );
                add_option( 'ac_cart_abandoned_time'        , '10' );
                add_option( 'ac_cart_abandoned_time_guest'  , '10' );
                add_option( 'ac_delete_abandoned_order_days', '' );
                add_option( 'ac_email_admin_on_recovery'    , '' );
                add_option( 'ac_track_coupons'              , '' );
                add_option( 'ac_disable_guest_cart_email'   , '' );
                add_option( 'wcap_use_auto_cron'            , 'on' );
                add_option( 'wcap_cron_time_duration'       , '15' );
                update_option( 'ac_settings_status'         , 'INDIVIDUAL' );
                add_option( 'wcap_from_name'                , 'Admin' );
                $wcap_get_admin_email = get_option( 'admin_email' );
                add_option( 'wcap_from_email'               , $wcap_get_admin_email );
                add_option( 'wcap_reply_email'              , $wcap_get_admin_email );
                add_option( 'wcap_product_image_height'     , '125' );
                add_option( 'wcap_product_image_width'      , '125' );
            } else {
                add_blog_option( $blog_id, 'ac_enable_cart_emails'         , 'on' );
                add_blog_option( $blog_id, 'ac_cart_abandoned_time'        , '10' );
                add_blog_option( $blog_id, 'ac_cart_abandoned_time_guest'  , '10' );
                add_blog_option( $blog_id, 'ac_delete_abandoned_order_days', '' );
                add_blog_option( $blog_id, 'ac_email_admin_on_recovery'    , '' );
                add_blog_option( $blog_id, 'ac_track_coupons'              , '' );
                add_blog_option( $blog_id, 'ac_disable_guest_cart_email'   , '' );
                add_blog_option( $blog_id, 'wcap_use_auto_cron'            , 'on' );
                add_blog_option( $blog_id, 'wcap_cron_time_duration'       , '15' );
                update_blog_option( $blog_id, 'ac_settings_status'         , 'INDIVIDUAL' );
                add_blog_option( $blog_id, 'wcap_from_name'                , 'Admin' );
                $wcap_get_admin_email = get_blog_option( $blog_id, 'admin_email' );
                add_blog_option( $blog_id, 'wcap_from_email'               , $wcap_get_admin_email );
                add_blog_option( $blog_id, 'wcap_reply_email'              , $wcap_get_admin_email );
                add_blog_option( $blog_id, 'wcap_product_image_height'     , '125' );
                add_blog_option( $blog_id, 'wcap_product_image_width'      , '125' );
            }
            
        }

        /** 
         * This function will load default template while activating the plugin.
         * @globals mixed $wpdb
         * @since 2.3.5
         */
        function wcap_create_default_templates( $db_prefix = '', $blog_id = 0 ) {
            if( $db_prefix === '' ) {
                return;
            }

            global $wpdb;

            $template_name_array    = array ( 'Initial', 'Interim', 'Final' );
            $site_title             = get_bloginfo( 'name' );
            $template_subject_array = array ( "Hey {{customer.firstname}}!! You left something in your cart", "Still Interested?", "10% off | We miss you…and so does your cart" );
            $active_post_array      = array ( 0, 0, 0 );
            $email_frequency_array  = array ( 15, 1, 24 );
            $day_or_hour_array      = array ( 'Minutes', 'Hours', 'Hours' );
            $discount_type          = "percent";
            $discount_shipping      = "no";
            $discount_expiry        = "7-days";

            $content = array();

            for ( $temp_num=1; $temp_num < 4; $temp_num++ ) { 
                ob_start();
                include( WCAP_PLUGIN_PATH . '/assets/html/templates/default_' . $temp_num . '.html' );
                $content[$temp_num] = ob_get_clean();
            }

            $body_content_array     = array ( 
                addslashes ( $content[1] ),
                addslashes ( $content[2] ),
                addslashes ( $content[3] ) 
            );

            $header_text = array(
                addslashes('You left Something in Your Cart!'),
                addslashes('We saved your cart.'),
                addslashes('It\'s not too late!')
            );

            $coupon_code_id   = '';
            $default_template = 1;
            $discount_array   = array( '0', '0', '10' );
            $is_wc_template   = 0 ;

            for ( $insert_count = 0 ; $insert_count < 3 ; $insert_count++ ) {

                $query = "INSERT INTO `" . $db_prefix . "ac_email_templates" . "`
                ( subject, body, is_active, frequency, day_or_hour, coupon_code, template_name, default_template, discount, discount_type, discount_shipping, discount_expiry, is_wc_template, wc_email_header )
                VALUES (
                            '" . $template_subject_array [ $insert_count ] . "',
                            '" . $body_content_array [ $insert_count ] . "',
                            '" . $active_post_array [ $insert_count ] . "',
                            '" . $email_frequency_array [ $insert_count ] . "',
                            '" . $day_or_hour_array [ $insert_count ] . "',
                            '" . $coupon_code_id . "',
                            '" . $template_name_array [ $insert_count ] . "',
                            '" . $default_template . "',
                            '" . $discount_array [ $insert_count ] . "',
                            '" . $discount_type . "',
                            '" . $discount_shipping . "',
                            '" . $discount_expiry . "',
                            '" . $is_wc_template . "',
                            '" . $header_text [ $insert_count ] . "' )";

                $wpdb->query( $query );

            }

            if( $blog_id === 0 ) {
                add_option( 'wcap_new_default_templates', 1 );
            } else {
                add_blog_option( $blog_id, 'wcap_new_default_templates', 1 );
            }
            
        }
    }

}
