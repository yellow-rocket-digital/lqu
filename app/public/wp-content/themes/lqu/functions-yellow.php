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

?>


