<?php
/**
 * Plugin Name:       WC EU Return Button
 * Plugin URI:        https://justbo.pl
 * Description:       Przycisk zwrotu zgodny z Dyrektywą UE 2023/2673 — elektroniczne odstąpienie od umowy w WooCommerce.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            JustBo
 * Author URI:        https://justbo.pl
 * Text Domain:       wc-eu-return
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * WC requires at least: 7.0
 * WC tested up to:   9.0
 */

defined( 'ABSPATH' ) || exit;

define( 'WC_EU_RETURN_VERSION', '1.0.0' );
define( 'WC_EU_RETURN_FILE', __FILE__ );
define( 'WC_EU_RETURN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC_EU_RETURN_URL', plugin_dir_url( __FILE__ ) );

register_activation_hook( __FILE__, 'wc_eu_return_activate' );
function wc_eu_return_activate() {
    add_rewrite_endpoint( 'moje-zwroty', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'zglos-zwrot', EP_ROOT | EP_PAGES );
    flush_rewrite_rules();
}

add_action( 'plugins_loaded', 'wc_eu_return_init' );
function wc_eu_return_init() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="error"><p>' .
                esc_html__( 'WC EU Return Button wymaga aktywnej wtyczki WooCommerce.', 'wc-eu-return' ) .
                '</p></div>';
        } );
        return;
    }

    load_plugin_textdomain( 'wc-eu-return', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

    require_once WC_EU_RETURN_DIR . 'includes/class-return-post-type.php';
    require_once WC_EU_RETURN_DIR . 'includes/class-my-account.php';
    require_once WC_EU_RETURN_DIR . 'includes/class-return-form.php';
    require_once WC_EU_RETURN_DIR . 'includes/class-return-emails.php';
    require_once WC_EU_RETURN_DIR . 'includes/class-admin.php';

    WC_EU_Return_Post_Type::init();
    WC_EU_Return_My_Account::init();
    WC_EU_Return_Form::init();
    WC_EU_Return_Emails::init();
    WC_EU_Return_Admin::init();
}

// Deklaracja zgodności z HPOS (High-Performance Order Storage)
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );
