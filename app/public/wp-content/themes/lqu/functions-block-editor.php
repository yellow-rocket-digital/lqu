<?php
// Gutenberg Aligment Support
add_theme_support( 'align-wide' ); //effects align full an wide, cannot get more granular

// Gutenberg Editor Styles
function lqu_editor_style(){
  // Support editor styles
	add_theme_support('editor-styles');
  add_editor_style('styles/style-editor.css'); // Careful, do not use get_stylesheet_directory_uri here!
}
add_action( 'after_setup_theme', 'lqu_editor_style' );

// Selectively Disable Gutenburg
// See https://developer.wordpress.org/reference/hooks/use_block_editor_for_post/
// and also see https://digwp.com/2018/12/enable-gutenberg-block-editor/
function selectively_use_block_editor($use_block_editor, $post) {

	// Just the homepage
  if ( isset($post->ID) ) {
    if ( $post->ID == get_option('page_on_front') ) {
      return false;
    }
  }

  // No Block Editor on Any Pages
  if ( get_post_type($post) == 'page' ) {
    return false;
  }

  return $use_block_editor; //by default, show the editor
}
add_filter('use_block_editor_for_post', 'selectively_use_block_editor', 10,2);
?>
