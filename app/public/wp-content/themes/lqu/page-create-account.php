<?php
  /*
   * Template name: Create Account Page
   */
?>

<?php
the_post();
get_header();
$top_navigation_color_theme = 'tan';
include('section-top-navigation.php');

echo do_shortcode('[wpforms id="2244" title="false"]');
get_footer();
?>
