<?php
defined( 'ABSPATH' ) || exit;
$order_id    = get_post_meta( $return_post->ID, '_order_id', true );
$customer_id = get_post_meta( $return_post->ID, '_customer_id', true );
$product_id  = get_post_meta( $return_post->ID, '_product_id', true );
$reason_key  = get_post_meta( $return_post->ID, '_reason', true );
$description = get_post_meta( $return_post->ID, '_description', true );
$date        = get_post_meta( $return_post->ID, '_date_submitted', true );
$order       = wc_get_order( $order_id );
$product     = wc_get_product( $product_id );
$customer    = get_userdata( $customer_id );
$reasons     = WC_EU_Return_Form::get_reasons();
echo esc_html( $email_heading ) . "\n\n";
echo esc_html__( 'Nowy wniosek o odstąpienie od umowy został złożony.', 'wc-eu-return' ) . "\n\n";
echo esc_html__( 'Nr wniosku:', 'wc-eu-return' ) . ' #' . absint( $return_post->ID ) . "\n";
echo esc_html__( 'Klient:', 'wc-eu-return' ) . ' ' . ( $customer ? $customer->display_name . ' <' . $customer->user_email . '>' : '—' ) . "\n";
echo esc_html__( 'Zamówienie:', 'wc-eu-return' ) . ' ' . ( $order ? '#' . $order->get_order_number() : '—' ) . "\n";
echo esc_html__( 'Produkt:', 'wc-eu-return' ) . ' ' . ( $product ? $product->get_name() : '—' ) . "\n";
echo esc_html__( 'Powód:', 'wc-eu-return' ) . ' ' . ( $reasons[ $reason_key ] ?? $reason_key ) . "\n";
if ( $description ) {
    echo esc_html__( 'Opis:', 'wc-eu-return' ) . ' ' . $description . "\n";
}
echo esc_html__( 'Data zgłoszenia:', 'wc-eu-return' ) . ' ' . ( $date ? date_i18n( get_option( 'date_format' ), strtotime( $date ) ) : '—' ) . "\n\n";
echo esc_html__( 'Zarządzaj wnioskami:', 'wc-eu-return' ) . ' ' . admin_url( 'edit.php?post_type=wc_return_request' ) . "\n";
