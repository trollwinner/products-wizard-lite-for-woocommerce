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
    'id' => $id,
    'stepId' => $stepId,
    'class' => 'woocommerce-products-wizard-form-item',
    'enableIndividualAddToCartButton' => WizardStep::getSetting($id, $stepId, 'enable_add_to_cart_button'),
    'individualAddToCartBehavior' => WizardStep::getSetting($id, $stepId, 'add_to_cart_behavior'),
    'individualAddToCartButtonText' => WizardStep::getSetting($id, $stepId, 'add_to_cart_button_text'),
    'individualAddToCartButtonClass' => WizardStep::getSetting($id, $stepId, 'add_to_cart_button_class'),
    'enableIndividualUpdateButton' => WizardStep::getSetting($id, $stepId, 'enable_update_button'),
    'individualUpdateButtonText' => WizardStep::getSetting($id, $stepId, 'update_button_text'),
    'individualUpdateButtonClass' => WizardStep::getSetting($id, $stepId, 'update_button_class'),
    'enableIndividualRemoveButton' => WizardStep::getSetting($id, $stepId, 'enable_remove_button'),
    'individualRemoveButtonText' => WizardStep::getSetting($id, $stepId, 'remove_button_text'),
    'individualRemoveButtonClass' => WizardStep::getSetting($id, $stepId, 'remove_button_class'),
    'cartItem' => null,
    'formId' => null,
    'product' => null
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

$productType = $product->get_type();
$disabled = !($product->is_purchasable() && ($product->is_in_stock() || $product->backorders_allowed()));
$priceHtml = $product->get_price_html();
$price = '<span class="woocommerce-products-wizard-control-price" data-component="wcpw-product-price" data-default="'
    . esc_attr($priceHtml) . '">' . $priceHtml . '</span>';

// WooCommerce Subscriptions plugin support
$typesAliases = [
    'variable-subscription' => 'variable',
    'subscription' => 'simple'
];

if (isset($typesAliases[$productType])) {
    $productType = $typesAliases[$productType];
}
?>
<div class="<?php echo esc_attr($arguments['class']); ?>-controls btn-group">
    <?php
    Template::html('form/item/prototype/quantity', $arguments);

    if ($arguments['cartItem'] && $arguments['cartItem']['key']) {
        if ($arguments['enableIndividualUpdateButton']) {
            $text = str_replace('[wcpw-product-price]', $price, $arguments['individualUpdateButtonText']);
            ?>
            <button class="<?php
                echo esc_attr($arguments['individualUpdateButtonClass'] . ' ' . $arguments['class']);
                ?>-control woocommerce-products-wizard-control btn is-update-in-cart"
                form="<?php echo esc_attr($arguments['formId']); ?>"
                name="update-cart-product"
                value="<?php echo esc_attr($arguments['cartItem']['key']); ?>"
                title="<?php echo esc_attr(wp_strip_all_tags($text)); ?>"
                data-component="wcpw-update-cart-product"
                data-update-cart-product-options="<?php
                echo esc_attr(wp_json_encode(['behavior' => $arguments['individualAddToCartBehavior']]));
                ?>"<?php disabled($disabled); ?>>
                <!--spacer-->
                <span class="woocommerce-products-wizard-control-inner"><?php echo wp_kses_post($text); ?></span>
                <!--spacer-->
            </button>
            <?php
        }

        if ($arguments['enableIndividualRemoveButton']) {
            $text = str_replace('[wcpw-product-price]', $price, $arguments['individualRemoveButtonText']);
            ?>
            <button class="<?php
                echo esc_attr($arguments['individualRemoveButtonClass'] . ' ' . $arguments['class']);
                ?>-control woocommerce-products-wizard-control btn is-remove-from-cart"
                form="<?php echo esc_attr($arguments['formId']); ?>"
                name="remove-cart-product"
                value="<?php echo esc_attr($arguments['cartItem']['key']); ?>"
                title="<?php echo esc_attr(wp_strip_all_tags($text)); ?>"
                data-component="wcpw-remove-cart-product"
                <?php disabled($disabled); ?>>
                <!--spacer-->
                <span class="woocommerce-products-wizard-control-inner"><?php echo wp_kses_post($text); ?></span>
                <!--spacer-->
            </button>
            <?php
        }
    } elseif ($arguments['enableIndividualAddToCartButton']) {
        $text = str_replace('[wcpw-product-price]', $price, $arguments['individualAddToCartButtonText']);
        ?>
        <button class="<?php
            echo esc_attr($arguments['individualAddToCartButtonClass'] . ' ' . $arguments['class']);
            ?>-control woocommerce-products-wizard-control btn is-add-to-cart"
            form="<?php echo esc_attr($arguments['formId']); ?>"
            name="add-cart-product"
            value="<?php echo esc_attr($arguments['addToCartKey']); ?>"
            title="<?php echo esc_attr(wp_strip_all_tags($text)); ?>"
            data-component="wcpw-add-cart-product"
            data-add-cart-product-options="<?php
            echo esc_attr(wp_json_encode(['behavior' => $arguments['individualAddToCartBehavior']]));
            ?>"<?php disabled($disabled); ?>>
            <!--spacer-->
            <span class="woocommerce-products-wizard-control-inner"><?php echo wp_kses_post($text); ?></span>
            <!--spacer-->
        </button>
        <?php
    }
    ?>
</div>
