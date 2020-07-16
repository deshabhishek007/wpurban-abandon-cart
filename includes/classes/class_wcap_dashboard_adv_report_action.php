<?php
/**
 * It will show the dashboard data.
 * @package     Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @since       3.5
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * It will show the dashboard data.
 */
class Wcap_Advanced_Report_Action {

    /**
     * It will generate all the reports for the Dashboard.
     * It will show charts, ATC stats, Template stats.
     * @param string $selected_data_range Selected range of date
     * @param string $start_date Start Date
     * @param string $end_date End date
     * @globals mixed $wpdb
     * @since 3.5
     */
    function wcap_get_all_reports( $selected_data_range, $start_date, $end_date ){

        global $wpdb;

        include_once( 'class_wcap_dashboard_report.php' );

        $wcap_month_total_orders_amount   = $wcap_month_recovered_cart_amount = 0;
        $wcap_month_recovered_cart_count  = $wcap_month_abandoned_cart_count  = 0;
        $ratio_of_recovered_number        = 0;
        $wcap_month_wc_orders             = 0;
        $ratio_of_recovered               = 0;
        $ratio_of_total_vs_abandoned      = 0;
        $wcap_atc_data                    = array();
        $orders = new Wcap_Dashboard_Report();

        $wcap_month_total_orders_amount   = $orders->get_this_month_amount_reports( 'wc_total_sales' , $selected_data_range, $start_date, $end_date );
        $wcap_month_recovered_cart_amount = $orders->get_this_month_amount_reports( 'recover'        , $selected_data_range, $start_date, $end_date );

        // if total order amount goes less than zero, then set it to 0.
        if ( $wcap_month_total_orders_amount < 0 ){

            $wcap_month_total_orders_amount = 0 ;
        }
        if ( $wcap_month_recovered_cart_amount > 0 && $wcap_month_total_orders_amount > 0 ){
            $ratio_of_recovered            = ( $wcap_month_recovered_cart_amount / $wcap_month_total_orders_amount ) * 100;
            $ratio_of_recovered            = round( $ratio_of_recovered, wc_get_price_decimals() );
        }

        /**
         * Stats structure
         * 
         * array(
         *   'abandoned_count' => $abandoned_count,
         *   'recovered_count' => $recovered_count,
         *   'abandoned_amount' => $abandoned_amount,
         *   'recovered_amount' => $recovered_amount
         * );
         */
        $stats = $orders->get_adv_stats( $selected_data_range, $start_date, $end_date );

        $wcap_month_abandoned_cart_count   = $stats['abandoned_count'];
        $wcap_month_recovered_cart_count   = $stats['recovered_count'];

        if ( $wcap_month_recovered_cart_count > 0 && $wcap_month_abandoned_cart_count > 0 ){
            $ratio_of_recovered_number     = ( $wcap_month_recovered_cart_count / $wcap_month_abandoned_cart_count ) * 100;
            $ratio_of_recovered_number     = round( $ratio_of_recovered_number, wc_get_price_decimals() );
        }

        $wcap_month_wc_orders              = $orders->get_this_month_total_vs_abandoned_order( 'wc_total_orders', $selected_data_range, $start_date, $end_date );

        if ( $wcap_month_abandoned_cart_count > 0 && $wcap_month_wc_orders > 0 ){
            $ratio_of_total_vs_abandoned   = ( $wcap_month_abandoned_cart_count / $wcap_month_wc_orders  ) * 100;
            $ratio_of_total_vs_abandoned   = round( $ratio_of_total_vs_abandoned, wc_get_price_decimals() );
        }

        $wcap_email_sent_count             = $orders->wcap_get_email_report( "total_sent", $selected_data_range, $start_date, $end_date );

        $wcap_email_opened_count           = $orders->wcap_get_email_report( "total_opened", $selected_data_range, $start_date, $end_date );

        $wcap_email_clicked_count          = $orders->wcap_get_email_report( "total_clicked", $selected_data_range, $start_date, $end_date );

        $wcap_atc_data                     = $orders->wcap_get_atc_data_of_range( $selected_data_range, $start_date, $end_date );

        $graph_data = $orders->get_abandoned_data( $selected_data_range, $start_date, $end_date );

        wp_localize_script(
            'wcap_graph_js', 
            'wcap_graph_data', 
            array(
                'data'   => $graph_data,
            ) 
        );

        wp_enqueue_script ( 'wcap_graph_js' );

    ?>
        <br>
        <div class = "wcap_dashboard_report_filter">
            <form id="wcap_report_search" method="get" >
            <input type="hidden" name="page" value="woocommerce_ac_page" />
                <?php
                $this->search_by_date();
                ?>
            </form>
        </div>

        <!-- Recovered Reports -->
        <div class="container-fluid">
            <div class="side-body">
                <div class="row">

                    <!-- Blue Panel -->
                    <div class="col-lg-3 col-md-3 col-sm-12">
                        <div class="card panel-primary wcap-center">
                            <div class="card-header panel-heading">
                                <div class="huge padding-25">
                                    <?php echo get_woocommerce_currency_symbol() . $wcap_month_recovered_cart_amount; ?>
                                </div>
                            </div>
                            <div class="card-body panel-heading panel-body">
                                <div class="body-label">
                                    <?php _e( 'Recovered Amount', 'woocommerce-ac' ); ?>
                                </div>
                            </div>
                            <div class="card-footer panel-footer">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#recoveredDetails" aria-expanded="true" aria-controls="recoveredDetails">
                                    <span class="pull-left"><?php _e( 'View Details', 'woocommerce-ac' ); ?></span> &nbsp;
                                    <span class="pull-right">
                                        <i class="fa fa-arrow-circle-right"></i>
                                    </span>
                                    <div class="clearfix"></div>
                                </button>
                                <div id="recoveredDetails" class="collapse" aria-labelledby="headingOne">
                                    <div class="card-body">
                                        <span><?php printf( __( '<strong>%s</strong> Recovered Orders', 'woocommerce-ac' ), $wcap_month_recovered_cart_count ); ?></span>
                                        <br>
                                        <span><?php printf( __( '<strong>%s%%</strong> of Abandoned Carts Recovered', 'woocommerce-ac' ), $ratio_of_recovered_number ); ?></span>
                                        <br>
                                        <span><?php printf( __( '<strong>%s%%</strong> of Total Revenue', 'woocommerce-ac' ), $ratio_of_recovered ); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Red Panel -->
                    <div class="col-lg-3 col-md-3 col-sm-12">
                        <div class="card panel-red wcap-center">
                            <div class="card-header panel-heading">
                                <div class="huge padding-25">
                                    <?php echo $wcap_month_abandoned_cart_count; ?>
                                </div>
                            </div>
                            <div class="card-body panel-heading panel-body">
                                <div class="body-label">
                                    <?php _e( 'Abandoned Orders', 'woocommerce-ac' ); ?>
                                </div>
                            </div>
                            <div class="card-footer panel-footer">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#abandonedCount" aria-expanded="true" aria-controls="abandonedCount">
                                    <span class="pull-left"><?php _e( 'View Details', 'woocommerce-ac' ); ?></span> &nbsp;
                                    <span class="pull-right">
                                        <i class="fa fa-arrow-circle-right"></i>
                                    </span>
                                    <div class="clearfix"></div>
                                </button>
                                <div id="abandonedCount" class="collapse" aria-labelledby="headingOne">
                                    <div class="card-body">
                                        <span><?php printf( __( '<strong>%s%s</strong> amount of Abandoned Orders', 'woocommerce-ac' ), get_woocommerce_currency_symbol(), round( $stats['abandoned_amount'], wc_get_price_decimals() ) ); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Green Panel -->
                    <div class="col-lg-3 col-md-3 col-sm-12">
                        <div class="card panel-green wcap-center">
                            <div class="card-header panel-heading">
                                <div class="huge padding-25">
                                    <?php echo $wcap_email_sent_count; ?>
                                </div>
                            </div>
                            <div class="card-body panel-heading panel-body">
                                <div class="body-label">
                                    <?php _e( 'Number of Emails Sent', 'woocommerce-ac' ); ?>
                                </div>
                            </div>
                            <div class="card-footer panel-footer">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#emailsCount" aria-expanded="true" aria-controls="emailsCount">
                                    <span class="pull-left"><?php _e( 'View Details', 'woocommerce-ac' ); ?></span> &nbsp;
                                    <span class="pull-right">
                                        <i class="fa fa-arrow-circle-right"></i>
                                    </span>
                                    <div class="clearfix"></div>
                                </button>
                                <div id="emailsCount" class="collapse" aria-labelledby="headingOne">
                                    <div class="card-body">
                                        <span><?php echo $wcap_email_opened_count; ?> Emails Opened</span><br>
                                        <span><?php echo $wcap_email_clicked_count; ?> Emails Clicked</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Yellow Panel -->
                    <div class="col-lg-3 col-md-3 col-sm-12">
                        <div class="card panel-yellow wcap-center">
                            <div class="card-header panel-heading">
                                <div class="huge padding-25">
                                    <?php echo $wcap_atc_data[ 'wcap_atc_open' ]; ?>
                                </div>
                            </div>
                            <div class="card-body panel-heading panel-body">
                                <div class="body-label">
                                    <?php _e( 'Email Capture Pop Displayed', 'woocommerce-ac' ); ?>
                                </div>
                            </div>
                            <div class="card-footer panel-footer">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#atcCount" aria-expanded="true" aria-controls="atcCount">
                                    <span class="pull-left"><?php _e( 'View Details', 'woocommerce-ac' ); ?></span> &nbsp;
                                    <span class="pull-right">
                                        <i class="fa fa-arrow-circle-right"></i>
                                    </span>
                                    <div class="clearfix"></div>
                                </button>
                                <div id="atcCount" class="collapse" aria-labelledby="headingOne">
                                    <div class="card-body">
                                        <span><?php echo $wcap_atc_data[ 'wcap_has_email' ]; ?> Emails Captured</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chartgraph"></div>

                <div class="wcap-center">
                    <?php _e( 'Looking for the older dashboard? <a href="admin.php?page=woocommerce_ac_page&action=wcap_dashboard">Click here</a> to switch to the older view', 'woocommerce-ac' ); ?>
                </div>
            </div>
        </div>

        <?php
    }

    /**
     * It will display the search filter on the dashboard.
     * @since 3.5
     */
    public function search_by_date(  ) {

        $this->duration_range_select = array(

            'this_month'   => __( 'This Month'   , 'woocommerce-ac' ),
            'last_month'   => __( 'Last Month'   , 'woocommerce-ac' ),
            'this_quarter' => __( 'This Quarter' , 'woocommerce-ac' ),
            'last_quarter' => __( 'Last Quarter' , 'woocommerce-ac' ),
            'this_year'    => __( 'This Year'    , 'woocommerce-ac' ),
            'last_year'    => __( 'Last Year'    , 'woocommerce-ac' ),
            'other'        => __( 'Custom'       , 'woocommerce-ac' ),
        );
        if ( isset( $_GET['duration_select'] ) ) {
            $duration_range = $_GET['duration_select'];
        }else{
            $duration_range = "this_month";
        }
        ?>
        <div class = "main_start_end_date" id = "main_start_end_date" >
            <div class = "filter_date_drop_down" id = "filter_date_drop_down" >
                <label class="date_time_filter_label" for="date_time_filter_label" >
                    <strong>
                        <?php _e( "Select date range:", "woocommerce-ac"); ?>
                    </strong>
                </label>

                <select id=duration_select name="duration_select" >
                    <?php
                    foreach ( $this->duration_range_select as $key => $value ) {
                        $sel = "";
                        if ( $key == $duration_range ) {
                            $sel = __( "selected ", "woocommerce-ac" );
                        }
                        echo"<option value='" . $key . "' $sel> " . __( $value,'woocommerce-ac' ) . " </option>";
                    }
                    ?>
                </select>
                <?php

                $start_date_range = "";
                if ( isset( $_GET['wcap_start_date'] ) ) {
                    $start_date_range = $_GET['wcap_start_date'];
                }

                $end_date_range = "";
                if ( isset( $_GET['wcap_end_date'] ) ){
                    $end_date_range = $_GET['wcap_end_date'];
                }
                $start_end_date_div_show = 'block';
                if ( !isset($_GET['duration_select']) || $_GET['duration_select'] != 'other' ) {
                    $start_end_date_div_show = 'none';
                }
                ?>

                <div class = "wcap_start_end_date_div" id = "wcap_start_end_date_div" style="display: <?php echo $start_end_date_div_show; ?>;"  >
                    <input type="text" id="wcap_start_date" name="wcap_start_date" readonly="readonly" value="<?php echo $start_date_range; ?>" placeholder="yyyy-mm-dd"/>
                    <input type="text" id="wcap_end_date" name="wcap_end_date" readonly="readonly" value="<?php echo $end_date_range; ?>" placeholder="yyyy-mm-dd"/>
                </div>
                <div id="wcap_submit_button" class="wcap_submit_button">
                    <?php submit_button( __( 'Go', 'woocommerce-ac' ), 'button', false, false, array('ID' => 'wcap-search-by-date-submit' ) ); ?>
                </div>
            </div>
        </div>

       <?php
    }
}
