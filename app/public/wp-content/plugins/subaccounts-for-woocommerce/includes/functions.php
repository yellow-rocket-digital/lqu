<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}




/**
 * Helper function to determine if current user role is eligible/valid for the subaccount system.
 * 
 * User Role should not have higher permissions than a Customer (or Subscriber) role, whose only capability is (by default) 'read'.
 * Two verification modes are available.
 *
 * @since 1.3.0
 * 
 *  -------------
 * | Strict Mode |
 *  -------------
 * 
 * It is the default mode.
 * 
 * We basically get all the roles assigned to the current user (a user could have more than one role assigned),
 * for each role assigned we get all the capabilities and merge them into a single array.
 * 
 * After that, we compare all the (merged) user capabilities against another array containing all potential WordPress user capabilities,
 * including those added by WooCommerce.
 *
 * If the only capability in common is the 'read' one, the current user role is eligible/valid for the subaccount system.
 * 
 *  ------------
 * | Loose Mode |
 *  ------------
 *
 * Since the Strict Mode is quite restrictive, we provide a way to loosen it up a bit.
 * 
 * To enable Loose Mode, it is necessary to define the following constant in wp-config.php file:
 *
 * define( 'SFWC_VALID_ROLE_LOOSE_MODE', true );
 * 
 * With Loose Mode enabled, the only conditions necessary for a user role to be valid are that it has 'read' capability and that it does
 * not have 'edit_posts' capability (Contributors and above).
 * 
 */
function sfwc_is_current_user_role_valid() {
	
	if ( is_user_logged_in() ) {
		
		// Get the User Object.
		$user = wp_get_current_user();
		
		// Get User's Roles.
		// User might has more than one role assigned.
		$current_user_roles = ( array ) $user->roles;
		
		// Create an array which will contain all user's capabilities
		// from all user's roles.
		$all_user_capabilities = array();
	
		if ( ! empty( $current_user_roles ) ) {
			
			foreach ( $current_user_roles as $role ) {
				
				#$role_obj = get_role( $role );
				
				$role_capabilities = get_role( $role )->capabilities;
				
				if ( ! empty( $role_capabilities ) ) {
				
					foreach( $role_capabilities as $key => $value ) {
						
						// Marge all user capabilities from all user's roles.
						$all_user_capabilities[$key] = $value;
					}
				}
			}
			// For debug.
			// print_r( $all_user_capabilities );
			
			
			
			
			if ( defined( 'SFWC_VALID_ROLE_LOOSE_MODE' ) && SFWC_VALID_ROLE_LOOSE_MODE == true ) {
			
				/*****************
				 * Loose Method  *
				 *****************/
				
				if ( ! empty( $all_user_capabilities ) ) {
					
					if ( array_key_exists( 'read', $all_user_capabilities ) && ! array_key_exists( 'edit_posts', $all_user_capabilities ) ) {
						
						return $current_user_roles;							
					}
					else {
						return false;
					}
				}
			}
			else {

				/*****************
				 * Strict Method *
				 *****************/
				
				/**
				 * Build an array containing all potential WordPress user capabilities, including those added by WooCommerce.
				 * 
				 * We could dinamically get the following list of capabilities with: get_role( 'administrator' )->capabilities;
				 * but in our case it is mandatory to create the array manually to avoid the following (and other similar) scenario:
				 * a custom capability is added to both the Customer role (or any other role that should be eligible for the subaccount system) and the Administrator role.
				 * At this point 'read' capability would no longer be the only capability in common between the two roles and the Strict Mode would no longer work.
				 */
				$all_potential_wp_capabilities = array( 'create_sites'=>1, 'delete_sites'=>1, 'manage_network'=>1, 'manage_sites'=>1, 'manage_network_users'=>1, 
				'manage_network_plugins'=>1, 'manage_network_themes'=>1, 'manage_network_options'=>1, 'upload_plugins'=>1, 'upload_themes'=>1, 'upgrade_network'=>1, 
				'setup_network'=>1, 'switch_themes'=>1, 'edit_themes'=>1, 'activate_plugins'=>1, 'edit_plugins'=>1, 'edit_users'=>1, 
				'edit_files'=>1, 'manage_options'=>1, 'moderate_comments'=>1, 'manage_categories'=>1, 'manage_links'=>1, 'upload_files'=>1, 'import'=>1, 
				'unfiltered_html'=>1, 'edit_posts'=>1, 'edit_others_posts'=>1, 'edit_published_posts'=>1, 'publish_posts'=>1, 'edit_pages'=>1, 
				'read'=>1, 'edit_others_pages'=>1, 'edit_published_pages'=>1, 'publish_pages'=>1, 'delete_pages'=>1, 'delete_others_pages'=>1, 
				'delete_published_pages'=>1, 'delete_posts'=>1, 'delete_others_posts'=>1, 'delete_published_posts'=>1, 'delete_private_posts'=>1, 
				'edit_private_posts'=>1, 'read_private_posts'=>1, 'delete_private_pages'=>1, 'edit_private_pages'=>1, 'read_private_pages'=>1, 
				'delete_users'=>1, 'create_users'=>1, 'unfiltered_upload'=>1, 'edit_dashboard'=>1, 'update_plugins'=>1, 'delete_plugins'=>1, 
				'install_plugins'=>1, 'update_themes'=>1, 'install_themes'=>1, 'update_core'=>1, 'list_users'=>1, 'remove_users'=>1, 'promote_users'=>1, 
				'edit_theme_options'=>1, 'delete_themes'=>1, 'export'=>1, 'manage_woocommerce'=>1, 'view_woocommerce_reports'=>1, 'edit_product'=>1, 
				'read_product'=>1, 'delete_product'=>1, 'edit_products'=>1, 'edit_others_products'=>1, 'publish_products'=>1, 'read_private_products'=>1, 
				'delete_products'=>1, 'delete_private_products'=>1, 'delete_published_products'=>1, 'delete_others_products'=>1, 'edit_private_products'=>1, 
				'edit_published_products'=>1, 'manage_product_terms'=>1, 'edit_product_terms'=>1, 'delete_product_terms'=>1, 'assign_product_terms'=>1, 
				'edit_shop_order'=>1, 'read_shop_order'=>1, 'delete_shop_order'=>1, 'edit_shop_orders'=>1, 'edit_others_shop_orders'=>1, 'publish_shop_orders'=>1, 
				'read_private_shop_orders'=>1, 'delete_shop_orders'=>1, 'delete_private_shop_orders'=>1, 'delete_published_shop_orders'=>1, 
				'delete_others_shop_orders'=>1, 'edit_private_shop_orders'=>1, 'edit_published_shop_orders'=>1, 'manage_shop_order_terms'=>1, 
				'edit_shop_order_terms'=>1, 'delete_shop_order_terms'=>1, 'assign_shop_order_terms'=>1, 'edit_shop_coupon'=>1, 'read_shop_coupon'=>1, 
				'delete_shop_coupon'=>1, 'edit_shop_coupons'=>1, 'edit_others_shop_coupons'=>1, 'publish_shop_coupons'=>1, 'read_private_shop_coupons'=>1, 
				'delete_shop_coupons'=>1, 'delete_private_shop_coupons'=>1, 'delete_published_shop_coupons'=>1, 'delete_others_shop_coupons'=>1, 
				'edit_private_shop_coupons'=>1, 'edit_published_shop_coupons'=>1, 'manage_shop_coupon_terms'=>1, 'edit_shop_coupon_terms'=>1, 
				'delete_shop_coupon_terms'=>1, 'assign_shop_coupon_terms'=>1, 'ure_edit_roles'=>1, 'ure_create_roles'=>1, 'ure_delete_roles'=>1, 
				'ure_create_capabilities'=>1, 'ure_delete_capabilities'=>1, 'ure_manage_options'=>1, 'ure_reset_roles'=>1, 'create_posts'=>1, 
				'install_languages'=>1, 'resume_plugins'=>1, 'resume_themes'=>1, 'view_site_health_checks'=>1 );
				

				$result = array_intersect_key( $all_user_capabilities, $all_potential_wp_capabilities );
				
				#print_r( $result );
				
				if ( ! empty( $result ) && count( $result ) === 1 && key( $result ) == 'read' ) {
				
					return $current_user_roles;
				}
				else {
					return false;
				}
			}
		}
		else {
			return false;
		}
	}
}




/**
 * Helper function to determine if current user role is enabled from plugin settings.
 *
 * This function is strictly related and dependent on the sfwc_is_current_user_role_valid() function.
 *
 * @since 1.3.0
 */
function sfwc_is_current_user_role_enabled() {
	
	// Get Options settings.
	$sfwc_options = (array) get_option('sfwc_options');

	// Get Selected Roles option value from Options settings.
	$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');


	// For debug.
	/*
	echo '<h4>Roles enabled from settings</h4><pre>';
	print_r( $sfwc_option_selected_roles  );
	echo '</pre><h4>Valid roles assigned to user:</h4><pre>';
	print_r( sfwc_is_current_user_role_valid() );
	echo '</pre><h4>Common stuff</h4><pre>';
	print_r( array_intersect( sfwc_is_current_user_role_valid(), $sfwc_option_selected_roles ) );
	echo '</pre>';
	*/


	if ( is_user_logged_in() ) {
		
		if ( function_exists( 'sfwc_is_current_user_role_valid' ) ) {

			// Checking if any of an array's elements are in another array.
			if ( ! empty( array_intersect( sfwc_is_current_user_role_valid(), $sfwc_option_selected_roles ) ) ) {
				
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
}




/**
 * Helper function to determine if a user role is eligible/valid for the subaccount system (by passing $user_id).
 * 
 * User Role should not have higher permissions than a Customer (or Subscriber) role, whose only capability is (by default) 'read'.
 * Two verification modes are available.
 *
 * @since 1.3.0
 * 
 *  -------------
 * | Strict Mode |
 *  -------------
 * 
 * It is the default mode.
 * 
 * We basically get all the roles assigned to the current user (a user could have more than one role assigned),
 * for each role assigned we get all the capabilities and merge them into a single array.
 * 
 * After that, we compare all the (merged) user capabilities against another array containing all potential WordPress user capabilities,
 * including those added by WooCommerce.
 *
 * If the only capability in common is the 'read' one, the current user role is eligible/valid for the subaccount system.
 * 
 *  ------------
 * | Loose Mode |
 *  ------------
 *
 * Since the Strict Mode is quite restrictive, we provide a way to loosen it up a bit.
 * 
 * To enable Loose Mode, it is necessary to define the following constant in wp-config.php file:
 *
 * define( 'SFWC_VALID_ROLE_LOOSE_MODE', true );
 * 
 * With Loose Mode enabled, the only conditions necessary for a user role to be valid are that it has 'read' capability and that it does
 * not have 'edit_posts' capability (Contributors and above).
 * 
 */
function sfwc_is_user_role_valid( $user_id ) {

	// Get the User Object by ID.
	$user = get_userdata( $user_id );
	
	// Get User's Roles.
	// User might has more than one role assigned.
	$user_roles = ( array ) $user->roles;
	
	// Create an array which will contain all user's capabilities
	// from all user's roles.
	$all_user_capabilities = array();

	if ( ! empty( $user_roles ) ) {
		
		foreach ( $user_roles as $role ) {
			
			#$role_obj = get_role( $role );
			
			$role_capabilities = get_role( $role )->capabilities;
			
			if ( ! empty( $role_capabilities ) ) {
			
				foreach( $role_capabilities as $key => $value ) {
					
					// Marge all user capabilities from all user's roles.
					$all_user_capabilities[$key] = $value;
				}
			}
		}
		// For debug.
		// print_r( $all_user_capabilities );
		
		
		
		
		if ( defined( 'SFWC_VALID_ROLE_LOOSE_MODE' ) && SFWC_VALID_ROLE_LOOSE_MODE == true ) {
		
			/*****************
			 * Loose Method  *
			 *****************/
			
			if ( ! empty( $all_user_capabilities ) ) {
				
				if ( array_key_exists( 'read', $all_user_capabilities ) && ! array_key_exists( 'edit_posts', $all_user_capabilities ) ) {
					
					return $user_roles;							
				}
				else {
					return false;
				}
			}
		}
		else {

			/*****************
			 * Strict Method *
			 *****************/
			
			/**
			 * Build an array containing all potential WordPress user capabilities, including those added by WooCommerce.
			 * 
			 * We could dinamically get the following list of capabilities with: get_role( 'administrator' )->capabilities;
			 * but in our case it is mandatory to create the array manually to avoid the following (and other similar) scenario:
			 * a custom capability is added to both the Customer role (or any other role that should be eligible for the subaccount system) and the Administrator role.
			 * At this point 'read' capability would no longer be the only capability in common between the two roles and the Strict Mode would no longer work.
			 */
			$all_potential_wp_capabilities = array( 'create_sites'=>1, 'delete_sites'=>1, 'manage_network'=>1, 'manage_sites'=>1, 'manage_network_users'=>1, 
			'manage_network_plugins'=>1, 'manage_network_themes'=>1, 'manage_network_options'=>1, 'upload_plugins'=>1, 'upload_themes'=>1, 'upgrade_network'=>1, 
			'setup_network'=>1, 'switch_themes'=>1, 'edit_themes'=>1, 'activate_plugins'=>1, 'edit_plugins'=>1, 'edit_users'=>1, 
			'edit_files'=>1, 'manage_options'=>1, 'moderate_comments'=>1, 'manage_categories'=>1, 'manage_links'=>1, 'upload_files'=>1, 'import'=>1, 
			'unfiltered_html'=>1, 'edit_posts'=>1, 'edit_others_posts'=>1, 'edit_published_posts'=>1, 'publish_posts'=>1, 'edit_pages'=>1, 
			'read'=>1, 'edit_others_pages'=>1, 'edit_published_pages'=>1, 'publish_pages'=>1, 'delete_pages'=>1, 'delete_others_pages'=>1, 
			'delete_published_pages'=>1, 'delete_posts'=>1, 'delete_others_posts'=>1, 'delete_published_posts'=>1, 'delete_private_posts'=>1, 
			'edit_private_posts'=>1, 'read_private_posts'=>1, 'delete_private_pages'=>1, 'edit_private_pages'=>1, 'read_private_pages'=>1, 
			'delete_users'=>1, 'create_users'=>1, 'unfiltered_upload'=>1, 'edit_dashboard'=>1, 'update_plugins'=>1, 'delete_plugins'=>1, 
			'install_plugins'=>1, 'update_themes'=>1, 'install_themes'=>1, 'update_core'=>1, 'list_users'=>1, 'remove_users'=>1, 'promote_users'=>1, 
			'edit_theme_options'=>1, 'delete_themes'=>1, 'export'=>1, 'manage_woocommerce'=>1, 'view_woocommerce_reports'=>1, 'edit_product'=>1, 
			'read_product'=>1, 'delete_product'=>1, 'edit_products'=>1, 'edit_others_products'=>1, 'publish_products'=>1, 'read_private_products'=>1, 
			'delete_products'=>1, 'delete_private_products'=>1, 'delete_published_products'=>1, 'delete_others_products'=>1, 'edit_private_products'=>1, 
			'edit_published_products'=>1, 'manage_product_terms'=>1, 'edit_product_terms'=>1, 'delete_product_terms'=>1, 'assign_product_terms'=>1, 
			'edit_shop_order'=>1, 'read_shop_order'=>1, 'delete_shop_order'=>1, 'edit_shop_orders'=>1, 'edit_others_shop_orders'=>1, 'publish_shop_orders'=>1, 
			'read_private_shop_orders'=>1, 'delete_shop_orders'=>1, 'delete_private_shop_orders'=>1, 'delete_published_shop_orders'=>1, 
			'delete_others_shop_orders'=>1, 'edit_private_shop_orders'=>1, 'edit_published_shop_orders'=>1, 'manage_shop_order_terms'=>1, 
			'edit_shop_order_terms'=>1, 'delete_shop_order_terms'=>1, 'assign_shop_order_terms'=>1, 'edit_shop_coupon'=>1, 'read_shop_coupon'=>1, 
			'delete_shop_coupon'=>1, 'edit_shop_coupons'=>1, 'edit_others_shop_coupons'=>1, 'publish_shop_coupons'=>1, 'read_private_shop_coupons'=>1, 
			'delete_shop_coupons'=>1, 'delete_private_shop_coupons'=>1, 'delete_published_shop_coupons'=>1, 'delete_others_shop_coupons'=>1, 
			'edit_private_shop_coupons'=>1, 'edit_published_shop_coupons'=>1, 'manage_shop_coupon_terms'=>1, 'edit_shop_coupon_terms'=>1, 
			'delete_shop_coupon_terms'=>1, 'assign_shop_coupon_terms'=>1, 'ure_edit_roles'=>1, 'ure_create_roles'=>1, 'ure_delete_roles'=>1, 
			'ure_create_capabilities'=>1, 'ure_delete_capabilities'=>1, 'ure_manage_options'=>1, 'ure_reset_roles'=>1, 'create_posts'=>1, 
			'install_languages'=>1, 'resume_plugins'=>1, 'resume_themes'=>1, 'view_site_health_checks'=>1 );
			

			$result = array_intersect_key( $all_user_capabilities, $all_potential_wp_capabilities );
			
			#print_r( $result );
			
			if ( ! empty( $result ) && count( $result ) === 1 && key( $result ) == 'read' ) {
			
				return $user_roles;
			}
			else {
				return false;
			}
		}
	}
	else {
		return false;
	}
}




/**
 * Helper function to determine if user role is enabled from plugin settings (by passing $user_id).
 *
 * This function is strictly related and dependent on the sfwc_is_user_role_valid() function.
 *
 * @since 1.3.0
 */
function sfwc_is_user_role_enabled( $user_id ) {

	// Get Options settings.
	$sfwc_options = (array) get_option('sfwc_options');

	// Get Selected Roles option value from Options settings.
	$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');


	// For debug.
	/*
	echo '<h4>Roles enabled from settings</h4><pre>';
	print_r( $sfwc_option_selected_roles  );
	echo '</pre><h4>Valid roles assigned to user:</h4><pre>';
	print_r( sfwc_is_user_role_valid( $user_id ) );
	echo '</pre><h4>Common stuff</h4><pre>';
	print_r( array_intersect( sfwc_is_user_role_valid( $user_id ), $sfwc_option_selected_roles ) );
	echo '</pre>';
	*/

	if ( function_exists( 'sfwc_is_user_role_valid' ) ) {
		// Checking if any of an array's elements are in another array.
		if ( ! empty( array_intersect( sfwc_is_user_role_valid( $user_id ), $sfwc_option_selected_roles ) ) ) {
			
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}




/**
 * Adding Meta fields in the meta container admin shop_order pages.
 * 
 * Used also for 'Subaccounts Info' section on WC order email (sfwc_add_subaccounts_info_to_order_email function from Pro).
 */
function sfwc_add_meta_box_content( $order ) { // Leave $order, otherwise getting error on checkout page while placing an order.

	global $post; // Keep it there, otherwise getting 'ID was called incorrectly' in emails when order is created from backend.
	
	// Get 'Options' settings.
    $sfwc_options = (array) get_option( 'sfwc_options' );
	
	// Get Subaccount Mode option value from Options settings.
	$sfwc_option_subaccount_mode = ( isset( $sfwc_options['sfwc_option_subaccount_mode'] ) ) ? $sfwc_options['sfwc_option_subaccount_mode'] : 'sub_user';
	
	// Get Customer Display Name mode.
	$sfwc_option_display_name = ( isset( $sfwc_options['sfwc_option_display_name'] ) ) ? $sfwc_options['sfwc_option_display_name'] : 'username';
	
	
	if ( is_admin() ) {
		$order = new WC_Order($post->ID); // Admin side only otherwise emails won't get sent.
	}
	
	$customer_id = $order->get_user_id();
	
	// Retrieve user data for customer related to order.
	$userdata_customer = get_userdata( $customer_id );


	// Get Account Level Type.
	$customer_account_level = $order->get_meta('_sfwc_customer_account_level_type');
	
	// Retrieve the ID of the Manager related to the Customer.
	$customer_related_manager_id = $order->get_meta('_sfwc_customer_related_manager');
	
	// Retrieve user data for Manager.
	$userdata_manager = get_userdata( $customer_related_manager_id );
	
	// Slightly change the background color of the meta box based on Subaccount Mode setting.
	$order_placed_by_css_background = $sfwc_option_subaccount_mode == 'sub_user' ? '#e4e4e4' : '#f5f5f5';


 
	/**
	 * Check if order has still a user id associated ($customer_id). 
	 * If not checked, in case a customer is deleted after he made an order they appear wrong values.
	 * 
	 * Also check if the user who made the order has role of Customer or Subscriber. Otherwise weird things might happen.
	 * E.g. If order was made by an Administrator wrong data is displayed within the meta box.
	 */

	if ( $customer_id && ( sfwc_is_user_role_valid( $customer_id ) && sfwc_is_user_role_enabled( $customer_id ) ) ) {
		
		if ( $sfwc_option_subaccount_mode == 'sub_user' ) {

			echo '<div style="background:#f5f5f5;padding:2px 8px;margin-top:12px;">';

			//Check 'Customer Display Name' in Subaccounts > Settings > Options and display it accordingly
			if ( ( $sfwc_option_display_name == 'full_name' ) && ( $userdata_customer->user_firstname || $userdata_customer->user_lastname ) ) {

				// Echo 'Full Name + Email' (if either First Name or Last Name has been set)
				echo '<p><strong>' . esc_html__( 'Customer', 'subaccounts-for-woocommerce' ) . ':</strong><br>' . esc_html( $userdata_customer->user_firstname ) . ' ' . esc_html( $userdata_customer->user_lastname ) . '<br>[' . esc_html( $userdata_customer->user_email ) . ']</p>';

			} elseif (($sfwc_option_display_name == 'company_name') && ($userdata_customer->billing_company)) {

				// Echo 'Company + Email' (if Company name has been set)
				echo '<p><strong>' . esc_html__( 'Customer', 'subaccounts-for-woocommerce' ) . ':</strong><br>' . esc_html( $userdata_customer->billing_company ) . '<br>[' . esc_html( $userdata_customer->user_email ) . ']</p>';

			} else {

				// Otherwise echo 'Username + Email'
				echo '<p><strong>' . esc_html__( 'Customer', 'subaccounts-for-woocommerce' ) . ':</strong><br>' . esc_html( $userdata_customer->user_login ) . '<br>[' . esc_html( $userdata_customer->user_email ) . ']</p>';
			}




			// Display Account Type
			echo '<p><strong>' . esc_html__( 'Account Type', 'subaccounts-for-woocommerce' ) . ':</strong> ';


			if ( $customer_account_level == 'supervisor' ) {

				echo esc_html__('Supervisor', 'subaccounts-for-woocommerce');

				if ( ! sfwc_is_plugin_active( 'woocommerce.php' ) ) {

					echo '<div style="background:#fff4bd; padding:5px; margin-bottom:.5em;">';
					
					$sup_deactivated_warning = '<strong>' . esc_html__( 'WARNING:', 'subaccounts-for-woocommerce' ) . '</strong>';
					$sup_deactivated_supervisor = '<strong><em>' . esc_html__( 'Supervisor', 'subaccounts-for-woocommerce' ) . '</em></strong>';
					$sup_deactivated_addon = '<strong><em>' . esc_html__( 'Supervisor Add-on', 'subaccounts-for-woocommerce' ) . '</em></strong>';
					
					printf(
						esc_html__( '%1$s This User\'s Account Type is set as %2$s, but the %3$s is either uninstalled or not active. You may want to install and activate the add-on or change the User\'s Account Type.', 'subaccounts-for-woocommerce' ), 
						$sup_deactivated_warning,
						$sup_deactivated_supervisor,
						$sup_deactivated_addon
					);			
				
					echo '</div>';
				}

			} elseif ( $customer_account_level == 'manager' ) {

				echo esc_html__( 'Manager', 'subaccounts-for-woocommerce' );

			} else {

				echo esc_html__( 'Default', 'subaccounts-for-woocommerce' );

			}

			echo '</p>';
		
		}
		
		// Display Order Placed By
		echo '<p style="background:' . $order_placed_by_css_background . '; border-radius: 3px; padding: 8px;"><strong>' . esc_html__( 'Order placed by', 'subaccounts-for-woocommerce' ) . ':</strong><br>';
		
		
		// Check if Pro version is active.
		if ( ! sfwc_is_plugin_active( 'subaccounts-for-woocommerce-pro.php' ) ) {
					
			echo '<a style="font-weight: 600; color: #e67d23; background: #fff; padding: 5px; display: block; margin-top: 5px; border-radius: 3px; width: 100%; box-sizing: border-box; text-align: center;" href="'
					. admin_url( '/admin.php?checkout=true&page=subaccounts-pricing&plugin_id=10457&plan_id=17669&pricing_id=19941&billing_cycle=annual' ) . '">'
					. esc_html__( 'Upgrade to Subaccounts Pro', 'subaccounts-for-woocommerce' ) . '</a>';
		}


		do_action('admin_order_page_before_manager_info', $order, $sfwc_option_display_name, $userdata_customer, $userdata_manager );
		

		echo '</p>';
		
		if ( $sfwc_option_subaccount_mode == 'sub_user' ) {

			echo '</div>';
		
		}

		if ( $sfwc_option_subaccount_mode == 'sub_user' ) {

			// Display Related Supervisor or Manager Account (if any)
			if ( $customer_account_level == 'supervisor' ) {

				//echo '<p><strong>' . __('Manager Account', 'subaccounts-for-woocommerce') . ':</strong><br> ' . __('None', 'subaccounts-for-woocommerce') . wc_help_tip( esc_attr__('Supervisor account types cannot have Managers above them', 'subaccounts-for-woocommerce') ) . '</p>';
				echo '<p><strong>' . esc_html__( 'Manager Account', 'subaccounts-for-woocommerce' ) . ':</strong><br> ' . esc_html__( 'None', 'subaccounts-for-woocommerce' ) . '<span class="tips woocommerce-help-tip" data-tip="' . esc_attr__( 'Supervisor account types cannot have Managers above them.', 'subaccounts-for-woocommerce' ) . '"></span></p>';

				#if ( is_plugin_active( 'sfwc-supervisor-addon/sfwc-supervisor-addon.php' ) ) {
				if ( sfwc_is_plugin_active( 'sfwc-supervisor-addon.php' ) ) {
					//echo '<p><strong>' . __('Supervisor Account', 'subaccounts-for-woocommerce') . ':</strong><br> ' . __('None', 'subaccounts-for-woocommerce') . wc_help_tip( esc_attr__('Supervisor account types cannot have Supervisors above them', 'subaccounts-for-woocommerce') ) . '</p>';
					echo '<p><strong>' . esc_html__( 'Supervisor Account', 'subaccounts-for-woocommerce' ) . ':</strong><br> ' . esc_html__( 'None', 'subaccounts-for-woocommerce' ) . '<span class="tips woocommerce-help-tip" data-tip="' . esc_attr__( 'Supervisor account types cannot have Supervisors above them.', 'subaccounts-for-woocommerce' ) . '"></span></p>';
				}
			} elseif ( $customer_account_level == 'manager' ) {


				//echo '<p><strong>' . __('Manager Account', 'subaccounts-for-woocommerce') . ':</strong><br> ' . __('None', 'subaccounts-for-woocommerce') . wc_help_tip( esc_attr__('Manager account types cannot have Managers above them', 'subaccounts-for-woocommerce') ) . '</p>';
				echo '<p><strong>' . esc_html__( 'Manager Account', 'subaccounts-for-woocommerce' ) . ':</strong><br> ' . esc_html__( 'None', 'subaccounts-for-woocommerce' ) . '<span class="tips woocommerce-help-tip" data-tip="' . esc_attr__( 'Manager account types cannot have Managers above them.', 'subaccounts-for-woocommerce' ) . '"></span></p>';


				do_action('order_page_after_manager_info_when_customer_is_manager', $order, $customer_account_level, $sfwc_option_display_name );


			} elseif ( ( $customer_account_level !== 'supervisor' ) && ( $customer_account_level !== 'manager' ) ) {

				echo '<p style="padding-left:20px;"><span style="-moz-transform: scale(-1, 1); -o-transform: scale(-1, 1); -webkit-transform: scale(-1, 1); transform: scale(-1, 1);" class="dashicons dashicons-editor-break"></span><strong>' . esc_html__('Manager Account', 'subaccounts-for-woocommerce') . ':</strong><br>';

				

				if ( $customer_related_manager_id && ($customer_related_manager_id !== 'not_set') ) {
					
					//$userdata_manager = get_userdata( $customer_related_manager_id );

					// foreach ( $user_query_manager->get_results() as $user ) {

						//Check 'Customer Display Name' in Subaccounts > Settings > Options and display it accordingly
						if ( ( $sfwc_option_display_name == 'full_name' ) && ( get_userdata($userdata_manager->ID)->user_firstname || get_userdata($userdata_manager->ID)->user_lastname ) ) {

							// Echo 'Full Name + Email' (if either First Name or Last Name has been set)
							printf( 'ID: %1$s - %2$s %3$s <br>[%4$s]</p>', esc_html( $userdata_manager->ID ), esc_html( $userdata_manager->user_firstname ), esc_html( $userdata_manager->user_lastname ), esc_html( $userdata_manager->user_email ) );
							

						} elseif ( ( $sfwc_option_display_name == 'company_name' ) && ( get_userdata($userdata_manager->ID)->billing_company ) ) {

							// Echo 'Company + Email' (if Company name has been set)
							printf( 'ID: %1$s - %2$s <br>[%3$s]</p>', esc_html( $userdata_manager->ID ), esc_html( $userdata_manager->billing_company ), esc_html( $userdata_manager->user_email ) );
							

						} else {

							// Otherwise echo 'Username + Email'
							printf( 'ID: %1$s - %2$s <br>[%3$s]</p>', esc_html( $userdata_manager->ID ), esc_html( $userdata_manager->user_login ), esc_html( $userdata_manager->user_email ) );
							
						}
					// }

				} else {
					//echo __('Not set', 'subaccounts-for-woocommerce') . wc_help_tip( esc_attr__('No Manager has been set yet', 'subaccounts-for-woocommerce') ) . '</p>';
					echo esc_html__( 'Not set', 'subaccounts-for-woocommerce' ) . '<span class="tips woocommerce-help-tip" data-tip="' . esc_attr__( 'No Manager has been set yet.', 'subaccounts-for-woocommerce' ) . '"></span></p>';
				}


				( isset( $userdata_manager ) ) ? $userdata_manager = $userdata_manager : $userdata_manager = ''; // Prevent: Undefined variable: $userdata_manager (wp_debug enabled) // Check if still needed.
				do_action('order_page_after_manager_info_when_customer_is_default', $order, $customer_account_level, $sfwc_option_display_name );

			}
		}

	} else {
		echo '<p>' . esc_html__( 'No data available for this order.', 'subaccounts-for-woocommerce' ) . '</p>';
	}
}