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

return array(
	'request' => array(
		'request-options' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'request-page' => array(
					'title' => esc_html_x( 'Page Options', 'Admin title of tab', 'yith-woocommerce-request-a-quote' ),
				),
				'request-form' => array(
					'title' => esc_html_x( 'Form options', 'Admin title of tab', 'yith-woocommerce-request-a-quote' ),
				),
			),
		),
	),
);
