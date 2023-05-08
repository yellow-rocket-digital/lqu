<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH WooCommerce Request A Quote Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$product_id = 0;
$args       = array(
	'posts_per_page' => 1,
	'orderby'        => 'rand',
	'post_type'      => 'product',
);

$random_products = get_posts( $args );
if ( $random_products ) {
	foreach ( $random_products as $product ) {
		$product_id = $product->ID;
	}
}

$blocks = array(

	'yith-ywraq-widget-quote'      => array(
		'style'                        => 'yith-ywraq-gutenberg',
		'title'                        => esc_html_x( 'Widget Quote List', '[gutenberg]: block name', 'yith-woocommerce-request-a-quote' ),
		'description'                  => esc_html_x( 'Show products added to your list.', '[gutenberg]: block description', 'yith-woocommerce-request-a-quote' ),
		'shortcode_name'               => 'yith_ywraq_widget_quote',
		'elementor_map_from_gutenberg' => true,
		'elementor_icon'               => 'eicon-kit-details',
		'do_shortcode'                 => true,
		'keywords'                     => array(
			esc_html_x( 'Quote', '[gutenberg]: keywords', 'yith-woocommerce-request-a-quote' ),
			esc_html_x( 'Quote Widget', '[gutenberg]: keywords', 'yith-woocommerce-request-a-quote' ),
		),
		'attributes'                   => array(
			'title'           => array(
				'type'    => 'text',
				'label'   => esc_html_x( 'Title', '[gutenberg]: title of widget', 'yith-woocommerce-request-a-quote' ),
				'default' => esc_html__( 'Quote List', 'yith-woocommerce-request-a-quote' ),
			),
			'button_label'    => array(
				'type'    => 'text',
				'label'   => esc_html_x( 'Button Label', '[gutenberg]: label of button', 'yith-woocommerce-request-a-quote' ),
				'default' => esc_html__( 'View list', 'yith-woocommerce-request-a-quote' ),
			),
			'show_thumbnail'  => array(
				'type'    => 'toggle',
				'label'   => esc_html_x( 'Show Thumbnail', '[gutenberg]: show or hide the thumbnail', 'yith-woocommerce-request-a-quote' ),
				'default' => true,
				'helps'   => array(
					'checked'   => esc_html_x( 'Show it', '[gutenberg]: Help to show thumbnail', 'yith-woocommerce-request-a-quote' ),
					'unchecked' => esc_html_x( 'Hide it', '[gutenberg]: Help to hide thumbnail', 'yith-woocommerce-request-a-quote' ),
				),
			),
			'show_price'      => array(
				'type'    => 'toggle',
				'label'   => esc_html_x( 'Show Price', '[gutenberg]: show or hide the price', 'yith-woocommerce-request-a-quote' ),
				'default' => true,
				'helps'   => array(
					'checked'   => esc_html_x( 'Show it', '[gutenberg]: Help to show price', 'yith-woocommerce-request-a-quote' ),
					'unchecked' => esc_html_x( 'Hide it', '[gutenberg]: Help to hide price', 'yith-woocommerce-request-a-quote' ),
				),
			),
			'show_quantity'   => array(
				'type'    => 'toggle',
				'label'   => esc_html_x( 'Show Quantity', '[gutenberg]: show or hide the quantity', 'yith-woocommerce-request-a-quote' ),
				'default' => true,
				'helps'   => array(
					'checked'   => esc_html_x( 'Show it', '[gutenberg]: Help to show quantity', 'yith-woocommerce-request-a-quote' ),
					'unchecked' => esc_html_x( 'Hide it', '[gutenberg]: Help to hide quantity', 'yith-woocommerce-request-a-quote' ),
				),
			),
			'show_variations' => array(
				'type'    => 'toggle',
				'label'   => esc_html_x( 'Show Variations', '[gutenberg]: show or hide the variations', 'yith-woocommerce-request-a-quote' ),
				'default' => true,
				'helps'   => array(
					'checked'   => esc_html_x( 'Show it', '[gutenberg]: Help to show variations', 'yith-woocommerce-request-a-quote' ),
					'unchecked' => esc_html_x( 'Hide it', '[gutenberg]: Help to hide variations', 'yith-woocommerce-request-a-quote' ),
				),
			),

		),

	),
	'yith-ywraq-mini-widget-quote' => array(
		'style'                        => 'yith-ywraq-gutenberg',
		'title'                        => esc_html_x( 'Mini Widget Quote List', '[gutenberg]: block name', 'yith-woocommerce-request-a-quote' ),
		'description'                  => esc_html_x( 'Show products added to your list.', '[gutenberg]: block description', 'yith-woocommerce-request-a-quote' ),
		'shortcode_name'               => 'yith_ywraq_mini_widget_quote',
		'elementor_map_from_gutenberg' => true,
		'elementor_icon'               => 'eicon-select',
		'do_shortcode'                 => true,
		'keywords'                     => array(
			esc_html_x( 'Quote', '[gutenberg]: keywords', 'yith-woocommerce-request-a-quote' ),
			esc_html_x( 'Mini Quote Widget', '[gutenberg]: keywords', 'yith-woocommerce-request-a-quote' ),
		),
		'attributes'                   => array(
			'title'             => array(
				'type'    => 'text',
				'label'   => esc_html_x( 'Title', '[gutenberg]: title of widget', 'yith-woocommerce-request-a-quote' ),
				'default' => esc_html__( 'Quote List', 'yith-woocommerce-request-a-quote' ),
			),
			'item_name'         => array(
				'type'    => 'text',
				'label'   => esc_html_x( 'Item name', '[gutenberg]: title of widget', 'yith-woocommerce-request-a-quote' ),
				'default' => esc_html__( 'item', 'yith-woocommerce-request-a-quote' ),
			),
			'item_plural_name'  => array(
				'type'    => 'text',
				'label'   => esc_html_x( 'Item plural name', '[gutenberg]: title of widget', 'yith-woocommerce-request-a-quote' ),
				'default' => esc_html__( 'items', 'yith-woocommerce-request-a-quote' ),
			),
			'button_label'      => array(
				'type'    => 'text',
				'label'   => esc_html_x( 'Button Label', '[gutenberg]: label of button', 'yith-woocommerce-request-a-quote' ),
				'default' => esc_html__( 'View list', 'yith-woocommerce-request-a-quote' ),
			),
			'show_title_inside' => array(
				'type'    => 'toggle',
				'label'   => esc_html_x( 'Show widget title inside', '[gutenberg]: Choose whether to show the title inside (visible when the widget is open) or outside next to the item counter (visible when it is collapsed).', 'yith-woocommerce-request-a-quote' ),
				'default' => true,
				'helps'   => array(
					'checked'   => esc_html_x( 'Show the title inside the widget dropdown', '[gutenberg]: Help to set title position', 'yith-woocommerce-request-a-quote' ),
					'unchecked' => esc_html_x( 'Show the title outside next to the item counter', '[gutenberg]: Help to set the title position', 'yith-woocommerce-request-a-quote' ),
				),
			),
			'show_thumbnail'    => array(
				'type'    => 'toggle',
				'label'   => esc_html_x( 'Show Thumbnail', '[gutenberg]: show or hide the thumbnail', 'yith-woocommerce-request-a-quote' ),
				'default' => true,
				'helps'   => array(
					'checked'   => esc_html_x( 'Show it', '[gutenberg]: Help to show thumbnail', 'yith-woocommerce-request-a-quote' ),
					'unchecked' => esc_html_x( 'Hide it', '[gutenberg]: Help to hide thumbnail', 'yith-woocommerce-request-a-quote' ),
				),
			),
			'show_price'        => array(
				'type'    => 'toggle',
				'label'   => esc_html_x( 'Show Price', '[gutenberg]: show or hide the price', 'yith-woocommerce-request-a-quote' ),
				'default' => true,
				'helps'   => array(
					'checked'   => esc_html_x( 'Show it', '[gutenberg]: Help to show price', 'yith-woocommerce-request-a-quote' ),
					'unchecked' => esc_html_x( 'Hide it', '[gutenberg]: Help to hide price', 'yith-woocommerce-request-a-quote' ),
				),
			),
			'show_quantity'     => array(
				'type'    => 'toggle',
				'label'   => esc_html_x( 'Show Quantity', '[gutenberg]: show or hide the quantity', 'yith-woocommerce-request-a-quote' ),
				'default' => true,
				'helps'   => array(
					'checked'   => esc_html_x( 'Show it', '[gutenberg]: Help to show quantity', 'yith-woocommerce-request-a-quote' ),
					'unchecked' => esc_html_x( 'Hide it', '[gutenberg]: Help to hide quantity', 'yith-woocommerce-request-a-quote' ),
				),
			),
			'show_variations'   => array(
				'type'    => 'toggle',
				'label'   => esc_html_x( 'Show Variations', '[gutenberg]: show or hide the variations', 'yith-woocommerce-request-a-quote' ),
				'default' => true,
				'helps'   => array(
					'checked'   => esc_html_x( 'Show it', '[gutenberg]: Help to show variations', 'yith-woocommerce-request-a-quote' ),
					'unchecked' => esc_html_x( 'Hide it', '[gutenberg]: Help to hide variations', 'yith-woocommerce-request-a-quote' ),
				),
			),
		),
	),
	'yith-ywraq-button-quote'      => array(
		'style'                        => 'yith-ywraq-gutenberg',
		'title'                        => esc_html_x( 'Quote button', '[gutenberg]: block name', 'yith-woocommerce-request-a-quote' ),
		'description'                  => esc_html_x( 'Show the quote button of a product', '[gutenberg]: block description', 'yith-woocommerce-request-a-quote' ),
		'shortcode_name'               => 'yith_ywraq_button_quote',
		'elementor_icon'               => 'eicon-button',
		'elementor_map_from_gutenberg' => true,
		'do_shortcode'                 => true,
		'keywords'                     => array(
			esc_html_x( 'Button Quote', '[gutenberg]: keywords', 'yith-woocommerce-request-a-quote' ),
			esc_html_x( 'Quote request', '[gutenberg]: keywords', 'yith-woocommerce-request-a-quote' ),
		),
		'attributes'                   => array(
			'label'          => array(
				'type'    => 'text',
				'label'   => esc_html_x( 'Label', '[gutenberg]: title of widget', 'yith-woocommerce-request-a-quote' ),
				'default' => esc_html__( 'Add to quote', 'yith-woocommerce-request-a-quote' ),
			),
			'product'        => array(
				'type'    => 'text',
				'label'   => esc_html_x( 'Product id', '[gutenberg]: title of widget', 'yith-woocommerce-request-a-quote' ),
				'default' => $product_id,
			),
			'style'          => array(
				'type'    => 'radio',
				'label'   => esc_html_x( 'Style button', '[gutenberg]: title of widget', 'yith-woocommerce-request-a-quote' ),
				'default' => 'button',
				'options' => array(
					'button'     => esc_html__( 'Button', 'yith-woocommerce-request-a-quote' ),
					'ywraq-link' => esc_html__( 'Text Link', 'yith-woocommerce-request-a-quote' ),
				),
			),
			'color'          => array(
				'type'    => 'colorpicker',
				'label'   => esc_html_x( 'Color', '[gutenberg]: title of widget', 'yith-woocommerce-request-a-quote' ),
				'default' => '#fff',
				'deps'    => array(
					'id'    => 'style',
					'value' => 'button',
				),
			),
			'color_hover'    => array(
				'type'    => 'colorpicker',
				'label'   => esc_html_x( 'Color on hover', '[gutenberg]: title of widget', 'yith-woocommerce-request-a-quote' ),
				'default' => '#fff',
				'deps'    => array(
					'id'    => 'style',
					'value' => 'button',
				),
			),
			'bg_color'       => array(
				'type'    => 'colorpicker',
				'label'   => esc_html_x( 'Background color', '[gutenberg]: title of widget', 'yith-woocommerce-request-a-quote' ),
				'default' => '#0066b4',
				'deps'    => array(
					'id'    => 'style',
					'value' => 'button',
				),
			),
			'bg_color_hover' => array(
				'type'    => 'colorpicker',
				'label'   => esc_html_x( 'Background color on hover', '[gutenberg]: title of widget', 'yith-woocommerce-request-a-quote' ),
				'default' => '#044a80',
				'deps'    => array(
					'id'    => 'style',
					'value' => 'button',
				),
			),
		),
	),
);

return apply_filters( 'ywraq_gutenberg_blocks', $blocks );
