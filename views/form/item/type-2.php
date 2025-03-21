<?php
defined('ABSPATH') || exit;

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'class' => 'woocommerce-products-wizard-form-item',
    'product' => null,
    'stepId' => null,
    'cartItem' => null,
    'titleSubClass' => 'panel-title card-header',
    'footerSubClass' => ''
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

$disabled = !($product->is_purchasable() && ($product->is_in_stock() || $product->backorders_allowed()));
?>
<article class="product panel panel-primary card type-2 <?php
    echo esc_attr($arguments['class']) . ($arguments['cartItem'] ? ' is-in-cart' : '')
        . ($disabled ? ' is-disabled' : '');
    ?>"
    data-component="wcpw-product"
    data-type="<?php echo esc_attr($product->get_type()); ?>"
    data-id="<?php echo esc_attr($product->get_id()); ?>"
    data-step-id="<?php echo esc_attr($arguments['stepId']); ?>"<?php
    echo $arguments['cartItem'] ? esc_attr(' data-cart-key="' . $arguments['cartItem']['key'] . '"') : '';
    ?>>
    <header class="<?php echo esc_attr($arguments['class']); ?>-header panel-heading"><?php
        Template::html('form/item/prototype/title', $arguments);
        ?></header>
    <div class="<?php echo esc_attr($arguments['class']); ?>-body panel-body card-body">
        <div class="<?php echo esc_attr($arguments['class']); ?>-thumbnail-wrapper"><?php
            Template::html('form/item/prototype/thumbnail', $arguments);
            Template::html('form/item/prototype/gallery', $arguments);
            Template::html('form/item/prototype/sku', $arguments);
            ?></div>
        <div class="<?php echo esc_attr($arguments['class']); ?>-inner"><?php
            Template::html('form/item/prototype/description', $arguments);
            Template::html('form/item/prototype/attributes', $arguments);
            Template::html('form/item/prototype/footer', $arguments);
            ?></div>
    </div>
</article>
