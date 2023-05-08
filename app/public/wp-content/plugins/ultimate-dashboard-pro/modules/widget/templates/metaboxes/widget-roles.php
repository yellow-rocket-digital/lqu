<?php
/**
 * Position metabox.
 *
 * @package Ultimate_Dashboard_PRO
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

use UdbPro\Helpers\Multisite_Helper;

return function ( $post ) {

	wp_nonce_field( 'udb_widget_roles', 'udb_widget_roles_nonce' );

	$widget_roles = get_post_meta( $post->ID, 'udb_widget_roles', true );
	$widget_roles = empty( $widget_roles ) ? array( 'all' ) : $widget_roles;
	$widget_roles = is_serialized( $widget_roles ) ? unserialize( $widget_roles ) : $widget_roles;

	$roles_obj = new \WP_Roles();
	$roles     = $roles_obj->role_names;

	$ms_helper = new Multisite_Helper();
	?>

	<p>
		<?php _e( 'Show Widget to:', 'ultimatedashboard' ); ?>
	</p>
	<select class="udb-widget-roles-field" name="udb_widget_roles[]" multiple>

		<option value="all"  <?php echo esc_attr( in_array( 'all', $widget_roles, true ) ? 'selected' : '' ); ?>>
			<?php _e( 'All', 'ultimatedashboard' ); ?>
		</option>

		<?php if ( $ms_helper->multisite_supported() ) : ?>
			<option value="super_admin"  <?php echo esc_attr( in_array( 'super_admin', $widget_roles, true ) ? 'selected' : '' ); ?>>
				<?php _e( 'Super Admin', 'ultimatedashboard' ); ?>
			</option>
		<?php endif; ?>

		<?php foreach ( $roles as $role_key => $role_name ) : ?>
			<?php
			$selected_attr = '';
			$selected_attr = in_array( $role_key, $widget_roles, true ) ? 'selected' : '';
			?>
			<option value="<?php echo esc_attr( $role_key ); ?>" <?php echo esc_attr( $selected_attr ); ?>>
				<?php echo esc_attr( $role_name ); ?>
			</option>
		<?php endforeach; ?>

	</select>

	<?php

};
