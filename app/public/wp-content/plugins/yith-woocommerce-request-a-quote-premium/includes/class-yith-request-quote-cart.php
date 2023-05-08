<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * YITH_YWRAQ_Cart class
 * @package YITH
 * @since   1.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

/**
 * Implements the YITH_YWRAQ_Cart class.
 */
if ( ! class_exists( 'YITH_YWRAQ_Cart' ) ) {

	/**
	 * Class YITH_YWRAQ_Cart
	 */
	class YITH_YWRAQ_Cart {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_YWRAQ_Cart
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_YWRAQ_Cart
		 * @since 1.0.0
		 */
		public static function get_instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  4.0
		 */
		public function __construct() {
			add_action( 'woocommerce_proceed_to_checkout', array( $this, 'show_button_on_cart' ), 30 );
		}

		/**
		 * Show the button on cart page.
		 */
		public function show_button_on_cart() {

			if ( WC()->cart && WC()->cart->is_empty() ) {
				return;
			}

			$button_style = get_option( 'ywraq_raq_checkout_button_style', 'button' );
			$label_button = get_option( 'ywraq_checkout_quote_button_label', __( 'or ask for a quote', 'yith-woocommerce-request-a-quote' ) );

			ob_start();
			if ( 'button' === $button_style ) {
				/**
				 * APPLY_FILTERS:ywraq_quote_button_cart_html
				 *
				 * Filter the HTML of "Add to quote" button
				 *
				 * @param   string  $html  Html to filter.
				 *
				 * @return string
				 */
				echo wp_kses_post( apply_filters( 'ywraq_quote_button_cart_html', '<button type="submit" class="button alt" id="ywraq_cart_quote" value="' . esc_attr( $label_button ) . '" data-value="' . esc_attr( $label_button ) . '">' . esc_html( $label_button ) . '</button>' ) );
			} else {
				echo wp_kses_post( apply_filters( 'ywraq_quote_button_cart_html', '<a href="#" class="quote-button alt" id="ywraq_cart_quote" data-value="' . esc_attr( $label_button ) . '">' . esc_html( $label_button ) . '</a>' ) );
			}

			wc_get_template( 'cart/request-quote-on-cart.php', array(), '', YITH_YWRAQ_TEMPLATE_PATH . '/' );

			echo ob_get_clean(); //phpcs:ignore
		}


	}

	/**
	 * Unique access to instance of YITH_YWRAQ_Cart class
	 *
	 * @return YITH_YWRAQ_Cart
	 */
	function yith_wraq_cart() { //phpcs:ignore
		return YITH_YWRAQ_Cart::get_instance();
	}
}
