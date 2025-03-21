<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Cart;
use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Form;
use WCProductsWizard\Template;
use WCProductsWizard\Utils;

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'cart' => Cart::get($id),
    'cartTotalPrice' => Cart::getTotalPrice($id),
    'navItems' => Form::getNavItems($id),
    'sidebarPosition' => Wizard::getSetting($id, 'sidebar_position'),
    'stickyWidget' => Wizard::getSetting($id, 'sticky_widget'),
    'totalString' => Wizard::getSetting($id, 'total_string')
]);

$class = [
    'is-position-' . $arguments['sidebarPosition'],
    'toggle-md'
];

$style = [];
$isSticky = !in_array($arguments['stickyWidget'], ['0', 'never']);
$key = "#woocommerce-products-wizard-widget-{$arguments['id']}-expanded";
$isExpanded = isset($_COOKIE[$key]) ? sanitize_text_field(wp_unslash($_COOKIE[$key])) : true;

if ($isSticky) {
    $class[] = 'is-sticky' . (!in_array($arguments['stickyWidget'], ['1', 'always']) ? "-{$arguments['stickyWidget']}" : '');
}
?>
<section class="woocommerce-products-wizard-widget panel panel-default card <?php
    echo esc_attr(implode(' ', $class));
    ?>"
    id="woocommerce-products-wizard-widget-<?php echo esc_attr($arguments['id']); ?>"
    aria-label="<?php esc_html_e('Cart', 'products-wizard-lite-for-woocommerce'); ?>"
    aria-expanded="<?php echo wp_json_encode(filter_var($isExpanded, FILTER_VALIDATE_BOOLEAN)); ?>"
    data-component="wcpw-widget"<?php
    echo !empty($style) ? ' style="' . esc_attr(Utils::stylesArrayToString($style)) . '"' : '';
    ?>>
    <?php
    if (empty($arguments['cart'])) {
        Template::html('messages/cart-is-empty', $arguments);
    } else {
        ?>
        <a href="#woocommerce-products-wizard-widget-<?php echo esc_attr($arguments['id']); ?>" role="button"
            class="woocommerce-products-wizard-widget-close close btn-close"
            aria-controls="woocommerce-products-wizard-widget-<?php echo esc_attr($arguments['id']); ?>"
            aria-label="<?php esc_attr_e('Close', 'products-wizard-lite-for-woocommerce'); ?>"
            aria-expanded="<?php echo wp_json_encode(filter_var($isExpanded, FILTER_VALIDATE_BOOLEAN)); ?>"
            data-component="wcpw-toggle">
            <span aria-hidden="true" class="visually-hidden">&times;</span>
        </a>
        <ul class="woocommerce-products-wizard-widget-body">
            <?php
            foreach ($arguments['navItems'] as $navItem) {
                foreach ($arguments['cart'] as $cartItemKey => $cartItem) {
                    if (!isset($cartItem['step_id']) || $cartItem['step_id'] != $navItem['id']
                        || (isset($cartItem['data']) && (!$cartItem['data']
                            || ($cartItem['data'] instanceof WC_Product && !$cartItem['data']->exists())))
                        || (isset($cartItem['quantity']) && $cartItem['quantity'] <= 0)
                        || (isset($cartItem['value']) && empty($cartItem['value']))
                    ) {
                        continue;
                    }

                    $itemArguments = array_replace(
                        $arguments,
                        [
                            'cartItem' => $cartItem,
                            'cartItemKey' => $cartItemKey,
                            'navItem' => $navItem
                        ]
                    );

                    if (isset($cartItem['product_id'], $cartItem['data'])) {
                        Template::html('widget/product', $itemArguments);
                    }
                }
            }
            ?>
        </ul>
        <footer class="woocommerce-products-wizard-widget-footer">
            <dl class="woocommerce-products-wizard-widget-footer-row is-total">
                <dt class="woocommerce-products-wizard-widget-footer-cell is-caption"><?php
                    echo wp_kses_post($arguments['totalString']);
                    ?></dt>
                <dd class="woocommerce-products-wizard-widget-footer-cell is-value"><?php
                    echo wp_kses_post($arguments['cartTotalPrice']);
                    ?></dd>
            </dl>
        </footer>
        <?php
    }
    ?>
</section>
