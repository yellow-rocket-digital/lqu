<?php
//This file is required at the end of functions.php in case you need it!
//

// add link to the menu
add_filter ( 'woocommerce_account_menu_items', 'misha_one_more_link' );
function misha_one_more_link( $menu_links ){

	// we will hook "anyuniquetext123" later
	$new = array( 'anyuniquetext123' => 'Projects' );

	// or in case you need 2 links
	// $new = array( 'link1' => 'Link 1', 'link2' => 'Link 2' );

	// array_slice() is good when you want to add an element between the other ones
	$menu_links = array_slice( $menu_links, 0, 1, true ) 
	+ $new 
	+ array_slice( $menu_links, 1, NULL, true );

	return $menu_links;
 
}

// hook the external URL
add_filter( 'woocommerce_get_endpoint_url', 'misha_hook_endpoint', 10, 4 );
function misha_hook_endpoint( $url, $endpoint, $value, $permalink ){
 
	if( 'anyuniquetext123' === $endpoint ) {
 
		// ok, here is the place for your custom URL, it could be external
		$url = '/projects';
 
	}
	return $url;
 
}

add_filter ( 'woocommerce_account_menu_items', 'misha_one_more_link_2' );
function misha_one_more_link_2( $menu_links ){

	// we will hook "anyuniquetext123" later
	$new = array( 'userfiles' => 'Files' );

	// or in case you need 2 links
	// $new = array( 'link1' => 'Link 1', 'link2' => 'Link 2' );

	// array_slice() is good when you want to add an element between the other ones
	$menu_links = array_slice( $menu_links, 0, 2, true ) 
	+ $new 
	+ array_slice( $menu_links, 2, NULL, true );

	return $menu_links;
 
}

// hook the external URL
add_filter( 'woocommerce_get_endpoint_url', 'misha_hook_endpoint_2', 10, 4 );
function misha_hook_endpoint_2( $url, $endpoint, $value, $permalink ){
 
	if( 'userfiles' === $endpoint ) {
 
		// ok, here is the place for your custom URL, it could be external
		$url = '/client-files';
 
	}
	return $url;
 
}


add_filter( 'woocommerce_account_menu_items', 'misha_remove_my_account_links' );
function misha_remove_my_account_links( $menu_links ){
	
	unset( $menu_links[ 'dashboard' ] ); // Remove Dashboard
	unset( $menu_links[ 'payment-methods' ] ); // Remove Payment Methods
	//unset( $menu_links[ 'orders' ] ); // Remove Orders
	unset( $menu_links[ 'downloads' ] ); // Disable Downloads
	unset( $menu_links[ 'edit-address' ] ); // Remove Addresses
	//unset( $menu_links[ 'edit-account' ] ); // Remove Account details tab
	unset( $menu_links[ 'customer-logout' ] ); // Remove Logout link
	
	return $menu_links;
	
}
add_filter("body_class", function($classes) {
    global $current_user;
    
    foreach ($current_user->roles as $user_role) {
        $classes[] = "role-{$user_role}";
    }

    return $classes;
});
add_filter("admin_body_class", function($classes) {
  $user = wp_get_current_user();
  foreach ($user->roles as $user_role) {
    $classes .= " role-{$user_role}";
  }
  return $classes;
});
?>
<?php
/**
 * Add a widget to the dashboard.
 *
 */
if ( get_current_user_id() === 4 ) {

function wc_orders_dashboard_widgets() {
	wp_add_dashboard_widget(
                 'wc_order_widget_id',         // Widget slug.
                 'New Quote Requests',         // Title.
                 'wc_orders_dashboard_widget_function' // Display function.
        );	
}
add_action( 'wp_dashboard_setup', 'wc_orders_dashboard_widgets' );

function wc_orders_dashboard_widget_function() {    
    $args   = array( 
            'post_type'         => 'shop_order',
            'post_status'       => 'wc-ywraq-new',  //Other options available choose one only: 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-cancelled', 'wc-refunded', 'wc-failed'
            'posts_per_page'    => 20, // Change this number to display how many orders you want to see 
          );
    $orders = new WP_Query( $args );
    if( $orders->have_posts() ) {
        ?>      
        <table width="100%">
            <tr>
                <th><?php _e( 'Order Id', 'woocommerce' ); ?></th>
                <th><?php _e( 'Total', 'woocommerce' ); ?></th>
                <th><?php _e( 'Status', 'woocommerce' ); ?></th>
            </tr>
        <?php       
        while ( $orders->have_posts() ) {
            $orders->the_post();
            ?>
            <tr style="text-align: center;">
                <td>
                <?php
                
                $order   =   new WC_Order( get_the_ID() );
                // 1. Get the order ID
                if ( $order ) {                 
                          echo '<a href="'. admin_url( 'post.php?post=' . absint( $order->id ) . '&action=edit' ) .'" >' . $order->get_order_number() . '</a>';
                ?>
                </td>
                <td>                
                <?php
                    // 2. Get the order total
                    echo wp_kses_post( wc_price( $order->get_total() ) );
                }
                ?>
                </td>
                <td>                
                <?php
                    // 3. Get the order status
                    echo esc_html( wc_get_order_status_name( $order->get_status() ) );
                }
                ?>
                </td>                   
            </tr>
            <?php           
        }
        ?></table><?php
    }


function wc_orders_dashboard_widget_css() {
    $css = "
        /* Custom styles for WooCommerce Orders widget */
        #wc_order_widget_id {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
        }

        #wc_order_widget_id table {
            width: 100%;
            border-collapse: collapse;
        }

        #wc_order_widget_id th, #wc_order_widget_id td {
            padding: 5px;
            border: 1px solid #ddd;
        }

        #wc_order_widget_id th {
            background-color: #f2f2f2;
            text-align: center;
        }

        #wc_order_widget_id .pagination {
            margin-top: 10px;
            text-align: center;
        }

        #wc_order_widget_id .pagination a {
            display: inline-block;
            padding: 5px;
            border: 1px solid #ddd;
            margin: 0 5px;
        }

        #wc_order_widget_id .pagination .current {
            font-weight: bold;
            background-color: #f2f2f2;
            color: #000;
            border-color: #f2f2f2;
        }
		#wc_order_widget_id .pagination .page-numbers {
		text-align:center;
		font-size:16p;
		font-weight:700;
		}
	    #wc_order_widget_id .pagination .page-numbers li {
        display:inline-block;
		}
    ";
    wp_add_inline_style( 'wp-admin', $css );
}
add_action( 'admin_enqueue_scripts', 'wc_orders_dashboard_widget_css' );
}
?>

