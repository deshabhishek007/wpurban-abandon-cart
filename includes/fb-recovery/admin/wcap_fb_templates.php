<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * Load FB Messenger Templates
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/FB-Messenger
 * @category Modules
 * @since    7.10.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;   //Exit if accessed directly.
}

if ( !class_exists( 'WCAP_FB_Templates' ) ) {

    /**
     * Class for FB Messenger Templates
     */
    class WCAP_FB_Templates {

        function __construct() {

            add_action( 'wp_ajax_wcap_fb_save_template', array( &$this, 'wcap_save_template_data' ) );
        }

        public function wcap_save_template_data(){

            if ( isset( $_POST['template_id'] ) ) {
                wcap_update_notifications(
                    $_POST['template_id'], 
                    stripslashes( $_POST['body'] ), 
                    $_POST['sent_time'], 
                    $_POST['active'], 
                    '',
                    $_POST['subject'] 
                );
            }else {
                $temp_id = wcap_insert_notifications( 
                    stripslashes( $_POST['body'] ), 
                    'fb', 
                    0, 
                    $_POST['sent_time'], 
                    '', 
                    0, 
                    $_POST['subject']
                );
            }
            die();
        }

        public static function wcap_fb_templates_list() {

            if( Wcap_EDD::wcap_edd_get_license_status() == 'valid' ):
            ?>
                <p><?php _e( 'Add Facebook Messenger templates to be sent at different intervals to maximize the possibility of recovering your abandoned carts.', 'woocommerce-ac' ); ?></p>

                <button id="add_fb_template" class="button-secondary" onclick="return false;" data-toggle="modal" data-target=".wcap-preview-modal">
                    <?php _e( 'Add New Template', 'woocommerce-ac' ); ?>
                </button>

                <div class="tablenav">
                    <?php
                        $fb_list = new WCAP_FB_Templates_List();
                        $fb_list->wcap_fb_templates_prepare_items();

                        wc_get_template( 
                            'wcap_fb_template_editor.php', 
                            '', 
                            'woocommerce-abandon-cart-pro/',
                            WCAP_PLUGIN_PATH . '/includes/fb-recovery/admin/' );
                    ?>

                    <div class="wrap">
                        <form id="wcap-sms-templates" method="get" >
                            <input type="hidden" name="page" value="woocommerce_ac_page" />
                            <input type="hidden" name="action" value="cart_recovery" />
                            <input type="hidden" name="section" value="sms" />
                            <?php $fb_list->display(); ?>
                        </form>
                    </div>

                </div>
            <?php
            else:
                wc_get_template( 
                    'license_missing.php', 
                    '', 
                    'woocommerce-abandon-cart-pro/',
                    WCAP_PLUGIN_PATH . '/includes/template/license_missing/' );
            endif;
        }
    }
}

return new WCAP_FB_Templates();