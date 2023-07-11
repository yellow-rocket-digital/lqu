<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>
<div class="y-product">
	<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>
		<?php
		/**
		 * Hook: woocommerce_before_single_product_summary.
		 *
		 * @hooked woocommerce_show_product_sale_flash - 10
		 * @hooked woocommerce_show_product_images - 20
		 */
		do_action( 'woocommerce_before_single_product_summary' );
		?>

		<div class="summary entry-summary mt-5 mt-md-0">
			<h1 class="y-product__title mt-0"><?= $product->get_title(); ?></h1>
			<?php if ($product->get_description()): ?>
				<h3 class="mb-3 mt-5">Description</h3>
				<div class="mb-4"><?= $product->get_description(); ?></div>	
			<?php endif; ?>
		
			<?php if ($product->has_dimensions() && is_user_logged_in()): ?>
				<div class="y-product__dimensions mt-5">
					<h3 class="mb-3 mt-0">Dimensions</h3>

					<?php if ( ! empty( $product->get_width() ) ): ?>
						<span class="product_dimensions"><?php echo $product->get_width(); ?>"W</span>
					<?php endif; ?>
					<?php if ( ! empty( $product->get_length() ) ): ?>
						<span class="product_dimensions"><?php echo $product->get_length(); ?>"D</span>
					<?php endif; ?>
					<?php if ( ! empty( $product->get_height() ) ): ?>
						<span class="product_dimensions"><?php echo $product->get_height(); ?>"H</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if (!has_term('mercado', 'product_cat', $product->get_ID())): ?>
			<p class="mt-5">All pieces are made to order, frame to fabric so the dimensions may be determined by the customer.</p>

			<h3 class="mb-3">Fabric</h3>
			<p>All fabrics are supplied by the customer. Yardage requirements are supplied in estimates once the design is specified.</p>
			<?php endif; ?>

			<?php if (is_user_logged_in()): ?>
				<div class="y-product__details">
					<?php
					/**
					 * Hook: woocommerce_single_product_summary.
					 *
					 * @hooked woocommerce_template_single_title - 5
					 * @hooked woocommerce_template_single_rating - 10
					 * @hooked woocommerce_template_single_price - 10
					 * @hooked woocommerce_template_single_excerpt - 20
					 * @hooked woocommerce_template_single_add_to_cart - 30
					 * @hooked woocommerce_template_single_meta - 40
					 * @hooked woocommerce_template_single_sharing - 50
					 * @hooked WC_Structured_Data::generate_product_data() - 60
					 */
					do_action( 'woocommerce_single_product_summary' );
					?>
				</div>
			<?php else: ?>
				<a class="button my-3" href="/my-account">Start Order</a>
			<?php endif; ?>
		</div>
	</div>

	<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	do_action( 'woocommerce_after_single_product_summary' );
	?>
</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>
