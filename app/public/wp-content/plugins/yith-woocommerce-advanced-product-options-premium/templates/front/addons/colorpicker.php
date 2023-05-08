<?php
/**
 * WAPO Template
 *
 * @author  Corrado Porzio <corradoporzio@gmail.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

$required         = $addon->get_option( 'required', $x, 'no', false ) === 'yes';
$checked          = $addon->get_option( 'default', $x, 'no', false ) === 'yes';
$selected         = $checked ? 'selected' : '';
$colorpicker_show = $addon->get_option( 'colorpicker_show', $x, 'default_color' );
$colorpicker      = $addon->get_option( 'colorpicker', $x, '#ffffff' );
if ( 'placeholder' === $colorpicker_show ) {
	$colorpicker = '';
}
$placeholder   = $addon->get_option( 'placeholder', $x );
$default_color = 'default_color' === $colorpicker_show ? wp_kses_post( $colorpicker ) : '';

?>

<div id="yith-wapo-option-<?php echo esc_attr( $addon->id ); ?>-<?php echo esc_attr( $x ); ?>"
	class="yith-wapo-option selection-<?php echo esc_attr( $selection_type ); ?> <?php echo esc_attr( $selected ); ?>"
	data-replace-image="<?php echo esc_attr( $image_replacement ); ?>">

	<!-- UNDER IMAGE -->
	<?php
	if ( 'above' === $addon_options_images_position || 'left' === $addon_options_images_position ) {
		include YITH_WAPO_DIR . '/templates/front/option-image.php'; }
	?>

	<small for="yith-wapo-<?php echo esc_attr( $addon->id ); ?>-<?php echo esc_attr( $x ); ?>" class="option-label"><?php echo ! $hide_option_label ? wp_kses_post( $addon->get_option( 'label', $x ) ) : ''; ?></small>
	<?php echo ! $hide_option_prices ? wp_kses_post( $addon->get_option_price_html( $x, $currency ) ) : ''; ?>
	<?php $colorpickerstyle = apply_filters('yith_wapo_color_picker_input', 'text');    ?>
	<div class="yith-wapo-colorpicker-container">
	<!-- Colorpicker -->
	<input type="<?php echo esc_attr($colorpickerstyle); ?>"
		class="wp-color-picker yith-wapo-option-value"
		id="yith-wapo-<?php echo esc_attr( $addon->id ); ?>-<?php echo esc_attr( $x ); ?>"
		name="yith_wapo[][<?php echo esc_attr( $addon->id . '-' . $x ); ?>]"
		data-price="<?php echo esc_attr( $price ); ?>"
		<?php
		if ( $price > 0 ) {
			?>
			data-price-sale="<?php echo esc_attr( $price_sale ); ?>"
			<?php
		}
		?>
		data-price-type="<?php echo esc_attr( $price_type ); ?>"
		data-price-method="<?php echo esc_attr( $price_method ); ?>"
		data-addon-id="<?php echo esc_attr( $addon->id ); ?>"
		data-addon-colorpicker-show="<?php echo esc_attr( $colorpicker_show ); ?>"
		<?php if ( ! empty( $default_color ) ) : ?>
		data-default-color="<?php echo wp_kses_post( $default_color ); ?>"
		<?php endif; ?>
		data-addon-placeholder="<?php echo esc_attr( $placeholder ); ?>"
	<?php echo $addon->get_option( 'required', $x, 'no', false ) === 'yes' ? 'required' : ''; ?>
	/>
	</div>

	<!-- REQUIRED -->
	<?php if ( $required ) : ?>
		<small class="required-error" style="color: #f00; padding: 5px 0px; display: none;">
			<?php echo esc_html__( 'This option is required.', 'yith-woocommerce-product-add-ons' ); ?>
		</small>
	<?php endif; ?>

	<!-- TOOLTIP -->
	<?php if ( 'yes' === get_option( 'yith_wapo_show_tooltips' ) && '' !== $addon->get_option( 'tooltip', $x ) ) : ?>
		<span class="tooltip position-<?php echo esc_attr( get_option( 'yith_wapo_tooltip_position' ) ); ?>" style="width: 100%;">
			<span><?php echo wp_kses_post( $addon->get_option( 'tooltip', $x ) ); ?></span>
		</span>
	<?php endif; ?>

	<!-- UNDER IMAGE -->
	<?php
	if ( 'under' === $addon_options_images_position || 'right' === $addon_options_images_position ) {
		include YITH_WAPO_DIR . '/templates/front/option-image.php'; }
	?>

	<!-- DESCRIPTION -->
	<?php if ( '' !== $option_description ) : ?>
		<p class="description"><?php echo wp_kses_post( $option_description ); ?></p>
	<?php endif; ?>
	<!-- Sold individually -->
	<?php if ( 'yes' === $sell_individually ) : ?>
		<input type="hidden" name="yith_wapo_sell_individually[<?php echo esc_attr( $addon->id . '-' . $x ); ?>]" value="yes">
	<?php endif; ?>
</div>
