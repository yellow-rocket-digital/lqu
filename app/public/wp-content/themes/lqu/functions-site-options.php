<?php
if( function_exists('acf_add_options_page') ) {

	acf_add_options_page(array(
		'page_title' 	=> 'Commitment to Craftsmanship',
		'menu_title'	=> 'Commitment to Craftsmanship',
		'menu_slug' 	=> 'commitment-to-craftsmanship-options',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));

	acf_add_options_page(array(
		'page_title' 	=> 'Locations',
		'menu_title'	=> 'Locations',
		'menu_slug' 	=> 'location-options',
		'capability'	=> 'edit_posts',
		'icon_url'		=> 'dashicons-building',
		'redirect'		=> false
	));

	acf_add_options_page(array(
		'page_title' 	=> 'Case Study Feature',
		'menu_title'	=> 'Case Study Feature',
		'menu_slug' 	=> 'case-study-feature',
		'capability'	=> 'edit_posts',
		'icon_url'		=> 'dashicons-star-filled',
		'redirect'		=> false
	));

	acf_add_options_page(array(
		'page_title' 	=> 'Footer',
		'menu_title'	=> 'Footer',
		'menu_slug' 	=> 'footer-options',
		'capability'	=> 'edit_posts',
		'icon_url'		=> 'dashicons-media-text',
		'redirect'		=> false
	));

	acf_add_options_page(array(
		'page_title' 	=> 'Site Options',
		'menu_title'	=> 'Site Options',
		'menu_slug' 	=> 'site-options',
		'capability'	=> 'edit_posts',
		'icon_url'		=> 'dashicons-admin-settings',
		'redirect'		=> false
	));

	acf_add_options_page(array(
		'page_title' 	=> 'Social Media and Websites',
		'menu_title'	=> 'Social Media and Websites',
		'menu_slug' 	=> 'social-media-and-websites',
		'capability'	=> 'edit_posts',
		'icon_url'		=> 'dashicons-networking',
		'redirect'		=> false
	));

  /*
	acf_add_options_sub_page(array(
		'page_title' 	=> 'Theme Header Settings',
		'menu_title'	=> 'Header',
		'parent_slug'	=> 'theme-general-settings',
	));

	acf_add_options_sub_page(array(
		'page_title' 	=> 'Theme Footer Settings',
		'menu_title'	=> 'Footer',
		'parent_slug'	=> 'theme-general-settings',
	));
  */

}
?>
