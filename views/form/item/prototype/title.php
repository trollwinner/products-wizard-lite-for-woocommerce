<?php
defined('ABSPATH') || exit;

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'class' => 'woocommerce-products-wizard-form-item',
    'product' => null,
    'titleSubClass' => '',
    'titleBefore' => '',
    'titleAfter' => '',
    'enableTitleLink' => false
]);

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}
?>
<h3 class="<?php echo esc_attr(trim($arguments['titleSubClass'] . ' ' . $arguments['class'])); ?>-title">
    <!--spacer-->
    <?php
    echo wp_kses_post($arguments['titleBefore']);

    if ($arguments['enableTitleLink']) {
        echo '<a href="' . esc_url($product->get_permalink())
            . '" target="_blank" class="woocommerce-products-wizard-outer-link '
            . esc_attr($arguments['class']) . '-title-link"> ';
    }

    echo wp_kses_post($product->get_name());

    if ($arguments['enableTitleLink']) {
        echo ' </a>';
    }

    echo wp_kses_post($arguments['titleAfter']);
    ?>
    <!--spacer-->
</h3>
