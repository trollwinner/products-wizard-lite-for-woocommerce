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
    'skipButtonText' => Wizard::getSetting($id, 'skip_button_text'),
    'skipButtonClass' => Wizard::getSetting($id, 'skip_button_class')
]);
?>
<button class="btn woocommerce-products-wizard-control is-skip <?php echo esc_attr($arguments['skipButtonClass']); ?>"
    form="<?php echo esc_attr($arguments['formId']); ?>" type="submit" name="skip-step"
    data-component="wcpw-skip wcpw-nav-item"
    data-nav-action="skip-step"><span class="woocommerce-products-wizard-control-inner">
        <!--spacer-->
        <?php echo wp_kses_post($arguments['skipButtonText']); ?>
        <!--spacer-->
    </span></button>
<!--spacer-->
