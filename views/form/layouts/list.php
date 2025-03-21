<?php
defined('ABSPATH') || exit;

use WCProductsWizard\Cart;
use WCProductsWizard\Entities\Product;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'id' => null,
    'stepId' => null,
    'itemTemplate' => 'form/item/type-1'
]);

$productsQuery = Product::getQueryObject($arguments);

if (!$productsQuery->have_posts()) {
    Template::html('messages/nothing-found', $arguments);
}

echo '<div class="woocommerce-products-wizard-form-layout is-list products">';

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
