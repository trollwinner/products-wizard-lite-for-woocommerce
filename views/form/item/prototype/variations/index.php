<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Entities\WizardStep;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'stepId' => $stepId,
    'class' => 'woocommerce-products-wizard-form-item',
    'addToCartKey' => '',
    'formId' => null,
    'variationsTemplate' => WizardStep::getSetting($id, $stepId, 'item_variations_template'),
    'variationsArguments' => []
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

$productId = method_exists($product, 'get_parent_id') && $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
?>
<dl class="<?php echo esc_attr($arguments['class']); ?>-variations" data-component="wcpw-product-variations-data"><?php
    if (isset($arguments['variationsArguments']['attributes'])
        && is_array($arguments['variationsArguments']['attributes'])
    ) {
        foreach ($arguments['variationsArguments']['attributes'] as $attributeKey => $attributeValues) {
            Template::html(
                "form/item/prototype/variations/item/{$arguments['variationsTemplate']}",
                array_replace(
                    $arguments,
                    [
                        'attributeValues' => $attributeValues,
                        'attributeKey' => $attributeKey
                    ]
                )
            );
        }
    }
    ?></dl>
<div class="<?php echo esc_attr($arguments['class']); ?>-variations-reset-wrapper">
    <a href="#" role="button" class="<?php echo esc_attr($arguments['class']); ?>-variations-reset" hidden
        data-component="wcpw-product-variations-reset"><?php esc_html_e('Clear', 'products-wizard-lite-for-woocommerce'); ?></a>
</div>
<input type="hidden" class="variation_id" value="" form="<?php echo esc_attr($arguments['formId']); ?>"
    name="<?php echo esc_attr("productsToAdd[{$arguments['addToCartKey']}][variation_id]"); ?>"
    data-component="wcpw-product-variations-variation-id">
