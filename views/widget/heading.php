<?php
defined('ABSPATH') || exit;

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'stepId' => null,
    'cartItem' => null,
    'navItem' => [],
    'bodyTemplate' => 'tabs'
]);
?>
<li class="woocommerce-products-wizard-widget-body-item is-heading <?php
    echo esc_attr("is-step-{$arguments['navItem']['id']}");
    echo $arguments['stepId'] == $arguments['navItem']['id'] ? ' is-current-step' : '';
    ?>">
    <?php
    if ($arguments['navItem']['thumbnail']) {
        echo wp_get_attachment_image(
            $arguments['navItem']['thumbnail'],
            'thumbnail',
            false,
            ['class' => 'woocommerce-products-wizard-widget-step-thumbnail']
        );
    }
    ?>
    <span class="woocommerce-products-wizard-widget-step-name"><?php
        echo wp_kses_post($arguments['navItem']['title']);
        ?></span>
</li>
