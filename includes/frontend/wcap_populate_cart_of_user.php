<?php
/**
 * It will decrypt url and redirect the customer to cart or checkpout page.
 * Also it will also handle the email open and unsubscribe data.
 * Also, if the url contain the email address then it will store email in the session. 
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Frontend/Cart-Populate
 * @since 5.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Populate_Cart_Of_User' ) ) {
    /**
     * It will decrypt url and redirect the customer to cart or checkpout page.
     * Also it will also handle the email open and unsubscribe data.
     * Also, if the url contain the email address then it will store email in the session
     */

    class Wcap_Populate_Cart_Of_User{
        /**
         * It will decrypt url and redirect the customer to cart or checkpout page.
         * @hook template_include
         * @param string $template The path of the template to include.
         * @return string $template The path of the template to include.
         * @globals mixed $wpdb
         * @globals mixed $woocommerce
         * @since 5.0
         */
        public static function wcap_email_track_links( $template ) {

            $track_link = '';
            if ( isset( $_GET['wacp_action'] ) ){
                $track_link = $_GET['wacp_action'];
            }

            if ( $track_link == 'track_links' || $track_link == 'checkout_link' ) {

                global $wpdb, $woocommerce;
                $cryptKey           = get_option( 'ac_security_key' );
                $validate_server_string     = rawurldecode( $_GET['validate'] );
                $validate_server_string     = str_replace( " " , "+", $validate_server_string );
                $validate_encoded_string    = $validate_server_string;
                $link_decode = Wcap_Aes_Ctr::decrypt( $validate_encoded_string, $cryptKey, 256 );

                if ( isset( $_GET['c'] ) ) { // it will check if coupon code parameter exists or not
                    $decrypt_coupon_code    = rawurldecode( $_GET['c'] );
                    $decrypt_coupon_code    = str_replace( " " , "+", $decrypt_coupon_code );
                    $decode_coupon_code     = Wcap_Aes_Ctr::decrypt( $decrypt_coupon_code, $cryptKey, 256 );

                    wcap_set_cart_session( 'wcap_c', $decode_coupon_code ); // we need to set in session coz we directly apply coupon
                    set_transient( 'wcap_c', $decode_coupon_code, 5 );
                } else {
                    $decode_coupon_code     = '';
                }
                
                if ( 'track_links' == $track_link ) {
                    $email_sent_id             = 0;
                    $sent_email_id_pos         = strpos( $link_decode, '&' );
                    $email_sent_id             = substr( $link_decode , 0, $sent_email_id_pos );

                    wcap_set_cart_session( 'wcap_email_sent_id', $email_sent_id );
                    set_transient( 'wcap_email_sent_id', $email_sent_id, 5 );
                    $get_ac_id_query           = "SELECT abandoned_order_id FROM `" . WCAP_EMAIL_SENT_HISTORY_TABLE."` WHERE id = %d";

                    $get_ac_id_results         = $wpdb->get_results( $wpdb->prepare( $get_ac_id_query, $email_sent_id ) );
                    $abandoned_id = $get_ac_id_results[0]->abandoned_order_id;
                } else if ( 'checkout_link' == $track_link ) {
                    $abandoned_id      = 0;
                    $abandoned_id_pos  = strpos( $link_decode, '&' );
                    $abandoned_id      = substr( $link_decode , 0, $abandoned_id_pos );
                }
                $url_pos                   = strpos( $link_decode, '=' );
                $url_pos                   = $url_pos + 1;
                $url                       = substr( $link_decode, $url_pos );
                $url = apply_filters( 'wcap_modify_decrypted_url', $url, $decode_coupon_code );
                $get_user_results          = array();
                if( $abandoned_id > 0 ) {
                    $get_user_id_query     = "SELECT user_id FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE id = %d";
                    $get_user_results      = $wpdb->get_results( $wpdb->prepare( $get_user_id_query, $abandoned_id ) );

                    wcap_set_cart_session( 'wcap_abandoned_id', $abandoned_id );
                    set_transient( 'wcap_abandoned_id', $abandoned_id, 5 );
                }
                $user_id                   = 0;
                if ( isset( $get_user_results ) && count( $get_user_results ) > 0 ) {
                    $user_id = $get_user_results[0]->user_id;
                }
                if ( $user_id == 0 ) {
                    echo "Link expired. Redirecting you to the shop..";
                    $shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
                    ?>
                    <br/>
                    <html>
                    <head>
                        <title>Redirecting...</title>
                        <meta http-equiv="refresh" content="5;URL=<?php echo $shop_page_url; ?>">
                    </head>
                    <br />
                    <body>
                        You are being automatically redirected to a home location of the website.<br />
                        If your browser does not redirect you in 5 seconds, or you do
                        not wish to wait, <a href="<?php echo $shop_page_url; ?>">click here</a>.
                    </body>
                    </html>
                     <?php
                } else {

                    if ( $user_id >= 63000000 ) {
                        $query_guest   = "SELECT * from `". WCAP_GUEST_CART_HISTORY_TABLE."` WHERE id = %d";
                        $results_guest = $wpdb->get_results( $wpdb->prepare( $query_guest, $user_id ) );
                        $query_cart    = "SELECT recovered_cart FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE user_id = %d";
                        $results       = $wpdb->get_results( $wpdb->prepare( $query_cart, $user_id ) );
                        if ( $results_guest  && $results[0]->recovered_cart == '0' ) {
                            wcap_set_cart_session( 'wcap_guest_first_name', $results_guest[0]->billing_first_name );
                            wcap_set_cart_session( 'wcap_guest_last_name',  $results_guest[0]->billing_last_name );
                            wcap_set_cart_session( 'wcap_guest_email',      $results_guest[0]->email_id );
                            wcap_set_cart_session( 'wcap_guest_phone',      $results_guest[0]->phone );
                            wcap_set_cart_session( 'wcap_user_id',          $user_id );
                        } else {
                            wp_redirect( get_permalink( wc_get_page_id( 'shop' ) ) );
                        }
                    }

                    if ( $user_id < "63000000" ) {
                        $user = wp_set_current_user( $user_id );
                        $query_guest = "SELECT * from `". WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE user_id = %d AND cart_ignored = '0' ";
                        $results     = $wpdb->get_results( $wpdb->prepare( $query_guest, $user_id ) );

                        $user_login  = $user->data->user_login;
                        wp_set_auth_cookie( $user_id );
                        $my_temp     = wc_load_persistent_cart( $user_login, $user );
                        if ( function_exists('icl_register_string') ) {
                            wcap_set_cart_session( 'wcap_selected_language', $results[0]->language );
                            set_transient( 'wcap_selected_language', $results[0]->language, 5 );
                        }
                        do_action( 'wp_login', $user_login, $user );
                        if ( isset( $sign_in ) && is_wp_error( $sign_in ) ) {
                            echo $sign_in->get_error_message();
                            exit;
                        }
                    } else{
                        $my_temp = Wcap_Populate_Cart_Of_User::wcap_load_guest_persistent_cart( $user_id );
                    }

                    do_action( 'wcap_recovery_link_accessed', $abandoned_id, 'email_link', $_GET[ 'validate' ] );
                    if ( isset( $email_sent_id ) && $email_sent_id > 0 && is_numeric( $email_sent_id ) && !preg_match( '/&wcap_manual_email=YES/', $link_decode ) ) {

                        $query = "INSERT INTO `" . WCAP_EMAIL_CLICKED_TABLE . "` ( email_sent_id, link_clicked, time_clicked )
                                 VALUES ( '" . $email_sent_id . "', '".$url."', '" . current_time( 'mysql' ) . "' )";
                        $wpdb->query( $query );
                        header( "Location: $url" );

                    } else if ( isset( $email_sent_id ) && $email_sent_id == 0 && is_numeric( $email_sent_id ) &&  preg_match( '/&wcap_manual_email=YES/', $link_decode ) ) {
                        header( "Location: $url" );
                    } else if ( 'checkout_link' == $track_link && $abandoned_id > 0 ) {
                        header( "Location: $url" );                        
                    }
                }
            } else {
                return $template;
            }
        }

        /**
         * It will populate the guest users cart in the WooComerce session.
         * @globals mixed $wpdb
         * @globals mixed $woocommerce
         * @since 5.0
         */
        public static function wcap_load_guest_persistent_cart() {
            global $woocommerce, $wpdb;

            $wcap_user_id = wcap_get_cart_session( 'wcap_user_id' );

            if( is_multisite() ) {
                $main_prefix = $wpdb->get_blog_prefix(1);
            }else {
                $main_prefix = $wpdb->prefix;
            }
            
            if ( $wcap_user_id != '' ) {
                $saved_cart_query = "SELECT * FROM `" . $main_prefix . "usermeta` WHERE user_id = %d AND meta_key = '_woocommerce_persistent_cart' ORDER BY umeta_id DESC LIMIT 1";
                $saved_cart_results = $wpdb->get_results( $wpdb->prepare( $saved_cart_query, $wcap_user_id ) );
                if ( count ($saved_cart_results) > 0 && isset( $saved_cart_results ) ) {
                    $saved_cart = json_decode( $saved_cart_results[0]->meta_value, true );
                }
            } else {
                $saved_cart = array();
            }
            $c = array();
            $cart_contents_total = $cart_contents_weight = $cart_contents_count = $cart_contents_tax = $total = $subtotal = $subtotal_ex_tax = $tax_total = 0;

            if ( count ($saved_cart) > 0 ){
                foreach ( $saved_cart as $key => $value ) {
                    if ( is_array( $value ) ) {
                        foreach ( $value as $a => $b ) {
                            $c['product_id']        = $b['product_id'];
                            $c['variation_id']      = $b['variation_id'];
                            $c['variation']         = $b['variation'];
                            $c['quantity']          = $b['quantity'];
                            $product_id             = $b['product_id'];
                            $c['data']              = wc_get_product($product_id);
                            $c['line_total']        = $b['line_total'];
                            $c['line_tax']          = $cart_contents_tax;
                            $c['line_subtotal']     = $b['line_subtotal'];
                            $c['line_subtotal_tax'] = $cart_contents_tax;
                            $value_new[$a]          = $c;
                            $cart_contents_total    = $b['line_subtotal'] + $cart_contents_total;
                            $cart_contents_count    = $cart_contents_count + $b['quantity'];
                            $total                  = $total + $b['line_total'];
                            $subtotal               = $subtotal + $b['line_subtotal'];
                            $subtotal_ex_tax        = $subtotal_ex_tax + $b['line_subtotal'];
                        }
                    }
                    $saved_cart_data[$key]      = $value_new;
                    $woocommerce_cart_hash      = $a;
                }
            }

            if ( $saved_cart ) {
                if ( empty( $woocommerce->session->cart ) || ! is_array( $woocommerce->session->cart ) || sizeof( $woocommerce->session->cart ) == 0 ) {
                    $woocommerce->session->cart                 = $saved_cart['cart'];
                    $woocommerce->session->cart_contents_total  = $cart_contents_total;
                    $woocommerce->session->cart_contents_weight = $cart_contents_weight;
                    $woocommerce->session->cart_contents_count  = $cart_contents_count;
                    $woocommerce->session->cart_contents_tax    = $cart_contents_tax;
                    $woocommerce->session->total                = $total;
                    $woocommerce->session->subtotal             = $subtotal;
                    $woocommerce->session->subtotal_ex_tax      = $subtotal_ex_tax;
                    $woocommerce->session->tax_total            = $tax_total;
                    $woocommerce->session->shipping_taxes       = array();
                    $woocommerce->session->taxes                = array();
                    $woocommerce->session->ac_customer          = array();
                    $woocommerce->cart->cart_contents           = $saved_cart_data['cart'];
                    $woocommerce->cart->cart_contents_total     = $cart_contents_total;
                    $woocommerce->cart->cart_contents_weight    = $cart_contents_weight;
                    $woocommerce->cart->cart_contents_count     = $cart_contents_count;
                    $woocommerce->cart->cart_contents_tax       = $cart_contents_tax;
                    $woocommerce->cart->total                   = $total;
                    $woocommerce->cart->subtotal                = $subtotal;
                    $woocommerce->cart->subtotal_ex_tax         = $subtotal_ex_tax;
                    $woocommerce->cart->tax_total               = $tax_total;
                }
            }
        }

        /**
         * It will handle the email open and unsubscribe the user from the cart.
         * @hook template_include
         * @param string $template The path of the template to include.
         * @return string $template The path of the template to include.
         * @globals mixed $wpdb
         * @globals mixed $woocommerce
         * @since 5.0
         */
        public static function wcap_email_track_open_and_unsubscribe( $args ) {
            global $wpdb;

            if ( isset( $_GET['wcap_track_email_opens'] ) && $_GET['wcap_track_email_opens'] == 'wcap_email_open' ) {
                $email_sent_id = $_GET['email_id'];
                
                if ( $email_sent_id > 0 && is_numeric( $email_sent_id ) ) {
                    $query = "INSERT INTO `" . WCAP_EMAIL_OPENED_TABLE . "` ( email_sent_id , time_opened )
                              VALUES ( '" . $email_sent_id . "' , '" . current_time( 'mysql' ) . "' )";
                    $wpdb->query( $query );
                }
                exit();
            } else if ( isset( $_GET['wcap_track_unsubscribe'] ) && $_GET['wcap_track_unsubscribe'] == 'wcap_unsubscribe' ) {
                $encoded_email_id              = rawurldecode ( $_GET['validate'] );
                $validate_email_id_string      = str_replace ( " " , "+", $encoded_email_id );
                $validate_email_address_string = '';
                $validate_email_id_decode      = 0;

                $cryptKey  = get_option( 'ac_security_key' );
                $validate_email_id_decode = Wcap_Aes_Ctr::decrypt( $validate_email_id_string, $cryptKey, 256 );

                if ( isset( $_GET['track_email_id'] ) ) {
                    $encoded_email_address         = rawurldecode ( $_GET['track_email_id'] );
                    $validate_email_address_string = str_replace ( " " , "+", $encoded_email_address );
                }

                $query_id      = "SELECT * FROM `" . WCAP_EMAIL_SENT_HISTORY_TABLE . "` WHERE id = %d ";
                $results_sent  = $wpdb->get_results ( $wpdb->prepare( $query_id, $validate_email_id_decode ) );
                $email_address = '';
                if ( isset( $results_sent[0] ) ) {
                    $email_address =  $results_sent[0]->sent_email_id;
                }
                if ( $validate_email_address_string == hash( 'sha256', $email_address ) ) {
                    $email_sent_id     = $validate_email_id_decode;
                    $get_ac_id_query   = "SELECT abandoned_order_id FROM `" . WCAP_EMAIL_SENT_HISTORY_TABLE . "` WHERE id = %d";
                    $get_ac_id_results = $wpdb->get_results( $wpdb->prepare( $get_ac_id_query , $email_sent_id ) );
                    $user_id           = 0;
                    if ( isset( $get_ac_id_results[0] ) ) {
                        $get_user_id_query = "SELECT user_id FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE id = %d";
                        $get_user_results  = $wpdb->get_results( $wpdb->prepare( $get_user_id_query , $get_ac_id_results[0]->abandoned_order_id ) );
                    }
                    if ( isset( $get_user_results[0] ) ) {
                        $user_id = $get_user_results[0]->user_id;
                    }
                    if ( 0 != $user_id ){
                        $unsubscribe_query = "UPDATE `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "`
                                                SET unsubscribe_link = '1'
                                                WHERE user_id= %d AND cart_ignored='0' ";
                        $wpdb->query( $wpdb->prepare( $unsubscribe_query , $user_id ) );
                    }

                    echo "Unsubscribed Successfully";

                    sleep( 2 );

                    $url = get_option( 'siteurl' );
                    ?>
               <script>
                    location.href = "<?php echo $url; ?>";
               </script>
               <?php
                 }
            } else {
                return $args;
            }
        }

        /**
         * If the url contain the email address then it will store email in the session.
         * @hook template_include
         * @param string $template The path of the template to include.
         * @return string $template The path of the template to include.
         * @globals mixed $wpdb
         * @globals mixed $woocommerce
         * @since 5.0
         */
        public static function wcap_if_email_address_exists( $template ) {
            $wcap_email_index = get_option ( "ac_capture_email_address_from_url" );
            
            if ( false !== $wcap_email_index && "" !== $wcap_email_index ) {
                if ( isset( $_GET[ $wcap_email_index ] ) && '' != $_GET[ $wcap_email_index ] ) {
                    
                    $wcap_get_email_address = rawurldecode( $_GET[ $wcap_email_index ] );
                    wcap_set_cart_session( 'wcap_populate_email', str_replace( " " , "+", $wcap_get_email_address ) );
                }
            }
            return $template;
        }

        /**
         * Redirects to the long url when the site is accessed
         * using short links
         * 
         * @param string $template - Template to use
         * @return string $template - Template to use
         * @hook template_include
         * @since 7.9
         */
        static function wcap_shortcode_redirects( $template ) {
        
            $request_uri = $_SERVER[ 'REQUEST_URI' ];
            
            if ( $request_uri !== '/' ) {
                
                $uri_params = explode( '/', $request_uri );            
                $shortcode = array_pop( $uri_params );            
                while( $shortcode == '' ) { // Shortcode should be populated only when $request_uri !== '/'.
                    $shortcode = array_pop( $uri_params );
                }
                
                if ( strlen( $shortcode ) < 15 ) {
                    // check if the shortcode exists in the tiny urls and return the long url
                    //$long_url = WCAP_Tiny_Url::get_long_url( $shortcode );
                    if( strpos( $shortcode, '?' ) ){
                        $long_url = WCAP_Tiny_Url::get_long_url( substr( $shortcode, 0, strpos( $shortcode, '?' ) ) );
                    }else{
                        $long_url = WCAP_Tiny_Url::get_long_url( $shortcode );
                    }
                    
                    if( $long_url ) {
                        $link_array = apply_filters( 'wcap_shortlinks_filter', array( 'sms_link' ) );
            
                        $found = false;
                        foreach( $link_array as $link ) {
                            if( strpos( $long_url, "wacp_action=$link" ) > 0 ) {
                                $found = true;
                                break;
                            }
                        }
            
                        if( $found ) {
                            wp_redirect( $long_url );
                            exit;
                        } else {
                            return $template;
                        }
                    } else {
                        return $template;
                    }
                }
            }
            
            return $template;
        }
        
        /**
         * Loads user cart and logs in the user (for registered users)
         * after completing all the checks. This function is used for
         * links accessed via SMS
         * 
         * @todo - Can be used for future cart recovery mediums as well
         * @param string $template - Template to use
         * @return string $template - Template to use
         * @hook template_include
         * 
         * @since 7.9
         */
        static function wcap_sms_redirects( $template ) {
        
            if( isset( $_GET[ 'wacp_action' ] ) && 
                ( $_GET[ 'wacp_action' ] == 'sms_link' || $_GET[ 'wacp_action' ] == 'fb_link' ) ) {
        
                // decrypt the validate code
                $link_decode = self::wcap_decode_validate_link( $_GET[ 'validate' ] );
        
                // decrypt the coupon code if present
                if ( isset( $_GET['c'] ) ) { // it will check if coupon code parameter exists or not
                    $decode_coupon_code = self::wcap_decode_coupon_code( $_GET[ 'c' ] );
        
                } else {
                    $decode_coupon_code     = '';
                }
        
                // get the Tiny URL ID
                $notifiction_sent_id       = 0;
                $sent_id_pos               = strpos( $link_decode, '&' );
                $notifiction_sent_id       = substr( $link_decode , 0, $sent_id_pos );
        
                // URL
                $url_pos                   = strpos( $link_decode, '=' );
                $url_pos                   = $url_pos + 1;
                $url                       = substr( $link_decode, $url_pos );
        
                // fetch the abandoned cart ID
                $abandoned_cart_id = self::get_ac_id( $notifiction_sent_id, WCAP_TINY_URLS );

                // get the user ID
                $user_id = Wcap_Common::get_user_id_from_cart( $abandoned_cart_id );

                if( $user_id == 0 ) {
                    self::redirect_to_shop();
                } else {
                    if( $user_id >= '63000000' ) { // guest user
        
                        // get the guest data
                        $guest_data = Wcap_Common::get_guest_data( $user_id );
                        $recovered_cart = Wcap_Common::get_recovered_id_for_user( $user_id );

                        // if cart is not recovered & guest data is present, load the guest data
                        if( $recovered_cart == 0 && count( $guest_data ) > 0 ) {
                            $setup_guest_data = true;
                        } else { // else redirect to the Shop page
                            $setup_guest_data = false;
                            wp_redirect( get_permalink( wc_get_page_id( 'shop' ) ) );
                        }
                         
                    } else if( $user_id < '63000000' ) { // registered user
        
                        // set language if needed
                        if ( function_exists('icl_register_string') ) {
                            self::setup_user_language( $user_id );
                        }
        
                        // setup user & login
                        self::login_registered_user( $user_id );
        
                    }
        
                    // set up the session
                    wcap_set_cart_session( 'wcap_c',             $decode_coupon_code ); // we need to set in session coz we directly apply coupon
                    wcap_set_cart_session( 'wcap_email_sent_id', $notifiction_sent_id );
                    wcap_set_cart_session( 'wcap_abandoned_id',  $abandoned_cart_id);
                  
                    if( isset( $guest_data ) && $guest_data ) {
        
                        wcap_set_cart_session( 'wcap_guest_first_name', $guest_data[ 'first_name' ] );
                        wcap_set_cart_session( 'wcap_guest_last_name',  $guest_data[ 'last_name' ] );
                        wcap_set_cart_session( 'wcap_guest_email',      $guest_data[ 'email_id' ] );
                        wcap_set_cart_session( 'wcap_guest_phone',      $guest_data[ 'phone' ] );
                        wcap_set_cart_session( 'wcap_user_id',          $user_id );
                        $my_temp = self::wcap_load_guest_persistent_cart( $user_id );
                    }
        
                    if ( is_numeric( $notifiction_sent_id ) && $notifiction_sent_id > 0 ) {
                        // increment counter
                        WCAP_Tiny_Url::increment_counter( $notifiction_sent_id );
                        // Update the Links Clicked Details
                        WCAP_Tiny_Url::wcap_update_link_details( $notifiction_sent_id );
                    }
        
                    if( is_numeric( $notifiction_sent_id ) ) {
                        do_action( 'wcap_recovery_link_accessed', $abandoned_cart_id, $_GET[ 'wacp_action' ], $_GET[ 'validate' ] );
                        header( "Location: $url" );
                    }
                }
            } else {
                return $template;
            }
        }
        
        /**
         * Decodes the validated link sent in recovery mediums
         * 
         * @param string $validate_link - Link to decode
         * @return string $link_decode - Decoded link
         * 
         * @since 7.9
         */
        static function wcap_decode_validate_link( $validate_link ) {
        
            $cryptKey                   = get_option( 'ac_security_key' );
            $validate_server_string     = rawurldecode( $validate_link );
            $validate_server_string     = str_replace( " " , "+", $validate_server_string );
            $validate_encoded_string    = $validate_server_string;
            $link_decode = Wcap_Aes_Ctr::decrypt( $validate_encoded_string, $cryptKey, 256 );
        
            return $link_decode;
        }
        
        /**
         * Decodes encrypted coupon codes sent
         * 
         * @param string $encrypt_coupon - Encrypted Coupon code
         * @return string $decode_coupon_code - Decoded Coupon Code
         * 
         * @since 7.9
         */
        static function wcap_decode_coupon_code( $encrypt_coupon ) {
        
            $cryptKey               = get_option( 'ac_security_key' );
            $decrypt_coupon_code    = rawurldecode( $encrypt_coupon );
            $decrypt_coupon_code    = str_replace( " " , "+", $decrypt_coupon_code );
            $decode_coupon_code     = Wcap_Aes_Ctr::decrypt( $decrypt_coupon_code, $cryptKey, 256 );
        
            return $decode_coupon_code;
        }
        
        /**
         * Returns abandoned cart ID. The cart ID is retrieved 
         * from the table name passed.
         * 
         * @param integer $url_id - ID of the record for which cart needs to be fetched
         * @param string $table_name - Table from which data should be accessed.
         * @return integer $cart_id - Abandoned Cart ID
         * 
         * @since 7.9
         */
        static function get_ac_id( $url_id, $table_name ) {
        
            global $wpdb;
        
            $cart_id = false;
        
            if( $url_id > 0 ) {
        
                $ac_query = "SELECT cart_id FROM `$table_name`
                                WHERE id = %d";
                $ac_results = $wpdb->get_results( $wpdb->prepare( $ac_query, $url_id ) );
        
                if( is_array( $ac_results ) && count( $ac_results ) > 0 ) {
                    $cart_id = isset( $ac_results[0]->cart_id ) ? $ac_results[0]->cart_id : false;
                }
        
            }
        
            return $cart_id;
        }
        
        /**
         * Redirects to the Shop page. Currently used when links sent in 
         * emails/SMS.
         * 
         * @since 7.9
         */
        static function redirect_to_shop() {
            _e( "Link expired. Redirecting you to the shop..", 'woocommerce-ac' );

            $shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );

            ?>
            <br/>
            <html>
                <head>
                    <title><?php _e( 'Redirecting...', 'woocommerce-ac' );?></title>
                    <meta http-equiv="refresh" content="5;URL=<?php echo $shop_page_url; ?>">
                </head>
                <br />
                <body>
                    <?php 
                    _e( "You are being automatically redirected to a home location of the website.<br />
                    If your browser does not redirect you in 5 seconds, or you do
                    not wish to wait, <a href='$shop_page_url'>click here</a>.", 'woocommerce-ac' );?>
                </body>
            </html>
            <?php 
        }

        /**
         * Sets up cart language in session. Called when
         * WPML is active.
         * 
         * @param integer $user_id - User ID
         * @since 7.9
         */
        static function setup_user_language( $user_id ) {
            
            if( $user_id > 0 ) {
                
                global $wpdb;
                
                $cart_query = "SELECT language FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "`
                                WHERE user_id = %d
                                AND cart_ignored = '0'";
                $cart_data = $wpdb->get_results( $wpdb->prepare( $cart_query, $user_id ) );
                
                if( isset( $cart_data[0]->language ) ) {
                    wcap_set_cart_session( 'wcap_selected_language', $cart_data[0]->language );
                }
            }
        }
        
        /**
         * Logs in the user.
         * 
         * @param integer $user_id - User ID
         * @since 7.9
         */
        static function login_registered_user( $user_id ) {
            
            if( $user_id > 0 ) {
                // set the current user
                $user = wp_set_current_user( $user_id );
                
                // login
                $user_login  = $user->data->user_login;
                wp_set_auth_cookie( $user_id );
                $my_temp     = wc_load_persistent_cart( $user_login, $user );
                
                do_action( 'wp_login', $user_login, $user );
                if ( isset( $sign_in ) && is_wp_error( $sign_in ) ) {
                    echo $sign_in->get_error_message();
                    exit;
                }
            }
        }
                
    } // end of class
}
