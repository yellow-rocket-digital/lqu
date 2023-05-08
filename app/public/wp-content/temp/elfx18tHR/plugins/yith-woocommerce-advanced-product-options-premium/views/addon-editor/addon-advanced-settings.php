<?php
/**
 * Addon Advanced Options Template
 *
 * @author  Corrado Porzio <corradoporzio@gmail.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 *
 * @var YITH_WAPO_Addon $addon
 * @var int $addon_id
 * @var string $addon_type
 * @var YITH_WAPO_Block $block
 * @var int $block_id
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

$selection_type         = $addon->get_setting( 'selection_type', 'single', false );
$first_options_selected = $addon->get_setting( 'first_options_selected', 'no', false );
$first_free_options     = $addon->get_setting( 'first_free_options', 0, false );
$enable_min_max         = $addon->get_setting( 'enable_min_max', 'no', false );
$min_max_rule           = (array) $addon->get_setting( 'min_max_rule', 'min', false );
$min_max_value          = (array) $addon->get_setting( 'min_max_value', 0, false );
$sell_individually      = $addon->get_setting( 'sell_individually', 'no', false );
$required               = $addon->get_setting( 'required', 'no', false );

$addon_type = isset( $_GET['addon_type'] ) ? $_GET['addon_type'] : $addon->type; //phpcs:ignore

?>

<div id="tab-advanced-settings" style="display: none;">
	<?php
	$selection_type_not_allowed_on = array(
		'radio',
		'select',
	);

	if ( ! in_array( $addon_type, $selection_type_not_allowed_on, true ) ) {
		?>
	<!-- Option field -->
	<div class="field-wrap">
		<label for="addon-selection-type"><?php echo esc_html__( 'Selection type', 'yith-woocommerce-product-add-ons' ); ?></label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'      => 'addon-selection-type',
					'name'    => 'addon_selection_type',
					'type'    => 'radio',
					'value'   => $selection_type,
					'options' => array(
						'single'   => __( 'Single - User can select only ONE of the options available', 'yith-woocommerce-product-add-ons' ),
						'multiple' => __( 'Multiple - User can select MULTIPLE options', 'yith-woocommerce-product-add-ons' ),
					),
				),
				true
			);
			?>
			<span class="description">
				<?php echo esc_html__( 'Choose to show these options in all products or only specific products or categories.', 'yith-woocommerce-product-add-ons' ); ?>
			</span>
		</div>
	</div>
	<!-- End option field -->
		<?php
	}
	?>

	<?php if ( defined( 'YITH_WAPO_PREMIUM' ) && YITH_WAPO_PREMIUM ) : ?>
		<?php
		if ( ! in_array( $addon_type, $selection_type_not_allowed_on, true ) ) {
			?>
		<!-- Option field -->
		<div class="field-wrap">
			<label for="addon-first-options-selected"><?php echo esc_html__( 'Set the first selected options as free', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'    => 'addon-first-options-selected',
						'name'  => 'addon_first_options_selected',
						'class' => 'enabler',
						'type'  => 'onoff',
						'value' => $first_options_selected,
					),
					true
				);
				?>
				<span class="description">
					<?php echo esc_html__( 'Enable to set a specific number of options as free.', 'yith-woocommerce-product-add-ons' ); ?><br />
					<?php echo esc_html__( 'For example, the first three "pizza toppings" are free and included in the product price.', 'yith-woocommerce-product-add-ons' ); ?><br />
					<?php echo esc_html__( 'The user will pay for the fourth topping.', 'yith-woocommerce-product-add-ons' ); ?>
				</span>
			</div>
		</div>
		<!-- End option field -->

	<?php } ?>

		<!-- Option field -->
		<div class="field-wrap enabled-by-addon-first-options-selected" style="display: none;">
			<label for="addon-first-free-options"><?php echo esc_html__( 'How many options the user can select for free', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'    => 'addon-first-free-options',
						'name'  => 'addon_first_free_options',
						'type'  => 'number',
						'value' => $first_free_options,
					),
					true
				);
				?>
				<span class="description">
					<?php echo esc_html__( 'Set how many options the user can select for free.', 'yith-woocommerce-product-add-ons' ); ?>
				</span>
			</div>
		</div>
		<!-- End option field -->

	<?php endif; ?>

	<?php if ( defined( 'YITH_WAPO_PREMIUM' ) && YITH_WAPO_PREMIUM ) : ?>
		<?php
		if ( ! in_array( $addon_type, $selection_type_not_allowed_on, true ) ) {
			?>
		<!-- Option field -->
		<div class="field-wrap">
			<label for="addon-enable-min-max"><?php echo esc_html__( 'Enable min/max selection rules', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'    => 'addon-enable-min-max',
						'name'  => 'addon_enable_min_max',
						'class' => 'enabler',
						'type'  => 'onoff',
						'value' => $enable_min_max,
					),
					true
				);
				?>
				<span class="description">
					<?php echo esc_html__( 'Enable if the user has to select a minimum, maximum, or the exact number of options to proceed with the purchase.', 'yith-woocommerce-product-add-ons' ); ?>
				</span>
			</div>
		</div>
		<!-- End option field -->
		<?php } ?>

		<!-- Option field -->
		<div class="field-wrap enabled-by-addon-enable-min-max" style="display: none;">
			<label for="min-max-rules"><?php echo esc_html__( 'To proceed to buy, the user has to select:', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div id="min-max-rules">
				<?php

				$min_max_count = count( $min_max_rule );
				for ( $y = 0; $y < $min_max_count; $y++ ) :

					$min_max_options = array(
						'min' => __( 'A minimum of', 'yith-woocommerce-product-add-ons' ),
						'max' => __( 'A maximum of', 'yith-woocommerce-product-add-ons' ),
						'exa' => __( 'Exactly', 'yith-woocommerce-product-add-ons' ),
					);

					if ( $y > 0 && ( 'min' === $min_max_rule[ $y ] || 'max' === $min_max_rule[ $y ] ) ) {
						$min_max_options = array(
							$min_max_rule[ $y ] => $min_max_options[ $min_max_rule[ $y ] ],
						);
					}
					?>
					<div class="field rule min-max-rule">
						<?php
						yith_plugin_fw_get_field(
							array(
								'id'      => 'addon-min-max-rule',
								'name'    => 'addon_min_max_rule[]',
								'type'    => 'select',
								'class'   => 'wc-enhanced-select',
								'value'   => $min_max_rule[ $y ],
								'options' => $min_max_options,
							),
							true
						);
						yith_plugin_fw_get_field(
							array(
								'id'    => 'addon-min-max-value',
								'name'  => 'addon_min_max_value[]',
								'type'  => 'number',
								'min'   => '0',
								'value' => $min_max_value[ $y ],
							),
							true
						);
						?>
						<span class="description">
							<?php echo esc_html__( 'option(s)', 'yith-woocommerce-product-add-ons' ); ?>
						</span>
						<img src="<?php echo esc_attr( YITH_WAPO_URL ); ?>/assets/img/delete.png" class="delete-min-max-rule" alt="">
					</div>
				<?php endfor; ?>
			</div>
			<div id="add-min-max-rule"><a href="#">+ <?php echo esc_html__( 'Add rule', 'yith-woocommerce-product-add-ons' ); ?></a></div>
		</div>
		<!-- End option field -->

		<!-- End option field -->
		<?php
		if ( 'select' === $addon_type ) {
			?>
			<!-- Option field -->
			<div class="field-wrap">
				<label for="addon-required"><?php echo esc_html__( 'Force user to select an option', 'yith-woocommerce-product-add-ons' ); ?>:</label>
				<div class="field">
					<?php

					yith_plugin_fw_get_field(
						array(
							'id'      => 'addon-required',
							'name'    => 'addon_required',
							'class'   => 'yith-wapo-required-select',
							'default' => 'no',
							'type'    => 'onoff',
							'value'   => $required,
						),
						true
					);
					?>
					<span class="description">
						<?php echo esc_html__( 'Enable to force the user to select an option of the select to proceed with the purchase.', 'yith-woocommerce-product-add-ons' ); ?>
					</span>
				</div>
			</div>
			<!-- End option field -->
			<?php
		}
		?>

		<!-- Option field -->
		<div class="field-wrap">
			<label for="addon-sell-options-individually"><?php echo esc_html__( 'Sell options individually', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'    => 'addon-sell-individually',
						'name'  => 'addon_sell_individually',
						'class' => 'enabler',
						'type'  => 'onoff',
						'value' => $sell_individually,
					),
					true
				);
				?>
				<span class="description">
					<?php echo esc_html__( 'Enable to sell options individually. The options are added to the cart in a separate row and their prices are not affected by the quantity of products purchased.', 'yith-woocommerce-product-add-ons' ); ?>
				</span>
			</div>
		</div>
		<!-- End option field -->

		<style>
			#min-max-rules .rule { position: relative; }
			#min-max-rules .rule .delete-min-max-rule { width: 8px; height: 10px; padding: 12px; cursor: pointer; position: absolute; top: 5px; left: 280px; }
			#min-max-rules .rule .delete-min-max-rule:hover { opacity: 0.5; }
		</style>

	<?php endif; ?>

</div>
