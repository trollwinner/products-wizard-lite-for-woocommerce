<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'formId' => null,
    'nextButtonText' => Wizard::getSetting($id, 'next_button_text')
]);
?>
<form action="#" method="POST" enctype="multipart/form-data" hidden
    id="<?php echo esc_attr($arguments['formId']); ?>" data-component="wcpw-form">
    <?php // no-js keyboard version of submit. should be upper the other ?>
    <button type="submit" class="sr-only visually-hidden" name="submit"
        data-component="wcpw-next wcpw-nav-item" data-nav-action="submit"><?php
        echo wp_kses_post($arguments['nextButtonText']);
        ?></button>
    <input type="hidden" name="woocommerce-products-wizard">
    <input type="hidden" name="id"
        value="<?php echo esc_attr($arguments['id']); ?>">
</form>

