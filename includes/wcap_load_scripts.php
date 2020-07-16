<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * This files will load the JavaScript files at front end for Add To Cart Popup Modal and it will also load scripts for migrating the data of LITE version to PRO version at backend.
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @category Classes
 * @since    5.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if ( !class_exists('Wcap_Load_Scripts' ) ) {
    /**
     * Load Scripts needed for Plugin.
     * 
     * @since  5.0
     */
    class Wcap_Load_Scripts {
        /** 
         * Enqueue Common JS Scripts to be included in Admin Side.
         *
         * @hook admin_enqueue_scripts
         * 
         * @param string $hook Hook suffix for the current admin page
         * @globals $pagenow Current page 
         * @since 5.0
         */
        public static function wcap_enqueue_scripts_js( $hook ) {
            global $pagenow, $woocommerce;

            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

            $wcap_is_import_page_displayed = get_option( 'wcap_import_page_displayed' );
            $wcap_is_lite_data_imported    = get_option( 'wcap_lite_data_imported' );

            $page = isset( $_GET['page'] ) ? $_GET['page'] : '';

            if ( 'yes' == $wcap_is_import_page_displayed && false === $wcap_is_lite_data_imported ) {
                if ( 'plugins.php' ==  $hook ) {
                    wp_enqueue_script( 'wcap_import_lite_data', WCAP_PLUGIN_URL . '/assets/js/admin/wcap_import_lite_data.js' );
                }
            }
            //plugins.php
            if ( 'dashboard_page_wcap-update' ==  $hook ) {
                wp_enqueue_script( 'wcap_import_lite_data',     WCAP_PLUGIN_URL . '/assets/js/admin/wcap_import_lite_data.js' );
            }

            if( 'index.php' == $pagenow ) {
                wp_enqueue_script( 'wcap_dashboard_widget',     WCAP_PLUGIN_URL . '/assets/js/admin/wcap_dashboard_widget' . $suffix . '.js' );
            }
            if ( $page === '' || $page !== 'woocommerce_ac_page' ) {
                return;
            } else {
                wp_enqueue_script( 'jquery' );
                wp_enqueue_script(
                    'jquery-ui-min',
                    WCAP_PLUGIN_URL . '/assets/js/jquery-ui.min.js',
                    '',
                    '',
                    false
                );
                // scripts included for woocommerce auto-complete coupons
                wp_register_script( 'woocommerce_admin',     plugins_url() . '/woocommerce/assets/js/admin/woocommerce_admin.js', array( 'jquery', 'jquery-ui-widget', 'jquery-ui-core' ) );
                wp_register_script( 'jquery-ui-datepicker',  plugins_url() . '/woocommerce/assets/js/admin/ui-datepicker.js' );
                
                wp_register_script( 'enhanced' ,             plugins_url() . '/woocommerce/assets/js/admin/wc-enhanced-select.js', array( 'jquery', 'select2' ) );
                wp_enqueue_script( 'accounting' );
                wp_enqueue_script( 'woocommerce_metaboxes' );
                wp_enqueue_script( 'jquery-ui-datepicker' );

                wp_register_script( 'flot',                  WCAP_PLUGIN_URL . '/assets/js/jquery-flot/jquery.flot.min.js',        array( 'jquery' ) );
                wp_register_script( 'flot-resize',           WCAP_PLUGIN_URL . '/assets/js/jquery-flot/jquery.flot.resize.min.js', array( 'jquery', 'flot' ) );
                wp_register_script( 'flot-time',             WCAP_PLUGIN_URL . '/assets/js/jquery-flot/jquery.flot.time.min.js',   array( 'jquery', 'flot' ) );
                wp_register_script( 'flot-pie',              WCAP_PLUGIN_URL . '/assets/js/jquery-flot/jquery.flot.pie.min.js',    array( 'jquery', 'flot' ) );
                wp_register_script( 'flot-stack',            WCAP_PLUGIN_URL . '/assets/js/jquery-flot/jquery.flot.stack.min.js',  array( 'jquery', 'flot' ) );
                wp_register_script( 'wcap-dashboard-report', WCAP_PLUGIN_URL . '/assets/js/admin/wcap_reports.min.js',             array( 'jquery' ) );
                wp_enqueue_script( 'flot' );
                wp_enqueue_script( 'flot-resize' );
                wp_enqueue_script( 'flot-time' );
                wp_enqueue_script( 'flot-pie' );
                wp_enqueue_script( 'flot-stack' );
                wp_enqueue_script( 'wcap-dashboard-report' );

                wp_enqueue_script ( 
                        'bootstrap_js', 
                        WCAP_PLUGIN_URL . '/assets/js/admin/bootstrap.min.js', 
                        '', 
                        '', 
                        false );

                wp_enqueue_script ( 
                        'd3_js', 
                        WCAP_PLUGIN_URL . '/assets/js/admin/d3.v3.min.js', 
                        '', 
                        '', 
                        false );

                wp_register_script ( 
                        'wcap_graph_js', 
                        WCAP_PLUGIN_URL . '/assets/js/admin/wcap_adv_dashboard' . $suffix . '.js', 
                        '', 
                        '', 
                        true );
                /*
                 * It is used for the Search coupon new functionality.
                 * Since: 3.3
                 */
                wp_localize_script( 'enhanced', 'wc_enhanced_select_params', array(
                                    'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'woocommerce' ),
                                    'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'woocommerce' ),
                                    'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
                                    'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
                                    'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
                                    'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
                                    'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
                                    'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
                                    'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
                                    'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
                                    'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
                                    'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
                                    'ajax_url'                  => WCAP_ADMIN_AJAX_URL,
                                    'search_products_nonce'     => wp_create_nonce( 'search-products' ),
                                    'search_customers_nonce'    => wp_create_nonce( 'search-customers' )
                ) );

                $wc_round_value         = wc_get_price_decimals();
                $wc_currency_position   = get_option( 'woocommerce_currency_pos' );
                
                wp_localize_script( 'wcap-dashboard-report', 'wcap_dashboard_report_params', array(
                                    'currency_symbol'               =>  get_woocommerce_currency_symbol(),
                                    'wc_round_value'                => $wc_round_value,
                                    'wc_currency_position'          => $wc_currency_position,
                                    'currency_format_decimal_sep'   => esc_attr( wc_get_price_decimal_separator() ),
                                    'currency_format_thousand_sep'  => esc_attr( wc_get_price_thousand_separator() ),
                                    'currency_format'               => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) )
                ) );

                wp_enqueue_script( 'enhanced' );
                wp_enqueue_script( 'woocommerce_admin' );
                wp_enqueue_script( 'jquery-ui-sortable' );

                $woocommerce_admin_meta_boxes = array(
                        'search_products_nonce' => wp_create_nonce( "search-products" ),
                        'plugin_url'            => plugins_url(),
                        'ajax_url'              => WCAP_ADMIN_AJAX_URL
                );
                wp_localize_script( 'woocommerce_metaboxes', 'woocommerce_admin_meta_boxes', $woocommerce_admin_meta_boxes );
                wp_dequeue_script( 'wc-enhanced-select' );

                if ( version_compare( $woocommerce->version, '3.2.0', ">=" ) ) {

                    wp_register_script( 'selectWoo' ,             plugins_url() . '/woocommerce/assets/js/selectWoo/selectWoo.full.min.js', array( 'jquery' ) );
                    wp_enqueue_script( 'selectWoo' );
                }

                wp_register_script( 'woocommerce_admin', plugins_url() . '/woocommerce/assets/js/admin/woocommerce_admin.min.js', array( 'jquery', 'jquery-tiptip' ), '', true );
                wp_register_script( 'woocommerce_tip_tap', plugins_url() . '/woocommerce/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery'), '', true );
                wp_enqueue_script( 'woocommerce_tip_tap');
                wp_enqueue_script( 'woocommerce_admin');

                wp_register_script( 'select2', plugins_url() . '/woocommerce/assets/js/select2/select2.min.js', array( 'jquery', 'jquery-ui-widget', 'jquery-ui-core' ) );
                wp_enqueue_script( 'select2' );

                $js_src = includes_url( 'js/tinymce/' ) . 'tinymce.min.js';
                wp_enqueue_script( 'tinyMCE_ac', $js_src );
                /*
                 *   When Bulk action is selected without any proper action then this file will be called
                 */

                $action = $action_down = '' ;
                if ( isset( $_GET[ 'action'] ) ) {
                    $action = $_GET['action'];
                }

                if ( isset( $_GET[ 'action2'] ) ) {
                    $action_down = $_GET['action2'];
                }

                if ( "-1" == $action && isset( $_GET['wcap_action'] ) ) {
                    $action    = $_GET['wcap_action'];
                }
                $section = ( isset( $_GET[ 'section' ] ) ) ? $_GET[ 'section' ] : '';
                if ( 'emailsettings' ==  $action ) {
                    wp_enqueue_script( 'wcap_guest_setting',        WCAP_PLUGIN_URL . '/assets/js/admin/wcap_guest_settings' . $suffix . '.js' );
                    wp_enqueue_style( 'wp-color-picker' );
                    wp_enqueue_script( 'iris' );
                    wp_enqueue_script ( 'wcap_vue_js', WCAP_PLUGIN_URL . '/assets/js/vue.min.js' );

                    /* Admin side model script for popup modal preview
                        @since: 6.0
                    */
                    wp_enqueue_script( 'wcap_enable_atc_modal',  WCAP_PLUGIN_URL . '/assets/js/admin/wcap_enable_atc_modal' . $suffix . '.js' );
                    wp_enqueue_script( 'wcap_mandatory_email_field',  WCAP_PLUGIN_URL . '/assets/js/admin/wcap_mandatory_atc_email' . $suffix . '.js' );
                    wp_enqueue_script( 'wcap_atc_reset_field',  WCAP_PLUGIN_URL . '/assets/js/admin/wcap_atc_reset_setting' . $suffix . '.js' );
                }

                /** Advance Settings **/
                $wcap_section = isset( $_GET[ 'wcap_section' ] ) ? $_GET[ 'wcap_section' ] : '';
                if( 'emailsettings' == $action && 'wcap_sms_settings' == $wcap_section ) {
                    wp_register_script( 'wcap_sms_settings', WCAP_PLUGIN_URL . '/assets/js/admin/wcap_sms_settings.js' );
                    wp_localize_script(
                                'wcap_sms_settings',
                                'wcap_advance',
                                array(
                                        'ajax_url' => WCAP_ADMIN_AJAX_URL
                                )
                    );
                    wp_enqueue_script( 'wcap_sms_settings' );
                
                }
                if ( 'cart_recovery' == $action || 'emailtemplates' ==  $section || 'emailtemplates&mode=wcap_manual_email' == $action || 'emailtemplates&mode=wcap_manual_email' == $action_down ){

                    wp_enqueue_script ( 
                        'wcap_vue_js', 
                        WCAP_PLUGIN_URL . '/assets/js/vue.min.js', 
                        '', 
                        '', 
                        false );
                    wp_enqueue_script ( 
                        'wcap_resource_js', 
                        WCAP_PLUGIN_URL . '/assets/js/admin/vue_resource.min.js', 
                        '', 
                        '', 
                        false );
                    wp_enqueue_script ( 
                        'popper_js', 
                        WCAP_PLUGIN_URL . '/assets/js/admin/popper.min.js', 
                        '', 
                        '', 
                        false );
                    wp_enqueue_script ( 
                        'bootstrap_js', 
                        WCAP_PLUGIN_URL . '/assets/js/admin/bootstrap.min.js', 
                        '', 
                        '', 
                        false );

                    wp_enqueue_script( 'wcap_template_preview',   WCAP_PLUGIN_URL . '/assets/js/admin/wcap_template_preview.js', '', '', true );

                    $template_localized_params = Wcap_Load_Scripts::wcap_template_params();
                    wp_localize_script( 
                        'wcap_template_preview', 
                        'wcap_template_params',
                        $template_localized_params
                    );

                    $recovery_section = ( isset( $_GET[ 'section' ] ) ) ? $_GET[ 'section' ] : 'emailtemplates';
                    wp_enqueue_script( 'wcap_template_activate',  WCAP_PLUGIN_URL . '/assets/js/admin/wcap_template_activate' . $suffix . '.js' );
                    wp_localize_script( 'wcap_template_activate', 
                                        'wcap_activate_params', 
                                        array( 'template_type' => $recovery_section ) 
                    );
                     
                    wp_enqueue_script( 'ac_email_variables',      WCAP_PLUGIN_URL . '/assets/js/admin/abandoncart_plugin_button' . $suffix . '.js' );
                    wp_enqueue_script( 'ac_email_button_css',     WCAP_PLUGIN_URL . '/assets/js/admin/abandoncart_plugin_button_css' . $suffix . '.js' );

                    wp_enqueue_script( 'wcap_maual_email',        WCAP_PLUGIN_URL . '/assets/js/admin/wcap_manual_email' . $suffix . '.js' );
                    wp_enqueue_script( 'wcap_preview_email',      WCAP_PLUGIN_URL . '/assets/js/admin/wcap_preview_email' . $suffix . '.js' );

                    wp_localize_script( 'wcap_preview_email', 'wcap_preview_email_params', array(
                                      'wcap_email_sent_image_path'  =>  WCAP_PLUGIN_URL . "/assets/images/wcap_email_sent.svg"
                    ) );
                    wp_enqueue_script( 'wcap_template_for_customer_email',      WCAP_PLUGIN_URL . '/assets/js/admin/wcap_template_for_customer_email' . $suffix . '.js' );
                    wp_register_script( 'woocommerce_admin', plugins_url() . '/woocommerce/assets/js/admin/woocommerce_admin.min.js', array( 'jquery', 'jquery-tiptip' ) );
                    wp_register_script( 'woocommerce_tip_tap', plugins_url() . '/woocommerce/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery') );
                    wp_enqueue_script( 'woocommerce_tip_tap');
                    wp_enqueue_script( 'woocommerce_admin');

                    $locale  = localeconv();
                    $decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';         
                    $params  = array(
                        /* translators: %s: decimal */
                        'i18n_decimal_error'                => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'woocommerce' ), $decimal ),
                        /* translators: %s: price decimal separator */
                        'i18n_mon_decimal_error'            => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'woocommerce' ), wc_get_price_decimal_separator() ),
                        'i18n_country_iso_error'            => __( 'Please enter in country code with two capital letters.', 'woocommerce' ),
                        'i18_sale_less_than_regular_error'  => __( 'Please enter in a value less than the regular price.', 'woocommerce' ),
                        'decimal_point'                     => $decimal,
                        'mon_decimal_point'                 => wc_get_price_decimal_separator(),
                        'strings' => array(
                            'import_products' => __( 'Import', 'woocommerce' ),
                            'export_products' => __( 'Export', 'woocommerce' ),
                        ),
                        'urls' => array(
                            'import_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer' ) ),
                            'export_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ),
                        ),
                    );
                    /**
                     * If we dont localize this script then from the WooCommerce check it will not run the javascript further and tooltip wont show any data.
                     * Also, we need above all parameters for the WooCoomerce js file. So we have taken it from the WooCommerce.
                     * @since: 7.7
                     */
                    wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );
                }
              
                if( 'cart_recovery' == $action && 'sms' == $section ) {
                
                    wp_register_script( 'wcap_sms_list',            WCAP_PLUGIN_URL . '/assets/js/admin/wcap_sms_template_list' . $suffix . '.js' );
                    wp_localize_script( 'wcap_sms_list',
                                'wcap_sms_params',
                                array(
                                        'ajax_url' => WCAP_ADMIN_AJAX_URL,
                                ));
                    wp_enqueue_script( 'wcap_sms_list' );
                }
                
                if ( 'listcart' == $action || 'emailstats' == $action ){
                    wp_enqueue_script( 'wcap_bulk_action',            WCAP_PLUGIN_URL . '/assets/js/admin/wcap_abandoned_order_bulk_action' . $suffix . '.js' );
                    wp_enqueue_script( 'wcap_abandoned_cart_details', WCAP_PLUGIN_URL . '/assets/js/admin/wcap_abandoned_cart_detail_modal' . $suffix . '.js' );
                }

                if ( 'stats' == $action || 'emailstats' == $action || 'listcart' == $action || 'cart_recovery' === $action ) {
                    wp_enqueue_script( 'wcap_date_filter',    WCAP_PLUGIN_URL . '/assets/js/admin/wcap_date_select_filter' . $suffix . '.js' );
                }

                if ( isset( $_GET['page'] ) && $_GET['page'] == "woocommerce_ac_page"  && "cart_recovery" == $action && "emailtemplates" == $section ) {
                    wp_enqueue_script( 'wcap_test_email',    WCAP_PLUGIN_URL . '/assets/js/admin/wcap_test_email' . $suffix . '.js' );

                    wp_localize_script( 'wcap_test_email', 'wcap_test_email_params', array(
                                      'wcap_test_email_sent_image_path'  =>  WCAP_PLUGIN_URL . "/assets/images/check.jpg"
                    ) );
                }

                wp_enqueue_script( 'wcap_dismiss_notice', WCAP_PLUGIN_URL . '/assets/js/admin/wcap_admin_notices.js' );
                wp_localize_script( 'wcap_dismiss_notice', 'wcap_dismiss_params', array( 
                        'ajax_url' => WCAP_ADMIN_AJAX_URL
                    )
                );
            }
        }

        /** 
         * Enqueue JS Scripts at front end for capturing the cart from checkout page.
         *
         * @hook woocommerce_after_checkout_billing_form
         * 
         * @since 5.0
         */
        public static function wcap_include_js_for_guest () {

            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

            $guest_cart = get_option( 'ac_disable_guest_cart_email' );

            // check if the script needs to be loaded on the cart page
            $load_cart = 'on' === get_option( 'wcap_countdown_cart', '' ) ? true : false;
            $cart_condition = $load_cart ? 'is_cart()' : '';
            
            if ( ( $cart_condition || is_checkout() ) && $guest_cart != "on" &&  !is_user_logged_in() ){
                wp_enqueue_script(  'wcap_capture_guest_user' , WCAP_PLUGIN_URL . '/assets/js/frontend/wcap_guest_user' . $suffix . '.js' );
                $guest_msg = get_option( 'wcap_guest_cart_capture_msg' );

                $session_gdpr = wcap_get_cart_session( 'wcap_cart_tracking_refused' );
                $show_gdpr = isset( $session_gdpr ) && 'yes' == $session_gdpr ? false : true;
        
                $vars = array();       
                if ( isset( $guest_msg ) && '' != $guest_msg ) {
                    $vars = array( 
                    '_show_gdpr_message'        => $show_gdpr,
                    '_gdpr_message'             => htmlspecialchars( get_option( 'wcap_guest_cart_capture_msg' ), ENT_QUOTES ),
                    '_gdpr_nothanks_msg'        => htmlspecialchars( get_option( 'wcap_gdpr_allow_opt_out'), ENT_QUOTES ),
                    '_gdpr_after_no_thanks_msg' => htmlspecialchars( get_option( 'wcap_gdpr_opt_out_message' ), ENT_QUOTES ),
                    'enable_ca_tracking'        => true,
                    );
                }

                $vars[ 'ajax_url' ] = WCAP_ADMIN_AJAX_URL;

                wp_localize_script( 'wcap_capture_guest_user', 'wcap_capture_guest_user_params', $vars );
            }
        }

        public static function wcap_include_js_atc_coupon() {

            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

            $guest_cart = get_option( 'ac_disable_guest_cart_email' );

            // check if the script needs to be loaded on the cart page
            $load_cart = 'on' === get_option( 'wcap_countdown_cart', '' ) ? true : false;
            $cart_condition = $load_cart ? 'is_cart()' : '';
            
            if ( ( $cart_condition || is_checkout() ) && $guest_cart != "on" &&  !is_user_logged_in() ){
                wp_enqueue_script(  'wcap_atc_coupon_countdown' , WCAP_PLUGIN_URL . '/assets/js/frontend/wcap_atc_coupon_countdown' . $suffix . '.js' );
                
                $vars = array();       
                
                $vars[ 'ajax_url' ] = WCAP_ADMIN_AJAX_URL;

                // ATC Coupons auto applied.                
                $atc_coupon_applied = 'on' === get_option( 'wcap_atc_auto_apply_coupon_enabled', '' ) ? true : false;
                $vars['_wcap_coupons_atc'] = $atc_coupon_applied;

                // Coupon, validity & expiry message is setup?
                if( 'on' === get_option( 'wcap_atc_auto_apply_coupon_enabled', '' ) && '' !== get_option( 'wcap_countdown_timer_msg', 'Coupon <coupon_code> expires in <hh:mm:ss>. Avail it now.' ) && 0 < get_option( 'wcap_atc_popup_coupon_validity', 0 ) ) {
                    $abandoned_id = wcap_get_cart_session( 'wcap_abandoned_id' );
                    $coupon_details = get_post_meta( $abandoned_id, '_woocommerce_ac_coupon', true );
                    $coupon_expiry  = isset( $coupon_details['time_expires'] ) ? $coupon_details['time_expires'] : '';
                    if ( '' !== $coupon_expiry ) {
                        $coupon_expiry_date = date( 'Y-m-d, H:i:s', $coupon_expiry );
                        $display_msg        = get_option( 'wcap_countdown_timer_msg', 'Coupon <coupon_code> expires in <hh:mm:ss>. Avail it now.' );
                        
                        $vars['_wcap_coupon_msg']     = __( $display_msg, 'woocommerce-ac' );
                        $vars['_wcap_coupon_expires'] = $coupon_expiry_date;
                        $vars['_wcap_expiry_msg']     = __( get_option( 'wcap_countdown_msg_expired', 'The offer is no longer valid.' ), 'woocommerce-ac' );
                        $vars['_wcap_server_offset']  = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
                    }
                } 
                wp_localize_script( 'wcap_atc_coupon_countdown', 'wcap_atc_coupon_countdown_params', $vars );
            }
        }
        /**
         * It will dequeue front end script for the Add To Cart Popup Modal on shop page.
         *
         * @hook plugins_loaded
         *
         * @since 8.0
         */
        public static function wcap_dequeue_scripts_atc_modal() {

            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

            wp_dequeue_script('wc-add-to-cart');

            wp_register_script( 
                'wc-add-to-cart', 
                WCAP_PLUGIN_URL . '/assets/js/frontend/wcap_atc_modal' . $suffix . '.js', 
                '', 
                '', 
                true );
            wp_enqueue_script( 'wc-add-to-cart' );
        }

        /**
         * It will load all the front end scripts for the Add To Cart Popup Modal.
         *
         * @hook wp_enqueue_scripts
         *
         * @globals WP_Post $post
         * @since 6.0
         */
        public static function wcap_enqueue_scripts_atc_modal () {

            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            
            if ( wcap_get_cart_session( 'wcap_populate_email' ) != '' &&  'on' != get_option ('wcap_atc_enable_modal') ) {
                $wcap_get_url_email_address = wcap_get_cart_session( 'wcap_populate_email' );
                $wcap_is_atc_enabled = get_option ('wcap_atc_enable_modal');
                
                wp_enqueue_script( 'jquery' );
                wp_enqueue_script(
                    'jquery-ui-min',
                    WCAP_PLUGIN_URL . '/assets/js/jquery-ui.min.js',
                    '',
                    '',
                    false
                );
                wp_register_script( 'wcap-capture-url-email' , WCAP_PLUGIN_URL . '/assets/js/frontend/wcap_capture_url_email' . $suffix . '.js' );
                wp_enqueue_script ( 'wcap-capture-url-email' );
                wp_localize_script( 'wcap-capture-url-email', 'wcap_capture_url_email_param', array(
                              'wcap_ajax_add' => get_option('woocommerce_enable_ajax_add_to_cart'),
                              'wcap_populate_email' => $wcap_get_url_email_address,
                              'wcap_ajax_url' => WCAP_ADMIN_URL,
                              'wc_ajax_url' => WC_AJAX::get_endpoint( "%%endpoint%%" ),
                              'wcap_is_atc_enabled' => $wcap_is_atc_enabled
                ) );
            }
            
            if ( 'on' == get_option ('wcap_atc_enable_modal') && wcap_get_cart_session( 'wcap_email_sent_id' ) == '' ) {
                global $post;

                if ( !is_user_logged_in() ) {
                    $wcap_atc_modal = '';
                    if ( !is_cart() && !is_checkout() ) {
                        ob_start();
                        include( WCAP_PLUGIN_PATH . '/includes/template/add_to_cart/wcap_add_to_cart.php' );
                        $wcap_atc_modal = ob_get_clean();
                        wp_enqueue_script(
                            'wcap_vue_js',
                            WCAP_PLUGIN_URL . '/assets/js/vue.min.js',
                            '',
                            '',
                            true
                        );
                    }

                    $wcap_atc_modal = apply_filters( 'wcap_add_custom_atc_template', $wcap_atc_modal );

                    $page_id = get_the_ID();
                    $custom_pages = is_array( get_option( 'wcap_custom_pages_list' ) ) && count( get_option( 'wcap_custom_pages_list' ) ) > 0 ? get_option( 'wcap_custom_pages_list' ) : array();
                    
                    $atc_coupon_code = '';
                    if( 'on' === get_option( 'wcap_atc_auto_apply_coupon_enabled', '' ) && 'pre-selected' === get_option( 'wcap_atc_coupon_type', '' ) && 0 < get_option( 'wcap_atc_popup_coupon', 0 ) ) {
                        $atc_coupon_code = get_the_title( get_option( 'wcap_atc_popup_coupon', 0 ) );
                    }
                    if ( ( is_shop() || is_home() || is_product_category() || is_front_page() || ( function_exists( 'is_demo' ) && is_demo() ) || in_array( $page_id, $custom_pages ) ) && 
                    apply_filters( 'wcap_enable_pages_popup_modal', true ) ) {
                        wp_dequeue_script('wc-add-to-cart');
                        wp_deregister_script('wc-add-to-cart');
                        wp_enqueue_script( 'jquery' );
                        wp_enqueue_script(
                            'jquery-ui-min',
                            WCAP_PLUGIN_URL . '/assets/js/jquery-ui.min.js',
                            '',
                            '',
                            false
                        );
                        wp_register_script( 'wc-add-to-cart' , WCAP_PLUGIN_URL . '/assets/js/frontend/wcap_atc_modal' . $suffix . '.js', '', '', true );
                        wp_enqueue_script ( 'wc-add-to-cart' );

                        $wcap_populate_email_address = NULL !== wcap_get_cart_session( 'wcap_populate_email' ) && '' != wcap_get_cart_session( 'wcap_populate_email' ) ? wcap_get_cart_session( 'wcap_populate_email' ) : '';
                        wp_localize_script( 'wc-add-to-cart', 'wcap_atc_modal_param', array(
                                      'wcap_atc_modal_data'  =>  $wcap_atc_modal,
                                      'wcap_atc_head'  => __( get_option('wcap_heading_section_text_email'), 'woocommerce-ac' ),
                                      'wcap_atc_text'  => __( get_option('wcap_text_section_text'), 'woocommerce-ac' ),
                                      'wcap_atc_email_place'  => __( get_option('wcap_email_placeholder_section_input_text'), 'woocommerce-ac' ),
                                      'wcap_atc_button'  => __( get_option('wcap_button_section_input_text'), 'woocommerce-ac' ),
                                      'wcap_atc_button_bg_color'  => get_option('wcap_button_color_picker'),
                                      'wcap_atc_button_text_color'=> get_option('wcap_button_text_color_picker'),
                                      'wcap_atc_popup_text_color' => get_option('wcap_popup_text_color_picker'),
                                      'wcap_atc_popup_heading_color'      => get_option('wcap_popup_heading_color_picker'),
                                      'wcap_atc_non_mandatory_input_text' => __( get_option('wcap_non_mandatory_text'), 'woocommerce-ac' ),
                                      'wcap_atc_mandatory_email' => get_option( 'wcap_atc_mandatory_email' ),
                                      'wcap_ajax_add' => get_option('woocommerce_enable_ajax_add_to_cart'),
                                      'wcap_populate_email' => $wcap_populate_email_address,
                                      'wcap_ajax_url' => WCAP_ADMIN_URL,
                                      'wcap_mandatory_text' => __('Email address is mandatory for adding product to the cart.', 'woocommerce-ac'),
                                      'wcap_mandatory_email_text' => __(' Please enter a valid email address.', 'woocommerce-ac'),
                                      'wcap_atc_coupon_applied_msg' => __( "Thank you. Coupon $atc_coupon_code will be auto-applied to your cart.", 'woocommerce-ac' ),
                        ) );
                    }
                    $wcap_populate_email_address = NULL !== wcap_get_cart_session( 'wcap_populate_email' ) && '' != wcap_get_cart_session( 'wcap_populate_email' ) ? wcap_get_cart_session( 'wcap_populate_email' ) : '';
                    if ( is_product() || ( function_exists( 'is_producto' ) && is_producto() ) ) {
                        $wcap_product = wc_get_product( $post->ID );
                    
                        if( $wcap_product->is_type( 'simple' ) || $wcap_product->is_type( 'course' ) || $wcap_product->is_type( 'subscription' ) || $wcap_product->is_type( 'composite' ) || $wcap_product->is_type( 'booking' ) || $wcap_product->is_type( 'appointment' ) || $wcap_product->is_type( 'bundle' ) ) {
                            wp_dequeue_script( 'astra-single-product-ajax-cart' );
                            wp_dequeue_script( 'wc-add-to-cart' );
                            wp_deregister_script( 'wc-add-to-cart' );
                            wp_enqueue_script( 'jquery' );
                            wp_register_script( 'wcap_atc_single_simple_product' , WCAP_PLUGIN_URL . '/assets/js/frontend/wcap_atc_simple_single_page' . $suffix . '.js' );
                            wp_enqueue_script ( 'wcap_atc_single_simple_product');

                            wp_localize_script( 'wcap_atc_single_simple_product', 'wcap_atc_modal_param', array(
                                      'wcap_atc_modal_data'  =>  $wcap_atc_modal,
                                      'wcap_atc_head'  => __( get_option('wcap_heading_section_text_email'), 'woocommerce-ac' ),
                                      'wcap_atc_text'  => __( get_option('wcap_text_section_text'), 'woocommerce-ac' ),
                                      'wcap_atc_email_place'  => __( get_option('wcap_email_placeholder_section_input_text'), 'woocommerce-ac' ),
                                      'wcap_atc_button'  => __( get_option('wcap_button_section_input_text'), 'woocommerce-ac' ),
                                      'wcap_atc_button_bg_color'  => get_option('wcap_button_color_picker'),
                                      'wcap_atc_button_text_color'=> get_option('wcap_button_text_color_picker'),
                                      'wcap_atc_popup_text_color' => get_option('wcap_popup_text_color_picker'),
                                      'wcap_atc_popup_heading_color' => get_option('wcap_popup_heading_color_picker'),
                                      'wcap_atc_non_mandatory_input_text' => __( get_option('wcap_non_mandatory_text'), 'woocommerce-ac' ),
                                      'wcap_atc_mandatory_email' => get_option( 'wcap_atc_mandatory_email' ),
                                      'wcap_ajax_add' => get_option('woocommerce_enable_ajax_add_to_cart'),
                                      'wcap_populate_email' => $wcap_populate_email_address,
                                      'wcap_ajax_url' => WCAP_ADMIN_URL,
                                      'wcap_mandatory_text' => __('Email address is mandatory for adding product to the cart.', 'woocommerce-ac'),
                                      'wcap_mandatory_email_text' => __(' Please enter a valid email address.', 'woocommerce-ac'),
                                      'wcap_atc_coupon_applied_msg' => __( "Thank you. Coupon $atc_coupon_code will be auto-applied to your cart.", 'woocommerce-ac' ),
                            ) );
                        }else if( $wcap_product->is_type( 'variable' ) || $wcap_product->is_type('variable-subscription') ) {
                            // Variable Product
                            if ( 'entrada' == get_option( 'template' ) ) {
                                wp_register_script( 'wcap_entrada_atc_variable_page', WCAP_PLUGIN_URL . '/assets/js/themes/wcap_entrada_atc_variable_page' . $suffix . '.js', array( 'jquery' , 'wp-util' ) );
                                wp_enqueue_script ( 'wcap_entrada_atc_variable_page' );

                                wp_localize_script( 'wcap_entrada_atc_variable_page', 'wcap_atc_modal_param',            array(
                                                    'wcap_atc_modal_data'  =>  $wcap_atc_modal,
                                                    'wcap_atc_head'  => __( get_option('wcap_heading_section_text_email'), 'woocommerce-ac' ),
                                                    'wcap_atc_text'  => __( get_option('wcap_text_section_text'), 'woocommerce-ac' ),
                                                    'wcap_atc_email_place'  => __( get_option('wcap_email_placeholder_section_input_text'), 'woocommerce-ac' ),
                                                    'wcap_atc_button'  => __( get_option('wcap_button_section_input_text'), 'woocommerce-ac' ),
                                                    'wcap_atc_button_bg_color'  => get_option('wcap_button_color_picker'),
                                                    'wcap_atc_button_text_color'=> get_option('wcap_button_text_color_picker'),
                                                    'wcap_atc_popup_text_color' => get_option('wcap_popup_text_color_picker'),
                                                    'wcap_atc_popup_heading_color' => get_option('wcap_popup_heading_color_picker'),
                                                    'wcap_atc_mandatory_email' => get_option( 'wcap_atc_mandatory_email' ),
                                                    'wcap_populate_email' => $wcap_populate_email_address,
                                                    'wcap_atc_non_mandatory_input_text' => __( get_option('wcap_non_mandatory_text'), 'woocommerce-ac' ),
                                                    'wcap_mandatory_text' => __('Email address is mandatory for adding product to the cart.', 'woocommerce-ac'),
                                                    'wcap_mandatory_email_text' => __(' Please enter a valid email address.', 'woocommerce-ac'),
                                                    'wcap_atc_coupon_applied_msg' => __( "Thank you. Coupon $atc_coupon_code will be auto-applied to your cart.", 'woocommerce-ac' ),
                                ) );
                            } elseif ( is_plugin_active( 'woo-variations-table-grid/woo-variations-table.php' ) && !get_option('vartable_disabled') && 
                                ( get_post_meta($wcap_product->get_id(), 'disable_variations_table', true) == '' || get_post_meta($wcap_product->get_id(), 'disable_variations_table', true) != 1 ) ) {

                                wp_dequeue_script( 'wc-add-to-cart-variation' );
                                wp_deregister_script( 'wc-add-to-cart-variation' );

                                wp_register_script( 'wc-add-to-cart-variation' , WCAP_PLUGIN_URL . '/assets/js/frontend/wcap_atc_modal' . $suffix . '.js', '', '', true );
                                wp_enqueue_script ( 'wc-add-to-cart-variation' );

                                $wcap_populate_email_address = NULL !== wcap_get_cart_session( 'wcap_populate_email' ) && '' != wcap_get_cart_session( 'wcap_populate_email' ) ? wcap_get_cart_session( 'wcap_populate_email' ) : '';
                                wp_localize_script( 'wc-add-to-cart-variation', 'wcap_atc_modal_param', array(
                                            'wcap_atc_modal_data'  =>  $wcap_atc_modal,
                                            'wcap_atc_head'  => __( get_option('wcap_heading_section_text_email'), 'woocommerce-ac' ),
                                            'wcap_atc_text'  => __( get_option('wcap_text_section_text'), 'woocommerce-ac' ),
                                            'wcap_atc_email_place'  => __( get_option('wcap_email_placeholder_section_input_text'), 'woocommerce-ac' ),
                                            'wcap_atc_button'  => __( get_option('wcap_button_section_input_text'), 'woocommerce-ac' ),
                                            'wcap_atc_button_bg_color'  => get_option('wcap_button_color_picker'),
                                            'wcap_atc_button_text_color'=> get_option('wcap_button_text_color_picker'),
                                            'wcap_atc_popup_text_color' => get_option('wcap_popup_text_color_picker'),
                                            'wcap_atc_popup_heading_color'      => get_option('wcap_popup_heading_color_picker'),
                                            'wcap_atc_non_mandatory_input_text' => __( get_option('wcap_non_mandatory_text'), 'woocommerce-ac' ),
                                            'wcap_atc_mandatory_email' => get_option( 'wcap_atc_mandatory_email' ),
                                            'wcap_ajax_add' => get_option('woocommerce_enable_ajax_add_to_cart'),
                                            'wcap_populate_email' => $wcap_populate_email_address,
                                            'wcap_ajax_url' => WCAP_ADMIN_URL,
                                            'wcap_mandatory_text' => __('Email address is mandatory for adding product to the cart.', 'woocommerce-ac'),
                                            'wcap_mandatory_email_text' => __(' Please enter a valid email address.', 'woocommerce-ac'),
                                            'wcap_atc_coupon_applied_msg' => __( "Thank you. Coupon $atc_coupon_code will be auto-applied to your cart.", 'woocommerce-ac' ),
                                ) );
                            } else {
                                wp_dequeue_script( 'wc-add-to-cart-variation' );
                                wp_deregister_script( 'wc-add-to-cart-variation' );

                                wp_register_script( 'wc-add-to-cart-variation' , WCAP_PLUGIN_URL . '/assets/js/frontend/wcap_atc_modal_single_product' . $suffix . '.js', array( 'jquery', 'wp-util' ), '', true );

                                wp_enqueue_script ( 'wc-add-to-cart-variation' );
        
                                wp_localize_script( 'wc-add-to-cart-variation', 'wcap_atc_modal_param_variation',            array(
                                                    'wcap_atc_modal_data'  =>  $wcap_atc_modal,
                                                    'wcap_atc_head'  => __( get_option('wcap_heading_section_text_email'), 'woocommerce-ac' ),
                                                    'wcap_atc_text'  => __( get_option('wcap_text_section_text'), 'woocommerce-ac' ),
                                                    'wcap_atc_email_place'  => __( get_option('wcap_email_placeholder_section_input_text'), 'woocommerce-ac' ),
                                                    'wcap_atc_button'  => __( get_option('wcap_button_section_input_text'), 'woocommerce-ac' ),
                                                    'wcap_atc_button_bg_color'  => get_option('wcap_button_color_picker'),
                                                    'wcap_atc_button_text_color'=> get_option('wcap_button_text_color_picker'),
                                                    'wcap_atc_popup_text_color' => get_option('wcap_popup_text_color_picker'),
                                                    'wcap_atc_popup_heading_color' => get_option('wcap_popup_heading_color_picker'),
                                                    'wcap_atc_mandatory_email' => get_option( 'wcap_atc_mandatory_email' ),
                                                    'wcap_atc_non_mandatory_input_text' => __( get_option('wcap_non_mandatory_text'), 'woocommerce-ac' ),
                                                    'wcap_populate_email' => $wcap_populate_email_address,
                                                    'wcap_ajax_url' => WCAP_ADMIN_URL,
                                                    'wcap_mandatory_text' => __('Email address is mandatory for adding product to the cart.', 'woocommerce-ac'),
                                                    'wcap_mandatory_email_text' => __(' Please enter a valid email address.', 'woocommerce-ac'),
                                                    'wcap_atc_coupon_applied_msg' => __( "Thank you. Coupon $atc_coupon_code will be auto-applied to your cart.", 'woocommerce-ac' ),
                                ) );
                            }
                        } else if( $wcap_product->is_type( 'grouped' ) ) {
                            wp_enqueue_script( 'jquery' );
                            wp_register_script( 'wcap_atc_group_product' , WCAP_PLUGIN_URL . '/assets/js/frontend/wcap_atc_group_page' . $suffix . '.js' );
                            wp_enqueue_script ( 'wcap_atc_group_product');

                            wp_localize_script( 'wcap_atc_group_product', 'wcap_atc_modal_param', array(
                                      'wcap_atc_modal_data'  =>  $wcap_atc_modal,
                                      'wcap_atc_head'  => __( get_option('wcap_heading_section_text_email'), 'woocommerce-ac' ),
                                      'wcap_atc_text'  => __( get_option('wcap_text_section_text'), 'woocommerce-ac' ),
                                      'wcap_atc_email_place'  => __( get_option('wcap_email_placeholder_section_input_text'), 'woocommerce-ac' ),
                                      'wcap_atc_button'  => __( get_option('wcap_button_section_input_text'), 'woocommerce-ac' ),
                                      'wcap_atc_button_bg_color'  => get_option('wcap_button_color_picker'),
                                      'wcap_atc_button_text_color'=> get_option('wcap_button_text_color_picker'),
                                      'wcap_atc_popup_text_color' => get_option('wcap_popup_text_color_picker'),
                                      'wcap_atc_popup_heading_color' => get_option('wcap_popup_heading_color_picker'),
                                      'wcap_atc_mandatory_email' => get_option( 'wcap_atc_mandatory_email' ),
                                      'wcap_atc_non_mandatory_input_text' => __( get_option('wcap_non_mandatory_text'), 'woocommerce-ac' ),
                                      'wcap_ajax_add' => get_option('woocommerce_enable_ajax_add_to_cart'),
                                      'wcap_populate_email' => $wcap_populate_email_address,
                                      'wcap_ajax_url' => WCAP_ADMIN_URL,
                                      'wcap_mandatory_text' => __('Email address is mandatory for adding product to the cart.', 'woocommerce-ac'),
                                      'wcap_mandatory_email_text' => __(' Please enter a valid email address.', 'woocommerce-ac'),
                                      'wcap_atc_coupon_applied_msg' => __( "Thank you. Coupon $atc_coupon_code will be auto-applied to your cart.", 'woocommerce-ac' ),
                            ) );
                        }
                    }

                    if ( is_cart() && ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) || 'no'===get_option( 'woocommerce_cart_redirect_after_add' ) ) ) {
                        wp_enqueue_script( 'jquery' );
                        wp_register_script( 'wcap_atc_cart' , WCAP_PLUGIN_URL . '/assets/js/frontend/wcap_atc_cart_page' . $suffix . '.js' );
                        wp_enqueue_script ( 'wcap_atc_cart');
                        wp_localize_script( 'wcap_atc_cart', 'wcap_atc_cart_param', array(
                                            'wcap_ajax_url' => WCAP_ADMIN_URL
                        ) );
                    }
                    do_action( 'wcap_after_atc_scripts_loaded', $wcap_atc_modal, $wcap_populate_email_address, $atc_coupon_code );
                }
            }

            if ( wcap_get_cart_session( 'wcap_email_sent_id' ) == '' && ( 'on' === get_option( 'ac_capture_email_from_forms' ) || '' !== get_option( 'ac_capture_email_address_from_url' ) ) && !is_user_logged_in() ) {
                wp_register_script( 'wcap_mailchimp_capture' , WCAP_PLUGIN_URL . '/assets/js/frontend/wcap_mailchimp_capture' . $suffix . '.js' );
                wp_localize_script( 
                    'wcap_mailchimp_capture', 
                    'wcap_mailchimp_setting', 
                    array(
                        'wcap_popup_setting' => get_option('wcap_atc_enable_modal'),
                        'wcap_form_classes'  => str_replace( ' ', '', trim( get_option('ac_email_forms_classes') ) ),
                        'wcap_ajax_url'      => WCAP_ADMIN_AJAX_URL,
                        'wcap_url_capture'   => get_option('ac_capture_email_address_from_url'),
                    )
                );
                wp_enqueue_script ( 'wcap_mailchimp_capture' );
            }
        }

        /**
         * Enqueue CSS file to be included at front end for Add To Cart Popup Modal.
         * 
         * @hook wp_enqueue_scripts
         * 
         * @since 6.0
         */
        public static function wcap_enqueue_css_atc_modal() {
            if ( !is_cart() && !is_checkout() && !is_user_logged_in() ) {
                wp_enqueue_style( 'wcap_abandoned_details_modal', WCAP_PLUGIN_URL . '/assets/css/frontend/wcap_atc_detail_modal.min.css' );
            }
            if ( ( is_cart() || is_checkout() ) && !is_user_logged_in() ) {
                wp_enqueue_style( 'wcap_countdown_timer', WCAP_PLUGIN_URL . '/assets/css/frontend/wcap_atc_countdown_timer.css' );
                wp_enqueue_style( 'wcap-font-awesome', WCAP_PLUGIN_URL . '/assets/css/admin/font-awesome.css' );
                wp_enqueue_style( 'wcap-font-awesome-min', WCAP_PLUGIN_URL . '/assets/css/admin/font-awesome.min.css' );
            }
        }

        /**
         * Load CSS file to be included at WordPress Admin.
         * 
         * @hook admin_enqueue_scripts
         *
         * @param   int $hook Hook suffix for the current admin page
         * @globals mixed $pagenow
         * @since   6.0
         */
        public static function wcap_enqueue_scripts_css( $hook ) {
            global $pagenow;

            $page = isset( $_GET['page'] ) ? $_GET['page'] : '';

            if ( $hook != 'woocommerce_page_woocommerce_ac_page' && 'index.php' === $pagenow ) {
                wp_enqueue_style( 'wcap-dashboard',                  WCAP_PLUGIN_URL . '/assets/css/admin/wcap_style.min.css' );
                return;
            } elseif ( $page === 'woocommerce_ac_page' ) {
                wp_enqueue_style( 'jquery-ui',                WCAP_PLUGIN_URL . '/assets/css/admin/jquery-ui.css', '', '', false );
                wp_enqueue_style( 'woocommerce_admin_styles', plugins_url() . '/woocommerce/assets/css/admin.css' );
                wp_enqueue_style( 'jquery-ui-style',          WCAP_PLUGIN_URL . '/assets/css/admin/jquery-ui-smoothness.css' );
                
                $action = '' ;
                if ( isset( $_GET[ 'action'] ) ) {
                    $action = Wcap_Common::wcap_get_action();
                }

                if ( 'wcap_dashboard_advanced' ==  $action || '' == $action ){
                    wp_enqueue_style( 'wcap-dashboard-adv',           WCAP_PLUGIN_URL . '/assets/css/admin/wcap_reports_adv.css' );

                    wp_register_style( 'bootstrap_css', WCAP_PLUGIN_URL . '/assets/css/admin/bootstrap.min.css', '', '', 'all' );
                    wp_enqueue_style( 'bootstrap_css' );

                    wp_enqueue_style( 'wcap-font-awesome', WCAP_PLUGIN_URL . '/assets/css/admin/font-awesome.css' );
            
                    wp_enqueue_style( 'wcap-font-awesome-min', WCAP_PLUGIN_URL . '/assets/css/admin/font-awesome.min.css' );
                }

                if ( 'wcap_dashboard' ==  $action || '' == $action ){
                    wp_enqueue_style( 'wcap-dashboard',               WCAP_PLUGIN_URL . '/assets/css/admin/wcap_reports.min.css' );
                }

                if ( 'listcart' == $action || 'cart_recovery' == $action ){
                    wp_enqueue_style( 'abandoned-orders-list',        WCAP_PLUGIN_URL . '/assets/css/admin/wcap_view_abandoned_orders_style.min.css' );
                }

                if ( 'cart_recovery' ==  $action ){
                    wp_register_style( 'bootstrap_css', WCAP_PLUGIN_URL . '/assets/css/admin/bootstrap.min.css', '', '', 'all' );
                    wp_enqueue_style( 'bootstrap_css' );
                    wp_enqueue_style( 'wcap_template_activate',       WCAP_PLUGIN_URL . '/assets/css/admin/wcap_template_activate.min.css' );
                    wp_enqueue_style( 'wcap_preview_email',           WCAP_PLUGIN_URL . '/assets/css/admin/wcap_preview_email.min.css' );
                    wp_enqueue_style( 'wcap_modal_preview',           WCAP_PLUGIN_URL . '/assets/css/admin/wcap_preview_modal.css' );
                }
                if ( 'listcart' ==  $action || 'emailstats' == $action ){
                    wp_enqueue_style( 'wcap_abandoned_details_modal', WCAP_PLUGIN_URL . '/assets/css/admin/wcap_abandoned_cart_detail_modal.min.css' );
                    wp_enqueue_style( 'wcap_abandoned_details',       WCAP_PLUGIN_URL . '/assets/css/admin/wcap_view_order_button.min.css' );
                }
                if ( 'emailsettings' ==  $action ) {
                    wp_enqueue_style( 'wcap_add_to_cart_popup_modal', WCAP_PLUGIN_URL . '/assets/css/admin/wcap_add_to_cart_popup_modal.min.css' );
                }
            }
            
            $action = isset( $_GET[ 'action' ] ) ? $_GET[ 'action' ] : '';
            $section = isset( $_GET[ 'section' ] ) ? $_GET[ 'section' ] : '';
            
            if( 'cart_recovery' == $action && ( 'sms' == $section || 'fb_templates' == $section ) ) {
                wp_enqueue_style( 'wcap_sms_list', WCAP_PLUGIN_URL . '/assets/css/admin/wcap_sms_template_list.css' );
                wp_enqueue_style( 'wcap-font-awesome', WCAP_PLUGIN_URL . '/assets/css/admin/font-awesome.css' );
            
                wp_enqueue_style( 'wcap-font-awesome-min', WCAP_PLUGIN_URL . '/assets/css/admin/font-awesome.min.css' );
            }
            $wcap_section = isset( $_GET['wcap_section'] ) ? $_GET['wcap_section'] : '';            
            if ( 'emailsettings' === $action && 'wcap_atc_settings' === $wcap_section ) {
                wp_enqueue_style( 'wcap-font-awesome', WCAP_PLUGIN_URL . '/assets/css/admin/font-awesome.css' );
                wp_enqueue_style( 'wcap-font-awesome-min', WCAP_PLUGIN_URL . '/assets/css/admin/font-awesome.min.css' );
            }
        }

        public static function wcap_template_params(){

            $localized_array = array();

            for ($temp=1; $temp < 13; $temp++) { 
                $temp_obj = new stdClass();
                $temp_obj->id = $temp;
                $temp_obj->url = WCAP_PLUGIN_URL . '/assets/images/templates/template_' . $temp . '.png';
                $temp_obj->html = WCAP_PLUGIN_URL . '/assets/html/templates/template_' . $temp . '.html';

                array_push( $localized_array, $temp_obj );
            }

            return $localized_array;
        }
    }
}
