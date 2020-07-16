<?php 
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * It will show list of Active and deactive email templates on Emails Templates tab.
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
 * Show Email templates list on Email Templates tab.
 * 
 * @since 2.0
 */
class Wcap_Templates_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 2.0
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 2.0
	 */
	public $base_url;

	/**
	 * Total number of templates
	 *
	 * @var int
	 * @since 2.0
	 */
	public $total_count;

    /**
	 * It will add the bulk action and other variable needed for the class.
	 *
	 * @since 2.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {

		// Set parent defaults
		parent::__construct( array(
	        'singular' => __( 'template_id', 'woocommerce-ac' ), //singular name of the listed records
	        'plural'   => __( 'template_ids', 'woocommerce-ac' ), //plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );
		$this->wcap_get_templates_count();
		$this->process_bulk_action();
        $this->base_url = admin_url( 'admin.php?page=woocommerce_ac_page&action=cart_recovery&section=emailtemplates' );
	}
	/**
	 * It will prepare the list of the Email Templates, columns, pagination, sortable column and other data.
	 *
	 * @since 2.0
	 */
	public function wcap_templates_prepare_items() {

		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->wcap_templates_get_sortable_columns();
		$data                  = $this->wcap_templates_data();		
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
	/**
	 * It will add the columns for Email Templates Tab.
	 *
	 * @return array $columns All columns name.
	 * @since  2.0
	 */
	public function get_columns() {
	    
        $columns = array(
            'cb'                  => '<input type="checkbox" />',
            'sr'                  => __( 'Sr', 'woocommerce-ac' ),
            'template_name'       => __( 'Name Of Template', 'woocommerce-ac' ),
        	'sent_time'     	  => __( 'Sent After Set Time', 'woocommerce-ac' ),
        	'template_filter'  	  => __( 'Send To Segment(s)', 'woocommerce-ac' ),
			'email_sent'  	      => __( 'Number of Emails Sent', 'woocommerce-ac' ),
			'open_rate'			  => __( 'Open Rate', 'woocommerce-ac' ),
			'link_rate'			  => __( 'Link Click Rate', 'woocommerce-ac' ),
			'coupon_rate'		  => __( 'Coupon Redemption Rate', 'woocommerce-ac' ),
        	'percentage_recovery' => __( 'Conversion Rate', 'woocommerce-ac' ),
        	'activate'  		  => __( 'Start Sending', 'woocommerce-ac' )
        );
		return apply_filters( 'wcap_templates_columns', $columns );
	}
	
	/**
	 * It is used to add the check box for the items
	 *
	 * @param string $item 
	 * @return string 
	 * @since 2.0
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
	 * We can mention on which column we need the sorting. Here we are sorting Name of the Template & Sent After Set Time.
	 *
	 * @return array $columns Name of the column
	 * @since  2.0
	 */
	public function wcap_templates_get_sortable_columns() {
		$columns = array(
			'template_name' => array( 'template_name', false ),
			'sent_time'		=> array( 'sent_time',false),
		);
		return apply_filters( 'wcap_templates_sortable_columns', $columns );
	}
	
	/**
	 * Render the Name of the Template. This function used for individual delete of row, It is for hover effect delete.
	 *
	 * @access public
	 * @since 2.4.9
	 * @param array $abandoned_row_info Contains all the data of the template row 
	 * @return string Data shown in the Email column
	 *
	 */
	public function column_template_name( $template_row_info ) {	
	    $row_actions = array();
	    $value       = '';
	    $template_id = 0;
	    if( isset( $template_row_info->template_name ) ) {	    
		    $template_id           = $template_row_info->id ; 	    
		    $row_actions['edit']   = '<a href="' . wp_nonce_url( add_query_arg( array( 'action' => 'cart_recovery', 'section' => 'emailtemplates', 'mode'=>'edittemplate', 'id' => $template_row_info->id ), $this->base_url ), 'abandoned_order_nonce') . '">' . __( 'Edit', 'woocommerce-ac' ) . '</a>';
		    $row_actions['copy']   = '<a href="' . wp_nonce_url( add_query_arg( array( 'action' => 'cart_recovery', 'section' => 'emailtemplates', 'mode'=>'copytemplate', 'id' => $template_row_info->id ), $this->base_url ), 'abandoned_order_nonce') . '">' . __( 'Duplicate', 'woocommerce-ac' ) . '</a>';
		    $row_actions['delete'] = '<a href="' . wp_nonce_url( add_query_arg( array( 'action' => 'wcap_delete_template', 'template_id' => $template_row_info->id ), $this->base_url ), 'abandoned_order_nonce') . '">' . __( 'Delete', 'woocommerce-ac' ) . '</a>';	    
		    $email                 = $template_row_info->template_name;
	        $value                 = $email . $this->row_actions( $row_actions );	    
	    }	
	    return apply_filters( 'wcap_template_single_column', $value, $template_id, 'email' );
	}
    
	/**
     * This function is used for email templates count.
     *
     * @globals mixed $wpdb
     * @return int $this->total_count total count of Email Templates.
     * @since   2.0
     */
    public function wcap_get_templates_count() {	
        global $wpdb;	
		$this->total_count = $wpdb->get_var( "SELECT COUNT(`id`) FROM `" .WCAP_EMAIL_TEMPLATE_TABLE."`" );
    }
	/**
     * It will manage for the Email Template list. 
     *
     * @globals mixed $wpdb
     * @return array $return_templates_display Key and value of all the columns
     * @since 2.0
     */
	public function wcap_templates_data() { 
    	global $wpdb;    	
    	$return_templates_data = array();
    	$per_page              = $this->per_page;
    	$results               = array();    	     
        $wcap_get_decimal      = wc_get_price_decimals();

    	$query                 = "SELECT id, is_active, frequency, day_or_hour, wc_template_filter, template_name, body 
    							  FROM `" . WCAP_EMAIL_TEMPLATE_TABLE . "` ORDER BY day_or_hour asc , frequency asc";
        $results               = $wpdb->get_results( $query );		
    
    	$i = 0;    	
    	$minute_seconds        = 60;
        $hour_seconds          = 3600; // 60 * 60
        $day_seconds           = 86400; // 24 * 60 * 60
		$time_to_send_template_after = '';
		
		// filter dates
        $start_date_range = '';
        if( isset( $_POST[ 'start_date_cart_recovery' ] ) && '' !== $_POST[ 'start_date_cart_recovery' ] ) {
        	$start_date_range = sanitize_text_field( wp_unslash( $_POST[ 'start_date_cart_recovery' ] ) );
        }
		if( '' === $start_date_range && isset( $_SESSION[ 'start_date' ] ) && '' !== $_SESSION[ 'start_date' ] ) {
			$start_date_range = sanitize_text_field( wp_unslash( $_SESSION[ 'start_date' ] ) );
		}

        $end_date_range = '';
        if ( isset( $_POST[ 'end_date_cart_recovery' ] ) && '' !== $_POST[ 'end_date_cart_recovery' ] ) {
            $end_date_range = $_POST[ 'end_date_cart_recovery' ];
		}
		if( '' === $end_date_range && isset( $_SESSION[ 'end_date' ] ) && '' !== $_SESSION[ 'end_date' ] ) {
			$end_date_range = sanitize_text_field( wp_unslash( $_SESSION[ 'end_date' ] ) );
		}

        $start_date = date( 'Y-m-d H:i:s', strtotime( "$start_date_range 00:01:01" ) );
		$end_date   = date( 'Y-m-d H:i:s', strtotime( "$end_date_range 23:59:59" ) );

    	foreach( $results as $key => $value ) {    	    
    	    $return_templates_data[ $i ] = new stdClass();    	    
    	    $id                          = $value->id;
    	    $query_no_emails             = "SELECT COUNT(`id`) FROM " . WCAP_EMAIL_SENT_HISTORY_TABLE . " WHERE template_id= %d AND sent_time >= %s AND sent_time <= %s";
    	    $ac_emails_count	         = $wpdb->get_var( $wpdb->prepare( $query_no_emails, $id, $start_date, $end_date ) );
			
			$query_no_recovers_test      = "SELECT COUNT(`id`) FROM " . WCAP_EMAIL_SENT_HISTORY_TABLE . " WHERE recovered_order = '1' AND template_id = %d AND sent_time >= %s AND sent_time <= %s";
    	    $wcap_number_of_time_recover = $wpdb->get_var( $wpdb->prepare( $query_no_recovers_test, $id, $start_date, $end_date ) );

			$is_active   = $value->is_active;
    	
    	    $frequency   = $value->frequency;
    	    $day_or_hour = $value->day_or_hour;

    	    if( 'Minutes' == $value->day_or_hour ) {
                $time_to_send_template_after = $value->frequency * $minute_seconds;
            } else if( 'Days' == $value->day_or_hour ) {
                $time_to_send_template_after = $value->frequency * $day_seconds;
            } else if( 'Hours' == $value->day_or_hour ) {
                $time_to_send_template_after = $value->frequency * $hour_seconds;
            }

			// Open Rate.
			$opened_emails = $wpdb->get_var( $wpdb->prepare( 'SELECT count( DISTINCT( open.email_sent_id ) ) FROM ' . WCAP_EMAIL_OPENED_TABLE . ' AS open INNER JOIN ' . WCAP_EMAIL_SENT_HISTORY_TABLE . ' AS sent ON open.email_sent_id = sent.id WHERE sent.template_id = %d AND open.time_opened >= %s AND open.time_opened <= %s', $id, $start_date, $end_date ) ); //phpcs:ignore
			$open_rate     = $opened_emails > 0 && $ac_emails_count > 0 ? round( ( $opened_emails / $ac_emails_count ) * 100, $wcap_get_decimal ) : 0;

			// Links Clicked Rate.
			$links_clicked = $wpdb->get_var( $wpdb->prepare( 'SELECT count( DISTINCT( link.email_sent_id ) ) FROM ' . WCAP_EMAIL_CLICKED_TABLE . ' AS link INNER JOIN ' . WCAP_EMAIL_SENT_HISTORY_TABLE . ' AS sent ON link.email_sent_id = sent.id WHERE sent.template_id = %d AND link.time_clicked >= %s AND link.time_clicked <= %s', $id, $start_date, $end_date ) ); //phpcs:ignore
			$links_rate    = $links_clicked > 0 && $ac_emails_count > 0 ? round( ( $links_clicked / $ac_emails_count ) * 100, $wcap_get_decimal ) : 0; 

			// coupon redemption rate.
			$coupon_rate = 0;
			if( stripos( $value->body, "{{coupon.code}}" ) ) {
				$coupon_rate = $links_clicked > 0 && $opened_emails > 0 ? round( ( $links_clicked / $opened_emails ) * 100, $wcap_get_decimal ) : 0;
			}
			

    		$wcap_recover_ratio = 0;
    	    if ( $ac_emails_count != 0 ) {
    	        $wcap_recover_ratio = $wcap_number_of_time_recover / $ac_emails_count * 100;
    	    }
    	    $template_filter = str_replace( ",", ", ", $value->wc_template_filter );
    	    $return_templates_data[ $i ]->sr                  = $i + 1;
    	    $return_templates_data[ $i ]->id                  = $id;
    	    $return_templates_data[ $i ]->template_name       = $value->template_name;
    	    $return_templates_data[ $i ]->sent_time           = __( $frequency . " " . $day_or_hour . " After Abandonment", 'woocommerce-ac' );

    	    $return_templates_data[ $i ]->template_time       = $time_to_send_template_after;
    	    $return_templates_data[ $i ]->template_filter     = $template_filter;
    	    $return_templates_data[ $i ]->email_sent          = __( ( string )$ac_emails_count, 'woocommerce-ac' );
    	    $return_templates_data[ $i ]->percentage_recovery = round ( $wcap_recover_ratio , $wcap_get_decimal )."%";
			$return_templates_data[ $i ]->is_active           = $is_active;
			$return_templates_data[ $i ]->open_rate           = $open_rate;
			$return_templates_data[ $i ]->link_rate           = $links_rate;
			$return_templates_data[ $i ]->coupon_rate         = $coupon_rate;
    	    $i++;  		    
    	}
    	
        // sort for order date
        if( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'template_name' ) {
        	if( isset( $_GET['order'] ) && $_GET['order'] == 'asc') {
        		usort( $return_templates_data, array( __CLASS__ ,"wcap_class_template_name_asc") ); 
        	} else {
        		usort( $return_templates_data, array( __CLASS__ ,"wcap_class_template_name_dsc") );
        	}
        }
        // sort for customer name
        else if( isset( $_GET['orderby']) && $_GET['orderby'] == 'sent_time' ) {
            if( isset( $_GET['order'] ) && $_GET['order'] == 'asc' ) {
        		usort( $return_templates_data, array( __CLASS__ ,"wcap_class_sent_time_asc" ) );
        	} else {
        		usort( $return_templates_data, array( __CLASS__ ,"wcap_class_sent_time_dsc" ) );
        	}
        }

        if ( isset( $_GET['paged'] ) && $_GET['paged'] > 1 ) {
		    $page_number = $_GET['paged'] - 1;
		    $k           = $per_page * $page_number;
		}else {
		    $k           = 0;
		}
		 
		$return_templates_display = array();
		for ( $j = $k; $j < ( $k + $per_page ); $j++ ) {
            if ( isset( $return_templates_data[ $j ] ) ) {
		        $return_templates_display[ $j ] = $return_templates_data[ $j ];
		    }else {
		        break;
		    }
		}
        return apply_filters( 'wcap_templates_table_data', $return_templates_display );
	}
	/**
	 * It will sort the alphabetically ascending on Name Of Template column.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 */
	function wcap_class_template_name_asc($value1,$value2) {
	    return strcasecmp($value1->template_name,$value2->template_name );
	}
	/**
	 * It will sort the alphabetically descending on Name Of Template column.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 */
	function wcap_class_template_name_dsc ($value1,$value2) {
	    return strcasecmp($value2->template_name,$value1->template_name );
	}
	/**
	 * It will sort the alphabetically ascending on the Sent After Set Time column.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 */
	function wcap_class_sent_time_asc($value1,$value2) {
	    return strnatcasecmp($value1->template_time,$value2->template_time );
	}
	/**
	 * It will sort the alphabetically descending on the Sent After Set Time column.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 */
	function wcap_class_sent_time_dsc ($value1,$value2) {
	    return strnatcasecmp($value2->template_time,$value1->template_time );
	}
	/**
	 * It will display the data for the Email Templates.
	 *
	 * @param  array | object $wcap_abandoned_orders All data of the list
	 * @param  stirng $column_name Name of the column
	 * @return string $value Data of the column
	 * @since  2.0
	 */
	public function column_default( $wcap_abandoned_orders, $column_name ) {
	    $value = '';
	    switch ( $column_name ) {	       
	        	            
			case 'activate' :
			    if( isset( $wcap_abandoned_orders->is_active ) ) {			       
			       $id        = $wcap_abandoned_orders->id;
			       $is_active = $wcap_abandoned_orders->is_active;
			       
			       $active    = ''; 
			       if( $is_active == '1' ) {
			           $active = "on";
			       } else {
			           $active = "off";
			       }
			       $active_text   = __( $active, 'woocommerce-ac' ); 
			       $value =  '<button type="button" class="wcap-switch wcap-toggle-template-status" '
					. 'wcap-template-id="'. $id .'" '
					. 'wcap-template-switch="'. ( $active ) . '">'
					. $active_text . '</button>'; 
			       
			    }
				break;	
				
			case 'template_filter' :
			    if( isset( $wcap_abandoned_orders->template_filter ) && '' != $wcap_abandoned_orders->template_filter) {
			        $value = $wcap_abandoned_orders->template_filter;
			    }else{
			    	$value = 'All';
			    }
				break;

			case 'open_rate':
			case 'link_rate':
			case 'coupon_rate':
				$value = isset( $wcap_abandoned_orders->$column_name ) ? $wcap_abandoned_orders->$column_name . '%' : '';
				break;

			default:			    
				$value = isset( $wcap_abandoned_orders->$column_name ) ? $wcap_abandoned_orders->$column_name : '';
				break;
	    }		
		return apply_filters( 'wcap_template_column_default', $value, $wcap_abandoned_orders, $column_name );
	}
	/**
	 * It will add the bulk action for delete the email template list.
	 *
	 * @return array $wcap_abandoned_bulk_actions bulk action
	 * @since  2.4.9
	 */
	public function get_bulk_actions() {
	    return array(
	        'wcap_delete_template' => __( 'Delete', 'woocommerce-ac' )
	    );
	}
}
?>