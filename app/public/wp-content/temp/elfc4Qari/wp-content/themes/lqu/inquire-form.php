<div class="inquire-overlay <?=( isset($_POST['lqucf'] ) ? ' shown':'')?> ">
  <div class="blank-area-to-close-the-overlay"></div>
  <div class="full-or-right">
    <div>
      <div class="top">
        <span class="close-inquire-button">Close</span>
      </div>
      <div class="bottom">

        <?php if ( !isset($_POST['submit']) ) { ?>
        <div>
          <p>Thank you for your interest.</p>
          <p>Please fill out the form below and someone from our team will reach out to you shortly.</p>
        </div>
        <?php } ?>

        <div class="lqu-contact-form sidebar">
          <div>
            <?php
            $success = the_contact_form( array(
              'show_message_field'=>false,
              'show_form_on_success'=>false,
            ));
            if ($success) {
              ?>
              <h3>Explore More</h3>
              <ul>
                <?php
                $fc = get_furniture_categories();
                foreach($fc as $c) {
                  ?>
                  <li><a href="<?=get_tag_link($c) ?>"><?=$c->name ?></a></li>
                  <?php
                }
                ?>
              </ul>
              <?php
            }
            ?>

          </div>
        </div>

      </div>

    </div>
  </div>
</div>
