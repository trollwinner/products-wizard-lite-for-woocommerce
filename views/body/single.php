<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Form;
use WCProductsWizard\Template;
use WCProductsWizard\Utils;

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'stepId' => null,
    'mode' => 'single-step',
    'navItems' => Form::getNavItems($id),
    'activeStepId' => Form::getActiveStepId($id),
    'showSidebar' => Wizard::isSidebarShowed($id),
    'sidebarWidth' => Wizard::getSidebarWidth($id),
    'sidebarPosition' => Wizard::getSetting($id, 'sidebar_position')
]);

$style = [];
$class = ["is-{$arguments['mode']}-mode", "is-sidebar-{$arguments['sidebarPosition']}"];

if ($arguments['showSidebar']) {
    $class[] = 'has-sidebar';
}

foreach ($arguments['sidebarWidth'] as $size => $value) {
    $style["--wcpw-sidebar-width-$size"] = $value;
}
?>
<div class="woocommerce-products-wizard-body woocommerce-products-wizard-main-row is-single <?php
    echo esc_attr(implode(' ', $class));
    ?>"<?php echo !empty($style) ? ' style="' . esc_attr(Utils::stylesArrayToString($style)) . '"' : ''; ?>
    data-component="wcpw-body">
    <?php
    if ($arguments['showSidebar']) {
        Template::html('sidebar', $arguments);
    }
    ?>
    <div class="woocommerce-products-wizard-main"><?php
        foreach ($arguments['navItems'] as $navItem) {
            $arguments['step'] = $navItem;
            $arguments['stepId'] = $navItem['id'];

            Template::html('body/step/index', $arguments);
        }
        ?></div>
</div>
