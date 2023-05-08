<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH WooCommerce Request A Quote Premium
 * @var $table
 * @var $get
 * @var $is_blank
 */

/**
 * Admin View: Quote Request List Table
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$args_export_url = array(
	'action' => 'ywraq_export_quotes'
);


?>
<div class="ywraq-admin-wrap-content yith-plugin-ui--classic-wp-list-style">
	<div class="wrap">
		<div class="icon32 icon32-posts-post" id="icon-edit"><br/></div>
		<div class="wrap-title">
			<h2><?php esc_html_e( 'Quote Requests', 'yith-woocommerce-request-a-quote' ); ?></h2>
			<div class="ywraq-add-new-quote" style="display: inline-block;">
				<a class="button-primary " href="
			<?php
				echo esc_url(
					add_query_arg(
						array(
							'post_type' => 'shop_order',
							'new_quote' => 1,
						),
						admin_url( 'post-new.php' )
					)
				);
				?>
			"><?php esc_html_e( '+ Add quote', 'yith-woocommerce-request-a-quote' ); ?></a>
			</div>
			<?php if ( ! $is_blank ) : ?>
			<div class="ywraq-export" style="display: inline-block;">
				<a class="button-secondary yith-button-ghost"
					href="<?php echo esc_url( add_query_arg( $args_export_url, admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Export CSV', 'yith-woocommerce-request-a-quote' ); ?></a>
			</div>
			<?php endif; ?>
		</div>

	</div>
	<?php if ( $is_blank ) : ?>
		<div class="ywraq-admin-no-posts">
			<div class="ywraq-admin-no-posts-container">
				<div class="ywraq-admin-no-posts-logo"><img width="80"
						src="<?php echo esc_url( YITH_YWRAQ_ASSETS_URL . '/images/mini-quote.svg' ); ?>"></div>
				<div class="ywraq-admin-no-posts-text">
									<span>
										<strong><?php echo esc_html_x( 'You don\'t have any request yet.', 'Text showed when the list of quotes is empty.', 'yith-woocommerce-request-a-quote' ); ?></strong>
									</span>
					<p><?php echo esc_html_x( 'But don\'t worry, your request will appear here soon!', 'Text showed when the list of quotes is empty.', 'yith-woocommerce-request-a-quote' ); ?></p>
				</div>
			</div>
		</div>
	<?php else : ?>
		<form method="get" id="ywraq-exclusions">
			<input type="hidden" name="page" value="<?php echo esc_attr( $get['page'] ); ?>">
			<?php
			$table->views();
			$table->prepare_items();
			$table->display();
			?>
		</form>
	<?php endif; ?>
</div>
