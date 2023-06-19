<?php
  /*
   * Template name: Create Account Page
   */
?>

<?php
the_post();
get_header();
$top_navigation_color_theme = 'tan';
include('section-top-navigation.php');
?>

<div class="woocommerce-page">
	<section class="generic-page">
		<div class="woocommerce">
			<div class="generic-content row col-md-6 mx-auto">
				<div class="p-3">
					<h1>Request An Account</h1>
					<form name="registerform" id="registerform" class="register-form"
						action="<?php echo esc_url( site_url( 'wp-login.php?action=register', 'login_post' ) ); ?>" method="post"
						novalidate="novalidate">
						<p>
							<input type="text" name="user_login" id="user_login" class="input"
								value="<?php echo esc_attr( wp_unslash( $user_login ) ); ?>" size="20" autocapitalize="off"
								autocomplete="username" />
							<label for="user_login"><?php _e( 'Username' ); ?></label>
						</p>
						<p>
							<input type="email" name="user_email" id="user_email" class="input" value="<?php echo esc_attr( wp_unslash( $user_email ) ); ?>" size="25" autocomplete="email" />
							<label for="user_email"><?php _e( 'Email Address' ); ?></label>

						</p>

						<?php do_action( 'register_form' ); ?>

						<p id="reg_passmail">
							<?php _e( 'Registration confirmation will be emailed to you.' ); ?>
						</p>
						<br class="clear" />
						<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
						<p class="submit">
							<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large"
								value="<?php esc_attr_e( 'Request Account' ); ?>" />
						</p>

					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
get_footer();
?>
