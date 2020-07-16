<?php

class Wcap_Process_Base {

    /**
     * Construct.
     */
	public function __construct() {

		$wcap_auto_cron = get_option( 'wcap_use_auto_cron' );
		if ( isset( $wcap_auto_cron ) && $wcap_auto_cron != false && '' != $wcap_auto_cron ) {
			// Hook into that action that'll fire based on the cron frequency.
			add_action( 'woocommerce_ac_send_email_action', array( &$this, 'wcap_process_handler' ), 11 );
		}
	}

    /**
     * Execute functions to send reminders.
     */
	public function wcap_process_handler() {

		// Check if reminders are enabled.
		$reminders_list = wcap_get_enabled_reminders();

		if ( is_array( $reminders_list ) && count( $reminders_list ) > 0 ) {
			foreach ( $reminders_list as $reminder_type ) {
				switch ( $reminder_type ) {
					case 'emails':
						Wpuac_Send_Email_Using_Cron::wcap_abandoned_cart_send_email_notification();
						break;
					case 'sms':
						Wpuac_Send_Email_Using_Cron::wcap_send_sms_notifications();
						break;
					case 'fb':
						WCAP_FB_Recovery::wcap_fb_cron();
						break;
				}
			}
		}

	}

}
new Wcap_Process_Base();

