<?php
/**
 * It will display the email template listing.
 * @author   Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/SMS Reminders Class
 * @since 7.9
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_SMS' ) ) {

    /**
     * It will display the SMS template listing, also it will add, update & delete the SMS template in the database.
     * @since 7.9
     */
    class Wcap_SMS{
    
        public function __construct() {
        }
        
        /**
         * Add the List of Cart Recovery Views
         * @since 7.9
         */
        static function cart_recovery_views() {
            
            $email_class = '';
            $sms_class = '';
            
            $section = isset( $_GET[ 'section' ] ) ? $_GET[ 'section' ] : '';
            
            switch( $section ) {
                case '':
                case 'emailtemplates':
                    $email_class = 'current';
                    break;
                case 'sms':
                    $sms_class = 'current';
                    break;
            }
            
            ?>
            <ul class="subsubsub">
                <li><a href="admin.php?page=woocommerce_ac_page&action=cart_recovery&section=emailtemplates" class="<?php echo $email_class; ?>"><?php _e( 'Email Templates', 'woocommerce-ac' );?></a></li> | 
                <li><a href="admin.php?page=woocommerce_ac_page&action=cart_recovery&section=sms" class="<?php echo $sms_class; ?>"><?php _e( 'SMS Notifications', 'woocommerce-ac' ); ?></a></li>            
            </ul>
            <br class="clear">
            <?php 
        }
        
        /**
         * Displays the SMS Templates
         *
         * @since 7.9
         */
        static function display_sms_list() {
            
            //Wcap_SMS::cart_recovery_views();
            $new_id = Wcap_SMS::get_new_id();
            
            ?>
            <p><?php _e( 'Add SMS notifications to be sent at different intervals to maximize the possibility of recovering your abandoned carts.', 'woocommerce-ac' ); ?></p>

            <p><?php _e( "<b>Edit Instructions:</b> Please click on the Text Message to edit it. Then click on the Save button. You can edit multiple text messages at once.", 'woocommerce-ac' ); ?></p>
            
            <div style="width:75%;display:inline-block;float:left;">
                <p style="float:left;">
                    <a cursor: pointer; href="javascript:void(0)" class="button-secondary" id="new_sms" ><?php _e( 'Add New Text Message', 'woocommerce-ac' ); ?></a>
					<input type='hidden' id='new_template_id' value='<?php echo $new_id;?>' />
					<?php 
					$defaults = Wcap_SMS::wcap_create_defaults();
					Wcap_SMS::wcap_add_defaults( $defaults ); ?>
                </p>
                <?php

                $sms_list = new Wcap_SMS_Templates();
                $sms_list->wcap_sms_templates_prepare_items();
                ?>
                <div class="wrap">
                    <form id="wcap-sms-templates" method="get" >
                        <input type="hidden" name="page" value="woocommerce_ac_page" />
                        <input type="hidden" name="action" value="cart_recovery" />
                        <input type="hidden" name="section" value="sms" />
                        <?php $sms_list->display(); ?>
                    </form>
                </div>
            	        
            </div>
            <div id="merge_tags" style='display:inline-block;width:25%;float:left;'>
                    <p style="float:left;">
                        <b><?php _e( 'Merge tags available for Text Messages:', 'woocommerce-ac' );?></b>
                        <br><br>
                        <i>{{user.name}} - <?php _e( 'First Name of the User', 'woocommerce-ac' );?><br><br>
                        {{shop.name}} - <?php _e( 'Shop Name ', 'woocommerce-ac' );?>[<?php echo get_option( 'blogname' ); ?>]<br><br>
                        {{shop.link}} - <?php _e( 'Shop Link ', 'woocommerce-ac' );?>[<?php echo get_permalink( wc_get_page_id( 'shop' ) ); ?>]<br><br>
                        {{date.abandoned}} - <?php _e( 'Date on which the Cart was abandoned', 'woocommerce-ac' );?><br><br>
                        {{coupon.code}} - <?php _e( 'Discount coupon code', 'woocommerce-ac' );?><br><br>
                        {{checkout.link}} - <?php _e( 'Checkout Link to complete the purchase.', 'woocommerce-ac' );?><br><br>
                        {{phone.number}} - <?php _e( 'Admin Phone number', 'woocommerce-ac' );?>
                        </i>
                    </p>
                </div>
            <?php
                        
        }
        
        /**
         * Returns an array of default value for each column.
         * This is specifically used when adding a new row.
         *
         * @since 7.9
         */
		static function wcap_create_defaults() {

		    // get the maxID
		    $new_id = Wcap_SMS::get_new_id();
			$default_sms_obj = new Wcap_SMS_Templates();

			$columns = $default_sms_obj->get_columns();
			$hidden = $default_sms_obj->wcap_hidden_sms_cols();
			// defaults for each column
			$defaults = array();
			
			// Default Values 
			$default_sms_values = (object) array( 
			    'id'             => $new_id,
			    'updated'        => 1,
			    'txt_msg'        => '',
			    'full_txt_msg'   => '',
			    'coupon_code'    => '',
			    'sent_time'      => '1 minutes',
			    'sms_sent'       => 0,
			    'active'       => 'on'
			);
            
			foreach( $columns as $col_name => $col_desc ) {
			    
			    // Style attributes for each col
			    $class = "class='$col_name column-$col_name'";
			    $style = ( in_array( $col_name, $hidden ) ) ? " style='display:none;'" : ''; 
			    $attributes = $class . $style;
			    	
				switch( $col_name ) {
					case 'cb':
						$value = "<th scope='row' class='check-column'><input name='template_id[]' value= '$new_id' type='checkbox'></th>"; 
						break;
					case 'id':
					    $value = "<td $attributes>";
					    $value .= $default_sms_obj->column_default( $default_sms_values, $col_name ) . "</td>";
					    break;
				    case 'updated':
				        $value = "<td $attributes>";
				        $value .= $default_sms_obj->column_default( $default_sms_values, $col_name ) . "</td>";
				        break;
					case 'txt_msg': 
	                    $value = "<td $attributes><textarea class='msg' rows='4'>";
	                    $value .= $default_sms_obj->column_default( $default_sms_values, $col_name ) . "</textarea></td>"; 
	                    break;
	                case 'full_txt_msg': 
	                    $value = "<td $attributes>";
                        $value .= $default_sms_obj->column_default( $default_sms_values, $col_name ) . "</td>"; 
	                    break;
                    case 'coupon_code':
                        $value = "<td $attributes>";
                        $value .= $default_sms_obj->column_default( $default_sms_values, $col_name ) . "</td>";
                        break;
	                case 'sent_time': 
	                    $value = "<td $attributes>";
	                    $value .= $default_sms_obj->column_default( $default_sms_values, $col_name ) . "</td>"; 
	                    break;
	                case 'sms_sent': 
	                    $value = "<td $attributes>";
	                    $value .= $default_sms_obj->column_default( $default_sms_values, $col_name ) . "</td>";
	                    break;
	                case "activate": 
	                    $value = "<td $attributes>";
	                    $value .= $default_sms_obj->column_default( $default_sms_values, $col_name ) . "</td>"; 
	                    break;
                    case "actions":
                        $value = "<td $attributes>";
                        $value .= $default_sms_obj->column_default( $default_sms_values, $col_name ) . "</td>";
                        break;
				}
				$defaults[ $col_name ] = $value;
			}
			
			return $defaults;
		}
		
		/**
		 * Returns the new ID for the SMS
		 * @since 7.9
		 */
		static function get_new_id() {
		    
		    global $wpdb;
		    
		    $max_id = $wpdb->get_results( "SELECT MAX(ID) as maxID FROM " . WCAP_NOTIFICATIONS );
		    $new_id = isset( $max_id[0]->maxID ) ? $max_id[0]->maxID + 1 : 1;
		    
		    return $new_id;
		}
		
		/**
		 * Creates hidden fields for the columns.
		 * These fields contain the default values
		 * for each column.
		 * @since 7.9
		 */
		static function wcap_add_defaults( $defaults ) {
		    
		    foreach( $defaults as $col_name => $default_value ) {
		        ?>
		        
		        <input type="hidden" id="<?php echo $col_name . '_default' ?>" value="<?php echo $default_value; ?>" />
		        <?php
		    }
		}
		
		/**
		 * Deletes the SMS Template when delete action is executed for 
		 * a single SMS template.
		 * Called via AJAX
		 * @since 7.9
		 */
        public static function wcap_delete_sms() {
            
            $template_id = isset( $_POST[ 'template_id' ] ) ? $_POST[ 'template_id' ] : 0;
            
            if( $template_id > 0 ) {
                self:: wcap_delete_template_data( $template_id );
            }
            die();
        }

        /**
         * Delete the SMS Template from the DB & its meta data
         * @since 7.9
         */
        static function wcap_delete_template_data( $template_id ) {
            global $wpdb;
        
            // delete the template meta
            $wpdb->delete( WCAP_NOTIFICATIONS_META, array( 'template_id' => $template_id ) );
        
            // delete the template from the parent table
            $wpdb->delete( WCAP_NOTIFICATIONS, array( 'id' => $template_id ) );
        }
        
        /**
         * Saves the SMS Templates
         * Called via AJAX
         * @since 7.9
         */
        static function wcap_save_bulk_sms() {
            
            global $wpdb;
            
            $clean_template_data = new stdClass();
            if ( isset( $_POST[ 'template_data' ] ) ) {
                $post_templates = $_POST[ 'template_data' ];
                $tempData = stripslashes( $post_templates );
                $clean_template_data = json_decode($tempData);
            }
            
            $insert = false;
            if ( $clean_template_data != false && count( $clean_template_data ) > 0 ) {
                foreach( $clean_template_data as $id => $data ) {
                    
                    // check if a record is present for the template ID
                    $template_details = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM " . WCAP_NOTIFICATIONS . " WHERE ID = %d", $id ) );
                    $template_present = ( isset( $template_details[0]->ID ) ) ? true : false;
                     
                    $data_entries = explode( '|~|', $data );
                    
                    $sms_body = isset( $data_entries[0] ) ? $data_entries[0] : '';
                    $frequency = isset( $data_entries[1] ) ? $data_entries[1] : '';
                    $active = ( isset( $data_entries[2] ) && $data_entries[2] == 'on' ) ? 1 : 0;
                    $coupon_code = isset( $data_entries[3] ) && $data_entries[3] > 0 ? $data_entries[3] : '';
                    $default_template = isset( $data_entries[4] ) ? $data_entries[4] : 0;
                    
                    // if template is active
                    if( $active == 1 ) {
                        // check if a record for the same frequency is present
                        $get_temp = "SELECT id FROM " . WCAP_NOTIFICATIONS . "
                                        WHERE type = 'sms'
                                        AND frequency = %s
                                        AND is_active = '1'";
                    
                        $result_templates = $wpdb->get_results( $wpdb->prepare( $get_temp, $frequency ) );
                    
                        //  if it's active, deactivate that
                        if( is_array( $result_templates ) && count( $result_templates ) > 0 ) {
                    
                            $wpdb->update( WCAP_NOTIFICATIONS, array( 'is_active' => 0 ), array( 'type' => 'sms', 'frequency' => $frequency ) );
                        }
                    }
                    // if yes, update the data
                    if( $template_present ) {
                        
                        wcap_update_notifications( 
                            $id, 
                            $sms_body, 
                            $frequency, 
                            $active, 
                            $coupon_code 
                        );
                    } else { // else add a new record

                        $temp_id = wcap_insert_notifications( 
                            $sms_body, 
                            'sms', 
                            $active, 
                            $frequency, 
                            $coupon_code, 
                            $default_template 
                        );
                        
                        $insert = true;
                    }
                }
            }
            echo $insert;
            die();
            
        }
    } // end of class
    $wcap_sms = new WCap_SMS();
}
