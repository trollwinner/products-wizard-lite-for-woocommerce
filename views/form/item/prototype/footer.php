<?php
defined('ABSPATH') || exit;

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs(['product' => null]);
$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

switch ($product->get_type()) {
    case 'variation':
    case 'variable':
    case 'variable-subscription':
        $view = 'variable';
        break;

    default:
        $view = 'simple';
}

do_action('woocommerce_before_add_to_cart_form');

Template::html("form/item/prototype/footer/$view", $arguments);

do_action('woocommerce_after_add_to_cart_form');
