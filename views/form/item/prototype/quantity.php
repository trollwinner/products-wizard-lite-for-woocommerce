<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'stepId' => $stepId,
    'addToCartKey' => '',
    'class' => 'woocommerce-products-wizard-form-item',
    'soldIndividually' => false,
    'defaultQuantity' => 1,
    'cartItem' => null,
    'product' => null,
    'formId' => null
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product || $product->is_sold_individually() || $arguments['soldIndividually']) {
    return;
}

$value = $arguments['cartItem'] ? $arguments['cartItem']['quantity'] : $arguments['defaultQuantity'];
$disabled = !($product->is_purchasable() && ($product->is_in_stock() || $product->backorders_allowed()));
$inputId = "woocommerce-products-wizard-{$arguments['id']}-form-{$arguments['stepId']}-item-{$product->get_id()}-quantity"; // phpcs:ignore
$input = woocommerce_quantity_input(
    [
        'input_id' => $inputId,
        'input_value' => $value,
        'input_name' => "productsToAdd[{$arguments['addToCartKey']}][quantity]"
    ],
    $product,
    false
);

$replacements = [
    '<input' => '<input data-component="wcpw-product-quantity-input" form="' . $arguments['formId'] . '"'
        . disabled($disabled, true, false),
    'class="input-text ' => 'class="input-text form-control input-sm form-control-sm '
];

$input = strtr($input, $replacements); // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
?>
<div class="<?php echo esc_attr($arguments['class']); ?>-quantity" data-component="wcpw-product-quantity"><?php
    echo $input;
    ?></div>
