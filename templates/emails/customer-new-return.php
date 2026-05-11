<?php
defined( 'ABSPATH' ) || exit;
/**
 * @var WP_Post $return_post
 * @var string  $email_heading
 * @var WC_Email $email
 */
$order_id   = get_post_meta( $return_post->ID, '_order_id', true );
$product_id = get_post_meta( $return_post->ID, '_product_id', true );
$reason_key = get_post_meta( $return_post->ID, '_reason', true );
$description = get_post_meta( $return_post->ID, '_description', true );
$date       = get_post_meta( $return_post->ID, '_date_submitted', true );
$order      = wc_get_order( $order_id );
$product    = wc_get_product( $product_id );
$reasons    = WC_EU_Return_Form::get_reasons();
$statuses   = WC_EU_Return_Post_Type::get_statuses();

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p>
    <?php esc_html_e( 'Twój wniosek o odstąpienie od umowy został przyjęty. Poniżej znajdziesz szczegóły zgłoszenia.', 'wc-eu-return' ); ?>
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
        <tr>
            <th style="text-align:left;border:1px solid #eee;padding:8px;"><?php esc_html_e( 'Status', 'wc-eu-return' ); ?></th>
            <td style="text-align:left;border:1px solid #eee;padding:8px;">
                <?php echo esc_html( $statuses['return-pending'] ); ?>
            </td>
        </tr>
    </tbody>
</table>

<p style="margin-top:16px;">
    <?php esc_html_e( 'O każdej zmianie statusu Twojego wniosku poinformujemy Cię mailowo.', 'wc-eu-return' ); ?>
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
