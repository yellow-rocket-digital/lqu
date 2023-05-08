<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Implements the YITH_YWRAQ_Gateway class.
 *
 * @class   YITH_YWRAQ_Gateway
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Request A Quote Premium
 */

defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'YITH_YWRAQ_Gateway' ) ) {
	/**
	 * Class YITH_YWRAQ_Gateway
	 */
	class YITH_YWRAQ_Gateway extends WC_Payment_Gateway {
		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			$this->id                 = 'yith-request-a-quote';
			$this->has_fields         = false;
			$this->title              = apply_filters( 'ywraq_payment_method_label', esc_html__( 'YITH Request a Quote', 'yith-woocommerce-request-a-quote' ) );
			$this->method_title       = apply_filters( 'ywraq_payment_method_label', esc_html__( 'YITH Request a Quote', 'yith-woocommerce-request-a-quote' ) );
			$this->method_description = esc_html__( 'Allows to request a quote at checkout.', 'yith-woocommerce-request-a-quote' );
			$this->description        = '';
			$this->enabled            = 'yes';
		}
	}
}
