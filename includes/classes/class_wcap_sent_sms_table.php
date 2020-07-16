<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * It will show records of the Abandoned cart reminder SMS which is sent to customers 
 * and displayed in Reminders Sent->SMS Sent.
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @category Classes
 * @since    7.10.0
 */
// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * Show abandoned cart reminder SMS record in Reminders Sent.
 * 
 * @since 7.10.0
 */
class Wcap_Sent_SMS_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 7.10.0
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 7.10.0
	 */
	public $base_url;

	/**
	 * Total number of Sent SMS
	 *
	 * @var int
	 * @since 7.10.0
	 */
	public $total_count;

	/**
	 * Total amount of Links clicked
	 *
	 * @var int
	 * @since 7.10.0
	 */
	public $link_click_count;

	/**
	 * Start date
	 *
	 * @var int
	 * @since 7.10.0
	 */
	public $start_date_db;

	/**
	 * End date
	 *
	 * @var int
	 * @since 7.10.0
	 */
	public $end_date_db;

	/**
	 * Duration
	 *
	 * @var int
	 * @since 7.10.0
	 */
	public $duration;

   	/**
	 * It will add the bulk action and other variable needed for the class.
	 *
	 * @since 7.10.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;
		// Set parent defaults
		parent::__construct( array(
	        'singular' => __( 'sent_sms_id', 'woocommerce-ac' ), //singular name of the listed records
	        'plural'   => __( 'sent_sms_ids', 'woocommerce-ac' ), //plural name of the listed records
			'ajax'      => true             			// Does this table support ajax?
		) );

		$this->base_url = admin_url( 'admin.php?page=woocommerce_ac_page&action=stats&section=sms' );
	}
	
	/**
	 * It will prepare the list of the User Phone Number, columns, pagination, sortable column and other data.
	 *
	 * @since 7.10.0
	 */
	public function wcap_sent_sms_prepare_items() {

		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$data                  = $this->wcap_sent_sms_data();
		$total_items           = $this->total_count;
 		$link_click_count      = $this->link_click_count;
		$this->items           = $data;
		$this->_column_headers = array( $columns, $hidden);
		$this->set_pagination_args( array(
                'total_items' => $total_items,                  	// WE have to calculate the total number of items
                'per_page'    => $this->per_page,                     	// WE have to determine how many items to show on a page
                'total_pages' => ceil( $total_items / $this->per_page )   // WE have to calculate the total number of pages
            )
		); 
	}

	/**
	 * It will add the columns for Reminders Sent->SMS Sent
	 *
	 * @return array $columns All columns name.
	 * @since  7.10.0
	 */
	public function get_columns() {

	    $columns = array(
	    	'cb'                => '<input type="checkbox" />',
	        'user_phone_number' => __( 'User Phone Number'       , 'woocommerce-ac' ),
            'sent_time'         => __( 'SMS Sent Time'          , 'woocommerce-ac' ),
	        'date_time_opened'  => __( 'Date / Time  Link Opened', 'woocommerce-ac' ),
			'link_clicked'  	=> __( 'Link Clicked'             , 'woocommerce-ac' ),
            'template_name'     => __( 'Sent SMS Template'      , 'woocommerce-ac'),
	    );
		return apply_filters( 'wcap_sent_sms_columns', $columns );
	}

	/**
	 * It is used to add the check box for the items
	 *
	 * @param string $item 
	 * @return string 
	 * @since 7.10.0
	 */
	function column_cb( $item ) {
	    $wcap_email_sent_id = '';
	    if( isset( $item->sms_sent_id ) && "" != $item->sms_sent_id ) {
	       $wcap_sms_sent_id = $item->sms_sent_id;
 	       return sprintf(
	           '<input type="checkbox" name="%1$s[]" value="%2$s" />',
	           'wcap_email_sent_id',
	           $wcap_sms_sent_id
	       );
	    }
	}
	
    /**
	 * This function used for deleting individual row of abandoned Cart. Render the User Phone Number. So we will add the action on the hover affect. 
	 *
	 * @access public
	 * @since 7.10.0
	 * @param array $abandoned_row_info Contains all the data of the template row
	 * @return string Data shown in the Email column
	 *
	 */
    static function column_user_phone_number( $sent_sms_row_info ) {

	    $row_actions = array();
	    $value = '';
	    $sent_id = 0;

	    if( isset( $sent_sms_row_info->user_phone_number ) ){
	        $display_link = $sent_sms_row_info->display_link ;
    	    if ( "Abandoned" == $display_link ){

    	    	$wcap_array = array(
								'action' => 'wcap_abandoned_cart_info',
								'cart_id' => $sent_sms_row_info->abandoned_order_id
					    	);
            	$wcap_url = add_query_arg(
            					$wcap_array , admin_url( 'admin-ajax.php' )
        					);

				$view_name = __( "Abandoned Order", "woocommerce-ac" );
    	        $row_actions['view_details']   = "<a class=wcap-js-open-modal data-wcap-cart-id=".$sent_sms_row_info->abandoned_order_id." data-wcap-cart-id=".$sent_sms_row_info->abandoned_order_id." data-modal-type=ajax href = $wcap_url>". $view_name ."</a>";
    	    }else{
    	        $view_link = "post.php?post=" . $sent_sms_row_info->recovered_order_id . "&action=edit";
    	        $view_name = __( " Recovered Order", "woocommerce-ac" );
    	        $row_actions['view_details']   = "<a target=_blank href = $view_link>". $view_name ."</a>";
    	    }
			$user_name = $sent_sms_row_info->user_phone_number; 
            $value     = $user_name . $this->row_actions( $row_actions ); 
	    }
        return apply_filters( 'wcap_sent_sms_single_column', $value, $sent_id, 'sms' );
	}
	
    /**
	 *  Return an array of SMS Template IDs
	 *  
	 *  @return array|false - Array of SMS Templates | False when none found 
	 *  @since 7.10.0
	 */
	static function get_sms_template_ids() {
	    global $wpdb;
	    
	    $sms_query = "SELECT id FROM " . WCAP_NOTIFICATIONS . "
	                   WHERE type = 'sms'";
	    
	    $get_sms = $wpdb->get_col( $sms_query );
	    
	    if( is_array( $get_sms ) && count( $get_sms ) > 0 ) {
	        return $get_sms;
	    } else {
	        return false;
	    }
	}
	
	/**
     * It will generate the sent abandoned cart reminder SMS list in Reminders Sent->SMS Sent
     *
     * @globals mixed $wpdb
     * @return array $return_sent_sms_display Key and value of all the columns
     * @since 7.10.0
     */
	public function wcap_sent_sms_data() {

	    global $wpdb;
	    $wcap_class 	  = new Woocommerce_Abandon_Cart ();
	    
	    // duration
	    if ( isset( $_POST['duration_select_sms'] ) && '' != $_POST['duration_select_sms'] ){
	        $duration_range         = $_POST['duration_select_sms'];
	        $_SESSION['duration']   = $duration_range;
	    } else if ( isset( $_SESSION ['duration'] ) && '' != $_SESSION ['duration'] ){
	        $duration_range         = $_SESSION ['duration'];
	    }
	    
	    if ( ! isset( $duration_range ) || ( isset( $duration_range ) && '' == $duration_range ) ) {
	        $duration_range         = "last_seven";
	        $_SESSION['duration']   = $duration_range;
	    }
	     
	    // start date
	    $start_date_range = '';
	    if ( isset( $_POST['start_date_sms'] ) && '' != $_POST['start_date_sms'] ) {
	        $start_date_range        = $_POST['start_date_sms'];
	        $_SESSION ['start_date'] = $start_date_range;
	    } else if ( isset( $_SESSION ['start_date'] ) &&  '' != $_SESSION ['start_date'] ) {
	        $start_date_range = $_SESSION ['start_date'];
	    }
	    
	    if ( '' == $start_date_range ) {
	        $start_date_range = $wcap_class->start_end_dates[$duration_range]['start_date'];
	        $_SESSION ['start_date'] = $start_date_range;
	    }
	    
	    // end date
	    $end_date_range = '';
	    if ( isset( $_POST['end_date_sms'] ) && '' != $_POST['end_date_sms'] ){
	        $end_date_range = $_POST['end_date_sms'];
	        $_SESSION ['end_date'] = $end_date_range;
	    } else if ( isset($_SESSION ['end_date'] ) && '' != $_SESSION ['end_date'] ){
	        $end_date_range = $_SESSION ['end_date'];
	    }
	    
	    if ( '' == $end_date_range ) {
	        $end_date_range = $wcap_class->start_end_dates[$duration_range]['end_date'];
	        $_SESSION ['end_date'] = $end_date_range;
	    }
	     
	    $start_date    		= strtotime( $start_date_range." 00:00:00" );
	    $end_date      		= strtotime( $end_date_range." 23:59:59" );
	    
	    $results_sms_sent = array();
	    
	    $total_sms_sent = 0;
	    $total_links_clicked = 0;
	    
	    $msg_ids = array();
	    // get the SMS templates list
	    $sms_templates = self::get_sms_template_ids();
	    
	    // for the sms templates, get the sms data
	    if( $sms_templates ) {
	        $i = 0;
	             
	        $template_ids_str = implode( ',', $sms_templates );
	        
            // get the sms sent data
            $get_data = "SELECT id, template_id, cart_id, notification_data FROM " . WCAP_TINY_URLS . "
                           WHERE date_created >= %s
                           AND date_created <= %s
                           AND template_id IN ($template_ids_str)
                           ORDER BY date_created DESC";
            
            $sms_data = $wpdb->get_results( $wpdb->prepare( $get_data, $start_date, $end_date ) );
    
            if( is_array( $sms_data ) && count( $sms_data ) > 0 ) {

                foreach( $sms_data as $display_data ) {
                    
                    if( $display_data->notification_data != '' ) {
	                    
	                    $display_data_decoded = json_decode( $display_data->notification_data );

	                    if( isset( $display_data_decoded->msg_id ) && isset( $display_data_decoded->phone_number ) && '' != $display_data_decoded->phone_number ) {
	                        
	                        // Add the message ID to the array
	                        if( ! in_array( $display_data_decoded->msg_id, $msg_ids ) ) {
	                           $msg_ids[ $i ] = $display_data_decoded->msg_id;
	                        } else {
	                            // get the key
	                            $key = array_search( $display_data_decoded->msg_id, $msg_ids );
	                            
	                            // add the new link name and time
	                            if( is_int( $key ) ) {
	                                
	                                if( $results_sms_sent[ $key ]->sent_time == '' ) {
	                                   $results_sms_sent[ $key ]->date_time_opened = isset( $display_data_decoded->link_opened_time ) ? $display_data_decoded->link_opened_time : '';
	                                   $results_sms_sent[ $key ]->link_clicked = isset( $display_data_decoded->link_clicked ) && '' != $display_data_decoded->link_opened_time ? $display_data_decoded->link_clicked : '';
	                                } else if( isset( $display_data_decoded->link_opened_time ) ) {
	                                    $results_sms_sent[ $key ]->date_time_opened .= ',' . $display_data_decoded->link_opened_time;
	                                    if( isset( $display_data_decoded->link_clicked ) ) {
	                                       $results_sms_sent[ $key ]->link_clicked .= ',' . $display_data_decoded->link_clicked;
	                                    }
	                                }
            	                    
	                            }
	                            continue;
	                        }
	                        $template_id = $display_data->template_id;
	                        
    	                    $results_sms_sent[$i] = new stdClass();
    	                    // SMS Sent ID
    	                    $results_sms_sent[$i]->sms_sent_id = $display_data->id;
    	                    // Phone Number
    	                    $results_sms_sent[$i]->user_phone_number = $display_data_decoded->phone_number;
    	                    
    	                    // SMS Sent Time
    	                    $results_sms_sent[$i]->sent_time = $display_data_decoded->sent_time;
    	                    
    	                    // Link Clicked Time
    	                    $results_sms_sent[$i]->date_time_opened = isset( $display_data_decoded->link_opened_time ) ? $display_data_decoded->link_opened_time : '';
    	                    
    	                    // Link Clicked
                            $results_sms_sent[$i]->link_clicked = isset( $display_data_decoded->link_clicked ) && '' != $results_sms_sent[$i]->date_time_opened ? $display_data_decoded->link_clicked : '';  	                    

	                        // Template ID
    	                    $results_sms_sent[$i]->template_name = $template_id;
    	                    
    	                    // abandoned order id
    	                    $abandoned_order_id = $display_data->cart_id;
    	                    $results_sms_sent[$i]->abandoned_order_id = $abandoned_order_id;
    	                    
    	                    // Order Recovered
    	                    $recovered_query = "SELECT recovered_cart FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "`
    	                                           WHERE id = %d";
    	                    $recovered_result = $wpdb->get_col( $wpdb->prepare( $recovered_query, $abandoned_order_id ) );
    	                    
    	                    $recovered_order = isset( $recovered_result[0] ) && $recovered_result[0] > 0 ? $recovered_result[0] : 0;
                            $results_sms_sent[$i]->recovered_order_id = $recovered_order;
                            
    	                    // display link
    	                    $results_sms_sent[$i]->display_link = $recovered_order > 0 ? '' : 'Abandoned';
    	                    
    	                    // update the count
    	                    $total_sms_sent++;
    	                    if( $results_sms_sent[$i]->date_time_opened != '' ) {
    	                        $total_links_clicked++;
    	                    } 
    	                    $i++;
                        }
                    }
                }
	                
	            
	        }
	        
	        $per_page        = $this->per_page;
	        if ( isset( $_GET['paged'] ) && $_GET['paged'] > 1 ) {
	            $page_number = $_GET['paged'] - 1;
	            $k           = $per_page * $page_number;
	        }else {
	            $k           = 0;
	        }
	         
	        $return_sent_sms_display = array();
	        for ( $j = $k; $j < ( $k + $per_page ); $j++ ) {
	            if ( isset( $results_sms_sent[ $j ] ) ) {
	                $return_sent_sms_display[ $j ] = $results_sms_sent[ $j ];
	            }else {
	                break;
	            }
	        }
	    }
	    
	    $this->total_count = $total_sms_sent;
	    $this->link_click_count = $total_links_clicked;
	    
		return apply_filters( 'wcap_sent_sms_table_data', $return_sent_sms_display );
	}
	
	/**
	 * It will display the data for the abanodned column.
	 *
	 * @param  array | object $wcap_sent_sms All sent SMS data
	 * @param  stirng $column_name Name of the column
	 * @return string $value Data of the column
	 * @since  7.10.0
	 */
	public function column_default( $wcap_sent_sms, $column_name ) {
	    $value = '';
	    
	    $wcap_date_format = get_option( 'date_format' );
	    $wcap_time_format = get_option( 'time_format' );
	     
	    switch ( $column_name ) {

	        case 'sent_time':
	            $value = isset( $wcap_sent_sms->sent_time ) ? date( "$wcap_date_format $wcap_time_format", $wcap_sent_sms->sent_time ) : '';
	            break;
            case 'date_time_opened':
                if( isset( $wcap_sent_sms->date_time_opened ) && strpos( $wcap_sent_sms->date_time_opened, ',' ) >= 0 ) {
                    
                    $link_times = explode( ',', $wcap_sent_sms->date_time_opened );
                    
                    $value = isset( $link_times[0] ) && $link_times[0] != '' ? date( "$wcap_date_format $wcap_time_format", trim( $link_times[0] ) ) : '';
                    $value .= isset( $link_times[1] ) && $link_times[1] != '' ? date( "$wcap_date_format $wcap_time_format", trim( $link_times[1] ) ) : '';
                } else {
                    $value = ( isset( $wcap_sent_sms->date_time_opened ) && $wcap_sent_sms->date_time_opened != '' ) ? date( "$wcap_date_format $wcap_time_format", $wcap_sent_sms->date_time_opened ) : '';
                }
                break;
            case 'link_clicked':
                if( isset( $wcap_sent_sms->link_clicked ) && strpos( $wcap_sent_sms->link_clicked, ',' ) >= 0 ) {
                    $link_names = explode( ',', $wcap_sent_sms->link_clicked );
                    
                    $value = isset( $link_names[0] ) ? $link_names[0] : '';
                    $value .= isset( $link_names[1] ) ? $link_names[1] : '';
                }
                break;
	        default:
				$value = isset( $wcap_sent_sms->$column_name ) ? $wcap_sent_sms->$column_name : '';
				break;
	    }

		return apply_filters( 'wcap_sent_sms_column_default', $value, $wcap_sent_sms, $column_name );
	} 
} 
?>