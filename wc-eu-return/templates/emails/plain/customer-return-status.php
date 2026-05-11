<?php
defined( 'ABSPATH' ) || exit;
$order_id   = get_post_meta( $return_post->ID, '_order_id', true );
$product_id = get_post_meta( $return_post->ID, '_product_id', true );
$order      = wc_get_order( $order_id );
$product    = wc_get_product( $product_id );
$statuses   = WC_EU_Return_Post_Type::get_statuses();
$status_label = $statuses[ $new_status ] ?? $new_status;
echo esc_html( $email_heading ) . "\n\n";
printf( esc_html__( 'Status Twojego wniosku #%d został zmieniony.', 'wc-eu-return' ), absint( $return_post->ID ) );
echo "\n\n";
echo esc_html__( 'Nr wniosku:', 'wc-eu-return' ) . ' #' . absint( $return_post->ID ) . "\n";
echo esc_html__( 'Zamówienie:', 'wc-eu-return' ) . ' ' . ( $order ? '#' . $order->get_order_number() : '—' ) . "\n";
echo esc_html__( 'Produkt:', 'wc-eu-return' ) . ' ' . ( $product ? $product->get_name() : '—' ) . "\n";
echo esc_html__( 'Nowy status:', 'wc-eu-return' ) . ' ' . $status_label . "\n\n";
echo esc_html__( 'Twoje zwroty:', 'wc-eu-return' ) . ' ' . wc_get_account_endpoint_url( WC_EU_Return_My_Account::ENDPOINT ) . "\n";
