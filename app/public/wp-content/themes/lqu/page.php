<?php
get_header();

if (is_account_page()) {
  $top_navigation_color_theme = 'tan';
} else {
$top_navigation_color_theme = 'white';
}

include('section-top-navigation.php');
?>
<section class="generic-page"><div>

  <div class="generic-content">
    <?php
    the_post();
    echo '<h1 class="visually-hidden">'.get_the_title().'</h1>';
    the_content();
    ?>
  </div>


</div></section>
<?php
get_footer();
?>
