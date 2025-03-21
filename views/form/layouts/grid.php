<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Cart;
use WCProductsWizard\Entities\Product;
use WCProductsWizard\Entities\WizardStep;
use WCProductsWizard\Template;
use WCProductsWizard\Utils;

$arguments = Template::getHTMLArgs([
    'id' => null,
    'stepId' => null,
    'itemTemplate' => 'form/item/type-1',
    'gridColumnWidth' => WizardStep::getGridColumnWidth($id, $stepId)
]);

$productsQuery = Product::getQueryObject($arguments);

if (!$productsQuery->have_posts()) {
    Template::html('messages/nothing-found', $arguments);
}

$style = [];

foreach ($arguments['gridColumnWidth'] as $size => $value) {
    $style["--wcpw-grid-item-width-$size"] = $value;
}

echo '<div class="woocommerce-products-wizard-form-layout is-grid products" style="' .
    esc_attr(Utils::stylesArrayToString($style)) . '">';

while ($productsQuery->have_posts()) {
    $productsQuery->the_post();

    global $product;

    if (!$product instanceof WC_Product) {
        continue;
    }

    $arguments['product'] = $product;
    $arguments['cartItem'] = Cart::getProductById($arguments['id'], $product->get_id(), $arguments['stepId']);
    $arguments['addToCartKey'] = "{$arguments['stepId']}-{$product->get_id()}";

    Template::html($arguments['itemTemplate'], $arguments);
}

echo '</div>';

Template::html('form/pagination', array_merge(['productsQuery' => $productsQuery], $arguments));

wp_reset_query(); // better than $productsQuery->reset_postdata();
