<?php
/**
 * Addon Display Options Template
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

$show_image             = $addon->get_setting( 'show_image', 'no', false );
$image                  = $addon->get_setting( 'image', '' );
$image_replacement      = $addon->get_setting( 'image_replacement', 'no', false );
$images_position        = $addon->get_setting( 'options_images_position', 'above', false );
$show_as_toggle         = $addon->get_setting( 'show_as_toggle', 'no', false );
$hide_product_prices    = $addon->get_setting( 'hide_products_prices', 'no', false );
$show_sku               = $addon->get_setting( 'show_sku', 'no', false );
$show_stock             = $addon->get_setting( 'show_stock', 'no', false );
$show_add_to_cart       = $addon->get_setting( 'show_add_to_cart', 'no', false );
$show_quantity          = $addon->get_setting( 'show_quantity', 'no', false );
$hide_options_images    = $addon->get_setting( 'hide_options_images', 'no', false );
$hide_options_label     = $addon->get_setting( 'hide_options_label', 'no', false );
$hide_options_prices    = $addon->get_setting( 'hide_options_prices', 'no', false );
$options_per_row        = $addon->get_setting( 'options_per_row', 1 );
$show_in_a_grid         = $addon->get_setting( 'show_in_a_grid', 'no', false );
$options_width          = $addon->get_setting( 'options_width', 100 );
$show_quantity_selector = $addon->get_setting( 'show_quantity_selector', 'no', false );

?>

<div id="tab-display-settings" style="display: none;">

	<!-- Option field -->
	<div class="field-wrap">
		<label for="addon-show-image"><?php echo esc_html__( 'Show an image for this add-on', 'yith-woocommerce-product-add-ons' ); ?></label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'    => 'addon-show-image',
					'class' => 'enabler',
					'name'  => 'addon_show_image',
					'type'  => 'onoff',
					'value' => $show_image,
				),
				true
			);
			?>
			<span class="description"><?php echo esc_html__( 'Enable to show an additional image or icon near the title.', 'yith-woocommerce-product-add-ons' ); ?></span>
		</div>
	</div>
	<!-- End option field -->

	<!-- Option field -->
	<div class="field-wrap enabled-by-addon-show-image" style="display: none;">
		<label for="addon-image"><?php echo esc_html__( 'Add-on image', 'yith-woocommerce-product-add-ons' ); ?></label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'    => 'addon-image',
					'name'  => 'addon_image',
					'type'  => 'upload',
					'value' => $image,
				),
				true
			);
			?>
		</div>
	</div>
	<!-- End option field -->

	<?php if ( 'product' !== $addon_type ) : ?>

		<!-- Option field -->
		<div class="field-wrap">
			<label><?php echo esc_html__( 'Product image replacing options', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'      => 'addon-image-replacement',
						'name'    => 'addon_image_replacement',
						'type'    => 'select',
						'class'   => 'wc-enhanced-select',
						'value'   => $image_replacement,
						'options' => array(
							'no'      => __( 'Don\'t replace the image', 'yith-woocommerce-product-add-ons' ),
							'addon'   => __( 'Replace with add-on image', 'yith-woocommerce-product-add-ons' ),
							'options' => __( 'Replace with options images', 'yith-woocommerce-product-add-ons' ),
						),
					),
					true
				);
				?>
				<span class="description"><?php echo esc_html__( 'Choose to replace the default product image when an option is selected and which image to use to replace it.', 'yith-woocommerce-product-add-ons' ); ?></span>
			</div>
		</div>
		<!-- End option field -->

		<?php if ( 'label' !== $addon_type ) : ?>
			<!-- Option field -->
			<div class="field-wrap">
				<label><?php echo esc_html__( 'Options images position', 'yith-woocommerce-product-add-ons' ); ?></label>
				<div class="field">
					<?php
					yith_plugin_fw_get_field(
						array(
							'id'      => 'addon-options-images-position',
							'name'    => 'addon_options_images_position',
							'type'    => 'select',
							'class'   => 'wc-enhanced-select',
							'value'   => $images_position,
							'options' => array(
								'above' => __( 'Above label', 'yith-woocommerce-product-add-ons' ),
								'under' => __( 'Under label', 'yith-woocommerce-product-add-ons' ),
								'left'  => __( 'Left side', 'yith-woocommerce-product-add-ons' ),
								'right' => __( 'Right side', 'yith-woocommerce-product-add-ons' ),
							),
						),
						true
					);
					?>
					<span class="description"><?php echo esc_html__( 'Choose the position of the options images.', 'yith-woocommerce-product-add-ons' ); ?></span>
				</div>
			</div>
			<!-- End option field -->
		<?php endif; ?>

	<?php endif; ?>

	<!-- Option field -->
	<div class="field-wrap">
		<label for="addon-show-as-toggle"><?php echo esc_html__( 'Show as toggle', 'yith-woocommerce-product-add-ons' ); ?></label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'      => 'addon-show-as-toggle',
					'name'    => 'addon_show_as_toggle',
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'value'   => $show_as_toggle,
					'options' => array(
						'no'     => __( 'Default', 'yith-woocommerce-product-add-ons' ),
						'open'   => __( 'Yes, with toggle opened by default', 'yith-woocommerce-product-add-ons' ),
						'closed' => __( 'Yes, with toggle closed by default', 'yith-woocommerce-product-add-ons' ),
					),
				),
				true
			);
			?>
			<span class="description"><?php echo esc_html__( 'Choose whether to show options in a toggle section.', 'yith-woocommerce-product-add-ons' ); ?></span>
		</div>
	</div>
	<!-- End option field -->

	<?php if ( 'product' === $addon_type ) : ?>

		<!-- Option field -->
		<div class="field-wrap">
			<label for="addon-hide-products-prices"><?php echo esc_html__( 'Hide product prices', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'    => 'addon-hide-products-prices',
						'name'  => 'addon_hide_products_prices',
						'type'  => 'onoff',
						'value' => $hide_product_prices,
					),
					true
				);
				?>
				<span class="description"><?php echo esc_html__( 'Enable if you want to hide the product prices.', 'yith-woocommerce-product-add-ons' ); ?></span>
			</div>
		</div>
		<!-- End option field -->

		<div class="field-wrap">
			<label for="addon-show-sku"><?php echo esc_html__( 'Show SKU label', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'    => 'addon-show-sku',
						'name'  => 'addon_show_sku',
						'type'  => 'onoff',
						'value' => $show_sku,
					),
					true
				);
				?>
				<span class="description"><?php echo esc_html__( 'Enable if you want to show the sku label.', 'yith-woocommerce-product-add-ons' ); ?></span>
			</div>
		</div>
		<!-- End option field -->

		<div class="field-wrap">
			<label for="addon-show-stock"><?php echo esc_html__( 'Show stock label', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'    => 'addon-show-stock',
						'name'  => 'addon_show_stock',
						'type'  => 'onoff',
						'value' => $show_stock,
					),
					true
				);
				?>
				<span class="description"><?php echo esc_html__( 'Enable if you want to show the stock label.', 'yith-woocommerce-product-add-ons' ); ?></span>
			</div>
		</div>
		<!-- End option field -->

		<div class="field-wrap">
			<label for="addon-show-add-to-cart"><?php echo esc_html__( 'Show "Add to cart" button', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'    => 'addon-show-add-to-cart',
						'class' => 'enabler',
						'name'  => 'addon_show_add_to_cart',
						'type'  => 'onoff',
						'value' => $show_add_to_cart,
					),
					true
				);
				?>
				<span class="description"><?php echo esc_html__( 'Enable if you want to show the "Add to cart" button.', 'yith-woocommerce-product-add-ons' ); ?></span>
			</div>
		</div>
		<!-- End option field -->

		<div class="field-wrap">
			<label for="addon-show-quantity"><?php echo esc_html__( 'Show quantity selector', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'    => 'addon-show-quantity',
						'name'  => 'addon_show_quantity',
						'type'  => 'onoff',
						'value' => $show_quantity,
					),
					true
				);
				?>
				<span class="description"><?php echo esc_html__( 'Enable if you want to show the quantity selector.', 'yith-woocommerce-product-add-ons' ); ?></span>
			</div>
		</div>
		<!-- End option field -->

	<?php else : ?>

		<!-- Option field -->
		<div class="field-wrap">
			<label for="addon-hide-options-images"><?php echo esc_html__( 'Hide options images', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'    => 'addon-hide-options-images',
						'name'  => 'addon_hide_options_images',
						'type'  => 'onoff',
						'value' => $hide_options_images,
					),
					true
				);
				?>
				<span class="description"><?php echo esc_html__( 'Enable if you want to hide the options images.', 'yith-woocommerce-product-add-ons' ); ?></span>
			</div>
		</div>
		<!-- End option field -->

		<!-- Option field -->
		<div class="field-wrap">
			<label for="addon-hide-options-label"><?php echo esc_html__( 'Hide options labels', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'    => 'addon-hide-options-label',
						'name'  => 'addon_hide_options_label',
						'type'  => 'onoff',
						'value' => $hide_options_label,
					),
					true
				);
				?>
				<span class="description"><?php echo esc_html__( 'Enable if you want to hide the options labels.', 'yith-woocommerce-product-add-ons' ); ?></span>
			</div>
		</div>
		<!-- End option field -->

		<!-- Option field -->
		<div class="field-wrap">
			<label for="addon-hide-options-prices"><?php echo esc_html__( 'Hide options prices', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'    => 'addon-hide-options-prices',
						'name'  => 'addon_hide_options_prices',
						'type'  => 'onoff',
						'value' => $hide_options_prices,
					),
					true
				);
				?>
				<span class="description"><?php echo esc_html__( 'Enable if you want to hide the options prices.', 'yith-woocommerce-product-add-ons' ); ?></span>
			</div>
		</div>
		<!-- End option field -->

	<?php endif; ?>

	<h3><?php echo esc_html__( 'Options layout', 'yith-woocommerce-product-add-ons' ); ?></h3>

	<!-- Option field -->
	<div class="field-wrap">
		<label for="addon-options-per-row"><?php echo esc_html__( 'Options per row', 'yith-woocommerce-product-add-ons' ); ?></label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'    => 'addon-options-per-row',
					'name'  => 'addon_options_per_row',
					'type'  => 'slider',
					'min'   => 1,
					'max'   => 10,
					'step'  => 1,
					'value' => $options_per_row,
				),
				true
			);
			?>
			<span class="description">
				<?php echo esc_html__( 'Enter how many options to display for each row.', 'yith-woocommerce-product-add-ons' ); ?>
			</span>
		</div>
	</div>
	<!-- End option field -->

	<!-- Option field -->
	<div class="field-wrap">
		<label for="addon-show-in-a-grid"><?php echo esc_html__( 'Use a grid layout', 'yith-woocommerce-product-add-ons' ); ?></label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'    => 'addon-show-in-a-grid',
					'class' => 'enabler',
					'name'  => 'addon_show_in_a_grid',
					'type'  => 'onoff',
					'value' => $show_in_a_grid,
				),
				true
			);
			?>
			<span class="description"><?php echo esc_html__( 'Enable to adjust the options in a grid based on the page width.', 'yith-woocommerce-product-add-ons' ); ?></span>
		</div>
	</div>
	<!-- End option field -->

	<!-- Option field -->
	<div class="field-wrap enabled-by-addon-show-in-a-grid" style="display: none;">
		<label for="addon-options-width"><?php echo esc_html__( 'Options width', 'yith-woocommerce-product-add-ons' ); ?> (%)</label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'    => 'addon-options-width',
					'name'  => 'addon_options_width',
					'type'  => 'slider',
					'min'   => 1,
					'max'   => 100,
					'step'  => 1,
					'value' => $options_width,
				),
				true
			);
			?>
			<span class="description">
				<?php echo esc_html__( 'Select the options field size.', 'yith-woocommerce-product-add-ons' ); ?>
			</span>
		</div>
	</div>
	<!-- End option field -->

	<!-- Option field -->
	<!--
	<div class="field-wrap">
		<label for="addon-show-quantity-selector"><?php echo esc_html__( 'Show quantity selector', 'yith-woocommerce-product-add-ons' ); ?></label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'    => 'addon-show-quantity-selector',
					'name'  => 'addon_show_quantity_selector',
					'type'  => 'onoff',
					'value' => $show_quantity_selector,
				),
				true
			);
			?>
			<span class="description"><?php echo esc_html__( 'Enable if you want to show a quantity selector for this option', 'yith-woocommerce-product-add-ons' ); ?></span>
		</div>
	</div>
	-->
	<!-- End option field -->

</div>
