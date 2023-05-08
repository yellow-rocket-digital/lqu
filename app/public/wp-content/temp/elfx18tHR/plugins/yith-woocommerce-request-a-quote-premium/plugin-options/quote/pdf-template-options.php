<?php
/**
 * Plugin Options
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH WooCommerce Request a Quote
 */

defined( 'ABSPATH' ) || exit;


return array(
	'quote-pdf-template' => array(
		'quote-pdf-template_list_table' => array(
			'type'          => 'post_type',
			'post_type'     => YITH_YWRAQ_Post_Types::$pdf_template,
			'wp-list-style' => 'boxed',
		),
	),
);
