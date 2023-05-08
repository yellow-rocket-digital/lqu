<?php
function the_acf_image( $args ) {
  if ( isset($args) ) {
    if ( is_array($args) and isset($args['image']) ) {
      $image = $args['image'];
    } else {
      if ( isset($args['type']) and $args['type'] == 'image') {
        $image = $args;
      } else {
        return;
      }
    }
  } else {
    return;
  }
  $class = isset($args['class']) ? $args['class'] : '';
  $size = isset($args['size']) ? $args['size'] : 'large';
  $alt = isset($args['alt']) ? $args['alt'] : $args['image']['alt']; //TODO: problem if not specified
  $div = isset($args['div']) ? $args['div'] : false;
  $pin = isset($args['pin']) ? true : false; //pinterest save button

  $tag = isset($args['tag']) ? $args['tag'] : false;

  if (!$tag and $div) $tag = 'div';

  if ($tag) {
    ?>
    <<?=$tag ?>
      <?php if ($class) echo 'class="'.$class.'"'; ?>
      style="background-image:url('<?=$image['sizes'][$size] ?>')"
      data-img-width="<?=$image['sizes'][$size.'-width'] ?>"
      data-img-height="<?=$image['sizes'][$size.'-height'] ?>"
    ></<?=$tag ?>>
    <?php
  } else {
    ?>
    <img
      <?php if ($class) echo 'class="'.$class.'"'; ?>
      src="<?=$image['sizes'][$size] ?>"
      width="<?=$image['sizes'][$size.'-width'] ?>"
      height="<?=$image['sizes'][$size.'-height'] ?>"
      alt="<?=$alt ?>"
    />
    <?php
  }

  if ( $pin ) {
    $pin_description = get_bloginfo('name').' - '.get_the_title();
    ?>
    <span class="lqu-pinterest-save-button"><a
      href="https://www.pinterest.com/pin/create/button/"
      data-pin-do="buttonPin"
      data-pin-id="<?=$image['url'] ?>"
      data-pin-round="true"
      data-pin-description="<?=$pin_description ?>"
      data-pin-media="<?=$image['url'] ?>"
    ></a></span>
    <?
  }

}
?>
