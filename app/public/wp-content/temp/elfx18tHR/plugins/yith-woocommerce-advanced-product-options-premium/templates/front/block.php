<?php
/**
 * WAPO Template
 *
 * @author  Corrado Porzio <corradoporzio@gmail.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 *
 * @var object $block
 * @var array  $addons
 * @var int    $x
 * @var string $style_addon_titles
 * @var string $style_addon_background
 * @var array  $style_addon_padding
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

$block_classes = apply_filters( 'yith_wapo_block_classes', 'yith-wapo-block', $block );

?>

<div id="yith-wapo-block-<?php echo esc_attr( $block->id ); ?>" class="<?php echo esc_attr( $block_classes ); ?>">

	<?php
	foreach ( $addons as $key => $addon ) :
		if ( '1' === $addon->visibility && yith_wapo_is_addon_type_available( $addon->type ) ) :

			// Display settings.
			$addon_title       = $addon->get_setting( 'title' );
			$addon_description = $addon->get_setting( 'description' );
			$required          = $addon->get_setting( 'required', 'no', false );

			if ( YITH_WAPO::$is_wpml_installed ) {
				$addon_title       = YITH_WAPO_WPML::string_translate( $addon_title );
				$addon_description = YITH_WAPO_WPML::string_translate( $addon_description );
			}
			$addon_show_image              = $addon->get_setting( 'show_image', '', false );
			$addon_image                   = $addon->get_setting( 'image', '' );
			$addon_image_replacement       = $addon->get_setting( 'image_replacement', 'no', false );
			$addon_options_images_position = $addon->get_setting( 'options_images_position', 'above', false );


			if ( get_option( 'yith_wapo_show_in_toggle' ) === 'yes' ) {
				$toggle_addon   = 'wapo-toggle';
				$toggle_status  = get_option( 'yith_wapo_show_toggle_opened' ) === 'yes' ? 'toggle-open' : 'toggle-closed';
				$toggle_default = get_option( 'yith_wapo_show_toggle_opened' ) === 'yes' ? 'default-open' : 'default-closed';
			} else {
				$toggle_addon   = $addon->get_setting( 'show_as_toggle', '', false ) !== 'no' ? 'wapo-toggle' : '';
				$toggle_status  = 'toggle-closed';
				$toggle_default = 'default-closed';
			}

			if ( '' !== $toggle_addon && 'wapo-toggle' !== $toggle_addon ) {
				$toggle_status  = $addon->get_setting( 'show_as_toggle', '', false ) === 'open' ? 'toggle-open' : 'toggle-closed';
				$toggle_default = $addon->get_setting( 'show_as_toggle', '', false ) === 'open' ? 'default-open' : 'default-closed';
			}

			if ( 'toggle' === $toggle_addon && '' === $addon_title ) {
				$addon_title = __( 'No title', 'yith-woocommerce-product-add-ons' );
			}

			$hide_option_images = $addon->get_setting( 'hide_options_images' ) === 'yes';
			$hide_option_label  = $addon->get_setting( 'hide_options_label' ) === 'yes';
			$hide_option_prices = $addon->get_setting( 'hide_options_prices' ) === 'yes';

			$hide_option_prices = apply_filters( 'yith_wapo_hide_option_prices', $hide_option_prices, $addon );

			// Layout.
			$options_per_row          = defined( 'YITH_WAPO_PREMIUM' ) && YITH_WAPO_PREMIUM ? $addon->get_setting( 'options_per_row', 1 ) : 1;
			$show_in_a_grid           = $addon->get_setting( 'show_in_a_grid', '', false ) === 'yes';
			$options_width            = $addon->get_setting( 'options_width', 100, false );
			$options_width_css        = $show_in_a_grid ? 'width: ' . ( $options_width ) . '%; min-width: ' . ( $options_width ) . '%;' : 'width: 100%;';
			$options_width_select_css = $show_in_a_grid ? 'width: ' . ( $options_width ) . '%;' : '';

			// Advanced settings.
			$first_options_selected = $addon->get_setting( 'first_options_selected', 'no', false );
			$first_free_options     = $addon->get_setting( 'first_free_options', 0, false );
			$selection_type         = $addon->get_setting( 'selection_type', 'single', false );
			$enable_min_max         = $addon->get_setting( 'enable_min_max', 'no', false );
			$min_max_rule           = $addon->get_setting( 'min_max_rule', 'min', false );
			$min_max_value          = $addon->get_setting( 'min_max_value', 0, false );
			$min_max_values         = array(
				'min' => '',
				'max' => '',
				'exa' => '',
			);
			if ( 'yes' === $enable_min_max && is_array( $min_max_rule ) ) {
				$min_max_rule_count = count( $min_max_rule );
				for ( $y = 0; $y < $min_max_rule_count; $y++ ) {
					$min_max_values[ $min_max_rule[ $y ] ] = $min_max_value[ $y ];
				}
			}
			// Sell individually.
			$sell_individually = $addon->get_setting( 'sell_individually', 'no', false );
			$required_addon    = false;

			if ( 'yes' === apply_filters( 'yith_wapo_addons_settings_required', $required, $addon ) || 'select' === $addon->type && 'yes' === $enable_min_max && ( ! empty( $min_max_values['min'] ) || ! empty( $min_max_values['max'] ) || ! empty( $min_max_values['exa'] ) ) ) {
				$required_addon = true;
			}

			// Conditional logic.
			$conditional_logic       = $addon->get_setting( 'enable_rules', 'no', false );
			$conditional_logic_class = '';
			if ( 'yes' === $conditional_logic ) {

				// Conditions.
				$conditional_logic_display    = $addon->get_setting( 'conditional_logic_display' );
				$conditional_logic_display_if = $addon->get_setting( 'conditional_logic_display_if' );

				$conditional_rule_addon    = apply_filters( 'yith_wapo_conditional_rule_addon', (array) $addon->get_setting( 'conditional_rule_addon' ) );
				$conditional_logic_rules   = ! empty( $conditional_rule_addon );
				$conditional_rule_addon_is = ! empty( $conditional_rule_addon ) ? (array) $addon->get_setting( 'conditional_rule_addon_is' ) : false;

				// Variations.
				$apply_variation_rule        = $addon->get_setting( 'enable_rules_variations', 'no', false );
				$conditional_logic_variation = apply_filters( 'yith_wapo_conditional_rule_variation', (array) $addon->get_setting( 'conditional_rule_variations' ) );
				$variations_logic            = 'yes' === $apply_variation_rule && ! empty( $conditional_logic_variation );
				if ( 'yes' === $apply_variation_rule ) {
					$set_conditions = $addon->get_setting( 'conditional_set_conditions' );
					if ( ! $set_conditions ) {
						$conditional_rule_addon = false;
					}
				}

				if ( $conditional_logic_rules || $variations_logic ) { // If conditions or variations, apply the conditional logic.
					$conditional_logic_class = 'conditional_logic';
				} else {
					$conditional_logic = 'no';
				}
			}

			$addon_classes       = apply_filters(
				'yith_wapo_addon_classes',
				'yith-wapo-addon yith-wapo-addon-type-' . esc_attr( $addon->type ) . ' ' . esc_attr( $toggle_addon ) . ' ' . esc_attr( $toggle_default ) . ' ' .
				esc_attr( $conditional_logic_class ) . ' ' . esc_attr( 'yes' === $sell_individually ? 'sell_individually' : '' ),
				$addon
			);
			$setting_hide_images = get_option( 'yith_wapo_hide_images' );

			?>

			<div id="yith-wapo-addon-<?php echo esc_attr( $addon->id ); ?>"
				class="<?php echo esc_attr( $addon_classes ); ?>"
				data-min="<?php echo esc_attr( $min_max_values['min'] ); ?>"
				data-max="<?php echo esc_attr( $min_max_values['max'] ); ?>"
				data-exa="<?php echo esc_attr( $min_max_values['exa'] ); ?>"
				data-addon-type="<?php echo esc_attr( $addon->type ); ?>"
				<?php if ( 'yes' === $conditional_logic ) : ?>
				data-addon_id="<?php echo esc_attr( $addon->id ); ?>"
				data-conditional_logic_display="<?php echo esc_attr( $conditional_logic_display ); ?>"
				data-conditional_logic_display_if="<?php echo esc_attr( $conditional_logic_display_if ); ?>"
				data-conditional_rule_addon="<?php echo ( $conditional_rule_addon ) ? esc_attr( implode( '|', $conditional_rule_addon ) ) : ''; ?>"
				data-conditional_rule_addon_is="<?php echo esc_attr( implode( '|', $conditional_rule_addon_is ) ); ?>"
				data-conditional_rule_variations="<?php echo ( $variations_logic ) ? esc_attr( implode( '|', $conditional_logic_variation ) ) : ''; ?>"
				<?php endif; ?>
				style="
				<?php
					echo 'background-color: ' . esc_attr( $style_addon_background ) . ';';
					echo ' padding: ' . esc_attr( $style_addon_padding['top'] ) . 'px ' . esc_attr( $style_addon_padding['right'] ) . 'px ' . esc_attr( $style_addon_padding['bottom'] ) . 'px ' . esc_attr( $style_addon_padding['left'] ) . 'px;';
					echo 'yes' === $conditional_logic ? ' display: none;' : '';
				?>
					">

				<?php if ( '' !== $addon_title ) : ?>
					<<?php echo esc_attr( $style_addon_titles ); ?> class="wapo-addon-title <?php echo esc_attr( $toggle_status ); ?>"><?php echo apply_filters( 'yith_wapo_addon_display_title', esc_html( $addon_title ) , $addon_title ); ?>
					<?php echo $required_addon ? '<span class="required">*</span>' : ''; ?>
					</<?php echo esc_attr( $style_addon_titles ); ?>>

					<?php if ( 'yes' === get_option( 'yith_wapo_show_blocks_in_cart', 'no' ) ) : ?>
						<?php echo '<input type="hidden" class="wapo-addon-title-hidden" name="yith_wapo[][' . esc_attr( $addon->id ) . '-addon_title]" value="' . esc_html( $addon_title ) . '" />'; ?>
					<?php endif; ?>
				<?php endif; ?>

				<?php

				if ( 'html_heading' === $addon->type || 'html_separator' === $addon->type || 'html_text' === $addon->type ) {
					include YITH_WAPO_DIR . '/templates/front/addons/' . $addon->type . '.php';
				} else {

					if ( 'yes' === $addon_show_image && '' !== $addon_image ) :
						?>
						<div class="title-image">
							<img src="<?php echo esc_attr( $addon_image ); ?>">
						</div>
						<?php
					endif;

					if ( 'select' === $addon->type ) {
						echo '<div class="options ' . esc_attr( $toggle_default ) . ' per-row-' . esc_attr( $options_per_row ) . ( $show_in_a_grid ? ' grid' : '' ) . '" style="' . esc_attr( $options_width_select_css ) . '">';
					} else {
						echo '<div class="options ' . esc_attr( $toggle_default ) . ' per-row-' . esc_attr( $options_per_row ) . ( $show_in_a_grid ? ' grid' : '' ) . '">';
					}



					if ( '' !== $addon_description ) {
						echo '<p class="wapo-addon-description">' . stripslashes( $addon_description ) . '</p>'; // phpcs:ignore
					}

					if ( 'select' === $addon->type ) {
						if ( ! $hide_option_images ) {
							echo '<div class="option-image"></div>';
						}

						$is_required = 'yes' === $required ? 'required' : '';

						echo '<select id="yith-wapo-' . esc_attr( $addon->id ) . '"
							name="yith_wapo[][' . esc_attr( $addon->id ) . ']"
							class="yith-wapo-option-value"
							data-addon-id="' . esc_attr( $addon->id ) . '"
							style="' . esc_attr( $options_width_select_css ) . '"
							' . esc_attr( $is_required ) . '
							>
								<option value="default">' . esc_html( apply_filters( 'yith_wapo_select_option_label', __( 'Select an option', 'yith-woocommerce-product-add-ons' ) ) ) . '</option>';
					}

					$options_total = is_array( $addon->options ) && isset( array_values( $addon->options )[0] ) ? count( array_values( $addon->options )[0] ) : 1;
					for ( $x = 0; $x < $options_total; $x++ ) {
						if ( file_exists( YITH_WAPO_DIR . '/templates/front/addons/' . $addon->type . '.php' ) ) {

							$enabled = $addon->get_option( 'addon_enabled', $x, 'yes', false );

							if ( 'yes' === $enabled ) {
								$option_show_image  = $addon->get_option( 'show_image', $x, false );
								$option_image       = $option_show_image ? $addon->get_option( 'image', $x ) : '';
								$option_description = $addon->get_option( 'description', $x );

								// todo: improve price calculation.
								$price_method = $addon->get_option( 'price_method', $x, 'free', false );
								$price_type   = $addon->get_option( 'price_type', $x, 'fixed', false );
								$price        = $addon->get_price( $x );
								$price_sale   = $addon->get_sale_price( $x );
								$price        = floatval( str_replace( ',', '.', $price ) );
								$price_sale   = '' !== $price_sale ? floatval( str_replace( ',', '.', $price_sale ) ) : '';

								$image_replacement = '';
								if ( 'addon' === $addon_image_replacement ) {
									$image_replacement = $addon_image;
								} elseif ( ! empty( $option_image ) && 'options' === $addon_image_replacement ) {
									$image_replacement = $option_image;
								}

								// todo: improve price calculation.
								if ( 'free' === $price_method ) {
									$price      = '0';
									$price_sale = '0';
								} elseif ( 'decrease' === $price_method ) {
									$price      = $price > 0 ? - $price : 0;
									$price_sale = '0';
								} elseif ( 'product' === $price_method ) {
									$price      = $price > 0 ? $price : 0;
									$price_sale = '0';
								} else {
									$price      = $price > 0 ? $price : '0';
									$price_sale = $price_sale >= 0 ? $price_sale : 'undefined';
								}

								wc_get_template(
									$addon->type . '.php',
									apply_filters(
										'yith_wapo_addon_arg',
										array(
											'addon'        => $addon,
											'addon_options_images_position' => $addon_options_images_position,
											'hide_option_images' => $hide_option_images,
											'hide_option_label' => $hide_option_label,
											'hide_option_prices' => $hide_option_prices,
											'image_replacement' => is_ssl() ? str_replace( 'http://', 'https://', $image_replacement ) : $image_replacement,
											'option_description' => $option_description,
											'option_image' => is_ssl() ? str_replace( 'http://', 'https://', $option_image ) : $option_image,
											'options_width_css' => $options_width_css,
											'price'        => $price,
											'price_method' => $price_method,
											'price_sale'   => $price_sale,
											'price_type'   => $price_type,
											'selection_type' => $selection_type,
											'setting_hide_images' => $setting_hide_images,
											'sell_individually' => $sell_individually,
											'x'            => $x,
										),
										$addon
									),
									'',
									YITH_WAPO_DIR . '/templates/front/addons/'
								);
							}
						}
					}

					if ( 'select' === $addon->type ) {
						echo '</select><p class="option-description"></p>';
					}

					if ( ( 'select' === $addon->type || 'radio' === $addon->type ) && 'yes' === $sell_individually ) {
						echo '<input type = "hidden" name = "yith_wapo_sell_individually[' . esc_attr( $addon->id ) . ']" value = "yes" >';
					}

					echo '<div class="clear" style="margin: 4px;"></div>';

					if ( 'yes' === $required || 'yes' === $enable_min_max ) :
						?>
							<small class="min-error" style="color: #f00; padding: 5px 0px; display: none;">
								<span class="min-error-select"><?php echo esc_html__( 'Please select', 'yith-woocommerce-product-add-ons' ); ?></span>
								<span class="min-error-an" style="display: none;"><?php echo esc_html__( 'an', 'yith-woocommerce-product-add-ons' ); ?></span>
								<span class="min-error-qty" style="display: none;"></span>
								<span class="min-error-option" style="display: none;"><?php echo esc_html__( 'option', 'yith-woocommerce-product-add-ons' ); ?></span>
								<span class="min-error-options" style="display: none;"><?php echo esc_html__( 'options', 'yith-woocommerce-product-add-ons' ); ?></span>
							</small>
						<?php
						endif;

					echo '</div>';

				}

				?>

			</div>

		<?php endif; ?>
	<?php endforeach; ?>

</div>
