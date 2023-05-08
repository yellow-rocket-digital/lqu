<?php
/**
 * WAPO Template ( colorpicker )
 *
 * @author  Corrado Porzio <corradoporzio@gmail.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 *
 * @var object $addon
 * @var int    $x
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

$colorpicker_show = $addon->get_option( 'colorpicker_show', $x, 'default_color', false );

?>

<div class="fields">
	<div class="col-left">
		<!-- Option field -->
		<div class="field-wrap">
			<label for="option-colorpicker-show-<?php echo esc_attr( $x ); ?>"><?php echo esc_html__( 'In picker show', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field colorpicker-show-as">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'      => 'option-colorpicker-show-' . $x,
						'class'   => 'option-colorpicker-show wc-enhanced-select',
						'name'    => 'options[colorpicker_show][]',
						'type'    => 'select',
						'value'   => $colorpicker_show,
						'options' => array(
							'default_color' => __( 'A default color', 'yith-woocommerce-product-add-ons' ),
							'placeholder'   => __( 'A placeholder text', 'yith-woocommerce-product-add-ons' ),
						),
					),
					true
				);
				?>
			</div>
		</div>
		<!-- End option field -->
	</div>
	<div class="col-right">
		<!-- Option field -->
		<div class="field-wrap default-colorpicker" style="<?php echo 'default_color' !== $colorpicker_show ? 'display: none;' : ''; ?>">
			<label for="option-colorpicker-<?php echo esc_attr( $x ); ?>"><?php echo esc_html__( 'Default color', 'yith-woocommerce-product-add-ons' ); ?></label>
			<div class="field">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'            => 'option-colorpicker-' . $x,
						'name'          => 'options[colorpicker][]',
						'type'          => 'colorpicker',
						'alpha_enabled' => false,
						'default'       => '#',
						'value'         => $addon->get_option( 'colorpicker', $x, '#ffffff', false ),
					),
					true
				);
				?>
			</div>
		</div>
		<!-- End option field -->
			<!-- Option field -->
		<div class="field-wrap colorpicker-placeholder" style="<?php echo 'placeholder' !== $colorpicker_show ? 'display: none;' : ''; ?>">
			<label for="option-tooltip-<?php echo esc_attr( $x ); ?>"><?php echo esc_html__( 'Placeholder', 'yith-woocommerce-product-add-ons' ); ?>:</label>
				<div class="field">
					<input type="text" name="options[placeholder][]" id="option-tooltip-<?php echo esc_attr( $x ); ?>" value="<?php echo esc_html( $addon->get_option( 'placeholder', $x, '', false ) ); ?>">
				</div>
		</div>
			<!-- End option field -->
	</div>

	<?php require YITH_WAPO_DIR . '/templates/admin/option-common-fields.php'; ?>
</div>
