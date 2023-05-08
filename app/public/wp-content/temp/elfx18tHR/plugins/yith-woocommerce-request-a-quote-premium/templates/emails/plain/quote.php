<?php
/**
 * Plain Template Email
 *
 * @package YITH Woocommerce Request A Quote
 * @version 2.0.8
 * @since   1.0.0
 * @author  YITH
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

echo esc_html( $email_heading ) . "\n\n";

$quote_number = apply_filters( 'ywraq_quote_number', $raq_data['order-number'] );
// translators: Email title and number of quote.
echo sprintf( esc_html__( '%1$s n. %2$d', 'yith-woocommerce-request-a-quote' ), esc_html( apply_filters( 'wpml_translate_single_string', $email_title, 'admin_texts_woocommerce_ywraq_send_quote_settings', '[woocommerce_ywraq_send_quote_settings]email-title', $raq_data['lang'] ) ), esc_html( $quote_number ) ) . "\n\n";

echo esc_html( apply_filters( 'wpml_translate_single_string', $email_description, 'admin_texts_woocommerce_ywraq_send_quote_settings', '[woocommerce_ywraq_send_quote_settings]email-description', $raq_data['lang'] ) ) . "\n\n";
// translators: Request a quote date.
echo sprintf( esc_html__( 'Request date: %s', 'yith-woocommerce-request-a-quote' ), esc_html( $raq_data['order-date'] ) ) . "\n\n";

if ( '' !== $raq_data['expiration_data'] ) {
	// translators: Expiration date.
	echo sprintf( esc_html__( 'Expiration date: %s', 'yith-woocommerce-request-a-quote' ), esc_html( $raq_data['expiration_data-date'] ) ) . "\n\n";
}

if ( ! empty( $raq_data['admin_message'] ) ) {
	echo esc_html( $raq_data['admin_message'] ) . "\n\n";
}

// Include table .
wc_get_template( 'emails/plain/quote-table.php', array( 'order' => $order ), '', YITH_YWRAQ_TEMPLATE_PATH . '/' );

if ( get_option( 'ywraq_show_accept_link' ) !== 'no' ) {
	echo esc_html( ywraq_get_label( 'accept' ) ) . "\n";
	echo esc_url(
		add_query_arg(
			array(
				'request_quote' => $raq_data['order-number'],
				'status'        => 'accepted',
				'lang'          => $order->get_meta( 'wpml_language', true ),
				'raq_nonce'     => ywraq_get_token( 'accept-request-quote', $raq_data['order-number'], $raq_data['user_email'] ),
			),
			YITH_Request_Quote()->get_raq_page_url()
		)
	);
	echo "\n\n";
}

if ( get_option( 'ywraq_show_reject_link' ) !== 'no' ) {
	echo esc_html( ywraq_get_label( 'reject' ) ) . "\n";
	echo esc_url( ywraq_get_rejected_quote_page( $order ) );
	echo "\n\n";
}

$after_list = $order->get_meta( '_ywraq_request_response_after', true );
if ( '' !== $after_list ) {
	echo esc_html( apply_filters( 'ywraq_quote_after_list', $after_list, $raq_data['order-id'] ) ) . "\n\n";
}


$user_name  = $order->get_meta( 'ywraq_customer_name', true );
$user_email = $order->get_meta( 'ywraq_customer_email', true ); //phpcs:ignore

$billing_company   = $order->get_billing_company();
$billing_address_1 = $order->get_billing_address_1();
$billing_address_2 = $order->get_billing_address_2();

$billing_surname  = $order->get_billing_last_name();
$billing_city     = $order->get_billing_city();
$billing_postcode = $order->get_billing_postcode();
$billing_state    = $order->get_billing_state();
$billing_country  = $order->get_billing_country();
$billing_email    = $order->get_billing_email();
$billing_email    = empty( $billing_email ) ? $user_email : $billing_email;
$billing_phone    = $order->get_billing_phone();
$billing_phone    = empty( $billing_phone ) ? $order->get_meta( 'ywraq_billing_phone' ) : $billing_phone;
$billing_vat      = $order->get_meta( 'ywraq_billing_vat' );
$billing_vat      = empty( $billing_vat ) ? $order->get_meta( '_billing_vat' ) : $billing_vat;


$billing_address = $billing_address_1 . ( ( '' !== $billing_address_1 ) ? ' ' : '' ) . $billing_address_2;
if ( '' === $billing_address ) {
	$billing_address = $order->get_meta( 'ywraq_billing_address' );
}

echo esc_html__( 'Customer\'s details', 'yith-woocommerce-request-a-quote' ) . "\n";

echo esc_html__( 'Name:', 'yith-woocommerce-request-a-quote' );
echo esc_html( $user_name ) . "\n";

if ( '' !== $billing_company ) {
	echo esc_html__( 'Company:', 'yith-woocommerce-request-a-quote' );
	echo esc_html( $billing_company ) . "\n";
}
if ( '' !== $billing_address ) {
	echo esc_html__( 'Address:', 'yith-woocommerce-request-a-quote' );
	echo esc_html( $billing_address ) . "\n";
}
if ( '' !== $billing_city ) {
	echo esc_html__( 'City:', 'yith-woocommerce-request-a-quote' );
	echo esc_html( $billing_city ) . "\n";
}
if ( '' !== $billing_postcode ) {
	echo esc_html__( 'Postcode:', 'yith-woocommerce-request-a-quote' );
	echo esc_html( $billing_postcode ) . "\n";
}
if ( '' !== $billing_state ) {

	if ( '' !== $billing_country ) {
		$states        = WC()->countries->get_states( $billing_country );
		$billing_state = ( '' !== $states[ $billing_state ] ) ? $states[ $billing_state ] : $billing_state;
	}

	echo esc_html__( 'State:', 'yith-woocommerce-request-a-quote' );
	echo esc_html( $billing_state ) . "\n";
}
if ( '' !== $billing_country ) {

	$countries = WC()->countries->get_countries();

	echo esc_html__( 'Country:', 'yith-woocommerce-request-a-quote' );
	echo esc_html( $countries[ $billing_country ] ) . "\n";

}
echo esc_html__( 'Email:', 'yith-woocommerce-request-a-quote' );
echo esc_html( $user_email ) . "\n";

if ( '' !== $billing_phone ) {
	echo esc_html__( 'Billing Phone:', 'yith-woocommerce-request-a-quote' );
	echo esc_html( $billing_phone ) . "\n";
}

if ( '' !== $billing_vat ) {
	echo esc_html__( 'Billing VAT:', 'yith-woocommerce-request-a-quote' );
	echo esc_html( $billing_vat ) . "\n";
}

echo "\n****************************************************\n\n";

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );

