<?php
/**
 * WAPO Template
 *
 * @author  Corrado Porzio <corradoporzio@gmail.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

$required     = $addon->get_option( 'required', $x, 'no', false ) === 'yes';
$number_limit = $addon->get_option( 'number_limit', $x );

$minimum_value = '';
$maximum_value = '';

if ( 'yes' === $number_limit ) {
	$minimum_value = $addon->get_option( 'number_limit_min', $x );
	$maximum_value = $addon->get_option( 'number_limit_max', $x );
}

$default_value = apply_filters( 'yith_wapo_default_addon_number', '', $addon );
$step_value    = apply_filters( 'yith_wapo_default_addon_number_step', '', $addon );

$allow_decimals = apply_filters( 'yith_wapo_allow_decimals_number', false );

?>

<div id="yith-wapo-option-<?php echo esc_attr( $addon->id ); ?>-<?php echo esc_attr( $x ); ?>" class="yith-wapo-option quantity">

	<!-- LEFT/ABOVE IMAGE -->
	<?php
	if ( ! empty( $option_image ) ) {
		if ( 'left' === $addon_options_images_position || 'above' === $addon_options_images_position ) :
			?>
		<label class="yith-wapo-img-label" for="yith-wapo-<?php echo esc_attr( $addon->id ); ?>-<?php echo esc_attr( $x ); ?>">
			<?php include YITH_WAPO_DIR . '/templates/front/option-image.php'; ?>
		</label>
		<?php endif; ?>
	<?php } ?>

	<div class="label">
		<label for="yith-wapo-<?php echo esc_attr( $addon->id ); ?>-<?php echo esc_attr( $x ); ?>">

			<!-- LABEL -->
			<?php echo ! $hide_option_label ? wp_kses_post( $addon->get_option( 'label', $x ) ) : ''; ?>
			<?php echo $required ? '<span class="required">*</span>' : ''; ?>

			<!-- PRICE -->
			<?php echo ! $hide_option_prices && 'value_x_product' !== $price_method ? wp_kses_post( $addon->get_option_price_html( $x, $currency ) ) : ''; ?>

		</label>
	</div>

	<!-- RIGHT/UNDER IMAGE -->
	<?php
	if ( ! empty( $option_image ) ) {
		if ( 'right' === $addon_options_images_position || 'under' === $addon_options_images_position ) :
			?>
		<label class="yith-wapo-img-label" for="yith-wapo-<?php echo esc_attr( $addon->id ); ?>-<?php echo esc_attr( $x ); ?>">
			<?php include YITH_WAPO_DIR . '/templates/front/option-image.php'; ?>
		</label>
		<?php endif; ?>
	<?php } ?>

	<!-- INPUT -->
	<input type="number"
		id="yith-wapo-<?php echo esc_attr( $addon->id ); ?>-<?php echo esc_attr( $x ); ?>"
		class="yith-wapo-option-value"
		name="yith_wapo[][<?php echo esc_attr( $addon->id . '-' . $x ); ?>]"
		placeholder="0"
		<?php if ( 'yes' === $number_limit ) : ?>
			min="<?php echo esc_attr( $minimum_value ); ?>"
			max="<?php echo esc_attr( $maximum_value ); ?>"
		<?php endif; ?>
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
		data-first-free-enabled="<?php echo esc_attr( $addon->get_setting( 'first_options_selected', 'no' ) ); ?>"
		data-first-free-options="<?php echo esc_attr( $addon->get_setting( 'first_free_options', 0 ) ); ?>"
		data-addon-id="<?php echo esc_attr( $addon->id ); ?>"
		<?php echo $addon->get_option( 'required', $x, 'no', false ) === 'yes' ? 'required' : ''; ?>
		<?php if ( '' !== $default_value ) : ?>
		value="<?php echo esc_attr( $default_value ); ?>"
		<?php endif ?>
		<?php if ( '' !== $step_value ) : ?>
		step="<?php echo esc_attr( $step_value ); ?>"
		<?php endif ?>
        <?php
            echo $allow_decimals ? 'step=any' : ''
        ?>
		style="<?php echo esc_attr( $options_width_css ); ?>">

	<?php do_action( 'woocommerce_after_quantity_input_field' ); ?>

	<!-- REQUIRED -->
	<?php if ( $required ) : ?>
		<small class="required-error" style="color: #f00; padding: 5px 0px; display: none;"><?php echo esc_html__( 'This option is required.', 'yith-woocommerce-product-add-ons' ); ?></small>
	<?php endif; ?>

	<!-- TOOLTIP -->
	<?php if ( $addon->get_option( 'tooltip', $x ) !== '' ) : ?>
		<span class="tooltip position-<?php echo esc_attr( get_option( 'yith_wapo_tooltip_position' ) ); ?>" style="<?php echo esc_attr( $options_width_css ); ?>">
			<span><?php echo esc_attr( $addon->get_option( 'tooltip', $x ) ); ?></span>
		</span>
	<?php endif; ?>

	<!-- DESCRIPTION -->
	<?php if ( '' !== $option_description ) : ?>
		<p class="description">
			<?php echo wp_kses_post( $option_description ); ?>
		</p>
	<?php endif; ?>
	<!-- Sold individually -->
	<?php if ( 'yes' === $sell_individually ) : ?>
		<input type="hidden" name="yith_wapo_sell_individually[<?php echo esc_attr( $addon->id . '-' . $x ); ?>]" value="yes">
	<?php endif; ?>
</div>
