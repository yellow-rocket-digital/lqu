<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}




/**
 * After plugin update, store updated plugin information in sfwc_plugin_info option
 * and perform after update required tasks (e.g. set transient for notices to show up).
 *
 * Since 1.1.0
 */
function sfwc_set_plugin_info_option() {
	
	if ( ! wp_doing_ajax() ) {

		$existing_values = get_option('sfwc_plugin_info');

		// Avoid undefined $existing_values_version
		$existing_values_version = ( isset( $existing_values['version'] ) ) ? $existing_values['version'] : '';

		
		
		// Check current version of the plugin against version which was stored in database from previously installed release.
		if ( version_compare( SFWC_CURRENT_VERSION, $existing_values_version, '>' ) ) {
		
			/**
			 * If curently installed version of the plugin is greater than previous one, perform following tasks.
			 *
			 */
			
			
			// If current release requires permalink settings update, set transient.
			if ( SFWC_REQUIRES_PERMALINK_UPDATE == 'yes') {
				
				// Set a trasient for the permalink update notice to appear.
				set_transient( 'sfwc_show_permalink_update_required_notice', 'show_notice_for_version_' . SFWC_CURRENT_VERSION );
				
				$permalink_notice_dismissed = 'not_yet';
				
			} else {
				$permalink_notice_dismissed = ( isset( $existing_values['requires_permalinks_update']['notice_dismissed'] ) ) ? $existing_values['requires_permalinks_update']['notice_dismissed'] : 'yes';
			}

			
			// Current Release Values.
			// Pay attention: in case a key-value pair is removed from below array,
			// it will be removed from database as well after option is updated.
			$current_values = array(
				'version' => SFWC_CURRENT_VERSION,
				'requires_permalinks_update' => array(
					'required_by_current_version' => SFWC_REQUIRES_PERMALINK_UPDATE,			// 'yes' | 'no'
					'notice_dismissed' => sanitize_text_field( $permalink_notice_dismissed ), 	// 'yes' | 'not_yet'
				),
				// Maybe add more.
			);
			
			
			/**
			 * Insert data in database.
			 */
			
			// If option doesn't exist yet (introduced since 1.1.0).
			if ( empty( $existing_values ) ){
				add_option( 'sfwc_plugin_info', $current_values );
			}
			// If option already exists.
			else {
				// Add to current values.
				$new_values = array_merge( $existing_values, $current_values );

				// Update Option.
				update_option( 'sfwc_plugin_info', $new_values );
			}
		}

		/***
		// For debug
		if ( ! empty( $existing_values ) ) {
			echo '<div style="margin-left:200px;"><pre>';
			print_r( $existing_values );
			echo '</pre></div>';
		}
		***/
	}
}
add_action('admin_init', 'sfwc_set_plugin_info_option', 20); // Priority must be lower than before sfwc_ignore_permalink_update_required_notice() 
															 // otherwise changes visible after page refresh.



/**
 * Show Permalink Settings update required notice.
 *
 */
function sfwc_add_permalink_update_required_notice() {
	
	// Get sfwc_plugin_info option.
	$sfwc_plugin_info = get_option('sfwc_plugin_info');

	// Check required_by_current_version option to see if current release requires permalink settings update.
	$permalink_update_required = ( isset( $sfwc_plugin_info['requires_permalinks_update']['required_by_current_version'] ) ) ? $sfwc_plugin_info['requires_permalinks_update']['required_by_current_version'] : 'no';
	
	$query_arg = add_query_arg( 
		array( 
			'sfwc_ignore_permalink_update_notice' => '1',
			'sfwc_nonce' => wp_create_nonce( 'sfwc_ignore_permalink_notice' )
		)
	);

	if ( ! wp_doing_ajax() ) {
		
		if ( is_user_logged_in() && ( current_user_can( 'update_plugins' ) ) ) {

			// If transient exists, permalink update is required.
			if ( $permalink_update_required == 'yes' && ! get_transient( 'sfwc_show_permalink_update_required_notice' ) === false ) {
				
				?>

				<div class="notice notice-warning">
					<h3>Subaccounts for WooCommerce</h3>
					<p>
						<?php
							$permalink_update_required_translation = '<strong>' . esc_html__( 'Permalink Settings update required', 'subaccounts-for-woocommerce' ) . '</strong>';
							$permalink_settings_path_translation = '<code>' . esc_html__( 'Settings', 'subaccounts-for-woocommerce' ) . ' ➝ ' . esc_html__( 'Permalinks', 'subaccounts-for-woocommerce' ) . '</code>';
							$permalink_save_changes_translation = '<code>' . esc_html__( 'Save Changes', 'subaccounts-for-woocommerce' ) . '</code>';
							
							printf(
								esc_html__( '%1$s: please update the WordPress permalink structure by going to: %2$s and clicking %3$s.', 'subaccounts-for-woocommerce' ), 
								$permalink_update_required_translation,
								$permalink_settings_path_translation,
								$permalink_save_changes_translation
							);
						?>
						
						<a class="button button-primary" style="display:block; max-width:100px; margin-top:10px; text-align:center;" href="<?php echo $query_arg ?>">
							<?php echo esc_html__( 'I got it', 'subaccounts-for-woocommerce' ); ?>
						</a>
					</p>
				</div>
				
				<?php
			}
		}
	}
}
add_action('admin_notices', 'sfwc_add_permalink_update_required_notice');




/**
 * Dismiss permalink update required notice.
 *
 */
function sfwc_ignore_permalink_update_required_notice() {
	 
	if ( isset( $_GET['sfwc_ignore_permalink_update_notice'] ) ) {
		$sfwc_ignore_permalink_update_notice_sanitized = sanitize_text_field( $_GET['sfwc_ignore_permalink_update_notice'] );
	}
	
	if ( isset( $_GET['sfwc_nonce'] ) ) {
		$sfwc_nonce_sanitized = sanitize_text_field( $_GET['sfwc_nonce'] );
	}
	
	$sfwc_plugin_info = get_option('sfwc_plugin_info');
	
	$permalinks_update_required_notice_dismissed = ( isset( $sfwc_plugin_info['requires_permalinks_update']['notice_dismissed'] ) ) ? $sfwc_plugin_info['requires_permalinks_update']['notice_dismissed'] : 'yes';
	
	if ( ! wp_doing_ajax() ) {
		
		if ( is_user_logged_in() && ( current_user_can( 'update_plugins' ) ) ) {

			if ( ( isset( $sfwc_ignore_permalink_update_notice_sanitized ) && $sfwc_ignore_permalink_update_notice_sanitized == '1' ) && $permalinks_update_required_notice_dismissed == 'not_yet' ) {
				
				// Verify nonce.
				if ( isset( $sfwc_nonce_sanitized ) && wp_verify_nonce( $sfwc_nonce_sanitized, 'sfwc_ignore_permalink_notice' ) ) {
				
					$sfwc_plugin_info['requires_permalinks_update']['notice_dismissed'] = 'yes';

					update_option( 'sfwc_plugin_info', $sfwc_plugin_info );
					
					delete_transient( 'sfwc_show_permalink_update_required_notice' );
				
				}
				else {
					 add_action( 'admin_notices', function() {
					?>
					
					<div class="notice notice-error">
						<h3>Subaccounts for WooCommerce</h3>
						<p>
						<?php
							$permalink_update_required_translation = '<em>' . esc_html__( 'Permalink Settings update required', 'subaccounts-for-woocommerce' ) . '</em>';
							
							printf(
								esc_html__( '%1$s notice could not be dismissed due to nonce verification failure.', 'subaccounts-for-woocommerce' ), 
								$permalink_update_required_translation
							);
						?>
						</p>
					</div>
					
					<?php
					 }); 
				}
			}
		}
	}
}
add_action('admin_init', 'sfwc_ignore_permalink_update_required_notice', 10); // Priority must be higher than before sfwc_set_plugin_info_option() 
																			  // otherwise changes visible after page refresh.



/**
 * Set WooCommerce screen IDs.
 *
 * This will enqueue WooCommerce CSS/JS and allow to show tooltips text on hover on:
 *
 *	- Plugin settings page
 *	- Admin users list
 */
function sfwc_set_wc_screen_ids( $screen ){
      $screen[] = 'woocommerce_page_subaccounts';
	  $screen[] = 'users';
      return $screen;
}
add_filter('woocommerce_screen_ids', 'sfwc_set_wc_screen_ids' );

/**
 * For debug. Keep commented.
 * For each admin page show current screen ID.
 */
/*
function sfwc_dump_screen_ids() {
	var_dump( get_current_screen()->id );
}
add_action('admin_notices', 'sfwc_dump_screen_ids');
*/




/**
 * Add Custom Links.
 *
 * Add following links next to 'Deactivate Plugin' option on plugins list page:
 *
 *	- Settings
 *	- Premium version of Subaccounts
 */
function sfwc_settings_plugin_link( $links, $file ) {

	if ( $file == 'subaccounts-for-woocommerce/subaccounts-for-woocommerce.php' ) {
		
        // Insert "Settings" link at the beginning.
        $sfwc_settings_link = '<a href="admin.php?page=subaccounts">' . esc_html__( 'Settings', 'subaccounts-for-woocommerce' ) . '</a>';
        array_unshift( $links, $sfwc_settings_link );

		$links['get_subaccounts_pro'] = '<nobr style="padding-top:2px; display:block;">
											<a href="' . admin_url( '/admin.php?checkout=true&page=subaccounts-pricing&plugin_id=10457&plan_id=17669&pricing_id=19941&billing_cycle=annual' ) . '" style="font-weight:bold;">'
												. esc_html__( 'Get Subaccounts Pro', 'subaccounts-for-woocommerce' ) . ' ★
											</a>
										</nobr>';
    }
    return $links;
}
add_filter('plugin_action_links', 'sfwc_settings_plugin_link', 10, 2);




/**
 * Backend style
 */
function sfwc_enqueue_backend_style() {
	
	global $pagenow;
	
	$plugin_settings_page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
	
	$active_settings_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '';

	/**
	 * Enqueue main backend style.
	 */	
	wp_enqueue_style( 'sfwc_backend_style', WP_PLUGIN_URL . '/subaccounts-for-woocommerce/assets/css/admin.css' );

	
	/**
	 * Enqueue Selectize JS and CSS on plugin Options page.
	 */
	if ( ! wp_doing_ajax() && current_user_can( 'administrator' ) && $pagenow == 'admin.php' && $plugin_settings_page == 'subaccounts' && ( $active_settings_tab == 'options' || $active_settings_tab == '' ) ) {

		// Load Selectize JS and CSS, necessary in: Dashboard -> Users -> Edit.
		wp_enqueue_style( 'sfwc_selectize_style', WP_PLUGIN_URL . '/subaccounts-for-woocommerce/assets/css/selectize/selectize.css' );
		wp_enqueue_script( 'sfwc_selectize_js', WP_PLUGIN_URL . '/subaccounts-for-woocommerce/assets/js/selectize/selectize.min.js', array( 'jquery' ), '0.13.6', false );
	}

}
add_action('admin_enqueue_scripts', 'sfwc_enqueue_backend_style');




/**
 * Display Subaccounts information on Order page.
 *
 * Add a meta box showing information about 'Manager' (and 'Supervisor' if 'Supervisor Add-on' installed) for the customer who made the order.
 */

// Adding Meta container admin shop_order pages
function sfwc_add_meta_box() {
	
	if ( ! wp_doing_ajax() ) {

		$sfwc_options = (array) get_option('sfwc_options');
		
		// Avoid undefined $sfwc_options_show_order_meta_box in case related setting has not been saved yet.
		$sfwc_options_show_order_meta_box = ( isset( $sfwc_options['sfwc_options_show_order_meta_box'] ) ) ? $sfwc_options['sfwc_options_show_order_meta_box'] : '0';

		// Check if option enabled in Subaccounts > Settings > Options
		if ( $sfwc_options_show_order_meta_box == '1' ) {
			add_meta_box('woocommerce-order-subaccounts', esc_html__( 'Subaccounts Info', 'subaccounts-for-woocommerce' ), 'sfwc_add_meta_box_content', 'shop_order', 'side', 'core');
		}
	}
}
add_action('add_meta_boxes', 'sfwc_add_meta_box'); // sfwc_add_meta_box_content in: includes > functions.php




/**
 * Save Manager info in Order's post meta when order is created from backend.
 * 
 * In case of order created from backend by admin, save Manager information in Order's post meta.
 *
 * See also: sfwc_order_placed_by_update_order_meta_after_payment function from my-account.php,
 * for updating Order's post meta with "order placed by" information (after the customer completes the order).
 */
function sfwc_store_subaccounts_meta_data_on_order_creation_admin_side( $order_id ) {
	
	// Get Options settings.
	$sfwc_options = (array) get_option('sfwc_options');

	// Get Selected Roles option value from Options settings.
	$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');
	
	if ( ! wp_doing_ajax() ) {
	
		// Get an instance of the WC_Order Object from the Order ID
		$order = wc_get_order( $order_id );

		// Get the Customer ID (User ID)
		$customer_id = $order->get_user_id();
		
		// Get Account Level Type
		$customer_account_level_type = get_user_meta( $customer_id, 'sfwc_account_level_type', true );
		
		
		$user_query_manager = []; // Prevent undefined variable when doing action below.
		
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


		do_action('sfwc_store_additional_subaccounts_meta_on_order_creation_admin_side', $order_id, $customer_account_level_type, $customer_id, $user_query_manager, $manager_id );	
			

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
		
		
		
		
		/**
		 * Validate the value of 'account level type' for 'manager' and 'default' account types. 
		 *
		 * For the validation of 'supervisor' see function: sfwc_store_subaccounts_meta_data_on_order_creation_admin_side_supervisor in Supervisor add-on
		 */	
		$valid_customer_account_level_type = array( 'manager', 'default', '' );

		if ( in_array( $customer_account_level_type, $valid_customer_account_level_type ) ) {
		
			// Store the customer's account level type.
			$order->update_meta_data( '_sfwc_customer_account_level_type', $customer_account_level_type );
		}
		
		$order->save();
	
	}
}
add_action('woocommerce_new_order', 'sfwc_store_subaccounts_meta_data_on_order_creation_admin_side', 10, 1);	// woocommerce_new_order triggered both for frontend
																												// and backend order creation; for backend only check with is_admin.
																												
																												// Also, woocommerce_new_order triggered only on order creation,
																												// but not on order update.

/**
 * woocommerce_process_shop_order_meta
 *
 * Triggered only for backend order creation.
 * Triggered for both order creation and order update; to trigger it only in one of the two cases,
 * check if order already exists.
 */

/*
function save_order_custom_field_meta( $order_id ) {

	// Do something...
}
add_action( 'woocommerce_process_shop_order_meta', 'save_order_custom_field_meta' );
*/




/**
 * Create admin plugin pages/subpages:
 *
 *	- Subaccounts
 * 		-- Settings
 */
function sfwc_admin_menu() {

	// Add WooCommerce menu sub-page.
	add_submenu_page(
		'woocommerce',															// $parent_slug
		esc_html__( 'Subaccounts Options Page', 'subaccounts-for-woocommerce' ),	// $page_title
		esc_html__( 'Subaccounts', 'subaccounts-for-woocommerce' ),					// $menu_title
		'manage_woocommerce',													// $capability
		'subaccounts',															// $menu_slug
		'sfwc_admin_page_contents',												// $function
		//9999																	// $position
	);

}
add_action('admin_menu', 'sfwc_admin_menu', 60);





/**
 * Create plugin's settings.
 *
 * 
 */
function sfwc_settings_init() {
	
	// Create Appearance Settings

	$args_sfwc_switcher_appearance = array(
		'type' => 'array',
		'sanitize_callback' => 'sfwc_switcher_appearance_validate' // validation callback
	);


	register_setting(
			'sfwc_switcher_appearance_group',	// group, used for settings_fields()
			'sfwc_switcher_appearance',			// option name, used as key in database
			$args_sfwc_switcher_appearance		// $args
	);



	add_settings_section(
			'sfwc_switcher_appearance_section',														// HTML ID tag for the section
			esc_html__( 'Frontend User Switcher Pane Appearance', 'subaccounts-for-woocommerce' ),	// Title for the section
			'', 																					//'sfwc_setting_section_callback_function'
			'subaccounts'                    														// Menu slug, same as 4th parameter of add_menu_page or 5th parameter of add_submenu_page
	);


	add_settings_field(
			'sfwc_switcher_pane_bg_color',										// HTML ID of the input element
			esc_html__( 'Background Color:', 'subaccounts-for-woocommerce' ),	// Title displayed at the left of input element
			'sfwc_switcher_pane_bg_color_html',									// Callback function for HTML input markup
			'subaccounts',
			'sfwc_switcher_appearance_section'
	);

	add_settings_field(
			'sfwc_switcher_pane_headline_color',
			esc_html__( 'Headline Color:', 'subaccounts-for-woocommerce' ),
			'sfwc_switcher_pane_headline_color_html',
			'subaccounts',
			'sfwc_switcher_appearance_section'
	);

	add_settings_field(
			'sfwc_switcher_pane_text_color',
			esc_html__( 'Text Color:', 'subaccounts-for-woocommerce' ),
			'sfwc_switcher_pane_text_color_html',
			'subaccounts',
			'sfwc_switcher_appearance_section'
	);

	add_settings_field(
			'sfwc_switcher_pane_select_bg_color',
			esc_html__( 'Button Background Color:', 'subaccounts-for-woocommerce' ),
			'sfwc_switcher_pane_select_bg_color_html',
			'subaccounts',
			'sfwc_switcher_appearance_section'
	);

	add_settings_field(
			'sfwc_switcher_pane_select_text_color',
			esc_html__( 'Button Text Color:', 'subaccounts-for-woocommerce' ),
			'sfwc_switcher_pane_select_text_color_html',
			'subaccounts',
			'sfwc_switcher_appearance_section'
	);




	// Create Options Settings

	$args_sfwc_options = array(
		'type' => 'array',
		'sanitize_callback' => 'sfwc_options_validate' // validation callback
	);

	register_setting(
			'sfwc_options_group',	// group, used for settings_fields()
			'sfwc_options',			// option name, used as key in database
			$args_sfwc_options		// $args
	);


	add_settings_section(
			'sfwc_options_section',								// HTML ID tag for the section
			'',													// Title for the section
			'',													//'sfwc_setting_section_callback_function' Callback for section description
			'subaccounts&tab=options'							// Menu slug, same as 4th parameter of add_menu_page or 5th parameter of add_submenu_page
	);

    // Check if function exists from PRO plugin
    if ( ! function_exists( 'sfwc_pro_settings_init' ) ) {
		
		add_settings_field(
				'sfwc_option_subaccount_mode',
				esc_html__('Subaccount Mode', 'subaccounts-for-woocommerce')
				. '<span class="tips woocommerce-help-tip" data-tip="' . esc_attr__( 'The way the whole subaccount system should work.', 'subaccounts-for-woocommerce' ) . '"></span>',
				'sfwc_option_subaccount_mode_html',
				'subaccounts&tab=options',
				'sfwc_options_section'
		);
		
		// Dummy: only available in Pro.
		add_settings_field(
				'sfwc_option_subaccount_creation_dummy',
				esc_html__('Choose who can create and add new subaccounts', 'subaccounts-for-woocommerce') 
					. '<br><a style="color:#148ff3;" href="' 
					. admin_url( '/admin.php?checkout=true&page=subaccounts-pricing&plugin_id=10457&plan_id=17669&pricing_id=19941&billing_cycle=annual' ) . '">' 
					. esc_html__( 'Get Subaccounts Pro', 'subaccounts-for-woocommerce' ) . '</a> <span style="font-weight:400;">' 
					. esc_html__( 'and unlock this feature!', 'subaccounts-for-woocommerce' ) . '</span>',
				'sfwc_option_subaccount_creation_html_dummy',
				'subaccounts&tab=options',
				'sfwc_options_section'
		);
	

		add_settings_field(
				'sfwc_option_selected_roles',
				esc_html__('Select the buyer role(s) to enable the subaccount system for', 'subaccounts-for-woocommerce'),
				'sfwc_option_selected_roles_html',
				'subaccounts&tab=options',
				'sfwc_options_section'
		);
		
		add_settings_field(
				'sfwc_option_subaccounts_number_limit',
				esc_html__('Maximum number of subaccounts allowed', 'subaccounts-for-woocommerce')
				. '<span class="tips woocommerce-help-tip" data-tip="' . esc_attr__( 'Limit the number of subaccounts that a parent account can create/add.', 'subaccounts-for-woocommerce' ) . '"></span>',
				'sfwc_option_subaccounts_number_limit_html',
				'subaccounts&tab=options',
				'sfwc_options_section'
		);

		add_settings_field(
				'sfwc_option_display_name',
				esc_html__('Customer Display Name', 'subaccounts-for-woocommerce')
				. '<span class="tips woocommerce-help-tip" data-tip="' . esc_attr__( 'Set Customer\'s Display Name across the Subaccounts Plugin.', 'subaccounts-for-woocommerce' ) . '"></span>',
				'sfwc_option_display_name_html',
				'subaccounts&tab=options',
				'sfwc_options_section'
		);
	
		add_settings_field(
				'sfwc_options_show_order_meta_box',
				esc_html__('Show subaccounts information on WooCommerce order page', 'subaccounts-for-woocommerce')
				. '<span class="tips woocommerce-help-tip" data-tip="' . esc_attr__( 'Show Customer\'s Manager and Supervisor information on WooCommerce Order Page (Admin area).', 'subaccounts-for-woocommerce' ) . '"></span>',
				'sfwc_options_show_order_meta_box_html',
				'subaccounts&tab=options',
				'sfwc_options_section'
		);
	}
}
add_action('admin_init', 'sfwc_settings_init');




/*
function sfwc_setting_section_callback_function() {
echo '<p>Intro text for our settings section</p>';
}
*/




/**
 * Appearance Tab markup
 */
function sfwc_switcher_pane_bg_color_html() {
	
	// Get 'Appearance' settings
	$sfwc_switcher_appearance = (array) get_option('sfwc_switcher_appearance');

	// Get Pane Background Color.
	$sfwc_switcher_pane_bg_color = ( isset( $sfwc_switcher_appearance['sfwc_switcher_pane_bg_color'] ) ) ? $sfwc_switcher_appearance['sfwc_switcher_pane_bg_color'] : '#def6ff';
	?>
	
	<!-- <label for="my-input"><?php esc_html_e('My Input'); ?></label> -->
	<input type="text" id="sfwc_switcher_pane_bg_color" name="sfwc_switcher_appearance[sfwc_switcher_pane_bg_color]" value="<?php echo esc_attr( $sfwc_switcher_pane_bg_color ); ?>" class="sfwc-color-field" data-default-color="#def6ff" />
	
	<?php
}

function sfwc_switcher_pane_headline_color_html() {
	
	// Get 'Appearance' settings
	$sfwc_switcher_appearance = (array) get_option('sfwc_switcher_appearance');

	// Get Pane Headline Color
	$sfwc_switcher_pane_headline_color = ( isset( $sfwc_switcher_appearance['sfwc_switcher_pane_headline_color'] ) ) ? $sfwc_switcher_appearance['sfwc_switcher_pane_headline_color'] : '#0088cc';
	?>

	<input type="text" id="sfwc_switcher_pane_headline_color" name="sfwc_switcher_appearance[sfwc_switcher_pane_headline_color]" value="<?php echo esc_attr( $sfwc_switcher_pane_headline_color ); ?>" class="sfwc-color-field" data-default-color="#0088cc" />

	<?php
}

function sfwc_switcher_pane_text_color_html() {
	
	// Get 'Appearance' settings
	$sfwc_switcher_appearance = (array) get_option('sfwc_switcher_appearance');

	// Get Pane Text Color.
	$sfwc_switcher_pane_text_color = ( isset( $sfwc_switcher_appearance['sfwc_switcher_pane_text_color'] ) ) ? $sfwc_switcher_appearance['sfwc_switcher_pane_text_color'] : '#3b3b3b';
	?>

	<input type="text" id="sfwc_switcher_pane_text_color" name="sfwc_switcher_appearance[sfwc_switcher_pane_text_color]" value="<?php echo esc_attr( $sfwc_switcher_pane_text_color ); ?>" class="sfwc-color-field" data-default-color="#3b3b3b" />

	<?php
}

function sfwc_switcher_pane_select_bg_color_html() {

	// Get 'Appearance' settings
	$sfwc_switcher_appearance = (array) get_option('sfwc_switcher_appearance');

	// Get Pane Select Button Background Color.
	$sfwc_switcher_pane_select_bg_color = ( isset( $sfwc_switcher_appearance['sfwc_switcher_pane_select_bg_color'] ) ) ? $sfwc_switcher_appearance['sfwc_switcher_pane_select_bg_color'] : '#0088cc';
	?>

	<input type="text" id="sfwc_switcher_pane_select_bg_color" name="sfwc_switcher_appearance[sfwc_switcher_pane_select_bg_color]" value="<?php echo esc_attr( $sfwc_switcher_pane_select_bg_color ); ?>" class="sfwc-color-field" data-default-color="#0088cc" />

	<?php
}

function sfwc_switcher_pane_select_text_color_html() {

	// Get 'Appearance' settings.
	$sfwc_switcher_appearance = (array) get_option('sfwc_switcher_appearance');

	// Get Pane Select Button Text Color.
	$sfwc_switcher_pane_select_text_color = ( isset( $sfwc_switcher_appearance['sfwc_switcher_pane_select_text_color'] ) ) ? $sfwc_switcher_appearance['sfwc_switcher_pane_select_text_color'] : '#ffffff';
	?>

	<input type="text" id="sfwc_switcher_pane_select_text_color" name="sfwc_switcher_appearance[sfwc_switcher_pane_select_text_color]" value="<?php echo esc_attr( $sfwc_switcher_pane_select_text_color ); ?>" class="sfwc-color-field" data-default-color="#ffffff" />

	<?php
}

/**
 * Options Tab markup
 */
function sfwc_option_subaccount_mode_html() {

	// Get 'Options' settings.
    $sfwc_options = (array) get_option( 'sfwc_options' );
	
	// Get Subaccount mode.
	$sfwc_option_subaccount_mode = ( isset( $sfwc_options['sfwc_option_subaccount_mode'] ) ) ? $sfwc_options['sfwc_option_subaccount_mode'] : 'sub_user';
    ?>

    <input style="float:left;clear:left;position:relative;top:5px;" type="radio" name="sfwc_options[sfwc_option_subaccount_mode]" value="sub_user" id="sub_user" <?php checked('sub_user', $sfwc_option_subaccount_mode, true); ?> >
    <label style="float:left;clear:right;margin-bottom:15px;" for="sub_user"><?php esc_html_e( 'Sub-User', 'subaccounts-for-woocommerce' ); ?>
		<br>
		<span style="color:#74808c;"><?php echo esc_html__( "Multiple separate accounts grouped together under one parent account (e.g. Holding Company).", 'subaccounts-for-woocommerce' ); ?></span><br>
	</label>

	
    <input style="float:left;clear:left;position:relative;top:5px;" type="radio" name="sfwc_options[sfwc_option_subaccount_mode]" value="multi_user" id="multi_user" <?php checked('multi_user', $sfwc_option_subaccount_mode, true); ?> >
    <label style="float:left;clear:right;margin-bottom:15px;" for="multi_user"><?php esc_html_e( 'Multi-User', 'subaccounts-for-woocommerce' ); ?>
		<small style="background:#fffbf4; border:1px solid orange; border-radius:3px; padding:0 3px; cursor:auto;"><strong>Beta feature:</strong> feel free to test it on staging sites and report any bugs. Enabling this feature on production sites is <strong>NOT</strong> recommended and could cause unpredictable issues.</small>
		<br>
		<span style="color:#74808c;"><?php echo esc_html__( "Multiple users have access to a same master account (useful in case of Corporate Accounts).", 'subaccounts-for-woocommerce' ); ?></span><br>
	</label>

    <?php
}




function sfwc_option_subaccount_creation_html_dummy() {	// Dummy: only available in Pro. 
	?>
	<input title="<?php esc_attr_e( 'This feature is only available on Subaccounts Pro', 'subaccounts-for-woocommerce'); ?>" type="radio" name="sfwc_set_subaccounts_option" value="" disabled>
	<label for=""><?php esc_html_e('Admin Only', 'subaccounts-for-woocommerce'); ?></label><br>

	<input title="<?php esc_attr_e( 'This feature is only available on Subaccounts Pro', 'subaccounts-for-woocommerce'); ?>" type="radio" name="sfwc_set_subaccounts_option" value="" disabled checked>
	<label for=""><?php esc_html_e('Customers Only', 'subaccounts-for-woocommerce'); ?></label><br>

	<input title="<?php esc_attr_e( 'This feature is only available on Subaccounts Pro', 'subaccounts-for-woocommerce'); ?>" type="radio" name="sfwc_set_subaccounts_option" value="" disabled>
	<label for=""><?php esc_html_e('Both Admin and Customers', 'subaccounts-for-woocommerce'); ?></label>
	
	<p style="margin-top:8px;">
	
	<?php
		$important_strong = '<strong>' . esc_html__( 'Important:', 'subaccounts-for-woocommerce' ) . '</strong>';
		$subaccounts_menu_item = '<em>' . esc_html__( 'Subaccounts', 'subaccounts-for-woocommerce' ) . '</em>';
		$my_account_translation = '<em>' . esc_html__( 'My Account', 'subaccounts-for-woocommerce' ) . '</em>';
		$permalink_path = '<em>' . esc_html__( 'Settings', 'subaccounts-for-woocommerce' ) . '</em> ➝ <em>' . esc_html__( 'Permalinks', 'subaccounts-for-woocommerce' ) . '</em>';
		$save_changes = '<em>' . esc_html__( 'Save Changes', 'subaccounts-for-woocommerce' ) . '</em>';
		
		
		printf(
			esc_html__( '%1$s in case the %2$s menu item is not shown on the %3$s page, please update the WordPress permalinks structure by going to: %4$s and clicking %5$s.', 'subaccounts-for-woocommerce' ), 
			$important_strong,
			$subaccounts_menu_item,
			$my_account_translation,
			$permalink_path,
			$save_changes
		);
	?>

	</p>
	<?php
}

function sfwc_option_selected_roles_html() {

	global $wp_roles;

	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}
	
	$roles = $wp_roles->get_names();

	// Get 'Options' settings
	$sfwc_options = (array) get_option('sfwc_options');

	// Get Selected Roles option value.
	$sfwc_option_selected_roles = ( isset( $sfwc_options['sfwc_option_selected_roles'] ) ) ? $sfwc_options['sfwc_option_selected_roles'] : array('customer', 'subscriber');
	
	// All potential WordPress user capabilities (excluding Super Admin ones), including those added by WooCommerce.
	// Following capabilities, if all present, represent capabilities granted for Admin users.
	$all_potential_wp_capabilities = array( 'switch_themes'=>1, 'edit_themes'=>1, 'activate_plugins'=>1, 'edit_plugins'=>1, 'edit_users'=>1, 
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

	echo '<div style="max-width:28rem;">';
	echo '<select id="sfwc_option_selected_roles" name="sfwc_options[sfwc_option_selected_roles][]" multiple>';

	if ( ! empty( $roles ) ) {

		foreach ( $roles as $role => $value ) {

			$selected = in_array( $role, $sfwc_option_selected_roles ) ? ' selected="selected" ' : '';

			$role_capabilities = get_role( $role )->capabilities;
			
			
			if ( defined( 'SFWC_VALID_ROLE_LOOSE_MODE' ) && SFWC_VALID_ROLE_LOOSE_MODE == true ) {
			
				/*****************
				 * Loose Method  *
				 *****************/
				
				if ( ! empty( $role_capabilities ) ) {
					
					if ( array_key_exists( 'read', $role_capabilities ) && ! array_key_exists( 'edit_posts', $role_capabilities ) ) {
						
						printf(
							'<option value="%1$s"' . $selected . '>%2$s</option>', 
							esc_attr( $role ), 
							esc_html( translate_user_role( $value ) )
						);						
					}
				}
			}
			else {
				
				/*****************
				 * Strict Method *
				 *****************/

				$result = array_intersect_key( $role_capabilities, $all_potential_wp_capabilities );
				
				#print_r( $result );
				
				if ( ! empty( $result ) && count( $result ) === 1 && key( $result ) == 'read' ) {
				
					printf(
						'<option value="%1$s"' . $selected . '>%2$s</option>', 
						esc_attr( $role ), 
						esc_html( translate_user_role( $value ) )
					);
				}
			}
		}	
	}	

	echo '</select>';
	echo '<p>' . esc_html__( "Only roles eligible for the subaccount system are available for selection.", 'subaccounts-for-woocommerce' ) .'<p>';
	echo '</div>';
?>	

	<script>
	(function($) {
		// Initialize Selectize.
		$("#sfwc_option_selected_roles").selectize({
			placeholder: 'Select role...',
			plugins:
			[
				"remove_button",
			]
		});
	})( jQuery );
	</script>	

<?php	
}

function sfwc_option_subaccounts_number_limit_html() {

	// Get 'Options' settings
	$sfwc_options = (array) get_option('sfwc_options');

	// Get Subaccounts Number Limit option value.
	$sfwc_option_subaccounts_number_limit = ( isset( $sfwc_options['sfwc_option_subaccounts_number_limit'] ) ) ? $sfwc_options['sfwc_option_subaccounts_number_limit'] : 10;
	?>

	<input type="number" id="sfwc_option_subaccounts_number_limit" name="sfwc_options[sfwc_option_subaccounts_number_limit]" value="<?php echo esc_attr( $sfwc_option_subaccounts_number_limit ); ?>" style="max-width:65px;" <?php echo ( ! is_plugin_active( 'subaccounts-for-woocommerce-pro/subaccounts-for-woocommerce-pro.php' ) ? 'min="1" max="10"' : 'min="0"' ); ?> />
	<?php
	esc_html_e( "Set 0 for unlimited", 'subaccounts-for-woocommerce' );
	
	if ( ! is_plugin_active( 'subaccounts-for-woocommerce-pro/subaccounts-for-woocommerce-pro.php' ) ) {

		/*
		$subaccounts_pro = '<a style="color: #148ff3; font-weight: 600;" href="' . 
							admin_url( '/admin.php?checkout=true&page=subaccounts-pricing&plugin_id=10457&plan_id=17669&pricing_id=19941&billing_cycle=annual' ) . 
							'">Subaccounts Pro</a>';
		*/
		
		$subaccounts_pro = 'Subaccounts Pro';
		
		printf(
			' (' . esc_html__( '%1$s required', 'subaccounts-for-woocommerce' ) . ')',
			$subaccounts_pro
		);
	}
	echo '.';
}

function sfwc_option_display_name_html() {

	// Get 'Options' settings.
    $sfwc_options = (array) get_option( 'sfwc_options' );
	
	// Get Display Name mode.
	$sfwc_option_display_name = ( isset( $sfwc_options['sfwc_option_display_name'] ) ) ? $sfwc_options['sfwc_option_display_name'] : 'username';
    ?>


    <input style="float:left;clear:left;position:relative;top:5px;" type="radio" name="sfwc_options[sfwc_option_display_name]" value="username" id="username" <?php checked('username', $sfwc_option_display_name, true); ?> >
    <label style="float:left;clear:right;margin-bottom:15px;" for="username"><?php esc_html_e( 'Username + Email', 'subaccounts-for-woocommerce' ); ?><br>
        <span style="color:#74808c;"><strong><?php esc_html_e( 'Example:', 'subaccounts-for-woocommerce' ); ?></strong> John.doe - [ johndoe@email.com ]</span></label><br><br>


    <input style="float:left;clear:left;position:relative;top:5px;" type="radio" name="sfwc_options[sfwc_option_display_name]" value="full_name" id="full_name" <?php checked('full_name', $sfwc_option_display_name, true); ?> >
    <label style="float:left;clear:right;margin-bottom:15px;" for="full_name"><?php esc_html_e( 'Full Name + Email', 'subaccounts-for-woocommerce' ); ?><br>
        <span style="color:#74808c;"><strong><?php esc_html_e( 'Example:', 'subaccounts-for-woocommerce' ); ?></strong> John Doe - [ johndoe@email.com ]</span><br>
        <em><?php esc_html_e( "If neither the First Name nor Last Name have been set, the customer's Username will be shown as a fallback.", "subaccounts-for-woocommerce" ); ?></em>
    </label><br><br>


    <input style="float:left;clear:left;position:relative;top:5px;" type="radio" name="sfwc_options[sfwc_option_display_name]" value="company_name" id="company_name" <?php checked('company_name', $sfwc_option_display_name, true); ?> >
    <label style="float:left;clear:right;margin-bottom:15px;" for="company_name"><?php esc_html_e( 'Company + Email', 'subaccounts-for-woocommerce' ); ?><br>
        <span style="color:#74808c;"><strong><?php esc_html_e( 'Example:', 'subaccounts-for-woocommerce' ); ?></strong> Enterprise Inc - [ johndoe@email.com ]</span><br>
        <em><?php esc_html_e( "If no Company Name has been set, the customer's Username will be shown as a fallback.", "subaccounts-for-woocommerce" ); ?></em>
    </label>

    <?php
}

function sfwc_options_show_order_meta_box_html() {

	// Get 'Options' settings
	$sfwc_options = (array) get_option('sfwc_options');

	// Get Show Meta Box value
	$sfwc_options_show_order_meta_box = ( isset( $sfwc_options['sfwc_options_show_order_meta_box'] ) ) ? $sfwc_options['sfwc_options_show_order_meta_box'] : 0;
	?>

	<input type="checkbox" id="sfwc_options_show_order_meta_box" name="sfwc_options[sfwc_options_show_order_meta_box]" value="1" <?php checked(1, $sfwc_options_show_order_meta_box, true) ?> />
	<?php
	esc_html_e( "You may need to enable this under 'Screen Options' too.", 'subaccounts-for-woocommerce' );
}




/**
 * Appearance Options Validation.
 */
function sfwc_switcher_appearance_validate( $input ) {

	// Create our array for storing the validated options.
	$output = array();

	// Loop through each of the incoming options.
	foreach ( $input as $key => $value ) {

		// Check if the current option has a value.
		if ( isset( $input[$key] ) ) {

			// if user insert a HEX color with #.
			if ( preg_match( '/^#[0-9a-fA-F]{6}$/i', $input[$key] ) ) {

				// Sanitization should not be required here due to strict preg_match comparison, anyway...
				$output[$key] = sanitize_text_field( $input[$key] );
			} else {

				if ( $input[$key] == $input['sfwc_switcher_pane_bg_color'] ) {

					add_settings_error( 'sfwc_settings_messages', 'sfwc_wrong_hex_color', esc_html__( 'Incorrect value entered for: Background Color.', 'subaccounts-for-woocommerce' ), 'warning' );
				}
				if ( $input[$key] == $input['sfwc_switcher_pane_headline_color'] ) {

					add_settings_error( 'sfwc_settings_messages', 'sfwc_wrong_hex_color', esc_html__( 'Incorrect value entered for: Headline Color.', 'subaccounts-for-woocommerce' ), 'warning' );
				}
				if ( $input[$key] == $input['sfwc_switcher_pane_text_color'] ) {

					add_settings_error( 'sfwc_settings_messages', 'sfwc_wrong_hex_color', esc_html__( 'Incorrect value entered for: Text Color.', 'subaccounts-for-woocommerce' ), 'warning' );
				}
				if ( $input[$key] == $input['sfwc_switcher_pane_select_bg_color'] ) {

					add_settings_error( 'sfwc_settings_messages', 'sfwc_wrong_hex_color', esc_html__( 'Incorrect value entered for: Button Background Color.', 'subaccounts-for-woocommerce' ), 'warning' );
				}
				if ( $input[$key] == $input['sfwc_switcher_pane_select_text_color'] ) {

					add_settings_error( 'sfwc_settings_messages', 'sfwc_wrong_hex_color', esc_html__( 'Incorrect value entered for: Button Text Color.', 'subaccounts-for-woocommerce' ), 'warning' );
				}
			}
		}
	}

	// Return the array processing any additional functions filtered by this action.
	return apply_filters('sfwc_switcher_appearance_validate', $output, $input);
}




/**
 * Settings Options Validation.
 */
function sfwc_options_validate( $input ) {

	// Create our array for storing the validated options.
	$output = array();



	if ( ! is_plugin_active( 'subaccounts-for-woocommerce-pro/subaccounts-for-woocommerce-pro.php' ) ) {
		// Check if the current option has a value.
		if ( isset( $input['sfwc_option_subaccounts_number_limit'] ) ) {

			// Sanitize sfwc_option_subaccounts_number_limit.
			$sanitized_subaccounts_number_limit = preg_replace( '/[^0-9]/', '', $input['sfwc_option_subaccounts_number_limit'] );
			
			
			// Validate sfwc_option_subaccounts_number_limit.
			if ( is_numeric( $sanitized_subaccounts_number_limit ) ) {
				
				if ( $sanitized_subaccounts_number_limit >= 1 && $sanitized_subaccounts_number_limit <= 10 ) {
				
					$output['sfwc_option_subaccounts_number_limit'] = $sanitized_subaccounts_number_limit;
				} 
				else {

					add_settings_error(
						'sfwc_settings_messages',
						'sfwc_wrong_subaccounts_number_limit',
						esc_html__( 'Incorrect value entered for: Maximum number of subaccounts allowed. Please enter a value between 1 and 10.', 'subaccounts-for-woocommerce' ),
						'error'
					);
				}
			}
			else {

				add_settings_error(
					'sfwc_settings_messages',
					'sfwc_wrong_subaccounts_number_limit',
					esc_html__( 'Incorrect value entered for: Maximum number of subaccounts allowed.', 'subaccounts-for-woocommerce' ),
					'error'
				);
			}
		}
	}




    // Check if the current option has a value.
    if ( isset( $input['sfwc_option_selected_roles'] ) ) {
		
		$sanitized_roles = array();
		$valid_roles = array();
		
		// Sanitize posted values.
		foreach ( $input['sfwc_option_selected_roles'] as $single_role ) {
			
			$sanitized_roles[] = sanitize_text_field( $single_role ); 
		}
		
		// All potential WordPress user capabilities (excluding Super Admin ones), including those added by WooCommerce.
		// Following capabilities, if all present, represent capabilities granted for Admin users.
		$all_potential_wp_capabilities = array( 'switch_themes'=>1, 'edit_themes'=>1, 'activate_plugins'=>1, 'edit_plugins'=>1, 'edit_users'=>1, 
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
		
		// Validate posted values.
		if ( ! empty( $sanitized_roles ) ) {
			
			foreach ( $sanitized_roles as $sanitized_role ) {
				
				$role_capabilities = get_role( $sanitized_role )->capabilities;

				if ( defined( 'SFWC_VALID_ROLE_LOOSE_MODE' ) && SFWC_VALID_ROLE_LOOSE_MODE == true ) {
				
					/*****************
					 * Loose Method  *
					 *****************/
					
					if ( ! empty( $role_capabilities ) ) {
						
						if ( array_key_exists( 'read', $role_capabilities ) && ! array_key_exists( 'edit_posts', $role_capabilities ) ) {
							
							$valid_roles[] = $sanitized_role;						
						}
					}
				}
				else {
					
					/*****************
					 * Strict Method *
					 *****************/

					$result = array_intersect_key( $role_capabilities, $all_potential_wp_capabilities );
					
					#print_r( $result );
					
					if ( ! empty( $result ) && count( $result ) === 1 && key( $result ) == 'read' ) {
					
						$valid_roles[] = $sanitized_role;
					}
				}
			}
		}

		if ( ! empty( $valid_roles ) ) {
			
			$output['sfwc_option_selected_roles'] = $valid_roles;
		}
		else {

            add_settings_error( 
				'sfwc_settings_messages',
				'sfwc_wrong_display_name',
				esc_html__( 'Incorrect value entered for: Select the buyer role(s) to enable the subaccount system for', 'subaccounts-for-woocommerce' ),
				'error'
			);
        }
    }




	// Check if the current option has a value.
    if ( isset( $input['sfwc_option_subaccount_mode'] ) ) {

        $valid_radio_subaccount_mode = array( 'sub_user', 'multi_user' );

        if ( in_array( $input['sfwc_option_subaccount_mode'], $valid_radio_subaccount_mode ) ) {

            $output['sfwc_option_subaccount_mode'] = sanitize_text_field( $input['sfwc_option_subaccount_mode'] );

        } else {

            add_settings_error( 
				'sfwc_settings_messages',
				'sfwc_wrong_subaccount_mode',
				esc_html__( 'Incorrect value entered for: Subaccount Mode.', 'subaccounts-for-woocommerce' ),
				'error'
			);
        }
    }




	// Check if the current option has a value.
    if ( isset( $input['sfwc_option_display_name'] ) ) {

        $valid_radio_display_name = array( 'username', 'full_name', 'company_name' );

        if ( in_array( $input['sfwc_option_display_name'], $valid_radio_display_name ) ) {

            $output['sfwc_option_display_name'] = sanitize_text_field( $input['sfwc_option_display_name'] );

        } else {

            add_settings_error( 
				'sfwc_settings_messages',
				'sfwc_wrong_display_name',
				esc_html__( 'Incorrect value entered for: Customer Display Name.', 'subaccounts-for-woocommerce' ),
				'error'
			);
        }
    }




	// Default value if not set.
	$output['sfwc_options_show_order_meta_box'] = '0';

	// Check if the current option has a value.
	if ( isset( $input['sfwc_options_show_order_meta_box'] ) ) {

		$output['sfwc_options_show_order_meta_box'] = '1';
	}


	// Validate PRO settings.
	do_action_ref_array('sfwc_options_validate', array(&$output, &$input));

	return $output;
}




/**
 * Create Tabbed content for settings page
 */
function sfwc_admin_page_contents() {

	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// add error/update messages
	// check if the user have submitted the settings
	// WordPress will add the "settings-updated" $_GET parameter to the url
	if ( isset( $_GET['settings-updated'] ) ) {

		// add settings saved message with the class of "updated"
		add_settings_error( 'sfwc_settings_messages', 'sfwc_settings_message', esc_html__( 'Settings Saved.', 'subaccounts-for-woocommerce' ), 'updated' );
	}

	// show error/update messages
	settings_errors('sfwc_settings_messages');


	global $sfwc_active_tab;
	$sfwc_active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'options';
	?>


	<h1 style="margin-top:40px;">
		<span style="font-size:30px; margin-right:5px; position:relative; top:-6px;" class="dashicons dashicons-groups"></span>
	<?php esc_html_e( 'Subaccounts for WooCommerce', 'subaccounts-for-woocommerce' ); ?>
	</h1>

	<h2>
	<?php esc_html_e('Settings Page', 'subaccounts-for-woocommerce'); ?>
	</h2>		

	<p>
	<?php esc_html_e( 'On this page you can configure the Subaccounts plugin settings.', 'subaccounts-for-woocommerce' ); ?>
	</p>



	<h2 class="nav-tab-wrapper">
	<?php
	do_action('sfwc_settings_tab');
	?>
	</h2>
	<?php
	do_action('sfwc_settings_content');
}

/**
 * Tab: 1
 */
function sfwc_options_tab() {
	global $sfwc_active_tab;
	?>
	<a class="nav-tab <?php echo $sfwc_active_tab == 'options' || '' ? 'nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=subaccounts&tab=options' ); ?>"><?php esc_html_e( 'Options', 'subaccounts-for-woocommerce' ); ?> </a>
	<?php
}
add_action('sfwc_settings_tab', 'sfwc_options_tab', 1);



function sfwc_options_tab_content() {

	global $sfwc_active_tab;

	if ( 'options' != $sfwc_active_tab )
		return;
	?>

	<div id="sub_accounts_settings_options_tab">

		<h3><?php esc_html_e( 'Options', 'subaccounts-for-woocommerce' ); ?></h3>


		<!-- Appearance content here -->
		<form method="POST" action="options.php">

		<?php		
		// Real options
		settings_fields('sfwc_options_group');
		do_settings_sections('subaccounts&tab=options');

		// Fake options (only available in Pro version)
		do_action('sfwc_dummy_html_markup_after_enabled_options');

		submit_button();
		?>

		</form>
	</div>
	<?php
}
add_action('sfwc_settings_content', 'sfwc_options_tab_content');




/**
 * Tab: 2
 */
function sfwc_appearance_tab() {
	global $sfwc_active_tab;
	?>
	<a class="nav-tab <?php echo $sfwc_active_tab == 'appearance' ? 'nav-tab-active' : ''; ?>" href="<?php echo admin_url('admin.php?page=subaccounts&tab=appearance'); ?>"><?php esc_html_e('Appearance', 'subaccounts-for-woocommerce'); ?> </a>
	<?php
}
add_action('sfwc_settings_tab', 'sfwc_appearance_tab', 2);




function sfwc_appearance_tab_content() {
	global $sfwc_active_tab;
	if ( '' || 'appearance' != $sfwc_active_tab )
		return;
	?>

	<div id="sub_accounts_settings_appearance_tab">
		<div id="sub_accounts_settings_appearance_tab_left">
		<!-- <h3><?php esc_html_e( 'Frontend User Switcher Pane Appearance', 'subaccounts-for-woocommerce' ); ?></h3> -->

			<!-- Appearance content here -->
			<form method="POST" action="options.php">
			<?php
			settings_fields('sfwc_switcher_appearance_group');
			do_settings_sections('subaccounts');
			submit_button();
			?>
			</form>

		</div>


		<div id="sub_accounts_settings_appearance_tab_right">

			<h3><?php esc_html_e( 'User Switcher Pane Preview', 'subaccounts-for-woocommerce' ); ?></h3>

			<p>
				<?php
					$user_switcher_pane_translation = '<em>' . esc_html__( 'User Switcher Pane', 'subaccounts-for-woocommerce' ) . '</em>';
					$my_account_translation = '<strong>' . esc_html__( 'My Account', 'subaccounts-for-woocommerce' ) . '</strong>';
					$supervisor_add_on_translation = '<em>' . esc_html__( 'Supervisor Add-on', 'subaccounts-for-woocommerce' ) . '</em>';
					
					printf(
						esc_html__( 'This is the preview of the %1$s, which is visible in the %2$s area only for Manager account types. In case the %3$s is installed, the User Switcher Pane is also visible for Supervisor account types.', 'subaccounts-for-woocommerce' ), 
						$user_switcher_pane_translation,
						$my_account_translation,
						$supervisor_add_on_translation
					);
				?>
			</p>

			<div id="sfwc-user-switcher-pane">

				<h3><?php esc_html_e( 'You are currently logged in as:', 'subaccounts-for-woocommerce' ); ?></h3>
				<p style="color:' . $sfwc_switcher_pane_text_color . ';"><strong><?php esc_html_e( 'User:', 'subaccounts-for-woocommerce' ); ?></strong> John Doe - [ johndoe@email.com ]</p>

				<form method="post">
					<select>

						<option selected="selected" disabled><?php esc_html_e( 'Select Account', 'subaccounts-for-woocommerce' ); ?>&nbsp; &#8644;</option>
						<option>James Miller - [ jamesmiller@email.com ]</option>
						<option>Robert Williams - [ robertwilliams@email.com ]</option>
						<option>Rebecca Smith - [ rebeccasmith@email.com ]</option>

					</select>
				</form>
			</div>

			<p id="sfwc-user-switcher-pane-default-values"><?php esc_html_e( '[Restore default color scheme]', 'subaccounts-for-woocommerce' ); ?></p>
		</div>
	</div>

	<script>
	jQuery(document).ready(function ($) {

		$('#sfwc-user-switcher-pane').css('background-color', ($('#sfwc_switcher_pane_bg_color').val()));
		$('#sfwc_switcher_pane_bg_color').wpColorPicker({
			change: function (event, ui) {
				var theColor = ui.color.toString();
				$('#sfwc-user-switcher-pane').css('background-color', theColor);
			}
		});



		$('#sfwc-user-switcher-pane h3').css('color', ($('#sfwc_switcher_pane_headline_color').val()));
		$('#sfwc_switcher_pane_headline_color').wpColorPicker({
			change: function (event, ui) {
				var theColor = ui.color.toString();
				$('#sfwc-user-switcher-pane h3').css('color', theColor);
			}
		});



		$('#sfwc-user-switcher-pane p').css('color', ($('#sfwc_switcher_pane_text_color').val()));
		$('#sfwc_switcher_pane_text_color').wpColorPicker({
			change: function (event, ui) {
				var theColor = ui.color.toString();
				$('#sfwc-user-switcher-pane p').css('color', theColor);
			}
		});



		$('#sfwc-user-switcher-pane select').css('background-color', ($('#sfwc_switcher_pane_select_bg_color').val()));
		$('#sfwc_switcher_pane_select_bg_color').wpColorPicker({
			change: function (event, ui) {
				var theColor = ui.color.toString();
				$('#sfwc-user-switcher-pane select').css('background-color', theColor);
			}
		});



		$('#sfwc-user-switcher-pane select').css('color', ($('#sfwc_switcher_pane_select_text_color').val()));
		$('#sfwc_switcher_pane_select_text_color').wpColorPicker({
			change: function (event, ui) {
				var theColor = ui.color.toString();
				$('#sfwc-user-switcher-pane select').css('color', theColor);
			}
		});


		/* Restore default color scheme */
		jQuery('#sfwc-user-switcher-pane-default-values').on('click', function () {

			$('#sfwc_switcher_pane_bg_color').val('#def6ff'); // Reset input value
			$('#sfwc_switcher_pane_bg_color').closest('.wp-picker-container').children('.wp-color-result').css('background-color', '#def6ff'); // Reset wp color picker box
			$('#sfwc-user-switcher-pane').css('background-color', '#def6ff'); // Reset User Switcher preview

			$('#sfwc_switcher_pane_headline_color').val('#0088cc');
			$('#sfwc_switcher_pane_headline_color').closest('.wp-picker-container').children('.wp-color-result').css('background-color', '#0088cc');
			$('#sfwc-user-switcher-pane h3').css('color', '#0088cc');

			$('#sfwc_switcher_pane_text_color').val('#3b3b3b');
			$('#sfwc_switcher_pane_text_color').closest('.wp-picker-container').children('.wp-color-result').css('background-color', '#3b3b3b');
			$('#sfwc-user-switcher-pane p').css('color', '#3b3b3b');

			$('#sfwc_switcher_pane_select_bg_color').val('#0088cc');
			$('#sfwc_switcher_pane_select_bg_color').closest('.wp-picker-container').children('.wp-color-result').css('background-color', '#0088cc');
			$('#sfwc-user-switcher-pane select').css('background-color', '#0088cc');

			$('#sfwc_switcher_pane_select_text_color').val('#ffffff');
			$('#sfwc_switcher_pane_select_text_color').closest('.wp-picker-container').children('.wp-color-result').css('background-color', '#ffffff');
			$('#sfwc-user-switcher-pane select').css('color', '#ffffff');
		});

	});
	</script>

	<?php
}
add_action('sfwc_settings_content', 'sfwc_appearance_tab_content');




/**
 * Dummy content for Option tab in plugin's Settings admin page.
 *
 * Add dummy content for Option tab in case Subaccounts Pro is not installed.
 */
function sfwc_add_dummy_html_markup_after_enabled_options() {
	?>

	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th scope="row" style="padding: 15px 10px 20px 0;">
					<?php echo esc_html__('Show subaccounts information on WooCommerce orders list page', 'subaccounts-for-woocommerce') . '<span class="tips woocommerce-help-tip" data-tip="' . esc_attr__("Add 'Account Type' and 'Parent Accounts' columns to WooCommerce orders list page in WordPress admin area.", 'subaccounts-for-woocommerce') . '"></span>'; ?>
					<br>
					<a style="color:#148ff3;" href="<?php echo admin_url( '/admin.php?checkout=true&page=subaccounts-pricing&plugin_id=10457&plan_id=17669&pricing_id=19941&billing_cycle=annual' ); ?>">
					<?php echo esc_html__( 'Get Subaccounts Pro', 'subaccounts-for-woocommerce' ); ?></a>

					<span style="font-weight:400;">
						<?php echo esc_html__( 'and unlock this feature!', 'subaccounts-for-woocommerce' ); ?>
					</span>
				</th>
				<td>
					<input title="<?php esc_attr_e( 'This feature is only available on Subaccounts Pro', 'subaccounts-for-woocommerce'); ?>" type="checkbox" name="sfwc_columns_order" value="" disabled>
					<span style="vertical-align: middle;"><?php esc_html_e( "You may need to enable this under 'Screen Options' too.", "subaccounts-for-woocommerce" ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding: 15px 10px 20px 0;">
					<?php echo esc_html__( 'Show subaccounts information on WordPress users list page', 'subaccounts-for-woocommerce' ) . '<span class="tips woocommerce-help-tip" data-tip="' . esc_attr__( "Add 'Account Type', 'Subaccounts' and 'Parent Accounts' columns to the users list page in WordPress admin area.", "subaccounts-for-woocommerce" ) . '"></span>'; ?>
					<br>
					<a style="color:#148ff3;" href="<?php echo admin_url( '/admin.php?checkout=true&page=subaccounts-pricing&plugin_id=10457&plan_id=17669&pricing_id=19941&billing_cycle=annual' ); ?>">
					<?php echo esc_html__( 'Get Subaccounts Pro', 'subaccounts-for-woocommerce' ); ?></a>

					<span style="font-weight:400;">
						<?php echo esc_html__( 'and unlock this feature!', 'subaccounts-for-woocommerce' ); ?>
					</span>
				</th>
				<td>
					<input title="<?php esc_attr_e( 'This feature is only available on Subaccounts Pro', 'subaccounts-for-woocommerce'); ?>" type="checkbox" name="sfwc_columns_users" value="" disabled>
					<span style="vertical-align: middle;"><?php esc_html_e( "You may need to enable this under 'Screen Options' too.", "subaccounts-for-woocommerce" ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding: 15px 10px 20px 0;">
					<?php echo esc_html__('Add subaccounts information to new order emails', 'subaccounts-for-woocommerce') . '<span class="tips woocommerce-help-tip" data-tip="' . esc_attr__( 'Show customer\'s Manager and Supervisor information in new order emails.', "subaccounts-for-woocommerce" ) . '"></span>'; ?>
					<br>
					<a style="color:#148ff3;" href="<?php echo admin_url( '/admin.php?checkout=true&page=subaccounts-pricing&plugin_id=10457&plan_id=17669&pricing_id=19941&billing_cycle=annual' ); ?>">
					<?php echo esc_html__( 'Get Subaccounts Pro', 'subaccounts-for-woocommerce' ); ?></a>

					<span style="font-weight:400;">
						<?php echo esc_html__( 'and unlock this feature!', 'subaccounts-for-woocommerce' ); ?>
					</span>
				</th>
				<td>
					<input title="<?php esc_attr_e( 'This feature is only available on Subaccounts Pro', 'subaccounts-for-woocommerce'); ?>" type="radio" name="sfwc_email_option" value="" disabled checked>
					<label for=""><?php esc_html_e('No', 'subaccounts-for-woocommerce'); ?></label><br>

					<input title="<?php esc_attr_e( 'This feature is only available on Subaccounts Pro', 'subaccounts-for-woocommerce'); ?>" type="radio" name="sfwc_email_option" value="" disabled>
					<label for=""><?php esc_html_e('For Admin Only', 'subaccounts-for-woocommerce'); ?></label><br>

					<input title="<?php esc_attr_e( 'This feature is only available on Subaccounts Pro', 'subaccounts-for-woocommerce'); ?>" type="radio" name="sfwc_email_option" value="" disabled>
					<label for=""><?php esc_html_e('For Customer Only', 'subaccounts-for-woocommerce'); ?></label><br>

					<input title="<?php esc_attr_e( 'This feature is only available on Subaccounts Pro', 'subaccounts-for-woocommerce'); ?>" type="radio" name="sfwc_email_option" value="" disabled>
					<label for=""><?php esc_html_e('For both Admin and Customer', 'subaccounts-for-woocommerce'); ?></label>
					
					<p style="margin-top:8px;">
					
						<?php
							$important_strong = '<strong>' . esc_html__( 'Important:', 'subaccounts-for-woocommerce' ) . '</strong>';
							$email_settings_path =	'<em>' . esc_html__( 'WooCommerce', 'subaccounts-for-woocommerce' ) . '</em> ➝ ' . 
													'<em>' . esc_html__( 'Settings', 'subaccounts-for-woocommerce' ) . '</em> ➝ ' . 
													'<em>' . esc_html__( 'Emails', 'subaccounts-for-woocommerce' ) . '</em>';
							
							
							printf(
								esc_html__( '%1$s make sure email notifications are properly set and enabled in: %2$s.', 'subaccounts-for-woocommerce' ), 
								$important_strong,
								$email_settings_path
							);
						?>

					</p>
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding: 15px 10px 20px 0;">
					<?php echo esc_html__( 'Subaccount billing address', 'subaccounts-for-woocommerce' ) . '<span class="tips woocommerce-help-tip" data-tip="' . esc_attr__( "With this option enabled whenever a Manager billing address is updated, the changes are reflected to subaccounts as well.", "subaccounts-for-woocommerce" ) . '"></span>'; ?>
					<br>
					<a style="color:#148ff3;" href="<?php echo admin_url( '/admin.php?checkout=true&page=subaccounts-pricing&plugin_id=10457&plan_id=17669&pricing_id=19941&billing_cycle=annual' ); ?>">
					<?php echo esc_html__( 'Get Subaccounts Pro', 'subaccounts-for-woocommerce' ); ?></a>

					<span style="font-weight:400;">
						<?php echo esc_html__( 'and unlock this feature!', 'subaccounts-for-woocommerce' ); ?>
					</span>
				</th>
				<td>
					<input title="<?php esc_attr_e( 'This feature is only available on Subaccounts Pro', 'subaccounts-for-woocommerce'); ?>" type="checkbox" name="sfwc_columns_users" value="" disabled>
					<span style="vertical-align: middle;"><?php esc_html_e( "Force subaccounts to inherit the billing address from their Manager.", "subaccounts-for-woocommerce" ); ?></span>
					
					<p style="margin-top:8px;">
					
						<?php
							$important_strong = '<strong>' . esc_html__( 'Important:', 'subaccounts-for-woocommerce' ) . '</strong>';
							$my_account_edit_address_path =	'<em>' . esc_html__( 'My Account', 'subaccounts-for-woocommerce' ) . '</em> ➝ ' . 
															'<em>' . esc_html__( 'Addresses', 'subaccounts-for-woocommerce' ) . '</em>';
							
							
							printf(
								esc_html__( '%1$s with this option enabled, subaccount billing address fields in: %2$s will become uneditable.', 'subaccounts-for-woocommerce' ), 
								$important_strong,
								$my_account_edit_address_path
							);
						?>

					</p>
				</td>
			</tr>
			<tr>
				<th scope="row" style="padding: 15px 10px 20px 0;">
					<?php echo esc_html__( 'Subaccount shipping address', 'subaccounts-for-woocommerce' ) . '<span class="tips woocommerce-help-tip" data-tip="' . esc_attr__( "With this option enabled whenever a Manager's shipping address is updated, the changes are reflected to subaccounts as well.", "subaccounts-for-woocommerce" ) . '"></span>'; ?>
					<br>
					<a style="color:#148ff3;" href="<?php echo admin_url( '/admin.php?checkout=true&page=subaccounts-pricing&plugin_id=10457&plan_id=17669&pricing_id=19941&billing_cycle=annual' ); ?>">
					<?php echo esc_html__( 'Get Subaccounts Pro', 'subaccounts-for-woocommerce' ); ?></a>

					<span style="font-weight:400;">
						<?php echo esc_html__( 'and unlock this feature!', 'subaccounts-for-woocommerce' ); ?>
					</span>
				</th>
				<td>
					<input title="<?php esc_attr_e( 'This feature is only available on Subaccounts Pro', 'subaccounts-for-woocommerce'); ?>" type="checkbox" name="sfwc_columns_users" value="" disabled>
					<span style="vertical-align: middle;"><?php esc_html_e( "Force subaccounts to inherit the shipping address from their Manager.", "subaccounts-for-woocommerce" ); ?></span>
					
					<p style="margin-top:8px;">
					
						<?php
							$important_strong = '<strong>' . esc_html__( 'Important:', 'subaccounts-for-woocommerce' ) . '</strong>';
							$my_account_edit_address_path =	'<em>' . esc_html__( 'My Account', 'subaccounts-for-woocommerce' ) . '</em> ➝ ' . 
															'<em>' . esc_html__( 'Addresses', 'subaccounts-for-woocommerce' ) . '</em>';
							
							
							printf(
								esc_html__( '%1$s with this option enabled, subaccount shipping address fields in: %2$s will become uneditable.', 'subaccounts-for-woocommerce' ), 
								$important_strong,
								$my_account_edit_address_path
							);
						?>

					</p>
				</td>
			</tr>
		<tbody>
	</table>
	<?php
}
add_action('sfwc_dummy_html_markup_after_enabled_options', 'sfwc_add_dummy_html_markup_after_enabled_options');




/**
 * Color picker scripts.
 *
 * Required for Appearance tab in plugin's Settings admin page.
 */
function sfwc_enqueue_color_picker() {
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
}
add_action('admin_enqueue_scripts', 'sfwc_enqueue_color_picker');

function sfwc_admin_inline_js_color_picker() {
	echo "<script>
		jQuery(document).ready(function($){
		$('.sfwc-color-field').wpColorPicker();
		});
		</script>";
}
add_action('admin_print_footer_scripts', 'sfwc_admin_inline_js_color_picker');




/**
 * Register new endpoint "Add Subaccount" to use for My Account page.
 * 
 *		-------------------------------------------------
 * 		Remember to update Permalinks to avoid 404 error.
 *		-------------------------------------------------
 *
 * Do NOT move this piece of code to: subaccounts-for-woocommerce > public > my-account.php
 * Other code in: subaccounts-for-woocommerce > public > my-account.php
 * See: "Add Subaccount" menu item to My Acount page.
 *
 * DO NOT REMOVE!
 * Removed on version 1.1.3 and restored on version 1.1.4 to prevent endpoint returning 'Not Found Error'.
 *
 * Keep this function here, in addition to function: sfwc_subaccounts_endpoint_query_vars
 * in: subaccounts-for-woocommerce > public > my-account.php
 */
function sfwc_register_subaccounts_endpoint() {
    add_rewrite_endpoint( 'subaccounts', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'sfwc_register_subaccounts_endpoint' );