<?php
/**
 * It will display the notices on the admin side.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Notices
 * @since   5.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Display_Notices' ) ) {
    /**
     * It will display the notices on the admin side.
     */
    class Wcap_Display_Notices {

        /**
         * It will display the notice all around the plugin.
         * @param string $wcap_get_notice_action Action name
         * @since 5.0
         */
        public static function wcap_display_notice( $wcap_get_notice_action ) {
            
            $order = 'order' ;
            if ( 'wcap_deleted' == $wcap_get_notice_action ) {
                $wcap_trash_selected_order_count = $_GET['wcap_count'];
                
                if ( $wcap_trash_selected_order_count > 1 ) {
                    $order = 'orders';
                }
                ?>
                <div id="message" class="updated fade">
                    <p>
                        <strong>
                            <?php _e( "$wcap_trash_selected_order_count Abandoned $order has been successfully deleted.", 'woocommerce-ac' ); ?>
                        </strong>
                    </p>
                </div>
            <?php }

            if ( 'wcap_rec_deleted' == $wcap_get_notice_action ) { ?>
                <div id="message" class="updated fade">
                    <p>
                        <strong>
                            <?php _e( 'The recovered cart has been successfully deleted.', 'woocommerce-ac' ); ?>
                        </strong>
                    </p>
                </div>
            <?php }

            if ( 'wcap_abandoned_trash' == $wcap_get_notice_action ) {
                $wcap_trash_selected_order_count = $_GET['wcap_count'];
                
                if ( $wcap_trash_selected_order_count > 1 ) {
                    $order = 'orders';
                }
                ?>
                <div id="message" class="updated fade">
                    <p>
                        <strong>
                            <?php _e( "$wcap_trash_selected_order_count Abandoned $order moved to Trash.", 'woocommerce-ac' ); ?>
                        </strong>
                    </p>
                </div>
            <?php }

            if ( 'wcap_abandoned_restore'  == $wcap_get_notice_action ) {
                $wcap_trash_selected_order_count = $_GET['wcap_count'];
                
                if ( $wcap_trash_selected_order_count > 1 ) {
                    $order = 'orders';
                }
                ?>
                <div id="message" class="updated fade">
                    <p>
                        <strong>
                            <?php _e( "$wcap_trash_selected_order_count Abandoned $order restored from Trash.", 'woocommerce-ac' ); ?>
                        </strong>
                    </p>
                </div>
            <?php }

            if ( 'wcap_rec_trash'  == $wcap_get_notice_action ) {
                $wcap_trash_selected_order_count = $_GET['wcap_count'];
                
                if ( $wcap_trash_selected_order_count > 1 ) {
                    $order = 'orders';
                }
                ?>
                <div id="message" class="updated fade">
                    <p>
                        <strong>
                            <?php _e( "$wcap_trash_selected_order_count recovered $order moved to the Trash.", 'woocommerce-ac' ); ?>
                        </strong>
                    </p>
                </div>
            <?php }

            if ( 'wcap_rec_restore'  == $wcap_get_notice_action ) {
                $wcap_trash_selected_order_count = $_GET['wcap_count'];
                
                if ( $wcap_trash_selected_order_count > 1 ) {
                    $order = 'orders';
                }
                ?>
                <div id="message" class="updated fade">
                    <p>
                        <strong>
                            <?php _e( "$wcap_trash_selected_order_count $order restored from the Trash.", 'woocommerce-ac' ); ?>
                        </strong>
                    </p>
                </div>
            <?php }

            if ( 'wcap_template_deleted'  == $wcap_get_notice_action ) { ?>
                <div id="message" class="updated fade">
                    <p>
                        <strong>
                            <?php _e( 'The Template has been successfully deleted.', 'woocommerce-ac' ); ?>
                        </strong>
                    </p>
                </div>
            <?php }

            if ( 'wcap_manual_email_sent'  == $wcap_get_notice_action ) { ?>
                <div id="message" class="updated fade">
                    <p>
                        <strong>
                            <?php _e( 'The abandoned cart reminder email has been sent successfully to the selected customer(s).', 'woocommerce-ac' ); ?>
                        </strong>
                    </p>
                </div>
            <?php }

            if ( 'wcap_import_lite_to_pro'  == $wcap_get_notice_action ) { ?>
                <div id="message" class="updated fade">
                    <p>
                        <strong>
                            <?php _e( 'The data from Lite version has been successfully imported to Pro version. Go to <a href="admin.php?page=woocommerce_ac_page&action=listcart">Abandoned Orders list</a> or click on Abandoned Orders tab below.', 'woocommerce-ac' ); ?>
                        </strong>
                    </p>
                </div>
            <?php }
        }
        /**
         * It will display the notice for tamplate save message.
         * @since 5.0
         */
        public static function wcap_template_save_success () {
            ?>
                <div id="message" class="updated fade">
                    <p>
                        <strong>
                            <?php _e( 'The Email Template has been successfully added.', 'woocommerce-ac' ); ?>
                                
                        </strong>
                    </p>
                </div>
            <?php
        }
        /**
         * It will display the notice for tamplate error message.
         * @since 5.0
         */
        public static function wcap_template_save_error () {
            ?>
                <div id="message" class="error fade">
                    <p>
                        <strong>
                            <?php _e( ' There was a problem adding the email template. Please contact the plugin author via <a href= "https://www.tychesoftwares.com/forums/forum/woocommerce-abandon-cart-pro/">support forum</a>.', 'woocommerce-ac' ); ?>
                        </strong>
                    </p>
                </div>
            <?php
        }

        /**
         * It will display the notice for tamplate updated message.
         * @since 5.0
         */
        public static function wcap_template_updated_success () {
            ?>
                <div id="message" class="updated fade">
                    <p>
                        <strong>
                            <?php _e( 'The Email Template has been successfully updated.', 'woocommerce-ac' ); ?>
                        </strong>
                    </p>
                </div>
            <?php
        }

        /**
         * It will display the notice if there is some problem while saving the template.
         * @since 5.0 
         */
        public static function wcap_template_updated_error () {
            ?>
                <div id="message" class="error fade">
                    <p>
                        <strong>
                            <?php _e( ' There was a problem updating the email template. Please contact the plugin author via <a href= "https://www.tychesoftwares.com/forums/forum/woocommerce-abandon-cart-pro/">support forum</a>.', 'woocommerce-ac' ); ?>
                        </strong>
                    </p>
                </div>
            <?php
        }

        /**
         * It will display the settings saved messge on the Add To Cart settings page.
         * @since 6.0
         */
        public static function wcap_add_to_cart_popup_save_success () {
            ?>
                <div id="message" class="updated fade">
                    <p>
                        <strong>
                            <?php _e( 'Settings saved.', 'woocommerce-ac' ); ?>
                        </strong>
                    </p>
                </div>
            <?php
        }
    }
}