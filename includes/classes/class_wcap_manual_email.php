<?php 
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * This files will show the Email Templates when we click on the Send Custom Email.
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @category Classes
 * @since    4.2
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * This class is used to display email Tempalates for Initiate Recovery.
 * 
 * @since 4.3
 */
class WCAP_Manual_Email {
    /**
     * This function is used for showing the all Email Templates when we click on the Send Custom Email.
     *
     * @globals mixed $wpdb
     * @globals mixed $woocommerce
     * @since    4.2
     */
    public static function wcap_display_manual_email_template (){
        global $wpdb, $woocommerce;
        
        $mode = 'manual_email';
        $abandoned_cart_id = $_GET['abandoned_order_id'];
        $query = "SELECT template_name, id, subject, body, is_wc_template, wc_email_header, coupon_code, generate_unique_coupon_code, discount, discount_type, discount_shipping, discount_expiry  FROM `" . WCAP_EMAIL_TEMPLATE_TABLE . "` ORDER BY day_or_hour asc , frequency asc";
        $results = $wpdb->get_results(  $query );
        
        $abadoned_cart_ids = $_GET['abandoned_order_id'];
        
        if ( isset( $abadoned_cart_ids ) && is_array ( $abadoned_cart_ids ) ){
            $abadoned_cart_ids = implode ( ",", $abadoned_cart_ids );
        }
        
        ?>
        <?php if ( isset( $_POST['ac_settings_frm'] ) && $_POST['ac_settings_frm'] == 'save' ) { ?>
        <div id="message" class="updated fade">
            <p>
                <strong>
                    <?php _e( 'Your settings have been saved.', 'woocommerce-ac' ); ?>
                </strong>
            </p>
        </div>
        <?php } ?>
        
        <div id="wcap_manual_email_data_loading" >
            <img  id="wcap_manual_email_data_loading_image" src="<?php echo plugins_url(); ?>/woocommerce-abandon-cart-pro/assets/images/loading.gif" alt="Loading...">
        </div>
        <div id="content">
            <form method="post" action="" id="ac_settings">                            
                <input type="hidden" name="mode" value="<?php echo $mode; ?>" />
                <input type="hidden" name="abandoned_cart_id" value="<?php print_r( $abadoned_cart_ids ); ?>" />                           
                <?php
                $button_mode = "save";
                $display_message          = __("Send Custom Email", "woocommerce-ac");
                $display_message_for_cart = __("The abandoned cart id(s) #$abadoned_cart_ids will receive this email.", "woocommerce-ac");
                        
                print'<input type="hidden" name="ac_settings_frm" value="'.$button_mode.'">'; ?>
                <div id="poststuff">
                    <div> <!-- <div class="postbox" > -->
                        <h3 class="handle">
                            <?php _e( $display_message, 'woocommerce-ac' ); ?>
                            <br>
                            <?php _e( $display_message_for_cart, 'woocommerce-ac' ); ?>
                        </h3>
                        <div>
                            <table class="form-table" id="addedit_template">
                                <tr>
                                   <th>
                                        <label for="woocommerce_ac_load_message_drop_down">
                                            <b>
                                                <?php _e( 'Load Message from existing Template:', 'woocommerce-ac' ); ?>
                                            </b>
                                        </label>
                                    </th>
                                    <td>
                                        <select name="wcap_manual_template_name" id="wcap_manual_template_name" class = "wcap_manual_template_name" >
                                            <?php
                                            
                                                foreach ( $results as $results_key => $results_value ){
                                                    printf( "<option %s value='%s'>%s</option>\n",
                                                        
                                                        selected( $results_key, $results_value->template_name, false ),
                                                        esc_attr( $results_value->id ),
                                                        $results_value->template_name
                                                    );
                                                }
                                            ?>
                                        </select>
                                        <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'Select the template from which you would like to load the tepmplate data.', 'woocommerce' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" />
                                        <strong>Note: </strong> Any changes made on this page will not update the selected email template. Changes made here are only applicable for the email to be sent for Abandoned Orders selected in previous step.
                                        </p>
                                    </td>
                                </tr> 
                                                                               
                               <tr>
                                    <th>
                                        <label for="woocommerce_ac_email_subject">
                                            <b>
                                                <?php _e( 'Subject:', 'woocommerce-ac' ); ?>
                                            </b>
                                        </label>
                                    </th>
                                    <td>
                        
                                    <?php
                                        $subject_edit="";
                                        
                                        if ( 'manual_email' == $mode ) {
                                            $subject_edit = $results[0]->subject;
                                        }
                                    
                                        print'<input type="text" name="woocommerce_ac_email_subject" id="woocommerce_ac_email_subject" class="regular-text" value="' . $subject_edit . '">'; ?>
                                        <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'Enter the subject that should appear in the email sent', 'woocommerce' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" /></p>
                                        Add the shortcode {{customer.firstname}} or {{product.name}} to include the Customer First Name and Product name (first in the cart) to the Subject Line.
                                        For e.g. Hi John!! You left some Protein Bread in your cart. 
                        
                                    </td>
                                </tr>            
                                <tr>
                                    <th>
                                        <label for="woocommerce_ac_email_body">
                                            <b>
                                                <?php _e( 'Email Body:', 'woocommerce-ac' ); ?>
                                        </b>
                                    </label>
                                </th>
                                <td>
                        
                                <?php
                                    $initial_data = "";
                                    
                                    if ( 'manual_email' == $mode ) {
                                        $initial_data = $results[0]->body;
                                    }
                                
                                    $initial_data = str_replace ( "My document title", "", $initial_data );
                                        
                                    wp_editor(
                                        $initial_data,
                                        'woocommerce_ac_email_body',
                                        array(
                                        'media_buttons' => true,
                                        'textarea_rows' => 15,
                                        'tabindex' => 4,
                                        'tinymce' => array(
                                            'theme_advanced_buttons1' => 'bold,italic,underline,|,bullist,numlist,blockquote,|,link,unlink,|,spellchecker,fullscreen,|,formatselect,styleselect',
                                         ),
                                        )
                                    );
                                ?>
                                    <span class="description">
                                        <?php
                                        echo __( 'Message to be sent in the reminder email.', 'woocommerce-ac' );
                                        ?>
                                        <img width="16" height="16" src="<?php echo plugins_url(); ?>/woocommerce-abandon-cart-pro/assets/images/information.png" onClick="bkap_show_help_tips()"/>
                                    </span>
                                    <span id="help_message" style="display:none">
                                        1. You can add customer & cart information in the template using this icon <img width="20" height="20" src="<?php echo plugins_url(); ?>/woocommerce-abandon-cart-pro/assets/images/ac_editor_icon.png" /> in top left of the editor.<br>
                                        2. You can now customize the product information/cart contents table that is added when using the {{products.cart}} merge field.<br>
                                        3. Add/Remove columns from the default table by selecting the column and clicking on the Remove Column Icon in the editor.<br>
                                        4. Insert/Remove any of the new shortcodes that have been included for the product table.<br>
                                        5. Change the look and feel of the table by modifying the table style properties using the Edit Table Icon in the editor. <br>
                                        6. Change the background color of the table rows by using the Edit Table Row Icon in the editor. <br>
                                        7. Use any of icons for the table in the editor to stylize the table as per your requirements.<img width="180" height="20" src="<?php echo plugins_url(); ?>/woocommerce-abandon-cart-pro/assets/images/tmce_table_editor.png" /> 
                                    </span>
                                </td>
                            </tr>  
                            <script type="text/javascript">
                            function bkap_show_help_tips() {
                                if( jQuery( '#help_message' ) . css( 'display' ) == 'none') {
                                    document.getElementById( "help_message" ).style.display = "block";
                                }
                                else {
                                    document.getElementById( "help_message" ) . style.display = "none";
                                }
                            } 
                            </script>  
                    
                            <tr>
                                <th>
                                    <label for="is_wc_template">
                                        <b>
                                            <?php _e( 'Use WooCommerce Template Style:', 'woocommerce-ac' ); ?>
                                    </b>
                                </label>
                            </th>
                            <td>
                        
                            <?php
                                $is_wc_template="";
                                if ( 'manual_email' == $mode ) {
                                    $use_wc_template = $results[0]->is_wc_template;
                                    $is_wc_template = "";
                                    if ( $use_wc_template == '1' ) {
                                        $is_wc_template = "checked";
                                    }
                                }
                            
                                print'<input type="checkbox" name="is_wc_template" id="is_wc_template" ' . $is_wc_template . '>  </input>'; ?>
                                <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'Use WooCommerce default style template for abandoned cart reminder emails.', 'woocommerce' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" /> 
                                <a href= '#' id ='wcap_wc_preview' class= 'wcap_wc_preview button-primary' data-modal-type='wcap_preview_ajax' data-email-type = 'wcap_wc_preview' > Preview WooCommerce Email </a> &nbsp; &nbsp; 
                                <a href='#' id='wcap_preview' class = 'wcap_preview button-primary' data-modal-type='wcap_preview_ajax' data-email-type = 'wcap_preview' >Preview Custom Email</a> 
                                </td>
                            </tr> 
                    
                            <tr>
                                <th>
                                    <label for="wcap_wc_email_header">
                                        <b>
                                            <?php _e( 'Email Template Header Text: ', 'woocommerce-ac' ); ?>
                                    </b>
                                </label>
                            </th>
                            <td>
                            <?php
                            
                                $wcap_wc_email_header = "";  
                                if ( 'manual_email' == $mode ) {
                                    $wcap_wc_email_header = $results[0]->wc_email_header;
                                }
                            
                                
                                if ( "" == $wcap_wc_email_header ){
                                    $wcap_wc_email_header = "Abandoned cart reminder";
                                }
                                print'<input type="text" name="wcap_wc_email_header" id="wcap_wc_email_header" class="regular-text" value="' . $wcap_wc_email_header . '">'; ?>
                                <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'Enter the header which will appear in the abandoned WooCommerce email sent. This is only applicable when only used when "Use WooCommerce Template Style:" is checked.', 'woocommerce-ac' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" /></p>
                                </td>
                            </tr>  
                               
                            <tr>
                                <th>
                                    <label for="unique_coupon">
                                        <b>
                                            <?php _e( 'Generate unique coupon codes:', 'woocommerce-ac' ); ?>
                                        </b>
                                    </label>
                                </th>
                                <td>
                                <?php
                                    $is_unique_coupon = "";

                                    if ( 'manual_email' == $mode ) {
                                        $unique_coupon = $results[0]->generate_unique_coupon_code;
                                        $is_unique_coupon = "";
                                        if ( '1' == $unique_coupon ) {
                                            $is_unique_coupon = "checked";
                                        }
                                    }
                                
                                    print'<input type="checkbox" name="unique_coupon" id="unique_coupon" ' . $is_unique_coupon . '>  </input>'; ?>
                                    <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'Replace this coupon with unique coupon codes for each customer', 'woocommerce' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" /></p>
                                </td>
                            </tr>

                            <!-- Below is the Coupon Code Options chnages -->

                            <?php 
                            $show_row = "display:none;";
                            if ( "" !== $is_unique_coupon ) {
                                $show_row = "";
                            }
                            ?>

                            <tr class="wcap_discount_options_rows" style="<?php echo $show_row; ?>">
                                <th>
                                    <label class="wcap_discount_options" for="wcap_discount_type">
                                        <?php _e( 'Discount Type:', 'woocommerce-ac' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <?php
                                    $discount_type  = isset( $results[0]->discount_type ) ? $results[0]->discount_type : '';
                                    $precent        = '';
                                    $fixed          = '';
                                    if ( $discount_type == 'percent' ) {
                                        $precent = 'selected';
                                    } else if ( $discount_type == 'fixed' ) {
                                        $fixed = 'selected';
                                    }
                                    ?>
                                    <select id="wcap_discount_type" name="wcap_discount_type">
                                        <option value="percent" <?php echo $precent; ?>><?php _e( 'Percentage discount', 'woocommerce-ac' );?></option>
                                        <option value="fixed" <?php echo $fixed; ?>><?php _e( 'Fixed cart discount', 'woocommerce-ac' );?></option>
                                    </select>                                                    
                                </td>
                            </tr>

                            <tr class="wcap_discount_options_rows" style="<?php echo $show_row; ?>">
                                <th>
                                    <label class="wcap_discount_options" for="wcap_coupon_amount">
                                        <?php _e( 'Coupon amount:', 'woocommerce-ac' );?>
                                    </label>
                                </th>
                                <td>
                                <?php

                                    $discount = $results[0]->discount;
                                    /*if ( 'edittemplate' == $mode || 'copytemplate' == $mode ) {
                                        $discount = $results[0]->discount;
                                    }*/

                                    print'<input type="text" style="width:8%;" name="wcap_coupon_amount" id="wcap_coupon_amount" class="short" value="' . $discount . '">'; ?>
                                    <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'Value of the coupon.' , 'woocommerce-ac' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" />
                                </td>
                            </tr>

                            <tr class="wcap_discount_options_rows" style="<?php echo $show_row; ?>">
                                <th>
                                    <label class="wcap_discount_options" for="wcap_allow_free_shipping">
                                        <?php _e( 'Allow free shipping:', 'woocommerce-ac' ); ?>
                                    </label>
                                </th>
                                <td>

                                <?php
                                    $discount_shipping_check = "";
                                    //if ( 'edittemplate' == $mode || $mode == 'copytemplate' ) {
                                        $discount_shipping = $results[0]->discount_shipping;
                                        if ( "yes" === $discount_shipping ) {
                                            $discount_shipping_check = "checked";
                                        }
                                    //}

                                    /*if ( $mode == 'copytemplate' ) {
                                        $use_wc_template = $results_copy[0]->generate_unique_coupon_code;
                                        $is_wc_template = "";
                                        if( '1' == $use_wc_template ) {
                                            $is_wc_template = "checked";
                                        }
                                    }*/
                                    print'<input type="checkbox" name="wcap_allow_free_shipping" id="wcap_allow_free_shipping" ' . $discount_shipping_check . '>  </input>'; ?>
                                    <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'Check this box if the coupon grants free shipping. A free shipping method must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'woocommerce' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" />
                                   
                                </td>
                            </tr>

                            <tr class="wcap_discount_options_rows" style="<?php echo $show_row; ?>">
                                <th>
                                    <label class="wcap_discount_options" for="wcac_coupon_expiry">
                                        <?php _e( 'Coupon validity:', 'woocommerce-ac' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <?php
                                    $wcac_coupon_expiry     = "7-days";
                                    $expiry_days_or_hours   = array( 'hours' => 'Hour(s)', 'days' => 'Day(s)' );
                                    //if ( 'edittemplate' == $mode || 'copytemplate' == $mode ) {
                                        $wcac_coupon_expiry = $results[0]->discount_expiry;
                                    //}

                                    $wcac_coupon_expiry_explode = explode( "-", $wcac_coupon_expiry );
                                    $expiry_number              = $wcac_coupon_expiry_explode[0];
                                    $expiry_freq                = $wcac_coupon_expiry_explode[1];

                                    print'<input type="text" style="width:8%;" name="wcac_coupon_expiry" id="wcac_coupon_expiry" value="' . $expiry_number . '">  </input>'; ?>

                                    <select name="expiry_day_or_hour" id="expiry_day_or_hour">
                                    <?php
                                        foreach( $expiry_days_or_hours as $k => $v ) {
                                            printf( "<option %s value='%s'>%s</option>\n",
                                                selected( $k, $expiry_freq, false ),
                                                esc_attr( $k ),
                                                $v
                                            );
                                        }
                                    ?>
                                    </select>
                                    <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'The coupon code which will be sent in the reminder emails will be expired based the validity set here. E.g if the coupon code sent in the reminder email should be expired after 7 days then set 7 Day(s) for this option.', 'woocommerce-ac' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" />
                                </td>
                            </tr>

                            <tr><th></th><td><b>OR</b></td></tr>

                            <!-- The Coupon Code Options chnages ends here -->

                            <tr>
                                <th>
                                    <label for="woocommerce_ac_coupon_auto_complete">
                                        <b>
                                            <?php _e( 'Enter a coupon code to add into email:', 'woocommerce-ac' ); ?>
                                    </b>
                                </label>
                                </th>
                                <td>                                                    
                                <!-- code started for woocommerce auto-complete coupons field emoved from class : woocommerce_options_panelfor WC 2.5 -->
                                    <div id="coupon_options" class="panel">
                                        <div class="options_group">
                                            <p class="form-field" style="padding-left:0px !important;">
                                            
                                            <?php
                                                    
                                                $json_ids       = array();
                                                $coupon_code_id = '';
                                                $coupon_ids     = array();
                                                if ( 'manual_email' == $mode ) {
                                                    $coupon_code_id = $results[0]->coupon_code;
                                                }
                                
                                                if ( $coupon_code_id > 0 ) {
                                                    
                                                    if ( 'manual_email' == $mode ) {
                                                        $coupon_ids  = explode ( ",", $results[0]->coupon_code );
                                                    }
                                                    
                                                    
                                                    foreach ( $coupon_ids as $product_id ) {
                                                        if ( $product_id > 0 ){
                                                            $product = get_the_title( $product_id );
                                                            $json_ids[ $product_id ] = $product ;
                                                        }
                                                    }
                                                }
                                            
                                                if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {            
                                                ?>
                                                    <select id="coupon_ids" name="coupon_ids[]" class="wc-product-search" multiple="multiple" style="width: 50%;" data-placeholder="<?php esc_attr_e( 'Search for a Coupon&hellip;', 'woocommerce-ac' ); ?>" data-action="wcap_json_find_coupons">
                                                        <?php
                                                        foreach ( $coupon_ids as $product_id ) {
                                                            if ( $product_id > 0  ) {
                                                                $product = get_the_title( $product_id );
                                                                echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product ) . '</option>';
                                                            }
                                                        }
                                                        ?> 
                                                    </select>
                                                    <?php
                                                } else {
                                                    ?>
                                                        <input type="hidden" id="coupon_ids" name="coupon_ids[]" class="wc-product-search" style="width: 30%;" data-placeholder="<?php esc_attr_e( 'Search for a Coupon&hellip;', 'woocommerce-ac' ); ?>" data-multiple="true" data-action="wcap_json_find_coupons" 
                                                           data-selected=" <?php echo esc_attr( json_encode( $json_ids ) ); ?> " value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>"
                                                        /> 
                                                        <?php
                                                    }
                                                ?>
                                                <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'Search & select one coupon code that customers should use to get a discount. Generated coupon code which will be sent in email reminder will have the settings of coupon selected in this option.', 'woocommerce-ac' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" />
                                            </p>
                                        </div>
                                    </div>                                                      
                                <!-- code ended for woocommerce auto-complete coupons field -->                                       
                                </td>
                            </tr> <!-- add new check box -->


                                <tr>
                                    <th>
                                        <label for="woocommerce_ac_email_preview">
                                            <b>
                                                <?php _e( 'Send a test email to:', 'woocommerce-ac' ); ?>
                                            </b>
                                        </label>
                                    </th>
                                    <td>                                       
                                        <input type="text" id="send_test_email" name="send_test_email" class="regular-text" >
                                        <input type="button" class="button" value="Send a test email" id="preview_email" onclick="javascript:void(0);">
                                        <img   class="help_tip" width="16" height="16" data-tip='<?php _e( 'Enter the email id to which the test email needs to be sent.', 'woocommerce' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" />
                                        <div   id="preview_email_sent_msg" style="display:none;"></div>
                                    </td>
                                </tr>  
                            </table>
                        </div>
                    </div>
                </div>
                <p class="submit">
                    <?php
                        $button_value = "Send Email";
                    ?>
                    <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( $button_value, 'woocommerce-ac' ); ?>"  />
                </p>
            </form>
        </div>
    <?php 
    }
}
?>