<?php
  $image = get_field('feature-case-study-image','option');
  $text = get_field('feature-case-study-text','option');
  $cta_text = 'View our Case Studies';
  $cta_link = '/case-studies';
?>

<section class="case-study-example"><div>
  <?php the_acf_image(array('image'=>$image,'div'=>true,'class'=>'image')); ?>
  <div class="text-and-cta">
    <div class="text"><?=$text ?></div>
    <div class="cta"><a href="<?=$cta_link ?>"><?=$cta_text ?></a></div>
  </div>
</div></section>
