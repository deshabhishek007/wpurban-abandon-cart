<?php

/**
 * Add to Cart popup modal template, it wll be displayed on shop, category, and products pages. 
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Frontend/ATC-Template
 * @since 6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
<div class = "wcap_container" id = "wcap_popup_main_div">
    <div class = "wcap_popup_wrapper">
        <div class = "wcap_popup_content">
            <div class = "wcap_popup_heading_container">
                <div class = "wcap_popup_icon_container" >
                    <span class = "wcap_popup_icon" v-model = "wcap_atc_button" >
                        <span class = "wcap_popup_plus_sign" v-bind:style = "wcap_atc_button">
                        </span>
                    </span>
                </div>
                <div class = "wcap_popup_text_container">
                    <h2 class = "wcap_popup_heading" v-model = "wcap_heading_section_text_email" v-bind:style = "wcap_atc_popup_heading" >{{wcap_heading_section_text_email}}</h2>
                    <div class = "wcap_popup_text" v-bind:style = "wcap_atc_popup_text" v-model = "wcap_text_section_text_field" >{{wcap_text_section_text_field}}
                    </div>
                </div>
            </div>
            <div class = "wcap_popup_form">
                <form action = "" name = "wcap_modal_form">
                    <?php
                    // check if any message is present in the settings
                    $guest_msg = get_option( 'wcap_guest_cart_capture_msg' );
                    
                    if( isset( $guest_msg ) && '' != $guest_msg ) {
                        ?>
                        <p><small><?php _e( $guest_msg, 'woocommerce-ac' ); ?></small></p>
                        <?php 
                    } 
                    ?>
                    <div class = "wcap_popup_input_field_container"  >
                        <input class = "wcap_popup_input" id = "wcap_popup_input" type = "text" name = "wcap_email" v-bind:placeholder = wcap_email_placeholder_section_input_text>
                    </div>
                    <span id = "wcap_placeholder_validated_msg" class = "wcap_placeholder_validated_msg" > Please enter a valid email address.</span>

                    <?php do_action( 'wcap_atc_after_email_field' ); ?>

                    <button class = "wcap_popup_button" v-bind:style = "wcap_atc_button" v-model = "wcap_button_section_input_text">{{wcap_button_section_input_text}}
                    </button>

                    <div class="clear"></div>

                    <?php if ( get_option( 'wcap_atc_mandatory_email' ) !== 'on' ) : ?>
                        <div id ="wcap_non_mandatory_text_wrapper" class = "wcap_non_mandatory_text_wrapper">
                            <a class = "wcap_popup_non_mandatory_button" href = "" v-model = "wcap_non_mandatory_modal_input_text" > {{wcap_non_mandatory_modal_input_text}}
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if ( 'on' === get_option( 'wcap_atc_auto_apply_coupon_enabled', '' ) && '' !== get_option( 'wcap_atc_coupon_type', '' ) ) { ?>
                        <div id="wcap_coupon_auto_applied" class="woocommerce-info">
                            <p class="wcap_atc_coupon_auto_applied_msg" > {{wcap_atc_coupon_applied_msg}} </p>
                        </div>
                    <?php } ?>
                </form>
            </div>
            <div class = "wcap_popup_close" ></div>
        </div>
    </div>
</div>