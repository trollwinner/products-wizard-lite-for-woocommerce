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
    'nextButtonText' => Wizard::getSetting($id, 'next_button_text'),
    'nextButtonClass' => Wizard::getSetting($id, 'next_button_class')
]);
?>
<button class="btn woocommerce-products-wizard-control is-next <?php echo esc_attr($arguments['nextButtonClass']); ?>"
    form="<?php echo esc_attr($arguments['formId']); ?>" type="submit" name="submit"
    data-component="wcpw-next wcpw-nav-item"
    data-nav-action="submit"><span class="woocommerce-products-wizard-control-inner">
        <!--spacer-->
        <?php echo wp_kses_post($arguments['nextButtonText']); ?>
        <!--spacer-->
    </span></button>
<!--spacer-->
