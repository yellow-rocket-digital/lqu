<?php
/**
 * WAPO WPML Class
 *
 * @author  Corrado Porzio <corradoporzio@gmail.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO_WPML' ) ) {

	/**
	 * WPML Class
	 */
	class YITH_WAPO_WPML {
		/**
		 * Single instance of the class
		 *
		 * @var YITH_WAPO_WPML
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WAPO_WPML
		 */
		public static function get_instance() {
			$self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

			return ! is_null( $self::$instance ) ? $self::$instance : $self::$instance = new $self();
		}

		/**
		 * Constructor
		 */
		private function __construct() {

			add_filter( 'yith_wapo_get_original_product_id', array( $this, 'get_parent_id' ) );
			add_filter( 'yith_wapo_get_original_category_ids', array( $this, 'get_original_category_ids' ), 10, 3 );
			add_filter( 'yith_wapo_conditional_rule_variation', array( $this, 'display_variation_based_current_language' ) );
			add_filter( 'yith_wapo_addon_product_id', array( $this, 'get_translated_product_id' ) );

		}

		/**
		 * Register string
		 *
		 * @param string $string String.
		 * @param string $name Name.
		 */
		public static function register_string( $string, $name = '' ) {
			if ( ! $name ) {
				$name = sanitize_title( $string );
			}
			$name_slug = substr( '[' . YITH_WAPO_LOCALIZE_SLUG . ']' . $name, 0, 150 );
			yith_wapo_wpml_register_string( YITH_WAPO_WPML_CONTEXT, $name_slug, $string );
		}

		/**
		 * String translate
		 *
		 * @param string $label Label.
		 * @param string $name Name.
		 * @return string
		 */
		public static function string_translate( $label, $name = '' ) {
			if ( is_string( $label ) ) {
				if ( ! $name ) {
					$name = sanitize_title( $label );
				}
				$name_slug = substr( '[' . YITH_WAPO_LOCALIZE_SLUG . ']' . $name, 0, 150 );
				return yit_wpml_string_translate( YITH_WAPO_WPML_CONTEXT, $name_slug, $label );
			}
			return $label;
		}

		/**
		 * Register Option Type
		 *
		 * @param string $title Title.
		 * @param string $description Description.
		 * @param array  $options Options.
		 * @param string $html_text Options.
		 * @param string $html_heading_text Options.
		 */
		public static function register_option_type( $title, $description, $options, $html_text, $html_heading_text ) {

			self::register_string( $title );
			self::register_string( $description );
			self::register_string( $html_text );
			self::register_string( $html_heading_text );

			if ( isset( $options ) ) {

				$options = maybe_unserialize( $options );

				if ( ! is_array( $options ) || ! ( isset( $options['label'] ) ) || count( $options['label'] ) <= 0 ) {
					return;
				}

				$options['label']       = isset( $options['label'] ) ? array_map( 'stripslashes', $options['label'] ) : array();
				$options['description'] = isset( $options['description'] ) ? array_map( 'stripslashes', $options['description'] ) : array();
				$options['placeholder'] = isset( $options['placeholder'] ) ? array_map( 'stripslashes', $options['placeholder'] ) : array();
				$options['tooltip']     = isset( $options['tooltip'] ) ? array_map( 'stripslashes', $options['tooltip'] ) : array();

				$options_count = count( $options['label'] );
				for ( $i = 0; $i < $options_count; $i ++ ) {
					if ( isset( $options['label'][ $i ] ) ) {
						self::register_string( $options['label'][ $i ] );
					}
					if ( isset( $options['description'][ $i ] ) ) {
						self::register_string( $options['description'][ $i ] );
					}
					if ( isset( $options['placeholder'][ $i ] ) ) {
						self::register_string( $options['placeholder'][ $i ] );
					}
					if ( isset( $options['tooltip'][ $i ] ) ) {
						self::register_string( $options['tooltip'][ $i ] );
					}
				}
			}

		}

		/**
		 * Retrieve the WPML parent product id
		 *
		 * @param int $id ID.
		 *
		 * @return int
		 */
		public function get_parent_id( $id ) {
			/**
			 * WPML Post Translations
			 *
			 * @var WPML_Post_Translation $wpml_post_translations
			 */
			global $wpml_post_translations;

			$parent_id = ! ! $wpml_post_translations ? $wpml_post_translations->get_original_element( $id ) : false;

			if ( $parent_id ) {
				$id = $parent_id;
			}

			return $id;
		}

		/**
		 * Retrieve the WPML parent category ids
		 *
		 * @param Array      $categories Categories.
		 * @param WC_Product $product Product.
		 * @param int        $product_id Parent product id.
		 * @return array
		 */
		public function get_original_category_ids( $categories, $product, $product_id ) {

			if ( $product_id !== $product->get_id() ) {
				$original_categories = array();
				$default_language    = apply_filters( 'wpml_default_language', null );
				foreach ( $categories as $id ) {
					$original_categories[] = apply_filters( 'wpml_object_id', $id, 'product_cat', true, $default_language );
				}
				if ( ! empty( $original_categories ) ) {
					$categories = $original_categories;
				}
			}
			return $categories;
		}

		/**
		 * Retrieve current variation translation product
		 *
		 * @param Array $conditional_rule_addon conditional rules.
		 * @return array
		 */
		public function display_variation_based_current_language( $conditional_rule_addon ) {

			$my_current_lang = apply_filters( 'wpml_current_language', null );
			$my_default_lang = apply_filters( 'wpml_default_language', null );

			if ( $my_current_lang !== $my_default_lang ) {
				if ( ! empty( $conditional_rule_addon ) ) {
					$new_conditional_rule_array = array();
					foreach ( $conditional_rule_addon as $variation_id ) {
						$variation_id                 = apply_filters( 'wpml_object_id', $variation_id, 'post' );
						$new_conditional_rule_array[] = $variation_id;
					}
					$conditional_rule_addon = $new_conditional_rule_array;
				}
			}
			return $conditional_rule_addon;
		}
		/**
		 * Retrieve product id in current language
		 *
		 * @param int $product_id Product id.
		 * @return int
		 */
		public function get_translated_product_id( $product_id ) {

			if ( $product_id ) {
				$my_current_lang = apply_filters( 'wpml_current_language', null );
				$my_default_lang = apply_filters( 'wpml_default_language', null );
				if ( $my_current_lang !== $my_default_lang ) {
					$product_id = apply_filters( 'wpml_object_id', $product_id, 'post' );
				}
			}
			return $product_id;
		}
	}
}
