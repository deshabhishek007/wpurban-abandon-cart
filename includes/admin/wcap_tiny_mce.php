<?php
/**
 * It will add the necessary action and filter for the Email template editor.
 * @author   Tyche Softwares.
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Template
 * @since 5.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Tiny_Mce' ) ) {
    /**
     * It will add the necessary action and filter for the Email template editor.
     */
    class Wcap_Tiny_Mce{

        /**
         * It will add the filters for adding the new buttons in the email template editor.
         * 
         * @hook admin_init
         * @since 3.0
         */
        public static function wcap_add_tiny_mce_button_and_plugin() {
            
            /**
             * Only hook up these filters if we're in the admin panel, and the current user has permission
             * to edit posts and pages.
             */
            if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
                return;
            }
            if ( !isset( $_GET['page'] ) || $_GET['page'] != "woocommerce_ac_page" ) {
                return;
            }
            if ( 'true' == get_user_option( 'rich_editing' ) ) {
                remove_filter( 'the_excerpt', 'wpautop' );

                add_filter( 'tiny_mce_before_init', array( 'Wcap_Tiny_Mce', 'wcap_format_tiny_MCE' ) );
                add_filter( 'mce_buttons',          array( 'Wcap_Tiny_Mce', 'wcap_filter_mce_button' ) );
                add_filter( 'mce_external_plugins', array( 'Wcap_Tiny_Mce', 'wcap_filter_mce_plugin' ) );
            }
            if ( isset( $_GET['page'] ) && 'woocommerce_ac_page' == $_GET['page'] ){
                if( session_id() === '' ){
                    //session has not started
                    if ( is_plugin_active( 'agilecrm/index.php' ) ) {
                        @session_start();
                    } else {
                        session_start();
                    }
                }
            }
        }

        /**
         * It will add the necessary fields for the Template editor
         * @hook tiny_mce_before_init
         * @param array $in List of contain field
         * @return array $in Contain all the fields
         * @since 3.0
         */
        public static function wcap_format_tiny_MCE( $in ) {
            add_editor_style();
            $in['force_root_block']             = false;
            $in['valid_children']               = '+body[style]';
            $in['remove_linebreaks']            = false;
            $in['gecko_spellcheck']             = false;
            $in['keep_styles']                  = true;
            $in['accessibility_focus']          = true;
            $in['tabfocus_elements']            = 'major-publishing-actions';
            $in['media_strict']                 = false;
            $in['paste_remove_styles']          = false;
            $in['paste_remove_spans']           = false;
            $in['paste_strip_class_attributes'] = 'none';
            $in['paste_text_use_dialog']        = true;
            $in['wpeditimage_disable_captions'] = true;
            $in['wpautop']                      = false;
            $in['apply_source_formatting']      = true;
            $in['cleanup']                      = true;
            $in['convert_newlines_to_brs']      = FALSE;
            $in['fullpage_default_xml_pi']      = false;
            $in['convert_urls']                 = false;
            // Do not remove redundant BR tags
            $in['remove_redundant_brs']         = false;

            return $in;
        }

        /**
         * It will add new button in the template editor.
         * @hook mce_buttons
         * @param array $buttons List of button
         * @return array $buttons List of button
         * @since 3.0
         */
        public static function wcap_filter_mce_button( $buttons ) {
            // add a separation before our button, here our button's id is &quot;mygallery_button&quot;
            array_push( $buttons, 'abandoncart_pro', '|' );
            array_push( $buttons, 'abandoncart_pro_css', '|' );
            return $buttons;
        }

        /**
         * It will add the action for new added button in the template editor.
         * @hook mce_external_plugins
         * @param array $plugins List attched action
         * @return array $plugins List of attached action
         * @since 3.0
         */
        public static function wcap_filter_mce_plugin( $plugins ) {

            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            // this plugin file will work the magic of our button
            $plugins['abandoncart_pro']     = WCAP_PLUGIN_URL . '/assets/js/admin/abandoncart_plugin_button' . $suffix . '.js';
            $plugins['abandoncart_pro_css'] = WCAP_PLUGIN_URL . '/assets/js/admin/abandoncart_plugin_button_css' . $suffix . '.js';
            return $plugins;
        }
    }
}
