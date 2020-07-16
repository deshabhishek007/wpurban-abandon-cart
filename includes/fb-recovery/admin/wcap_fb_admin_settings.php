<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * Load FB Messenger settings and related actions
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/FB-Messenger
 * @category Modules
 * @since    7.10.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;   //Exit if accessed directly.
}

if ( !class_exists( 'WCAP_FB_Admin' ) ) {

    /**
     * FB Admin Settings and Actions Class
     */
    class WCAP_FB_Admin {
        
        function __construct() {
            
            add_filter( 'wcap_recovery_submenu', array( &$this, 'wcap_add_recovery_submenu' ) );

            add_action( 'admin_init', array( &$this, 'wcap_add_fb_settings' ) );

            add_action( 'admin_enqueue_scripts', array( &$this, 'wcap_fb_js_scripts' ), 9999 );
            add_action( 'admin_enqueue_scripts', array( &$this, 'wcap_fb_css_scripts' ), 9999 );
        }

        function wcap_fb_js_scripts() {

            wp_enqueue_media();

            wp_register_script(
                'wcap_fb_script',
                WCAP_PLUGIN_URL . '/includes/fb-recovery/assets/js/wcap_fb_templates.js',
                '',
                '',
                true
            );

            wp_localize_script( 
                'wcap_fb_script',
                'wcap_fb_params',
                array( 'wcap_fb_header_image' => WCAP_PLUGIN_URL . '/includes/fb-recovery/assets/css/images/carts_div.png' ) 
            );

            wp_enqueue_script( 'wcap_fb_script' );
        }

        function wcap_fb_css_scripts() {
            
            wp_enqueue_style(
                'wcap_fb_style',
                WCAP_PLUGIN_URL . '/includes/fb-recovery/assets/css/wcap_fb_templates.css',
                '',
                '',
                'all'
            );
        }

        function wcap_add_recovery_submenu( $menu ) {
            
            $menu['fb_templates'] = array(
                'key' => 'fb_templates',
                'label' => 'Facebook Messenger Templates',
                'active' => '',
                'callback' => array( 'WCAP_FB_Templates', 'wcap_fb_templates_list' )
            );

            return $menu;
        }

        public static function wcap_fb_settings() {

            if( Wcap_EDD::wcap_edd_get_license_status() == 'valid' ) {
                ?>
                    <form method="post" action="options.php">
                        <?php 
                        //settings_errors();
                        settings_fields( 'woocommerce_fb_settings' );
                        do_settings_sections( 'woocommerce_ac_fb_page' );
                        submit_button(); 
                        ?>
                    </form>
                <?php

                self::wcap_fb_whitelist();

                self::wcap_fb_webhook();
            } else {
                wc_get_template( 
                    'license_missing.php', 
                    '', 
                    'woocommerce-abandon-cart-pro/',
                    WCAP_PLUGIN_PATH . '/includes/template/license_missing/' );
            }
        }

        function wcap_add_fb_settings() {

            add_settings_section(
                'wcap_fb_settings_section',
                __( 'Facebook Messenger Settings', 'woocommerce-ac' ),
                array( &$this, 'wcap_fb_description' ),
                'woocommerce_ac_fb_page'
            );

            add_settings_field(
                'wcap_enable_fb_reminders',
                __( 'Enable Facebook Messenger Reminders', 'woocommerce-ac'  ),
                array( &$this, 'wcap_fb_checkbox_callback' ),
                'woocommerce_ac_fb_page',
                'wcap_fb_settings_section',
                array( '<i>This option will display a checkbox after the Add to cart button for user consent to connect with Facebook.</i>', 'woocommerce-ac', 'wcap_enable_fb_reminders' )
            );

            add_settings_field(
                'wcap_enable_fb_reminders_popup',
                __( 'Facebook Messenger on Add to Cart Pop-up modal', 'woocommerce-ac'  ),
                array( &$this, 'wcap_fb_checkbox_callback' ),
                'woocommerce_ac_fb_page',
                'wcap_fb_settings_section',
                array( '<i>This option will display a checkbox on the pop-up modal to connect with Facebook.</i>', 'woocommerce-ac', 'wcap_enable_fb_reminders_popup' )
            );

            /*add_settings_field(
                'wcap_fb_prechecked',
                __( 'Checkbox pre-checked', 'woocommerce-ac'  ),
                array( &$this, 'wcap_fb_checkbox_callback' ),
                'woocommerce_ac_fb_page',
                'wcap_fb_settings_section',
                array( '<i>This option will enable the consent checkbox by default.</i>', 'woocommerce-ac', 'wcap_fb_prechecked' )
            );*/

            add_settings_field(
                'wcap_fb_user_icon',
                __( 'Icon size of user', 'woocommerce-ac'  ),
                array( &$this, 'wcap_fb_dropdown_callback' ),
                'woocommerce_ac_fb_page',
                'wcap_fb_settings_section',
                array( 
                    '<i>Select the size of user icon which shall be displayed below the checkbox in case the user is logged in.</i>', 
                    'woocommerce-ac', 
                    'wcap_fb_user_icon',
                    array( 
                        'small' => __( 'Small', 'woocommerce-ac' ),
                        'medium' => __( 'Medium', 'woocommerce-ac' ),
                        'large' => __( 'Large', 'woocommerce-ac' ),
                        'standard' => __( 'Standard', 'woocommerce-ac' ),
                        'xlarge' => __( 'Extra Large', 'woocommerce-ac' )
                    ) 
                )
            );

            add_settings_field(
                'wcap_fb_consent_text',
                __( 'Consent text', 'woocommerce-ac'  ),
                array( &$this, 'wcap_fb_text_callback' ),
                'woocommerce_ac_fb_page',
                'wcap_fb_settings_section',
                array( '<i>Text that will appear above the consent checkbox. HTML tags are also allowed.</i>', 'woocommerce-ac', 'wcap_fb_consent_text' )
            );

            add_settings_field(
                'wcap_fb_page_id',
                __( 'Facebook Page ID', 'woocommerce-ac'  ),
                array( &$this, 'wcap_fb_text_callback' ),
                'woocommerce_ac_fb_page',
                'wcap_fb_settings_section',
                array( '<i>Facebook Page ID in numberic format. You can find your page ID from <a href="https://www.tychesoftwares.com/docs/docs/abandoned-cart-pro-for-woocommerce/send-abandoned-cart-reminder-notifications-using-facebook-messenger#fbpageid" target="_blank">here</a></i>', 'woocommerce-ac', 'wcap_fb_page_id' )
            );

            add_settings_field(
                'wcap_fb_app_id',
                __( 'Messenger App ID', 'woocommerce-ac'  ),
                array( &$this, 'wcap_fb_text_callback' ),
                'woocommerce_ac_fb_page',
                'wcap_fb_settings_section',
                array( '<i>Enter your Messenger App ID</i>', 'woocommerce-ac', 'wcap_fb_app_id' )
            );

            add_settings_field(
                'wcap_fb_page_token',
                __( 'Facebook Page Token', 'woocommerce-ac'  ),
                array( &$this, 'wcap_fb_text_callback' ),
                'woocommerce_ac_fb_page',
                'wcap_fb_settings_section',
                array( '<i>Enter your Facebook Page Token</i>', 'woocommerce-ac', 'wcap_fb_page_token' )
            );

            add_settings_field(
                'wcap_fb_verify_token',
                __( 'Verify Token', 'woocommerce-ac'  ),
                array( &$this, 'wcap_fb_text_callback' ),
                'woocommerce_ac_fb_page',
                'wcap_fb_settings_section',
                array( '<i>Enter your Verify Token</i>', 'woocommerce-ac', 'wcap_fb_verify_token' )
            );

            register_setting(
                'woocommerce_fb_settings',
                'wcap_enable_fb_reminders'
            );

            register_setting(
                'woocommerce_fb_settings',
                'wcap_enable_fb_reminders_popup'
            );

            /*register_setting(
                'woocommerce_fb_settings',
                'wcap_fb_prechecked'
            );*/

            register_setting(
                'woocommerce_fb_settings',
                'wcap_fb_consent_text'
            );

            register_setting(
                'woocommerce_fb_settings',
                'wcap_fb_page_id'
            );

            register_setting(
                'woocommerce_fb_settings',
                'wcap_fb_user_icon'
            );

            register_setting(
                'woocommerce_fb_settings',
                'wcap_fb_app_id'
            );

            register_setting(
                'woocommerce_fb_settings',
                'wcap_fb_page_token'
            );

            register_setting(
                'woocommerce_fb_settings',
                'wcap_fb_verify_token'
            );
        }

        function wcap_fb_description(){
            _e( 'Configure the plugin to send notifications to Facebook Messenger using the settings below. Please refer the <a href="https://www.tychesoftwares.com/docs/docs/abandoned-cart-pro-for-woocommerce/send-abandoned-cart-reminder-notifications-using-facebook-messenger" target="_blank">following documentation</a> to complete the setup.', 'woocommerce-ac' );
        }

        function wcap_fb_checkbox_callback( $args ) {

            $checkbox_value = get_option( $args[2] );
        
            if  (isset( $checkbox_value ) &&  $checkbox_value == "" ) {
                $checkbox_value = 'off';
            }
        
            $html = "<input type='checkbox' id='$args[2]' name='$args[2]' value='on' " . checked( 'on', $checkbox_value, false ) . "/>";
        
            $html .= '<label for="$args[2]"> ' . $args[0] . '</label>';
            echo $html;
        }

        function wcap_fb_text_callback( $args ) {

            $saved_value = get_option( $args[2] );
        
            $html = "<input type='text' id='$args[2]' name='$args[2]' value='$saved_value'  />";
        
            $html .= '<label for="$args[2]"> ' . $args[0] . '</label>';
            echo $html;
        }

        function wcap_fb_dropdown_callback( $args ) {

            $selected_value = get_option( $args[2] );
            $selected = '';

            $html = "<select name='$args[2]' id='$args[2]'>";

            foreach ( $args[3] as $key => $value ) {
                $selected = $selected_value === $key ? 'selected="selected"' : '';
                $html .= "<option value='$key' " . $selected . ">$value</option>";
            }

            $html .= "</select>";

            $html .= '<label for="$args[2]"> ' . $args[0] . '</label>';
            echo $html;
        }

        public static function wcap_fb_whitelist() {

            $list = WCAP_FB_Domain_Whitelisting::get_whitelisted_domains();

            ?>
                <style>
                .wcap_whitelist {
                    margin-bottom: 05px;
                    display: inline-block;
                    padding: 5px;
                    background: #008000;
                    margin-right: 5px;
                    border-radius: 10px;
                    color: white;
                }
                </style>
                
                <h3><?php _e( 'Domains Whitelisted for the page mentioned above:', 'woocommerce-ac' );?></h3>

                <p><?php _e( 'The current domain shall be listed in the below list. Please note the domain will not get listed if it is not over https due to <a href="https://developers.facebook.com/docs/messenger-platform/reference/messenger-profile-api/domain-whitelisting#requirements" target="_blank">Facebook restrictions.</a>', 'woocommerce-ac' );?></p>

                <?php if ( $list != '' ) : ?>

                    <div class="wcap_whitelist_div">
                        <ul>

                        <?php foreach ( $list as $link ) : ?>
                            <li class="wcap_whitelist"><?php echo $link;?></li>
                        <?php endforeach; ?>

                        </ul>
                    </div>

                <?php endif; ?>
            <?php
        }

        public static function wcap_fb_webhook() {
            
            ?>
                <h3><?php _e( 'Webhook callback URL', 'woocommerce-ac' ); ?></h3>
                <p><?php _e( 'Your webhook callback URL is: ', 'woocommerce-ac' ); ?>
                    <u><?php echo get_home_url() . '/acpro-callback-webhook/';?></u>
                </p>

                <p><?php _e( 'This webhook needs to added to your <a href="https://www.tychesoftwares.com/docs/docs/abandoned-cart-pro-for-woocommerce/send-abandoned-cart-reminder-notifications-using-facebook-messenger#attachment_2719" target="_blank">Facebook Developer App</a> for the checkbox to appear on site.', 'woocommerce-ac' );?></p>
            <?php
        }
    }
}

return new WCAP_FB_Admin();