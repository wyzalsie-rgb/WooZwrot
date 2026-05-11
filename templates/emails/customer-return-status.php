<?php
defined( 'ABSPATH' ) || exit;
/**
 * @var WP_Post  $return_post
 * @var string   $new_status
 * @var string   $email_heading
 * @var WC_Email $email
 */
$order_id   = get_post_meta( $return_post->ID, '_order_id', true );
$product_id = get_post_meta( $return_post->ID, '_product_id', true );
$order      = wc_get_order( $order_id );
$product    = wc_get_product( $product_id );
$statuses   = WC_EU_Return_Post_Type::get_statuses();
$status_label = $statuses[ $new_status ] ?? $new_status;
$list_url   = wc_get_account_endpoint_url( WC_EU_Return_My_Account::ENDPOINT );

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p>
    <?php
    printf(
        esc_html__( 'Status Twojego wniosku #%d został zmieniony.', 'wc-eu-return' ),
        absint( $return_post->ID )
    );
    ?>
</p>

<table cellspacing="0" cellpadding="6" style="width:100%;border:1px solid #eee;" border="1" bordercolor="#eee">
    <tbody>
        <tr>
            <th style="text-align:left;border:1px solid #eee;padding:8px;"><?php esc_html_e( 'Nr wniosku', 'wc-eu-return' ); ?></th>
            <td style="text-align:left;border:1px solid #eee;padding:8px;">#<?php echo absint( $return_post->ID ); ?></td>
        </tr>
        <tr>
            <th style="text-align:left;border:1px solid #eee;padding:8px;"><?php esc_html_e( 'Zamówienie', 'wc-eu-return' ); ?></th>
            <td style="text-align:left;border:1px solid #eee;padding:8px;">
                <?php echo $order ? '#' . esc_html( $order->get_order_number() ) : '—'; ?>
            </td>
        </tr>
        <tr>
            <th style="text-align:left;border:1px solid #eee;padding:8px;"><?php esc_html_e( 'Produkt', 'wc-eu-return' ); ?></th>
            <td style="text-align:left;border:1px solid #eee;padding:8px;">
                <?php echo $product ? esc_html( $product->get_name() ) : '—'; ?>
            </td>
        </tr>
        <tr>
            <th style="text-align:left;border:1px solid #eee;padding:8px;"><?php esc_html_e( 'Nowy status', 'wc-eu-return' ); ?></th>
            <td style="text-align:left;border:1px solid #eee;padding:8px;">
                <strong><?php echo esc_html( $status_label ); ?></strong>
            </td>
        </tr>
    </tbody>
</table>

<p style="margin-top:16px;">
    <a href="<?php echo esc_url( $list_url ); ?>" style="background:#7f54b3;color:#fff;padding:10px 20px;text-decoration:none;border-radius:3px;">
        <?php esc_html_e( 'Zobacz moje zwroty', 'wc-eu-return' ); ?>
    </a>
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
