<?php
//https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/summary-card-with-large-image

function the_social_meta($post) {
  $title = get_the_title($post);
  $permalink = get_the_permalink($post);
  $description = false;
  $image = false;
  if (get_post_type() == 'furniture') {
    $description = get_field('description');
    $image = get_furniture_first_image_from_post($post);
    if ($image) {
      $image = $image['sizes']['large'];
    } else {
      $image = false;
    }
  } else {
    $image = false;
  }
  $type = ucfirst(get_post_type($post));
  if ($title) {
    echo '<meta property="og:title" content="'.$title.'" />';
    echo '<meta property="twitter:title" content="'.$title.'" />';
  }
  if ($description) {
    echo '<meta property="og:description" content="'.$description.'" />';
    echo '<meta property="twitter:description" content="'.$description.'" />';
  }
  if ($image) {
    echo '<meta property="og:image" content="'.$image.'" />';
    echo '<meta property="twitter:image" content="'.$image.'" />';
    echo '<meta property="twitter:card" content="summary_large_image" />';
  }
  if ($permalink) {
    echo '<meta property="og:url" content="'.$permalink.'" />';
  }
  if ($type) {
    echo '<meta property="og:type" content="'.$type.'" />';
  }
}

?>
