<?php

// For the furniture grid
$term = get_queried_object();
$term_name = $term->name;
$term_id = $term->term_id;
$furniture = get_posts(array(
  'posts_per_page' => -1,
  'post_type' => 'furniture',
  'tax_query' => array(
    array(
      'taxonomy' => 'furniture_category',
      'field' => 'term_id',
      'terms' => $term_id,
    )
  ),
  'orderby' => 'title',
  'order' => 'ASC'
));

// For "explore more"
$furniture_categories = get_furniture_categories();

?>

<section class="category-furniture"><div>

  <!-- Section Navigation -->
  <nav class="section-nav">
    <span>Custom Furniture</span> &gt; <span><?=$term_name ?></span>
  </nav>

  <!-- Category Title -->
  <h3 class="category-title"><?=$term_name ?></h3>

  <!-- Furniture -->
  <div class="furniture grid">
    <?php
    foreach($furniture as $f) {
      $title = get_the_title($f);
      $image = get_furniture_first_image_from_post($f);
      $new_badge = get_field('new',$f);
      $available = get_field('available',$f);
      ?>
      <a href="<?=get_the_permalink($f) ?>">
        <?php if ($new_badge or $available) { ?>
          <span class="badges">
            <?php if ($new_badge) { ?>
              <span class="lqu-circle-badge"><span>NEW</span></span>
            <? } ?>
            <?php if ($available) { ?>
              <span class="lqu-circle-badge"><span>NOW</span></span>
            <? } ?>
          </span>
        <?php } ?>

        <?php if ($image) { ?>
          <?php the_acf_image(array('image'=>$image, 'size'=>'medium', 'tag'=>'span', 'class'=>'image' )); ?>
        <?php } else { ?>
          <span class="image placeholder"></span>
        <?php } ?>
        <span class="text">
          <span><?=get_the_title($f) ?></span>
          <?php if ($available) { ?>
            <span class="available">Available Now</span>
          <? } ?>
          <?php if ( current_user_can('edit_post', $f) ) { ?>
            <span class="edit-link" onclick="event.preventDefault(); location.href='<?=get_edit_post_link($f); ?>';"><span>Edit</span></span>
          <? } ?>
        </span>
      </a>
      <?php
    }
    ?>
  </div>
  <div class="grid-bottom-border"></div>

  <!-- Explore more categories-->
  <h3 class="explore-more">Explore More</h3>

  <div class="explore grid">
    <?php
    foreach($furniture_categories as $c) {
      //echo $c->term_id;
      if ($c->term_id !== $term_id) {
        $image = get_field('image',$c); //category image
        ?>
        <a href="<?=get_term_link($c) ?>">
          <?php if ($image) { ?>
            <?php the_acf_image(array('image'=>$image, 'size'=>'medium', 'tag'=>'span', 'class'=>'image' )); ?>
          <?php } else { ?>
            <span class="image placeholder"></span>
          <?php } ?>
          <span class="text">
            <?=$c->name ?>
          </span>
        </a>
        <?php
      }
    }
    ?>
  </div>

</div></section>
