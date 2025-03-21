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
    'stepId' => null,
    'mode' => 'step-by-step',
    'navItems' => Form::getNavItems($id),
    'navTemplate' => Wizard::getSetting($id, 'nav_template'),
    'navButtonClass' => Wizard::getSetting($id, 'nav_button_class'),
    'showSidebar' => Wizard::isSidebarShowed($id),
    'sidebarWidth' => Wizard::getSidebarWidth($id),
    'sidebarPosition' => Wizard::getSetting($id, 'sidebar_position')
]);

$style = [];
$class = [
    "is-{$arguments['mode']}-mode",
    "is-step-{$arguments['stepId']}",
    "is-sidebar-{$arguments['sidebarPosition']}"
];

if ($arguments['showSidebar']) {
    $class[] = 'has-sidebar';
}

foreach ($arguments['sidebarWidth'] as $size => $value) {
    $style["--wcpw-sidebar-width-$size"] = $value;
}
?>
<div class="woocommerce-products-wizard-body woocommerce-products-wizard-main-row is-tabs <?php
    echo esc_attr(implode(' ', $class));
    ?>"<?php echo !empty($style) ? ' style="' . esc_attr(Utils::stylesArrayToString($style)) . '"' : ''; ?>
    role="tablist" data-component="wcpw-body">
    <?php
    if ($arguments['showSidebar']) {
        Template::html('sidebar', $arguments);
    }
    ?>
    <div class="woocommerce-products-wizard-main"><?php
        foreach ($arguments['navItems'] as $navItem) {
            if ($arguments['navTemplate'] != 'none') {
                Template::html('nav/button', array_replace($arguments, $navItem));
            }

            if (empty($navItem['state']) || $navItem['state'] != 'active') {
                continue;
            }

            $arguments['step'] = $navItem;
            $arguments['stepId'] = $navItem['id'];

            Template::html('body/step/index', $arguments);
        }
        ?></div>
</div>
