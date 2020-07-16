<?php

/**
 * License missing template. To be displayed on pages where license is mandatory 
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/License
 * @since 6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

<div class="wcap_license_invalid" style="text-align: center;">
    <img src="https://www.tychesoftwares.com/wp-content/themes/tyche-softwares/assets/images/icons/Tyche-plugins-SHOPPINGCART.png" height="150px" width="150px"> 
    <p style='font-size:12pt; color:#b00606;'>
        <?php _e( 'Please activate your license to use this feature. License can be activated from the Settings tab above or by clicking <a href="admin.php?page=woocommerce_ac_page&action=emailsettings&wcap_section=wcap_license_settings">here.</a> Then navigate to <strong>Plugin License Options.</strong>', 'woocommerce-ac' ); ?>
    </p>
    <p style='font-size:12pt; color:#b00606;'>
        <?php _e( 'If you facing any issues in activating the license then do contact us on <a href="mailto:support@tychesoftwares.freshdesk.com">support@tychesoftwares.freshdesk.com</a> and we shall be more than happy to help you.' ) ?>
    </p>
    <p style='font-size:12pt; color:#b00606;'>
        <?php _e( 'If your license key is expired or if you don\'t have a valid license key, you will need to purchase a new license of the plugin. Head over to our site to purchase a license of <strong>Abandoned Cart Pro for WooCommerce</strong>', 'woocommerce-ac' ); ?>
    </p>
    <a href="https://www.tychesoftwares.com/store/premium-plugins/woocommerce-abandoned-cart-pro/" class="button button-primary" target="_blank">
        <?php _e( 'Buy Abandoned Cart Pro', 'woocommerce-ac' ); ?>
    </a>
</div>