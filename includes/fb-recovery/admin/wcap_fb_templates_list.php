<?php 
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * This will display the list of FB Templates
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @category Classes
 * @since    7.10
 */

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Show SMS templates list in Cart Recovery->SMS Notifications
 * 
 * @since 7.10
 */
class WCAP_FB_Templates_List extends WP_List_Table {


    /**
     * Number of results to show per page
     *
     * @var string
     * @since 7.10
     */
    public $per_page = 20;

    /**
     * URL of this page
     *
     * @var string
     * @since 7.10
     */
    public $base_url;

    /**
     * Total number of templates
     *
     * @var int
     * @since 7.10
     */
    public $total_count;

    /**
     * It will add the bulk action and other variable needed for the class.
     *
     * @since 7.10
     * @see WP_List_Table::__construct()
     */
    public function __construct() {

        // Set parent defaults
        parent::__construct( array(
            'singular' => __( 'fb_template_id', 'woocommerce-ac' ), //singular name of the listed records
            'plural'   => __( 'fb_template_ids', 'woocommerce-ac' ), //plural name of the listed records
            'ajax'      => true                         // Does this table support ajax?
        ) );
        $this->wcap_get_fb_templates_count();
        $this->process_bulk_action();
        $this->base_url = admin_url( 'admin.php?page=woocommerce_ac_page&action=cart_recovery&section=fb_templates' );
    }

    /**
     * It will prepare the list of the SMS Templates, columns, pagination, sortable column and other data.
     *
     * @since 7.10
     */
    public function wcap_fb_templates_prepare_items() {

        $columns               = $this->get_columns();
        $hidden                = $this->wcap_hidden_fb_cols();
        $sortable              = array();
        $data                  = $this->wcap_fb_templates();       
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $total_items           = $this->total_count;
        $this->items           = $data;
        
        $this->set_pagination_args( array(
                'total_items' => $total_items,                      // WE have to calculate the total number of items
                'per_page'    => $this->per_page,                       // WE have to determine how many items to show on a page
                'total_pages' => ceil( $total_items / $this->per_page )   // WE have to calculate the total number of pages
              )
        );
    }
    
    function wcap_hidden_fb_cols() {
        return ( apply_filters( 'wcap_fb_hidden_cols', array() ) );
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
            'template_name'       => __( 'Text', 'woocommerce-ac' ),
            //'coupon_code'         => __( 'Coupon Code', 'woocommerce-ac' ),
            'sent_time'           => __( 'Template send time after abandonment', 'woocommerce-ac' ),
            'activate'            => __( 'Start Sending', 'woocommerce-ac' ),
            'actions'             => __( 'Actions', 'woocommerce-ac' )
        );
        return apply_filters( 'wcap_fb_templates_col', $columns );
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
    public function wcap_fb_templates() { 
        global $wpdb;       
        
        $return_templates_data = array();
        
        // Get the count of sms templates from the DB
        $fb_query = "SELECT * FROM " . WCAP_NOTIFICATIONS . "
                       WHERE type = %s";
        $fb_list = $wpdb->get_results( $wpdb->prepare( $fb_query, 'fb' ) );
        
        $template_count = 0;
        if( is_array( $fb_list ) && count( $fb_list ) > 0 && false !== $fb_list ) {
            foreach( $fb_list as $fb_details ) {
                // SMS ID
                $template_id = $fb_details->id;
                
                // Default
                $return_templates_data[ $template_count ] = new stdClass();
                
                $return_templates_data[ $template_count ]->id = $template_id;
                
                $return_templates_data[ $template_count ]->updated = 0;
                 
                // Subject
                $return_templates_data[ $template_count ]->template_subject = $fb_details->subject;

                // Subject
                $return_templates_data[ $template_count ]->body = $fb_details->body;               

                // Coupon Code
                $return_templates_data[ $template_count ]->coupon_code = $fb_details->coupon_code;
                
                $return_templates_data[ $template_count ]->sent_time = $fb_details->frequency;
                $return_templates_data[ $template_count ]->sms_sent = 0;
                $return_templates_data[ $template_count ]->active = $fb_details->is_active == 1 ? 'on' : 'off';
                $template_count++;
            }
            
        }
        
        return apply_filters( 'wcap_fb_templates_data', $return_templates_data );
        
    }
    /**
     * Displays the column data. The data sent in is displayed with
     * correct HTML or as needed.
     *
     * @param  array | object $wcap_fb_list All data of the list
     * @param  stirng $column_name Name of the column
     * @return string $value Data of the column
     * @since  7.9
     */
    public function column_default( $wcap_fb_list, $column_name ) {
        $value = '';

        $fb_id = $wcap_fb_list->id;
        
        switch ( $column_name ) {          
                            
            case 'activate' :
                if( isset( $wcap_fb_list->active ) ) {                
                   $id = $wcap_fb_list->id;
                   $active = $wcap_fb_list->active;
        
                   $active_text   = __( $active, 'woocommerce-ac' ); 
                   $value =  "<button type='button' class='wcap-switch wcap-toggle-template-status' " 
                    . "wcap-fb-id='$id' "
                    . "wcap-template-switch='" . ( $active ) . "'>"
                    . $active_text . '</button>'; 
                   
                }
                break;  

            case 'template_name':
                // Display '-' if Subject not present
                $value = ( isset( $wcap_fb_list->template_subject ) && '' !== $wcap_fb_list->template_subject ) ? $wcap_fb_list->template_subject : '-';
                break;

            case 'coupon_code':
                $id        = $wcap_fb_list->id;
                $value = "
                <div id='coupon_options' class='panel'>
                    <div class='options_group'>
                        <p class='form-field' style='padding-left:0px !important;'>";
                
                $json_ids       = array();
                $coupon_ids     = array();
                $coupon_code_id = ( isset( $wcap_fb_list->coupon_code ) ) ? $wcap_fb_list->coupon_code : '';
                
                if ( $coupon_code_id > 0 ) {
                    
                    $product = get_the_title( $coupon_code_id );
                    $json_ids[ $coupon_code_id ] = $product ;
                
                }
                
                if( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) > 0 ) {
                    $value .= "
                    <select id='coupon_ids_$id' name='coupon_ids[]' class='wc-coupon-search wc-product-search' style='width: 100%;' data-placeholder='" . esc_attr__( 'Search for a Coupon&hellip;', 'woocommerce' ) . "' data-action='wcap_json_find_coupons'>";
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
                $freq = $wcap_fb_list->sent_time;
                $value = __( "$freq", 'woocommerce-ac' );

                break;
            case 'actions':
                $fb_id = $wcap_fb_list->id;

                $template_string = json_encode( $wcap_fb_list );
                $value = "
                <button 
                    id='edit_$fb_id' 
                    data-wcap-template-id='$fb_id' 
                    class='button-secondary edit_fb' 
                    onclick='return false;' 
                    data-toggle='modal' 
                    data-target='.wcap-preview-modal'
                    data-wcap-template='$template_string'
                    >
                    <i class='fa fa-edit'></i>
                </button>

                <button id='delete_$fb_id' class='button-secondary delete_fb' onclick='return false;'>
                    <i class='fa fa-trash'></i>
                </button>";
                break;
            default:                
                $value = isset( $wcap_fb_list->$column_name ) ? $wcap_fb_list->$column_name : '';
                break;
        }       
        return apply_filters( 'wcap_fb_template_column_default', $value, $wcap_fb_list, $column_name );
    }
    
    /**
     * It will add the 'Delete' bulk action in the SMS template list.
     *
     * @return array - Bulk action
     * @since  7.9
     */
    public function get_bulk_actions() {
        return array(
            'wcap_delete_fb_template' => __( 'Delete', 'woocommerce-ac' )
        );
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
                echo '<tr id="fb_'.$rec->id.'">';
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
                        case "template_name": 
                            echo "<td $attributes>";
                            echo $this->column_default( $rec, $column_name ) . '</td>'; 
                            break;
                        case "template_subject": 
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