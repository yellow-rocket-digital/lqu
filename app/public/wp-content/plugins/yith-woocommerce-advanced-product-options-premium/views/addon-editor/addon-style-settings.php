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


$custom_style            = $addon->get_setting( 'custom_style', 'yes', false );
$options_images_position = $addon->get_setting( 'options_images_position', 'above', false );
$label_content_align     = $addon->get_setting( 'label_content_align', 'left', false );
$image_equal_height      = $addon->get_setting( 'image_equal_height', 'no', false );
$images_height           = $addon->get_setting( 'images_height', 100 );
$label_position          = $addon->get_setting( 'label_position', 'inside', false );
$description_position    = $addon->get_setting( 'description_position', 'outside', false );
$label_padding_array     = $addon->get_setting(
	'label_padding',
	array(
		'dimensions' => array(
			'top'    => 10,
			'right'  => 10,
			'bottom' => 10,
			'left'   => 10,
		),
	)
);
?>

<div id="tab-style-settings" style="display: none;">

	<!-- Option field -->
	<div class="field-wrap">
		<label for="addon-custom-style"><?php echo esc_html__( 'Override default style for this set of labels', 'yith-woocommerce-product-add-ons' ); ?></label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'    => 'addon-custom-style',
					'class' => 'enabler',
					'name'  => 'addon_custom_style',
					'type'  => 'onoff',
					'value' => $custom_style,
				),
				true
			);
			?>
		</div>
	</div>
	<!-- End option field -->

	<!-- Option field -->
	<div class="field-wrap enabled-by-addon-custom-style" style="display: none;">
		<label><?php echo esc_html__( 'Options images position', 'yith-woocommerce-product-add-ons' ); ?></label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'      => 'addon-options-images-position',
					'name'    => 'addon_options_images_position',
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'value'   => $options_images_position,
					'options' => array(
						'above' => __( 'Above label', 'yith-woocommerce-product-add-ons' ),
						'under' => __( 'Under label', 'yith-woocommerce-product-add-ons' ),
						'left'  => __( 'Left side', 'yith-woocommerce-product-add-ons' ),
						'right' => __( 'Right side', 'yith-woocommerce-product-add-ons' ),
					),
					'default' => 'above',
				),
				true
			);
			?>
		</div>
	</div>
	<!-- End option field -->

	<!-- Option field -->
	<div class="field-wrap enabled-by-addon-custom-style" style="display: none;">
		<label><?php echo esc_html__( 'Content alignment', 'yith-woocommerce-product-add-ons' ); ?></label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'      => 'addon-label-content-align',
					'name'    => 'addon_label_content_align',
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'value'   => $label_content_align,
					'options' => array(
						'left'   => __( 'Left', 'yith-woocommerce-product-add-ons' ),
						'center' => __( 'Center', 'yith-woocommerce-product-add-ons' ),
						'right'  => __( 'Right', 'yith-woocommerce-product-add-ons' ),
					),
				),
				true
			);
			?>
		</div>
	</div>
	<!-- End option field -->

	<!-- Option field -->
	<div class="field-wrap addon-image-equal-height enabled-by-addon-custom-style" style="display: none;">
		<label for="addon-image-equal-height"><?php echo esc_html__( 'Force image equal heights', 'yith-woocommerce-product-add-ons' ); ?></label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'    => 'addon-image-equal-height',
					'class' => 'enabler',
					'name'  => 'addon_image_equal_height',
					'type'  => 'onoff',
					'value' => $image_equal_height,
				),
				true
			);
			?>
		</div>
	</div>
	<!-- End option field -->

	<!-- Option field -->
	<div class="field-wrap enabled-by-addon-image-equal-height" style="display: none;">
		<label for="addon-images-height"><?php echo esc_html__( 'Image heights', 'yith-woocommerce-product-add-ons' ); ?> (px)</label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'    => 'addon-images-height',
					'name'  => 'addon_images_height',
					'type'  => 'number',
					'min'   => 0,
					'value' => $images_height,
				),
				true
			);
			?>
		</div>
	</div>
	<!-- End option field -->

	<!-- Option field -->
	<div class="field-wrap enabled-by-addon-custom-style" style="display: none;">
		<label><?php echo esc_html__( 'Label position', 'yith-woocommerce-product-add-ons' ); ?></label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'      => 'addon-label-position',
					'name'    => 'addon_label_position',
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'value'   => $label_position,
					'options' => array(
						'inside'  => __( 'Inside borders', 'yith-woocommerce-product-add-ons' ),
						'outside' => __( 'Outside borders', 'yith-woocommerce-product-add-ons' ),
					),
				),
				true
			);
			?>
		</div>
	</div>
	<!-- End option field -->

	<!-- Option field -->
	<div class="field-wrap enabled-by-addon-custom-style" style="display: none;">
		<label><?php echo esc_html__( 'Description position', 'yith-woocommerce-product-add-ons' ); ?></label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'      => 'addon-description-position',
					'name'    => 'addon_description_position',
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'value'   => $description_position,
					'options' => array(
						'inside'  => __( 'Inside borders', 'yith-woocommerce-product-add-ons' ),
						'outside' => __( 'Outside borders', 'yith-woocommerce-product-add-ons' ),
					),
				),
				true
			);
			?>
		</div>
	</div>
	<!-- End option field -->

	<!-- Option field -->
	<div class="field-wrap enabled-by-addon-custom-style" style="display: none;">
		<label for="addon-label-padding"><?php echo esc_html__( 'Padding', 'yith-woocommerce-product-add-ons' ); ?> (px)</label>
		<div class="field">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'    => 'addon-label-padding',
					'name'  => 'addon_label_padding',
					'type'  => 'dimensions',
					'units' => array( 'px' => 'px' ),
					'value' => $label_padding_array,
				),
				true
			);
			?>
		</div>
	</div>
	<!-- End option field -->

</div>

