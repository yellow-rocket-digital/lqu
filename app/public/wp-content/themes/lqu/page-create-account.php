<?php
  /*
   * Template name: Create Account Page
   */
?>
<?php if(is_user_logged_in()){
  wp_redirect(get_permalink(get_option('woocommerce_myaccount_page_id')));
} ?>
<?php get_header();?>
<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

<h1>Create account page</h1>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>

<?php get_footer();?>
