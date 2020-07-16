<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * Load Aelia Currency Switcher
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/FB-Messenger
 * @category Modules
 * @since    7.11.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;   //Exit if accessed directly.
}

if ( !class_exists( 'WCAP_Aelia_Switcher' ) ) {

    /**
     * Aelia Currency Switcher class
     */
    class WCAP_Aelia_Switcher {
        
        function __construct() {
            add_action ( 'acfac_add_data'          , array( &$this, 'acfac_add_abandoned_currency' ),10, 1 );
            add_filter ( 'acfac_change_currency'   , array( &$this, 'acfac_change_abandoned_currency' ),10, 4 );
            add_filter ( 'acfac_get_cart_currency' , array( &$this, 'acfac_get_abandoned_currency' ),10, 2 );
            add_action ( 'woocommerce_init'        , array( &$this, 'acfac_set_currency_from_recovered_cart'), 0);
        }

        /** 
         * This function will insert the selected Currency of the Aelia plugin
         */
        function acfac_add_abandoned_currency ( $acfac_abandoned_id ) {
            global $wpdb;

            $acfac_currencey = get_woocommerce_currency(); 
            $acfc_table_name = $wpdb->prefix . "abandoned_cart_aelia_currency";

            $acfac_get_currency_for_cart     = "SELECT  acfac_currency FROM $acfc_table_name WHERE abandoned_cart_id = $acfac_abandoned_id";
            $acfac_get_currency_for_cart_res = $wpdb->get_results( $acfac_get_currency_for_cart );

            if ( !empty( $acfac_get_currency_for_cart_res ) ){
                $wpdb->update( $acfc_table_name,
                        array( 'acfac_currency'    => $acfac_currencey ),
                        array( 'abandoned_cart_id' => $acfac_abandoned_id )
                );
            }else{

                $wpdb->insert( $acfc_table_name, array(
                    'abandoned_cart_id' => $acfac_abandoned_id,
                    'acfac_currency'    => $acfac_currencey
                ));
            }
        }

        /** 
         * This function will change the currency symbol on the order details, email & abandoned orders tab.
         */
        function acfac_change_abandoned_currency ( $acfac_default_currency, $acfac_abandoned_id,  $abandoned_total, $is_ajax ) {
            
            global $wpdb;
            $acfc_table_name                 = $wpdb->prefix . "abandoned_cart_aelia_currency";

            $acfac_get_currency_for_cart     = "SELECT acfac_currency FROM $acfc_table_name WHERE abandoned_cart_id = $acfac_abandoned_id ORDER BY `id` desc limit 1";
            $acfac_get_currency_for_cart_res = $wpdb->get_results( $acfac_get_currency_for_cart );

            $acfac_aelia_settings = get_option('wc_aelia_currency_switcher');

            if ( count( $acfac_get_currency_for_cart_res ) > 0 ){
                $aelia_cur = $acfac_get_currency_for_cart_res[0]->acfac_currency;
            }else{
                $aelia_cur = get_option('woocommerce_currency');
            }

            $acfac_currency_position = $acfac_aelia_settings ['exchange_rates'][$aelia_cur]['symbol_position'];
            $acfac_format = '%1$s%2$s';
            switch ( $acfac_currency_position ) {
                case 'left' :
                    $acfac_format = '%1$s%2$s';
                break;
                case 'right' :
                    $acfac_format = '%2$s%1$s';
                break;
                case 'left_space' :
                    $acfac_format = '%1$s&nbsp;%2$s';
                break;
                case 'right_space' :
                    $acfac_format = '%2$s&nbsp;%1$s';
                break;
            }
            if ( count( $acfac_get_currency_for_cart_res ) > 0 ) {

                $aelia_cur = $acfac_get_currency_for_cart_res[0]->acfac_currency;

                $acfac_change_currency = array(
                    'ex_tax_label'       => false,
                    'currency'           => $aelia_cur,
                    'decimal_separator'  => $acfac_aelia_settings ['exchange_rates'][$aelia_cur]['decimal_separator'],
                    'thousand_separator' => $acfac_aelia_settings ['exchange_rates'][$aelia_cur]['thousand_separator'],
                    'decimals'           => $acfac_aelia_settings ['exchange_rates'][$aelia_cur]['decimals'],
                    'price_format'       => $acfac_format
                ) ;
                $acfac_default_currency = wc_price ( $abandoned_total, $acfac_change_currency );

            } else {
                $acfac_change_currency = array(
                    'ex_tax_label'       => false,
                    'currency'           => get_option('woocommerce_currency'),
                    'decimal_separator'  => $acfac_aelia_settings ['exchange_rates'][$aelia_cur]['decimal_separator'],
                    'thousand_separator' => $acfac_aelia_settings ['exchange_rates'][$aelia_cur]['thousand_separator'],
                    'decimals'           => $acfac_aelia_settings ['exchange_rates'][$aelia_cur]['decimals'],
                    'price_format'       => $acfac_format
                ) ;
                $acfac_default_currency = wc_price ( $abandoned_total, $acfac_change_currency );
            }
            return $acfac_default_currency;
        }

        /**
         * It will return the abandoned cart currency.
         */
        function acfac_get_abandoned_currency ( $acfac_default_currency, $acfac_abandoned_id ) {

            global $wpdb;
            $acfc_table_name                 = $wpdb->prefix . "abandoned_cart_aelia_currency";
            $acfac_get_currency_for_cart     = "SELECT acfac_currency FROM $acfc_table_name WHERE abandoned_cart_id = $acfac_abandoned_id ORDER BY `id` desc limit 1";
            $acfac_get_currency_for_cart_res = $wpdb->get_results( $acfac_get_currency_for_cart );
            if ( count( $acfac_get_currency_for_cart_res ) > 0 ){
                $acfac_default_currency = $acfac_get_currency_for_cart_res[0]->acfac_currency;
                return $acfac_default_currency;
            }
                
            return $acfac_default_currency;
        }

        /**
         * This function will load the Client selected curency while client comes from the abandoned cart reminder emails.
         */
        function acfac_set_currency_from_recovered_cart() {
            // User explicitly selected a currency, we should not do anything
            if(!empty($_POST['aelia_cs_currency'])) {
                return;
            }

            // Only change the currency on the frontend
            if( !is_admin() || defined('DOING_AJAX') ) {
                // If the user comes from a “recover cart” link, take the currency from
                // the stored “abandoned cart” data
                $track_link = '';
                if ( isset( $_GET['wacp_action'] ) ){ 
                    $track_link = $_GET['wacp_action'];
                }
            
                if ( $track_link == 'track_links' ) {
                     
                    global $wpdb;
                    
                    $validate_server_string  = rawurldecode ( $_GET ['validate'] );
                    $validate_server_string = str_replace ( " " , "+", $validate_server_string);
                    $validate_encoded_string = $validate_server_string;
                    
                    $cryptKey    = get_option( 'ac_security_key' );
                    $link_decode = Wcap_Aes_Ctr::decrypt( $validate_encoded_string, $cryptKey, 256 );
                    
                    $email_sent_id   = 0;
                    
                    $sent_email_id_pos          = strpos( $link_decode, '&' );
                    $email_sent_id              = substr( $link_decode , 0, $sent_email_id_pos );
                    $_POST['aelia_cs_currency'] = WCAP_Aelia_Switcher::acfac_get_currency_of_abandoned_cart( $email_sent_id );
                }
            }
        }

        /** 
         * This function will give the currency of the selected abandoned cart
         */
        function acfac_get_currency_of_abandoned_cart( $abandoned_sent_id ) {

            global $wpdb;
            $wcap_email_sent_table_name       = $wpdb->prefix . "ac_sent_history"; 
            $acfac_get_abandoned_order_id     = "SELECT abandoned_order_id FROM $wcap_email_sent_table_name WHERE id = $abandoned_sent_id ";
            $acfac_get_abandoned_order_id_res = $wpdb->get_results( $acfac_get_abandoned_order_id );

            if ( !empty( $acfac_get_abandoned_order_id_res ) ){
                $acfac_abandoned_id = $acfac_get_abandoned_order_id_res[0]->abandoned_order_id;

                $acfc_table_name                 = $wpdb->prefix . "abandoned_cart_aelia_currency";
                $acfac_get_currency_for_cart     = "SELECT acfac_currency FROM $acfc_table_name WHERE abandoned_cart_id = $acfac_abandoned_id ORDER BY `id` desc limit 1";
                $acfac_get_currency_for_cart_res = $wpdb->get_results( $acfac_get_currency_for_cart );

                $selected_currency = $acfac_get_currency_for_cart_res[0]->acfac_currency;
            }
            return $selected_currency;
        }
    }
}

return new WCAP_Aelia_Switcher();