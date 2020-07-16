<?php
/**
 * It will display the WordPress widget for abandoned and recovered carts.
 * @author   Tyche Softwares
 * @package     Abandoned-Cart-Pro-for-WooCommerce/Admin/Report
 * @since       2.7
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Dashboard_Widget' ) ) {
	/**
	 * It will display the WordPress widget for abandoned and recovered carts.
	 */
    class Wcap_Dashboard_Widget {
		/**
         * Registers the dashboard widgets.
         * @since 2.7
         */
        public static function wcap_register_dashboard_widget() {
        	if ( current_user_can( 'manage_woocommerce' ) ) {
        		wp_add_dashboard_widget( 'abandoned_dashboard_carts', __('Abandoned & Recovered Carts Summary','woocommerce-ac' ), array ( __CLASS__ , 'abandoned_dashboard_carts_widget' ) );
        	}
        }

        /**
         * Abandoned & Recovered Carts Summary Dashboard Widget
         *
         * Builds and renders the Abandoned & Recovered Carts Summary dashboard widget. This widget displays
         * the current month's Abandoned carts, Recovered Carts, and Total COnversion Rate in Summary.
         * Also, it will display top abandoned product name & top recovered product
         * Also, it wil display Last 3 abandoned carts.
         * @since 2.7
         *
         */
        public static function abandoned_dashboard_carts_widget( ) {
        	echo '<p><img src=" ' . esc_attr( set_url_scheme( plugins_url() . '/woocommerce-abandon-cart-pro/assets/images/loading.gif', 'relative' ) ) . '"/></p>';
        }

        /**
         * Loads the dashboard sales widget via ajax.
         * @since 2.7
         */
        public static function wcap_dashboard_widget_report( ) {

            if ( ! current_user_can( "manage_woocommerce" ) ){
        		die();
        	}

        	$reports = new Wcap_Dashboard_Widget_Report; ?>
        	<div class="wcap_dashboard_report_widget">
        		<div class="table table_left table_current_month">
        			<table>
        				<thead>
        					<tr>
        						<td colspan="2"><?php _e( 'Current Month', 'woocommerce-ac' ) ?></td>
        					</tr>
        				</thead>
        				<tbody>
        					<tr>
        						<td class="first t monthly_earnings"><?php _e( 'Abandoned Carts', 'woocommerce-ac' ); ?></td>
        						<td class="wcap b-earnings"><?php
        						    $abandoned_this_month = $reports->get_this_month_reports( 'abandoned' );
            						echo $abandoned_this_month;
            						?>
        						</td>
        					</tr>
        					<tr>
        						<?php $recover_this_month = $reports->get_this_month_reports( 'recover' ); ?>
        						<td class="first t monthly_sales"><?php echo __( 'Recovered Carts', 'woocommerce-ac' ); ?></td>
        						<td class="wcap b-sales"><?php echo $recover_this_month; ?></td>
        					</tr>

        					<tr>
        						<?php $ratio_this_month = $reports->get_this_month_reports( 'ratio' ); ?>
        						<td class="first t monthly_sales"><?php echo __( 'Conversion Ratio','woocommerce-ac' ); ?></td>
        						<td class="wcap b-sales-ratio"><?php echo round( $ratio_this_month,2 ).'%' ; ?></td>
        					</tr>
        				</tbody>
        			</table>

        			 <table>
        				<thead>
        					<tr>
        						<td colspan="2"><?php _e( 'Last Month', 'woocommerce-ac' ) ?></td>
        					</tr>
        				</thead>
        				<tbody>
        					<tr>
        						<td class="first t earnings"><?php echo __( 'Abandoned Carts', 'woocommerce-ac' ); ?></td>
        						<td class="wcap b-last-month-earnings"><?php
            						    $abandoned_last_month = $reports->get_last_month_reports( 'abandoned' );
                                        echo $abandoned_last_month;
        						    ?>
        						</td>
        					</tr>
        					<tr>
        						<td class="first t sales">
        							<?php echo __( 'Recovered Carts',  'woocommerce-ac' ); ?>
        						</td>
        						<td class="wcap b-last-month-sales">
        							<?php
            						    $recover_last_month = $reports->get_last_month_reports( 'recover' );
                                        echo $recover_last_month;
        						    ?>
        						</td>
        					</tr>

        					<tr>
        						<td class="first t sales">
        							<?php echo __( 'Conversion Ratio',  'woocommerce-ac' ); ?>
        						</td>
        						<td class="wcap b-last-month-sales">
        							<?php
            						    $conversion_last_month = $reports->get_last_month_reports( 'ratio' );
                                        echo round( $conversion_last_month, 2) .'%';
        						    ?>
        						</td>
        					</tr>
        				</tbody>
        			</table>
        		</div>

        		<div class="table table_right table_today">
        			<table>
        				<thead>
        					<tr>
        						<td colspan="2">
        							<?php _e( 'Today', 'woocommerce-ac' ); ?>
        						</td>
        					</tr>
        				</thead>
        				<tbody>
        					<tr>
        						<td class="t sales"><?php _e( 'Abandoned Carts', 'woocommerce-ac' ); ?></td>
        						<td class="wcap b-earnings">
        							<?php $abandoned_today = $reports->get_today_reports( 'abandoned' ); ?>
        							<?php echo $abandoned_today; ?>
        						</td>
        					</tr>
        					<tr>
        						<td class="t sales">
        							<?php _e( 'Recovered Carts', 'woocommerce-ac' ); ?>
        						</td>
        						<td class="wcap b-sales">
        							<?php $recovered_today = $reports->get_today_reports( 'recover' ); ?>
        							<?php echo  $recovered_today; ?>
        						</td>
        					</tr>

        					<tr>
        						<td class="t sales">
        							<?php _e( 'Conversion Ratio', 'woocommerce-ac' ); ?>
        						</td>
        						<td class="wcap b-sales-ratio">
        							<?php $ratio_today = $reports->get_today_reports( 'ratio' );

        							?>
        							<?php echo  round( $ratio_today, 2 ) .'%' ; ?>
        						</td>
        					</tr>

        				</tbody>
        			</table>
        		</div>

        		<div class="table table_right table_totals">
        			<table>
        				<thead>
        					<tr>
        						<td colspan="2"><?php _e( 'Totals', 'woocommerce-ac' ) ?></td>
        					</tr>
        				</thead>
        				<tbody>
        					<tr>
        						<td class="t earnings"><?php _e( 'Total Abandoned Carts', 'woocommerce-ac' ); ?></td>
        						<td class="wcap b-earnings">
        						  <?php
        						    $abandoned_total = $reports->get_total_reports( 'abandoned' );
        							echo $abandoned_total;
        					       ?>
        					    </td>
        					</tr>
        					<tr>
        						<td class="t sales"><?php _e( 'Total Recovered Carts', 'woocommerce-ac' ); ?></td>
        						<td class="wcap b-sales">
        						      <?php
            						      $recover_total = $reports->get_total_reports( 'recover' );
            						      echo $recover_total;
        						      ?>
        						</td>
        					</tr>
        					<tr>
        						<td class="t sales"><?php _e( 'Total Conversion Ratio', 'woocommerce-ac' ); ?></td>
        						<td class="wcap b-sales-ratio">
    					        <?php
        					          $ratio_total = $reports->get_total_reports( 'ratio' );
        						      echo round ( $ratio_total, 2).'%' ;
    					        ?>
        						</td>
        					</tr>
        				</tbody>
        			</table>
        		</div>
        		<div style="clear: both"></div>
        		<hr>
        		  <div class="table top_abandoned_product">
        			<table>
        				<thead>
        					<tr>
        						<td>
        							<strong><?php _e( 'Top Abandoned Product', 'woocommerce-ac' ); ?></strong>
        							<a href="<?php  echo admin_url( 'admin.php?page=woocommerce_ac_page&action=report' ); ?>" target="_blank" >&nbsp;&ndash;&nbsp;<?php _e( 'View All', 'woocommerce-ac' ); ?></a>
        						</td>
        					</tr>
        				</thead>

        				<tbody>
        					<tr>
        						<td>
        						    <?php
                                        $product_name = '';
            						    $recieved_product_id = $reports->get_product( 'abandoned' );
            						    if ( $recieved_product_id != '0' ){
                						    $product         = get_post( $recieved_product_id );
                                            $product_name    = '';
                                            if ( gettype( $product ) !== 'array' && count( get_object_vars( $product ) ) > 0 ) {
                						      $product_name    = $product->post_title;
                                            }
            						    }
        						    ?>
        							<a href="<?php echo "post.php?post=$recieved_product_id&action=edit"; ?>" target="_blank" >
        								<span><?php echo $product_name; ?></span>
        							</a>
        						</td>
        					</tr>
        				</tbody>
        			</table>
        		</div>
        		<hr>
        		  <div class="table top_recover_product">
        			<table>
        				<thead>
        					<tr>
        						<td>
        							<strong><?php _e( 'Top Recovered Product', 'woocommerce-ac' ); ?></strong>
        							<a href="<?php echo admin_url( 'admin.php?page=woocommerce_ac_page&action=report' ); ?>" target="_blank" >&nbsp;&ndash;&nbsp;<?php _e( 'View All', 'woocommerce-ac' ); ?></a>
        						</td>
        					</tr>
        				</thead>

        				<tbody>
        					<tr>
        						<td>
        						    <?php
                                        $product_name = '';
            						    $recieved_product_id = $reports->get_product( 'recover' );
            						    if ( $recieved_product_id != '0'){
                						    $product         = get_post( $recieved_product_id );
                						    $product_name    = $product->post_title;
            						    }
        						    ?>
        							<a href="<?php echo "post.php?post=$recieved_product_id&action=edit"; ?>" target="_blank" >
        							<span><?php echo $product_name; ?></span>
        							</a>
        						</td>
        					</tr>
        				</tbody>
        			</table>
        		</div>
        	</div>
        	<?php
        	die();
        }
    }
}
