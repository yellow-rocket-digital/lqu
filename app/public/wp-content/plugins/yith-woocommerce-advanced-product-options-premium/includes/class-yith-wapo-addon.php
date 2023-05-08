<?php
/**
 * WAPO Addon Class
 *
 * @author  Corrado Porzio <corradoporzio@gmail.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO_Addon' ) ) {

	/**
	 *  Addon class.
	 *  The class manage all the Addon behaviors.
	 */
	class YITH_WAPO_Addon {

		/**
		 *  ID
		 *
		 *  @var int
		 */
		public $id = 0;

		/**
		 *  Settings
		 *
		 *  @var array
		 */
		public $settings = array();

		/**
		 *  Options
		 *
		 *  @var array
		 */
		public $options = array();

		/**
		 *  Priority
		 *
		 *  @var int
		 */
		public $priority = 0;

		/**
		 *  Visibility
		 *
		 *  @var array
		 */
		public $visibility = 1;

		/**
		 *  Type
		 *
		 *  @var string
		 */
		public $type = 0;

		/**
		 *  Constructor
		 *
		 * @param int $id Addon ID.
		 */
		public function __construct( $id ) {

			global $wpdb;

			if ( $id > 0 ) {

				$query = "SELECT * FROM {$wpdb->prefix}yith_wapo_addons WHERE id='$id'";
				$row   = $wpdb->get_row( $query ); // phpcs:ignore

				if ( isset( $row ) && $row->id === (string) $id ) {

					$this->id         = $row->id;
					$this->settings   = @unserialize( $row->settings ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize, WordPress.PHP.NoSilencedErrors.Discouraged
					$this->options    = @unserialize( $row->options ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize, WordPress.PHP.NoSilencedErrors.Discouraged
					$this->priority   = $row->priority;
					$this->visibility = $row->visibility;

					// Settings.
					$this->type = isset( $this->settings['type'] ) ? $this->settings['type'] : 'html_text';

				}
			}

		}

		/**
		 *  Get Setting
		 *
		 * @param string  $option    Option name.
		 * @param string  $default   Default value.
		 * @param boolean $translate Translate the setting or not.
		 */
		public function get_setting( $option, $default = '', $translate = true ) {

			$value = isset( $this->settings[ $option ] ) && ! empty( $this->settings[ $option ] ) ? $this->settings[ $option ] : $default;
			if ( is_string( $value ) && YITH_WAPO::$is_wpml_installed && $translate ) {
				$value = YITH_WAPO_WPML::string_translate( $value );
			}
			/**
			 * APPLY_FILTER: yith_wapo_get_addon_'. $option. '_settings
			 *
			 * Get setting of a specific option of an add-on..
			 *
			 * @param string $value      the value
			 * @param string  $option    the option
			 * @param string  $default   the default value
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wapo_get_addon_' . $option . '_settings', $value, $option, $default );
		}

		/**
		 *  Get Option
		 *
		 * @param string $option Option name.
		 * @param int    $index Option index.
		 * @param string $default Default value.
		 * @param bool   $translate Translate the option or not.
		 */
		public function get_option( $option, $index, $default = '', $translate = true ) {
			$index = is_numeric( $index ) ? $index : 0;
			if ( is_array( $this->options )
				&& isset( $this->options[ $option ] )
				&& is_array( $this->options[ $option ] )
				&& isset( $this->options[ $option ][ $index ] ) ) {
				if ( YITH_WAPO::$is_wpml_installed && $translate ) {
					return YITH_WAPO_WPML::string_translate( $this->options[ $option ][ $index ] );
				}
				$option_to_return = $this->options[ $option ][ $index ];
				return apply_filters( 'yith_wapo_get_option', $option_to_return, $option );
			}
			return $default;
		}

		/**
		 *  Get Option Price HTML
		 *
		 * @param int $index Option index.
		 */
		public function get_option_price_html( $index, $currency = false ) {
			$html_price    = '';
			$product_price = YITH_WAPO_Front()->current_product_price;

			$price_method = $this->get_option( 'price_method', $index, 'free', false );
			$price_type   = $this->get_option( 'price_type', $index, 'fixed', false );

			$option_price      = $this->get_price( $index );
			$option_price_sale = $this->get_sale_price( $index );
			$option_price      = floatval( str_replace( ',', '.', $option_price ) );
			$option_price_sale = '' !== $option_price_sale ? floatval( str_replace( ',', '.', $option_price_sale ) ) : '';

			if ( 'free' !== $price_method ) {
				if ( 'percentage' === $price_type ) {
					$option_percentage      = $option_price;
					$option_percentage_sale = $option_price_sale;
					$option_price           = ( $product_price / 100 ) * $option_percentage;
					$option_price_sale      = $option_percentage && $option_percentage_sale > 0 ? ( $product_price / 100 ) * $option_percentage_sale : '';
				} elseif ( 'multiplied' === $price_type ) {
					$option_price      = $this->get_price( $index );
					$option_price_sale = '';
				}

				$sign       = '+';
				$sign_class = 'positive';
				if ( $this->get_option( 'price_method', $index, 'free', false ) === 'decrease' ) {
					$sign              = '-';
					$sign_class        = 'negative';
					$option_price_sale = '';
				}

				$sign = apply_filters( 'yith_wapo_price_sign', $sign );

				if ( '' !== $option_price ) {

					$option_price      = apply_filters( 'yith_wapo_option_price', $option_price );
					$option_price_sale = apply_filters( 'yith_wapo_option_price_sale', $option_price_sale );
					if ( '' !== $option_price_sale && floatval( $option_price_sale ) >= 0 ) {
						$html_price = '<small class="option-price"><span class="brackets">(</span><span class="sign ' . $sign_class . '">' . $sign . '</span><del>' . wc_price( $option_price, array( 'currency' => $currency ) ) . '</del> ' . wc_price( $option_price_sale, array( 'currency' => $currency ) ) . '<span class="brackets">)</span></small>';
					} else {
						$html_price = '<small class="option-price"><span class="brackets">(</span><span class="sign ' . $sign_class . '">' . $sign . '</span>' . wc_price( $option_price, array( 'currency' => $currency ) ) . '<span class="brackets">)</span></small>';
					}
				}
			}
			return apply_filters( 'yith_wapo_option_' . $this->id . '_' . $index . '_price_html', $html_price );
		}

		/**
		 *  Get add-on price.
		 *
		 * @param int     $index Option index.
		 * @param boolean $calculate_taxes Calculate the taxes of the prices.
		 * @return float
		 */
		public function get_price( $index, $calculate_taxes = true ) {

			$price        = $this->get_option( 'price', $index );
			$price_method = $this->get_option( 'price_method', $index, 'free', false );
			$price_type   = $this->get_option( 'price_type', $index, 'fixed', false );
			if ( $calculate_taxes ) {
				$price = YITH_WAPO_Premium::get_instance()->calculate_price_depending_on_tax( $price );
			}

			return apply_filters( 'yith_wapo_get_addon_price', $price, false, $price_method, $price_type, $index );
		}

		/**
		 *  Get add-on sale price.
		 *
		 * @param int     $index Option index.
		 * @param boolean $calculate_taxes Calculate the taxes of the prices.
		 * @return float
		 */
		public function get_sale_price( $index, $calculate_taxes = true ) {

			$sale_price   = $this->get_option( 'price_sale', $index );
			$price_method = $this->get_option( 'price_method', $index, 'free', false );
			$price_type   = $this->get_option( 'price_type', $index, 'fixed', false );
			if ( $calculate_taxes ) {
				$sale_price = YITH_WAPO_Premium::get_instance()->calculate_price_depending_on_tax( $sale_price );
			}

			return apply_filters( 'yith_wapo_get_addon_sale_price', $sale_price, false, $price_method, $price_type, $index );
		}

	}

}
