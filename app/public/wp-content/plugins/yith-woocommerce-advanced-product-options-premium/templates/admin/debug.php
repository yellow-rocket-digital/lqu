<?php
/**
 * Debug Template
 *
 * @author  Corrado Porzio <corradoporzio@gmail.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

global $wpdb;
$nonce = wp_create_nonce( 'wapo_action' );

$block_table_results        = array();
$addon_table_results        = array();
$blocks_table_exists        = false;
$addons_table_exists        = false;
$blocks_backup_table_exists = false;
$addons_backup_table_exists = false;

$blocks_table_name = $wpdb->prefix . 'yith_wapo_blocks';
$addons_table_name = $wpdb->prefix . 'yith_wapo_addons';

$blocks_backup_table_name = $wpdb->prefix . 'yith_wapo_blocks_backup';
$addons_backup_table_name = $wpdb->prefix . 'yith_wapo_addons_backup';

if ( $wpdb->get_var( "SHOW TABLES LIKE '$blocks_table_name'" ) === $blocks_table_name ) {
	$blocks_table_exists = true; // phpcs:ignore
}
if ( $wpdb->get_var( "SHOW TABLES LIKE '$addons_table_name'" ) === $addons_table_name ) {
	$addons_table_exists = true;
}

if ( false !== $blocks_table_exists && false !== $addons_table_exists ) {
	$block_table_results = $wpdb->get_results( "SELECT id from {$wpdb->prefix}yith_wapo_blocks WHERE id IS NOT NULL" );
	$addon_table_results = $wpdb->get_results( "SELECT id from {$wpdb->prefix}yith_wapo_addons WHERE id IS NOT NULL" );
}

if ( $wpdb->get_var( "SHOW TABLES LIKE '$blocks_backup_table_name'" ) === $blocks_backup_table_name ) {
	$blocks_backup_table_exists = true; // phpcs:ignore
}
if ( $wpdb->get_var( "SHOW TABLES LIKE '$addons_backup_table_name'" ) === $addons_backup_table_name ) {
	$addons_backup_table_exists = true; // phpcs:ignore
}

?>

<div id="plugin-fw-wc" class="yit-admin-panel-content-wrap yith-plugin-ui yith-wapo">
	<div id="yith-wapo-panel-debug" class="yith-plugin-fw yit-admin-panel-container">
		<div class="yith-plugin-fw-panel-custom-tab-container">

			<div class="list-table-title">
				<h2><?php echo 'Debug panel'; ?></h2>
			</div>

			<div class="fields">

				<!-- Option field -->
				<div class="field-wrap">
					<label for="option-characters-limit"><?php echo 'Create datatables'; ?>:</label>
					<div class="field">
						<?php


						if ( false !== $blocks_table_exists && false !== $addons_table_exists ) {
							echo '<span style="color: #94aa09;"><span class="dashicons dashicons-database-view"></span> '
								 . 'Tables created successfully.' . '</span><br>';
						} else {
							echo '<span style="color: #c92c2c;"><span class="dashicons dashicons-database-remove"></span> '
								 . 'Tables does not exists' . '</span><br>';
							?>
							<a href="admin.php?page=yith_wapo_panel&tab=debug&wapo_action=control_debug_options&option=create_tables&nonce=<?php echo esc_attr( $nonce ); ?>" class="yith-plugin-fw__button--primary">
								<span class="dashicons dashicons-database-add"></span> Create tables
							</a>
							<?php
						}
						?>
						<span class="description"><?php echo '<b>Create</b> the datatables yith_wapo_blocks and yith_wapo_addons'; ?></span>
					</div>
				</div>
				<!-- End option field -->

				<!-- Option field -->
				<div class="field-wrap">
					<label for="option-characters-limit"><?php echo 'Clear datatables'; ?>:</label>
					<div class="field">
						<?php


						if ( false !== $blocks_table_exists && false !== $addons_table_exists ) {
							echo '<span style="color: #94aa09;"><span class="dashicons dashicons-database-view"></span> '
								 . 'Tables exists.' . '</span><br>';
							?>
							<a href="admin.php?page=yith_wapo_panel&tab=debug&wapo_action=control_debug_options&option=clear_tables&nonce=<?php echo esc_attr( $nonce ); ?>" class="yith-plugin-fw__button--delete">
								Clear tables
							</a>
							<?php
						} else {
							echo '<span style="color: #c92c2c;"><span class="dashicons dashicons-database-remove"></span> '
								 . 'Tables does not exists. Create the tables before doing this action.' . '</span><br>';
						}
						?>
						<span class="description"><?php echo '<b>Clear</b> the datatables yith_wapo_blocks and yith_wapo_addons'; ?></span>
					</div>
				</div>
				<!-- End option field -->

				<!-- Option field -->
				<div class="field-wrap">
					<label for="option-characters-limit"><?php echo 'Restore addons from backup tables'; ?>:</label>
					<div class="field">
						<?php
						if ( count( $addon_table_results ) > 0 && count( $block_table_results ) > 0 ) {
							echo '<span style="color: #c92c2c;"><span class="dashicons dashicons-database-remove"></span> '
								 . 'Tables are not empty! Clear tables before doing this action' . '</span><br>';
						} else {
							if ( false !== $blocks_table_exists && false !== $addons_table_exists ) {
								if ( false !== $blocks_backup_table_exists && false !== $addons_backup_table_exists ) {
									echo '<span style="color: #94aa09;"><span class="dashicons dashicons-database-view"></span> '
										 . 'Backup tables exists and original tables are empty.' . '</span><br>';
									?>
									<a href="admin.php?page=yith_wapo_panel&tab=debug&wapo_action=control_debug_options&option=restore_addons&nonce=<?php echo esc_attr( $nonce ); ?>" class="yith-update-button">
										Restore addons
									</a>
									<?php
								} else {
									echo '<span style="color: #c92c2c;"><span class="dashicons dashicons-database-remove"></span> '
										 . 'Backup tables does not exists.' . '</span><br>';
								}
							} else {
								echo '<span style="color: #c92c2c;"><span class="dashicons dashicons-database-remove"></span> '
									 . 'Tables does not exists. Create the tables before doing this action.' . '</span><br>';
							}
						}

						?>
						<span class="description"><?php echo 'Copy all addons saved in the backup tables (yith_wapo_addons_backup and yith_wapo_blocks_backup)'; ?></span>
					</div>
				</div>
				<!-- End option field -->

				<div class="custom-field-addons" style="
						background: #85e6c1;
						font-size: 14px;
						font-weight: 700;
						padding: 10px;
						width: 50%;
					">
					<span>Executing both actions, the migration background process will be executed again ( Copy of all addons from v1 ).</span>
				</div>
				<div style="border: 3px solid #85e6c1;
				padding: 10px;
				padding-top: 20px;
				border-top: none;
				width: 49.78%;
				margin-bottom: 10px">

					<!-- Option field -->
					<div class="field-wrap">
						<label for="option-characters-limit"><span style="font-size: 15px"><b>STEP 1 - </b></span>Remove <b>imported</b> column from tables':</label>
						<div class="field">
							<a href="admin.php?page=yith_wapo_panel&tab=debug&wapo_action=control_debug_options&option=remove_column&nonce=<?php echo esc_attr( $nonce ); ?>" class="yith-plugin-fw__button--delete">
								Remove column
							</a>
							<span class="description">
								Remove "<b>imported</b>" column from <b>yith_wapo_groups</b> and <b>yith_wapo_types</b>
							</span>
						</div>
					</div>
					<!-- End option field -->

					<!-- Option field -->
					<div class="field-wrap">
						<label for="option-characters-limit"><span style="font-size: 15px"><b>STEP 2 - </b></span>Delete database options to test migration':</label>
						<div class="field">
							<a href="admin.php?page=yith_wapo_panel&tab=debug&wapo_action=control_debug_options&option=db_options&nonce=<?php echo esc_attr( $nonce ); ?>" class="yith-update-button">
								Delete database options
							</a>
							<span class="description">
							<?php
							echo wp_kses_post(
								'DB options:<br><b>yith_wapo_db_update_scheduled_for</b>
						<b>yith_wapo_db_version_option</b>
						'
							);
							?>
							</span>
						</div>
					</div>
					<!-- End option field -->

					<span><b>Important: The backup tables are created and all the addons are copied in the migration process. From the debug tab this process won't be executed.</b></span>
					<br><br>

					<a href="admin.php?page=wc-status&tab=action-scheduler&s=wapo&action=-1&paged=1&action2=-1" target="_blank" class="yith-plugin-fw__button--primary">
						Check action schedulers
					</a>
					<a href="admin.php?page=yith_wapo_panel&tab=debug&wapo_action=control_debug_options&option=remove_schedulers&nonce=<?php echo esc_attr( $nonce ); ?>" class="yith-plugin-fw__button--delete">
						Remove action schedulers
					</a>

					<div style="margin-top: 15px;">
						<span>Options in Database:</span>
						<div style="display:flex;">
							<span><b>yith_wapo_v2 -</b></span>
							<div><?php echo '> ' . ( ! empty( get_option( 'yith_wapo_v2' ) ) ? get_option( 'yith_wapo_v2' ) : 'Empty' ); ?></div>
							<div>&ensp;&ensp; ---> Empty or 'no' means that customer was in the old panel</div>
						</div>
						<div style="display:flex;">
							<span><b>yith_wapo_db_update_scheduled_for -</b></span>
							<div><?php echo '> ' . get_option( 'yith_wapo_db_update_scheduled_for' ); ?></div>
						</div>
						<div style="display:flex;">
							<span><b>yith_wapo_db_version_option -</b></span>
							<div><?php echo '> ' . get_option( 'yith_wapo_db_version_option' ); ?></div>
						</div>
						<div style="display:flex;">
							<span><b>yith_wapo_remove_del_column -</b></span>
							<div><?php echo '> ' . get_option( 'yith_wapo_remove_del_column' ); ?></div>
						</div>

					</div>
				</div>
		</div>
	</div>
</div>
