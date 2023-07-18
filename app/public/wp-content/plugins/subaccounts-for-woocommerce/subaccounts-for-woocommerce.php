<?php
/**
 * Plugin Name: Subaccounts for WooCommerce
 * Plugin URI: https://subaccounts.pro/
 * Description: Subaccounts for WooCommerce allows the creation of subaccounts for your WooCommerce customers and subscribers.
 * Version: 1.4.0
 * Author: Mediaticus
 * Update URI: https://wordpress.org/plugins/subaccounts-for-woocommerce/
 *
 * Text Domain: subaccounts-for-woocommerce
 * Domain Path: /languages/
 *
 * Requires at least: 5.7
 * Tested up to: 6.2
 *
 * WC tested up to: 7.8.2
 * Requires PHP: 5.7
 *
 * Copyright 2022 Mediaticus
 *
 * License: GPL3
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}




if ( ! function_exists( 'sfwc_free' ) ) {
    // Create a helper function for easy SDK access.
    function sfwc_free() {
        global $sfwc_free;

        if ( ! isset( $sfwc_free ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $sfwc_free = fs_dynamic_init( array(
                'id'                  => '10450',
                'slug'                => 'subaccounts-for-woocommerce',
                'type'                => 'plugin',
                'public_key'          => 'pk_5e73c22e9eb9062ca988afae26a46',
                'is_premium'          => false,
                'has_addons'          => true,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'subaccounts',
                    'account'        => true,
                    'support'        => false,
                    'parent'         => array(
                        'slug' => 'woocommerce',
                    ),
                ),
            ) );
        }

        return $sfwc_free;
    }

    // Init Freemius.
    sfwc_free();
    // Signal that SDK was initiated.
    do_action( 'sfwc_free_loaded' );
}




if ( ! defined( 'SFWC_CURRENT_VERSION' ) ) {
	define('SFWC_CURRENT_VERSION', '1.4.0');			// MAJOR.MINOR.PATCH
}

if ( ! defined( 'SFWC_REQUIRES_PERMALINK_UPDATE' ) ) {
	define('SFWC_REQUIRES_PERMALINK_UPDATE', 'yes');	// 'yes' | 'no'
}




/**
 * Declare plugin compatible with High-Performance Order Storage (HPOS) feature.
 *
 * @since 1.2.0
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );




/**
 * Check Plugin Requirements.
 *
 * Custom function to get list of active plugins. Unlike is_plugin_active WordPress function,
 * this one is available also on frontend.
 */
if ( ! function_exists( 'sfwc_is_plugin_active' ) ) {  // Check if function already exists from Pro plugin to avoid issues in case the Pro is activated first and then Free plugin.
	
	function sfwc_is_plugin_active( $plugin_name ) {

		$active_plugins = (array) get_option( 'active_plugins', array() );

		$plugin_filenames = array();

		foreach ( $active_plugins as $plugin ) {

			if ( false !== strpos( $plugin, '/' ) ) {

				// Normal plugin name (plugin-dir/plugin-filename.php).
				list( , $filename ) = explode( '/', $plugin );

			} else {

				// No directory, just plugin file.
				$filename = $plugin;
			}

			$plugin_filenames[] = $filename;
		}

		return in_array( $plugin_name, $plugin_filenames );
	}
}


/**
 * Check Plugin Requirements.
 *
 * Add admin notice in case WooCommerce is not active.
 */
if ( ! sfwc_is_plugin_active( 'woocommerce.php' ) ) {

	add_action('admin_notices', 'sfwc_child_plugin_notice_woocommerce_not_active');

	return;
}




/**
 * Check Plugin Requirements.
 *
 * Echo admin notice in case WooCommerce is not active.
 */
function sfwc_child_plugin_notice_woocommerce_not_active() {

    echo '<div class="error"><p>';

	printf( esc_html__( '%1$s must be installed and activated in order to use %2$s.', 'subaccounts-for-woocommerce' ), '<strong>WooCommerce</strong>', '<strong>Subaccounts for WooCommerce</strong>' );

    echo '</p></div>';
}






/**
 * Load plugin textdomain for translations.
 */
function sfwc_load_textdomain() {
    load_plugin_textdomain( 'subaccounts-for-woocommerce', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action('init', 'sfwc_load_textdomain');




/**
 * Load files.
 */
if ( is_admin() ) {
	
	if ( ! wp_doing_ajax() ) {
		
		// Admin area (non-ajax functions).
		require_once( plugin_dir_path( __FILE__ ) . 'admin/admin.php' );
		
	} else {
		
		// Ajax functions, both admin and public area.
		require_once( plugin_dir_path( __FILE__ ) . 'admin/ajax.php' );
	}
	
} else {
	
	// Public area.
	require_once( plugin_dir_path( __FILE__ ) . 'public/my-account.php' );

}

require_once( plugin_dir_path( __FILE__ ) . 'includes/functions.php' );