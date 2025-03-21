<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Cart;
use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'cart' => Cart::get($id),
    'cartTotalPrice' => Cart::getTotalPrice($id),
    'formId' => null,
    'widgetToggleButtonText' => Wizard::getSetting($id, 'widget_toggle_button_text'),
    'widgetToggleButtonClass' => Wizard::getSetting($id, 'widget_toggle_button_class')
]);

$text = str_replace('[wcpw-cart-total-price]', $arguments['cartTotalPrice'], $arguments['widgetToggleButtonText']);
$key = "#woocommerce-products-wizard-widget-{$arguments['id']}-expanded";
$isExpanded = isset($_COOKIE[$key]) ? sanitize_text_field(wp_unslash($_COOKIE[$key])) : true;
?>
<a href="#woocommerce-products-wizard-widget-<?php echo esc_attr($arguments['id']); ?>" role="button"
    class="btn woocommerce-products-wizard-control is-widget-toggle <?php
    echo esc_attr($arguments['widgetToggleButtonClass']);
    ?>"
    aria-controls="woocommerce-products-wizard-widget-<?php echo esc_attr($arguments['id']); ?>"
    aria-expanded="<?php echo wp_json_encode(filter_var($isExpanded, FILTER_VALIDATE_BOOLEAN)); ?>"
    data-component="wcpw-toggle wcpw-scroll-to-element"
    data-scroll-to-element-target="#woocommerce-products-wizard-widget-<?php echo esc_attr($arguments['id']); ?>">
    <?php if (count($arguments['cart']) > 0) { ?>
        <span class="woocommerce-products-wizard-control-badge badge badge-pill"><?php
            echo count($arguments['cart']);
            ?></span>
    <?php } ?>
    <span class="woocommerce-products-wizard-control-inner">
        <!--spacer-->
        <?php echo wp_kses_post($text); ?>
        <!--spacer-->
    </span>
</a>
<!--spacer-->
