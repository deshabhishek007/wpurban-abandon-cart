<?php
/*
* Plugin Name: Abandoned Cart for WooCommerce
* Plugin URI: http://www.wpurban.com/store/premium-plugins/woocommerce-abandoned-cart
* Description: This plugin captures abandoned carts by logged-in users and guest users. It allows to create multiple email templates to be sent at fixed intervals. Thereby reminding customers about their abandoned orders & resulting in increased sales by completing those orders. Go to <strong>WooCommerce -> <a href="admin.php?page=woocommerce_ac_page">Abandoned Carts</a> </strong>to get started.
* Version: 8.7.2
* Author: WPUrban
* Author URI: http://www.wpurban.com/
* Text Domain: woocommerce-ac
* Domain Path: /i18n/languages/
* Requires PHP: 5.6
* WC requires at least: 3.0.0
* WC tested up to: 4.0.1
*
* @package Abandoned-Cart-Pro-for-WooCommerce
*/


global $ACUpdateChecker;
$ACUpdateChecker = '8.7.2';

/**
 * This is the URL our updater / license checker pings. This should be the URL of the site with EDD installed.
 * IMPORTANT: change the name of this constant to something unique to prevent conflicts with other plugins using this system.
 */ 
define( 'EDD_SL_STORE_URL_AC_WOO', 'http://www.tychesoftwares.com/' );

/**
 * The name of your product. This is the title of your product in EDD and should match the download title in EDD exactly.
 * IMPORTANT: change the name of this constant to something unique to prevent conflicts with other plugins using this system.
 */ 
define( 'EDD_SL_ITEM_NAME_AC_WOO', 'Abandoned Cart Pro for WooCommerce' );

if( ! class_exists( 'EDD_AC_WOO_Plugin_Updater' ) ) {
    // load our custom updater if it doesn't already exist
    include( dirname( __FILE__ ) . '/plugin-updates/EDD_AC_WOO_Plugin_Updater.php' );
}
/**
 * Retrieve our license key from the DB
 */ 
$license_key = trim( get_option( 'edd_sample_license_key_ac_woo' ) );
/**
 * Setup the updater
 */ 
$edd_updater = new EDD_AC_WOO_Plugin_Updater( EDD_SL_STORE_URL_AC_WOO, __FILE__, array(
        'version'   => '8.7.2',                     // current version number
        'license'   => $license_key,                // license key (used get_option above to retrieve from DB)
        'item_name' => EDD_SL_ITEM_NAME_AC_WOO,     // name of this plugin
        'author'    => 'Ashok Rane'                 // author of this plugin
        )
);

/**
 * Woocommerce_Abandon_Cart class
 */
if ( ! class_exists( 'Woocommerce_Abandon_Cart' ) ) {

    /**
     * It will call all the functions for the file inclusion, global variable and action & filter
     * @since 1.0
     */
    class Woocommerce_Abandon_Cart {
        public  $one_hour;
        public  $three_hours;
        public  $six_hours;
        public  $twelve_hours;
        public  $one_day;
        public  $one_week;
        public  $duration_range_select = array();
        public  $start_end_dates = array();

        /**
         * It will call all the functions for the file inclusion, global variable and action & filter
         */
        public function __construct() {

            $this->wcap_declare_variable();
            $this->wcap_load_files();

            do_action( 'wcap_after_load_files' );

            // Initialize settings
            register_activation_hook( __FILE__, array( 'Wcap_Activate_Plugin', 'wcap_activate' ) );

            $this->wcap_load_hooks();

            do_action( 'wcap_after_load_hooks' );
        }

        /**
         * Declare the common variables and the constants needed for the plugin.
         * @since 5.0
         */
        function wcap_declare_variable (){

            $this->one_hour     = 60 * 60;
            $this->three_hours  = 3  * $this->one_hour;
            $this->six_hours    = 6  * $this->one_hour;
            $this->twelve_hours = 12 * $this->one_hour;
            $this->one_day      = 24 * $this->one_hour;
            $this->one_week     = 7  * $this->one_day;
            $this->duration_range_select = array(
                    'yesterday'         => __( 'Yesterday',    'woocommerce-ac' ),
                    'today'             => __( 'Today',        'woocommerce-ac' ),
                    'last_seven'        => __( 'Last 7 days',  'woocommerce-ac' ),
                    'last_fifteen'      => __( 'Last 15 days', 'woocommerce-ac' ),
                    'last_thirty'       => __( 'Last 30 days', 'woocommerce-ac' ),
                    'last_ninety'       => __( 'Last 90 days', 'woocommerce-ac' ),
                    'last_year_days'    => __( 'Last 365',     'woocommerce-ac' ) );

            $this->start_end_dates = array(
                'yesterday'     => array( 'start_date' => date( "d M Y", ( current_time('timestamp') - 24*60*60 ) ), 'end_date' => date( "d M Y", ( current_time( 'timestamp' ) - 7*24*60*60 ) ) ),

                'today'         => array( 'start_date' => date( "d M Y", ( current_time( 'timestamp' ) ) ), 'end_date' => date( "d M Y", ( current_time( 'timestamp' ) ) ) ),

                'last_seven'    => array( 'start_date' => date( "d M Y", ( current_time( 'timestamp' ) - 7*24*60*60 ) ), 'end_date' => date( "d M Y", ( current_time( 'timestamp' ) ) ) ),

                'last_fifteen'  => array( 'start_date' => date( "d M Y", ( current_time( 'timestamp' ) - 15*24*60*60 ) ), 'end_date' => date( "d M Y", ( current_time( 'timestamp' ) ) ) ),

                'last_thirty'   => array( 'start_date' => date( "d M Y", ( current_time( 'timestamp' ) - 30*24*60*60 ) ), 'end_date' => date( "d M Y", ( current_time( 'timestamp' ) ) ) ),

                'last_ninety'   => array( 'start_date' => date( "d M Y", ( current_time( 'timestamp' ) - 90*24*60*60 ) ), 'end_date' => date( "d M Y", ( current_time( 'timestamp' ) ) ) ),

                'last_year_days'=> array( 'start_date' => date( "d M Y", ( current_time( 'timestamp' ) - 365*24*60*60 ) ) , 'end_date' => date( "d M Y", ( current_time( 'timestamp' ) ) ) ) );

            /**
             * Define The constants for plugin table names and other constants.
             */ 
            Woocommerce_Abandon_Cart::wcap_define_constants_for_table_and_other();
        }

        /**
         * Define The constants for plugin table names and other constants.
         * @globals mixed $wpdb
         * @since 5.0
         */
        function wcap_define_constants_for_table_and_other (){

            global $wpdb;

            if ( !defined( 'WCAP_PLUGIN_VERSION' ) ) {
                define('WCAP_PLUGIN_VERSION', "8.7.2" );
            }

            if ( !defined( 'WCAP_ABANDONED_CART_HISTORY_TABLE' ) ) {
                define('WCAP_ABANDONED_CART_HISTORY_TABLE', $wpdb->prefix . "ac_abandoned_cart_history" );
            }

            if ( !defined( 'WCAP_GUEST_CART_HISTORY_TABLE' ) ) {
                define('WCAP_GUEST_CART_HISTORY_TABLE'    , $wpdb->prefix . "ac_guest_abandoned_cart_history" );
            }

            if ( !defined( 'WCAP_EMAIL_TEMPLATE_TABLE' ) ) {
                define('WCAP_EMAIL_TEMPLATE_TABLE'        , $wpdb->prefix . "ac_email_templates" );
            }

            if ( !defined( 'WCAP_EMAIL_CLICKED_TABLE' ) ) {
                define('WCAP_EMAIL_CLICKED_TABLE'         , $wpdb->prefix . "ac_link_clicked_email" );
            }

            if ( !defined( 'WCAP_EMAIL_OPENED_TABLE' ) ) {
                define('WCAP_EMAIL_OPENED_TABLE'          , $wpdb->prefix . "ac_opened_emails" );
            }

            if ( !defined( 'WCAP_EMAIL_SENT_HISTORY_TABLE' ) ) {
                define('WCAP_EMAIL_SENT_HISTORY_TABLE'    , $wpdb->prefix . "ac_sent_history" );
            }

            if ( !defined( 'WCAP_PLUGIN_FILE' ) ) {
                define('WCAP_PLUGIN_FILE'                 , __FILE__ );
            }

            if ( !defined( 'WCAP_PLUGIN_URL' ) ) {
                define('WCAP_PLUGIN_URL'                 , untrailingslashit(plugins_url('/', __FILE__)) );
            }

            if ( !defined( 'WCAP_ADMIN_URL' ) ) {
                define('WCAP_ADMIN_URL'                   , admin_url( 'admin.php' ) );
            }

            if ( !defined( 'WCAP_ADMIN_AJAX_URL' ) ) {
                define('WCAP_ADMIN_AJAX_URL'              , admin_url( 'admin-ajax.php' ) );
            }

            if ( !defined( 'WCAP_PLUGIN_PATH' ) ) {
                define('WCAP_PLUGIN_PATH'                 , untrailingslashit(plugin_dir_path(__FILE__)) );
            }
            
            if( !defined( 'WCAP_NOTIFICATIONS' ) ) {
                define( 'WCAP_NOTIFICATIONS'    , $wpdb->prefix . "ac_notifications" );
            }
            
            
            if( !defined( 'WCAP_NOTIFICATIONS_META' ) ) {
                define( 'WCAP_NOTIFICATIONS_META'    , $wpdb->prefix . "ac_notifications_meta" );
            }
            
            if( !defined( 'WCAP_TINY_URLS' ) ) {
                define( 'WCAP_TINY_URLS'    , $wpdb->prefix . "ac_tiny_urls" );
            }
        }

        /**
         * Load all files needed for the plugin.
         * @globals string $pagenow Name of the current page
         * @since 5.0
         */
        function wcap_load_files( ) {

            /**
             * Load only admin side  files.
             */
            global $pagenow;

            require_once( "includes/wcap_activate_plugin.php" );
            require_once( "includes/wcap_update_check.php" );
            require_once( "includes/admin/wcap_edd.php" );
            require_once( "includes/wcap_load_hooks.php" );
            require_once( "includes/wcap_load_scripts.php" );
            require_once( 'includes/admin/class-wcap-orders-listing.php' );
            
            // This condition confirm that the lite plugin active, so we need to perform further action.
            if ( in_array( 'woocommerce-abandoned-cart/wpurban-ac.php', (array) get_option( 'active_plugins', array() ) ) || 
                ( isset( $_GET ['wcap_plugin_link'] ) && 'wcap-update' == $_GET ['wcap_plugin_link'] ) ) {
                require_once( "includes/admin/wcap_import_lite_to_pro.php" );
            }

            require_once( "includes/wcap_default_settings.php" );
            require_once( "includes/wcap_common.php" );
            require_once( "includes/wcap_functions.php" );
            require_once( "includes/wcap_ajax.php" );
            require_once( 'includes/wcap_process_base.php' );
            require_once( "includes/admin/wcap_actions_handler.php" );
			require_once( 'includes/class-wcap-webhooks.php' );
			
            // Database interaction file
            require_once( "includes/wcap_database_layer.php" );

            // Files for SMS Settings & Reminders
            require_once( 'includes/admin/wcap_sms_reminders.php' );
            require_once( 'includes/admin/wcap_sms_settings.php' );
            
            $is_admin = is_admin();

            if ( true === $is_admin ) {
                require_once( "includes/admin/wcap_menu.php" );

                require_once( "includes/admin/wcap_abandoned_cart_details.php" );
                // Files for Personal Data Export & Erasure
                require_once( 'includes/admin/wcap_privacy_export.php' );
                require_once( 'includes/admin/wcap_privacy_erase.php' );
                // Sent SMS List
                require_once( 'includes/admin/wcap_sent_sms_list.php' );                
            }

            if ( true === $is_admin && (
                ( isset( $_GET['page'] ) && "woocommerce_ac_page" === $_GET['page'] ) || 
                  ( isset( $_POST[ 'option_page' ] ) && 
                    ( 'woocommerce_ac_settings' === $_POST[ 'option_page' ] || 
                      'woocommerce_ac_license' === $_POST[ 'option_page' ] || 
                      'woocommerce_sms_settings' === $_POST[ 'option_page' ] ) 
                  )
                )
                ) {
                Woocommerce_Abandon_Cart::wcap_load_admin_side_files();

                /**
                 * Load class files.
                 */ 
                Woocommerce_Abandon_Cart::wcap_load_support_class_files();
            } elseif ( true === $is_admin && ( 'index.php' === $pagenow || 'admin-ajax.php' === $pagenow ) ) {
                Woocommerce_Abandon_Cart::wcap_load_dashboard_widget_files();
            }

            /**
             * Load front side files.
             */
            if( false === $is_admin ) {
                Woocommerce_Abandon_Cart::wcap_load_front_side_files();
            }

            require_once( "includes/classes/class_wcap_aes.php" );
            require_once( "includes/classes/class_wcap_aes_ctr.php" );

            require_once( "includes/admin/wcap_admin_recovery.php" );
            
            /**
             * Load cart details file.
             */ 
            $wcap_auto_cron = get_option ( 'wcap_use_auto_cron');
            if ( isset( $wcap_auto_cron ) && $wcap_auto_cron != false && '' != $wcap_auto_cron ) {
                require_once( "cron/wcap_send_email_using_cron.php" );
                require_once( "includes/fb-recovery/fb-recovery.php" );
            }

            /**
             * Load FB Messenger Files
             */
            require_once( "includes/fb-recovery/fb-recovery.php" );

            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            if ( is_plugin_active( 'woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php' ) ) {
                require_once( "includes/aelia-currency-switcher/wcap_aelia_currency_switcher.php" );
            }

            /**
             * Files needed for Components folder
             */
            require_once( 'includes/component/tracking-data/class-ts-tracker.php' );
            require_once( "includes/component/license-active-notice/ts-active-license-notice.php" );
            require_once( "includes/component/woocommerce-check/ts-woo-active.php" );

            require_once( "includes/component/tracking-data/ts-tracking.php" );
            require_once( "includes/component/deactivate-survey-popup/class-ts-deactivation.php" );

            if ( true === $is_admin ) {
                require_once( "includes/component/welcome-page/ts-welcome.php" );
            }
            require_once( "includes/component/faq-support/ts-faq-support.php" );
            require_once( "includes/wcap_all_component.php" );
        }

        /**
         * Load files which needed in the admin side.
         * @since 5.0
         */
        function wcap_load_admin_side_files(){
            
            require_once( "includes/admin/wcap_add_settings.php" );
            require_once( "includes/admin/wcap_actions.php" );
            require_once( "includes/admin/wcap_display_notices.php" );
            require_once( "includes/admin/wcap_email_settings.php" );
            require_once( "includes/admin/wcap_dashboard.php" );
            require_once( "includes/admin/wcap_dashboard_advanced.php" );
            require_once( "includes/admin/wcap_abandoned_cart_list.php" );
            require_once( "includes/admin/wcap_email_template_list.php" );
            require_once( "includes/admin/wcap_sent_email_list.php" );
            require_once( "includes/admin/wcap_email_template_fields.php" );
            require_once( "includes/admin/wcap_product_report_list.php" );
            require_once( "includes/admin/wcap_tiny_mce.php" );
            require_once( "includes/admin/wcap_localization.php" );
            require_once( "includes/admin/wcap_print_and_csv.php" );
            require_once( "includes/admin/wcap_add_cart_popup_modal.php" );
        }

        /**
         * Load files needed for the front end.
         * @since 5.0
         */
        function wcap_load_front_side_files() {
            require_once( "includes/frontend/wcap_cart_updated.php" );
            require_once( "includes/frontend/wcap_order_received.php" );
            require_once( "includes/frontend/wcap_populate_cart_of_user.php" );
            require_once( "includes/frontend/wcap_on_placed_order.php" );
            require_once( "includes/frontend/wcap_coupon_code.php" );
            require_once( 'includes/frontend/class-wcap-tracking-msg.php' );
            require_once( 'includes/wcap_tiny_url.php' );
        }

        /**
         * Load the supporting class files.
         * @since 5.0
         */
        function wcap_load_support_class_files(){
            
            require_once( "includes/classes/class_wcap_manual_email.php" );
            require_once( "includes/classes/class_wcap_send_manual_email.php" );
            require_once( "includes/classes/class_wcap_dashboard_report_action.php" );
            require_once( "includes/classes/class_wcap_dashboard_adv_report_action.php" );
            require_once( "includes/classes/class_wcap_abandoned_orders_table.php" );
            require_once( "includes/classes/class_wcap_abandoned_trash_orders_table.php" );
            require_once( "includes/classes/class_wcap_templates_table.php" );
            require_once( "includes/classes/class_wcap_sent_emails_table.php" );
            
            require_once( "includes/classes/class_wcap_product_report_table.php" );
            require_once( "includes/classes/class_wcap_atc_dashboard.php" );
            require_once( 'includes/classes/class_wcap_sms_templates_table.php' );
            require_once( 'includes/classes/class_wcap_sent_sms_table.php' );
        }

        /**
         * Loads the dashboard widget files.
         * @since 5.0
         */
        function wcap_load_dashboard_widget_files() {
            require_once( "includes/admin/wcap_dashboard_widget.php" );
            require_once( "includes/classes/class_wcap_dashboard_widget_report.php" );
            require_once( "includes/classes/class_wcap_dashboard_widget_heartbeat.php" );
        }

        /**
         * It will load all the hooks needed for the plugin.
         * @since 5.0
         */
        function wcap_load_hooks (){
            Wcap_Load_Hooks::wcap_load_hooks_and_filters();
        }
    }
}
$woocommerce_abandon_cart = new Woocommerce_Abandon_Cart();
?>
