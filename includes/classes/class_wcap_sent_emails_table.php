<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * It will show records of the Abandoned cart reminder email which is sent to customers on Sent Email tab.
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @category Classes
 * @since    5.0
 */
// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * Show abandoned cart reminder emails record on Send Emails tab.
 * 
 * @since 2.4.7
 */
class Wcap_Sent_Emails_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 2.5
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 2.5
	 */
	public $base_url;

	/**
	 * Total number of Sent Emails
	 *
	 * @var int
	 * @since 2.5
	 */
	public $total_count;

	/**
	 * Total number of Open Emails
	 *
	 * @var int
	 * @since 2.5
	 */
	public $open_emails;

	/**
	 * Total amount of Links clicked
	 *
	 * @var int
	 * @since 2.5
	 */
	public $link_click_count;

	/**
	 * Start date
	 *
	 * @var int
	 * @since 2.5
	 */
	public $start_date_db;

	/**
	 * End date
	 *
	 * @var int
	 * @since 2.5
	 */
	public $end_date_db;

	/**
	 * Duration
	 *
	 * @var int
	 * @since 2.5
	 */
	public $duration;

   	/**
	 * It will add the bulk action and other variable needed for the class.
	 *
	 * @since 2.5
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;
		// Set parent defaults
		parent::__construct( array(
	        'singular' => __( 'sent_email_id', 'woocommerce-ac' ), //singular name of the listed records
	        'plural'   => __( 'sent_email_ids', 'woocommerce-ac' ), //plural name of the listed records
			'ajax'      => true             			// Does this table support ajax?
		) );

		$this->base_url = admin_url( 'admin.php?page=woocommerce_ac_page&action=stats' );
	}
	/**
	 * It will prepare the list of the User Email Address, columns, pagination, sortable column and other data.
	 *
	 * @since 2.5
	 */
	public function wcap_sent_emails_prepare_items() {

		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$data                  = $this->wcap_sent_emails_data();
		$total_items           = $this->total_count;
 		$open_emails           = $this->open_emails;
 		$link_click_count      = $this->link_click_count;
 		$end_date_db           = $this->end_date_db;
 		$start_date_db         = $this->start_date_db;
        $duration              = $this->duration;
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
	 * It will add the columns for Sent Emails Tab.
	 *
	 * @return array $columns All columns name.
	 * @since  2.5
	 */
	public function get_columns() {

	    $columns = array(
	    	'cb'                => '<input type="checkbox" />',
	        'user_email_id'     => __( 'User Email Address'       , 'woocommerce-ac' ),
            'sent_time'         => __( 'Email Sent Time'          , 'woocommerce-ac' ),
	        'date_time_opened'  => __( 'Date / Time  Email Opened', 'woocommerce-ac' ),
			'link_clicked'  	=> __( 'Link Clicked'             , 'woocommerce-ac' ),
            'template_name'     => __( 'Sent Email Template'      , 'woocommerce-ac'),
	    );
		return apply_filters( 'wcap_sent_emails_columns', $columns );
	}

	/**
	 * It is used to add the check box for the items
	 *
	 * @param string $item 
	 * @return string 
	 * @since 4.3
	 */
	function column_cb( $item ) {
	    $wcap_email_sent_id = '';
	    if( isset( $item->email_sent_id ) && "" != $item->email_sent_id ) {
	       $wcap_email_sent_id = $item->email_sent_id;
 	       return sprintf(
	           '<input type="checkbox" name="%1$s[]" value="%2$s" />',
	           'wcap_email_sent_id',
	           $wcap_email_sent_id
	       );
	    }
	}
    /**
	 * This function used for deleting individual row of abandoned Cart. Render the User Email Address. So we will add the action on the hover affect. 
	 *
	 * @access public
	 * @since 2.5
	 * @param array $abandoned_row_info Contains all the data of the template row
	 * @return string Data shown in the Email column
	 *
	 */
	public function column_user_email_id( $sent_emails_row_info ) {

	    $row_actions = array();
	    $value = '';
	    $sent_id = 0;

	    if( isset( $sent_emails_row_info->user_email_id ) ){
	        $display_link = $sent_emails_row_info->display_link ;
    	    if ( "Abandoned" == $display_link ){

    	    	$wcap_array = array(
								'action' => 'wcap_abandoned_cart_info',
								'cart_id' => $sent_emails_row_info->abandoned_order_id
					    	);
            	$wcap_url = add_query_arg(
            					$wcap_array , admin_url( 'admin-ajax.php' )
        					);

				$view_name = __( "Abandoned Order", "woocommerce-ac" );
    	        $row_actions['view_details']   = "<a class=wcap-js-open-modal data-wcap-cart-id=".$sent_emails_row_info->abandoned_order_id." data-wcap-cart-id=".$sent_emails_row_info->abandoned_order_id." data-modal-type=ajax href = $wcap_url>". $view_name ."</a>";
    	    }else{
    	        $view_link = "post.php?post=" . $sent_emails_row_info->recover_order_id . "&action=edit";
    	        $view_name = __( " Recovered Order", "woocommerce-ac" );
    	        $row_actions['view_details']   = "<a target=_blank href = $view_link>". $view_name ."</a>";
    	    }
			$user_name = $sent_emails_row_info->user_email_id;
            $value     = $user_name . $this->row_actions( $row_actions );
	    }
        return apply_filters( 'wcap_sent_emails_single_column', $value, $sent_id, 'email' );
	}
	/**
	 * If the customer click on the cart or checkout page link from email notification then we get the record of abandoned cart reminder email. 
	 *
	 * @param array $start_date_db Selected start date
	 * @param array $end_date_db Selected end date
	 * @return string $wcap_link_clicked_count Link clicked count
	 * @since 2.0
	 */
	public static function wcap_get_link_click_count ( $start_date_db, $end_date_db ) {
		global $wpdb;

		$wcap_link_clicked       = "SELECT COUNT( DISTINCT( wplc.email_sent_id ) ) FROM " . WCAP_EMAIL_CLICKED_TABLE . " as wplc 
									LEFT JOIN ".WCAP_EMAIL_SENT_HISTORY_TABLE." AS wpsh ON wplc.email_sent_id = wpsh.id 
									WHERE wplc.time_clicked >= '" . $start_date_db . "' AND 
									wplc.time_clicked <= '" . $end_date_db . "' ";
		$wcap_link_clicked_count = $wpdb->get_var( $wcap_link_clicked );

		return $wcap_link_clicked_count;
	}
	/**
	 * We get the abandoned cart reminder email opened record once the customer openes the email notification. 
	 *
	 * @param array $start_date_db Selected start date
	 * @param array $end_date_db Selected end date
	 * @return string $wcap_email_open_count Email opened count
	 * @since 2.0
	 */
	public static function wcap_get_open_count ( $start_date_db, $end_date_db ) {
		global $wpdb;
		$wcap_email_open       = "SELECT COUNT( DISTINCT( wpoe.email_sent_id ) ) FROM " . WCAP_EMAIL_OPENED_TABLE . " as wpoe 
									LEFT JOIN ".WCAP_EMAIL_SENT_HISTORY_TABLE." AS wpsh ON wpoe.email_sent_id = wpsh.id 
									WHERE time_opened >= '" . $start_date_db . "' AND time_opened <= '" . $end_date_db . "' 
									AND wpsh.id = wpoe.email_sent_id ";
		$wcap_email_open_count = $wpdb->get_var( $wcap_email_open );

		return $wcap_email_open_count;
	}
	/**
     * It will generate the sent abandoned cart reminder emails list for Sent Emails tab.
     *
     * @globals mixed $wpdb
     * @globals mixed $woocommerce
     * @return array $return_sent_email_display Key and value of all the columns
     * @since 2.0
     */
	public function wcap_sent_emails_data() {

		if( session_id() === '' ){
		    //session has not started
		    session_start();
		}
		global $wpdb;
		$wcap_class 	  = new Woocommerce_Abandon_Cart ();
		$ac_results_sent  = $ac_results_opened = array();
		$duration_range   = '';
		$wcap_date_format = get_option( 'date_format' );
		$wcap_time_format = get_option( 'time_format' );

		if ( isset( $_POST['duration_select_email'] ) && '' != $_POST['duration_select_email'] ){
		    $duration_range         = $_POST['duration_select_email'];
		    $_SESSION['duration']   = $duration_range;
		}

		if ( isset( $_SESSION ['duration'] ) && '' != $_SESSION ['duration'] ){
            $duration_range         = $_SESSION ['duration'];
		}

		if ( '' == $duration_range ) {
		    $duration_range         = "last_seven";
		    $_SESSION['duration']   = $duration_range;
		}

		$start_date_range = '';
		if ( isset( $_POST['start_date_email'] ) && '' != $_POST['start_date_email'] ) {
		    $start_date_range        = $_POST['start_date_email'];
		    $_SESSION ['start_date'] = $start_date_range;
		}

		if ( isset( $_SESSION ['start_date'] ) &&  '' != $_SESSION ['start_date'] ) {
            $start_date_range = $_SESSION ['start_date'];
		}

		if ( '' == $start_date_range ) {
		   $start_date_range = $wcap_class->start_end_dates[$duration_range]['start_date'];
		   $_SESSION ['start_date'] = $start_date_range;
		}

		$end_date_range = '';
		if ( isset( $_POST['end_date_email'] ) && '' != $_POST['end_date_email'] ){
            $end_date_range = $_POST['end_date_email'];
            $_SESSION ['end_date'] = $end_date_range;
        }

		if ( isset($_SESSION ['end_date'] ) && '' != $_SESSION ['end_date'] ){
            $end_date_range = $_SESSION ['end_date'];
		}

		if ( '' == $end_date_range ) {
		    $end_date_range = $wcap_class->start_end_dates[$duration_range]['end_date'];
		    $_SESSION ['end_date'] = $end_date_range;
		}

		$start_date    		= strtotime( $start_date_range." 00:01:01" );
		$end_date      		= strtotime( $end_date_range." 23:59:59" );
		$start_date_db 		= date( 'Y-m-d H:i:s', $start_date );
		$end_date_db   		= date( 'Y-m-d H:i:s', $end_date );

        if( version_compare( WOOCOMMERCE_VERSION, "2.3" ) < 0 ) {
		    $checkout_page_id   = get_option( 'woocommerce_checkout_page_id' );
		    
		    if( $checkout_page_id ) {
                $checkout_page      = get_post( $checkout_page_id );
                $checkout_page_link = $checkout_page->guid;
		    } else {
		        $checkout_page_link = '';
		    }
		    
		    $cart_page_id       = get_option( 'woocommerce_cart_page_id' );
		    
		    if( $cart_page_id ) {
    		    $cart_page          = get_post( $cart_page_id );
    		    $cart_page_link     = $cart_page->guid;
		    } else {
		        $cart_page_link     = '';
		    }
		    
		} else {
            $checkout_page_id   = wc_get_page_id( 'checkout' );
            $checkout_page_link = $checkout_page_id ? get_permalink( $checkout_page_id ) : '';
            
            $cart_page_id   = wc_get_page_id( 'cart' );
            $cart_page_link = $cart_page_id ? get_permalink( $cart_page_id ) : '';
            
		}
		
		$per_page        = $this->per_page;
		$offset           = 0;
		if ( isset( $_GET['paged'] ) && $_GET['paged'] > 1 ) {
			$page_number = $_GET['paged'] - 1;
			$offset           = $per_page * $page_number;
		}

		$total_query = "SELECT COUNT(wpsh.id) as count FROM " . WCAP_EMAIL_SENT_HISTORY_TABLE . " as wpsh
						WHERE wpsh.sent_time >= %s
						AND wpsh.sent_time <= %s ORDER BY wpsh.id DESC";

		$ac_get_count = $wpdb->get_results( $wpdb->prepare( $total_query, $start_date_db, $end_date_db ) );
		$this->total_count = $ac_get_count[0]->count;

		/* Now we use the LIMIT clause to grab a range of rows */
		$query_ac_sent          = "SELECT wpsh.sent_time, wpsh.template_id, wpsh.abandoned_order_id, wpsh.id, wpsh.sent_email_id, 
									wpac.manual_email, wpac.recovered_cart FROM ". WCAP_ABANDONED_CART_HISTORY_TABLE." AS wpac 
									LEFT JOIN ( SELECT * FROM " . WCAP_EMAIL_SENT_HISTORY_TABLE . " 
										ORDER BY id DESC
										LIMIT %d OFFSET %d ) as wpsh 
									ON wpsh.abandoned_order_id = wpac.id
									WHERE wpsh.abandoned_order_id = wpac.id AND wpsh.sent_time >= %s 
									AND wpsh.sent_time <= %s";
		$ac_results_sent        = $wpdb->get_results( $wpdb->prepare( $query_ac_sent, $per_page, $offset, $start_date_db, $end_date_db ) );
		
		$this->open_emails      = $this->wcap_get_open_count ( $start_date_db, $end_date_db ); //count ( $ac_results_opened );
		$this->link_click_count = $this->wcap_get_link_click_count ($start_date_db, $end_date_db); // count ( $ac_results_clicked );

		$i = 0;
		
    	foreach ( $ac_results_sent as $key => $value ) {

		    $sent_tmstmp                = strtotime( $value->sent_time );
		    $sent_date_format  			= date_i18n( $wcap_date_format, $sent_tmstmp );
            $sent_time_format   	    = date_i18n( $wcap_time_format, $sent_tmstmp );
		    $sent_date                  = $sent_date_format . ' ' . $sent_time_format;
		    $query_template_name        = "SELECT template_name FROM " . WCAP_EMAIL_TEMPLATE_TABLE . " WHERE id= %d";
		    $ac_results_template_name   = $wpdb->get_results( $wpdb->prepare( $query_template_name, $value->template_id ) );

		    $link_clicked               = '';

		    $ac_email_template_name     = '';
		    if ( isset( $ac_results_template_name[0]->template_name ) ) {
		        $ac_email_template_name = $ac_results_template_name[0]->template_name;
		    }

		    if ( isset( $value->manual_email ) && 'YES' == $value->manual_email ) {
		        $ac_email_template_name .= ' (#'. $value->template_id  .' manual )' ;
		    }

            $return_sent_emails[ $i ]     = new stdClass();

			$query_ac_clicked   = "SELECT DISTINCT wplc.email_sent_id, wplc.link_clicked FROM " . WCAP_EMAIL_CLICKED_TABLE . " as wplc 
									LEFT JOIN ".WCAP_EMAIL_SENT_HISTORY_TABLE." AS wpsh ON wplc.email_sent_id = wpsh.id 
									WHERE wplc.email_sent_id = %d
									 ORDER BY wplc.id DESC ";
			$ac_results_clicked = $wpdb->get_results( $wpdb->prepare( $query_ac_clicked, $value->id  ) ) ;

			if ( count( $ac_results_clicked ) > 0 ) {
				if( $ac_results_clicked[0]->link_clicked == $checkout_page_link ) {
	                $link_clicked   = "Checkout Page";
	            } elseif( $ac_results_clicked[0]->link_clicked == $cart_page_link ) {
	                $link_clicked   = "Cart Page";
	            }
        	}
			
			$query_ac_opened   = "SELECT DISTINCT wpoe.time_opened  FROM " . WCAP_EMAIL_OPENED_TABLE . " as 
        							wpoe LEFT JOIN ".WCAP_EMAIL_SENT_HISTORY_TABLE." AS wpsh ON wpsh.id = wpoe.email_sent_id 
									WHERE wpoe.email_sent_id = %d ";
			$ac_results_opened = $wpdb->get_results( $wpdb->prepare( $query_ac_opened, $value->id  ) );

			
		    $email_opened = "";

		    if ( count( $ac_results_opened ) >  0 ) {
		    	$opened_tmstmp 		= strtotime( $ac_results_opened[0]->time_opened );
	    		$opened_date_format = date_i18n( $wcap_date_format, $opened_tmstmp );
        		$opened_time_format = date_i18n( $wcap_time_format, $opened_tmstmp );
	            $email_opened  		=  $opened_date_format . ' ' . $opened_time_format;
		    } 
		    $recover_id 	= '';
            $view_name_flag = 'Abandoned';

		    if ( isset( $value->recovered_cart ) && $value->recovered_cart != 0 ) {
                $recover_id = $value->recovered_cart;
	            $view_name_flag = "";
		    }
		    $return_sent_emails[ $i ]->sent_time          = $sent_date ;
		    $return_sent_emails[ $i ]->user_email_id      = $value->sent_email_id;
		    $return_sent_emails[ $i ]->date_time_opened   = $email_opened;
		    $return_sent_emails[ $i ]->link_clicked       = $link_clicked;
		    $return_sent_emails[ $i ]->template_name      = $ac_email_template_name;
		    $return_sent_emails[ $i ]->display_link       = $view_name_flag;
		    $return_sent_emails[ $i ]->abandoned_order_id = $value->abandoned_order_id;
		    $return_sent_emails[ $i ]->recover_order_id   = $recover_id;
		    $return_sent_emails[ $i ]->email_sent_id      = $value->id;;

		    $i++;
		 }

		$return_sent_email_display = ( isset( $return_sent_emails ) ) ? $return_sent_emails : array();
		return apply_filters( 'wcap_sent_emails_table_data', $return_sent_email_display );
	}
	/**
	 * It will display the data for the abanodned column.
	 *
	 * @param  array | object $wcap_sent_emails All sent emails data
	 * @param  stirng $column_name Name of the column
	 * @return string $value Data of the column
	 * @since  2.0
	 */
	public function column_default( $wcap_sent_emails, $column_name ) {
	    $value = '';
	    switch ( $column_name ) {

	        default:

				$value = isset( $wcap_sent_emails->$column_name ) ? $wcap_sent_emails->$column_name : '';
				break;
	    }

		return apply_filters( 'wcap_sent_emails_column_default', $value, $wcap_sent_emails, $column_name );
	}
}
?>