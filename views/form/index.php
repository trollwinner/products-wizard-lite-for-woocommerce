<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Cart;
use WCProductsWizard\Entities\Product;
use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Form;
use WCProductsWizard\Template;

$activeStepId = Form::getActiveStepId($id);
$stepId = isset($stepId) ? $stepId : $activeStepId;
$step = Form::getStep($id, $stepId);
$arguments = Template::getHTMLArgs([
    'id' => $id,
    'formId' => null,
    'title' => !empty($step['title']) ? $step['title'] : '',
    'thumbnail' => isset($step['thumbnail']) ? $step['thumbnail'] : '',
    'description' => isset($step['description']) ? $step['description'] : '',
    'notices' => WCProductsWizard\Instance()->form->getNotices($stepId),
    'page' => Form::getStepPageValue($stepId),
    'orderBy' => Form::getStepOrderByValue($stepId),
    'bodyTemplate' => Wizard::getBodyTemplate($id),
    'disabled' => false
]);

$arguments['stepId'] = $stepId; // force define the current step
$fieldsetId = "{$arguments['id']}-{$arguments['stepId']}";
$class = ['woocommerce-products-wizard-step', 'woocommerce-products-wizard-form', 'is-step-' . $arguments['stepId']];

if (!empty(Cart::getByStepId($arguments['id'], $arguments['stepId']))) {
    $class[] = 'has-products-in-cart';
}

if ($activeStepId == $arguments['stepId']) {
    $class[] = 'is-active';
}
?>
<fieldset class="<?php echo esc_attr(implode(' ', $class)); ?>"
    data-component="wcpw-form-step" data-id="<?php echo esc_attr($arguments['stepId']); ?>"
    id="woocommerce-products-wizard-form-<?php echo esc_attr($fieldsetId); ?>"
    <?php disabled($arguments['disabled']); ?>>
    <?php
    if (!empty($arguments['notices'])) {
        foreach ($arguments['notices'] as $notice) {
            Template::html("messages/{$notice['view']}", array_replace($arguments, $notice));
        }
    }

    if ($arguments['description']) {
        Template::html('form/description', $arguments);
    }

    if (is_numeric($arguments['stepId'])) {
        ?>
        <input type="hidden" form="<?php echo esc_attr($arguments['formId']); ?>"
            name="productsToAddChecked[<?php echo esc_attr($arguments['stepId']); ?>][]" value="">
        <div class="woocommerce-products-wizard-form-controls"><?php
            Template::html('form/order-by', $arguments);
            Template::html('form/products-per-page', $arguments);
            ?></div>
        <?php
        Product::request($arguments);
    }
    ?>
</fieldset>
