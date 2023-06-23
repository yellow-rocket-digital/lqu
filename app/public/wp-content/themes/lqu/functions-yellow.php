<?php

/**
 * Remove product page tabs
 */
add_filter( 'woocommerce_product_tabs', 'remove_all_product_tabs', 98 );
 
function remove_all_product_tabs( $tabs ) {
  unset( $tabs['description'] );        // Remove the description tab
  unset( $tabs['reviews'] );       // Remove the reviews tab
  unset( $tabs['additional_information'] );    // Remove the additional information tab
  return $tabs;
}

/* Remove content from product page(s) */
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

/* Rearragne product page(s) */
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);

/* Remove woocommerce aesthetic styles */
add_filter( 'woocommerce_enqueue_styles', 'remove_woo_styles' );
function remove_woo_styles( $enqueue_styles ) {
	unset( $enqueue_styles['woocommerce-general'] );
	return $enqueue_styles;
}

// Remove account links
add_filter('woocommerce_account_menu_items', 'remove_my_account_links');
function remove_my_account_links($menu_links) {
	unset($menu_links['downloads']);
	unset($menu_links['edit-address']);
	unset($menu_links['dashboard']);

	return $menu_links;
}

// user redirects
add_action('template_redirect', 'redirection_function');
function redirection_function(){
	global $wp;

	$request = explode('/', $wp->request);
	if (end($request) == 'my-account') {
		wp_safe_redirect(home_url('/my-account/quotes'));
		exit;
	}
}

// wp login
function login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
        background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/logo_mobile_with_2px_padding_100x45.svg);
		height:65px;
		width:320px;
		background-size: 320px 65px;
		background-repeat: no-repeat;
        padding-bottom: 30px;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'login_logo' );

function login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'login_logo_url' );

function my_login_logo_url_title() {
    return 'Luther Quintana Upholstery';
}
add_filter( 'login_headertext', 'login_logo_url_title' );


// documents account section
add_action('init', function() {
	add_rewrite_endpoint('documents', EP_PAGES);
});

add_action('woocommerce_account_documents_endpoint', function() {
	wc_get_template('myaccount/documents.php');
});

add_filter('woocommerce_account_menu_items', function($items) {
	$logout = $items['customer-logout'];
	unset($items['customer-logout']);
	$items['documents'] = __('Documents', 'txtdomain');
	$items['customer-logout'] = $logout;
	return $items;
});

?>


