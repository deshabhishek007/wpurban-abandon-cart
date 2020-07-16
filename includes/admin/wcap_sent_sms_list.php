<?php
/**
 * It will display the menu of the Abadoned cart.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Menu
 * @since 1.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if ( !class_exists('Wcap_Sent_SMS_List' ) ) {
    /**
     * It will display the menu of the Abadoned cart.
     */
    class Wcap_Sent_SMS_List {

        static function wcap_sent_logs_view() {
            
            $sent_emails = '';
            $sent_sms = '';
            
            $view = isset( $_GET[ 'section' ] ) ? $_GET[ 'section' ] : 'emails';
            
            switch( $view ) {
                case '':
                case 'emails':
                    $sent_emails = 'current';
                    break;
                case 'sms':
                    $sent_sms = 'current';
                    break;
            }
            ?>
            
            <ul class="subsubsub">
                <li><a href="admin.php?page=woocommerce_ac_page&action=emailstats&section=emails" class="<?php echo $sent_emails; ?>"><?php _e( 'Emails Sent', 'woocommerce-ac' );?></a></li> | 
                <li><a href="admin.php?page=woocommerce_ac_page&action=emailstats&section=sms" class="<?php echo $sent_sms; ?>"><?php _e( 'SMS Sent', 'woocommerce-ac' ); ?></a></li>            
            </ul>
            <br class="clear">
            <?php
        }
        
        static function wcap_sent_sms() {
            
            self::wcap_sent_logs_view();
            ?>
            <p><?php _e( 'The report below shows the SMS sent, links clicked and other related stats.', 'woocommerce-ac' );?></p>
            <?php 
            
            // display table
            $sent_sms_list = new Wcap_Sent_SMS_Table();
            $sent_sms_list->wcap_sent_sms_prepare_items();
            
            // display filter
            self::wcap_display_sent_sms_filter();
            
            // display stats
            self::wcap_display_sms_stats( $sent_sms_list );
            
            $sent_sms_list->display();
            
        }
        
        static function wcap_display_sms_stats( $sent_sms_list ) {
            
            ?>
            <div id="sms_sent_stats" class="postbox" style="display:block">
                <table class='wp-list-table widefat fixed posts' cellspacing='0' id='sms_sent_data' style="font-size : 15px">
                    <tr>
                        <td>
                            <p style="font-size : 15px"> <?php _e( 'SMS Sent :', 'woocommerce-ac' ); ?>
                                <?php echo $sent_sms_list->total_count; ?>
                            </p>
                        </td>
                        <td>
                            <p style="font-size : 15px"> <?php _e( 'Links Clicked :', 'woocommerce-ac' ); ?>
                                <?php echo $sent_sms_list->link_click_count;  ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <?php 
        }
        
        static function wcap_display_sent_sms_filter() {

            if( session_id() === '' ) {
                //session has not started
                session_start();
            }
            $duration_range = '';
            if ( isset( $_POST['duration_select_sms'] ) ) {
                $duration_range = $_POST['duration_select_sms'];
                $_SESSION ['duration'] = $_POST['duration_select_sms'];
            
            }
            if ( '' == $duration_range ) {
                if ( isset( $_GET['duration_select_sms'] )  ) {
                    $duration_range = $_GET['duration_select_sms'];
                    $_SESSION ['duration'] = $_GET['duration_select_sms'];
                }
            } else if ( isset($_SESSION ['duration'] ) ){
                $duration_range = $_SESSION ['duration'];
            }
            if ( '' == $duration_range ) {
                $duration_range = "last_seven";
                $_SESSION ['duration'] = $duration_range;
            }
            $wcap_ac_class = new Woocommerce_Abandon_Cart();
            ?>
                    <div id="sent_sms_filter" class="postbox" style="display:block">
                        <div class="inside">
                            <form method="post" action="admin.php?page=woocommerce_ac_page&action=emailstats&section=sms" id="ac_sms_filter">
                                <select id="duration_select_sms" name="duration_select_sms" >
                                    <?php
                                    foreach ( $wcap_ac_class->duration_range_select as $key => $value ) {
                                        $sel = "";
                                        if ( $key == $duration_range ) {
                                            $sel = __( " selected ", "woocommerce-ac" );
                                        }
                                        echo"<option value='$key' $sel> $value </option>";
                                    }
                                    $date_sett = $wcap_ac_class->start_end_dates[$duration_range];
                                        ?>
                                </select>
                                <?php
                                $start_date_range = '';
                                if ( isset( $_POST['start_date_sms'] ) ) {
                                    $start_date_range = $_POST['start_date_sms'];
                                    $_SESSION ['start_date'] = $start_date_range;
                                } else if ( isset( $_SESSION ['start_date'] ) ) {
                                    $start_date_range = $_SESSION ['start_date'];
                                }
                                if ( '' == $start_date_range ) {
                                    $start_date_range = $date_sett['start_date'];
                                    $_SESSION ['start_date'] = $start_date_range;
                                }
                
                                $end_date_range = '';
                                if ( isset( $_POST['end_date_sms'] ) ) {
                                    $end_date_range = $_POST['end_date_sms'];
                                    $_SESSION ['end_date'] = $end_date_range;
                                } else if ( isset($_SESSION ['end_date'] ) ) {
                                    $end_date_range = $_SESSION ['end_date'];
                                }
                                if ( '' == $end_date_range ) {
                                    $end_date_range = $date_sett['end_date'];
                                    $_SESSION ['end_date'] = $end_date_range;
                                }
                                ?>
                                <label class="start_label" for="start_date_sms"><?php _e( 'Start Date:', 'woocommerce-ac' ); ?></label>
                                <input type="text" id="start_date_sms" name="start_date_sms" readonly="readonly" value="<?php echo $start_date_range; ?>" />
                                <label class="end_label" for="end_date_sms"> <?php _e( 'End Date:', 'woocommerce-ac' ); ?> </label>
                                <input type="text" id="end_date_sms" name="end_date_sms" readonly="readonly" value="<?php echo $end_date_range; ?>" />
                                <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Go', 'woocommerce-ac' ); ?>"  />
                            </form>
                        </div>
                    </div>
                <?php 
                }
         
    }
}
$Wcap_Sent_SMS_List = new Wcap_Sent_SMS_List();