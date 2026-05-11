<?php
defined( 'ABSPATH' ) || exit;

class WC_EU_Return_Admin {

    public static function init() {
        add_filter( 'manage_wc_return_request_posts_columns', [ __CLASS__, 'set_columns' ] );
        add_action( 'manage_wc_return_request_posts_custom_column', [ __CLASS__, 'render_column' ], 10, 2 );
        add_filter( 'manage_edit-wc_return_request_sortable_columns', [ __CLASS__, 'sortable_columns' ] );
        add_action( 'admin_init', [ __CLASS__, 'save_status_change' ] );
        add_action( 'transition_post_status', [ __CLASS__, 'on_status_transition' ], 10, 3 );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
        add_filter( 'post_row_actions', [ __CLASS__, 'row_actions' ], 10, 2 );
        add_action( 'restrict_manage_posts', [ __CLASS__, 'add_status_filter' ] );
        add_action( 'admin_notices', [ __CLASS__, 'donation_notice' ] );
        add_action( 'wp_ajax_wc_eu_return_dismiss_donation', [ __CLASS__, 'dismiss_donation' ] );
        add_filter( 'parse_query', [ __CLASS__, 'filter_by_status' ] );
    }

    public static function set_columns( $columns ) {
        return [
            'cb'          => '<input type="checkbox" />',
            'return_id'   => __( 'Wniosek', 'wc-eu-return' ),
            'order'       => __( 'Zamówienie', 'wc-eu-return' ),
            'customer'    => __( 'Klient', 'wc-eu-return' ),
            'product'     => __( 'Produkt', 'wc-eu-return' ),
            'reason'      => __( 'Powód', 'wc-eu-return' ),
            'status'      => __( 'Status', 'wc-eu-return' ),
            'date'        => __( 'Data zgłoszenia', 'wc-eu-return' ),
            'actions'     => __( 'Akcje', 'wc-eu-return' ),
        ];
    }

    public static function render_column( $column, $post_id ) {
        switch ( $column ) {
            case 'return_id':
                echo '<strong>#' . absint( $post_id ) . '</strong>';
                break;

            case 'order':
                $order_id = get_post_meta( $post_id, '_order_id', true );
                $order    = wc_get_order( $order_id );
                if ( $order ) {
                    printf(
                        '<a href="%s">#%s</a>',
                        esc_url( $order->get_edit_order_url() ),
                        esc_html( $order->get_order_number() )
                    );
                } else {
                    echo '—';
                }
                break;

            case 'customer':
                $customer_id = get_post_meta( $post_id, '_customer_id', true );
                $customer    = get_userdata( $customer_id );
                if ( $customer ) {
                    printf(
                        '<a href="%s">%s</a><br><small>%s</small>',
                        esc_url( get_edit_user_link( $customer_id ) ),
                        esc_html( $customer->display_name ),
                        esc_html( $customer->user_email )
                    );
                } else {
                    echo '—';
                }
                break;

            case 'product':
                $product_id = get_post_meta( $post_id, '_product_id', true );
                $product    = wc_get_product( $product_id );
                echo $product ? esc_html( $product->get_name() ) : '—';
                break;

            case 'reason':
                $reason  = get_post_meta( $post_id, '_reason', true );
                $reasons = WC_EU_Return_Form::get_reasons();
                echo esc_html( $reasons[ $reason ] ?? $reason );
                break;

            case 'status':
                $status   = get_post_status( $post_id );
                $statuses = WC_EU_Return_Post_Type::get_statuses();
                $label    = $statuses[ $status ] ?? $status;
                $classes  = [
                    'return-pending'    => 'wcer-status wcer-status--pending',
                    'return-processing' => 'wcer-status wcer-status--processing',
                    'return-approved'   => 'wcer-status wcer-status--approved',
                    'return-rejected'   => 'wcer-status wcer-status--rejected',
                ];
                $class = $classes[ $status ] ?? 'wcer-status';

                printf(
                    '<select class="wcer-status-select" data-return-id="%d"><options>%s</options></select>',
                    absint( $post_id ),
                    implode( '', array_map( function ( $key, $val ) use ( $status ) {
                        return sprintf(
                            '<option value="%s"%s>%s</option>',
                            esc_attr( $key ),
                            selected( $key, $status, false ),
                            esc_html( $val )
                        );
                    }, array_keys( $statuses ), $statuses ) )
                );
                printf( '<span class="%s">%s</span>', esc_attr( $class ), esc_html( $label ) );
                break;

            case 'date':
                $date = get_post_meta( $post_id, '_date_submitted', true );
                echo $date ? esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date ) ) ) : '—';
                break;

            case 'actions':
                $statuses = WC_EU_Return_Post_Type::get_statuses();
                echo '<div class="wcer-actions">';
                foreach ( $statuses as $key => $label ) {
                    if ( get_post_status( $post_id ) !== $key ) {
                        printf(
                            '<a href="%s" class="button button-small">%s</a> ',
                            esc_url( wp_nonce_url(
                                add_query_arg( [ 'wcer_action' => 'change_status', 'return_id' => $post_id, 'new_status' => $key ] ),
                                'wcer_change_status_' . $post_id
                            ) ),
                            esc_html( $label )
                        );
                    }
                }
                echo '</div>';
                break;
        }
    }

    public static function sortable_columns( $columns ) {
        $columns['date'] = 'date';
        return $columns;
    }

    public static function on_status_transition( $new_status, $old_status, $post ) {
        if ( 'wc_return_request' !== $post->post_type ) {
            return;
        }

        if ( $new_status === $old_status ) {
            return;
        }

        $valid_statuses = array_keys( WC_EU_Return_Post_Type::get_statuses() );
        if ( ! in_array( $new_status, $valid_statuses, true ) ) {
            return;
        }

        do_action( 'wc_eu_return_status_changed', $post->ID, $new_status );
    }

    public static function save_status_change() {
        if ( ! is_admin() ) {
            return;
        }
        if ( ! isset( $_GET['wcer_action'] ) || 'change_status' !== $_GET['wcer_action'] ) {
            return;
        }

        $return_id  = absint( $_GET['return_id'] ?? 0 );
        $new_status = sanitize_text_field( $_GET['new_status'] ?? '' );

        if ( ! $return_id || ! $new_status ) {
            return;
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'wcer_change_status_' . $return_id ) ) {
            wp_die( esc_html__( 'Błąd bezpieczeństwa.', 'wc-eu-return' ) );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $valid_statuses = array_keys( WC_EU_Return_Post_Type::get_statuses() );
        if ( ! in_array( $new_status, $valid_statuses, true ) ) {
            return;
        }

        wp_update_post( [
            'ID'          => $return_id,
            'post_status' => $new_status,
        ] );

        wp_safe_redirect( add_query_arg( 'updated', 1, remove_query_arg( [ 'wcer_action', 'return_id', 'new_status', '_wpnonce' ] ) ) );
        exit;
    }

    public static function row_actions( $actions, $post ) {
        if ( 'wc_return_request' !== $post->post_type ) {
            return $actions;
        }
        unset( $actions['inline hide-if-no-js'] );
        return $actions;
    }

    public static function add_status_filter() {
        global $typenow;
        if ( 'wc_return_request' !== $typenow ) {
            return;
        }

        $current  = sanitize_text_field( $_GET['return_status'] ?? '' );
        $statuses = WC_EU_Return_Post_Type::get_statuses();

        echo '<select name="return_status"><option value="">' . esc_html__( 'Wszystkie statusy', 'wc-eu-return' ) . '</option>';
        foreach ( $statuses as $key => $label ) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr( $key ),
                selected( $key, $current, false ),
                esc_html( $label )
            );
        }
        echo '</select>';
    }

    public static function filter_by_status( $query ) {
        global $pagenow, $typenow;
        if ( 'edit.php' !== $pagenow || 'wc_return_request' !== $typenow ) {
            return;
        }
        $status = sanitize_text_field( $_GET['return_status'] ?? '' );
        if ( $status ) {
            $query->set( 'post_status', $status );
        } else {
            $query->set( 'post_status', array_keys( WC_EU_Return_Post_Type::get_statuses() ) );
        }
    }

    public static function enqueue_assets( $hook ) {
        $screen = get_current_screen();
        if ( ! $screen || 'wc_return_request' !== $screen->post_type ) {
            return;
        }
        wp_enqueue_style(
            'wc-eu-return-admin',
            WC_EU_RETURN_URL . 'assets/css/wc-eu-return-admin.css',
            [],
            WC_EU_RETURN_VERSION
        );
    }

    public static function donation_notice() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }
        if ( get_option( 'wc_eu_return_donation_dismissed' ) ) {
            return;
        }
        $screen = get_current_screen();
        if ( ! $screen || 'wc_return_request' !== $screen->post_type ) {
            return;
        }
        ?>
        <div class="notice notice-info wcer-donation-notice" style="display:flex;align-items:center;gap:16px;padding:12px 16px;">
            <span style="font-size:28px;">☕</span>
            <div style="flex:1;">
                <strong><?php esc_html_e( 'Podoba Ci się wtyczka WC EU Return Button?', 'wc-eu-return' ); ?></strong><br>
                <?php esc_html_e( 'Jeśli wtyczka oszczędza Ci czas i stres przed deadline\'m June 2026 — postaw autorowi kawę!', 'wc-eu-return' ); ?>
                &nbsp;
                <a href="https://buycoffee.to/cyfrowymenel" target="_blank" rel="noopener noreferrer" class="button button-primary" style="margin-left:8px;">
                    ☕ <?php esc_html_e( 'Postaw kawę autorowi', 'wc-eu-return' ); ?>
                </a>
            </div>
            <a href="#" class="wcer-dismiss-donation notice-dismiss" style="text-decoration:none;" title="<?php esc_attr_e( 'Zamknij', 'wc-eu-return' ); ?>"></a>
        </div>
        <script>
        (function(){
            var btn = document.querySelector('.wcer-dismiss-donation');
            if(!btn) return;
            btn.addEventListener('click', function(e){
                e.preventDefault();
                var notice = btn.closest('.wcer-donation-notice');
                if(notice) notice.style.display='none';
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>');
                xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
                xhr.send('action=wc_eu_return_dismiss_donation&_wpnonce=<?php echo esc_js( wp_create_nonce( 'wc_eu_return_dismiss_donation' ) ); ?>');
            });
        })();
        </script>
        <?php
    }

    public static function dismiss_donation() {
        check_ajax_referer( 'wc_eu_return_dismiss_donation' );
        if ( current_user_can( 'manage_woocommerce' ) ) {
            update_option( 'wc_eu_return_donation_dismissed', 1 );
        }
        wp_die();
    }
}
