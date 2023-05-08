<?php
/**
 * Choose layout field.
 *
 * @package Ultimate_Dashboard_PRO
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

return function () {

	$branding = get_option( 'udb_branding' );
	$layout   = isset( $branding['layout'] ) ? $branding['layout'] : 'default';

	echo '<select name="udb_branding[layout]">';

	?>

	<option value="default" <?php selected( $layout, 'default' ); ?>>Default</option>

	<option value="modern" <?php selected( $layout, 'modern' ); ?>>Modern</option>

	<?php

	echo '</select>';

};
