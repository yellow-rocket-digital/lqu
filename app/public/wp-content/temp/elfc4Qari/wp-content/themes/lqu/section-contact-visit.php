<?php
  //this section uses the locations section css; contact-visit not needed now
  $visit_title = get_field('visit_title');
  $visit_text = get_field('visit_text');
?>

<a name="visit"></a>
<section class="locations"><div>
  <div class="introduction">
    <?php
      echo ($visit_title ? '<h1>'.$visit_title.'</h1>' : '');
      echo ($visit_text ? '<div>'.$visit_text.'</div>' : '');
    ?>
  </div>
  <?php
  the_locations_grid(array(
    'class'=>'locations showroom',
    'showrooms_only'=>true,
  ));
  ?>
</div></section>
