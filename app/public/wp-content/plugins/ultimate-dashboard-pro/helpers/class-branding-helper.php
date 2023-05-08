<?php
/**
 * Branding helper.
 *
 * @package Ultimate_Dashboard_Pro
 */

namespace UdbPro\Helpers;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Class to setup branding helper.
 */
class Branding_Helper {

	/**
	 * Check whether or not branding option is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {

		$branding = get_option( 'udb_branding' );

		if ( isset( $branding['enabled'] ) ) {
			return true;
		}

		return false;

	}

}
