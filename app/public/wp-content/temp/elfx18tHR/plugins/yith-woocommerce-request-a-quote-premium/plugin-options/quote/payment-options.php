<?php
/**
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH WooCommerce Request a quote
 * @since   3.0.0
 * @author  YITH
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAQ_VERSION' ) ) {
	exit;
}


$section = array(
	'quote_payment'           => array(
		'name' => esc_html__( 'Quote Payment', 'yith-woocommerce-request-a-quote' ),
		'desc' => __( 'These options apply to all new quote requests, but can be overridden on the quote detail page.', 'yith-woocommerce-request-a-quote' ),
		'type' => 'title',
		'id'   => 'ywraq_quote_payment',
	),
	'pay_quote_now'           => array(
		'name'      => esc_html__( 'Redirect the user to "Pay for Quote" page', 'yith-woocommerce-request-a-quote' ),
		'desc'      => sprintf(
			'%s<br>%s<br>%s',
			esc_html__( 'If billing and shipping fields are filled, you can send the customer to the "Pay for Quote" Page.', 'yith-woocommerce-request-a-quote' ),
			esc_html__( 'In this page, neither billing nor shipping information will be requested.', 'yith-woocommerce-request-a-quote' ),
			esc_html__( 'If billing and shipping are empty, the user will be redirected to the default Checkout page.', 'yith-woocommerce-request-a-quote' )
		),
		'id'        => 'ywraq_pay_quote_now',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => apply_filters( 'ywraq_set_default_pay_quote_now', 'no' ),
	),

	'checkout_info'           => array(
		'name'      => esc_html__( 'Override checkout fields with the billing and shipping info of all orders', 'yith-woocommerce-request-a-quote' ),
		'type'      => 'yith-field',
		'yith-type' => 'select',
		'id'        => 'ywraq_checkout_info',
		'class'     => 'wc-enhanced-select',
		'desc'      => esc_html__( 'Choose whether to override the billing and shipping checkout fields of all orders.', 'yith-woocommerce-request-a-quote' ),
		'default'   => '-',
		'options'   => array(
			'-'        => esc_html__( 'Do not override billing and shipping info', 'yith-woocommerce-request-a-quote' ),
			'both'     => esc_html__( 'Override billing and shipping info', 'yith-woocommerce-request-a-quote' ),
			'billing'  => esc_html__( 'Override billing info', 'yith-woocommerce-request-a-quote' ),
			'shipping' => esc_html__( 'Override shipping info', 'yith-woocommerce-request-a-quote' ),
		),
	),

	'disable_shipping_method' => array(
		'name'      => esc_html__( 'Override shipping costs', 'yith-woocommerce-request-a-quote' ),
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'id'        => 'ywraq_disable_shipping_method',
		'desc'      => esc_html__( 'Enable if you want to apply the shipping costs applied in the quote, and not the default shipping costs.', 'yith-woocommerce-request-a-quote' ),
		'default'   => apply_filters( 'override_shipping_option_default_value', 'yes' ),
	),

	// @since 1.6.3
	'lock_editing'            => array(
		'name'      => esc_html__( 'Lock the editing of checkout fields', 'yith-woocommerce-request-a-quote' ),
		'type'      => 'yith-field',
		'id'        => 'ywraq_lock_editing',
		'yith-type' => 'onoff',
		'desc'      => esc_html__( 'If enabled, the customer can not edit the checkout fields.', 'yith-woocommerce-request-a-quote' ),
		'default'   => 'no',
	),
	'quote_payment_end'       => array(
		'type' => 'sectionend',
		'id'   => 'ywraq_quote_payment_end',
	),
);
return array( 'quote-payment' => apply_filters( 'ywraq_quote_payment_options', $section ) );
