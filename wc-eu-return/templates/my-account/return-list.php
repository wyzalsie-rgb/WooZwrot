<?php
defined( 'ABSPATH' ) || exit;
/**
 * @var WP_Post[] $returns
 */
?>
<div class="wc-eu-return-list">

    <?php if ( empty( $returns ) ) : ?>
        <p class="woocommerce-message woocommerce-message--info woocommerce-info">
            <?php esc_html_e( 'Nie masz jeszcze żadnych wniosków o zwrot.', 'wc-eu-return' ); ?>
        </p>
    <?php else : ?>

        <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive">
            <thead>
                <tr>
                    <th class="woocommerce-orders-table__header"><?php esc_html_e( 'Nr wniosku', 'wc-eu-return' ); ?></th>
                    <th class="woocommerce-orders-table__header"><?php esc_html_e( 'Zamówienie', 'wc-eu-return' ); ?></th>
                    <th class="woocommerce-orders-table__header"><?php esc_html_e( 'Produkt', 'wc-eu-return' ); ?></th>
                    <th class="woocommerce-orders-table__header"><?php esc_html_e( 'Powód', 'wc-eu-return' ); ?></th>
                    <th class="woocommerce-orders-table__header"><?php esc_html_e( 'Status', 'wc-eu-return' ); ?></th>
                    <th class="woocommerce-orders-table__header"><?php esc_html_e( 'Data', 'wc-eu-return' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $returns as $return_post ) :
                    $order_id   = get_post_meta( $return_post->ID, '_order_id', true );
                    $product_id = get_post_meta( $return_post->ID, '_product_id', true );
                    $reason_key = get_post_meta( $return_post->ID, '_reason', true );
                    $date       = get_post_meta( $return_post->ID, '_date_submitted', true );
                    $order      = wc_get_order( $order_id );
                    $product    = wc_get_product( $product_id );
                    $statuses   = WC_EU_Return_Post_Type::get_statuses();
                    $reasons    = WC_EU_Return_Form::get_reasons();
                    $status     = get_post_status( $return_post->ID );
                    $status_label = $statuses[ $status ] ?? $status;
                ?>
                <tr class="woocommerce-orders-table__row">
                    <td data-title="<?php esc_attr_e( 'Nr wniosku', 'wc-eu-return' ); ?>">
                        <strong>#<?php echo absint( $return_post->ID ); ?></strong>
                    </td>
                    <td data-title="<?php esc_attr_e( 'Zamówienie', 'wc-eu-return' ); ?>">
                        <?php echo $order ? '<strong>#' . esc_html( $order->get_order_number() ) . '</strong>' : '—'; ?>
                    </td>
                    <td data-title="<?php esc_attr_e( 'Produkt', 'wc-eu-return' ); ?>">
                        <?php echo $product ? esc_html( $product->get_name() ) : '—'; ?>
                    </td>
                    <td data-title="<?php esc_attr_e( 'Powód', 'wc-eu-return' ); ?>">
                        <?php echo esc_html( $reasons[ $reason_key ] ?? $reason_key ); ?>
                    </td>
                    <td data-title="<?php esc_attr_e( 'Status', 'wc-eu-return' ); ?>">
                        <span class="wcer-status wcer-status--<?php echo esc_attr( str_replace( 'return-', '', $status ) ); ?>">
                            <?php echo esc_html( $status_label ); ?>
                        </span>
                    </td>
                    <td data-title="<?php esc_attr_e( 'Data', 'wc-eu-return' ); ?>">
                        <?php echo $date ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) ) : '—'; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</div>
