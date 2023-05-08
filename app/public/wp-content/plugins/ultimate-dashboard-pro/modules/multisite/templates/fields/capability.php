<?php
/**
 * Capability field.
 *
 * @package Ultimate_Dashboard_Pro
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

return function () {

	$udb_capability = get_site_option( 'udb_multisite_capability' );

	$capabilities = array(
		'manage_network' => 'Super Admin',
		'manage_options' => 'Administrators',
	);

	echo '<select name="udb_multisite_capability">';

	foreach ( $capabilities as $capability => $value ) {
		?>
		<option value="<?php echo esc_attr( $capability ); ?>" <?php selected( $capability, $udb_capability ); ?>><?php echo esc_html( $value ); ?></option>
		<?php
	}

	echo '</select>';

	?>

	<p>
		<?php _e( 'Users with the given User Role (and above) can access the Ultimate Dashboard settings & create widgets on sites on network.', 'ultimatedashboard' ); ?>
	</p>

	<?php

};
