<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs(['emptyCartMessage' => Wizard::getSetting($id, 'empty_cart_message')]);
?>
<div class="woocommerce-products-wizard-message empty-cart woocommerce-info"
    aria-live="polite"><?php echo wp_kses_post($arguments['emptyCartMessage']); ?></div>
