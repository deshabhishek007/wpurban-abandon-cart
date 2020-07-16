<?php
/**
 * It will update the tables, options and any other changes when we update the plugin.
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Update
 * @since 5.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if ( !class_exists('Wcap_Update_Check' ) ) {
    /**
     * It will update the tables, options and any other changes when we update the plugin.
     */
    class Wcap_Update_Check {

        /**
         * It will upadate the tables and other options for the plugin, it will be called when we upadate the plugin.
         * @globals mixed $wpdb
         * @globals int | string $woocommerce_ac_plugin_version Old version of plugin
         * @globals 
         * @since 5.0
         */
        public static function wcap_update_db_check() {

            global $wpdb, $woocommerce_ac_plugin_version;
            $woocommerce_ac_plugin_version = get_option( 'woocommerce_ac_db_version' );

            if ( $woocommerce_ac_plugin_version != Wcap_Common::wcap_get_version() ) {

                // check whether its a multi site install or a single site install
                if ( is_multisite() ) {
                    
                    // check if tables exist for the child sites, if not, create
                    if( 'yes' != get_blog_option( 1, 'wcap_update_multisite' ) ) {
                        // run the activate function
                        Wcap_Activate_Plugin::wcap_activate();
                        update_blog_option( 1, 'wcap_update_multisite', 'yes' );
                    }
                    $blog_list = get_sites();
                    foreach( $blog_list as $blog_list_key => $blog_list_value ) {
                        if( $blog_list_value->blog_id > 1 ){ // child sites
                            $blog_id = $blog_list_value->blog_id;
                            self::wcap_process_db_update( $blog_id );
                        } else { // parent site
                            self::wcap_process_db_update();
                        }
                    }
                } else { // single site
                    self::wcap_process_db_update();
                }
            }
        }

        static function wcap_process_db_update( $blog_id = 0 ) {
                
            global $woocommerce, $wpdb;

            $db_prefix = ( $blog_id === 0 ) ? $wpdb->prefix : $wpdb->prefix . $blog_id . "_";

            if( $blog_id === 0 ) {
                //get the option, if it is not set to individual then convert to individual records and delete the base record
                $ac_settings = get_option( 'ac_settings_status' );
                if ( $ac_settings != 'INDIVIDUAL' ) {

                    //fetch the existing settings and save them as inidividual to be used for the settings API
                    $woocommerce_ac_settings = json_decode( get_option( 'woocommerce_ac_settings' ) );
                    add_option( 'ac_enable_cart_emails',              $woocommerce_ac_settings[0]->enable_cart_notification );
                    add_option( 'ac_cart_abandoned_time',             $woocommerce_ac_settings[0]->cart_time );
                    add_option( 'ac_delete_abandoned_order_days',     $woocommerce_ac_settings[0]->delete_order_days );
                    add_option( 'ac_email_admin_on_recovery',         $woocommerce_ac_settings[0]->email_admin );
                    add_option( 'ac_track_coupons',                   $woocommerce_ac_settings[0]->track_coupons );
                    add_option( 'ac_disable_guest_cart_email',        $woocommerce_ac_settings[0]->disable_guest_cart );
                    add_option( 'ac_disable_logged_in_cart_email',    $woocommerce_ac_settings[0]->disable_logged_in_cart );
                    add_option( 'ac_track_guest_cart_from_cart_page', $woocommerce_ac_settings[0]->disable_guest_cart_from_cart_page );
                    update_option( 'ac_settings_status', 'INDIVIDUAL' );
                    //Delete the main settings record
                    delete_option( 'woocommerce_ac_settings' );
                }
                update_option( 'woocommerce_ac_db_version', '8.7.2' );
            } else {
                //get the option, if it is not set to individual then convert to individual records and delete the base record
                $ac_settings = get_blog_option( $blog_id, 'ac_settings_status' );
                if ( $ac_settings != 'INDIVIDUAL' ) {

                    //fetch the existing settings and save them as inidividual to be used for the settings API
                    $woocommerce_ac_settings = json_decode( get_blog_option( $blog_id, 'woocommerce_ac_settings' ) );
                    if( ! isset( $woocommerce_ac_settings[0] ) && is_multisite() ) {
                        $woocommerce_ac_settings = json_decode( get_blog_option( 1, 'woocommerce_ac_settings' ) );
                    }
                    add_blog_option( $blog_id, 'ac_enable_cart_emails',              $woocommerce_ac_settings[0]->enable_cart_notification );
                    add_blog_option( $blog_id, 'ac_cart_abandoned_time',             $woocommerce_ac_settings[0]->cart_time );
                    add_blog_option( $blog_id, 'ac_delete_abandoned_order_days',     $woocommerce_ac_settings[0]->delete_order_days );
                    add_blog_option( $blog_id, 'ac_email_admin_on_recovery',         $woocommerce_ac_settings[0]->email_admin );
                    add_blog_option( $blog_id, 'ac_track_coupons',                   $woocommerce_ac_settings[0]->track_coupons );
                    add_blog_option( $blog_id, 'ac_disable_guest_cart_email',        $woocommerce_ac_settings[0]->disable_guest_cart );
                    add_blog_option( $blog_id, 'ac_disable_logged_in_cart_email',    $woocommerce_ac_settings[0]->disable_logged_in_cart );
                    add_blog_option( $blog_id, 'ac_track_guest_cart_from_cart_page', $woocommerce_ac_settings[0]->disable_guest_cart_from_cart_page );
                    update_blog_option( $blog_id, 'ac_settings_status', 'INDIVIDUAL' );
                    //Delete the main settings record
                    delete_blog_option( $blog_id, 'woocommerce_ac_settings' );
                }
                update_blog_option( $blog_id, 'woocommerce_ac_db_version', '8.7.2' );
            }

            $check_table_query = "SHOW COLUMNS FROM `" . $db_prefix . "ac_link_clicked_email` LIKE 'link_clicked'";
                $results = $wpdb->get_results( $check_table_query );
            if ( isset( $results, $results[0]->Type ) && $results[0]->Type == 'varchar(60)' ) {
                $alter_table_query = "ALTER TABLE `". $db_prefix . "ac_link_clicked_email` MODIFY COLUMN link_clicked varchar (500)";
                $wpdb->get_results( $alter_table_query );
            }

            /**
             * As we do not use the trash feature in sent emails tab we dont need the trash coulmn in the database.
             * @since: 7.6
             */
            if ( $wpdb->get_var( "SHOW COLUMNS FROM `". $db_prefix ."ac_sent_history` LIKE 'wcap_sent_trash';" ) ) {
                $wpdb->query( "ALTER TABLE ".$db_prefix."ac_sent_history DROP COLUMN `wcap_sent_trash`;" );
            }
                
            // @since 7.7 - Add new cart status for cart_ignored column in cart history table.
            $check_table_query = "SHOW COLUMNS FROM " . $db_prefix . "ac_abandoned_cart_history LIKE 'cart_ignored'";
            $results = $wpdb->get_results( $check_table_query );
            if ( isset( $results, $results[0]->Type ) && ( $results[0]->Type == "enum('0','1')" OR $results[0]->Type == "enum('0','1','2')" ) ) {
                $alter_table_query = "ALTER TABLE " . $db_prefix . "ac_abandoned_cart_history MODIFY COLUMN cart_ignored enum('0','1','2','3')";
                $wpdb->query( $alter_table_query );
            }
                
            /**
             * Create 3 new tables
             * @since 7.9
             */
            $wcap_collate = '';
            if ( $wpdb->has_cap( 'collation' ) ) {
                $wcap_collate = $wpdb->get_charset_collate();
            }
                
            $sql_parent = "CREATE TABLE IF NOT EXISTS " . $db_prefix . "ac_notifications (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `subject` text COLLATE utf8mb4_unicode_ci,
                                `body` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
                                `type` text COLLATE utf8mb4_unicode_ci NOT NULL,
                                `is_active` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
                                `frequency` text NOT NULL,
                                `coupon_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                                `default_template` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
                                PRIMARY KEY (`id`)
                                ) $wcap_collate AUTO_INCREMENT=1 ";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
            $wpdb->query( $sql_parent );
                 
            $sql_meta = "CREATE TABLE IF NOT EXISTS " . $db_prefix . "ac_notifications_meta (
                                `meta_id` int(11) NOT NULL AUTO_INCREMENT,
                                `template_id` int(11) NOT NULL,
                                `meta_key` text COLLATE utf8mb4_unicode_ci NOT NULL,
                                `meta_value` text COLLATE utf8mb4_unicode_ci,
                                PRIMARY KEY(`meta_id`)
                                ) $wcap_collate AUTO_INCREMENT=1";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
            $wpdb->query( $sql_meta );

            $sql_tinyurls = "CREATE TABLE IF NOT EXISTS " . $db_prefix . "ac_tiny_urls (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `cart_id` int(11) NOT NULL,
                                    `template_id` int(11) NOT NULL,
                                    `long_url` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `short_code` VARCHAR(10) COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `date_created` int(11) NOT NULL,
                                    `counter` int(11) NOT NULL DEFAULT '0',
                                    `notification_data` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                    PRIMARY KEY (`id`),
                                    KEY short_code (`short_code`)
                                    ) $wcap_collate AUTO_INCREMENT=1000000";
                                     
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
            $wpdb->query( $sql_tinyurls );

            /**
             * @since 7.11.0
             * Integration with Aelia Currency Switcher
             */
            $aelia_table = $db_prefix . "abandoned_cart_aelia_currency";

            $aelia_sql = "CREATE TABLE IF NOT EXISTS $aelia_table (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `abandoned_cart_id` int(11) COLLATE utf8_unicode_ci NOT NULL,
                        `acfac_currency` text COLLATE utf8_unicode_ci NOT NULL,
                        `date_time` TIMESTAMP on update CURRENT_TIMESTAMP COLLATE utf8_unicode_ci NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (`id`)
                        ) $wcap_collate AUTO_INCREMENT=1 ";           
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $wpdb->query( $aelia_sql );
                
            $check_query = "SELECT COUNT(id) FROM " . $db_prefix . "ac_notifications WHERE `default_template` = '1'";
                 
            $get_count = $wpdb->get_var( $check_query );

            $default_fb_body = array( 
                    '{"header":"We saved your cart","subheader":"Purchase now before they are out of stock","header_image":"' . WCAP_PLUGIN_URL . '/includes/fb-recovery/assets/css/images/carts_div.png","checkout_text":"Checkout Now!","unsubscribe_text":"Unsubscribe"}',
                    '{"header":"You left some items in your cart","subheader":"We have saved some items in your cart","header_image":"' . WCAP_PLUGIN_URL . '/includes/fb-recovery/assets/css/images/carts_div.png","checkout_text":"Checkout","unsubscribe_text":"Unsubscribe"}'
                );

            if( isset( $get_count ) && $get_count == 0 ) {

                // add 2 default sms templates and 2 FB templates
                $insert_templates = "INSERT INTO " . $db_prefix . "ac_notifications 
                                (`subject`, `body`, `type`, `is_active`, `frequency`, `coupon_code`, `default_template`) 
                                VALUES
                                ( 
                                    NULL,
                                    'Hey {{user.name}}, I noticed you left some products in your cart at {{shop.link}}. If you have any queries, please get in touch with me on {{phone.number}}. - {{shop.name}}', 
                                    'sms', 
                                    '0', 
                                    '30 minutes',
                                    '', 
                                    '1' 
                                ),
                                ( 
                                    NULL,
                                    'Hey {{user.name}}, we have saved your cart at {{shop.name}}. Complete your purchase using {{checkout.link}} now!', 
                                    'sms', 
                                    '0', 
                                    '1 days', 
                                    '',
                                    '1' 
                                ),
                                ( 
                                    'Hey there, We noticed that you left some great products in your cart at " . get_bloginfo( 'name' ) . ". Do not worry we saved them for you:',
                                    '" . $default_fb_body[0] . "', 
                                    'fb', 
                                    '0', 
                                    '30 minutes', 
                                    '',
                                    '1' 
                                ),
                                ( 
                                    'Hey there, There are some great products in your cart you left behind at " . get_bloginfo( 'name' ) . ". Here is a list of items you left behind:',
                                    '" . $default_fb_body[1] . "', 
                                    'fb', 
                                    '0', 
                                    '6 hours', 
                                    '',
                                    '1' 
                                )";
                $wpdb->query( $insert_templates );
            } else if( isset( $get_count ) && $get_count == 2 ) {

                /** @since 7.10.0 - Addded default FB templates **/
                // add 2 default FB templates
                $insert_fb = "INSERT INTO " . $db_prefix . "ac_notifications 
                                (`subject`, `body`, `type`, `is_active`, `frequency`, `coupon_code`, `default_template`) 
                                VALUES
                                ( 
                                    'Hey there, We noticed that you left some great products in your cart at " . get_bloginfo( 'name' ) . ". Do not worry we saved them for you:',
                                    '" . $default_fb_body[0] . "', 
                                    'fb', 
                                    '0', 
                                    '30 minutes', 
                                    '',
                                    '1' 
                                ),
                                ( 
                                    'Hey there, There are some great products in your cart you left behind at " . get_bloginfo( 'name' ) . ". Here is a list of items you left behind:',
                                    '" . $default_fb_body[1] . "', 
                                    'fb', 
                                    '0', 
                                    '6 hours', 
                                    '',
                                    '1' 
                                )";
                $wpdb->query( $insert_fb );
            }
                
                /** @since 7.10.0 - Added a new colum in Tiny URls **/
            if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `". $db_prefix."ac_tiny_urls` LIKE 'notification_data';" ) ) {
                $wpdb->query( "ALTER TABLE ".$db_prefix."ac_tiny_urls ADD `notification_data` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL;" );
            }

            // check the auto increment of the tiny urls table and update if needed
            $get_auto = $wpdb->get_results( "SHOW TABLE STATUS FROM `" . DB_NAME . "` WHERE `name` LIKE '" . $db_prefix . "ac_tiny_urls'" );
            if( isset( $get_auto[0]->Auto_increment ) && $get_auto[0]->Auto_increment < 1000000 ) {
                $wpdb->query( "ALTER TABLE  ". $db_prefix . "ac_tiny_urls AUTO_INCREMENT = 1000000" );
            }

            // if the tables are not indexed, add them
            $get_index_cart = "SHOW INDEX FROM " . $db_prefix . "ac_abandoned_cart_history WHERE Key_name = 'id'";
            $check_index_cart = $wpdb->get_results( $get_index_cart );
            if( is_array( $check_index_cart ) && count( $check_index_cart ) == 0 ) {
                $add_index_cart = "CREATE UNIQUE INDEX id ON " . $db_prefix . "ac_abandoned_cart_history (id)";
                $wpdb->query( $add_index_cart );
            }

            $get_index_guest = "SHOW INDEX FROM " . $db_prefix . "ac_guest_abandoned_cart_history WHERE Key_name = 'id'";
            $check_index_guest = $wpdb->get_results( $get_index_guest );
            if( is_array( $check_index_guest ) && count( $check_index_guest ) == 0 ) {
                $add_index_guest = "CREATE UNIQUE INDEX id on " . $db_prefix . "ac_guest_abandoned_cart_history (id)";
                $wpdb->query( $add_index_guest );
            }

            $get_index_sent = "SHOW INDEX FROM " . $db_prefix . "ac_sent_history WHERE Key_name = 'order_id'";
            $check_index_sent = $wpdb->get_results( $get_index_sent );
            if( is_array( $check_index_sent ) && count( $check_index_sent ) == 0 ) {
                $add_index_sent = "CREATE INDEX order_id on " . $db_prefix . "ac_sent_history (abandoned_order_id)";
                $wpdb->query( $add_index_sent );
            }
                
            if( $blog_id === 0 ) {
                $cleanup_data               = get_option( 'ac_pro_user_cleanup', '' );
                $move_templates_data        = get_option( 'wcap_move_templates_data' );
                $wcap_get_admin_option      = get_option( 'ac_email_admin_on_abandoned' );
                $update_sent_history        = get_option( 'wcap_cleanup_sent_history' );
                $update_coupon_code_meta    = get_option( 'wcap_update_coupon_code_meta' );
                
                if ( !get_option( 'ac_cart_abandoned_time_guest' ) ) {
                    $cart_abandoned_time = get_option( 'ac_cart_abandoned_time' );
                    update_option( 'ac_cart_abandoned_time_guest', $cart_abandoned_time );
                }

                $wcap_check_option_available        = "SELECT `option_name` FROM {$db_prefix}options WHERE `option_name` LIKE 'wcap_use_auto_cron'";
                $result_wcap_check_option_available = $wpdb->get_results ($wcap_check_option_available);

                if ( count( $result_wcap_check_option_available ) == 0  ) {
                    $wcap_auto_cron = 'on';
                    update_option( 'wcap_use_auto_cron', $wcap_auto_cron );
                }

                if ( !get_option( 'wcap_cron_time_duration' ) ) {
                    $wcap_cron_duration_time_minutes = 15;
                    update_option( 'wcap_cron_time_duration', $wcap_cron_duration_time_minutes );
                }

                if ( !get_option( 'wcap_new_default_templates' ) ) {
                    $default_template = new Wcap_Default_Settings();
                    $default_template->wcap_create_default_templates( $db_prefix, $blog_id );
                }

            } else {
                $cleanup_data               = get_blog_option( $blog_id, 'ac_pro_user_cleanup' );
                $move_templates_data        = get_blog_option( $blog_id, 'wcap_move_templates_data' );
                $wcap_get_admin_option      = get_blog_option( $blog_id, 'ac_email_admin_on_abandoned' );
                $update_sent_history        = get_blog_option( $blog_id, 'wcap_cleanup_sent_history' );
                $update_coupon_code_meta    = get_blog_option( $blog_id, 'wcap_update_coupon_code_meta' );

                if ( !get_blog_option( $blog_id, 'ac_cart_abandoned_time_guest' ) ) {
                    $cart_abandoned_time = get_blog_option( $blog_id, 'ac_cart_abandoned_time' );
                    update_blog_option( $blog_id, 'ac_cart_abandoned_time_guest', $cart_abandoned_time );
                }

                $wcap_check_option_available        = "SELECT `option_name` FROM {$db_prefix}options WHERE `option_name` LIKE 'wcap_use_auto_cron'";
                $result_wcap_check_option_available = $wpdb->get_results ($wcap_check_option_available);

                if ( count( $result_wcap_check_option_available ) == 0  ) {
                    $wcap_auto_cron = 'on';
                    update_blog_option( $blog_id, 'wcap_use_auto_cron', $wcap_auto_cron );
                }

                if ( !get_blog_option( $blog_id, 'wcap_cron_time_duration' ) ) {
                    $wcap_cron_duration_time_minutes = 15;
                    update_blog_option( $blog_id, 'wcap_cron_time_duration', $wcap_cron_duration_time_minutes );
                }

                if ( !get_blog_option( $blog_id, 'wcap_new_default_templates' ) ) {
                    $default_template = new Wcap_Default_Settings();
                    $default_template->wcap_create_default_templates( $db_prefix, $blog_id );
                }
            }
            
            if ( 'yes' !== $cleanup_data ) {
                $query_cleanup = "UPDATE `" . $db_prefix . "ac_guest_abandoned_cart_history` SET 
                        billing_first_name = IF (billing_first_name LIKE '%<%', '', billing_first_name),
                        billing_last_name = IF (billing_last_name LIKE '%<%', '', billing_last_name),
                        billing_company_name = IF (billing_company_name LIKE '%<%', '', billing_company_name),
                        billing_address_1 = IF (billing_address_1 LIKE '%<%', '', billing_address_1),
                        billing_address_2 = IF (billing_address_2 LIKE '%<%', '', billing_address_2),
                        billing_city = IF (billing_city LIKE '%<%', '', billing_city),
                        billing_county = IF (billing_county LIKE '%<%', '', billing_county),
                        billing_zipcode = IF (billing_zipcode LIKE '%<%', '', billing_zipcode),
                        email_id = IF (email_id LIKE '%<%', '', email_id),
                        phone = IF (phone LIKE '%<%', '', phone),
                        ship_to_billing = IF (ship_to_billing LIKE '%<%', '', ship_to_billing),
                        order_notes = IF (order_notes LIKE '%<%', '', order_notes),
                        shipping_first_name = IF (shipping_first_name LIKE '%<%', '', shipping_first_name),
                        shipping_last_name = IF (shipping_last_name LIKE '%<%', '', shipping_last_name),
                        shipping_company_name = IF (shipping_company_name LIKE '%<%', '', shipping_company_name),
                        shipping_address_1 = IF (shipping_address_1 LIKE '%<%', '', shipping_address_1),
                        shipping_address_2 = IF (shipping_address_2 LIKE '%<%', '', shipping_address_2),
                        shipping_city = IF (shipping_city LIKE '%<%', '', shipping_city),
                        shipping_county = IF (shipping_county LIKE '%<%', '', shipping_county)";

                $wpdb->query( $query_cleanup );

                $email = 'woouser401a@mailinator.com';
                $exists = email_exists( $email );
                if ( $exists ) {
                    wp_delete_user( esc_html( $exists ) );
                }

                if( $blog_id === 0 ) {
                    update_option( 'ac_pro_user_cleanup', 'yes' );
                } else {
                    update_blog_option( $blog_id, 'ac_pro_user_cleanup', 'yes' );
                }
            }

            // @since 7.14.0
            if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `". $db_prefix."ac_email_templates` LIKE 'cart_rules';" ) ) {
                $wpdb->query( "ALTER TABLE ". $db_prefix."ac_email_templates ADD `cart_rules` varchar(50) COLLATE   utf8_unicode_ci NOT NULL AFTER `wc_template_filter`;" );
            }

            if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `". $db_prefix."ac_email_templates` LIKE 'product_ids';" ) ) {
                $wpdb->query( "ALTER TABLE ".$db_prefix."ac_email_templates ADD `product_ids` varchar(100) COLLATE     utf8_unicode_ci NOT NULL AFTER `cart_rules`;" );
            }

            // Move all the email template data that is saved in post meta table to the email templates table. 
            if( 'yes' != $move_templates_data ) {
                $columns_added = false; 
                if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `". $db_prefix ."ac_email_templates` LIKE 'send_emails_to'" ) ) {
                    $wpdb->query( "ALTER TABLE " . $db_prefix . "ac_email_templates ADD `send_emails_to` VARCHAR(300) NOT NULL AFTER `product_ids`" );
                    $columns_added = true;
                }

                if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `".$db_prefix."ac_email_templates` LIKE 'activated_time'" ) ) {
                    $wpdb->query( "ALTER TABLE " . $db_prefix . "ac_email_templates ADD `activated_time` int(11) NOT NULL AFTER `send_emails_to`" );
                    $columns_added = true;
                }

                //
                $email_templates = $wpdb->get_col( "SELECT id FROM " . $db_prefix . "ac_email_templates" );
                if( is_array( $email_templates ) && $columns_added ) {
                    
                    foreach( $email_templates as $id ) {
                        $activated_time = get_post_meta( $id, 'wcap_template_time', true );
                        $email_action = get_post_meta( $id, 'wcap_email_action', true );
                        $other_action = get_post_meta( $id, 'wcap_other_emails', true );

                        $send_emails_data = json_encode( array(  'action' => $email_action,
                                                    'others' => trim( implode( ',', explode( PHP_EOL, $other_action ) ) ) ) );
                        $update_array = [  'activated_time' => $activated_time,
                                        'send_emails_to' => $send_emails_data ];

                        $wpdb->update( $db_prefix . "ac_email_templates", $update_array, array( 'id' => $id ) );

                    }

                    // delete all the data from post meta
                    $wpdb->query( "DELETE FROM `" . $db_prefix . "postmeta` WHERE meta_key = 'wcap_template_time'" );
                    $wpdb->query( "DELETE FROM `" . $db_prefix . "postmeta` WHERE meta_key = 'wcap_email_action'" );
                    $wpdb->query( "DELETE FROM `" . $db_prefix . "postmeta` WHERE meta_key = 'wcap_other_emails'" ); 
    
                }

                if( $blog_id === 0 ) {
                    update_option( 'wcap_move_templates_data', 'yes' );
                } else {
                    update_blog_option( $blog_id, 'wcap_move_templates_data', 'yes' );
                }
            }
            
            // since 8.4
            if ( '' == $update_sent_history ) {
                $last_365 = date( 'Y-m-d H:i:s', strtotime( '-365 days', current_time( 'timestamp' ) ) );
                $wpdb->query( "UPDATE " . WCAP_EMAIL_SENT_HISTORY_TABLE . " as sent LEFT JOIN " . WCAP_EMAIL_CLICKED_TABLE . " as link ON sent.id = link.email_sent_id SET sent.recovered_order = '0' where link.id IS NULL AND sent.recovered_order = '1' AND sent.sent_time > '$last_365'" );

                if ( 0 === $blog_id ) {
                    update_option( 'wcap_cleanup_sent_history', 'yes' );
                } else {
                    update_blog_option( $blog_id, 'wcap_cleanup_sent_history', 'yes' );
                }

            }

            /* Adding coupon meta to coupons which are generated by this plugin */
            if ( 'yes' !== $update_coupon_code_meta ) {

                $args = array(
                    'posts_per_page' => -1,
                    'post_type'      => 'shop_coupon',
                    'post_status'    => 'publish',
                    'post_content'   => 'This coupon provides 5% discount on cart price.'
                );

                $coupons = get_posts( $args );
                foreach ( $coupons as $key => $value ) {
                    update_post_meta( $value->ID, 'wcap_created_by', 'wcap' );
                }

                if ( $blog_id === 0 ) {
                    update_option( 'wcap_update_coupon_code_meta', 'yes' );
                    update_option( 'ac_cart_abandoned_after_x_days_order_placed', 7 );
                } else {
                    update_blog_option( $blog_id, 'wcap_update_coupon_code_meta', 'yes' );
                    update_blog_option( $blog_id, 'ac_cart_abandoned_after_x_days_order_placed', 7 );
                }
            }
            
            // @since 8.7.0 - Add Checkout Links - Currently used in Webhooks. These links can be used directly to recover the cart.
            if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `".$db_prefix."ac_abandoned_cart_history` LIKE 'checkout_link'" ) ) {
                $wpdb->query( "ALTER TABLE " . $db_prefix . "ac_abandoned_cart_history ADD `checkout_link` varchar(200) NOT NULL AFTER `wcap_trash`" );
            }

            /**
             * Please change in this function if it requires any updation in tables or in options
             */
            Wcap_Update_Check::wcap_alter_tables_if_required( $db_prefix, $blog_id );
            Wcap_Update_Check::wcap_update_options_if_required( $blog_id );

            if ( ( $wcap_get_admin_option == 'on' || '' == $wcap_get_admin_option ) && false !== $wcap_get_admin_option ) {
                Wcap_Update_Check::wcap_add_customer_for_template( $db_prefix, $blog_id );
            }
        }

        /**
         * It will alter the tables if required.
         * @globals mixed $wpdb
         * @since 5.0
         */
        public static function wcap_alter_tables_if_required( $db_prefix = '', $blog_id = 0 ) {

            if( $db_prefix === '' ) {
                return;
            }

            if( $blog_id === 0 ) {
                $guest_user_id_altered = get_option ('wcap_guest_user_id_altered');
                $alter_tables_ran = get_option( 'wcap_alter_tables_ran' );
                $alter_guest_columns = get_option( 'wcap_alter_guest_columns' );
            } else {
                $guest_user_id_altered = get_blog_option( $blog_id, 'wcap_guest_user_id_altered');
                $alter_tables_ran = get_blog_option( $blog_id, 'wcap_alter_tables_ran' );
                $alter_guest_columns = get_blog_option( $blog_id, 'wcap_alter_guest_columns' );
            }

            global $wpdb;
            if ( $wpdb->get_var( "SHOW TABLES LIKE '".$db_prefix."ac_guest_abandoned_cart_history' " )  && 'yes' != $guest_user_id_altered ) {
                $last_id = $wpdb->get_var( "SELECT max(id) FROM `".$db_prefix."ac_guest_abandoned_cart_history`;" );
                if ( NULL != $last_id && $last_id <= 63000000 ) {
                    $wpdb->query( "ALTER TABLE ".$db_prefix."ac_guest_abandoned_cart_history AUTO_INCREMENT = 63000000;" );
                    update_option ( 'wcap_guest_user_id_altered' , 'yes' );
                }
            }
            if ( '1' != $alter_tables_ran ) {

                if ( $wpdb->get_var( "SHOW TABLES LIKE '".$db_prefix."ac_email_templates' " ) ) {
                    
                    if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `".$db_prefix."ac_email_templates` LIKE 'wc_template_filter';" ) ) {
                        $wpdb->query( "ALTER TABLE ".$db_prefix."ac_email_templates ADD `wc_template_filter` varchar(500) COLLATE utf8_unicode_ci NOT NULL AFTER `wc_email_header`;" );
                    }
                }
                
                if ( $wpdb->get_var( "SHOW TABLES LIKE '".$db_prefix."ac_abandoned_cart_history' " ) ) {
                    if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `".$db_prefix."ac_abandoned_cart_history` LIKE 'language';" ) ) {
                        $wpdb->query( "ALTER TABLE ".$db_prefix."ac_abandoned_cart_history ADD `language` varchar(50) COLLATE utf8_unicode_ci NOT NULL AFTER `user_type`;" );
                    }
                    
                    if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `".$db_prefix."ac_abandoned_cart_history` LIKE 'session_id';" ) ) {
                        $wpdb->query( "ALTER TABLE ".$db_prefix."ac_abandoned_cart_history ADD `session_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL AFTER `language`;" );
                    }
                    
                    if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `".$db_prefix."ac_abandoned_cart_history` LIKE 'ip_address';" ) ) {
                        $wpdb->query( "ALTER TABLE ".$db_prefix."ac_abandoned_cart_history ADD `ip_address` longtext COLLATE utf8_unicode_ci NOT NULL AFTER  `session_id`;" );
                    }
                    
                    if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `".$db_prefix."ac_abandoned_cart_history` LIKE 'manual_email';" ) ) {
                        $wpdb->query( "ALTER TABLE ".$db_prefix."ac_abandoned_cart_history ADD `manual_email` varchar(50) COLLATE utf8_unicode_ci NOT NULL AFTER `ip_address`;" );
                    }
                    
                    if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `".$db_prefix."ac_abandoned_cart_history` LIKE 'wcap_trash';" ) ) {
                        $wpdb->query( "ALTER TABLE ".$db_prefix."ac_abandoned_cart_history ADD `wcap_trash` varchar(1) COLLATE utf8_unicode_ci NOT NULL AFTER `manual_email`;" );
                    }
                }

                /**
                 * Since 4.3
                 * We have added Trash feature in the sent emails tab. It will add new coulmn in the sent history table
                 */
                if ( $wpdb->get_var( "SHOW TABLES LIKE '". $db_prefix ."ac_sent_history'" ) ) {
                    if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `".$db_prefix."ac_sent_history` LIKE 'wcap_sent_trash';" ) ) {
                        $wpdb->query( "ALTER TABLE ".$db_prefix."ac_sent_history ADD `wcap_sent_trash` varchar(1) COLLATE utf8_unicode_ci NOT NULL AFTER `recovered_order`;" );
                    }
                }
                /**
                 * This is used to prevent guest users wrong Id. If guest users id is less then 63000000 then this code will ensure that we will change the id of guest tables so it wont affect on the next guest users.
                 */
        /**        if ( $wpdb->get_var( "SHOW TABLES LIKE '".$db_prefix."ac_guest_abandoned_cart_history' " )  && 'yes' != get_option ('wcap_guest_user_id_altered') ) {
                    $last_id = $wpdb->get_var( "SELECT max(id) FROM `".$db_prefix."ac_guest_abandoned_cart_history`;" );
                    if ( NULL != $last_id && $last_id <= 63000000 ) {
                        $wpdb->query( "ALTER TABLE ".$db_prefix."ac_guest_abandoned_cart_history AUTO_INCREMENT = 63000000;" );
                        update_option ( 'wcap_guest_user_id_altered' , 'yes' );
                    }
                } **/

                /*
                 * Since 4.7
                 * We have moved email templates fields in the setings section. SO to remove that fields column fro the db we need it.
                 * For existing user we need to fill this setting with the first template.
                 */
                if ( $wpdb->get_var( "SHOW TABLES LIKE '".$db_prefix."ac_email_templates' " ) ) {

                    if ( $wpdb->get_var( "SHOW COLUMNS FROM `".$db_prefix."ac_email_templates` LIKE 'from_email';" ) ) {
                        $get_email_template_query  = "SELECT `from_email` FROM ".$db_prefix."ac_email_templates WHERE `is_active` = '1' ORDER BY `id` ASC LIMIT 1";
                        $get_email_template_result = $wpdb->get_results ($get_email_template_query);

                        $wcap_from_email = '';
                        if ( isset( $get_email_template_result ) && count ( $get_email_template_result ) > 0 ) {
                            $wcap_from_email =  $get_email_template_result[0]->from_email;

                            /* Store data in setings api*/
                            if( $blog_id === 0 ) {
                                update_option ( 'wcap_from_email', $wcap_from_email );
                            } else {
                                update_blog_option( $blog_id, 'wcap_from_email', $wcap_from_email );
                            }   

                            /* Delete table from the Db*/
                            $wpdb->query( "ALTER TABLE ".$db_prefix."ac_email_templates DROP COLUMN `from_email`;" );
                        }
                    }

                    if ( $wpdb->get_var( "SHOW COLUMNS FROM `".$db_prefix."ac_email_templates` LIKE 'from_name';" ) ) {
                        $get_email_template_from_name_query  = "SELECT `from_name` FROM ".$db_prefix."ac_email_templates WHERE `is_active` = '1' ORDER BY `id` ASC LIMIT 1";
                        $get_email_template_from_name_result = $wpdb->get_results ($get_email_template_from_name_query);

                        $wcap_from_name = '';
                        if ( isset( $get_email_template_from_name_result ) && count ( $get_email_template_from_name_result ) > 0 ){
                            $wcap_from_name =  $get_email_template_from_name_result[0]->from_name;

                            /* Store data in setings api*/
                            if( $blog_id === 0 ) {
                                update_option( 'wcap_from_name', $wcap_from_name );
                            } else {
                                update_blog_option( $blog_id, 'wcap_from_name', $wcap_from_name );
                            }

                            /* Delete table from the Db*/
                            $wpdb->query( "ALTER TABLE ".$db_prefix."ac_email_templates DROP COLUMN `from_name`;" );
                        }
                    }

                    if ( $wpdb->get_var( "SHOW COLUMNS FROM `".$db_prefix."ac_email_templates` LIKE 'reply_email';" ) ) {
                        $get_email_template_reply_email_query  = "SELECT `reply_email` FROM ".$db_prefix."ac_email_templates WHERE `is_active` = '1' ORDER BY `id` ASC LIMIT 1";
                        $get_email_template_reply_email_result = $wpdb->get_results ($get_email_template_reply_email_query);

                        $wcap_reply_email = '';
                        if ( isset( $get_email_template_reply_email_result ) && count ( $get_email_template_reply_email_result ) > 0 ){
                            $wcap_reply_email =  $get_email_template_reply_email_result[0]->reply_email;

                            /* Store data in setings api*/
                            if( $blog_id === 0 ) {
                                update_option( 'wcap_reply_email', $wcap_reply_email );
                            } else {
                                update_option( $blog_id, 'wcap_reply_email', $wcap_reply_email );
                            }

                            /* Delete table from the Db*/
                            $wpdb->query( "ALTER TABLE ".$db_prefix."ac_email_templates DROP COLUMN `reply_email`;" );
                        }
                    }
                }

                if( $blog_id === 0 ) {
                    update_option( 'wcap_alter_tables_ran', '1', 'no' );
                } else {
                    update_blog_option( $blog_id, 'wcap_alter_tables_ran', '1' );
                }
            }

            if ( '1' != $alter_guest_columns ) {
                $wpdb->query( "
                    ALTER TABLE " . $db_prefix . "ac_guest_abandoned_cart_history 
                    ADD billing_country TEXT AFTER billing_last_name" );
                if( $blog_id === 0 ) {
                    update_option( 'wcap_alter_guest_columns', '1', 'no' );
                } else {
                    update_blog_option( $blog_id, 'wcap_alter_guest_columns', '1' );
                }
            }

            /* Creating new columns for coupon code option available in the email template */
            if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `". $db_prefix."ac_email_templates` LIKE 'discount_type';" ) ) {                
                $wpdb->query( "ALTER TABLE ".$db_prefix."ac_email_templates ADD `discount_type` VARCHAR(50) DEFAULT 'percent' AFTER `discount`, ADD `discount_shipping` VARCHAR(50) DEFAULT 'no' AFTER `discount_type`, ADD `discount_expiry` VARCHAR(50) DEFAULT '7-days' AFTER `discount_shipping`;" );
            }


        }

        /**
         * It will alter the options if required.
         * @globals mixed $wpdb
         * @since 5.0
         */
        public static function wcap_update_options_if_required( $blog_id = 0 ) {

            $wcap_product_image_height_px = 125;
            $wcap_product_image_width_px = 125;
            $wcap_atc_heading = 'Please enter your email';
            $wcap_atc_heading_color = '#737f97';
            $wcap_atc_sub_text = 'To add this item to your cart, please enter your email address.';
            $wcap_atc_sub_text_color = '#bbc9d2';
            $wcap_atc_email_field_placeholder = 'Email address';
            $wcap_atc_button = 'Add to Cart';
            $wcap_atc_button_color = '#0085ba';
            $wcap_atc_button_text_color = '#ffffff';
            $wcap_atc_non_mandatory_text = 'No thanks';
            $wcap_atc_mandatory = 'off';
            $wcap_atc_email_mandatory = 'on';
            $wcap_countdown_cart = 'on';
            $countdown_timer_msg = 'Coupon <coupon_code> expires in <hh:mm:ss>. Avail it now.';
            
            if( $blog_id === 0 ) {
                if ( '1' != get_option( 'wcap_update_options_ran' ) ) {

                    if ( !get_option( 'ac_security_key' ) ){
                        update_option( 'ac_security_key', "qJB0rGtIn5UB1xG03efyCp" );
                    }

                    if ( !get_option( 'wcap_product_image_height' ) ) {
                        update_option( 'wcap_product_image_height', $wcap_product_image_height_px);
                    }

                    if ( !get_option( 'wcap_product_image_width' ) ) {
                        update_option( 'wcap_product_image_width', $wcap_product_image_width_px );
                    }

                    if ( !get_option( 'wcap_heading_section_text_email' ) ) {
                        add_option( 'wcap_heading_section_text_email', $wcap_atc_heading );
                    }
                    
                    if ( !get_option( 'wcap_popup_heading_color_picker' ) ) {
                        add_option( 'wcap_popup_heading_color_picker', $wcap_atc_heading_color );
                    }

                    if ( !get_option( 'wcap_text_section_text' ) ) {
                        add_option( 'wcap_text_section_text', $wcap_atc_sub_text );
                    }

                    if ( !get_option( 'wcap_popup_text_color_picker' ) ) {
                        add_option( 'wcap_popup_text_color_picker', $wcap_atc_sub_text_color );
                    }

                    if ( !get_option( 'wcap_email_placeholder_section_input_text' ) ) {
                        add_option( 'wcap_email_placeholder_section_input_text', $wcap_atc_email_field_placeholder );
                    }

                    if ( !get_option( 'wcap_button_section_input_text' ) ) {
                        add_option( 'wcap_button_section_input_text', $wcap_atc_button );
                    }

                    if ( !get_option( 'wcap_button_color_picker' ) ) {
                        add_option( 'wcap_button_color_picker', $wcap_atc_button_color );
                    }

                    if ( !get_option( 'wcap_button_text_color_picker' ) ) {
                        add_option( 'wcap_button_text_color_picker', $wcap_atc_button_text_color );
                    }
                    
                    if ( !get_option( 'wcap_non_mandatory_text' ) ) {
                        add_option( 'wcap_non_mandatory_text', $wcap_atc_non_mandatory_text );
                    }
                    
                    if ( !get_option( 'wcap_atc_enable_modal' ) ) {
                        add_option( 'wcap_atc_enable_modal', $wcap_atc_mandatory );
                    }
                    
                    if ( !get_option( 'wcap_atc_mandatory_email' ) ) {
                        add_option( 'wcap_atc_mandatory_email', $wcap_atc_email_mandatory );
                    }

                    update_option( 'wcap_update_options_ran', '1', 'no' );

                }

                if ( !get_option( 'wcap_fb_consent_text' ) ) {
                    add_option( 'wcap_fb_consent_text', 'Allow order status to be sent to Facebook Messenger' );
                }

                if( !get_option( 'wcap_countdown_cart' ) ) {
                    add_option( 'wcap_countdown_cart', $wcap_countdown_cart );
                }

                if( !get_option( 'wcap_countdown_timer_msg' ) ) {
					add_option( 'wcap_countdown_timer_msg', $countdown_timer_msg );
                }
                
                if( false === get_option( 'wcap_atc_coupon_validity_minutes', false ) ) {
                    if( 0 < get_option( 'wcap_atc_popup_coupon_validity', 0 ) ) {
                        $validity_in_minutes = get_option( 'wcap_atc_popup_coupon_validity' ) * 60;
                        update_option( 'wcap_atc_popup_coupon_validity', $validity_in_minutes );
                        update_option( 'wcap_atc_coupon_validity_minutes', true );
                    }
                }
            } else {

                if ( '1' != get_blog_option( $blog_id, 'wcap_update_options_ran' ) ) {

                    if ( !get_blog_option( $blog_id, 'ac_security_key' ) ){
                        update_blog_option( $blog_id, 'ac_security_key', "qJB0rGtIn5UB1xG03efyCp" );
                    }

                    if ( !get_blog_option( $blog_id, 'wcap_product_image_height' ) ) {
                        update_blog_option( $blog_id, 'wcap_product_image_height', $wcap_product_image_height_px);
                    }

                    if ( !get_blog_option( $blog_id, 'wcap_product_image_width' ) ) {
                        update_blog_option( $blog_id, 'wcap_product_image_width', $wcap_product_image_width_px );
                    }

                    if ( !get_blog_option( $blog_id, 'wcap_heading_section_text_email' ) ) {
                        add_blog_option( $blog_id, 'wcap_heading_section_text_email', $wcap_atc_heading );
                    }
                    
                    if ( !get_blog_option( $blog_id, 'wcap_popup_heading_color_picker' ) ) {
                        add_blog_option( $blog_id, 'wcap_popup_heading_color_picker', $wcap_atc_heading_color );
                    }

                    if ( !get_blog_option( $blog_id, 'wcap_text_section_text' ) ) {
                        add_blog_option( $blog_id, 'wcap_text_section_text', $wcap_atc_sub_text );
                    }

                    if ( !get_blog_option( $blog_id, 'wcap_popup_text_color_picker' ) ) {
                        add_blog_option( $blog_id, 'wcap_popup_text_color_picker', $wcap_atc_sub_text_color );
                    }

                    if ( !get_blog_option( $blog_id, 'wcap_email_placeholder_section_input_text' ) ) {
                        add_blog_option( $blog_id, 'wcap_email_placeholder_section_input_text', $wcap_atc_email_field_placeholder );
                    }

                    if ( !get_blog_option( $blog_id, 'wcap_button_section_input_text' ) ) {
                        add_blog_option( $blog_id, 'wcap_button_section_input_text', $wcap_atc_button );
                    }

                    if ( !get_blog_option( $blog_id, 'wcap_button_color_picker' ) ) {
                        add_blog_option( $blog_id, 'wcap_button_color_picker', $wcap_atc_button_color );
                    }

                    if ( !get_blog_option( $blog_id, 'wcap_button_text_color_picker' ) ) {
                        add_blog_option( $blog_id, 'wcap_button_text_color_picker', $wcap_atc_button_text_color );
                    }
                    
                    if ( !get_blog_option( $blog_id, 'wcap_non_mandatory_text' ) ) {
                        add_blog_option( $blog_id, 'wcap_non_mandatory_text', $wcap_atc_non_mandatory_text );
                    }
                    
                    if ( !get_blog_option( $blog_id, 'wcap_atc_enable_modal' ) ) {
                        add_blog_option( $blog_id, 'wcap_atc_enable_modal', $wcap_atc_mandatory );
                    }
                    
                    if ( !get_blog_option( $blog_id, 'wcap_atc_mandatory_email' ) ) {
                        add_blog_option( $blog_id, 'wcap_atc_mandatory_email', $wcap_atc_email_mandatory );
                    }

                    update_blog_option( $blog_id, 'wcap_update_options_ran', '1' );

                }

                if ( !get_blog_option( $blog_id, 'wcap_fb_consent_text' ) ) {
                    add_blog_option( $blog_id, 'wcap_fb_consent_text', 'Allow order status to be sent to Facebook Messenger' );
                }

                if( !get_blog_option( $blog_id, 'wcap_countdown_cart' ) ) {
                    add_blog_option( $blog_id, 'wcap_countdown_cart', $wcap_countdown_cart );
                }

                if( !get_blog_option( $blog_id, 'wcap_countdown_timer_msg' ) ) {
					add_blog_option( $blog_id, 'wcap_countdown_timer_msg', $countdown_timer_msg );
                }
                
                if( false === get_blog_option( $blog_id, 'wcap_atc_coupon_validity_minutes', false ) ) {
                    if( 0 < get_blog_option( $blog_id, 'wcap_atc_popup_coupon_validity', 0 ) ) {
                        $validity_in_minutes = get_blog_option( $blog_id, 'wcap_atc_popup_coupon_validity' ) * 60;
                        update_blog_option( $blog_id, 'wcap_atc_popup_coupon_validity', $validity_in_minutes );
                        update_blog_option( $blog_id, 'wcap_atc_coupon_validity_minutes', true );
                    }
                }
            }
            
        }
        /**
         * When we update the plugin we need to add who will receive the email template.
         * @globals mixed $wpdb
         * @since 7.1
         */
        public static function wcap_add_customer_for_template( $db_prefix, $blog_id ) {
            global $wpdb;

            $wcap_get_all_template   = "SELECT id FROM ". $db_prefix . "ac_email_templates";
            $wcap_result_of_template = $wpdb->get_results ( $wcap_get_all_template );

            if( $blog_id === 0 ) {
                $wcap_admin_setting = get_option ('ac_email_admin_on_abandoned');
            } else {
                $wcap_admin_setting = get_blog_option( $blog_id, 'ac_email_admin_on_abandoned');
            }

            if ( isset( $wcap_admin_setting ) && 'on' == $wcap_admin_setting ) {
                $wcap_customers_key = 'wcap_email_customer_admin';
            }else{
                $wcap_customers_key = 'wcap_email_customer';
            }
            if ( count( $wcap_result_of_template ) > 0 ) {

                foreach ($wcap_result_of_template as $wcap_result_of_template_key => $wcap_result_of_template_value ) {
                    add_post_meta( $wcap_result_of_template_value->id , 'wcap_email_action', $wcap_customers_key );
                }

                if( $blog_id === 0 ) {
                    delete_option ( 'ac_email_admin_on_abandoned' );
                } else {
                    delete_blog_option ( $blog_id, 'ac_email_admin_on_abandoned' );
                }
            
            }
        }
    }
}
