<?php
/**
 * Plugin Name: Products Wizard Lite for WooCommerce
 * Description: This plugin helps you sell your WooCommerce products by the step-by-step wizard
 * Version: 1.0.2
 * Author: mail@troll-winner.com
 * Author URI: https://troll-winner.com/
 * Text Domain: products-wizard-lite-for-woocommerce
 * Domain Path: /languages/
 * Requires at least: 4.5
 * Requires PHP: 5.5
 * WC requires at least: 2.4
 * WC tested up to: 9.7.1
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 * Link: https://products-wizard.troll-winner.com/
 * Donate link: https://shop.troll-winner.com/product/custom-development/
 */

namespace {

    defined('ABSPATH') || exit;

    $uploadDir = wp_upload_dir();

    if (!defined('WC_PRODUCTS_WIZARD_VERSION')) {
        define('WC_PRODUCTS_WIZARD_VERSION', '1.0.2');
    }

    if (!defined('WC_PRODUCTS_WIZARD_DEBUG')) {
        if (defined('SCRIPT_DEBUG')) {
            define('WC_PRODUCTS_WIZARD_DEBUG', SCRIPT_DEBUG);
        } else {
            define('WC_PRODUCTS_WIZARD_DEBUG', false);
        }
    }

    if (!defined('WC_PRODUCTS_WIZARD_ROOT_FILE')) {
        define('WC_PRODUCTS_WIZARD_ROOT_FILE', __FILE__);
    }

    if (!defined('WC_PRODUCTS_WIZARD_THEME_TEMPLATES_DIR')) {
        define('WC_PRODUCTS_WIZARD_THEME_TEMPLATES_DIR', 'woocommerce-products-wizard');
    }

    if (!defined('WC_PRODUCTS_WIZARD_PLUGIN_PATH')) {
        define('WC_PRODUCTS_WIZARD_PLUGIN_PATH', plugin_dir_path(WC_PRODUCTS_WIZARD_ROOT_FILE));
    }

    if (!defined('WC_PRODUCTS_WIZARD_PLUGIN_URL')) {
        define('WC_PRODUCTS_WIZARD_PLUGIN_URL', plugin_dir_url(WC_PRODUCTS_WIZARD_ROOT_FILE));
    }

    if (!class_exists('\WCProductsWizard\Core')) {
        require_once(__DIR__ . '/includes/classes/Core.php');
    }

    require_once(__DIR__ . '/includes/global/shortcodes.php');
}

namespace WCProductsWizard {

    if (!function_exists(__NAMESPACE__  . '\Instance')) {
        function Instance()
        {
            return Core::instance();
        }
    } else {
        add_filter('admin_notices', function () {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><strong><?php esc_html_e('Products Wizard for WooCommerce is enabled a few times', 'products-wizard-lite-for-woocommerce'); ?></strong></p>
                <p><?php
                    // phpcs:disable
                    esc_html_e('A few Products Wizard plugins are enabled at once. Keep enabled only one of them.', 'products-wizard-lite-for-woocommerce');
                    // phpcs:enable
                    ?></p>
            </div>
            <?php
        });
    }

    $GLOBALS['WCProductsWizard'] = Instance();
}
