<?php
/**
 * WAPO Template
 *
 * @author  Corrado Porzio <corradoporzio@gmail.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

$show_variation_att = apply_filters( 'yith_wapo_show_attributes_on_variations', true );

$price_type = '';

$product_id   = $addon->get_option( 'product', $x );
$price_method = $addon->get_option( 'price_method', $x, 'free', false );
if ( 'product' !== $price_method ) {
	$price_type = $addon->get_option( 'price_type', $x, 'fixed', false );
}
$selected = $addon->get_option( 'default', $x, 'no', false ) === 'yes';
$checked  = $addon->get_option( 'default', $x, 'no', false ) === 'yes' ? 'checked="checked"' : '';
$required = $addon->get_option( 'required', $x, 'no', false ) === 'yes';

$product_id = apply_filters( 'yith_wapo_addon_product_id', $product_id );
$_product   = wc_get_product( $product_id );
$parent_id  = '';
if ( $_product instanceof WC_Product ) {
	$_product_name = $_product->get_title();
	if ( $_product instanceof WC_Product_Variation ) {
		$variation      = new WC_Product_Variation( $product_id );
		if ( $show_variation_att ) {
			$var_attributes = implode( ' / ', $variation->get_variation_attributes() );
			$_product_name  = $_product_name . ' - ' . urldecode( $var_attributes );
		}
		$parent_id      = $variation->get_parent_id();
	}
	$_product_price = wc_get_price_to_display( $_product );
	$_product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'thumbnail' );
	if ( ! $_product_image && $parent_id ) { // If variation doesn't have default image.
		$_product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $parent_id ), 'thumbnail' );
	}
	$image_product = $_product_image[0] ?? array();

	$instock = $_product->is_in_stock();

	$option_price      = ! empty( $price_sale ) && 'undefined' !== $price_sale ? $price_sale : $price;
	$option_price_html = '';
	if ( 'product' === $price_method ) {
		$price_sale = '';
		$option_price = $_product_price;
		$option_price_html = $addon->get_setting( 'hide_products_prices' ) !== 'yes' ? '<small class="option-price">' . wc_price( $option_price ) . '</small>' : '';

	} elseif ( 'discount' === $price_method ) {
		$option_price          = $_product_price;
		$option_discount_value = $addon->get_price( $x );
		$price_sale            = $option_price - $option_discount_value;
		if ( 'percentage' === $price_type ) {
			$price_sale = $option_price - ( ( $option_price / 100 ) * $option_discount_value );
		}

		$option_price_html = $addon->get_setting( 'hide_products_prices' ) !== 'yes' ?
			'<small class="option-price"><del>' . wc_price( $option_price ) . '</del> ' . wc_price( $price_sale ) . '</small>' : '';
	} else {
		$option_price_html = $addon->get_option_price_html( $x, $currency );
	}


	?>

	<div id="yith-wapo-option-<?php echo esc_attr( $addon->id ); ?>-<?php echo esc_attr( $x ); ?>"
		 class="yith-wapo-option selection-<?php echo esc_attr( $selection_type ); ?><?php echo $selected ? ' selected' : ''; ?><?php echo ! $instock ? ' out-of-stock' : ''; ?>"
		 data-replace-image="<?php echo esc_attr( $image_replacement ); ?>"
		 data-product-id="<?php echo esc_attr( $_product->get_id() ); ?>"
	>

		<?php
		if ( 'left' === $addon_options_images_position ) {
			include YITH_WAPO_DIR . '/templates/front/option-image.php'; }
		?>

		<input type="checkbox"
			   id="yith-wapo-<?php echo esc_attr( $addon->id ); ?>-<?php echo esc_attr( $x ); ?>"
			   class="yith-proteo-standard-checkbox yith-wapo-option-value"
			   name="yith_wapo[][<?php echo esc_attr( $addon->id . '-' . $x ); ?>]"
			   value="<?php echo 'product-' . esc_attr( $_product->get_id() ) . '-1'; ?>"
			   data-price="<?php echo esc_attr( $option_price ); ?>"
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
			<?php echo $required ? 'required' : ''; ?>
			<?php echo ! $instock ? 'disabled="disabled"' : ''; ?>
			<?php echo esc_attr( $checked ); ?>
			   style="display: none;">

		<?php // Changed <label> tag by a <div> tag ?>
		<div for="yith-wapo-<?php echo esc_attr( $addon->id ); ?>-<?php echo esc_attr( $x ); ?>" style="<?php echo esc_attr( $options_width_css ); ?>" class="product-container<?php echo ! $instock ? ' disabled' : ''; ?>">
			<img src="<?php echo esc_attr( ! empty( $image_product ) ? $image_product : '' ); ?>" data-id="<?php echo esc_attr( $product_id ); ?>">
			<div class="product-info">
				<!-- PRODUCT NAME -->
				<span class="product-name"><?php echo wp_kses_post( $_product_name ); ?></span>
				<?php
				if ( $addon->get_setting( 'show_sku' ) === 'yes' && $_product->get_sku() !== '' ) {
					echo '<div><small style="font-size: 11px;">SKU: ' . esc_html( $_product->get_sku() ) . '</small></div>'; }
				?>
				<?php
				do_action( 'yith_wapo_after_addon_product_name', $_product, $product_id, $addon );
				?>

				<!-- PRICE -->
				<?php echo ! $hide_option_prices ? wp_kses_post( $option_price_html ) : ''; ?>

				<!-- STOCK -->
				<?php
				$stock_class  = '';
				$stock_style  = '';
				$stock_status = '';
				if ( $instock ) {
					$stock_class = 'in-stock';
					$stock_style = 'style="margin-bottom: 10px"';
					if ( $_product->get_manage_stock() ) {
						$stock_status = $_product->get_stock_quantity() . ' ' . esc_html__( 'in stock', 'yith-woocommerce-product-add-ons' );
					} else {
						$stock_status = esc_html__( 'In stock', 'yith-woocommerce-product-add-ons' );
					}
				} else {
					$stock_class  = 'out-of-stock';
					$stock_status = esc_html__( 'Out of stock', 'yith-woocommerce-product-add-ons' );
				}
				$stock_qty = $_product->get_manage_stock() ? $_product->get_stock_quantity() : false;
				if ( $addon->get_setting( 'show_stock' ) ) {
					echo '<div ' . esc_attr( $stock_style ) . '><small class="stock ' . esc_attr( $stock_class ) . '" style="font-size: 11px;">' . esc_html( $stock_status ) . '</small></div>';
				}
				?>

				<?php if ( $_product->get_stock_status() === 'instock' ) : ?>

					<div class="option-add-to-cart">
						<?php

						$input_name           = 'yith_wapo_product_qty[' . esc_attr( $addon->id . '-' . $x ) . ']';

						if ( $addon->get_setting( 'show_quantity' ) === 'yes' ) {

							$default_qty = apply_filters( 'yith_wapo_default_product_qty', 1, $_product );

							$input_class_quantity = array( 'input-text', 'qty', 'text', 'wapo-product-qty' );
							$max_value            = $_product->get_stock_quantity();

							woocommerce_quantity_input(
								array(
									'input_id'    => $input_name,
									/**
									 * APPLY_FILTERS: yith_wapo_input_class_quantity_product
									 *
									 * Filter the array with the CSS clases for the quantity input in add-on type Products.
									 *
									 * @param array      $input_class_quantity CSS classes
									 * @param WC_Product $_product             WooCommerce product
									 *
									 * @return array
									 */
									'classes'     => apply_filters( 'yith_wapo_input_class_quantity_product', $input_class_quantity, $_product ),
									'input_name'  => $input_name,
									'min_value'   => apply_filters( 'woocommerce_quantity_input_min', 1, $_product ),
									'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $max_value, $_product ),
									'input_value' => $default_qty,
									//'step'        => '',
								)
							);
						}
						?>
						<?php if ( $addon->get_setting( 'show_add_to_cart' ) === 'yes' ) : ?>
							<a href="?add-to-cart=<?php echo esc_attr( $_product->get_id() ); ?>&quantity=1" class="button add_to_cart_button">
								<?php echo esc_html__( 'Add to cart', 'yith-woocommerce-product-add-ons' ); ?>
							</a>
						<?php endif; ?>

						<?php
						if ( apply_filters( 'yith_wapo_show_addon_product_add_to_quote', false ) ) :
							function_exists( 'yith_ywraq_render_button' ) ? yith_ywraq_render_button( $product_id ) : '';
						endif;
						?>
					</div>

				<?php endif; ?>
			</div>

			<?php
			if ( apply_filters( 'yith_wapo_show_addon_product_link', false ) ) {
				$link_target = apply_filters( 'yith_wapo_show_addon_product_link_target', '' );
				echo '<a class="button view-product" target="' . $link_target . '" href="' . get_permalink( $product_id ) . '">' . esc_html__( 'View product', 'yith-woocommerce-product-add-ons' ) . '</a>';
			}
			?>
			<div class="clear"></div>
		</div>

		<?php
		if ( 'right' === $addon_options_images_position ) {
			include YITH_WAPO_DIR . '/templates/front/option-image.php'; }
		?>

		<?php if ( $required ) : ?>
			<small class="required-error" style="color: #f00; padding: 5px 0px; display: none;"><?php echo esc_html__( 'This option is required.', 'yith-woocommerce-product-add-ons' ); ?></small>
		<?php endif; ?>

		<?php if ( $addon->get_option( 'tooltip', $x ) !== '' ) : ?>
			<span class="tooltip">
				<span><?php echo esc_attr( $addon->get_option( 'tooltip', $x ) ); ?></span>
			</span>
		<?php endif; ?>

		<?php
		if ( 'above' === $addon_options_images_position ) {
			include YITH_WAPO_DIR . '/templates/front/option-image.php'; }
		?>

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
	<?php
}
