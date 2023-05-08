<?php

$show_reupholstery = current_user_can('edit_posts') ? true : get_field('show_reupholstery','option');
if (!$show_reupholstery) die();

the_post();
get_header();
$top_navigation_color_theme = 'white';
include('section-top-navigation.php');

//info - left column
$title = get_the_title();
$description = get_field('description');
$process = get_field('description');

//images and quotes - right column
$iq_items = get_furniture_images_and_quotes($post);
$first_image = get_furniture_first_image_from_iq_items($iq_items);

//related
$related = get_field('related_projects');
?>

<section class="single-upholstery"><div>

    <div class="info_and_images">

      <div class="info">

        <nav class="section-nav">
          <div>
          <span>Reupholstery</span> &gt; <span><?=$title ?></span>
          </div>
        </nav>

        <div class="first-image">
          <?php the_acf_image(array('image'=>$first_image,'pin'=>true)); ?>
        </div>

        <div class="title">
          <h1><?=$title ?></h1>
        </div>

        <?php if ($description) { ?>
        <div class="description">
          <?=$description ?>
        </div>
        <?php  } ?>

        <?php if ($process) { ?>
        <div class="process attribute-value">
          <div class="attribute">Process</div>
          <div class="value"><?=$process ?></div>
        </div>
        <?php  } ?>

        <div class="contact">
          <a class="contact show-inquire-link" href="/contact">Inquire About Reupholstery</a>
        </div>
        <div class="finishes">
          <a href="/wood-finishes">View Wood Finish Options</a>
        </div>

      </div>

      <div class="images_and_quotes">
        <?php
        $first_image_not_shown = true;
        foreach ($iq_items as $i) {
          if ($i['image']) {
            ?>
            <div class="image <?=( $first_image_not_shown ? 'first-image' : '' ) ?>">
              <?php the_acf_image(array('image'=>$i['image'],'pin'=>true)); ?>
            </div>
            <?php
            $first_image_not_shown = false;
          }
          if ($i['quote']) {
            ?>
            <div class="quote-and-author">
              <div class="quote">&ldquo;<?=$i['quote'] ?>&rdquo;</div>
              <? if ($i['quote_author']) { ?>
              <div class="author">&mdash; <?=$i['quote_author'] ?></div>
              <? } ?>
            </div>
            <?php
          }
        }
        ?>
      </div>

    </div>

</div></section>

<section class="related"><div>
  <h3>Explore More Projects</h3>
</div></section>

<?php
include('section-commitment.php');
include('section-location.php');
include('section-case-study-example.php');
get_footer();
?>
