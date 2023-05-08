<?php
function the_contact_form($args) {
  $show_message = (
    ( isset($args['show_message_field']) and $args['show_message_field']==false )
    ? false : true
  );

  $show_form_on_success = (
    ( isset($args['show_form_on_success']) and $args['show_form_on_success']==false )
    ? false : true
  );
  $success = null;
  $show_thank_you_and_send_email = false;
  $show_contact_form = true;
  $contact_errors = array();

  if (isset($_POST['submit']) and $_POST['submit'] = 'Submit') {

    //$contact_errors[] = 'Testing the contact form. Please check back in 15 minutes.';

    // recaptcha check
    if (isset($_POST['recaptcha_token'])) {
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_secret = '6LdtsbwZAAAAAFHkPMh7-QxJeJtFTJWBQ3shh4VO';
        $recaptcha_token = $_POST['recaptcha_token'];
        $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_token);
        $recaptcha = json_decode($recaptcha);
        if (!($recaptcha->success)) {
          $contact_errors[] = 'Google could not verify this request (Error 01). Please try again or give us a call.';
          //check $recaptcha->error-codes
          //echo 'Error report: ';
          //print_r($contact_errors);
          //print_r($recaptcha);
        } elseif (!$recaptcha->score or $recaptcha->score < 0.5) {
          $contact_errors[] = 'Google could not verify this request (Error 02). Please try again or give us a call.';
        }
    }

    //other checks here
    if ( empty(trim($_POST['first_name'])) or empty(trim($_POST['last_name'])) ) {
      $contact_errors[] = 'Please your first and last name to your request.';
    }
    if ( empty(trim($_POST['email'])) ) {
      $contact_errors[] = 'Please add your email to your request.';
    }

    //any errors?
    if ( empty($contact_errors) ) {
      $success = true;
      $show_thank_you_and_send_email = true;
      if ($show_form_on_success == false) {
        $show_contact_form = false;
      }
    } else {
      $success = false;
    }

  }
  ?>

  <?php if ($show_thank_you_and_send_email) { ?>
    <?php
    $message = "\r\n".'LQU Website Message';
    $message_parts = '';
    if ( isset( $_POST['first_name'] ) ) { $message_parts .= "\r\n\r\n".'First Name: '.$_POST['first_name']; }
    if ( isset( $_POST['last_name'] ) ) { $message_parts .= "\r\n\r\n".'Last Name: '.$_POST['last_name']; }
    if ( isset( $_POST['email'] ) ) { $message_parts .= "\r\n\r\n".'Email: '.$_POST['email']; }
    if ( isset( $_POST['business_registration'] ) ) { $message_parts .= "\r\n\r\n".'Business Registration: '.$_POST['business_registration']; }
    if ( isset( $_POST['message'] ) ) { $message_parts .= "\r\n\r\n".'Message: '.$_POST['message']; }
    $message .= strip_tags(stripslashes(filter_var($message_parts,FILTER_SANITIZE_STRING,FILTER_FLAG_NO_ENCODE_QUOTES)));
    //$message .= "\r\n\r\n".print_r($_POST,true);
    wp_mail( 'info@lqupholstery.com', 'LQU Website Message', $message);
    ?>
    <div class="contact-response sent">
      <h1>Received!</h1>
      <p>Someone from our team will reach out to you shortly.<p>
      <?php
      //echo '<textarea style="width:100%; height:200px;">'.$message.'</textarea>';
      ?>
    </div>
  <? } ?>

  <?php if ( !empty($contact_errors) ) { ?>
    <div class="contact-response problem">
      <h1>There was a problem with your request.</h1>
      <ul>
        <?php foreach ($contact_errors as $contact_errors) { ?>
          <li><?=$contact_errors ?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>


  <?php if ($show_contact_form) { ?>
    <script src="https://www.google.com/recaptcha/api.js?render=6LdtsbwZAAAAAEpOWuuC55w3hI0K3rkt_U663B0N"></script>
    <?php
    //Site key: 6LdtsbwZAAAAAEpOWuuC55w3hI0K3rkt_U663B0N
    //Secret Key: 6LdtsbwZAAAAAFHkPMh7-QxJeJtFTJWBQ3shh4VO
    ?>
    <script>
      jQuery('document').ready( function() {
        $ = jQuery;
        $(window).keydown(function(e){
          if(e.keyCode == 13) {
            //console.log('Please use the submit button.');
            e.preventDefault();
            return false;
          }
        });
        $("form#lqu-contact-form").submit(function(e) {
          e.preventDefault();
          grecaptcha.ready(function () {
            console.log('Starting Protection');
            grecaptcha
              .execute('6LdtsbwZAAAAAEpOWuuC55w3hI0K3rkt_U663B0N', { action: 'contact' })
              .then(function (token) {
                var recaptcha_token_field = document.getElementById('recaptcha_token'); //token field
                recaptcha_token_field.value = token;
                //console.log(recaptcha_token_field.value);
                $("form#lqu-contact-form").off();
                $('input#submit').click();
            });
          });
        });
      });
    </script>
    <form id="lqu-contact-form" method="post">

      <div class="first-and-last-names">
        <span class="lqu-field first-name">
          <label for="first_name">First Name</label>
          <input type="text" name="first_name" required value="<?=(
              (!empty($contact_errors) and isset($_POST['first_name']) and $_POST['first_name'])
              ? strip_tags(stripslashes($_POST['first_name']))
              : ''
            ) ?>"  />
        </span>
        <span class="lqu-field last-name">
          <label for="last_name">Last Name</label>
          <input type="text" name="last_name" required value="<?=(
              (!empty($contact_errors) and isset($_POST['last_name']) and $_POST['last_name'])
              ? strip_tags(stripslashes($_POST['last_name']))
              : ''
            ) ?>"  />
        </span>
      </div>

      <span class="lqu-field email">
        <label for="email">Email</label>
        <input type="email" name="email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" value="<?=(
            (!empty($contact_errors) and isset($_POST['email']) and $_POST['email'])
            ? strip_tags(stripslashes($_POST['email']))
            : ''
          ) ?>"/>
      </span>

      <span class="lqu-field business-registration">
        <label for="business_registration">Business Registration Certificate of Authority</label>
        <input type="text" name="business_registration" value="<?=(
            (!empty($contact_errors) and isset($_POST['business_registration']) and $_POST['business_registration'])
            ? strip_tags(stripslashes($_POST['business_registration']))
            : ''
          ) ?>" />
      </span>

      <?php if ($show_message) { ?>
      <span class="lqu-field text-area your-message">
        <label for="message">How can we help?</label>
        <textarea name="message" placeholder="Your message..." required><?=(
            (!empty($contact_errors) and isset($_POST['message']) and $_POST['message'])
            ? strip_tags(stripslashes($_POST['message']))
            : ''
          ) ?></textarea>
      </span>
      <? } ?>

      <input
        class="cta-button"
        id="submit"
        name="submit"
        type="submit"
        value="Submit"
      />

      <input type="hidden" name="lqucf" value="1">
      <input type="hidden" name="recaptcha_token" id="recaptcha_token">
      <div class="google-recatcha-notice">
        <span>Protected by Google&nbsp;reCaptcha</span>
        <span>
          <a href="https://www.google.com/intl/en/policies/privacy/" target="_blank">Privacy</a> &middot;
          <a href="https://www.google.com/intl/en/policies/terms/" target="_blank">Terms</a>
        </span>
      </div>

    </form>
  <? } //end show form ?>

  <?php
  return $success;
}
?>
