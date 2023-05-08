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

$builder_notice = ywraq_is_gutenberg_active() ? '' : sprintf( '<span class="ywraq-notice">%s</span>',
	_x( 'In order to use the PDF builder you need to install Gutenberg and update WordPress to the last version.', 'Admin notice', 'yith-woocommerce-request-a-quote' ) );

$section = array(

	'quote_pdf' => array(
		'name' => esc_html__( 'Quote PDF', 'yith-woocommerce-request-a-quote' ),
		'type' => 'title',
		'id'   => 'ywraq_quote_pdf',
	),

	'pdf_in_myaccount' => array(
		'name'      => esc_html__( 'Allow quotes to be downloaded as PDF', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'If enabled, users can download the quote in a PDF version from "My Account".',
			'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_pdf_in_myaccount',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
	),

	'pdf_file_name' => array(
		'name'      => esc_html__( 'PDF file name', 'yith-woocommerce-request-a-quote' ),
		'desc'      => sprintf( '%s <br> %s ',
			esc_html__( 'Enter a name to identify the PDF quote. Customers will see this name when they download or open the file. It is possible to use %quote_number% to use the number of the quote and %rand% to add a random number.', 'yith-woocommerce-request-a-quote' ),
			wp_kses_post( sprintf( __( 'All pdf documents are stored in %s.', 'placeholder is the folder of the quotes', 'yith-woocommerce-request-a-quote' ), '<code>' . YITH_YWRAQ_DOCUMENT_SAVE_DIR . '</code>' )
			) ),
		'id'        => 'ywraq_pdf_file_name',
		'type'      => 'yith-field',
		'required'  => true,
		'yith-type' => 'text',
		'default'   => 'quote_%rand%',
	),


	'hide_table_is_pdf_attachment' => array(
		'name'      => esc_html__( 'Hide product list in the email content when a PDF quote is attached', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Hide product list in the content if the PDF version is attached to the email.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_hide_table_is_pdf_attachment',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
	),

	'pdf_attachment' => array(
		'name'      => esc_html__( 'Attach a PDF version to the quote email', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'If enabled, users can download a PDF version of the quotes.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_pdf_attachment',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
	),


	'quote_pdf_end' => array(
		'type' => 'sectionend',
		'id'   => 'ywraq_quote_pdf_end',
	),

	'pdf_layout' => array(
		'name' => esc_html__( 'PDF Quote Templates', 'yith-woocommerce-request-a-quote' ),
		'type' => 'title',
		'id'   => 'ywraq_pdf_layout',
	),

	'pdf_template_to_use' => array(
		'name'      => esc_html__( 'PDF template to use', 'yith-woocommerce-request-a-quote' ),
		'desc'      => sprintf(
			'%s <br> <strong>%s</strong> %s ',
			esc_html__(
				'Choose if you want to use the default template included in the plugin, or if you want to enable the templates builder
to create a custom template for your quotes.', 'yith-woocommerce-request-a-quote'
			),
			esc_html_x( 'Note:', 'part of a sentence in the option "PDF template to use" ', 'yith-woocommerce-request-a-quote' ),
			esc_html_x( 'to enable the builder you need to enable Gutenberg.', 'part of a sentence in the option "PDF template to use" ', 'yith-woocommerce-request-a-quote' )
		),
		'id'        => 'ywraq_pdf_template_to_use',
		'type'      => 'yith-field',
		'yith-type' => 'radio',
		'options'   => array(
			'default' => esc_html__( 'Use the default template', 'yith-woocommerce-request-a-quote' ),
			'builder' => esc_html__( 'Create and choose a custom template', 'yith-woocommerce-request-a-quote' ) . wp_kses_post( $builder_notice ),
		),
		'default'   => ywraq_is_gutenberg_active() ? 'builder' : 'default',
	),

	'pdf_custom_templates' => array(
		'name'      => esc_html_x( 'Choose template', 'Admin option label', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose which template to use by default for your PDF Quotes. You can create unlimited templates in the Quote Template tab.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_pdf_custom_templates',
		'type'      => 'yith-field',
		'yith-type' => 'select',
		'options'   => YITH_YWRAQ_Post_Types::get_pdf_template_list(),
		'deps'      => array(
			'id'    => 'ywraq_pdf_template_to_use',
			'value' => 'builder',
		),
	),

	'pdf_template' => array(
		'name'      => esc_html__( 'PDF layout based on a', 'yith-woocommerce-request-a-quote' ),
		'desc'      => sprintf( '%s <br> %s',
			esc_html__( 'Table allows adding content to the HTML table.', 'yith-woocommerce-request-a-quote' ),
			esc_html__( 'DIV replaces the HTML table with DIVs (use this to avoid some issues with pagination).', 'yith-woocommerce-request-a-quote' ) ),
		'id'        => 'ywraq_pdf_template',
		'type'      => 'yith-field',
		'yith-type' => 'radio',
		'options'   => array(
			'table' => esc_html__( 'Table', 'yith-woocommerce-request-a-quote' ),
			'div'   => esc_html__( 'DIV', 'yith-woocommerce-request-a-quote' ),
		),
		'default'   => 'table',
		'deps'      => array(
			'id'    => 'ywraq_pdf_template_to_use',
			'value' => 'default',
		),
	),

	'pdf_logo' => array(
		'name'      => esc_html__( 'Logo', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Upload a logo to identify your shop in the PDF file.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_pdf_logo',
		'type'      => 'yith-field',
		'yith-type' => 'upload',
		'default'   => YITH_YWRAQ_DIR . 'assets/images/logo.jpg',
		'deps'      => array(
			'id'    => 'ywraq_pdf_template_to_use',
			'value' => 'default',
		),
	),

	'pdf_info' => array(
		'name'      => esc_html__( 'Sender info text in PDF quote', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enter the sender information that will be shown in the PDF quote.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_pdf_info',
		'type'      => 'yith-field',
		'yith-type' => 'textarea',
		'default'   => get_bloginfo( 'name' ),
		'deps'      => array(
			'id'    => 'ywraq_pdf_template_to_use',
			'value' => 'default',
		),
	),

	'show_author_quote' => array(
		'name'      => esc_html__( 'Show quote author', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enable to show information about the user that sent the quote.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_show_author_quote',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
		'deps'      => array(
			'id'    => 'ywraq_pdf_template_to_use',
			'value' => 'default',
		),
	),

	'pdf_columns' => array(
		'name'      => esc_html__( 'In product table show this info:', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose the information to show in the product list.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_pdf_columns',
		'type'      => 'yith-field',
		'yith-type' => 'select',
		'class'     => 'wc-enhanced-select',
		'multiple'  => true,
		'required'  => true,
		'options'   => array_merge(
			array(
				'all' => esc_html_x( 'All', 'show all fields', 'yith-woocommerce-request-a-quote' ),
			),
			apply_filters(
				'ywpar_pdf_columns',
				array(
					'thumbnail'        => 'Product Thumbnail',
					'product_name'     => 'Product Name',
					'unit_price'       => 'Unit Price',
					'quantity'         => 'Quantity',
					'product_subtotal' => 'Product Subtotal',
				)
			)
		),
		'default'   => array( 'all' ),
		'deps'      => array(
			'id'    => 'ywraq_pdf_template_to_use',
			'value' => 'default',
		),
	),


	'pdf_hide_total_row' => array(
		'name'      => esc_html__( 'Hide "Total Price" row', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enable to hide the "Total Price" row in the product list.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_pdf_hide_total_row',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
		'deps'      => array(
			'id'    => 'ywraq_pdf_template_to_use',
			'value' => 'default',
		),
	),

	'pdf_link' => array(
		'name'      => esc_html__( 'Show "Accept | Reject" links', 'yith-woocommerce-request-a-quote' ),
		'desc'      => sprintf( '%s <br> %s',
			esc_html__( 'Enable to add the link to accept or reject the quote into the PDF.', 'yith-woocommerce-request-a-quote' ),
			esc_html__( 'To show both links be sure to enable also the option in "Quote option" tab.', 'yith-woocommerce-request-a-quote' ) ),
		'id'        => 'ywraq_pdf_link',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'default'   => 'no',
		'deps'      => array(
			'id'    => 'ywraq_pdf_template_to_use',
			'value' => 'default',
		),
	),

	'pdf_footer_content' => array(
		'name'      => esc_html__( 'Optional text in PDF quote footer', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enter an additional text content to show in the footer area of the PDF Quote.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_pdf_footer_content',
		'type'      => 'yith-field',
		'yith-type' => 'textarea',
		'default'   => '',
		'deps'      => array(
			'id'    => 'ywraq_pdf_template_to_use',
			'value' => 'default',
		),
	),
	'pdf_pagination'     => array(
		'name'      => esc_html__( 'Enable pagination in PDF', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Enable to add pagination numbers at the end of the PDF quote, if the quote has more pages.', 'yith-woocommerce-request-a-quote' ),
		'type'      => 'yith-field',
		'id'        => 'ywraq_pdf_pagination',
		'yith-type' => 'onoff',
		'default'   => 'yes',
		'deps'      => array(
			'id'    => 'ywraq_pdf_template_to_use',
			'value' => 'default',
		),
	),

	'pdf_layout_end' => array(
		'type' => 'sectionend',
		'id'   => 'ywraq_pdf_layout_end',
	),

);

return array( 'quote-pdf' => apply_filters( 'ywraq_quote_pdf_settings_options', $section ) );
