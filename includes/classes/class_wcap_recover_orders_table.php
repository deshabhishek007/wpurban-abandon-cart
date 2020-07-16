<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * It will show details of recovery orders on Recovered Orders tab.
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
 * This class is used to display recovered orders as well as the total recovered amount for the products.
 * 
 * @since 2.0
 */
class Wcap_Recover_Orders_Table extends WP_List_Table {

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
	 * Total number of recovered orders
	 *
	 * @var int
	 * @since 2.0
	 */
	public $total_count;


	/**
	 * Total number of abandoned orders
	 *
	 * @var int
	 * @since 2.0
	 */
	public $total_abandoned_cart_count;

	/**
	 * Total amount of abandoned orders
	 *
	 * @var int
	 * @since 2.0
	 */
	public $total_order_amount;

	/**
	 * Total number recovered orders item
	 *
	 * @var int
	 * @since 2.0
	 */
	public $recovered_item;

	/**
	 * Total number recovered orders total
	 *
	 * @var int
	 * @since 2.0
	 */
	public $total_recover_amount;
	/**
	 * It will add the bulk action and other variable needed for the class.
	 *
	 * @since 2.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
        global $status, $page;
        // Set parent defaults
		parent::__construct( array(
		        'singular' => __( 'rec_abandoned_id', 'woocommerce-ac' ), //singular name of the listed records
		        'plural'   => __( 'rec_abandoned_ids', 'woocommerce-ac' ), //plural name of the listed records
				'ajax'      => false             			// Does this table support ajax?
		) );
		$this->base_url = admin_url( 'admin.php?page=woocommerce_ac_page&action=stats' );
	}
	/**
	 * It will prepare the list of the recovered orders, columns and other data.
	 *
	 * @since 2.0
	 */
	public function wcap_recovered_orders_prepare_items() {
		$columns                    = $this->get_columns();
		$hidden                     = array(); // No hidden columns
		$sortable                   = $this->recovered_orders_get_sortable_columns();
		$data                       = $this->wcap_recovered_orders_data();
		$total_items                = $this->total_count;
		$total_abandoned_cart_count = $this->total_abandoned_cart_count;
		$total_order_amount         = $this->total_order_amount;
		$total_recover_amount       = $this->total_recover_amount;
		$recovered_item             = $this->recovered_item;
		$this->items                = $data;
		$this->_column_headers = array( $columns, $hidden, $sortable);
		$this->set_pagination_args( array(
				'total_items' => $total_items,                  	// WE have to calculate the total number of items
				'per_page'    => $this->per_page,                     	// WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $this->per_page )   // WE have to calculate the total number of pages
		      )
		);
	}
	/**
	 * It will add the columns for Recovered Orders Tab.
	 *
	 * @return array $columns All columns name.
	 * @since  2.0
	 */
	public function get_columns() {
	    $columns = array(
	    	'cb'              => '<input type="checkbox" />',
 		    'user_name'       => __( 'Customer Name', 'woocommerce-ac' ),
	        'user_email_id'   => __( 'Email Address', 'woocommerce-ac' ),
			'created_on'      => __( 'Cart Abandoned Date', 'woocommerce-ac' ),
			'email_sent'  	  => __( 'Email Sent?', 'woocommerce-ac' ),
            'recovered_date'  => __( 'Cart Recovered Date' , 'woocommerce-ac'),
            'order_total'     => __( 'Order Total', 'woocommerce-ac' )
		);
		return apply_filters( 'wcap_recovered_orders_columns', $columns );
	}

	/**
	 * It is used to add the check box for the items
	 *
	 * @param string $item Recovered Carts
	 * @since 2.0
	 */
	function column_cb( $item ) {
	    $abandoned_order_id = '';
	    if( isset( $item->ac_id ) && "" != $item->ac_id ) {
	       $abandoned_order_id = $item->ac_id;
 	       return sprintf(
	           '<input type="checkbox" name="%1$s[]" value="%2$s" />',
	           'abandoned_order_id',
	           $abandoned_order_id
	       );
	    }
	}

	/**
	 * We can mention on which column we need the sorting. Here we are sorting on Cart Abandoned Date & Cart Recovered Date.
	 *
	 * @return array $columns Name of the column
	 * @since  2.0
	 */
	public function recovered_orders_get_sortable_columns() {
		$columns = array(
			'created_on'      => array( 'created_on', false ),
			'recovered_date'  => array( 'recovered_date',false)
		);
		return apply_filters( 'wcap_templates_sortable_columns', $columns );
	}

	/**
	 * This function used for deleting individual row of Recovered Orders. Render the Email Column. So we will add the action on the hover affect.
	 *
	 * @access public
	 * @param  array $recovered_orders_row_info Contains all the data of the recovered order's row
	 * @return string Data shown in the Email column
	 * @since  2.5
	 */
	public function column_user_name( $recovered_orders_row_info ) {
	    $row_actions  = array();
	    $value 		  = '';
	    $recovered_id = 0;
	    if( isset( $recovered_orders_row_info->user_name ) ) {
    	    $recovered_id 				 = $recovered_orders_row_info->recovered_id;
    	    $abandoned_order_id 		 = $recovered_orders_row_info->ac_id;

    	    if( $recovered_orders_row_info->order_total != 'Order has been deleted' ) {
    	    	$row_actions['view_details'] = "<a target=_blank href = post.php?post=$recovered_id&action=edit>". __( 'View Details', 'woocommerce-ac' )."</a>";
    		}
    	    $user_name = $recovered_orders_row_info->user_name;
    	    $row_actions['trash']       = '<a href="' . wp_nonce_url( add_query_arg( array( 'action' => 'wcap_rec_trash',    'abandoned_order_id' => $recovered_orders_row_info->ac_id ), $this->base_url ), 'abandoned_order_nonce') . '">' . __( 'Trash', 'woocommerce-ac' ) . '</a>';
            $value = $user_name . $this->row_actions( $row_actions );
	    }
        return apply_filters( 'wcap_recovered_orders_single_column', $value, $recovered_id, 'email' );
	}
	/**
	 * It will manage the recovered items from database and shows it on Recovered Orders tab with the custom email address and cart abandoned & recovered date. 
	 *
	 * @return String $return_recovered_display Recovdered Carts for the selected date range.
	 * @globals mixed $wpdb
	 * @globals mixed $woocommerce
	 * @since  2.0
	 */
	public function wcap_recovered_orders_data() {
		global $wpdb, $woocommerce;
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

		$wcap_include_tax        = get_option( 'woocommerce_prices_include_tax' );
        $wcap_include_tax_setting= get_option( 'woocommerce_calc_taxes' );

		$start_date              = strtotime( $start_date_range." 00:01:01" );
		$end_date                = strtotime( $end_date_range." 23:59:59" );
		$blank_cart_info         = '{"cart":[]}';
		$blank_cart_info_guest   = '[]';

		$ac_cutoff_time          = get_option( 'ac_cart_abandoned_time' );
		$cut_off_time            = $ac_cutoff_time * 60;
		$current_time            = current_time( 'timestamp' );
		$compare_time            = $current_time - $cut_off_time;

		$ac_cutoff_time_guest    = get_option( 'ac_cart_abandoned_time_guest' );
		$cut_off_time_guest      = $ac_cutoff_time_guest * 60;
		$current_time            = current_time ('timestamp');
		$compare_time_guest      = $current_time - $cut_off_time_guest;

		$query_ac                = "SELECT * FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND recovered_cart > 0 AND wcap_trash = '' ) OR ( user_type = 'GUEST' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND recovered_cart > 0 AND wcap_trash = '' ) ORDER BY recovered_cart desc ";
		$ac_results              = $wpdb->get_results( $query_ac );

		$query_abandoned         = "SELECT * FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE ( user_type = 'REGISTERED' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wcap_trash = '' AND cart_ignored <> '1') OR ( user_type = 'GUEST' AND abandoned_cart_time >=  $start_date AND abandoned_cart_time <= $end_date AND abandoned_cart_info NOT LIKE '$blank_cart_info_guest' AND abandoned_cart_info NOT LIKE '%$blank_cart_info%' AND wcap_trash = '' AND cart_ignored <> '1') ";
		$ac_carts_results        = $wpdb->get_results( $query_abandoned );

		$recovered_item          = $recovered_total = $count_carts = $total_value = $order_total = 0;
		$return_recovered_orders = array();
		$per_page                = $this->per_page;
		$i = 0;
		$currency = '';

		foreach ( $ac_carts_results as $key => $value ) {
			$abandoned_order_id = $value->id;
		    $count_carts    	+= 1;
	        $cart_detail 		= json_decode( stripslashes( $value->abandoned_cart_info ) );
	        $product_details 	= array();
	        if( isset( $cart_detail->cart ) ) {
	            $product_details = $cart_detail->cart;
	        }

	        // Currency selected
	        $currency = isset( $cart_detail->currency ) ? $cart_detail->currency : '';

	        $line_total = 0;

	        if ( isset( $product_details ) && $product_details !== false && count( get_object_vars( $product_details ) ) > 0 ) {

	            foreach ( $product_details as $k => $v ) {
                    if( isset( $wcap_include_tax ) && $wcap_include_tax == 'no' && isset( $wcap_include_tax_setting ) && $wcap_include_tax_setting == 'yes' ) {
                        $line_total    = $line_total + $v->line_total;
                    } else if ( isset( $wcap_include_tax ) && $wcap_include_tax == 'yes' && isset( $wcap_include_tax_setting ) && $wcap_include_tax_setting == 'yes' ) {
                        // Item subtotal is calculated as product total including taxes
                        if( $v->line_tax != 0 && $v->line_tax > 0 ) {
							$line_total    = $line_total + $v->line_subtotal + $v->line_subtotal_tax;
                        } else {
                            $line_total    = $line_total + $v->line_total;
                        }
                    } else {
                    	$line_total = $line_total + $v->line_total;
                    }
	            }
	        }
	        $total_value += $line_total;
		}
		$recovered_order_total            = 0;
		$this->total_recover_amount       = round( $recovered_order_total, 2 );
		$this->recovered_item             = 0;
		$table_data                       = "";

		foreach ( $ac_results as $key => $value ) {
            if( 0 != $value->recovered_cart ) {
    	        $return_recovered_orders[$i] = new stdClass();
    	        $abandoned_order_id 		 = $value->id;
    	        $recovered_id       		 = $value->recovered_cart;
    	        $rec_order          		 = get_post_meta( $recovered_id );
    	        $wcap_include_tax 			 = get_option( 'woocommerce_prices_include_tax' );
            	$wcap_include_tax_setting    = get_option( 'woocommerce_calc_taxes' );
    	        $woo_order 				     = array();
		        try{
	    	        $woo_order          	 = new WC_Order( $recovered_id );
	    	        $wcap_order_tax_amount   = '';

	    	        if( version_compare( $woocommerce->version, '3.0.0', ">=" ) ) {
	    	        	$order = get_post( $recovered_id );
						$recovered_date 	   = strtotime ( $order->post_date );
						$date_format 		   = date_i18n( get_option( 'date_format' ), $recovered_date );
                		$time_format 		   = date_i18n( get_option( 'time_format' ), $recovered_date );
                        $recovered_date_new    = $date_format . ' ' . $time_format;
                        if( isset( $wcap_include_tax_setting ) && $wcap_include_tax_setting == 'yes' ) {
                        	$wcap_order_tax_amount = $woo_order->get_cart_tax();
                    	}
					} else {
	    	        	$recovered_date        = strtotime( $woo_order->order_date );
	    	        	$date_format 		   = date_i18n( get_option( 'date_format' ), $recovered_date );
                		$time_format 		   = date_i18n( get_option( 'time_format' ), $recovered_date );
                        $recovered_date_new    = $date_format . ' ' . $time_format;
                        if( isset( $wcap_include_tax_setting ) && $wcap_include_tax_setting == 'yes' ) {
	    	        		$wcap_order_tax_amount = $woo_order->cart_tax;
	    	    		}
	    	    	}
	    	    	$show_taxes = apply_filters('wcap_show_taxes', true);

	    	        if( $show_taxes && isset( $wcap_include_tax ) && $wcap_include_tax == 'no' &&
		                isset( $wcap_include_tax_setting ) && $wcap_include_tax_setting == 'yes' ) {
				    	$line_subtotal_tax = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $wcap_order_tax_amount, $currency ), $abandoned_order_id, $wcap_order_tax_amount, 'wcap_recover' ); //wc_price( $wcap_order_tax_amount );
		                $wcap_order_tax_amount =  '<br>'. __( "Tax: ", "woocommerce-ac" ) . $line_subtotal_tax ;

		            } else if( $show_taxes && isset( $wcap_include_tax ) && $wcap_include_tax == 'yes' &&
		                isset( $wcap_include_tax_setting ) && $wcap_include_tax_setting == 'yes' ) {

		            	$wcap_order_tax_amount =  apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $wcap_order_tax_amount, $currency ), $abandoned_order_id, $wcap_order_tax_amount, 'wcap_recover' ); //wc_price( $wcap_order_tax_amount );
		                $wcap_order_tax_amount =  ' (' . __( "includes Tax: " , "woocommerce-ac" ) . $wcap_order_tax_amount . ')';
		            }
					$recovered_item    += 1;
	    	        if ( isset( $rec_order ) && $rec_order != false ) {
	    	            $recovered_total += $rec_order['_order_total'][0];
	    	        }
	    	        $abandoned_date_format 		  = date_i18n( get_option( 'date_format' ), $value->abandoned_cart_time );
                	$abandoned_time_format 		  = date_i18n( get_option( 'time_format' ), $value->abandoned_cart_time );
	    	        $abandoned_date               = $abandoned_date_format . ' ' . $abandoned_time_format;
	    	        $abandoned_order_id           = $value->id;
	    	        $is_email_sent_for_this_order = Wcap_Common::wcap_check_email_sent_for_order( $abandoned_order_id ) ? 'Yes' : 'No';
	    	        $billing_first_name           = $billing_last_name = $billing_email = '';
	    	        $recovered_order_total        = 0;
	    	        if ( isset( $rec_order['_billing_first_name'][0] ) ) {
	    	            $billing_first_name = $rec_order['_billing_first_name'][0];
	    	        }
	    	        if ( isset( $rec_order['_billing_last_name'][0] ) ) {
	    	            $billing_last_name = $rec_order['_billing_last_name'][0];
	    	        }
	    	        if ( isset( $rec_order['_billing_email'][0] ) ) {
	    	            $billing_email = $rec_order['_billing_email'][0];
	    	        }
	    	        if ( isset( $rec_order['_order_total'][0] ) ) {
	    	            $recovered_order_total = $rec_order['_order_total'][0];
	    	        }
	    	        $return_recovered_orders[ $i ]->user_name          = $billing_first_name . " " . $billing_last_name;
	    	        $return_recovered_orders[ $i ]->user_email_id      = $billing_email;
	    	        $return_recovered_orders[ $i ]->created_on         = $abandoned_date;
	    	        $return_recovered_orders[ $i ]->email_sent         = $is_email_sent_for_this_order;
	    	        $return_recovered_orders[ $i ]->recovered_date     = $recovered_date_new;
	    	        $return_recovered_orders[ $i ]->recovered_id       = $recovered_id;
	    	        $return_recovered_orders[ $i ]->recover_order_date = $recovered_date;
	    	        $return_recovered_orders[ $i ]->abandoned_date     = $value->abandoned_cart_time;
	    	        if ($show_taxes) {
	    	        	$return_recovered_orders[ $i ]->order_total        = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $recovered_order_total, $currency ), $abandoned_order_id, $recovered_order_total, 'wcap_recover' ) . $wcap_order_tax_amount; 
					} else {

						$return_recovered_orders[ $i ]->order_total        = apply_filters ( 'acfac_change_currency', Wcap_Common::wcap_get_price( $recovered_order_total, $currency ), $abandoned_order_id, $recovered_order_total, 'wcap_recover' ); 

					}
	    	        $return_recovered_orders[ $i ]->ac_id          	   = $abandoned_order_id;
	    	        $this->recovered_item                              = $recovered_item;
	    	        $this->total_recover_amount                        = round( ( $recovered_order_total + $this->total_recover_amount ) , 2 );
	    	        $i++;
    	    	}catch (Exception $e) {
    	    		$class              = new WC_Order( $e );
    	    		$wcap_user_id 		= $value->user_id;
    	    		$recovered_date_new = $recovered_date = '';
    	    		$recovered_item     += 1;
	    	        if ( isset( $rec_order ) && $rec_order != false ) {
	    	            $recovered_total += $rec_order['_order_total'][0];
	    	        }
	    	        $abandoned_date_format 		  = date_i18n( get_option( 'date_format' ), $value->abandoned_cart_time );
                	$abandoned_time_format 		  = date_i18n( get_option( 'time_format' ), $value->abandoned_cart_time );
	    	        $abandoned_date               = $abandoned_date_format . ' ' . $abandoned_time_format;
	    	        $abandoned_order_id           = $value->id;
	    	        $is_email_sent_for_this_order = Wcap_Common::wcap_check_email_sent_for_order( $abandoned_order_id ) ? 'Yes' : 'No';
	    	        $billing_first_name           = $billing_last_name = $billing_email = '';
	    	        $recovered_order_total        = 0;

					if( $value->user_type == "GUEST" && $value->user_id != '0' ) {
                        $value->user_login  = "";
                        $query_guest        = "SELECT billing_first_name, billing_last_name, email_id FROM `" . WCAP_GUEST_CART_HISTORY_TABLE . "` WHERE id = %d";
                        $results_guest      = $wpdb->get_results( $wpdb->prepare( $query_guest, $value->user_id ) );
                        if( count( $results_guest ) > 0 ) {
                            $billing_email 		= $results_guest[0]->email_id;
                            $billing_first_name = $results_guest[0]->billing_first_name;
                            $billing_last_name  = $results_guest[0]->billing_last_name;
                        }
                    } else {
                        $user_id            = $value->user_id;
                        $key                = 'billing_email';
                        $single             = true;
                        $user_biiling_email = get_user_meta( $user_id, $key, $single );
                        if( isset( $user_biiling_email ) && $user_biiling_email != '' ) {
                           $billing_email = $user_biiling_email;
                       }

                       $user_first_name_temp = get_user_meta( $user_id, 'billing_first_name', true );
		                if( isset( $user_first_name_temp ) && "" == $user_first_name_temp ) {
		                    $user_data  = get_userdata( $user_id );
		                    $billing_first_name = '';
		                    if( isset( $user_data ) && false != $user_data ){
		                    	$billing_first_name = $user_data->first_name;
		                	}
		                } else {
		                    $billing_first_name = $user_first_name_temp;
		                }

		                $user_last_name_temp = get_user_meta( $user_id, 'billing_last_name', true );
		                if( isset( $user_last_name_temp ) && "" == $user_last_name_temp ) {
		                    $user_data  = get_userdata( $user_id );
		                    $billing_last_name = '';
		                    if ( isset( $user_data ) && false != $user_data ) {
		                    	$billing_last_name = $user_data->last_name;
		                	}
		                } else {
		                    $billing_last_name = $user_last_name_temp;
		                }
                    }
	    	        if ( isset( $rec_order['_order_total'][0] ) ) {
	    	            $recovered_order_total = $rec_order['_order_total'][0];
	    	        }
	    	        $return_recovered_orders[ $i ]->user_name          = $billing_first_name . " " . $billing_last_name;
	    	        $return_recovered_orders[ $i ]->user_email_id      = $billing_email;
	    	        $return_recovered_orders[ $i ]->created_on         = $abandoned_date;
	    	        $return_recovered_orders[ $i ]->email_sent         = $is_email_sent_for_this_order;
	    	        $return_recovered_orders[ $i ]->recovered_date     = $recovered_date_new;
	    	        $return_recovered_orders[ $i ]->recovered_id       = $recovered_id;
	    	        $return_recovered_orders[ $i ]->recover_order_date = $recovered_date;
	    	        $return_recovered_orders[ $i ]->abandoned_date     = $value->abandoned_cart_time;
	    	        $return_recovered_orders[ $i ]->order_total        = 'Order has been deleted';
	    	        $return_recovered_orders[ $i ]->ac_id          	   = $abandoned_order_id;
	    	        $this->recovered_item                              = $recovered_item;
	    	        $this->total_recover_amount                        = round( ( $recovered_order_total + $this->total_recover_amount ), 2 );
	    	        $i++;
			    }
			}
        }

        $total_value 					  += $this->total_recover_amount; 
        $total_value 					  = Wcap_Common::wcap_get_price( $total_value, $currency );
		$this->total_order_amount         = $total_value;
		$this->total_abandoned_cart_count = $count_carts + $recovered_item;
	
		$templates_count   = count($return_recovered_orders);
		$this->total_count = $templates_count;
    	// sort for order date
		 if ( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'created_on' ) {
    		if ( isset( $_GET['order']) && $_GET['order'] == 'asc' ) {
				usort( $return_recovered_orders, array( __CLASS__ ,"wcap_class_recovered_created_on_asc" ) );
			}else {
				usort( $return_recovered_orders, array( __CLASS__ ,"wcap_class_recovered_created_on_dsc" ) );
			}
		}

	    // sort for customer name
        else if ( isset( $_GET['orderby']) && $_GET['orderby'] == 'recovered_date' ) {
            if ( isset( $_GET['order'] ) && $_GET['order'] == 'asc' ) {
				usort( $return_recovered_orders, array( __CLASS__ ,"wcap_class_recovered_date_asc" ) );
			}else {
				usort( $return_recovered_orders, array( __CLASS__ ,"wcap_class_recovered_date_dsc" ) );
			}
        }
        $per_page        = $this->per_page;
		 if ( isset( $_GET['paged'] ) && $_GET['paged'] > 1 ) {
		     $page_number = $_GET['paged'] - 1;
		     $k           = $per_page * $page_number;
		 } else {
		     $k           = 0;
		 }

		 $return_recovered_display = array();
		 for ( $j = $k; $j < ( $k + $per_page ); $j++ ) {
            if ( isset( $return_recovered_orders[ $j ] ) ) {
		         $return_recovered_display[ $j ] = $return_recovered_orders[ $j ];
		     } else {
		         break;
		     }
		}
        return apply_filters( 'wcap_recovered_orders_table_data', $return_recovered_display );
	}
    /**
	 * It will sort the alphabetically ascending on Cart Abandoned Date.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 * @todo   Change function name
	 */
	function wcap_class_recovered_created_on_asc( $value1, $value2 ) {
	    return $value1->abandoned_date - $value2->abandoned_date;
	}
	/**
	 * It will sort the alphabetically descending on Cart Abandoned Date.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 * @todo   Change function name
	 */
	function wcap_class_recovered_created_on_dsc ( $value1, $value2 ) {
	    return $value2->abandoned_date - $value1->abandoned_date;
	}
	/**
	 * It will sort the alphabetically ascending on Cart Recovered Date.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 */
	function wcap_class_recovered_date_asc( $value1, $value2 ) {
		return $value1->recover_order_date - $value2->recover_order_date;
	}
	/**
	 * It will sort the alphabetically descending on Cart Recovered Date.
	 *
	 * @param  array | object $value1 All data of the list
	 * @param  array | object $value2 All data of the list
	 * @return sorted array  
	 * @since  3.4
	 */
	function wcap_class_recovered_date_dsc ( $value1, $value2 ) {
		return $value2->recover_order_date - $value1->recover_order_date;
	}
	/**
	 * It will display the data for the Recovered Orders tab.
	 *
	 * @param  array | object $wcap_abandoned_orders All data of the list
	 * @param  stirng $column_name Name of the column
	 * @return string $value Data of the column
	 * @since  2.3.7
	 */
	public function column_default( $wcap_abandoned_orders, $column_name ) {
	    $value = '';
	    switch ( $column_name ) {

	        case 'user_email_id' :
			    if( isset( $wcap_abandoned_orders->user_email_id ) ){

			        $user_email_id = "<a href= mailto:$wcap_abandoned_orders->user_email_id>". $wcap_abandoned_orders->user_email_id."</a>" ;
				    $value = $user_email_id;
			    }
				break;

			case 'created_on' :
			    if( isset( $wcap_abandoned_orders->created_on ) ){
			       $value = $wcap_abandoned_orders->created_on;
			    }
				break;

			case 'email_sent' :
			    if( isset( $wcap_abandoned_orders->email_sent ) ){
			       $value   = $wcap_abandoned_orders->email_sent;
			    }
				break;

			case 'recovered_date' :
			    if( isset( $wcap_abandoned_orders->recovered_date ) ){
	 			   $value = $wcap_abandoned_orders->recovered_date;
			    }
				break;

			case 'order_total' :
			    if( isset( $wcap_abandoned_orders->order_total ) ){
			     $value = $wcap_abandoned_orders->order_total;
			    }
			    break;
		    default:

				$value = isset( $wcap_abandoned_orders->$column_name ) ? $wcap_abandoned_orders->$column_name : '';
				break;
	    }
		return apply_filters( 'wcap_recovered_orders_column_default', $value, $wcap_abandoned_orders, $column_name );
	}
	/**
	 * It will add the bulk action for moving data to trash.
	 *
	 * @since 2.4.7
	 */
	public function get_bulk_actions() {
	    return array(
	        'wcap_rec_trash' => __( 'Move to Trash', 'woocommerce-ac' )
	    );
	}
}
?>
