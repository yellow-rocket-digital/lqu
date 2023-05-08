<?
/*----------------------------------------------------------------------------*/
// Remove Menu Items for Admin Menu and Admin Bar */
/*----------------------------------------------------------------------------*/
// Remove Admin Sidebar Menu Items
function remove_admin_menus() {
  remove_menu_page( 'edit-comments.php' );
  remove_menu_page( 'edit.php' );
}
add_action( 'admin_menu', 'remove_admin_menus' );

// Remove Top Admin Bar Items
function change_toolbar($wp_admin_bar) {
  //print_r($wp_admin_bar); die();
	$wp_admin_bar->remove_node('wp-logo');
	$wp_admin_bar->remove_node('comments');
  $wp_admin_bar->remove_node('customize');
  // remove new post, page, user
  $wp_admin_bar->remove_node('new-post');
  $wp_admin_bar->remove_node('new-page');
  $wp_admin_bar->remove_node('new-user');
  // make site name shorter
  $site_name_node = $wp_admin_bar->get_node('site-name');
  $site_name_node->title = 'LQU';
  $wp_admin_bar->add_node($site_name_node);
  // change new content to new furniture
  $new_content_node = $wp_admin_bar->get_node('new-content');
  $new_content_node->href = admin_url( 'post-new.php?post_type=furniture' );
  $wp_admin_bar->add_node($new_content_node);
  // remove howdy
  $my_account = $wp_admin_bar->get_node('my-account');
  $new_howdy = str_replace('Howdy,','',$my_account->title);
  $wp_admin_bar->add_node(array('id' => 'my-account','title' => $new_howdy));
}
add_action('admin_bar_menu', 'change_toolbar', 999);

/*----------------------------------------------------------------------------*/
// Disable Admin Editing */
/*----------------------------------------------------------------------------*/
@define( 'DISALLOW_FILE_EDIT', true ); // disable file editing from admin

/*----------------------------------------------------------------------------*/
/* Remove Dashboard Widgets */
/*----------------------------------------------------------------------------*/
remove_action( 'welcome_panel', 'wp_welcome_panel' );
function remove_dashboard_widgets() {
  remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' ); //Drafts
  remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' ); // News
  remove_meta_box( 'wpe_dify_news_feed', 'dashboard', 'side' ); // WP Engine Has Your Back
  remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' ); // Acitivity, including recent comments
  remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' ); //At a Glance
  remove_meta_box( 'dashboard_primary', 'dashboard', 'side');// Remove WordPress Events and News
  //remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' ); //Site Health
}
add_action('wp_dashboard_setup', 'remove_dashboard_widgets' );

/*----------------------------------------------------------------------------*/
/* Customize Footer */
/*----------------------------------------------------------------------------*/
function update_footer_admin () {
  echo '<span id="footer-thankyou">
    Website development by <a href="https://kingcow.com" target="_blank">King Cow Interactive, LLC</a>
    </span>';
}
add_filter('admin_footer_text', 'update_footer_admin');

/*----------------------------------------------------------------------------*/
// Remove Edit Metaboxes */
// Use ACF to remove metaboxes to avoid PHP notice with classic editor */
/*----------------------------------------------------------------------------*/
/*
function remove_post_meta_boxes() {
	// DO NOT REMOVE SLUGDIV!!! IT BREAKS STUFF IF YOU DO
	// posts
	remove_meta_box( 'postcustom' , 'post' , 'normal' );
	remove_meta_box( 'commentsdiv' , 'post' , 'normal' );
	remove_meta_box( 'trackbacksdiv' , 'post' , 'normal' );
	remove_meta_box( 'commentstatusdiv' , 'post' , 'normal' );
	//remove_meta_box( 'authordiv' , 'post' , 'normal' );
	remove_meta_box( 'postexcerpt' , 'post' , 'normal' );
	// pages
	// remove_meta_box( 'postcustom' , 'page' , 'normal' );
	remove_meta_box( 'commentsdiv' , 'page' , 'normal' ); // Comments
	remove_meta_box( 'commentstatusdiv' , 'page' , 'normal' ); // Discussion Settings
	remove_meta_box( 'authordiv' , 'page' , 'normal' ); //Author
}
if ( is_admin() ) {
  // https://codex.wordpress.org/Plugin_API/Action_Reference/add_meta_boxes
  // https://developer.wordpress.org/reference/functions/remove_meta_box/
  // add_action( 'add_meta_boxes' , 'remove_post_meta_boxes' );
}
*/
?>
