<?php
defined( 'ABSPATH' ) || exit;
/**
 * @var WP_Post  $return_post
 * @var string   $email_heading
 * @var WC_Email $email
 */
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
$admin_url   = admin_url( 'edit.php?post_type=wc_return_request' );

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p>
    <?php esc_html_e( 'Nowy wniosek o odstąpienie od umowy został złożony przez klienta.', 'wc-eu-return' ); ?>
</p>

<table cellspacing="0" cellpadding="6" style="width:100%;border:1px solid #eee;" border="1" bordercolor="#eee">
    <tbody>
        <tr>
            <th style="text-align:left;border:1px solid #eee;padding:8px;"><?php esc_html_e( 'Nr wniosku', 'wc-eu-return' ); ?></th>
            <td style="text-align:left;border:1px solid #eee;padding:8px;">#<?php echo absint( $return_post->ID ); ?></td>
        </tr>
        <tr>
            <th style="text-align:left;border:1px solid #eee;padding:8px;"><?php esc_html_e( 'Klient', 'wc-eu-return' ); ?></th>
            <td style="text-align:left;border:1px solid #eee;padding:8px;">
                <?php if ( $customer ) : ?>
                    <?php echo esc_html( $customer->display_name ); ?> &lt;<?php echo esc_html( $customer->user_email ); ?>&gt;
                <?php else : ?>
                    —
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th style="text-align:left;border:1px solid #eee;padding:8px;"><?php esc_html_e( 'Zamówienie', 'wc-eu-return' ); ?></th>
            <td style="text-align:left;border:1px solid #eee;padding:8px;">
                <?php if ( $order ) : ?>
                    <a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>">#<?php echo esc_html( $order->get_order_number() ); ?></a>
                <?php else : ?>
                    —
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th style="text-align:left;border:1px solid #eee;padding:8px;"><?php esc_html_e( 'Produkt', 'wc-eu-return' ); ?></th>
            <td style="text-align:left;border:1px solid #eee;padding:8px;">
                <?php echo $product ? esc_html( $product->get_name() ) : '—'; ?>
            </td>
        </tr>
        <tr>
            <th style="text-align:left;border:1px solid #eee;padding:8px;"><?php esc_html_e( 'Powód', 'wc-eu-return' ); ?></th>
            <td style="text-align:left;border:1px solid #eee;padding:8px;">
                <?php echo esc_html( $reasons[ $reason_key ] ?? $reason_key ); ?>
            </td>
        </tr>
        <?php if ( $description ) : ?>
        <tr>
            <th style="text-align:left;border:1px solid #eee;padding:8px;"><?php esc_html_e( 'Opis', 'wc-eu-return' ); ?></th>
            <td style="text-align:left;border:1px solid #eee;padding:8px;"><?php echo esc_html( $description ); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th style="text-align:left;border:1px solid #eee;padding:8px;"><?php esc_html_e( 'Data zgłoszenia', 'wc-eu-return' ); ?></th>
            <td style="text-align:left;border:1px solid #eee;padding:8px;">
                <?php echo $date ? esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date ) ) ) : '—'; ?>
            </td>
        </tr>
    </tbody>
</table>

<p style="margin-top:16px;">
    <a href="<?php echo esc_url( $admin_url ); ?>" style="background:#7f54b3;color:#fff;padding:10px 20px;text-decoration:none;border-radius:3px;">
        <?php esc_html_e( 'Zarządzaj wnioskami', 'wc-eu-return' ); ?>
    </a>
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
