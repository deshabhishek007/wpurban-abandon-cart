<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * Class for abandon cart webhooks.
 *
 * @author   Tyche Softwares
 * @package  WCAP/webhooks
 * @category Classes
 */

/**
 * WCAP Webhooks
 *
 * @since 8.7.0
 */
class Wcap_Webhooks {

	/**
	 * Plugin hooks & functions.
	 *
	 * @since 8.7.0
	 */
	public static function init() {

		// Setup webhooks.
		add_filter( 'woocommerce_webhook_topics', array( __CLASS__, 'wcap_add_new_webhook_topics' ), 10, 1 );
		add_filter( 'woocommerce_webhook_topic_hooks', array( __CLASS__, 'wcap_add_topic_hooks' ), 10, 1 );
		add_filter( 'woocommerce_valid_webhook_resources', array( __CLASS__, 'wcap_add_cart_resources' ), 10, 1 );
		add_filter( 'woocommerce_valid_webhook_events', array( __CLASS__, 'wcap_add_cart_events' ), 10, 1 );
		add_filter( 'woocommerce_webhook_payload', array( __CLASS__, 'wcap_generate_payload' ), 10, 4 );
		add_filter( 'woocommerce_webhook_deliver_async', array( __CLASS__, 'wcap_deliver_sync' ), 10, 3 );
		// Process Webhook actions.
		add_action( 'wcap_atc_record_created', array( __CLASS__, 'wcap_atc_created' ), 10, 1 );
		add_action( 'wcap_guest_created_at_checkout', array( __CLASS__, 'wcap_checkout_created' ), 10, 1 );
		add_action( 'wcap_guest_using_forms', array( __CLASS__, 'wcap_guest_forms' ), 10, 1 );
		add_action( 'wcap_reminder_email_sent', array( __CLASS__, 'wcap_reminder_emails' ), 10, 3 );
		add_action( 'wcap_reminder_sms_sent', array( __CLASS__, 'wcap_reminder_sms' ), 10, 2 );
		add_action( 'wcap_reminder_fb_sent', array( __CLASS__, 'wcap_reminder_fb' ), 10, 2 );
		add_action( 'wcap_recovery_link_accessed', array( __CLASS__, 'wcap_recovery_link' ), 10, 3 );
		add_action( 'wcap_cart_recovered', array( __CLASS__, 'wcap_cart_recovered' ), 10, 2 );
		add_action( 'wcap_webhook_after_cutoff', array( __CLASS__, 'wcap_cart_cutoff_reached' ), 10, 1 );
	}

	/**
	 * Add new list of events for webhooks in WC->Settings->Advanced->Webhooks.
	 *
	 * @param array $topics - Topic Hooks.
	 * @return array $topics - Topic Hooks including the ones from our plugin.
	 * @since 8.7.0
	 */
	public static function wcap_add_new_webhook_topics( $topics ) {

		$new_topics = array(
			// Email Captured.
			'wcap_cart.atc'       => __( 'Email Address Captured via Add to Cart Popup', 'woocommerce-ac' ),
			'wcap_cart.checkout'  => __( 'Email Address Captured at Checkout', 'woocommerce-ac' ),
			'wcap_cart.form'      => __( 'Email Address Captured via Form Integrations', 'woocommerce-ac' ),
			// Cut off reached.
			'wcap_cart.cutoff'    => __( 'Cart Abandoned after cut-off time', 'woocommerce-ac' ),
			// Reminders Sent.
			'wcap_email.sent'     => __( 'Cart Abandonment Reminder Email Sent', 'woocommerce-ac' ),
			'wcap_sms.sent'       => __( 'Cart Abandonment Reminder SMS Sent', 'woocommerce-ac' ),
			'wcap_fb.sent'        => __( 'Cart Abandonment Reminder FB message Sent', 'woocommerce-ac' ),
			// Link Clicked.
			'wcap_link.clicked'   => __( 'Recovery link clicked in reminders sent', 'woocommerce-ac' ),
			// Order Recovered.
			'wcap_cart.recovered' => __( 'Abandoned Order Recovered', 'woocommerce-ac' ),
		);
		return array_merge( $topics, $new_topics );
	}

	/**
	 * Trigger hooks for the plugin topics.
	 *
	 * @param array $topic_hooks - Topic Hooks.
	 * @return array $topic_hooks - Topic Hooks including the ones from our plugin.
	 * @since 8.7.0
	 */
	public static function wcap_add_topic_hooks( $topic_hooks ) {

		$new_hooks = array(
			// ATC Record Created.
			'wcap_cart.atc'       => array( 'wcap_popup_record_created' ),
			'wcap_cart.checkout'  => array( 'wcap_checkout_record_created' ),
			'wcap_cart.form'      => array( 'wcap_guest_record_via_form' ),
			'wcap_cart.cutoff'    => array( 'wcap_abandoned_cart_cutoff' ),
			'wcap_email.sent'     => array( 'wcap_reminder_sent_email' ),
			'wcap_sms.sent'       => array( 'wcap_reminder_sent_sms' ),
			'wcap_fb.sent'        => array( 'wcap_reminder_sent_fb' ),
			'wcap_link.clicked'   => array( 'wcap_recovery_link' ),
			'wcap_cart.recovered' => array( 'wcap_abandoned_cart_recovered' ),
		);

		return array_merge( $new_hooks, $topic_hooks );
	}

	/**
	 * Add webhook resources.
	 *
	 * @param array $topic_resources - Webhook Resources.
	 * @return array $topic_resources - Webhook Resources including the ones from our plugin.
	 * @since 8.7.0
	 */
	public static function wcap_add_cart_resources( $topic_resources ) {

		// Webhook resources for wcap.
		$new_resources = array(
			'wcap_cart',
			'wcap_email',
			'wcap_sms',
			'wcap_fb',
			'wcap_link',
		);

		return array_merge( $new_resources, $topic_resources );
	}

	/**
	 * Add webhook events.
	 *
	 * @param array $topic_events - List of events.
	 * @return array $topic_events - List of events including the ones from the plugin.
	 * @since 8.7.0
	 */
	public static function wcap_add_cart_events( $topic_events ) {

		// Webhook events for wcap.
		$new_events = array(
			'atc',
			'checkout',
			'form',
			'cutoff',
			'sent',
			'clicked',
			'recovered',
		);

		return array_merge( $new_events, $topic_events );

	}

	/**
	 * Deliver the webhooks in background or realtime.
	 *
	 * @param bool   $value - true|false - deliver the webhook in background|deliver in realtime.
	 * @param object $webhook - WC Webhook object.
	 * @param array  $arg - Arguments.
	 * @return bool  $value - Return false causes the webhook to be delivered immediately.
	 *
	 * @since 8.7.0
	 */
	public static function wcap_deliver_sync( $value, $webhook, $arg ) {
		$wcap_webhook_topics = array(
			'wcap_cart.atc',
			'wcap_cart.checkout',
			'wcap_cart.form',
			'wcap_cart.cutoff',
			'wcap_email.sent',
			'wcap_sms.sent',
			'wcap_fb.sent',
			'wcap_link.clicked',
			'wcap_cart.recovered',
		);

		if ( in_array( $webhook->get_topic(), $wcap_webhook_topics, true ) ) {
			return false;
		}

		return $value;
	}

	/**
	 * Generate data for webhook delivery.
	 *
	 * @param array  $payload - Array of Data.
	 * @param string $resource - Resource.
	 * @param array  $resource_data - Resource Data.
	 * @param int    $id - Webhook ID.
	 * @return array $payload - Array of Data.
	 *
	 * @since 8.7.0
	 */
	public static function wcap_generate_payload( $payload, $resource, $resource_data, $id ) {

		switch ( $resource_data['action'] ) {
			case 'atc':
			case 'checkout':
			case 'form':
			case 'cutoff':
			case 'sent':
			case 'clicked':
			case 'recovered':
				$webhook_meta = array(
					'webhook_id'          => $id,
					'webhook_action'      => $resource_data['action'],
					'webhook_resource'    => $resource,
					'webhook_resource_id' => $resource_data['id'],
				);

				$payload = array_merge( $webhook_meta, $resource_data['data'] );
				break;
		}

		return $payload;
	}

	/**
	 * Triggers a webhook when cart is abandoned using ATC.
	 *
	 * @param int $abandoned_cart_id - Abandoned Cart ID.
	 * @since 8.7.0
	 */
	public static function wcap_atc_created( $abandoned_cart_id ) {

		$cart_history = wcap_get_data_cart_history( $abandoned_cart_id );

		if ( $cart_history ) {
			$user_id   = $cart_history->user_id;
			$user_type = $cart_history->user_type;

			if ( $user_id >= 63000000 && 'GUEST' === $user_type ) {
				$guest_data = wcap_get_data_guest_history( $user_id );
			}

			if ( isset( $cart_history ) && isset( $guest_data ) ) {

				// Prepare the Data to be sent.
				$cart_value = json_decode( stripslashes( $cart_history->abandoned_cart_info ) );

				if ( isset( $cart_value->cart ) && count( get_object_vars( $cart_value->cart ) ) > 0 ) {
					foreach ( $cart_value->cart as $product_details ) {
						$product_id   = $product_details->product_id;
						$variation_id = $product_details->variation_id;
						$product_name = get_the_title( $product_id );
					}
				}

				$send_data = array(
					'id'           => $cart_history->id,
					'product_id'   => $product_id,
					'variation_id' => $variation_id,
					'product_name' => $product_name,
					'timestamp'    => $cart_history->abandoned_cart_time,
					'email_id'     => $guest_data->email_id,
					'source'       => 'atc',
				);

				$data = array(
					'id'     => $abandoned_cart_id,
					'data'   => $send_data,
					'action' => 'atc',
				);

				do_action( 'wcap_popup_record_created', $data );

			}
		}

	}

	/**
	 * Triggers a webhook when cart is abandoned using forms.
	 *
	 * @param int $abandoned_cart_id - Abandoned Cart ID.
	 * @since 8.7.0
	 */
	public static function wcap_guest_forms( $abandoned_cart_id ) {

		$cart_history = wcap_get_data_cart_history( $abandoned_cart_id );

		if ( $cart_history ) {
			$user_id   = $cart_history->user_id;
			$user_type = $cart_history->user_type;

			if ( $user_id >= 63000000 && 'GUEST' === $user_type ) {
				$guest_data = wcap_get_data_guest_history( $user_id );
			}

			if ( isset( $cart_history ) && isset( $guest_data ) ) {

				// Prepare the Data to be sent.
				$product_details = wcap_get_product_details( $cart_history->abandoned_cart_info );

				$send_data = array(
					'id'              => $cart_history->id,
					'product_details' => $product_details,
					'timestamp'       => $cart_history->abandoned_cart_time,
					'email_id'        => $guest_data->email_id,
					'source'          => 'form',
				);

				$data = array(
					'id'     => $abandoned_cart_id,
					'data'   => $send_data,
					'action' => 'form',
				);

				do_action( 'wcap_guest_record_via_form', $data );

			}
		}

	}

	/**
	 * Triggers a webhook when cart is abandoned at Checkout.
	 *
	 * @param int $abandoned_cart_id - Abandoned Cart ID.
	 * @since 8.7.0
	 */
	public static function wcap_checkout_created( $abandoned_cart_id ) {

		$cart_history = wcap_get_data_cart_history( $abandoned_cart_id );

		if ( $cart_history ) {
			$user_id   = $cart_history->user_id;
			$user_type = $cart_history->user_type;

			if ( $user_id >= 63000000 && 'GUEST' === $user_type ) {
				$guest_data = wcap_get_data_guest_history( $user_id );
			}
			if ( isset( $cart_history ) && isset( $guest_data ) ) {

				// Prepare the Data to be sent.
				$product_details = wcap_get_product_details( $cart_history->abandoned_cart_info );

				$send_data = array(
					'id'                 => $cart_history->id,
					'product_details'    => $product_details,
					'timestamp'          => $cart_history->abandoned_cart_time,
					'billing_first_name' => $guest_data->billing_first_name,
					'billing_last_name'  => $guest_data->billing_last_name,
					'billing_country'    => $guest_data->billing_country,
					'billing_zipcode'    => $guest_data->billing_zipcode,
					'email_id'           => $guest_data->email_id,
					'phone'              => $guest_data->phone,
					'source'             => 'checkout',
				);

				$data = array(
					'id'     => $abandoned_cart_id,
					'data'   => $send_data,
					'action' => 'checkout',
				);
				do_action( 'wcap_checkout_record_created', $data );

			}
		}

	}

	/**
	 * Triggers a webhook when reminder email is sent.
	 *
	 * @param int   $abandoned_id - Abandoned Cart ID.
	 * @param int   $sent_id      - Sent ID from the Sent History table.
	 * @param array $links        - List of links sent in the reminder.
	 * @since 8.7.0
	 */
	public static function wcap_reminder_emails( $abandoned_id = 0, $sent_id = 0, $links = array() ) {

		if ( $abandoned_id > 0 && $sent_id > 0 ) {

			// Setup the data.
			$send_data = self::wcap_reminders_webhook_data( $abandoned_id );

			if ( is_array( $send_data ) ) {
				$send_data['links_included'] = $links;
				$send_data['sent_id']        = $sent_id;
				$send_data['reminder_type']  = 'email';

				$data = array(
					'id'     => $abandoned_id,
					'data'   => $send_data,
					'action' => 'sent',
				);

				do_action( 'wcap_reminder_sent_email', $data );
			}
		}
	}

	/**
	 * Returns an array of cart data.
	 *
	 * @param int $abandoned_id - Abandoned Cart ID.
	 * @return array $send_data - Array of Cart Data.
	 * @since 8.7.0
	 */
	public static function wcap_reminders_webhook_data( $abandoned_id ) {

		$cart_history = wcap_get_data_cart_history( $abandoned_id );

		if ( $cart_history ) {
			$user_id   = $cart_history->user_id;
			$user_type = $cart_history->user_type;

			$billing_first_name = '';
			$billing_last_name  = '';
			$email_id           = '';
			$phone              = '';

			if ( $user_id >= 63000000 && 'GUEST' == $user_type ) { //phpcs:ignore.
				$guest_data = wcap_get_data_guest_history( $user_id );

				if ( $guest_data ) {
					$billing_first_name = $guest_data->billing_first_name;
					$billing_last_name  = $guest_data->billing_last_name;
					$email_id           = $guest_data->email_id;
					$phone              = $guest_data->phone;
				}
			} elseif ( 'REGISTERED' == $user_type ) { //phpcs:ignore.

				$billing_first_name = get_user_meta( $user_id, 'billing_first_name', true );
				$billing_last_name  = get_user_meta( $user_id, 'billing_last_name', true );
				$email_id           = get_user_meta( $user_id, 'billing_email', true );
				$phone              = get_user_meta( $user_id, 'billing_phone', true );
			}

			$product_details = wcap_get_product_details( $cart_history->abandoned_cart_info );

			$send_data = array(
				'id'                 => $abandoned_id,
				'product_details'    => $product_details,
				'timestamp'          => $cart_history->abandoned_cart_time,
				'billing_first_name' => $billing_first_name,
				'billing_last_name'  => $billing_last_name,
				'email_id'           => $email_id,
				'phone'              => $phone,
				'user_type'          => $user_type,
			);

			return $send_data;
		}
		return false;
	}

	/**
	 * Triggers a webhook when SMS message is sent.
	 *
	 * @param int $abandoned_id - Abandoned Cart ID.
	 * @param int $template_id  - Template ID.
	 * @since 8.7.0
	 */
	public static function wcap_reminder_sms( $abandoned_id = 0, $template_id = 0 ) {

		if ( $abandoned_id > 0 && $template_id > 0 ) {

			// Get the Links created.
			global $wpdb;
			$links_included = $wpdb->get_results( $wpdb->prepare( 'SELECT id, long_url, short_code FROM ' . WCAP_TINY_URLS . ' WHERE cart_id = %d AND template_id = %d', $abandoned_id, $template_id ) );//phpcs:ignore.

			if ( is_array( $links_included ) && count( $links_included ) > 0 ) {
				$site_url = get_option( 'siteurl' );
				foreach ( $links_included as $link_details ) {
					$encrypted_url = $link_details->short_code;
					$sent_id       = $link_details->id;
					if ( strpos( $link_details->long_url, 'shop' ) > 0 ) {
						$links['shop_link'] = $site_url . "/$encrypted_url";
					} else {
						$links['checkout_link'] = $site_url . "/$encrypted_url";
					}
				}
			} else {
				$links   = array();
				$sent_id = 0;
			}
			// Setup the data.
			$send_data = self::wcap_reminders_webhook_data( $abandoned_id );

			if ( is_array( $send_data ) ) {
				$send_data['links_included'] = $links;
				$send_data['sent_id']        = $sent_id;
				$send_data['reminder_type']  = 'sms';

				$data = array(
					'id'     => $abandoned_id,
					'data'   => $send_data,
					'action' => 'sent',
				);

				do_action( 'wcap_reminder_sent_sms', $data );
			}
		}
	}

	/**
	 * Triggers a webhook when FB Msg is sent.
	 *
	 * @param int $abandoned_id - Abandoned Cart ID.
	 * @param int $template_id  - FB Template ID.
	 * @since 8.7.0
	 */
	public static function wcap_reminder_fb( $abandoned_id = 0, $template_id = 0 ) {

		if ( $abandoned_id > 0 && $template_id > 0 ) {

			// Get the Links created.
			global $wpdb;
			$links_included = $wpdb->get_results( $wpdb->prepare( 'SELECT id, long_url, short_code FROM ' . WCAP_TINY_URLS . ' WHERE cart_id = %d AND template_id = %d', $abandoned_id, $template_id ) );//phpcs:ignore.

			if ( is_array( $links_included ) && count( $links_included ) > 0 ) {
				$site_url = get_option( 'siteurl' );
				foreach ( $links_included as $link_details ) {
					$encrypted_url = $link_details->short_code;
					$sent_id       = $link_details->id;
					if ( strpos( $link_details->long_url, 'shop' ) > 0 ) {
						$links['shop_link'] = $site_url . "/$encrypted_url";
					} else {
						$links['checkout_link'] = $site_url . "/$encrypted_url";
					}
				}
			} else {
				$links   = array();
				$sent_id = 0;
			}
			// Setup the data.
			$send_data = self::wcap_reminders_webhook_data( $abandoned_id );

			if ( is_array( $send_data ) ) {
				$send_data['links_included'] = $links;
				$send_data['sent_id']        = $sent_id;
				$send_data['reminder_type']  = 'fb';

				$data = array(
					'id'     => $abandoned_id,
					'data'   => $send_data,
					'action' => 'sent',
				);

				do_action( 'wcap_reminder_sent_fb', $data );
			}
		}
	}

	/**
	 * Triggers a webhook when a recovery link is accessed.
	 *
	 * @param int    $cart_id - Abandoned Cart ID.
	 * @param string $recovery_medium - Recovery Medium - Email|SMS|FB.
	 * @param string $link_used - Link accessed.
	 * @since 8.7.0
	 */
	public static function wcap_recovery_link( $cart_id, $recovery_medium, $link_used ) {

		if ( $cart_id > 0 ) {

			// Setup the data.
			$send_data = self::wcap_reminders_webhook_data( $cart_id );

			if ( is_array( $send_data ) ) {

				switch ( $recovery_medium ) {
					case 'email_link':
						$send_data['reminder_type'] = 'email';
						break;
					case 'sms_link':
						$send_data['reminder_type'] = 'sms';
						break;
					case 'fb_link':
						$send_data['reminder_type'] = 'fb';
						break;
					default:
						$send_data['reminder_type'] = 'email';
						break;

				}

				$send_data['link_clicked'] = $link_used;
				$send_data['time_clicked'] = current_time( 'timestamp' );

				$data = array(
					'id'     => $cart_id,
					'data'   => $send_data,
					'action' => 'clicked',
				);

				do_action( 'wcap_recovery_link', $data );
			}
		}
	}

	/**
	 * Triggers a webhook when a cart is marked as recovered.
	 *
	 * @param int $abandoned_id - Abandoned Cart ID.
	 * @param int $order_id     - Order ID.
	 * @since 8.7.0
	 */
	public static function wcap_cart_recovered( $abandoned_id, $order_id ) {

		if ( $abandoned_id > 0 && $order_id > 0 ) {

			// Setup the data.
			$send_data = self::wcap_reminders_webhook_data( $abandoned_id );

			if ( is_array( $send_data ) ) {

				$order = wc_get_order( $order_id );

				$send_data['order_id']  = $order_id;
				$send_data['total']     = $order->get_total();
				$send_data['tax_total'] = $order->get_total_tax();
				$data                   = array(
					'id'     => $abandoned_id,
					'data'   => $send_data,
					'action' => 'recovered',
				);

				do_action( 'wcap_abandoned_cart_recovered', $data );
			}
		}
	}

	/**
	 * Triggers a webhook once cart cutoff is reached.
	 *
	 * @param int $abandoned_cart_id - Abandoned Cart ID.
	 * @since 8.7.0
	 */
	public static function wcap_cart_cutoff_reached( $abandoned_cart_id ) {
		if ( $abandoned_cart_id > 0 ) {

			$cart_data = wcap_get_data_cart_history( $abandoned_cart_id );

			if ( $cart_data ) {
				$user_id   = $cart_data->user_id;
				$user_type = $cart_data->user_type;

				$billing_first_name = '';
				$billing_last_name  = '';
				$email_id           = '';
				$phone              = '';
				$billing_country    = '';
				$billing_zipcode    = '';
				$coupon_code        = '';
				$checkout_link      = '';

				if ( 'GUEST' == $user_type && $user_id >= 63000000 ) { //phpcs:ignore
					$guest_data = wcap_get_data_guest_history( $user_id );

					if ( $guest_data ) {
						$billing_first_name = $guest_data->billing_first_name;
						$billing_last_name  = $guest_data->billing_last_name;
						$email_id           = $guest_data->email_id;
						$phone              = $guest_data->phone;
						$billing_country    = $guest_data->billing_country;
						$billing_zipcode    = $guest_data->billing_zipcode;
					}
				} elseif ( 'REGISTERED' == $user_type && $user_id > 0 ) {
					$billing_first_name = get_user_meta( $user_id, 'billing_first_name', true );
					$billing_last_name  = get_user_meta( $user_id, 'billing_last_name', true );
					$email_id           = get_user_meta( $user_id, 'billing_email', true );
					$phone              = get_user_meta( $user_id, 'billing_phone', true );
					$billing_country    = get_user_meta( $user_id, 'billing_country', true );
					$billing_zipcode    = get_user_meta( $user_id, 'billing_country', true );
				}

				$product_details = wcap_get_product_details( $cart_data->abandoned_cart_info );

				$total      = 0;
				$total_tax  = 0;
				$cart_value = json_decode( stripslashes( $cart_data->abandoned_cart_info ) );

				if ( isset( $cart_value->cart ) && count( get_object_vars( $cart_value->cart ) ) > 0 ) { 
					foreach ( $cart_value->cart as $product_data ) {
						$total     += $product_data->line_subtotal;
						$total_tax += $product_data->line_tax;
					}
				}

				$coupon_meta = get_post_meta( $abandoned_cart_id, '_woocommerce_ac_coupon', true );

				if ( is_array( $coupon_meta ) && count( $coupon_meta ) > 0 ) {
					$coupon_code = isset( $coupon_meta['coupon_code'] ) ? $coupon_meta['coupon_code'] : '';
				}
				$checkout_link = $cart_data->checkout_link;

				$send_data = array(
					'id'                 => $abandoned_cart_id,
					'user_id'			 => $user_id,
					'product_details'    => $product_details,
					'total'              => $total,
					'total_tax'          => $total_tax,
					'timestamp'          => $cart_data->abandoned_cart_time,
					'billing_first_name' => $billing_first_name,
					'billing_last_name'  => $billing_last_name,
					'billing_country'    => $billing_country,
					'billing_zipcode'    => $billing_zipcode,
					'email_id'           => $email_id,
					'phone'              => $phone,
					'user_type'          => $user_type,
					'coupon_code'        => $coupon_code,
					'checkout_link'      => $checkout_link,
				);

				$data = array(
					'id'     => $abandoned_cart_id,
					'data'   => $send_data,
					'action' => 'cutoff',
				);

				do_action( 'wcap_abandoned_cart_cutoff', $data );
			}
		}
	}
}
Wcap_Webhooks::init();
