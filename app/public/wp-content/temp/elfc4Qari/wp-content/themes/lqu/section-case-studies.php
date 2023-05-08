<?php
$title = get_the_title();
$introduction = get_field('introduction');
get_posts()
?>
<section class="case-studies"><div>

  <?php
  /*
  <!-- Section Navigation -->
  <!--
  <nav class="section-nav">
    <span><?=$title ?></span>
  </nav>
  -->
  */
  ?>

  <h1><?=$introduction ?></h1>

  <?php
  $cases = get_posts(array(
    'posts_per_page' => -1,
    'post_type' => 'case',
    'orderby' => 'menu_order',
    'order' => 'ASC',
  ));
  the_case_studies_grid($cases)
  ?>


</div></section>
