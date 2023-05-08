<?php
/**
 * WAPO Frontend Class
 *
 * @author  Corrado Porzio <corradoporzio@gmail.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO_Front' ) ) {

	/**
	 *  Front class.
	 *  The class manage all the frontend behaviors.
	 */
	class YITH_WAPO_Front {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WAPO_Front
		 */
		protected static $instance;

		/**
		 * Current product price
		 *
		 * @var float
		 */
		public $current_product_price = 0;

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WAPO_Front | YITH_WAPO_Front_Premium
		 */
		public static function get_instance() {
			$self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

			return ! is_null( $self::$instance ) ? $self::$instance : $self::$instance = new $self();
		}

		/**
		 * Constructor
		 */
		public function __construct() {

			// Enqueue scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Print Options.
			if ( get_option( 'yith_wapo_options_position' ) === 'after' ) {
				add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'print_container' ) );
			} else { // Default.
				add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'print_container' ) );
			}

			add_action( 'yith_gift_cards_template_before_add_to_cart_button', array( $this, 'print_container' ) );

			// Ajax live print.
			add_action( 'wp_ajax_live_print_blocks', array( $this, 'live_print_blocks' ) );
			add_action( 'wp_ajax_nopriv_live_print_blocks', array( $this, 'live_print_blocks' ) );
			// Ajax upload file.
			add_action( 'wp_ajax_upload_file', array( $this, 'ajax_upload_file' ) );
			add_action( 'wp_ajax_nopriv_upload_file', array( $this, 'ajax_upload_file' ) );
			// Ajax update product price.
			add_action( 'wp_ajax_update_totals_with_suffix', array( $this, 'update_totals_with_suffix' ) );
			add_action( 'wp_ajax_nopriv_update_totals_with_suffix', array( $this, 'update_totals_with_suffix' ) );

			// Ajax update default product price.
			add_action( 'wp_ajax_get_default_variation_price', array( $this, 'get_default_variation_price' ) );
			add_action( 'wp_ajax_nopriv_get_default_variation_price', array( $this, 'get_default_variation_price' ) );

			// Shortcodes.
			add_shortcode( 'yith_wapo_show_options', array( $this, 'yith_wapo_show_options_shortcode' ) );
			add_action( 'yith_wapo_show_options_shortcode', array( $this, 'print_container' ) );

		}

		/**
		 * Front enqueue scripts
		 */
		public function enqueue_scripts() {

			if ( apply_filters( 'yith_wapo_enqueue_front_scripts', true ) ) {

				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

				// CSS.
				wp_enqueue_style( 'yith_wapo_front', YITH_WAPO_URL . 'assets/css/front.css', false, YITH_WAPO_SCRIPT_VERSION );
				wp_enqueue_style( 'yith_wapo_jquery-ui', YITH_WAPO_URL . 'assets/css/_new_jquery-ui-1.12.1.css', false, YITH_WAPO_SCRIPT_VERSION );
				wp_enqueue_style( 'yith_wapo_jquery-ui-timepicker', YITH_WAPO_URL . 'assets/css/_new_jquery-ui-timepicker-addon.css', false, YITH_WAPO_SCRIPT_VERSION );
				wp_enqueue_style( 'dashicons' );

				if ( ! wp_script_is( 'yith-plugin-fw-icon-font', 'registered' ) ) {
					wp_register_style( 'yith-plugin-fw-icon-font', YIT_CORE_PLUGIN_URL . '/assets/css/yith-icon.css', array(), YITH_WAPO_SCRIPT_VERSION );
				}
				wp_enqueue_style( 'yith-plugin-fw-icon-font' );

				// ColorPicker with Iris library.
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script(
					'iris',
					admin_url( 'js/iris.min.js' ),
					array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ),
					YITH_WAPO_SCRIPT_VERSION
				);
				wp_enqueue_script(
					'wp-color-picker',
					admin_url( 'js/color-picker.min.js' ),
					array( 'iris', 'wp-i18n' ),
					YITH_WAPO_SCRIPT_VERSION
				);

				// JS.
				wp_register_script( 'yith_wapo_front', YITH_WAPO_URL . 'assets/js/front' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'wc-add-to-cart-variation' ), YITH_WAPO_SCRIPT_VERSION, true );

				$front_localize = array(
					'dom'                         => array(
						'single_add_to_cart_button' => '.single_add_to_cart_button',
					),
					'ajaxurl'                     => admin_url( 'admin-ajax.php' ),
					'replace_image_path'          => apply_filters(
						'yith_wapo_additional_replace_image_path',
						'.woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image:first-child img.zoomImg,
					.woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image:first-child source,
					.yith_magnifier_zoom img, .yith_magnifier_zoom_magnifier,
					.owl-carousel .woocommerce-main-image,
					.woocommerce-product-gallery__image .wp-post-image,
					.dt-sc-product-image-gallery-container .wp-post-image'
					),
					'upload_allowed_file_types'   => get_option( 'yith_wapo_upload_allowed_file_types', '.jpg, .jpeg, .pdf, .png, .rar, .zip' ),
					'upload_max_file_size'        => get_option( 'yith_wapo_upload_max_file_size', '5' ),
					'total_price_box_option'      => get_option( 'yith_wapo_total_price_box', 'all' ),
					'replace_product_price'       => get_option( 'yith_wapo_replace_product_price', 'no' ),
					'woocommerce_currency_symbol' => esc_attr( get_woocommerce_currency_symbol() ),
                    'woocommerce_currency'        => esc_attr( get_woocommerce_currency() ),
					'woocommerce_currency_pos'    => get_option( 'woocommerce_currency_pos', 'left' ),
					'total_thousand_sep'          => get_option( 'woocommerce_price_thousand_sep', ',' ),
					'decimal_sep'                 => get_option( 'woocommerce_price_decimal_sep', '.' ),
					'num_decimal'                 => get_option( 'woocommerce_price_num_decimals', 2 ),
					'price_suffix'                => wc_tax_enabled() ? get_option( 'woocommerce_price_display_suffix', '' ) : '',
					'replace_product_price_class' => esc_attr(
						apply_filters(
							'yith_wapo_replace_product_price_class',
							'.product .entry-summary .price,
						div.elementor.product .elementor-widget-woocommerce-product-price .price'
						)
					),
					'priceSuffix'                 => get_option( 'woocommerce_price_display_suffix', '' ),
					'hide_button_required'        => get_option( 'yith_wapo_hide_button_if_required', 'no' ),
					'addons_nonce'                => wp_create_nonce( 'addons-nonce' ),
					'maxOptionsSelectedMessage'   => _x( 'More options than allowed have been selected', '[FRONT] Error when the user select more than allowed options ( min/max feature ).', 'yith-woocommerce-product-add-ons' ),
					'productQuantitySelector'   => apply_filters( 'yith_wapo_product_quantity_selector',
						'form.cart .quantity input.qty:not(.wapo-product-qty)'
					),
				);

				$front_localize = apply_filters( 'yith_wapo_frontend_localize_args', $front_localize );

				wp_localize_script( 'yith_wapo_front', 'yith_wapo', $front_localize );
				wp_enqueue_script( 'yith_wapo_front' );
				wp_register_script( 'yith_wapo_jquery-ui-timepicker', YITH_WAPO_URL . 'assets/js/_new_jquery-ui-timepicker-addon.js', array( 'jquery', 'jquery-ui-datepicker', 'wc-add-to-cart-variation' ), YITH_WAPO_SCRIPT_VERSION, true );
				wp_enqueue_script( 'yith_wapo_jquery-ui-timepicker' );
			}

		}

		/**
		 *  Ajax upload file
		 */
		public function ajax_upload_file() {

			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			$uploadedfile     = $_FILES['file'] ?? null; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$filename         = isset( $uploadedfile['name'] ) ? $uploadedfile['name'] : '';
			$upload_overrides = array( 'test_form' => false );
			$movefile         = wp_handle_upload( $uploadedfile, $upload_overrides );

			if ( $movefile && ! isset( $movefile['error'] ) && $filename ) {
				$movefile['file_name'] = $filename;
				echo wp_json_encode( $movefile );
			} else {
				echo 'ERROR!';
			}
			wp_die();

		}

		/**
		 * Custom price
		 *
		 * @param string $price Price.
		 * @param object $product Product.
		 *
		 * @return float
		 */
		public function custom_price( $price, $product ) {
			return (float) $price;
		}

		/**
		 * Custom variable price
		 *
		 * @param string $price Price.
		 * @param string $variation Variation.
		 * @param object $product Product.
		 *
		 * @return float
		 */
		public function custom_variable_price( $price, $variation, $product ) {
			return (float) $price;
		}

		/**
		 * Live print blocks
		 */
		public function live_print_blocks() {

			// Simple, grouped and external products.
			add_filter( 'woocommerce_product_get_price', array( $this, 'custom_price' ), 99, 2 );
			add_filter( 'woocommerce_product_get_regular_price', array( $this, 'custom_price' ), 99, 2 );
			// Variations.
			add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'custom_price' ), 99, 2 );
			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'custom_price' ), 99, 2 );
			// Variable (price range).
			add_filter( 'woocommerce_variation_prices_price', array( $this, 'custom_variable_price' ), 99, 3 );
			add_filter( 'woocommerce_variation_prices_regular_price', array( $this, 'custom_variable_price' ), 99, 3 );

			global $woocommerce, $product, $variation;
			$woocommerce  = WC();
			$product_id   = 0;
			$variation_id = 0;
			$variation    = false;
			$addons       = $_POST['addons'] ?? array(); // phpcs:ignore
			foreach ( $addons as $key => $input ) {
				if ( 'yith_wapo_product_id' === $input['name'] ) {
					$product_id = $input['value'];
				}
				if ( 'variation_id' === $input['name'] ) {
					$variation_id = $input['value'];
					if ( $variation_id > 0 ) {
						$variation = new WC_Product_Variation( $variation_id );
					}
				}
			}

			$product = wc_get_product( $product_id );

			$this->print_blocks();
			wp_die();
		}

		/**
		 * Print container
		 */
		public function print_container() {
			global $product;

			$not_allowed_product_types = array( 'grouped' );

			if ( apply_filters( 'yith_wapo_allowed_product_types', true, $not_allowed_product_types ) && in_array( $product->get_type(), $not_allowed_product_types, true ) ) {
				return;
			}

			wc_get_template(
				'addons-container.php',
				array(
					'instance' => $this,
					'product'  => $product,
				),
				'',
				YITH_WAPO_DIR . '/templates/front/'
			);

		}

		/**
		 * Print blocks
		 */
		public function print_blocks() {

			global $product, $variation;

            $currency = $_POST['currency'] ?? false;

			if ( $product ) {

				$exclude_global = $product && apply_filters( 'yith_wapo_exclude_global', get_post_meta( $product->get_id(), '_wapo_disable_global', true ) === 'yes' ? 1 : 0 );

				$blocks_product_price        = floatval( $_POST['price'] ?? ( $variation ? $variation->get_price() : $product->get_price() ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$blocks_product_price        = apply_filters( 'yith_wapo_blocks_product_price', $blocks_product_price, $product, $variation );
				$blocks_product_price        = $blocks_product_price + ( ( $blocks_product_price / 100 ) * yith_wapo_get_tax_rate() );

				$this->current_product_price = $blocks_product_price;

				echo '<input type="hidden" id="yith_wapo_product_id" name="yith_wapo_product_id" value="' . esc_attr( $product->get_id() ) . '">';
				echo '<input type="hidden" id="yith_wapo_product_img" name="yith_wapo_product_img" value="">';
				echo '<input type="hidden" id="yith_wapo_is_single" name="yith_wapo_is_single" value="1">';

				$color_array_default      = array( 'color' => '#ffffff' );
				$dimensions_array_default = array(
					'dimensions' => array(
						'top'    => '',
						'right'  => '',
						'bottom' => '',
						'left'   => '',
					),
				);

				$setting_hide_images = get_option( 'yith_wapo_hide_images' );

				// Style options.
				$style_addon_titles          = get_option( 'yith_wapo_style_addon_titles', 'h3' );
				$style_addon_background      = get_option( 'yith_wapo_style_addon_background', $color_array_default )['color'];
				$style_addon_padding         = get_option( 'yith_wapo_style_addon_padding', $dimensions_array_default )['dimensions'];
				$style_form_style            = get_option( 'yith_wapo_style_form_style' );
				$style_accent_color          = get_option( 'yith_wapo_style_accent_color', $color_array_default )['color'];
				$style_borders_color         = get_option( 'yith_wapo_style_borders_color', $color_array_default )['color'];
				$style_label_font_size       = get_option( 'yith_wapo_style_label_font_size' );
				$style_description_font_size = get_option( 'yith_wapo_style_description_font_size' );
				// Color Swatches.
				$style_color_swatch_style = get_option( 'yith_wapo_style_color_swatch_style' );
				$style_color_swatch_size  = get_option( 'yith_wapo_style_color_swatch_size' );

				$show_addons_hook     = false;
				$show_total_price_box = false;

				$product_id         = apply_filters( 'yith_wapo_get_original_product_id', $product->get_id() );
				$variation_id       = $variation instanceof WC_Product_Variation ? apply_filters( 'yith_wapo_get_original_product_id', $variation->get_id() ) : '';
				$product_categories = apply_filters( 'yith_wapo_get_original_category_ids', $product->get_category_ids(), $product, $product_id );

				foreach ( yith_wapo_get_blocks() as $key => $block ) {

					if ( '1' === $block->visibility ) {

						$show_in                = $block->get_rule( 'show_in' );
						$included_product_check = in_array( (string) $product_id, (array) $block->get_rule( 'show_in_products' ), true );
						if ( ! $included_product_check && ! empty( $variation_id ) ) {
							$included_product_check = in_array( (string) $variation_id, (array) $block->get_rule( 'show_in_products' ), true );
						}
						$included_category_check = count( array_intersect( (array) $block->get_rule( 'show_in_categories' ), $product_categories ) ) > 0;
						$exclude_in              = $block->get_rule( 'exclude_products' );
						$excluded_product_check  = in_array( (string) $product_id, (array) $block->get_rule( 'exclude_products_products' ), true );
						if ( ! $excluded_product_check && ! empty( $variation_id ) ) {
							$excluded_product_check = in_array( (string) $variation_id, (array) $block->get_rule( 'show_in_products' ), true );
						}
						$excluded_categories_check = 'all' === $show_in && count( array_intersect( (array) $block->get_rule( 'exclude_products_categories' ), $product_categories ) ) > 0;

						$show_to            = $block->get_rule( 'show_to' );
						$show_to_user_roles = $block->get_rule( 'show_to_user_roles' );
						$show_to_membership = $block->get_rule( 'show_to_membership' );

						$vendor_check = true;
						if ( $block->vendor_id > 0 && function_exists( 'yith_get_vendor' ) ) {
							$vendor = yith_get_vendor( $product_id, 'product' );
							if ( $vendor->is_valid() ) {
								$vendor_id    = version_compare( YITH_WPV_VERSION, '4.0', '>=' ) ? $vendor->get_id() : $vendor->id;
								$vendor_check = (string) $block->vendor_id === (string) $vendor_id;
							} else {
								$vendor_check = false;
							}
						}

						// Vendor.
						if ( $vendor_check ) {
							// Include.
							if ( ( 'all' === $show_in && ! $exclude_global ) || ( 'products' === $show_in && ( $included_product_check || $included_category_check ) ) ) {
								// Exclude.
								if ( 'yes' !== $exclude_in || ( ! $excluded_product_check && ! $excluded_categories_check ) ) {
									// Show to.
									if (
									        apply_filters( 'yith_wapo_show_blocks_to',
                                                false,
                                                $block
                                            )
										|| '' === $show_to
										|| 'all' === $show_to
										|| ( 'guest_users' === $show_to && ! is_user_logged_in() )
										|| ( 'logged_users' === $show_to && is_user_logged_in() )
										|| ( 'user_roles' === $show_to && count( array_intersect( (array) $show_to_user_roles, (array) wp_get_current_user()->roles ) ) > 0 )
										|| ( 'membership' === $show_to && yith_wcmbs_user_has_membership( get_current_user_id(), $show_to_membership ) )
									) {
										$addons       = yith_wapo_get_addons_by_block_id( $block->id );
										$total_addons = count( $addons );
										if ( $total_addons > 0 ) {
											if ( ! $show_addons_hook ) {
												$show_addons_hook = true;
												do_action( 'yith_wapo_before_addons' );
											}
											$show_total_price_box = true;

											wc_get_template(
												'block.php',
												array(
													'block'  => $block,
													'addons' => $addons,
													'style_addon_titles' => $style_addon_titles,
													'style_addon_background' => $style_addon_background,
													'style_addon_padding' => $style_addon_padding,
                                                    'currency' => $currency,
												),
												'',
												YITH_WAPO_DIR . '/templates/front/'
											);
										}
									}
								}
							}
						}
					}
				}
				if ( $show_addons_hook ) {
					do_action( 'yith_wapo_after_addons' );
				}

				$total_price_box = get_option( 'yith_wapo_total_price_box', 'all' );

				$price_display_suffix = get_option( 'woocommerce_price_display_suffix', '' );
				$price_suffix         = ' <small>' . $price_display_suffix . '</small>';

				if ( 'hide_all' !== $total_price_box && $show_total_price_box && apply_filters( 'yith_wapo_show_total_table', true, $total_price_box, $product_id ) ) :
					$suffix          = '';
					$suffix_callback = '';
					if ( $price_display_suffix ) {
						if ( strpos( $price_display_suffix, '{price_including_tax}' ) !== false ) {
							$suffix          = '{price_including_tax}';
							$suffix_callback = 'wc_get_price_including_tax';
						} elseif ( strpos( $price_display_suffix, '{price_excluding_tax}' ) !== false ) {
							$suffix          = '{price_excluding_tax}';
							$suffix_callback = 'wc_get_price_excluding_tax';
						}
						if ( $suffix_callback ) {
							$price_callback       = $suffix_callback( $product );
							$price_callback       = wc_price( $price_callback );
							$price_display_suffix = str_replace(
								$suffix,
								$price_callback,
								$price_display_suffix
							);
							$price_suffix         = $price_display_suffix;
						}
					}

					$product_price_label = apply_filters( 'yith_wapo_table_product_price_label', __( 'Product price', 'yith-woocommerce-product-add-ons' ) );
					$total_options_label = apply_filters( 'yith_wapo_table_total_options_label', __( 'Total options', 'yith-woocommerce-product-add-ons' ) );
					$order_total_label   = apply_filters( 'yith_wapo_table_order_total_label', __( 'Order total', 'yith-woocommerce-product-add-ons' ) );

					?>

					<div id="wapo-total-price-table">
						<table class="<?php echo esc_attr( $total_price_box ); ?>">
							<?php if ( $blocks_product_price >= 0 ) : ?>
								<tr class="wapo-product-price" style="<?php echo esc_attr( 'only_final' === $total_price_box ? 'display: none;' : '' ); ?>">
									<th><?php echo esc_html( $product_price_label ); ?>:</th>
									<td id="wapo-total-product-price"><?php echo wp_kses_post( wc_price( $blocks_product_price ) ); ?><?php echo wc_tax_enabled() ? wp_kses_post( $price_suffix ) : ''; ?></td>
								</tr>
							<?php endif; ?>
							<tr class="wapo-total-options" style="<?php echo esc_attr( 'all' !== $total_price_box ? 'display: none;' : '' ); ?>">
								<th><?php echo esc_html( $total_options_label ); ?>:</th>
								<td id="wapo-total-options-price"></td>
							</tr>
							<?php if ( apply_filters( 'yith_wapo_table_hide_total_order', true ) ) { ?>
								<tr class="wapo-total-order">
									<th><?php echo esc_html( $order_total_label ); ?>:</th>
									<td id="wapo-total-order-price"></td>
								</tr>
							<?php } ?>
						</table>
					</div>
				<?php endif; ?>

				<style type="text/css">
					<?php for ( $i = 1; $i < 20; $i++ ) : ?>
					.yith-wapo-block .yith-wapo-addon .options.per-row-<?php echo esc_attr( $i ); ?> .yith-wapo-option {
						width: auto;
						max-width: <?php echo esc_attr( 100 / $i ) - 2; ?>%;
						float: left;
					}

					.yith-wapo-block .yith-wapo-addon .options.per-row-<?php echo esc_attr( $i ); ?> .yith-wapo-option:nth-of-type(<?php echo esc_attr( $i ); ?>n+1) {
						clear: both;
					}

					.yith-wapo-block .yith-wapo-addon .options.grid.per-row-<?php echo esc_attr( $i ); ?> .yith-wapo-option {
						width: <?php echo esc_attr( 100 / $i ) - 2; ?>%;
						margin-right: 2%;
						float: left;
						clear: none;
					}

					.yith-wapo-block .yith-wapo-addon .options.grid.per-row-<?php echo esc_attr( $i ); ?> .yith-wapo-option:nth-of-type(<?php echo esc_attr( $i ); ?>n+1) {
						clear: both;
					}

					<?php endfor; ?>

					<?php if ( 'custom' === $style_form_style ) : ?>
					/* COLOR */
					.yith-wapo-block .yith-wapo-addon.yith-wapo-addon-type-color .yith-wapo-option label:hover span.color,
					.yith-wapo-block .yith-wapo-addon.yith-wapo-addon-type-color .yith-wapo-option.selected label span.color {
						border: 2px solid <?php echo esc_attr( $style_accent_color ); ?>;
					}

					/* LABEL */
					.yith-wapo-block .yith-wapo-addon.yith-wapo-addon-type-label .yith-wapo-option label {
						border: 1px solid <?php echo esc_attr( $style_borders_color ); ?>;
					}

					.yith-wapo-block .yith-wapo-addon.yith-wapo-addon-type-label .yith-wapo-option.selected label {
						border: 1px solid <?php echo esc_attr( $style_accent_color ); ?>;
					}

					/* PRODUCT */
					.yith-wapo-block .yith-wapo-addon.yith-wapo-addon-type-product .yith-wapo-option .product-container {
						border: 1px solid <?php echo esc_attr( $style_borders_color ); ?>;
					}

					.yith-wapo-block .yith-wapo-addon.yith-wapo-addon-type-product .yith-wapo-option .product-container:hover,
					.yith-wapo-block .yith-wapo-addon.yith-wapo-addon-type-product .yith-wapo-option.selected .product-container {
						border: 1px solid <?php echo esc_attr( $style_accent_color ); ?>;
					}

					/* CUSTOM RADIO & CHECKBOX */
					.yith-wapo-block .yith-wapo-addon span.checkboxbutton {
						width: 20px;
						height: 20px;
						position: relative;
						display: block;
						float: left;
					}

					.yith-wapo-block .yith-wapo-addon span.checkboxbutton input[type="checkbox"] {
						width: 20px;
						height: 20px;
						opacity: 0;
						position: absolute;
						top: 0;
						left: 0;
						cursor: pointer;
					}

					.yith-wapo-block .yith-wapo-addon span.checkboxbutton:before {
						content: '';
						background: #ffffff;
						width: 20px;
						height: 20px;
						line-height: 20px;
						border: 1px solid <?php echo esc_attr( $style_borders_color ); ?>;
						border-radius: <?php echo esc_attr( get_option( 'yith_wapo_style_checkbox_style' ) === 'rounded' ? '50%' : '5px' ); ?>;
						margin-right: 10px;
						text-align: center;
						font-size: 17px;
						vertical-align: middle;
						cursor: pointer;
						margin-bottom: 5px;
						transition: background-color ease 0.3s;
						display: inline-block;
					}

					.yith-wapo-block .yith-wapo-addon span.checkboxbutton.checked:before {
						background-image: url('<?php echo esc_attr( YITH_WAPO_URL ); ?>/assets/img/check.svg') !important;
						background-size: 65%;
						background-position: center center;
						background-repeat: no-repeat !important;
						background-color: <?php echo esc_attr( $style_accent_color ); ?>;
						border-color: <?php echo esc_attr( $style_accent_color ); ?>;
						color: #ffffff;
					}

					.yith-wapo-block .yith-wapo-addon span.radiobutton {
						width: 20px;
						height: 20px;
						position: relative;
						display: block;
						float: left;
					}

					.yith-wapo-block .yith-wapo-addon span.radiobutton input[type="radio"] {
						width: 20px;
						height: 20px;
						opacity: 0;
						position: absolute;
						top: 0;
						left: 0;
						cursor: pointer;
					}

					.yith-wapo-block .yith-wapo-addon span.radiobutton:before {
						content: '';
						background: #ffffff;
						background-clip: content-box;
						width: 20px;
						height: 20px;
						line-height: 20px;
						border: 1px solid <?php echo esc_attr( $style_borders_color ); ?>;
						border-radius: 100%;
						padding: 2px;
						margin-bottom: 0px;
						margin-right: 0px;
						font-size: 20px;
						text-align: center;
						display: inline-block;
						float: left;
						cursor: pointer;
					}

					.yith-wapo-block .yith-wapo-addon span.radiobutton.checked:before {
						background-color: <?php echo esc_attr( $style_accent_color ); ?>;
						background-clip: content-box !important;
					}

					input[type=text], input[type=email], input[type=url], input[type=password], input[type=search], input[type=number],
					input[type=tel], input[type=range], input[type=date], input[type=month], input[type=week], input[type=time],
					input[type=datetime], input[type=datetime-local], input[type=color], textarea, input[type=file] {
						padding: 15px;
					}

					/* FONT SIZE */
					.yith-wapo-block .yith-wapo-addon .yith-wapo-option label {
						font-size: <?php echo esc_attr( $style_label_font_size ); ?>px;
					}

					.yith-wapo-block .yith-wapo-addon .yith-wapo-option .description {
						font-size: <?php echo esc_attr( $style_description_font_size ); ?>px;
					}

					/* ACCENT COLOR */
					.yith-wapo-block .yith-wapo-addon.yith-wapo-addon-type-label .yith-wapo-option.selected label:after,
					.yith-wapo-block .yith-wapo-addon.yith-wapo-addon-type-product .yith-wapo-option.selected .product-container::after,
					.yith-wapo-block .yith-wapo-addon.yith-wapo-addon-type-color .yith-wapo-option.selected label:after {
						background-color: <?php echo esc_attr( $style_accent_color ); ?>;
					}

					/* MEDIA QUERIES */
					@media screen and (min-width: 1024px) {
						/* LABEL */
						.yith-wapo-block .yith-wapo-addon.yith-wapo-addon-type-label .yith-wapo-option label:hover {
							border: 1px solid <?php echo esc_attr( $style_accent_color ); ?>;
						}
					}

					<?php endif; ?>

					/* COLOR SWATCHES */
					.yith-wapo-block .yith-wapo-addon.yith-wapo-addon-type-color .yith-wapo-option label {
						height: <?php echo esc_attr( $style_color_swatch_size ); ?>px;
					}

					.yith-wapo-block .yith-wapo-addon.yith-wapo-addon-type-color .yith-wapo-option label span.color {
						width: <?php echo esc_attr( $style_color_swatch_size ); ?>px;
						height: <?php echo esc_attr( $style_color_swatch_size ); ?>px;
					<?php
					if ( 'square' === $style_color_swatch_style ) :
						?>
						border-radius: 0px;
					<?php endif; ?>
					}

					.yith-wapo-block .yith-wapo-addon.yith-wapo-addon-type-color .yith-wapo-option.selected label:after {
						margin: 0px -<?php echo esc_attr( $style_color_swatch_size / 2 + 5 ); ?>px <?php echo esc_attr( $style_color_swatch_size - 18 + 5 ); ?>px 0px;
					}

					/* LABEL / IMAGES */
					<?php
					$tooltip_option = get_option( 'yith_wapo_tooltip_color' );
					if ( ! $tooltip_option ) {
						$tooltip_option['text']       = '#ffffff';
						$tooltip_option['background'] = '#03bfac';
					}
					?>
					/* TOOLTIP */
					.yith-wapo-block .yith-wapo-addon .yith-wapo-option .tooltip span {
						background-color: <?php echo esc_attr( $tooltip_option['background'] ); ?>;
						color: <?php echo esc_attr( $tooltip_option['text'] ); ?>;
					}

					.yith-wapo-block .yith-wapo-addon .yith-wapo-option .tooltip span:after {
						border-top-color: <?php echo esc_attr( $tooltip_option['background'] ); ?>;
					}

					.yith-wapo-block .yith-wapo-addon .yith-wapo-option .tooltip.position-bottom span:after {
						border-bottom-color: <?php echo esc_attr( $tooltip_option['background'] ); ?>;
					}

					/* TOOLTIP */
					.yith-wapo-block .yith-wapo-addon .yith-wapo-option .tooltip span {
						background-color: <?php echo esc_attr( $tooltip_option['background'] ); ?>;
						color: <?php echo esc_attr( $tooltip_option['text'] ); ?>;
					}

					.yith-wapo-block .yith-wapo-addon .yith-wapo-option .tooltip span:after {
						border-top-color: <?php echo esc_attr( $tooltip_option['background'] ); ?>;
					}

					.yith-wapo-block .yith-wapo-addon .yith-wapo-option .tooltip.position-bottom span:after {
						border-bottom-color: <?php echo esc_attr( $tooltip_option['background'] ); ?>;
					}
				</style>
				<?php
			}
		} // end print_blocks()

		/**
		 * Get product blocks
		 *
		 * @param int $product_id Product ID.
		 *
		 * @return array
		 */
		public function get_product_blocks( $product_id ) {
			return yith_wapo_get_blocks();
		}

		/**
		 * Show Options Shortcode
		 *
		 * @param array $atts Attributes.
		 */
		public function yith_wapo_show_options_shortcode( $atts ) {
			ob_start();
			if ( is_product() ) {
				do_action( 'yith_wapo_show_options_shortcode' );
			} else {
				echo '<strong>' . esc_html__( 'This is not a product page!', 'yith-woocommerce-product-add-ons' ) . '</strong>';
			}

			return ob_get_clean();
		}
		/**
		 * Update totals with suffix
		 *
		 * Update totals when there are suffix configured
		 *
		 * @author Carlos Rodríguez <carlos.rodriguez@yithemes.com>
		 * @since  3.2.0
		 */
		public function update_totals_with_suffix() {
			check_ajax_referer( 'addons-nonce', 'security' );
			$values = array( 'price_html' => '' );
			if ( isset( $_POST['product_id'] ) && $_POST['product_id'] ) {
				$product_id = $_POST['product_id'];
				$product    = wc_get_product( $product_id );
				if ( $product instanceof WC_Product ) {
					if ( $product instanceof WC_Product_Variable && empty( $product->get_default_attributes() ) ) {
						$price = 0;
					} else {
						$price                = apply_filters( 'yith_wapo_convert_price', wc_get_price_to_display( $product ), true );
					}
					$totals_price_args    = apply_filters( 'yith_wapo_totals_price_args', array() );
					$display_product      = wc_price( $price, $totals_price_args ) . $product->get_price_suffix();
					$values['price_html'] = $display_product;
				}
			}
			wp_send_json( $values );
		}

		/**
		 * Get the default product price when variation is reset.
		 *
		 * @author YITH
		 * @since  3.2.0
		 * @return array
		 */
		public function get_default_variation_price() {
			check_ajax_referer( 'addons-nonce', 'security' );
			$values = array( 'price_html' => '' );
			if ( isset( $_POST['product_id'] ) && $_POST['product_id'] ) {
				$product_id = $_POST['product_id'];
				$product    = wc_get_product( $product_id );
				if ( $product instanceof WC_Product ) {
                    $product_price      = $product->get_price();
                    $product_price_html = $product->get_price_html();

					$values['price_html']    = $product_price_html;
					$values['current_price'] = $product_price;

				}
			}
			wp_send_json( $values );
		}

	}

}

/**
 * Unique access to instance of YITH_WAPO_Front class
 *
 * @return YITH_WAPO_Front
 */
function YITH_WAPO_Front() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return YITH_WAPO_Front::get_instance();
}
