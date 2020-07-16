<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * It will show Abandoned & Recovered Product data on Product Reports tab.
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
 * The Product Report Page will display the total amount of the abandoned products as well as the total recovered amount for the products.
 * 
 * @since 2.3.7
 */
class Wcap_Product_Report_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 2.3.7
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 2.3.7
	 */
	public $base_url;

	/**
	 * Total number of recovred orders
	 *
	 * @var int
	 * @since 2.3.7
	 */
	public $total_count;

	/**
	 * It will add the bulk action and other variable needed for the class.
	 *
	 * @since 2.3.7
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;
		// Set parent defaults
		parent::__construct( array(
		        'singular' => __( 'product_id', 'woocommerce-ac' ), //singular name of the listed records
		        'plural'   => __( 'product_ids', 'woocommerce-ac' ), //plural name of the listed records
				'ajax'      => false             			// Does this table support ajax?
		) );

		$this->base_url = admin_url( 'admin.php?page=woocommerce_ac_page&action=stats' );
	}
	/**
	 * It will prepare the list of the abandoned products, columns and other data.
	 *
	 * @since 2.3.7
	 */
	public function wcap_product_report_prepare_items() {
		$columns   	 = $this->get_columns();
		$hidden   	 = array(); // No hidden columns
		$sortable 	 = $this->product_report_sortable_columns();
		$data     	 = $this->wacp_product_report_data ();
 		$total_items = $this->total_count;
        $this->items = $data;
		$this->_column_headers = array( $columns, $hidden, $sortable);
		$this->set_pagination_args( array(
				'total_items' => $total_items,                  	// WE have to calculate the total number of items
				'per_page'    => $this->per_page,                     	// WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $this->per_page )   // WE have to calculate the total number of pages
		      )
		);
	}
	/**
	 * It will add the columns for Product Reports Tab.
	 *
	 * @return array $columns All columns name.
	 * @since  2.3.7
	 */
	public function get_columns() {
	    $columns = array(
	        'product_name'     => __( 'Product Name', 'woocommerce-ac' ),
            'abandoned_number' => __( 'Number of Times Abandoned', 'woocommerce-ac' ),
	        'recover_number'   => __( 'Number of Times Recovered', 'woocommerce-ac' )
		);
	   return apply_filters( 'wcap_product_report_columns', $columns );
	}
	/**
	 * We can mention on which column we need the sorting. Here we are sorting on Product name, Number of Times abandoned, Number of Times Recovered.
	 *
	 * @return array $columns Name of the column
	 * @since  2.3.7
	 */
	public function product_report_sortable_columns() {
	    $columns = array(
	        'product_name'     => array( 'product_name', true ),
	        'abandoned_number' => array( 'abandoned_number', false ),
	        'recover_number'   => array( 'recover_number',false ),
	    );
	    return apply_filters( 'wcap_product_report_columns', $columns );
	}
    /**
	 * It will get the abandoned product data from database and calculate the number of abandoned & recovered products.
	 *
	 * @return String $return_product_report_display Data shown in the Email column
	 * @since  2.3.7
	 * @globals mixed $wpdb
	 */
	public function wacp_product_report_data() {
		global $wpdb;
		//$wcap_class            = new Woocommerce_Abandon_Cart ();
		$i                     = 0;
		$wc_round_value 	   = wc_get_price_decimals();
		
		$ac_cutoff_time        = get_option( 'ac_cart_abandoned_time' );
		$cut_off_time          = $ac_cutoff_time * 60;
		$current_time          = current_time( 'timestamp' );
		$compare_time          = $current_time - $cut_off_time;

		$ac_cutoff_time_guest  = get_option( 'ac_cart_abandoned_time_guest' );
		$cut_off_time_guest    = $ac_cutoff_time_guest * 60;
		$compare_time_guest    = $current_time - $cut_off_time_guest;
		$blank_cart_info       = '{"cart":[]}';
		$blank_cart_info_guest = '[]';
		$blank_cart            = '""';

		$query                 = "SELECT id,abandoned_cart_time, abandoned_cart_info, recovered_cart 
			FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` 
			WHERE 
				( user_type = 'REGISTERED' AND 
				abandoned_cart_time <= '$compare_time' AND 
				wcap_trash = '' AND 
				abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND 
				abandoned_cart_info NOT LIKE '%$blank_cart%' AND 
				( ( recovered_cart <> '0' ) OR ( cart_ignored <> '1' ) ) )
			OR 
				( user_type = 'GUEST' AND 
				abandoned_cart_time <= '$compare_time_guest' AND 
				wcap_trash = '' AND 
				abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND 
				abandoned_cart_info NOT LIKE '%$blank_cart%' AND 
				( ( recovered_cart <> '0' ) OR ( cart_ignored <> '1' ) ) )
			ORDER BY recovered_cart DESC";
		$recover_query         = $wpdb->get_results( $query );
		$rec_carts_array       = array();
		$recover_product_array = array();
		$return_product_report = array();
		$quantity_array 	   = array();
		$recover_price 		   = array();

		foreach ( $recover_query as $recovered_cart_key => $recovered_cart_value ) {
		    $coupon              = '';
		    $used_coupon         = 'NO';
		    $recovered_cart_info = json_decode( stripslashes( $recovered_cart_value->abandoned_cart_info ) );
		    $recovered_cart_dat  = $recovered_cart_value->recovered_cart;
		    $abandoned_order_id  = $recovered_cart_value->id;
		    if( $recovered_cart_dat > 0 ) {
		    	$order = array();
		    	try{
    		    	$order   = new WC_Order( $recovered_cart_dat );
    		    	$items   = $order->get_items();

	    	        foreach ( $items as $items_key => $items_value ){
	    	            $item_subtotal= 0;

	    	            $recover_product_id = $items_value['product_id'];
	    	            $recover_product_array[$recover_product_id] = isset( $recover_product_array[$recover_product_id] ) ? $recover_product_array[$recover_product_id] + $items_value['quantity'] : $items_value['quantity'];
	                    if( $items_value['line_subtotal_tax']!= 0 && $items_value['line_tax'] > 0 ) {
	                        $item_subtotal = $item_subtotal + $items_value['line_total'] + $items_value['line_tax'];
	                    } else {
	                        $item_subtotal = $item_subtotal + $items_value['line_total'];
	                    }
	                    //	Line total
	                    $total_price    = round ( $item_subtotal, $wc_round_value) ;
						if ( isset( $recover_price [ $recover_product_id ] ) && array_key_exists ( $recover_product_id, $recover_price )) {
	                    	$wcap_recover_price = $total_price + $recover_price [ $recover_product_id ];
							$recover_price [ $recover_product_id ] = $wcap_recover_price;
	                    } else {
	              			$recover_price [ $recover_product_id ] = $total_price;
	                    }
	    	        }
    			}catch (Exception $e){

			    }
		    }

		    $cart_update_time    = $recovered_cart_value->abandoned_cart_time;
		    $cart_details        = new stdClass();
		    if( isset( $recovered_cart_info->cart ) ){
		        $cart_details = $recovered_cart_info->cart;
		    }
		    if( $cart_details != false && count( get_object_vars( $cart_details ) ) > 0 ) {
		        foreach ( $cart_details as $k => $v ) {
		            $item_subtotal = 0;
                   if ( isset( $v->product_id ) ){

                       if( $v->line_subtotal_tax != 0 && $v->line_subtotal_tax > 0 ) {
                           $item_subtotal = $item_subtotal + $v->line_total + $v->line_subtotal_tax;
                       } else {
                           $item_subtotal = $item_subtotal + $v->line_total;
                       }
                       //	Line total
                       $total_price         = $item_subtotal;
                  		if ( isset( $quantity_array [ $v->product_id ] ) && array_key_exists ( $v->product_id, $quantity_array )) {

                       		$wcap_abandoned_amount = $total_price + $quantity_array [ $v->product_id ];
							$quantity_array [ $v->product_id ] = $wcap_abandoned_amount;
                       } else {

                           $quantity_array [ $v->product_id ] = $total_price;
                       }
                    }
		        }
		    }

		    $cut_off_time   = $ac_cutoff_time * 60 ;
		    $compare_time   = $current_time - $cart_update_time;
		    if ( is_array( $recovered_cart_info ) || is_object( $recovered_cart_info ) ) {
		        foreach ( $recovered_cart_info as $rec_cart_key => $rec_cart_value ) {

		        	if (is_array($rec_cart_value) || is_object($rec_cart_value) ){
			            foreach ( $rec_cart_value as $rec_product_id_key => $rec_product_id_value ) {
			                $product_id	= $rec_product_id_value->product_id;
			                if ( $compare_time > $cut_off_time ) {
			                	$rec_carts_array[$product_id] = isset( $rec_carts_array[$product_id] ) ? $rec_carts_array[$product_id] + $rec_product_id_value->quantity : $rec_product_id_value->quantity;
			                }
			            }
		        	}
		        }
		    }
		}

		arsort( $rec_carts_array );
		arsort( $recover_product_array );

		foreach ( $rec_carts_array as $count_abandoned_array_key => $count_abandoned_array_value ) {
		    $return_product_report[$i] = new stdClass();
		    if( array_key_exists ( $count_abandoned_array_key, $recover_product_array ) ) {
	            $recover_cart = $recover_product_array[$count_abandoned_array_key];
	        }
	        if( ! array_key_exists ( $count_abandoned_array_key, $recover_product_array ) ) {
	            $recover_cart = "0";
	        }
            $prod_name        = get_post( $count_abandoned_array_key );
            if ( NULL != $prod_name || '' != $prod_name ) {
    	        $product_name         = $prod_name->post_title;
    	        $abandoned_count      = $count_abandoned_array_value;
    	        $recover_price_amount = array_key_exists ( $count_abandoned_array_key, $recover_price) ? $recover_price[$count_abandoned_array_key] : 0;

    	        $return_product_report[ $i ]->product_name        = $product_name ;
    	        $return_product_report[ $i ]->abandoned_number    = $abandoned_count;
    	        $return_product_report[ $i ]->recover_number      = $recover_cart;
    	        $return_product_report[ $i ]->product_id          = $count_abandoned_array_key;
    	        $return_product_report[ $i ]->product_total_price = $quantity_array [ $count_abandoned_array_key ];
    	        $return_product_report[ $i ]->recover_total_price = $recover_price_amount;
    	        $i++;
            }
		}
        $this->total_count = count ( $return_product_report ) >= 0 ? count ( $return_product_report )  : 0 ;
		// sort for abandoned_number
		 if ( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'abandoned_number' ) {
		    if ( isset( $_GET['order' ]) && $_GET['order'] == 'asc' ) {
		        usort( $return_product_report, array( __CLASS__ , "wcap_class_abandoned_number_asc" ) );
		    }
		    else {
		        usort( $return_product_report, array( __CLASS__ , "wcap_class_abandoned_number_dsc" ) );
		    }
		}
		// sort for recover_number
		else if ( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'recover_number' ) {
		    if ( isset( $_GET['order' ]) && $_GET['order'] == 'asc' ) {
		        usort( $return_product_report, array( __CLASS__ , "wcap_class_recover_number_asc" ) );
		    }
		    else {
		        usort( $return_product_report, array( __CLASS__ , "wcap_class_recover_number_dsc" ) );
		    }
		}
		// sort for product name
		else if ( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'product_name' ) {
		    if ( isset( $_GET['order'] ) && $_GET['order'] == 'asc' ) {
		        usort( $return_product_report, array( __CLASS__ ,"wcap_class_product_name_asc" ) );
		    }
		    else {
		        usort( $return_product_report, array( __CLASS__ ,"wcap_class_product_name_dsc" ) );
		    }
		}
        $per_page         = $this->per_page;
        if ( isset( $_GET['paged'] ) && $_GET['paged'] > 1 ) {
         $page_number     = $_GET['paged'] - 1;
         $k               = $per_page * $page_number;
        } else {
         $k = 0;
        }

        $return_product_report_display = array();
        for ( $j = $k; $j < ( $k + $per_page ); $j++ ) {
            if ( isset( $return_product_report[ $j ] ) ) {
             $return_product_report_display[ $j ] = $return_product_report[ $j ];
            }else {
             break;
            }
        }
    	return apply_filters( 'wcap_product_report_table_data', $return_product_report_display );
	}
	/**
	 * It will sort the alphabetically ascending on the Number of Times Abandoned.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 */
	function wcap_class_abandoned_number_asc( $value1, $value2 ) {
	    return $value1->abandoned_number - $value2->abandoned_number;
	}
	/**
	 * It will sort the alphabetically descending on the Number of Times Abandoned.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 */
	function wcap_class_abandoned_number_dsc( $value1, $value2 ) {
	    return $value2->abandoned_number - $value1->abandoned_number;
	}
	/**
	 * It will sort the alphabetically ascending on the Number of Times Recovered.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 */
	function wcap_class_recover_number_asc( $value1, $value2 ) {
	    return $value1->recover_number - $value2->recover_number;
	}
	/**
	 * It will sort the alphabetically descending on the Number of Times Recovered.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 */
	function wcap_class_recover_number_dsc( $value1, $value2 ) {
	    return $value2->recover_number - $value1->recover_number;
	}
	/**
	 * It will sort the alphabetically ascending on the Product Name.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 */
	function wcap_class_product_name_asc( $value1, $value2 ) {
	    return strcasecmp( $value1->product_name,$value2->product_name );
	}
	/**
	 * It will sort the alphabetically descending on the Product Name.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 */
	function wcap_class_product_name_dsc ( $value1, $value2 ) {
	    return strcasecmp( $value2->product_name,$value1->product_name );
	}

	/**
	 * It will display the data for the Product Reports tab.
	 *
	 * @param  array | object $wcap_product_report All data of the list
	 * @param  stirng $column_name Name of the column
	 * @return string $value Data of the column
	 * @since  2.3.7
	 */
	public function column_default( $wcap_product_report, $column_name ) {
	    $value = '';
	    switch ( $column_name ) {

	        case 'product_name' :
			    if( isset( $wcap_product_report->product_name ) ) {
			        $value = "<a href= post.php?post=$wcap_product_report->product_id&action=edit title = product name > $wcap_product_report->product_name </a>";
			    }
				break;

			case 'abandoned_number' :
			    if( isset( $wcap_product_report->abandoned_number ) ) {
			       $value           = $wcap_product_report->abandoned_number. "&nbsp(&nbsp" . wc_price ( $wcap_product_report->product_total_price ) . "&nbsp)" ;
			    }
				break;

			case 'recover_number' :
			    if(isset($wcap_product_report->recover_number)){
			        $recover_price = 0;
			        if ( $wcap_product_report->recover_total_price > 0 ){
                        $recover_price = $wcap_product_report->recover_total_price;
			        }
			        $value = $wcap_product_report->recover_number . "&nbsp(&nbsp" . wc_price ( $recover_price ) . "&nbsp)" ;
			    }
				break;
			default:

				$value = isset( $wcap_product_report->$column_name ) ? $wcap_product_report->$column_name : '';
				break;
	    }
		return apply_filters( 'wcap_product_report_column_default', $value, $wcap_product_report, $column_name );
	}
}
?>