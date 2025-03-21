<?php
defined('ABSPATH') || exit;

use WCProductsWizard\Entities\ProductVariation;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'class' => 'woocommerce-products-wizard-form-item',
    'footerSubClass' => '',
    'showFooterPrice' => true,
    'showFooterChoose' => true,
    'severalProducts' => false,
    'product' => null,
    'defaultAttributes' => [],
    'variationsArguments' => []
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

// get default selected attributes
if (empty($arguments['defaultAttributes'])) {
    if (method_exists($product, 'get_default_attributes')) {
        $arguments['defaultAttributes'] = $product->get_default_attributes();
    } elseif (method_exists($product, 'get_variation_default_attributes')) {
        $arguments['defaultAttributes'] = $product->get_variation_default_attributes();
    }
}

// get variations data
if (empty($arguments['variationsArguments'])) {
    $arguments['variationsArguments'] = ProductVariation::getArguments($arguments);
}
?>
<form data-component="wcpw-product-footer wcpw-product-variations"
    data-product_id="<?php echo esc_attr($product->get_id()); ?>"
    data-product_variations="<?php
    echo esc_attr(wp_json_encode(array_values($arguments['variationsArguments']['variations'])));
    ?>"
    class="<?php
    echo esc_attr(trim($arguments['footerSubClass'] . ' ' . $arguments['class']));
    ?>-footer cart variations_form"><?php
    do_action('woocommerce_before_variations_form');

    Template::html('form/item/prototype/variations/index', $arguments);
    Template::html('form/item/prototype/availability', $arguments);

    do_action('woocommerce_before_add_to_cart_button');

    if ($arguments['showFooterPrice'] || $arguments['showFooterChoose']) {
        $inputType = $arguments['severalProducts'] ? 'checkbox' : 'radio';
        ?>
        <div class="<?php echo esc_attr($arguments['class']); ?>-check <?php
            echo esc_attr('form-check custom-control custom-' . $inputType);
            ?>">
            <?php
            if ($arguments['showFooterChoose']) {
                Template::html('form/item/prototype/choose', $arguments);
            }

            if ($arguments['showFooterPrice']) {
                Template::html('form/item/prototype/price', $arguments);
            }
            ?>
        </div>
        <?php
    }

    Template::html('form/item/prototype/controls', $arguments);

    do_action('woocommerce_after_add_to_cart_button');
    do_action('woocommerce_after_variations_form');
    ?></form>
