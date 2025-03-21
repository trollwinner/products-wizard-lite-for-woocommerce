<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'navTemplate' => Wizard::getSetting($id, 'nav_template'),
    'toggleMobileNavOn' => 'sm',
    'stepId' => null
]);

if ($arguments['navTemplate'] == 'none') {
    return;
}

$class = [
    'is-' . $arguments['navTemplate'],
    'is-step-' . $arguments['stepId']
];

if (!in_array($arguments['toggleMobileNavOn'], ['0', 'never'])) {
    $class[] = "d-none d-{$arguments['toggleMobileNavOn']}-block";
}
?>
<nav class="woocommerce-products-wizard-nav <?php echo esc_attr(implode(' ', $class)); ?>"
    data-component="wcpw-nav"><?php Template::html('nav/views/' . $arguments['navTemplate'], $arguments); ?></nav>
