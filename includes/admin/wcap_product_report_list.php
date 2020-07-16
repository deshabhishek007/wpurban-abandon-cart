<?php

/**
 * It will display the Product report tab data.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Report
 * @since 5.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Product_Report_List' ) ) {
    /**
     * It will display the Product report tab data
     */
    class Wcap_Product_Report_List{
        /**
         * It will display the Product report tab data.
         * @since 2.3.7
         */
        public static function wcap_display_product_report_list( ){

            $wcap_product_report_list = new Wcap_Product_Report_Table();
            $wcap_product_report_list->wcap_product_report_prepare_items();
            ?>
            <div class="wrap">
                <form id="wcap-sent-emails" method="get" >
                    <input type="hidden" name="page" value="woocommerce_ac_page" />
                    <input type="hidden" name="action" value="report" />
                    <input type="hidden" name="wcap_action" value="report" />
                        <?php $wcap_product_report_list->display(); ?>
                </form>
            </div>
            <?php
        }
    }
}
