<?php
get_header();
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
