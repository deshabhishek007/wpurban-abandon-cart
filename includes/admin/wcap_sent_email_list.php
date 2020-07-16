<?php
/**
 * It will display the sent emails list.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Report
 * @since 5.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Eent_Email_List' ) ) {
    /**
     * It will display the sent emails list
     */
    class Wcap_Eent_Email_List {
        /**
         * It will display the sent emails list and the date filter
         * @since 1.0
         */
        public static function wcap_display_sent_emails_list(  ){

            if( session_id() === '' ) {
                //session has not started
                session_start();
            }
            
            Wcap_Sent_SMS_List::wcap_sent_logs_view();
            
            $duration_range = '';
            if ( isset( $_POST['duration_select_email'] ) ) {
                $duration_range = $_POST['duration_select_email'];
                $_SESSION ['duration'] = $_POST['duration_select_email'];

            }
            if ( '' == $duration_range ) {
                if ( isset( $_GET['duration_select_email'] )  ) {
                    $duration_range = $_GET['duration_select_email'];
                    $_SESSION ['duration'] = $_GET['duration_select_email'];
                }
            }
            if ( isset($_SESSION ['duration'] ) ){
                $duration_range = $_SESSION ['duration'];
            }
            if ( '' == $duration_range ) {
                $duration_range = "last_seven";
                $_SESSION ['duration'] = $duration_range;
            }
            $wcap_ac_class = new Woocommerce_Abandon_Cart();
            ?>
            <p>
                <?php _e( 'The Report below shows emails sent, emails opened and other related stats for the selected date range', 'woocommerce-ac' );?>
            </p>
            <div id="email_stats" class="postbox" style="display:block">
                <div class="inside">
                    <form method="post" action="admin.php?page=woocommerce_ac_page&action=emailstats" id="ac_email_stats">
                        <select id="duration_select_email" name="duration_select_email" >
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
                        if ( isset( $_POST['start_date_email'] ) ) {
                            $start_date_range = $_POST['start_date_email'];
                            $_SESSION ['start_date'] = $start_date_range;
                        }
                        if ( isset( $_SESSION ['start_date'] ) ) {
                            $start_date_range = $_SESSION ['start_date'];
                        }
                        if ( '' == $start_date_range ) {
                            $start_date_range = $date_sett['start_date'];
                            $_SESSION ['start_date'] = $start_date_range;
                        }

                        $end_date_range = '';
                        if ( isset( $_POST['end_date_email'] ) ) {
                            $end_date_range = $_POST['end_date_email'];
                            $_SESSION ['end_date'] = $end_date_range;
                        }
                        if ( isset($_SESSION ['end_date'] ) ) {
                            $end_date_range = $_SESSION ['end_date'];
                        }
                        if ( '' == $end_date_range ) {
                            $end_date_range = $date_sett['end_date'];
                            $_SESSION ['end_date'] = $end_date_range;
                        }
                        ?>
                        <label class="start_label" for="start_day">
                            <?php _e( 'Start Date:', 'woocommerce-ac' ); ?>
                        </label>
                        <input type="text" id="start_date_email" name="start_date_email" readonly="readonly" value="<?php echo $start_date_range; ?>" />
                        <label class="end_label" for="end_day"> <?php _e( 'End Date:', 'woocommerce-ac' ); ?> </label>
                        <input type="text" id="end_date_email" name="end_date_email" readonly="readonly" value="<?php echo $end_date_range; ?>" />
                        <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Go', 'woocommerce-ac' ); ?>"  />
                    </form>
                </div>
            </div>

            <?php
                $wcap_all_sent_emails = $wcap_trash_sent_emails = $section = "";
                if ( isset( $_GET[ 'wcap_section' ] ) ) {
                    $section = $_GET[ 'wcap_section' ];
                } else {
                    $section = '';
                }
                if (  '' == $section  || 'wcap_all_sent' == $section ) {
                    $wcap_all_sent_emails = "current";
                }
                if( 'wcap_trash_sent' == $section  ) {
                    $wcap_trash_sent_emails = "current";
                }
                //$get_all_sent_emails_count   = Wcap_Common::wcap_get_sent_emails_count ( $start_date_range, $end_date_range, 'wcap_all_sent' );
                $wcap_sent_emails_list = new Wcap_Sent_Emails_Table();
            ?>
            <div id="email_sent_stats" class="postbox" style="display:block">
                <?php
                    $wcap_sent_emails_list->wcap_sent_emails_prepare_items();
                ?>
                <table class='wp-list-table widefat fixed posts' cellspacing='0' id='cart_data_sent' style="font-size : 15px">
                    <tr>
                        <td>
                            <p style="font-size : 15px"> <?php _e( 'Emails Sent :', 'woocommerce-ac' ); ?>
                                <?php echo $wcap_sent_emails_list->total_count; ?>
                            </p>
                        </td>
                        <td>
                            <p style="font-size : 15px"> <?php _e( 'Emails Opened :', 'woocommerce-ac' ); ?>
                                <?php echo $wcap_sent_emails_list->open_emails; ?>
                            </p>
                        </td>
                        <td>
                            <p style="font-size : 15px"> <?php _e( 'Links Clicked :', 'woocommerce-ac' ); ?>
                                <?php echo $wcap_sent_emails_list->link_click_count;  ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <br class="clear">
            <div class="wrap">
                <form id="wcap-sent-emails" method="get" >
                    <input type="hidden" name="page" value="woocommerce_ac_page" />
                    <input type="hidden" name="action" value="emailstats" />
                    <input type="hidden" name="wcap_action" value="emailstats" />
                        <?php $wcap_sent_emails_list->display(); ?>
                </form>
            </div>
        <?php
        }
    }
}
