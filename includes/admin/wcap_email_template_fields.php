<?php
/**
 * It will display the add/edit fields of the email template.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Template
 * @since 5.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Email_Template_Fields' ) ) {
    /**
     * It will display the add/edit fields of the email template.
     */
    class Wcap_Email_Template_Fields {
        /**
         * It will display the add/edit fields of the email template.
         * @globals mixed $wpdb
         * @globals mixed $woocommerce
         * @since 1.0 
         * @todo Remove inline Javascript.
         */
        public static function wcap_display_email_template_fields( ) {
            global $wpdb, $woocommerce;

            $mode = Wcap_Common::wcap_get_mode();

            if( 'edittemplate' == $mode ) {
                $edit_id = $_GET['id'];
                $query = "SELECT wpet . *  FROM `" . WCAP_EMAIL_TEMPLATE_TABLE . "` AS wpet WHERE id= %d";
                $results = $wpdb->get_results( $wpdb->prepare( $query,  $edit_id ) );
            }
            if( 'copytemplate' == $mode ) {
                $copy_id        = $_GET['id'];
                $query_copy     = "SELECT wpet . *  FROM `" . WCAP_EMAIL_TEMPLATE_TABLE . "` AS wpet WHERE id= %d";
                $results_copy   = $wpdb->get_results( $wpdb->prepare( $query_copy,$copy_id ) );
            }
            $active_post = ( empty( $_POST['is_active'] ) ) ? '0' : '1';
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
                <div id="content">
                    <form method="post" action="admin.php?page=woocommerce_ac_page&action=cart_recovery&section=emailtemplates" id="ac_settings">
                        <input type="hidden" name="mode" value="<?php echo $mode; ?>" />
                        <input type="hidden" name="id" value="<?php if( isset( $_GET['id'] ) ) echo $_GET['id']; ?>" />
                        <?php
                        $button_mode = "save";
                        $display_message = "Add Email Template";
                        if ( 'edittemplate' == $mode ) {
                            $button_mode     = "update";
                            $display_message = "Edit Email Template";
                        }
                        print'<input type="hidden" name="ac_settings_frm" value="'.$button_mode.'">'; ?>
                            <div id="poststuff">
                                <div> <!-- <div class="postbox" > -->
                                    <h3 class="hndle">
                                        <?php _e( $display_message, 'woocommerce-ac' ); ?>
                                    </h3>
                                    <div>
                                        <table class="form-table" id="addedit_template">
                                            <tr>
                                                <th>
                                                    <label for="woocommerce_ac_email_send_email_filter">
                                                        <?php _e( 'Send to selected Segment(s):', 'woocommerce-ac' ); ?>
                                                    </label>
                                                </th>
                                                <td >
                                                    <?php
                                                    $frequency_edit = array();
                                                    $wcap_template_filter_types = array( 'All', 'Carts abandoned with one product',  'Carts abandoned with more than one product', 'Registered Users', 'Guest Users' );
                                                    if ( 'edittemplate' == $mode ) {
                                                      $frequency_edit = $results[0]->wc_template_filter ;
                                                      $frequency_edit = explode ( ",", $frequency_edit );
                                                    }
                                                    if( 'copytemplate' == $mode ) {
                                                      $frequency_edit = $results_copy[0]->wc_template_filter ;
                                                      $frequency_edit = explode ( ",", $frequency_edit );
                                                    }
                                                    if ( isset( $frequency_edit [0] ) && "" == $frequency_edit [0] ) {
                                                    
                                                        $frequency_edit = array( 'All' );
                                                    }

                                                    ?>
                                                    <select class = "wcap_email_filter" id="wcap_email_filter" name="wcap_email_filter[]"
                                                    multiple="multiple" placeholder="Select segment">
                                                    <?php
                                                        foreach ( $wcap_template_filter_types as $wcap_template_filter_types_key => $wcap_template_filter_types_value ) {
                                                           if ( in_array ( $wcap_template_filter_types_value, $frequency_edit ) ) {
                                                                echo '<option value="' . $wcap_template_filter_types_value . '" selected>' . __( $wcap_template_filter_types_value, 'woocommerce-ac') . '</option>';
                                                            }else{
                                                                echo '<option value="' . $wcap_template_filter_types_value . '">' . __( $wcap_template_filter_types_value, 'woocommerce-ac') . '</option>';
                                                            }
                                                        }
                                                    ?>
                                                    </select>
                                                    <?php
                                                    
                                                    $help_tip_text = '';
                                                    if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {
                                                        $help_tip_text  = "help_tip_filter_3.0";
                                                    }else{
                                                        $help_tip_text = "help_tip_filter";
                                                    }
                                                    ?>
                                                    <img id = <?php echo $help_tip_text; ?> class="help_tip" width="16" height="16" data-tip='<?php _e( 'Send this email template to selected segment(s). For example, you can send different email templates to registered users & different templates to guest users.', 'woocommerce-ac' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" /></p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>
                                                    <label for="wcap_email_product_filter">
                                                        <?php _e( 'Cart Products:', 'woocommerce-ac' ); ?>
                                                    </label>
                                                </th>
                                                <td>
                                                    <?php
                                                    $cart_products = isset( $results[0]->cart_rules ) ? $results[0]->cart_rules : '';
                                                    $include_select = '';
                                                    $exclude_select = '';
                                                    if( $cart_products == 'includes' ) {
                                                        $include_select = 'selected';
                                                    } else if( $cart_products == 'excludes' ) {
                                                        $exclude_select = 'selected';
                                                    }
                                                    ?>
                                                    <select id="wcap_email_product_filter" name="wcap_email_product_filter">
                                                        <option value="includes" <?php echo $include_select; ?>><?php _e( 'Includes any of', 'woocommerce-ac' );?></option>
                                                        <option value="excludes" <?php echo $exclude_select; ?>><?php _e( 'Excludes any of', 'woocommerce-ac' );?></option>
                                                    </select>
                                                    <div id="product_options" class="panel">
                                                        <div class="options_group">
                                                            <p class="form-field" style="padding-left:0px !important;">

                                                            <?php

                                                                $json_ids       = array();
                                                                $product_ids     = array();
                                                                $product_id = '';
                                                                if ( 'edittemplate' == $mode ) {
                                                                    $product_id = isset( $results[0]->product_ids ) ? $results[0]->product_ids : '';
                                                                }
                                                                if ( 'copytemplate' == $mode ) {
                                                                    $product_id = isset( $results_copy[0]->product_ids ) ? $results_copy[0]->product_ids : '';
                                                                }
                                                                
                                                                if ( strlen( $product_id ) > 0 ) {
                                                                    if ( 'edittemplate' == $mode ) {
                                                                        $product_ids  = explode ( ",", $results[0]->product_ids );
                                                                    }
                                                                    if ( 'copytemplate' == $mode ) {
                                                                        $product_ids  = explode ( ",", $results_copy[0]->product_ids );
                                                                    }
                                                                     foreach ( $product_ids as $product_id ) {
                                                                        if ( $product_id > 0 ) {
                                                                            $product = get_the_title( $product_id );
                                                                            $json_ids[ $product_id ] = $product ;
                                                                        }
                                                                    }

                                                                } 
                                                                if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {
                                                                    ?>
                                                                    <select id="product_ids" name="product_ids[]" class="wc-product-search" multiple="multiple" style="width: 50%;" data-placeholder="<?php esc_attr_e( 'Search for a Product&hellip;', 'woocommerce' ); ?>" data-action="wcap_json_find_products">
                                                                        <?php
                                                                        foreach ( $product_ids as $product_id ) {
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
                                                                    <input type="hidden" id="product_ids" name="product_ids[]" class="wc-product-search" style="width: 30%;" data-placeholder="<?php esc_attr_e( 'Search for a Product&hellip;', 'woocommerce' ); ?>" data-multiple="true" data-action="wcap_json_find_products"
                                                                       data-selected=" <?php echo esc_attr( json_encode( $json_ids ) ); ?> " value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>"
                                                                    />
                                                                    <?php
                                                                }
                                                            ?>
                                                            </p>

                                                        <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'Rules that should be applied to the email template with regards to the products that might or might not be present in the abandoned cart.', 'woocommerce-ac' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" />
                                                        </div>
                                                    </div>
                                                    
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>
                                                    <label for="woocommerce_ac_email_send_email_filter">
                                                        <?php _e( 'Send the Abandoned cart emails to:', 'woocommerce-ac' ); ?>
                                                    </label>
                                                </th>
                                                <td>
                                                    <?php
                                                    $frequency_edit = array();

                                                    $wcap_email_action = '';
            
                                                    $wcap_email_other_action = '';

                                                    $wcap_other_emails_style = 'display:none';
                                                    $wcap_other_emails_list = '';
                                                    
                                                    $wcap_email_to_segment = array(
                                                            'wcap_email_customer'       => 'Customers',
                                                            'wcap_email_admin'          => 'Admin',
                                                            'wcap_email_customer_admin' => 'Customers & Admin',
                                                            'wcap_email_others'         => 'Others'
                                                        );

                                                    if ( 'addnewtemplate' ==  $mode ){
                                                        $wcap_email_action = 'wcap_email_customer';
                                                    }
                                                    if ( 'edittemplate' == $mode ) {
                                                        $send_emails_to = isset( $results[0]->send_emails_to ) && '' != $results[0]->send_emails_to ? json_decode( str_replace( array( "\n", "\r\n", "\r" ), '', $results[0]->send_emails_to ), true ) : '';
                                                        $wcap_email_action = isset( $send_emails_to[ 'action' ] ) ? $send_emails_to[ 'action' ] : '';

                                                        if ( 'wcap_email_others' == $wcap_email_action ) {
                                                            $wcap_other_emails_style = 'display:block';

                                                            $wcap_email_other_action = isset( $send_emails_to[ 'others' ] ) && '' != $send_emails_to[ 'others' ] ? $send_emails_to[ 'others' ] : '';
                                                            
                                                            if ( '' != $wcap_email_other_action ) {
                                                                $wcap_other_emails_list = str_replace( array( "\n", "\r\n", "\r" ), '', $wcap_email_other_action );
                                                            }
                                                        }
                                                    }
                                                    if ( 'copytemplate' == $mode ) {
                                                        $wcap_template_id = $results_copy[0]->id ;
                                                        $send_emails_to = isset( $results_copy[0]->send_emails_to ) && is_array( $results_copy[0]->send_emails_to ) ? json_decode( $results_copy[0]->send_emails_to, true ) : '';
                                                        
                                                        $wcap_email_action = isset( $send_emails_to[ 'action' ] ) ? $send_emails_to[ 'action' ] : '';

                                                        if ( 'wcap_email_others' == $wcap_email_action ) {
                                                            $wcap_other_emails_style = 'display:block';

                                                            $wcap_email_other_action = isset( $send_emails_to[ 'others' ] ) && '' != $send_emails_to[ 'others' ] ? $send_emails_to[ 'others' ] : '';

                                                            if ( '' != $wcap_email_other_action ) {
                                                                $wcap_other_emails_list = $wcap_email_other_action;
                                                            }

                                                        }
                                                    }
                                                    ?>
                                                    <select class = "wcap_email_action" id="wcap_email_action" name="wcap_email_action[]" >
                                                    <?php
                                                        foreach ( $wcap_email_to_segment as $wcap_email_to_segment_key => $wcap_email_to_segment_value ) {
                                                           if ( $wcap_email_to_segment_key == $wcap_email_action ) {
                                                                echo '<option value="' . $wcap_email_to_segment_key . '" selected>' . __( $wcap_email_to_segment_value, 'woocommerce-ac') . '</option>';
                                                            }else{
                                                                echo '<option value="' . $wcap_email_to_segment_key . '">' . __( $wcap_email_to_segment_value, 'woocommerce-ac') . '</option>';
                                                            }
                                                        }
                                                    ?>
                                                    </select>

                                                    <?php
                                                    
                                                    $help_tip_text = '';
                                                    if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {
                                                        $help_tip_text  = "help_tip_filter_3.0";
                                                    }else{
                                                        $help_tip_text = "help_tip_filter";
                                                    }
                                                    ?>
                                                    <img id = <?php echo $help_tip_text; ?> class="help_tip" width="16" height="16" data-tip='<?php _e( 'Send this email template to selected email address(s).', 'woocommerce-ac' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" /></p>
                                                    <br>
                                                    <textarea type = 'text' id='wcap_other_emails' cols='70' rows='5' class = 'wcap_other_emails' name = 'wcap_other_emails' style = <?php echo $wcap_other_emails_style; ?> ><?php echo $wcap_other_emails_list; ?></textarea>
                                                    <p class='wcap_other_emails' style=<?php echo $wcap_other_emails_style; ?>><?php esc_html_e( 'Please enter email addresses separated by comma(,)', 'woocommerce-ac' ); ?></p>

                                                </td>
                                            </tr>
                                            <tr>
                                                <th>
                                                    <label for="woocommerce_ac_template_name">
                                                        <?php _e( 'Template Name:', 'woocommerce-ac' );?>
                                                    </label>
                                                </th>
                                                <td>
                                                <?php
                                                    $template_name = "";
                                                    if( 'edittemplate' == $mode ) {
                                                        $template_name = $results[0]->template_name;
                                                    }
                                                    if( 'copytemplate' == $mode ) {
                                                        $template_name = "Copy of ".$results_copy[0]->template_name;
                                                    }
                                                    print'<input type="text" name="woocommerce_ac_template_name" id="woocommerce_ac_template_name" class="regular-text" value="' . $template_name . '">'; ?>
                                                    <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'Enter a template name for reference' , 'woocommerce' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>
                                                    <label for="woocommerce_ac_email_frequency">
                                                        <?php _e( 'Send this email:', 'woocommerce-ac' ); ?>
                                                    </label>
                                                </th>
                                                <td>
                                                    <select name="email_frequency" id="email_frequency">
                                                    <?php
                                                        $frequency_edit="";
                                                        if ( 'edittemplate' == $mode ) {
                                                            $frequency_edit=$results[0]->frequency;
                                                        }
                                                        if ( 'copytemplate' == $mode ) {
                                                            $frequency_edit=$results_copy[0]->frequency;
                                                        }
                                                        for ( $i=1;$i<60;$i++ ) {
                                                            printf( "<option %s value='%s'>%s</option>\n",
                                                                selected( $i, $frequency_edit, false ),
                                                                esc_attr( $i ),
                                                                $i
                                                            );
                                                        }
                                                    ?>
                                                    </select>
                                                    <select name="day_or_hour" id="day_or_hour">
                                                    <?php
                                                        $days_or_hours_edit = "";
                                                        if ( 'edittemplate' == $mode ) {
                                                            $days_or_hours_edit=$results[0]->day_or_hour;
                                                        }
                                                        if ( 'copytemplate' == $mode ) {

                                                            $days_or_hours_edit=$results_copy[0]->day_or_hour;
                                                        }
                                                        $days_or_hours = array(
                                                               'Minutes'    => 'Minute(s)',
                                                               'Days'       => 'Day(s)',
                                                               'Hours'      => 'Hour(s)'
                                                            );
                                                        foreach( $days_or_hours as $k => $v ) {
                                                            printf( "<option %s value='%s'>%s</option>\n",
                                                                selected( $k, $days_or_hours_edit, false ),
                                                                esc_attr( $k ),
                                                                $v
                                                            );
                                                        }
                                                    ?>
                                                    </select>
                                                    <span class="description"><?php
                                                        echo __( 'after cart is abandoned.', 'woocommerce-ac' );
                                                    ?>
                                                    </span>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>
                                                    <label for="woocommerce_ac_email_subject">
                                                        <?php _e( 'Subject:', 'woocommerce-ac' ); ?>
                                                    </label>
                                                </th>
                                                <td>

                                                    <?php
                                                    $subject_edit="";

                                                    if ( 'edittemplate' == $mode ) {
                                                        $subject_edit=$results[0]->subject;
                                                    }

                                                    if ( 'copytemplate' == $mode ) {
                                                        $subject_edit=$results_copy[0]->subject;
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
                                                        <?php _e( 'Email Body:', 'woocommerce-ac' ); ?>
                                                    </label>
                                                </th>
                                                <td>
                                                <?php
                                                    $initial_data = "";
                                                    if ( 'edittemplate' == $mode ) {
                                                        $initial_data = $results[0]->body;
                                                    }
                                                    if ( 'copytemplate' == $mode ) {
                                                        $initial_data = $results_copy[0]->body;
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
                                                        <?php _e( 'Use WooCommerce Template Style:', 'woocommerce-ac' ); ?>
                                                    </label>
                                                </th>
                                                <td>

                                                <?php
                                                    $is_wc_template="";
                                                    if ( 'edittemplate' == $mode ) {
                                                        $use_wc_template = $results[0]->is_wc_template;
                                                        $is_wc_template = "";
                                                        if ( $use_wc_template == '1' ) {
                                                            $is_wc_template = "checked";
                                                        }
                                                    }

                                                    if ( $mode == 'copytemplate' ) {
                                                        $use_wc_template = $results_copy[0]->generate_unique_coupon_code;
                                                        $is_wc_template = "";
                                                        if( '1' == $use_wc_template ) {
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
                                                        <?php _e( 'Email Template Header Text: ', 'woocommerce-ac' ); ?>
                                                    </label>
                                                </th>
                                                <td>
                                                <?php

                                                    $wcap_wc_email_header = "";
                                                    if ( 'edittemplate' == $mode ) {
                                                        $wcap_wc_email_header = $results[0]->wc_email_header;
                                                    }
                                                    if ( 'copytemplate' == $mode ) {
                                                        $wcap_wc_email_header = $results_copy[0]->wc_email_header;
                                                    }
                                                    if ( "" == $wcap_wc_email_header ) {
                                                        $wcap_wc_email_header = "Abandoned cart reminder";
                                                    }
                                                    print'<input type="text" name="wcap_wc_email_header" id="wcap_wc_email_header" class="regular-text" value="' . $wcap_wc_email_header . '">'; ?>
                                                    <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'Enter the header which will appear in the abandoned WooCommerce email sent. This is only applicable when only used when "Use WooCommerce Template Style:" is checked.', 'woocommerce-ac' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" /></p>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>
                                                    <label for="unique_coupon">                                                        
                                                        <?php _e( 'Generate unique coupon codes:', 'woocommerce-ac' ); ?>
                                                    </label>
                                                </th>
                                                <td>
                                                <?php
                                                    $is_unique_coupon = "";
                                                    if ( 'edittemplate' == $mode ) {
                                                        $unique_coupon = $results[0]->generate_unique_coupon_code;
                                                        $is_unique_coupon = "";
                                                        if ( '1' == $unique_coupon ) {
                                                            $is_unique_coupon = "checked";
                                                        }
                                                    }
                                                    if ( 'copytemplate' == $mode ) {
                                                        $unique_coupon = $results_copy[0]->generate_unique_coupon_code;
                                                        $is_unique_coupon = "";
                                                        if( '1' == $unique_coupon ) {
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

                                                    if ( 'copytemplate' == $mode ) {
                                                        $discount_type = $results_copy[0]->discount_type;
                                                    }

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
                                                    $discount = 0;
                                                    if ( 'edittemplate' == $mode ) {
                                                        $discount = $results[0]->discount;
                                                    }

                                                    if ( 'copytemplate' == $mode ) {
                                                        $discount = $results_copy[0]->discount;
                                                    }

                                                    print'<input type="text" style="width:8%;" name="wcap_coupon_amount" id="wcap_coupon_amount" class="short" value="' . $discount . '">'; ?>
                                                    <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'Value of the coupon.' , 'woocommerce' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" />
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
                                                    $discount_shipping_check    = "";
                                                    $discount_shipping          = '';
                                                    if ( 'edittemplate' == $mode ) {
                                                        $discount_shipping = $results[0]->discount_shipping;
                                                    }

                                                    if ( 'copytemplate' == $mode ) {
                                                        $discount_shipping = $results_copy[0]->discount_shipping;
                                                    }

                                                    if ( "yes" === $discount_shipping ) {
                                                        $discount_shipping_check = "checked";
                                                    }
                                                    
                                                    print'<input type="checkbox" name="wcap_allow_free_shipping" id="wcap_allow_free_shipping" ' . $discount_shipping_check . '>  </input>'; ?>
                                                    <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'Check this box if the coupon grants free shipping. A free shipping method must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'woocommerce-ac' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" />
                                                   
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
                                                    if ( 'edittemplate' == $mode ) {
                                                        $wcac_coupon_expiry = $results[0]->discount_expiry;
                                                    }
                                                    if ( 'copytemplate' == $mode ) {
                                                        $wcac_coupon_expiry = $results_copy[0]->discount_expiry;
                                                    }

                                                    $wcac_coupon_expiry_explode = explode( "-", $wcac_coupon_expiry );
                                                    $expiry_number              = isset( $wcac_coupon_expiry_explode[0] ) ? $wcac_coupon_expiry_explode[0] : 0;
                                                    $expiry_freq                = isset( $wcac_coupon_expiry_explode[1] ) ? $wcac_coupon_expiry_explode[1] : 'hours';

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

                                            <!-- The Coupon Code Options chnages ends here -->
                                            <tr><th></th><td><b>OR</b></td></tr>
                                            <tr>
                                                <th>
                                                    <label for="woocommerce_ac_coupon_auto_complete">
                                                        <?php _e( 'Enter a coupon code to add into email:', 'woocommerce-ac' ); ?>
                                                    </label>
                                                </th>
                                                <td>
                                                    <!-- code started for woocommerce auto-complete coupons field emoved from class : woocommerce_options_panelfor WC 2.5 -->
                                                    <div id="coupon_options" class="panel">
                                                        <div class="options_group">
                                                            <p class="form-field" style="padding-left:0px !important;">

                                                            <?php

                                                                $json_ids       = array();
                                                                $coupon_ids     = array();
                                                                $coupon_code_id = '';
                                                                if ( 'edittemplate' == $mode ) {
                                                                    $coupon_code_id = $results[0]->coupon_code;
                                                                }
                                                                if ( 'copytemplate' == $mode ) {
                                                                    $coupon_code_id = $results_copy[0]->coupon_code;
                                                                }
                                                                if ( $coupon_code_id > 0 ) {
                                                                    if ( 'edittemplate' == $mode ) {
                                                                        $coupon_ids  = explode ( ",", $results[0]->coupon_code );
                                                                    }
                                                                    if ( 'copytemplate' == $mode ) {
                                                                        $coupon_ids  = explode ( ",", $results_copy[0]->coupon_code );
                                                                    }
                                                                     foreach ( $coupon_ids as $product_id ) {
                                                                        if ( $product_id > 0 ) {
                                                                            $product = get_the_title( $product_id );
                                                                            $json_ids[ $product_id ] = $product ;
                                                                        }
                                                                    }

                                                                }
                                                                if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {
                                                                    ?>
                                                                    <select id="coupon_ids" name="coupon_ids[]" class="wc-product-search" multiple="multiple" style="width: 50%;" data-placeholder="<?php esc_attr_e( 'Search for a Coupon&hellip;', 'woocommerce' ); ?>" data-action="wcap_json_find_coupons">
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
                                                                    <input type="hidden" id="coupon_ids" name="coupon_ids[]" class="wc-product-search" style="width: 30%;" data-placeholder="<?php esc_attr_e( 'Search for a Coupon&hellip;', 'woocommerce' ); ?>" data-multiple="true" data-action="wcap_json_find_coupons"
                                                                       data-selected=" <?php echo esc_attr( json_encode( $json_ids ) ); ?> " value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>"
                                                                    />
                                                                    <?php
                                                                }
                                                            ?>
                                                                <img class="help_tip" width="16" height="16" data-tip='<?php _e( 'Search & select one coupon code that customers should use to get a discount.  Generated coupon code which will be sent in email reminder will have the settings of coupon selected in this option.', 'woocommerce-ac' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" />
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <!-- code ended for woocommerce auto-complete coupons field -->
                                                </td>
                                            </tr> <!-- add new check box -->

                                            <tr>
                                                <th>
                                                    <label for="woocommerce_ac_email_preview">
                                                        <?php _e( 'Send a test email to:', 'woocommerce-ac' ); ?>
                                                    </label>
                                                </th>
                                                <td>
                                                    <input type="text" id="send_test_email" name="send_test_email" class="regular-text" >
                                                    <input type="button" value="Send a test email" id="preview_email" class="button" onclick="javascript:void(0);">
                                                    <img   class="help_tip" width="16" height="16" data-tip='<?php _e( 'Enter the email id to which the test email needs to be sent.', 'woocommerce' ) ?>' src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" />
                                                    <br>
                                                    <img class="ajax_img" src="<?php echo WCAP_PLUGIN_URL . '/assets/images/ajax-loader.gif';?>" style="display:none;" />
                                                    <div   id="preview_email_sent_msg" style="display:none;"></div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <p class="submit">
                        <?php
                            $button_value = "Save Changes";
                            if ( 'edittemplate' == $mode ) {
                                $button_value = "Update Changes";
                            }
                        ?>
                            <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( $button_value, 'woocommerce-ac' ); ?>"  />
                        </p>
                    </form>
                </div>
            <?php

            wc_get_template( 
                'preview_modal.php', 
                '', 
                'woocommerce-abandon-cart-pro/',
                WCAP_PLUGIN_PATH . '/includes/template/preview_modal/' );
        }
    }
}