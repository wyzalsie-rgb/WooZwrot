<?php
defined( 'ABSPATH' ) || exit;
/**
 * @var WC_Order $order
 */
$reasons = WC_EU_Return_Form::get_reasons();
?>
<div class="wc-eu-return-form-wrap">

    <h3><?php
        printf(
            esc_html__( 'Odstąpienie od umowy — zamówienie #%s', 'wc-eu-return' ),
            esc_html( $order->get_order_number() )
        );
    ?></h3>

    <p class="wcer-legal-note">
        <?php esc_html_e(
            'Zgodnie z Dyrektywą UE 2023/2673 masz prawo odstąpić od umowy zawartej na odległość w ciągu 14 dni bez podawania przyczyny. '
            . 'Wypełnij poniższy formularz, aby elektronicznie złożyć oświadczenie o odstąpieniu.',
            'wc-eu-return'
        ); ?>
    </p>

    <?php wc_print_notices(); ?>

    <form id="wc-eu-return-form" method="post" class="woocommerce-form">
        <?php wp_nonce_field( 'wc_eu_return_submit' ); ?>
        <input type="hidden" name="order_id" value="<?php echo absint( $order->get_id() ); ?>">

        <p class="form-row form-row-wide">
            <label for="product_id"><?php esc_html_e( 'Produkt do zwrotu', 'wc-eu-return' ); ?> <span class="required">*</span></label>
            <select id="product_id" name="product_id" class="woocommerce-Input" required>
                <option value=""><?php esc_html_e( '— wybierz produkt —', 'wc-eu-return' ); ?></option>
                <?php foreach ( $order->get_items() as $item ) :
                    $product = $item->get_product();
                ?>
                <option value="<?php echo absint( $item->get_product_id() ); ?>">
                    <?php echo esc_html( $item->get_name() ); ?>
                    <?php if ( $item->get_quantity() > 1 ) : ?>
                        (<?php echo esc_html( sprintf( __( 'szt.: %d', 'wc-eu-return' ), $item->get_quantity() ) ); ?>)
                    <?php endif; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p class="form-row form-row-wide">
            <label for="return_reason"><?php esc_html_e( 'Powód odstąpienia', 'wc-eu-return' ); ?> <span class="required">*</span></label>
            <select id="return_reason" name="return_reason" class="woocommerce-Input" required>
                <option value=""><?php esc_html_e( '— wybierz powód —', 'wc-eu-return' ); ?></option>
                <?php foreach ( $reasons as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <p class="form-row form-row-wide">
            <label for="return_description"><?php esc_html_e( 'Dodatkowy opis (opcjonalnie)', 'wc-eu-return' ); ?></label>
            <textarea id="return_description" name="return_description" class="woocommerce-Input" rows="4" maxlength="1000" placeholder="<?php esc_attr_e( 'Opcjonalnie opisz powód zwrotu...', 'wc-eu-return' ); ?>"></textarea>
        </p>

        <p class="form-row form-row-wide">
            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                <input type="checkbox" id="confirm_policy" name="confirm_policy" class="woocommerce-form__input woocommerce-form__input-checkbox" required>
                <span><?php
                    printf(
                        wp_kses(
                            __( 'Zapoznałem/am się z <a href="%s">polityką zwrotów</a> i składam oświadczenie o odstąpieniu od umowy.', 'wc-eu-return' ),
                            [ 'a' => [ 'href' => [] ] ]
                        ),
                        esc_url( get_privacy_policy_url() ?: '#' )
                    );
                ?></span>
            </label>
        </p>

        <p class="form-row">
            <button type="submit" name="wc_eu_return_submit" class="woocommerce-Button button" value="1">
                <?php esc_html_e( 'Złóż wniosek o zwrot', 'wc-eu-return' ); ?>
            </button>
            &nbsp;
            <a href="<?php echo esc_url( wc_get_account_endpoint_url( WC_EU_Return_My_Account::ENDPOINT ) ); ?>" class="woocommerce-Button button button--secondary">
                <?php esc_html_e( 'Anuluj', 'wc-eu-return' ); ?>
            </a>
        </p>

    </form>
</div>
