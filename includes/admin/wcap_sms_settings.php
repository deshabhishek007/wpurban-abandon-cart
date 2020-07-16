<?php
/**
 * It will display the email template listing.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Settings
 * @since 7.9
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Get the PHP helper library from twilio.com/docs/php/install
if( ! class_exists( 'SplClassLoader' ) ) { 
    require_once( WCAP_PLUGIN_PATH . '/includes/libraries/twilio-php/Twilio/autoload.php' ); // Loads the library
}
use Twilio\Rest\Client;

if ( !class_exists('Wcap_SMS_settings' ) ) {
    /**
     * It will display the SMS settings for the plugin.
     * @since 7.9
     */
    class Wcap_SMS_settings{

        /**
         * Construct
         */
        public function __construct() {
            
        }
        
        /**
         * Adds settings for SMS Notifications
         * 
         * @since 7.9
         */
        public static function wcap_sms_settings() {
            
            ?>
            <form method="post" action="options.php">
                <?php 
                settings_errors();
                settings_fields     ( 'woocommerce_sms_settings' );
                do_settings_sections( 'woocommerce_ac_sms_page' );
                submit_button(); 
                ?>
            </form>
            <div id="test_fields">
                <h2><?php _e( 'Send Test SMS', 'woocommerce-ac' ); ?></h2>
                <div id="status_msg" style="background: white;border-left: #6389DA 4px solid;padding: 10px;display: none;width: 90%;"></div>
                <table class="form-table">
                    <tr>
                        <th><?php _e( 'Recipient', 'woocommerce-ac' ); ?></th>
                        <td>
                            <input id="test_number" name="test_number" type=text />
                            <i><?php _e( 'Must be a valid phone number in E.164 format.', 'woocommerce-ac' );?></i>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Message', 'woocommerce-ac' );?></th>
                        <td><textarea id="test_msg" rows="4" cols="70"><?php _e( 'Hello World!', 'woocommerce-ac' );?></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="button" id="wcap_test_sms" class="button-primary" value="<?php _e( 'Send', 'wocommerce-ac' );?>" /></td>
                    </tr>
                </table>
            </div>
            <?php 
            
        }
        
        /**
         * Sends a Test SMS
         * Called via AJAX
         * 
         * @since 7.9
         */
        static function wcap_send_test_sms() {
            
            $msg_array = array();
             
            $phone_number = ( isset( $_POST[ 'number' ] ) ) ? $_POST[ 'number' ] : 0;
            
            $msg = ( isset( $_POST[ 'msg' ] ) && $_POST[ 'msg' ] != '') ? $_POST[ 'msg' ] : '';
            
            if( $phone_number != '' && $msg != '' ) {
                
                // Verify the Phone number
                if( is_numeric( $phone_number ) ) {
                
                    // if first character is not a +, add it
                    if( substr( $phone_number, 0, 1 ) != '+' ) {
                        $phone_number = '+' . $phone_number;
                    }
                    
                    $sid = get_option( 'wcap_sms_account_sid' );
                    $token = get_option( 'wcap_sms_auth_token' );
                    
                    if( $sid != '' && $token != '' ) {
                        
                        try {
                            $client = new Client($sid, $token);
                            
                            $message = $client->messages->create(
                                $phone_number,
                                array(
                                    'from' => get_option( 'wcap_sms_from_phone' ),
                                    'body' => $msg,
                                )
                            );
                            
                            if( $message->sid ) {
                                $message_sid = $message->sid;
    
                                $message_details = $client->messages( $message_sid )->fetch();
                                
                                $status = $message_details->status;
                                $error_msg = $message_details->errorMessage;
                                
                                $msg_array[] = __( "Message Status: $status", 'woocommerce-ac' );
                            }
                        } catch( Exception $e ) {
                            $msg_array[] = $e->getMessage();
                        } 
                    } else { // Account Information is incomplete
                        $msg_array[] = __( 'Incomplete Twilio Account Details. Please provide an Account SID and Auth Token to send a test message.', 'woocommerce-ac' );
                    }
                } else {
                    $msg_array[] = __( 'Please enter the phone number in E.164 format', 'woocommerce-ac' );
                } 
            } else { // Phone number/Msg has not been provided
                $msg_array[] = __( 'Please make sure the Recipient Number and Message field are populated with valid details.', 'woocommerce-ac' );
            }
            
            echo json_encode( $msg_array );
            die();
        }
    } // end of class
    $wcap_SMS_settings = new Wcap_SMS_settings();
}
