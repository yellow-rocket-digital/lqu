<?php
$furniture_categories = get_furniture_categories();
?>

<section class="category-pathing">
  <?php
  //print_r($furniture_categories);
  ?>
  <div class="inside">

    <div class="image-place"></div>
    <div class="title-and-items">

      <div class="title">Explore our Designs</div>

      <?php foreach($furniture_categories as $c) { ?>
        <div class="item">
          <a href="<?=get_term_link($c) ?>"><?=$c->name ?></a>
          <?php
          $image = get_field('image',$c); //category image
          the_acf_image(array('image'=>$image,'div'=>true,'class'=>'image'));
          ?>
        </div>
      <?php } ?>

    </div>





  </div>
  <div class="clear"></div>
</section>
