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
    'formId' => "wcpw-form-$id",
    'showHeader' => Wizard::getSetting($id, 'show_header'),
    'showFooter' => Wizard::getSetting($id, 'show_footer'),
    'bodyTemplate' => Wizard::getBodyTemplate($id),
    'mode' => Wizard::getSetting($id, 'mode')
]);

do_action('wcpw_before_output', $arguments);

$arguments['stepId'] = Form::getActiveStepId($id); // force define the active step

echo '<div class="woocommerce-products-wizard-content">';

Template::html('form', $arguments);

if ($arguments['bodyTemplate'] == 'tabs') {
    Template::html('nav/index', $arguments);
}

if ($arguments['showHeader']) {
    Template::html('header', $arguments);
}

Template::html("body/{$arguments['bodyTemplate']}", $arguments);

if ($arguments['showFooter']) {
    Template::html('footer', $arguments);
}

echo '</div>';

Template::html('spinner', $arguments);

do_action('wcpw_after_output', $arguments);
