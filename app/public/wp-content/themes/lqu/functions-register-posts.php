<?php
/*----------------------------------------------------------------------------*/
/* Register Custom Post Types
/*----------------------------------------------------------------------------*/
//Helper Functions
function get_standard_post_type_labels($args) {
	$plural = $args['plural_name'];
	$singular = $args['singular_name'];
	return array(
		'name'               => __($plural),
		'singular_name'      => __($singular),
		'add_new_item'       => __( 'Add New '.$singular),
		'new_item'           => __( 'New '.$singular),
		'edit_item'          => __( 'Edit '.$singular),
		'view_item'          => __( 'View '.$singular),
		'all_items'          => __( 'All '.$plural),
		'search_items'       => __( 'Search '.$plural),
		'parent_item_colon'  => __( 'Parent '.$plural),
		'not_found'          => __( 'No '.$plural.' found.'),
		'not_found_in_trash' => __( 'No '.$plural.' found in Trash.')
	);
}
function register_website_post_type($args) {
	$supports = array( 'title', 'author', 'page-attributes');
	if (!(is_array($args))) {
		// no args supplied
		$post_type_name = $args;
		$singular_name = ucfirst($post_type_name);
		$plural_name = ucfirst($singular_name.'s');
		$menu_icon = 'dashicons-hammer';
		$hierarchical = false;
	} else {
		$post_type_name = $args['post_type_name'];
		$singular_name = ( isset($args['singular_name']) ? $args['singular_name'] : ucfirst($post_type_name) );
		$plural_name = ( isset($args['plural_name']) ? $args['plural_name'] : ucfirst($singular_name).'s' );
		$menu_icon = ( isset($args['menu_icon']) ? $args['menu_icon'] : 'dashicons-hammer' );
		$hierarchical = ( isset($args['hierarchical']) ? true : false );
		$supports = ( isset($args['supports']) ? $args['supports'] : $supports );
		$taxonomies = ( isset($args['taxonomies']) ? $args['taxonomies'] : array() );
		$show_in_rest = ( isset($args['show_in_rest']) ? $args['show_in_rest'] : false );
	}
	register_post_type( $post_type_name, array(
		  'labels' => get_standard_post_type_labels(array(
			'plural_name' => $plural_name,
			'singular_name' => $singular_name,
		)),
		'public'         		=> true, //default is false
		//'show_ui'        		=> true, //defaults to public value
		//'show_in_menu'   		=> true, //defaults to show_ui value
		'query_var'      		=> true,
		'has_archive'    		=> false,
		'hierarchical'   		=> $hierarchical,
		'menu_position'  		=> null,
		'supports'          => $supports,
		'menu_icon'					=> $menu_icon,
    'capability_type'   => 'post',
		'taxonomies'   => $taxonomies,
		'show_in_rest'   => $show_in_rest,
	));
}


//Register POST TYPES
//Note: remember to use underscores, not dashes, for post_type_names
function register_website_post_types() {
  register_website_post_type(array(
		'post_type_name'=>'furniture',
    'singular_name'=>'Piece of Furniture',
		'plural_name'=>'Furniture',
		'menu_icon'=>'dashicons-products',
		'supports'=>array( 'title', 'author'), //'title', 'editor', 'page-attributes', 'author'
		'taxonomies'=>array('furniture_category', 'caption_category', 'pairings'),
		//'show_in_rest'=>true,
		//'template'=>array(array('zax/single-portfolio-item'))
		//'hierarchical'=>true,
	));
	register_website_post_type(array(
		'post_type_name'=>'upholstery', //remove re_ for better urls
    'singular_name'=>'Reupholstery',
		'plural_name'=>'Reupholsteries',
		'menu_icon'=>'dashicons-hammer',
		'taxonomies'=>array('caption_category', 'pairings'),
		'supports'=>array( 'title', 'page-attributes', 'author'),
	));
	register_website_post_type(array(
		'post_type_name'=>'finish',
    'singular_name'=>'Wood Finish',
		'plural_name'=>'Wood Finishes',
		'menu_icon'=>'dashicons-admin-appearance',
		'supports'=>array( 'title', 'page-attributes', 'author'),
	));
	//unregister_post_type( 'case-study' ); // NO DASHES
	register_website_post_type(array(
		'post_type_name'=>'case',
		'singular_name'=>'Case Study',
		'plural_name'=>'Case Studies',
		'menu_icon'=>'dashicons-portfolio',
		'supports'=>array( 'title', 'page-attributes', 'author'),
	));
	register_website_post_type(array(
		'post_type_name'=>'drapery',
    'singular_name'=>'Drapery',
		'plural_name'=>'Draperies',
		'menu_icon'=>'data:image/svg+xml;base64,'.base64_encode(
			'
			<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
				 viewBox="0 0 20 20" style="enable-background:new 0 0 20 20;" xml:space="preserve">
			<style type="text/css">
				.st0{fill-rule:evenodd;clip-rule:evenodd;}
				.st1{fill:none;}
			</style>
			<circle class="st0" cx="3" cy="3" r="2"/>
			<rect x="3" y="2" transform="matrix(-1 -4.750790e-11 4.750790e-11 -1 23 6)" class="st0" width="17" height="2"/>
			<line class="st1" x1="7" y1="18" x2="6" y2="16"/>
			<rect x="6" y="4" class="st0" width="1" height="1"/>
			<rect x="9" y="4" class="st0" width="1" height="1"/>
			<rect x="12" y="4" class="st0" width="1" height="1"/>
			<rect x="15" y="4" class="st0" width="1" height="1"/>
			<rect x="18" y="4" class="st0" width="1" height="1"/>
			<path class="st0" d="M11.7,20c0.6-2.5,1-5.2,1.2-8C17,11.2,20,8.3,20,5H6.2C5.6,5,5,5.7,5,6.5L5.5,20H11.7z"/>
			</svg>
			'
		),
		'supports'=>array( 'title', 'page-attributes', 'author'),
	));

}
add_action( 'init', 'register_website_post_types' );


// REGISTER TAXONOMIES

// Furniture Categories
$furniture_category_labels = array(
	'name'						=> _x( 'Menu Categories', 'taxonomy general name'),
	'singular_name'				=> _x( 'Menu Category', 'taxonomy singular name'),
	'search_items'				=> __( 'Search Menu Categories ' ),
	'all_items'					=> __( 'All Menu Categories ' ),
	'parent_item'				=> __( 'Parent Menu Category' ),
	'parent_item_colon'			=> __( 'Parent Menu Category:' ),
	'edit_item'					=> __( 'Edit Menu Category' ),
	'update_item'				=> __( 'Update Menu Category' ),
	'add_new_item'				=> __( 'Add New Menu Category' ),
	'new_item_name'				=> __( 'New Menu Category Name' ),
	'search_items'				=> __( 'Search Menu Categories ' ),
	'separate_items_with_commas'=> __( 'Separate menu categories with commas' ),
	'menu_name'					=> __( 'Menu Categories ' ),
);
$furniture_category_args = array(
	'hierarchical'				=> false,
	'labels'					=> $furniture_category_labels,
	'show_ui'					=> true,
	'show_admin_column'			=> true,
	'query_var'					=> true,
	'rewrite'					=> array( 'slug' => 'furniture_category' ),
	'show_in_rest'   => true,
);
register_taxonomy( 'furniture_category', array( 'furniture' ), $furniture_category_args );



// Product Caption Categories (for furniture and upholsteries)
// These captions are used on case study pages to highlight
// specific pieces in photos
// UPDATE: These are now called "Furniture Types" and are used for both captinos and exploring related products
$caption_category_labels = array(
	'name'						=> _x( 'Types', 'taxonomy general name'),
	'singular_name'				=> _x( 'Type', 'taxonomy singular name'),
	'search_items'				=> __( 'Search Types ' ),
	'all_items'					=> __( 'All Types ' ),
	'parent_item'				=> __( 'Parent Type' ),
	'parent_item_colon'			=> __( 'Parent Type:' ),
	'edit_item'					=> __( 'Edit Type' ),
	'update_item'				=> __( 'Update Type' ),
	'add_new_item'				=> __( 'Add New Type' ),
	'new_item_name'				=> __( 'New Type Name' ),
	'search_items'				=> __( 'Search Types ' ),
	'separate_items_with_commas'=> __( 'Separate types with commas' ),
	'menu_name'					=> __( 'Types ' ),
);
$caption_category_args = array(
	'hierarchical'				=> false,
	'labels'					=> $caption_category_labels,
	'show_ui'					=> true,
	'show_admin_column'			=> true,
	'query_var'					=> true,
	'rewrite'					=> array( 'slug' => 'caption_category' ),
	'show_in_rest'   => true,
);
register_taxonomy( 'caption_category', array( 'furniture', 'upholstery' ), $caption_category_args );


/*
// Unused for now

// Furniture Pairings
$furniture_pairing_group_labels = array(
	'name'						=> _x( 'Pairing Groups', 'taxonomy general name'),
	'singular_name'				=> _x( 'Pairing Group', 'taxonomy singular name'),
	'search_items'				=> __( 'Search Pairing Groups ' ),
	'all_items'					=> __( 'All Pairing Groups ' ),
	'parent_item'				=> __( 'Parent Pairing Group' ),
	'parent_item_colon'			=> __( 'Parent Pairing Group:' ),
	'edit_item'					=> __( 'Edit Pairing Group' ),
	'update_item'				=> __( 'Update Pairing Group' ),
	'add_new_item'				=> __( 'Add New Pairing Group' ),
	'new_item_name'				=> __( 'New Pairing Group Name' ),
	'search_items'				=> __( 'Search Pairing Groups ' ),
	'separate_items_with_commas'=> __( 'Separate pairing groups with commas' ),
	'menu_name'					=> __( 'Pairing Groups ' ),
);
$furniture_pairing_group_args = array(
	'hierarchical'				=> false,
	'labels'					=> $furniture_pairing_group_labels,
	'show_ui'					=> true,
	'show_admin_column'			=> true,
	'query_var'					=> true,
	'rewrite'					=> array( 'slug' => 'pairings' ),
	'show_in_rest'   => true,
);
register_taxonomy( 'furniture_pairing', array( 'furniture' ), $furniture_pairing_group_args );
*/


?>
