<?php
  $location_introduction = get_field('location_introduction','option');
?>
<section class="locations">
  <div>
    <div class="introduction">
      <h1><?=$location_introduction ?></h1>
      <div class="cta">
        <a href="/contact">Visit Us</a>
      </div>
    </div>
    <?php
    the_locations_grid( array(
      'class' => 'locations'
    ));
    ?>
  </div>
</section>
