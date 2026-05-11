<?php
defined( 'ABSPATH' ) || exit;

class WC_EU_Return_Post_Type {

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_post_type' ] );
        add_action( 'init', [ __CLASS__, 'register_post_statuses' ] );
    }

    public static function register_post_type() {
        register_post_type( 'wc_return_request', [
            'label'               => __( 'Zwroty', 'wc-eu-return' ),
            'labels'              => [
                'name'               => __( 'Wnioski zwrotu', 'wc-eu-return' ),
                'singular_name'      => __( 'Wniosek zwrotu', 'wc-eu-return' ),
                'add_new'            => __( 'Dodaj wniosek', 'wc-eu-return' ),
                'add_new_item'       => __( 'Dodaj nowy wniosek', 'wc-eu-return' ),
                'edit_item'          => __( 'Edytuj wniosek', 'wc-eu-return' ),
                'view_item'          => __( 'Zobacz wniosek', 'wc-eu-return' ),
                'search_items'       => __( 'Szukaj wniosków', 'wc-eu-return' ),
                'not_found'          => __( 'Nie znaleziono wniosków', 'wc-eu-return' ),
                'not_found_in_trash' => __( 'Brak wniosków w koszu', 'wc-eu-return' ),
            ],
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'woocommerce',
            'show_in_admin_bar'   => false,
            'show_in_rest'        => false,
            'capability_type'     => 'post',
            'capabilities'        => [
                'create_posts' => 'do_not_allow',
            ],
            'map_meta_cap'        => true,
            'supports'            => [ 'title' ],
            'has_archive'         => false,
            'rewrite'             => false,
        ] );
    }

    public static function register_post_statuses() {
        $statuses = [
            'return-pending'    => [
                'label'                     => _x( 'Zgłoszony', 'status zwrotu', 'wc-eu-return' ),
                'label_count'               => _n_noop( 'Zgłoszony <span class="count">(%s)</span>', 'Zgłoszone <span class="count">(%s)</span>', 'wc-eu-return' ),
            ],
            'return-processing' => [
                'label'                     => _x( 'W trakcie', 'status zwrotu', 'wc-eu-return' ),
                'label_count'               => _n_noop( 'W trakcie <span class="count">(%s)</span>', 'W trakcie <span class="count">(%s)</span>', 'wc-eu-return' ),
            ],
            'return-approved'   => [
                'label'                     => _x( 'Zatwierdzony', 'status zwrotu', 'wc-eu-return' ),
                'label_count'               => _n_noop( 'Zatwierdzony <span class="count">(%s)</span>', 'Zatwierdzone <span class="count">(%s)</span>', 'wc-eu-return' ),
            ],
            'return-rejected'   => [
                'label'                     => _x( 'Odrzucony', 'status zwrotu', 'wc-eu-return' ),
                'label_count'               => _n_noop( 'Odrzucony <span class="count">(%s)</span>', 'Odrzucone <span class="count">(%s)</span>', 'wc-eu-return' ),
            ],
        ];

        foreach ( $statuses as $status => $args ) {
            register_post_status( $status, array_merge( [
                'public'                    => false,
                'exclude_from_search'       => true,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
            ], $args ) );
        }
    }

    public static function get_statuses() {
        return [
            'return-pending'    => _x( 'Zgłoszony', 'status zwrotu', 'wc-eu-return' ),
            'return-processing' => _x( 'W trakcie', 'status zwrotu', 'wc-eu-return' ),
            'return-approved'   => _x( 'Zatwierdzony', 'status zwrotu', 'wc-eu-return' ),
            'return-rejected'   => _x( 'Odrzucony', 'status zwrotu', 'wc-eu-return' ),
        ];
    }

    public static function create_return_request( $data ) {
        $post_id = wp_insert_post( [
            'post_type'   => 'wc_return_request',
            'post_title'  => sprintf(
                __( 'Zwrot #%s — zamówienie #%s', 'wc-eu-return' ),
                date( 'YmdHis' ),
                absint( $data['order_id'] )
            ),
            'post_status' => 'return-pending',
            'post_author' => get_current_user_id(),
        ] );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        update_post_meta( $post_id, '_order_id',        absint( $data['order_id'] ) );
        update_post_meta( $post_id, '_customer_id',     absint( $data['customer_id'] ) );
        update_post_meta( $post_id, '_product_id',      absint( $data['product_id'] ) );
        update_post_meta( $post_id, '_reason',          sanitize_text_field( $data['reason'] ) );
        update_post_meta( $post_id, '_description',     sanitize_textarea_field( $data['description'] ?? '' ) );
        update_post_meta( $post_id, '_date_submitted',  current_time( 'mysql' ) );

        return $post_id;
    }

    public static function get_customer_returns( $customer_id ) {
        return get_posts( [
            'post_type'      => 'wc_return_request',
            'post_status'    => array_keys( self::get_statuses() ),
            'author'         => absint( $customer_id ),
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );
    }
}
