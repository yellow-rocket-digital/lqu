<?php
/**
 * WAPO Functions
 *
 * @author  Corrado Porzio <corradoporzio@gmail.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'yith_wapo_update_300_migrate_db' ) ) {
	/**
	 * Migration from 1.x version
	 *
	 * @return bool
	 * @since 2.0.0
	 */
	function yith_wapo_update_300_migrate_db() {

		global $wpdb;

		$YITH_WAPO = YITH_WAPO::get_instance(); //phpcs:ignore

		$limit = apply_filters( 'yith_wapo_db_migration_limit', 10 );

		// Get the groups (blocks) that has not been imported and deleted in v1.
		$query            = "SELECT * FROM {$wpdb->prefix}yith_wapo_groups WHERE imported='0' AND del='0' ORDER BY priority, name ASC  LIMIT  {$limit}";
		$old_groups_array = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( empty( $old_groups_array ) ) {
			// Stop the execution, since there are no more groups to update.
			return false;
		}

		// If the block is not already migrated.
		if ( ! empty( $old_groups_array ) && isset( $old_groups_array[0] ) ) {

			$block = $old_groups_array[0];

			$import_products_id         = strpos( $block->products_id, ',' ) !== false ? explode( ',', $block->products_id ) : $block->products_id;
			$import_categories_id       = strpos( $block->categories_id, ',' ) !== false ? explode( ',', $block->categories_id ) : $block->categories_id;
			$import_products_exclude_id = strpos( $block->products_exclude_id, ',' ) !== false ? explode( ',', $block->products_exclude_id ) : $block->products_exclude_id;

			$request['block_id']                             = 'new';
			$request['block_name']                           = empty( $block->name ) ? '' : $block->name;
			$request['block_rule_show_in']                   = empty( $block->products_id ) && empty( $block->categories_id ) ? 'all' : 'products';
			$request['block_rule_show_in_products']          = empty( $block->products_id ) ? '' : $import_products_id;
			$request['block_rule_show_in_categories']        = empty( $block->categories_id ) ? '' : $import_categories_id;
			$request['block_rule_exclude_products_products'] = empty( $block->products_exclude_id ) ? '' : $import_products_exclude_id;
			$request['block_rule_show_to']                   = 'all';
			$request['block_priority']                       = ! empty( $block->priority ) ? $block->priority : 1;
			$request['block_visibility']                     = ( '9' === $block->visibility ) ? 1 : '';
			$block_id                                        = $YITH_WAPO->save_block( $request );

			// Get the types(addons) that has not been imported and deleted in v1.
			$old_addons_query = "SELECT * FROM {$wpdb->prefix}yith_wapo_types WHERE group_id='$block->id' AND imported='0' AND del='0' ORDER BY priority ASC";
			$old_addons_array = $wpdb->get_results( $old_addons_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			foreach ( $old_addons_array as $addon_key => $addon ) {

				// General.
				$request['addon_id'] = $addon->id;
				$request['block_id'] = $block_id;
				if ( 'labels' === $addon->type ) {
					$request['addon_type'] = 'label';
				} elseif ( 'multiple_labels' === $addon->type ) {
					$request['addon_type'] = 'label';
				} elseif ( 'color' === $addon->type ) {
					$request['addon_type'] = 'colorpicker';
				} else {
					$request['addon_type'] = $addon->type;
				}

				// Addon priority.
				$request['addon_priority'] = isset( $addon->priority ) ? $addon->priority : 0;

				// Display options.
				$request['addon_title']             = $addon->label;
				$request['addon_description']       = $addon->description;
				$request['addon_show_image']        = ( '' !== $addon->image ? 'yes' : 'no' );
				$request['addon_image']             = $addon->image;
				$request['addon_image_replacement'] = $addon->change_featured_image ? 'options' : '';
				$request['addon_show_as_toggle']    = $addon->collapsed ? 'closed' : 'no';

				$conditional_rules_addon    = yith_wapo_formatted_conditional_rules( $addon );
				$conditional_rules_addon_is = array();

				// "not-empty" for inputs texts, "selected" for other addons.
				$rule_addon_is = 'selected';
				foreach ( $conditional_rules_addon['is_input'] as $rule => $is_input ) {
					if ( 'yes' === $is_input ) {
						$rule_addon_is = 'not-empty';
					}
					$conditional_rules_addon_is[] = $rule_addon_is;
				}

				// Conditional logic.
				$request['addon_enable_rules']                 = ( $addon->depend || $addon->depend_variations ) ? 'yes' : '';
				$enable_rules                                  = 'yes' === $request['addon_enable_rules'];
				$request['addon_conditional_logic_display']    = $enable_rules ? 'show' : '';
				$request['addon_conditional_logic_display_if'] = $enable_rules ? ( 'and' === $addon->operator ? 'all' : 'any' ) : '';
				$request['addon_conditional_rule_addon']       = $enable_rules ? $conditional_rules_addon['dependencies'] : '';
				$request['addon_conditional_rule_addon_is']    = $enable_rules ? $conditional_rules_addon_is : '';

				$dep_variations_   = $addon->depend_variations;
				$depend_variations = explode( ',', $dep_variations_ );

				$request['addon_enable_rules_variations']     = ! empty( $dep_variations_ ) ? 'yes' : 'no';
				$request['addon_conditional_rule_variations'] = ! empty( $dep_variations_ ) ? $depend_variations : array();
				$request['addon_conditional_set_conditions']  = $addon->depend ? '1' : '0';

				// Advanced options.
				$request['addon_first_options_selected'] = $addon->first_options_free > 0 ? 'yes' : 'no'; // yes/no.
				$request['addon_first_free_options']     = $addon->first_options_free;
				$request['addon_selection_type']         = 'checkbox' === $addon->type ? 'multiple' : 'single'; // single or multiple.
				$request['addon_enable_min_max']         = $addon->max_item_selected > 0 ? 'yes' : 'no';

				$min_max_rule  = array();
				$min_max_value = array();

				if ( 'yes' === $request['addon_enable_min_max'] ) {
						$min_max_rule[]  = 'max';
						$min_max_value[] = $addon->max_item_selected;
				}

				$request['addon_min_max_rule']      = $min_max_rule;
				$request['addon_min_max_value']     = $min_max_value;
				$request['addon_sell_individually'] = isset( $addon->sold_individually ) && $addon->sold_individually > 0 ? 'yes' : 'no';
				$request['options']                 = array();
				$options                            = maybe_unserialize( $addon->options );
				if ( isset( $options['label'] ) && is_array( $options['label'] ) ) {
					foreach ( $options['label'] as $index => $value ) {
						$request['options']['label'][]         = $options['label'][ $index ];
						$request['options']['tooltip'][]       = $options['tooltip'][ $index ];
						$request['options']['placeholder'][]   = $options['placeholder'][ $index ];
						$request['options']['description'][]   = $options['description'][ $index ];
						$request['options']['addon_enabled'][] = ! isset( $options['hideoption'][ $index ] ) ? 'yes' : 'no';

						$price_method = 'free';
						if ( $options['price'][ $index ] > 0 ) {
							$price_method = 'increase';
						} elseif ( $options['price'][ $index ] < 0 ) {
							$price_method = 'decrease';
						}

						$request['options']['price_method'][]     = $price_method;
						$request['options']['price'][]            = abs( (float) $options['price'][ $index ] ); // abs() > Positive number (price_method says free, increase or decrease the price).
						$request['options']['price_type'][]       = $options['type'][ $index ];
						$request['options']['default'][]          = isset( $options['default'][ $index ] ) ? 'yes' : '';
						$request['options']['required'][]         = $options['required'][ $index ] ?? '';
						$request['options']['show_image'][]       = isset( $options['image'][ $index ] ) && '' !== $options['image'][ $index ] ? 'yes' : 'no';
						$request['options']['image'][]            = $options['image'][ $index ] ?? '';
						$request['options']['number_limit'][]     = ( $addon->min_input_values_amount > 0 || $addon->max_input_values_amount > 0 ) ? 'yes' : 'no';
						$request['options']['number_limit_min'][] = $addon->min_input_values_amount;
						$request['options']['number_limit_max'][] = $addon->max_input_values_amount;
					}
				}

				$YITH_WAPO->save_addon( $request, 'migration' ); //phpcs:ignore

				$sql = "UPDATE {$wpdb->prefix}yith_wapo_types SET imported='1' WHERE id='$addon->id'";
				$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			}

			$sql = "UPDATE {$wpdb->prefix}yith_wapo_groups SET imported='1' WHERE id='$block->id'";
			$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		}

		// Next execution!
		return true;

	}
}

if ( ! function_exists( 'yith_wapo_formatted_conditional_rules' ) ) {
	/**
	 * Format the logical condition of the addons to the correct v2 format.
	 *
	 * @param object $addon The addon object.
	 * @return array
	 * @since 2.0.0
	 */
	function yith_wapo_formatted_conditional_rules( $addon ) {

		$formatted_rules['has_addons_conditions'] = false;

		$depend = $addon->depend;
		$depend = explode( ',', $depend );

		if ( ! empty( $depend ) ) {
			$formatted_rules['has_addons_conditions'] = true;
		}

		$depend = array_map( 'yith_wapo_mapping_conditional_depend_rule', $depend );

		$formatted_rules['dependencies'] = $depend;
		$formatted_rules['is_input']     = yith_wapo_check_if_addon_is_input( $depend );

		return $formatted_rules;
	}
}
if ( ! function_exists( 'yith_wapo_mapping_conditional_depend_rule' ) ) {
	/**
	 * Mapping each rule of the logical condition array.
	 *
	 * @param string $depend_arr The string of the array.
	 * @return string
	 * @since 2.0.0
	 */
	function yith_wapo_mapping_conditional_depend_rule( $depend_arr ) {
		$depend_arr = str_replace( '_', '-', $depend_arr );
		$depend_arr = preg_replace( '/^option-/', '', $depend_arr );

		return $depend_arr;
	}
}

if ( ! function_exists( 'yith_wapo_check_if_addon_is_input' ) ) {
	/**
	 * Check if an addon use input text in order to apply 'not_empty' status in the conditional logic.
	 *
	 * @param array $depend_arr The string of the array.
	 * @return array
	 * @since 2.0.0
	 */
	function yith_wapo_check_if_addon_is_input( $depend_arr ) {

		$input_types = array( 'text', 'textarea', 'color', 'date', 'number' );

		global $wpdb;
		foreach ( $depend_arr as $key => &$value ) {

			if ( ! empty( $value ) ) {

				$addon    = explode( '-', $value );
				$addon_id = isset( $addon[0] ) ? $addon[0] : '';

				$result = $wpdb->get_row( $wpdb->prepare( "SELECT type FROM {$wpdb->prefix}yith_wapo_types WHERE id=%s and del=0", $addon_id ) );

				if ( isset( $result->type ) ) {
					if ( in_array( $result->type, $input_types ) ) {
						$value = 'yes';
					}
				}
			}
		}

		return $depend_arr;
	}
}

if ( ! function_exists( 'yith_wapo_update_320_migrate_conditional_logic' ) ) {
	/**
	 * Migration conditional logic
	 *
	 * @return bool
	 * @since 3.2.0
	 */
	function yith_wapo_update_320_migrate_conditional_logic() {

		global $wpdb;

		$limit = apply_filters( 'yith_wapo_db_migration_limit', 60 );

		$query  = "SELECT id,settings FROM {$wpdb->prefix}yith_wapo_addons WHERE last_update='0' LIMIT  {$limit}";
		$addons = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( empty( $addons ) ) {
			// Stop the execution, since there are no more groups to update.
			return false;
		}

		foreach ( $addons as $addon ) {

			$settings = maybe_unserialize( $addon->settings );

			$default_variations = array(
				'enable_rules_variations'     => 'no',
				'conditional_rule_variations' => array(),
				'conditional_set_conditions'  => 1,

			);

			if ( is_array( $settings ) && ! empty( $settings ) ) {

				// Seems this addons is migrated using migration tool and migration section.
				if ( isset( $settings['enable_rules_variations'] ) || isset( $settings['conditional_rule_variations'] ) ) {
					$sql = "UPDATE {$wpdb->prefix}yith_wapo_addons SET last_update= CURRENT_TIMESTAMP WHERE id='$addon->id'";
					$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
					continue;
				}

				if ( isset( $settings['conditional_rule_addon'] ) ) {

					foreach ( $settings['conditional_rule_addon'] as $key => $rule ) {
						$rule_variation = explode( '-', $rule );
						if ( $rule_variation ) {
							if ( 'v' === $rule_variation[0] ) { // Migrate variation to new option.
								if ( isset( $rule_variation[2] ) ) {
									$default_variations['conditional_rule_variations'][] = $rule_variation[2];
									unset( $settings['conditional_rule_addon'][ $key ] );
									unset( $settings['conditional_rule_addon_is'][ $key ] );
									$default_variations['enable_rules_variations'] = 'yes';
								}
							}
						}
					}
				}

				$settings = array_merge( $settings, $default_variations );

				$settings = maybe_serialize( $settings );

				$sql = "UPDATE {$wpdb->prefix}yith_wapo_addons SET last_update= CURRENT_TIMESTAMP, settings = '$settings' WHERE id='$addon->id'";
				$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			}
		}
		// Next execution!.
		return true;

	}
}
