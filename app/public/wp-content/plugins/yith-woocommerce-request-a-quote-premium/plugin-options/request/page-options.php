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
	'page_settings' => array(
		'name' => esc_html__( '"Request quote" Page Options', 'yith-woocommerce-request-a-quote' ),
		'type' => 'title',
		'id'   => 'ywraq_page_settings',
	),
	'page_id'       => array(
		'name' => esc_html__( '"Request a quote" page', 'yith-woocommerce-request-a-quote' ),
		'desc' => sprintf(
			'%s<br/>%s<br/>%s',
			esc_html__( 'Choose from this list the page on which users will see the list of products added to the quote and send the request.', 'yith-woocommerce-request-a-quote' ),
			esc_html__( 'Please note: if you choose a page different from the default one (request quote) you need to insert', 'yith-woocommerce-request-a-quote' ),
			esc_html__( 'in the page the following shortcode: [yith_ywraq_request_quote] ', 'yith-woocommerce-request-a-quote' )
		),

		'id'       => 'ywraq_page_id',
		'type'     => 'single_select_page',
		'class'    => 'wc-enhanced-select',
		'css'      => 'min-width:300px',
		'desc_tip' => false,
	),

	'html_create_page' => array(
		'type'             => 'yith-field',
		'yith-type'        => 'html',
		'yith-display-row' => false,
		'html'             => sprintf(
			'<div class="ywraq-create-page">%s <a href="%s">%s</a></div>',
			esc_html_x( 'or', 'part of the string (or Create a page) inside admin panel', 'yith-woocommerce-request-a-quote' ),
			esc_url( admin_url( 'post-new.php?post_type=page' ) ),
			esc_html__( 'Create a page', 'yith-woocommerce-request-a-quote' )
		),
	),

	'page_list_layout_template' => array(
		'name'      => esc_html__( 'Page Layout', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose the layout for "Request a quote" page.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_page_list_layout_template',
		'type'      => 'yith-field',
		'yith-type' => 'radio',
		'options'   => array(
			'wide'     => esc_html__( 'Product list on left side, form on right side', 'yith-woocommerce-request-a-quote' ),
			'vertical' => esc_html__( 'Product list above, form below', 'yith-woocommerce-request-a-quote' ),
		),
		'default'   => 'vertical',
	),

	'show_form_with_empty_list' => array(
		'name'      => esc_html__( 'Show form even with empty list', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enable to show the form in request quote page also with an empty list of products.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_show_form_with_empty_list',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
	),

	'title_before_form' => array(
		'name'      => esc_html__( 'Title before form', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enter an optional title to show above the form.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_title_before_form',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'default'   => apply_filters( 'ywraq_form_title', __( 'Send the request', 'yith-woocommerce-request-a-quote' ) ),
	),


	'product_table_show' => array(
		'name'      => esc_html__( 'In product table, show:', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose which info to show in the product table.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_product_table_show',
		'type'      => 'yith-field',
		'yith-type' => 'checkbox-array',
		'options'   => array(
			'images'       => esc_html__( 'Product images', 'yith-woocommerce-request-a-quote' ),
			'single_price' => esc_html__( 'Product prices', 'yith-woocommerce-request-a-quote' ),
			'sku'          => esc_html__( 'Product SKU', 'yith-woocommerce-request-a-quote' ),
			'quantity'     => esc_html__( 'Quantity', 'yith-woocommerce-request-a-quote' ),
			'line_total'   => esc_html__( 'Total amount of single products', 'yith-woocommerce-request-a-quote' ),
			'total'        => esc_html__( 'Total amount of all products', 'yith-woocommerce-request-a-quote' ),
			'tax'          => esc_html__( 'Taxes', 'yith-woocommerce-request-a-quote' ),
		),
		'default'   => array( 'images', 'line_total', 'quantity' ),
	),

	'show_return_to_shop' => array(
		'name'      => esc_html__( 'Show "Return to Shop" button', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enable to show the "Return to shop" button.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_show_return_to_shop',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'yes',
	),

	'return_to_shop_label' => array(
		'name'      => esc_html__( '"Return to Shop" label', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enter the button\'s label', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_return_to_shop_label',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'required'  => true,
		'deps'      => array(
			'id'    => 'ywraq_show_return_to_shop',
			'value' => 'yes',
		),
		'default'   => esc_html__( 'Return to Shop', 'yith-woocommerce-request-a-quote' ),
	),

	'return_to_shop_url_choice' => array(
		'name'      => esc_html__( '"Return to Shop" URL', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose the URL to assign to the button.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_return_to_shop_url_choice',
		'type'      => 'yith-field',
		'yith-type' => 'radio',
		'options'   => array(
			'wc-shop' => __( 'WooCommerce Shop page', 'yith-woocommerce-request-a-quote' ),
			'custom'  => __( 'Custom URL', 'yith-woocommerce-request-a-quote' ),
		),
		'deps'      => array(
			'id'    => 'ywraq_show_return_to_shop',
			'value' => 'yes',
		),
		'default'   => get_option( 'ywraq_return_to_shop_after_sent_the_request_url' ) ? 'custom' : 'wc-shop',
	),

	'return_to_shop_url' => array(
		'name'              => '',
		'desc'              => esc_html__( 'Enter the URL to assign to the button.', 'yith-woocommerce-request-a-quote' ),
		'id'                => 'ywraq_return_to_shop_url',
		'type'              => 'yith-field',
		'yith-type'         => 'text',
		'required'          => true,
		'custom_attributes' => array(
			'data-deps'       => 'ywraq_show_return_to_shop,ywraq_return_to_shop_url_choice',
			'data-deps_value' => 'yes,custom',
		),
		'default'           => get_permalink( wc_get_page_id( 'shop' ) ),
	),

	'show_update_list' => array(
		'name'      => esc_html__( 'Show "Update List" button', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enable to show the "Update list" button.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_show_update_list',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'yes',
	),

	'update_list_label' => array(
		'name'      => esc_html__( '"Update List" label', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enter the button\'s label.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_update_list_label',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'deps'      => array(
			'id'    => 'ywraq_show_update_list',
			'value' => 'yes',
		),
		'default'   => esc_html__( 'Update List', 'yith-woocommerce-request-a-quote' ),
	),

	'clear_list_button' => array(
		'name'      => esc_html__( 'Show "Clear list" button', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enable to show the "Clear list" button.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_show_clear_list_button',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
	),

	'clear_list_label' => array(
		'name'      => esc_html__( '"Clear List" label', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enter the button\'s label.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_clear_list_label',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'deps'      => array(
			'id'    => 'ywraq_show_clear_list_button',
			'value' => 'yes',
		),
		'default'   => esc_html__( 'Clear List', 'yith-woocommerce-request-a-quote' ),
	),

	'show_download_pdf_on_request' => array(
		'name'      => esc_html__( 'Show “View PDF” button', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enable to allow users to download the products list in a PDF file.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_show_download_pdf_on_request',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
	),

	'show_download_pdf_on_request_label' => array(
		'name'      => esc_html__( '“View PDF” label', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enter the button\'s label.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_show_download_pdf_on_request_label',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'deps'      => array(
			'id'    => 'ywraq_show_download_pdf_on_request',
			'value' => 'yes',
		),
		'default'   => esc_html_x( 'View PDF', 'Admin option label for button to make a PDF on Request a quote page', 'yith-woocommerce-request-a-quote' ),
	),
	'download_pdf_on_request_logo'       => array(
		'name'      => esc_html__( 'Logo', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Upload a logo to identify your shop in the PDF file.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_download_pdf_on_request_logo',
		'type'      => 'yith-field',
		'yith-type' => 'upload',
		'default'   => YITH_YWRAQ_DIR . 'assets/images/logo.jpg',
		'deps'      => array(
			'id'    => 'ywraq_show_download_pdf_on_request',
			'value' => 'yes',
		),
	),
	'how_show_after_sent_the_request'    => array(
		'name'      => esc_html__( 'After request sending, show:', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose what to show after a quote request has been sent.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_how_show_after_sent_the_request',
		'type'      => 'yith-field',
		'yith-type' => 'radio',
		'options'   => array(
			'simple_message'  => esc_html__( 'A simple text message', 'yith-woocommerce-request-a-quote' ),
			'thank_you_quote' => esc_html__( 'A detail page of quote request', 'yith-woocommerce-request-a-quote' ),
			'thank_you_page'  => esc_html__( 'A specific "Thank you" page', 'yith-woocommerce-request-a-quote' ),
		),
		'default'   => 'simple_message',
	),

	'message_after_sent_the_request' => array(
		'name'      => esc_html__( 'Text to show after request sending', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose what message to show to the user after the request is sent. It is possible to use %quote_number% to show the link to the quote details.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_message_after_sent_the_request',
		'type'      => 'yith-field',
		'yith-type' => 'textarea',
		'default'   => esc_html__( 'Your request has been sent successfully. You can see details at: %quote_number%', 'yith-woocommerce-request-a-quote' ),
		'deps'      => array(
			'id'    => 'ywraq_how_show_after_sent_the_request',
			'value' => 'simple_message',
		),
	),

	'return_to_shop_after_sent_the_request' => array(
		'name'      => esc_html__( '“Return to shop” label after request sending', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enter the button\'s label', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_return_to_shop_after_sent_the_request',
		'type'      => 'yith-field',
		'yith-type' => 'text',
		'deps'      => array(
			'id'    => 'ywraq_how_show_after_sent_the_request',
			'value' => 'simple_message',
		),
		'default'   => esc_html__( 'Return to Shop', 'yith-woocommerce-request-a-quote' ),
	),

	'return_to_shop_after_sent_the_request_url_choice' => array(
		'name'      => esc_html__( '"Return to Shop" URL', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose the URL to assign to the button.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_return_to_shop_after_sent_the_request_url_choice',
		'type'      => 'yith-field',
		'yith-type' => 'radio',
		'options'   => array(
			'wc-shop' => __( 'WooCommerce Shop page', 'yith-woocommerce-request-a-quote' ),
			'custom'  => __( 'Custom URL', 'yith-woocommerce-request-a-quote' ),
		),
		'deps'      => array(
			'id'    => 'ywraq_how_show_after_sent_the_request',
			'value' => 'simple_message',
		),
		'default'   => get_option( 'ywraq_return_to_shop_after_sent_the_request_url' ) ? 'custom' : 'wc-shop'
	),

	'return_to_shop_after_sent_the_request_url' => array(
		'name'              => '',
		'desc'              => esc_html__( 'Enter the URL to assign to the button.', 'yith-woocommerce-request-a-quote' ),
		'id'                => 'ywraq_return_to_shop_after_sent_the_request_url',
		'type'              => 'yith-field',
		'yith-type'         => 'text',
		'required'          => true,
		'custom_attributes' => array(
			'data-deps'       => 'ywraq_how_show_after_sent_the_request,ywraq_return_to_shop_after_sent_the_request_url_choice',
			'data-deps_value' => 'simple_message,custom',
		),
		'default'           => get_permalink( wc_get_page_id( 'shop' ) ),
	),

	'thank_you_page' => array(
		'name'    => esc_html__( 'Choose the "Thank you" page', 'yith-woocommerce-request-a-quote' ),
		'desc'    => esc_html__( 'Choose the page to show to the user after the request is sent.', 'yith-woocommerce-request-a-quote' ),
		'id'      => 'ywraq_thank_you_page',
		'type'    => 'single_select_page',
		'default' => '',
		'class'   => 'wc-enhanced-select',
		'css'     => 'min-width:300px',
		'deps'    => array(
			'id'    => 'ywraq_how_show_after_sent_the_request',
			'value' => 'thank_you_page',
		),
	),

	'page_settings_end' => array(
		'type' => 'sectionend',
		'id'   => 'ywraq_page_settings_end',
	),
);

return array( 'request-page' => apply_filters( 'ywraq_request_page_settings_options', $section ) );
