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
    'startButtonText' => Wizard::getSetting($id, 'start_button_text'),
    'startButtonClass' => Wizard::getSetting($id, 'start_button_class')
]);
?>
<button class="btn woocommerce-products-wizard-control is-start <?php echo esc_attr($arguments['startButtonClass']); ?>"
    form="<?php echo esc_attr($arguments['formId']); ?>" type="submit" name="submit"
    data-component="wcpw-start wcpw-nav-item"
    data-nav-action="submit"><span class="woocommerce-products-wizard-control-inner">
        <!--spacer-->
        <?php echo wp_kses_post($arguments['startButtonText']); ?>
        <!--spacer-->
    </span></button>
<!--spacer-->
