<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Form;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'formId' => null,
    'previousStepId' => Form::getPreviousStepId($id),
    'backButtonText' => Wizard::getSetting($id, 'back_button_text'),
    'backButtonClass' => Wizard::getSetting($id, 'back_button_class')
]);
?>
<button class="btn woocommerce-products-wizard-control is-back <?php echo esc_attr($arguments['backButtonClass']); ?>"
    form="<?php echo esc_attr($arguments['formId']); ?>" type="submit" name="get-step"
    value="<?php echo esc_attr($arguments['previousStepId']); ?>" data-component="wcpw-back wcpw-nav-item"
    data-nav-id="<?php echo esc_attr($arguments['previousStepId']); ?>"
    data-nav-action="get-step"><span class="woocommerce-products-wizard-control-inner">
        <!--spacer-->
        <?php echo wp_kses_post($arguments['backButtonText']); ?>
        <!--spacer-->
    </span></button>
<!--spacer-->
