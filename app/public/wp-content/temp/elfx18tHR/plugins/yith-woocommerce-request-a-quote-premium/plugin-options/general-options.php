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
	'general' => array(
		'general-options' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'general-settings'     => array(
					'title' => esc_html_x( 'General Options', 'Admin title of tab', 'yith-woocommerce-request-a-quote' ),
				),
				'general-button-label' => array(
					'title' => esc_html_x( 'Buttons & Labels', 'Admin title of tab', 'yith-woocommerce-request-a-quote' ),
				),
			),
		),
	),
);
