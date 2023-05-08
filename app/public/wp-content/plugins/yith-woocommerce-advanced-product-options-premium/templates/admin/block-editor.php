<?php
/**
 * Block Editor Template
 *
 * @author  Corrado Porzio <corradoporzio@gmail.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 *
 * @var $block_id
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

$block = new YITH_WAPO_Block( $block_id );
$nonce = wp_create_nonce( 'wapo_action' );

?>

<div id="plugin-fw-wc" class="yit-admin-panel-content-wrap yith-plugin-ui yith-wapo">
	<div id="yith-wapo-panel-block" class="yith-plugin-fw yit-admin-panel-container">
		<div class="yith-plugin-fw-panel-custom-tab-container">

			<a href="admin.php?page=yith_wapo_panel&tab=blocks">< <?php echo esc_html__( 'Back to blocks list', 'yith-woocommerce-product-add-ons' ); ?></a>
			<div class="list-table-title">
				<h2><?php echo is_numeric( $block_id ) ? esc_html__( 'Edit block', 'yith-woocommerce-product-add-ons' ) : esc_html__( 'Add new block', 'yith-woocommerce-product-add-ons' ); ?></h2>
			</div>

			<form action="admin.php?page=yith_wapo_panel&tab=blocks&block_id=<?php echo esc_attr( $block_id ); ?>" method="post" id="block">
				<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">

				<!-- Option field -->
				<div class="field-wrap">
					<label for="block-name"><?php echo esc_html__( 'Block name', 'yith-woocommerce-product-add-ons' ); ?></label>
					<div class="field">
						<input type="text" name="block_name" id="block-name" value="<?php echo esc_attr( $block->name ); ?>">
						<span class="description"><?php echo esc_html__( 'Enter a name to identify this block of options.', 'yith-woocommerce-product-add-ons' ); ?></span>
					</div>
				</div>
				<!-- End option field -->

				<!-- Option field -->
				<div class="field-wrap">
					<label for="block-priority"><?php echo esc_html__( 'Block priority level', 'yith-woocommerce-product-add-ons' ); ?></label>
					<div class="field">
						<input type="number" name="block_priority" id="block-priority" value="<?php echo esc_attr( round( $block->priority ) ); ?>" min="0" max="9999">
						<span class="description">
							<?php echo esc_html__( 'Set the priority level assigned to this rule. The priority level is important to arrange the different rules that apply to the same products. 1 has the highest priority level.', 'yith-woocommerce-product-add-ons' ); ?>
						</span>
					</div>
				</div>
				<!-- End option field -->

				<!-- BLOCK RULES -->
				<?php require YITH_WAPO_DIR . '/templates/admin/block-rules.php'; ?>

				<div id="addons-tabs">
					<a href="#addons-tabs" id="-addons" class="selected"><?php echo esc_html__( 'OPTIONS', 'yith-woocommerce-product-add-ons' ); ?></a>
				</div>

				<div id="addons-tab">
					<div id="block-addons">
						<div id="block-addons-container">
							<ul id="sortable-addons">
								<?php
								$addons       = yith_wapo_get_addons_by_block_id( $block_id );
								$total_addons = count( $addons );
								if ( $total_addons > 0 ) :
									$addons = apply_filters( 'yith_wapo_admin_addons', $addons );
									foreach ( $addons as $key => $addon ) :
										if ( yith_wapo_is_addon_type_available( $addon->type ) ) :
											$total_options = is_array( $addon->options ) && isset( array_values( $addon->options )[1] ) ? count( array_values( $addon->options )[1] ) : 0; // Count of labels.
											?>
											<li id="addon-<?php echo esc_attr( $addon->id ); ?>" data-id="<?php echo esc_attr( $addon->id ); ?>" data-priority="<?php echo esc_attr( ! empty( floatval( $addon->priority ) ) ? floatval( $addon->priority ) : floatval( $addon->id ) ); ?>">
												<span class="addon-icon <?php echo esc_attr( $addon->type ); ?>">
													<span class="wapo-icon wapo-icon-<?php echo esc_attr( $addon->type ); ?>"></span>
												</span>
												<span class="addon-name">
													<a href="admin.php?page=yith_wapo_panel&tab=blocks&block_id=<?php echo esc_attr( $block->id ); ?>&addon_id=<?php echo esc_attr( $addon->id ); ?>&addon_type=<?php echo esc_attr( $addon->type ); ?>">
														<?php
															echo esc_html( $addon->get_setting( 'title' ) ? $addon->get_setting( 'title' ) . ' - ' : '' );
															echo esc_html( ucwords( str_replace( 'html', 'HTML', str_replace( '_', ' ', $addon->type ) ) ) );

														if ( strpos( $addon->type, 'html' ) === false ) {
															echo ' (' . esc_html( $total_options ) . ' ';
															echo 1 === $total_options ? esc_html__( 'option', 'yith-woocommerce-product-add-ons' ) : esc_html__( 'options', 'yith-woocommerce-product-add-ons' );
															echo ')';
														}
														do_action( 'yith_wapo_admin_after_addon_title', $addon );
														?>
													</a>
												</span>
												<span class="addon-actions" style="display: none;">

													<?php
													$actions = array(
														'edit'   => array(
															'title' => __( 'Edit', 'yith-woocommerce-product-add-ons' ),
															'action' => 'edit',
															'url' => add_query_arg(
																array(
																	'page'       => 'yith_wapo_panel',
																	'tab'        => 'blocks',
																	'block_id'   => $block->id,
																	'addon_id'   => $addon->id,
																	'addon_type' => $addon->type,
																	'nonce'      => $nonce,
																),
																admin_url( 'admin.php' )
															),
														),
														'duplicate' => array(
															'title' => __( 'Duplicate', 'yith-woocommerce-product-add-ons' ),
															'action' => 'duplicate',
															'icon' => 'clone',
															'url'  => add_query_arg(
																array(
																	'page'       => 'yith_wapo_panel',
																	'tab'        => 'blocks',
																	'wapo_action' => 'duplicate-addon',
																	'block_id'   => $block->id,
																	'addon_id'   => $addon->id,
																	'nonce'      => $nonce,
																),
																admin_url( 'admin.php' )
															),
														),
														'delete' => array(
															'title' => __( 'Delete', 'yith-woocommerce-product-add-ons' ),
															'action' => 'delete',
															'icon' => 'trash',
															'url'  => add_query_arg(
																array(
																	'page'        => 'yith_wapo_panel',
																	'wapo_action' => 'remove-addon',
																	'block_id'    => $block->id,
																	'addon_id'    => $addon->id,
																	'nonce'       => $nonce,
																),
																admin_url( 'admin.php' )
															),
															'confirm_data' => array(
																'title'               => __( 'Confirm delete', 'yith-woocommerce-product-add-ons' ),
																'message'             => __( 'Are you sure you want to delete this add-on?', 'yith-woocommerce-product-add-ons' ),
																'confirm-button'      => _x( 'Yes, delete', 'Delete confirmation action', 'yith-woocommerce-product-add-ons' ),
																'confirm-button-type' => 'delete',
															),
														),
														'move'   => array(
															'title' => __( 'Move', 'yith-woocommerce-product-add-ons' ),
															'action' => 'move',
															'icon' => 'drag',
															'url'  => '#',
														),
													);

													yith_plugin_fw_get_action_buttons( $actions, true );
													?>
												</span>
												<span class="addon-onoff">
													<?php
														yith_plugin_fw_get_field(
															array(
																'id' => 'yith-wapo-active-addon-' . $addon->id,
																'type' => 'onoff',
																'value' => '1' === $addon->visibility ? 'yes' : 'no',
															),
															true
														);
													?>
												</span>
											</li>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
							<div id="add-option">
								<?php if ( ! $total_addons > 0 ) : ?>
									<p><?php echo esc_html__( 'Start to add your options to this block!', 'yith-woocommerce-product-add-ons' ); ?></p>
								<?php endif; ?>
								<input type="submit" name="add_options_after_save" value="<?php echo esc_html__( 'Add options', 'yith-woocommerce-product-add-ons' ); ?>" class="yith-add-button">

							</div>
						</div>
					</div>
				</div>

				<input type="hidden" name="wapo_action" value="save-block">
				<input type="hidden" name="id" value="<?php echo esc_attr( $block_id ); ?>">
				<!-- YITH WooCommerce Multi Vendor Integration -->
				<?php
				// manage_option capability prevent to assign rules to specific vendor if the admin create the rules.
				if ( function_exists( 'yith_get_vendor' ) && ! current_user_can( 'manage_options' ) ) {
					$vendor = yith_get_vendor( 'current', 'user' );
					if ( $vendor->is_valid() ) {
						$vendor_id = version_compare( YITH_WPV_VERSION, '4.0', '>=' ) ? $vendor->get_id() : $vendor->id;
						printf( '<input type="hidden" name="vendor_id" value="%1$s">', esc_attr( $vendor_id ) );
					}
				}
				?>
				<div id="save-button">
					<button class="yith-save-button"><?php echo esc_html__( 'Save', 'yith-woocommerce-product-add-ons' ); ?></button>
				</div>

			</form>

		</div>
	</div>

	<?php
	if ( isset( $_REQUEST['addon_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		include YITH_WAPO_DIR . '/templates/admin/addon-editor.php';
	}
	?>

</div>
