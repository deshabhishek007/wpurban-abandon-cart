<?php
/**
 * It will fetch the Add to cart data, generate and populate data in the modal.
 * @author  Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Settings
 * @since 6.0
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
if ( ! class_exists( 'Wcap_Add_Cart_Popup_Modal' ) ) {
    
    /**
 	 * It will fetch the Add to cart data, generate and populate data in the modal.
 	 * @since 6.0
 	 */
    class Wcap_Add_Cart_Popup_Modal {

    	/**
    	 * This function will add the add to cart popup medal's settings.
    	 * @since 6.0
    	 */
        public static function wcap_add_to_cart_popup_settings() {
    		$wcap_atc_enabled        = get_option( 'wcap_atc_enable_modal' );
    		$wcap_disabled_field     = '';
    		if ( 'off' == $wcap_atc_enabled ) {
    			$wcap_disabled_field = 'disabled="disabled"';
    		} 
    		?>
			<div id = "wcap_popup_main_div" class = "wcap_popup_main_div ">
    			<table id = "wcap_popup_main_table" class = "wcap_popup_main_table test_borders">
    				<tr id = "wcap_popup_main_table_tr" class = "wcap_popup_main_table_tr test_borders">
    					<td id = "wcap_popup_main_table_td_settings" class = "wcap_popup_main_table_td_settings test_borders">    						
    						<?php Wcap_Add_Cart_Popup_Modal::wcap_enable_modal_section( $wcap_disabled_field ); ?>
    						<?php self::wcap_custom_pages_section( $wcap_disabled_field ); ?>
							<hr>
    						<div class = "wcap_atc_all_fields_container" >
	    						<?php Wcap_Add_Cart_Popup_Modal::wcap_add_heading_section( $wcap_disabled_field ); ?>
	    						<?php Wcap_Add_Cart_Popup_Modal::wcap_add_text_section( $wcap_disabled_field ); ?>
	    						<?php Wcap_Add_Cart_Popup_Modal::wcap_email_placeholder_section( $wcap_disabled_field ); ?>
	    						<?php Wcap_Add_Cart_Popup_Modal::wcap_button_section( $wcap_disabled_field ); ?>
	    						<?php Wcap_Add_Cart_Popup_Modal::wcap_mandatory_modal_section( $wcap_disabled_field ); ?>
	    						<?php Wcap_Add_Cart_Popup_Modal::wcap_non_mandatory_modal_section_field( $wcap_disabled_field ); ?>
								<hr>
								<?php Wcap_Add_cart_Popup_modal::wcap_coupon_section( $wcap_disabled_field ); ?>
    						</div>
    					</td>
    					<td id = "wcap_popup_main_table_td_preview" class = "wcap_popup_main_table_td_preview test_borders">
    						<div class = "wcap_atc_all_fields_container" >
    							<?php Wcap_Add_Cart_Popup_Modal::wcap_add_to_cart_popup_modal_preview( $wcap_disabled_field ); ?>
    						</div>
    					</td>
					</tr>
    				<tr>
    					<td>
    						<div class = "wcap_atc_all_fields_container" >
    							<p class = "submit">
    								<input type = "submit" name = "submit" id = "submit" class = "button button-primary" value = "Save Changes" <?php echo $wcap_disabled_field; ?> >
    								<input type = "submit" name = "submit" id = "submit" class = "wcap_reset_button button button-secondary" value = "Reset to default configuration" <?php echo $wcap_disabled_field; ?> >
    							</p>
    						</div>
    					</td>
					</tr>
    			</table>
			</div>
    		<?php
    	}

    	/**
    	 * It will add the "Enable Add to cart popup modal" setting on the add to cart modal settings page.
    	 * @param string $wcap_disabled_field It will indicate if field need to be disabled or not.
    	 * @since 6.0
    	 */
		public static function wcap_enable_modal_section( $wcap_disabled_field ){
    		?>
    			<table class = "wcap_enable_atc wcap_atc_between_fields_space wcap_atc_content" id = "wcap_enable_atc" >
    				<th id = "wcap_button_section_table_heading" class = "wcap_button_section_table_heading"> Enable Add to cart popup modal </th>
    				<tr>
    					<td>
    					   <?php 
    					   $wcap_atc_enabled = get_option('wcap_atc_enable_modal');
    				       $active_text      = __( $wcap_atc_enabled, 'woocommerce-ac' ); 
    				       ?>
    				       <button type = "button" class = "wcap-enable-atc-modal wcap-toggle-atc-modal-enable-status" wcap-atc-switch-modal-enable = <?php echo $wcap_atc_enabled; ?> >
    					   <?php echo $active_text; ?>  
    					   </button>
    					</td>
					</tr>
				</table>
    		<?php	
    	}

    	/**
    	 * Adds a multi select searchable dropdown from where
    	 * the admin can select custom pages on which the
    	 * Add to Cart Popup modal should be displayed. 
    	 * 
    	 * @param string $wcap_disabled_field It will indicate if field need to be disabled or not.
    	 * @since 7.10.0
    	 */
    	public static function wcap_custom_pages_section( $wcap_disabled_field ) {
             global $woocommerce;
            $post_title_array = array();
    	    ?>
    	    <table class = "wcap_custom_pages wcap_atc_between_fields_space wcap_atc_content" id = "wcap_custom_pages" >
                <tr>
            	    <th id="wcap_button_section_table_heading" class="wcap_button_section_table_heading"> <?php _e( 'Custom pages to display the pop-up modal on', 'woocommerce-ac' );?> </th>
                </tr>
        	    <tr>
            	    <td>
                	    <?php
                        $custom_pages = get_option('wcap_custom_pages_list');
                        ?>
                        <?php if ( $woocommerce->version >= '3.0' ) { ?>
                            <select style="width:76%" multiple="multiple" class="wcap_page_select wc-product-search" name="wcap_page_select[]" data-placeholder='<?php esc_attr__( 'Search for a Page&hellip;', 'woocommerce-ac' )?>' data-action='wcap_json_find_pages'>

                                <?php 
                                   if( is_array( $custom_pages ) && count( $custom_pages ) > 0 ) {
                                       foreach( $custom_pages as $page_ids ) {
                                           $post_id = $page_ids;
                                           $post_title = get_the_title( $post_id );
                                           
                                           printf( "<option value='%s' selected>%s</option>\n", $post_id, $post_title );
                                       }
                                   }
                                ?> 
                            </select> 
                        <?php } else { 
                            if( is_array( $post_title_array ) && is_array( $custom_pages ) && count( $custom_pages ) > 0 ) {
                                       foreach( $custom_pages as $page_ids ) {
                                           $post_id = $page_ids;
                                           $post_title = get_the_title( $post_id );
                                           $post_title_array[$post_title] = $post_title;
                                          
                                       }
                                   }
                            ?>
                        
                        <input type="hidden" style="width:80%" id = "wcap_page_select" class="wc-product-search" name="wcap_page_select[]" data-placeholder='<?php esc_attr_e( 'Search for a Page&hellip;', 'woocommerce-ac' )?>' data-multiple="true" data-action='wcap_json_find_pages' data-selected=" <?php echo esc_attr( json_encode( $post_title_array ) ); ?>" value="<?php echo implode( ',', array_keys( $post_title_array ) ); ?>"/>
                            
                        <?php } ?>
               
                        <?php $toolTip = __( 'Please add any custom pages (not created by WooCommerce) where you wish to display the Add to cart Pop-up Modal.', 'woocommerce-ac' ); ?>
                        <?php echo wc_help_tip( $toolTip ); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: justify;">
                        <?php _e( "<b>Note:</b> Please ensure that the Add to Cart button links on these pages are added with the correct classes and attributes to ensure the plugin can capture the cart data correctly. For further guidance, please check the documentation.", 'woocommerce-ac' );?>
                    </td>
                </tr>
			</table>
    		<?php 
    	}
    	/**
    	 * It will Save the setting on the add to cart modal settings page.
    	 * @since 6.0
    	 */
        public static function wcap_add_to_cart_popup_save_settings() {
			if ( $_POST ['wcap_heading_section_text_email'] ) {
				update_option ( 'wcap_heading_section_text_email', stripslashes( $_POST ['wcap_heading_section_text_email'] ) );
			}			
			if ( $_POST ['wcap_popup_heading_color_picker'] ) {
				update_option ( 'wcap_popup_heading_color_picker', $_POST ['wcap_popup_heading_color_picker'] );
			}			
			if ( $_POST ['wcap_text_section_text'] ) {
				update_option ( 'wcap_text_section_text', stripslashes( $_POST ['wcap_text_section_text'] ) );
			}			
			if ( $_POST ['wcap_popup_text_color_picker'] ) {
				update_option ( 'wcap_popup_text_color_picker', $_POST ['wcap_popup_text_color_picker'] );
			}			
			if ( $_POST ['wcap_email_placeholder_section_input_text'] ) {
				update_option ( 'wcap_email_placeholder_section_input_text', $_POST ['wcap_email_placeholder_section_input_text'] );
			}			
			if ( $_POST ['wcap_button_section_input_text'] ) {
				update_option ( 'wcap_button_section_input_text', stripslashes( $_POST ['wcap_button_section_input_text'] ) );
			}
			if ( $_POST ['wcap_button_color_picker'] ) {
				update_option ( 'wcap_button_color_picker', $_POST ['wcap_button_color_picker'] );
			}
			if ( isset( $_POST ['wcap_button_text_color_picker'] ) ){
				update_option ( 'wcap_button_text_color_picker', $_POST ['wcap_button_text_color_picker'] );
			}
			if ( isset( $_POST ['wcap_non_mandatory_modal_section_fields_input_text'] ) ) {
				update_option( 'wcap_non_mandatory_text', $_POST ['wcap_non_mandatory_modal_section_fields_input_text'] );
			}
			
			$custom_pages = isset( $_POST[ 'wcap_page_select' ] ) ? $_POST[ 'wcap_page_select' ] : array();
			update_option( 'wcap_custom_pages_list', $custom_pages );

			$wcap_atc_coupon_type = isset( $_POST['wcap_atc_coupon_type'] ) ? $_POST['wcap_atc_coupon_type'] : '';
			update_option( 'wcap_atc_coupon_type', $wcap_atc_coupon_type );

			$auto_apply_coupon_code = isset( $_POST['coupon_ids'][0] ) ? $_POST['coupon_ids'][0] : 0;
			update_option( 'wcap_atc_popup_coupon', $auto_apply_coupon_code );

			$wcap_atc_discount_type = isset( $_POST['wcap_atc_discount_type'] ) ? $_POST['wcap_atc_discount_type'] : '';
			update_option( 'wcap_atc_discount_type', $wcap_atc_discount_type );

			$wcap_atc_discount_amount = isset( $_POST['wcap_atc_discount_amount'] ) ? $_POST['wcap_atc_discount_amount'] : 0;
			update_option( 'wcap_atc_discount_amount', $wcap_atc_discount_amount );

			$wcap_atc_coupon_free_shipping = isset( $_POST['wcap_atc_coupon_free_shipping'] ) ? $_POST['wcap_atc_coupon_free_shipping'] : 'off';
			update_option( 'wcap_atc_coupon_free_shipping' , $wcap_atc_coupon_free_shipping );
			
			$auto_apply_coupon_validity = isset( $_POST['wcap_atc_coupon_validity'] ) ? $_POST['wcap_atc_coupon_validity'] : 0;
			update_option( 'wcap_atc_popup_coupon_validity', $auto_apply_coupon_validity );

			$countdown_msg = isset( $_POST['wcap_countdown_msg'] ) ? $_POST['wcap_countdown_msg'] : '';
			update_option( 'wcap_countdown_timer_msg', htmlspecialchars( $countdown_msg ) );

			$countdown_msg_expired = isset( $_POST['wcap_countdown_msg_expired'] ) ? $_POST['wcap_countdown_msg_expired'] : '';
			update_option( 'wcap_countdown_msg_expired', $countdown_msg_expired );
			
		}

		/**
    	 * It will add the setting for Heading section on the add to cart modal settings page.
    	 * @param string $wcap_disabled_field It will indicate if field need to be disabled or not.
    	 * @since 6.0
    	 */
        public static function wcap_add_heading_section( $wcap_disabled_field ) {	
    	    ?>
            <div id = "wcap_heading_section_div" class = "wcap_heading_section_div wcap_atc_between_fields_space">
                <table id = "wcap_heading_section_table" class = "wcap_heading_section_table wcap_atc_content">
                    <th id = "wcap_heading_section_table_heading" class ="wcap_heading_section_table_heading"> Modal Heading </th>
                    <tr id = "wcap_heading_section_tr" class = "wcap_heading_section_tr" >
        				<td id = "wcap_heading_section_text_field" class = "wcap_heading_section_text_field test_borders">
        					<input type="text" id = "wcap_heading_section_text_email" v-model = "wcap_heading_section_text_email"  name = "wcap_heading_section_text_email"class = "wcap_heading_section_text_email"
        					<?php echo $wcap_disabled_field; ?> >
        				</td>            				
        				<td id = "wcap_heading_section_text_field_color" class = "wcap_heading_section_text_field_color test_borders">
        					<?php $wcap_popup_heading_color_picker = get_option( 'wcap_popup_heading_color_picker' ); ?>
        					<span class = "colorpickpreview" style = "background:<?php echo $wcap_popup_heading_color_picker; ?>"></span>
        					<input type="text" class = "wcap_popup_heading_color_picker colorpick" name = "wcap_popup_heading_color_picker" value = "{{wcap_popup_heading_color}}" v-model = "wcap_popup_heading_color" v-on:input = "wcap_atc_popup_heading.color = $event.target.value"
					           <?php echo $wcap_disabled_field; ?> >
        				</td>
    			     </tr>
        		</table>
    		</div>
    		<?php
    	}

    	/**
    	 * It will add the setting for Text displayed below heading section on the add to cart modal settings page.
    	 * @param string $wcap_disabled_field It will indicate if field need to be disabled or not.
    	 * @since 6.0
    	 */
    	public static function wcap_add_text_section( $wcap_disabled_field ) {
    		?>
            <div id = "wcap_text_section_div" class = "wcap_text_section_div wcap_atc_between_fields_space">
                <table id = "wcap_text_section_table" class = "wcap_text_section_table wcap_atc_content">
                    <th id = "wcap_text_section_table_heading" class = "wcap_text_section_table_heading"> Modal Text </th>
                	<tr id = "wcap_text_section_tr" class = "wcap_text_section_tr" >
                		<td id = "wcap_text_section_text_field" class = "wcap_text_section_text_field test_borders">
                			<input type="text" id = "wcap_text_section_text" v-model = "wcap_text_section_text_field" class="wcap_text_section_input_text" name = "wcap_text_section_text"
                			<?php echo $wcap_disabled_field; ?> >
                		</td>                    		
                		<td id = "wcap_text_section_field_color" class = "wcap_text_section_field_color test_borders">
                			<?php $wcap_atc_popup_text_color = get_option( 'wcap_popup_text_color_picker' ); ?>
                			<span class = "colorpickpreview" style = "background:<?php echo $wcap_atc_popup_text_color; ?>"></span>
                			<input type="text" class = "wcap_popup_text_color_picker colorpick" name = "wcap_popup_text_color_picker" value = "{{wcap_popup_text_color}}" v-model = "wcap_popup_text_color" v-on:input = "wcap_atc_popup_text.color = $event.target.value"
                			<?php echo $wcap_disabled_field; ?> >
                		</td>
                	</tr>
                </table>
            </div>
    		<?php
		}

		/**
    	 * It will add the setting for email placeholder on the add to cart modal settings page.
    	 * @param string $wcap_disabled_field It will indicate if field need to be disabled or not.
    	 * @since 6.0
    	 */
    	public static function wcap_email_placeholder_section( $wcap_disabled_field ) {
			?>
    		<div id = "wcap_email_placeholder_section_div" class = "wcap_email_placeholder_section_div wcap_atc_between_fields_space">
	    		<table id = "wcap_email_placeholder_section_table" class = "wcap_email_placeholder_section_table wcap_atc_content">
	    		<th id = "wcap_email_placeholder_section_table_heading" class = "wcap_email_placeholder_section_table_heading"> Email placeholder </th>
	    			<tr id = "wcap_email_placeholder_section_tr" class = "wcap_email_placeholder_section_tr" >
	    				<td id = "wcap_email_placeholder_section_text_field" class = "wcap_email_placeholder_section_text_field test_borders">
	    					<input type="text" id = "wcap_email_placeholder_section_input_text" v-model = "wcap_email_placeholder_section_input_text" class="wcap_email_placeholder_section_input_text" name = "wcap_email_placeholder_section_input_text" 
	    					<?php echo $wcap_disabled_field; ?> >
	    				</td>
	    			</tr>
	    		</table>
    		</div>
    		<?php
    	}

    	/**
    	 * It will add the setting for Add to cart button on the add to cart modal settings page.
    	 * @param string $wcap_disabled_field It will indicate if field need to be disabled or not.
    	 * @since 6.0
    	 */
    	public static function wcap_button_section( $wcap_disabled_field ) {
    		?>
    		<div id = "wcap_button_section_div" class = "wcap_button_section_div wcap_atc_between_fields_space">
	    		<table id = "wcap_button_section_table" class = "wcap_button_section_table wcap_atc_content">
	    		<th id = "wcap_button_section_table_heading" class="wcap_button_section_table_heading"> Add to cart button text </th>
	    			<tr>
	    				<td id = "wcap_button_section_text_field" class = "wcap_button_section_text_field test_borders">
	    					<input type="text" id = "wcap_button_section_input_text" v-model = "wcap_button_section_input_text" class="wcap_button_section_input_text" name = "wcap_button_section_input_text"
	    					<?php echo $wcap_disabled_field ; ?> >
	    				</td>
	    			</tr>
	    			<tr id = "wcap_button_color_section_tr" class = "wcap_button_color_section_tr">
	    				<td id = "wcap_button_color_section_text_field" class = "wcap_button_color_section_text_field test_borders">
	    					<?php $wcap_atc_button_bg_color = get_option( 'wcap_button_color_picker' ); ?>
	    					<span class = "colorpickpreview" style = "background:<?php echo $wcap_atc_button_bg_color; ?>"></span>
	    					<input type="text" id = "wcap_button_color_picker" value = "{{wcap_button_bg_color}}" v-model ="wcap_button_bg_color" v-on:input="wcap_atc_button.backgroundColor = $event.target.value" class="wcap_button_color_picker colorpick" name = "wcap_button_color_picker"
							<?php echo $wcap_disabled_field; ?> >
	    				</td>
	    				<td id = "wcap_button_text_color_section_text_field" class = "wcap_button_text_color_section_text_field test_borders">
	    					<?php $wcap_button_text_color_picker = get_option('wcap_button_text_color_picker'); ?>
	    					<span class = "colorpickpreview" style = "background:<?php echo $wcap_button_text_color_picker; ?>"></span>
	    					<input type="text" id = "wcap_button_text_color_picker" value = "{{wcap_button_text_color}}" v-model = "wcap_button_text_color" v-on:input = "wcap_atc_button.color = $event.target.value" class="wcap_button_text_color_picker colorpick" name = "wcap_button_text_color_picker" 
	    					<?php echo $wcap_disabled_field; ?> >
	    				</td>
					</tr>
	    		</table>
    		</div>
    		<?php
    	}
		
		/**
    	 * It will add the setting for Email address mandatory field on the add to cart modal settings page.
    	 * @param string $wcap_disabled_field It will indicate if field need to be disabled or not.
    	 * @since 6.0
    	 */
		public static function wcap_mandatory_modal_section( $wcap_disabled_field ) {
			?>
			<table class = "wcap_atc_between_fields_space wcap_atc_content">
				<th id = "wcap_button_section_table_heading" class = "wcap_button_section_table_heading"> Email address is mandatory ? </th>
				<tr>
					<td>
					   <?php 
					   $wcap_atc_email_mandatory = get_option( 'wcap_atc_mandatory_email' );
				       $active_text   			 = __( $wcap_atc_email_mandatory, 'woocommerce-ac' );
				       ?>
				       <button type = "button" class = "wcap-switch-atc-modal-mandatory wcap-toggle-atc-modal-mandatory" wcap-atc-switch-modal-mandatory = <?php echo $wcap_atc_email_mandatory; ?> 
				       <?php echo $wcap_disabled_field; ?> >
					   <?php echo $active_text; ?> </button>
					</td>
				</tr>
			</table>
    		<?php
    	}

    	/**
    	 * It will add the setting for Email address non mandatory field on the add to cart modal settings page.
    	 * @param string $wcap_disabled_field It will indicate if field need to be disabled or not.
    	 * @since 6.0
    	 */
    	public static function wcap_non_mandatory_modal_section_field( $wcap_disabled_field ) {
    		$wcap_get_mandatory_field      = get_option( 'wcap_atc_mandatory_email' );
    		$wcap_disabled_email_field     = '';
    		if ( 'on' == $wcap_get_mandatory_field ) {
    			$wcap_disabled_email_field = 'disabled="disabled"';
    		}
			?>
    		<div id = "wcap_non_mandatory_modal_section_fields_div" class = "wcap_non_mandatory_modal_section_fields_div wcap_atc_between_fields_space">
	    		<table id = "wcap_non_mandatory_modal_section_fields_div_table" class = "wcap_non_mandatory_modal_section_fields_div_table wcap_atc_content">
    	    		<th id = "wcap_non_mandatory_modal_section_fields_table_heading" 
    	    		class="wcap_non_mandatory_modal_section_fields_table_heading"> Not mandatory text </th>
	    			<tr id = "wcap_non_mandatory_modal_section_fields_tr" class = "wcap_non_mandatory_modal_section_fields_tr" >
	    				<td id = "wcap_non_mandatory_modal_section_fields_text_field" class = "wcap_non_mandatory_modal_section_fields_text_field test_borders">
	    					<input type="text" id = "wcap_non_mandatory_modal_section_fields_input_text" v-model = "wcap_non_mandatory_modal_input_text" class = "wcap_non_mandatory_modal_section_fields_input_text" name = "wcap_non_mandatory_modal_section_fields_input_text" 
	    					<?php echo $wcap_disabled_field; 
	    						  echo $wcap_disabled_email_field;
	    					?> >
	    				</td>
	    			</tr>
	    		</table>
    		</div>
    		<?php
    	}

		/**
		 * Auto Apply coupons for atc settings.
		 *
		 * @param string $disabled - Enable/disable fields.
		 * @since 8.5.0
		 */
		public static function wcap_coupon_section( $disabled ) {
			$auto_apply_coupon     = get_option( 'wcap_atc_auto_apply_coupon_enabled', 'off' );
			$active_text           = __( $auto_apply_coupon, 'woocommerce-ac' );

			$wcap_atc_coupon_type  = get_option( 'wcap_atc_coupon_type', '' );
			$pre_selected          = 'pre-selected' === $wcap_atc_coupon_type || '' === $wcap_atc_coupon_type ? 'selected' : '';
			$unique                = 'unique' === $wcap_atc_coupon_type ? 'selected' : '';

			$coupon_code_id        = get_option( 'wcap_atc_popup_coupon' );

			$wcap_atc_discount_type = get_option( 'wcap_atc_discount_type', '' );
			$percent_discount       = 'percent' === $wcap_atc_discount_type || '' === $wcap_atc_discount_type ? 'selected' : '';
			$amount_discount        = 'amount' === $wcap_atc_discount_type ? 'selected' : '';

			$wcap_atc_discount_amount = get_option( 'wcap_atc_discount_amount', '' );
			$wcap_atc_coupon_free_shipping = get_option( 'wcap_atc_coupon_free_shipping' );
			$free_shipping_enabled = 'on' === $wcap_atc_coupon_free_shipping ? 'checked' : '';

			$coupon_validity       = get_option( 'wcap_atc_popup_coupon_validity' );
			$countdown_msg         = htmlspecialchars_decode( get_option( 'wcap_countdown_timer_msg', 'Coupon <coupon_code> expires in <hh:mm:ss>. Avail it now.' ) );
			$countdown_msg_expired = get_option( 'wcap_countdown_msg_expired', 'The offer is no longer valid.' );
			$countdown_cart        = get_option( 'wcap_countdown_cart', 'on' );
			$active_cart           = __( $countdown_cart, 'woocommerce-ac' );
			?>
			<div id='wcap_coupon_settings'>
				<table id='wcap_coupon_settings_div_table' class='wcap_coupon_settings_div_table wcap_atc_content'>
					<th id='wcap_auto_apply_coupons_heading' class='wcap_auto_apply_coupons_heading'><?php esc_html_e( 'Auto apply coupons on email address capture:', 'woocommerce-ac' ); ?></th>
					<tr>
						<td>
							<button type="button" class="wcap-auto-apply-coupons-atc wcap-toggle-auto-apply-coupons-status" wcap-atc-switch-coupon-enable = <?php echo esc_attr( $auto_apply_coupon ); ?> 
							<?php echo esc_attr( $disabled ); ?> >
							<?php echo $active_text; ?></button>
						</td>
					</tr>
					<th id='wcap_atc_coupon_type_label' class='wcap_atc_coupon_type_label'><?php esc_html_e( 'Type of Coupon to apply:', 'woocommerce-ac' ); ?></th>
					<tr>
						<td>
							<select id='wcap_atc_coupon_type' name='wcap_atc_coupon_type'>
								<option value='pre-selected' <?php echo esc_html( $pre_selected ); ?>><?php esc_html_e( 'Existing Coupons', 'woocommerce-ac' ); ?></option>
								<option value='unique' <?php echo esc_html( $unique ); ?>><?php esc_html_e( 'Generate Unique Coupon code', 'woocommerce-ac' ); ?></option>
							</select>
						</td>
					</tr>
					<th id='wcap_auto_apply_coupon_id' class='wcap_auto_apply_coupon_id wcap_atc_pre_selected'><?php esc_html_e( 'Coupon code to apply:', 'woocommerce-ac' ); ?></th>
					<tr class='wcap_atc_pre_selected'>
						<td>
							<div id="coupon_options" class="panel">
								<div class="options_group">
									<p class="form-field" style="padding-left:0px !important;">
									<?php
										$json_ids       = array();
										
										if ( $coupon_code_id > 0 ) {
											$coupon = get_the_title( $coupon_code_id );
											$json_ids[ $coupon_code_id ] = $coupon ;
										}
										if( version_compare( WC()->version, '3.0.0', ">=" ) ) {
										?>
											<select id="coupon_ids" name="coupon_ids[]" class="wc-product-search" multiple="multiple" style="width: 37%;" data-placeholder="<?php esc_attr_e( 'Search for a Coupon&hellip;', 'woocommerce' ); ?>" data-action="wcap_json_find_coupons" <?php echo esc_attr( $disabled ); ?>>
											<?php
											if ( $coupon_code_id > 0  ) {
												$coupon = get_the_title( $coupon_code_id );
												echo '<option value="' . esc_attr( $coupon_code_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $coupon ) . '</option>';
											}
											?>
											</select>
											<?php
										} else {
										?>
										<input type="hidden" id="coupon_ids" name="coupon_ids[]" class="wc-product-search" style="width: 30%;" data-placeholder="<?php esc_attr_e( 'Search for a Coupon&hellip;', 'woocommerce' ); ?>" data-multiple="true" data-action="wcap_json_find_coupons"
										data-selected=" <?php echo esc_attr( json_encode( $json_ids ) ); ?> " value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>" <?php echo esc_attr( $disabled ); ?>
										/>
										<?php 
										}
										?>												
									</p>
								</div>
							</div>
						</td>
					</tr>
					<th id='wcap_atc_discount_type_label' class='wcap_atc_discount_type_label wcap_atc_unique'><?php esc_html_e( 'Discount Type:', 'woocommerce-ac' ); ?></th>
					<tr class='wcap_atc_unique'>
						<td>
							<select id='wcap_atc_discount_type' name='wcap_atc_discount_type'>
								<option value='percent' <?php echo esc_html( $percent_discount ); ?>><?php esc_html_e( 'Percentage Discount', 'woocommerce-ac'); ?></option>
								<option value='amount' <?php echo esc_html( $amount_discount ); ?>><?php esc_html_e( 'Fixed Cart Amount', 'woocommerce-ac' ); ?></option>
							</select>
						</td>
					</tr>
					<th id='wcap_atc_discount_amount_label' class='wcap_atc_discount_amount_label wcap_atc_unique'><?php esc_html_e( 'Discount Amount:', 'woocommerce-ac' ); ?></th>
					<tr class='wcap_atc_unique'>
						<td>
							<input type='number' id='wcap_atc_discount_amount' name='wcap_atc_discount_amount' min='0' value='<?php echo esc_html( $wcap_atc_discount_amount ); ?>' <?php echo esc_attr( $disabled ); ?> />
						</td>
					</tr>
					<th id='wcap_atc_coupon_free_shipping_label' class='wcap_atc_coupon_free_shipping_label wcap_atc_unique'><?php esc_html_e( 'Allow Free Shipping?', 'woocommerce-ac' ); ?></th>
					<tr class='wcap_atc_unique'>
						<td>
							<input type='checkbox' id='wcap_atc_coupon_free_shipping' name='wcap_atc_coupon_free_shipping' <?php echo esc_attr( $free_shipping_enabled ); ?> <?php echo esc_attr( $disabled ); ?> />
						</td>
					</tr>		
					<th id='wcap_atc_coupon_validity_label' class='wcap_atc_coupon_validity_label'><?php esc_html_e( 'Coupon validity (in minutes):', 'woocommerce-ac' ); ?></th>
					<tr>
						<td>
							<input type='number' id='wcap_atc_coupon_validity' name='wcap_atc_coupon_validity' min='0' value='<?php echo esc_attr( $coupon_validity ); ?>' <?php echo esc_attr( $disabled ); ?> />
						</td>
					</tr>
					<th id='countdown_timer_cart_label' class='countdown_timer_cart_label'><?php esc_html_e( 'Display Urgency message on Cart page (If disabled it will display only on Checkout page)', 'woocommerce-ac' ); ?></th>
					<tr>
						<td>
							<button type="button" class="wcap-countdown-timer-cart wcap-toggle-countdown-timer-cart" wcap-atc-countdown-timer-cart-enable = <?php echo esc_attr( $countdown_cart ); ?> 
							<?php echo esc_attr( $disabled ); ?> >
							<?php echo $active_text; ?></button>
						</td>
					</tr>
					<th id='wcap_countdown_msg_label' class='wcap_countdown_msg_label'><?php esc_html_e( 'Urgency message to boost your conversions', 'woocommerce-ac' ); ?></th>
					<tr>
						<td>
							<input type='text' id='wcap_countdown_msg' name='wcap_countdown_msg' placeholder='<?php echo esc_attr('Coupon <coupon_code> expires in <hh:mm:ss>. Avail it now.' ); ?>' value='<?php echo esc_attr( $countdown_msg ); ?>' <?php echo esc_attr( $disabled ); ?>/>
							<br>
							<i><?php echo esc_html_e( 'Merge tags available: <coupon_code>, <hh:mm:ss>', 'woocommerce-ac' ); ?></i>
						</td>
					</tr>
					<th id='wcap_countdown_msg_label' class='wcap_countdown_msg_expired_label'><?php esc_html_e( 'Message to display after coupon validity is reached', 'woocommerce-ac' ); ?></th>
					<tr>
						<td>
							<input type='text' id='wcap_countdown_msg_expired' name='wcap_countdown_msg_expired' placeholder='<?php echo esc_attr('The offer is no longer valid.' ); ?>' value='<?php echo esc_attr( $countdown_msg_expired ); ?>' <?php echo esc_attr( $disabled ); ?>/>
							<br>
							<i><?php // echo esc_html_e( 'Merge tags available: <coupon_code>', 'woocommerce-ac' ); ?></i>
						</td>
					</tr>
					<th id='wcap_atc_coupon_note' class='wcap_atc_coupon_note'><i><?php esc_html_e( 'Note: For orders which use the coupon selected/generated by the ATC module will be marked as "ATC Coupon Used" in WooCommerce->Orders.', 'woocommerce-ac' ); ?></i></th>
					<tr></tr>
				</table>
			</div>
			<?php
		}

    	/**
    	 * It will will show th preview of the Add To cart Popup modal with the changes made on any of the settings for it.
    	 * @param string $wcap_disabled_field It will indicate if field need to be disabled or not.
    	 * @since 6.0
    	 */
    	public static function wcap_add_to_cart_popup_modal_preview( $wcap_disabled_field ) {
    		
    		?>
    		<div class = "wcap_container">
				<div class = "wcap_popup_wrapper">
				    <div class = "wcap_popup_content">
				        <div class = "wcap_popup_heading_container">
				            <div class = "wcap_popup_icon_container" >
				                <span class = "wcap_popup_icon"  >
				                    <span class = "wcap_popup_plus_sign" v-bind:style = "wcap_atc_button">
				                    </span>
				                </span>
				            </div>
				            <div class = "wcap_popup_text_container">
				                <h2 class = "wcap_popup_heading" v-bind:style = "wcap_atc_popup_heading" >{{wcap_heading_section_text_email}}</h2>
				                <div class = "wcap_popup_text" v-bind:style = "wcap_atc_popup_text" >{{wcap_text_section_text_field}}</div>
				            </div>
				        </div>
				        <div class = "wcap_popup_form">
				            <form action = "" name = "wcap_modal_form">
				                <div class = "wcap_popup_input_field_container"  >
				                    <input class = "wcap_popup_input" type = "text" value = "" name = "email" placeholder = {{wcap_email_placeholder_section_input_text}}
				                    <?php echo $wcap_disabled_field; ?> readonly >
				                </div>
				                <button class = "wcap_popup_button" v-bind:style = "wcap_atc_button"
				                <?php echo $wcap_disabled_field ; ?> >{{wcap_button_section_input_text}}</button>
				                <br>
				                <br>
				                <div id = "wcap_non_mandatory_text_wrapper" class = "wcap_non_mandatory_text_wrapper">
				                    <a class = "wcap_popup_non_mandatory_button" href = "" > {{wcap_non_mandatory_modal_input_text}}</a>
				                </div>
				            </form>
				        </div>
				        <div class = "wcap_popup_close" ></div>
				    </div>
				</div>
			</div>
    		<?php
    	}
	}
}
