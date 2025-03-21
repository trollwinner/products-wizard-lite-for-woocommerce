<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'stepId' => null,
    'controls' => Wizard::getSetting($id, 'footer_controls')
]);

$class = ['is-step-' . $arguments['stepId']];
?>
<footer class="woocommerce-products-wizard-footer <?php echo esc_attr(implode(' ', $class)); ?>"
    data-component="wcpw-footer"><?php Template::html('controls/index', $arguments); ?></footer>
