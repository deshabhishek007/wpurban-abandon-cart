<?php
/**
 * Common function that can be used to get the data
 * from the notifications_meta table
 * 
 * @param integer $template_id - Template ID
 * @param string $meta_key - Meta Key
 * @return boolean|string - Meta Value. Returns false if meta key not found
 * 
 * @since 7.9
 */
function wcap_get_notification_meta( $template_id, $meta_key ) {
    
    global $wpdb;
    
    if( $template_id > 0 && $meta_key != '' ) {
        $get_query = "SELECT meta_value FROM `" . WCAP_NOTIFICATIONS_META . "`
                        WHERE template_id = %d
                        AND meta_key = %s";
        $query_data = $wpdb->get_results( $wpdb->prepare( $get_query, $template_id, $meta_key ) );
        
        if( is_array( $query_data ) && count( $query_data ) > 0 ) {
            return ( isset( $query_data[0]->meta_value ) ) ? $query_data[0]->meta_value : false;
        } else {
            return false;
        }
    } else {
        return false;
    }
    
}

/**
 * Common function that can be used to update the 
 * Notifications_meta table
 * 
 * @param integer $template_id - Template ID
 * @param string $meta_key - Meta Key
 * @param string $meta_value - Meta Value
 * 
 * @since 7.9
 */
function wcap_update_notification_meta( $template_id, $meta_key, $meta_value ) {
    
    global $wpdb;
    
    if( $template_id > 0 && $meta_key != '' ) {
        
        
        $update = $wpdb->update( WCAP_NOTIFICATIONS_META, 
                        array( 'meta_value' => $meta_value ),
                        array( 'template_id' => $template_id,
                               'meta_key' => $meta_key
                        ) 
            );
    
        if( $update === 0 && wcap_get_notification_meta( $template_id, $meta_key ) === false ) { // no record was found for update
            wcap_add_notification_meta( $template_id, $meta_key, $meta_value );
        } 
    }
    
}

/**
 * Common function that can be used to insert in the 
 * Notifications_meta table
 * 
 * @param integer $template_id - Template ID
 * @param string $meta_key - Meta Key
 * @param string $meta_value - Meta Value
 * 
 * @since 7.9
 */
function wcap_add_notification_meta( $template_id, $meta_key, $meta_value ) {

    global $wpdb;
    
    $update = $wpdb->insert( WCAP_NOTIFICATIONS_META,
                array( 'template_id' => $template_id,
                       'meta_key' => $meta_key,
                       'meta_value' => $meta_value )
    );
     
}

/**
 * Returns the data from the Notifications meta 
 * table that have the meta key as passed
 * 
 * @param string $meta_key - Meta Key
 * @return array $results - Results array
 * 
 * @since 7.9
 */
function wcap_get_notification_meta_by_key( $meta_key ) {
    global $wpdb;
    
    $meta_query = "SELECT meta_id, template_id, meta_value FROM `" . WCAP_NOTIFICATIONS_META . "`
                    WHERE meta_key = %s";
    
    $meta_results = $wpdb->get_results( $wpdb->prepare( $meta_query, $meta_key ) );
    
    return $meta_results;
}

/**
 * Returns the template status
 * 
 * @param integer $template_id - Template ID
 * @return boolean $status - Template status - true - active|false - inactive
 * 
 * @since 7.9
 */
function wcap_get_template_status( $template_id ) {
    
    $status = false;

    global $wpdb;
    
    $status_query = "SELECT is_active FROM `" . WCAP_NOTIFICATIONS . "`
                    WHERE id = %d";

    $status_col = $wpdb->get_results( $wpdb->prepare( $status_query, $template_id ) );
    
    $status = ( isset( $status_col[0] ) ) ? $status_col[0]->is_active : false;
    
    return $status;
}

/**
 * Returns the list of enabled reminder methods
 *
 * @return array $reminders_enabled - Reminder Methods that are enabled.
 * @since 7.10.0
 */
function wcap_get_enabled_reminders() {

    $reminders_enabled = array();

    $reminders_list = array();

    $reminders_list[ 'emails' ] = get_option( 'ac_enable_cart_emails' );
    $reminders_list[ 'sms' ] = get_option( 'wcap_enable_sms_reminders' );

    foreach( $reminders_list as $names => $status ) {
        if( $status == 'on' ) {
            array_push( $reminders_enabled, $names );
        }
    }

    $reminders_enabled = apply_filters( 'wcap_reminders_list', $reminders_enabled );

    return $reminders_enabled;
}

function wcap_update_notifications( $id, $body, $frequency, $active, $coupon_code, $subject = null ) {

    global $wpdb;

    $wpdb->update( WCAP_NOTIFICATIONS, 
        array( 
            'body'          => $body,
            'frequency'     => $frequency,
            'is_active'     => $active,
            'coupon_code'   => $coupon_code,
            'subject'       => $subject
        ),
        array( 'id' => $id ) 
    );
}

function wcap_insert_notifications( $body, $type, $active, $frequency, $coupon_code, $default, $subject = null ) {

    global $wpdb;

    $wpdb->insert( WCAP_NOTIFICATIONS, 
        array(
            'body'  => $body,
            'type'  => $type,
            'is_active' => $active,
            'frequency' => $frequency,
            'coupon_code'   => $coupon_code,
            'default_template'  => $default,
            'subject' => $subject
        )
    );

    return $wpdb->insert_id;
}

/**
 * Returns the list of templates
 * 
 * @param string $type Type of notification
 * @return array Templates data
 *
 * @since 7.9
 */
function wcap_get_notification_templates( $type ) {

    global $wpdb;

    // get active templates
    $template_query = "SELECT * FROM `" . WCAP_NOTIFICATIONS . "`
                        WHERE type = '" . $type . "'
                        AND is_active = '1'";

    $template_data = $wpdb->get_results( $template_query );

    if( is_array( $template_data ) && count( $template_data ) > 0 ) {

        $templates = array();

        $minute_seconds            = 60;
        $hour_seconds              = 3600; // 60 * 60
        $day_seconds               = 86400; // 24 * 60 * 60

        foreach( $template_data as $data ) {

            $frequency_array = explode( ' ', $data->frequency );

            switch( $frequency_array[1] ) {
                case '':
                case 'minutes':
                    $frequency = $frequency_array[0] * $minute_seconds;
                    break;
                case 'hours':
                    $frequency = $frequency_array[0] * $hour_seconds;
                    break;
                case 'days':
                    $frequency = $frequency_array[0] * $day_seconds;
                    break;
            }

            $templates[ $frequency ] = array( 
                'id' => $data->id,
                'body' => $data->body,
                'coupon_code' => $data->coupon_code
            );

            if ( $type === 'fb' ) {
                $templates[ $frequency ]['subject'] = $data->subject;
            }
        }
    } else {
        $templates = array();
    }

    return $templates;
}

/**
 * Returns the list of carts with cart data
 * for which the notification needs to be sent.
 *
 * @param string $registered_time - Time before which, registered user carts need to be abandoned for notification to be sent.
 * @param string $guest_time - Time before which guest cart needs to be abandoned for the notification to be sent
 * @param integer $template_id - Template ID
 * @return object $carts - Carts for which reminder needs to be sent
 *
 * @since 7.9
 */
function wcap_get_notification_carts( $registered_time, $guest_time, $template_id, $type = '' ) {

    global $wpdb;

    $carts = array();

    $sent_carts_str = '';
    $sent_carts_list = wcap_get_notification_meta( $template_id, 'to_be_sent_cart_ids' );

    if( $sent_carts_list ) {
        $sent_carts = explode( ',', $sent_carts_list );

        foreach( $sent_carts as $cart_id ) {
            if( $sent_carts_str != '' ) {
                $sent_carts_str .= ( $cart_id != '' ) ? ",'$cart_id'" : '';
            } else {
                $sent_carts_str = ( $cart_id != '' ) ? "'$cart_id'" : '';
            }
        }
    }

    if ( $type == 'fb' || $type == 'sms' ) {
        $user_id_query = 'AND user_id >= 0';
    }else {
        $user_id_query = 'AND user_id > 0';
    }

    if( $sent_carts_str != '' ) {
        // cart query
        $cart_query = "SELECT DISTINCT wpac.id, wpac.abandoned_cart_info, wpac.abandoned_cart_time, wpac.user_id, wpac.language FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` as wpac
                        WHERE cart_ignored IN ('0', '2')
                        AND recovered_cart = 0
                        AND unsubscribe_link = '0'
                        " . $user_id_query . "
                        AND wpac.id IN ( $sent_carts_str )
                        AND (( user_type = 'REGISTERED' AND abandoned_cart_time < %s )
                        OR ( user_type = 'GUEST' AND abandoned_cart_time < %s ))";

        $carts = $wpdb->get_results( $wpdb->prepare( $cart_query, $registered_time, $guest_time ) );

    }
    return $carts;
}

/**
 * Updates the Notifications meta table and removes
 * the Cart ID from the list of carts for which the SMS
 * needs to be sent.
 *
 * @param integer $template_id - Template ID
 * @param integer $cart_id - Abandoned Cart ID
 *
 * @since 7.9
 */
function wcap_update_meta( $template_id, $cart_id ) {

    global $wpdb;

    $list_carts = wcap_get_notification_meta( $template_id, 'to_be_sent_cart_ids' );

    $carts_array = explode( ',', $list_carts );

    if( in_array( $cart_id, $carts_array ) ) {
        $key = array_search( $cart_id, $carts_array );
        unset( $carts_array[ $key ] );

        $updated_cart_list = implode( ',', $carts_array );
        wcap_update_notification_meta( $template_id, 'to_be_sent_cart_ids', $updated_cart_list );
    }
}

/**
* Creates a checkout link and inserts a record in the WCAP_TINY_URLS table.
*
* @param object $cart_data - Abandoned Cart Data
* @param array $template_data - contains the id, coupon_code & body
* @param string $link_type - Link Type: sms_links
* @return integer $insert_id - ID of the record inserted in tiny_urls table
*/
function generate_checkout_url( $cart_data, $template_data, $link_type ) {

    global $wpdb;

    $abandoned_id = $cart_data->id;
    $cart_language = $cart_data->language;

    $template_id = $template_data[ 'id' ];
    $coupon_id              = $template_data[ 'coupon_code' ];
    $coupon_to_apply        = get_post( $coupon_id, ARRAY_A );
    $coupon_code            = $coupon_to_apply[ 'post_title' ];

    $checkout_page_id   = wc_get_page_id( 'checkout' );
    $checkout_page_link = $checkout_page_id ? get_permalink( $checkout_page_id ) : '';

    // Force SSL if needed
    $ssl_is_used = is_ssl() ? true : false;

    if( true === $ssl_is_used || 'yes' === get_option( 'woocommerce_force_ssl_checkout' ) ) {
        $checkout_page_https = true;
        $checkout_page_link  = str_replace( 'http:', 'https:', $checkout_page_link );
    }

    // check if WPML is active
    $icl_register_function_exists = function_exists( 'icl_register_string' ) ? true : false;
    
    if( $checkout_page_id ) {
        if( true === $icl_register_function_exists ) {
            if( 'en' == $cart_language  ) {
            // do nothing
            } else {
                $checkout_page_link = apply_filters( 'wpml_permalink', $checkout_page_link, $cart_language );
                // if ssl is enabled
                if( isset( $checkout_page_https ) && true === $checkout_page_https ) {
                   $checkout_page_link = str_replace( 'http:', 'https:', $checkout_page_link );
                }
            }
        }
    }

    $wpdb->insert( 
        WCAP_TINY_URLS,
        array( 
            'cart_id'        => $abandoned_id,
            'template_id'    => $template_id,
            'long_url'       => '',
            'short_code'     => '',
            'date_created'   => current_time( 'timestamp' ),
            'counter'        => 0,
            'notification_data' => json_encode( array( 'link_clicked' => 'Checkout Page'  ) ),
        )
    );
    $insert_id = $wpdb->insert_id;

    $encoding_checkout = $insert_id . '&url=' . $checkout_page_link;
    $validate_checkout = Wcap_Common::encrypt_validate( $encoding_checkout );

    $site_url = get_option( 'siteurl' );

    if( isset( $coupon_code ) && $coupon_code != '' ) {
        $encrypted_coupon_code = Wcap_Common::encrypt_validate( $coupon_code );
        $checkout_link_track  = "$site_url/?wacp_action=$link_type&validate=$validate_checkout&c=$encrypted_coupon_code";
    } else {
        $checkout_link_track = "$site_url/?wacp_action=$link_type&validate=$validate_checkout";
    }

    $wpdb->update( 
        WCAP_TINY_URLS,
        array( 'long_url'   => $checkout_link_track ),
        array( 'id' => $insert_id ) 
    );

    return $insert_id;
}

/**
 * Set Cart Session variables
 * 
 * @param string $session_key Key of the session
 * @param string $session_value Value of the session
 * @since 7.11.0
 */
function wcap_set_cart_session( $session_key, $session_value ) {
    WC()->session->set( $session_key, $session_value );
}

/**
 * Get Cart Session variables
 * 
 * @param string $session_key Key of the session
 * @return mixed Value of the session
 * @since 7.11.0
 */
function wcap_get_cart_session( $session_key ) {
    if ( ! is_object( WC()->session ) ) {
            return false;
    }
    return WC()->session->get( $session_key );
}

/**
 * Delete Cart Session variables
 * 
 * @param string $session_key Key of the session
 * @since 7.11.0
 */
function wcap_unset_cart_session( $session_key ) {
    WC()->session->__unset( $session_key );
}

/**
 * Returns the Cart History Data.
 *
 * @param int $cart_id - Abandoned Cart ID
 * @return object $cart_history - From the Abandoned Cart History table.
 * @since 8.7.0
 */
function wcap_get_data_cart_history( $cart_id ) {
    global $wpdb;

    $cart_history = $wpdb->get_results( $wpdb->prepare( 'SELECT id, user_id, abandoned_cart_info, abandoned_cart_time, cart_ignored, recovered_cart, user_type, language, checkout_link FROM ' . WCAP_ABANDONED_CART_HISTORY_TABLE . ' WHERE id = %d', $cart_id ) );

    if ( is_array( $cart_history ) && count( $cart_history ) > 0 ) {
        return $cart_history[0];
    } else {
        return false;
    }
}

/**
 * Returns the Guest Data.
 *
 * @param int $user_id - Guest User ID.
 * @return object $guest_data - From the Guest History table.
 * @since 8.7.0
 */
function wcap_get_data_guest_history( $user_id ) {

    global $wpdb;

    $guest_data = $wpdb->get_results( $wpdb->prepare( 'SELECT billing_first_name, billing_last_name, billing_country, billing_zipcode, email_id, phone, shipping_zipcode, shipping_charges FROM ' . WCAP_GUEST_CART_HISTORY_TABLE . ' WHERE id = %d', $user_id ) );

    if ( is_array( $guest_data ) && count( $guest_data ) > 0 ) {
        return $guest_data[0];
    } else {
        return false;
    }
}

/**
 * Return an array of product details.
 *
 * @param string $cart_data - Abandoned Cart Data frm the Cart History table.
 * @return array $product_details - Product Details.
 * @since 8.7.0
 */
function wcap_get_product_details( $cart_data ) {

    $product_details = array();
    $cart_value = json_decode( stripslashes( $cart_data ) );

    if ( isset( $cart_value->cart ) && count( get_object_vars( $cart_value->cart ) ) > 0 ) {
        foreach ( $cart_value->cart as $product_data ) {
            $product_id = $product_data->variation_id > 0 ? $product_data->variation_id : $product_data->product_id;
            $details = (object) [
                'product_id'    => $product_data->product_id,
                'variation_id'  => $product_data->variation_id,
                'product_name'  => get_the_title( $product_id ),
                'line_subtotal' => $product_data->line_subtotal
            ];
            array_push( $product_details, $details );
        }
    }
    
    return $product_details;
}
?>
