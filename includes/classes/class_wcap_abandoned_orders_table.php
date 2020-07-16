<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * It will show Abandoned Carts data on Abandoned Orders tab.
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @category Classes
 * @since    5.0
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * Show Abandoned Carts data on Abandoned Orders tab.
 * 
 * @since 2.4.7
 */
class Wcap_Abandoned_Orders_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 2.4.7
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 2.4.7
	 */
	public $base_url;

	/**
	 * Total number of abandoned carts
	 *
	 * @var int
	 * @since 2.4.7
	 */
	public $total_count;

	/**
	 * Total number of carts
	 *
	 * @var int
	 * @since 7.14.0
	 */
	public $total_all_count;

	/**
	 * Total number of registered user carts
	 *
	 * @var int
	 * @since 7.14.0
	 */
	public $total_registered_count;

	/**
	 * Total number of guest carts
	 *
	 * @var int
	 * @since 7.14.0
	 */
	public $total_guest_count;

	/**
	 * Total number of visitor carts
	 *
	 * @var int
	 * @since 7.14.0
	 */
	public $total_visitors_count;

	/**
	 * Total number of recovered carts
	 *
	 * @var int
	 * @since 8.3
	 */
	public static $recovered_count;

	/**
	 * Total amount of recovered orders
	 *
	 * @var float
	 * @since 8.3
	 */
	public static $recovered_amount;

    /**
	 * It will add the bulk action and other variable needed for the class.
	 *
	 * @since 2.4.7
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;
		// Set parent defaults
		parent::__construct( array(
    	        'singular' => __( 'abandoned_order_id', 'woocommerce-ac' ),  //singular name of the listed records
    	        'plural'   => __( 'abandoned_order_ids', 'woocommerce-ac' ), //plural name of the listed records
    			'ajax'     => false             			                 // Does this table support ajax?
    		    )
		);
		$this->process_bulk_action();
        $this->base_url = admin_url( 'admin.php?page=woocommerce_ac_page&action=listcart' );
	}
	/**
	 * It will prepare the list of the abandoned carts, columns, pagination, sortable column and other data.
	 *
	 * @since 2.0
	 */
	public function wcap_abandoned_order_prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$this->total_count     = $this->wcap_get_total_abandoned_count();
		$sortable              = $this->get_sortable_columns();
		$data                  = $this->wcap_abandoned_cart_data();
		$this->_column_headers = array( $columns, $hidden, $sortable);
		$total_items           = $this->total_count;

		if( count($data) > 0 ) {
		  $this->items = $data;
		} else {
		    $this->items = array();
		}
		$this->set_pagination_args( array(
				'total_items' => $total_items,                  	      // WE have to calculate the total number of items
				'per_page'    => $this->per_page,                     	  // WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $this->per_page )   // WE have to calculate the total number of pages
		      )
		);
	}
	/**
	 * It will add the columns for Abanodned Orders Tab.
	 *
	 * @return array $columns All columns name.
	 * @since  2.0
	 */
	public function get_columns() {
        $display_tracked_coupons = get_option( 'ac_track_coupons' );
        $columns                 = array();
        if( "on" == $display_tracked_coupons ) {
            $columns = array(
                'cb'                 => '<input type="checkbox" />',
                'id'                 => __( 'Id', 'woocommerce-ac' ),
                'email'              => __( 'Email Address', 'woocommerce-ac' ),
                'customer'     		 => __( 'Customer Details', 'woocommerce-ac' ),
                'order_total'  		 => __( 'Order Total', 'woocommerce-ac' ),
                'date'               => __( 'Abandoned Date', 'woocommerce-ac' ),
                'coupon_code_used'   => __( 'Coupon Code Used','woocommerce-ac' ),
                'coupon_code_status' => __( 'Coupon Status','woocommerce-ac' ),
                'email_captured_by'  => __( 'Email Captured By','woocommerce-ac' ),
                'status'             => __( 'Status of Cart', 'woocommerce-ac' ),
                'wcap_actions' 		 => __( 'More info', 'woocommerce-ac' )
            );
        } else {
        	$columns = array(
    	        'cb'                => '<input type="checkbox"/>',
                'id'                => __( 'Id', 'woocommerce-ac' ),
    	        'email'             => __( 'Email Address', 'woocommerce-ac' ),
    			'customer'     		=> __( 'Customer Details', 'woocommerce-ac' ),
    			'order_total'  		=> __( 'Order Total', 'woocommerce-ac' ),
    	        'date'              => __( 'Abandoned Date', 'woocommerce-ac' ),
    	        'email_captured_by' => __( 'Email Captured By','woocommerce-ac' ),
    			'status'            => __( 'Status of Cart', 'woocommerce-ac' ),
    			'wcap_actions' 		=> __( 'More info', 'woocommerce-ac' )
        	);
        }
    	return apply_filters( 'wcap_abandoned_orders_columns', $columns );
	}

	/**
	 * It is used to add the check box for the items
	 *
	 * @param string $item 
	 * @return string 
	 * @since 2.0
	 */
	function column_cb( $item ) {
	    $abandoned_order_id = '';
	    if( isset( $item->id ) && "" != $item->id ) {
	       $abandoned_order_id = $item->id;
 	       return sprintf(
	           '<input type="checkbox" class = "abandoned_order_id" name="%1$s[]" value="%2$s" />',
	           'abandoned_order_id',
	           $abandoned_order_id
	       );
	    }
	}
    /**
	 * We can mention on which column we need the sorting. Here we are sorting abandoned cart date & abandoned cart status.
	 *
	 * @return array $columns Name of the column
	 * @since  2.0
	 */
	public function get_sortable_columns() {
		$columns = array(
				'date' 	 => array( 'date', false ),
				'status' => array( 'status',false),
		);
		return apply_filters( 'wcap_abandoned_orders_sortable_columns', $columns );
	}

	/**
	 * This function used for deleting individual row of abandoned Cart. Render the Email Column. So we will add the action on the hover affect. 
	 *
	 * @param  array $abandoned_row_info Contains all the data of the abandoned order tabs row
	 * @return string Data shown in the Email column
	 * @since  2.0
	 */
	public function column_email( $abandoned_row_info ) {
	    $actions            = array();
	    $value              = '';
	    $abandoned_order_id = 0;
	    if( isset( $abandoned_row_info->email ) ) {
    	    $abandoned_order_id = $abandoned_row_info->id ;
    	    $wcap_cart_status   = strip_tags($abandoned_row_info->status );
    	    if ( $abandoned_row_info->user_id != 0 && 
    	    	 '' != $abandoned_row_info->email &&
    	    	 'User Deleted' != $abandoned_row_info->email &&
    	    	 'Unsubscribed' != $wcap_cart_status &&
    	    	 'Recovered' != $wcap_cart_status ){

    	       $actions['wcap_manual_email']   = '<a href="' . wp_nonce_url( add_query_arg( array( 'action' => 'cart_recovery', 'section' => 'emailtemplates', 'mode'     => 'wcap_manual_email', ' abandoned_order_id' => $abandoned_row_info->id ), $this->base_url ), 'abandoned_order_nonce') . '">' . __( 'Send Custom Email', 'woocommerce-ac' ) . '</a>';
    	    }
    	    $actions['trash']                  = '<a href="' . wp_nonce_url( add_query_arg( array( 'action' => 'wcap_abandoned_trash',    'abandoned_order_id' => $abandoned_row_info->id ), $this->base_url ), 'abandoned_order_nonce') . '">' . __( 'Trash', 'woocommerce-ac' ) . '</a>';
    	    $email                             = $abandoned_row_info->email;
			$actions  						   = apply_filters( 'wcap_abandoned_orders_single_column', $actions, $abandoned_row_info );
			$value                             = $email . $this->row_actions( $actions );
	    }
	    return $value;
	}
	/**
     * It will get the abandoned cart data from data base.
     *
     * @globals mixed $wpdb
     * @return int $results_count total count of Abandoned Cart data.
     * @since   2.0
     */
	public function wcap_get_total_abandoned_count() {
	    global $wpdb;
	    $results               = array();
	    $blank_cart_info       = '{"cart":[]}';
	    $blank_cart_info_guest = '[]';
	    $ac_cutoff_time  	   = get_option( 'ac_cart_abandoned_time' );
	    $cut_off_time   	   = $ac_cutoff_time * 60;
	    $current_time   	   = current_time( 'timestamp' );
	    $compare_time  		   = $current_time - $cut_off_time;
	    $ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
	    $cut_off_time_guest    = $ac_cutoff_time_guest * 60;
	    $compare_time_guest    = $current_time - $cut_off_time_guest;
	    $get_section_of_page   = Wcap_Abandoned_Orders_Table::wcap_get_current_section ();
		$wcap_class     = new Woocommerce_Abandon_Cart();
		$duration_range = "";
		if ( isset( $_POST['duration_select'] ) ) {
		    $duration_range = $_POST['duration_select'];
		}
		if( "" == $duration_range ) {
		    if ( isset( $_GET['duration_select'] ) && '' != $_GET['duration_select'] ) {
		        $duration_range = $_GET['duration_select'];
		    }
		}
		if ( isset( $_SESSION ['duration'] ) && '' != $_SESSION ['duration'] ) {
            $duration_range     = $_SESSION ['duration'];
		}

		if ( "" == $duration_range ) {
		    $duration_range = "last_seven";
		}
		$start_date_range = "";
        if ( isset( $_POST['start_date'] ) && '' != $_POST['start_date'] ){
            $start_date_range = $_POST['start_date'];
        }
        if ( isset( $_SESSION ['start_date'] ) &&  '' != $_SESSION ['start_date'] ) {
            $start_date_range = $_SESSION ['start_date'];
		}
		if ( "" == $start_date_range ) {
		   $start_date_range = $wcap_class->start_end_dates[$duration_range]['start_date'];
		}
		$end_date_range = "";
        if ( isset( $_POST['end_date'] ) && '' != $_POST['end_date'] ){
            $end_date_range = $_POST['end_date'];
        }

        if ( isset($_SESSION ['end_date'] ) && '' != $_SESSION ['end_date'] ){
            $end_date_range = $_SESSION ['end_date'];
		}

		if ( "" == $end_date_range ) {
		    $end_date_range = $wcap_class->start_end_dates[$duration_range]['end_date'];
		}

		$start_date = strtotime( $start_date_range." 00:01:01" );
		$end_date   = strtotime( $end_date_range." 23:59:59" );

		$status_filter = "( ( cart_ignored <> '1' AND recovered_cart = 0) OR ( cart_ignored = '1' AND recovered_cart > 0 ) ) ";
	    if( isset( $_POST[ 'cart_status' ] ) && '' != $_POST[ 'cart_status' ] ) {
	    	switch( $_POST[ 'cart_status' ] ) {
	    		case 'abandoned':
	    			$status_filter = "( cart_ignored = '0' AND recovered_cart = 0 ) ";
	    			break;
	    		case 'recovered':
	    			$status_filter = "( cart_ignored = '1' AND recovered_cart > 0 ) ";
	    			break;
	    		case 'unpaid':
	    			$status_filter = "( cart_ignored = '2' AND recovered_cart = 0 ) ";
					break;
				case 'received':
					$status_filter = "( cart_ignored = '3' AND recovered_cart = 0 ) ";
					break;
	    		default:
	    			$status_filter = "( ( cart_ignored <> '1' AND recovered_cart = 0) OR ( cart_ignored = '1' AND recovered_cart > 0 ) ) ";
	    			break;
	    	}
	    }
	    
	    $results_count   = array();

	    $all_query = "SELECT COUNT(id) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND $status_filter ) OR ( user_type = 'GUEST' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND $status_filter ) ORDER BY abandoned_cart_time DESC";

        $this->total_all_count = $wpdb->get_var( $all_query );
	     		
        $registered_query = "SELECT COUNT(id) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE user_type = 'REGISTERED' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time' AND wcap_trash = '' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND $status_filter ORDER BY abandoned_cart_time DESC";
        $this->total_registered_count = $wpdb->get_var( $registered_query );

        $guest_query = "SELECT COUNT(id) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE user_type = 'GUEST' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND user_id >= 63000000  AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND $status_filter ORDER BY abandoned_cart_time DESC";
        $this->total_guest_count = $wpdb->get_var( $guest_query );

        $visitor_query = "SELECT COUNT(id) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE user_type = 'GUEST' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND user_id = 0  AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND $status_filter ORDER BY abandoned_cart_time DESC";
        $this->total_visitors_count = $wpdb->get_var( $visitor_query );

        // set up the total count for the view being displayed
	    switch ( $get_section_of_page ) {
	     	case 'wcap_all_abandoned':

		        $results_count = $this->total_all_count;
	     		break;

	     	case 'wcap_all_registered':

		        $results_count = $this->total_registered_count;
	     		break;

     		case 'wcap_all_guest':

		        $results_count = $this->total_guest_count;
	     		break;

     		case 'wcap_all_visitor':

		        $results_count = $this->total_visitors_count;
	     		break;
	
			case 'wcap_all_unsubscribe_carts':

		        $query = "SELECT COUNT(id) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."`  WHERE abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND wcap_trash = '' AND unsubscribe_link = '1' ORDER BY abandoned_cart_time DESC";
		        $results_count = $wpdb->get_var( $query );
	     		break;
	     	default:
	     		
	     		$results_count = 0;
	     		break;
	     }
	     return $results_count;
	}
	/**
     * It will generate the abandoned cart list data.
     *
     * @globals mixed $wpdb
     * @globals mixed $woocommerce
     * @return array $return_abandoned_orders Key and value of all the columns
     * @since 2.0
     */
	public function wcap_abandoned_cart_data() {
	    global $wpdb, $woocommerce;
		$return_abandoned_orders = array();
		$per_page                = $this->per_page;
		$results                 = array();
		$blank_cart_info         = '{"cart":[]}';
		$blank_cart_info_guest   = '[]';

		if( isset( $_GET['paged'] ) && $_GET['paged'] > 1 ) {
		    $page_number = $_GET['paged'] - 1;
		    $start_limit = ( $per_page * $page_number );
		    $end_limit   =  $per_page;
		    $limit       = 'limit' .' '.$start_limit . ','. $end_limit;
		} else {
		    $start_limit = 0;
		    $end_limit   = $per_page;
		    $limit       = 'limit' .' '.$start_limit . ','. $end_limit;
		}
		$get_section_of_page   = Wcap_Abandoned_Orders_Table::wcap_get_current_section ();
	    $wcap_class     = new Woocommerce_Abandon_Cart();
		$duration_range = "";
		if ( isset( $_POST['duration_select'] ) ) {
		    $duration_range = $_POST['duration_select'];
		}
		if( "" == $duration_range ) {
		    if ( isset( $_GET['duration_select'] ) && '' != $_GET['duration_select'] ) {
		        $duration_range = $_GET['duration_select'];
		    }
		}
		if ( isset( $_SESSION ['duration'] ) && '' != $_SESSION ['duration'] ) {
            $duration_range     = $_SESSION ['duration'];
		}

		if ( "" == $duration_range ) {
		    $duration_range = "last_seven";
		}
		$start_date_range = "";
        if ( isset( $_POST['start_date'] ) && '' != $_POST['start_date'] ){
            $start_date_range = $_POST['start_date'];
        }
        if ( isset( $_SESSION ['start_date'] ) &&  '' != $_SESSION ['start_date'] ) {
            $start_date_range = $_SESSION ['start_date'];
		}
		if ( "" == $start_date_range ) {
		   $start_date_range = $wcap_class->start_end_dates[$duration_range]['start_date'];
		}
		$end_date_range = "";
        if ( isset( $_POST['end_date'] ) && '' != $_POST['end_date'] ){
            $end_date_range = $_POST['end_date'];
        }

        if ( isset($_SESSION ['end_date'] ) && '' != $_SESSION ['end_date'] ){
            $end_date_range = $_SESSION ['end_date'];
		}

		if ( "" == $end_date_range ) {
		    $end_date_range = $wcap_class->start_end_dates[$duration_range]['end_date'];
		}

		$start_date = strtotime( $start_date_range." 00:01:01" );
		$end_date   = strtotime( $end_date_range." 23:59:59" );

		$status_filter = "( ( wpac.cart_ignored <> '1' AND wpac.recovered_cart = 0) OR ( wpac.cart_ignored = '1' AND wpac.recovered_cart > 0 ) ) ";
	    if( isset( $_POST[ 'cart_status' ] ) && '' != $_POST[ 'cart_status' ] ) {
	    	switch( $_POST[ 'cart_status' ] ) {
	    		case 'abandoned':
	    			$status_filter = "( wpac.cart_ignored = '0' AND wpac.recovered_cart = 0 ) ";
	    			break;
	    		case 'recovered':
	    			$status_filter = "( wpac.cart_ignored = '1' AND wpac.recovered_cart > 0 ) ";
	    			break;
	    		case 'unpaid':
	    			$status_filter = "( wpac.cart_ignored = '2' AND wpac.recovered_cart = 0 ) ";
					break;
				case 'received':
					$status_filter = "( wpac.cart_ignored = '3' AND wpac.recovered_cart = 0 ) ";
					break;
	    		default:
	    			$status_filter = "( ( wpac.cart_ignored <> '1' AND wpac.recovered_cart = 0) OR ( wpac.cart_ignored = '1' AND wpac.recovered_cart > 0 ) ) ";
	    			break;
	    	}
	    }

	    $results 	= array();

	    switch ( $get_section_of_page ) {
	     	case 'wcap_all_abandoned':
	     		# code...
	     		if( is_multisite() ) {
				    // get main site's table prefix
				    $main_prefix = $wpdb->get_blog_prefix(1);
				    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` AS wpac LEFT JOIN ".$main_prefix."users AS wpu ON wpac.user_id = wpu.id WHERE wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND $status_filter AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.wcap_trash = '' ORDER BY wpac.abandoned_cart_time DESC $limit";
				    $results = $wpdb->get_results($query);
				} else {
				    // non-multisite - regular table name
				    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` AS wpac LEFT JOIN ".$wpdb->prefix."users AS wpu ON wpac.user_id = wpu.id WHERE wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND $status_filter AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.wcap_trash = '' ORDER BY wpac.abandoned_cart_time DESC $limit";
				    $results = $wpdb->get_results($query);
				}
				// get the total recovered carts list
				$recovered_list = $wpdb->get_col( "SELECT recovered_cart FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE recovered_cart > 0 AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_time >= '$start_date' AND abandoned_cart_time <= '$end_date' AND wcap_trash = ''" ); //wpcs:ignore
	     		break;

	     	case 'wcap_all_registered':
	     		# code...
	     		if( is_multisite() ) {
				    // get main site's table prefix
				    $main_prefix = $wpdb->get_blog_prefix(1);
				    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` AS wpac LEFT JOIN ".$main_prefix."users AS wpu ON wpac.user_id = wpu.id
  				                  WHERE wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND $status_filter AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.wcap_trash = '' ORDER BY wpac.abandoned_cart_time DESC $limit";
				    $results = $wpdb->get_results($query);
				} else {
				    // non-multisite - regular table name
				    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` AS wpac LEFT JOIN ".$wpdb->prefix."users AS wpu ON wpac.user_id = wpu.id
  				                  WHERE wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND $status_filter AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wpac.user_type = 'REGISTERED' AND  wpac.wcap_trash = '' ORDER BY wpac.abandoned_cart_time DESC $limit";
				    $results = $wpdb->get_results($query);
				}
				$recovered_list = $wpdb->get_col( $wpdb->prepare( "SELECT recovered_cart FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE recovered_cart > 0 AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' AND user_type = %s AND abandoned_cart_time >= '$start_date' AND abandoned_cart_time <= '$end_date'", 'REGISTERED' ) ); //wpcs:ignore
	     		break;

     		case 'wcap_all_guest':
     		# code...
	     		if( is_multisite() ) {
				    // get main site's table prefix
				    $main_prefix = $wpdb->get_blog_prefix(1);
				    $query = "SELECT wpac . * FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` AS wpac WHERE wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND $status_filter AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.wcap_trash = '' AND wpac.user_id >= 63000000  AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' ORDER BY wpac.abandoned_cart_time DESC $limit";
				    $results = $wpdb->get_results($query);
				} else {
				    // non-multisite - regular table name
				    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` AS wpac LEFT JOIN ".$wpdb->prefix."users AS wpu ON wpac.user_id = wpu.id WHERE wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND $status_filter AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.wcap_trash = '' AND wpac.user_id >= 63000000 AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' ORDER BY wpac.abandoned_cart_time DESC $limit";
				    $results = $wpdb->get_results($query);
				}
				$recovered_list = $wpdb->get_col( $wpdb->prepare( "SELECT recovered_cart FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE recovered_cart > 0 AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' AND abandoned_cart_time >= '$start_date' AND abandoned_cart_time <= '$end_date' AND user_type = %s AND user_id >= 63000000", 'GUEST' ) ); //wpcs:ignore
	     		break;

	     		case 'wcap_all_visitor':
     			# code...
	     		if( is_multisite() ) {
				    // get main site's table prefix
				    $main_prefix = $wpdb->get_blog_prefix(1);
				    $query = "SELECT wpac.* FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` AS wpac WHERE wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND $status_filter AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.wcap_trash = '' AND wpac.user_id = 0 AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' ORDER BY wpac.abandoned_cart_time DESC $limit";
				    $results = $wpdb->get_results($query);
				} else {
				    // non-multisite - regular table name
				    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` AS wpac LEFT JOIN ".$wpdb->prefix."users AS wpu ON wpac.user_id = wpu.id WHERE wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND $status_filter AND wpac.abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wpac.wcap_trash = '' AND wpac.user_id = 0 AND wpac.abandoned_cart_info NOT LIKE '%$blank_cart_info%' ORDER BY wpac.abandoned_cart_time DESC $limit";
				    $results = $wpdb->get_results($query);
				}
				$recovered_list = $wpdb->get_col( $wpdb->prepare( "SELECT recovered_cart FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE recovered_cart > 0 AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND wcap_trash = '' AND abandoned_cart_time >= '$start_date' AND abandoned_cart_time <= '$end_date' AND user_type = %s AND user_id = 0", 'GUEST' ) ); //wpcs:ignore
	     		break;

	     		case 'wcap_all_unsubscribe_carts':
     			# code...
	     		if( is_multisite() ) {
				    // get main site's table prefix
				    $main_prefix = $wpdb->get_blog_prefix(1);
				    $query = "SELECT wpac.* FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` AS wpac WHERE abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_time <= '$compare_time_guest' AND wcap_trash = '' AND unsubscribe_link = '1' ORDER BY wpac.abandoned_cart_time DESC $limit";

				    $results = $wpdb->get_results($query);
				} else {
				    // non-multisite - regular table name
				    $query = "SELECT wpac . * , wpu.user_login, wpu.user_email FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` AS wpac LEFT JOIN ".$wpdb->prefix."users AS wpu ON wpac.user_id = wpu.id WHERE wpac.abandoned_cart_time >=  $start_date AND wpac.abandoned_cart_time <= $end_date AND wpac.wcap_trash = '' AND wpac.unsubscribe_link = '1' ORDER BY wpac.abandoned_cart_time DESC $limit";
				    $results = $wpdb->get_results($query);
				}
	     		break;

	     	default:
	     		# code...
	     		break;
	     }

		if ( isset( $recovered_list ) && is_array( $recovered_list ) && count( $recovered_list ) > 0 ) {
			$recovered_total = 0;
			foreach ( $recovered_list as $order_id ) {
				$recovered_total += get_post_meta( $order_id, '_order_total', true );
			}
			self::$recovered_count = count( $recovered_list );
			self::$recovered_amount = $recovered_total;
		} else {
			self::$recovered_count = 0;
			self::$recovered_amount = 0;
		}
		
		$i = 0;
		$display_tracked_coupons   = get_option( 'ac_track_coupons' );
		$wp_date_format            = get_option( 'date_format' );
        $wp_time_format            = get_option( 'time_format' );
     	$guest_ac_cutoff_time      = get_option( 'ac_cart_abandoned_time_guest' );
		$ac_cutoff_time            = get_option( 'ac_cart_abandoned_time' );
		$current_time              = current_time( 'timestamp' );
       	$wcap_include_tax          = get_option( 'woocommerce_prices_include_tax' );
        $wcap_include_tax_setting  = get_option( 'woocommerce_calc_taxes' );

		foreach( $results as $key => $value ) {
		    if( $value->user_type == "GUEST" ) {
		        $query_guest   = "SELECT * from `" . WCAP_GUEST_CART_HISTORY_TABLE . "` WHERE id = %d";
		        $results_guest = $wpdb->get_results( $wpdb->prepare( $query_guest, $value->user_id ) );
		    }
		    $abandoned_order_id = $value->id;
		    $user_id            = $value->user_id;
		    $user_first_name    = '';
			$user_last_name     = '';
			$user_email         = '';

		    if( $value->user_type == "GUEST" ) {
    		    if( isset( $results_guest[0]->email_id ) ) {
		            $user_email = $results_guest[0]->email_id;
		        } elseif ( $value->user_id == "0" ) {
		            $user_email = '';
		        } else {
		            $user_email = '';
	            }
		        if ( isset( $results_guest[0]->billing_first_name ) ) {
		            $user_first_name = $results_guest[0]->billing_first_name;
		        } else if( $value->user_id == "0" ) {
		            $user_first_name = "Visitor";
		        } else {
		            $user_first_name = "";
		        }
		        if( isset( $results_guest[0]->billing_last_name ) ) {
		            $user_last_name = $results_guest[0]->billing_last_name;
		        } else if( $value->user_id == "0" ) {
		            $user_last_name = "";
		        } else {
		            $user_last_name = "";
		        }
		        if( isset( $results_guest[0]->phone ) ) {
		            $phone = $results_guest[0]->phone;
		        } elseif ( $value->user_id == "0" ) {
		            $phone = '';
		        } else {
		            $phone = '';
		        }
		    } else {
		        $user_email_biiling = get_user_meta( $user_id, 'billing_email', true );
		        $user_email = __( "User Deleted" , "woocommerce-ac" );
		        if( isset( $user_email_biiling ) && "" == $user_email_biiling ) {
		            $user_data  = get_userdata( $user_id );
		            if( isset( $user_data->user_email ) && "" != $user_data->user_email ) {
		            	$user_email = $user_data->user_email;
		        	} 
		        } else if ( '' != $user_email_biiling ) {
		            $user_email = $user_email_biiling;
		        } 
		        $user_first_name_temp = get_user_meta( $user_id, 'billing_first_name', true );
		        if( isset( $user_first_name_temp ) && "" == $user_first_name_temp ) {
		            $user_data  = get_userdata( $user_id );
		            if( isset( $user_data->first_name ) && "" != $user_data->first_name ) {
		            	$user_first_name = $user_data->first_name;
		            }
		        } else {
		            $user_first_name = $user_first_name_temp;
		        }

		        $user_last_name_temp = get_user_meta( $user_id, 'billing_last_name', true );
		        if( isset( $user_last_name_temp ) && "" == $user_last_name_temp ) {
		            $user_data  = get_userdata( $user_id );
		            if( isset( $user_data->last_name ) && "" != $user_data->last_name ) {
		            	$user_last_name = $user_data->last_name;
		            }
		        } else {
		            $user_last_name = $user_last_name_temp;
		        }

		        $user_phone_number = get_user_meta( $value->user_id, 'billing_phone' );
		        if( isset( $user_phone_number[0] ) ) {
		            $phone = $user_phone_number[0];
		        } else {
		            $phone = "";
		        }
		    }
		    $cart_info        = json_decode( stripslashes( $value->abandoned_cart_info ) );
		    $order_date       = "";
		    $cart_update_time = $value->abandoned_cart_time;

		    if( $cart_update_time != "" && $cart_update_time != 0 ) {
		    	$date_format = date_i18n( $wp_date_format, $cart_update_time );
                $time_format = date_i18n( $wp_time_format, $cart_update_time );
                $order_date  = $date_format . ' ' . $time_format;
		    }

		    if( "GUEST" == $value->user_type ) {
                $ac_cutoff_time = $guest_ac_cutoff_time;
		    }
		    $cut_off_time   = $ac_cutoff_time * 60;
		    $compare_time   = $current_time - $cart_update_time;
		    $cart_details   = new stdClass();
		    $line_total     = 0;
		    $cart_total     = $item_subtotal = $item_total = $line_subtotal_tax_display =  $after_item_subtotal = $after_item_subtotal_display = 0;
            $line_subtotal_tax = 0;

		    if( isset( $cart_info->cart ) ) {
		        $cart_details = $cart_info->cart;
		    }
		    $quantity_total = 0;
		    $currency = isset( $cart_info->currency ) ? $cart_info->currency : '';
		    if( gettype( $cart_details ) !== 'array' && count( get_object_vars( $cart_details ) ) > 0 ) {

		    //$currency = isset( $cart_info->currency ) ? $cart_info->currency : '';

    	        foreach( $cart_details as $k => $v ) {
    	        	if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {
                      $wcap_product   = wc_get_product($v->product_id );
                      $product        = wc_get_product($v->product_id );
                    }else {
                        $product      = get_product( $v->product_id );
                        $wcap_product = get_product($v->product_id );
                    }
                    if ( false !== $product ) {
	    	            if( isset($wcap_include_tax) && $wcap_include_tax == 'no' &&
	                        isset($wcap_include_tax_setting) && $wcap_include_tax_setting == 'yes' ) {
	                        $line_total        += isset( $v->line_total ) ? $v->line_total : 0;
	                        $line_subtotal_tax += isset( $v->line_tax ) ? $v->line_tax : 0; // This is fix

	                    }else if ( isset($wcap_include_tax) && $wcap_include_tax == 'yes' &&
	                        isset($wcap_include_tax_setting) && $wcap_include_tax_setting == 'yes' ) {
	                        // Item subtotal is calculated as product total including taxes
	                        if( $v->line_tax != 0 && $v->line_tax > 0 ) {
								$line_subtotal_tax_display += $v->line_tax;
	                            /* After copon code price */
	                            $after_item_subtotal = $item_subtotal + $v->line_total + $v->line_tax;
								/*Calculate the product price*/
								$item_subtotal = $item_subtotal + $v->line_subtotal + $v->line_subtotal_tax;
								$line_total    = $line_total +   $v->line_subtotal + $v->line_subtotal_tax;
	                        } else {
	                            $item_subtotal = $item_subtotal + $v->line_total;
	                            $line_total    = $line_total +  $v->line_total;
	                            $line_subtotal_tax_display += $v->line_tax;
	                        }
	                    }else{
	                    	$line_total = $line_total + $v->line_total;
	                    }
	                    $quantity_total = $quantity_total + $v->quantity;
	    	        }
    	    	}
		    }

		    $wcap_check_order_total = $line_total;
		    $wcap_item_total_with_tax = $line_total + $line_subtotal_tax;
		    $line_total     		= apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $line_total, $currency ), $abandoned_order_id, $line_total, 'wcap_order_page' );
			$show_taxes = apply_filters('wcap_show_taxes', true);
		    if( $show_taxes && isset( $wcap_include_tax ) && $wcap_include_tax == 'no' && isset( $wcap_include_tax_setting ) && $wcap_include_tax_setting == 'yes' ) {
		    	$wcap_item_total_tax_display     = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $wcap_item_total_with_tax, $currency ), $abandoned_order_id, $wcap_item_total_with_tax, 'wcap_order_page' );
		    	$line_total = $wcap_item_total_tax_display . '<br> ('. __( "incl. tax", "woocommerce-ac" ) . ')';
            }else if( isset( $wcap_include_tax ) && $wcap_include_tax == 'yes' && isset( $wcap_include_tax_setting ) && $wcap_include_tax_setting == 'yes' ) {
            	$line_subtotal_tax_display     = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $line_subtotal_tax_display, $currency ), $abandoned_order_id, $line_subtotal_tax_display, 'wcap_order_page' );
                if ($show_taxes) {
                	$line_total = $line_total . ' (' . __( "includes Tax: " , "woocommerce-ac" ) . $line_subtotal_tax_display . ')';
            	} 
            	else {
            		$line_total = $line_total;
            	}
            }
		    
		    if( 1 == $quantity_total ) {
		        $item_disp = __( "item", "woocommerce-ac" );
		    } else {
		        $item_disp = __( "items", "woocommerce-ac" );
		    }
		    $coupon_details          = get_user_meta( $value->user_id, '_woocommerce_ac_coupon', true );
		    $coupon_detail_post_meta = get_post_meta( $value->id, '_woocommerce_ac_coupon');
		    if( $value->unsubscribe_link == 1 ) {
		        $ac_status = __( "Unsubscribed", "woocommerce-ac" );
		        $ac_status = "<span id ='wcap_unsubscribe_link' class = 'unsubscribe_link'  >" . $ac_status . "</span>";
		    } elseif( $value->cart_ignored == 0 && $value->recovered_cart == 0 ) {
		        $ac_status = __( "Abandoned", "woocommerce-ac" );
		        $ac_status = "<span id ='wcap_abandoned_new' class = 'wcap_abandoned_new'  >" . $ac_status . "</span>";
		    } elseif ( $value->cart_ignored == 1 && $value->recovered_cart == 0 ) {
		        $ac_status = __( "Abandoned but <br> new cart created after this", "woocommerce-ac" );
		        $ac_status = "<span id ='wcap_abandoned_new' class = 'wcap_abandoned_new'  >" . $ac_status . "</span>";
		    } elseif ( $value->cart_ignored == 2 && $value->recovered_cart == 0 ) {
		        $ac_status = __( "Abandoned - Order Unpaid", "woocommerce-ac" );
		        $ac_status = "<span id ='wcap_abandoned_unpaid' class = 'wcap_abandoned_unpaid'  >" . $ac_status . "</span>";
		    } elseif ( $value->cart_ignored == 1 && $value->recovered_cart > 0 ) {
		        $ac_status = __( "Recovered", "woocommerce-ac" );
		        $ac_status = "<span id ='wcap_abandoned' class = 'wcap_abandoned'  >" . $ac_status . "</span>";
		    } elseif ( $value->cart_ignored == 3 && $value->recovered_cart == 0 ) {
		        $ac_status = __( "Abandoned - Order Received", "woocommerce-ac" );
		        $ac_status = "<span id ='wcap_abandoned_received' class = 'wcap_abandoned_received'  >" . $ac_status . "</span>";
		    } else {
		    	$ac_status = "";
		    }

		    $coupon_code_used = $coupon_code_message = "";
		    if ( $compare_time > $cut_off_time && $ac_status != "" ) {
		        $return_abandoned_orders[$i] 				 = new stdClass();
		        $customer_information                        = $user_first_name . " ".$user_last_name;
                $return_abandoned_orders[ $i ]->id           = $abandoned_order_id;
                $return_abandoned_orders[ $i ]->email        = $user_email;
                if( $phone == '' ) {
                    $return_abandoned_orders[ $i ]->customer = $customer_information;
                } else {
                    $return_abandoned_orders[ $i ]->customer = $customer_information . "<br>" . $phone;
                }
                $return_abandoned_orders[ $i ]->check_cart_total = $wcap_check_order_total;
                $return_abandoned_orders[ $i ]->user_id      = $user_id;
                $return_abandoned_orders[ $i ]->date         = $order_date;
                $return_abandoned_orders[ $i ]->status       = $ac_status;

                if ( isset( $cart_info->wcap_user_ref ) && $cart_info->wcap_user_ref != '' ) {
                	$return_abandoned_orders[ $i ]->fb_consent = 'yes';
                }

                if( $quantity_total > 0 ) {
                    $return_abandoned_orders[ $i ]->order_total = $line_total . "<br>" . $quantity_total . " " . $item_disp;
                    $return_abandoned_orders[ $i ]->quantity    = $quantity_total . " " . $item_disp;
                    if( $coupon_detail_post_meta != '' ) {
                        foreach( $coupon_detail_post_meta as $key => $value ) {
                           if( isset( $coupon_detail_post_meta[$key]['coupon_code'] ) && $coupon_detail_post_meta[$key]['coupon_code'] != '' ) {
                                $coupon_code_used .= $coupon_detail_post_meta[$key]['coupon_code'] . "</br>";
                           }
                        }
                        $return_abandoned_orders[ $i ]->coupon_code_used = $coupon_code_used;
                    }
                    if( $coupon_detail_post_meta != '' && $coupon_code_used !== '' ) {
                        foreach( $coupon_detail_post_meta as $key => $value ) {
                            $coupon_code_message .= $coupon_detail_post_meta[$key]['coupon_message'] . "</br>";
                        }
                        $return_abandoned_orders[ $i ]->coupon_code_status = $coupon_code_message;
                    }
            	}
                $i++;
            }
		}
		// sort for order date
    	 if( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'date' ) {
    	     if( isset( $_GET['order'] ) && $_GET['order'] == 'asc' ) {
    	         usort( $return_abandoned_orders, array( __CLASS__ , "wcap_class_order_date_asc" ) );
    	     } else {
    	         usort( $return_abandoned_orders, array( __CLASS__ , "wcap_class_order_date_dsc" ) );
    	     }
    	 } else if( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'status' ) { // sort for customer name
    		if ( isset( $_GET['order'] ) && $_GET['order'] == 'asc' ) {
    			usort( $return_abandoned_orders, array( __CLASS__ , "wcap_class_status_asc" ) );
    		} else {
    			usort( $return_abandoned_orders, array( __CLASS__ , "wcap_class_status_dsc" ) );
    		}
    	}
    	return apply_filters( 'wcap_abandoned_orders_table_data', $return_abandoned_orders );
    }
	/**
	 * It will sort the ascending data based on the abandoned cart date.
	 *
	 * @param array | object $value1 All data of the list
	 * @param array | object $value2 All data of the list
	 * @return timestamp  
	 * @since  3.4
	 */
    function wcap_class_order_date_asc( $value1,$value2 ) {
	   if( isset( $value1->date ) && isset( $value2->date ) ) {
    	    $date_two           = $date_one = '';
    	    $value_one          = $value1->date;
    	    $value_two          = $value2->date;
    	    $date_formatted_one = date_create_from_format( 'd M, Y h:i A', $value_one );
    	    $date_formatted_two = date_create_from_format( 'd M, Y h:i A', $value_two );
    	    if( isset( $date_formatted_one ) && $date_formatted_one != '' ) {
    	        $date_one = date_format( $date_formatted_one, 'Y-m-d h:i A' );
    	    }
    	    if( isset( $date_formatted_two ) && $date_formatted_two != '' ) {
    	        $date_two = date_format( $date_formatted_two, 'Y-m-d h:i A' );
    	    }
    	    return strtotime($date_one) - strtotime($date_two);
	    } else {
	        return 1;
	    }
	}

	/**
	 * It will sort the descending data based on the abandoned cart date.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return timestamp  
	 * @since   3.4
	 */
	function wcap_class_order_date_dsc( $value1,$value2 ) {
	   if( isset( $value1->date ) && isset( $value2->date ) ) {
    	    $date_two            = $date_one = '';
    	    $value_one           = $value1->date;
    	    $value_two           = $value2->date;
    	    $date_formatted_one  = date_create_from_format( 'd M, Y h:i A', $value_one );
    	    $date_formatted_two  = date_create_from_format( 'd M, Y h:i A', $value_two );
    	    if( isset( $date_formatted_one ) && $date_formatted_one != '' ) {
    	        $date_one = date_format( $date_formatted_one, 'Y-m-d h:i A' );
    	    }
    	    if( isset( $date_formatted_two ) && $date_formatted_two != '' ) {
    	        $date_two = date_format( $date_formatted_two, 'Y-m-d h:i A' );
    	    }
    	    return strtotime( $date_two ) - strtotime( $date_one );
	    } else {
	        return 1;
	    };
	}
	/**
	 * It will sort the alphabetically ascending on the abandoned cart staus.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 */
	function wcap_class_status_asc( $value1,$value2 ) {
	    return strcasecmp( $value1->status,$value2->status );
	}

	/**
	 * It will sort the alphabetically descending on the abandoned cart status.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 */
	function wcap_class_status_dsc ( $value1,$value2 ) {
	    return strcasecmp( $value2->status,$value1->status );
	}
	/**
	 * It will display the data for the abanodned column.
	 *
	 * @param  array | object $wcap_abandoned_orders All data of the list
	 * @param  stirng $column_name Name of the column
	 * @return string $value Data of the column
	 * @since  3.4
	 */
	public function column_default( $wcap_abandoned_orders, $column_name ) {
	    $value = '';
	    switch ( $column_name ) {
	        case 'id' :
			    if( isset( $wcap_abandoned_orders->id ) ) {
			    	$wcap_array = array(
									'action' => 'wcap_abandoned_cart_info',
									'cart_id' => $wcap_abandoned_orders->id
					    		);
	            	$wcap_url   = add_query_arg(
	            					$wcap_array , admin_url( 'admin-ajax.php' )
            					);
					$value =  '<strong> <a class="wcap-js-open-modal" data-wcap-cart-id="'.$wcap_abandoned_orders->id.'" data-modal-type="ajax" href="' . $wcap_url . '">'.$wcap_abandoned_orders->id.'</a> </strong>' ;
			    }
				break;
			case 'customer' :
			    if( isset( $wcap_abandoned_orders->customer ) ) {
			        $user_role = '';
			        if ( $wcap_abandoned_orders->user_id == 0 ) {
			            $user_role = 'Guest';
			        }
			        elseif ( $wcap_abandoned_orders->user_id >= 63000000 ) {
			            $user_role = 'Guest';
			        }else{
			            $user_role = Wcap_Common::wcap_get_user_role ( $wcap_abandoned_orders->user_id );
			        }
			        $fb_image = '';
			        if ( isset( $wcap_abandoned_orders->fb_consent ) && $wcap_abandoned_orders->fb_consent == 'yes' ) {
			        	$fb_image = '<div class="clear"></div>
			        				 <img src="' . WCAP_PLUGIN_URL . "/assets/images/fb-messenger.png" . '" width="15" title="' . __( 'Facebook Messenger consent given', 'woocommerce-ac' ) . '">';
			        }
			        $value = $wcap_abandoned_orders->customer . "<br>" . $user_role . $fb_image;
			    }
				break;
			case 'order_total' :
			    if( isset( $wcap_abandoned_orders->order_total ) ) {
			       $value = $wcap_abandoned_orders->order_total;
			    }
				break;
			case 'date' :
			    if( isset( $wcap_abandoned_orders->date ) ) {
	 			   $value = $wcap_abandoned_orders->date;
			    }
				break;
			case 'status' :
			    if( isset( $wcap_abandoned_orders->status ) ) {
					$value = $wcap_abandoned_orders->status;
			    }
			    break;
		    case 'coupon_code_used' :
		        if( isset( $wcap_abandoned_orders->coupon_code_used ) ) {
		            $value = $wcap_abandoned_orders->coupon_code_used;
		        }
		        break;
	        case 'coupon_code_status' :
	            if( isset( $wcap_abandoned_orders->coupon_code_status ) ) {
	                $value = $wcap_abandoned_orders->coupon_code_status;
	            }
	            break;
            case 'wcap_actions' :
	            if( isset( $wcap_abandoned_orders->id ) ) {
	            	$wcap_array = array(
								'action' => 'wcap_abandoned_cart_info',
								'cart_id' => $wcap_abandoned_orders->id
		    				);
	            	$wcap_url 	= add_query_arg(
            					$wcap_array , admin_url( 'admin-ajax.php' )
							);

					$value =  '<a oncontextmenu="return false;" class="button tips view wcap-button-icon wcap-js-open-modal" data-tip= "View" data-modal-type="ajax" data-wcap-cart-id="'.$wcap_abandoned_orders->id.'" href="' . $wcap_url . '">View</a>';
	            }
	            break;
            case 'email_captured_by':
            	if( isset( $wcap_abandoned_orders->id ) && isset( $wcap_abandoned_orders->user_id ) ) {
            		$value = '';
            		if ( $wcap_abandoned_orders->user_id > 0 ) {
	            		$wcap_cart_popup_data = get_post_meta ( $wcap_abandoned_orders->id, 'wcap_atc_report' );
	            		if ( count( $wcap_cart_popup_data ) > 0 ) {
	            			$wcap_user_selected_action = $wcap_cart_popup_data[0]['wcap_atc_action'];
	            			if ( 'yes' == $wcap_user_selected_action ) {
	            				$value = __( 'Cart Popup', 'woocommerce-ac' );
	            			}else if ( 'no' == $wcap_user_selected_action ) {
	            				$value = __( 'Checkout page', 'woocommerce-ac' );
	            			}
	            		}else{
	            			if ( $wcap_abandoned_orders->user_id >= 63000000 ) {
	            				$value = __( 'Checkout Page', 'woocommerce-ac' );
	            			}else if ( $wcap_abandoned_orders->user_id > 0 &&  $wcap_abandoned_orders->user_id < 63000000 ) {
	            				$value = __( 'User Profile', 'woocommerce-ac' );
	            			}
	            		}
            		}
            	}
			break;

			default:
				$value = isset( $wcap_abandoned_orders->$column_name ) ? $wcap_abandoned_orders->$column_name : '';
				break;
	    }
		return apply_filters( 'wcap_abandoned_orders_column_default', $value, $wcap_abandoned_orders, $column_name );
	}
	/**
	 * It will add the bulk action for move to trash and Send Custom email.
	 *
	 * @return array $wcap_abandoned_bulk_actions bulk action
	 * @since 2.4.7
	 */
	public function get_bulk_actions() {
		$wcap_abandoned_bulk_actions = array(
			'emailtemplates&mode=wcap_manual_email' => __( 'Send Custom Email', 'woocommerce-ac' ),
			'wcap_abandoned_trash'                  => __( 'Move to Trash', 'woocommerce-ac' ),
			'wcap_abandoned_trash_visitor'          => __( 'Move all Visitor carts to Trash', 'woocommerce-ac' ),
			'wcap_abandoned_trash_guest'            => __( 'Move all Guest User carts to Trash', 'woocommerce-ac' ),
			'wcap_abandoned_trash_registered'       => __( 'Move all Registered User carts to Trash', 'woocommerce-ac' ),
			'wcap_abandoned_trash_all'              => __( 'Move all carts to Trash', 'woocommerce-ac' )
	    );
	    $wcap_abandoned_bulk_actions = apply_filters ( 'wcap_abandoned_order_add_bulk_action', $wcap_abandoned_bulk_actions );
	    return $wcap_abandoned_bulk_actions;
	}
	/**
	 * It will give the section name.
	 *
	 * @return string $section Name of the current section
	 * @since  2.4.7
	 */
	public function wcap_get_current_section() {
		$section = 'wcap_all_abandoned';
		if ( isset( $_GET[ 'wcap_section' ] ) ) {
			$section = $_GET[ 'wcap_section' ];
		}
		return $section;
	}
}
?>
