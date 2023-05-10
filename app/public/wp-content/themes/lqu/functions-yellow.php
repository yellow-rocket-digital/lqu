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

?>


