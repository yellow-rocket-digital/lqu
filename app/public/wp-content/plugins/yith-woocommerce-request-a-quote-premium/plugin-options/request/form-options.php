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

$section  = array(
	'form_settings' => array(
		'name' => esc_html__( '"Request a quote" Form', 'yith-woocommerce-request-a-quote' ),
		'type' => 'title',
		'id'   => 'ywraq_form_settings',
	),

	'inquiry_form' => array(
		'name'      => esc_html__( 'Choose the form to show in "Request a quote" page', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose the form to show to request quote details. You can also add Contact Form 7, Gravity Form, Ninja forms or WPForms, which must be installed and activated.', 'yith-woocommerce-request-a-quote' ),
		'type'      => 'yith-field',
		'yith-type' => 'select',
		'class'     => 'wc-enhanced-select',
		'default'   => 'default',
		'options'   => apply_filters(
			'ywraq_form_type_list',
			array(
				'default' => esc_html__( 'Default', 'yith-woocommerce-request-a-quote' ),
			)
		),
		'id'        => 'ywraq_inquiry_form_type',
	),

);
$section  = apply_filters( 'ywraq_additional_form_options', $section );
$section2 = array(
	// @since 3.0.0
	'default_table_form_title'   => array(
		'id'        => 'ywraq_default_table_form_title',
		'type'      => 'yith-field',
		'yith-type' => 'title',
		'desc'      => esc_html_x( 'Default form fields', 'Admin options title', 'yith-woocommerce-request-a-quote' ),
		'deps'      => array(
			'id'    => 'ywraq_inquiry_form_type',
			'value' => 'default',
		),
	),
	'default_table_form'         => array(
		'id'                    => 'ywraq_default_table_form',
		'type'                  => 'yith-field',
		'yith-type'             => 'default-form',
		'yith-display-row'      => false,
		'callback_default_form' => 'ywraq_get_default_form_fields',
		'custom_attributes'     => array(
			'data-deps'       => 'ywraq_inquiry_form_type',
			'data-deps_value' => 'default',
		),
	),
	'default_form_title_options' => array(
		'id'        => 'ywraq_default_form_title_options',
		'type'      => 'yith-field',
		'yith-type' => 'title',
		'desc'      => esc_html_x( 'Default form options', 'Admin options title', 'yith-woocommerce-request-a-quote' ),
		'deps'      => array(
			'id'    => 'ywraq_inquiry_form_type',
			'value' => 'default',
		),
	),
	'user_registration'          => array(
		'name'      => esc_html__( 'User registration', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose whether to register the user or make this optional.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_user_registration',
		'type'      => 'yith-field',
		'yith-type' => 'radio',
		'options'   => array(
			'none'   => esc_html__( 'Don\'t show a registration option in this form', 'yith-woocommerce-request-a-quote' ),
			'enable' => esc_html__( 'Show an optional checkbox to allow registration', 'yith-woocommerce-request-a-quote' ),
			'force'  => esc_html__( 'Force user registration', 'yith-woocommerce-request-a-quote' ),
		),
		'deps'      => array(
			'id'    => 'ywraq_inquiry_form_type',
			'value' => 'default',
			'type'  => 'hide',
		),
		'default'   => 'none',
	),

	'reCAPTCHA' => array(
		'name'      => esc_html__( 'Add a reCAPTCHA to the default form', 'yith-woocommerce-request-a-quote' ),
		// translators: html tags.
		'desc'      => sprintf( esc_html_x( 'Enable to add reCAPTCHA option in default form. %1$s To start using reCAPTCHA, you need to %2$s sign up for an API key %3$s pair for your site.', 'string with placeholder do not translate or remove it', 'yith-woocommerce-request-a-quote' ), '<br>',
			'<a href="https://www.google.com/recaptcha/admin">', '</a>' ),
		'id'        => 'ywraq_reCAPTCHA',
		'class'     => 'field_with_deps',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'deps'      => array(
			'id'    => 'ywraq_inquiry_form_type',
			'value' => 'default',
			'type'  => 'hide',
		),
		'default'   => 'no',
	),

	'reCAPTCHA_version'   => array(
		'name'              => esc_html__( 'Choose the reCAPTCHA version', 'yith-woocommerce-request-a-quote' ),
		// translators: html tags.
		'desc'              => sprintf( esc_html_x( 'Select the reCAPTCHA version.', 'string with placeholder do not translate or remove it', 'yith-woocommerce-request-a-quote' ), '<br>', '<a href="https://www.google.com/recaptcha/admin">', '</a>' ),
		'id'                => 'ywraq_reCAPTCHA_version',
		'type'              => 'yith-field',
		'yith-type'         => 'radio',
		'options'           => array(
			'v2' => esc_html_x( 'v2', 'reCAPTCHA version in admin options', 'yith-woocommerce-request-a-quote' ),
			'v3' => esc_html_x( 'v3 - Invisible', 'reCAPTCHA version in admin options', 'yith-woocommerce-request-a-quote' ),
		),
		'custom_attributes' => array(
			'data-deps'       => 'ywraq_inquiry_form_type,ywraq_reCAPTCHA',
			'data-deps_value' => 'default,yes',
		),
		'default'           => 'v2',
	),

	// @since 1.9.0
	'reCAPTCHA_sitekey'   => array(
		'name'              => esc_html__( 'Site key', 'yith-woocommerce-request-a-quote' ),
		'desc'              => esc_html__( 'Enter the reCAPTCHA site key', 'yith-woocommerce-request-a-quote' ),
		'id'                => 'ywraq_reCAPTCHA_sitekey',
		'type'              => 'yith-field',
		'yith-type'         => 'text',
		'required'          => true,
		'default'           => '',
		'custom_attributes' => array(
			'data-deps'       => 'ywraq_inquiry_form_type,ywraq_reCAPTCHA',
			'data-deps_value' => 'default,yes',
		),
	),
	// @since 1.9.0
	'reCAPTCHA_secretkey' => array(
		'name'              => esc_html__( 'Secret key', 'yith-woocommerce-request-a-quote' ),
		'desc'              => esc_html__( 'Enter reCAPTCHA secret key', 'yith-woocommerce-request-a-quote' ),
		'id'                => 'ywraq_reCAPTCHA_secretkey',
		'class'             => 'regular-input',
		'type'              => 'yith-field',
		'yith-type'         => 'text',
		'required'          => true,
		'default'           => '',
		'custom_attributes' => array(
			'data-deps'       => 'ywraq_inquiry_form_type,ywraq_reCAPTCHA',
			'data-deps_value' => 'default,yes',
		),
	),

	'autocomplete_default_form' => array(
		'name'      => esc_html__( 'Autocomplete Form', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'If enabled, the fields connected to WooCommerce will be filled automatically.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_autocomplete_default_form',
		'type'      => 'yith-field',
		'yith-type' => 'onoff',
		'deps'      => array(
			'id'    => 'ywraq_inquiry_form_type',
			'value' => 'default',
			'type'  => 'hide',
		),
		'default'   => 'no',
	),

	'data_format_datepicker' => array(
		'name'      => esc_html__( 'Date picker format', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose the format for the date picker in the default form.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_date_format_datepicker',
		'type'      => 'yith-field',
		'yith-type' => 'date-format',
		'js'        => true,
		'default'   => 'dd/mm/yy',
		'deps'      => array(
			'id'    => 'ywraq_inquiry_form_type',
			'value' => 'default',
		),
	),

	'time-format-datepicker' => array(
		'name'      => esc_html__( 'Time picker format', 'yith-woocommerce-request-a-quote' ),
		'desc'      => esc_html__( 'Choose the format for the time picker in default form.', 'yith-woocommerce-request-a-quote' ),
		'id'        => 'ywraq_time_format_datepicker',
		'type'      => 'yith-field',
		'yith-type' => 'radio',
		'options'   => array(
			'12' => date( 'h:i A', current_time( 'timestamp', 0 ) ), //phpcs:ignore
			'24' => date( 'H:i', current_time( 'timestamp', 0 ) ),  //phpcs:ignore
		),
		'deps'      => array(
			'id'    => 'ywraq_inquiry_form_type',
			'value' => 'default',
		),
		'default'   => '24',
	),

	'form_settings_end' => array(
		'type' => 'sectionend',
		'id'   => 'ywraq_form_settings_end',
	),

);


return array( 'request-form' => apply_filters( 'ywraq_request_form_settings_options', array_merge( $section, $section2 ) ) );
