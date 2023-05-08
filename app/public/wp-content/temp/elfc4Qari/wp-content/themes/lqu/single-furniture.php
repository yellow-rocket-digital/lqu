<?php
the_post();
get_header();
$top_navigation_color_theme = 'white';
include('section-top-navigation.php');

//info - left column
$title = get_the_title();
$available = get_field('available');
$description = get_field('description');
$com_yardage = get_field('com_yardage');
$image_archive = get_field('image_archive');
$categories = get_the_terms($post,'furniture_category');
if ($categories) $category = $categories[0];

//images and quotes - right column
$iq_items = get_furniture_images_and_quotes($post);
$first_image = get_furniture_first_image_from_iq_items($iq_items);

// Image archive link -- see themes/lqu/lquzip.php
$image_archive_link = get_furniture_image_zip_link($post,$iq_items);

// Type of furniture -- for captions and related
$plural_type_name = '';
$furniture_types = get_the_terms($post,'caption_category'); //type
if ($furniture_types) {
  $furniture_type = array_pop($furniture_types);
  if ($furniture_type) {
    $plural_type_name = get_field('plural_type_name',$furniture_type);
    if (!$plural_type_name) $plural_type_name = $furniture_type->name.'s';
    $plural_type_name = ucfirst($plural_type_name);
  }
}

// Similar Furniture - bottom
$similar_source = get_field('similar_source');
$similar = array();
$similar_limit = null;
if ($similar_source == 'specify') {
  $similar = get_field('similar_furniture');
} elseif ($similar_source == 'type') {
  $similar = get_posts(array(
    'posts_per_page' => -1,
    'post_type' => 'furniture',
    'tax_query' => array(
      array(
        'taxonomy' => 'caption_category',
        'field' => 'term_id',
        'terms' => $furniture_type->term_id,
      )
    ),
    'exclude' => $post->ID,
  ));
  $similar_limit = get_field('similar_limit');
}

// Purchase
$show_order_online_button = trim(get_field('show_order_online_button'));
$paypal_link = $show_order_online_button ? trim(get_field('paypal_link')) : false;

?>


<section class="single-furniture"><div>

    <div class="info_and_images">

      <div class="info">

        <nav class="section-nav">
          <div>
          <span>Custom Furniture</span>
          <?php if ($categories) { ?>
            &gt;
            <span>
              <a href="<?=get_term_link($category) ?>">
                <?=$category->name ?>
              </a>
            </span>
          <?php } ?>
            &gt;
            <span><?=$title ?></span>
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

        <div class="dimensions attribute-value">
          <div class="attribute">Dimensions</div>
          <div class="value">All pieces are made to order, frame to fabric so the dimensions may be determined by the customer.</div>
          <?php if ($available) { ?>
            <div class="value available">This frame is in stock and has a short lead time.</div><?php // OLD TEXT: Given the popularity of this design, this frame is in stock and has a shorter lead time. ?>
          <? } ?>

        </div>

        <?php if ($com_yardage) { ?>
        <div class="fabric attribute-value">
          <div class="attribute">Fabric</div>
          <div class="value">
            <?php /*
            <div class="yardage">COM Yardage: <?=$com_yardage ?></div>
            */ ?>
            <div class="fabric-note">All fabrics are supplied by the customer. Yardage requirements are supplied in estimates once the design is specified.</div>
          </div>
        </div>
        <?php  } ?>

        <div class="order">
          <?php if ($paypal_link) { ?>
            <a href="<?=$paypal_link ?>" target="_blank">Order Online</a>
          <?php } else { ?>
            <a href="/contact" class="show-inquire-link">Start Order</a>
          <?php } ?>
        </div>
        <div class="finishes">
          <a href="/wood-finishes">View Wood Finish Options</a>
        </div>

        <?php if ($image_archive_link) { ?>
        <div class="download">
          <a class="download-images" href="<?=$image_archive_link ?>" target="_blank" >Download Images</a>
        </div>
        <?php } ?>

      </div>

      <div class="images_and_quotes">
        <?php
        $first_image_not_shown = true;
        foreach ($iq_items as $i) {
          if ($i['image']) {
            ?>
            <div class="image <?=( $first_image_not_shown ? 'first-image' : '' ) ?>">
              <?php
              the_acf_image(array(
                'image'=>$i['image'],
                'pin'=>true,
              ));
              ?>
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


<?php if ($similar) { ?>
  <section class="similar-types-of-furniture"><div>
    <h3>Explore More <?=$plural_type_name ?></h3>
    <div class="similar-grid <?=$similar_limit ? 'limited' : '' ?>" <?=$similar_limit ? 'data-similar-limit="'.$similar_limit.'"' : '' ?>>
      <?php
      foreach ($similar as $p) {
        $product_image = get_furniture_first_image_from_post($p);
        $product_title = get_the_title($p);
        $product_link = get_the_permalink($p);
        ?>
        <a href="<?=$product_link ?>">
          <?php if ($product_image) { ?>
            <?php the_acf_image(array('image'=>$product_image, 'size'=>'medium', 'tag'=>'span', 'class'=>'image' )); ?>
          <?php } else { ?>
            <span class="image placeholder"></span>
          <?php } ?>
          <span class="text">
            <?=$product_title ?>
          </span>
        </a>
        <?php
      } //end each
      ?>
    </div>
  </div></section>
<? } ?>

<?php
include('section-commitment.php');
include('section-location.php');
include('section-case-study-example.php');
get_footer();
?>
