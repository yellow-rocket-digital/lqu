<?php
  $form_title = get_field('form_title');
  $form_text = get_field('form_text');
?>

<section class="contact-form-section"><div>

  <?php
    echo ($form_title ? '<h1>'.$form_title.'</h1>' : '');
    echo ($form_text ? '<div class="introduction">'.$form_text.'</div>' : '');
  ?>
  <a name="contact-form"></a>
  <div class="lqu-contact-form">
    <div>

      <?php
      the_contact_form( array(
        'show_message_field'=>true,
        'show_form_on_success'=>true,
      ));
      ?>

    </div>
  </div>

</div></section>
