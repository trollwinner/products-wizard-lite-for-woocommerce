<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Entities\WizardStep;
use WCProductsWizard\Form;
use WCProductsWizard\Template;

$availableControls = [];
$arguments = Template::getHTMLArgs([
    'stepId' => $stepId,
    'mode' => 'step-by-step',
    'controls' => [],
    'minProductsSelected' => WizardStep::getSetting($id, $stepId, 'min_products_selected'),
    'enableResultsStep' => Wizard::getSetting($id, 'enable_results_step'),
    'showWidget' => Wizard::isWidgetShowed($id),
    'nextStepId' => Form::getNextStepId($id),
    'canGoBack' => Form::canGoBack($id),
    'canGoForward' => Form::canGoForward($id)
]);
?>
<div class="woocommerce-products-wizard-controls" data-component="wcpw-controls"><?php
    if ($arguments['mode'] == 'single-step') {
        // is single-step mode
        $availableControls = [
            'spacer' => true,
            'widget-toggle' => $arguments['showWidget'],
            'reset' => true,
            'add-to-cart' => true,
            'share' => true
        ];
    } elseif (is_numeric($arguments['stepId']) && !$arguments['canGoForward']) {
        // is a numeric and the last step
        $availableControls = [
            'spacer' => true,
            'widget-toggle' => $arguments['showWidget'],
            'reset' => true,
            'back' => $arguments['canGoBack'],
            'add-to-cart' => true,
            'share' => true
        ];
    } elseif (is_numeric($arguments['stepId']) && $arguments['canGoForward']) {
        // is a numeric step but not the last
        $availableControls = [
            'spacer' => true,
            'widget-toggle' => $arguments['showWidget'],
            'reset' => true,
            'back' => $arguments['canGoBack'],
            'skip' => empty($arguments['minProductsSelected']['value']),
            'next' => true,
            'to-results' => $arguments['enableResultsStep'],
            'share' => true
        ];
    } elseif ($arguments['stepId'] == 'start') {
        // is the start step
        $availableControls = [
            'spacer' => true,
            'widget-toggle' => $arguments['showWidget'],
            'start' => true
        ];
    } elseif ($arguments['stepId'] == 'result') {
        // is the results step
        $availableControls = [
            'spacer' => true,
            'widget-toggle' => $arguments['showWidget'],
            'reset' => true,
            'back' => $arguments['canGoBack'],
            'add-to-cart' => true,
            'share' => true
        ];
    } elseif (!$arguments['canGoForward']) {
        // is the last step
        $availableControls = [
            'spacer' => true,
            'widget-toggle' => $arguments['showWidget'],
            'reset' => true,
            'back' => $arguments['canGoBack']
        ];
    }

    foreach ($arguments['controls'] as $control) {
        $control = $control == 'spacer-2' ? 'spacer' : $control;

        if (empty($availableControls[$control])) {
            continue;
        }

        Template::html('controls/' . $control, $arguments);
    }
    ?></div>
