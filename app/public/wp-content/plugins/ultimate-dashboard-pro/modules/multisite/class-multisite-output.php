<?php
/**
 * Multisite output.
 *
 * @package Ultimate_Dashboard_Pro
 */

namespace UdbPro\Multisite;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Base\Base_Output;
use UdbPro\Setup;

/**
 * Class to setup multisite output.
 */
class Multisite_Output extends Base_Output {

	/**
	 * The class instance.
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * The current module url.
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Module constructor.
	 */
	public function __construct() {

		$this->url = ULTIMATE_DASHBOARD_PRO_PLUGIN_URL . '/modules/multisite';

	}

	/**
	 * Get instance of the class.
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	/**
	 * Init the class setup.
	 */
	public static function init() {

		$class = new self();
		$class->setup();

	}

	/**
	 * Setup the module output.
	 */
	public function setup() {

		require_once __DIR__ . '/output/class-ms-widget-output.php';
		Output\Ms_Widget_Output::init();

		require_once __DIR__ . '/output/class-ms-setting-output.php';
		Output\Ms_Setting_Output::init();

		$module        = new Setup();
		$saved_modules = $module->saved_modules();

		if ( isset( $saved_modules['white_label'] ) && 'true' === $saved_modules['white_label'] ) {
			require_once __DIR__ . '/output/class-ms-branding-output.php';
			Output\Ms_Branding_Output::init();
		}

		if ( isset( $saved_modules['login_customizer'] ) && 'true' === $saved_modules['login_customizer'] ) {
			require_once __DIR__ . '/output/class-ms-login-customizer-output.php';
			Output\Ms_Login_Customizer_Output::init();
		}

		if ( isset( $saved_modules['login_redirect'] ) && 'true' === $saved_modules['login_redirect'] && class_exists( '\Udb\LoginRedirect\Login_Redirect_Output' ) ) {
			require_once __DIR__ . '/output/class-ms-login-redirect-output.php';
			Output\Ms_Login_Redirect_Output::init();
		}

		// require_once __DIR__ . '/output/class-ms-admin-page-output.php';
		// Output\Ms_Admin_Page_Output::init();

		if ( isset( $saved_modules['admin_menu_editor'] ) && 'true' === $saved_modules['admin_menu_editor'] ) {
			require_once __DIR__ . '/output/class-ms-admin-menu-output.php';
			Output\Ms_Admin_Menu_Output::init();
		}

		if ( version_compare( ULTIMATE_DASHBOARD_PLUGIN_VERSION, '3.2.1', '>' ) ) {
			if ( isset( $saved_modules['admin_bar_editor'] ) && 'true' === $saved_modules['admin_bar_editor'] ) {
				require_once __DIR__ . '/output/class-ms-admin-bar-output.php';
				Output\Ms_Admin_Bar_Output::init();
			}
		}

	}

}
