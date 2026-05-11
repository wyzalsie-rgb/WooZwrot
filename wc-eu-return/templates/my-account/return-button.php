<?php
defined( 'ABSPATH' ) || exit;
/**
 * @var int    $order_id
 * @var string $return_url
 */
?>
<a href="<?php echo esc_url( $return_url ); ?>" class="woocommerce-button button wc-eu-return-btn">
    <?php esc_html_e( 'Zgłoś zwrot', 'wc-eu-return' ); ?>
</a>
