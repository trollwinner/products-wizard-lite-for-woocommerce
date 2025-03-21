<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Form;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'navItems' => Form::getNavItems($id),
    'formId' => null,
    'class' => '',
    'itemClass' => '',
    'itemButtonClass' => ''
]);
?>
<ul class="woocommerce-products-wizard-nav-list <?php echo esc_attr($arguments['class']); ?>"
    data-component="wcpw-nav-list" role="tablist">
    <?php
    foreach ($arguments['navItems'] as $navItem) {
        ?>
        <li role="presentation"
            class="woocommerce-products-wizard-nav-list-item <?php
            echo esc_attr("{$navItem['class']} {$arguments['itemClass']}");
            ?>">
            <button type="submit" role="tab"
                form="<?php echo esc_attr($arguments['formId']); ?>"
                name="<?php echo esc_attr($navItem['action']); ?>"
                value="<?php echo esc_attr($navItem['value']); ?>"
                class="woocommerce-products-wizard-nav-list-item-button <?php
                echo esc_attr("{$navItem['class']} {$arguments['itemButtonClass']}");
                ?>"
                data-component="wcpw-nav-item"
                data-nav-action="<?php echo esc_attr($navItem['action']); ?>"
                data-nav-id="<?php echo esc_attr($navItem['value']); ?>"
                <?php disabled($navItem['state'], 'disabled'); ?>>
                <?php
                if ($navItem['thumbnail']) {
                    echo wp_get_attachment_image(
                        $navItem['thumbnail'],
                        'thumbnail',
                        false,
                        ['class' => 'woocommerce-products-wizard-nav-list-item-thumbnail']
                    );
                }
                ?>
                <span class="woocommerce-products-wizard-nav-list-item-text">
                    <span class="woocommerce-products-wizard-nav-list-item-title"><?php
                        echo wp_kses_post($navItem['title']);
                        ?></span>
                    <span class="woocommerce-products-wizard-nav-list-item-sub-title"><?php
                        echo wp_kses_post($navItem['subtitle']);
                        ?></span>
                </span>
            </button>
        </li>
        <?php
    }
    ?>
</ul>
