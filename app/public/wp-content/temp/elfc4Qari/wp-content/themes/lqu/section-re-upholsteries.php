<?php
$title = get_the_title();
$introduction = get_field('introduction');
$reupholstery_projects = get_posts(array(
  'posts_per_page' => -1,
  'post_type' => 'upholstery',
));
get_posts()
?>
<section class="re-upholsteries"><div>

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
  <a class="contact show-inquire-link" href="/contact">
    Inquire about reupholstery
  </a>

  <!-- Grid of Reupholstery Projects – Similar to Wood Swatches -->
  <div class="grid">
    <?php
    foreach($reupholstery_projects as $p) {
      $title = get_the_title($p);
      $image_1 = get_field('after_image',$p);
      $image_2 = get_field('before_image',$p); //hover
      ?>
      <a id="<?=$p->post_name ?>" class="project" href="<?=get_the_permalink($p) ?>">
        <?php if ($image_1) { ?>
          <?php the_acf_image(array('image'=>$image_1, 'size'=>'medium', 'tag'=>'span', 'class'=>'image image1' )); ?>
          <?php the_acf_image(array('image'=>$image_2, 'size'=>'medium', 'tag'=>'span', 'class'=>'image image2' )); ?>
        <?php } else { ?>
          <span class="image placeholder"></span>
        <?php } ?>
        <span class="text">
          <?=get_the_title($p) ?>
        </span>
      </a>
      <?php
    }
    ?>
  </div>
  <div class="grid-bottom-border"></div>

</div></section>
