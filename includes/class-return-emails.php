<?php
defined( 'ABSPATH' ) || exit;

class WC_EU_Return_Emails {

    public static function init() {
        add_filter( 'woocommerce_email_classes', [ __CLASS__, 'register_email_classes' ] );
        add_action( 'wc_eu_return_submitted', [ __CLASS__, 'trigger_new_return_emails' ] );
        add_action( 'wc_eu_return_status_changed', [ __CLASS__, 'trigger_status_changed_email' ], 10, 2 );
    }

    public static function register_email_classes( $email_classes ) {
        require_once WC_EU_RETURN_DIR . 'includes/emails/class-email-customer-return-submitted.php';
        require_once WC_EU_RETURN_DIR . 'includes/emails/class-email-admin-return-submitted.php';
        require_once WC_EU_RETURN_DIR . 'includes/emails/class-email-customer-return-status.php';

        $email_classes['WC_Email_Customer_Return_Submitted'] = new WC_Email_Customer_Return_Submitted();
        $email_classes['WC_Email_Admin_Return_Submitted']    = new WC_Email_Admin_Return_Submitted();
        $email_classes['WC_Email_Customer_Return_Status']    = new WC_Email_Customer_Return_Status();

        return $email_classes;
    }

    public static function trigger_new_return_emails( $return_post_id ) {
        $mailer = WC()->mailer();
        $emails = $mailer->get_emails();

        if ( isset( $emails['WC_Email_Customer_Return_Submitted'] ) ) {
            $emails['WC_Email_Customer_Return_Submitted']->trigger( $return_post_id );
        }

        if ( isset( $emails['WC_Email_Admin_Return_Submitted'] ) ) {
            $emails['WC_Email_Admin_Return_Submitted']->trigger( $return_post_id );
        }
    }

    public static function trigger_status_changed_email( $return_post_id, $new_status ) {
        $mailer = WC()->mailer();
        $emails = $mailer->get_emails();

        if ( isset( $emails['WC_Email_Customer_Return_Status'] ) ) {
            $emails['WC_Email_Customer_Return_Status']->trigger( $return_post_id, $new_status );
        }
    }
}
