<?php
$title = get_the_title();
$introduction = get_field('introduction');
$finishes = get_posts(array(
  'posts_per_page' => -1,
  'post_type' => 'finish',
  'orderby' => 'title',
  'order' => 'DESC',
));
?>
<section class="finishes"><div>

  <!-- Section Navigation -->
  <nav class="section-nav">
    <span><?=$title ?></span>
  </nav>

  <!-- Title -->
  <h1><?=$title ?></h1>

  <!-- Introduction -->
  <div class="introduction">
    <?=$introduction ?>
  </div>

  <!-- Inquire -->
  <a class="contact show-inquire-link" href="/contact">Request Swatch Samples</a>

  <!-- Grid of Swatches -->
  <div class="wood-finishes grid">
    <?php
    foreach($finishes as $p) {
      $title = get_the_title($p);
      $image_1 = get_field('image_1',$p);
      $image_2 = get_field('image_2',$p); //hover
      ?>
      <span id="<?=$p->post_name ?>" class="finish">
        <?php if ($image_1) { ?>
          <?php the_acf_image(array('image'=>$image_1, 'size'=>'medium', 'tag'=>'span', 'class'=>'image image1' )); ?>
          <?php the_acf_image(array('image'=>$image_2, 'size'=>'medium', 'tag'=>'span', 'class'=>'image image2' )); ?>
        <?php } else { ?>
          <span class="image placeholder"></span>
        <?php } ?>
        <span class="text">
          <?=get_the_title($p) ?>
        </span>
      </span>
      <?php
    }
    ?>
  </div>
  <div class="grid-bottom-border"></div>

</div></section>
