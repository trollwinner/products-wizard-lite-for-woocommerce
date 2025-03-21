<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Entities\WizardStep;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'class' => 'woocommerce-products-wizard-form-item',
    'descriptionSource' => WizardStep::getSetting($id, $stepId, 'item_description_source'),
    'showDescriptions' => WizardStep::getSetting($id, $stepId, 'show_item_descriptions'),
    'product' => null
]);

if (!$arguments['showDescriptions']) {
    return;
}

$product = $arguments['product'];

if (!$product instanceof WC_Product) {
    return;
}

switch ($arguments['descriptionSource']) {
    default:
    case 'content':
        $description = force_balance_tags(do_shortcode(wpautop($product->get_description())));
        break;

    case 'excerpt':
        $description = force_balance_tags(do_shortcode(wpautop($product->get_short_description())));
        break;

    case 'none':
        $description = '';
}
?>
<div class="<?php echo esc_attr($arguments['class']); ?>-description"
    data-component="wcpw-product-description"
    data-default="<?php echo esc_attr($description); ?>"><?php echo wp_kses_post($description); ?></div>
