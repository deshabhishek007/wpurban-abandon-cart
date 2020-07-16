<?php 
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * It will show list of Active and deactive SMS templates in 
 * WooCommerce->Abandoned Carts->Cart Recovery->SMS Notifications
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @category Classes
 * @since    7.9
 */
// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * Show SMS templates list in Cart Recovery->SMS Notifications
 * 
 * @since 7.9
 */
class Wcap_SMS_Templates extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 7.9
	 */
	public $per_page = 20;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 7.9
	 */
	public $base_url;

	/**
	 * Total number of templates
	 *
	 * @var int
	 * @since 7.9
	 */
	public $total_count;

    /**
	 * It will add the bulk action and other variable needed for the class.
	 *
	 * @since 7.9
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {

		// Set parent defaults
		parent::__construct( array(
	        'singular' => __( 'sms_template_id', 'woocommerce-ac' ), //singular name of the listed records
	        'plural'   => __( 'sms_template_ids', 'woocommerce-ac' ), //plural name of the listed records
			'ajax'      => true             			// Does this table support ajax?
		) );
		$this->wcap_get_sms_templates_count();
		$this->process_bulk_action();
        $this->base_url = admin_url( 'admin.php?page=woocommerce_ac_page&action=cart_recovery&section=sms' );
	}
	/**
	 * It will prepare the list of the SMS Templates, columns, pagination, sortable column and other data.
	 *
	 * @since 7.9
	 */
	public function wcap_sms_templates_prepare_items() {

		$columns               = $this->get_columns();
		$hidden                = $this->wcap_hidden_sms_cols();
		$sortable              = array();
		$data                  = $this->wcap_sms_templates();		
		$this->_column_headers = array( $columns, $hidden, $sortable);
		$total_items           = $this->total_count;
		$this->items           = $data;
		
		$this->set_pagination_args( array(
				'total_items' => $total_items,                  	// WE have to calculate the total number of items
				'per_page'    => $this->per_page,                     	// WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $this->per_page )   // WE have to calculate the total number of pages
		      )
		);
	}
	
	function wcap_hidden_sms_cols() {
	    return ( apply_filters( 'wcap_sms_hidden_cols', array( 'updated', 'full_txt_msg', 'sms_sent' ) ) );
	}
	/**
	 * It will add the columns for Cart Recovery->SMS Notifications
	 *
	 * @return array $columns All columns name.
	 * @since  7.9
	 */
	public function get_columns() {
	    
        $columns = array(
            'cb'                  => '<input type="checkbox" />',
            'id'                  => __( 'ID', 'woocommerce-ac' ),
            'updated'             => __( 'Updated', 'woocommerce-ac' ),
            'txt_msg'             => __( 'Text Message', 'woocommerce-ac' ),
            'full_txt_msg'        => __( 'Complete Message', 'woocommerce-ac' ),
            'coupon_code'         => __( 'Coupon Code', 'woocommerce-ac' ),
        	'sent_time'     	  => __( 'Sent After Set Time', 'woocommerce-ac' ),
        	'sms_sent'  	      => __( 'Messages Sent', 'woocommerce-ac' ),
        	'activate'  		  => __( 'Start Sending', 'woocommerce-ac' ),
            'actions'             => __( 'Actions', 'woocommerce-ac' )
        );
		return apply_filters( 'wcap_sms_templates_col', $columns );
	}
	
	/**
	 * It is used to add the check box for the items
	 *
	 * @param string $item 
	 * @return string 
	 * @since 7.9
	 */
	function column_cb( $item ){
	    
	    $template_id = '';
	    if( isset( $item->id ) && "" != $item->id ){
	       $template_id = $item->id; 
	    }
	    return sprintf(
	        '<input type="checkbox" name="%1$s[]" value="%2$s" />',
	        'template_id',
	        $template_id
	    );
	}

	/**
     * Returns the data for the SMS Template list. 
     *
     * @globals mixed $wpdb
     * @return array $return_templates_display Key and value of all the columns
     * @since 7.9
     */
	public function wcap_sms_templates() { 
    	global $wpdb;    	
    	
    	$return_templates_data = array();
    	
    	// Get the count of sms templates from the DB
    	$sms_query = "SELECT * FROM " . WCAP_NOTIFICATIONS . "
    	               WHERE type = %s";
    	$sms_list = $wpdb->get_results( $wpdb->prepare( $sms_query, 'sms' ) );
    	
    	$template_count = 0;
    	if( is_array( $sms_list ) && count( $sms_list ) > 0 && false !== $sms_list ) {
            foreach( $sms_list as $sms_details ) {
                // SMS ID
                $template_id = $sms_details->id;
                
                // Default
                $return_templates_data[ $template_count ] = new stdClass();
                
                $return_templates_data[ $template_count ]->id = $template_id;
                
                $return_templates_data[ $template_count ]->updated = 0;
                 
                // Complete Message
                $return_templates_data[ $template_count ]->full_txt_msg = $sms_details->body;

                // Coupon Code
                $return_templates_data[ $template_count ]->coupon_code = $sms_details->coupon_code;
                
                $return_templates_data[ $template_count ]->sent_time = $sms_details->frequency;
                $return_templates_data[ $template_count ]->sms_sent = 0;
                $return_templates_data[ $template_count ]->active = $sms_details->is_active == 1 ? 'on' : 'off';
                $template_count++;
            }
        	
    	}
    	
        return apply_filters( 'wcap_sms_templates_data', $return_templates_data );
        
	}
	/**
	 * Displays the column data. The data sent in is displayed with
	 * correct HTML or as needed.
	 *
	 * @param  array | object $wcap_sms_list All data of the list
	 * @param  stirng $column_name Name of the column
	 * @return string $value Data of the column
	 * @since  7.9
	 */
	public function column_default( $wcap_sms_list, $column_name ) {
	    $value = '';
	    
	    switch ( $column_name ) {	       
	        	            
			case 'activate' :
			    if( isset( $wcap_sms_list->active ) ) {			       
			       $id        = $wcap_sms_list->id;
			       $active = $wcap_sms_list->active;
		
			       $active_text   = __( $active, 'woocommerce-ac' ); 
			       $value =  "<button type='button' class='wcap-switch wcap-toggle-template-status' " 
					. "wcap-sms-id='$id' "
					. "wcap-template-switch='" . ( $active ) . "'>"
					. $active_text . '</button>'; 
			       
			    }
			    
				break;	

			case 'txt_msg':
			    
			    $sms_id = $wcap_sms_list->id;
			    
			    // Truncated Message to be displayed on page load
			    if( isset( $wcap_sms_list->full_txt_msg ) && '' !== $wcap_sms_list->full_txt_msg ) {
			        
			        $truncated_msg = substr( $wcap_sms_list->full_txt_msg, 0, 30 );
			        $msg_link = "<a id='sms_txt_$sms_id' href='javascript:void(0)'>$truncated_msg...</a>";
			    } else {
			        $msg_link = '';
			    }
			        
		        $value = $msg_link;
			    
			    break;

			case 'coupon_code':
			    $id        = $wcap_sms_list->id;
			    $value = "
                <div id='coupon_options' class='panel'>
                    <div class='options_group'>
                        <p class='form-field' style='padding-left:0px !important;'>";
			    
			    $json_ids       = array();
			    $coupon_ids     = array();
			    $coupon_code_id = ( isset( $wcap_sms_list->coupon_code ) ) ? $wcap_sms_list->coupon_code : '';
			    
			    if ( $coupon_code_id > 0 ) {
			        
                    $product = get_the_title( $coupon_code_id );
                    $json_ids[ $coupon_code_id ] = $product ;
                
			    }
			    
			    if( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) > 0 ) {
			        $value .= "
                    <select id='coupon_ids_$id' name='coupon_ids[]' class='wc-coupon-search wc-product-search' style='width: 100%;' data-placeholder='" . esc_attr__( 'Search for a Coupon&hellip;', 'woocommerce' ) . "' data-action='wcap_json_find_coupons' multiple='multiple'>";
                            if ( $coupon_code_id > 0  ) {
                                $coupon = get_the_title( $coupon_code_id );
                                $value .= "<option value='" . esc_attr( $coupon_code_id ) . "'" . selected( true, true, false ) . ">" . wp_kses_post( $coupon ) . "</option>";
                            }
                        
                    $value .= "</select>";
                } else {
                    $value .= "
                    <input type='hidden' id='coupon_ids_$id' name='coupon_ids[]' class='wc-coupon-search wc-product-search' style='width: 100%;' data-placeholder='" . esc_attr__( 'Search for a Coupon&hellip;', 'woocommerce' ) ."' data-action='wcap_json_find_coupons'
                       data-selected='" . esc_attr( json_encode( $json_ids ) ) . "' value='" . implode( ',', array_keys( $json_ids ) ) . "'
                    />";
                }
                
                $value .= "</p></div></div>";
                 
			    break;
			case 'sent_time':
			    $sms_id = $wcap_sms_list->id;
			    
			    $freq = $wcap_sms_list->sent_time;
			    
			    $min_loop = 59;
			    $hour_loop = 23;
			    $day_loop = 90;
			     
			    $freq_split = explode( ' ', $freq );
			    
			    $frequency_numeric = $freq_split[0];
			    $frequency_value = isset( $freq_split[1] ) ? $freq_split[1] : 'minutes';
			    
			    switch( strtolower( $frequency_value ) ) {
			        case '':
			        case 'minutes':
			            $count = $min_loop;
			            break;
			        case 'hours':
			            $count = $hour_loop;
			            break;
			        case 'days':
			            $count = $day_loop;
			            break;
			    }
			     
			    $select_count = "<select id='freq_count_$sms_id'>";
			    for( $i = 0; $i <= $count; $i++ ) {
			        $select_count .= ( $frequency_numeric == $i ) ? "<option value='$i' selected>$i</option>" : "<option value='$i'>$i</option>";
			    }
			    $select_count .= '</select>';
			     
			    $select_freq = "<select id='freq_value_$sms_id'>";
			     
			    $value_array = array( 'minutes' => __( 'Minute(s)', 'woocommerce-ac' ),
			                          'hours'   => __( 'Hour(s)', 'woocommerce-ac' ),
			                          'days'    => __( 'Day(s)', 'woocommerce-ac' ) 
			     );
			    
			    foreach( $value_array as $k => $v ) {
			        $select_freq .= ( strtolower( $frequency_value ) == $k ) ? "<option value='$k' selected>$v</option>" : "<option value='$k'>$v</option>"; 
			    }

                $select_freq .= "</select>";
			     
			    $value = "$select_count &nbsp; $select_freq &nbsp; " . __( 'After Abandonment', 'woocommerce-ac' );
			     
			     break;
			case 'actions':
			    $sms_id = $wcap_sms_list->id;
			    $value = "<a href='javascript:void(0)' id='delete_$sms_id' class='delete_sms' ><i class='fa fa-trash-o fa-2x'></i></a>";
			    break;
			default:			    
				$value = isset( $wcap_sms_list->$column_name ) ? $wcap_sms_list->$column_name : '';
				break;
	    }		
		return apply_filters( 'wcap_sms_template_column_default', $value, $wcap_sms_list, $column_name );
	}
	
	/**
	 * It will add the 'Delete' bulk action in the SMS template list.
	 *
	 * @return array - Bulk action
	 * @since  7.9
	 */
	public function get_bulk_actions() {
	    return array(
	        'wcap_delete_sms_template' => __( 'Delete', 'woocommerce-ac' )
	    );
	}
	
	/**
	 * Adds the Save button at the top and bottom of the
	 * SMS Template list
	 * 
	 * @since 7.9
	 */
	function extra_tablenav( $which ) {
	    ?>
	    <input type='button' class='sms_bulk_save button-primary alignright' value='<?php _e( 'Save', 'woocommerce-ac' ); ?>' /> 
	    <?php 
	}
	
	/**
	 * Displays the table rows
	 * 
	 * @since 7.9
	 */
	function display_rows() {
	    
	    // Get the records registered in the prepare_items method
	    $records = $this->items;
	    
	    // Get the columns registered in the get_columns and get_sortable_columns methods
	    list( $columns, $hidden ) = $this->get_column_info();
	    
	    // Loop for each record
	    if( !empty( $records ) ) {
	        foreach($records as $rec){
	    
    	        // Open the row
    	        echo '<tr id="sms_'.$rec->id.'">';
    	        foreach ( $columns as $column_name => $column_display_name ) {
    	    
    	            // Style attributes for each col
    	            $class = "class='$column_name column-$column_name'";
    	            $style = "";
    	            if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
    	            $attributes = $class . $style;
    	    
    	            // Display the cell
    	            switch ( $column_name ) {
    	                case "cb":
    	                    echo '<th scope="row" class="check-column">';
				            echo $this->column_cb( $rec );
				            echo '</th>';
    	                    break;
    	                case "id":  
    	                    echo "<td $attributes>";
    	                    echo $this->column_default( $rec, $column_name ) . '</td>';   
    	                    break;
    	                case "updated":
    	                    echo "<td $attributes>";
    	                    echo $this->column_default( $rec, $column_name ) . '</td>';
    	                    break;
    	                case "txt_msg": 
    	                    echo "<td $attributes>";
    	                    echo $this->column_default( $rec, $column_name ) . '</td>'; 
    	                    break;
    	                case "full_txt_msg": 
    	                    echo "<td $attributes>";
    	                    echo $this->column_default( $rec, $column_name ) . '</td>'; 
    	                    break;
    	                case 'coupon_code':
    	                    echo "<td $attributes>";
    	                    echo $this->column_default( $rec, $column_name ) . '</td>';
    	                    break;
    	                case "sent_time": 
    	                    echo "<td $attributes>";
    	                    echo $this->column_default( $rec, $column_name ) . '</td>'; 
    	                    break;
    	                case "sms_sent": 
    	                    echo "<td $attributes>";
    	                    echo $this->column_default( $rec, $column_name ) . '</td>'; 
    	                    break;
    	                case "activate": 
    	                    echo "<td $attributes>";
    	                    echo $this->column_default( $rec, $column_name ) .'</td>'; 
    	                    break;
	                    case "actions":
	                        echo "<td $attributes>";
	                        echo $this->column_default( $rec, $column_name ) . '</td>';
	                        break;
    	            }
    	        }
    	    
    	        // Complete the row
    	        echo'</tr>';
            }
	    }
	}  
}
?>