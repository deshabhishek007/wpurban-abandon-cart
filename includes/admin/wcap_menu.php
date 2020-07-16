<?php
/**
 * It will display the menu of the Abadoned cart.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Menu
 * @since 1.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if ( !class_exists('Wcap_Menu' ) ) {
    /**
     * It will display the menu of the Abadoned cart.
     */
    class Wcap_Menu {

        /**
         * It will add the 'Abandoned Carts' as the sub menu under the WooCommerce menu.
         * @hook admin_menu
         * @since 1.0
         */
        public static function wcap_admin_menu() {

            $page = add_submenu_page( 'woocommerce', __( 'Abandoned Carts', 'woocommerce-ac' ), __( 'Abandoned Carts', 'woocommerce-ac' ), 'manage_woocommerce', 'woocommerce_ac_page', array( 'Wcap_Menu', 'wcap_menu_page' ) );
        }

        /**
         * It is the call back for the Abandon Cart Page.
         * It will display all the tabs of the plugin.
         * @since 1.0
         */
        public static function wcap_menu_page() {

            if ( is_user_logged_in() ) {
                Wcap_Common::wcap_check_user_can_manage_woocommerce();
                ?>
                <div class="wrap">
                <h2>
                    <?php _e( 'WooCommerce - Abandon Cart', 'woocommerce-ac' ); ?>
                </h2>
                <?php
                $action     = Wcap_Common::wcap_get_action();
                $mode       = Wcap_Common::wcap_get_mode();
                $action_two = Wcap_Common::wcap_get_action_two();
                $section    = Wcap_Common::wcap_get_section();

                Wcap_Actions::wcap_perform_action( $action, $action_two );

                $wcap_get_notice_action = Wcap_Common::wcap_get_notice_action();
                if ( '' != $wcap_get_notice_action ){
                    Wcap_Display_Notices::wcap_display_notice( $wcap_get_notice_action );
                }
                if ( isset( $_POST ['mode'] ) && 'manual_email' == $_POST ['mode'] && isset( $_POST ['Submit'] ) && 'Send Email' == $_POST ['Submit'] ) {
                        Wcap_Send_Manual_Email::wcap_create_and_send_manual_email ();
                }

                do_action ('wcap_display_message');

                Wcap_Menu::wcap_display_tabs();

                do_action ( 'wcap_crm_data' );
                do_action ( 'wcap_add_tab_content' );

                if ( 'emailsettings' == $action ) {

                    Wcap_Email_Settings::wcap_display_email_setting();
                } elseif ( 'wcap_dashboard_advanced' == $action || '' == $action ) {

                    Wcap_Dashboard_Advanced::wcap_display_dashboard();
                } elseif ( 'wcap_dashboard' == $action || '' == $action ) {

                    Wcap_Dashboard::wcap_display_dashboard();
                } elseif ( 'listcart' == $action && ( !isset($_GET['action_details'])  )
                    && ( !isset($_GET['wcap_download']) || 'wcap.csv' != $_GET['wcap_download'] )
                    && ( !isset($_GET['wcap_download']) || 'wcap.print' != $_GET['wcap_download'] )
                    ) {

                    Wcap_Abandoned_Cart_List::wcap_display_abandoned_cart_list();
                } else if ( 'listcart' == $action && ( isset($_GET['wcap_download']) && 'wcap.csv' == $_GET['wcap_download'] ) ) {
                    /**
                     * Here we take all the previous echoed, printed data. Then we clear the buffer.
                     */
                    $old_data = ob_get_clean ();
                    $wcap_csv = Wcap_Print_And_CSV::wcap_generate_csv_report();

                    header("Content-type: application/x-msdownload");
                    header("Content-Disposition: attachment; filename=wcap_cart_report.csv");
                    header("Pragma: no-cache");
                    header("Expires: 0");
                    echo "\xEF\xBB\xBF";
                    /**
                     * Here Space before and after variable is needed other wise it is not printing th data in csv format.
                     */
                    echo $wcap_csv;
                    exit;
                } elseif ( 'cart_recovery' == $action && ( 'edittemplate' != $mode && 'addnewtemplate' != $mode && 'copytemplate' != $mode && 'wcap_manual_email' != $mode ) ) {

                    Wcap_Email_Template_List::wcap_display_recovery_submenu( $action, $section, $mode );
                } elseif( 'emailstats' == $action && 'sms' == $section ) {

                    Wcap_Sent_SMS_List::wcap_sent_sms();
                } elseif ( 'emailstats' == $action ) {

                    Wcap_Eent_Email_List::wcap_display_sent_emails_list();
                }
                if ( 'cart_recovery' == $action && ( 'emailtemplates' == $section ) && ( 'addnewtemplate' == $mode || 'edittemplate' == $mode || 'copytemplate' == $mode ) ) {

                    Wcap_Email_Template_Fields::wcap_display_email_template_fields();
                } else if ( 'cart_recovery' == $action && 'emailtemplates' == $section && 'wcap_manual_email' == $mode ) {

                    WCAP_Manual_Email::wcap_display_manual_email_template();
                } elseif ( $action == 'report' ) {

                    Wcap_Product_Report_List::wcap_display_product_report_list();
                } 
                echo( "</table>" );
            }
        }
        /**
         * It will display all the tabs of the plugin
         * @since 1.0
         */
        public static function wcap_display_tabs() {
            $action = Wcap_Common::wcap_get_action();
        
            $active_wcap_dashboard = "";
            $active_dashboard_adv  = "";
            $active_listcart       = "";
            $active_cart_recovery  = "";
            $active_settings       = "";
            $active_stats          = "";
            
            switch( $action ) {
                case 'wcap_dashboard':
                    $active_wcap_dashboard = "nav-tab-active";
                    break;
                case 'wcap_dashboard_advanced':
                case '':
                    $active_dashboard_adv = "nav-tab-active";
                    break;
                case 'listcart':
                    $active_listcart = "nav-tab-active";
                    break;
                case 'cart_recovery':
                    $active_cart_recovery = "nav-tab-active";
                    break;
                case 'sms':
                    $active_sms = "nav-tab-active";
                    break;
                case 'emailsettings':
                    $active_settings       = "nav-tab-active";
                    break;
                case 'emailstats':
                    $active_emailstats     = "nav-tab-active";
                    break;
                case 'report':
                    $active_report         = "nav-tab-active";
                    break;
            }
            ?>

            <div style="background-image: url( '<?php echo plugins_url(); ?>/woocommerce-abandon-cart-pro/assets/images/ac_tab_icon.png' ) !important;" class="icon32">
            <br>
            </div>
            <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
                <a href="admin.php?page=woocommerce_ac_page" class="nav-tab <?php echo $active_dashboard_adv; ?>"> <?php _e( 'Dashboard', 'woocommerce-ac' );?> </a>
                <!-- <a href="admin.php?page=woocommerce_ac_page&action=wcap_dashboard" class="nav-tab <?php echo $active_wcap_dashboard; ?>"> <?php _e( 'Dashboard', 'woocommerce-ac' );?> </a> -->
                <a href="admin.php?page=woocommerce_ac_page&action=listcart" class="nav-tab <?php echo $active_listcart; ?>"> <?php _e( 'Abandoned Orders', 'woocommerce-ac' );?> </a>
                <a href="admin.php?page=woocommerce_ac_page&action=cart_recovery" class="nav-tab <?php echo $active_cart_recovery; ?>"> <?php _e( 'Templates', 'woocommerce-ac' );?> </a>
                <a href="admin.php?page=woocommerce_ac_page&action=emailsettings" class="nav-tab <?php echo $active_settings; ?>"> <?php _e( 'Settings', 'woocommerce-ac' );?> </a>
                <a href="admin.php?page=woocommerce_ac_page&action=emailstats" class="nav-tab <?php if( isset( $active_emailstats ) ) echo $active_emailstats; ?>"> <?php _e( 'Reminders Sent', 'woocommerce-ac' );?> </a>
                <a href="admin.php?page=woocommerce_ac_page&action=report" class="nav-tab <?php if( isset( $active_report ) ) echo $active_report; ?>"> <?php _e( 'Product Report', 'woocommerce-ac' );?> </a>
                <?php
                    do_action ('wcap_add_settings_tab');
                    do_action ( 'wcap_add_tabs' );
                    if ( has_action( 'wcap_add_tabs' ) ) {
                        if ( isset( $_GET['action'] ) && 'wcap_crm' == $_GET['action'] ) {
                            settings_errors();
                        }
                    }
                ?>
            </h2>
            <?php
        }

    }
}
