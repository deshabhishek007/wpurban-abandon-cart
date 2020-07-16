<?php
/**
 * This class will add messages as needed informing users of data being tracked.
 *
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @since 7.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'Wcap_Tracking_msg' ) ) {

	/**
	 * It will add messages as needed informing users of data being tracked.
	 */
	class Wcap_Tracking_Msg {

		/**
		 * Construct.
		 */
		public function __construct() {
			// Product page notice for logged in users.
			add_action( 'woocommerce_after_add_to_cart_button', array( &$this, 'wcap_add_logged_msg' ), 10 );
		}

		/**
		 * Adds a message to be displayed for logged in users
		 * Called on Shop & Product page
		 *
		 * @hook woocommerce_after_add_to_cart_button
		 *       woocommerce_before_shop_loop
		 * @since 7.8
		 */
		public static function wcap_add_logged_msg() {
			if ( is_user_logged_in() ) {

				$registered_msg = get_option( 'wcap_logged_cart_capture_msg' );
				$gdpr_consent   = get_user_meta( get_current_user_id(), 'wcap_gdpr_tracking_choice', true );

				if ( '' === $gdpr_consent ) {
					$gdpr_consent = true;
				}

				if ( isset( $registered_msg ) && '' !== $registered_msg && $gdpr_consent ) {
					$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
					wp_enqueue_script(
						'wcap_registered_capture',
						WCAP_PLUGIN_URL . '/assets/js/frontend/wcap_registered_user_capture' . $suffix . '.js',
						'',
						WCAP_PLUGIN_VERSION,
						true
					);

					$vars = array(
						'_gdpr_after_no_thanks_msg' => htmlspecialchars( get_option( 'wcap_gdpr_opt_out_message' ), ENT_QUOTES ),
						'ajax_url'                  => WCAP_ADMIN_AJAX_URL,
					);

					wp_localize_script(
						'wcap_registered_capture',
						'wcap_registered_capture_params',
						$vars
					);

					$registered_msg .= " <span id='wcap_gdpr_no_thanks'><a style='cursor: pointer' id='wcap_gdpr_no_thanks'>" . htmlspecialchars( get_option( 'wcap_gdpr_allow_opt_out' ), ENT_QUOTES ) . '</a></span>';
					echo "<span id='wcap_gdpr_message_block'><p><small>" . wp_kses_post( $registered_msg ) . '</small></p></span>';
				}
			}
		}

	} // end of class
	$wcap_tracking_msg = new Wcap_Tracking_Msg();
} // end IF
