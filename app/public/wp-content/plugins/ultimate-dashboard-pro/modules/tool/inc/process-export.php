<?php
/**
 * Export processing.
 *
 * @package Ultimate_Dashboard
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

return function ( $module ) {

	$selected_modules = isset( $_POST['udb_export_modules'] ) && is_array( $_POST['udb_export_modules'] ) ? $_POST['udb_export_modules'] : array();

	$export_data = [];

	if ( in_array( 'admin_menu', $selected_modules, true ) ) {
		$admin_menu = get_option( 'udb_admin_menu', array() );

		if ( ! empty( $admin_menu ) ) {
			$admin_menu = $module->replace_admin_menu_urls( $admin_menu, site_url(), '{udb_site_url}' );

			$export_data['admin_menu'] = $admin_menu;
		}
	}

	if ( in_array( 'admin_bar', $selected_modules, true ) ) {
		$admin_bar = get_option( 'udb_admin_bar', array() );

		if ( ! empty( $admin_bar ) ) {
			$admin_bar = $module->replace_admin_bar_urls( $admin_bar, site_url(), '{udb_site_url}' );

			$export_data['admin_bar'] = $admin_bar;
		}
	}

	if ( in_array( 'multisite', $selected_modules, true ) ) {
		$multisite_settings = array(
			'udb_multisite_blueprint'    => 0,
			'udb_multisite_exclude'      => '',
			'udb_multisite_widget_order' => 0,
			'udb_multisite_capability'   => 'manage_network',
		);

		foreach ( $multisite_settings as $key => $value ) {
			$multisite_settings[ $key ] = get_site_option( $key, $value );
		}

		$export_data['multisite_settings'] = $multisite_settings;
	}

	return $export_data;

};
