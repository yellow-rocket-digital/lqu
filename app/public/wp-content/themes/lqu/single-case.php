<?php
the_post();
get_header();
//$top_navigation_color_theme = 'white';
include('section-top-navigation.php');
?>

<?php
$title = get_the_title();
$introduction = get_field('introduction');
$quote = get_field('quote');
$quote_author = get_field('quote_author');
$layouts = get_field('layouts');
?>

<!-- Case Study Header -->
<section class="case-study-header"><div>
  <nav class="section-nav">
    <div>
      <span><a href="/case-studies">Case Studies</a></span>
      &gt;
      <span><?=$title ?></span>
    </div>
  </nav>
  <div class="content">
    <h1><?=$title ?></h1>
    <?php
    echo ($introduction ? '<div class="introduction">'.$introduction.'</div>' : '');
    echo ($quote ? '<div class="quote">&ldquo;'.$quote.'&rdquo;</div>' : '');
    echo ( ($quote and $quote_author) ? '<div class="quote_author">&mdash; '.$quote_author.'</div>' : '');
    ?>
  </div>
</div></section>

<!-- Case Study Images and Quotes -->
<?php
if ($layouts) {
  ?>
  <section class="case-study-images-and-quotes"><div>
    <?php
    foreach ($layouts as $layout) {
      ?>
      <div class="layout">

        <?php
        // 1a. Photo and/or Quote
        $photo = $layout['photo'];
        $quote = $layout['quote'];
        $quote_author = $layout['quote_author'];
        // 1b. Photo Credit
        $photo_credit = $layout['photo_credit'];
        $photo_products = $layout['photo_products']; //furniture product or reupholstery project
        // 2. Products
        if ( $photo or $quote ) { //photo and/or quote required

          ?>
          <div class="photo-and-quote-and-credit  <?=($quote ? 'has-quote' : '') ?>">
            <?php

            // 1a. Photo and/or Quote
            ?>
            <div class="photo-and-quote" >
              <?php
              // i. Photo
              if ($photo) {
                echo '<div class="photo">';
                //the_acf_image($photo);
                the_acf_image(array('image'=>$photo,'pin'=>true));
                echo '</div>';
              }
              // ii. Quote
              if ($quote) {
                echo '<div class="quote-and-author">';
                echo ($quote ? '<div class="quote">&ldquo;'.$quote.'&rdquo;</div>' : '');
                echo ($quote_author ? '<div class="author">&mdash; '.$quote_author.'</div>' : '');
                echo '</div>';
              }
              ?>
            </div>
            <?php

            // 1b. Photo Credit -- always below both photo and quote
            if ($photo_credit) {
              ?>
              <div class="photo-credit">
                Photo: <?=$photo_credit ?>
              </div>
              <?
            }

            ?>
          </div>
          <?php

          // 2. Photo products -- optional products in images
          if ($photo_products) {
            ?>
            <div class="photo-products">
              <h3>Featured Above</h3>
              <div class="photo-products-grid">
                <?php
                foreach ($photo_products as $row) {
                  $p = $row['product'];
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
            </div>
            <?php
          } // End Photo Products
        }
        ?>
      </div>
      <?php
    } //end each section
    ?>
  </div></section>
  <?php
}
?>


<section class="case-studies related"><div>
  <h3>Additional Case Studies</h3>
  <?php
  // set the post to the case, and find the previous and next case
  // then, reset
  global $post;
  $this_post = $post;
  $post = get_post($this_post);
  $next_post = get_previous_post(); //NOTE: reversed b/c of plugin
  $previous_post = get_next_post();
  $post = $this_post;
  if (!$previous_post) {
    $previous_post_r = get_posts(array(
      'posts_per_page' => 1,
      'post_type' => 'case',
      'orderby' => 'menu_order',
      'order' => 'DESC',
    ));
    $previous_post = $previous_post_r[0];
  }
  if (!$next_post) {
    $next_post_r = get_posts(array(
      'posts_per_page' => 1,
      'post_type' => 'case',
      'orderby' => 'menu_order',
      'order' => 'ASC',
    ));
    $next_post = $next_post_r[0];
  }
  $cases = array($previous_post, $next_post);
  the_case_studies_grid($cases);
  ?>
</div></section>


<?php
include('section-commitment.php');
include('section-location.php');
include('section-case-study-example.php');
get_footer();
?>
