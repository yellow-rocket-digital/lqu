<?php

function the_locations_grid($args) {
  $showrooms_only = ( isset($args['showrooms_only']) ? $args['showrooms_only'] : false);
  $class = ( isset($args['class']) ? $args['class'] : 'locations');
  $locations = get_field('locations','option');
  ?>
  <div class="<?=$class ?>">
  <?php
  foreach ($locations as $i) {
    $image = $i['image'];
    $title = $i['title'];
    $address = $i['address'];
    $phone = $i['phone'];
    $text = $i['text'];
    $showroom = $i['showroom'];
    if ( (!$showrooms_only) or ($showrooms_only and $showroom) ) {
      ?>
        <div class="location">
          <div>
            <div class="image">
              <?php the_acf_image(array('image'=>$image,'size'=>'large')); ?>
            </div>
            <div class="title">
              <?=$i['title'] ?>
            </div>
            <div class="address">
              <?=nl2br($i['address']) ?>
            </div>
            <div class="phone">
              <?=$i['phone'] ?>
            </div>
            <div class="text">
              <?=$i['text'] ?>
            </div>
          </div>
        </div>
      <?php
    }
  }
  ?>
  </div>
  <?php
}

function the_case_studies_grid($cases) {
  ?>
  <div class="case-studies grid">
    <?php
    //for ($jj=1; $jj<=5; $jj++) //for testing
    foreach($cases as $p) {
      $title = get_the_title($p);
      $image = get_field('image',$p); //hero image
      $product_rows = get_field('products',$p); //hero image products
      $products = array();
      if ($product_rows) foreach($product_rows as $product_row) {
        if ($product_row['product']) {
          $products[] = $product_row['product'];
        }
      }
      ?>
      <a
        id="<?=$p->post_name ?>"
        class="
          case-study
          fill-effect
          <?=(isset($_GET['border']) ? 'border-effect' : '') ?>
          <?=(isset($_GET['fill']) ? 'fill-effect' : '') ?>
          <?=(isset($_GET['shadow']) ? 'shadow-effect' : '') ?>
          <?=(isset($_GET['zoom']) ? 'zoom-effect' : '') ?>
        "
        href="<?=get_the_permalink($p) ?>"
      >
        <?php if ($image) { ?>
          <?php the_acf_image(array('image'=>$image, 'size'=>'medium', 'tag'=>'span', 'class'=>'image' )); ?>
        <?php } else { ?>
          <span class="image placeholder"></span>
        <?php } ?>
        <span class="case-hero-text">
          <h4 class="title"><?=get_the_title($p) ?></h4>
          <div class="text">
            <?=get_the_products_caption($products) ?>
            <?php
            $custom_products = get_field('custom_products',$p);
            if ( $custom_products and count($custom_products) > 0 ) {
              foreach ($custom_products as $cp) {
                if ( $cp['attribute'] and $cp['value'] ) {
                  echo '<span class="product-category">'.$cp['attribute'].'</span> ';
                  echo '<span class="product-category-title">'.$cp['value'].'</span> ';
                }
              }
            }
            ?>
          </div>
        </span>
      </a>
      <?php
    }
    ?>
  </div>
  <?php
}


function get_caption_category_title($post) {
  if (!$post) return false;
  // caption category
  $caption_category_title = false;
  $caption_categories = get_the_terms($post,'caption_category');
  if ($caption_categories) {
    // the product has a specific caption category set
    $caption_category = $caption_categories[0];
    $caption_category_title = $caption_category->name;
  } else {
    // Check if a regular category is defined;
    // if it is, use the default caption category for it
    if ($post->post_type == 'furniture') {
      //no categories for re-upholsteries at this time
      $furniture_categories = get_the_terms($post,'furniture_category');
      if ($furniture_categories) {
        $furniture_category = $furniture_categories[0];
        $caption_category_id = get_field('default_caption_category',$furniture_category);
        if ($caption_category_id) {
          $caption_category = get_term($caption_category_id);
          $caption_category_title = $caption_category->name;
        }
      } else {
        // no default caption category -- e.g. for "Chairs and Chaises"
      }
    }
  }
  return $caption_category_title;
}
function get_the_products_caption($posts) {
  $caption_items = array();
  foreach ($posts as $post) {
    if ($post) {
      $caption_part = '';
      //caption category
      $caption_category_title = get_caption_category_title($post);
      if ($caption_category_title) {
        $caption_part .= '<span class="product-category">'.$caption_category_title.'</span>'.' ';
      }
      // title
      $title = get_the_title($post);
      $caption_part .= '<span class="product-title">'.$title.'</span>';
      $caption_items[] = $caption_part;
    }
  }
  return implode(', ',$caption_items);
}

function get_furniture_categories() {
  return $furniture_categories = get_terms( array(
    'taxonomy' => 'furniture_category',
    'orderby' => 'name',
    'order' => 'ASC',
    'hide_empty' => true,
  ));
}

function get_furniture_images_and_quotes($post) {
  $iq_items = array(); //images and quotes
  $image_source = get_field('image_source',$post);
  $gallery_images = get_field('images',$post);
  $quote = get_field('quote',$post);
  $quote_author = get_field('quote_author',$post);
  $images_and_quotes = get_field('images_and_quotes',$post);
  if ($image_source == 'gallery' or $image_source == 'gallery_and_quote') {
    $i = 0;
    foreach ($gallery_images as $image) {
      if ($i == 1 and $image_source == 'gallery_and_quote') {
        //add quote to second image
        $iq_items[] = array(
          'image'=>$image,
          'quote'=>$quote,
          'quote_author'=>$quote_author,
        );
      } else {
        $iq_items[] = array(
          'image'=>$image,
          'quote'=>false,
          'quote_author'=>false,
        );
      }
      $i++;
    }
  } elseif ($image_source = 'gallery_and_quotes') {
    if ($images_and_quotes) {
      foreach ($images_and_quotes as $i) {
        if ($i['image'] or $i['quote']) {
          $iq_items[] = array(
            'image'=>$i['image'],
            'quote'=>$i['quote'],
            'quote_author'=>$i['quote_author'],
          );
        }
      }
    } else {
      // No images and quotes given;
    }
  }
  return $iq_items;
}

function get_furniture_first_image_from_iq_items($iq_items) {
  foreach($iq_items as $iq_item) {
    if ( isset($iq_item['image']) and $iq_item['image']) {
      return $iq_item['image'];
    }
  }
  return false;
}

function get_furniture_first_image_from_post($post) {
  return get_furniture_first_image_from_iq_items(
    get_furniture_images_and_quotes($post)
  );
}

function get_furniture_image_zip_link($post,$iq_items) {
  $image_archive_link = false;
  $image_archive_source = get_field('image_archive_source',$post);
  if ($image_archive_source == 'file') {
    $image_archive_link_object = get_field('image_archive',$post);
    $image_archive_link = $image_archive_link_object['url'];
  } elseif ($image_archive_source == 'generate') {
    $image_archive_link = '';
    $image_ids = array();
    $image_file_paths = array();
    foreach ($iq_items as $iq_item) {
      if ( $iq_item['image'] ) {
        $image_ids[] = $iq_item['image']['id'];
        $image_file_paths[] = get_attached_file($iq_item['image']['id']);
      }
    }
    $image_archive_link =
      get_template_directory_uri().
      '/lquzip.php?is='
      .implode(',',$image_ids)
      .'&p='.get_the_id($post)
      .'&c='
      .sha1(
        implode(',',$image_ids)
        .implode(',',$image_file_paths)
        .'1L1Dqdhw0w0zha-VqjaaaiCLnJim1YLJrU3fKZf61uR'
        .get_the_title($post).$post->ID.$post->post_modified.$post->post_name
      );
  }
  return $image_archive_link;
}

?>


