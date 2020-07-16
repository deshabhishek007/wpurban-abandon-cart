<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * This file will set webhooks for communication with FB Messenger API.
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/FB
 * @category Classes
 * @since    7.10.0
 */

if ( !defined('ABSPATH') ) {
    exit; // Exit if accessed directly.
}

add_action( 'wcap_fb_messenger_callback_webhook', 'wcap_fb_messenger_handler' );

function wcap_fb_messenger_handler() {

    $bot = new pimax\FbBotApp( WCAP_FB_PAGE_TOKEN );

    $data = json_decode(file_get_contents("php://input"), true);
    $logdata = print_r($data['entry'], true);

    //Check if something is received
    if ( !empty( $_REQUEST['hub_mode'] ) && 
         $_REQUEST['hub_mode'] == 'subscribe' && 
         $_REQUEST['hub_verify_token'] == WCAP_FB_VERIFY_TOKEN ) {

        // Webhook setup request
        echo $_REQUEST['hub_challenge'];
    } else {
        // Other event
        $data = json_decode( file_get_contents( "php://input" ), true );

        // Log Webhook Calls if wp_debug is turned on
        if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
            //Log latest connections    
            $logdata = print_r($data['entry'], true);
        }

        if ( !empty( $data['entry'][0]['messaging'] ) ) {

            foreach ($data['entry'][0]['messaging'] as $message) {

                $command = "";

                //If Authentication Callback is received
                if ( !empty( $message['optin'] ) ) {
                    
                    //Is order subsciption
                    //if (derweili_mbot_woocommerce_startswith($message['optin']['ref'], 'derweiliSubscribeToOrder' )) {

                        /*$orderid = str_replace("derweiliSubscribeToOrder", "", $message['optin']['ref']);
                        $mbot_Order = new Derweili_Mbot_Order($orderid);

                        // store user messenger id as post meta
                        if ( isset( $message['sender']['id'] ) ) {

                            derweili_mbot_log( "Sender Id is " . $message['sender']['id'] );

                            $mbot_Order->add_user_id($message['sender']['id']);

                            //add_post_meta($orderid, 'derweili_mbot_woocommerce_customer_messenger_id', $message['sender']['id'], true);
                            //$receiver_id = $message['sender']['id'];
                        }elseif ( isset( $message['optin']['user_ref'] ) ){

                            $mbot_Order->add_user_reference( $message['optin']['user_ref'] );

                            derweili_mbot_log( "User Referece is " . $message['optin']['user_ref'] );

                           // add_post_meta($orderid, 'derweili_mbot_woocommerce_customer_messenger_id', $message['optin']['user_ref'], true);
                           // add_post_meta($orderid, 'derweili_mbot_woocommerce_customer_ref', true, true);
                           // $receiver_id = $message['optin']['user_ref'];
                        }

                        // store user messenger id as user meta
                        //if ($order->get_user_id() != 0) {
                            //add_user_meta( $order->get_user_id(), 'derweili_mbot_woocommerce_messenger_id', $message['sender']['id'], true );
                        //}
                        
                        //send text message to messenger
                        $sendmessage = $mbot_Order->send_text_message( get_site_option( 'derweili_mbot_new_order_message' ) );
                        //$bot->send( new Der_Weili_Message( $receiver_id, __('Thank you for your order, you will be immediately notified when your order status changes.', 'mbot-woocommerce') ) );
                        //send Order notification to messenger
                        //$bot->send(new WooOrderMessage( $receiver_id, $order ) );

                        //file_put_contents("log2.html", print_r( $sendmessage, true ), FILE_APPEND);
                        //file_put_contents("log2.html", print_r( '<hr />', true ), FILE_APPEND);

                        $receipt_send_return = $mbot_Order->send_order();
                        derweili_mbot_log( "Order Sent" );
                        derweili_mbot_log( $receipt_send_return );

                        do_action('derweili_mbot_woocommerce_after_optin_message', $message, $order );*/

                    //}else{
                        //derweili_mbot_log( "Optin Message does not contain a valid prefix" );
                        //derweili_mbot_log( "Optin Message is " . $message['optin']['ref'] );

                    //};

                }else{
                    //derweili_mbot_log( "Webhook Call is not an optin message" );
                };

            }; //endforeach
        }else{
            //derweili_mbot_log( "Webhook Call contains no message" );
        }; //endif

    }

} // function