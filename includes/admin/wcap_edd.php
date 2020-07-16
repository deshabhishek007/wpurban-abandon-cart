<?php
/**
 * It will handle the license activate & deactivate funtionality.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/License
 * @since   5.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_EDD' ) ) {
    /**
     * It will handle the license activate & deactivate funtionality.
     */
    class Wcap_EDD{

        /**
         * It will register the license setting.
         * @since 2.3.1
         */
        public static function wcap_edd_ac_register_option() {
            // creates our settings in the options table
            register_setting( 'edd_sample_license', 'edd_sample_license_key_ac_woo', array( 'Wcap_EDD', 'wcap_edd_sanitize_license' ) );
        }

        /**
         * It will sanitize the license key entred by the admin.
         * @param string $new Licese key
         * @return string $new Licese key
         * @since 2.3.1
         */
        public static function wcap_edd_sanitize_license( $new ) {
            $old = get_option( 'edd_sample_license_key_ac_woo' );
            if ( $old && $old != $new ) {
                delete_option( 'edd_sample_license_status_ac_woo' ); // new license has been entered, so must reactivate
            }
            return $new;
        }

        /**
         * It will activate the licese key on our server.
         * @since 2.3.1
         */
        public static function wcap_edd_ac_activate_license() {
            // listen for our activate button to be clicked
            if ( isset( $_POST['edd_ac_license_activate'] ) ) {
                // run a quick security check
                if ( ! check_admin_referer( 'edd_sample_nonce', 'edd_sample_nonce' ) )
                    return; // get out if we didn't click the Activate button
                // retrieve the license from the database
                $license = trim( get_option( 'edd_sample_license_key_ac_woo' ) );
                // data to send in our API request
                $api_params = array(
                        'edd_action'=> 'activate_license',
                        'license'   => $license,
                        'item_name' => urlencode( EDD_SL_ITEM_NAME_AC_WOO ) // the name of our product in EDD
                );
                // Call the custom API.
                $response = wp_remote_get( add_query_arg( $api_params, EDD_SL_STORE_URL_AC_WOO ), array( 'timeout' => 15, 'sslverify' => false ) );
                // make sure the response came back okay
                if ( is_wp_error( $response ) )
                    return false;
                // decode the license data
                $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                // $license_data->license will be either "active" or "inactive"
                update_option( 'edd_sample_license_status_ac_woo', $license_data->license );
            }
        }

        /**
         * It will deactivate a license key.
         * This will descrease the site count.
         * @since 2.3.1
         */
        public static    function wcap_edd_ac_deactivate_license() {
            // listen for our activate button to be clicked
            if ( isset( $_POST['edd_ac_license_deactivate'] ) ) {
                // run a quick security check
                if ( ! check_admin_referer( 'edd_sample_nonce', 'edd_sample_nonce' ) )
                    return; // get out if we didn't click the Activate button
                // retrieve the license from the database
                $license = trim( get_option( 'edd_sample_license_key_ac_woo' ) );
                // data to send in our API request
                $api_params = array(
                        'edd_action'=> 'deactivate_license',
                        'license'   => $license,
                        'item_name' => urlencode( EDD_SL_ITEM_NAME_AC_WOO ) // the name of our product in EDD
                );
                // Call the custom API.
                $response = wp_remote_get( add_query_arg( $api_params, EDD_SL_STORE_URL_AC_WOO ), array( 'timeout' => 15, 'sslverify' => false ) );
                // make sure the response came back okay
                if ( is_wp_error( $response ) )
                    return false;
                // decode the license data
                $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                // $license_data->license will be either "deactivated" or "failed"
                if ( $license_data->license == 'deactivated' )
                    delete_option( 'edd_sample_license_status_ac_woo' );
            }
        }

        /**
         * This illustrates how to check if a license key is still valid the updater does this for you, so this is only needed if you want to do something custom.
         * @since 2.3.1
         */
        public static    function edd_sample_check_license() {
            global $wp_version;
            $license = trim( get_option( 'edd_sample_license_key_ac_woo' ) );
            $api_params = array(
                    'edd_action' => 'check_license',
                    'license'    => $license,
                    'item_name'  => urlencode( EDD_SL_ITEM_NAME_AC_WOO )
            );
            // Call the custom API.
            $response = wp_remote_get( add_query_arg( $api_params, EDD_SL_STORE_URL_AC_WOO ), array( 'timeout' => 15, 'sslverify' => false ) );
            if ( is_wp_error( $response ) )
                return false;
            $license_data = json_decode( wp_remote_retrieve_body( $response ) );
            if ( $license_data->license == 'valid' ) {
                echo 'valid';
                exit;
                // this license is still valid
            } else {
                echo 'invalid';
                exit;
                // this license is no longer valid
            }
        }

        /**
         * This will fetch the current license status.
         * @since 7.10.0
         */
        public static function wcap_edd_get_license_status() {
            return get_option( 'edd_sample_license_status_ac_woo' );
        }


    }
}
