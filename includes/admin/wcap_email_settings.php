<?php
/**
 * It will display all the settings of the plugin.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Setting
 * @since   5.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Email_Settings' ) ) {
    /**
     * It will display all the settings of the plugin.
     */
    class Wcap_Email_Settings{

        /**
         * It will display the all settings sections and all the fields for it.
         * It will show the settings using the WordPress settings api.
         * @since 5.0
         * @todo Change the function, class name & description.
         */
        public static function wcap_display_email_setting( ) {
            
            ?>
            <p><?php _e( 'Change settings for sending email notifications to Customers, to Admin, Tracking Coupons etc.', 'woocommerce-ac' ); ?></p>
            <div id="wcap_content">

            <?php
                $wcap_general_settings_class = $wcap_license = $wcap_fb_settings = $wcap_sms_settings = $wcap_add_to_cart = $section = "";
                
                $section = isset( $_GET[ 'wcap_section' ] ) ? $_GET[ 'wcap_section' ] : '';

                switch( $section ) {
                    
                    case 'wcap_general_settings':
                    case '':
                    default:
                        $wcap_general_settings_class = "current";
                        break;
                    case 'wcap_cron_settings':
                        $wcap_cron_setting = "current";
                        break;
                    case 'wcap_block_settings':
                        $wcap_block_settings = "current";
                        break;
                    case 'wcap_atc_settings':
                        $wcap_add_to_cart = "current";
                        break;
                    case 'wcap_license_settings':
                        $wcap_license = 'current';
                        break;
                    case 'wcap_fb_settings':
                        $wcap_fb_settings = 'current';
                        break;
                    case 'wcap_sms_settings':
                        $wcap_sms_settings = 'current';
                        break;
                }
                ?>
                <ul class="subsubsub" id="wcap_general_settings_list">
                    <li>
                        <a href="admin.php?page=woocommerce_ac_page&action=emailsettings&wcap_section=wcap_general_settings" class="<?php echo $wcap_general_settings_class; ?>"><?php _e( 'General', 'woocommerce-ac' );?> </a> |
                    </li>
                    <li>
                        <a href="admin.php?page=woocommerce_ac_page&action=emailsettings&wcap_section=wcap_atc_settings" class="<?php echo $wcap_add_to_cart; ?>"><?php _e( 'Add To Cart Popup Editor', 'woocommerce-ac' );?> </a> |
                    </li>
                    <li>
                        <a href="admin.php?page=woocommerce_ac_page&action=emailsettings&wcap_section=wcap_fb_settings" class="<?php echo $wcap_fb_settings; ?>"><?php _e( 'Facebook Messenger', 'woocommerce-ac' );?> </a> | 
                    </li>
                    <li>
                        <a href="admin.php?page=woocommerce_ac_page&action=emailsettings&wcap_section=wcap_sms_settings" class="<?php echo $wcap_sms_settings; ?>"><?php _e( 'SMS', 'woocommerce-ac' );?> </a> | 
                    </li>                    
                    <li>                        
                        <a href="admin.php?page=woocommerce_ac_page&action=emailsettings&wcap_section=wcap_license_settings" class="<?php echo $wcap_license; ?>"><?php _e( 'License', 'woocommerce-ac' );?> </a>                     
                    </li>                    
                </ul>
                <br class="clear">
                <?php
                if ( $section == 'wcap_general_settings' || $section == '' ) {
                ?>
                    <form method="post" action="options.php">
                        <?php settings_fields     ( 'woocommerce_ac_settings' ); ?>
                        <?php do_settings_sections( 'woocommerce_ac_page' ); ?>
                        <?php settings_errors(); ?>
                        <?php submit_button(); ?>
                    </form>
                    <?php
                } else if ( $section == 'wcap_license_settings' ) {
                ?>
                    <form method="post" action="options.php">
                        <?php settings_fields     ( 'woocommerce_ac_license' ); ?>
                        <?php do_settings_sections( 'woocommerce_ac_license_page' ); ?>
                        <?php settings_errors(); ?>
                        <?php submit_button(); ?>
                    </form>
                    <?php
                }else if( $section == 'wcap_fb_settings' ) {
                    WCAP_FB_Admin::wcap_fb_settings();
                } else if( $section == 'wcap_sms_settings' ) {
                    Wcap_SMS_settings::wcap_sms_settings();
                }else if ( $section == 'wcap_atc_settings' ) {
                    /** 
                     * @since 6.0
                     * @comment add to cart popup modal setting.
                     */
                    if ( isset( $_POST['atc_settings_frm'] ) && 'save' == $_POST['atc_settings_frm'] ) {
                        Wcap_Add_Cart_Popup_Modal::wcap_add_to_cart_popup_save_settings( );
                        Wcap_Display_Notices::wcap_add_to_cart_popup_save_success();
                    }
                    wp_enqueue_script( 'wcap_atc_color_picker',   WCAP_PLUGIN_URL . '/assets/js/admin/wcap_atc_color_picker.min.js' );

                    wp_localize_script( 'wcap_atc_color_picker', 'wcap_atc_color_picker_params', array(
                                        'wcap_atc_head'  => get_option('wcap_heading_section_text_email'),
                                        'wcap_atc_text'  => get_option('wcap_text_section_text'),
                                        'wcap_atc_email_place'  => get_option('wcap_email_placeholder_section_input_text'),
                                        'wcap_atc_button'  => get_option('wcap_button_section_input_text'),
                                        'wcap_atc_button_bg_color'  => get_option('wcap_button_color_picker'),
                                        'wcap_atc_button_text_color'=> get_option('wcap_button_text_color_picker'),
                                        'wcap_atc_popup_text_color' => get_option('wcap_popup_text_color_picker'),
                                        'wcap_atc_popup_heading_color' => get_option('wcap_popup_heading_color_picker'),
                                        'wcap_atc_non_mandatory_input_text' => get_option('wcap_non_mandatory_text'),
                    ) );
                    
                    wp_enqueue_script( 'wcap_atc_vue_field_data', WCAP_PLUGIN_URL . '/assets/js/admin/wcap_atc_vue_field_data.min.js' );
                    wp_localize_script( 'wcap_atc_vue_field_data', 'wcap_vue_field_data_params', array(
                                        'wcap_atc_head'  => get_option('wcap_heading_section_text_email'),
                                        'wcap_atc_text'  => get_option('wcap_text_section_text'),
                                        'wcap_atc_email_place'  => get_option('wcap_email_placeholder_section_input_text'),
                                        'wcap_atc_button'  => get_option('wcap_button_section_input_text'),
                                        'wcap_atc_button_bg_color'  => get_option('wcap_button_color_picker'),
                                        'wcap_atc_button_text_color'=> get_option('wcap_button_text_color_picker'),
                                        'wcap_atc_popup_text_color' => get_option('wcap_popup_text_color_picker'),
                                        'wcap_atc_popup_heading_color' => get_option('wcap_popup_heading_color_picker'),
                                        'wcap_atc_non_mandatory_input_text' => get_option('wcap_non_mandatory_text'),
                    ) );

                    ?>
                    <form method="post" action="admin.php?page=woocommerce_ac_page&action=emailsettings&wcap_section=wcap_atc_settings">
                        <input type="hidden" name="atc_settings_frm" value="save">
                        <?php Wcap_Add_Cart_Popup_Modal::wcap_add_to_cart_popup_settings(); ?>
                    </form>
                    <?php
                }
                ?>
            </div>
        <?php
        }
    }
}