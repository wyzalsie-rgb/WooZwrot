<?php
defined( 'ABSPATH' ) || exit;

class WC_Email_Admin_Return_Submitted extends WC_Email {

    public function __construct() {
        $this->id             = 'admin_return_submitted';
        $this->title          = __( 'Nowy wniosek zwrotu (sklep)', 'wc-eu-return' );
        $this->description    = __( 'Wysyłane do administratora sklepu po złożeniu nowego wniosku o zwrot.', 'wc-eu-return' );
        $this->template_html  = 'emails/admin-new-return.php';
        $this->template_plain = 'emails/plain/admin-new-return.php';
        $this->template_base  = WC_EU_RETURN_DIR . 'templates/';
        $this->placeholders   = [
            '{site_title}'   => $this->get_blogname(),
            '{return_id}'    => '',
            '{order_number}' => '',
        ];

        parent::__construct();
        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
    }

    public function trigger( $return_post_id ) {
        $this->setup_locale();

        $return_post = get_post( $return_post_id );
        if ( ! $return_post ) {
            return;
        }

        $this->object                           = $return_post;
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
                $this->get_option( 'subject', __( 'Nowy wniosek o zwrot #{return_id} — zamówienie #{order_number}', 'wc-eu-return' ) )
            ),
            $this->object,
            $this
        );
    }

    public function get_heading() {
        return apply_filters(
            'woocommerce_email_heading_' . $this->id,
            $this->format_string(
                $this->get_option( 'heading', __( 'Nowy wniosek o zwrot', 'wc-eu-return' ) )
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
                'sent_to_admin' => true,
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
                'sent_to_admin' => true,
                'plain_text'    => true,
                'email'         => $this,
            ],
            '',
            $this->template_base
        );
    }

    public function init_form_fields() {
        parent::init_form_fields();
        $this->form_fields['recipient'] = [
            'title'       => __( 'Adres e-mail odbiorcy', 'wc-eu-return' ),
            'type'        => 'text',
            'description' => __( 'Podaj adres e-mail, na który mają trafiać zgłoszenia. Wiele adresów oddziel przecinkami.', 'wc-eu-return' ),
            'placeholder' => get_option( 'admin_email' ),
            'default'     => '',
        ];
    }
}
