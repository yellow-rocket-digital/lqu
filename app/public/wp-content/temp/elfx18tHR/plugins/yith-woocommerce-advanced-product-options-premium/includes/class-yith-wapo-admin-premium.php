<?php
/**
 * WAPO Admin Premium Class
 *
 * @author  Corrado Porzio <corradoporzio@gmail.com>
 * @package YITH\ProductAddOns
 * @version 3.0.4
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO_Admin_Premium' ) ) {

	/**
	 *  YITH_WAPO_Admin_Premium Class
	 */
	class YITH_WAPO_Admin_Premium extends YITH_WAPO_Admin {
		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WAPO_Admin_Premium
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0.0
		 * @author Emanuela Castorina
		 */
		public function __construct() {

			parent::__construct();

			add_action( 'admin_footer', array( $this, 'yith_wapo_date_rule_template_js' ) );

		}

		/**
		 * New date rule template to add via JS with wp.template
		 *
		 * @return void
		 */
		public function yith_wapo_date_rule_template_js() {
			?>
			<script type="text/html" id="tmpl-yith-wapo-date-rule-template">
				<div class="rule" style="margin-bottom: 10px;">
					<div class="field what">
						<?php
						yith_plugin_fw_get_field(
							array(
								'id'      => 'date-rule-what-{{data.addon_id}}-{{data.option_id}}',
								'name'    => 'options[date_rule_what][{{data.addon_id}}][]',
								'class'   => 'micro select_what wc-enhanced-select',
								'type'    => 'select',
								'value'   => 'days',
								'options' => array(
									'days'     => esc_html__( 'Days', 'yith-woocommerce-product-add-ons' ),
									'daysweek' => esc_html__( 'Days of the week', 'yith-woocommerce-product-add-ons' ),
									'months'   => esc_html__( 'Months', 'yith-woocommerce-product-add-ons' ),
									'years'    => esc_html__( 'Years', 'yith-woocommerce-product-add-ons' ),
								),
							),
							true
						);
						?>
					</div>

					<div class="field days">
						<?php
						yith_plugin_fw_get_field(
							array(
								'id'    => 'date-rule-value-days-{{data.addon_id}}-{{data.option_id}}',
								'name'  => 'options[date_rule_value_days][{{data.addon_id}}][{{data.option_id}}]',
								'type'  => 'datepicker',
								'value' => '',
								'data'  => array(
									'date-format' => 'yy-mm-dd',
								),
							),
							true
						);
						?>
					</div>
					<div class="field daysweek" style="display: none";>
						<?php
						yith_plugin_fw_get_field(
							array(
								'id'       => 'date-rule-value-daysweek-{{data.addon_id}}-{{data.option_id}}',
								'name'     => 'options[date_rule_value_daysweek][{{data.addon_id}}][{{data.option_id}}]',
								'type'     => 'select',
								'multiple' => true,
								'class'    => 'wc-enhanced-select',
								'options'  => array(
									'1' => esc_html__( 'Monday', 'yith-woocommerce-product-add-ons' ),
									'2' => esc_html__( 'Tuesday', 'yith-woocommerce-product-add-ons' ),
									'3' => esc_html__( 'Wednesday', 'yith-woocommerce-product-add-ons' ),
									'4' => esc_html__( 'Thursday', 'yith-woocommerce-product-add-ons' ),
									'5' => esc_html__( 'Friday', 'yith-woocommerce-product-add-ons' ),
									'6' => esc_html__( 'Saturday', 'yith-woocommerce-product-add-ons' ),
									'0' => esc_html__( 'Sunday', 'yith-woocommerce-product-add-ons' ),
								),
								'value'    => '',
							),
							true
						);
						?>
					</div>

					<div class="field months" style="display: none";>
						<?php
						yith_plugin_fw_get_field(
							array(
								'id'       => 'date-rule-value-months-{{data.addon_id}}-{{data.option_id}}',
								'name'     => 'options[date_rule_value_months][{{data.addon_id}}][{{data.option_id}}]',
								'type'     => 'select',
								'multiple' => true,
								'class'    => 'wc-enhanced-select',
								'options'  => array(
									'1'  => esc_html__( 'January', 'yith-woocommerce-product-add-ons' ),
									'2'  => esc_html__( 'February', 'yith-woocommerce-product-add-ons' ),
									'3'  => esc_html__( 'March', 'yith-woocommerce-product-add-ons' ),
									'4'  => esc_html__( 'April', 'yith-woocommerce-product-add-ons' ),
									'5'  => esc_html__( 'May', 'yith-woocommerce-product-add-ons' ),
									'6'  => esc_html__( 'June', 'yith-woocommerce-product-add-ons' ),
									'7'  => esc_html__( 'July', 'yith-woocommerce-product-add-ons' ),
									'8'  => esc_html__( 'August', 'yith-woocommerce-product-add-ons' ),
									'9'  => esc_html__( 'September', 'yith-woocommerce-product-add-ons' ),
									'10' => esc_html__( 'October', 'yith-woocommerce-product-add-ons' ),
									'11' => esc_html__( 'November', 'yith-woocommerce-product-add-ons' ),
									'12' => esc_html__( 'December', 'yith-woocommerce-product-add-ons' ),
								),
								'value'    => '',
							),
							true
						);
						?>
					</div>

					<div class="field years" style="display: none";>
						<?php
						$years = array();
						$datey = gmdate( 'Y' );
						for ( $yy = $datey; $yy < $datey + 10; $yy++ ) {
							$years[ $yy ] = $yy;
						}
						yith_plugin_fw_get_field(
							array(
								'id'       => 'date-rule-value-years{{data.addon_id}}-{{data.option_id}}',
								'name'     => 'options[date_rule_value_years][{{data.addon_id}}][{{data.option_id}}]',
								'type'     => 'select',
								'multiple' => true,
								'class'    => 'wc-enhanced-select',
								'options'  => $years,
								'value'    => '',
							),
							true
						);
						?>
					</div>

					<img src="<?php echo esc_attr( YITH_WAPO_URL ); ?>/assets/img/delete.png" class="delete-rule">

					<div class="clear"></div>
				</div>
			</script>
			<?php
		}
	}
}
