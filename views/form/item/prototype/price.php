<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'stepId' => null,
    'product' => null,
    'class' => 'woocommerce-products-wizard-form-item'
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

$priceHtml = $product->get_price_html();
?>
<label class="<?php
    echo ($product->get_price() == 0 ? 'is-zero-price ' : '') . esc_attr($arguments['class']);
    ?>-price"
    for="woocommerce-products-wizard-form-item-choose-<?php
    echo esc_attr($arguments['stepId'] . '-'  . $product->get_id());
    ?>"
    data-component="wcpw-product-price"
    data-default="<?php echo esc_attr($priceHtml); ?>"><?php echo wp_kses_post($priceHtml); ?></label>
<!--spacer-->
