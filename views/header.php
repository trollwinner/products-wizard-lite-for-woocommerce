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
    'controls' => Wizard::getSetting($id, 'header_controls')
]);

$class = ['is-step-' . $arguments['stepId']];
?>
<header class="woocommerce-products-wizard-header <?php echo esc_attr(implode(' ', $class)); ?>"
    data-component="wcpw-header"><?php Template::html('controls/index', $arguments); ?></header>
