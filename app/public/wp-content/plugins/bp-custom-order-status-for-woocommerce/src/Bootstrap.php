<?php

namespace Brightplugins_COS;

class Bootstrap {

	public function __construct() {

		new Cpt();
		new StatusColums();
		new Status();
		new Email();
		new Checkout();
		new Settings();
	}

	/**
	 * Check if WooCommerce is installed
	 *
	 * @since 1.2.7
	 * @access public
	 *
	 * @return bool
	 */
	public static function is_woocommerce_installed() {

		/**
		 * Checks if it is a multisite
		 */
		if ( is_multisite() ) {
			add_filter( 'active_plugins', function ( $active_plugins ) {

				$network = get_network();

				if ( !isset( $network->id ) ) {
					return $active_plugins;
				}
				$active_sitewide_plugins = get_network_option( $network->id, 'active_sitewide_plugins', null );

				if ( !empty( $active_sitewide_plugins ) ) {
					$network_active_plugins = array();

					foreach ( $active_sitewide_plugins as $key => $value ) {
						$network_active_plugins[] = $key;
					}

					$active_plugins = array_merge( $active_plugins, $network_active_plugins );
				}

				return $active_plugins;
			} );
		}

		$filter_active_plugins    = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		$is_woocommerce_installed = in_array( 'woocommerce/woocommerce.php', $filter_active_plugins, true );

		return $is_woocommerce_installed;
	}

	public function on_activation() {
		if ( !get_option( 'bp_custom_order_status_installed' ) ) {
			update_option( 'bp_custom_order_status_installed', date( "Y/m/d" ) );
		}
		do_action( 'bp_custom_order_status_on_activation' );
	}
	public function onDeactivation() {
		do_action( 'bp_custom_order_status_on_deactivation' );
	}

}
