<?php
namespace Brightplugins_COS;

class Checkout {

	public function __construct() {
		add_filter( 'woocommerce_thankyou', array( $this, 'set_default_order_status' ), 90 );
	}

	/**
	 * Set default order status acording payment method
	 *
	 * @param  int    $order_id Order ID.
	 * @return void
	 */
	public function set_default_order_status( $order_id ) {
		if ( !$order_id ) {
			return;
		}
		$order          = wc_get_order( $order_id );
		$payment_method = $order->get_payment_method();
		$option_prefix  = 'orderstatus_default_statusgateway_' . $payment_method;
		$defaultStatus  = get_option( 'wcbv_status_default', null );
		if ( $defaultStatus ) {
			if ( isset( $defaultStatus[$option_prefix] ) && 'bpos_disabled' !== $defaultStatus[$option_prefix] ) {
				$order->update_status( $defaultStatus[$option_prefix] );
			} elseif ( isset( $defaultStatus['orderstatus_default_status'] ) && 'bpos_disabled' !== $defaultStatus['orderstatus_default_status'] ) {
				$order->update_status( $defaultStatus['orderstatus_default_status'] );
			}
		}
	}

}
