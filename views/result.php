<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;
$mode = isset($mode) ? $mode : 'step-by-step';
$notices = WCProductsWizard\Instance()->form->getNotices($mode == 'single-step' ? 'result' : null);

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Cart;
use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Entities\WizardStep;
use WCProductsWizard\Form;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'cart' => Cart::get($id),
    'cartTotalPrice' => Cart::getTotalPrice($id),
    'steps' => Form::getSteps($id),
    'notices' => $notices,
    'enableRemoveButton' => Wizard::getSetting($id, 'enable_remove_button'),
    'removeButtonClass' => Wizard::getSetting($id, 'remove_button_class'),
    'removeButtonText' => Wizard::getSetting($id, 'remove_button_text'),
    'enableEditButton' => Wizard::getSetting($id, 'enable_edit_button'),
    'editButtonText' => Wizard::getSetting($id, 'edit_button_text'),
    'editButtonClass' => Wizard::getSetting($id, 'edit_button_class'),
    'resultsStepDescription' => Wizard::getSetting($id, 'results_step_description'),
    'resultsRemoveString' => Wizard::getSetting($id, 'results_remove_string'),
    'resultsPriceString' => Wizard::getSetting($id, 'results_price_string'),
    'resultsThumbnailString' => Wizard::getSetting($id, 'results_thumbnail_string'),
    'resultsProductString' => Wizard::getSetting($id, 'results_product_string'),
    'resultsQuantityString' => Wizard::getSetting($id, 'results_quantity_string'),
    'subtotalString' => Wizard::getSetting($id, 'subtotal_string'),
    'totalString' => Wizard::getSetting($id, 'total_string')
]);

$previousStep = null;
$showProductsHeader = true;
$columnsNumber = 5 + (int) $arguments['enableRemoveButton'];
$class = ['woocommerce-products-wizard-step', 'woocommerce-products-wizard-results', 'is-step-result'];

if (Form::getActiveStepId($arguments['id']) == 'result') {
    $class[] = 'is-active';
}

echo '<article class="' . esc_attr(implode(' ', $class)) . '" data-component="wcpw-form-step">';

if (!empty($arguments['notices'])) {
    foreach ($arguments['notices'] as $notice) {
        Template::html("messages/{$notice['view']}", array_replace($arguments, $notice));
    }
}

if (empty($arguments['cart'])) {
    Template::html('messages/cart-is-empty', $arguments);

    echo '</article>';

    return;
}

if ($arguments['resultsStepDescription']) {
    echo '<div class="woocommerce-products-wizard-results-description">'
        . do_shortcode(wpautop($arguments['resultsStepDescription']))
        . '</div>';
}
?>
<div class="wcpw-table-responsive-wrapper">
    <table class="woocommerce-products-wizard-results-table table table-hover wcpw-table-responsive">
        <tbody class="woocommerce-products-wizard-results-table-body">
            <?php
            foreach ($arguments['cart'] as $cartItemKey => $cartItem) {
                if ((isset($cartItem['data'])
                        && (!$cartItem['data'] || ($cartItem['data'] instanceof WC_Product && !$cartItem['data']->exists())))
                    || (isset($cartItem['quantity']) && $cartItem['quantity'] <= 0)
                    || (isset($cartItem['value']) && empty($cartItem['value']))
                    || !isset($cartItem['step_id'])
                ) {
                    continue;
                }

                if ($showProductsHeader && isset($cartItem['product_id'], $cartItem['data'])) {
                    ?>
                    <tr class="woocommerce-products-wizard-results-table-body-row is-products wcpw-table-responsive-hidden">
                        <?php
                        if ($arguments['enableRemoveButton']) {
                            ?>
                            <th class="woocommerce-products-wizard-results-table-header-cell is-remove">
                            <span class="sr-only visually-hidden"><?php
                                echo wp_kses_post($arguments['resultsRemoveString']);
                                ?></span>
                            </th>
                            <?php
                        }
                        ?>
                        <th class="woocommerce-products-wizard-results-table-header-cell is-thumbnail">
                            <span class="sr-only visually-hidden"><?php
                                echo wp_kses_post($arguments['resultsThumbnailString']);
                                ?></span>
                        </th>
                        <th class="woocommerce-products-wizard-results-table-header-cell is-product"><?php
                            echo wp_kses_post($arguments['resultsProductString']);
                            ?></th>
                        <th class="woocommerce-products-wizard-results-table-header-cell is-price"><?php
                            echo wp_kses_post($arguments['resultsPriceString']);
                            ?></th>
                        <th class="woocommerce-products-wizard-results-table-header-cell is-quantity"><?php
                            echo wp_kses_post($arguments['resultsQuantityString']);
                            ?></th>
                        <th class="woocommerce-products-wizard-results-table-header-cell is-subtotal"><?php
                            echo wp_kses_post($arguments['subtotalString']);
                            ?></th>
                    </tr>
                    <?php
                    $showProductsHeader = false;
                }

                if ($previousStep != $cartItem['step_id']
                    && !empty($arguments['steps'][$cartItem['step_id']])
                ) {
                    $previousStep = $cartItem['step_id'];
                    ?>
                    <tr class="woocommerce-products-wizard-results-table-body-row is-heading <?php
                    echo esc_attr("is-step-{$cartItem['step_id']}");
                    ?>">
                        <td class="woocommerce-products-wizard-results-table-body-cell"
                            colspan="<?php echo esc_attr($columnsNumber); ?>">
                            <?php
                            if ($arguments['steps'][$cartItem['step_id']]['thumbnail']) {
                                echo wp_get_attachment_image(
                                    $arguments['steps'][$cartItem['step_id']]['thumbnail'],
                                    'thumbnail',
                                    false,
                                    ['class' => 'woocommerce-products-wizard-results-step-thumbnail']
                                );
                            }
                            ?>
                            <span class="woocommerce-products-wizard-results-step-name"><?php
                                echo wp_kses_post($arguments['steps'][$cartItem['step_id']]['title'])
                                ?></span>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <tr class="woocommerce-products-wizard-results-table-body-row is-item <?php
                    echo esc_attr("is-step-{$cartItem['step_id']}");

                    if (isset($cartItem['product_id'])) {
                        echo esc_attr(" is-product-{$cartItem['product_id']}");
                    }
                    ?>">
                    <?php
                    if (isset($cartItem['product_id'], $cartItem['data'])) {
                        $product = $cartItem['data'];

                        if (!$product instanceof WC_Product) {
                            continue;
                        }

                        $price = Cart::getItemPrice($cartItem);

                        if ($arguments['enableRemoveButton']) {
                            ?>
                            <td class="woocommerce-products-wizard-results-table-body-cell is-remove" data-th="">
                                <button class="woocommerce-products-wizard-results-item-remove woocommerce-products-wizard-control <?php
                                echo esc_attr($arguments['removeButtonClass']);
                                ?> btn is-remove-from-cart"
                                    form="<?php echo esc_attr($arguments['formId']); ?>"
                                    name="remove-cart-product"
                                    value="<?php echo esc_attr($cartItemKey); ?>"
                                    title="<?php echo esc_attr($arguments['removeButtonText']); ?>"
                                    data-component="wcpw-remove-cart-product">
                                    <!--spacer-->
                                    <span class="woocommerce-products-wizard-control-inner"><?php
                                        echo wp_kses_post($arguments['removeButtonText']);
                                        ?></span>
                                    <!--spacer-->
                                </button>
                            </td>
                            <?php
                        }
                        ?>
                        <td class="woocommerce-products-wizard-results-table-body-cell is-thumbnail" data-th="">
                            <?php if (WizardStep::getSetting($arguments['id'], $cartItem['step_id'], 'show_item_thumbnails')) { ?>
                                <figure class="woocommerce-products-wizard-results-item-thumbnail"><?php
                                    // phpcs:disable
                                    $href = wp_get_attachment_image_src($product->get_image_id(), 'full');
                                    $thumbnail = $product->get_image('shop_thumbnail', ['class' => 'img-thumbnail']);
                                    $thumbnail = apply_filters('wcpw_result_item_thumbnail', $thumbnail, $cartItem, $cartItemKey);

                                    echo isset($href[0])
                                        ? "<a href=\"$href[0]\" data-rel=\"prettyPhoto\" rel=\"lightbox\">$thumbnail</a>"
                                        : $thumbnail;
                                    // phpcs:enable
                                    ?></figure>
                            <?php } ?>
                        </td>
                        <td class="woocommerce-products-wizard-results-table-body-cell is-product" data-th="">
                            <div class="woocommerce-products-wizard-results-item-title-container">
                                <div class="woocommerce-products-wizard-results-item-title"><?php
                                    if (method_exists($product, 'get_name')) {
                                        echo wp_kses_post($product->get_name());
                                    }
                                    ?></div>
                                <?php
                                if ($arguments['enableEditButton']) {
                                    $stepId = $cartItem['step_id'];
                                    ?>
                                    <button class="woocommerce-products-wizard-results-item-edit woocommerce-products-wizard-control <?php
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
                                <div class="woocommerce-products-wizard-results-item-data"><?php
                                    echo wp_kses_post(Cart::getProductMeta($cartItem));

                                    // Backorder notification
                                    if ($product->backorders_require_notification()
                                        && $product->is_on_backorder($cartItem['quantity'])
                                    ) {
                                        echo '<p class="backorder_notification">'
                                            . esc_html__('Available on backorder', 'products-wizard-lite-for-woocommerce') . '</p>';
                                    }
                                    ?></div>
                            </div>
                        </td>
                        <td class="woocommerce-products-wizard-results-table-body-cell is-price"
                            data-th="<?php echo esc_attr($arguments['resultsPriceString']); ?>">
                            <span class="woocommerce-products-wizard-results-item-price<?php
                                echo $price == 0 ? ' is-zero-price ' : '';
                                ?>"><?php
                                // apply the filter for Subscriptions support
                                echo wp_kses_post(apply_filters('woocommerce_cart_product_price', wc_price($price), $product));
                                ?></span>
                        </td>
                        <td class="woocommerce-products-wizard-results-table-body-cell is-quantity"
                            data-th="<?php echo esc_attr($arguments['resultsQuantityString']); ?>">
                            <span class="woocommerce-products-wizard-results-item-quantity"><?php
                                echo wp_kses_post($cartItem['quantity']);
                                ?></span>
                        </td>
                        <td class="woocommerce-products-wizard-results-table-body-cell is-subtotal"
                            data-th="<?php echo esc_attr($arguments['subtotalString']); ?>">
                            <span class="woocommerce-products-wizard-results-item-subtotal<?php
                                echo $price == 0 ? ' is-zero-price ' : '';
                                ?>"><?php echo wp_kses_post(wc_price($price * $cartItem['quantity'])); ?></span>
                        </td>
                        <?php
                    }
                    ?>
                </tr>
                <?php
            }
            ?>
        </tbody>
        <tfoot class="woocommerce-products-wizard-results-table-footer">
            <tr class="woocommerce-products-wizard-results-table-footer-row is-total">
                <th class="woocommerce-products-wizard-results-table-footer-cell is-caption"
                    colspan="<?php echo esc_attr(ceil($columnsNumber / 2)); ?>"><?php
                    echo wp_kses_post($arguments['totalString']);
                    ?></th>
                <td class="woocommerce-products-wizard-results-table-footer-cell is-value"
                    colspan="<?php echo esc_attr(floor($columnsNumber / 2)); ?>"
                    data-th="<?php echo esc_attr($arguments['totalString']); ?>"><?php
                    echo wp_kses_post($arguments['cartTotalPrice']);
                    ?></td>
            </tr>
        </tfoot>
    </table>
</div>
<?php
echo '</article>';
