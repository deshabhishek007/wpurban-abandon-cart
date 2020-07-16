<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * Class for Whitelisting Domains and Getting the domains
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/FB-Messenger
 * @category Modules
 * @since    7.10.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;   //Exit if accessed directly.
}

if ( !class_exists( 'WCAP_FB_Domain_Whitelisting' ) ) {

    /**
     * Domain Whitelisting class
     */
    class WCAP_FB_Domain_Whitelisting {
        
        function __construct() {

            add_action( 'admin_head', array( &$this, 'wcap_fb_whitelist_handler' ) );
        }

        public function wcap_fb_whitelist_handler() {

            if ( isset( $_GET['wcap_section']) && $_GET['wcap_section'] === 'wcap_fb_settings' ) {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL,"https://graph.facebook.com/v3.0/me/thread_settings?fields=whitelisted_domains&access_token=" . WCAP_FB_PAGE_TOKEN );
                curl_setopt($ch, CURLOPT_POST, 0);

                // receive server response ...
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $server_output = curl_exec ($ch);

                curl_close ($ch);

                $server_output_json = json_decode( $server_output );

                if ( isset( $server_output_json->data ) && 
                     is_array( $server_output_json->data ) ) {

                    $whitelisted_urls = array();

                    if ( count( $server_output_json->data ) > 0 ) {
                        $whitelisted_urls = $server_output_json->data[0]->whitelisted_domains;
                    }

                    if ( is_array( $whitelisted_urls ) ) {
                        if ( in_array( get_home_url(), $whitelisted_urls) || in_array( 'https://' . $_SERVER['HTTP_HOST'] , $whitelisted_urls) ) {
                            // everythin is ok
                        }else{
                            $this->whitelist_domain();
                        }
                    } // is_array
                }
            }
        }

        /**
         * Send the Site URL to facebook as Whitelisted URL
         */
        public function whitelist_domain( $action_type = 'add' ) {
            $ch = curl_init();

            $post_params = array(
                'setting_type' => 'domain_whitelisting', 
                'whitelisted_domains' => array( get_home_url(), 'https://' . $_SERVER['HTTP_HOST'] ), 
                'domain_action_type' => $action_type, 
            );

            $post_params = http_build_query( $post_params );

            curl_setopt($ch, CURLOPT_URL,"https://graph.facebook.com/v3.0/me/thread_settings?access_token=" . WCAP_FB_PAGE_TOKEN );
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params );

            // receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $server_output = curl_exec ($ch);

            curl_close ($ch);
        }

        /**
         * Get a list of all whitelisted domains
         */
        public static function get_whitelisted_domains(){
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL,"https://graph.facebook.com/v3.0/me/thread_settings?fields=whitelisted_domains&access_token=" . WCAP_FB_PAGE_TOKEN );
            curl_setopt($ch, CURLOPT_POST, 0);

            // receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $server_output = curl_exec ($ch);

            curl_close ($ch);

            $server_output_json = json_decode( $server_output );

            if ( isset( $server_output_json->data ) && 
                 is_array( $server_output_json->data ) && count( $server_output_json->data ) > 0 ) {
                $whitelisted_urls = $server_output_json->data[0]->whitelisted_domains;

                return $whitelisted_urls;
            }elseif ( isset( $server_output_json->error ) ) {
                return '';
            }
        }
    }
}

return new WCAP_FB_Domain_Whitelisting();