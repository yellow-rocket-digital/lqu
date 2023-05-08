<?php
/**
 * Bricks builder helper.
 *
 * @package Ultimate_Dashboard_Pro
 */

namespace UdbPro\Helpers;

use Bricks\Assets;
use Bricks\Database;
use ReflectionClass;
use WP_Post;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Class to setup branding helper.
 */
class Bricks_Helper {

	/**
	 * WP_Post instance.
	 *
	 * @var WP_Post
	 */
	private $post;

	/**
	 * Bricks data.
	 *
	 * @var array
	 */
	private $bricks_data;

	/**
	 * Class constructor.
	 *
	 * @param WP_Post|null $post Instance of WP_Post.
	 */
	public function __construct( $post = null ) {

		$this->post = $post;

		$this->bricks_data = $post ? $this->get_data() : [];

	}

	/**
	 * Check whether or not "Bricks" theme is active.
	 *
	 * @return bool
	 */
	public function is_active() {

		if (
			! class_exists( '\Bricks\Helpers' )
			|| ! class_exists( '\Bricks\Database' )
			|| ! class_exists( '\Bricks\Settings' )
			|| ! class_exists( '\Bricks\Setup' )
			|| ! class_exists( '\Bricks\Frontend' )
			|| ! class_exists( '\Bricks\Templates' )
			|| ! defined( 'BRICKS_DB_EDITOR_MODE' )
			|| ! defined( 'BRICKS_DB_PAGE_CONTENT' )
			|| ! defined( 'BRICKS_URL_ASSETS' )
			|| ! defined( 'BRICKS_PATH_ASSETS' )
		) {
			return false;
		}

		return true;

	}

	/**
	 * Verify if a post was built with Bricks Builder.
	 *
	 * @return bool
	 */
	public function built_with_bricks() {

		if ( ! $this->is_active() ) {
			return false;
		}

		if ( ! get_post_meta( $this->post->ID, BRICKS_DB_EDITOR_MODE, true ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Prepare Bricks output.
	 */
	public function prepare_output() {

		$setup_reflection = new ReflectionClass( '\Bricks\Setup' );
		$bricks_setup     = $setup_reflection->newInstanceWithoutConstructor();

		add_action( 'admin_enqueue_scripts', [ $bricks_setup, 'enqueue_scripts' ] );

		$frontend_reflection = new ReflectionClass( '\Bricks\Frontend' );
		$bricks_frontend     = $frontend_reflection->newInstanceWithoutConstructor();

		\Bricks\Database::$active_templates['content'] = $this->post->ID;
		\Bricks\Settings::set_controls();

		add_action( 'admin_enqueue_scripts', [ $bricks_frontend, 'enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $bricks_frontend, 'enqueue_inline_css' ], 11 );

		add_action( 'bricks_after_site_wrapper', [ $bricks_frontend, 'add_photoswipe_html' ] );

		// Load custom header body script (for analytics) only on the frontend.
		add_action( 'admin_head', [ $bricks_frontend, 'add_header_scripts' ] );
		add_action( 'bricks_body', [ $bricks_frontend, 'add_body_header_scripts' ] );

		// Change the priority to 21 to load the custom scripts after the default Bricks scripts in the footer (@since 1.5)
		// @see core: add_action( 'wp_footer', 'wp_print_footer_scripts', 20 );
		add_action( 'admin_footer', [ $bricks_frontend, 'add_body_footer_scripts' ], 21 );

		add_action( 'render_header', [ $bricks_frontend, 'render_header' ] );
		add_action( 'render_footer', [ $bricks_frontend, 'render_footer' ] );

	}

	/**
	 * Render the content of a post.
	 */
	public function render_content() {

		if ( ! $this->is_active() ) {
			echo apply_filters( 'the_content', $this->post->post_content );
			return;
		}

		if ( $this->bricks_data ) {
			\Bricks\Frontend::render_content( $this->bricks_data );
			return;
		}

		echo apply_filters( 'the_content', $this->post->post_content );

	}

	/**
	 * Get bricks data.
	 *
	 * @return array
	 */
	public function get_data() {

		if ( ! defined( 'BRICKS_DB_PAGE_CONTENT' ) ) {
			return [];
		}

		/**
		 * We can't use \Bricks\Helpers::get_bricks_data( $this->post->ID, 'content' ) here
		 * because the checking there doesn't cover our need.
		 */
		$bricks_data = get_post_meta( $this->post->ID, BRICKS_DB_PAGE_CONTENT, true );

		if ( $bricks_data ) {
			return $bricks_data;
		}

		return [];

	}

	/**
	 * Get bricks templates.
	 *
	 * @return array
	 */
	public function get_templates() {

		$query = \Bricks\Templates::get_templates_query();

		return ( $query->found_posts ? $query->posts : [] );

	}

}
