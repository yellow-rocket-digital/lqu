<?php /** @noinspection ALL */ //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Implements the YITH_YWRAQ_Admin class.
 *
 * @class   YITH_YWRAQ_Admin
 * @package YITH
 * @since   1.0.0
 * @author  YITH
 * @package YITH WooCommerce Request A Quote Premium
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_YWRAQ_Admin' ) ) {

	/**
	 * Class YITH_YWRAQ_Admin
	 */
	class YITH_YWRAQ_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWRAQ_Admin
		 */
		protected static $instance;

		/**
		 * Panel
		 *
		 * @var $_panel YIT_Plugin_Panel_WooCommerce
		 */
		protected $_panel;


		/**
		 * Panel Page
		 *
		 * @var string Panel page
		 */
		public $_panel_page = 'yith_woocommerce_request_a_quote';

		/**
		 * List of Messages
		 *
		 * @var string List of messages
		 */
		protected $messages = array();


		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_YWRAQ_Admin
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
		 * @since  1.0
		 * @author Emanuela Castorina
		 */
		public function __construct() {

			$this->create_menu_items();

			// Add action links.
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_YWRAQ_DIR . '/' . basename( YITH_YWRAQ_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );

			$option_value = get_option( 'ywraq_page_id' );
			if ( empty( $option_value ) ) {
				add_action( 'init', array( $this, 'add_page' ) );
			}

			// notices.
			add_action( 'admin_notices', array( $this, 'check_coupon' ) );
			add_action( 'admin_notices', array( $this, 'check_deprecated_template' ) );
			add_action( 'wp_ajax_ywraq_dismiss_notice_message', array( $this, 'ajax_dismiss_notice' ) );

			// add custom tabs.
			add_action( 'yith_ywraq_exclusions_table', array( $this, 'exclusions_table' ) );
			add_action( 'yith_ywraq_request_list_table', array( $this, 'request_list_table' ) );
			add_action( 'plugins_loaded', array( $this, 'load_privacy_dpa' ), 20 );
			add_filter( 'yith_plugin_fw_metabox_class', array( $this, 'add_custom_metabox_class' ), 10, 2 );
			add_filter( 'yith_plugin_fw_get_field_template_path', array( $this, 'get_yith_panel_custom_template' ), 10, 2 );
			add_filter( 'yith_plugin_fw_metabox_extra_row_classes', array( $this, 'add_class_to_metaboxes_rows' ), 10, 2 );
			add_action( 'admin_action_ywraq_export_quotes', array( $this, 'export_quotes_via_csv' ) );

			// YITH Multi Currency Integration
			add_filter( 'yith_wcmcs_apply_currency_filters', array( $this, 'apply_multi_currency_filters_in_quote_requests_list' ) );

			add_filter( 'ywraq_request_list_item_total', array( $this, 'filter_request_list_totals' ), 10, 2 );

			add_action( 'updated_option', array( $this, 'set_a_template_as_default' ), 10, 3 );
			add_filter( 'yith_plugin_fw_panel_active_tab_class', array( $this, 'add_class_on_quote_tab' ), 10, 3 );

			add_action( 'wp_ajax_ywraq_template_pdf_preview', array( $this, 'ajax_get_template_pdf_preview' ), 10 );

			add_filter( 'display_post_states', array( $this, 'add_display_post_states' ), 10, 2 );


			add_action( 'admin_notices', array( $this, 'ywraq_check_template_pdf' ), 15 );
		}

		/**
		 * Show a notice
		 */
		function ywraq_check_template_pdf() {
			// this popup will be visible only for old customers.
			$ywraq_option_version = get_option( 'ywraq_update_3_0', '2.0.0' );

			if ( ! $ywraq_option_version ) {
				return;
			}

			$check_pdf = get_option( 'ywraq_check_template_pdf', 'yes' );
			if ( 'no' === $check_pdf ) {
				return;
			}

			if ( ! empty( $_COOKIE['ywraq_check_template_pdf_notice'] ) && 'yes' === $_COOKIE['ywraq_check_template_pdf_notice'] ) { //phpcs:ignore
				update_option( 'ywraq_check_template_pdf', 'no' );

				return;
			}

			$current_screen    = get_current_screen();
			$sections_to_check = array(
				'dashboard',
				'update-core',
				'plugins',
				'yith-plugins_page_yith_woocommerce_request_a_quote',
			);

			$show_notice = ( ! is_null( $current_screen ) && ( in_array( $current_screen->id, $sections_to_check, true ) ) );

			if ( true === $show_notice ) {
				?>
                <div id="ywraq-check-pdf" class="notice notice-warning is-dismissible" style="position: relative;">
                    <p>
                        <span class="yith-logo" style="align-items: center;justify-content: center;border-radius: 50%;background: #265b7a; float: left; width: 30px;height: 30px;display: flex;margin: 0 10px 0 0;"><img src="<?php echo esc_attr( yith_plugin_fw_get_default_logo() ); ?>"/></span>
						<?php

						$text = _x( "Dear customer,<br /><br />With version 4.0 of YITH WooCommerce Request a Quote, the DOMPDF library has been replaced with the MPDF one. <br />We recommend checking the layout of the generated PDF and, in case of any issue, contact our developers through our support desk.",
							'do not translate the plugin name', 'yith-woocommerce-request-a-quote' );

						echo wp_kses_post( $text );
						?>
                    </p>
                    <span class="notice-dismiss"></span>

                </div>
				<?php
			}
		}


		/**
		 * Add a post display state for special RAQ pages in the page list table.
		 *
		 * @param   array    $post_states  An array of post display states.
		 * @param   WP_Post  $post         The current post object.
		 */
		public function add_display_post_states( $post_states, $post ) {

			if ( (int) YITH_Request_Quote()->get_raq_page_id() === $post->ID ) {
				$post_states['ywraq_page'] = _x( 'Request a Quote Page', 'Add a post display state for RAQ page', 'yith-woocommerce-request-a-quote' );
			}

			return $post_states;
		}

		/**
		 * Add a class inside the quote tab
		 *
		 * @param   string  $active_class  Active class.
		 * @param   string  $current_tab   Current tab.
		 * @param   string  $tab_slug      Slug of tab.
		 *
		 * @return string
		 * @since 4.0
		 */
		public function add_class_on_quote_tab( $active_class, $current_tab, $tab_slug ) {
			if ( 'quote' === $tab_slug ) {
				$template_to_use = get_option( 'ywraq_pdf_template_to_use', ywraq_is_gutenberg_active() ? 'builder' : 'default' );
				if ( 'default' === $template_to_use ) {
					$active_class = ' hide-tab-templates';
				}
			}

			return $active_class;
		}

		/**
		 * Set the template as default when the option 'ywraq_pdf_custom_templates' changes
		 *
		 * @param   string  $option     Options name.
		 * @param   string  $old_value  Old value.
		 * @param   string  $value      New value.
		 *
		 * @return void
		 * @since 4.0
		 */
		public function set_a_template_as_default( $option, $old_value, $value ) {

			if ( 'ywraq_pdf_custom_templates' !== $option || $old_value === $value ) {
				return;
			}

			$template = ywraq_get_pdf_template( $value );
			$template->set_as_default( true );
		}

		/**
		 * Create the pdf from raq list.
		 */
		public function ajax_get_template_pdf_preview() {

			if ( isset( $_REQUEST['action'], $_REQUEST['pdf_template_preview'] ) && 'ywraq_template_pdf_preview' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) {
				$template_id      = sanitize_text_field( wp_unslash( $_REQUEST['pdf_template_preview'] ) );
				$template         = ywraq_get_pdf_template( $template_id );
				$preview_products = $_REQUEST['preview_product'];
				$pdf_url          = $template->get_preview( $preview_products );
				wp_send_json(
					array( 'pdf' => $pdf_url )
				);
			}
		}


		/**
		 * Add new plugin-fw style.
		 *
		 * @param   string   $class  .
		 * @param   WP_Post  $post   .
		 *
		 * @return string
		 */
		public function add_custom_metabox_class( $class, $post ) {
			$allow_post_types = array( 'shop_order', 'product' );

			if ( in_array( $post->post_type, $allow_post_types, true ) ) {
				$class .= ' ' . yith_set_wrapper_class();
			}

			return $class;
		}


		/**
		 *  Load the Privacy DPA
		 */
		public function load_privacy_dpa() {
			if ( class_exists( 'YITH_Privacy_Plugin_Abstract' ) ) {
				require_once YITH_YWRAQ_INC . 'class.yith-request-quote-privacy-dpa.php';
			}
		}


		/**
		 * Create Menu Items
		 *
		 * Print admin menu items
		 *
		 * @since  1.0
		 * @author Emanuela Castorina
		 */
		private function create_menu_items() {
			// Add a panel under YITH Plugins tab.
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use      /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->_panel ) ) {
				return;
			}
			/**
			 * APPLY_FILTERS:ywraq_admin_tabs
			 *
			 * Filter the plugin panel tabs
			 *
			 * @param   array  $tabs  Tabs.
			 *
			 * @return array
			 */
			$admin_tabs = apply_filters( 'ywraq_admin_tabs', array(
				'request-list' => __( 'Requests', 'yith-woocommerce-request-a-quote' ),
				'general'      => __( 'General Options', 'yith-woocommerce-request-a-quote' ),
				'request'      => __( '"Request a Quote" Page', 'yith-woocommerce-request-a-quote' ),
				'quote'        => __( 'Quote Options', 'yith-woocommerce-request-a-quote' ),
				'exclusions'   => __( 'Exclusion List', 'yith-woocommerce-request-a-quote' ),
			) );

			/**
			 * APPLY_FILTERS:ywraq_register_panel_capability
			 *
			 * Filter plugin panel capability
			 *
			 * @param   string  $capability  Capability.
			 *
			 * @return string
			 */
			$capability = apply_filters( 'ywraq_register_panel_capability', 'manage_options' );

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => 'YITH WooCommerce Request a Quote',
				'menu_title'       => 'Request a Quote',
				'capability'       => $capability,
				'parent'           => '',
				'parent_page'      => 'yith_plugin_panel',
				'page'             => $this->_panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YITH_YWRAQ_DIR . '/plugin-options',
				'class'            => yith_set_wrapper_class(),
				'plugin_slug'      => YITH_YWRAQ_SLUG,
				'help_tab'         => array(
					'main_video' => array(
						'desc' => _x( 'Check this video to learn how to enable a <b>"Request a quote"</b> system in your shop:', '[HELP TAB] Video title', 'yith-woocommerce-request-a-quote' ),
						'url'  => array(
							'it' => 'https://www.youtube.com/embed/D0WGsjCiCsU',
							'es' => 'https://www.youtube.com/embed/_U6p4Qr_R54',
							'en' => 'https://www.youtube.com/embed/EPiF1dVEywM',
						),
					),
					'playlists'  => array(
						'it' => 'https://www.youtube.com/watch?v=D0WGsjCiCsU&list=PL9c19edGMs08r8ROMV3HTMlbldbLKIvr2&ab_channel=YITHITALIA',
						'es' => 'https://www.youtube.com/watch?v=_U6p4Qr_R54&list=PL9Ka3j92PYJOf0KlertE_XpaRFqC9xolk&ab_channel=YITHESPA%C3%91A',
						'en' => 'https://www.youtube.com/watch?v=EPiF1dVEywM&list=PLDriKG-6905mc-dCLO_Fy2VUNKh-V7HR1&ab_channel=YITH',
					),
					'hc_url'     => 'https://support.yithemes.com/hc/en-us/categories/360003474478-YITH-WOOCOMMERCE-REQUEST-A-QUOTE',
				),
			);

			/* === Fixed: not updated theme  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once YITH_YWRAQ_DIR . '/plugin-fw/lib/yit-plugin-panel-wc.php';
			}

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );

			add_action( 'woocommerce_admin_field_ywraq_upload', array( $this->_panel, 'yit_upload' ), 10, 1 );

			$this->check_db_update();

		}

		/**
		 * Check if there's a new version of plugin to update something.
		 *
		 * @since  2.0.0
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		private function check_db_update() {
			$current_option_version = get_option( 'yit_ywraq_option_version', '0' );
			$forced                 = isset( $_GET['update_ywraq_options'] ) && 'forced' === $_GET['update_ywraq_options'];//phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( version_compare( $current_option_version, YITH_YWRAQ_VERSION, '>=' ) && ! $forced ) {
				return;
			}

			// Save all products with the meta _ywraq_hide_quote_button inside the exclusion list.
			$is_populated = get_option( 'yith_ywraw_exclusion_list_populated' );
			if ( ! $is_populated ) {
				$this->populate_exclusion_list();
			}

			update_option( 'yit_ywraq_option_version', YITH_YWRAQ_VERSION );

		}

		/**
		 * Save all products with meta _ywraq_hide_quote_button inside the exclusion list
		 *
		 * @since  2.0.0
		 * @author Emanuela Castorina
		 */
		private function populate_exclusion_list() {
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => '_ywraq_hide_quote_button',
						'value'   => 1,
						'compare' => 'LIKE',
					),
				),
			);

			$products       = get_posts( $args );
			$exclusion_prod = explode( ',', get_option( 'yith-ywraq-exclusions-prod-list', '' ) );
			if ( $products ) {
				$exclusion_prod = array_unique( array_merge( $exclusion_prod, $products ) );
			}

			update_option( 'yith-ywraq-exclusions-prod-list', implode( ',', $exclusion_prod ) );
			update_option( 'yith_ywraw_exclusion_list_populated', true );
		}

		/**
		 * Add a page "Request a Quote".
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_page() {
			global $wpdb;

			$option_value = get_option( 'ywraq_page_id' );
			if ( get_post( $option_value ) ) {
				return;
			}

			$page_found = $wpdb->get_var( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_name` = 'request-quote' LIMIT 1;" );
			if ( $page_found ) :
				if ( ! $option_value ) {
					update_option( 'ywraq_page_id', $page_found );
				}

				return;
			endif;

			if ( version_compare( get_bloginfo( 'version' ), '5.0', '>=' ) ) {
				$post_content = '<!-- wp:shortcode  -->[yith_ywraq_request_quote]<!-- /wp:shortcode -->';
			} else {
				$post_content = '[yith_ywraq_request_quote]';
			}
			$page_data = array(
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_author'    => 1,
				'post_name'      => esc_sql( _x( 'request-quote', 'page_slug', 'yith-woocommerce-request-a-quote' ) ),
				'post_title'     => _x( 'Request a Quote', 'Request a quote page name', 'yith-woocommerce-request-a-quote' ),
				'post_content'   => $post_content,
				'post_parent'    => 0,
				'comment_status' => 'closed',
			);
			$page_id   = wp_insert_post( $page_data );

			update_option( 'ywraq_page_id', $page_id );
		}

		/**
		 * Premium Tab Template
		 *
		 * Load the premium tab template on admin page
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function premium_tab() {
			$premium_tab_template = YITH_YWRAQ_TEMPLATE_PATH . '/admin/' . $this->_premium;
			if ( file_exists( $premium_tab_template ) ) {
				include_once $premium_tab_template;
			}
		}

		/**
		 * Action Links
		 *
		 * Add the action links to plugin admin page.
		 *
		 * @param   array  $links  .
		 *
		 * @return mixed
		 * @use      plugin_action_links_{$plugin_file_name}
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @since    1.0
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, $this->_panel_page, true, YITH_YWRAQ_SLUG );

			return $links;
		}

		/**
		 * Plugin_row_meta
		 *
		 * Add the action links to plugin admin page.
		 *
		 * @param   array   $new_row_meta_args  .
		 * @param   array   $plugin_meta        .
		 * @param   string  $plugin_file        .
		 * @param   array   $plugin_data        .
		 * @param   string  $status             .
		 * @param   string  $init_file          .
		 *
		 * @return   array
		 * @since    1.6.5
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use      plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_YWRAQ_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug']       = YITH_YWRAQ_SLUG;
				$new_row_meta_args['is_premium'] = true;
			}

			if ( defined( 'YITH_YWRAQ_FREE_INIT' ) && YITH_YWRAQ_FREE_INIT === $plugin_file ) {
				$new_row_meta_args['support'] = array(
					'url' => 'https://wordpress.org/support/plugin/yith-woocommerce-request-a-quote/',
				);
			}

			return $new_row_meta_args;
		}


		/**
		 * Display Admin Notice if coupons are enabled
		 *
		 * @access public
		 * @return void
		 *
		 * @since  1.3.0
		 */
		public function check_coupon() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( 'yes' !== get_option( 'woocommerce_enable_coupons' ) && 'yes' !== get_option( 'ywraq_dismiss_disabled_coupons_warning_message', 'no' ) ) { ?>
                <div id="message" class="notice notice-warning is-dismissible ywraq_disabled_coupons">
                    <p>
                        <strong><?php esc_html_e( 'YITH WooCommerce Request a Quote', 'yith-woocommerce-request-a-quote' ); ?></strong>
                    </p>

                    <p>
						<?php esc_html_e( 'WooCommerce coupon system has been disabled. In order to make YITH WooCommerce Request a Quote work correctly, you have to enable coupons.', 'yith-woocommerce-request-a-quote' ); ?>
                    </p>

                    <p>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=general' ) ); ?>"><?php echo esc_html__( 'Enable the use of coupons', 'yith-woocommerce-request-a-quote' ); ?></a>
                    </p>
                </div>
                <script>
                  (function($) {
                    $('.ywraq_disabled_coupons').on('click', '.notice-dismiss', function() {
                      jQuery.post("<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>", {
                        action: 'ywraq_dismiss_notice_message',
                        dismiss_action: 'ywraq_dismiss_disabled_coupons_warning_message',
                        nonce: "<?php echo esc_js( wp_create_nonce( 'ywraq_dismiss_notice' ) ); ?>",
                      });
                    });
                  })(jQuery);
                </script>
				<?php
			}
		}

		/**
		 * Show a notice on the dashboard if the old form template is override in the theme.
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		public function check_deprecated_template() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$located = wc_locate_template( 'request-quote-form.php', '', YITH_YWRAQ_TEMPLATE_PATH . '/' );
			$message = esc_html__( 'The template \'request-quote-form.php\' that you\'ve override in your theme was deprecated since version 2.0 and will be ignored.', 'yith-woocommerce-request-a-quote' );

			if ( YITH_YWRAQ_TEMPLATE_PATH . '/request-quote-form.php' !== $located && 'yes' !== get_option( 'ywraq_dismiss_old_template_warning_message', 'no' ) ) {
				?>
                <div class="notice notice-warning is-dismissible ywraq-dismiss-old-template-warning-message">
                    <p>
                        <strong><?php esc_html_e( 'YITH WooCommerce Request a Quote', 'yith-woocommerce-request-a-quote' ); ?></strong>
                    </p>
                    <p>
						<?php echo wp_kses_post( $message ); ?>
                    </p>
                </div>
                <script>
                  (function($) {
                    $('.ywraq-dismiss-old-template-warning-message').on('click', '.notice-dismiss', function() {
                      jQuery.post("<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>", {
                        action: 'ywraq_dismiss_notice_message',
                        dismiss_action: 'ywraq_dismiss_old_template_warning_message',
                        nonce: "<?php echo esc_js( wp_create_nonce( 'ywraq_dismiss_notice' ) ); ?>",
                      });
                    });
                  })(jQuery);
                </script>
				<?php
			}
		}

		/**
		 * AJAX handler for dismiss notice action.
		 *
		 * @since  2.0.0
		 * @access public
		 */
		public function ajax_dismiss_notice() {
			if ( empty( $_POST['dismiss_action'] ) ) {
				return;
			}

			check_ajax_referer( 'ywraq_dismiss_notice', 'nonce' );
			switch ( $_POST['dismiss_action'] ) {
				case 'ywraq_dismiss_old_template_warning_message':
					update_option( 'ywraq_dismiss_old_template_warning_message', 'yes' );
					break;
				case 'ywraq_dismiss_disabled_coupons_warning_message':
					update_option( 'ywraq_dismiss_disabled_coupons_warning_message', 'yes' );
					break;
			}
			wp_die();
		}

		/**
		 * Custom tab to show the request list.
		 *
		 * @since 3.1.0
		 */
		public function request_list_table() {

			$get = $_GET; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( isset( $get['page'] ) && $get['page'] === $this->_panel_page && file_exists( YITH_YWRAQ_VIEW_PATH . '/panel/custom-tabs/ywraq-request-list-table.php' ) ) {
				include_once YITH_YWRAQ_INC . '/admin/class.ywraq-request-list-table.php';
				$table  = new YWRAQ_Request_List_Table();

				$quotes = wc_get_orders(
					array(
						'limit'     => 1,
						'ywraq_raq' => 'yes',
						'status'    => ywraq_get_quote_status_list(),
					)
				);

				$is_blank = 0 === count( $quotes );

				include_once YITH_YWRAQ_VIEW_PATH . '/panel/custom-tabs/ywraq-request-list-table.php';
			}

		}

		/**
		 * Add categories exclusion table.
		 *
		 * @access public
		 * @since  2.0.0
		 * @author Francesco Licandro
		 */
		public function exclusions_table() {

			$get           = $_GET; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$template_file = YITH_YWRAQ_VIEW_PATH . '/panel/custom-tabs/ywraq-exclusions-table.php';

			if ( isset( $get['page'] ) && $get['page'] === $this->_panel_page && isset( $get['tab'] ) && 'exclusions' === $get['tab'] && file_exists( $template_file ) ) {
				include_once YITH_YWRAQ_INC . '/admin/class.ywraq-exclusions-list-table.php';
				$table = new YWRAQ_Exclusions_List_Table();
				$table->handle_bulk_action();

				$exclusions_prod = array_filter( explode( ',', get_option( 'yith-ywraq-exclusions-prod-list' ) ) );
				$exclusions_cat  = array_filter( explode( ',', get_option( 'yith-ywraq-exclusions-cat-list' ) ) );
				$exclusions_tag  = array_filter( explode( ',', get_option( 'yith-ywraq-exclusions-tag-list' ) ) );
				$list            = array_merge( $exclusions_prod, $exclusions_cat, $exclusions_tag );
				$is_blank        = count( $list ) == 0;
				wp_enqueue_style( 'ywraq_exclusion_list' );
				wp_enqueue_script( 'ywraq_exclusion_list' );
				$table->prepare_items();

				include_once $template_file;
			}
		}

		/**
		 * Add custom panel fields.
		 *
		 * @param   string  $template  Template.
		 * @param   string  $field     Fields.
		 *
		 * @return string
		 */
		public function get_yith_panel_custom_template( $template, $field ) {
			$custom_option_types = array(
				'default-form',
			);

			$field_type = $field['type'];

			if ( isset( $field['type'] ) && in_array( $field['type'], $custom_option_types, true ) ) {
				$template = YITH_YWRAQ_VIEW_PATH . "/panel/types/{$field_type}.php";
			}

			return $template;
		}

		/**
		 * Add a new class to the metabox rows
		 *
		 * @param   string  $classes  Classes to add.
		 * @param   array   $field    Specific field.
		 */
		public function add_class_to_metaboxes_rows( $classes, $field ) {
			if ( isset( $field['extra-classes'] ) ) {
				array_push( $classes, $field['extra-classes'] );
			}

			return $classes;
		}

		/**
		 * Filter Quote Request totals in WP_List
		 *
		 * @param   string    $total      Quote Request total.
		 * @param   WC_Order  $list_item  List Item.
		 */
		public function filter_request_list_totals( $total, $list_item ) {
			if ( defined( 'YITH_WCMCS_VERSION' ) && function_exists( 'yith_wcmcs_set_currency' ) ) {
				yith_wcmcs_set_currency( $list_item->get_currency() );
				$list_item = wc_get_order( $list_item->get_id() );

				if ( $list_item ) {
					$total = wc_price( $list_item->get_total() );
				}
			}

			return $total;
		}

		/**
		 * Apply Multi Currency Filters also in Quote Requests List
		 *
		 * @param   bool  $apply_multi_currency_filters  Apply multi currency filetrs
		 *
		 * @return bool
		 */
		public function apply_multi_currency_filters_in_quote_requests_list( $apply_multi_currency_filters ) {
			$is_raq_list_page = isset( $_GET['page'] ) && 'yith_woocommerce_request_a_quote' === sanitize_text_field( wp_unslash( $_GET['page'] ) );

			return $apply_multi_currency_filters || $is_raq_list_page;
		}

		/**
		 * Export quotes via csv.
		 *
		 * @since 3.1.0
		 */
		public function export_quotes_via_csv() {
            $args = array(
				'limit'     => - 1,
				'ywraq_raq' => 'yes',
				'status'    => ywraq_get_quote_status_list(),
			);

			$quotes = wc_get_orders( $args );
            /**
             * APPLY_FILTERS:ywraq_export_columns
             *
             * Filter the columns to export
             *
             * @param   array  $export_columns  List of columns.
             *
             * @return array
             */
			$columns = apply_filters(
				'ywraq_export_columns',
				array(
					'id'                       => __( 'Quote ID', 'yith-woocommerce-request-a-quote' ),
					'order_number'             => __( 'Quote Number', 'yith-woocommerce-request-a-quote' ),
					'order_date'               => __( 'Quote Date', 'yith-woocommerce-request-a-quote' ),
					'_ywcm_request_expire'     => __( 'Quote Expired Date', 'yith-woocommerce-request-a-quote' ),
					'status'                   => __( 'Status', 'yith-woocommerce-request-a-quote' ),
					'user_id'                  => __( 'Customer ID', 'yith-woocommerce-request-a-quote' ),
					'ywraq_customer_name'      => __( 'Customer Name', 'yith-woocommerce-request-a-quote' ),
					'ywraq_customer_email'     => __( 'Customer Email', 'yith-woocommerce-request-a-quote' ),
					'ywraq_customer_message'   => __( 'Customer Message', 'yith-woocommerce-request-a-quote' ),
					'ywraq_other_email_fields' => __( 'Additional Email field', 'yith-woocommerce-request-a-quote' ),
					'products'                 => __( 'Products', 'yith-woocommerce-request-a-quote' ),
					'currency'                 => __( 'Currency', 'yith-woocommerce-request-a-quote' ),
					'prices_include_tax'       => __( 'Prices Include tax', 'yith-woocommerce-request-a-quote' ),
					'total'                    => __( 'Total', 'yith-woocommerce-request-a-quote' ),
					'total_tax'                => __( 'Total Tax', 'yith-woocommerce-request-a-quote' ),
					'shipping_total'           => __( 'Order Shipping', 'yith-woocommerce-request-a-quote' ),
					'shipping_tax'             => __( 'Order Shipping Tax', 'yith-woocommerce-request-a-quote' ),
					'discount_total'           => __( 'Discount', 'yith-woocommerce-request-a-quote' ),
					'discount_tax'             => __( 'Discount Tax', 'yith-woocommerce-request-a-quote' ),
					'billing_country'          => __( 'Billing Country', 'yith-woocommerce-request-a-quote' ),
					'billing_first_name'       => __( 'Billing First Name', 'yith-woocommerce-request-a-quote' ),
					'billing_last_name'        => __( 'Billing Last Name', 'yith-woocommerce-request-a-quote' ),
					'billing_company'          => __( 'Billing Company', 'yith-woocommerce-request-a-quote' ),
					'billing_address_1'        => __( 'Billing Address 1', 'yith-woocommerce-request-a-quote' ),
					'billing_address_2'        => __( 'Billing Address 2', 'yith-woocommerce-request-a-quote' ),
					'billing_city'             => __( 'Billing City', 'yith-woocommerce-request-a-quote' ),
					'billing_state'            => __( 'Billing State', 'yith-woocommerce-request-a-quote' ),
					'billing_postcode'         => __( 'Billing Postcode', 'yith-woocommerce-request-a-quote' ),
					'billing_email'            => __( 'Billing Email', 'yith-woocommerce-request-a-quote' ),
					'billing_phone'            => __( 'Billing Phone', 'yith-woocommerce-request-a-quote' ),
					'shipping_country'         => __( 'Shipping Country', 'yith-woocommerce-request-a-quote' ),
					'shipping_first_name'      => __( 'Shipping First Name', 'yith-woocommerce-request-a-quote' ),
					'shipping_last_name'       => __( 'Shipping Last Name', 'yith-woocommerce-request-a-quote' ),
					'shipping_company'         => __( 'Shipping Company', 'yith-woocommerce-request-a-quote' ),
					'shipping_address_1'       => __( 'Shipping Address 1', 'yith-woocommerce-request-a-quote' ),
					'shipping_address_2'       => __( 'Shipping Address 2', 'yith-woocommerce-request-a-quote' ),
					'shipping_city'            => __( 'Shipping City', 'yith-woocommerce-request-a-quote' ),
					'shipping_state'           => __( 'Shipping State', 'yith-woocommerce-request-a-quote' ),
					'shipping_postcode'        => __( 'Shipping Postcode', 'yith-woocommerce-request-a-quote' ),
				)
			);


			if ( ! empty( $quotes ) ) {

				$formatted_quotes = array();

				foreach ( $quotes as $quote ) {

					foreach ( $columns as $key => $column ) {
						$value = '';

						switch ( $key ) {
							case 'status':
								$value = wc_get_order_status_name( $quote->get_status() );
								break;
							case 'products':
								$value = ywraq_export_get_products( $quote );
								break;
							case 'order_date':
								$value = date_i18n( 'Y-m-d H:i:s', $quote->get_date_created()->getTimestamp() );
								break;
							case 'ywraq_other_email_fields':
								$fields = $quote->get_meta( 'ywraq_other_email_fields' );
								if ( $fields ) {
									$values = array();
									foreach ( $fields as $label => $field ) {
										$values[] = $label . ': ' . ( is_array( $field ) ? implode( ',', $field ) : $field );
									}
									$value = implode( ' - ', $values );
								}

								break;
							default:
								if ( method_exists( $quote, 'get_' . $key ) ) {
									$getter = 'get_' . $key;
									/**
									 * APPLY_FILTERS:ywraq_export_column_value
									 *
									 * Filter the value of the column to export.
									 *
									 * @param   mixed  $value  Value
									 * @param   WC_Order  $quote  Quote
									 * @param   string  $key Current column-
									 *
									 * @return mixed
									 */
									$value  = apply_filters( 'ywraq_export_column_value', $quote->$getter(), $quote, $key );
								} else {
									$value = $quote->get_meta( $key );
									if ( is_array( $value ) ) {
										$value = implode( ' - ', $value );
									}
								}
						}

						$formatted_quotes[ $quote->get_id() ][] = apply_filters( 'ywraq_export_column_value', $value, $quote, $key );
					}
				}

				if ( ! empty( $formatted_quotes ) ) {
					$sitename = sanitize_key( get_bloginfo( 'name' ) );
					$sitename .= ( ! empty( $sitename ) ) ? '-' : '';
					$filename = $sitename . 'yith-request-a-quote-' . gmdate( 'Y-m-d-H-i' ) . '.csv';

					// Add Labels to CSV.
					$formatted_labels[] = array_values( $columns );
					$formatted_quotes   = array_merge( $formatted_labels, $formatted_quotes );

					header( 'Content-Description: File Transfer' );
					header( 'Content-Disposition: attachment; filename=' . $filename );
					header( 'Content-Type: text/xml; charset=' . apply_filters( 'ywraq_csv_charset', get_option( 'blog_charset' ) ), true );

					$df = fopen( 'php://output', 'w' );

					foreach ( $formatted_quotes as $row ) {
						fputcsv( $df, $row, ';' );
					}

					fclose( $df );
				}
			}

			die();

		}


		/**
		 * Enqueue styles and scripts
		 *
		 * @access public
		 * @return void
		 * @since  1.0.0
		 * @deprecated
		 */
		public function enqueue_styles_scripts() {
			_deprecated_function( 'YITH_YWRAQ_Admin::enqueue_styles_scripts', '3.0.0', 'YITH_Request_Quote_Assets::enqueue_admin_scripts' );
		}


	}


}

/**
 * Unique access to instance of YITH_YWRAQ_Admin class
 *
 * @return \YITH_YWRAQ_Admin
 */
function YITH_YWRAQ_Admin() { // phpcs:ignore
	return YITH_YWRAQ_Admin::get_instance();
}
