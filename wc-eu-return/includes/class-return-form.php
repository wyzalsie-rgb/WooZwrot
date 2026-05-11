<?php
defined( 'ABSPATH' ) || exit;

class WC_EU_Return_Form {

    public static function init() {
        add_action( 'template_redirect', [ __CLASS__, 'handle_submission' ] );
    }

    public static function handle_submission() {
        if ( ! isset( $_POST['wc_eu_return_submit'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'wc_eu_return_submit' ) ) {
            wc_add_notice( __( 'Błąd weryfikacji formularza. Spróbuj ponownie.', 'wc-eu-return' ), 'error' );
            return;
        }

        if ( ! is_user_logged_in() ) {
            wc_add_notice( __( 'Musisz być zalogowany, aby zgłosić zwrot.', 'wc-eu-return' ), 'error' );
            return;
        }

        $order_id   = absint( $_POST['order_id'] ?? 0 );
        $product_id = absint( $_POST['product_id'] ?? 0 );
        $reason     = sanitize_text_field( $_POST['return_reason'] ?? '' );
        $description = sanitize_textarea_field( $_POST['return_description'] ?? '' );
        $confirmed  = isset( $_POST['confirm_policy'] );

        $errors = self::validate( $order_id, $product_id, $reason, $confirmed );

        if ( ! empty( $errors ) ) {
            foreach ( $errors as $error ) {
                wc_add_notice( $error, 'error' );
            }
            return;
        }

        $post_id = WC_EU_Return_Post_Type::create_return_request( [
            'order_id'    => $order_id,
            'customer_id' => get_current_user_id(),
            'product_id'  => $product_id,
            'reason'      => $reason,
            'description' => $description,
        ] );

        if ( is_wp_error( $post_id ) ) {
            wc_add_notice( __( 'Wystąpił błąd podczas zapisywania wniosku. Spróbuj ponownie.', 'wc-eu-return' ), 'error' );
            return;
        }

        do_action( 'wc_eu_return_submitted', $post_id );

        wc_add_notice( __( 'Twój wniosek o zwrot został złożony. Otrzymasz potwierdzenie na e-mail.', 'wc-eu-return' ), 'success' );
        wp_safe_redirect( wc_get_account_endpoint_url( WC_EU_Return_My_Account::ENDPOINT ) );
        exit;
    }

    private static function validate( $order_id, $product_id, $reason, $confirmed ) {
        $errors = [];

        if ( ! $order_id ) {
            $errors[] = __( 'Brak identyfikatora zamówienia.', 'wc-eu-return' );
            return $errors;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order || $order->get_customer_id() !== get_current_user_id() ) {
            $errors[] = __( 'Nieprawidłowe zamówienie.', 'wc-eu-return' );
            return $errors;
        }

        if ( ! WC_EU_Return_My_Account::is_within_return_window( $order ) ) {
            $errors[] = __( 'Minął 14-dniowy termin odstąpienia od umowy.', 'wc-eu-return' );
        }

        if ( ! $product_id ) {
            $errors[] = __( 'Wybierz produkt do zwrotu.', 'wc-eu-return' );
        }

        $allowed_reasons = array_keys( self::get_reasons() );
        if ( ! in_array( $reason, $allowed_reasons, true ) ) {
            $errors[] = __( 'Wybierz powód odstąpienia od umowy.', 'wc-eu-return' );
        }

        if ( ! $confirmed ) {
            $errors[] = __( 'Musisz potwierdzić zapoznanie się z polityką zwrotów.', 'wc-eu-return' );
        }

        return $errors;
    }

    public static function get_reasons() {
        return [
            'change_of_mind'    => __( 'Zmieniłem/am zdanie', 'wc-eu-return' ),
            'defective_product' => __( 'Produkt wadliwy lub uszkodzony', 'wc-eu-return' ),
            'not_as_described'  => __( 'Produkt niezgodny z opisem', 'wc-eu-return' ),
            'wrong_item'        => __( 'Otrzymałem/am niewłaściwy produkt', 'wc-eu-return' ),
            'other'             => __( 'Inne', 'wc-eu-return' ),
        ];
    }
}
