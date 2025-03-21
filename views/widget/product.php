<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Cart;
use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Entities\WizardStep;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'stepId' => null,
    'formId' => null,
    'cartItem' => null,
    'cartItemKey' => null,
    'navItem' => null,
    'enableRemoveButton' => Wizard::getSetting($id, 'enable_remove_button'),
    'removeButtonText' => Wizard::getSetting($id, 'remove_button_text'),
    'removeButtonClass' => Wizard::getSetting($id, 'remove_button_class'),
    'enableEditButton' => Wizard::getSetting($id, 'enable_edit_button'),
    'editButtonText' => Wizard::getSetting($id, 'edit_button_text'),
    'editButtonClass' => Wizard::getSetting($id, 'edit_button_class')
]);

$product = $arguments['cartItem']['data'];

if (!$product instanceof WC_Product) {
    return;
}
?>
<li class="woocommerce-products-wizard-widget-body-item is-product <?php
    echo esc_attr("is-step-{$arguments['cartItem']['step_id']} is-product-{$arguments['cartItem']['product_id']}");
    echo $arguments['stepId'] == $arguments['cartItem']['step_id'] ? ' is-current-step' : '';
    ?>">
    <article class="woocommerce-products-wizard-widget-item is-product">
        <?php if (WizardStep::getSetting($arguments['id'], $arguments['cartItem']['step_id'], 'show_item_thumbnails')) { ?>
            <figure class="woocommerce-products-wizard-widget-item-thumbnail">
                <?php
                $href = wp_get_attachment_image_src($product->get_image_id(), 'large');
                $thumbnail = $product->get_image('shop_thumbnail', ['class' => 'img-thumbnail']);
                $thumbnail = apply_filters(
                    'wcpw_widget_item_thumbnail',
                    $thumbnail,
                    $arguments['cartItem'],
                    $arguments['cartItemKey']
                );

                echo isset($href[0])
                    ? wp_kses_post("<a href=\"$href[0]\" data-rel=\"prettyPhoto\" rel=\"lightbox\">$thumbnail</a>")
                    : wp_kses_post($thumbnail);
                ?>
            </figure>
        <?php } ?>
        <div class="woocommerce-products-wizard-widget-item-inner">
            <header class="woocommerce-products-wizard-widget-item-header">
                <h4 class="woocommerce-products-wizard-widget-item-title"><?php
                    if (method_exists($product, 'get_name')) {
                        echo wp_kses_post($product->get_name());
                    }

                    if (empty($arguments['cartItem']['sold_individually'])) {
                        ?>
                        <bdi class="woocommerce-products-wizard-widget-item-times">x</bdi>
                        <span class="woocommerce-products-wizard-widget-item-quantity"><?php
                            echo wp_kses_post($arguments['cartItem']['quantity']);
                            ?></span>
                        <?php
                    }
                    ?></h4>
                <?php
                if ($arguments['enableEditButton']) {
                    $stepId = $arguments['cartItem']['step_id'];
                    ?>
                    <button class="woocommerce-products-wizard-widget-item-control woocommerce-products-wizard-control <?php
                        echo esc_attr($arguments['editButtonClass']);
                        ?> btn is-edit-in-cart"
                        form="<?php echo esc_attr($arguments['formId']); ?>"
                        name="get-step"
                        value="<?php echo esc_attr($stepId); ?>"
                        title="<?php echo esc_attr($arguments['editButtonText']); ?>"
                        data-component="wcpw-product-edit-in-cart wcpw-nav-item"
                        data-nav-action="get-step"
                        data-nav-id="<?php echo esc_attr($stepId); ?>">
                        <!--spacer-->
                        <span class="woocommerce-products-wizard-control-inner"><?php
                            echo wp_kses_post($arguments['editButtonText']);
                            ?></span>
                        <!--spacer-->
                    </button>
                    <?php
                }
                ?>
            </header>
            <div class="woocommerce-products-wizard-widget-item-data"><?php
                echo wp_kses_post(Cart::getProductMeta($arguments['cartItem']));
                ?></div>
            <footer class="woocommerce-products-wizard-widget-item-footer">
                <?php $price = Cart::getItemPrice($arguments['cartItem']); ?>
                <span class="woocommerce-products-wizard-widget-item-price<?php
                    echo $price == 0 ? ' is-zero-price ' : '';
                    ?>"><?php
                    // apply the filter for Subscriptions support
                    echo wp_kses_post(apply_filters('woocommerce_cart_product_price', wc_price($price), $product));

                    if (!isset($arguments['cartItem']['sold_individually'])
                        || !$arguments['cartItem']['sold_individually']
                    ) {
                        ?>
                        <bdi class="woocommerce-products-wizard-widget-item-price-times">x</bdi>
                        <span class="woocommerce-products-wizard-widget-item-price-quantity"><?php
                            echo wp_kses_post($arguments['cartItem']['quantity']);
                            ?></span>
                        <?php
                    }
                    ?></span>
                <?php
                if ($arguments['enableRemoveButton']) {
                    ?>
                    <button class="woocommerce-products-wizard-widget-item-control woocommerce-products-wizard-control <?php
                        echo esc_attr($arguments['removeButtonClass']);
                        ?> btn is-remove-from-cart"
                        form="<?php echo esc_attr($arguments['formId']); ?>"
                        name="remove-cart-product"
                        value="<?php echo esc_attr($arguments['cartItemKey']); ?>"
                        title="<?php echo esc_attr($arguments['removeButtonText']); ?>"
                        data-component="wcpw-remove-cart-product">
                        <!--spacer-->
                        <span class="woocommerce-products-wizard-control-inner"><?php
                            echo wp_kses_post($arguments['removeButtonText']);
                            ?></span>
                        <!--spacer-->
                    </button>
                    <?php
                }
                ?>
            </footer>
        </div>
    </article>
</li>
