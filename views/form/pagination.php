<?php
defined('ABSPATH') || exit;

use WCProductsWizard\Form;
use WCProductsWizard\Template;

$items = Form::getPaginationItems(Template::getHTMLArgs());

if (empty($items)) {
    return;
}
?>
<nav class="woocommerce-products-wizard-form-pagination"
    aria-label="<?php esc_attr_e('Page navigation', 'products-wizard-lite-for-woocommerce'); ?>">
    <ul class="woocommerce-products-wizard-form-pagination-list pagination" data-component="wcpw-form-pagination">
        <?php foreach ($items as $item) { ?>
            <li class="page-item <?php echo esc_attr($item['class']); ?>"><?php
                echo wp_kses_post($item['innerHtml']);
                ?></li>
        <?php } ?>
    </ul>
</nav>
