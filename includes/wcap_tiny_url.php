<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WCAP_Tiny_Url' ) ) {

    class WCAP_Tiny_Url {
        
        protected static $chars = 'b1c2d3f4g5h6j7k8l9mnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ';
        protected static $checkUrlExists = true;
        
        /**
         * Return the Long URL when the ID of the Tiny URL data
         * is sent.
         * 
         * @param integer $db_id - ID of the Tiny URL Record
         * @return boolean|string - Long URL
         * 
         * @since 7.9
         */
        static function get_long_url_from_id( $db_id ) {
            global $wpdb;
            
            $query = "SELECT long_url FROM `" . WCAP_TINY_URLS . "`
                        WHERE id = %d";
            $url_data = $wpdb->get_results( $wpdb->prepare( $query, $db_id ) );
            
            if( is_array( $url_data ) && count( $url_data ) > 0 ) {
                $long_url = isset( $url_data[0]->long_url ) ? $url_data[0]->long_url : false;
            } else {
                $long_url = false;
            }
            
            return $long_url;
        }
        
        static function get_short_url( $url ) {

            if( empty( $url ) ) {
                throw new Exception( "No URL was supplied." );
            }
            
            if( self::validateUrlFormat( $url ) == false ) {
                throw new Exception( "URL does not have a valid format." );
            }
            
            $shortCode = self::urlExistsInDb( $url );
            if ( $shortCode == false ) {
                $db_id = self::getDbEntry( $url );
                $shortCode = self::createShortCode( $db_id );
            }
            
            return $shortCode;
        }
        
        static function validateUrlFormat( $url ) {
            return filter_var( $url, FILTER_VALIDATE_URL );
        }
        
        static function urlExistsInDb( $url ) {
            
            global $wpdb;
            
            $query_url = "SELECT short_code FROM `" . WCAP_TINY_URLS . "`
                            WHERE long_url = %s";
            
            $get_url = $wpdb->get_results( $wpdb->prepare( $query_url, $url ) );
            
            if( is_array( $get_url ) && count( $get_url ) > 0 ) {
                return isset( $get_url[0]->short_code ) ? $get_url[0]->short_code : false;
            } else {
                return false;
            }
            
        }
        
        static function getDbEntry( $url ) {

            global $wpdb;
            
            $query = "SELECT id FROM `" . WCAP_TINY_URLS . "`
                        WHERE long_url = %s";
            $url_data = $wpdb->get_results( $wpdb->prepare( $query, $url ) );
            
            if( is_array( $url_data ) && count( $url_data ) > 0 ) {
                $db_id = isset( $url_data[0]->id ) ? $url_data[0]->id : false;
            } else {
                $db_id = false;
            }
            
            return $db_id;
            
        }
        
        static function createShortCode( $id ) {
            
            $id = intval($id);
            if( $id < 0 ) {
                throw new Exception(
                    "The ID is not a valid integer");
            }
            
            $length = strlen(self::$chars);
            // make sure length of available characters is at
            // least a reasonable minimum - there should be at
            // least 10 characters
            if( $length < 10 ) {
                throw new Exception("Length of chars is too small");
            }
            
            $code = "";
            
            while( $id > $length - 1 ) {
                // determine the value of the next higher character
                // in the short code should be and prepend
                $code = self::$chars[ intval( fmod( $id, $length ) ) ] . $code;
                // reset $id to remaining value to be converted
                $id = floor( $id / $length );
            }
            
            // remaining value of $id is less than the length of
            // self::$chars
            $code = self::$chars[ intval( $id ) ] . $code;
            
            return $code;
        }
        
        static function update_short_url( $db_id, $short_url ) {
            
            global $wpdb;
            
            if( $db_id > 0 && $short_url != '' ) {
                $wpdb->update( WCAP_TINY_URLS,
                                array( 'short_code' => $short_url ),
                                array( 'id' => $db_id )
                    );
            }
        }
        
        static function get_long_url( $short_code ) {
            global $wpdb;
            
            $long_url = false;
            
            if( $short_code != '' ) {
                $query = "SELECT long_url FROM `" . WCAP_TINY_URLS . "`
                            WHERE BINARY short_code = %s";
                $url_data = $wpdb->get_results( $wpdb->prepare( $query, $short_code ) );
                
                if( is_array( $url_data ) && count( $url_data ) > 0 ) {
                    $long_url = isset( $url_data[0]->long_url ) ? $url_data[0]->long_url : false;
                } 
            }
            
            return $long_url;
        }
        
        static function increment_counter( $id ) {
            
            global $wpdb;
            
            if( $id > 0 ) {
                $query = "UPDATE `" . WCAP_TINY_URLS ."` 
                            SET counter=counter+1
                            WHERE id = %d";
                $wpdb->query( $wpdb->prepare( $query, $id ) );
            }
            
        }
        
        /**
         * Updates the timestamp for when the link was opened.
         * 
         * @param integer $id - ID of the Tiny Urls table
         * @since 7.10.0
         */
        static function wcap_update_link_details( $id ) {
        
            global $wpdb;
        
            if( $id > 0 ) {
        
                // check if the link has already been accessed once
                $check_time = "SELECT notification_data FROM `" . WCAP_TINY_URLS . "`
                                WHERE id = %d";
                $get_data = $wpdb->get_results( $wpdb->prepare( $check_time, $id ) );
        
                if( is_array( $get_data ) && count( $get_data ) > 0 ) {
        
                    $notification_data = json_decode( $get_data[0]->notification_data );
        
                    $opened_time = isset( $notification_data->link_opened_time ) ? $notification_data->link_opened_time : '';
                    if( $opened_time == '' ) {
        
                        $notification_data->link_opened_time = current_time( 'timestamp' );
        
                        $new_data = json_encode( $notification_data );
        
                        $wpdb->update( WCAP_TINY_URLS, array( 'notification_data' => $new_data ), array( 'id' => $id ) );
                    }
                }
            }
        }
    }  // end of class
}
?>