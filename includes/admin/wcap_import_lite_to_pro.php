<?php
/**
 * It will display all the data for the import of lite version to the pro version.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Import-Pro
 * @since 7.5
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Import_Lite_to_Pro' ) ) {
    /**
     * It will display all the data for the import of lite version to the pro version
     */
    class Wcap_Import_Lite_to_Pro {

        /**
         * It will display all the data for the import of lite version to the pro version
         * @since 7.5
         */
        public static function wcap_show_import_data () {

            ob_start();
            Wcap_Import_Lite_to_Pro::wcap_get_import_data();
        }
        /**
         * It will dispplay all the data.
         * @since 7.5
         */
        public static function wcap_get_import_data () {

            $wcap_version = Wcap_Common::wcap_get_version();
            ?>
            <div class="wrap about-wrap">
                <h2><?php printf( __( 'Welcome to Abandoned Cart Pro for WooCommerce v%s', 'woocommerce-ac' ), $wcap_version ); ?></h2>
            <?php
                Wcap_Import_Lite_to_Pro::wcap_display_information_of_lite_active();
                ?>
                 <div id = "wcap_import_yes_no" class = "wcap_import_yes_no" > 
                <?php
                    Wcap_Import_Lite_to_Pro::wcap_display_yes_button();
                    Wcap_Import_Lite_to_Pro::wcap_display_no_button();
                ?>
                </div>
            </div>
            <?php
        }


        /**
         * It will display the body of the lite to pro version import page.
         * @since 7.5
         */
        public static function wcap_display_information_of_lite_active () {
            ?>
            <div>
                
                <p><?php _e( 'We have noticed that you are using the Lite version of the Abandoned Cart plugin on your store. Thus, when activating the Pro version, you should choose if you want to import the Lite version data in Pro. We fully understand the importance of data captured in the Lite version of the plugin & hence would like to give you an option to choose what you want to do with that data.', 'woocommerce-ac' ); ?></p>
                
                <p><?php _e( 'You can import all your data by clicking on the <strong>Yes </strong> button below. In the next step, you will be asked to select what data you want to import before beginning the actual process of import.', 'woocommerce-ac' ); ?></p>
                
                <p><?php _e( 'If you don\'t wish to import the data from the Lite version of the plugin, then please click on the <strong>No </strong>button below. Selecting <strong>No </strong>will not import the data captured in the Lite version of the plugin into the Pro version.', 'woocommerce-ac' ); ?></p>
                
                <div id = "wcap_import_checkboxes" class = "wcap_import_checkboxes" style="display: none">

                <p><?php _e( 'You can choose what data you want to import from Lite plugin using the options below:', 'woocommerce-ac' ); ?></p>

                <table>
                    <tr>
                        <td>
                            <?php _e( 'Abandoned Carts:', 'woocommerce-ac' ); ?>         
                        </td>
                        <td>
                          &nbsp<input type="checkbox" name="wcap_abandoned_cart_import" value = "wcap_abandoned_cart_import" id = "wcap_abandoned_cart_import" checked>  
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <?php _e( 'Settings:', 'woocommerce-ac' ); ?>         
                        </td>
                        <td>
                            &nbsp<input type="checkbox" name="wcap_settings_import" id = "wcap_settings_import" value ="wcap_settings_import" checked>  
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php _e( 'Email templates:', 'woocommerce-ac' ); ?>        
                        </td>
                        <td>
                            &nbsp<input type="checkbox" name="wcap_email_template_import" value ="wcap_email_template_import" id = "wcap_email_template_import" checked>  
                        </td>
                    </tr>
                </table>
                <h5> 
                <?php _e( '<strong>Note: Note: Once the import is complete, we will not send any abandoned cart reminder emails from Pro version to the carts which were imported from the Lite plugin. Please activate the email templates to ensure that reminder emails are sent to carts captured in the Pro version.</strong>', 'woocommerce-ac' ); ?> 

                 </h5>
                <?php
                    Wcap_Import_Lite_to_Pro::wcap_display_import_button();
                    Wcap_Import_Lite_to_Pro::wcap_display_no_button();
                ?>
                </div>
            </div>
            <?php
        }

        /**
         * It will display Yes button on the page.
         * @since 7.5
         */
        public static function wcap_display_yes_button () {
            ?>
            <input type="submit" name="submit" id="wcap-import-yes" class="button button-primary wcap-import-yes" value="Yes"  />
            <?php
        }
        /**
         * It will display No button on the page.
         * @since 7.5
         */
        public static function wcap_display_no_button () {
            ?>
            <input type="submit" name="submit" id="wcap-import-no" class="button button-primary wcap-import-no" value="No"  />
            <?php
        }
        /**
         * It will display Import Data button on the page.
         * @since 7.5
         */
        public static function wcap_display_import_button () {
            ?>
            <input type="submit" name="submit" id="wcap-import-now" class="button button-primary wcap-import-now" value="Import data"  />
            <?php
        }

        /**
         * When first time pro version activated along with the lite version then this function will redirect to the new page.
         * @hook admin_init
         * @since 7.5
         */
        public static function wcap_admin_init () {

            $woocommerce_ac_plugin_version = get_option( 'woocommerce_ac_db_version' );
            $wcap_is_import_page_displayed = get_option( 'wcap_import_page_displayed' );

            $wcap_is_lite_data_imported    = get_option( 'wcap_lite_data_imported' );

            if (  ( $wcap_is_import_page_displayed != 'yes' || '' != $wcap_is_import_page_displayed ) && false === $wcap_is_import_page_displayed  ) {
                
                update_option( 'wcap_import_page_displayed', 'yes' );    
                wp_safe_redirect( admin_url( 'admin.php?page=wcap-update' ) );
                exit;
            }

            if ( !isset( $_GET ['page'] ) || ( isset( $_GET ['page'] ) && 'wcap-update' != $_GET ['page'] ) ) {

                if ( $wcap_is_import_page_displayed == 'yes' && in_array( 'woocommerce-abandoned-cart/wpurban-ac.php', (array) get_option( 'active_plugins', array() ) ) ) {

                    $wcap_lite_plugin_path =   ( dirname( dirname ( WCAP_PLUGIN_FILE ) ) ) . '/woocommerce-abandoned-cart/wpurban-ac.php';
                    deactivate_plugins( $wcap_lite_plugin_path );
                }
            }
        }

        /**
         * It will create the admin sub menu for the import data.
         * @hook admin_menu
         * @since 7.5
         */
        public static function wcap_admin_menus() {
            
            $woocommerce_ac_plugin_version = get_option( 'woocommerce_ac_db_version' );
            
            if ( empty( $_GET['page'] ) ) {
                return;
            }

            $wcap_update_page_name   = __( 'About Abandoned Cart Pro for WooCommerce', 'woocommerce-ac' );
            $wcap_welcome_page_title = __( 'Welcome to Abandoned Cart Pro for WooCommerce', 'woocommerce-ac' );
            
            add_dashboard_page( $wcap_welcome_page_title, '', 'manage_options', 'wcap-update', array( 'Wcap_Import_Lite_to_Pro' , 'wcap_show_import_data') );
        }
        
    }
}
