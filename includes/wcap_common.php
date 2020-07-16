<?php
/**
 * It will have all the common function needed all over the plugin.
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Common-Functions
 * @since 5.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if ( !class_exists('Wcap_Common' ) ) {

    /**
     * It will have all the common function needed all over the plugin.
     */
    class Wcap_Common {

        /**
         * It will add the Questions while admin deactivate the plugin.
         * @hook ts_deativate_plugin_questions
         * @param array $wcap_add_questions Blank array
         * @return array $wcap_add_questions List of all questions.
         */
        public static function wcap_deactivate_add_questions ( $wcap_add_questions ) {

            $wcap_add_questions = array(
                0 => array(
                    'id'                => 4,
                    'text'              => __( "Emails are not being sent to customers.", "woocommerce-ac" ),
                    'input_type'        => '',
                    'input_placeholder' => ''
                    ), 
                1 =>  array(
                    'id'                => 5,
                    'text'              => __( "Capturing of cart and other information was not satisfactory.", "woocommerce-ac" ),
                    'input_type'        => '',
                    'input_placeholder' => ''
                ),
                2 => array(
                    'id'                => 6,
                    'text'              => __( "My customers got annoyed with multiple emails sent to them.", "woocommerce-ac" ),
                    'input_type'        => '',
                    'input_placeholder' => ''
                ),
                3 => array(
                    'id'                => 7,
                    'text'              => __( "Abandoned carts data are not imported from LITE version.", "woocommerce-ac" ),
                    'input_type'        => '',
                    'input_placeholder' => ''
                )

            );
            return $wcap_add_questions;
        }
        /**
         * Get abandoned amount of all users.
         * @globals mixed $wpdb 
         * @return int $wcap_abandoned_amount All abandoned amount.
         */
        public static function wcap_get_abandoned_amount () {
            global $wpdb;
            $wcap_data = array();
    
            $blank_cart_info       = '{"cart":[]}';
            $blank_cart_info_guest = '[]';
    
            $ac_cutoff_time        = get_option( 'ac_cart_abandoned_time' );
            $cut_off_time          = $ac_cutoff_time * 60;
            $current_time          = current_time( 'timestamp' );
            $compare_time          = $current_time - $cut_off_time;
    
            $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
            $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
            $current_time          = current_time ('timestamp');
            $compare_time_guest    = $current_time - $cut_off_time_guest;

            $wcap_abandoned_amount = 0;

            $wcap_get_abandoned_amount         = "SELECT abandoned_cart_info FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND recovered_cart = 0 AND wcap_trash = '') OR ( user_type = 'GUEST' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND recovered_cart = 0 AND wcap_trash = '' ) ";
            
            $wcap_get_abandoned_amount_results = $wpdb->get_results( $wcap_get_abandoned_amount, ARRAY_A );

            $wcap_abandoned_amount = self::wcap_get_amount ( $wcap_get_abandoned_amount_results );

            return $wcap_abandoned_amount;

        }

        /**
         * It will fetch the total abandoned cart of the logged in users.
         * @globals mixed $wpdb 
         * @return int $wcap_loggedin_users_carts_count Count of loggedin users cart.
         */
        public static function wcap_get_loggedin_abandoned_carts () {
            global $wpdb;
            $wcap_data = array();
    
            $blank_cart_info       = '{"cart":[]}';
            $blank_cart_info_guest = '[]';
    
            $ac_cutoff_time        = get_option( 'ac_cart_abandoned_time' );
            $cut_off_time          = $ac_cutoff_time * 60;
            $current_time          = current_time( 'timestamp' );
            $compare_time          = $current_time - $cut_off_time;
            $wcap_loggedin_users_carts_count = 0;
            
            $wcap_loggedin_users_carts       = "SELECT COUNT(id) FROM " . WCAP_ABANDONED_CART_HISTORY_TABLE . " WHERE ( user_type = 'REGISTERED' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND recovered_cart = 0 AND wcap_trash = '')";
            $wcap_loggedin_users_carts_count = $wpdb->get_var( $wcap_loggedin_users_carts );

            return $wcap_loggedin_users_carts_count;
        }

        /**
         * It will fetch the total abandoned cart of the guest users.
         * @globals mixed $wpdb 
         * @return int $wcap_guest_user_cart_count Count of guest users carts..
         */
        public static function wcap_get_guest_abandoned_carts () {
            global $wpdb;
            $wcap_data = array();
    
            $blank_cart_info       = '{"cart":[]}';
            $blank_cart_info_guest = '[]';
    
            $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
            $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
            $current_time          = current_time ('timestamp');
            $compare_time_guest    = $current_time - $cut_off_time_guest;
            $wcap_guest_user_cart_count = 0;
            
            $wcap_guest_users_carts       = "SELECT COUNT(id) FROM " . WCAP_ABANDONED_CART_HISTORY_TABLE . " WHERE  ( user_type = 'GUEST' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND recovered_cart = 0 AND wcap_trash = '' )";
            $wcap_guest_user_cart_count = $wpdb->get_var( $wcap_guest_users_carts );

            return $wcap_guest_user_cart_count;
        }

        /**
         * It will fetch all amount of the recovered orders.
         * @globals mixed $wpdb 
         * @return int $wcap_recovered_amount All recovered amount.
         */
        public static function wcap_get_recovered_amount () {
            global $wpdb;
            $wcap_data = array();
    
            $blank_cart_info       = '{"cart":[]}';
            $blank_cart_info_guest = '[]';
    
            $ac_cutoff_time        = get_option( 'ac_cart_abandoned_time' );
            $cut_off_time          = $ac_cutoff_time * 60;
            $current_time          = current_time( 'timestamp' );
            $compare_time          = $current_time - $cut_off_time;
    
            $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
            $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
            $current_time          = current_time ('timestamp');
            $compare_time_guest    = $current_time - $cut_off_time_guest;

            $wcap_recovered_amount = 0;

            $wcap_get_recovered_amount  = "SELECT abandoned_cart_info FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND recovered_cart != 0 AND wcap_trash = '') OR ( user_type = 'GUEST' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND recovered_cart != 0 AND wcap_trash = '' )  ";
            
            $wcap_get_recovered_results = $wpdb->get_results ( $wcap_get_recovered_amount, ARRAY_A );

            $wcap_recovered_amount = self::wcap_get_amount ( $wcap_get_recovered_results );

            return $wcap_recovered_amount;
        }

        /**
         * Get all sent emails.
         * @globals mixed $wpdb 
         * @return int $wcap_emails_sent_count All Sent emails amount.
         */

        public static function wcap_get_send_emails_count() {
            global $wpdb;
            $wcap_sent_emails       = "SELECT COUNT(id) FROM " . WCAP_EMAIL_SENT_HISTORY_TABLE . " ";
            $wcap_emails_sent_count = $wpdb->get_var( $wcap_sent_emails );

            return $wcap_emails_sent_count;
        }

        /**
         * It will fetch all the loggedin user abandoned cart amount.
         * @globals mixed $wpdb 
         * @return int $wcap_loggedin_abandoned_amount All loggedin users abandoned amount.
         */
        public static function wcap_get_loggedin_user_abandoned_cart_amount() {

            global $wpdb;
            $wcap_data = array();
    
            $blank_cart_info       = '{"cart":[]}';
            $blank_cart_info_guest = '[]';
            
            $ac_cutoff_time        = get_option( 'ac_cart_abandoned_time' );
            $cut_off_time          = $ac_cutoff_time * 60;
            $current_time          = current_time( 'timestamp' );
            $compare_time          = $current_time - $cut_off_time;

            $wcap_loggedin_abandoned_amount = 0;
            $wcap_get_loggedin_abandoned_amount        = "SELECT abandoned_cart_info FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND recovered_cart = 0 AND wcap_trash = '') ";
            
            $wcap_get_loggedin_abandoned_amount_results = $wpdb->get_results( $wcap_get_loggedin_abandoned_amount, ARRAY_A );

            $wcap_loggedin_abandoned_amount = self::wcap_get_amount ( $wcap_get_loggedin_abandoned_amount_results );
            
            return $wcap_loggedin_abandoned_amount;
        }

        /**
         * It will fetch all the Guest user abandoned cart amount.
         * @globals mixed $wpdb 
         * @return int $wcap_guest_abandoned_amount All Guest users abandoned amount.
         */

        public static function wcap_get_guest_user_abandoned_cart_amount() {

            global $wpdb;
            $wcap_data = array();
            $blank_cart_info       = '{"cart":[]}';
            $blank_cart_info_guest = '[]';
            
            $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
            $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
            $current_time          = current_time ('timestamp');
            $compare_time_guest    = $current_time - $cut_off_time_guest;

            $wcap_loggedin_abandoned_amount = 0;
            $wcap_get_guest_abandoned_amount        = "SELECT abandoned_cart_info FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` WHERE ( user_type = 'GUEST' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND recovered_cart = 0 AND wcap_trash = '' ) ";
                
            $wcap_get_guest_abandoned_amount_results = $wpdb->get_results( $wcap_get_guest_abandoned_amount, ARRAY_A );

            $wcap_guest_abandoned_amount    = self::wcap_get_amount ( $wcap_get_guest_abandoned_amount_results );
            
            return $wcap_guest_abandoned_amount;
        }

        /**
         * It will fetch all the loggedin user recovered cart amount.
         * @globals mixed $wpdb 
         * @return int $wcap_loggedin_recovered_amount All loggedin users recovered amount.
         */

        public static function wcap_get_loggedin_user_recovered_cart_amount() {

            global $wpdb;
            $wcap_data = array();
    
            $blank_cart_info       = '{"cart":[]}';
            $blank_cart_info_guest = '[]';
            
            $ac_cutoff_time        = get_option( 'ac_cart_abandoned_time' );
            $cut_off_time          = $ac_cutoff_time * 60;
            $current_time          = current_time( 'timestamp' );
            $compare_time          = $current_time - $cut_off_time;

            $wcap_loggedin_recovered_amount = 0;
            $wcap_get_loggedin_recovered_amount  = "SELECT abandoned_cart_info FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND recovered_cart != 0 AND wcap_trash = '') ";
            $wcap_get_loggedin_recovered_results = $wpdb->get_results ( $wcap_get_loggedin_recovered_amount, ARRAY_A );

            $wcap_loggedin_recovered_amount = self::wcap_get_amount ( $wcap_get_loggedin_recovered_results );
            
            return $wcap_loggedin_recovered_amount;
        }

        /**
         * It will fetch all the Guest user recovered cart amount.
         * @globals mixed $wpdb 
         * @return int $wcap_guest_recovered_amount All Guest users recovered amount.
         */

        public static function wcap_get_guest_user_recovered_cart_amount() {

            global $wpdb;
            $wcap_data = array();
            $blank_cart_info       = '{"cart":[]}';
            $blank_cart_info_guest = '[]';
            
            $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
            $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
            $current_time          = current_time ('timestamp');
            $compare_time_guest    = $current_time - $cut_off_time_guest;

            $wcap_guest_recovered_amount = 0;
            $wcap_get_guest_recovered_amount  = "SELECT abandoned_cart_info FROM `". WCAP_ABANDONED_CART_HISTORY_TABLE ."` WHERE ( user_type = 'GUEST' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND recovered_cart != 0 AND wcap_trash = '' )  ";
            
            $wcap_get_guest_recovered_results = $wpdb->get_results ( $wcap_get_guest_recovered_amount, ARRAY_A );

            $wcap_guest_recovered_amount      = self::wcap_get_amount ( $wcap_get_guest_recovered_results );
            
            return $wcap_guest_recovered_amount;
        }
        
        /**
         * It will fetch the Abandoned and Recovered orders total amount.
         * @param array $wcap_result Result of query.
         * @return string $wcap_amount Total Abandoned | Recovered amount.
         *  
         */
        public static function wcap_get_amount ( $wcap_result ) {
            $wcap_amount = 0;
            foreach ( $wcap_result as $wcap_result_key => $wcap_result_value ) {
                $wcap_cart_info = json_decode( stripslashes( $wcap_result_value[ 'abandoned_cart_info' ] ) );

                $wcap_cart_details = array();
                if ( isset( $wcap_cart_info->cart ) ) {
                    $wcap_cart_details = $wcap_cart_info->cart;
                }

                if ( count( get_object_vars( $wcap_cart_details ) ) > 0 ) {
                    foreach ( $wcap_cart_details as $k => $v ) {
                        if ( $v->line_subtotal_tax != 0 && $v->line_subtotal_tax > 0 ) {
                            $wcap_amount = $wcap_amount + $v->line_total + $v->line_subtotal_tax;
                        } else {
                            $wcap_amount = $wcap_amount + $v->line_total;
                        }
                    }
                }
            }
            return $wcap_amount;
        }

        /**
         * It will fetch all the template data.
         * @globals mixed $wpdb
         * @return array $wcap_templates_data All data of template
         */
        private static function wcap_get_email_templates_data() {

            global $wpdb;
            $wcap_email_templates_count   = 0;
            $wcap_email_templates_query   = "SELECT id, is_active, is_wc_template,frequency, day_or_hour, subject, wc_template_filter, send_emails_to FROM `" . WCAP_EMAIL_TEMPLATE_TABLE . "`";
            $wcap_email_templates_results = $wpdb->get_results( $wcap_email_templates_query );

            $wcap_email_templates_count = count( $wcap_email_templates_results );

            $wcap_templates_data                     = array();
            $wcap_templates_data ['total_templates'] = $wcap_email_templates_count;

            foreach ( $wcap_email_templates_results as $wcap_email_templates_results_key => $wcap_email_templates_results_value ) {

                $wcap_template_time = $wcap_email_templates_results_value->frequency . ' ' . $wcap_email_templates_results_value->day_or_hour;

                $wcap_get_total_email_sent_for_template       = "SELECT COUNT(id) FROM `" . WCAP_EMAIL_SENT_HISTORY_TABLE . "` WHERE template_id = " . $wcap_email_templates_results_value->id;
                $wcap_get_total_email_sent_for_template_count = $wpdb->get_var( $wcap_get_total_email_sent_for_template );

                $query_no_recovers_test      = "SELECT COUNT(`id`) FROM " . WCAP_EMAIL_SENT_HISTORY_TABLE . " WHERE recovered_order = '1' AND template_id = %d ";
                $wcap_number_of_time_recover = $wpdb->get_var( $wpdb->prepare( $query_no_recovers_test, $wcap_email_templates_results_value->id ) );

                $wcap_templates_data [ 'template_id_' . $wcap_email_templates_results_value->id ] ['is_activate']      = ( $wcap_email_templates_results_value->is_active == 1 ) ? 'Active' : 'Deactive';
                $wcap_templates_data [ 'template_id_' . $wcap_email_templates_results_value->id ] ['is_wc_template']   = ( $wcap_email_templates_results_value->is_wc_template == 1 ) ? 'Yes' : 'No';
                $wcap_templates_data [ 'template_id_' . $wcap_email_templates_results_value->id ] ['template_time']    = $wcap_template_time;
                $wcap_templates_data [ 'template_id_' . $wcap_email_templates_results_value->id ] ['total_email_sent'] = $wcap_get_total_email_sent_for_template_count;

                $wcap_templates_data [ 'template_id_' . $wcap_email_templates_results_value->id ] ['subject'] = $wcap_email_templates_results_value->subject;

                $wcap_templates_data [ 'template_id_' . $wcap_email_templates_results_value->id ] ['wc_template_filter'] = $wcap_email_templates_results_value->wc_template_filter;

                $email_actions = isset( $wcap_email_templates_results_value->send_email_to ) && '' != $wcap_email_templates_results_value->send_email_to ? json_decode( $wcap_email_templates_results_value->send_email_to, true ) : '';

                $wcap_email_action_name = isset( $email_actions[ 'action' ] ) ? $email_actions[ 'action' ] : '';
                
                $wcap_templates_data [ 'template_id_' . $wcap_email_templates_results_value->id ] ['wcap_email_action'] = $wcap_email_action_name;

                $wcap_recover_ratio = 0;
                if ( $wcap_get_total_email_sent_for_template_count != 0 ) {
                    $wcap_recover_ratio = $wcap_number_of_time_recover / $wcap_get_total_email_sent_for_template_count * 100;
                }

                $wcap_template_ratio = round ( $wcap_recover_ratio , 2 )."%";
                $wcap_templates_data [ 'template_id_' . $wcap_email_templates_results_value->id ] ['wcap_recover_ratio'] = $wcap_template_ratio;
                
            }

            return $wcap_templates_data;
        }

        /**
         * Get all options of the plugin.
         *
         * @return array $wcap_settings  All settings
         */
        private static function wcap_get_plugin_settings() {

            $wcap_settings [ 'ac_enable_cart_emails' ]                     = get_option( 'ac_enable_cart_emails' );
            $wcap_settings [ 'ac_cart_abandoned_time' ]                    = get_option( 'ac_cart_abandoned_time' ) ;
            $wcap_settings [ 'ac_cart_abandoned_time_guest' ]              = get_option( 'ac_cart_abandoned_time_guest' ) ;
            $wcap_settings [ 'ac_delete_abandoned_order_days' ]            = get_option( 'ac_delete_abandoned_order_days' ) ;
            $wcap_settings [ 'ac_email_admin_on_recovery' ]                = get_option( 'ac_email_admin_on_recovery' ) ;
            $wcap_settings [ 'ac_disable_guest_cart_email' ]               = get_option( 'ac_disable_guest_cart_email' ) ;
            $wcap_settings [ 'wcap_use_auto_cron' ]                        = get_option( 'wcap_use_auto_cron' ) ;
            $wcap_settings [ 'wcap_cron_time_duration' ]                   = get_option( 'wcap_cron_time_duration' ) ;
            $wcap_settings [ 'wcap_product_image_height' ]                 = get_option( 'wcap_product_image_height' ) ;
            $wcap_settings [ 'wcap_product_image_width' ]                  = get_option( 'wcap_product_image_width' ) ;
            $wcap_settings [ 'ac_track_guest_cart_from_cart_page' ]        = get_option( 'ac_track_guest_cart_from_cart_page' ) ;
            $wcap_settings [ 'ac_disable_logged_in_cart_email' ]           = get_option( 'ac_disable_logged_in_cart_email' ) ;
            $wcap_settings [ 'wcap_heading_section_text_email' ]           = get_option( 'wcap_heading_section_text_email' ) ;
            $wcap_settings [ 'wcap_popup_heading_color_picker' ]           = get_option( 'wcap_popup_heading_color_picker' ) ;
            $wcap_settings [ 'wcap_text_section_text' ]                    = get_option( 'wcap_text_section_text' ) ;
            $wcap_settings [ 'wcap_popup_text_color_picker' ]              = get_option( 'wcap_popup_text_color_picker' ) ;
            $wcap_settings [ 'wcap_email_placeholder_section_input_text' ] = get_option( 'wcap_email_placeholder_section_input_text' ) ;
            $wcap_settings [ 'wcap_button_section_input_text' ]            = get_option( 'wcap_button_section_input_text' ) ;
            $wcap_settings [ 'wcap_button_color_picker' ]                  = get_option( 'wcap_button_color_picker' ) ;
            $wcap_settings [ 'wcap_button_text_color_picker' ]             = get_option( 'wcap_button_text_color_picker' ) ;
            $wcap_settings [ 'wcap_non_mandatory_text' ]                   = get_option( 'wcap_non_mandatory_text' ) ;
            $wcap_settings [ 'wcap_atc_enable_modal' ]                     = get_option( 'wcap_atc_enable_modal' ) ;
            $wcap_settings [ 'wcap_atc_mandatory_email' ]                  = get_option( 'wcap_atc_mandatory_email' ) ;
            return $wcap_settings;
        }
        
        /**
         * Returns the total number of SMS sent from the plugin
         * 
         * @return integer $sms_count - Total number of SMS sent
         * @since 7.9
         */
        public static function wcap_get_sent_sms_count() {
        
            $sms_count = 0;
            
            global $wpdb;
            $wcap_sms_query       = "SELECT n_meta.meta_id, n_meta.meta_value as count_sms FROM `" . WCAP_NOTIFICATIONS_META ."` AS n_meta
                                        INNER JOIN `" . WCAP_NOTIFICATIONS . "` AS notify
                                        ON n_meta.template_id = notify.id
                                        WHERE n_meta.meta_key = 'sent_count'
                                        AND notify.type='sms' ";
        
            $wcap_sms_results = $wpdb->get_results( $wcap_sms_query );
        
            if( is_array( $wcap_sms_results ) && count( $wcap_sms_results ) > 0 ) {
                foreach( $wcap_sms_results as $count ) {
                    $sms_count += $count->count_sms;
                }
            }
            
            return $sms_count;
        
        }
        
        /**
         * Returns the SMS template data for tracking
         * 
         * @return array $wcap_sms_data - SMS Template Data
         * @since 7.9
         */
        public static function wcap_get_sms_templates_data() {
        
            global $wpdb;
        
            $wcap_sms_templates_count     = 0;
            $wcap_query_sms               = "SELECT id, is_active, frequency FROM `" . WCAP_NOTIFICATIONS . "` WHERE type='sms'";
            $wcap_sms_results             = $wpdb->get_results( $wcap_query_sms );
        
            $wcap_sms_templates_count = count( $wcap_sms_results );
        
            $wcap_sms_data                     = array();
            $wcap_sms_data ['sms_total_templates'] = $wcap_sms_templates_count;
        
            foreach ( $wcap_sms_results as $sms_key => $sms_data ) {
        
                $wcap_get_total_sms_sent_query       = "SELECT meta_value FROM `" . WCAP_NOTIFICATIONS_META . "`
                                                          WHERE template_id = " . $sms_data->id;
                $wcap_total_sms_sent = $wpdb->get_var( $wcap_get_total_sms_sent_query );
        
                $recovered_count = 0;
                $query_cart_links           = "SELECT urls.cart_id as ids FROM `" . WCAP_TINY_URLS . "` AS urls
                                                INNER JOIN `" . WCAP_NOTIFICATIONS . "` as notify
                                                WHERE urls.template_id = notify.id
                                                AND notify.type = 'sms'
                                                AND urls.counter > 0";
        
                $results_cart_links         = $wpdb->get_results( $query_cart_links );
        
                if( is_array( $results_cart_links ) && count( $results_cart_links ) > 0 ) {
                    $cart_links = '';
                    foreach( $results_cart_links as $links ) {
                        $clicked_id = $links->ids;
                        $cart_links .= $cart_links == '' ? "'$clicked_id'" : ",'$clicked_id'";
                    }
        
                    $query_no_recovered      = "SELECT COUNT(`id`) FROM " . WCAP_ABANDONED_CART_HISTORY_TABLE . " AS cart
                                                    WHERE cart.recovered_cart > 0 AND id IN (%s) ";
                    $recovered_count = $wpdb->get_var( $wpdb->prepare( $query_no_recovered, $cart_links ) );
                }
        
                $wcap_sms_data [ 'sms_template_id_' . $sms_data->id ] ['is_activate']      = ( $sms_data->is_active == 1 ) ? 'Active' : 'Inactive';
        
                $wcap_sms_data [ 'sms_template_id_' . $sms_data->id ] ['template_time']    = $sms_data->frequency;
        
                $wcap_sms_data [ 'sms_template_id_' . $sms_data->id ] ['total_sms_sent'] = $wcap_total_sms_sent;
    
                $wcap_recover_ratio = 0;
                if ( $recovered_count != 0 ) {
                    $wcap_recover_ratio = $recovered_count / $wcap_total_sms_sent * 100;
                }
    
                $wcap_template_ratio = round ( $wcap_recover_ratio , 2 )."%";
                $wcap_sms_data [ 'sms_template_id_' . $sms_data->id ] ['wcap_recover_ratio'] = $wcap_template_ratio;
            
            }
    
            return $wcap_sms_data;

        }
        
        /** 
         * Send the plugin data when the user has opted in
         * @hook ts_tracker_data
         * @param array $data All data to send to server
         * @return array $plugin_data All data to send to server
         */
        public static function ts_add_plugin_tracking_data( $data ) {
            //if ( isset( $_GET[ 'wcap_tracker_optin' ] ) && isset( $_GET[ 'wcap_tracker_nonce' ] ) && wp_verify_nonce( $_GET[ 'wcap_tracker_nonce' ], 'wcap_tracker_optin' ) ) {

                $plugin_data[ 'ts_meta_data_table_name' ]         = 'ts_tracking_wcap_meta_data';
                $plugin_data[ 'ts_plugin_name' ]                  = 'Abandoned Cart Pro for WooCommerce';
                
                // Store abandoned count info
                $plugin_data['abandoned_orders']                  = self::wcap_get_abandoned_order_count( 'wcap_all_abandoned_lifetime' );

                // Store recovred count info
                $plugin_data['recovered_orders']                  = self::wcap_get_reovered_order_count();
                
                // store abandoned orders amount
                $plugin_data['abandoned_orders_amount']           = self::wcap_get_abandoned_amount();

                // Store recovered count info
                $plugin_data['recovered_orders_amount']           = self::wcap_get_recovered_amount();

                // Store abandoned cart emails sent count info
                $plugin_data['sent_emails']                       = self::wcap_get_send_emails_count(); 

               // Store email templates  info
                $plugin_data['email_templates_data']              = self::wcap_get_email_templates_data();

               // Store only logged-in users abandoned cart count info
                $plugin_data['logged_in_abandoned_orders']        =  self::wcap_get_loggedin_abandoned_carts();

                // Store only logged-in users abandoned cart count info
                $plugin_data['guest_abandoned_orders']            = self::wcap_get_guest_abandoned_carts();

                // Store only logged-in users abandoned cart amount info
                $plugin_data['logged_in_abandoned_orders_amount'] = self::wcap_get_loggedin_user_abandoned_cart_amount();

                // store only guest users abandoned cart amount
                $plugin_data['guest_abandoned_orders_amount']     = self::wcap_get_guest_user_abandoned_cart_amount();

                // Store only logged-in users recovered cart amount info
                $plugin_data['logged_in_recovered_orders_amount'] = self::wcap_get_loggedin_user_recovered_cart_amount();

                // Store only guest users recovered cart amount
                $plugin_data['guest_recovered_orders_amount']     = self::wcap_get_guest_user_recovered_cart_amount();

                // Store abandoned cart SMS reminders sent count info
                $plugin_data['sent_sms']                          = self::wcap_get_sent_sms_count();
                
                // Store SMS templates  info
                $plugin_data['sms_templates_data']                = self::wcap_get_sms_templates_data();
                
                // Get all plugin options info
                $plugin_data['settings']                          = self::wcap_get_plugin_settings(); 
                $plugin_data['plugin_version']                    = self::wcap_get_version();
                $plugin_data['tracking_usage']                    = get_option( 'wcap_allow_tracking' );
                $data[ 'plugin_data' ]                            = $plugin_data;
            //}
            return $data;
        }

        /** 
         * This function used to send the data to the server. It is used for tracking the data when admin do not wish to share the tarcking informations.
         * @hook ts_tracker_opt_out_data
         * @param array $params Parameters
         * @return array $params Parameters
         */
        public static function wcap_get_data_for_opt_out( $params ) {
            $plugin_data[ 'ts_meta_data_table_name'] = 'ts_tracking_wcap_meta_data';
            $plugin_data[ 'ts_plugin_name' ]         = 'Abandoned Cart Pro for WooCommerce';
            
            $params[ 'plugin_data' ]                 = $plugin_data;
            
            return $params;
        }

        /**
         * This function returns the AC plugin version number.
         * @return string $plugin_version Current version of plguin
         * @since 5.0
         */
        public static function wcap_get_version() {
            $wcap_plugin_dir =  dirname ( dirname (__FILE__) );
            $wcap_plugin_dir .= '/wpurban-ac.php';

            $plugin_data = get_plugin_data( $wcap_plugin_dir );
            $plugin_version = $plugin_data['Version'];
            return $plugin_version;
        }

        /**
         * Show action links on the plugin screen.
         * @param mixed $links Plugin Action links
         * @return array $action_links
         * @since 5.0
         */

        public static function wcap_plugin_action_links( $links ) {
            $action_links = array(
                'settings' => '<a href="' . admin_url( 'admin.php?page=woocommerce_ac_page&action=emailsettings' ) . '" title="' . esc_attr( __( 'View WooCommerce abandoned Cart Settings', 'woocommerce-ac' ) ) . '">' . __( 'Settings', 'woocommerce-ac' ) . '</a>',
            );

            $wcap_is_import_page_displayed = get_option( 'wcap_import_page_displayed' );

            $wcap_is_lite_data_imported    = get_option( 'wcap_lite_data_imported' );
            
            if ( 'yes' == $wcap_is_import_page_displayed && ( false === $wcap_is_lite_data_imported || 'no' == $wcap_is_lite_data_imported ) ) {
                
                $action_links = array(
                'settings' => '<a href="' . admin_url( 'admin.php?page=woocommerce_ac_page&action=emailsettings' ) . '" title="' . esc_attr( __( 'View WooCommerce abandoned Cart Settings', 'woocommerce-ac' ) ) . '">' . __( 'Settings', 'woocommerce-ac' ) . '</a>',
                'impot_lite_data' => '<a id = "wcap_plugin_page_import" href="' . admin_url( 'admin.php?page=wcap-update' ) . '" title="' . esc_attr( __( 'Import data from Lite version.', 'woocommerce-ac' ) ) . '">' . __( 'Import from Lite version', 'woocommerce-ac' ) . '</a>',
                );                
            }            
            return array_merge( $action_links, $links );
        }
        /**
         * Show row meta on the plugin screen.
         * @param mixed $links Plugin Action links
         * @param string $file Plugin path
         * @return array $links Plugin Action links
         * @since 5.0
         */
        public static function wcap_plugin_row_meta( $links, $file ) {
            $plugin_base_name  =  dirname ( dirname ( plugin_basename( __FILE__ ) ) );
            $plugin_base_name .= '/wpurban-ac.php';

            if ( $file == $plugin_base_name ) {
                $row_meta = array(
                    'docs'    => '<a href="' . esc_url( apply_filters( 'woocommerce_abandoned_cart_docs_url'   , 'https://www.tychesoftwares.com/docs/docs/abandoned-cart-pro-for-woocommerce/' ) ) . '" title="' . esc_attr( __( 'View WooCommerce abandoned Cart Documentation', 'woocommerce-ac' ) ) . '">' . __( 'Docs', 'woocommerce-ac' ) . '</a>',
                    'support' => '<a href="' . esc_url( apply_filters( 'woocommerce_abandoned_cart_support_url', 'https://tychesoftwares.freshdesk.com/' ) ) . '" title="' . esc_attr( __( 'Submit Ticket', 'woocommerce-ac' ) ) . '">' . __( 'Submit Ticket', 'woocommerce-ac' ) . '</a>',
                );
                return array_merge( $links, $row_meta );
            }
            return (array) $links;
        }

        /**
         * Check if user have the permission to access the WooCommerce pages.
         * @since 5.0
         */
        public static function wcap_check_user_can_manage_woocommerce() {
            // Check the user capabilities
            if ( ! current_user_can( 'manage_woocommerce' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.', 'woocommerce-ac' ) );
            }
        }

        /**
         * It will return the current action.
         * @return string $wcap_action Action name
         * @since 5.0
         */
        public static function wcap_get_action() {
            $wcap_action = "";
            if ( isset( $_GET['action'] ) ) {
                $wcap_action = $_GET['action'];
            }

            /**
             * @since : 4.2
             * This is done as we are sending the manaul email with bulk action.
             * Bulk action do not allow to give the multiple parameter, so in single parameter we are giving the long 
             * string with all needed data.
             * So for that we need to break the string.
             */
            if ( isset( $_GET['action2'] ) ) {
                if ( "-1" == $_GET['action'] ) {
                    $wcap_action = $_GET['action2'];
                    if( strpos( $wcap_action, "wcap_manual_email"  ) !== false  ) {
                        $explode_action = explode ( '&' , $_GET['action2'] );
                        $wcap_action    = $explode_action [0];
                        $_GET['mode']   = 'wcap_manual_email';
                    }
                } else {
                    $wcap_action = $_GET['action'];
                    if( strpos( $wcap_action, "wcap_manual_email"  ) !== false  ) {
                        $explode_action = explode ( '&' , $_GET['action'] );
                        $wcap_action    = $explode_action [0];
                        $_GET['mode']   = 'wcap_manual_email';
                    }
                }
            }

            if ( isset( $_GET['action2'] ) ) {
                if ( "-1" == $_GET['action'] ){
                    $wcap_action = $_GET['action2'];
                    if( strpos( $wcap_action, "wcap_add_agile" ) !== false  ) {
                        $explode_action = explode ( '&' , $_GET['action2'] );
                        $wcap_action    = $explode_action [0];
                        $_GET['mode']   = 'wcap_add_agile';
                    }
                } else {
                    $wcap_action = $_GET['action'];
                    if( strpos( $wcap_action, "wcap_add_agile" ) !== false  ) {
                        $explode_action = explode ( '&' , $_GET['action'] );
                        $wcap_action    = $explode_action [0];
                        $_GET['mode']   = 'wcap_add_agile';
                    }
                }
            }
            if ( "-1" == $wcap_action && isset( $_GET['wcap_action'] ) ) {

                $wcap_action    = $_GET['wcap_action'];
            }
            return $wcap_action;
        }

        /**
         * It will return the mode of the plugin.
         * @return $wcap_mode Mode name
         * @since 5.0
         */
        public static function wcap_get_mode () {

            $wcap_mode = "";
            if ( isset( $_GET['mode'] ) ){
                $wcap_mode = $_GET['mode'];
            }
            return $wcap_mode;
        }

        /**
         * Returns the section set for the admin page
         * @since 7.9
         */
        public static function wcap_get_section() {
            return ( isset( $_GET[ 'section' ] ) ) ? $_GET[ 'section' ] : 'emailtemplates';
        }
        /**
         * It will retunrn the user selectd action from the below bulk action editor.
         * @return string $wcap_action_two Action name
         * @since 5.0
         */
        public static function wcap_get_action_two() {
            $wcap_action_two = "";

            if ( isset( $_GET['action2'] ) ) {
                $wcap_action_two = $_GET['action2'];
            }
            return $wcap_action_two;
        }

        /**
         * It will return the abandoned cart ids from the url.
         * @return string $wcap_ac_ids Abandoned cart ids
         * @since 5.0
         */
        public static function wcap_get_abandoned_cart_ids_from_get() {

            $wcap_ac_ids = isset( $_GET['abandoned_order_id'] ) ? $_GET['abandoned_order_id'] : false;
            return $wcap_ac_ids;
        }

        /**
         * It will return the template id from the url.
         * @return string $wcap_template_ids Template ids
         * @since 5.0
         */
        public static function wcap_get_template_ids_from_get(){

            $wcap_template_ids = isset( $_GET['template_id'] ) ? $_GET['template_id'] : false;
            return $wcap_template_ids;
        }

        /**
         * It will return the email sent id from the url.
         * @return string $wcap_email_sent_ids Email sent ids
         * @since 5.0
         */
        public static function wcap_get_email_sent_ids_from_get() {

            $wcap_email_sent_ids = isset( $_GET['wcap_email_sent_id'] ) ? $_GET['wcap_email_sent_id'] : false;
            return $wcap_email_sent_ids;
        }

        /**
         * It will return the selected action by the admin based on the url.
         * @return string $wcap_notice_action Notice action
         * @since 5.0
         */
        public static function wcap_get_notice_action () {

            $wcap_notice_action = "";

            if ( isset( $_GET ['wcap_deleted'] )            && 'YES' == $_GET['wcap_deleted'] ){
                $wcap_notice_action = 'wcap_deleted';      
            }

            if ( isset( $_GET ['wcap_rec_deleted'] )        && 'YES' == $_GET['wcap_rec_deleted'] ){
                $wcap_notice_action = 'wcap_rec_deleted';         
            }

            if ( isset( $_GET ['wcap_abandoned_trash'] )    && 'YES' == $_GET['wcap_abandoned_trash'] ){
                $wcap_notice_action = 'wcap_abandoned_trash';         
            }

            if ( isset( $_GET ['wcap_abandoned_restore'] )  && 'YES' == $_GET['wcap_abandoned_restore'] ){
                $wcap_notice_action = 'wcap_abandoned_restore';         
            }

            if ( isset( $_GET ['wcap_rec_trash'] )          && 'YES' == $_GET['wcap_rec_trash'] ){
                $wcap_notice_action = 'wcap_rec_trash';         
            }            

            if ( isset( $_GET ['wcap_rec_restore'] )        && 'YES' == $_GET['wcap_rec_restore'] ){
                $wcap_notice_action = 'wcap_rec_restore';         
            }

            if ( isset( $_GET ['wcap_sent_email_restore'] ) && 'YES' == $_GET['wcap_sent_email_restore'] ){
                $wcap_notice_action = 'wcap_sent_email_restore';         
            }

            if ( isset( $_GET ['wcap_template_deleted'] )   && 'YES' == $_GET['wcap_template_deleted'] ){
                $wcap_notice_action = 'wcap_template_deleted';         
            }

            if ( isset( $_GET ['wcap_manual_email_sent'] )  && 'YES' == $_GET['wcap_manual_email_sent'] ){
                $wcap_notice_action = 'wcap_manual_email_sent';         
            }

            if ( isset( $_GET ['wcap_lite_import'] )  && 'YES' == $_GET['wcap_lite_import'] ){
                $wcap_notice_action = 'wcap_import_lite_to_pro';         
            }            

            return $wcap_notice_action;
        }

        /**
         * It will return the WC order id.
         * @param object|array $wcap_order WooCommerce order
         * @globals mixed $woocommerce 
         * @return int|string $wcap_order_id Order Id
         * @since 5.0
         * @todo Change the function name
         */
        public static function wcap_get_ordrer_id( $wcap_order) {
            global $woocommerce;
            $wcap_order_id = '';
            if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {
                $wcap_order_id = $wcap_order->get_id();
            }else{
                $wcap_order_id = $wcap_order->id;
            }

            return $wcap_order_id;
        }

        /**
         * It will check if the abandoned cart email is sent to the cart id or not.
         * @param int|string $abandoned_order_id Abandoned cart id
         * @globals mixed $wpdb
         * @return true Email sent
         * @return false Email not sent
         * @since 5.0
         */
        public static function wcap_check_email_sent_for_order( $abandoned_order_id ) {
            global $wpdb;
            $query   = "SELECT id FROM `" . WCAP_EMAIL_SENT_HISTORY_TABLE . "` WHERE abandoned_order_id = %d";
            $results = $wpdb->get_results( $wpdb->prepare( $query, $abandoned_order_id ) );
            if ( count( $results ) > 0 ) {
                return true;
            }
            return false;
        }

        /**
         * This function is used to encode the string.
         * @param string $validate String need to encrypt
         * @return string $validate_encoded Encrypted string
         * @since 5.0
         */
        public static function encrypt_validate( $validate ) {
            $cryptKey         = get_option( 'ac_security_key' );
            $validate_encoded = Wcap_Aes_Ctr::encrypt( $validate, $cryptKey, 256 );
            return( $validate_encoded );
        }

        /**
         * It will return the user selected language.
         * @return string $wcap_current_user_lang User selected language
         * @since 5.0
         */

        public static function wcap_get_language () {

            $wcap_current_user_lang = 'en';
            if ( function_exists( 'icl_register_string' ) ) {
              $wcap_current_user_lang = ICL_LANGUAGE_CODE;
            }

            return $wcap_current_user_lang;
        }

        /**
         * When cron job time changed this function will be called.
         * It is used to reset the cron time again.
         * @since 5.0
         */
        public static function wcap_cron_time_duration() {
            wp_clear_scheduled_hook('woocommerce_ac_send_email_action');
        }

        /**
         * We have changed the WooCommerce session expiration date. 
         * @param int $seconds 
         * @return int $days_7 7 days in seconds
         * @since 5.0 
         */
        public static function wcap_set_session_expiring( $seconds ) {
            $hours_23 = 60 * 60 * 23 ;
            $days_7 = $hours_23 * 7 ;
            return $days_7;
        }

        /**
         * We have changed the WooCommerce session expiration date.
         * @param int $seconds 
         * @return int $days_7 7 days in seconds
         * @since 5.0
         */
        public static function wcap_set_session_expired( $seconds ) {
            $hours_24 = 60 * 60 * 24 ;
            $days_7 = $hours_24 * 7 ;
            return $days_7;
        }

        /**
         * It will remove the cart updated hook from our plugin.
         * @since 5.0
         */
        public static function wcap_remove_action_hook() {
            if ( class_exists( 'Wcap_Cart_Updated' ) ) {
                remove_action( 'woocommerce_cart_updated', array( 'Wcap_Cart_Updated', 'wcap_store_cart_timestamp' ) );
            }
        }

        /**
         * To output the print and preview email template we need it.
         * @since 5.0
         */
        public static function wcap_output_buffer() {
            ob_start();
        }

        /**
         * We will return the customers IP address. We are using the WooCommerce geoloctaion.
         * @return string User IP address
         * @since 5.0
         */
        public static function wcap_get_client_ip() {            
            $ipaddress = WC_Geolocation::get_ip_address();
            return $ipaddress;
        }

        /**
         * We will return the user role on the user id.
         * @param int|string $uid User Id
         * @globals mixed $wpdb
         * @return string $roles User role
         * @since 5.0
         */
        public  static function wcap_get_user_role( $uid ) {
            global $wpdb;
            $role = $wpdb->get_var("SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'wp_capabilities' AND user_id = {$uid}");
            
            if( !$role ){
              return '';  
            } 
            $rarr  = unserialize($role);
            
            $roles = is_array($rarr) ? array_keys( $rarr ) : array('non-user');

            /**
             * When store have the wpml it have so many user roles to fix the user role for admin we have applied this fix. 
             */ 
            if ( in_array( 'administrator' , $roles) ){
                
                $roles[0] = 'administrator';
            }

            return ucfirst ( $roles[0] );
        }

        /**
         * We are checking if the customer IP addres is blocked by the admin.
         * @param string $wcap_user_ip_address User IP address
         * @return true|false $wcap_restricted_ip_data_exists IP address restricted | IP address not restricted
         * @since 5.0
         */
        public static function wcap_is_ip_restricted ( $wcap_user_ip_address ) {

            $wcap_restricted_ip_data_exists = false;
            $wcap_restricted_ip_records          = get_option ( 'wcap_restrict_ip_address' );
            if ( false != $wcap_restricted_ip_records ) {
                $explode_on_new_line_data_ip_records = explode( PHP_EOL, $wcap_restricted_ip_records );

                $implode_ip_address = '';
                $explode_ip_address = array();

                if ( count ( $explode_on_new_line_data_ip_records ) > 1 ){
                    $implode_ip_address = implode( ",", $explode_on_new_line_data_ip_records );
                    $explode_ip_address = explode( ",", $implode_ip_address );
                }else {
                    $explode_ip_address = explode( ",", $wcap_restricted_ip_records );
                }

                $trimmed_explode_ip_address = array_map( 'trim' , $explode_ip_address );

                if ( in_array ( $wcap_user_ip_address , $trimmed_explode_ip_address ) ){
                    $wcap_restricted_ip_data_exists = true;
                }

                $block_ip_address = Wcap_Common::block_users ( $trimmed_explode_ip_address, $wcap_user_ip_address );

                if ( $block_ip_address == 1 ){
                    $wcap_restricted_ip_data_exists = true;
                }
            }

            return $wcap_restricted_ip_data_exists;
        }

        /**
         * We are checking if the customer Email addres is blocked by the admin.
         * @param string $current_user_email_address User Email address
         * @return true|false $wcap_restricted_email_data_exists Email address restricted | Email address not restricted
         * @since 5.0
         */
        public static function wcap_is_email_address_restricted ( $current_user_email_address ) {

            $wcap_restricted_email_data_exists = false;

            $wcap_restricted_email_records          = get_option ( 'wcap_restrict_email_address' );
            if ( false != $wcap_restricted_email_records ) {
                $explode_on_new_line_data_email_records = explode( PHP_EOL, $wcap_restricted_email_records );

                $implode_email_address = '';
                $explode_email_address = array();

                if ( count ( $explode_on_new_line_data_email_records ) > 1 ){
                    $implode_email_address = implode( "," , $explode_on_new_line_data_email_records );
                    $explode_email_address = explode( ",", $implode_email_address);
                }else {
                    $explode_email_address = explode( ",", $wcap_restricted_email_records );
                }

                
                $trimmed_explode_email_address     = array_map( 'trim' , $explode_email_address );
                $trimmed_explode_email_address     = array_map( 'strtolower' , $explode_email_address );

                $current_user_email_address        =  strtolower( $current_user_email_address ) ;

                if ( in_array ( $current_user_email_address , $trimmed_explode_email_address ) && '' != $current_user_email_address ){
                    $wcap_restricted_email_data_exists = true;
                }
            }
            return $wcap_restricted_email_data_exists;
        }

        /**
         * We are checking if the customer Domain name is blocked by the admin.
         * @param string $current_user_email_address User Email address
         * @return true|false $wcap_restricted_domain_data_exists Domain restricted | Domain not restricted
         * @since 5.0
         */
        public static function wcap_is_domain_restricted ( $current_user_email_address ) {

            $wcap_restricted_domain_data_exists = false;

            $wcap_restricted_domain_records          = get_option ( 'wcap_restrict_domain_address' );
            if ( false != $wcap_restricted_domain_records ) {
                $explode_on_new_line_data_domain_records = explode( PHP_EOL, $wcap_restricted_domain_records );

                $implode_domain_address = '';
                $explode_domain_address = array();

                if ( count ( $explode_on_new_line_data_domain_records ) > 1 ){
                    $implode_domain_address = implode ( "," , $explode_on_new_line_data_domain_records );
                    $explode_domain_address = explode( ",", $implode_domain_address);
                }else {
                    $explode_domain_address = explode( ",", $wcap_restricted_domain_records );
                }
                $get_domain = '';
                $explode_user_email_addresson_at = array();

                $explode_user_email_addresson_at = explode ("@" , $current_user_email_address );

                if ( isset( $explode_user_email_addresson_at [1] ) && '' != $explode_user_email_addresson_at [1] ){
                    $get_domain = $explode_user_email_addresson_at [1];
                }

                
                $trimmed_explode_domain_address = array_map( 'trim' , $explode_domain_address);
                $trimmed_explode_domain_address = array_map( 'strtolower' , $explode_domain_address);
                $get_domain                     = strtolower( $get_domain );

                if ( in_array ( $get_domain , $trimmed_explode_domain_address ) ){
                    $wcap_restricted_domain_data_exists = true;
                }
            }
            return $wcap_restricted_domain_data_exists;
        }

        /**
         * It will break the bulk of the IP address and verify each email IP is blocked or not.
         * @param array $user_inputs All blocked IP
         * @param string $customer_ip_address User IP address
         * @return true|false $block IP blocked | IP not blocked
         */
        public static function block_users( $user_inputs, $customer_ip_address ) {

            $userOctets = explode( '.', $customer_ip_address ); // get the client's IP address and split it by the period character
            $userOctetsCount = count($userOctets);  // Number of octets we found, should always be four

            $block = false; // boolean that says whether or not we should block this user

            foreach($user_inputs as $ipAddress) { // iterate through the list of IP addresses
                $octets = explode('.', $ipAddress);
                if(count($octets) != $userOctetsCount) {
                    continue;
                }

                for($i = 0; $i < $userOctetsCount; $i++) {
                    if($userOctets[$i] == $octets[$i] || $octets[$i] == '*') {
                        continue;
                    } else {
                        break;
                    }
                }

                if($i == $userOctetsCount) { // if we looked at every single octet and there is a match, we should block the user
                    $block = true;
                    break;
                }
            }

            return $block;
        }

        /**
         * We will return the recovered order amount based on the time period has been given.
         * @param date $start_date_range Admin selected start date
         * @param date $end_date_range Admin selected end date
         * @param string $get_section_result Section name
         * @globals mixed $wpdb
         * @return int $return_recovered_count Count of recovered order
         * @since 5.0
         */
        public static function wcap_get_reovered_order_count( $start_date_range = '' , $end_date_range = '' , $get_section_result = '' ) {
            global $wpdb;
            $return_recovered_count = 0;

            $start_date = strtotime( $start_date_range." 00:01:01" );
            $end_date   = strtotime( $end_date_range." 23:59:59" );

            $blank_cart_info       = '{"cart":[]}';
            $blank_cart_info_guest = '[]';

            $ac_cutoff_time        = get_option( 'ac_cart_abandoned_time' );
            $cut_off_time          = $ac_cutoff_time * 60;
            $current_time          = current_time( 'timestamp' );
            $compare_time          = $current_time - $cut_off_time;

            $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
            $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
            $current_time          = current_time ('timestamp');
            $compare_time_guest    = $current_time - $cut_off_time_guest;

            switch ( $get_section_result ) {
                case 'wcap_all_rec':
                    $query_ac        = "SELECT recovered_cart FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND recovered_cart > 0 AND wcap_trash = '') OR ( user_type = 'GUEST' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND recovered_cart > 0 AND wcap_trash = '' )  ORDER BY recovered_cart desc ";

                    $ac_results      = $wpdb->get_results( $query_ac );
                    $return_recovered_count = count( $ac_results );
                break;

                case 'wcap_trash_rec':
                    $query_ac        = "SELECT recovered_cart FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."`  WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND recovered_cart > 0 AND wcap_trash = '1') OR ( user_type = 'GUEST' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND recovered_cart > 0 AND wcap_trash = '1' ) ORDER BY recovered_cart desc ";
                    $ac_results      = $wpdb->get_results( $query_ac );
                    $return_recovered_count = count( $ac_results );
                break;

                default:
                    $query_recover  = "SELECT COUNT(wach.recovered_cart) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` as wach 
                        LEFT JOIN ".$wpdb->prefix."posts AS wposts ON wach.recovered_cart = wposts.ID 
                        WHERE 
                        wach.recovered_cart != 0 
                        AND 
                        wach.abandoned_cart_info NOT LIKE '%$blank_cart_info%' 
                        AND 
                        wach.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' 
                        AND 
                        wach.wcap_trash = '' 
                        AND 
                        wach.recovered_cart = wposts.ID ";
                    $return_recovered_count    = $wpdb->get_var( $query_recover );
                    break;
            }

            return $return_recovered_count;

        }

        /**
         * It will give you the total abandoned carts.
         *
         * @param string $get_section_result Section name
         * @globals mixed $wpdb
         * @return int $return_abandoned_count Count of abandoned order
         * @since 5.0
         */
        public static function wcap_get_abandoned_order_count( $get_section_result ) {
            global $wpdb;
            $wcap_class     = new Woocommerce_Abandon_Cart();
            $duration_range = "";
            if ( isset( $_POST['duration_select'] ) ) {
                $duration_range = $_POST['duration_select'];
            }
            if( "" == $duration_range ) {
                if ( isset( $_GET['duration_select'] ) && '' != $_GET['duration_select'] ) {
                    $duration_range = $_GET['duration_select'];
                }
            }
            if ( isset( $_SESSION ['duration'] ) && '' != $_SESSION ['duration'] ) {
                $duration_range     = $_SESSION ['duration'];
            }

            if ( "" == $duration_range ) {
                $duration_range = "last_seven";
            }
            $start_date_range = "";
            if ( isset( $_POST['start_date'] ) && '' != $_POST['start_date'] ){
                $start_date_range = $_POST['start_date'];
            }
            if ( isset( $_SESSION ['start_date'] ) &&  '' != $_SESSION ['start_date'] ) {
                $start_date_range = $_SESSION ['start_date'];
            }
            if ( "" == $start_date_range ) {
               $start_date_range = $wcap_class->start_end_dates[$duration_range]['start_date'];
            }
            $end_date_range = "";
            if ( isset( $_POST['end_date'] ) && '' != $_POST['end_date'] ){
                $end_date_range = $_POST['end_date'];
            }

            if ( isset($_SESSION ['end_date'] ) && '' != $_SESSION ['end_date'] ){
                $end_date_range = $_SESSION ['end_date'];
            }

            if ( "" == $end_date_range ) {
                $end_date_range = $wcap_class->start_end_dates[$duration_range]['end_date'];
            }

            $start_date              = strtotime( $start_date_range." 00:01:01" );
            $end_date                = strtotime( $end_date_range." 23:59:59" );

            $return_abandoned_count = 0;

            $blank_cart_info       = '{"cart":[]}';
            $blank_cart_info_guest = '[]';

            $ac_cutoff_time        = get_option( 'ac_cart_abandoned_time' );
            $cut_off_time          = $ac_cutoff_time * 60;
            $current_time          = current_time( 'timestamp' );
            $compare_time          = $current_time - $cut_off_time;

            $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
            $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
            $current_time          = current_time ('timestamp');
            $compare_time_guest    = $current_time - $cut_off_time_guest;

            switch ( $get_section_result ) {
                case 'wcap_all_abandoned_lifetime':
                    $query_ac        = "SELECT * FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND recovered_cart = 0 AND wcap_trash = '' AND cart_ignored <> '1' ) OR ( user_type = 'GUEST' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND recovered_cart = 0 AND wcap_trash = '' AND cart_ignored <> '1' ) ORDER BY recovered_cart desc ";
                    $ac_results      = $wpdb->get_results( $query_ac );
                    $return_abandoned_count = count( $ac_results );
                break;

                case 'wcap_all_abandoned':
                // 7.14.0 - @todo - remove this case
                    $query_ac        = "SELECT * FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND recovered_cart = 0 AND wcap_trash = '' AND cart_ignored <> '1' ) OR ( user_type = 'GUEST' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND recovered_cart = 0 AND wcap_trash = '' AND cart_ignored <> '1' ) ORDER BY recovered_cart desc ";
                    $ac_results      = $wpdb->get_results( $query_ac );
                    $return_abandoned_count = count( $ac_results );
                break;

                case 'wcap_trash_abandoned':
                    $query_ac        = "SELECT * FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND recovered_cart = 0 AND wcap_trash = '1' AND cart_ignored <> '1' ) OR ( user_type = 'GUEST' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND recovered_cart = 0 AND wcap_trash = '1' AND cart_ignored <> '1' ) ORDER BY recovered_cart desc ";
                    $ac_results      = $wpdb->get_results( $query_ac );
                    $return_abandoned_count = count( $ac_results );
                break;

                case 'wcap_all_registered':
                // 7.14.0 - @todo - remove this case
                    $query_ac        = "SELECT * FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND recovered_cart = 0 AND wcap_trash = '' AND cart_ignored <> '1' ) ORDER BY recovered_cart desc ";
                    $ac_results      = $wpdb->get_results( $query_ac );
                    $return_abandoned_count = count( $ac_results );
                break;

                case 'wcap_all_guest':
                // 7.14.0 - @todo - remove this case
                    $query_ac        = "SELECT * FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'GUEST' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND recovered_cart = 0 AND wcap_trash = '' AND user_id >= 63000000 AND cart_ignored <> '1' ) ORDER BY recovered_cart desc ";
                    $ac_results      = $wpdb->get_results( $query_ac );
                    $return_abandoned_count = count( $ac_results );
                break;

                case 'wcap_all_visitor':
                // 7.14.0 - @todo - remove this case
                    $query_ac        = "SELECT * FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'GUEST' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND recovered_cart = 0 AND wcap_trash = '' AND user_id = 0 AND cart_ignored <> '1' ) ORDER BY recovered_cart desc ";
                    $ac_results      = $wpdb->get_results( $query_ac );
                    $return_abandoned_count = count( $ac_results );
                break;

                case 'wcap_all_unsubscribe_carts':

                $query_ac = "SELECT * FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND wcap_trash = '' AND unsubscribe_link = '1' ORDER BY abandoned_cart_time DESC";
                $ac_results      = $wpdb->get_results( $query_ac );
                $return_abandoned_count = count( $ac_results );
                break;
    

                default:
                    
                break;
            }

            return $return_abandoned_count;
        }

        /**
         * It will get the total amount of email sent for the time period.
         * @param date $start_date_range Admin selected start date
         * @param date $end_date_range Admin selected end date
         * @param string $get_section_result Section name
         * @globals mixed $wpdb
         * @return int $return_sent_email_count Count of email sent
         * @since 5.0
         */
        public static function wcap_get_sent_emails_count( $start_date_range, $end_date_range, $get_section_result ) {
            global $wpdb;
            $return_sent_email_count = 0;

            $start_date            = strtotime( $start_date_range." 00:01:01" );
            $end_date              = strtotime( $end_date_range." 23:59:59" );
            $start_date_db         = date( 'Y-m-d H:i:s', $start_date );
            $end_date_db           = date( 'Y-m-d H:i:s', $end_date );

            $blank_cart_info       = '{"cart":[]}';
            $blank_cart_info_guest = '[]';

            $ac_cutoff_time        = get_option( 'ac_cart_abandoned_time' );
            $cut_off_time          = $ac_cutoff_time * 60;
            $current_time          = current_time( 'timestamp' );
            $compare_time          = $current_time - $cut_off_time;

            $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
            $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
            $current_time          = current_time ('timestamp');
            $compare_time_guest    = $current_time - $cut_off_time_guest;

            switch ( $get_section_result ) {
                case 'wcap_all_sent':
                    $query_ac_sent          = "SELECT wpsh.* FROM " . WCAP_EMAIL_SENT_HISTORY_TABLE . " as wpsh WHERE wpsh.sent_time >= %s AND wpsh.sent_time <= %s ORDER BY wpsh.id DESC";
                    $ac_results_sent        = $wpdb->get_results( $wpdb->prepare( $query_ac_sent, $start_date_db, $end_date_db ) );
                    $return_sent_email_count = count( $ac_results_sent );
                break;
                
                default:
                    # code...
                break;
            }

            return $return_sent_email_count;
        }

        /**
         * It will get the session key for the gusst users.
         * @return string $wcap_session_id Session key
         * @since 5.0
         */
        public static function wcap_get_guest_session_key () {

            $wcap_get_cookie = WC()->session->get_session_cookie();
            if ( $wcap_get_cookie ) {
                $wcap_session_id = $wcap_get_cookie[0];
            } else {
                $wcap_session_id = 0;
            }
            return $wcap_session_id;
        }

        /**
         * It will automatically populate data of the guest user when user comes from the abandoned cart reminder email.
         * @param array $fields List of fields
         * @return array $fields List of fields
         * @since 5.0
         */
        public static function guest_checkout_fields( $fields ) {

            if ( !is_ajax() && is_checkout() ) {

                if ( wcap_get_cart_session( 'wcap_guest_first_name' ) != "" ) {
                    $_POST['billing_first_name'] = wcap_get_cart_session( 'wcap_guest_first_name' );
                }
                if ( wcap_get_cart_session( 'wcap_guest_last_name' ) != "" ) {
                    $_POST['billing_last_name'] = wcap_get_cart_session( 'wcap_guest_last_name' );
                }
                if ( wcap_get_cart_session( 'wcap_populate_email' ) != "" ) {
                    $_POST['billing_email'] = wcap_get_cart_session( 'wcap_populate_email' );
                } else if ( wcap_get_cart_session( 'wcap_guest_email' ) != "" ) {
                    $_POST['billing_email'] = wcap_get_cart_session( 'wcap_guest_email' );
                }
                if ( wcap_get_cart_session( 'wcap_guest_phone' ) != "" ) {
                    $_POST['billing_phone'] = wcap_get_cart_session( 'wcap_guest_phone' );
                }
            }
            return $fields;
        }

        public static function wfacp_guest_checkout_fields( $field_value, $key, $fields ){

            if ( $key === 'billing_email' ) {
                if ( wcap_get_cart_session( 'wcap_populate_email' ) != "" ) {
                    $field_value = $_POST['billing_email'] = wcap_get_cart_session( 'wcap_populate_email' );
                } else if ( wcap_get_cart_session( 'wcap_guest_email' ) != "" ) {
                    $field_value = $_POST['billing_email'] = wcap_get_cart_session( 'wcap_guest_email' );
                }
            }
            return $field_value;
        }

        /**
         * It will replace the email body merge codes with content.
         * @param string $body_email_preview Email body
         * @globals mixed $wpdb
         * @return strinig $body_email_preview Email body
         * @since 7.0
         */
        public static function wcap_replace_email_body_merge_code ( $body_email_preview ) {
            global $wpdb;

            $wcap_get_current_user_id  = get_current_user_id();
            $wcap_product_image_height = get_option( 'wcap_product_image_height' );
            $wcap_product_image_width  = get_option( 'wcap_product_image_width' );
            $user_email_biiling = get_user_meta( $wcap_get_current_user_id, 'billing_email', true );
            if( isset( $user_email_biiling ) && "" == $user_email_biiling ) {
                $user_data  = get_userdata( $wcap_get_current_user_id );
                if( isset( $user_data->user_email ) && "" != $user_data->user_email ) {
                    $user_email = $user_data->user_email;
                }
            } else {
                $user_email = $user_email_biiling;
            }
            
            // default the name variables
            $user_first_name = '';
            $user_last_name = '';
            
            $user_first_name_temp = get_user_meta( $wcap_get_current_user_id, 'billing_first_name', true );
            if( isset( $user_first_name_temp ) && "" == $user_first_name_temp ) {
                $user_data  = get_userdata( $wcap_get_current_user_id );
                if( isset( $user_data->first_name ) && "" != $user_data->first_name ) {
                    $user_first_name = $user_data->first_name;
                }
            } else {
                $user_first_name = $user_first_name_temp;
            }

            $user_last_name_temp = get_user_meta( $wcap_get_current_user_id, 'billing_last_name', true );
            if( isset( $user_last_name_temp ) && "" == $user_last_name_temp ) {
                $user_data  = get_userdata( $wcap_get_current_user_id );
                if( isset( $user_data->last_name ) && "" != $user_data->last_name ) {
                    $user_last_name = $user_data->last_name;
                }
            } else {
                $user_last_name = $user_last_name_temp;
            }
            $body_email_preview        = str_replace( '{{customer.firstname}}', $user_first_name, $body_email_preview );
            $body_email_preview        = str_replace( '{{customer.lastname}}', $user_last_name, $body_email_preview );
            $body_email_preview        = str_replace( '{{customer.fullname}}', $user_first_name." ".$user_last_name, $body_email_preview );

            $wcap_product_query = "SELECT wpost.id, wpost.post_title from ".$wpdb->prefix."posts as wpost 
                                    LEFT JOIN ".$wpdb->prefix."postmeta as wpm ON wpost.id = wpm.post_id 
                                    WHERE 
                                    wpost.post_type = 'product' 
                                    AND 
                                    wpost.post_status= 'publish' 
                                    AND 
                                    wpm.meta_key = '_regular_price' 
                                    AND wpm.meta_value > '0' 
                                    ORDER BY id DESC LIMIT 1";
            
            $wcap_get_products  = $wpdb->get_results( $wcap_product_query );

            $wcap_product_id = '';
            $wcap_products = array();

            if ( count( $wcap_get_products ) > 0 ) {
                $checkout_link_track              = wc_get_page_permalink( 'checkout' );
                $email_body = $body_email_preview;
                if( preg_match( "{{item.image}}", $email_body, $matched ) || preg_match( "{{item.name}}", $email_body, $matched ) || preg_match( "{{item.price}}", $email_body, $matched ) || preg_match( "{{item.quantity}}", $email_body, $matched ) || preg_match( "{{item.subtotal}}", $email_body, $matched ) || preg_match( "{{cart.total}}", $email_body, $matched ) ) {

                    $replace_html      = '';
                    
                    $cart_total        = $item_subtotal = $item_total = $line_subtotal_tax_display =  $after_item_subtotal = $after_item_subtotal_display = 0;
                    $line_subtotal_tax = '';
                    $wcap_include_tax  = get_option( 'woocommerce_prices_include_tax' );
                    $wcap_include_tax_setting = get_option( 'woocommerce_calc_taxes' );
                    // This array will be used to house the columns in the hierarchy they appear
                    $position_array = array();
                    $start_position = $end_position = $image_start_position = $name_start_position = 0;
                    //check which columns are present
                    if( preg_match( "{{item.image}}", $email_body, $matched ) ) {
                        $image_start_position = strpos( $email_body, '{{item.image}}' );
                        $position_array[ $image_start_position ] = 'image';
                    }
                    if( preg_match( "{{item.name}}", $email_body, $matched ) ) {
                        $name_start_position = strpos( $email_body,'{{item.name}}' );
                        $position_array[ $name_start_position ] = 'name';
                    }
                    if( preg_match( "{{item.price}}", $email_body, $matched ) ) {
                        $price_start_position = strpos( $email_body, '{{item.price}}' );
                        $position_array[ $price_start_position ] = 'price';
                    }
                    if( preg_match( "{{item.quantity}}", $email_body, $matched ) ) {
                        $quantity_start_position = strpos( $email_body, '{{item.quantity}}' );
                        $position_array[ $quantity_start_position ] = 'quantity';
                    }
                    if( preg_match( "{{item.subtotal}}", $email_body, $matched ) ) {
                        $subtotal_start_position = strpos( $email_body,'{{item.subtotal}}' );
                        $position_array[ $subtotal_start_position ] = 'subtotal';
                    }
                    // Complete populating the array
                    ksort( $position_array );
                    $tr_array   = explode( "<tr", $email_body );
                    $check_html = $style = '';
                    foreach( $tr_array as $tr_key => $tr_value ) {
                        if( ( preg_match( "{{item.image}}", $tr_value, $matched ) || preg_match( "{{item.name}}", $tr_value, $matched) || preg_match( "{{item.price}}", $tr_value, $matched ) || preg_match( "{{item.quantity}}", $tr_value, $matched) || preg_match( "{{item.subtotal}}", $tr_value, $matched)) && ! preg_match( "{{cart.total}}", $tr_value, $matched ) && count( $wcap_get_products ) > 1 ) {

                            $style_start  = strpos( $tr_value, 'style' );
                            $style_end    = strpos( $tr_value, '>', $style_start );
                            $style_end    = $style_end - $style_start;
                            $style        = substr( $tr_value, $style_start, $style_end );
                            $tr_value     = "<tr" . $tr_value;
                            $end_position = strpos( $tr_value, '</tr>' );
                            $end_position = $end_position + 5;
                            $check_html   = substr( $tr_value, 0, $end_position );
                        }
                    }
                    $i            = 1;
                    $bundle_child = array();
                    foreach( $wcap_get_products as $k => $v ) {
                        $product   = wc_get_product( $v->id );

                        $wcap_products[ $i ] = new stdClass();
                        $wcap_products[ $i ]->product_id = $v->id;
                        
                        $image_size   = array( $wcap_product_image_width, $wcap_product_image_height, '1' );
                        $image_url    = Wcap_Common::wcap_get_product_image( $v->id, $image_size );

                        //$item_name    = $product->get_name() ; 
                        $item_name    = get_the_title( $v->id );
                        $prod_name    = apply_filters( 'wcap_product_name', $item_name );   
                        $quantity     = 1;
                        
                        if( isset( $wcap_include_tax ) && 'no' == $wcap_include_tax &&
                        isset( $wcap_include_tax_setting ) && 'yes' == $wcap_include_tax_setting ) {
                            $wcap_get_product_price = get_post_meta ( $v->id, '_regular_price' );
                            $wcap_product_price =  $wcap_get_product_price[0] ;
                            $after_item_subtotal = $wcap_product_price;
                            $item_subtotal       = $item_subtotal + $wcap_product_price;  
                            $line_subtotal_tax   = 7;
                            
                        } elseif ( isset( $wcap_include_tax ) && $wcap_include_tax == 'yes' &&
                        isset( $wcap_include_tax_setting ) && $wcap_include_tax_setting == 'yes' ) {
                            $wcap_get_product_price = get_post_meta ( $v->id, '_regular_price' );
                            $wcap_product_price =  $wcap_get_product_price[0] ;
                            $item_subtotal = $item_subtotal + $wcap_product_price;
                            $after_item_subtotal = $wcap_product_price;
                        } else {
                            
                            $wcap_get_product_price = get_post_meta ( $v->id, '_regular_price' );
                            $wcap_product_price =  $wcap_get_product_price[0] ;
                            $item_subtotal = $item_subtotal + $wcap_product_price;
                            $after_item_subtotal = $wcap_product_price;
                        }
                        //  Line total
                        $item_total            = $item_subtotal;
                        $item_price            = $item_subtotal / 1;
                        $item_subtotal_display = wc_price( $item_total );

                        $item_price            = wc_price( $item_price );
                        $cart_total            += $after_item_subtotal;

                        $item_subtotal         = $item_total = 0;
                        /*if( $i % 2 == 0 ) {
                            $replace_html .= '<tr>';
                        } else {*/
                            $replace_html .= '<tr ' . $style . '>';
                        /*}*/
                        foreach( $position_array as $k => $v ) {
                            switch( $v ) {
                                case 'image':
                                    $replace_html .= '<td style="text-align:center;"> <a href="' . $checkout_link_track . '">' . $image_url . '</a> </td>';
                                    break;
                                case 'name':
                                    $replace_html .= '<td style="text-align:center;"> <a href="' . $checkout_link_track . '">' . $prod_name . '</a> </td>';
                                    break;
                                case 'price':
                                    if ( '' == $item_price ) {
                                        $replace_html .= '<td></td>';
                                    } else {
                                        $replace_html .= '<td style="text-align:center;">' . $item_price . '</td>';
                                    }
                                    break;
                                case 'quantity':
                                    $replace_html .= '<td style="text-align:center;">' . $quantity . '</td>';
                                    break;
                                case 'subtotal':
                                    if ( '' == $item_subtotal_display ) {
                                        $replace_html .= '<td></td>';
                                    } else {
                                        $replace_html .= '<td style="text-align:center;">' . $item_subtotal_display . '</td>';
                                    }
                                    break;
                                default:
                                    $replace_html .= '<td></td>';
                            }
                        }
                        $replace_html .= '</tr>';
                        $i++;
                    }
                    $show_taxes = apply_filters('wcap_show_taxes', true);

                    if( $show_taxes && isset( $wcap_include_tax ) && 'no' == $wcap_include_tax &&
                        isset( $wcap_include_tax_setting ) && 'yes' == $wcap_include_tax_setting ) {

                        $wcap_line_subtotal_tax = wc_price( $line_subtotal_tax );

                        $replace_html .= '<tr>
                                            <td> </td>
                                            <td> </td>
                                            <td> </td>
                                            <td>'.__( "<strong>Tax:</strong>", "woocommerce-ac" ).'</td>
                                            <td> '. $wcap_line_subtotal_tax .'</td>
                                        </tr>';
                    }
                    // Calculate the cart total
                    if( isset( $wcap_include_tax ) && 'yes' == $wcap_include_tax &&
                        isset( $wcap_include_tax_setting ) && 'yes' == $wcap_include_tax_setting ) {

                        $cart_total                = wc_price( $cart_total ); 
                        $line_subtotal_tax_display = wc_price( 7 );
                        if ($show_taxes) {

                        $cart_total  = $cart_total . ' (includes Tax: '. $line_subtotal_tax_display .')';
                        
                        } else {
                            $cart_total  = $cart_total;
                        }

                    }elseif( isset( $wcap_include_tax ) && $wcap_include_tax == 'no' &&
                        isset( $wcap_include_tax_setting ) && 'yes' == $wcap_include_tax_setting ) {
                        $cart_total = $cart_total + $line_subtotal_tax ;
                        $cart_total = wc_price( $cart_total ); 
                    } else {

                        $cart_total = wc_price( $cart_total ); 
                    }

                    // Populate/Add the product rows
                    $email_body    = str_replace( $check_html, $replace_html, $email_body );    
                    $email_body    = str_replace( "{{cart.total}}", $cart_total, $email_body );

                    $wcap_product_image = $image_url;
                    $email_body    = str_replace( '{{item.image}}', $wcap_product_image, $email_body );

                    $email_body    = str_replace( '{{item.name}}', $prod_name, $email_body );

                    $email_body    = str_replace( '{{item.price}}', wc_price( $wcap_product_price ), $email_body );

                    $email_body    = str_replace( '{{item.quantity}}', 1, $email_body );

                    $email_body    = str_replace( '{{item.subtotal}}', $item_subtotal_display, $email_body );
                }
                $email_settings = array(
                    'image_height'  => $wcap_product_image_height,
                    'image_width'   => $wcap_product_image_width,
                    'site_url'      => get_option( 'siteurl' ),
                    'email_sent_id' => 0
                );

                $email_body = Wpuac_Send_Email_Using_Cron::wcap_replace_upsell_data( $email_body, $wcap_products, $email_settings );
                $email_body = Wpuac_Send_Email_Using_Cron::wcap_replace_crosssell_data( $email_body, $wcap_products, $email_settings );

                $body_email_preview = $email_body;
            }
            
            $shop_name                 = get_option( 'blogname' );
            $body_email_preview        = str_replace( '{{shop.name}}',  $shop_name, $body_email_preview );
            $shop_url                  = get_option( 'siteurl' );
            $body_email_preview        = str_replace( '{{shop.url}}',  $shop_url, $body_email_preview );
            if( version_compare( WOOCOMMERCE_VERSION, '3.2.0', ">=" ) ) {
                $store_address             = Wcap_Common::wcap_get_wc_address();
                $body_email_preview        = str_replace( "{{store.address}}", $store_address, $body_email_preview );
            }
            $body_email_preview        = str_replace( '{{coupon.code}}', "TESTCOUPON", $body_email_preview );
            $current_time_stamp        = current_time( 'timestamp' );
            $date_format               = date_i18n( get_option( 'date_format' ), $current_time_stamp );
            $time_format               = date_i18n( get_option( 'time_format' ), $current_time_stamp );
            $test_date                 = $date_format . ' ' . $time_format;
            $body_email_preview        = str_replace( '{{cart.abandoned_date}}', $test_date, $body_email_preview );
            $to_email_preview          = "";
            if ( isset( $_POST[ 'send_email_id' ] ) ) {
                $to_email_preview      = $_POST[ 'send_email_id' ];
            }
            $cart_url                  = wc_get_page_permalink( 'cart' );
            $body_email_preview        = str_replace( '{{cart.link}}', $cart_url, $body_email_preview );
            $checkout_url              = wc_get_page_permalink( 'checkout' );
            $body_email_preview        = str_replace( '{{checkout.link}}', $checkout_url, $body_email_preview );
            $body_email_preview        = str_replace( '{{cart.unsubscribe}}', $shop_url, $body_email_preview );
            $user_email                = get_option( 'admin_email' );
            $body_email_preview        = str_replace( '{{customer.email}}', $user_email, $body_email_preview );
            
            $admin_phone               = get_user_meta( $wcap_get_current_user_id,'billing_phone',true );
            $body_email_preview        = str_replace( '{{admin.phone}}', $admin_phone, $body_email_preview );

            $body_email_preview        = str_replace( '{{customer.phone}}', $admin_phone, $body_email_preview );

            return $body_email_preview;
        }

        /**
         * It will return the current section.
         * @return string $section Section name
         */
        public static function wcap_get_current_section () {
            $section = 'wcap_all_abandoned';
            if ( isset( $_GET[ 'wcap_section' ] ) ) {
                $section = $_GET[ 'wcap_section' ];
            }
            return $section ;
        }

        /**
         * Get the image to be attached to the emails
         * 
         * @param string|int $id Product ID or the variation ID
         * @param string $size (default: 'shop_thumbnail')
         * @param array $attr
         * @param bool True to return $placeholder if no image is found, or false to return an empty string.
         * @return string
         * 
         * @since 7.6.0
         */
        public static function wcap_get_product_image( $id, $size = 'shop_thumbnail', $attr = array(), $placeholder = true ) {

            if ( has_post_thumbnail( $id ) ) {
                $image = get_the_post_thumbnail( $id, $size, $attr );
                $image = apply_filters( "wcac_product_img_html", $image, $id, $size, $attr );
            } elseif ( ( $parent_id = wp_get_post_parent_id( $id ) ) && has_post_thumbnail( $parent_id ) ) {
                $image = get_the_post_thumbnail( $parent_id, $size, $attr );
            } elseif ( $placeholder ) {
                $image = wc_placeholder_img( $size );
            } else {
                $image = '';
            }
            return $image;
        }

        /**
         * Add the From Name for WooCommerce Template Emails via Filters
         * @param string $from_name From name
         * @return string 
         * @since 7.6.0
         */
        public static function wcap_from_name( $from_name ) {
            return get_option ( 'wcap_from_name' );
        }

        /**
         * Add the From Emails for WooCommerce Template Emails via Filters
         * @param string $from_address From address
         * @return string
         * @since 7.6.0
         */
        public static function wcap_from_address( $from_address ) {
            return get_option ( 'wcap_from_email' );
        }

        /**
         * Add the From Name and Emails for WooCommerce Template Emails via Filters
         * 
         * @since 7.6.0
         */
        public static function wcap_add_wc_mail_header( ) {

            add_filter( 'woocommerce_email_from_name', array( 'Wcap_Common', 'wcap_from_name' ) );
            add_filter( 'woocommerce_email_from_address', array( 'Wcap_Common', 'wcap_from_address' ) );

            add_action('phpmailer_init',     array( 'Wcap_Common', 'wcap_set_plaintext_body' ) );
        }

        /**
         * Remove the From Name and Emails for WooCommerce Template Emails via Filters.
         * This will be called after Abandoned Cart Emails are sent
         * 
         * @since 7.6.0
         */
        public static function wcap_remove_wc_mail_header( ) {

            remove_filter( 'woocommerce_email_from_name', array( 'Wcap_Common', 'wcap_from_name' ) );
            remove_filter( 'woocommerce_email_from_address', array( 'Wcap_Common', 'wcap_from_address' ) );

            remove_action( 'phpmailer_init', array( 'Wcap_Common', 'wcap_set_plaintext_body' ) );
        }

        /**
         * Add the From Name and Emails for WordPress Template Emails via Filters
         * 
         * @since 7.6.0
         */
        public static function wcap_add_wp_mail_header( ) {

            add_filter( 'wp_mail_from_name', array( 'Wcap_Common', 'wcap_from_name' ) );
            add_filter( 'wp_mail_from',      array( 'Wcap_Common', 'wcap_from_address' ) );

            add_action('phpmailer_init',     array( 'Wcap_Common', 'wcap_set_plaintext_body' ) );
        }

        /**
         * Remove the From Name and Emails for WordPress Template Emails via Filters.
         * This will be called after Abandoned Cart Emails are sent
         * 
         * @since 7.6.0
         */
        public static function wcap_remove_wp_mail_header( ) {

            remove_filter( 'wp_mail_from_name', array( 'Wcap_Common', 'wcap_from_name' ) );
            remove_filter( 'wp_mail_from', array( 'Wcap_Common', 'wcap_from_address' ) );

            remove_action( 'phpmailer_init', array( 'Wcap_Common', 'wcap_set_plaintext_body' ) );
        }

        /**
         * It will restrict the user and do not capture the cart.
         * @param object $user User data
         * @since 7.6
         */
        public static function wcap_add_restrict_user_meta_field( $user ) {
            echo '<h3 class="heading">Restrict user for capturing the abandoned cart.</h3>';

            $wcap_is_user_blocked = "";
            if ( isset( $user->ID ) && "" != $user->ID ) {
                $wcap_get_is_user_blocked = get_user_meta( $user->ID, 'wcap_restrict_user' );
                if ( count( $wcap_get_is_user_blocked ) > 0 && isset( $wcap_get_is_user_blocked[0] ) && "on" == $wcap_get_is_user_blocked[0] ) {
                    $wcap_is_user_blocked = "checked";
                }
            }?>
            <table class="form-table">
                <tr>
                    <th>
                        <label for="wcap_restrict_user">Do not capture abandoned cart of this user</label>
                    </th>
         
                    <td>
                        <input type="checkbox" id="wcap_restrict_user" name="wcap_restrict_user" value="on" <?php echo $wcap_is_user_blocked; ?> />
                    </td>
                </tr>
            </table>
            <?php
        }

        /**
         * Save the setting for the user restriction setting. 
         * @param int|string wcap_user_id User Id
         * @since 7.6
         */
        public static function wcap_save_restrict_user_meta_field( $wcap_user_id ) {
            if ( isset( $_POST['wcap_restrict_user'] ) && "" != $_POST['wcap_restrict_user'] ){
                $wcap_restrict_user = $_POST['wcap_restrict_user'];
                update_user_meta( $wcap_user_id, 'wcap_restrict_user', $wcap_restrict_user );
            }
        }

        /**
         * It will  add the plain text in the Abanodned cart reminder emails.
         * 
         * @param PHPMailer $phpmailer
         * @since  7.6
         */
        public static function wcap_set_plaintext_body( $phpmailer ) {

            $previous_altbody = '';
            
            // don't run if sending plain text email already
            if( $phpmailer->ContentType === 'text/plain' ) {
                return;
            }

            // don't run if altbody is set (by other plugin)
            if( ! empty( $phpmailer->AltBody ) && $phpmailer->AltBody !== $previous_altbody ) {
                return;
            }

            // set AltBody
            $text_message = Wcap_Common::wcap_strip_html_tags( $phpmailer->Body );
            $phpmailer->AltBody = wordwrap ( $text_message ) ;
            $previous_altbody = $text_message;
        }

        /**
         * Remove HTML tags, including invisible text such as style and
         * script code, and embedded objects.  Add line breaks around
         * block-level tags to prevent word joining after tag removal.
         * @param string $text Texts with html
         * @return string $text Texts without html
         */
        private static function wcap_strip_html_tags( $text ) {
            $text = preg_replace(
                array(
                  // Remove invisible content
                    '@<head[^>]*?>.*?</head>@siu',
                    '@<style[^>]*?>.*?</style>@siu',
                    '@<script[^>]*?.*?</script>@siu',
                    '@<object[^>]*?.*?</object>@siu',
                    '@<embed[^>]*?.*?</embed>@siu',
                    '@<noscript[^>]*?.*?</noscript>@siu',
                    '@<noembed[^>]*?.*?</noembed>@siu',
                    '@\t+@siu',
                    '@\n+@siu',
                ),
                '',
                $text );

            // replace certain elements with a line-break
            $text = preg_replace(
                array(
                    '@</?((div)|(h[1-9])|(/tr)|(p)|(pre))@iu'
                ),
                "\n\$0",
                $text );

            // replace other elements with a space
            $text = preg_replace(
                array(
                    '@</((td)|(th))@iu'
                ),
                "\n\$0",
                $text );

            $plain_replace = array(
                '',                                             // Non-legal carriage return
                ' ',                                            // Non-breaking space
                '"',                                            // Double quotes
                "'",                                            // Single quotes
                '>',                                            // Greater-than
                '<',                                            // Less-than
                '&',                                            // Ampersand
                '&',                                            // Ampersand
                '&',                                            // Ampersand
                '(c)',                                          // Copyright
                '(tm)',                                         // Trademark
                '(R)',                                          // Registered
                '--',                                           // mdash
                '-',                                            // ndash
                '*',                                            // Bullet
                '£',                                            // Pound sign
                'EUR',                                          // Euro sign. € ?
                '$',                                            // Dollar sign
                '',                                             // Unknown/unhandled entities
                ' ',                                             // Runs of spaces, post-handling
            );

            $plain_search = array(
                "/\r/",                                          // Non-legal carriage return
                '/&(nbsp|#160);/i',                              // Non-breaking space
                '/&(quot|rdquo|ldquo|#8220|#8221|#147|#148);/i', // Double quotes
                '/&(apos|rsquo|lsquo|#8216|#8217);/i',           // Single quotes
                '/&gt;/i',                                       // Greater-than
                '/&lt;/i',                                       // Less-than
                '/&#38;/i',                                      // Ampersand
                '/&#038;/i',                                     // Ampersand
                '/&amp;/i',                                      // Ampersand
                '/&(copy|#169);/i',                              // Copyright
                '/&(trade|#8482|#153);/i',                       // Trademark
                '/&(reg|#174);/i',                               // Registered
                '/&(mdash|#151|#8212);/i',                       // mdash
                '/&(ndash|minus|#8211|#8722);/i',                // ndash
                '/&(bull|#149|#8226);/i',                        // Bullet
                '/&(pound|#163);/i',                             // Pound sign
                '/&(euro|#8364);/i',                             // Euro sign
                '/&#36;/',                                       // Dollar sign
                '/&[^&\s;]+;/i',                                 // Unknown/unhandled entities
                '/[ ]{2,}/',                                      // Runs of spaces, post-handling
            );

            $text = preg_replace( $plain_search, $plain_replace, $text ) ;
            // strip all remaining HTML tags
            $text = strip_tags( $text );

            // trim text
            $text = trim( $text );

            return $text;
        }
        
        /**
         * Updates the Abandoned Cart History table as well as the 
         * Email Sent History table to indicate the order has been
         * recovered
         * 
         * @param integer $cart_id - ID of the Abandoned Cart 
         * @param integer $order_id - Recovered Order ID
         * @param integer $wcap_check_email_sent_to_cart - ID of the record in the Email Sent History table.
         * @param WC_Order $order - Order Details
         * 
         * @since 7.7
         */
        static function wcap_updated_recovered_cart( $cart_id, $order_id, $wcap_check_email_sent_to_cart, $order ) {

            global $wpdb;

            // check & make sure that the recovered cart details are not already updated
            $query_status = "SELECT recovered_cart FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE id = %d";

            $get_status = $wpdb->get_col( $wpdb->prepare( $query_status, $cart_id ) );

            $recovered_status = isset( $get_status[0] ) ? $get_status[0] : '';

            if( $recovered_status == 0 ) {
           
                // Update the cart history table
                $update_details = array( 
                    'recovered_cart'      => $order_id,
                    'cart_ignored'        => '1',
                    'language'            => '',
                );

                // check if more than one reminder email has been sent
                $check_emails = "SELECT abandoned_order_id FROM " . WCAP_EMAIL_SENT_HISTORY_TABLE . " WHERE id = %d";
                $get_old_cart_id = $wpdb->get_col( $wpdb->prepare( $check_emails, $wcap_check_email_sent_to_cart ) );

                $update_sent_history = array( 
                    'recovered_order'    => '1' 
                );

                if ( get_post_meta( $order_id, 'wcap_abandoned_timestamp', true ) != '' ) {
                    $update_details['abandoned_cart_time'] = get_post_meta( $order_id, 'wcap_abandoned_timestamp', true );

                    $update_sent_history['abandoned_order_id'] = $cart_id;

                    delete_post_meta( $order_id, 'wcap_abandoned_timestamp', $update_details['abandoned_cart_time'] );
                    //wcap_unset_cart_session( 'wcap_abandoned_timestamp' );
                }

                $wpdb->update( WCAP_ABANDONED_CART_HISTORY_TABLE, $update_details, array( 'id' => $cart_id ) );
                
                // update the email sent history table
                $wpdb->update( 
                    WCAP_EMAIL_SENT_HISTORY_TABLE, 
                    $update_sent_history, 
                    array( 'id' => $wcap_check_email_sent_to_cart ) 
                );

                // Add Order Note
                $order->add_order_note( __( 'This order was abandoned & subsequently recovered.', 'woocommerce-ac' ) );
            
                // Delete post meta records
                //delete_post_meta( $order_id,  'wcap_recover_order_placed',         $cart_id );
                //delete_post_meta( $order_id , 'wcap_recover_order_placed_sent_id', $wcap_check_email_sent_to_cart );

                // delete the cart from the sms notifications list
                self::wcap_delete_cart_notification( $cart_id );
                do_action( 'wcap_cart_recovered', $cart_id, $order_id );
            }       
        }

        /**
         * Display Prices as per the currency selected during cart creation
         * 
         * @param string $price Price to be displayed
         * @param string $currency Currency in which price needs to be displayed
         * 
         * @return string modified price with currency symbol
         * 
         * @since 7.7
         */
        public static function wcap_get_price( $price, $currency ) {
        
            if ( function_exists( 'icl_object_id' ) && isset( $currency ) && $currency !== '' ) {
                return wc_price( $price, array( 'currency' => $currency ) );
            }else{
                return wc_price( $price );
            }
        }

        /**
         * Validate Cart and check if its not empty
         * 
         * @param mixed $cart_info Cart Info Object
         * @return bool true if valid else false
         * 
         * @since 7.7.0
         */
        public static function wcap_validate_cart( $cart_info ) {

            $cart_info = json_decode( stripslashes($cart_info), true );

            if ( !empty( $cart_info ) && isset( $cart_info['cart'] ) && 
                 !empty( $cart_info['cart'] ) && count( $cart_info ) > 0 ) {

                return true;
            }else {
                return false;
            }
        }
        
        /**
         * Adds the Abandoned Cart ID to the list of carts for which
         * SMS and other reminders need to be sent.
         * 
         * @param string|int $cart_id Inserted Cart ID
         * @param array $reminder_types Reminder Types indicating the type of notifications
         *
         * @since 7.9
         * @since 7.10 added a new parameter $reminder_types to insert for all the notification types
         */
        static function wcap_insert_cart_id( $cart_id, $reminder_types ) {

            global $wpdb;

            // check if templates are present
            $template_query = "SELECT id from `" . WCAP_NOTIFICATIONS . "`
                               WHERE ( " . implode( ' OR ', $reminder_types ) . " ) AND is_active = '1'";

            $template_list = $wpdb->get_results( $template_query );
        
            // if yes, add the cart ID
            if( false != $template_list && is_array( $template_list ) && count( $template_list ) > 0 ) {
                foreach( $template_list as $template_data ) {
                    $template_id = $template_data->id;
        
                    // check template ID
                    if( $template_id > 0 ) {
        
                        $cart_list = wcap_get_notification_meta( $template_id, 'to_be_sent_cart_ids' );
        
                        if( $cart_list ) {
        
                            // check if the ID is already present
                            $explode_list = explode( ',', $cart_list );
        
                            if( ! in_array( $cart_id, $explode_list ) ) {
                                array_push( $explode_list, $cart_id );
                            }
        
                            $carts_str = implode( ',', $explode_list );
        
                        } else {
                            $carts_str = $cart_id;
                        }
        
                        // update the record
                        wcap_update_notification_meta( $template_id, 'to_be_sent_cart_ids', $carts_str );
                    }
        
                }
            }
        
        }
        
        /**
         * Removes the Cart ID from the list of carts for which
         * SMS reminders need to be sent.
         *
         * @param integer $cart_id - Abandoned Cart ID
         * @since 7.9
         */
        static function wcap_delete_cart_notification( $cart_id ) {
        
            global $wpdb;
            // check if templates are present
            $sms_query = "SELECT id from `" . WCAP_NOTIFICATIONS . "`
                            WHERE type = 'sms' AND is_active = '1'";
        
            $sms_list = $wpdb->get_results( $sms_query );
        
            // check for active SMS templates
            if( false != $sms_list && is_array( $sms_list ) && count( $sms_list ) > 0 ) {
                foreach( $sms_list as $sms_data ) {
                    $template_id = $sms_data->id;
        
                    // check if template is active
                    if( $template_id > 0 ) {
        
                        $cart_list = wcap_get_notification_meta( $template_id, 'to_be_sent_cart_ids' );
        
                        if( $cart_list ) {
        
                            // check if the ID is already present
                            $explode_list = explode( ',', $cart_list );
        
                            if( in_array( $cart_id, $explode_list ) ) {
                                $key = array_search( $cart_id, $explode_list );
                                unset( $explode_list[ $key ] );
                            }
        
                            $carts_str = implode( ',', $explode_list );
        
                            // update the record
                            wcap_update_notification_meta( $template_id, 'to_be_sent_cart_ids', $carts_str );
        
                        }
        
                    }
        
                }
            }
        
        }
        
        /**
         * Returns the User ID for a given abandoned
         * cart ID
         * 
         * @param integer $cart_id - Abandoned Cart ID
         * @return integer $user_id - User ID
         * 
         * @since 7.9 
         */
        static function get_user_id_from_cart( $cart_id ) {
        
            global $wpdb;
        
            $user_id = 0;
        
            if( $cart_id > 0 ) {
                $user_query = "SELECT user_id FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "`
                                WHERE id = %d";
        
                $user_results = $wpdb->get_results( $wpdb->prepare( $user_query, $cart_id ) );
        
                if( is_array( $user_results ) && count( $user_results ) > 0 ) {
                    $user_id = isset( $user_results[0]->user_id ) ? $user_results[0]->user_id : 0;
                }
            }
        
            return $user_id;
        }
        
        /**
         * Returns the Guest user data for a given user ID from
         * the Guest Table
         * 
         * @param integer $user_id - User ID
         * @return array $guest - Array containing guest user details array
         * 
         * @since 7.9
         */
        static function get_guest_data( $user_id ) {
        
            global $wpdb;
        
            $guest = false;
            if( $user_id >= '63000000' ) {
        
                $guest_query = "SELECT billing_first_name, billing_last_name, email_id, phone FROM `" . WCAP_GUEST_CART_HISTORY_TABLE ."`
                                WHERE id = %d";
                $guest_results = $wpdb->get_results( $wpdb->prepare( $guest_query, $user_id ) );
        
                if( is_array( $guest_results ) && count( $guest_results ) > 0 ) {
        
                    $guest = array( 'first_name' => $guest_results[0]->billing_first_name,
                        'last_name'  => $guest_results[0]->billing_last_name,
                        'email_id'   => $guest_results[0]->email_id,
                        'phone'      => $guest_results[0]->phone
                    );
                }
            }
        
            return $guest;
        }

        /**
         * Returns the recovered cart ID for an abadoned cart record
         * for which the user ID is passed
         * 
         * @param integer $user_id - User ID (Guest & Registered)
         * @return integer $recovered_order - Recovered Order ID 
         * @since 7.9
         */
        static function get_recovered_id_for_user( $user_id ) {
        
            global $wpdb;
        
            $recovered_order = 0;
            if( $user_id > 0 ) {
        
                $cart_query = "SELECT recovered_cart FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE ."`
                                WHERE user_id = %d
                                AND cart_ignored IN ( '0','2' )";
                $cart_results = $wpdb->get_results( $wpdb->prepare( $cart_query, $user_id ) );
        
                if( is_array( $cart_results ) && count( $cart_results ) > 0 ) {
        
                    $recovered_order = isset( $cart_results[0]->recovered_cart ) ? $cart_results[0]->recovered_cart : 0;
                }
            }
        
            return $recovered_order;
        }

        /**
         * Returns an array with mapped Country codes with ISD codes
         * 
         * @return array Mapped Array
         * 
         * @since 7.9
         */
        public static function wcap_country_code_map() {
            
            return [
                'IL' => ['name' => 'Israel', 'dial_code' => '+972'],
                'AF' => ['name' => 'Afghanistan', 'dial_code' => '+93'],
                'AL' => ['name' => 'Albania', 'dial_code' => '+355'],
                'DZ' => ['name' => 'Algeria', 'dial_code' => '+213'],
                'AS' => ['name' => 'AmericanSamoa', 'dial_code' => '+1684'],
                'AD' => ['name' => 'Andorra', 'dial_code' => '+376'],
                'AO' => ['name' => 'Angola', 'dial_code' => '+244'],
                'AI' => ['name' => 'Anguilla', 'dial_code' => '+1264'],
                'AG' => ['name' => 'Antigua and Barbuda', 'dial_code' => '+1268'],
                'AR' => ['name' => 'Argentina', 'dial_code' => '+54'],
                'AM' => ['name' => 'Armenia', 'dial_code' => '+374'],
                'AW' => ['name' => 'Aruba', 'dial_code' => '+297'],
                'AU' => ['name' => 'Australia', 'dial_code' => '+61'],
                'AT' => ['name' => 'Austria', 'dial_code' => '+43'],
                'AZ' => ['name' => 'Azerbaijan', 'dial_code' => '+994'],
                'BS' => ['name' => 'Bahamas', 'dial_code' => '+1 242'],
                'BH' => ['name' => 'Bahrain', 'dial_code' => '+973'],
                'BD' => ['name' => 'Bangladesh', 'dial_code' => '+880'],
                'BB' => ['name' => 'Barbados', 'dial_code' => '+1 246'],
                'BY' => ['name' => 'Belarus', 'dial_code' => '+375'],
                'BE' => ['name' => 'Belgium', 'dial_code' => '+32'],
                'BZ' => ['name' => 'Belize', 'dial_code' => '+501'],
                'BJ' => ['name' => 'Benin', 'dial_code' => '+229'],
                'BM' => ['name' => 'Bermuda', 'dial_code' => '+1 441'],
                'BT' => ['name' => 'Bhutan', 'dial_code' => '+975'],
                'BA' => ['name' => 'Bosnia and Herzegovina', 'dial_code' => '+387'],
                'BW' => ['name' => 'Botswana', 'dial_code' => '+267'],
                'BR' => ['name' => 'Brazil', 'dial_code' => '+55'],
                'IO' => ['name' => 'British Indian Ocean Territory', 'dial_code' => '+246'],
                'BG' => ['name' => 'Bulgaria', 'dial_code' => '+359'],
                'BF' => ['name' => 'Burkina Faso', 'dial_code' => '+226'],
                'BI' => ['name' => 'Burundi', 'dial_code' => '+257'],
                'KH' => ['name' => 'Cambodia', 'dial_code' => '+855'],
                'CM' => ['name' => 'Cameroon', 'dial_code' => '+237'],
                'CA' => ['name' => 'Canada', 'dial_code' => '+1'],
                'CV' => ['name' => 'Cape Verde', 'dial_code' => '+238'],
                'KY' => ['name' => 'Cayman Islands', 'dial_code' => '+ 345'],
                'CF' => ['name' => 'Central African Republic', 'dial_code' => '+236'],
                'TD' => ['name' => 'Chad', 'dial_code' => '+235'],
                'CL' => ['name' => 'Chile', 'dial_code' => '+56'],
                'CN' => ['name' => 'China', 'dial_code' => '+86'],
                'CX' => ['name' => 'Christmas Island', 'dial_code' => '+61'],
                'CO' => ['name' => 'Colombia', 'dial_code' => '+57'],
                'KM' => ['name' => 'Comoros', 'dial_code' => '+269'],
                'CG' => ['name' => 'Congo', 'dial_code' => '+242'],
                'CK' => ['name' => 'Cook Islands', 'dial_code' => '+682'],
                'CR' => ['name' => 'Costa Rica', 'dial_code' => '+506'],
                'HR' => ['name' => 'Croatia', 'dial_code' => '+385'],
                'CU' => ['name' => 'Cuba', 'dial_code' => '+53'],
                'CY' => ['name' => 'Cyprus', 'dial_code' => '+537'],
                'CZ' => ['name' => 'Czech Republic', 'dial_code' => '+420'],
                'DK' => ['name' => 'Denmark', 'dial_code' => '+45'],
                'DJ' => ['name' => 'Djibouti', 'dial_code' => '+253'],
                'DM' => ['name' => 'Dominica', 'dial_code' => '+1 767'],
                'DO' => ['name' => 'Dominican Republic', 'dial_code' => '+1849'],
                'EC' => ['name' => 'Ecuador', 'dial_code' => '+593'],
                'EG' => ['name' => 'Egypt', 'dial_code' => '+20'],
                'SV' => ['name' => 'El Salvador', 'dial_code' => '+503'],
                'GQ' => ['name' => 'Equatorial Guinea', 'dial_code' => '+240'],
                'ER' => ['name' => 'Eritrea', 'dial_code' => '+291'],
                'EE' => ['name' => 'Estonia', 'dial_code' => '+372'],
                'ET' => ['name' => 'Ethiopia', 'dial_code' => '+251'],
                'FO' => ['name' => 'Faroe Islands', 'dial_code' => '+298'],
                'FJ' => ['name' => 'Fiji', 'dial_code' => '+679'],
                'FI' => ['name' => 'Finland', 'dial_code' => '+358'],
                'FR' => ['name' => 'France', 'dial_code' => '+33'],
                'GF' => ['name' => 'French Guiana', 'dial_code' => '+594'],
                'PF' => ['name' => 'French Polynesia', 'dial_code' => '+689'],
                'GA' => ['name' => 'Gabon', 'dial_code' => '+241'],
                'GM' => ['name' => 'Gambia', 'dial_code' => '+220'],
                'GE' => ['name' => 'Georgia', 'dial_code' => '+995'],
                'DE' => ['name' => 'Germany', 'dial_code' => '+49'],
                'GH' => ['name' => 'Ghana', 'dial_code' => '+233'],
                'GI' => ['name' => 'Gibraltar', 'dial_code' => '+350'],
                'GR' => ['name' => 'Greece', 'dial_code' => '+30'],
                'GL' => ['name' => 'Greenland', 'dial_code' => '+299'],
                'GD' => ['name' => 'Grenada', 'dial_code' => '+1 473'],
                'GP' => ['name' => 'Guadeloupe', 'dial_code' => '+590'],
                'GU' => ['name' => 'Guam', 'dial_code' => '+1 671'],
                'GT' => ['name' => 'Guatemala', 'dial_code' => '+502'],
                'GN' => ['name' => 'Guinea', 'dial_code' => '+224'],
                'GW' => ['name' => 'Guinea-Bissau', 'dial_code' => '+245'],
                'GY' => ['name' => 'Guyana', 'dial_code' => '+595'],
                'HT' => ['name' => 'Haiti', 'dial_code' => '+509'],
                'HN' => ['name' => 'Honduras', 'dial_code' => '+504'],
                'HU' => ['name' => 'Hungary', 'dial_code' => '+36'],
                'IS' => ['name' => 'Iceland', 'dial_code' => '+354'],
                'IN' => ['name' => 'India', 'dial_code' => '+91'],
                'ID' => ['name' => 'Indonesia', 'dial_code' => '+62'],
                'IQ' => ['name' => 'Iraq', 'dial_code' => '+964'],
                'IE' => ['name' => 'Ireland', 'dial_code' => '+353'],
                'IL' => ['name' => 'Israel', 'dial_code' => '+972'],
                'IT' => ['name' => 'Italy', 'dial_code' => '+39'],
                'JM' => ['name' => 'Jamaica', 'dial_code' => '+1876'],
                'JP' => ['name' => 'Japan', 'dial_code' => '+81'],
                'JO' => ['name' => 'Jordan', 'dial_code' => '+962'],
                'KZ' => ['name' => 'Kazakhstan', 'dial_code' => '+77'],
                'KE' => ['name' => 'Kenya', 'dial_code' => '+254'],
                'KI' => ['name' => 'Kiribati', 'dial_code' => '+686'],
                'KW' => ['name' => 'Kuwait', 'dial_code' => '+965'],
                'KG' => ['name' => 'Kyrgyzstan', 'dial_code' => '+996'],
                'LV' => ['name' => 'Latvia', 'dial_code' => '+371'],
                'LB' => ['name' => 'Lebanon', 'dial_code' => '+961'],
                'LS' => ['name' => 'Lesotho', 'dial_code' => '+266'],
                'LR' => ['name' => 'Liberia', 'dial_code' => '+231'],
                'LI' => ['name' => 'Liechtenstein', 'dial_code' => '+423'],
                'LT' => ['name' => 'Lithuania', 'dial_code' => '+370'],
                'LU' => ['name' => 'Luxembourg', 'dial_code' => '+352'],
                'MG' => ['name' => 'Madagascar', 'dial_code' => '+261'],
                'MW' => ['name' => 'Malawi', 'dial_code' => '+265'],
                'MY' => ['name' => 'Malaysia', 'dial_code' => '+60'],
                'MV' => ['name' => 'Maldives', 'dial_code' => '+960'],
                'ML' => ['name' => 'Mali', 'dial_code' => '+223'],
                'MT' => ['name' => 'Malta', 'dial_code' => '+356'],
                'MH' => ['name' => 'Marshall Islands', 'dial_code' => '+692'],
                'MQ' => ['name' => 'Martinique', 'dial_code' => '+596'],
                'MR' => ['name' => 'Mauritania', 'dial_code' => '+222'],
                'MU' => ['name' => 'Mauritius', 'dial_code' => '+230'],
                'YT' => ['name' => 'Mayotte', 'dial_code' => '+262'],
                'MX' => ['name' => 'Mexico', 'dial_code' => '+52'],
                'MC' => ['name' => 'Monaco', 'dial_code' => '+377'],
                'MN' => ['name' => 'Mongolia', 'dial_code' => '+976'],
                'ME' => ['name' => 'Montenegro', 'dial_code' => '+382'],
                'MS' => ['name' => 'Montserrat', 'dial_code' => '+1664'],
                'MA' => ['name' => 'Morocco', 'dial_code' => '+212'],
                'MM' => ['name' => 'Myanmar', 'dial_code' => '+95'],
                'NA' => ['name' => 'Namibia', 'dial_code' => '+264'],
                'NR' => ['name' => 'Nauru', 'dial_code' => '+674'],
                'NP' => ['name' => 'Nepal', 'dial_code' => '+977'],
                'NL' => ['name' => 'Netherlands', 'dial_code' => '+31'],
                'AN' => ['name' => 'Netherlands Antilles', 'dial_code' => '+599'],
                'NC' => ['name' => 'New Caledonia', 'dial_code' => '+687'],
                'NZ' => ['name' => 'New Zealand', 'dial_code' => '+64'],
                'NI' => ['name' => 'Nicaragua', 'dial_code' => '+505'],
                'NE' => ['name' => 'Niger', 'dial_code' => '+227'],
                'NG' => ['name' => 'Nigeria', 'dial_code' => '+234'],
                'NU' => ['name' => 'Niue', 'dial_code' => '+683'],
                'NF' => ['name' => 'Norfolk Island', 'dial_code' => '+672'],
                'MP' => ['name' => 'Northern Mariana Islands', 'dial_code' => '+1670'],
                'NO' => ['name' => 'Norway', 'dial_code' => '+47'],
                'OM' => ['name' => 'Oman', 'dial_code' => '+968'],
                'PK' => ['name' => 'Pakistan', 'dial_code' => '+92'],
                'PW' => ['name' => 'Palau', 'dial_code' => '+680'],
                'PA' => ['name' => 'Panama', 'dial_code' => '+507'],
                'PG' => ['name' => 'Papua New Guinea', 'dial_code' => '+675'],
                'PY' => ['name' => 'Paraguay', 'dial_code' => '+595'],
                'PE' => ['name' => 'Peru', 'dial_code' => '+51'],
                'PH' => ['name' => 'Philippines', 'dial_code' => '+63'],
                'PL' => ['name' => 'Poland', 'dial_code' => '+48'],
                'PT' => ['name' => 'Portugal', 'dial_code' => '+351'],
                'PR' => ['name' => 'Puerto Rico', 'dial_code' => '+1939'],
                'QA' => ['name' => 'Qatar', 'dial_code' => '+974'],
                'RO' => ['name' => 'Romania', 'dial_code' => '+40'],
                'RW' => ['name' => 'Rwanda', 'dial_code' => '+250'],
                'WS' => ['name' => 'Samoa', 'dial_code' => '+685'],
                'SM' => ['name' => 'San Marino', 'dial_code' => '+378'],
                'SA' => ['name' => 'Saudi Arabia', 'dial_code' => '+966'],
                'SN' => ['name' => 'Senegal', 'dial_code' => '+221'],
                'RS' => ['name' => 'Serbia', 'dial_code' => '+381'],
                'SC' => ['name' => 'Seychelles', 'dial_code' => '+248'],
                'SL' => ['name' => 'Sierra Leone', 'dial_code' => '+232'],
                'SG' => ['name' => 'Singapore', 'dial_code' => '+65'],
                'SK' => ['name' => 'Slovakia', 'dial_code' => '+421'],
                'SI' => ['name' => 'Slovenia', 'dial_code' => '+386'],
                'SB' => ['name' => 'Solomon Islands', 'dial_code' => '+677'],
                'ZA' => ['name' => 'South Africa', 'dial_code' => '+27'],
                'GS' => ['name' => 'South Georgia and the South Sandwich Islands', 'dial_code' => '+500'],
                'ES' => ['name' => 'Spain', 'dial_code' => '+34'],
                'LK' => ['name' => 'Sri Lanka', 'dial_code' => '+94'],
                'SD' => ['name' => 'Sudan', 'dial_code' => '+249'],
                'SR' => ['name' => 'Suriname', 'dial_code' => '+597'],
                'SZ' => ['name' => 'Swaziland', 'dial_code' => '+268'],
                'SE' => ['name' => 'Sweden', 'dial_code' => '+46'],
                'CH' => ['name' => 'Switzerland', 'dial_code' => '+41'],
                'TJ' => ['name' => 'Tajikistan', 'dial_code' => '+992'],
                'TH' => ['name' => 'Thailand', 'dial_code' => '+66'],
                'TG' => ['name' => 'Togo', 'dial_code' => '+228'],
                'TK' => ['name' => 'Tokelau', 'dial_code' => '+690'],
                'TO' => ['name' => 'Tonga', 'dial_code' => '+676'],
                'TT' => ['name' => 'Trinidad and Tobago', 'dial_code' => '+1868'],
                'TN' => ['name' => 'Tunisia', 'dial_code' => '+216'],
                'TR' => ['name' => 'Turkey', 'dial_code' => '+90'],
                'TM' => ['name' => 'Turkmenistan', 'dial_code' => '+993'],
                'TC' => ['name' => 'Turks and Caicos Islands', 'dial_code' => '+1649'],
                'TV' => ['name' => 'Tuvalu', 'dial_code' => '+688'],
                'UG' => ['name' => 'Uganda', 'dial_code' => '+256'],
                'UA' => ['name' => 'Ukraine', 'dial_code' => '+380'],
                'AE' => ['name' => 'United Arab Emirates', 'dial_code' => '+971'],
                'GB' => ['name' => 'United Kingdom', 'dial_code' => '+44'],
                'US' => ['name' => 'United States', 'dial_code' => '+1'],
                'UY' => ['name' => 'Uruguay', 'dial_code' => '+598'],
                'UZ' => ['name' => 'Uzbekistan', 'dial_code' => '+998'],
                'VU' => ['name' => 'Vanuatu', 'dial_code' => '+678'],
                'WF' => ['name' => 'Wallis and Futuna', 'dial_code' => '+681'],
                'YE' => ['name' => 'Yemen', 'dial_code' => '+967'],
                'ZM' => ['name' => 'Zambia', 'dial_code' => '+260'],
                'ZW' => ['name' => 'Zimbabwe', 'dial_code' => '+263'],
                'BO' => ['name' => 'Bolivia, Plurinational State of', 'dial_code' => '+591'],
                'BN' => ['name' => 'Brunei Darussalam', 'dial_code' => '+673'],
                'CC' => ['name' => 'Cocos (Keeling) Islands', 'dial_code' => '+61'],
                'CD' => ['name' => 'Congo, The Democratic Republic of the', 'dial_code' => '+243'],
                'CI' => ['name' => 'Cote dIvoire', 'dial_code' => '+225'],
                'FK' => ['name' => 'Falkland Islands (Malvinas)', 'dial_code' => '+500'],
                'GG' => ['name' => 'Guernsey', 'dial_code' => '+44'],
                'VA' => ['name' => 'Holy See (Vatican City State)', 'dial_code' => '+379'],
                'HK' => ['name' => 'Hong Kong', 'dial_code' => '+852'],
                'IR' => ['name' => 'Iran, Islamic Republic of', 'dial_code' => '+98'],
                'IM' => ['name' => 'Isle of Man', 'dial_code' => '+44'],
                'JE' => ['name' => 'Jersey', 'dial_code' => '+44'],
                'KP' => ['name' => 'Korea, Democratic Peoples Republic of', 'dial_code' => '+850'],
                'KR' => ['name' => 'Korea, Republic of', 'dial_code' => '+82'],
                'LA' => ['name' => 'Lao Peoples Democratic Republic', 'dial_code' => '+856'],
                'LY' => ['name' => 'Libyan Arab Jamahiriya', 'dial_code' => '+218'],
                'MO' => ['name' => 'Macao', 'dial_code' => '+853'],
                'MK' => ['name' => 'Macedonia, The Former Yugoslav Republic of', 'dial_code' => '+389'],
                'FM' => ['name' => 'Micronesia, Federated States of', 'dial_code' => '+691'],
                'MD' => ['name' => 'Moldova, Republic of', 'dial_code' => '+373'],
                'MZ' => ['name' => 'Mozambique', 'dial_code' => '+258'],
                'PS' => ['name' => 'Palestinian Territory, Occupied', 'dial_code' => '+970'],
                'PN' => ['name' => 'Pitcairn', 'dial_code' => '+872'],
                'RE' => ['name' => 'Réunion', 'dial_code' => '+262'],
                'RU' => ['name' => 'Russia', 'dial_code' => '+7'],
                'BL' => ['name' => 'Saint Barthélemy', 'dial_code' => '+590'],
                'SH' => ['name' => 'Saint Helena, Ascension and Tristan Da Cunha', 'dial_code' => '+290'],
                'KN' => ['name' => 'Saint Kitts and Nevis', 'dial_code' => '+1 869'],
                'LC' => ['name' => 'Saint Lucia', 'dial_code' => '+1758'],
                'MF' => ['name' => 'Saint Martin', 'dial_code' => '+590'],
                'PM' => ['name' => 'Saint Pierre and Miquelon', 'dial_code' => '+508'],
                'VC' => ['name' => 'Saint Vincent and the Grenadines', 'dial_code' => '+1784'],
                'ST' => ['name' => 'Sao Tome and Principe', 'dial_code' => '+239'],
                'SO' => ['name' => 'Somalia', 'dial_code' => '+252'],
                'SJ' => ['name' => 'Svalbard and Jan Mayen', 'dial_code' => '+47'],
                'SY' => ['name' => 'Syrian Arab Republic', 'dial_code' => '+963'],
                'TW' => ['name' => 'Taiwan, Province of China', 'dial_code' => '+886'],
                'TZ' => ['name' => 'Tanzania, United Republic of', 'dial_code' => '+255'],
                'TL' => ['name' => 'Timor-Leste', 'dial_code' => '+670'],
                'VE' => ['name' => 'Venezuela, Bolivarian Republic of', 'dial_code' => '+58'],
                'VN' => ['name' => 'Viet Nam', 'dial_code' => '+84'],
                'VG' => ['name' => 'Virgin Islands, British', 'dial_code' => '+1284'],
                'VI' => ['name' => 'Virgin Islands, U.S.', 'dial_code' => '+1340']
            ];
        }

        /**
         * Display notices in Admin related to new features released along with appropriate links
         * 
         * @since 7.10.0
         */
        public static function wcap_admin_promotions() {

            if ( isset( $_GET['page'] ) && 'woocommerce_ac_page' === $_GET['page'] ) {

                if ( ! get_option( 'wcap_scheduler_update_dismiss', FALSE ) ) {
                    $post_link = '<a href="https://www.tychesoftwares.com/moving-to-the-action-scheduler-library/?utm_source=AcProNotice&utm_medium=link&utm_campaign=AbandonCartPro" target="_blank">here</a>';
                    ?>
                        <div id='wcap_cron_notice' class='is-dismissible notice notice-info wcap-cron-notice'>
                            <p><?php _e( "The <b>Abandoned Cart Pro for WooCommerce</b> now uses the Action Scheduler library to send reminders. For further details, please visit $post_link.", 'woocommerce-ac' ); ?></p>
                        </div>
                    <?php	
                }
            }
	
            global $current_screen;
            $current_screen = get_current_screen();
            if ( ( method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor() )
                || ( function_exists('is_gutenberg_page') && is_gutenberg_page() ) ) {
                return;
            }

            if ( get_option( 'wcap_notice_dissmissed' ) == 'yes' ) {
                return;
            }

            ?>
                <div class="notice notice-info is-dismissible" id="ac-pro-new-feature">
                    <p><?php _e( 'Want to recover more Abandoned Carts? Click on the link <a href="admin.php?page=woocommerce_ac_page&action=emailsettings&wcap_section=wcap_fb_settings">here to set up</a> the integration with <strong>Facebook Messenger</strong>.</br>Meanwhile some exciting <strong>pre-designed responsive email templates</strong> have been added for you. Click <a href="admin.php?page=woocommerce_ac_page&action=cart_recovery">here</a> to view them', 'woocommerce-ac' ); ?></p>
                    <a style="margin-bottom: 10px;" href="https://www.tychesoftwares.com/boost-your-abandoned-cart-recovery-with-facebook-messenger-notifications/" target="_blank" class="button button-primary">
                        <?php _e( 'Read More', 'woocommerce-ac' ); ?>
                    </a>
                </div>

                <script type='text/javascript'>
                    jQuery('body').on('click', '#ac-pro-new-feature .notice-dismiss', function(e) {
                        e.preventDefault();

                        wp.ajax.post( 'wcap_dismiss_new_feature', {
                            wcap_notice_dissmissed: true
                        });
                    });
                </script>
            <?php
        }

        /**
         * Mark the admin notice as dismissed.
         *
         * @since 8.6
         */
        public static function wcap_dismiss_admin_notice() {

            $option_key = isset( $_POST['notice'] ) ? $_POST['notice'] : '';

            if ( '' !== $option_key ) {
                update_option( $option_key, true );
            }
        }

        public static function wcap_hide_notices() {
            
            if ( isset( $_POST['wcap_notice_dissmissed'] ) && $_POST['wcap_notice_dissmissed'] == true ) {
                update_option( 'wcap_notice_dissmissed', 'yes' );
            }
        }

        public static function wcap_get_wc_address() {

            $countries = new WC_Countries();

            $address         = $countries->get_base_address();
            $address_2       = $countries->get_base_address_2();
            $city            = $countries->get_base_city();
            $state           = $countries->get_base_state();
            $country         = $countries->get_base_country();
            $postcode        = $countries->get_base_postcode();
            $country_display = '';
            $state_display   = '';

            // Get all countries key/names in an array:
            $countries_array = $countries->get_countries();

            // Get all country states key/names in a multilevel array:
            $country_states_array = $countries->get_states();

            if ( isset( $country ) && $country != '' &&
                 isset( $countries_array[$country] ) && $countries_array[$country] != '' ) {
                
                $country_display = $countries_array[$country];
            }

            if ( isset( $state ) && $state != '' &&
                 isset( $country_states_array[$country] ) && isset( $country_states_array[$country][$state] ) ) {
                
                $country_display = $country_states_array[$country][$state];
            }

            return $address . ' ' . $address_2 . ' ' . $city . ' ' . $state . ' ' . $postcode;
        }

        /**
		 * Display Date Filter
		 *
		 * @since 8.4.0
		 */
		public static function wcap_display_date_filter( $name ) {

			$duration_range   = '';
			$input_name       = "duration_select_$name";
			$input_start_date = "start_date_$name";
			$input_end_date   = "end_date_$name";

			if ( isset( $_POST[ $input_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$duration_range        = sanitize_text_field( wp_unslash( $_POST[ $input_name ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				$_SESSION ['duration'] = $duration_range;
			}

			if ( '' === $duration_range ) {
				if ( isset( $_GET[ $input_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$duration_range        = sanitize_text_field( wp_unslash( $_GET[ $input_name ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
					$_SESSION ['duration'] = $duration_range;
				}
			}

			if ( isset( $_SESSION['duration'] ) ) {
				$duration_range = sanitize_text_field( wp_unslash( $_SESSION['duration'] ) );
			}

			if ( '' === $duration_range ) {
				$duration_range       = 'last_seven';
				$_SESSION['duration'] = $duration_range;
			}

			$wcap_ac_class = new Woocommerce_Abandon_Cart();
			?>

			<div id="<?php echo esc_attr( $name ); ?>" class="postbox" style="display:block">
				<div class="inside">
					<form method="post" action="admin.php?page=woocommerce_ac_page&action=<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>">
						<select id="<?php echo esc_attr( $input_name ); ?>" name="<?php echo esc_attr( $input_name ); ?>" >
							<?php
							foreach ( $wcap_ac_class->duration_range_select as $key => $value ) {
								$sel = '';
								if ( $key == $duration_range ) { //phpcs:ignore
									$sel = __( ' selected ', 'woocommerce-ac' );
								}
								echo sprintf( "<option value='%s' %s>%s</option>", esc_attr( $key ), esc_attr( $sel ), esc_attr( __( $value, 'woocommerce-ac' ) ) ); //phpcs:ignore
							}

							$date_sett = $wcap_ac_class->start_end_dates[ $duration_range ];
							?>
						</select>

						<?php
						$start_date_range = '';
						if ( isset( $_POST[ $input_start_date ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
							$start_date_range        = sanitize_text_field( wp_unslash( $_POST[ $input_start_date ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
							$_SESSION ['start_date'] = $start_date_range;
						}
						if ( isset( $_SESSION ['start_date'] ) ) {
							$start_date_range = sanitize_text_field( wp_unslash( $_SESSION ['start_date'] ) );
						}
						if ( '' === $start_date_range ) {
							$start_date_range        = $date_sett['start_date'];
							$_SESSION ['start_date'] = $start_date_range;
						}

						$end_date_range = '';
						if ( isset( $_POST[ $input_end_date ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
							$end_date_range        = sanitize_text_field( wp_unslash( $_POST[ $input_end_date ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
							$_SESSION ['end_date'] = $end_date_range;
						}
						if ( isset( $_SESSION['end_date'] ) ) {
							$end_date_range = sanitize_text_field( wp_unslash( $_SESSION ['end_date'] ) );
						}
						if ( '' === $end_date_range ) {
							$end_date_range       = $date_sett['end_date'];
							$_SESSION['end_date'] = $end_date_range;
						}
						?>
						<label class="start_label" for="start_day">
							<?php esc_html_e( 'Start Date:', 'woocommerce-ac' ); ?>
						</label>
						<input type="text" id="<?php echo esc_attr( $input_start_date ); ?>" name="<?php echo esc_attr( $input_start_date ); ?>" readonly="readonly" value="<?php echo esc_attr( $start_date_range ); ?>" />
						<label class="end_label" for="end_day"> <?php esc_html_e( 'End Date:', 'woocommerce-ac' ); ?> </label>
						<input type="text" id="<?php echo esc_attr( $input_end_date ); ?>" name="<?php echo esc_attr( $input_end_date ); ?>" readonly="readonly" value="<?php echo esc_attr( $end_date_range ); ?>" />
						<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Go', 'woocommerce-ac' ); ?>"  />
					</form>
				</div>
			</div>

			<?php

		}

		/**
		 * Add a scheduled action for the webhok to be delivered once the cart cut off is reached.
		 *
		 * @param int $cart_id - Abandoned Cart ID.
		 * @since 8.7.0
		 */
		public static function wcap_run_webhook_after_cutoff( $cart_id ) {
			// check if the Webhook is present & active.
			global $wpdb;
  
			$get_webhook_status = $wpdb->get_var( $wpdb->prepare( 'SELECT status FROM `' . $wpdb->prefix . 'wc_webhooks` WHERE topic = %s', 'wcap_cart.cutoff' ) );
  
			if ( isset( $get_webhook_status ) && 'active' == $get_webhook_status ) {
			  	// Reconfirm that the cart is either a registered user cart or a guest cart. The webhook will not be run for visitor carts.
			  	$cart_data = $wpdb->get_results( $wpdb->prepare( "SELECT user_id, user_type, cart_ignored, recovered_cart FROM " . WCAP_ABANDONED_CART_HISTORY_TABLE . " WHERE id = %d", $cart_id ) );
  
			  	$user_id = isset( $cart_data[0]->user_id ) ? $cart_data[0]->user_id : 0;
			  	$user_type = isset( $cart_data[0]->user_type ) ? $cart_data[0]->user_type : '';
  
			  	if ( $user_id > 0 && '' != $user_type && '0' == $cart_data[0]->cart_ignored && $cart_data[0]->recovered_cart <= 0 ) {
  
					$cut_off = 0;
					if ( 'REGISTERED' == $user_type ) {
				  		$cut_off = get_option( 'ac_cart_abandoned_time', 10 ) * 60;
					} else if ( 'GUEST' == $user_type ) {
				  		$cut_off = get_option( 'ac_cart_abandoned_time_guest', 10 ) * 60;
					}
  
					if ( $cut_off > 0 ) {
				  		// run the hook
						as_schedule_single_action( time() + $cut_off, 'wcap_webhook_after_cutoff', array( 'id' => $cart_id ) );  
					}
				}
			}
		}
  
		/**
		 * Update Checkout Link in cart history table.
		 *
		 * @param int $cart_id - Cart ID.
		 * @since 8.7.0
		*/
		public static function wcap_add_checkout_link( $cart_id ) {
  
			global $wpdb;
			if( version_compare( WOOCOMMERCE_VERSION, '2.3' ) < 0 ) {
				global $woocommerce;
				$checkout_page_link = $woocommerce->cart->get_checkout_url();
			} else {
				$checkout_page_id   = wc_get_page_id( 'checkout' );
				$checkout_page_link = $checkout_page_id ? get_permalink( $checkout_page_id ) : '';
			}
  
			if( function_exists( 'icl_register_string' ) ) {
				$cart_language = $wpdb->get_var( $wpdb->prepare( 'SELECT language FROM ' . WCAP_ABANDONED_CART_HISTORY_TABLE . ' WHERE id = %d', $cart_id ) );
			  	$checkout_page_link = apply_filters( 'wpml_permalink', $checkout_page_link, $cart_language );
			}
			// Force SSL if needed.
			$ssl_is_used = is_ssl() ? true : false;
  
			if( true === $ssl_is_used || 'yes' === get_option( 'woocommerce_force_ssl_checkout' ) ) {
				$checkout_page_link  = str_ireplace( 'http:', 'https:', $checkout_page_link );
			}
  
			$encoding_checkout = $cart_id . '&url=' . $checkout_page_link;
			$validate_checkout = Wcap_Common::encrypt_validate( $encoding_checkout );
  
			$checkout_link = get_option( 'siteurl' ) . '/?wacp_action=checkout_link&validate=' . $validate_checkout;
			$wpdb->update( WCAP_ABANDONED_CART_HISTORY_TABLE, array( 'checkout_link' => $checkout_link ), array( 'id' => $cart_id ) );
		}

    } // end of class
}
?>
