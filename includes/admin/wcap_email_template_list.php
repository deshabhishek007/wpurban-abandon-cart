<?php
/**
 * It will display the email template listing.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Template
 * @since 5.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Email_Template_List' ) ) {
    /**
     * It will display the email template listing, also it will add, update & delete the email template in the database.
     * @since 5.0
     */
    class Wcap_Email_Template_List{

        public static function wcap_display_recovery_submenu( $action, $section, $mode ) {

            $menu = array(
                'emailtemplates' => array(
                    'key' => 'emailtemplates',
                    'label' => 'Email Templates',
                    'active' => '',
                    'callback' => array( 'Wcap_Email_Template_List', 'wcap_display_email_template_list' ),
                    'params' => array( $action, $section, $mode )
                ),
                'sms' => array(
                    'key' => 'sms',
                    'label' => 'SMS Notifications',
                    'active' => '',
                    'callback' => array( 'Wcap_SMS', 'display_sms_list' )
                ),
            );

            $menu = apply_filters( 'wcap_recovery_submenu', $menu );

            // set the class value for the view to be displayed
            $menu[$section]['active'] = 'current';

            ?>

                <!-- Setup the views -->
                <div id="wcap_content">
                    <ul class="subsubsub">

                        <?php foreach ( $menu as $m_key => $m_value ) : ?>
                            <li>
                                <a href="admin.php?page=woocommerce_ac_page&action=cart_recovery&section=<?php echo $m_key;?>" class="<?php echo $m_value['active'];?>"><?php _e( $m_value['label'], 'woocomerce-ac' );?> </a> |
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <br class="clear">
                </div>
            <?php 
            
            // Add content for each of the views
            if ( isset( $menu[$section]['params'] ) ) {
                call_user_func_array( $menu[$section]['callback'], $menu[$section]['params'] );
            }else{
                $menu[$section]['callback']();
            }
        }

        /**
         * It will display the email template listing, also it will add, update & delete the email template in the database.
         * @param string $wcap_action Action name
         * @param string $wcap_section Section Name 
         * @param string $wcap_mode Mode name.
         * @globals mixed $wpdb 
         * @globals mixed $woocommerce
         * @since 5.0
         */
        public static function wcap_display_email_template_list( $wcap_action, $wcap_section, $wcap_mode ) {
            global $woocommerce, $wpdb;
            //Wcap_SMS::cart_recovery_views();
            ?>
            <p>
                <?php _e( 'Add email templates at different intervals to maximize the possibility of recovering your abandoned carts.', 'woocommerce-ac' ); ?>
            </p>
            <?php
            Wcap_Common::wcap_display_date_filter( 'cart_recovery' );
            $insert_template_successfuly_pro = $update_template_successfuly_pro = '';
            // Save the field values
            if ( isset( $_POST['ac_settings_frm'] ) && 'save' == $_POST['ac_settings_frm'] ) {
                $insert_template_successfuly_pro = Wcap_Email_Template_List::wcap_save_email_template( );
            }

            if ( isset( $_POST['ac_settings_frm'] ) && 'update' == $_POST['ac_settings_frm'] ) {
                $update_template_successfuly_pro = Wcap_Email_Template_List::wcap_update_email_template( );
            }

            if ( $wcap_action == 'cart_recovery' && $wcap_section == 'emailtemplates' && $wcap_mode == 'removetemplate' ) {
                if (isset($_GET['id']) && '' != $_GET['id'] ){
                    $query_remove = "DELETE FROM `" . WCAP_EMAIL_TEMPLATE_TABLE . "` WHERE id='" . $_GET['id'] . "' ";
                    $wpdb->query( $query_remove );
                }
            }

            if ( $wcap_action == 'cart_recovery' && $wcap_section == 'emailtemplates' && $wcap_mode == 'activate_template' ) {
                global $wpdb;
                $template_id             = $_GET['id'];
                $current_template_status = $_GET['active_state'];
                $active = ( "1" == $current_template_status ) ? "0" : "1";
                    
                $query_update = "UPDATE `" . WCAP_EMAIL_TEMPLATE_TABLE . "`
                        SET
                        is_active = '" . $active . "'
                        WHERE id  = '" . $template_id . "' ";
                $wpdb->query( $query_update );

                wp_safe_redirect( admin_url( '/admin.php?page=woocommerce_ac_page&action=cart_recovery&section=emailtemplates' ) );
            }

            if ( isset( $_POST['ac_settings_frm'] )  && 
                 $_POST['ac_settings_frm'] == 'save' && 
                 (isset($insert_template_successfuly_pro) && $insert_template_successfuly_pro != '') ) {

                Wcap_Display_Notices::wcap_template_save_success();

             } else if ( ( isset($insert_template_successfuly_pro) && $insert_template_successfuly_pro == '') &&
                isset( $_POST[ 'ac_settings_frm' ] ) &&
                 $_POST[ 'ac_settings_frm' ] == 'save'
                 
                ){

                Wcap_Display_Notices::wcap_template_save_error();
            }

            if ( isset($update_template_successfuly_pro) &&  $update_template_successfuly_pro >= 0  &&
                 isset( $_POST['ac_settings_frm'] ) && 
                 $_POST['ac_settings_frm'] == 'update'
                  ) {

                Wcap_Display_Notices::wcap_template_updated_success();

             } else if ( isset($update_template_successfuly_pro ) && false === $update_template_successfuly_pro && 
                isset( $_POST[ 'ac_settings_frm' ] ) &&
                $_POST[ 'ac_settings_frm' ] == 'update'
                 ) {

                Wcap_Display_Notices::wcap_template_updated_error();
            }
            ?>
            <div>
                <p>
                    <a cursor: pointer; href="<?php echo "admin.php?page=woocommerce_ac_page&action=cart_recovery&section=emailtemplates&mode=addnewtemplate"; ?>" class="button-secondary"><?php _e( 'Add New Template', 'woocommerce-ac' ); ?></a>
                </p>
                <?php
                /* From here you can do whatever you want with the data from the $result link. */

                $wcap_template_list = new Wcap_Templates_Table();
                $wcap_template_list->wcap_templates_prepare_items();
                ?>
                <div class="wrap">
                    <form id="wcap-abandoned-templates" method="get" >
                        <input type="hidden" name="page" value="woocommerce_ac_page" />
                        <input type="hidden" name="action" value="cart_recovry" />
                        <input type="hidden" name="wcap_action" value="cart_recovry" />
                        <input type="hidden" name="section" value="emailtemplates" />
                        <input type="hidden" name="wcap_section" value="emailtemplates" />
                        <?php $wcap_template_list->display(); ?>
                    </form>
                </div>
                <p><?php _e( "<strong><i>Open Rate: </i></strong>Number of emails opened versus number of emails sent.", 'woocommerce-ac' ); ?></p>
                <p><?php _e( "<strong><i>Link Click Rate: </i></strong>Number of links clicked versus number of emails sent. In cases where coupons are present for the template, the coupon application rate will be same as Link Click Rate, since coupons are auto applied when a link is clicked.", 'woocommerce-ac' );?></p>
                <p><?php _e( "<strong><i>Coupon Redemption Rate: </i></strong>Number of Coupons applied (i.e. number of links clicked) versus number of emails opened.", 'woocommerce-ac' );?></p>
                <p><?php _e( "<strong><i>Conversion Rate: </i></strong>Number of carts recovered versus number of emails sent.", 'woocommerce-ac' );?></p>
            </div>
            <?php
        }

        /**
         * It will save the new created email templates.
         * @return true | false $insert_template_successfuly_pro If template inserted successfully
         * @since 5.0
         */
        public static function wcap_save_email_template ( ) {
           
            $coupon_code_id = "";
            if ( isset( $_POST['coupon_ids'][0] ) ) {
                $coupon_code_id = $_POST['coupon_ids'][0];
            }
            $insert_template_successfuly_pro = '';
            $active_post    = '0';
            $unique_coupon  = ( empty( $_POST['unique_coupon'] ) ) ? '0' : '1';
            $is_wc_template = ( empty( $_POST['is_wc_template'] ) ) ? '0' : '1';
            $selected_template_filter = '';
            if ( isset( $_POST['wcap_email_filter'] ) && !empty ( $_POST['wcap_email_filter'] ) ) {
                if ( in_array( "All" ,$_POST['wcap_email_filter'] ) ) {
                    $selected_template_filter = 'All';
                } else {
                    $selected_template_filter = implode ( ",", $_POST['wcap_email_filter'] );
                }
            }
            // include/exclude product IDs
            $product_ids = '';
            if( isset( $_POST[ 'product_ids' ] ) ) {
                $product_ids = implode( ',', $_POST[ 'product_ids' ] );
            }

            $coupon_code_options = Wcap_Email_Template_List::wcap_coupon_options(); // Fetching the coupon options
            
            $insert_template_successfuly_pro = Wcap_Email_Template_List::wcap_insert_template_data( $active_post, $coupon_code_id, $unique_coupon, $is_wc_template, $selected_template_filter, $product_ids, $coupon_code_options );
            return $insert_template_successfuly_pro;
        }

        /**
         * It will insert the new email template data into the database.
         * It will insert the post meta, it is used to check that for the old cart we will not send this email template.
         * Also it will insert the post meta for the email action, it will decide who will recive this email template.  
         * @param int | string $active_post Template active or not
         * @param int $coupon_code_id Selected coupon code id
         * @param int $unique_coupon Allow to generate unique coupon code
         * @param int $is_wc_template Use WooCommerce email css
         * @param string $selected_template_filter Selected segment
         * @globals mixed $wpdb 
         * @globals mixed $woocommerce
         * @return true | false $insert_template_successfuly_pro If template inserted successfully
         * @since 5.0
         */
        public static function wcap_insert_template_data ( $active_post, $coupon_code_id, $unique_coupon, $is_wc_template, $selected_template_filter, $product_ids, $coupon_code_options ) {

            global $woocommerce, $wpdb;
            $send_emails = [];
            if ( isset( $_POST['wcap_email_action'] ) && '' != $_POST['wcap_email_action'][0]  ) {
                $send_emails[ 'action' ] = $_POST['wcap_email_action'][0];
            }
            
            $send_emails[ 'others' ] = ( 'wcap_email_others' == $send_emails[ 'action' ] && '' != $_POST['wcap_other_emails'] ) ? trim( str_replace( '\n', ',', $_POST['wcap_other_emails'] ) ) : '';

            $query = "INSERT INTO `". WCAP_EMAIL_TEMPLATE_TABLE ."` ( subject, body, is_active, frequency, day_or_hour, coupon_code, template_name, default_template, discount, discount_type, discount_shipping, discount_expiry, generate_unique_coupon_code, is_wc_template, wc_email_header, wc_template_filter, cart_rules, product_ids, send_emails_to, activated_time )
                              VALUES (
                                       '" . $_POST['woocommerce_ac_email_subject'] . "',
                                       '" . $_POST['woocommerce_ac_email_body'] . "',
                                       '" . $active_post . "',
                                       '" . $_POST['email_frequency'] . "',
                                       '" . $_POST['day_or_hour'] . "',
                                       '" . $coupon_code_id . "',
                                       '" . $_POST['woocommerce_ac_template_name'] . "',
                                       '0',
                                       '" . $coupon_code_options['coupon_amount'] . "',
                                       '" . $coupon_code_options['discount_type'] . "',
                                       '" . $coupon_code_options['discount_shipping'] . "',
                                       '" . $coupon_code_options['coupon_expiry'] . "',
                                       '" . $unique_coupon . "',
                                       '" . $is_wc_template . "',
                                       '" . $_POST['wcap_wc_email_header'] . "',
                                       '" . $selected_template_filter . "',
                                       '" . $_POST['wcap_email_product_filter'] . "',
                                       '" . $product_ids . "',
                                       '" . json_encode( $send_emails ) . "',
                                       '" . current_time( 'timestamp' ) . "' )";
            $insert_template_successfuly_pro = $wpdb->query( $query );

            $wcap_template_id = $wpdb->insert_id;

            return $insert_template_successfuly_pro;
        }

        /**
         * It will update created email templates.
         * @return true | false $update_template_successfuly_pro If template updated successfully
         * @since 5.0
         */
        public static function wcap_update_email_template ( ) {

            $update_template_successfuly_pro = '';
            if ( isset( $_POST['coupon_ids'] ) ) {
                $coupon_code_id = $_POST['coupon_ids'][0];
            } else {
                if ( isset( $_POST['coupon_ids'][0] ) ) {
                    $coupon_code_id = $_POST['coupon_ids'][0];
                } else {
                    $coupon_code_id = "";
                }
            }
            $coupon_code_id_last_character = substr( $coupon_code_id, -1 );
            if ( "," == $coupon_code_id_last_character ) {
                $coupon_code_id = rtrim( $coupon_code_id, "," );
            }
            
            $unique_coupon  = ( empty( $_POST['unique_coupon'] ) )  ? '0' : '1';
            $is_wc_template = ( empty( $_POST['is_wc_template'] ) ) ? '0' : '1';

            $selected_template_filter = '';
            if ( isset( $_POST['wcap_email_filter'] ) && !empty ( $_POST['wcap_email_filter'] ) ){
                if( in_array( "All" ,$_POST['wcap_email_filter'] ) ) {
                    $selected_template_filter = 'All';
                } else {
                    $selected_template_filter = implode ( ",", $_POST['wcap_email_filter'] );
                }
            }

            // include/exclude product IDs
            $product_ids = "";
            if ( isset( $_POST['product_ids'] ) ) {
                $product_ids = implode( ',', $_POST['product_ids'] );
            }

            $coupon_code_options = Wcap_Email_Template_List::wcap_coupon_options(); // Fetching the coupon options

            $update_template_successfuly_pro = Wcap_Email_Template_List::wcap_update_template ( $unique_coupon, $is_wc_template, $selected_template_filter, $coupon_code_id, $product_ids, $coupon_code_options );

            return $update_template_successfuly_pro;
        }

        public static function wcap_coupon_options(){

            $coupon_expiry = '';
            $coupon_amount = '';

            if ( isset( $_POST['wcap_discount_type'] ) && "" !== $_POST['wcap_discount_type'] ) {
                $discount_type = $_POST['wcap_discount_type'];
            }

            if ( isset( $_POST['wcap_coupon_amount'] ) && "" !== $_POST['wcap_coupon_amount'] ) {
                $coupon_amount = $_POST['wcap_coupon_amount'];
            }

            if ( isset( $_POST['wcac_coupon_expiry'] ) && "" !== $_POST['wcac_coupon_expiry'] ) {
                $coupon_expiry = $_POST['wcac_coupon_expiry'];
            }

            if ( isset( $_POST['expiry_day_or_hour'] ) && "" !== $_POST['expiry_day_or_hour'] ) {
                $expiry_day_or_hour = $_POST['expiry_day_or_hour'];
            }
            $coupon_expiry = $coupon_expiry."-".$expiry_day_or_hour;

            $discount_shipping = "off";
            if ( isset( $_POST['wcap_allow_free_shipping'] ) && "" !== $_POST['wcap_allow_free_shipping'] ) {
                $discount_shipping = "yes";
            }

            $coupon_code_options = array(   "discount_type" => $discount_type,
                                            "coupon_amount" => $coupon_amount,
                                            "coupon_expiry" => $coupon_expiry,
                                            "discount_shipping" => $discount_shipping
                                        );

            return $coupon_code_options;
        }

        /**
         * It will update email template data into the database.
         * It will insert the post meta for the email action, it will decide who will recive this email template.
         * @param int | string $active_post Template active or not
         * @param int $coupon_code_id Selected coupon code id
         * @param int $unique_coupon Allow to generate unique coupon code
         * @param int $is_wc_template Use WooCommerce email css
         * @param string $selected_template_filter Selected segment
         * @globals mixed $wpdb 
         * @globals mixed $woocommerce
         * @return true | false $update_template_successfuly_pro If template updated successfully
         * @since 5.0
         */
        public static function wcap_update_template ( $unique_coupon, $is_wc_template, $selected_template_filter, $coupon_code_id, $product_ids, $coupon_code_options ) {
            global $woocommerce, $wpdb;

            $send_emails = [];

            if ( isset( $_POST['wcap_email_action'] ) && '' != $_POST['wcap_email_action'][0] ) {
                $send_emails[ 'action' ] = $_POST['wcap_email_action'][0];
            }
            
            $send_emails[ 'others' ] = ( 'wcap_email_others' == $send_emails[ 'action' ] && '' != $_POST['wcap_other_emails'] ) ? trim( str_replace( array( "\n", "\r\n", "\r" ), '', $_POST[ 'wcap_other_emails' ] ) ) : '';
            
            // update the template data
            $query_update  = "UPDATE `" . WCAP_EMAIL_TEMPLATE_TABLE . "`
                                SET
                                subject                     = '" . $_POST['woocommerce_ac_email_subject'] . "',
                                body                        = '" . $_POST['woocommerce_ac_email_body'] . "',
                                frequency                   = '" . $_POST['email_frequency'] . "',
                                day_or_hour                 = '" . $_POST['day_or_hour'] . "',
                                coupon_code                 = '" . $coupon_code_id . "',
                                template_name               = '" . $_POST['woocommerce_ac_template_name'] . "',
                                generate_unique_coupon_code = '" . $unique_coupon . "',
                                discount                    = '" . $coupon_code_options['coupon_amount'] . "',
                                discount_type               = '" . $coupon_code_options['discount_type'] . "',
                                discount_shipping           = '" . $coupon_code_options['discount_shipping'] . "',
                                discount_expiry             = '" . $coupon_code_options['coupon_expiry'] . "',
                                is_wc_template              = '" . $is_wc_template . "',
                                wc_email_header             = '" . $_POST[ 'wcap_wc_email_header'] . "',
                                wc_template_filter          = '" . $selected_template_filter . "',
                                cart_rules                  = '" . $_POST['wcap_email_product_filter'] . "',
                                product_ids                 = '" . $product_ids . "',
                                send_emails_to              = '" . json_encode( $send_emails ) . "'
                                WHERE id                    = '" . $_POST['id'] . "' ";

            $update_template_successfuly_pro = $wpdb->query( $query_update );

            return $update_template_successfuly_pro;
        }
    }
}
