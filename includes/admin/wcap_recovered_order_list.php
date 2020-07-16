<?php
/**
 * Display the recovered order list and the date filter.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Report
 * @since 5.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Recovered_Order_List' ) ) {
    /**
     * Display the recovered order list and the date filter
     */
    class Wcap_Recovered_Order_List{
        /**
         * Display the recovered order list and the date filter.
         * @since 1.0
         */
        public static function wcap_display_recovered_list( ) {
            if( session_id() === '' ){
                //session has not started
                session_start();
            }

            $duration_range = "";
            if ( isset( $_POST['duration_select'] ) ) {
                $duration_range = $_POST['duration_select'];
                $_SESSION['duration']   = $duration_range;
            }
            if ( '' == $duration_range && isset( $_GET['duration_select'] ) ) {
                $duration_range = $_GET['duration_select'];
                $_SESSION['duration']   = $duration_range;
            }
            if ( isset($_SESSION ['duration'] ) && '' != $_SESSION ['duration'] ){
                $duration_range = $_SESSION ['duration'];
            }
            if ( '' == $duration_range ) {
                $duration_range = "last_seven";
                $_SESSION['duration']   = $duration_range;
            }
            $wcap_ac_class  = new Woocommerce_Abandon_Cart();

            ?>
            <p>
                <?php _e( 'The Report below shows how many Abandoned Carts we were able to recover for you by sending automatic emails to encourage shoppers.', 'woocommerce-ac' )  ?>
            </p>
            <div id="recovered_stats_date" class="postbox" style="display:block">
                <div class="inside">
                    <form method="post" action="admin.php?page=woocommerce_ac_page&action=stats" id="ac_stats">
                        <select id="duration_select" name="duration_select" >
                            <?php
                            foreach ( $wcap_ac_class->duration_range_select as $key => $value ) {
                                $sel = "";
                                if ( $key == $duration_range ) {
                                    $sel = __( " selected ", "woocommerce-ac" );
                                }
                                echo"<option value='" . $key . "' $sel> " . __( $value,'woocommerce-ac' ) . " </option>";
                            }
                            $date_sett = $wcap_ac_class->start_end_dates[ $duration_range ];
                            ?>
                        </select>

                        <?php
                        $start_date_range = '';
                        if ( isset( $_POST['start_date'] ) ){
                            $start_date_range = $_POST['start_date'];
                            $_SESSION ['start_date'] = $start_date_range;
                        }

                        if ( isset( $_SESSION ['start_date'] ) &&  '' != $_SESSION ['start_date'] ) {
                            $start_date_range = $_SESSION ['start_date'];
                        }
                        if ( '' == $start_date_range ) {
                            $start_date_range = $date_sett['start_date'];
                            $_SESSION ['start_date'] = $start_date_range;
                        }
                        $end_date_range = '';
                        if ( isset( $_POST['end_date'] ) ) {
                            $end_date_range = $_POST['end_date'];
                            $_SESSION ['end_date'] = $end_date_range;
                        }
                        if ( isset($_SESSION ['end_date'] ) && '' != $_SESSION ['end_date'] ){
                            $end_date_range = $_SESSION ['end_date'];
                        }
                        if ( '' == $end_date_range ) {
                            $end_date_range = $date_sett['end_date'];
                            $_SESSION ['end_date'] = $end_date_range;
                        }
                        ?>
                        <label class="start_label" for="start_day"> <?php _e( 'Start Date:', 'woocommerce-ac' ); ?> </label>
                        <input type="text" id="start_date" name="start_date" readonly="readonly" value="<?php echo $start_date_range; ?>"/>

                        <label class="end_label" for="end_day"> <?php _e( 'End Date:', 'woocommerce-ac' ); ?> </label>
                        <input type="text" id="end_date" name="end_date" readonly="readonly" value="<?php echo $end_date_range; ?>"/>

                        <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Go', 'woocommerce-ac' ); ?>"  />
                    </form>
                </div>
            </div>
            <?php
            $wcap_all_recovered_carts = $wcap_trash_recovered = $section = "";
            if ( isset( $_GET[ 'wcap_section' ] ) ) {
                $section = $_GET[ 'wcap_section' ];
            } else {
                $section = '';
            }
            if ( '' == $section || 'wcap_all_rec' == $section ) {
                $wcap_all_recovered_carts = "current";
            }
            if ( 'wcap_trash_rec' == $section ) {
                $wcap_trash_recovered = "current";
            }
            if ( '' != $_SESSION ['start_date'] && isset( $_SESSION ['start_date'] ) ) {
                $start_date_range = $_SESSION ['start_date'];
            }
            if ( '' != $_SESSION ['end_date'] && isset($_SESSION ['end_date'] ) ) {
                $end_date_range = $_SESSION ['end_date'];
            }
            $get_all_recovered_count   = Wcap_Common::wcap_get_reovered_order_count ( $start_date_range, $end_date_range, 'wcap_all_rec' );
            $get_trash_recovered_count = Wcap_Common::wcap_get_reovered_order_count ( $start_date_range, $end_date_range, 'wcap_trash_rec' );
            if ( '' == $section || 'wcap_all_rec' == $section ) {

                $wcap_recover_orders_list = new Wcap_Recover_Orders_Table();
            } else if ( 'wcap_trash_rec' == $section ) {

                $wcap_recover_orders_list = new Wcap_Recover_Trash_Orders_Table();
            }
            ?>
                <div id="recovered_stats" class="postbox" style="display:block">
                    <?php
                        $wcap_recover_orders_list->wcap_recovered_orders_prepare_items();
                    ?>
                    <div class="inside" >
                        <p style="font-size: 15px"><?php  _e( 'During the selected range ', 'woocommerce-ac' ); ?>
                            <strong>
                                <?php $count = $wcap_recover_orders_list->total_abandoned_cart_count;
                                      echo $count; ?>
                            </strong>
                            <?php _e( 'carts totaling', 'woocommerce-ac' ); ?>
                            <strong>
                                <?php $total_of_all_order = $wcap_recover_orders_list->total_order_amount;
                                echo $total_of_all_order; ?>
                             </strong>
                             <?php _e( ' were abandoned. We were able to recover', 'woocommerce-ac' ); ?>
                             <strong>
                                <?php
                                    $recovered_item = $wcap_recover_orders_list->recovered_item;
                                    echo $recovered_item;
                                ?>
                             </strong>
                             <?php _e( ' of them, which led to an extra', 'woocommerce-ac' ); ?>
                             <strong>
                                <?php
                                    $recovered_total = $wcap_recover_orders_list->total_recover_amount;
                                    echo wc_price ( $recovered_total ); ?>
                             </strong>
                               <?php

                                ?>
                         </p>
                    </div>
                </div>
                <ul class="subsubsub" id="wcap_recovered_orders_list">
                    <li>
                        <a href="admin.php?page=woocommerce_ac_page&action=stats&wcap_section=wcap_all_rec" class="<?php echo $wcap_all_recovered_carts; ?>"><?php _e( "All", 'woocommerce-ac' ) ;?>  <span class = "count" > <?php echo "( $get_all_recovered_count )" ?> </a>
                    </li>
                    <?php if ($get_trash_recovered_count > 0 ) { ?>
                       <li>
                       | <a href="admin.php?page=woocommerce_ac_page&action=stats&wcap_section=wcap_trash_rec" class="<?php echo $wcap_trash_recovered; ?>"><?php _e( "Trash", 'woocommerce-ac' );?>  <span class = "count" > <?php echo "( $get_trash_recovered_count )" ?> </a>
                    </li>
                    <?php } ?>
                </ul>
                <br class="clear">
                <?php
                if ( '' == $section || 'wcap_all_rec' == $section ) {
                ?>
                <div class="wrap">
                    <form id="wcap-recover-orders" method="get" >
                        <input type="hidden" name="page" value="woocommerce_ac_page" />
                        <input type="hidden" name="action" value="stats" />
                        <input type="hidden" name="wcap_action" value="stats" />
                        <?php $wcap_recover_orders_list->display(); ?>
                    </form>
                </div>
                <?php
            } else if ( 'wcap_trash_rec' == $section ) {
                ?>
                <div class="wrap">
                    <form id="wcap-recover-orders" method="get" >
                        <input type="hidden" name="page" value="woocommerce_ac_page" />
                        <input type="hidden" name="action" value="stats" />
                        <input type="hidden" name="wcap_action" value="stats" />
                        <?php $wcap_recover_orders_list->display(); ?>
                    </form>
                </div>
                <?php
            }
        }
    }
}
