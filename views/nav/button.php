<?php
defined('ABSPATH') || exit;

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'class' => '',
    'state' => '',
    'action' => '',
    'id' => '',
    'title' => '',
    'subtitle' => '',
    'formId' => null,
    'toggleMobileNavOn' => 'sm',
    'navButtonClass' => 'btn-default btn-light'
]);
?>
<button type="submit" role="tab"
    form="<?php echo esc_attr($arguments['formId']); ?>"
    name="<?php echo esc_attr($arguments['action']); ?>"
    value="<?php echo esc_attr($arguments['value']); ?>"
    class="woocommerce-products-wizard-nav-button btn btn-block <?php
    echo esc_attr(implode(
        ' ',
        [
            $arguments['class'],
            $arguments['navButtonClass'],
            "d-{$arguments['toggleMobileNavOn']}-none"
        ]
    ));
    ?>"
    data-component="wcpw-nav-item"
    data-nav-action="<?php echo esc_attr($arguments['action']); ?>"
    data-nav-id="<?php echo esc_attr($arguments['value']); ?>"<?php
    disabled($arguments['state'], 'disabled');
    ?>><?php
    echo $arguments['thumbnail']
        ? wp_get_attachment_image(
            $arguments['thumbnail'],
            'thumbnail',
            false,
            ['class' => 'woocommerce-products-wizard-nav-button-thumbnail']
        ) . ' '
        : '';
    ?><span class="woocommerce-products-wizard-nav-button-text">
        <span class="woocommerce-products-wizard-nav-button-title"><?php
            echo wp_kses_post($arguments['title']);
            ?></span>
        <span class="woocommerce-products-wizard-nav-button-sub-title"><?php
            echo wp_kses_post($arguments['subtitle']);
            ?></span>
    </span></button>
