<?php
/**
 * Widget saving process.
 *
 * @package Ultimate_Dashboard_Pro
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

return function ( $post_id ) {

	$post_type = get_post_type( $post_id );

	if ( 'udb_widgets' !== $post_type ) {
		return;
	}

	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$is_valid_widget_roles_nonce   = isset( $_POST['udb_widget_roles_nonce'] ) && wp_verify_nonce( $_POST['udb_widget_roles_nonce'], 'udb_widget_roles' ) ? true : false;
	$is_valid_restrict_users_nonce = isset( $_POST['udb_restrict_users_nonce'] ) && wp_verify_nonce( $_POST['udb_restrict_users_nonce'], 'udb_restrict_users' ) ? true : false;

	if ( ! $is_valid_widget_roles_nonce || ! $is_valid_restrict_users_nonce ) {
		return;
	}

	// Video widget.
	if ( isset( $_POST['udb_video_thumbnail'] ) ) {
		update_post_meta( $post_id, 'udb_video_thumbnail', esc_url_raw( $_POST['udb_video_thumbnail'] ) );
	}

	if ( isset( $_POST['udb_video_platform'] ) ) {
		update_post_meta( $post_id, 'udb_video_platform', $_POST['udb_video_platform'] );
	}

	if ( isset( $_POST['udb_video_id'] ) ) {
		update_post_meta( $post_id, 'udb_video_id', esc_url_raw( $_POST['udb_video_id'] ) );
	}

	// Contact form widget.
	if ( isset( $_POST['udb_form_notes'] ) ) {
		update_post_meta( $post_id, 'udb_form_notes', sanitize_text_field( $_POST['udb_form_notes'] ) );
	}

	if ( isset( $_POST['udb_form_name'] ) ) {
		update_post_meta( $post_id, 'udb_form_name', sanitize_text_field( $_POST['udb_form_name'] ) );
	}

	if ( isset( $_POST['udb_form_email'] ) ) {
		update_post_meta( $post_id, 'udb_form_email', sanitize_text_field( $_POST['udb_form_email'] ) );
	}

	if ( isset( $_POST['udb_form_subject'] ) ) {
		update_post_meta( $post_id, 'udb_form_subject', sanitize_text_field( $_POST['udb_form_subject'] ) );
	}

	if ( isset( $_POST['udb_form_message'] ) ) {
		update_post_meta( $post_id, 'udb_form_message', sanitize_text_field( $_POST['udb_form_message'] ) );
	}

	$check = isset( $_POST['udb_form_subject_enable'] ) && $_POST['udb_form_subject_enable'] ? true : false;
	update_post_meta( $post_id, 'udb_form_subject_enable', $check );

	$check = isset( $_POST['udb_form_enable_logs'] ) && $_POST['udb_form_enable_logs'] ? true : false;
	update_post_meta( $post_id, 'udb_form_enable_logs', $check );

	if ( isset( $_POST['udb_form_success_message'] ) ) {
		update_post_meta( $post_id, 'udb_form_success_message', sanitize_text_field( $_POST['udb_form_success_message'] ) );
	}

	if ( isset( $_POST['udb_form_failed_message'] ) ) {
		update_post_meta( $post_id, 'udb_form_failed_message', sanitize_text_field( $_POST['udb_form_failed_message'] ) );
	}
	$check = isset( $_POST['udb_form_enable_autoresponder'] ) && $_POST['udb_form_enable_autoresponder'] ? true : false;
	update_post_meta( $post_id, 'udb_form_enable_autoresponder', $check );

	if ( isset( $_POST['udb_form_autoresponder'] ) ) {
		update_post_meta( $post_id, 'udb_form_autoresponder', sanitize_text_field( $_POST['udb_form_autoresponder'] ) );
	}

	$check = isset( $_POST['udb_form_enable_custom_to_address'] ) && $_POST['udb_form_enable_custom_to_address'] ? true : false;
	update_post_meta( $post_id, 'udb_form_enable_custom_to_address', $check );

	if ( isset( $_POST['udb_form_custom_to_address'] ) ) {
		update_post_meta( $post_id, 'udb_form_custom_to_address', sanitize_email( $_POST['udb_form_custom_to_address'] ) );
	}

	// Widget roles.
	if ( isset( $_POST['udb_widget_roles'] ) ) {
		$_POST['udb_widget_roles'] = empty( $_POST['udb_widget_roles'] ) ? array() : $_POST['udb_widget_roles'];

		foreach ( $_POST['udb_widget_roles'] as $index => $widget_role ) {
			$_POST['udb_widget_roles'][ $index ] = sanitize_text_field( $widget_role );
		}

		update_post_meta( $post_id, 'udb_widget_roles', $_POST['udb_widget_roles'] );
	}

	// Restrict users.
	if ( isset( $_POST['udb_restrict_users'] ) ) {
		$_POST['udb_restrict_users'] = empty( $_POST['udb_restrict_users'] ) ? array() : $_POST['udb_restrict_users'];

		foreach ( $_POST['udb_restrict_users'] as $index => $user_id ) {
			$_POST['udb_restrict_users'][ $index ] = 'all' === $user_id ? 'all' : absint( $user_id );
		}

		update_post_meta( $post_id, 'udb_restrict_users', $_POST['udb_restrict_users'] );
	}

};
