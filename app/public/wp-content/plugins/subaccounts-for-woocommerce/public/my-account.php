<?php
// Exit if accessed directly
// Edited: Yellow Rocket, 7/12/23
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}




/**
 * Frontend style and scripts.
 */
function sfwc_enqueue_frontend_style() {
	
	// Check if logged in user has a valid role. No need to enqueue it everywhere.
	if ( is_user_logged_in() && is_account_page() && sfwc_is_current_user_role_valid() && sfwc_is_current_user_role_enabled() ) {
		
		// Load main CSS.
		wp_enqueue_style( 'sfwc_frontend_style', esc_url( WP_PLUGIN_URL ) . '/subaccounts-for-woocommerce/assets/css/style.css' );
		
		// Load Selectize JS and CSS, necessary in: My Account -> Orders endpoint.
		wp_enqueue_style( 'sfwc_selectize_style', esc_url( WP_PLUGIN_URL ) . '/subaccounts-for-woocommerce/assets/css/selectize/selectize.css' );
		wp_enqueue_script( 'sfwc_selectize_js', esc_url( WP_PLUGIN_URL ) . '/subaccounts-for-woocommerce/assets/js/selectize/selectize.min.js', array( 'jquery' ), '0.13.6', false );
		
		// Load jQuery Accordion JS and CSS, necessary in: My Account -> Manage Subaccounts endpoint.
		wp_enqueue_style( 'sfwc_jquery_ui_accordion_style', esc_url( WP_PLUGIN_URL ) . '/subaccounts-for-woocommerce/assets/css/jquery-ui-accordion.css' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		
		// My Account -> Manage Subaccounts -> Edit subaccount. 		
		wp_enqueue_script( 'selectWoo' );
		wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'wc-country-select' );
	}
}
add_action('wp_enqueue_scripts', 'sfwc_enqueue_frontend_style');




/**
 * Set Manager / Supervisor Cookie.
 * 
 * Set 'is_manager' cookie if customer with 'Manager' Account Level Type does login.
 * Set 'is_supervisor' cookie if customer with 'Supervisor' Account Level Type does login.
 */
function sfwc_set_parent_account_cookie() {

	// Check if logged in user has has a valid role.
	if ( is_user_logged_in() && sfwc_is_current_user_role_valid() && sfwc_is_current_user_role_enabled() ) {
		
		
		
		// Get Account Level Type
		$user_id = get_current_user_id();
		$key = 'sfwc_account_level_type';
		$single = true;
		$account_level_type = get_user_meta($user_id, $key, $single);
	
	

		// Check if user is parent (Manager).
		if ( $account_level_type == 'manager' ) {

			// If not already build and set 'is_manager' cookie.
			if ( ! isset( $_COOKIE['is_manager'] ) ) {

				// Assign variable to cookie name: wordpress_logged_in_ + hashed site URL.
				$finalUserCookieName = 'wordpress_logged_in_' . COOKIEHASH;

				if ( isset( $_COOKIE[$finalUserCookieName] ) ) {
					
					// Sanitize cookie value.
					$finalUserCookieNameValue = sanitize_text_field( $_COOKIE[$finalUserCookieName] );
					
					// Generate random code for transient name.
					$generate_random_code = random_bytes( 5 );
					// Convert binary data into hexadecimal.
					$output_random_code = bin2hex( $generate_random_code );
					
					// Set a trasient to check 'is_manager' cookie value against (they must be equal).
					set_transient( 'sfwc_is_or_was_manager_' . $output_random_code, $finalUserCookieNameValue, 3600 );

					// Set 'is_manager' cookie name and its value (same as 'wordpress_logged_in_...' value).
					setcookie('is_manager', $finalUserCookieNameValue, [
						'expires' => '',
						'path' => COOKIEPATH,
						'domain' => COOKIE_DOMAIN,
						'secure' => is_ssl(),
						'httponly' => true,
						'samesite' => 'Strict',
					]);


					// Provide initial value to cookie otherwise frontend switcher will be shown only after page refresh.
					$_COOKIE['is_manager'] = get_transient( 'sfwc_is_or_was_manager_' . $output_random_code );
				}
			}
		}
		
		
		// Check if user is parent (Supervisor).
		if ( $account_level_type == 'supervisor'  ) {

			// If is Supervisor build 'is_supervisor' cookie
			if ( ! isset($_COOKIE['is_supervisor'] ) ) {

				// Assign variable to cookie: wordpress_logged_in_ + hashed site URL
				$finalUserCookieName = 'wordpress_logged_in_' . COOKIEHASH;
				
				if ( isset( $_COOKIE[$finalUserCookieName] ) ) {
					
					// Sanitize cookie value.
					$finalUserCookieNameValue = sanitize_text_field( $_COOKIE[$finalUserCookieName] );


					// Generate random code for transient name
					$generate_random_code = random_bytes( 5 );
					// Convert binary data into hexadecimal
					$output_random_code = bin2hex( $generate_random_code );

					// Set transient
					set_transient('sfwc_is_or_was_supervisor_' . $output_random_code, $finalUserCookieNameValue, 3600);

					// Set 'is_supervisor' cookie name and its value (same as 'wordpress_logged_in_...' value)
					setcookie('is_supervisor', $finalUserCookieNameValue, [
						'expires' => '',
						'path' => COOKIEPATH,
						'domain' => COOKIE_DOMAIN,
						'secure' => is_ssl(),
						'httponly' => true,
						'samesite' => 'Strict',
					]);

					// Provide initial value to cookie otherwise frontend switcher will be shown only after page refresh
					$_COOKIE['is_supervisor'] = get_transient('sfwc_is_or_was_supervisor_' . $output_random_code);
				}
			}
		}
		
		
	}
}
add_action('template_redirect', 'sfwc_set_parent_account_cookie', 20);	// Priority must be lower than sfwc_add_subaccount_form_handler function for later execution.
																		// Otherwise the user switcher pane won't show instantly after the first subaccount creation for new accounts (page refresh would be required).




// Destroy is_manager / is_supervisor cookie and transient on logout
function sfwc_destroy_parent_account_cookie_on_logout() {

	global $wpdb;
	

    if ( isset( $_COOKIE['is_manager'] ) ) {


		$cookie_value_manager = sanitize_text_field( $_COOKIE['is_manager'] );

		$current_transient_manager = $wpdb->get_var( " SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_sfwc_is_or_was_manager_%' AND option_value LIKE '$cookie_value_manager' " );
		
		$curr_transient_name_manager = str_replace( '_transient_', '', $current_transient_manager );		// Or: $curr_transient_name_manager = substr( $current_transient_manager, 11);

		delete_transient( $curr_transient_name_manager );
		


		// Destroy is_manager cookie
		setcookie('is_manager', null, -1, COOKIEPATH);
    }
	


	
	if ( isset( $_COOKIE['is_supervisor'] ) ) {

		
		$cookie_value_supervisor = sanitize_text_field( $_COOKIE['is_supervisor'] );

		$current_transient_supervisor = $wpdb->get_var( " SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_sfwc_is_or_was_supervisor_%' AND option_value LIKE '$cookie_value_supervisor' " );
		
		$curr_transient_name_supervisor = str_replace( '_transient_', '', $current_transient_supervisor );		// Or: $curr_transient_name_supervisor = substr( $current_transient_supervisor, 11);

		delete_transient( $curr_transient_name_supervisor );
		

		
		// Destroy is_supervisor cookie
		setcookie('is_supervisor', null, -1, COOKIEPATH);

	}
}
add_action('wp_logout', 'sfwc_destroy_parent_account_cookie_on_logout');




/**
 * Display User Account Switcher.
 * 
 * Echo User Account Switcher on WooCommerce My Account page.
 */
function sfwc_action_woocommerce_account_dashboard() {

    // Retrieve the current user object
    $current_user = wp_get_current_user();
	
	// Get ID of currently logged in user.
	$user_id = get_current_user_id();

	// Get Account Level Type of currently logged in user (Superviore | Manager | Default).
	$account_level_type = get_user_meta( $user_id, 'sfwc_account_level_type', true );


	/**
	 * Get plugin settings
	 *
	 * Check if values have been set first to prevent 'undefined' 
	 */

    // Get 'Appearance' settings.
    $sfwc_switcher_appearance = (array) get_option('sfwc_switcher_appearance');
	
		// Get Pane Background Color.
		$sfwc_switcher_pane_bg_color = ( isset( $sfwc_switcher_appearance['sfwc_switcher_pane_bg_color'] ) ) ? $sfwc_switcher_appearance['sfwc_switcher_pane_bg_color'] : '#def6ff';
	
		// Get Pane Headline Color.
		$sfwc_switcher_pane_headline_color = ( isset( $sfwc_switcher_appearance['sfwc_switcher_pane_headline_color'] ) ) ? $sfwc_switcher_appearance['sfwc_switcher_pane_headline_color'] : '#0088cc';

		// Get Pane Text Color.
		$sfwc_switcher_pane_text_color = ( isset( $sfwc_switcher_appearance['sfwc_switcher_pane_text_color'] ) ) ? $sfwc_switcher_appearance['sfwc_switcher_pane_text_color'] : '#3b3b3b';
	
		// Get Pane Select Button Background Color.
		$sfwc_switcher_pane_select_bg_color = ( isset( $sfwc_switcher_appearance['sfwc_switcher_pane_select_bg_color'] ) ) ? $sfwc_switcher_appearance['sfwc_switcher_pane_select_bg_color'] : '#0088cc';

		// Get Pane Select Button Text Color.
		$sfwc_switcher_pane_select_text_color = ( isset( $sfwc_switcher_appearance['sfwc_switcher_pane_select_text_color'] ) ) ? $sfwc_switcher_appearance['sfwc_switcher_pane_select_text_color'] : '#ffffff';


    // Get 'Options' settings
    $sfwc_options = (array) get_option('sfwc_options');

		// Get Customer Display Name.
		$sfwc_option_display_name = ( isset( $sfwc_options['sfwc_option_display_name'] ) ) ? $sfwc_options['sfwc_option_display_name'] : 'username';
		
		// Get Selected Roles option value from Options settings.
		$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');
		


	// Get children (array) of currently logged in user.																		
	$children_ids = get_user_meta( $user_id, 'sfwc_children', true ); // 3rd must be: true, otherwise will turn it into a two-dimensional array.

	/**
	 * Remove no longer existing users from the $children_ids array,
	 * in case a user has been deleted (but still present within 'sfwc_children' meta of an ex parent account).
	 *
	 * Also exclude users whose role is not enabled in the plugin settings.
	 */
	$existing_children_ids = array();
	
	if ( ! empty ( $children_ids ) ) {
		
		foreach ( $children_ids as $single_id ) {
			
			// Check if user still exists.
			$user_exists = get_userdata( $single_id );
			if ( $user_exists !== false ) {
				
				if ( sfwc_is_user_role_valid( $single_id ) && sfwc_is_user_role_enabled( $single_id ) ) {
					
					$existing_children_ids[] = $single_id;
				}
			}
		}
	}



	// Check if logged in user has has a valid role.
	if ( is_user_logged_in() && sfwc_is_current_user_role_valid() && sfwc_is_current_user_role_enabled() ) {








		// Get all users with user meta 'sfwc_account_level_type' == 'manager' and filter only the one which has 'sfwc_children' user_meta containing the child ID who made the order
		$args_manager = array(
			//'role'    => 'customer',
			//'role__in' => ['customer', 'subscriber'],
			'role__in' => $sfwc_option_selected_roles,
			'exclude' => $user_id, // Exclude ID of customer who made currently displayed order
			'orderby' => 'ID',
			'order' => 'ASC',
			'meta_key' => 'sfwc_account_level_type',
			'meta_value' => 'manager',
			'meta_query' => array(
				array(
					'key' => 'sfwc_children',
					'value' => '"'.$user_id.'"',
					'compare' => 'LIKE',
				),
			),
		);


		// The User Query
		$user_query_manager = new WP_User_Query( $args_manager );




		
		if ( isset( $_COOKIE['is_supervisor'] ) ) {

				/**
				 * Get sfwc_is_or_was_supervisor_% (transient name randomly postfixed) transient value from is_supervisor cookie value.
				 *
				 * The implicit logic here is to also check that both a transient and a cookie (both tied to currently logged in customer) exist 
				 * and they match.
				 */
			
				global $wpdb;
				
				$cookie_value_supervisor = sanitize_text_field( $_COOKIE['is_supervisor'] );
				
				// Get transient name (randomly postfixed) stored in DB by transient value.
				$current_transient_supervisor = $wpdb->get_var( " SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_sfwc_is_or_was_supervisor_%' AND option_value LIKE '$cookie_value_supervisor' " );
				
				// WordPress will automatically prefix the transient name with "_transient_"
				// So it is necessary to strip the prefix out in order to get the "real" transient name.
				$curr_transient_name_supervisor = str_replace( '_transient_', '', $current_transient_supervisor );		// Or: $curr_transient_name_supervisor = substr( $current_transient_supervisor, 11);
				
				// Get transient value by transient name.
				$is_or_was_supervisor = get_transient( $curr_transient_name_supervisor );
		}


		if ( isset( $_COOKIE['is_manager'] ) ) {			
			
				/**
				 * Get sfwc_is_or_was_manager_% (transient name randomly postfixed) transient value from is_manager cookie value.
				 *
				 * The implicit logic here is to also check that both a transient and a cookie (both tied to currently logged in customer) exist 
				 * and they match.
				 */
			
				global $wpdb;
				
				$cookie_value_manager = sanitize_text_field( $_COOKIE['is_manager'] );

				// Get transient name (randomly postfixed) stored in DB by transient value.
				$current_transient_manager = $wpdb->get_var( " SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_sfwc_is_or_was_manager_%' AND option_value LIKE '$cookie_value_manager' " );
				
				// WordPress will automatically prefix the transient name with "_transient_"
				// So it is necessary to strip the prefix out in order to get the "real" transient name.
				$curr_transient_name_manager = str_replace( '_transient_', '', $current_transient_manager );		// Or: $curr_transient_name_manager = substr( $current_transient_manager, 11);
				
				// Get transient value by transient name.
				$is_or_was_manager = get_transient( $curr_transient_name_manager );
		}


		/**
		 * In the rare case that frontend switcher is not displayed (cookie or transient has not been properly set or expired) show 'Login Again' button.
		 */
		if ( isset( $cookie_value_manager ) && ( $cookie_value_manager !== $is_or_was_manager ) && ! isset( $cookie_value_supervisor ) ) {

			echo '<p id="sfwc-session-expired" class="sfwc-reload-page-text">' . esc_html__('Your session has most likely timed out. You can try reloading the page or logging in again.', 'subaccounts-for-woocommerce');
			echo '<a id="sfwc-login-again" href="' . wp_logout_url( esc_url( wc_get_page_permalink( 'myaccount' ) ) ) . '">' . esc_html__('Login Again', 'subaccounts-for-woocommerce') . '</a></p>';
		}





		/**
		 * User Switcher.
		 */
		if ( isset( $cookie_value_manager ) && ( $cookie_value_manager === $is_or_was_manager ) && ! isset( $cookie_value_supervisor ) ) {


			/***
			
			// For debug.
			echo '<p><strong style="color:red;">All subaccount IDs (including no longer existing subaccounts)</strong></p>';
			if ( ! empty( $children_ids ) ) {
				echo '<pre>';
				print_r( $children_ids );
				echo '</pre>';
			} else {
				echo '<p>None.</p>';
			}
			
			// For debug.
			echo '<p><strong style="color:red;">Only existing subaccount IDs</strong></p>';
			if ( ! empty( $existing_children_ids ) ) {
				echo '<pre>';
				print_r( $existing_children_ids );
				echo '</pre>';
			} else {
				echo '<p>None.</p>';
			}

			***/



			/**
			 * Make sure the user switcher is shown based on the following conditions:
			 *
			 *	- a Manager account has got existing (not deleted or unset as subaccount by admin) subaccounts;
			 *	- or in case of a Default account, so that it can switch back to its parent.
			 */
			if ( ( $account_level_type == 'manager' && ! empty( $existing_children_ids ) ) || ( $account_level_type !== 'manager' && $account_level_type !== 'supervisor' ) ) {
				
				/**
				 * Echo Subaccounts Switcher HTML and populate it
				 */

				echo '<div id="sfwc-user-switcher-pane" style="background-color:' . esc_attr( $sfwc_switcher_pane_bg_color ) . ';">';
				echo '<h3 style="color:' . esc_attr( $sfwc_switcher_pane_headline_color ) . ';">' . esc_html__('You are currently logged in as:', 'subaccounts-for-woocommerce') . '</h3>';

				// Check 'Customer Display Name' in Subaccounts > Settings > Options and display it accordingly
				if ( ( $sfwc_option_display_name == 'full_name' ) && ( $current_user->user_firstname || $current_user->user_lastname ) ) {

					// Echo 'Full Name + Email' (if either First Name or Last Name has been set)
					echo '<p style="color:' . esc_attr( $sfwc_switcher_pane_text_color ) . ';">' . esc_html( $current_user->user_firstname ) . ' ' . esc_html( $current_user->user_lastname ) . ' (' . esc_html( $current_user->user_email ) . ')</p>';

				} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( $current_user->billing_company ) ) {

					// Echo 'Company + Email' (if Company name has been set)
					echo '<p style="color:' . esc_attr( $sfwc_switcher_pane_text_color ) . ';"><strong>' . esc_html__( 'Company: ', 'subaccounts-for-woocommerce' ) . '</strong>' . esc_html( $current_user->billing_company ) . ' (' . esc_html( $current_user->user_email ) . ')</p>';

				} else {

					// Otherwise echo 'Username + Email'
					echo '<p style="color:' . esc_attr( $sfwc_switcher_pane_text_color ) . ';">' . esc_html( $current_user->user_email ) . '</p>';
				}
				
				$class_selectize = empty( $user_query_manager->get_results() ) ? 'class="sfwc_frontend_children_selectize"' : '';
				?>

				<form method="post">
					<select id="sfwc_frontend_children" <?php echo $class_selectize; ?> name="sfwc_frontend_children" onchange="this.form.submit();" style="background-color:<?php echo esc_attr( $sfwc_switcher_pane_select_bg_color ); ?>; color:<?php echo esc_attr( $sfwc_switcher_pane_select_text_color ); ?>;">
						<option value="" selected="selected" disabled><?php echo esc_html__( 'Select Account', 'subaccounts-for-woocommerce' ); ?></option>

						<?php
						if ( empty( $existing_children_ids ) ) {

							// User Loop
							if ( ! empty( $user_query_manager->get_results() ) ) {
								foreach ( $user_query_manager->get_results() as $user ) {
								?>
									<option style="font-weight:bold;" value="<?php echo esc_attr($user->ID); ?>">&#129044; <?php echo esc_html__('Back to Manager', 'subaccounts-for-woocommerce'); ?></option>
								<?php
								}
							}
						}
			
						
						
						if ( ! empty( $existing_children_ids ) ) {

							foreach ( $existing_children_ids as $key => $value ) {

								//Check 'Customer Display Name' in Subaccounts > Settings > Options and display it accordingly
								if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata($value)->user_firstname || get_userdata($value)->user_lastname ) ) {

									// Echo 'Full Name + Email' (if either First Name or Last Name has been set)
									echo "<option value=" . esc_attr( $value ) . ">" . esc_html( get_userdata($value)->user_firstname ) . " " . esc_html( get_userdata($value)->user_lastname ) . " - [" . esc_html( get_userdata($value)->user_email ) . "]</option>";

								} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata($value)->billing_company ) ) {

									// Echo 'Company + Email' (if Company name has been set)
									echo "<option value=" . esc_attr( $value ) . ">" . esc_html( get_userdata($value)->billing_company ) . " - [" . esc_html( get_userdata($value)->user_email ) . "]</option>";

								} else {

									// Otherwise echo 'Username + Email'
									echo "<option value=" . esc_attr( $value ) . ">" . esc_html( get_userdata($value)->user_login ) . " - [" . esc_html( get_userdata($value)->user_email ) . "]</option>";
								}
							}
						}
						?>
					</select>
					<input name="setc" value="submit" type="submit" style="display:none;">
				</form>

			<?php
			echo '</div>';
			}
		}
	}	
?>
		
<script>
(function($) {
	
	// Initialize Selectize.
	$(".sfwc_frontend_children_selectize").selectize();
	
	// Apply settings background color.
	$('body').append('<style>#sfwc-user-switcher-pane .selectize-control.single .selectize-input{background-color: <?php echo esc_attr( $sfwc_switcher_pane_select_bg_color ); ?>;}</style>');
	
	// Apply settings text color.
	$('body').append('<style>#sfwc-user-switcher-pane .selectize-control.single .selectize-input input::placeholder, #sfwc-user-switcher-pane .selectize-control.single .selectize-input .item {color: <?php echo esc_attr( $sfwc_switcher_pane_select_text_color ); ?>;}</style>');
	
})( jQuery );
</script>

<?php
}
add_action('woocommerce_before_account_navigation', 'sfwc_action_woocommerce_account_dashboard');




/**
 * User Account Switcher: Validation and Authentication Cookies Installation.
 *
 * On WooCommerce My Account page, when selecting a subaccount or a parent account from the dropdwon list, set authentication cookies for the selected user.
 */
function sfwc_set_current_user_cookies() {
	
	// Get Options settings.
	$sfwc_options = (array) get_option('sfwc_options');

	// Get Selected Roles option value from Options settings.
	$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');
	
	
	if ( isset( $_COOKIE['is_supervisor'] ) ) {
		
			global $wpdb;
			
			$cookie_value_supervisor = sanitize_text_field( $_COOKIE['is_supervisor'] );

			$current_transient_supervisor = $wpdb->get_var( " SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_sfwc_is_or_was_supervisor_%' AND option_value LIKE '$cookie_value_supervisor' " );
			
			$curr_transient_name_supervisor = str_replace( '_transient_', '', $current_transient_supervisor );		// Or: $curr_transient_name_supervisor = substr( $current_transient_supervisor, 11);
			
			$is_or_was_supervisor = get_transient( $curr_transient_name_supervisor );

	}
			

		
	if ( isset( $_COOKIE['is_manager'] ) ) {

			global $wpdb;
			
			$cookie_value_manager = sanitize_text_field( $_COOKIE['is_manager'] );

			$current_transient_manager = $wpdb->get_var( " SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_sfwc_is_or_was_manager_%' AND option_value LIKE '$cookie_value_manager' " );
			
			$curr_transient_name_manager = str_replace( '_transient_', '', $current_transient_manager );		// Or: $curr_transient_name_manager = substr( $current_transient_manager, 11);
			
			$is_or_was_manager = get_transient( $curr_transient_name_manager );
	}


	// Get ID of currently logged in user.
	$current_user_id = get_current_user_id();
	
	// Get Account Level Type of currently logged in user (Superviore | Manager | Default).
	$account_level_type = get_user_meta( $current_user_id, 'sfwc_account_level_type', true );

	// Get children (array) of currently logged in user.																				## A SCENDERE ↓ ##		OTTIENI LISTA SUBACCOUNT RELATIVI A UTENTE ATTUALMENTE LOGGATO
	// 3rd must be: true, otherwise will turn it into a two-dimensional array and validation won't work															(VALE SIA PER SUPERVISOR CHE MANAGER)
	$children_ids = get_user_meta( $current_user_id, 'sfwc_children', true );







	/**
	 * Create an array of IDs of 'Default' users tied to Managers which are tied to Supervisors											## A SCENDERE ↓ ##
	 * to create list of whitelisted/validated Deafult users a Supervisor can directly switch to (without passing from a Manger first).
	 *
	 * Necessary in order to switch directly from Supervisor to (whitelisted/validated) Default users.
	 */

	$children_of_manager = array();

	// 
	// So basically getting Default users tied to Supervisors
	if ( ! empty( $children_ids ) ) {
		foreach ( $children_ids as $children_id ) {
			
			
			$single_array = get_user_meta( $children_id, 'sfwc_children', true );
			//$children_of_manager[] = $single_value;
			
			if ( ! empty( $single_array ) ) {
				
				foreach ( $single_array as $single_value ) {
					$children_of_manager[] = $single_value;
				}

			}
			
		}
	}
	// var_dump($children_ids);
	// var_dump($children_of_manager);
	
	
	
	
	

	/**
	 * Get the Supervisor of currently logged in Manager.																				 ## A SALIRE ↑ ##		OTTIENI SUPERVISOR RELATIVO A MANAGER ATTUALMENTE LOGGATO
	 *
	 * By querying all users with user meta 'sfwc_account_level_type' == 'supervisor' 
	 * and filtering only the one which has 'children' user_meta containing the child ID of currently logged in Manager.
	 */

	$args_supervisor = array(
		//'role'    => 'customer',
		//'role__in' => ['customer', 'subscriber'],
		'role__in' => $sfwc_option_selected_roles,
		'orderby' => 'ID',
		'order' => 'ASC',
		'meta_key' => 'sfwc_account_level_type',
		'meta_value' => 'supervisor',
		'meta_query' => array(
			array(
				'key' => 'sfwc_children',
				'value' => '"'.$current_user_id.'"',
				'compare' => 'LIKE',
			),
		),
	);


	// The User Query
	$user_query_supervisor = new WP_User_Query( $args_supervisor );



	// User Loop
	if ( ! empty( $user_query_supervisor->get_results() ) ) {
		foreach ( $user_query_supervisor->get_results() as $user ) {

			$supervisor_id = $user->ID;
		}
	}




	/**
	 * Get the Manager of currently logged in Default user.																					## A SALIRE ↑ ##		OTTIENI MANAGER RELATIVO A DEFAULT USER ATTUALMENTE LOGGATO
	 *
	 * By querying all users with user meta 'sfwc_account_level_type' == 'manager' 
	 * and filtering only the one which has 'children' user_meta containing the child ID of currently logged in Default user.
	 */
	 
	$args_manager = array(
		//'role'    => 'customer',
		//'role__in' => ['customer', 'subscriber'],
		'role__in' => $sfwc_option_selected_roles,
		'orderby' => 'ID',
		'order' => 'ASC',
		'meta_key' => 'sfwc_account_level_type',
		'meta_value' => 'manager',
		'meta_query' => array(
			array(
				'key' => 'sfwc_children',
				'value' => '"'.$current_user_id.'"',
				'compare' => 'LIKE',
			),
		),
	);


	// The User Query
	$user_query_manager = new WP_User_Query( $args_manager );






	// User Loop
	if ( ! empty( $user_query_manager->get_results() ) ) {
		foreach ( $user_query_manager->get_results() as $user ) {

			$manager_id = $user->ID;
		}
	}





	/**
	 * Validation before account switch.
	 */
	if ( isset( $_POST['sfwc_frontend_children'] ) ) {

		$selected = sanitize_text_field( $_POST['sfwc_frontend_children'] );


		/**
		 * Caveat for the team: "a form input value is always a string", so is_int is not an option here.
		 * See: https://www.php.net/manual/en/function.is-int.php
		 *
		 * Check if $selected (numeric string) is a positive number.
		 *
		 * 3			true
		 * 03			false
		 * 3.5			false
		 * 3,5			false
		 * +3			false
		 * -3			false
		 * 1337e0		false
		 */
		if ( is_numeric( $selected ) && $selected >= 1 && preg_match( '/^[1-9][0-9]*$/', $selected ) ) {


			/**
			 * Check if logged in user is Supervisor.
			 *
			 * In this case this should be enough:
			 * if ( is_user_logged_in() && $account_level_type == 'supervisor' ) {...}
			 *
			 * Anyway...
			 */
			if ( ( is_user_logged_in() && $account_level_type == 'supervisor' ) && ( isset( $cookie_value_supervisor ) && ( $cookie_value_supervisor === $is_or_was_supervisor ) ) ) {

				/**
				 * Check if selected user is a subaccount of currently logged Supervisor.
				 *
				 * Or, in case Supervisor has switched to a Manager, check if selected user is a sub-acount of currently logged Mangaer (tied to the initially logged Supervisor).
				 */
				if ( in_array( $selected, $children_ids, true ) || in_array( $selected, $children_of_manager, true ) ) {

					// Clears the cart session when called.
					wc_empty_cart(); // Necessary in order for the cart to be auto-populated with correct data after the switch.
					
					// Removes all of the cookies associated with authentication.
					wp_clear_auth_cookie();
										
					wp_set_current_user( $selected );
					wp_set_auth_cookie( $selected );
										
					// Fix cart not populating after switch from user with empty cart to user with data in cart.
					wc_setcookie( 'woocommerce_cart_hash', md5( json_encode( WC()->cart->get_cart() ) ) );

					//wc_setcookie( 'woocommerce_items_in_cart', 1 );
					//do_action( 'woocommerce_set_cart_cookies', true );

				} else {

					wc_add_notice( esc_html__( 'You are not authorized to access the chosen account.', 'subaccounts-for-woocommerce' ), 'error' );
				}

			}
			/**
			 * Check if logged in user is Manager.
			 */
			elseif ( is_user_logged_in() && $account_level_type == 'manager' ) {

				/**
				 * Check if currently logged in 'Manager' has come to its account after switching from 'Supervisor'.
				 *
				 * Checking this by verifying if 'is_supervisor' cookie is set on its browser.
				 */
				if ( isset( $cookie_value_supervisor ) && ( $cookie_value_supervisor === $is_or_was_supervisor ) ) {

					if ( ( ! empty( $children_ids ) && in_array( $selected, $children_ids, true ) ) || $selected == $supervisor_id ) {

						// Clears the cart session when called.
						wc_empty_cart(); // Necessary in order for the cart to be auto-populated with correct data after the switch.
						
						// Removes all of the cookies associated with authentication.
						wp_clear_auth_cookie();
											
						wp_set_current_user( $selected );
						wp_set_auth_cookie( $selected );
											
						// Fix cart not populating after switch from user with empty cart to user with data in cart.
						wc_setcookie( 'woocommerce_cart_hash', md5( json_encode( WC()->cart->get_cart() ) ) );

						//wc_setcookie( 'woocommerce_items_in_cart', 1 );
						//do_action( 'woocommerce_set_cart_cookies', true );

					} else {

						wc_add_notice( esc_html__( 'You are not authorized to access the chosen account.', 'subaccounts-for-woocommerce' ), 'error' );
					}
				}
				/**
				 * Otherwise it means that 'Manager' has logged in from its own account (without switching from Supervisor).
				 *
				 * Therefore do not provide him access to its 'Supervisor' by removing: $selected == $supervisor_id.
				 */				
				elseif ( ! isset( $cookie_value_supervisor ) || ( $cookie_value_supervisor !== $is_or_was_supervisor ) ) {
					
					// Make sure 'is_manager' cookie is set and its value is equal to transient stored in DB
					if ( isset( $cookie_value_manager ) && ( $cookie_value_manager === $is_or_was_manager ) ) {

						if ( ! empty( $children_ids ) && in_array( $selected, $children_ids, true ) ) {

							// Clears the cart session when called.
							wc_empty_cart(); // Necessary in order for the cart to be auto-populated with correct data after the switch.
							
							// Removes all of the cookies associated with authentication.
							wp_clear_auth_cookie();
												
							wp_set_current_user( $selected );
							wp_set_auth_cookie( $selected );
												
							// Fix cart not populating after switch from user with empty cart to user with data in cart.
							wc_setcookie( 'woocommerce_cart_hash', md5( json_encode( WC()->cart->get_cart() ) ) );

							//wc_setcookie( 'woocommerce_items_in_cart', 1 );
							//do_action( 'woocommerce_set_cart_cookies', true );

						} else {

							wc_add_notice( esc_html__( 'You are not authorized to access the chosen account.', 'subaccounts-for-woocommerce' ), 'error' );
						}

					}
				}
			}
			/**
			 * Check if currently logged in 'Default' user has come to its account after switching from 'Supervisor' or 'Manager'.
			 *
			 * Checking this by verifying if either 'is_supervisor' or 'is_manager' cookie is set on its browser.
			 */
			elseif (
						( is_user_logged_in() && $account_level_type !== 'supervisor' || $account_level_type !== 'manager' ) 
						&& ( ( isset( $cookie_value_supervisor ) && ($cookie_value_supervisor === $is_or_was_supervisor ) ) || ( isset( $cookie_value_manager ) && ( $cookie_value_manager === $is_or_was_manager ) ) ) 
					) {


				// Get Supervisor's ID from Default user's Manager ID
				$args_supervisor = array(
					//'role'    => 'customer',
					//'role__in' => ['customer', 'subscriber'],
					'role__in' => $sfwc_option_selected_roles,
					//'exclude'  => $user_id,	// Exclude ID of customer who made currently displayed order
					'orderby' => 'ID',
					'order' => 'ASC',
					'meta_key' => 'sfwc_account_level_type',
					'meta_value' => 'supervisor',
					'meta_query' => array(
						array(
							'key' => 'sfwc_children',
							'value' => '"'.$manager_id.'"',
							'compare' => 'LIKE',
						),
					),
				);


				// The User Query
				$user_query_supervisor = new WP_User_Query( $args_supervisor );



				// User Loop
				if ( ! empty( $user_query_supervisor->get_results() ) ) {
					foreach ( $user_query_supervisor->get_results() as $user ) {

						$supervisor_id = $user->ID;
					}
				}


				if ( ( ! empty( $children_ids ) && in_array( $selected, $children_ids, true ) ) || isset( $supervisor_id ) && $supervisor_id == $selected || isset( $manager_id ) && $manager_id == $selected ) {

					// Clears the cart session when called.
					wc_empty_cart(); // Necessary in order for the cart to be auto-populated with correct data after the switch.
					
					// Removes all of the cookies associated with authentication.
					wp_clear_auth_cookie();
										
					wp_set_current_user( $selected );
					wp_set_auth_cookie( $selected );
										
					// Fix cart not populating after switch from user with empty cart to user with data in cart.
					wc_setcookie( 'woocommerce_cart_hash', md5( json_encode( WC()->cart->get_cart() ) ) );

					//wc_setcookie( 'woocommerce_items_in_cart', 1 );
					//do_action( 'woocommerce_set_cart_cookies', true );

				} else {
					
					wc_add_notice( esc_html__( 'You are not authorized to access the chosen account.', 'subaccounts-for-woocommerce' ), 'error' );

				}
			} else {

				wc_add_notice( esc_html__( 'You are not authorized to access the chosen account.', 'subaccounts-for-woocommerce' ), 'error' );

			}
		}

		// If $selected is not a positive integer.
		else {
			
			wc_add_notice( esc_html__( 'Incorrect data sent.', 'subaccounts-for-woocommerce' ), 'error' );
			
		}
	}
}
add_action('wp', 'sfwc_set_current_user_cookies');




/**
 * Add "Subaccounts" menu item and content to My Account page (Pt.1).
 *
 * Declare "subaccounts" endpoint as query vars.
 *
 * To allow the composite filter hook woocommerce_endpoint_{$endpoint}_title to work with custom My Account endpoints,
 * you need to declare the new endpoint as query vars in woocommerce_get_query_vars filter hook.
 *
 * Also required in order to be able to detect if "subaccounts" is the current page with is_wc_endpoint_url() function.
 */
function sfwc_subaccounts_endpoint_query_vars( $query_vars ) {
    $query_vars['subaccounts'] = 'subaccounts';
	
    return $query_vars;
}
add_filter( 'woocommerce_get_query_vars', 'sfwc_subaccounts_endpoint_query_vars' );




/**
 * Add "Subaccounts" menu item and content to My Account page (Pt.2).
 *
 * Insert the new endpoint into the My Account menu.
 */
function sfwc_insert_subaccounts_link_my_account( $items ) {
	
	// Get ID of currently logged-in user
	$current_user_id = get_current_user_id();

	// Get account type of currently logged-in user
	$user_account_level_type = get_user_meta( $current_user_id, 'sfwc_account_level_type', true );
	
	// Get Options settings.
	$sfwc_options = (array) get_option('sfwc_options');
						
	// Get 'Choose who can create and add new subaccounts' from Options settings.
	$sfwc_options_subaccount_creation = ( isset( $sfwc_options['sfwc_options_subaccount_creation'] ) ) ? $sfwc_options['sfwc_options_subaccount_creation'] : 'customer';
	
	// Get Selected Roles option value from Options settings.
	$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');

	
		/* Query of all Managers */
		$args_are_managers = array(
			//'role'    => 'customer',
			//'role__in' => ['customer', 'subscriber'],
			'role__in' => $sfwc_option_selected_roles,
			'orderby' => 'ID',
			'order' => 'ASC',
			'meta_query' => array(
				array(
					'key' => 'sfwc_account_level_type',
					'value' => 'manager',
					'compare' => '=',
				),
			),
		);
		// The User Query
		$customers_are_managers = new WP_User_Query( $args_are_managers );
					
					
					
		// User Loop
		if ( ! empty( $customers_are_managers->get_results() ) ) {

			foreach ( $customers_are_managers->get_results() as $user ) {

				$list_of_children_for_single_user = get_user_meta( $user->ID, 'sfwc_children', true );

				if ( ! empty( $list_of_children_for_single_user ) ) {
					
					foreach ( $list_of_children_for_single_user as $single_id ) {

						$list_of_children_for_all_managers[] = $single_id;
					}
				}
			}
		}


	if ( is_user_logged_in() && sfwc_is_current_user_role_valid() && sfwc_is_current_user_role_enabled() ) {
		
		// Show 'Add Subaccount' menu item if logged in user is a Manager.
		if ( $user_account_level_type == "manager" ) {

			unset( $items[ 'customer-logout' ] ); // Remove Logout link.
			$items['subaccounts'] = esc_html__( 'Subaccounts', 'subaccounts-for-woocommerce' );
			$items['customer-logout'] = __( 'Logout', 'woocommerce' ); // Put Logout link back after Subaccounts item.
			
			/*
			// Or set a specific position of the item.			
			$new_item = array ( 'subaccounts' => esc_html__( 'Subaccounts', 'subaccounts-for-woocommerce' ) );
			$items = array_slice($items, 0, 5, true) +
			$new_item +
			array_slice($items, 5, null, true);
			*/
		}
		/**
		 * Also show 'Add Subaccount' menu item if we are in one of these situations:
		 *
		 *	- First condition is for those cases where the plugin has been recently installed (first-time installations), thus no manager (or children of managers) have been set yet
		 *	- User logged in as default user that has not got a manager above him (in this case the user should be turned into a manager itself while adding a subaccount)
		 *
		 * If one of these conditions is verified it is possible to show 'Add Subaccount' tab.
		 */
		elseif (
			( ( $user_account_level_type == "default" || $user_account_level_type == "" ) && ! isset( $list_of_children_for_all_managers ) ) ||
			( ( $user_account_level_type == "default" || $user_account_level_type == "" ) && ( isset( $list_of_children_for_all_managers ) && is_array( $list_of_children_for_all_managers ) && ! in_array( $current_user_id, $list_of_children_for_all_managers ) ) )
		) {
			/**
			 * Only in this case (for Default users) we need to check if the option to allow subaccounts creation in front end is allowed.
			 *
			 * For a Manager user this is not necessary because the "Subaccounts" menu item can be always shown,
			 * regardless of whether the option to allow subaccounts creation in front end is enabled or not.
			 * This because of the fact that the "Subaccounts" menu item will be always populated with the "Subaccount Orders" tab content.
			 * 
			 * In the case of a Default user (where the "Subaccount Orders" tab content will never be shown) the only available tab under "Subaccounts" menu item would be the "Add Subaccount" tab,
			 * but this should be shown only if the option to allow subaccounts creation in front end is allowed.
			 */
			if ( $sfwc_options_subaccount_creation == 'customer' || $sfwc_options_subaccount_creation == 'admin_customer' ) {
				
				unset( $items[ 'customer-logout' ] ); // Remove Logout link.
				$items['subaccounts'] = esc_html__( 'Subaccounts', 'subaccounts-for-woocommerce' );
				$items['customer-logout'] = __( 'Logout', 'woocommerce' ); // Put Logout link back after Subaccounts item.
				
				/*
				// Or set a specific position of the item.			
				$new_item = array ( 'subaccounts' => esc_html__( 'Subaccounts', 'subaccounts-for-woocommerce' ) );
				$items = array_slice($items, 0, 5, true) +
				$new_item +
				array_slice($items, 5, null, true);
				*/
			}
			
		}
	}

	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'sfwc_insert_subaccounts_link_my_account' );




/**
 * Add "Subaccounts" menu item and content to My Account page (Pt.3).
 *
 * Add content conditionally based on selected tab.
 */
function sfwc_insert_subaccounts_endpoint_content( $value ) {
	
	$sanitized_value = sanitize_text_field( $value );
	
	if ( isset( $_GET['user_page'] ) ) {
		$sanitized_user_page = absint( sanitize_text_field( $_GET['user_page'] ) );
	}
	
	if ( isset( $_GET['user'] ) ) {
		$sanitized_user_id = absint( sanitize_text_field( $_GET['user'] ) ); // Subaccount user.
	}
	
	if ( isset( $_GET['order_page'] ) ) {
		$sanitized_order_page = absint( sanitize_text_field( $_GET['order_page'] ) );
	}
	
	if ( isset( $_GET['order'] ) ) {
		$sanitized_order_id = absint( sanitize_text_field( $_GET['order'] ) );
	}
	

	
	// Get Options settings.
	$sfwc_options = (array) get_option('sfwc_options');
	
	// Get Subaccount Mode option value from Options settings.
	$sfwc_option_subaccount_mode = ( isset( $sfwc_options['sfwc_option_subaccount_mode'] ) ) ? $sfwc_options['sfwc_option_subaccount_mode'] : 'sub_user';

	// Get 'Customer Display Name' from Options settings.
	$sfwc_option_display_name = ( isset( $sfwc_options['sfwc_option_display_name'] ) ) ? $sfwc_options['sfwc_option_display_name'] : 'username';
						
	// Get 'Choose who can create and add new subaccounts' from Options settings.
	$sfwc_options_subaccount_creation = ( isset( $sfwc_options['sfwc_options_subaccount_creation'] ) ) ? $sfwc_options['sfwc_options_subaccount_creation'] : 'customer';
	
	// Get Selected Roles option value from Options settings.
	$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');
	
	// Get ID of currently logged-in user
	$current_user_id = get_current_user_id();
	
	// Get account type of currently logged-in user
	$user_account_level_type = get_user_meta( $current_user_id, 'sfwc_account_level_type', true );

	// Required in order to get the pagination to work on "Manage Subaccounts" tab.
	$sanitized_current_page_users = isset( $sanitized_user_page ) ? $sanitized_user_page : 1;

	// Required in order to get the pagination to work on "Subaccount Orders" tab.
	$sanitized_current_page_orders = isset( $sanitized_order_page ) ? $sanitized_order_page : 1;
	
	// Get children (array) of currently logged in user.																		
	$children_ids = get_user_meta( $current_user_id, 'sfwc_children', true ); // 3rd must be: true, otherwise will turn it into a two-dimensional array.

	/**
	 * Remove no longer existing users from the $children_ids array
	 * in case a user has been deleted (but still present within 'sfwc_children' meta of an ex parent account).
	 *
	 * Also exclude users whose role is not enabled in the plugin settings.
	 */
	$existing_children_ids = array();
	
	if ( ! empty ( $children_ids ) ) {
		
		foreach ( $children_ids as $single_id ) {
			
			// Check if user still exists.
			$user_exists = get_userdata( $single_id );
			if ( $user_exists !== false ) {
				
				// Check if user role is valid and enabled from plugin settings.
				if ( sfwc_is_user_role_valid( $single_id ) && sfwc_is_user_role_enabled( $single_id ) ) {
					
					$existing_children_ids[] = $single_id;
				}
			}
		}
	}
	

	
	// Query of all Managers.
	$args_are_managers = array(
		//'role'    => 'customer',
		//'role__in' => ['customer', 'subscriber'],
		'role__in' => $sfwc_option_selected_roles,
		'orderby' => 'ID',
		'order' => 'ASC',
		'meta_query' => array(
			array(
				'key' => 'sfwc_account_level_type',
				'value' => 'manager',
				'compare' => '=',
			),
		),
	);
	
	// The User Query.
	$customers_are_managers = new WP_User_Query( $args_are_managers );
				
	// User Loop.
	if ( ! empty( $customers_are_managers->get_results() ) ) {

		foreach ( $customers_are_managers->get_results() as $user ) {

			$list_of_children_for_single_user = get_user_meta( $user->ID, 'sfwc_children', true );

			if ( ! empty( $list_of_children_for_single_user ) ) {
				
				foreach ( $list_of_children_for_single_user as $single_id ) {

					$list_of_children_for_all_managers[] = $single_id;
				}
			}
		}
	}
	
	if ( is_user_logged_in() && sfwc_is_current_user_role_valid() && sfwc_is_current_user_role_enabled() ) {
	?>
	
		<?php if ( $user_account_level_type == 'manager' || $user_account_level_type != 'manager' && $user_account_level_type != 'supervisor' ) : // Check to prevent double <ul> from appearing in DOM (one already set from main plugin) ?>
		<ul class="sfwc_my_account_tabrow">
		<?php endif; ?>
	
			<?php 
			/**
			 * "Manage Subaccounts" tab should be displayed only if logged in customer is a Manager.												// Or a Supervisor (Supervisor Add-on)
			 */
			if ( $user_account_level_type == 'manager' ) : ?>
				<li class="sfwc_my_account_tab <?php if ( $sanitized_value == '' ) { echo 'selected'; } ?>">
					<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'subaccounts' ) ); ?>">
						<?php echo esc_html__( 'Manage Subaccounts', 'subaccounts-for-woocommerce' ); ?>
					</a>
				</li>
			<?php endif; ?>
			
			
			<?php 
			// Check if option is enabled.
			if ( $sfwc_options_subaccount_creation == 'customer' || $sfwc_options_subaccount_creation == 'admin_customer' ) {
				
				/**
				 * Check to see if we are in one of these situations:
				 *
				 *	- User logged in as manager
				 *	- 2nd condition is for those cases where the plugin has been recently installed (first-time installations), thus no manager (or children of managers) have been set yet
				 *	- User logged in as default user that has not got a manager above him (in this case the user should be turned into a manager itself while adding a subaccount)
				 *
				 * If one of these conditions is verified it is possible to show 'Add Subaccount' tab.
				 */
				if ( $user_account_level_type == "manager" 
					 || ( ( $user_account_level_type == "default" || $user_account_level_type == "" ) && ! isset( $list_of_children_for_all_managers ) )
					 || ( ( $user_account_level_type == "default" || $user_account_level_type == "" ) && ( isset( $list_of_children_for_all_managers ) && is_array( $list_of_children_for_all_managers ) && ! in_array( $current_user_id, $list_of_children_for_all_managers ) ) ) 
				) {
				?>
					<li class="sfwc_my_account_tab <?php if ( $sanitized_value == 'add-subaccount' ) { echo 'selected'; } ?>">
						<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'subaccounts/add-subaccount' ) ); ?>">
							<?php echo esc_html__( 'Add Subaccount', 'subaccounts-for-woocommerce' ); ?>
						</a>
					</li>
				<?php 
				}
			}
			?>


			<?php 
			/**
			 * "Subaccount Orders" tab should be displayed only if logged in customer is a Manager and if Sub-User mode is enabled.												// Or a Supervisor (Supervisor Add-on)
			 */
			if ( $user_account_level_type == 'manager' && $sfwc_option_subaccount_mode == 'sub_user' ) : ?>
				<li class="sfwc_my_account_tab <?php if ($sanitized_value == 'subaccount-orders') { echo 'selected'; } ?>">
					<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'subaccounts/subaccount-orders' ) ); ?>">
						<?php echo esc_html__( 'Subaccount Orders', 'subaccounts-for-woocommerce' ); ?>
					</a>
				</li>
			<?php endif; ?>
			
			
		<?php if ( $user_account_level_type == 'manager' || $user_account_level_type != 'manager' && $user_account_level_type != 'supervisor' ) : ?>	
		</ul>
		<?php endif; ?>
		
		<?php
		if ( $sanitized_value === '' ) {
			
			/**
			 * "Manage Subaccounts" tab content should be displayed only if logged in customer is a Manager.			// Or a Supervisor (Supervisor Add-on)
			 */
			if ( $user_account_level_type == 'manager' ) {

				echo '<div id="frontend_manage_subaccounts">';
			
				echo '<img id="sfwc-loading-icon" src="' . esc_url( WP_PLUGIN_URL ) . '/subaccounts-for-woocommerce/assets/images/loader.gif">';

					if ( ! isset( $sanitized_user_id ) ) {	// If the query string ?user=n is not set,
															// display the list of subaccounts.
															// Validation of the list of subaccounts is made in the Ajax function.
					
					// Content loaded via Ajax.
					?>

						<script>
						(function($) {
							
							$("#sfwc-loading-icon").hide();

							var sfwc_ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";

							var sfwc_nonce_frontend_manage_subaccounts = "<?php echo wp_create_nonce('sfwc_nonce_frontend_manage_subaccounts'); ?>";
							
							
							
							/***************
							 Ajax Pagination
							 ***************/
							
							$(window).on("load", function () {
							
								$.ajax({
									type: 'POST',
									data: {
										action: 'sfwc_frontend_manage_subaccounts',
										ajax_get_current_page: <?php echo $sanitized_current_page_users; ?>,
										nonce: sfwc_nonce_frontend_manage_subaccounts,
										
									},
									url: sfwc_ajax_url,
									beforeSend: function () {
										
										// Remove previous content page.
										$("#frontend_manage_subaccounts_users_list").remove();
										
										// Show loading icon.
										$("#sfwc-loading-icon").show();
									},
									success: function (data) {
										
										// Hide loading icon.
										$("#sfwc-loading-icon").hide();

										// Send data.
										$('#frontend_manage_subaccounts').html(data);
									}
								});
							});

						})( jQuery );
						</script>
								
					<?php	
					} elseif ( isset( $sanitized_user_id ) ) {	// If the query string ?user=n is set,
																// retrieve the user ID of the subaccount and display the "Edit Subaccount" page.
																// Validation of the user ID is made in the Ajax function. 
					?>

						<script>
						(function($) {
							
							$("#sfwc-loading-icon").hide();

							var sfwc_ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";

							var sfwc_nonce_frontend_manage_subaccounts = "<?php echo wp_create_nonce('sfwc_nonce_frontend_manage_subaccounts'); ?>";
							

							/********************
							 Ajax Edit Subaccount
							 ********************/
							
							$(window).on("load", function () {
							
								$.ajax({
									type: 'POST',
									data: {
										action: 'sfwc_frontend_edit_subaccount',
										ajax_user_id: <?php echo $sanitized_user_id; ?>,  // Subaccount user.
										ajax_current_page: <?php echo $sanitized_current_page_users; ?>,
										nonce: sfwc_nonce_frontend_manage_subaccounts,
									},
									url: sfwc_ajax_url,
									beforeSend: function () {
										
										// Remove previous content page.
										$("#frontend_manage_subaccounts_users_list").remove();
										
										// Show loading icon.
										$("#sfwc-loading-icon").show();
									},
									success: function (data) {
										
										// Hide loading icon.
										$("#sfwc-loading-icon").hide();

										// Send data.
										$('#frontend_manage_subaccounts').html(data);
									}
								});
							});
							
							
						})( jQuery );
						</script>
								
					<?php
					}
				
					echo '</div>'; // END #frontend_manage_subaccounts.
			}
			
		} elseif ( $sanitized_value === 'subaccount-orders' ) {
			
			/**
			 * "Subaccount Orders" tab content can be displayed only if both of the following conditions are true:
			 *
			 * 	- The logged in customer is a Manager 		(Or a Supervisor, handled in Supervisor Add-on)
			 *	- Subaccount Mode is set to Sub-user
			 */
			if ( $user_account_level_type == 'manager' && $sfwc_option_subaccount_mode == 'sub_user') {

				if ( ! empty( $existing_children_ids ) ) {	// Prevent showing the Subaccounts Order form in case the logged in customer has no subaccounts.
															// This was happening in versions prior to 1.1.3, when the account type for a customer was set to 'Manager' 
															// by the admin (Pro version is/was installed) but no subaccounts were set.
			
					if ( ! isset( $sanitized_order_id ) ) {
						
						echo '<h2 style="margin: 0 0 10px 0;">' . esc_html__( 'Subaccount Orders', 'subaccounts-for-woocommerce' ) . '</h2>';

						// Query for counting total number of orders of selected subaccounts.
						$customer__all_orders = wc_get_orders( apply_filters( 'woocommerce_my_account_my_orders_query',
							array(
								//'customer' => array( ID1, ID2, etc. ),	// IDs of subaccounts retrived from function: sfwc_my_account_orders_query_subaccounts_orders
								'limit' => -1, // Query all orders
							)
						));

						// Count total number of pages to show for pagination.
						$posts_per_page = 10;
						$total_records = count( $customer__all_orders );
						$total_pages = ceil( $total_records / $posts_per_page );


						// Query subaccounts orders.
						$customer_orders = wc_get_orders( apply_filters( 'woocommerce_my_account_my_orders_query',
							array(
								//'customer' => array( ID1, ID2, etc. ),	// IDs of subaccounts retrived from function: sfwc_my_account_orders_query_subaccounts_orders
								'page'     => $sanitized_current_page_orders,
								'paginate' => true,
								'posts_per_page' => $posts_per_page,
							)
						));


						$has_orders = 0 < $customer_orders->total;


						// Load orders.php template to use for "Subaccount Orders" section.
						wc_get_template(
							'myaccount/orders.php',
							array(
								'current_page'    => $sanitized_current_page_orders,
								'customer_orders' => $customer_orders,
								'has_orders'      => $has_orders,
								'wp_button_class' => ' view', 	// Add class to "view" order button (required, othrwise throwing undefined).
																// Leave initial space to separate this class from previous one(s).
							)
						);
					

						// Custom pagination.
						// Default one would redirect to the default My Account -> Orders section.
						$args = array(
							'base' => esc_url( wc_get_account_endpoint_url( 'subaccounts/subaccount-orders' ) ) . '%_%',
							'format' => '?order_page=%#%',
							'total' => $total_pages,
							'current' => $sanitized_current_page_orders,
							'show_all' => false,
							'mid_size' => 2,
							'end_size' => 2,
							'prev_next' => true,
							'prev_text' => __('&laquo; Previous'),
							'next_text' => __('Next &raquo;'),
							'type' => 'plain',
							'add_args' => false,
							'add_fragment' => ''
						);

					   
						// Check if there are orders before echoing pagination.
						if ( $has_orders && $total_records > $posts_per_page ) {
							 echo '<div class="pagination">' . paginate_links( $args ) . '</div>';
						}
					

						/***
						Or instead of wc_get_template, load custom template:

						include( WP_PLUGIN_DIR . '/subaccounts-for-woocommerce/templates/custom-orders-template.php' );
						***/
						
					} elseif ( isset( $sanitized_order_id ) ) {
						
						// Prevent ?order=0.
						if ( $sanitized_order_id <= 0 ) {
							
							echo esc_html__( 'Order is not valid.', 'subaccounts-for-woocommerce' );
							return;
						}
						
						// Get ID of currently logged-in user.
						$user_id = get_current_user_id();
						
						// Get children (array) of currently logged in user.																		
						$children_ids = get_user_meta( $user_id, 'sfwc_children', true );
					
						// Get Order data.
						$order = wc_get_order( $sanitized_order_id );
						
						// Check if order exists.
						if( ! empty( $order ) ) {
							
							// Get ID of user tied to selected Order
							// as a string otherwise below in_array not working for validation.
							$user_id_tied_to_order = (string) $order->get_user_id(); // or $order->get_customer_id();
						
							// Validation: verify that the order belongs to a subaccount of the currently logged parent account.
							if ( in_array( $user_id_tied_to_order, $children_ids, true ) ) {
				
				
								// Show the order.
								wc_get_template(
									'myaccount/view-order.php',
									array(
										 'order' => $order,
										 'order_id' => $sanitized_order_id,
									)
								);			
								
								// IMPORTANT: Remove "Order Again" button,	
								// see anonymous function below.
							
							} else {
								echo esc_html__( 'You are not allowed to view this order.', 'subaccounts-for-woocommerce' );
							}
							
						} else {
							echo esc_html__( 'Selected order does not exist.', 'subaccounts-for-woocommerce' );
						}
					}
				} else {
					
					//In case there is no subaccount yet add a notice.
					if ( $sfwc_options_subaccount_creation == 'customer' || $sfwc_options_subaccount_creation == 'admin_customer' ) {
						
						wc_print_notice(
							esc_html__( 'There is no subaccount yet.', 'subaccounts-for-woocommerce' ) . 
							'<a class="button" href="' . esc_url( wc_get_account_endpoint_url( 'subaccounts/add-subaccount' ) ) . '">' . esc_html__( 'Add Subaccount', 'subaccounts-for-woocommerce' ) . '</a>'
						, 'notice');
						
					} elseif ( $sfwc_options_subaccount_creation == 'admin' ) {
						wc_print_notice( esc_html__( 'There is no subaccount yet. Ask the site administrator to add one for you.', 'subaccounts-for-woocommerce' ), 'notice');
					}
					else {
						wc_print_notice( esc_html__( 'There is no subaccount yet.', 'subaccounts-for-woocommerce' ), 'notice');
					}
				}
			}
			else {
					echo '<p>' . esc_html__( 'There is no content available on this page.', 'subaccounts-for-woocommerce' ) . '</p>';
				}

		} elseif ( $sanitized_value === 'add-subaccount' ) {

			// Check if option is enabled.
			if ( $sfwc_options_subaccount_creation == 'customer' || $sfwc_options_subaccount_creation == 'admin_customer' ) {

				/**
				 * Check to see if we are in one of these situations:
				 *
				 *	- User logged in as manager
				 *	- 2nd condition is for those cases where the plugin has been recently installed (first-time installations), thus no manager (or children of managers) have been set yet
				 *	- User logged in as default user that has not got a manager above him (in this case the user should be turned into a manager itself while adding a subaccount)
				 *
				 * If one of these conditions is verified it is possible to show 'Add Subaccount' tab.
				 */
				if ( $user_account_level_type == "manager" 
					 || ( ( $user_account_level_type == "default" || $user_account_level_type == "" ) && ! isset( $list_of_children_for_all_managers ) )
					 || ( ( $user_account_level_type == "default" || $user_account_level_type == "" ) && ( isset( $list_of_children_for_all_managers ) && is_array( $list_of_children_for_all_managers ) && ! in_array( $current_user_id, $list_of_children_for_all_managers ) ) ) 
				) {
					echo '<h2 style="margin: 0 0 10px 0;">' . esc_html__( 'Add Subaccount', 'subaccounts-for-woocommerce' ) . '</h2>';
					echo do_shortcode( '[sfwc_add_subaccount_shortcode]' );
				}
			}

		} else {
			echo '<p>' . esc_html__( 'There is no content available on this page.', 'subaccounts-for-woocommerce' ) . '</p>';
		}
	}
}
add_action( 'woocommerce_account_subaccounts_endpoint', 'sfwc_insert_subaccounts_endpoint_content' );




/**
 * Add "Subaccounts" menu item and content to My Account page (Pt.4).
 *
 * Hide default WooCommerce orders pagination when viewing subaccounts orders list table, in: Subaccounts > Subaccount Orders.
 */
function sfwc_hide_default_woocommerce_orders_pagination() {
	
	// Hide only if we are in: My Account > Subaccounts
	// and not in: My Account > Orders.
	if ( is_wc_endpoint_url( 'subaccounts' ) ) {
		echo "<style>.woocommerce-pagination { display:none !important; }</style>";
	}
}
add_action('wp_head', 'sfwc_hide_default_woocommerce_orders_pagination', 100);




/**
 * Add "Subaccounts" menu item and content to My Account page (Pt.5).
 *
 * Remove "Order Again" button when viewing an order of a subaccount, in: Subaccounts > Subaccount Orders -> View -> Order Again.
 */
add_action('init', function() {
	
	if ( is_wc_endpoint_url( 'subaccounts' ) . '?order' ) {
		remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button' );
	}
});




/**
 * Add "Subaccounts" menu item and content to My Account page (Pt.6).
 *
 * Create content for the shortcode (AKA Form for subaccounts creation on frontend).
 */
function sfwc_add_subaccount_form_content () {

	/**
	 * Check number of subaccount already created.
	 *
	 *
	 */


	// Get ID of currently logged-in user
	$user_parent = get_current_user_id();

	$user_parent_data = get_userdata( $user_parent );
	
	

	
    $sfwc_options = (array) get_option('sfwc_options');

	// Get 'Customer Display Name' from Options settings.
	$sfwc_option_display_name = ( isset( $sfwc_options['sfwc_option_display_name'] ) ) ? $sfwc_options['sfwc_option_display_name'] : 'username';
	
	// Get 'Subaccounts Number Limit' from Options settings.
	$sfwc_option_subaccounts_number_limit = ( isset( $sfwc_options['sfwc_option_subaccounts_number_limit'] ) ) ? $sfwc_options['sfwc_option_subaccounts_number_limit'] : 10;
	
	//Check 'Customer Display Name' in Subaccounts > Settings > Options and display it accordingly.
	if ( ( $sfwc_option_display_name == 'full_name' ) && ( $user_parent_data->user_firstname || $user_parent_data->user_lastname ) ) {

		// Echo 'Full Name + Email' (if either First Name or Last Name has been set).
		$user_parent_name = '<strong>' . esc_html( $user_parent_data->user_firstname ) . ' ' . esc_html( $user_parent_data->user_lastname ) . '</strong>';

	} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( $user_parent_data->billing_company ) ) {

		// Echo 'Company + Email' (if Company name has been set).
		$user_parent_name = '<strong>' . esc_html( $user_parent_data->billing_company ) . '</strong>';

	} else {

		// Otherwise echo 'Username + Email'.
		$user_parent_name = '<strong>' . esc_html( $user_parent_data->user_login ) . '</strong>';
	}

	/**
	 * Get subaccounts of the currently logged in user.
	 *
	 * This array might include empty values, aka users that are still set as subaccounts of the current user,
	 * but no longer exist (have been deleted from admin).
	 */
	$already_children = get_user_meta( $user_parent, 'sfwc_children', true );
	
	// Exclude possible empty values (no longer existing users) from array.
	if ( ! empty( $already_children ) ) {

		foreach ( $already_children as $key => $value ) {
			
			// Prevent empty option values within the frontend dropdown user switcher 
			// in case a user has been deleted (but still present within 'sfwc_children' meta of an ex parent account).
			$user_exists = get_userdata( $value );
			if ( $user_exists !== false ) {

				$already_children_existing[] = $value;
			}
		}
		
		if ( isset( $already_children_existing ) && is_array( $already_children_existing ) ) {
		
			$qty_subaccount_already_set = count( $already_children_existing );
			
			if ( 	
					( ( $qty_subaccount_already_set >= $sfwc_option_subaccounts_number_limit || $qty_subaccount_already_set >= 10 ) && ! sfwc_is_plugin_active( 'subaccounts-for-woocommerce-pro.php' ) ) ||
					( $qty_subaccount_already_set >= $sfwc_option_subaccounts_number_limit && $sfwc_option_subaccounts_number_limit != 0 )
			
				) {
				
				wc_print_notice(
					sprintf(
						esc_html__( 'Maximum number of subaccounts already reached for %1$s. Please contact the site administrator and ask to increase this value.', 'subaccounts-for-woocommerce' ), 
						$user_parent_name
					)
				, 'error');
				
				return;
			}
		}
	}






	/**
	 * In case form submit was unsuccessful, re-populate input fields with previously posted (wrong) data,
	 * so that user can correct it.
	 *
	 * If successful, input fields cleared with $_POST = array(); in above validation function.
	 */
	$user_login = isset( $_POST['user_login'] ) && $_POST['user_login'] != "" ? sanitize_user( $_POST['user_login'] ) : "";
	$email = isset( $_POST['email'] ) && $_POST['email'] != "" ? sanitize_email( $_POST['email'] ) : "";
	$first_name = isset( $_POST['first_name'] ) && $_POST['first_name'] != "" ? sanitize_text_field( $_POST['first_name'] ) : "";
	$last_name = isset( $_POST['last_name'] ) && $_POST['last_name'] != "" ? sanitize_text_field( $_POST['last_name'] ) : "";
	$company = isset( $_POST['company'] ) && $_POST['company'] != "" ? sanitize_text_field( $_POST['company'] ) : "";

	?>


		<form id="sfwc_form_add_subaccount_frontend" method="post">
		
		<?php wp_nonce_field( 'sfwc_add_subaccount_frontend_action', 'sfwc_add_subaccount_frontend' ); ?>

		<?php
		$username_required_css = ( ( isset( $_POST['user_login'] ) && $_POST['user_login'] == "" ) 
									 || ( isset( $_POST['user_login'] ) &&  username_exists( $_POST['user_login'] ) ) 
									 || ( isset( $_POST['user_login'] ) && ! validate_username( $_POST['user_login'] ) ) 
								 ) ? "color:red;" : "";
		
		
		$email_required_css = ( ( isset( $_POST['email'] ) && $_POST['email'] == "" ) 
								  || ( isset( $_POST['email'] ) && email_exists( $_POST['email'] ) ) 
								  || ( isset( $_POST['email'] ) && ! is_email( $_POST['email'] ) ) 
							  ) ? "color:red;" : "";
		?>

			<div class="user_login" style="margin-bottom:20px; width:48%; float:left;">
				<label for="user_login" style="display:block; margin-bottom:0; <?php echo esc_attr( $username_required_css ); ?>"><?php esc_html_e( 'Username', 'subaccounts-for-woocommerce' ); ?> <span style="font-weight:bold;">*</span></label>
				<input type="text" name="user_login" id="user_login" value="<?php echo esc_attr( $user_login ); ?>" style="width:100%;">
			</div>


			<div class="email" style="margin-bottom:20px; width:48%; float:right;">
				<label for="email" style="display:block; margin-bottom:0; <?php echo esc_attr( $email_required_css ); ?>"><?php esc_html_e( 'Email', 'subaccounts-for-woocommerce' ); ?> <span style="font-weight:bold;">*</span></label>
				<input type="text" name="email" id="email" value="<?php echo esc_attr( $email ); ?>" style="width:100%;">
			</div>


			<div class="first_name" style="margin-bottom:20px; width:48%; float:left;">
				<label for="first_name" style="display:block; margin-bottom:0;"><?php esc_html_e( 'First Name', 'subaccounts-for-woocommerce' ); ?></label>
				<input type="text" name="first_name" id="first_name" value="<?php echo esc_attr( $first_name ); ?>" style="width:100%;">
			</div>

			
			<div class="last_name" style="margin-bottom:20px; width:48%; float:right;">
				<label for="last_name" style="display:block; margin-bottom:0;"><?php esc_html_e( 'Last Name', 'subaccounts-for-woocommerce' ); ?></label>
				<input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $last_name ); ?>" style="width:100%;">
			</div>


			<div class="company" style="margin-bottom:20px; width:100%;">
				<label for="company" style="display:block; margin-bottom:0;"><?php esc_html_e( 'Company', 'subaccounts-for-woocommerce' ); ?></label>
				<input type="text" name="company" id="company" value="<?php echo esc_attr( $company ); ?>" style="width:100%;">
			</div>
			
			<p style="padding:15px; background:#f5f5f5; border-left:5px; border-left-color:#7eb330; border-left-style:solid; display:flex;">
				<span style="font-size:35px; color:#7eb330; align-self:center;">&#128712;</span>
				
				<span style="align-self:center; padding-left:10px;">
					<?php echo esc_html__( 'An email containing the username and a link to set the password will be sent to the new account after the subaccount is created.', 'subaccounts-for-woocommerce' ); ?>
				</span>
			</p>

			<input type="submit" value="Add Subaccount" style="padding:10px 40px;">
			
			<p style="margin-top:50px;">
				<span style="font-weight:bold;">*</span> <?php echo esc_html__( 'These fields are required.', 'subaccounts-for-woocommerce' ); ?></span>
			</p>

		</form>


	<?php
}
add_shortcode( 'sfwc_add_subaccount_shortcode', 'sfwc_add_subaccount_form_content');




/**
 * Add "Subaccounts" menu item and content to My Account page (Pt.7).
 *
 * Handle form submitted data when adding a new subaccount.
 *
 * My Account -> Subaccounts -> Add Subaccount.
 */
function sfwc_add_subaccount_form_handler() {
	
	// Get Options settings.
	$sfwc_options = (array) get_option('sfwc_options');

	// Get Selected Roles option value from Options settings.
	$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');


	// Get ID of currently logged-in user
	$user_parent = get_current_user_id();
	
	
	$user_parent_data = get_userdata( $user_parent );


	$user_login = ""; // For validation and sanitization see below
	$email = ""; // For validation and sanitization see below
	$first_name = isset( $_POST['first_name'] ) && $_POST['first_name'] != "" ? sanitize_text_field( $_POST['first_name'] ) : "";
	$last_name = isset( $_POST['last_name'] ) && $_POST['last_name'] != "" ? sanitize_text_field( $_POST['last_name'] ) : "";
	$company = isset( $_POST['company'] ) && $_POST['company'] != "" ? sanitize_text_field( $_POST['company'] ) : ""; 
	



	// Validation and sanitization of Username input field.
	if ( isset( $_POST['user_login'] ) && $_POST['user_login'] == "" ) {

		wc_add_notice( esc_html__( 'Username is required.', 'subaccounts-for-woocommerce' ), 'error');
	
	} elseif ( isset( $_POST['user_login'] ) && $_POST['user_login'] != "" ) {
		
		if ( ! validate_username( $_POST['user_login'] ) ) {
																			
			wc_add_notice( esc_html__( 'Username is not valid.', 'subaccounts-for-woocommerce' ), 'error');

		} else {
			$user_login = sanitize_user( $_POST['user_login'] );
		}
	}



	// Validation and sanitization of Email input field.
	if ( isset( $_POST['email'] ) && $_POST['email'] == "" ) {

		wc_add_notice( esc_html__( 'Email is required.', 'subaccounts-for-woocommerce' ), 'error');

	} elseif ( isset( $_POST['email'] ) && $_POST['email'] != "" ) {
		
		if ( ! is_email( $_POST['email'] ) ) {

			wc_add_notice( esc_html__( 'Email is not valid.', 'subaccounts-for-woocommerce' ), 'error');

		} else {
			$email = sanitize_email( $_POST['email'] );
		}
	}	
	


	

	/**
	 * If at least basic required information for subaccount creation is provided and validated:
	 * 
	 * 	- $user_login
	 * 	- $email
	 *
	 * proceed with subaccount creation.
	 */
	if ( ( isset( $user_login ) && $user_login != "" && validate_username( $user_login ) ) && ( isset( $email ) && $email != "" && is_email( $email ) ) ) {
		
		// Check if nonce is in place and verfy it.
		if ( ! isset( $_POST['sfwc_add_subaccount_frontend'] ) || isset( $_POST['sfwc_add_subaccount_frontend'] ) && ! wp_verify_nonce( $_POST['sfwc_add_subaccount_frontend'], 'sfwc_add_subaccount_frontend_action' ) ) {
																										
			wc_add_notice( esc_html__( 'Nonce could not be verified.', 'subaccounts-for-woocommerce' ), 'error');

		} else {
			
			/**
			 * Everything looks fine, we can proceed with subaccount creation.
			 */
		
			// Generate a password for the subaccount.
			$password = wp_generate_password();
			
			
			$userinfo = array(
				'user_login' => $user_login,
				'user_email' => $email,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'user_pass' => $password,
				//'role' => 'customer'			// Leave commented, this way 
												// Settings > General > New User Default Role 
												// will apply (E.g. customer || subscriber)
			);


			// In case 'New User Default Role' has not been already properly set (E.g. customer || subscriber) in General settings.
			$default_user_role = get_option('default_role');
			
			if ( $default_user_role !== 'customer' && $default_user_role !== 'subscriber' ) {
			
				$userinfo['role'] = 'customer';
			}


			// Create the WordPress User object with the basic required information.
			$user_id = wp_insert_user( $userinfo );

			// If wp_insert_user executes successfully, it will return the id of the created user.
			if ( ! $user_id || is_wp_error( $user_id ) ) {

				// In case something went wrong with wp_insert_user, throw related errors.
				wc_add_notice( $user_id->get_error_message(), 'error');

			} else {
				
				// If wp_insert_user has executed successfully and the id of the created user has been returned,
				// use that id to insert Company name.
				update_user_meta( $user_id, 'billing_company', $company ); // Sanitized @ 1225.

				wc_add_notice( '<strong>' . esc_html__( 'Subaccount successfully added.', 'subaccounts-for-woocommerce' ) . '</strong><br>' . esc_html__( 'You can now switch to the newly added subaccount by selecting it from the drop-down menu.', 'subaccounts-for-woocommerce' ), 'success');			
			

				$already_children = get_user_meta( $user_parent, 'sfwc_children', true ); 	// We need to get the value of the array and update it by adding the new ID,
																							// otherwise array values which are already present will be overwritten and only the last ID will be added.
				// Check to see if thare are children already set...
				if ( is_array( $already_children ) && ! empty( $already_children ) ) {
				
					array_push( $already_children, (string)$user_id );
				  
				// ... If not, create a single element array and store it.
				} else {
					$already_children = array();
					$already_children[] = (string)$user_id;
				}

				update_user_meta( $user_parent, 'sfwc_children', $already_children );
				




				/**
				 * In case the logged in user has a "default" (or "") account level type AND has not got a manager above him,
				 * turn the user itself into a manager while adding a subaccount.
				 */


				/* Query of all Managers */
				$args_are_managers = array(
					//'role' => 'customer',
					//'role__in' => ['customer', 'subscriber'],
					'role__in' => $sfwc_option_selected_roles,
					'orderby' => 'ID',
					'order' => 'ASC',
					'meta_query' => array(
						array(
							'key' => 'sfwc_account_level_type',
							'value' => 'manager',
							'compare' => '=',
						),
					),
				);
				// The User Query
				$customers_are_managers = new WP_User_Query( $args_are_managers );
							
							
							
				// User Loop
				if ( ! empty( $customers_are_managers->get_results() ) ) {

					foreach ( $customers_are_managers->get_results() as $user ) {

						$list_of_children_for_single_user = get_user_meta( $user->ID, 'sfwc_children', true );

						if ( ! empty( $list_of_children_for_single_user ) ) {
							
							foreach ( $list_of_children_for_single_user as $single_id ) {

								$list_of_children_for_all_managers[] = $single_id;
							}
						}
					}
				}

					
				// Get account type of currently logged-in user
				$user_parent_account_type = get_user_meta( $user_parent, 'sfwc_account_level_type', true );



				/**
				 * Check to see if:
				 *
				 *	- 1st condition is for those cases where the plugin has been recently installed (first-time installations), thus no manager (or children of managers) have been set yet
				 *	- User logged in as default user that has not got a manager above him (in this case the user must be turned into a manager itself while adding a subaccount)
				 *
				 * If the condition is verified change account type from "default" (or "") to "manager".
				 */
				if ( ( ( $user_parent_account_type == "default" || $user_parent_account_type == "" ) && ! isset( $list_of_children_for_all_managers ) ) ||
					 ( ( $user_parent_account_type == "default" || $user_parent_account_type == "" ) && ( isset( $list_of_children_for_all_managers ) && is_array( $list_of_children_for_all_managers ) && ! in_array( $user_parent, $list_of_children_for_all_managers ) ) ) ) {

					// Turn a "default" user into "manager"
					update_user_meta( $user_parent, 'sfwc_account_level_type', 'manager' );
				}






				
				/**
				 * In case the subaccount is being added from a Supervisor account, make the subaccount a Manager
				 * by updating its 'sfwc_account_level_type' meta value.
				 */

				// Check account type of parent account.
				$check_account_type = get_user_meta( $user_parent, 'sfwc_account_level_type', true );
				
				if ( $check_account_type == 'supervisor') {
					
					update_user_meta( $user_id, 'sfwc_account_level_type', 'manager' );
				}





				
				/**
				 * Send WooCommerce "Customer New Account" email notification to newly created subaccount with username and link to set its the password.
				 *
				 * Make sure it's enabled:
				 *
				 * WooCommerce > Settings > Account and privacy 
				 * 
				 * 		- When creating an account, automatically generate an account password
				 *
				 * WooCommerce > Settings > Emails
				 *
				 *		- 'New Account' email
				 *
				 * https://stackoverflow.com/questions/61576356/send-an-email-notification-with-the-generated-password-on-woocommerce-user-creat/#answer-61582804
				 */
				 
				// Get all WooCommerce emails Objects from WC_Emails Object instance
				$emails = WC()->mailer()->get_emails();

				// Send "Customer New Account" email notification.
				$emails['WC_Email_Customer_New_Account']->trigger( $user_id, $password, true );




				// If subaccount has been successfully created, clear the form.
				if ( isset( $user_id ) && !is_wp_error( $user_id ) ) {
					
					$_POST = array();
				}
			}
		}
	}

	/**
	 * Debug.
	 */
	 
	//echo $user_parent;

	// $already_children = get_user_meta( $user_parent, 'sfwc_children', true );
	// echo '<pre>',print_r($already_children,1),'</pre>';

	// $check_account_type = get_user_meta( $user_parent, 'sfwc_account_level_type', true );
	// var_dump( $check_account_type );

}

/**
 * Do not change template_redirect as hook here.
 * 
 * This function needs to run before than:
 *
 *		woocommerce_before_account_navigation, see: add_action('woocommerce_before_account_navigation', 'sfwc_action_woocommerce_account_dashboard');
 *
 * so that the newly created subaccount will appear immediately after form submission within the user switcher (no page refresh required).
 */
add_action( 'template_redirect', 'sfwc_add_subaccount_form_handler', 10);	// Priority must be higher than sfwc_set_parent_account_cookie function for earlier execution.
																			// Otherwise the user switcher pane won't show instantly after the first subaccount creation for new accounts (page refresh would be required).




/**
 * Add "Subaccounts" menu item and content to My Account page (Pt.8).
 *
 * Handle form submitted data when editing subaccount.
 *
 * My Account -> Subaccounts -> Manage Subaccounts -> Edit Subaccount.
 */
function sfwc_frontend_edit_subaccount_form_handler() {
	
	// Make sure we don't interfere My Account -> Addresses forms.
	if ( is_wc_endpoint_url( 'subaccounts' ) ) {

		/**
		 * 
		 *
		 */
		 
		// Retrieve (Ajax) user_id of customer which is currently being edited My Account -> Subaccounts -> Manage Subaccounts.
		if ( isset( $_POST['sfwc_frontend_edit_subaccount_user_id'] ) ) {
			$user_id = absint( sanitize_text_field( $_POST['sfwc_frontend_edit_subaccount_user_id'] ) );
		} else {
			return;
		}
		
		// Before proceeding check if nonce is in place and verfy it.
		// Leave this after checking: isset( $_POST['sfwc_frontend_edit_subaccount_user_id'] )
		if ( ! isset( $_POST['sfwc_nonce_frontend_edit_subaccount_form'] ) || isset( $_POST['sfwc_nonce_frontend_edit_subaccount_form'] ) && ! wp_verify_nonce( $_POST['sfwc_nonce_frontend_edit_subaccount_form'], 'sfwc_nonce_frontend_edit_subaccount_action' ) ) {
			wc_add_notice( esc_html__( 'Nonce could not be verified.', 'subaccounts-for-woocommerce' ), 'error');
			return;
		}
		
		
		/**
		 * Handling of "Account Details" submitted data.
		 * 
		 */ 
		 

		// Sanitize and update user first_name.
		if ( isset( $_POST['first_name'] ) ) {
			$user_first_name_sanitized = sanitize_text_field( $_POST['first_name'] );
			update_user_meta( $user_id, 'first_name', $user_first_name_sanitized );
		}
		
		// Sanitize and update user last_name.
		if ( isset( $_POST['last_name'] ) ) {
			$user_last_name_sanitized = sanitize_text_field( $_POST['last_name'] );
			update_user_meta( $user_id, 'last_name', $user_last_name_sanitized );
		}
		
		// Sanitize and update user password.
		if ( isset( $_POST['password'] ) && $_POST['password'] != "" ) {
			
			$user_password = $_POST['password'];	// No need to sanitize here.
				
			$user_pass = array (
				'ID' => $user_id, 
				'user_pass' => $user_password,
			);

			$result = wp_update_user( $user_pass ); // wp_update_user() already take care of sanitizing with trim() and hashing.
			
			// Throw an error if the email is already used by another user.
			if ( is_wp_error( $result ) ) {
				$error_password = $result->get_error_message();
				wc_add_notice( esc_html__( $error_password, 'subaccounts-for-woocommerce' ), 'error');
			}
		}
		
		// Sanitize, validate and update user email.
		if ( isset( $_POST['email'] ) && $_POST['email'] != "" ) {
			
			$user_email_sanitized = sanitize_email( $_POST['email'] );
			
			// Verify that provided email is valid.
			if ( ! is_email( $user_email_sanitized ) ) {

				wc_add_notice( esc_html__( 'Email is not valid.', 'subaccounts-for-woocommerce' ), 'error');

			} else {
				
                $user_email = array (
                    'ID' => $user_id, 
                    'user_email' => $user_email_sanitized,
                );

                $result = wp_update_user( $user_email );
				
				// Throw an error if the email is already used by another user.
				if ( is_wp_error( $result ) ) {
					$error_email = $result->get_error_message();
					wc_add_notice( esc_html__( $error_email, 'subaccounts-for-woocommerce' ), 'error');
				}
			}
		}		
		
		
		

		/**
		 * Handling of Billing and Shipping submitted data.
		 *
		 * Quick and dirty adaptation of the sanitization/validation code present in:
		 * woocommerce > includes > class-wc-form-handler.php
		 *
		 * Code taken from WooCommerce 7.8.0.
		 * Part of the original code left as reference (commented out).
		 * 
		 */ 
		
		
		
		/*		
		global $wp;

		$nonce_value = wc_get_var( $_REQUEST['woocommerce-edit-address-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.

		if ( ! wp_verify_nonce( $nonce_value, 'woocommerce-edit_address' ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'edit_address' !== $_POST['action'] ) {
			return;
		}
		*/
		wc_nocache_headers();
		/*
		$user_id = get_current_user_id();
		*/
		if ( $user_id <= 0 ) {
			return;
		}

		$customer = new WC_Customer( $user_id );

		if ( ! $customer ) {
			return;
		}
		
		




		/**
		 * Handling of Billing submitted data.
		 *
		 * Quick and dirty adaptation of the sanitization/validation code present in:
		 * woocommerce > includes > class-wc-form-handler.php
		 *
		 * Code taken from WooCommerce 7.8.0.
		 * Part of the original code left as reference (commented out).
		 * 
		 */ 

		
		/*
		$load_address = isset( $wp->query_vars['edit-address'] ) ? wc_edit_address_i18n( sanitize_title( $wp->query_vars['edit-address'] ), true ) : 'billing';

		if ( ! isset( $_POST[ $load_address . '_country' ] ) ) {
			return;
		}
		*/
		$load_billing_address = 'billing';

		if ( ! isset( $_POST[ $load_billing_address . '_country' ] ) ) {
			return;
		}


		$address = WC()->countries->get_address_fields( wc_clean( wp_unslash( $_POST[ $load_billing_address . '_country' ] ) ), $load_billing_address . '_' );

		foreach ( $address as $key => $field ) {
			if ( ! isset( $field['type'] ) ) {
				$field['type'] = 'text';
			}

			// Get Value.
			if ( 'checkbox' === $field['type'] ) {
				$value = (int) isset( $_POST[ $key ] );
			} else {
				$value = isset( $_POST[ $key ] ) ? wc_clean( wp_unslash( $_POST[ $key ] ) ) : ''; // Sanitization.
			}

			// Hook to allow modification of value.
			$value = apply_filters( 'woocommerce_process_myaccount_field_' . $key, $value );

			// Validation: Required fields.
			if ( ! empty( $field['required'] ) && empty( $value ) ) {
				/* translators: %s: Field name. */
				wc_add_notice( sprintf( __( '%s is a required field.', 'woocommerce' ), $field['label'] ), 'error', array( 'id' => $key ) );
			}

			if ( ! empty( $value ) ) {
				// Validation and formatting rules.
				if ( ! empty( $field['validate'] ) && is_array( $field['validate'] ) ) {
					foreach ( $field['validate'] as $rule ) {
						switch ( $rule ) {
							case 'postcode':
								$country = wc_clean( wp_unslash( $_POST[ $load_billing_address . '_country' ] ) );
								$value   = wc_format_postcode( $value, $country );

								if ( '' !== $value && ! WC_Validation::is_postcode( $value, $country ) ) {
									switch ( $country ) {
										case 'IE':
											$postcode_validation_notice = __( 'Please enter a valid Eircode.', 'woocommerce' );
											break;
										default:
											$postcode_validation_notice = __( 'Please enter a valid postcode / ZIP.', 'woocommerce' );
									}
									wc_add_notice( $postcode_validation_notice, 'error' );
								}
								break;
							case 'phone':
								if ( '' !== $value && ! WC_Validation::is_phone( $value ) ) {
									/* translators: %s: Phone number. */
									wc_add_notice( sprintf( __( '%s is not a valid phone number.', 'woocommerce' ), '<strong>' . $field['label'] . '</strong>' ), 'error' );
								}
								break;
							case 'email':
								$value = strtolower( $value );

								if ( ! is_email( $value ) ) {
									/* translators: %s: Email address. */
									wc_add_notice( sprintf( __( '%s is not a valid email address.', 'woocommerce' ), '<strong>' . $field['label'] . '</strong>' ), 'error' );
								}
								break;
						}
					}
				}
			}

			try {
				// Set prop in customer object.
				if ( is_callable( array( $customer, "set_$key" ) ) ) {
					$customer->{"set_$key"}( $value );
				} else {
					$customer->update_meta_data( $key, $value );
				}
			} catch ( WC_Data_Exception $e ) {
				// Set notices. Ignore invalid billing email, since is already validated.
				if ( 'customer_invalid_billing_email' !== $e->getErrorCode() ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}
		
		
		

		
		
		/**
		 * Handling of Shipping submitted data.
		 *
		 * Quick and dirty adaptation of the sanitization/validation code present in:
		 * woocommerce > includes > class-wc-form-handler.php
		 *
		 * Code taken from WooCommerce 7.8.0.
		 * Part of the original code left as reference (commented out).
		 * 
		 */ 

		
		/*
		$load_address = isset( $wp->query_vars['edit-address'] ) ? wc_edit_address_i18n( sanitize_title( $wp->query_vars['edit-address'] ), true ) : 'billing';

		if ( ! isset( $_POST[ $load_address . '_country' ] ) ) {
			return;
		}
		*/
		$load_shipping_address = 'shipping';

		if ( ! isset( $_POST[ $load_shipping_address . '_country' ] ) ) {
			return;
		}


		$address = WC()->countries->get_address_fields( wc_clean( wp_unslash( $_POST[ $load_shipping_address . '_country' ] ) ), $load_shipping_address . '_' );

		foreach ( $address as $key => $field ) {
			if ( ! isset( $field['type'] ) ) {
				$field['type'] = 'text';
			}

			// Get Value.
			if ( 'checkbox' === $field['type'] ) {
				$value = (int) isset( $_POST[ $key ] );
			} else {
				$value = isset( $_POST[ $key ] ) ? wc_clean( wp_unslash( $_POST[ $key ] ) ) : ''; // Sanitization.
			}

			// Hook to allow modification of value.
			$value = apply_filters( 'woocommerce_process_myaccount_field_' . $key, $value );

			// Validation: Required fields.
			if ( ! empty( $field['required'] ) && empty( $value ) ) {
				/* translators: %s: Field name. */
				wc_add_notice( sprintf( __( '%s is a required field.', 'woocommerce' ), $field['label'] ), 'error', array( 'id' => $key ) );
			}

			if ( ! empty( $value ) ) {
				// Validation and formatting rules.
				if ( ! empty( $field['validate'] ) && is_array( $field['validate'] ) ) {
					foreach ( $field['validate'] as $rule ) {
						switch ( $rule ) {
							case 'postcode':
								$country = wc_clean( wp_unslash( $_POST[ $load_shipping_address . '_country' ] ) );
								$value   = wc_format_postcode( $value, $country );

								if ( '' !== $value && ! WC_Validation::is_postcode( $value, $country ) ) {
									switch ( $country ) {
										case 'IE':
											$postcode_validation_notice = __( 'Please enter a valid Eircode.', 'woocommerce' );
											break;
										default:
											$postcode_validation_notice = __( 'Please enter a valid postcode / ZIP.', 'woocommerce' );
									}
									wc_add_notice( $postcode_validation_notice, 'error' );
								}
								break;
							case 'phone':
								if ( '' !== $value && ! WC_Validation::is_phone( $value ) ) {
									/* translators: %s: Phone number. */
									wc_add_notice( sprintf( __( '%s is not a valid phone number.', 'woocommerce' ), '<strong>' . $field['label'] . '</strong>' ), 'error' );
								}
								break;
							case 'email':
								$value = strtolower( $value );

								if ( ! is_email( $value ) ) {
									/* translators: %s: Email address. */
									wc_add_notice( sprintf( __( '%s is not a valid email address.', 'woocommerce' ), '<strong>' . $field['label'] . '</strong>' ), 'error' );
								}
								break;
						}
					}
				}
			}

			try {
				// Set prop in customer object.
				if ( is_callable( array( $customer, "set_$key" ) ) ) {
					$customer->{"set_$key"}( $value );
				} else {
					$customer->update_meta_data( $key, $value );
				}
			} catch ( WC_Data_Exception $e ) {
				// Set notices. Ignore invalid billing email, since is already validated.
				if ( 'customer_invalid_billing_email' !== $e->getErrorCode() ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}
		
		

		/**
		 * Hook: woocommerce_after_save_address_validation.
		 *
		 * Allow developers to add custom validation logic and throw an error to prevent save.
		 *
		 * @param int         $user_id User ID being saved.
		 * @param string      $load_address Type of address e.g. billing or shipping.
		 * @param array       $address The address fields.
		 * @param WC_Customer $customer The customer object being saved. @since 3.6.0
		 */
		#do_action( 'woocommerce_after_save_address_validation', $user_id, $load_address, $address, $customer );

		if ( 0 < wc_notice_count( 'error' ) ) {
			return;
		}

		$customer->save();

		wc_add_notice( __( 'Address changed successfully.', 'woocommerce' ) );		
	
	}
}
add_action( 'template_redirect', 'sfwc_frontend_edit_subaccount_form_handler' );




/**
 * Check if the user is switching to another account and if so redirect him to the Dashboard endpoint after the user swtch.
 *
 * Premise: by default after an account switch a user is automatically redirected to the same My Account page endpoint he was before the switch.
 * 
 * This was causing a couple of issues:
 *
 *	1. If a Manager account was visiting the "Add Subaccount" endpoint before the switch,
 *	   after the switch to a subaccount was still on the "Add Subaccount" endpoint, despite of the page being blank (content conditionally removed, see above).
 *
 *	2. Also, if the above Manager decided to go back to its account (while still visiting the above subaccount's blank page),
 *	   was still redirected to its "Add Subaccount" endpoint. 
 *	   But at that point if he tried to add a new subaccount would get nonce error: "Nonce could not be verified".
 *
 * To prevent all of this, here it is the redirect.
 */
function sfwc_redirect_to_dashboard_after_account_switch () {
	if ( isset( $_POST['sfwc_frontend_children'] ) ) {
		wp_safe_redirect( esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) );
		exit;
	}
}
add_action('template_redirect', 'sfwc_redirect_to_dashboard_after_account_switch');




/**
 * Redirect Default customers with no parent set above them to "Add Subaccount" tab
 *
 * If a logged in customer:
 * 
 *	- has account type equal to Default (or "")
 *	- and has no parent account already set above him
 *
 * is eligible for adding a its own subaccounts.
 *
 * In this case we have to provide him the possibility to add a subaccount by showing the "Add Subaccount" tab/content.
 * 
 * The problem here is that the "Subaccounts" menu item points to: /my-account/subaccounts/
 * that in this case would show no content, since the user is a Default one and is not allowed to access this section.
 *
 * To solve this issue with this function we change the "Subaccounts" menu item URL in order to point to: /my-account/subaccounts/add-subaccount/
 */
function sfwc_redirect_default_user_with_no_parent ( $url, $endpoint, $value, $permalink ) {
	
	// Get Options settings.
	$sfwc_options = (array) get_option('sfwc_options');

	// Get Selected Roles option value from Options settings.
	$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');
	
	$target_url = esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) . 'subaccounts/add-subaccount' );
	
	// Check if logged in user has has a valid role.
	if ( is_user_logged_in() && sfwc_is_current_user_role_valid() && sfwc_is_current_user_role_enabled() ) {
		
		// Get ID of currently logged-in user
		$current_user_id = get_current_user_id();
		
		// Get account type of currently logged-in user
		$user_account_level_type = get_user_meta( $current_user_id, 'sfwc_account_level_type', true );
		
		
		
			/* Query of all Managers */
			$args_are_managers = array(
				//'role'    => 'customer',
				//'role__in' => ['customer', 'subscriber'],
				'role__in' => $sfwc_option_selected_roles,
				'orderby' => 'ID',
				'order' => 'ASC',
				'meta_query' => array(
					array(
						'key' => 'sfwc_account_level_type',
						'value' => 'manager',
						'compare' => '=',
					),
				),
			);
			// The User Query
			$customers_are_managers = new WP_User_Query( $args_are_managers );
						
						
						
			// User Loop
			if ( ! empty( $customers_are_managers->get_results() ) ) {

				foreach ( $customers_are_managers->get_results() as $user ) {

					$list_of_children_for_single_user = get_user_meta( $user->ID, 'sfwc_children', true );

					if ( ! empty( $list_of_children_for_single_user ) ) {
						
						foreach ( $list_of_children_for_single_user as $single_id ) {

							$list_of_children_for_all_managers[] = $single_id;
						}
					}
				}
			}



		/**
		 * Check to see if we are in one of these situations:
		 *
		 *	- User logged in as default user that has not got a manager above him
		 *	- 2nd condition is for those cases where the plugin has been recently installed (first-time installations), thus no manager (or children of managers) have been set yet
		 *
		 * If one of these conditions is verified we must redirect the user who clicks on "Subaccounts" menu item to /my-account/subaccounts/add-subaccount/
		 * in order to show 'Add Subaccount' tab content.
		 */
		if ( 
			( ( $user_account_level_type == "default" || $user_account_level_type == "" ) && ( isset( $list_of_children_for_all_managers ) && is_array( $list_of_children_for_all_managers ) && ! in_array( $current_user_id, $list_of_children_for_all_managers ) ) )
			|| ( ( $user_account_level_type == "default" || $user_account_level_type == "" ) && ! isset( $list_of_children_for_all_managers ) )
		) {

			if( $endpoint == 'subaccounts' && $value == "" ) {
				$url = $target_url;
			}
			
			
		}
	}
	
	return $url;
}
add_filter( 'woocommerce_get_endpoint_url', 'sfwc_redirect_default_user_with_no_parent', 10, 4 );




/**
 * For the following function DO NOT move validation of supervisor related values,
 * so that if Supervisor Add-on is deactivated and there are still customers with account level type set to 'supervisor'
 * the plugin will continue to store/display correct data.
 */
function sfwc_store_subaccounts_meta_data_on_order_creation( $order, $data ) {
	
	// Get Options settings.
	$sfwc_options = (array) get_option('sfwc_options');

	// Get Selected Roles option value from Options settings.
	$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');
	
	
	// Check if logged in user has a valid role.
	if ( is_user_logged_in() && sfwc_is_current_user_role_valid() && sfwc_is_current_user_role_enabled() ) {
	
		// Get user id from order
		$customer_id = $order->get_user_id();
		
		// Get Account Level Type
		$customer_account_level_type = get_user_meta( $customer_id, 'sfwc_account_level_type', true );
		







		if ( $customer_account_level_type == 'manager' || $customer_account_level_type == 'supervisor' ) {
	
			$manager_id = 'none';

		} else {
			
			
			/**
			 * Retrieve the ID of the manager related to Customer.
			 */
	
			// Get all users with user meta 'sfwc_account_level_type' == 'manager' and filter only the one which has 'sfwc_children' user_meta containing the child ID who made the order
			$args_manager = array(
				//'role'    => 'customer',
				//'role__in' => ['customer', 'subscriber'],
				'role__in' => $sfwc_option_selected_roles,
				'exclude' => $customer_id, // Exclude ID of customer who made currently displayed order
				'orderby' => 'ID',
				'order' => 'ASC',
				'meta_key' => 'sfwc_account_level_type',
				'meta_value' => 'manager',
				'meta_query' => array(
					array(
						'key' => 'sfwc_children',
						'value' => '"'.$customer_id.'"',
						'compare' => 'LIKE',
					),
				),
			);


			// The User Query
			$user_query_manager = new WP_User_Query( $args_manager );


			// User Loop
			if ( ! empty( $user_query_manager->get_results() ) ) {

				foreach ( $user_query_manager->get_results() as $user ) {
					
					$manager_id = $user->ID;
				}

			} else {
				
				$manager_id = 'not_set';
			}
				
		}		
				



 
		
	if ( $customer_account_level_type == 'supervisor' ) {
		
		$supervisor_id = 'none';
	}
	
	elseif ( $customer_account_level_type == 'manager' ) {
		
		/**
		 * Retrieve the Supervisor's ID related to Customer.
		 */

		// Get all users with user meta 'sfwc_account_level_type' == 'supervisor' and filter only the one which has 'sfwc_children' user_meta containing the ID of the Manager
		$args_supervisor = array(
			//'role'    => 'customer',
			//'role__in' => ['customer', 'subscriber'],
			'role__in' => $sfwc_option_selected_roles,
			'exclude' => $customer_id, // Exclude ID of customer who made currently displayed order
			'orderby' => 'ID',
			'order' => 'ASC',
			'meta_key' => 'sfwc_account_level_type',
			'meta_value' => 'supervisor',
			'meta_query' => array(
				array(
					'key' => 'sfwc_children',
					'value' => '"'.$customer_id.'"',
					'compare' => 'LIKE',
				),
			),
		);


		// The User Query
		$user_query_supervisor = new WP_User_Query( $args_supervisor );


		// User Loop
		if ( ! empty( $user_query_supervisor->get_results() ) ) {

			foreach ( $user_query_supervisor->get_results() as $user ) {
				
				$supervisor_id = $user->ID;
			}

		} else {
			
			$supervisor_id = 'not_set';
		}

	}

	elseif ( $customer_account_level_type == 'default' || $customer_account_level_type == '' || empty( $customer_account_level_type ) ) {

		// Get all users with user meta 'sfwc_account_level_type' == 'supervisor' and filter only the one which has 'sfwc_children' user_meta containing the ID of the Manager
		$args_supervisor = array(
			//'role'    => 'customer',
			//'role__in' => ['customer', 'subscriber'],
			'role__in' => $sfwc_option_selected_roles,
			'exclude' => $customer_id, // Exclude ID of customer who made currently displayed order
			'orderby' => 'ID',
			'order' => 'ASC',
			'meta_key' => 'sfwc_account_level_type',
			'meta_value' => 'supervisor',
			'meta_query' => array(
				array(
					'key' => 'sfwc_children',
					'value' => '"'.$manager_id.'"',
					'compare' => 'LIKE',
				),
			),
		);


		// The User Query
		$user_query_supervisor = new WP_User_Query( $args_supervisor );


		if ( ! empty( $user_query_manager->get_results() ) ) {
		
			// User Loop
			if ( ! empty( $user_query_supervisor->get_results() ) ) {

				foreach ( $user_query_supervisor->get_results() as $user ) {
					
					$supervisor_id = $user->ID;
				}

			} else {
				
				$supervisor_id = 'not_set';
			}
		
		} else {
				
				$supervisor_id = 'not_set';
		}
		
	}





		if ( isset($_COOKIE['is_supervisor'] ) ) {
			

			if ( $customer_account_level_type == 'supervisor' ) {
				
				// The order was placed by a supervisor for himself.
				$order_placed_by = 'supervisor_for_himself';

				
			} elseif ( $customer_account_level_type == 'manager' ) {
				
				// The order was placed by a manager for himself.
				$order_placed_by = 'supervisor_for_manager';

				
			} elseif ( $customer_account_level_type == 'default' || $customer_account_level_type == '' || $customer_account_level_type == null ) {
				
				// The order was placed by a manager on behalf of his subaccount.
				$order_placed_by = 'supervisor_for_default';
			}
			
		} elseif ( ! isset($_COOKIE['is_supervisor'] )  && isset($_COOKIE['is_manager'] ) ) {
			
			
			if ( $customer_account_level_type == 'manager' ) {
				
				// The order was placed by a manager for himself.
				$order_placed_by = 'manager_for_himself';
				
			} elseif ( $customer_account_level_type == 'default' || $customer_account_level_type == '' || $customer_account_level_type == null ) {
				
				// The order was placed by a manager on behalf of his subaccount.
				$order_placed_by = 'manager_for_default';
			}
			
		} else {
			
			// The order was placed by a 'default' customer for himself.
			$order_placed_by = 'default_for_himself';

		}




        $valid_customer_account_level_type = array( 'supervisor', 'manager', 'default', '' );

        if ( in_array( $customer_account_level_type, $valid_customer_account_level_type ) ) {
		
			// Store the customer's account level type.
			$order->update_meta_data( '_sfwc_customer_account_level_type', $customer_account_level_type );
		}



		if ( ( $supervisor_id && is_numeric( $supervisor_id ) && $supervisor_id >= 1 && preg_match( '/^[1-9][0-9]*$/', $supervisor_id ) ) || ( $supervisor_id && $supervisor_id == 'not_set' ) || ( $supervisor_id && $supervisor_id == 'none' ) ) {
		
			$valid_supervisor_id = sanitize_text_field( $supervisor_id );
			
			// Store the ID of the Supervisor related to the Customer.
			$order->update_meta_data( '_sfwc_customer_related_supervisor', $valid_supervisor_id );
		}
		
		
		/**
		 * Check if $manager_id (numeric string) is a positive number.
		 *
		 * 3			true
		 * 03			false
		 * 3.5			false
		 * 3,5			false
		 * +3			false
		 * -3			false
		 * 1337e0		false
		 */
		if ( ( $manager_id && is_numeric( $manager_id ) && $manager_id >= 1 && preg_match( '/^[1-9][0-9]*$/', $manager_id ) ) || ( $manager_id && $manager_id == 'not_set' )  || ( $manager_id && $manager_id == 'none' ) ) {
		
			$valid_manager_id = sanitize_text_field( $manager_id );
			
			// Store the ID of the Manager related to the Customer.
			$order->update_meta_data( '_sfwc_customer_related_manager', $valid_manager_id );
		}
		
		

		// Store info about who actually placed the order.
        $valid_order_placed_by = array( 'manager_for_himself', 'manager_for_default', 'default_for_himself', 'supervisor_for_himself', 'supervisor_for_manager', 'supervisor_for_default' );

        if ( in_array( $order_placed_by, $valid_order_placed_by ) ) {

			$order->update_meta_data( '_sfwc_order_placed_by', $order_placed_by );
        }
		
		$order->save();
	}
}
add_action('woocommerce_checkout_create_order', 'sfwc_store_subaccounts_meta_data_on_order_creation', 20, 2);







/**
 * In case an order is created on backend by Admin, _sfwc_order_placed_by order meta will be empty,
 * so update it with the proper value after customer has payed the order.
 *
 */
function sfwc_order_placed_by_update_order_meta_after_payment( $order_id ) {

    // Create an order instance.
    $order = wc_get_order( $order_id );
	
	// Get user id from order
	$customer_id = $order->get_user_id();
	
	
	// Get Account Level Type
	$customer_account_level_type = get_user_meta( $customer_id, 'sfwc_account_level_type', true );
	
	
	if ( ! isset($_COOKIE['is_supervisor'] )  && isset($_COOKIE['is_manager'] ) ) {
		
		
		if ( $customer_account_level_type == 'manager' ) {
			
			// The order was placed by a manager for himself.
			$order_placed_by = 'manager_for_himself';
			
		} elseif ( $customer_account_level_type == 'default' || $customer_account_level_type == '' || $customer_account_level_type == null ) {
			
			// The order was placed by a manager on behalf of his subaccount.
			$order_placed_by = 'manager_for_default';
		}
		
	} elseif ( ! isset($_COOKIE['is_supervisor'] )  && ! isset($_COOKIE['is_manager'] ) ) {
		
		// The order was placed by a 'default' customer for himself.
		$order_placed_by = 'default_for_himself';

	}
	
	
	if ( isset( $order_placed_by ) ) {	// Avoid undefined variable in case 
										// $customer_account_level_type == supervisor
										// which is handled by sfwc_order_placed_by_update_order_meta_after_payment_supervisor in Supervisor add-on

		// Store info about who actually placed the order.
		// In case of supervisor, see function: sfwc_order_placed_by_update_order_meta_after_payment_supervisor in Supervisor add-on
		$valid_order_placed_by = array( 'manager_for_himself', 'manager_for_default', 'default_for_himself' );

		if ( in_array( $order_placed_by, $valid_order_placed_by ) ) {
			
			$order->update_meta_data( '_sfwc_order_placed_by', $order_placed_by );
			$order->save();
		}
	}
}
add_action('woocommerce_thankyou', 'sfwc_order_placed_by_update_order_meta_after_payment', 10, 1);




/**
 * Adds new columns to the "Orders" table in the account.
 *
 * @param string[] $columns the columns in the orders table
 * @return string[] updated columns
 */
function sfwc_add_my_account_orders_column( $columns ) {

	$new_columns = array();

	foreach ( $columns as $key => $name ) {

		$new_columns[ $key ] = $name;

		// Add new columns after order status column.
		if ( 'order-status' === $key ) {
			
			// Add order-account column only when we viewing in "Subaccount Orders" table (no need for main "Orders" table).
			if ( is_wc_endpoint_url( 'subaccounts' ) ) {
				$new_columns['order-account'] = esc_html__( 'Account', 'subaccounts-for-woocommerce' );
			}
			
			// Add order-placed-by column.
			$new_columns['order-placed-by'] = esc_html__( 'Order placed by', 'subaccounts-for-woocommerce' );
		}
	}

	return $new_columns;
}
add_filter( 'woocommerce_account_orders_columns', 'sfwc_add_my_account_orders_column' );




/**
 * Adds data to the custom "Account" column.
 *
 */
function sfwc_my_account_orders_order_account( $order ) {
	
	$customer_id = $order->get_user_id();
	
	// Retrieve user data for customer related to order.
	$userdata_customer = get_userdata( $customer_id );
	
	// Get 'Customer Display Name' from Options settings.
	$sfwc_options = (array) get_option('sfwc_options');

	// Avoid undefined $sfwc_option_display_name in case related setting has not been saved yet.
	$sfwc_option_display_name = ( isset( $sfwc_options['sfwc_option_display_name'] ) ) ? $sfwc_options['sfwc_option_display_name'] : 'username';
	
	// Add content to column only when we viewing in "Subaccount Orders" table (no need for main "Orders" table).
	if ( is_wc_endpoint_url( 'subaccounts' ) ) {
		
		if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata( $userdata_customer->ID )->user_firstname || get_userdata( $userdata_customer->ID )->user_lastname ) ) {

			// Echo 'Full Name' (if either First Name or Last Name has been set)
			printf( '%1$s %2$s', esc_html( $userdata_customer->user_firstname ), esc_html( $userdata_customer->user_lastname ) );

		} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata( $userdata_customer->ID )->billing_company ) ) {

			// Echo 'Company' (if Company name has been set)
			printf( '%1$s', esc_html( $userdata_customer->billing_company ) );
		
		} else {

			// Otherwise echo 'Username'
			printf( '%1$s', esc_html( $userdata_customer->user_login ) );
		}
	}
}
add_action( 'woocommerce_my_account_my_orders_column_order-account', 'sfwc_my_account_orders_order_account' );




/**
 * Adds data to the custom "Order Placed By" column.
 *
 * For the following function DO NOT move validation of supervisor related values
 * so that if Supervisor Add-on is deactivated and there are still customers with account level type set to 'supervisor'
 * the plugin will continue to store/display correct data.
 */
function sfwc_my_account_orders_order_placed_by( $order ) {
	
	// Get Options settings.
	$sfwc_options = (array) get_option('sfwc_options');

	// Get 'Customer Display Name' from Options settings.
	$sfwc_option_display_name = ( isset( $sfwc_options['sfwc_option_display_name'] ) ) ? $sfwc_options['sfwc_option_display_name'] : 'username';
	
	// Get 'Subaccount Mode' from Options settings.
	$sfwc_option_subaccount_mode = ( isset( $sfwc_options['sfwc_option_subaccount_mode'] ) ) ? $sfwc_options['sfwc_option_subaccount_mode'] : 'sub_user';

	// Retrieve the customer who actually placed the order.
	$order_placed_by = $order->get_meta('_sfwc_order_placed_by');
	
	// Retrieve the ID of the customer who actually placed the order (Multi-User mode).
	$order_placed_by_user_id = $order->get_meta( '_sfwc_order_placed_by_user_id' );
	
	// Retrieve user data for customer who placed the order (Multi-User mode).
	$userdata_customer_placing_order = get_userdata( $order_placed_by_user_id );
	
	$customer_id = $order->get_user_id();
	
	// Retrieve user data for customer related to order.
	$userdata_customer = get_userdata( $customer_id );

	// Retrieve the ID of the Manager related to the Customer.
	$customer_related_manager_id = $order->get_meta('_sfwc_customer_related_manager');
	
	// Retrieve user data for Manager.
	$userdata_manager = get_userdata( $customer_related_manager_id );
	
	// Retrieve the ID of the Supervisor related to the Customer.
	$customer_related_supervisor_id = $order->get_meta('_sfwc_customer_related_supervisor');
	
	// Retrieve user data for Supervisor.
	$userdata_supervisor = get_userdata( $customer_related_supervisor_id );
	
	

	

	if ( $order_placed_by ) {
	
		if ( $order_placed_by == 'default_for_himself' ) {
			
			// Multi-User mode.
			if ( $sfwc_option_subaccount_mode == 'multi_user' ) {
				$userdata_customer = $userdata_customer_placing_order;
				echo( '<small><strong>' . esc_html__( 'Subaccount', 'subaccounts-for-woocommerce' ) . '</strong></small><br>' );
			}
			
			if ( $sfwc_option_subaccount_mode == 'sub_user' || ( $sfwc_option_subaccount_mode == 'multi_user' && ! empty( $userdata_customer_placing_order ) ) ) {
				
				if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata( $userdata_customer->ID )->user_firstname || get_userdata( $userdata_customer->ID )->user_lastname ) ) {
					
					printf( '%1$s %2$s', esc_html( $userdata_customer->user_firstname ), esc_html( $userdata_customer->user_lastname ) );
					
				} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata( $userdata_customer->ID )->billing_company ) ) {

					// Echo 'Company' (if Company name has been set)
					printf( '%1$s', esc_html( $userdata_customer->billing_company ) );
			
				} else {

					// Otherwise echo 'Username'
					printf( '%1$s', esc_html( $userdata_customer->user_login ) );
				}
			} 
			else {
				echo esc_html__( 'No information available', 'subaccounts-for-woocommerce' );
			}
			
		} elseif ( $order_placed_by == 'supervisor_for_himself' || $order_placed_by == 'manager_for_himself' ) {
			
			// Multi-User mode.
			if ( $sfwc_option_subaccount_mode == 'multi_user' ) {
				
				if ( $order_placed_by == 'supervisor_for_himself' ) {
					echo( '<small><strong>' . esc_html__( 'Supervisor', 'subaccounts-for-woocommerce' ) . '</strong></small><br>' );
				}
				elseif( $order_placed_by == 'manager_for_himself' ) {
					echo( '<small><strong>' . esc_html__( 'Manager', 'subaccounts-for-woocommerce' ) . '</strong></small><br>' );
				}
			}
			
			if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata( $userdata_customer->ID )->user_firstname || get_userdata( $userdata_customer->ID )->user_lastname ) ) {
				
				printf( '%1$s %2$s', esc_html( $userdata_customer->user_firstname ), esc_html( $userdata_customer->user_lastname ) );
				
			} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata( $userdata_customer->ID )->billing_company ) ) {

				// Echo 'Company' (if Company name has been set)
				printf( '%1$s', esc_html( $userdata_customer->billing_company ) );
		
			} else {

				// Otherwise echo 'Username'
				printf( '%1$s', esc_html( $userdata_customer->user_login ) );
			}
			
		} elseif ( $order_placed_by == 'supervisor_for_manager' || $order_placed_by == 'supervisor_for_default' ) {
			
			if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata( $userdata_supervisor->ID )->user_firstname || get_userdata( $userdata_supervisor->ID )->user_lastname ) ) {
				
				printf( '<small><strong>' . esc_html__( 'Supervisor', 'subaccounts-for-woocommerce' ) . '</strong></small><br>' . '%1$s %2$s', esc_html( $userdata_supervisor->user_firstname ), esc_html( $userdata_supervisor->user_lastname ) );
			
			} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata( $userdata_supervisor->ID )->billing_company ) ) {

				printf( '<small><strong>' . esc_html__( 'Supervisor', 'subaccounts-for-woocommerce' ) . '</strong></small><br>' . '%1$s', esc_html( $userdata_supervisor->billing_company ) );
		
			} else {

				printf( '<small><strong>' . esc_html__( 'Supervisor', 'subaccounts-for-woocommerce' ) . '</strong></small><br>' . '%1$s', esc_html( $userdata_supervisor->user_login ) );
			}
			
		} elseif ( $order_placed_by == 'manager_for_default' ) {
			
			if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata( $userdata_manager->ID )->user_firstname || get_userdata( $userdata_manager->ID )->user_lastname ) ) {
				
				printf( '<small><strong>' . esc_html__( 'Manager', 'subaccounts-for-woocommerce' ) . '</strong></small><br>' . '%1$s %2$s', esc_html( $userdata_manager->user_firstname ), esc_html( $userdata_manager->user_lastname ) );
			
			} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata( $userdata_manager->ID )->billing_company ) ) {

				printf( '<small><strong>' . esc_html__( 'Manager', 'subaccounts-for-woocommerce' ) . '</strong></small><br>' . '%1$s', esc_html( $userdata_manager->billing_company ) );
		
			} else {

				printf(  '<small><strong>' . esc_html__( 'Manager', 'subaccounts-for-woocommerce' ) . '</strong></small><br>' . '%1$s', esc_html( $userdata_manager->user_login ) );
			}
			
			
			
			// Multi-User mode.
			if ( $sfwc_option_subaccount_mode == 'multi_user' ) {

				printf(  '<br><small><strong>' . esc_html__( 'On behalf of', 'subaccounts-for-woocommerce' ) . '</strong></small><br>');
				
				if ( ! empty( $userdata_customer_placing_order ) ) {
				
					if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata( $userdata_customer_placing_order->ID )->user_firstname || get_userdata( $userdata_customer_placing_order->ID )->user_lastname ) ) {
						
						printf( '%1$s %2$s', esc_html( $userdata_customer_placing_order->user_firstname ), esc_html( $userdata_customer_placing_order->user_lastname ) );
						
					} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata( $userdata_customer_placing_order->ID )->billing_company ) ) {

						// Echo 'Company' (if Company name has been set)
						printf( '%1$s', esc_html( $userdata_customer_placing_order->billing_company ) );
				
					} else {

						// Otherwise echo 'Username'
						printf( '%1$s', esc_html( $userdata_customer_placing_order->user_login ) );
					}
				} else {
					echo esc_html__( 'No information available', 'subaccounts-for-woocommerce' );
				}
			}				
			
			
			
		}
		
	} else {
		echo esc_html__( 'No information available', 'subaccounts-for-woocommerce' );
	}

}
add_action( 'woocommerce_my_account_my_orders_column_order-placed-by', 'sfwc_my_account_orders_order_placed_by' );




/**
 * Show subaccount orders in My Account area of parent account (Pt.1).
 *
 * Adding the form in My Account -> Subaccounts -> Subaccount Orders.
 */
function sfwc_my_account_orders_add_select_subaccount_orders_form( $has_orders ) {

	// Sanitize cookie value.
	if ( isset( $_COOKIE['subaccount_ids'] ) ) {

		// Decode cookie
		$subaccount_ids_cookie_decoded = json_decode( str_replace ('\"','', $_COOKIE['subaccount_ids']), true );
		
		// Sanitize cookie value
		$subaccount_ids_cookie_sanitized = preg_replace('/[^0-9]/', '', $subaccount_ids_cookie_decoded);
	}
	
	// Sanitize posted values.
	if ( isset( $_POST['sfwc_subaccount_orders'] ) ) {
		$selected_subaccount_orders_sanitized = preg_replace('/[^0-9]/', '', $_POST['sfwc_subaccount_orders']);	// sanitize_text_field() only works for strings, so this is not the case.
	}																											// preg_replace: If subject (3rd parameter) is an array, then the search 
																												// and replace is performed on every entry of subject, and the return 
																												// value is an array as well.
	
	
	// Get 'Options' settings.
	$sfwc_options = (array) get_option( 'sfwc_options' );
		
	// Get Subaccount Mode option value from Options settings.
	$sfwc_option_subaccount_mode = ( isset( $sfwc_options['sfwc_option_subaccount_mode'] ) ) ? $sfwc_options['sfwc_option_subaccount_mode'] : 'sub_user';

	// Get 'Customer Display Name' from Options settings
	$sfwc_option_display_name = ( isset( $sfwc_options['sfwc_option_display_name'] ) ) ? $sfwc_options['sfwc_option_display_name'] : 'username';
	
	
	
	// Get ID of currently logged in user.
	$user_id = get_current_user_id();

	// Get Account Level Type of currently logged in user.
	$account_level_type = get_user_meta( $user_id, 'sfwc_account_level_type', true );
	
	// Get children (array) of currently logged in user.
	$children_ids = get_user_meta( $user_id, 'sfwc_children', true );	
	
	/**
	 * Remove no longer existing users from the $children_ids array,
	 * in case a user has been deleted (but still present within 'sfwc_children' meta of an ex parent account).
	 *
	 * Also exclude users whose role is not enabled in the plugin settings.
	 */
	$existing_children_ids = array();
	
	if ( ! empty ( $children_ids ) ) {
		
		foreach ( $children_ids as $single_id ) {
			
			// Check if user still exists.
			$user_exists = get_userdata( $single_id );
			if ( $user_exists !== false ) {
				
				if ( sfwc_is_user_role_valid( $single_id ) && sfwc_is_user_role_enabled( $single_id ) ) {
					
					$existing_children_ids[] = $single_id;
				}
			}
		}
	}
	
	// If Multi-User Mode is enabled include the user ID of the parent account among the users to show.
	if ( is_wc_endpoint_url( 'orders' ) && $sfwc_option_subaccount_mode == 'multi_user' ) {
		array_push( $existing_children_ids, $user_id );
	}
	
	
	
	
	// Only show the form to manager account types.
	if ( $account_level_type == 'manager' ) {
	
		/**
		 * If Sub-User mode is enabled, show the form in: My Account -> Subaccounts -> Subaccount Orders.
		 * If instead Multi-User mode is enabled, show the form in: My Account -> Orders.
		 */
		if ( ( is_wc_endpoint_url( 'subaccounts' ) && $sfwc_option_subaccount_mode == 'sub_user' ) || ( is_wc_endpoint_url( 'orders' ) && $sfwc_option_subaccount_mode == 'multi_user' ) ) {
		?>
			
			<div id="sfwc-select-subaccount-orders-form-wrap" style="margin-bottom:50px; background-color:#f4f4f4; padding:30px;">
				<form method="post">

					<?php wp_nonce_field( 'sfwc_filter_subaccount_orders_action', 'sfwc_filter_subaccount_orders' ); ?>
					
					<label for="sfwc-select-subaccount-orders" style="display:block; font-weight:600; font-size:16px; margin-bottom:10px; color:#484848;">
						<?php 
						if ( $sfwc_option_subaccount_mode == 'sub_user' ) {
							echo esc_html__( 'Select Subaccount Orders', 'subaccounts-for-woocommerce' );
						} elseif ( $sfwc_option_subaccount_mode == 'multi_user' ) {
							echo esc_html__( 'Filter Orders', 'subaccounts-for-woocommerce' );
						}
						?>
					</label>
					
					<div style="width:70%; float:left;">
						<select id="sfwc-select-subaccount-orders" name="sfwc_subaccount_orders[]" multiple placeholder="Select account...">

							<?php
							if ( ! empty( $existing_children_ids ) ) {

								foreach ( $existing_children_ids as $key => $value ) {
									
									// When Sub-User mode is enabled, keep selected options in the multi-select field in: My Account -> Subaccounts -> Subaccount Orders.
									if ( is_wc_endpoint_url( 'subaccounts' ) && $sfwc_option_subaccount_mode == 'sub_user' ) {

										// Check if value is 'selected' from stored cookie.
										if ( ! empty( $subaccount_ids_cookie_sanitized ) ) {
											$selected = in_array( $value, $subaccount_ids_cookie_sanitized ) ? ' selected="selected" ' : '';
										} else {
											$selected = '';
										}
									}
									// When Multi-User mode is enabled, keep selected options in the multi-select field in: My Account -> Orders.
									elseif ( is_wc_endpoint_url( 'orders' ) && $sfwc_option_subaccount_mode == 'multi_user' ) {
										
										// Check if value is 'selected' from posted data.
										$selected = selected( in_array( $value, $selected_subaccount_orders_sanitized ), true );
									}
									
									
									
									//Check 'Customer Display Name' in Subaccounts > Settings > Options and display it accordingly
									if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata($value)->user_firstname || get_userdata($value)->user_lastname ) ) {

										// Echo 'Full Name + Email' (if either First Name or Last Name has been set)
										echo "<option value=" . esc_attr( $value ) . " " . $selected .">" . esc_html( get_userdata($value)->user_firstname ) . " " . esc_html( get_userdata($value)->user_lastname ) . " - [" . esc_html( get_userdata($value)->user_email ) . "]</option>";

									} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata($value)->billing_company ) ) {

										// Echo 'Company + Email' (if Company name has been set)
										echo "<option value=" . esc_attr( $value ) . " " . $selected . ">" . esc_html( get_userdata($value)->billing_company ) . " - [" . esc_html( get_userdata($value)->user_email ) . "]</option>";

									} else {

										// Otherwise echo 'Username + Email'
										echo "<option value=" . esc_attr( $value ) . " " . $selected . ">" . esc_html( get_userdata($value)->user_login ) . " - [" . esc_html( get_userdata($value)->user_email ) . "]</option>";
									}
								}
							} 
							?>
							
						</select>	
					</div>
					<input type="submit" value="Submit" style="width:30%; float:left; font-weight:600;">
				</form>
			</div>
		<?php
		}
		?>

			<script>
			(function($) {

				// Select/deselect all
				// https://jsfiddle.net/9zme6krw/2/
				Selectize.define("select_remove_all_options", function(options) {
					if (this.settings.mode === "single") return;

					var self = this;

					self.setup = (function() {
						var original = self.setup;
						return function() {
							original.apply(this, arguments);

							var allBtn = $("<button type=\"button\" class=\"btn btn-xs btn-success\">Select All</button>");
							var clearBtn = $("<button type=\"button\" class=\"btn btn-xs btn-default\">Clear</button>");
							var btnGrp = $("<div class=\"selectize-plugin-select_remove_all_options-btn-grp\"></div>");
							btnGrp.append(allBtn, " ", clearBtn);

							allBtn.on("click", function() {
								self.setValue($.map(self.options, function(v, k) {
									return k
								}));
							});
							clearBtn.on("click", function() {
								self.setValue([]);
							});

							this.$wrapper.append(btnGrp)
						};
					})();
				});
				
				
				
				
				/**
				 * Plugin: "preserve_search" (selectize.js)
				 * Based on: "preserve_on_blur" of Eric M. Klingensmith
				 *
				 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
				 * file except in compliance with the License. You may obtain a copy of the License at:
				 * http://www.apache.org/licenses/LICENSE-2.0
				 *
				 * Unless required by applicable law or agreed to in writing, software distributed under
				 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
				 * ANY KIND, either express or implied. See the License for the specific language
				 * governing permissions and limitations under the License.
				 */
				Selectize.define('preserve_search', function (options) {
					var self = this;

					options.text = options.text || function (option) {
						return option[this.settings.labelField];
					};

					this.onBlur = (function (e) {
						var original = self.onBlur;

						return function (e) {
							// Capture the current input value
							var $input = this.$control_input;
							var inputValue = $input.val();

							// Do the default actions
							original.apply(this, [e]);

							// Set the value back                    
							this.setTextboxValue(inputValue);
						};
					})();

					this.onOptionSelect = (function (e) {
						var original = self.onOptionSelect;

						return function (e) {
							// Capture the current input value
							var $input = this.$control_input;
							var inputValue = $input.val();

							original.apply(this, [e]);
							this.setTextboxValue(inputValue);
							this.refreshOptions();
							if (this.currentResults.items.length <= 0) {
								this.setTextboxValue('');
								this.refreshOptions();
							}
						};
					})();
				});
			
			


				// Initialize Selectize.
				$("#sfwc-select-subaccount-orders").selectize({
					plugins:
					[
						"remove_button", 
						"select_remove_all_options",
						"preserve_search"
					]
				});
			

			})( jQuery );
			</script>
					
				
		<?php
		
	}
}
add_action('woocommerce_before_account_orders', 'sfwc_my_account_orders_add_select_subaccount_orders_form');




/**
 * Show subaccount orders in My Account area of parent account (Pt.2).
 *
 * Sanitizing, validating and submitting data.
 */
function sfwc_my_account_orders_query_subaccounts_orders( $args ) {
	
	
	
	// Sanitize cookie value.
	if ( isset( $_COOKIE['subaccount_ids'] ) ) {

		// Decode cookie
		$subaccount_ids_cookie_decoded = json_decode( str_replace ('\"','', $_COOKIE['subaccount_ids']), true );
		
		// Sanitize cookie value
		$subaccount_ids_cookie_sanitized = preg_replace('/[^0-9]/', '', $subaccount_ids_cookie_decoded);
	}
	
	// Sanitize posted values.
	if ( isset( $_POST['sfwc_subaccount_orders'] ) ) {
		$selected_subaccount_orders_sanitized = preg_replace('/[^0-9]/', '', $_POST['sfwc_subaccount_orders']);	// sanitize_text_field() only works for strings, so this is not the case.
	}																						// preg_replace: If subject (3rd parameter) is an array, then the search and replace is performed on every entry of subject,
																							// and the return value is an array as well.


	// Get ID of currently logged in user.
	$user_id = get_current_user_id();
	
	// Get children (array) of currently logged in user.																		
	$children_ids = get_user_meta( $user_id, 'sfwc_children', true );
	
	
	if ( is_wc_endpoint_url( 'subaccounts' ) ) {
		
		if ( ! isset( $selected_subaccount_orders_sanitized ) ) {
			$args['customer'] = false;		// Initially do not show any results.
		}

		/**
		 * Validation start.
		 *
		 * Only orders that belong to the currently logged in parent account ID or orders that belong to the parent's subaccount IDs are valid
		 */
		if ( isset( $selected_subaccount_orders_sanitized ) ) {
			
			
			// Check if nonce is in place and verfy it.
			if ( ! isset( $_POST['sfwc_filter_subaccount_orders'] ) || isset( $_POST['sfwc_filter_subaccount_orders'] ) && ! wp_verify_nonce( $_POST['sfwc_filter_subaccount_orders'], 'sfwc_filter_subaccount_orders_action' ) ) {
																													
				wc_print_notice( esc_html__( 'Nonce could not be verified.', 'subaccounts-for-woocommerce' ), 'error');

			} else {

				// Validate posted values.
				if ( ! empty( $selected_subaccount_orders_sanitized ) ) {

					foreach ( $selected_subaccount_orders_sanitized as $key => $value ) {

						// Prevent empty option values in case a user has been deleted,
						// but is still present within 'sfwc_children' meta of an ex parent account.
						$user_exists = get_userdata( $value );
						if ( $user_exists !== false ) {
							
							// Check if submitted data is valid.
							if ( in_array( $value, $children_ids, true ) || $value ==  $user_id ) {

								$validated_subaccount_orders[] = $value;
							
							} else {
								
								$not_valid_subaccount_orders[] = $value;
								
							}
						} else {
							$not_existing_subaccount_orders[] = $value;
						}
					}
				}





				if ( ! empty( $not_valid_subaccount_orders ) ) {

					foreach ( $not_valid_subaccount_orders as $key => $value ) {
						wc_print_notice( esc_html__( 'You are not allowed to select orders for user ID: ', 'subaccounts-for-woocommerce' ) . $value . '.', 'error');
					}
					
				}

				if ( ! empty( $not_existing_subaccount_orders ) ) {

					foreach ( $not_existing_subaccount_orders as $key => $value ) {
						wc_print_notice( esc_html__( 'Selected user ID: ', 'subaccounts-for-woocommerce' ) . $value . esc_html__( ' does not exist.', 'subaccounts-for-woocommerce' ), 'error');
					}
					
				}

				if ( ! empty( $validated_subaccount_orders ) ) {
				
					$args['customer'] = $validated_subaccount_orders;
				}
			}
		} 
		
		// Cookie validation.
		elseif ( isset( $subaccount_ids_cookie_sanitized ) && ! empty( $subaccount_ids_cookie_sanitized ) ) {

			foreach ( $subaccount_ids_cookie_sanitized as $key => $value ) {

				// Prevent empty option values in case a user has been deleted,
				// but is still present within 'sfwc_children' meta of an ex parent account.
				$user_exists = get_userdata( $value );
				if ( $user_exists !== false ) {
					
					// Check if submitted data is valid.
					if ( in_array( $value, $children_ids, true ) || $value ==  $user_id ) {

						$validated_subaccount_orders[] = $value;
					
					} else {
						
						$not_valid_subaccount_orders[] = $value;
						
					}
				} else {
					$not_existing_subaccount_orders[] = $value;
				}
			}






			if ( ! empty( $not_valid_subaccount_orders ) ) {

				foreach ( $not_valid_subaccount_orders as $key => $value ) {
					wc_print_notice( esc_html__( 'You are not allowed to select orders for user ID: ', 'subaccounts-for-woocommerce' ) . $value . '.', 'error');
				}
				
			}

			if ( ! empty( $not_existing_subaccount_orders ) ) {

				foreach ( $not_existing_subaccount_orders as $key => $value ) {
					wc_print_notice( esc_html__( 'Selected user ID: ', 'subaccounts-for-woocommerce' ) . $value . esc_html__( ' does not exist.', 'subaccounts-for-woocommerce' ), 'error');
				}
				
			}

			if ( ! empty( $validated_subaccount_orders ) ) {
			
				$args['customer'] = $validated_subaccount_orders;
			}
		}
				
				
					
		/***
		
		// For debug.
		if ( ! empty( $validated_subaccount_orders ) ) {
			echo '<p><strong>Validated</strong></p><pre>';
			print_r( $validated_subaccount_orders );
			echo '</pre>';
		}
		
		// For debug.
		if ( ! empty( $not_valid_subaccount_orders ) ) {
			echo '<p><strong>Not Validated</strong></p><pre>';
			print_r( $not_valid_subaccount_orders );
			echo '</pre>';
		}
		
		// For debug.
		if ( ! empty( $not_existing_subaccount_orders ) ) {
			echo '<p><strong>Not Existing</strong></p><pre>';
			print_r( $not_existing_subaccount_orders );
			echo '</pre>';
		}		

		// For debug.
		if ( ! empty( $args ) ) {
			echo '<pre>';
			print_r( $args );
			echo '</pre>';
		}
		
		***/
				
	}

				
	return $args;
}
add_filter('woocommerce_my_account_my_orders_query', 'sfwc_my_account_orders_query_subaccounts_orders');






/**
 * Show subaccount orders in My Account area of parent account (Pt.3).
 *
 * Set a cookie storing an array of IDs of subaccounts submitted in the Select Subaccount Orders form.
 *
 * This is necessary because otherwise those IDs are not kept in memory for alterating the orders query
 * when navigating through order pages with paginate_links().
 */
function sfwc_set_selected_subaccount_orders_cookie() {

	if ( isset( $_POST['sfwc_subaccount_orders'] ) ) {
		
		// Sanitizing
		$selected_subaccount_orders_sanitized = preg_replace('/[^0-9]/', '', $_POST['sfwc_subaccount_orders']);
		
		// Set cookie as an array (json encoded).
		setcookie('subaccount_ids', json_encode( $selected_subaccount_orders_sanitized ), [
			'expires' => '',
			'path' => COOKIEPATH,
			'domain' => COOKIE_DOMAIN,
			'secure' => is_ssl(),
			'httponly' => true,
			'samesite' => 'Strict',
		]);


		/**
		 * In My Account -> Subaccounts -> Subaccount Orders, redirect to first page after each form submission. 
		 * 
		 * This is to prevent the following scenario:
		 *
		 * If for example we are viewing page 4 of paginated orders of a particular subaccount and then we select another subaccount,
		 * after the form is submitted we are still on page 4. 
		 * The problem here is that if the selected subaccount has not made enough orders to be displayed on page 4, we get "No order has been made yet."
		 */
		if ( is_wc_endpoint_url( 'subaccounts' ) ) {
			wp_safe_redirect( esc_url( wc_get_account_endpoint_url( 'subaccounts/subaccount-orders' ) ) );
			exit;
		}
	}
}
add_action('init', 'sfwc_set_selected_subaccount_orders_cookie');




/**
 * Show subaccount orders in My Account area of parent account (Pt.4).
 *
 * When a parent account is viewing subaccount orders list table display the view_subaccount_order action only.
 */
function sfwc_modify_available_action_buttons( $actions, $order ){

	if ( is_wc_endpoint_url( 'subaccounts' ) ) {
		
		// Add "View Subaccount Order" button.
		$actions['view_subaccount_order'] = array(
			'url'  => esc_url( wc_get_endpoint_url( 'subaccounts/subaccount-orders' ) . '?order=' . $order->get_id() ),
			'name' => esc_html__( 'View', 'subaccounts-for-woocommerce' ),
		);
		
		// Unset all other buttons.
		foreach ( $actions as $key => $action ) {
			
			if ( $key !== 'view_subaccount_order' ) {
				unset( $actions[$key] );
			}
		}
	}
	
	return $actions;
}
add_filter('woocommerce_my_account_my_orders_actions', 'sfwc_modify_available_action_buttons', 9999, 2);




/**
 * Add "Account", "Account Type" and "Order placed by" information in "Order Details" page.
 *  
 * 		- My Account -> Orders -> Single Order
 *		- My Account -> Subaccounts -> Subaccount Orders -> Single Order
 *		- Order Received (Thank You Page)
 */
function sfwc_add_subaccounts_related_information_in_order_details_page( $order_id ) {

	$order = wc_get_order( $order_id );
	
	$customer_id = $order->get_user_id();
	
	// Retrieve user data for customer related to order.
	$userdata_customer = get_userdata( $customer_id );

	// Retrieve the customer who actually placed the order.
	$order_placed_by = $order->get_meta('_sfwc_order_placed_by');
	
	// Retrieve the customer who actually placed the order (Multi-User mode).
	$order_placed_by_user_id = $order->get_meta( '_sfwc_order_placed_by_user_id' );
	
	// Retrieve user data for customer who placed the order (Multi-User mode).
	$userdata_customer_placing_order = get_userdata( $order_placed_by_user_id );
	
	// Get Account Level Type
	$customer_account_level = $order->get_meta('_sfwc_customer_account_level_type');
	
	
	// Retrieve the ID of the Manager related to the Customer.
	$customer_related_manager_id = $order->get_meta('_sfwc_customer_related_manager');
	
	// Retrieve user data for Manager.
	$userdata_manager = get_userdata( $customer_related_manager_id );
	
	
	// Retrieve the ID of the Supervisor related to the Customer.
	// Leave it here even if Supervisor related.
	$customer_related_supervisor_id = $order->get_meta('_sfwc_customer_related_supervisor');
	
	// Retrieve user data for Supervisor.
	// Leave it here even if Supervisor related.
	$userdata_supervisor = get_userdata( $customer_related_supervisor_id );
	
	
	// Get 'Customer Display Name' from Options settings.
	$sfwc_options = (array) get_option('sfwc_options');

	// Avoid undefined $sfwc_option_display_name in case related setting has not been saved yet.
	$sfwc_option_display_name = ( isset( $sfwc_options['sfwc_option_display_name'] ) ) ? $sfwc_options['sfwc_option_display_name'] : 'username';
	
	// Get Subaccount Mode option value from Options settings.
	$sfwc_option_subaccount_mode = ( isset( $sfwc_options['sfwc_option_subaccount_mode'] ) ) ? $sfwc_options['sfwc_option_subaccount_mode'] : 'sub_user';

	/*
	if ( $customer_id ) {
		// Get roles the user who made the order is part of (as an array).
		$role_user_current_order = $userdata_customer->roles;
	}
	*/

	
	/**
	 * Check if order has still a user id associated ($customer_id). 
	 * If not checked, in case a customer is deleted after he made an order they appear wrong values.
	 * 
	 * Also check if the user who made the order has a valid role. Otherwise weird things might happen.
	 * E.g. If order was made by an Administrator (borderline case, but possible) wrong data is displayed.
	 */
	#if ( $customer_id && ( in_array( 'customer', $role_user_current_order ) || in_array( 'subscriber', $role_user_current_order ) ) ) {
	if ( $customer_id && ( sfwc_is_user_role_valid( $customer_id ) && sfwc_is_user_role_enabled( $customer_id ) ) ) {

		echo '<div style="padding: 10px; background: #f5f5f5; margin-bottom:20px;">';
		
		if ( $sfwc_option_subaccount_mode == 'sub_user' ) {

			/**
			 * Display Account tied to order.
			 */
			 
			//Check 'Customer Display Name' in Subaccounts > Settings > Options and display it accordingly
			if ( ( $sfwc_option_display_name == 'full_name' ) && ( $userdata_customer->user_firstname || $userdata_customer->user_lastname ) ) {

				// Echo 'Full Name + Email' (if either First Name or Last Name has been set)
				echo '<strong>' . esc_html__( 'Account', 'subaccounts-for-woocommerce' ) . ':</strong> ' . esc_html( $userdata_customer->user_firstname ) . ' ' . esc_html( $userdata_customer->user_lastname ) . ' (' . esc_html( $userdata_customer->user_email ) . ')';

			} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( $userdata_customer->billing_company ) ) {

				// Echo 'Company + Email' (if Company name has been set)
				echo '<strong>' . esc_html__( 'Account', 'subaccounts-for-woocommerce' ) . ':</strong> ' . esc_html( $userdata_customer->billing_company ) . ' (' . esc_html( $userdata_customer->user_email ) . ')';

			} else {

				// Otherwise echo 'Username + Email'
				echo '<strong>' . esc_html__( 'Account', 'subaccounts-for-woocommerce' ) . ':</strong> ' . esc_html( $userdata_customer->user_login ) . ' (' . esc_html( $userdata_customer->user_email ) . ')';
			}


			echo '<hr style="background-color: #e3e3e3; border: 0; height: 1px; margin: 10px 0 10px;">';


			/**
			 * Display Account Type
			 */
			
			echo '<strong>' . esc_html__( 'Account Type', 'subaccounts-for-woocommerce' ) . ':</strong> ';


			if ( $customer_account_level == 'supervisor' ) {

				echo esc_html__('Supervisor', 'subaccounts-for-woocommerce');

			} elseif ( $customer_account_level == 'manager' ) {

				echo esc_html__( 'Manager', 'subaccounts-for-woocommerce' );

			} else {

				echo esc_html__( 'Default', 'subaccounts-for-woocommerce' );

			}


			echo '<hr style="background-color: #e3e3e3; border: 0; height: 1px; margin: 10px 0 10px;">';
		}

		/**
		 * Display "Order placed by" information.
		 */
		echo '<strong>' . esc_html__( 'Order placed by', 'subaccounts-for-woocommerce' ) . ':</strong> ';

		if ( $order_placed_by ) {

			if ( $order_placed_by == 'manager_for_himself' ) {

				echo esc_html__( 'Manager', 'subaccounts-for-woocommerce' ) . ' <span style="background:#fdfdfd; padding:3px; border:1px solid #ebebeb; border-radius:6px; white-space:nowrap;">';
				
				
					if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata( $userdata_customer->ID )->user_firstname || get_userdata( $userdata_customer->ID )->user_lastname ) ) {

						// Echo 'Full Name' (if either First Name or Last Name has been set)
						printf( '%1$s %2$s', esc_html( $userdata_customer->user_firstname ), esc_html( $userdata_customer->user_lastname ) );
						

					} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata( $userdata_customer->ID )->billing_company ) ) {

						// Echo 'Company' (if Company name has been set)
						printf( '%1$s', esc_html( $userdata_customer->billing_company ) );
						

					} else {

						// Otherwise echo 'Username'
						printf( '%1$s', esc_html( $userdata_customer->user_login ) );
						
					}
					
					echo '</span>';
					
					// Sub-User mode.
					if ( $sfwc_option_subaccount_mode == 'sub_user' ) {
						
						echo ' ' . esc_html__( 'for himself', 'subaccounts-for-woocommerce' );
					}
					
					echo '.';	
			
			} elseif ( $order_placed_by == 'manager_for_default' ) {
				
				// Multi-User mode.
				if ( $sfwc_option_subaccount_mode == 'multi_user' ) {
				
					/**
					 * $userdata_customer_placing_order not available yet at Thank You page.
					 * But in case of default_for_himself, $userdata_customer_placing_order is still the same as $userdata_customer at Thank You ('order-received') page.
					 */
					$userdata_customer = is_wc_endpoint_url( 'order-received' ) ?  $userdata_customer : $userdata_customer_placing_order;
				}
			
				echo esc_html__( 'Manager', 'subaccounts-for-woocommerce' ) . ' <span style="background:#fdfdfd; padding:3px; border:1px solid #ebebeb; border-radius:6px; white-space:nowrap;">';

					if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata($userdata_manager->ID)->user_firstname || get_userdata($userdata_manager->ID)->user_lastname ) ) {

						// Echo 'Full Name' (if either First Name or Last Name has been set)
						printf( '%1$s %2$s', esc_html( $userdata_manager->user_firstname ), esc_html( $userdata_manager->user_lastname ) );
						

					} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata($userdata_manager->ID)->billing_company ) ) {

						// Echo 'Company' (if Company name has been set)
						printf( '%1$s', esc_html( $userdata_manager->billing_company ) );
						

					} else {

						// Otherwise echo 'Username'
						printf( '%1$s', esc_html( $userdata_manager->user_login ) );
						
					}
					
				echo '</span> ' . esc_html__( 'on behalf of', 'subaccounts-for-woocommerce' ) . ' ' . esc_html__( 'subaccount', 'subaccounts-for-woocommerce' ) . ' <span style="background:#fdfdfd; padding:3px; border:1px solid #ebebeb; border-radius:6px; white-space:nowrap;">';
					
					if ( $sfwc_option_subaccount_mode == 'sub_user' || ( $sfwc_option_subaccount_mode == 'multi_user' && ! empty( $userdata_customer_placing_order ) ) ) {
					
						if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata( $userdata_customer->ID )->user_firstname || get_userdata( $userdata_customer->ID )->user_lastname ) ) {

							// Echo 'Full Name' (if either First Name or Last Name has been set)
							printf( '%1$s %2$s', esc_html( $userdata_customer->user_firstname ), esc_html( $userdata_customer->user_lastname ) );
							

						} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata( $userdata_customer->ID )->billing_company ) ) {

							// Echo 'Company' (if Company name has been set)
							printf( '%1$s', esc_html( $userdata_customer->billing_company ) );
							
						} else {

							// Otherwise echo 'Username'
							printf( '%1$s', esc_html( $userdata_customer->user_login ) );
						}
					} else {
						echo esc_html__( 'No information available', 'subaccounts-for-woocommerce' );
					}
					
				echo '</span>';
				
			} elseif ( $order_placed_by == 'default_for_himself' ) {
				
				// Sub-User mode.
				if ( $sfwc_option_subaccount_mode == 'sub_user' ) {
				
					echo esc_html__( 'Customer', 'subaccounts-for-woocommerce' ) . ' <span style="background:#fdfdfd; padding:3px; border:1px solid #ebebeb; border-radius:6px; white-space:nowrap;">';
					
						if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata($userdata_customer->ID)->user_firstname || get_userdata($userdata_customer->ID)->user_lastname ) ) {

							// Echo 'Full Name' (if either First Name or Last Name has been set)
							printf( '%1$s %2$s', esc_html( $userdata_customer->user_firstname ), esc_html( $userdata_customer->user_lastname ) );
							

						} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata($userdata_customer->ID)->billing_company ) ) {

							// Echo 'Company' (if Company name has been set)
							printf( '%1$s', esc_html( $userdata_customer->billing_company ) );
							

						} else {

							// Otherwise echo 'Username'
							printf( '%1$s', esc_html( $userdata_customer->user_login ) );
							
						}
						
					echo '</span> ' . esc_html__( 'for himself', 'subaccounts-for-woocommerce' ) . '.';
				}
				
				// Multi-User mode.
				elseif ( $sfwc_option_subaccount_mode == 'multi_user' ) {
					
					/**
					 * $userdata_customer_placing_order not available yet at Thank You page.
					 * But in case of default_for_himself, $userdata_customer_placing_order is still the same as $userdata_customer at Thank You ('order-received') page.
					 */
					$userdata_customer_placing_order = is_wc_endpoint_url( 'order-received' ) ?  $userdata_customer : $userdata_customer_placing_order;
					
					echo esc_html__( 'Subaccount', 'subaccounts-for-woocommerce' ) . ' <span style="background:#fdfdfd; padding:3px; border:1px solid #ebebeb; border-radius:6px; white-space:nowrap;">';
					
					if ( ! empty( $userdata_customer_placing_order ) ) {

						if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata($userdata_customer_placing_order->ID)->user_firstname || get_userdata($userdata_customer_placing_order->ID)->user_lastname ) ) {

							// Echo 'Full Name' (if either First Name or Last Name has been set)
							printf( '%1$s %2$s', esc_html( $userdata_customer_placing_order->user_firstname ), esc_html( $userdata_customer_placing_order->user_lastname ) );
							

						} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata($userdata_customer_placing_order->ID)->billing_company ) ) {

							// Echo 'Company' (if Company name has been set)
							printf( '%1$s', esc_html( $userdata_customer_placing_order->billing_company ) );
							
						} else {

							// Otherwise echo 'Username'
							printf( '%1$s', esc_html( $userdata_customer_placing_order->user_login ) );
						}
					} else {
						echo esc_html__( 'No information available', 'subaccounts-for-woocommerce' );
					}
						
					echo '</span>.';
				}
			}

			/**
			 * Even if Supervisor related, DO NOT move the following code to the Supervisor Add-On,
			 * so that if the Supervisor Add-on is deactivated and there are old orders placed by a supervisor
			 * the plugin will continue to display correct data.
			 */
			elseif ( $order_placed_by == 'supervisor_for_himself' ) {

				echo esc_html__( 'Supervisor', 'subaccounts-for-woocommerce' ) . ' <span style="background:#fdfdfd; padding:3px; border:1px solid #ebebeb; border-radius:6px; white-space:nowrap;">';
				
					if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata( $userdata_customer->ID )->user_firstname || get_userdata( $userdata_customer->ID )->user_lastname ) ) {

						// Echo 'Full Name' (if either First Name or Last Name has been set)
						printf( '%1$s %2$s', esc_html( $userdata_customer->user_firstname ), esc_html( $userdata_customer->user_lastname ) );
						

					} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata( $userdata_customer->ID )->billing_company ) ) {

						// Echo 'Company' (if Company name has been set)
						printf( '%1$s', esc_html( $userdata_customer->billing_company ) );
						

					} else {

						// Otherwise echo 'Username'
						printf( '%1$s', esc_html( $userdata_customer->user_login ) );
						
					}
				
					echo '</span>';
					
					// Sub-User mode.
					if ( $sfwc_option_subaccount_mode == 'sub_user' ) {
						
						echo ' ' . esc_html__( 'for himself', 'subaccounts-for-woocommerce' );
					}
					
					echo '.';	
			
			} elseif ( $order_placed_by == 'supervisor_for_manager' ) {
				
				// Multi-User mode.
				if ( $sfwc_option_subaccount_mode == 'multi_user' ) {
				
					/**
					 * $userdata_customer_placing_order not available yet at Thank You page.
					 * But in case of default_for_himself, $userdata_customer_placing_order is still the same as $userdata_customer at Thank You ('order-received') page.
					 */
					$userdata_customer = is_wc_endpoint_url( 'order-received' ) ?  $userdata_customer : $userdata_customer_placing_order;
				}
			
				echo esc_html__( 'Supervisor', 'subaccounts-for-woocommerce' ) . ' <span style="background:#fdfdfd; padding:3px; border:1px solid #ebebeb; border-radius:6px; white-space:nowrap;">';

					if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata( $userdata_supervisor->ID )->user_firstname || get_userdata( $userdata_supervisor->ID )->user_lastname ) ) {

						// Echo 'Full Name' (if either First Name or Last Name has been set)
						printf( '%1$s %2$s', esc_html( $userdata_supervisor->user_firstname ), esc_html( $userdata_supervisor->user_lastname ) );
						

					} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata( $userdata_supervisor->ID )->billing_company ) ) {

						// Echo 'Company' (if Company name has been set)
						printf( '%1$s', esc_html( $userdata_supervisor->billing_company ) );
						

					} else {

						// Otherwise echo 'Username'
						printf( '%1$s', esc_html( $userdata_supervisor->user_login ) );
						
					}

				echo '</span> ' . esc_html__( 'on behalf of', 'subaccounts-for-woocommerce' ) . ' ' . esc_html__( 'Manager', 'subaccounts-for-woocommerce' ) . ' <span style="background:#fdfdfd; padding:3px; border:1px solid #ebebeb; border-radius:6px; white-space:nowrap;">';
					
					if ( $sfwc_option_subaccount_mode == 'sub_user' || ( $sfwc_option_subaccount_mode == 'multi_user' && ! empty( $userdata_customer_placing_order ) ) ) {
						
						if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata($userdata_customer->ID)->user_firstname || get_userdata($userdata_customer->ID)->user_lastname ) ) {

							// Echo 'Full Name' (if either First Name or Last Name has been set)
							printf( '%1$s %2$s', esc_html( $userdata_customer->user_firstname ), esc_html( $userdata_customer->user_lastname ) );
							

						} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata( $userdata_customer->ID )->billing_company ) ) {

							// Echo 'Company' (if Company name has been set)
							printf( '%1$s', esc_html( $userdata_customer->billing_company ) );

						} else {

							// Otherwise echo 'Username'
							printf( '%1$s', esc_html( $userdata_customer->user_login ) );
						}
					} else {
						echo esc_html__( 'No information available', 'subaccounts-for-woocommerce' );
					}
					
					echo '</span>';
			
			} elseif ( $order_placed_by == 'supervisor_for_default' ) {
				
				// Multi-User mode.
				if ( $sfwc_option_subaccount_mode == 'multi_user' ) {
				
					/**
					 * $userdata_customer_placing_order not available yet at Thank You page.
					 * But in case of default_for_himself, $userdata_customer_placing_order is still the same as $userdata_customer at Thank You ('order-received') page.
					 */
					$userdata_customer = is_wc_endpoint_url( 'order-received' ) ?  $userdata_customer : $userdata_customer_placing_order;
				}
			
				echo esc_html__( 'Supervisor', 'subaccounts-for-woocommerce' ) . ' <span style="background:#fdfdfd; padding:3px; border:1px solid #ebebeb; border-radius:6px; white-space:nowrap;">';

					if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata( $userdata_supervisor->ID )->user_firstname || get_userdata($userdata_supervisor->ID)->user_lastname ) ) {

						// Echo 'Full Name' (if either First Name or Last Name has been set)
						printf( '%1$s %2$s', esc_html( $userdata_supervisor->user_firstname ), esc_html( $userdata_supervisor->user_lastname ) );
						

					} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata( $userdata_supervisor->ID )->billing_company ) ) {

						// Echo 'Company' (if Company name has been set)
						printf( '%1$s', esc_html( $userdata_supervisor->billing_company ) );
						

					} else {

						// Otherwise echo 'Username'
						printf( '%1$s', esc_html( $userdata_supervisor->user_login ) );
						
					}

				echo '</span> ' . esc_html__( 'on behalf of', 'subaccounts-for-woocommerce' ) . ' ' . esc_html__( 'subaccount', 'subaccounts-for-woocommerce' ) . ' <span style="background:#fdfdfd; padding:3px; border:1px solid #ebebeb; border-radius:6px; white-space:nowrap;">';
				
					if ( $sfwc_option_subaccount_mode == 'sub_user' || ( $sfwc_option_subaccount_mode == 'multi_user' && ! empty( $userdata_customer_placing_order ) ) ) {
						
						if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata( $userdata_customer->ID )->user_firstname || get_userdata( $userdata_customer->ID )->user_lastname ) ) {

							// Echo 'Full Name' (if either First Name or Last Name has been set)
							printf( '%1$s %2$s', esc_html( $userdata_customer->user_firstname ), esc_html( $userdata_customer->user_lastname ) );
							

						} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata( $userdata_customer->ID )->billing_company ) ) {

							// Echo 'Company' (if Company name has been set)
							printf( '%1$s', esc_html( $userdata_customer->billing_company ) );
							
						} else {

							// Otherwise echo 'Username'
							printf( '%1$s', esc_html( $userdata_customer->user_login ) );
						}
					} else {
						echo esc_html__( 'No information available', 'subaccounts-for-woocommerce' );
					}
					
					echo '</span>';
			}
			
		} else {
			
			// In case order has been created by admin from backend
			// see also: sfwc_order_placed_by_update_order_meta_after_payment function in: subaccounts-for-woocommerce > public > my-account.php.
			
			
			if( ! $order->get_date_paid() ) {
				
				// Order has NOT been paid for yet.

				
				// If an order is created by an admin on backend, we will know who actually placed the order (who paid for it) only after the order has been paid (from frontend My Account area).
				echo esc_html__( 'No information yet available', 'subaccounts-for-woocommerce' ) . '. <br>' . esc_html__( 'This information will be available after the customer has paid for the order.', 'subaccounts-for-woocommerce' );


			} else {
				
				// Order is paid.
				
				
				// Most probably the order was marked as processing/completed by the admin from backend.
				echo esc_html__( 'No information available', 'subaccounts-for-woocommerce' ) . '. <br>' . esc_html__( 'This order was probably marked by the administrator as Completed or Processing.', 'subaccounts-for-woocommerce' );
			}
		}

	echo '</div>';	
	}
}
add_action( 'woocommerce_order_details_before_order_table', 'sfwc_add_subaccounts_related_information_in_order_details_page' );




/**
 * Multi-User mode A.K.A. Company Accounts (Pt.1).
 * 
 * Populate subaccount's checkout fields with "Billing Firstname", "Billing Lastname" and "Billing Email" of the Manager.
 */
function sfwc_multi_user_populate_subaccount_checkout_fields_with_manager_billing_firstname_lastname_email( $value, $input ) {
	
	if ( is_plugin_active( 'sfwc-supervisor-addon/sfwc-supervisor-addon.php' ) ) {
		// In case Supervisor Add-On is active, use "Billing Firstname", "Billing Lastname" and "Billing Email" 
		// of the Supervisor (or Manager in case there is no Supervisor set).
		// See function: sfwc_multi_user_populate_subaccount_checkout_fields_with_parent_billing_firstname_lastname_email
		return $value;
	}
	
	// Get 'Options' settings.
    $sfwc_options = (array) get_option( 'sfwc_options' );
	
	// Get Subaccount Mode option value from Options settings.
	$sfwc_option_subaccount_mode = ( isset( $sfwc_options['sfwc_option_subaccount_mode'] ) ) ? $sfwc_options['sfwc_option_subaccount_mode'] : 'sub_user';
	
	// Get Selected Roles option value from Options settings.
	$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');

	// Get currently logged in user ID.
	$current_user_id = get_current_user_id();
	
	// Get account type of currently logged in user.
	$user_account_level_type = get_user_meta( $current_user_id, 'sfwc_account_level_type', true );
	
	// Get the user object of the current user from the user ID.
	$current_user = new WC_Customer( $current_user_id );
	
	// If no Billing First Name is set, use First Name of the user as a fallback.
	$current_user_billing_first_name = '' !== $current_user->get_billing_first_name() ? $current_user->get_billing_first_name() : $current_user->get_first_name();

	// If no Billing Last Name is set, use Last Name of the user as a fallback.
	$current_user_billing_last_name = '' !== $current_user->get_billing_last_name() ? $current_user->get_billing_last_name() : $current_user->get_last_name();
	
	// If no Billing Email is set, use Email of the user as a fallback.
	$current_user_billing_email = '' !== $current_user->get_billing_email() ? $current_user->get_billing_email() : $current_user->get_email();
	
	
	
	if ( is_user_logged_in() && sfwc_is_current_user_role_valid() && sfwc_is_current_user_role_enabled() ) {

		// Check if currently logged in user has a Default account type.
		if ( $user_account_level_type != 'supervisor' && $user_account_level_type != 'manager' ) {
			
			/**
			* Retrieve the ID of the manager related to the subaccount.
			*/

			// Get all users with user meta 'sfwc_account_level_type' == 'manager' and filter only the one which has 'sfwc_children' user_meta containing the child ID corrisponding to the subaccount.
			$args_manager = array(
				'role__in' => $sfwc_option_selected_roles,
				'exclude' => $current_user_id, 
				'orderby' => 'ID',
				'order' => 'ASC',
				'meta_key' => 'sfwc_account_level_type',
				'meta_value' => 'manager',
				'meta_query' => array(
				   array(
					'key' => 'sfwc_children',
					'value' => '"'.$current_user_id.'"',
					'compare' => 'LIKE',
				   ),
				),
			);


			// The User Query.
			$user_query_manager = new WP_User_Query( $args_manager );


			// User Loop.
			if ( ! empty( $user_query_manager->get_results() ) ) {

				foreach ( $user_query_manager->get_results() as $user ) {
										   
					$manager_id = $user->ID;
				}
			} else {		   
				return; // If the user being edited has not a Manager above him,
						// there's no need to proceed.
			}
			
			// Get the user object of the Manager from the user ID.
			$manager = new WC_Customer( $manager_id );
			
			
			/**
			 * On the checkout page, the "Billing First Name" and "Billing Last Name" of the subaccount are shown (even if
			 * with function sfwc_multi_user_assign_order_to_manager_after_successful_order we are replacing the User ID of the customer tied to the order.
			 *
			 * And this happens regardless of whether the PRO version is installed or not and whether the billing address inheritance is enabled or not.
			 * In fact in Sub-User Mode the "Billing First Name", "Billing Last Name" and "Billing Email" are deliberately left out from the inheritance function.
			 * 
			 * So in multi-user mode it is necessary to programmatically set the "Billing First Name", "Billing Last Name" and "Billing Email" of the Manager.
			 *
			 * If we also need billing/shipping addresses to be inherited from the parent, we need to force this from the plugin settings (PRO required).
			 * Or, in case the Pro version is not installed, it is necessary to set the addresses of the subaccount manually so that they are identical to those of the parent.
			 */
			 
			// If no Billing First Name is set, use First Name of the user as a fallback.
			$manager_billing_first_name = '' !== $manager->get_billing_first_name() ? $manager->get_billing_first_name() : $manager->get_first_name();
		
			// If no Billing Last Name is set, use Last Name of the user as a fallback.
			$manager_billing_last_name = '' !== $manager->get_billing_last_name() ? $manager->get_billing_last_name() : $manager->get_last_name();
			
			// If no Billing Email is set, use Email of the user as a fallback.
			$manager_billing_email = '' !== $manager->get_billing_email() ? $manager->get_billing_email() : $manager->get_email();
			
			if ( $sfwc_option_subaccount_mode ==  'multi_user' ) {
				
				$checkout_fields = array(
					// Get Manager's billing details.
					'billing_first_name'    => sanitize_text_field( $manager_billing_first_name ),
					'billing_last_name'     => sanitize_text_field( $manager_billing_last_name ),
					'billing_email'         => sanitize_email( $manager_billing_email ),
				);
			}
			// We need to explicitly set the subaccount details back in case multi_user is disabled, to avoid checkout fields 
			// continuing to popolate with Manager's details after previously placed orders with multi_user option enabled.
			else {
				
				$checkout_fields = array(
					// Get Manager's billing details.
					'billing_first_name'    => sanitize_text_field( $current_user_billing_first_name ),
					'billing_last_name'     => sanitize_text_field( $current_user_billing_last_name ),
					'billing_email'         => sanitize_email( $current_user_billing_email ),
				);
			}
		}
	}
	
	if ( ! empty( $checkout_fields ) ) {
		
		// Populate checkout fileds with billing and shipping details.
		foreach( $checkout_fields as $key_field => $field_value ){
			if( $input == $key_field ){
				$value = $field_value;
			}
		}
	}
		
	return $value;
}
add_filter( 'woocommerce_checkout_get_value', 'sfwc_multi_user_populate_subaccount_checkout_fields_with_manager_billing_firstname_lastname_email', 15, 2 ); // Priority 15: must run after function
																																							// sfwc_populate_subaccount_checkout_fields_with_manager_data (priority 10).



/**
 * Multi-User mode A.K.A. Company Accounts (Pt.2).
 *
 * Prevent WooCommerce from updating user data after checkout.
 */
 function sfwc_multi_user_prevent_woocommerce_from_updating_user_data_after_checkout() {
	
	$sfwc_options = (array) get_option('sfwc_options');
	
    $sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');
	
	$sfwc_option_subaccount_mode = ( isset( $sfwc_options['sfwc_option_subaccount_mode'] ) ) ? $sfwc_options['sfwc_option_subaccount_mode'] : 'sub_user';
	
	$sfwc_options_subaccounts_inherit_shipping_address_from_manager	= ( isset( $sfwc_options['sfwc_options_subaccounts_inherit_shipping_address_from_manager'] ) ) ? 
																		$sfwc_options['sfwc_options_subaccounts_inherit_shipping_address_from_manager'] : 0;
	
	$sfwc_options_subaccounts_inherit_billing_address_from_manager 	= ( isset( $sfwc_options['sfwc_options_subaccounts_inherit_billing_address_from_manager'] ) ) ? 
																		$sfwc_options['sfwc_options_subaccounts_inherit_billing_address_from_manager'] : 0;
																			
	if ( ! is_admin() && is_user_logged_in() && sfwc_is_current_user_role_valid() && sfwc_is_current_user_role_enabled() ) { 	// Do not check
																																// ! wp_doing_ajax() 
																																// here.
		
		// Get currently logged in user ID.
		$current_user_id = get_current_user_id();
		
		// Get account type of currently logged in user.
		$user_account_level_type = get_user_meta( $current_user_id, 'sfwc_account_level_type', true );

		
		/**
		* Check to see if the user currently being edited is a subaccount (by verifying if has a Manager above him),
		* or a simple standalone customer.
		*/

		// Get all users with user meta 'sfwc_account_level_type' == 'manager' and filter only the one which has 'sfwc_children' user_meta containing the child ID corrisponding to the subaccount.
		$args_manager = array(
			//'role'    => 'customer',
			//'role__in' => ['customer', 'subscriber'],
			'role__in' => $sfwc_option_selected_roles,
			'exclude' => $current_user_id, 
			'orderby' => 'ID',
			'order' => 'ASC',
			'meta_key' => 'sfwc_account_level_type',
			'meta_value' => 'manager',
			'meta_query' => array(
			   array(
				'key' => 'sfwc_children',
				'value' => '"'.$current_user_id.'"',
				'compare' => 'LIKE',
			   ),
			),
		);


		// The User Query
		$user_query_manager = new WP_User_Query( $args_manager );


		// User Loop
		if ( ! empty( $user_query_manager->get_results() ) ) {

			foreach ( $user_query_manager->get_results() as $user ) {
									   
				$manager_id = $user->ID;
			}
		} else {		   
			return;	// If the user being edited has not a Manager above him, it means it's a default standalone customer and there's no need to proceed.
		}		
		

		// Check if currently logged in user has a Default account type.
		if ( $user_account_level_type != 'supervisor' && $user_account_level_type != 'manager' ) {
		
			// Verify options.
			if ( $sfwc_option_subaccount_mode == 'multi_user' ) {
	
				add_filter( 'woocommerce_checkout_update_customer_data', '__return_false' );
			}
		}
	}
}
add_action( 'init', 'sfwc_multi_user_prevent_woocommerce_from_updating_user_data_after_checkout' );




/**
 * Multi-User mode A.K.A. Company Accounts (Pt.3).
 * 
 * Assign the order placed by a subaccount to its Manager after a successful order.
 */
function sfwc_multi_user_assign_order_to_manager_after_successful_order( $order_id ) {
	
	if ( is_plugin_active( 'sfwc-supervisor-addon/sfwc-supervisor-addon.php' ) ) {
		// In case Supervisor Add-On is active, the order placed by a Manager or a Subaccount must be assigned to the Supervior.
		// (or to the Manager in case there is no Supervisor set).
		// See function: sfwc_multi_user_assign_order_to_parent_after_successful_order
		return;
	}
	
	// Get 'Options' settings.
    $sfwc_options = (array) get_option( 'sfwc_options' );
	
	// Get Subaccount Mode option value from Options settings.
	$sfwc_option_subaccount_mode = ( isset( $sfwc_options['sfwc_option_subaccount_mode'] ) ) ? $sfwc_options['sfwc_option_subaccount_mode'] : 'sub_user';
	
	// Get Selected Roles option value from Options settings.
	$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');
	
	// Get order object from order ID.
	$order = wc_get_order( $order_id );
	
	// Get user ID from the order.
	$user_id = $order->get_user_id();
	
	// Get account level type from user ID.
	$user_account_level_type = get_user_meta( $user_id, 'sfwc_account_level_type', true );
	
	if ( is_user_logged_in() && sfwc_is_current_user_role_valid() && sfwc_is_current_user_role_enabled() ) {
		
		if ( $sfwc_option_subaccount_mode ==  'multi_user' ) {
		
			// Check if currently logged in user has a Default account type.
			if ( $user_account_level_type != 'supervisor' && $user_account_level_type != 'manager' ) {
				
				/**
				 * Retrieve the ID of the manager related to the subaccount.
				 */

				// Get all users with user meta 'sfwc_account_level_type' == 'manager' and filter only the one which has 'sfwc_children' user_meta containing the child ID corrisponding to the subaccount.
				$args_manager = array(
					'role__in' => $sfwc_option_selected_roles,
					'exclude' => $user_id, 
					'orderby' => 'ID',
					'order' => 'ASC',
					'meta_key' => 'sfwc_account_level_type',
					'meta_value' => 'manager',
					'meta_query' => array(
					   array(
						'key' => 'sfwc_children',
						'value' => '"'.$user_id.'"',
						'compare' => 'LIKE',
					   ),
					),
				);


				// The User Query.
				$user_query_manager = new WP_User_Query( $args_manager );


				// User Loop.
				if ( ! empty( $user_query_manager->get_results() ) ) {

					foreach ( $user_query_manager->get_results() as $user ) {
											   
						$manager_id = $user->ID;
					}
				} else {		   
					return; // If the user being edited has not a Manager above him,
							// there's no need to proceed.
				}
				
				// Get user object from user ID.
				$manager = new WC_Customer( $manager_id );
				
				
				/**
				 * In the order list on the admin side, the "Billing First Name" and "Billing Last Name" of the subaccount is shown in the "Order" column (even if
				 * with this function we are replacing the User ID of the customer tied to the order.
				 *
				 * And this happens regardless of whether the PRO version is installed or not and whether the billing address inheritance is enabled or not.
				 * In fact in Sub-User Mode the "Billing First Name", "Billing Last Name" and "Billing Email" are deliberately left out from the inheritance function.
				 * 
				 * So in multi-user mode it is necessary to programmatically set the "Billing First Name", "Billing Last Name" and "Billing Email" of the Manager.
				 *
				 * If we also need billing/shipping addresses to be inherited from the parent, we need to force this from the plugin settings (PRO required).
				 * Or, in case the Pro version is not installed, it is necessary to set the addresses of the subaccount manually so that they are identical to those of the parent.
				 */
				 
				// If no Billing First Name is set, use First Name of the user as a fallback.
				$manager_billing_first_name = '' !== $manager->get_billing_first_name() ? $manager->get_billing_first_name() : $manager->get_first_name();
			
				// If no Billing Last Name is set, use Last Name of the user as a fallback.
				$manager_billing_last_name = '' !== $manager->get_billing_last_name() ? $manager->get_billing_last_name() : $manager->get_last_name();
				
				// If no Billing Email is set, use Email of the user as a fallback.
				$manager_billing_email = '' !== $manager->get_billing_email() ? $manager->get_billing_email() : $manager->get_email();
				
				// Set data.
				$order->set_billing_first_name( sanitize_text_field( $manager_billing_first_name ) );
				$order->set_billing_last_name( sanitize_text_field( $manager_billing_last_name ) );
				$order->set_billing_email( sanitize_email( $manager_billing_email ) );
				$order->set_customer_id( absint( $manager_id ) );
				
				
				/**
				 * In Multi-User mode, the customer ID associated with an order is replaced with the Manager's user ID:
				 *
				 *		$order->set_customer_id( absint( $manager_id ) );
				 *
				 * For this reason, in order to keep track of the ID of the customer who did place the order,
				 * we need to store it somewhere.
				 *
				 * _sfwc_order_placed_by_user_id meta has been added (only for Multi-User mode) for this purpose.
				 *
				 * Note: Despite being similar to _sfwc_order_placed_by, _sfwc_order_placed_by_user_id and _sfwc_order_placed_by
				 * have completely different purposes and store different kinds of values.
				 */
				$order->update_meta_data( '_sfwc_order_placed_by_user_id', absint( $user_id ) );
				
				// Update the order.
				$order->save();
			}
		}
	}
}
add_action( 'woocommerce_thankyou', 'sfwc_multi_user_assign_order_to_manager_after_successful_order', 20, 1 ); // Both woocommerce_new_order and woocommerce_thankyou hooks can be used.




/**
 * Multi-User mode A.K.A. Company Accounts (Pt.4).		// Maybe integrate this function with: sfwc_my_account_orders_query_subaccounts_orders
 * 
 * Sanitize, validate submitted data and show orders in: My Account -> Orders for Managers.
 */
function sfwc_multi_user_my_account_orders_query_subaccounts_orders( $args ) {

	/*	
	// Sanitize cookie value.
	if ( isset( $_COOKIE['subaccount_ids'] ) ) {

		// Decode cookie
		$subaccount_ids_cookie_decoded = json_decode( str_replace ('\"','', $_COOKIE['subaccount_ids']), true );
		
		// Sanitize cookie value
		$subaccount_ids_cookie_sanitized = preg_replace('/[^0-9]/', '', $subaccount_ids_cookie_decoded);
	}
	*/
	
	// Sanitize posted values.
	if ( isset( $_POST['sfwc_subaccount_orders'] ) ) {
		$selected_subaccount_orders_sanitized = preg_replace('/[^0-9]/', '', $_POST['sfwc_subaccount_orders']);	// sanitize_text_field() only works for strings, so this is not the case.
	}																											// preg_replace: If subject (3rd parameter) is an array, then the search and replace 
																												// is performed on every entry of subject, and the return value is an array as well.
																							
	// Get 'Options' settings.
    $sfwc_options = (array) get_option( 'sfwc_options' );
	
	// Get Subaccount Mode option value from Options settings.
	$sfwc_option_subaccount_mode = ( isset( $sfwc_options['sfwc_option_subaccount_mode'] ) ) ? $sfwc_options['sfwc_option_subaccount_mode'] : 'sub_user';
	
	// Get Selected Roles option value from Options settings.
	$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');
	
	// Get ID of currently logged in user.
	$user_id = get_current_user_id();
	
	// Get Account Level Type of currently logged in user.
	$account_level_type = get_user_meta( $user_id, 'sfwc_account_level_type', true );
	

	
	if ( is_wc_endpoint_url( 'orders' ) && $sfwc_option_subaccount_mode == 'multi_user' ) {	// <--- NOT is_wc_endpoint_url( 'subaccounts' ) here,
																							// see: sfwc_my_account_orders_query_subaccounts_orders.
		// Check if user is a Manager.
		if ( $account_level_type == 'manager' ) {
			
			
			// Get children (array) of currently logged in user.																		
			$children_ids = get_user_meta( $user_id, 'sfwc_children', true );
			
			// Retrieve all orders of the Manager (in Multi-User mode this will include also orders placed by subaccounts).
			$args_manager_orders = array(
				'customer_id' => $user_id,
				'limit' => -1, // Retrieve all orders.
			);
			$orders = wc_get_orders( $args_manager_orders );
			
			/**
			 * Remove no longer existing users from the $children_ids array,
			 * in case a user has been deleted (but still present within 'sfwc_children' meta of an ex parent account).
			 *
			 * Also exclude users whose role is not enabled in the plugin settings.
			 */
			$existing_children_ids = array();
			
			if ( ! empty ( $children_ids ) ) {
				
				foreach ( $children_ids as $single_id ) {
					
					// Check if user still exists.
					$user_exists = get_userdata( $single_id );
					if ( $user_exists !== false ) {
						
						if ( sfwc_is_user_role_valid( $single_id ) && sfwc_is_user_role_enabled( $single_id ) ) {
							
							$existing_children_ids[] = $single_id;
						}
					}
				}
			}
			
			// Include the user ID of the Manager among the orders to show.
			array_push( $existing_children_ids, $user_id );			
			
			
			
			
			
			
			
			
			
				
			if ( ! isset( $selected_subaccount_orders_sanitized ) ) {			
				
				$args['customer'] = $existing_children_ids;		// Initially show all results: both Manager and subaccount orders.
			}

			/**
			 * Validation start.
			 *
			 * Only orders that belong to the currently logged in parent account ID or orders that belong to the parent's subaccount IDs are valid.
			 */
			if ( isset( $selected_subaccount_orders_sanitized ) ) {
				
				
				// Check if nonce is in place and verfy it.
				if ( ! isset( $_POST['sfwc_filter_subaccount_orders'] ) || isset( $_POST['sfwc_filter_subaccount_orders'] ) && ! wp_verify_nonce( $_POST['sfwc_filter_subaccount_orders'], 'sfwc_filter_subaccount_orders_action' ) ) {
																														
					wc_print_notice( esc_html__( 'Nonce could not be verified.', 'subaccounts-for-woocommerce' ), 'error');

				} else {

					// Validate posted values.
					if ( ! empty( $selected_subaccount_orders_sanitized ) ) {

						foreach ( $selected_subaccount_orders_sanitized as $key => $value ) {

							// Prevent empty option values in case a user has been deleted,
							// but is still present within 'sfwc_children' meta of an ex parent account.
							$user_exists = get_userdata( $value );
							if ( $user_exists !== false ) {
								
								// Check if submitted data is valid.
								if ( in_array( $value, $children_ids, true ) || $value ==  $user_id ) {
									
									if ( ! empty( $orders ) ) {
										
										foreach ( $orders as $order ) {

											$sfwc_order_placed_by_user_id = $order->get_meta( '_sfwc_order_placed_by_user_id' );
											
											
											if ( $sfwc_order_placed_by_user_id == $value || $user_id  == $value ) {
											
												$validated_subaccount_orders[] = absint( $value );
											}
										}
									}
								} else {
									$not_valid_subaccount_orders[] = absint( $value );
								}
							} else {
								$not_existing_subaccount_orders[] = absint( $value );
							}
						}
					}





					if ( ! empty( $not_valid_subaccount_orders ) ) {

						foreach ( $not_valid_subaccount_orders as $key => $value ) {
							wc_print_notice( esc_html__( 'You are not allowed to select orders for user ID: ', 'subaccounts-for-woocommerce' ) . $value . '.', 'error');
						}
						
					}

					if ( ! empty( $not_existing_subaccount_orders ) ) {

						foreach ( $not_existing_subaccount_orders as $key => $value ) {
							wc_print_notice( esc_html__( 'Selected user ID: ', 'subaccounts-for-woocommerce' ) . $value . esc_html__( ' does not exist.', 'subaccounts-for-woocommerce' ), 'error');
						}
						
					}

					if ( ! empty( $validated_subaccount_orders ) ) {
						
						$args['meta_key'] = '_sfwc_order_placed_by_user_id';
						$args['meta_value'] = $validated_subaccount_orders;
						$args['meta_compare'] = 'IN';
						
						
						if ( in_array( $user_id, $validated_subaccount_orders ) ) { // In this case (at least for now) the above args are probably unnecessary, in fact if the order search 
																					// includes the Manager's orders, also all of the subaccounts orders would be shown, despite of the values
																					// of the '_sfwc_order_placed_by_user_id' key. So maybe:
																					//
																					//
																					//	if ( in_array( $user_id, $validated_subaccount_orders ) ) {
																					//		$args['meta_key'] = '_customer_user';
																					//		$args['meta_value'] = $user_id;
																					//		$args['meta_compare'] = 'LIKE';
																					//	} else {
																					//		$args['meta_key'] = '_sfwc_order_placed_by_user_id';
																					//		$args['meta_value'] = $validated_subaccount_orders;
																					//		$args['meta_compare'] = 'IN';
																					//	}
																					//
																					//
																					// Also, selecting the Manager would be the same as resetting the search to default (by clicking on "Clear").
																					// If confirmed, even showing the Manager as an option in the multi-select field would be unnecessary (unless - if selected - we 
																					// decide to exclude orders not placed directly by the Manager), since that would be the same as resetting the search to default by 
																					// clicking on "Clear" and performing a new a submit.
							
							$args['meta_key'] = '_customer_user';
							$args['meta_value'] = $user_id;
							$args['meta_compare'] = 'LIKE';
						}
						
					}
					else {
						$args['customer'] = false;	// If the search did not produce any results,
													// don't show any orders.
					}
				}
			}
		}
		elseif ( $account_level_type !== 'manager' && $account_level_type !== 'supervisor' && ! is_plugin_active( 'sfwc-supervisor-addon/sfwc-supervisor-addon.php' ) ) { // In case Supervisor Add-On is active, see:
																																										  // sfwc_multi_user_my_account_orders_query_subaccounts_orders_for_supervisors
			
			/**
			* Check to see if the user currently being edited is a subaccount (by verifying if has a Manager above him),
			* or a simple standalone customer.
			*/

			// Get all users with user meta 'sfwc_account_level_type' == 'manager' and filter only the one which has 'sfwc_children' user_meta containing the child ID corrisponding to the subaccount.
			$args_manager = array(
				//'role'    => 'customer',
				//'role__in' => ['customer', 'subscriber'],
				'role__in' => $sfwc_option_selected_roles,
				'exclude' => $user_id, 
				'orderby' => 'ID',
				'order' => 'ASC',
				'meta_key' => 'sfwc_account_level_type',
				'meta_value' => 'manager',
				'meta_query' => array(
				   array(
					'key' => 'sfwc_children',
					'value' => '"'.$user_id.'"',
					'compare' => 'LIKE',
				   ),
				),
			);


			// The User Query
			$user_query_manager = new WP_User_Query( $args_manager );


			// User Loop
			if ( ! empty( $user_query_manager->get_results() ) ) {

				foreach ( $user_query_manager->get_results() as $user ) {
										   
					$manager_id = $user->ID;
				}
			} else {		   
				return $args;	// If the user being edited has not a Manager above him, 
								// it means it's a default standalone customer and there's no need to alter the query.
			}
			
			$args['customer'] = $manager_id;
			$args['meta_key'] = '_sfwc_order_placed_by_user_id';
			$args['meta_value'] = $user_id;
			$args['meta_compare'] = '=';
		}
	}

				
	return $args;
}
add_filter('woocommerce_my_account_my_orders_query', 'sfwc_multi_user_my_account_orders_query_subaccounts_orders');




/**
 * Multi-User mode A.K.A. Company Accounts (Pt.5).
 * 
 * Allow subaccount to view Manager orders in: My Account -> Orders -> View.
 *
 * When Multi-User mode is enabled, all orders placed by a subaccount are assigned to its Manager 
 * and displayed in My Account -> Orders area of the subaccount based on the _sfwc_order_placed_by_user_id order meta value.
 *
 * For this reason it is necessary to guarantee the subaccount the appropriate permissions to be able to view the order.
 */
function sfwc_multi_user_allow_subaccount_to_view_manager_orders( $allcaps, $caps, $args, $user ) {
	
	global $wp;

	// Get 'Options' settings.
    $sfwc_options = (array) get_option( 'sfwc_options' );
	
	// Get Subaccount Mode option value from Options settings.
	$sfwc_option_subaccount_mode = ( isset( $sfwc_options['sfwc_option_subaccount_mode'] ) ) ? $sfwc_options['sfwc_option_subaccount_mode'] : 'sub_user';
	
	// Get Selected Roles option value from Options settings.
	$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');
	
	// Get ID of currently logged in user.
	$user_id = get_current_user_id();
	
	// Get Account Level Type of currently logged in user.
	$account_level_type = get_user_meta( $user_id, 'sfwc_account_level_type', true );
	

	
	
	
	if ( is_user_logged_in() && $account_level_type !== 'manager' && $account_level_type !== 'supervisor' && sfwc_is_current_user_role_valid() && sfwc_is_current_user_role_enabled() ) {
		
		if ( $sfwc_option_subaccount_mode == 'multi_user' && is_wc_endpoint_url( 'view-order' ) ) {
			
			// Get ID of currently viewed order in: My Account -> Orders -> View Order.
			$order_id = absint( $wp->query_vars['view-order'] );
			
			// Create an order instance.
			$order = new WC_Order( $order_id );
			
			// Get the costumer ID from order object.
			$customer_id = $order->get_user_id();
			
			// Retrieve the ID of the customer who did actually placed the order.
			$sfwc_order_placed_by_user_id = $order->get_meta('_sfwc_order_placed_by_user_id');
			
			
			/**
			* Check to see if the user currently being edited is a subaccount (by verifying if has a Manager above him),
			* or a simple standalone customer.
			*/

			// Get all users with user meta 'sfwc_account_level_type' == 'manager' and filter only the one which has 'sfwc_children' user_meta containing the child ID corrisponding to the subaccount.
			$args_manager = array(
				//'role'    => 'customer',
				//'role__in' => ['customer', 'subscriber'],
				'role__in' => $sfwc_option_selected_roles,
				'exclude' => $user_id, 
				'orderby' => 'ID',
				'order' => 'ASC',
				'meta_key' => 'sfwc_account_level_type',
				'meta_value' => 'manager',
				'meta_query' => array(
				   array(
					'key' => 'sfwc_children',
					'value' => '"'.$user_id.'"',
					'compare' => 'LIKE',
				   ),
				),
			);


			// The User Query
			$user_query_manager = new WP_User_Query( $args_manager );


			// User Loop
			if ( ! empty( $user_query_manager->get_results() ) ) {	// Logged-in user has a Manager above him,
																	// so we can proceed.
			
			
				foreach ( $user_query_manager->get_results() as $user ) {
										   
					$manager_id = $user->ID;
				}
				
				
				// Some permission checks.
				if ( $manager_id == $customer_id ) {
					
					if ( absint( $sfwc_order_placed_by_user_id ) ===  $user_id ) {
					
						if ( $caps[0] == 'view_order' 
							 #|| $caps[0] == 'order_again'
						) {		
							$allcaps[ $caps[0] ] = true;
						}
					}
				}
			}
		}
	}
	
    return ( $allcaps );
}
add_filter( 'user_has_cap', 'sfwc_multi_user_allow_subaccount_to_view_manager_orders', 10, 4 );
