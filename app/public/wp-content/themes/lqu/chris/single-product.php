<?php
get_header();
echo '<h1>TESTING: This is /chris/single-product.php<h1>';
include(__DIR__ . "/../section-top-navigation.php");
?>
<section class="single-product">
  <div>
    <?php the_content() ?>
  </div>
</section>

<?php
get_footer();
?>
