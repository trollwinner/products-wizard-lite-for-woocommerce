<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'stepId' => $stepId,
    'product' => null,
    'addToCartKey' => '',
    'activeProductsIds' => [],
    'class' => 'woocommerce-products-wizard-form-item',
    'severalProducts' => false,
    'formId' => null
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

$inputType = $arguments['severalProducts'] ? 'checkbox' : 'radio';
$productId = $product->get_id();
?>
<input type="hidden" value="<?php echo esc_attr($arguments['stepId']); ?>"
    form="<?php echo esc_attr($arguments['formId']); ?>"
    name="<?php echo esc_attr("productsToAdd[{$arguments['addToCartKey']}][step_id]"); ?>">
<input type="hidden" value="<?php echo esc_attr($productId); ?>"
    form="<?php echo esc_attr($arguments['formId']); ?>"
    name="<?php echo esc_attr("productsToAdd[{$arguments['addToCartKey']}][product_id]"); ?>">
<span class="<?php echo esc_attr($arguments['class']); ?>-choose is-<?php echo esc_attr($inputType); ?>">
    <input type="<?php echo esc_attr($inputType); ?>"
        form="<?php echo esc_attr($arguments['formId']); ?>"
        id="woocommerce-products-wizard-form-item-choose-<?php echo esc_attr($arguments['addToCartKey']); ?>"
        name="productsToAddChecked[<?php echo esc_attr($arguments['stepId']); ?>][]"
        value="<?php echo esc_attr($productId); ?>"
        class="form-check-input custom-control-input"
        data-component="wcpw-product-choose"
        data-step-id="<?php echo esc_attr($arguments['stepId']); ?>"
        aria-label="<?php esc_attr_e('Choose', 'products-wizard-lite-for-woocommerce'); ?>"<?php
        checked(in_array($productId, $arguments['activeProductsIds']));
        disabled(!($product->is_purchasable() && ($product->is_in_stock() || $product->backorders_allowed())));
        ?>>
    <span class="custom-control-label d-inline-block"></span>
</span>
<!--spacer-->
