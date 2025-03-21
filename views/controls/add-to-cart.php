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
    'formId' => null,
    'cartTotalPrice' => Cart::getTotalPrice($id),
    'addToCartButtonText' => Wizard::getSetting($id, 'add_to_cart_button_text'),
    'addToCartButtonClass' => Wizard::getSetting($id, 'add_to_cart_button_class')
]);

$text = str_replace('[wcpw-cart-total-price]', $arguments['cartTotalPrice'], $arguments['addToCartButtonText']);
?>
<button class="btn woocommerce-products-wizard-control is-add-to-cart <?php
    echo esc_attr($arguments['addToCartButtonClass']);
    ?>"
    form="<?php echo esc_attr($arguments['formId']); ?>" type="submit" name="add-to-main-cart"
    data-component="wcpw-add-to-cart wcpw-nav-item"
    data-nav-action="add-to-main-cart"><span class="woocommerce-products-wizard-control-inner">
        <!--spacer-->
        <?php echo wp_kses_post($text); ?>
        <!--spacer-->
    </span></button>
<!--spacer-->
