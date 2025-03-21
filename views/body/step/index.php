<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Form;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'step' => null,
    'stepId' => null,
    'activeStepId' => Form::getActiveStepId($id),
]);

if ($arguments['stepId'] == 'result') {
    if ($arguments['activeStepId'] == 'result') {
        Template::html('result', $arguments);
    }

    return;
}

Template::html('form/index', $arguments);
