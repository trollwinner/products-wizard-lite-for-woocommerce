<?php
namespace WCProductsWizard\ShortCodes;

use WCProductsWizard\Core;
use WCProductsWizard\Template;
use WCProductsWizard\Utils;

add_shortcode('woocommerce-products-wizard', __NAMESPACE__. '\\app');

if (!function_exists(__NAMESPACE__ . '\\app')) {
    function app($attributes = [])
    {
        // if have no WooCommerce or is the admin part (and not AJAX) or Elementor preview
        if (!Core::$wcIsActive
            || (Utils::isAdminSide()
                && (!class_exists('\Elementor\Plugin') || !\Elementor\Plugin::$instance->editor->is_edit_mode()))
        ) {
            return 'woocommerce-products-wizard';
        }

        do_action('wcpw_shortcode', $attributes);

        return Template::html('app', $attributes, ['echo' => false]);
    }
}
