<?php
/**
 *
 * Abandoned Cart Pro for WooCommerce
 *
 * It will show record of Add To Cart popup Modal on Dashboard tab.
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @category Classes
 * @since    6.0
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Wcap_Atc_Dashboard' ) ) {
    
    /**
     * Display states of Add To Cart Popup Modal on Dashboard.
     *
     * @since 6.0
     */
    class Wcap_Atc_Dashboard {

        /**
         * It will count Collected emails Add To Cart Popup Modal, Opened Add to Cart Modal  on Dashboard.
         *
         * @param int $wcap_atc_data count of Add To Cart Popup
         * @since 6.0
         */
        public static function wcap_display_atc_dashboard( $wcap_atc_data ) { 
            $wcap_total_atc_open                = ( isset( $wcap_atc_data[ 'wcap_atc_open' ] )  )  ? $wcap_atc_data[ 'wcap_atc_open' ] : 0  ;
            $wcap_total_atc_has_email           = ( isset ( $wcap_atc_data[ 'wcap_has_email' ] ) )  ? $wcap_atc_data[ 'wcap_has_email' ] : 0 ;
            $wcap_total_atc_not_has_email       = ( isset( $wcap_atc_data[ 'wcap_not_has_email' ] ) ) != '' ? $wcap_atc_data[ 'wcap_not_has_email' ] : 0;
            $wcap_ratio_email_captured_from_atc = 0;
            if ( $wcap_total_atc_open > 0 && $wcap_total_atc_has_email > 0 ) {
                $wcap_ratio_email_captured_from_atc = ( $wcap_total_atc_has_email / $wcap_total_atc_open ) * 100;
                $wcap_wc_decimal                    = get_option( 'woocommerce_price_num_decimals' );
                $wcap_ratio_email_captured_from_atc = round ( $wcap_ratio_email_captured_from_atc, $wcap_wc_decimal );
            }
            Wcap_Atc_Dashboard::wcap_display_all_data( $wcap_total_atc_open, $wcap_total_atc_has_email, $wcap_ratio_email_captured_from_atc );
        }

        /**
         * Show Collected emails Add To Cart Popup Modal, Opened Add to Cart Modal and Conversion Ratio on Dashboard.
         *
         * @param int $wcap_total_atc_open count of Add To Cart Popup
         * @param int $wcap_total_atc_has_email Collected emails
         * @param int $wcap_ratio_email_captured_from_atc Conversion Rate
         * @since 6.0
         */
        public static function wcap_display_all_data( $wcap_total_atc_open, $wcap_total_atc_has_email, $wcap_ratio_email_captured_from_atc ) {
            ?>
            <span class = "wcap_atc_heading"> <h1> Add To Cart Popup </h1></span>
            <br>
            <div class = "wcap_three_cell">
                <a href = "admin.php?page=woocommerce_ac_page&action=emailsettings&wcap_section=wcap_atc_settings" class = "wcap_redirect_atc_setting button button-primary"> Edit Settings </a>
                <div class = "top">
                    <div class = "wcap_cell_box">
                        <span class = "wcap_num"><?php echo $wcap_total_atc_open; ?></span>
                        <span class = "wcap_label">Add to Carts</span>
                    </div>
                    <div class = "wcap_cell_box">
                        <span class = "wcap_num"><?php echo $wcap_total_atc_has_email; ?></span>
                        <span class = "wcap_label">Collected Emails</span>
                    </div>
                </div>
                <div class = "wcap_graphic"></div>
                <div class = "wcap_bottom">
                    <div class = "wcap_cell_box">
                        <span class = "wcap_num"><?php echo $wcap_ratio_email_captured_from_atc . '%' ?></span>
                        <span class = "wcap_label">Conversion Rate</span>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}