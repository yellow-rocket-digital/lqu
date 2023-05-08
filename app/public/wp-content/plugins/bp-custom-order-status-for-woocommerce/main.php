<?php
/**
 * Plugin Name: Custom Order Status Manager for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/bp-custom-order-status-for-woocommerce/
 * Description: This plugin allows you to create, delete and edit order statuses to better control the flow of your orders.
 * Version: 0.12
 * Author: Bright Plugins
 * Requires PHP: 7.2.0
 * Requires at least: 4.9
 * Tested up to: 6.1.1
 * WC tested up to: 7.4
 * WC requires at least: 4.0
 * Author URI: https://brightplugins.com
 * Text Domain: bp-custom-order-status
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/include/functions.php';

// Define Values.
define( 'BVOS_PLUGIN_DIR', __DIR__ );
define( 'BVOS_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );
define( 'BVOS_PLUGIN_FILE', __FILE__ );
define( 'BVOS_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'BVOS_PLUGIN_VER', '0.12' );


use Brightplugins_COS\Bootstrap;

final class Bright_Plugins_COSW {

    static $instance = null;

    private function __construct() {

        $this->init_plugin();
    }

    /**
     * Initializes a singleton instance
     *
     * @since 1.2.7
     * @access public
     * @static
     *
     * @return $instance
     */
    public static function init() {

        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the plugin
     *
     * @since 1.2.7
     * @access public
     *
     * @return void
     */
    public function init_plugin() {

		// Check if WooCommerce is active
		if ( Bootstrap::is_woocommerce_installed() ) {
            add_action( 'before_woocommerce_init', function() {
                if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
                }
            } );
            
			add_action( 'woocommerce_loaded', function () {

				$bootstrap = new Bootstrap();
				register_activation_hook( __FILE__, array( $bootstrap, 'on_activation' ) );
				register_deactivation_hook( __FILE__, [$bootstrap, 'onDeactivation'] );

			} );
		} else {
			add_action( 'admin_notices', function () {
				$class   = 'notice notice-error';
				$message = __( 'Oops! looks like WooCommerce is disabled. Please, enable it in order to use BP Custom Order Status for WooCommerce.', 'bp-custom-order-status' );
				printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
			} );
		}

    }

}

/**
 * Initializes the main plugin
 */
function Bright_Plugins_COSW_start() {
    return Bright_Plugins_COSW::init();
}

// kick-off the plugin
Bright_Plugins_COSW_start();

/**
 * strtolower for status slug
 *
 */
if( !function_exists( 'bpos_cb_strtolower_status_slug' ) ) {

	function bpos_cb_strtolower_status_slug() {

		$status_slug = wc_strtolower( get_post_meta( get_the_ID(), 'status_slug', true ) );

		update_post_meta( get_the_ID(), 'status_slug', $status_slug );
		echo '<style>.post-type-order_status #edit-slug-box{display:none}</style>';
	}
}
