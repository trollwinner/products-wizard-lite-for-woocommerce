<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'formId' => null,
    'resetButtonText' => Wizard::getSetting($id, 'reset_button_text'),
    'resetButtonClass' => Wizard::getSetting($id, 'reset_button_class')
]);
?>
<button class="btn woocommerce-products-wizard-control is-reset <?php echo esc_attr($arguments['resetButtonClass']); ?>"
    form="<?php echo esc_attr($arguments['formId']); ?>" type="submit" name="reset-all"
    data-component="wcpw-reset wcpw-nav-item"
    data-nav-action="reset"><span class="woocommerce-products-wizard-control-inner">
        <!--spacer-->
        <?php echo wp_kses_post($arguments['resetButtonText']); ?>
        <!--spacer-->
    </span></button>
<!--spacer-->
