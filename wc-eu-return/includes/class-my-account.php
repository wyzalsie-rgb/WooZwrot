<?php
defined( 'ABSPATH' ) || exit;

class WC_EU_Return_My_Account {

    const ENDPOINT       = 'moje-zwroty';
    const FORM_ENDPOINT  = 'zglos-zwrot';
    const RETURN_WINDOW  = 14; // dni

    public static function init() {
        add_action( 'init', [ __CLASS__, 'add_endpoints' ] );
        add_filter( 'woocommerce_account_menu_items', [ __CLASS__, 'add_menu_item' ] );
        add_action( 'woocommerce_account_' . self::ENDPOINT . '_endpoint', [ __CLASS__, 'render_return_list' ] );
        add_action( 'woocommerce_account_' . self::FORM_ENDPOINT . '_endpoint', [ __CLASS__, 'render_return_form' ] );
        add_filter( 'woocommerce_my_account_my_orders_actions', [ __CLASS__, 'add_return_action' ], 10, 2 );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
    }

    public static function add_endpoints() {
        add_rewrite_endpoint( self::ENDPOINT, EP_ROOT | EP_PAGES );
        add_rewrite_endpoint( self::FORM_ENDPOINT, EP_ROOT | EP_PAGES );
    }

    public static function add_menu_item( $items ) {
        $logout = isset( $items['customer-logout'] ) ? [ 'customer-logout' => $items['customer-logout'] ] : [];
        unset( $items['customer-logout'] );

        $items[ self::ENDPOINT ] = __( 'Moje zwroty', 'wc-eu-return' );

        return array_merge( $items, $logout );
    }

    public static function render_return_list() {
        $returns = WC_EU_Return_Post_Type::get_customer_returns( get_current_user_id() );
        wc_get_template(
            'my-account/return-list.php',
            [ 'returns' => $returns ],
            '',
            WC_EU_RETURN_DIR . 'templates/'
        );
    }

    public static function render_return_form() {
        $order_id = absint( get_query_var( self::FORM_ENDPOINT ) );

        if ( ! $order_id ) {
            wc_add_notice( __( 'Nieprawidłowe zamówienie.', 'wc-eu-return' ), 'error' );
            wp_safe_redirect( wc_get_account_endpoint_url( self::ENDPOINT ) );
            exit;
        }

        $order = wc_get_order( $order_id );

        if ( ! $order || $order->get_customer_id() !== get_current_user_id() ) {
            wc_add_notice( __( 'Nie masz dostępu do tego zamówienia.', 'wc-eu-return' ), 'error' );
            wp_safe_redirect( wc_get_account_endpoint_url( self::ENDPOINT ) );
            exit;
        }

        if ( ! self::is_within_return_window( $order ) ) {
            wc_add_notice( __( 'Minął 14-dniowy termin odstąpienia od umowy dla tego zamówienia.', 'wc-eu-return' ), 'error' );
            wp_safe_redirect( wc_get_account_endpoint_url( self::ENDPOINT ) );
            exit;
        }

        wc_get_template(
            'my-account/return-form.php',
            [ 'order' => $order ],
            '',
            WC_EU_RETURN_DIR . 'templates/'
        );
    }

    public static function add_return_action( $actions, $order ) {
        if ( ! self::is_within_return_window( $order ) ) {
            return $actions;
        }

        $allowed_statuses = apply_filters( 'wc_eu_return_allowed_order_statuses', [ 'completed', 'processing' ] );

        if ( ! in_array( $order->get_status(), $allowed_statuses, true ) ) {
            return $actions;
        }

        $actions['eu-return'] = [
            'url'  => wc_get_account_endpoint_url( self::FORM_ENDPOINT ) . $order->get_id() . '/',
            'name' => __( 'Zgłoś zwrot', 'wc-eu-return' ),
        ];

        return $actions;
    }

    public static function is_within_return_window( $order ) {
        $order_date = $order->get_date_created();
        if ( ! $order_date ) {
            return false;
        }
        $diff = ( new DateTime() )->diff( $order_date->date_i18n( 'Y-m-d H:i:s' ) ? new DateTime( $order_date->date_i18n( 'Y-m-d H:i:s' ) ) : new DateTime() );

        return $diff->days <= self::RETURN_WINDOW;
    }

    public static function enqueue_assets() {
        if ( is_account_page() ) {
            wp_enqueue_style(
                'wc-eu-return',
                WC_EU_RETURN_URL . 'assets/css/wc-eu-return.css',
                [],
                WC_EU_RETURN_VERSION
            );
            wp_enqueue_script(
                'wc-eu-return',
                WC_EU_RETURN_URL . 'assets/js/wc-eu-return.js',
                [ 'jquery' ],
                WC_EU_RETURN_VERSION,
                true
            );
        }
    }
}
