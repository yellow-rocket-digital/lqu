<?php
ini_set('display_errors',0);
error_reporting(E_ALL|E_STRICT);
//error_reporting(E_ALL & ~E_NOTICE);

// Add SVG Capability to Media Library
// function allow_mime_types($mimes) { $mimes['svg'] = 'image/svg+xml'; return $mimes; }
// add_filter('upload_mimes', 'allow_mime_types');

// Fonts and Styles
function lqu_style() {
  wp_enqueue_style('lqu-fonts', get_stylesheet_directory_uri().'/fonts/fonts.css');
  if( !is_admin() ){
    // Front End
    wp_enqueue_style('lqu-style', get_stylesheet_directory_uri().'/styles/style.css');
    wp_enqueue_style('font-awesome', '//use.fontawesome.com/releases/v5.2.0/css/all.css?ver=5.4.2');
  }
}
add_action('init', 'lqu_style');

// Scripts
function lqu_scripts() {
  // Javscript
  if( !is_admin() ){
    wp_enqueue_script( 'jquery' );
  }
  if ( current_user_can('administrator') ) {
    // if( !is_admin() ){ wp_enqueue_script( 'kc-css-reload', get_template_directory_uri() . '/scripts/kc-css-reload.js'); }
  }
  wp_enqueue_script( 'lqu-js-init', get_template_directory_uri() . '/scripts/init.js');
  wp_enqueue_script( 'lqu-pinterest', '//assets.pinterest.com/js/pinit.js', 'lqu-js-init', false, true); //in footer instead of async defer
  // wp_enqueue_script( 'vimeo', '//player.vimeo.com/api/player.js'); // https://developer.vimeo.com/player/sdk

}
add_action('init', 'lqu_scripts');


// Breaking it up!
require_once('functions-register-posts.php');       // register custom posts
require_once('functions-site-options.php');         // ACF Site Options
require_once('functions-acf-simple-editor.php');    //italics only option
require_once('functions-cleanup.php');              // remove unwanted wordpress stuff
require_once('functions-block-editor.php');         // gutenberg
require_once('functions-lqu.php');                  // lqu helpers
require_once('the-contact-form.php');               // contact form helper
require_once('functions-social-meta.php');          // lqu social metadata for fb and twitter
require_once('functions-acf-helpers.php');          // the_acf_image()

// Allow editors to edit privacy policy page
// https://wordpress.stackexchange.com/questions/318666/how-to-allow-editor-to-edit-privacy-page-settings-only
function custom_manage_privacy_options($caps, $cap, $user_id, $args) {
  if (!is_user_logged_in()) return $caps;
  $user_meta = get_userdata($user_id);
  if (array_intersect(['editor', 'administrator'], $user_meta->roles)) {
    if ('manage_privacy_options' === $cap) {
      $manage_name = is_multisite() ? 'manage_network' : 'manage_options';
      $caps = array_diff($caps, [ $manage_name ]);
    }
  }
  return $caps;
}
if (is_user_logged_in()) {
  add_action('map_meta_cap', 'custom_manage_privacy_options', 1, 4);
}

//require_once('chris/functions-chris.php');
require_once('functions-yellow.php');


//require_once('functions-tinymce.php');        // editor customization
//require_once('acf-blocks/register.php');      // register ACF blocks
//require_once('functions-site-settings.php');  // site settings page
//require_once('functions-blog.php');
//require_once('functions-classes.php');
//require_once('functions-person.php');
//require_once('functions-manage-table-columns.php');
// require_once('functions-person.php');  // person settings page
