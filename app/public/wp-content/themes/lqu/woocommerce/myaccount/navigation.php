<?php
/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/navigation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="woocommerce-MyAccount-navigation py-5">
	<ul class="ps-4">
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php if( current_user_can('editor') || current_user_can('administrator') ) {  ?>
		<ul class="admin-tools ps-4">
			<li class="admin-tools__heading">Admin</li>
			<li class="admin-tools__link"><a href="<?= get_admin_url(); ?>edit.php?post_status=wc-ywraq-new&post_type=shop_order">New Quotes</a></li>
			<li class="admin-tools__link"><a href="<?= get_admin_url(); ?>edit.php?post_status=wc-ywraq-pending&post_type=shop_order">Pending Quotes</a></li>
			<li class="admin-tools__link"><a href="<?= get_admin_url(); ?>edit.php?post_type=shop_order">Orders</a></li>
			<li class="admin-tools__link"><a href="<?= get_admin_url(); ?>users.php">Users</a></li>
			<li class="admin-tools__link"><a href="<?= get_admin_url(); ?>admin.php?page=new-user-approve-admin">Account Requests</a></li>
		</ul>
	<?php } ?>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
