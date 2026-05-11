<?php
defined( 'ABSPATH' ) || exit;

class WC_Email_Customer_Return_Submitted extends WC_Email {

    public function __construct() {
        $this->id             = 'customer_return_submitted';
        $this->customer_email = true;
        $this->title          = __( 'Potwierdzenie wniosku zwrotu (klient)', 'wc-eu-return' );
        $this->description    = __( 'Wysyłane do klienta po złożeniu wniosku o zwrot.', 'wc-eu-return' );
        $this->template_html  = 'emails/customer-new-return.php';
        $this->template_plain = 'emails/plain/customer-new-return.php';
        $this->template_base  = WC_EU_RETURN_DIR . 'templates/';
        $this->placeholders   = [
            '{site_title}'   => $this->get_blogname(),
            '{return_id}'    => '',
            '{order_number}' => '',
        ];

        parent::__construct();
    }

    public function trigger( $return_post_id ) {
        $this->setup_locale();

        $return_post = get_post( $return_post_id );
        if ( ! $return_post ) {
            return;
        }

        $customer_id = (int) get_post_meta( $return_post_id, '_customer_id', true );
        $customer    = get_userdata( $customer_id );

        if ( ! $customer ) {
            return;
        }

        $this->object                           = $return_post;
        $this->recipient                        = $customer->user_email;
        $this->placeholders['{return_id}']      = $return_post_id;
        $this->placeholders['{order_number}']   = get_post_meta( $return_post_id, '_order_id', true );

        if ( $this->is_enabled() && $this->get_recipient() ) {
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }

        $this->restore_locale();
    }

    public function get_subject() {
        return apply_filters(
            'woocommerce_email_subject_' . $this->id,
            $this->format_string(
                $this->get_option( 'subject', __( 'Twój wniosek o zwrot #{return_id} został złożony', 'wc-eu-return' ) )
            ),
            $this->object,
            $this
        );
    }

    public function get_heading() {
        return apply_filters(
            'woocommerce_email_heading_' . $this->id,
            $this->format_string(
                $this->get_option( 'heading', __( 'Wniosek o zwrot przyjęty', 'wc-eu-return' ) )
            ),
            $this->object,
            $this
        );
    }

    public function get_content_html() {
        return wc_get_template_html(
            $this->template_html,
            [
                'return_post'   => $this->object,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => false,
                'plain_text'    => false,
                'email'         => $this,
            ],
            '',
            $this->template_base
        );
    }

    public function get_content_plain() {
        return wc_get_template_html(
            $this->template_plain,
            [
                'return_post'   => $this->object,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => false,
                'plain_text'    => true,
                'email'         => $this,
            ],
            '',
            $this->template_base
        );
    }
}
