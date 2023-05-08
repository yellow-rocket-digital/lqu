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

$allow_add_to_cart = get_option( 'ywraq_allow_add_to_cart', 'yes' );

$section = array(
	'quote_settings'        => array(
		'name' => esc_html__( 'Quote Options', 'yith-woocommerce-request-a-quote' ),
		'type' => 'title',
		'id'   => 'ywraq_quote_settings',
	),
	'enable_order_creation' => array(
		'name'      => esc_html_x( 'When a user sends a quote request', 'Admin quote options name', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html_x( 'Choose to automatically generate an order when a new quote request is received or to only receive a notification email and manage the quote manually.', 'Admin quote options description', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_enable_order_creation',
		'type'      => 'yith-field',
		'yith-type' => 'radio',
		'options'   => array(
			'yes' => esc_html_x( 'Save the request as an order with status "New quote request" and send an email to the site admin (Recommended)', 'Admin quote options radio option', 'yith-woocommerce-request-a-quote' ),
			'no'  => esc_html_x( 'Only send an email to the site admin', 'Admin quote options radio option', 'yith-woocommerce-request-a-quote' ),
		),
		'default'   => 'yes',
	),

	'enable_expired_time' => array(
		'name'      => esc_html__( 'Set an expiry time for quotes', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enable if you want to set an expiry time for all quotes sent.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_enable_expired_time',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
	),

	'expired_time' => array(
		'name'      => esc_html__( 'All quotes will expire after', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose after how many days quotes will expire.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_expired_time',
		'type'      => 'yith-field',
		'yith-type' => 'inline-fields',
		'fields'    => array(
			'days'  => array(
				'id'      => 'days',
				'type'    => 'number',
				'min'     => 1,
				'step'    => 1,
				'default' => 10,
			),
			'html0' => array(
				'type' => 'html',
				'html' => esc_html__( 'days', 'yith-woocommerce-request-a-quote' ),
			),
		),
		'deps'      => array(
			'id'    => 'ywraq_enable_expired_time',
			'value' => 'yes',
			'type'  => 'hide-disable',
		),

	),

	'show_old_price' => array(
		'name'      => esc_html__( 'Strikethrough on original prices on discounted quote', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enable to show the original price with a strikethrough if a discounted price is shown on all quotes.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_show_old_price',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
	),

	'calculate_default_shipping_quote' => array(
		'name'      => esc_html__( 'Add default shipping fee to quote', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enable to add default shipping cost to the quote.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_calculate_default_shipping_quote',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
	),

	'sum_multiple_shipping_costs' => array(
		'name'      => esc_html__( 'Enable the option to add multiple shipping costs', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'If enabled, it will be possible to manually add more shipping costs by the admin which will show as a total at checkout.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_sum_multiple_shipping_costs',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'yes',
	),

	'automate_send_quote' => array(
		'name'      => esc_html__( 'Generate and send quotes automatically', 'yith-woocommerce-request-a-quote' ),
		'desc'      => wp_kses_post( __( 'Enable to send an automatic quote with product prices. This option is useful if you hide prices in your shop and want to send a quote with all prices only to users that sends a quote request.', 'yith-woocommerce-request-a-quote' ) ),
		'id'        => 'ywraq_automate_send_quote',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
	),


	'cron_time' => array(
		'name'      => esc_html__( 'Send automatically the quote after', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose when to send the quote automatically.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_cron_time',
		'type'      => 'yith-field',
		'yith-type' => 'inline-fields',
		'fields'    => array(
			'time' => array(
				'type'    => 'number',
				'min'     => 1,
				'default' => '4',
			),
			'type' => array(
				'id'      => 'type',
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'options' => array(
					'minutes' => esc_html__( 'Minutes', 'yith-woocommerce-request-a-quote' ),
					'hours'   => esc_html__( 'Hours', 'yith-woocommerce-request-a-quote' ),
					'days'    => esc_html__( 'Days', 'yith-woocommerce-request-a-quote' ),
				),
				'default' => 'hours',
			),
		),
		'deps'      => array(
			'id'    => 'ywraq_automate_send_quote',
			'value' => 'yes',
		),
	),


	'show_accept_link' => array(
		'name'      => esc_html__( 'Show "Accept" button on quote', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enable to show the "Accept" button in the email received by the user and in "My Account" page.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_show_accept_link',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'yes',
	),

	'accept_link_label' => array(
		'name'      => esc_html__( '"Accept" label', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enter the button\'s label.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_accept_link_label',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'required'  => true,
		'deps'      => array(
			'id'    => 'ywraq_show_accept_link',
			'value' => 'yes',
		),
		'default'   => esc_html__( 'Accept', 'yith-woocommerce-request-a-quote' ),
	),

	'page_accepted' => array(
		'name'      => esc_html__( 'Redirect after accepting quote:', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose to which page the user will be redirected after clicking the “Accept” link.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_page_accepted',
		'type'      => 'yith-field',
		'yith-type' => 'select',
		'class'     => 'wc-enhanced-select',
		'options'   => ywraq_get_pages(),
		'deps'      => array(
			'id'    => 'ywraq_show_accept_link',
			'value' => 'yes',
		),
		'default'   => get_option( 'woocommerce_checkout_page_id' ),
	),

	'show_reject_link'  => array(
		'name'      => esc_html__( 'Show "Reject" button on quote', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enable to show the "Reject" button in the email received by the user and in "My Account" page.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_show_reject_link',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'yes',
	),
	'reject_link_label' => array(
		'name'      => esc_html__( '"Reject" label', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enter the button\'s label.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_reject_link_label',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'required'  => true,
		'deps'      => array(
			'id'    => 'ywraq_show_reject_link',
			'value' => 'yes',
		),
		'default'   => esc_html__( 'Reject', 'yith-woocommerce-request-a-quote' ),
	),
	'block_cart'        => array(
		'name'      => esc_html__( 'Block cart content after accepting a quote', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'If enabled, the user that accepts a quote can’t add additional products to the cart or change items quantity.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_block_cart',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => ( 'yes' === $allow_add_to_cart ) ? 'no' : 'yes',
		'deps'      => array(
			'id'    => 'ywraq_show_accept_link',
			'value' => 'yes',
		),
	),

	'enable_specific_gateways' => array(
		'name'      => esc_html_x( 'Quote payment preference', 'Admin quote options name', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html_x( 'Choose which payment methods to accept for quote payments. You can allow all gateways enabled in WooCommerce or only specific ones.', 'Admin quote options description', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_enable_specific_gateways',
		'type'      => 'yith-field',
		'yith-type' => 'radio',
		'options'   => array(
			'all'      => esc_html_x( 'Through any enabled WooCommerce payment gateways', 'Admin quote options radio option', 'yith-woocommerce-request-a-quote' ),
			'specific' => esc_html_x( 'Only through specific payment methods', 'Admin quote options radio option', 'yith-woocommerce-request-a-quote' ),
		),
		'default'   => 'all',
	),

	'select_gateway' => array(
		'name'      => esc_html__( 'Accepted quote payment gateways', 'yith-woocommerce-request-a-quote' ),
		// translators: html tags.
		'desc'      => sprintf( esc_html_x( 'Choose the payment methods to accept for quote payment. %sLeave empty if the user can pay using all payment methods that are enabled in the store.', 'Placeholder is an html tag', 'yith-woocommerce-request-a-quote' ), '<br/>' ),
		'type'      => 'yith-field',
		'yith-type' => 'select',
		'id'        => 'ywraq_select_gateway',
		'class'     => 'wc-enhanced-select',
		'options'   => ywraq_get_available_gateways(),
		'multiple'  => 'true',
		'required'  => true,
		'deps'      => array(
			'id'    => 'ywraq_enable_specific_gateways',
			'value' => 'specific',
		),
	),

	'quote_settings_end' => array(
		'type' => 'sectionend',
		'id'   => 'ywraq_quote_settings_end',
	),
);

return array( 'quote-settings' => apply_filters( 'ywraq_quote_settings_options', $section ) );
