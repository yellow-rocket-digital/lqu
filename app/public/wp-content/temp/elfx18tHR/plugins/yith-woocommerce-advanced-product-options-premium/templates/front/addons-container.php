<?php
/**
 * Add-ons Container.
 *
 * @author  YITH
 * @package YITH\ProductAddOns\Templates
 * @version 3.0.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

$product_price = apply_filters( 'yith_wapo_product_price' , yit_get_display_price( $product ), $product );

do_action( 'yith_wapo_before_main_container' );
?>

<!-- #yith-wapo-container -->
<div id="yith-wapo-container" data-product-price="<?php echo esc_attr( $product_price ); ?>" data-product-id="<?php echo esc_attr( $product->get_id() )  ?>">
	<?php $instance->print_blocks(); ?>
</div>

<?php
do_action( 'yith_wapo_after_main_container' );
