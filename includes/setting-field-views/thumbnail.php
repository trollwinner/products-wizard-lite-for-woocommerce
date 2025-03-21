<?php
defined('ABSPATH') || exit;

$arguments = isset($arguments) ? $arguments : [];
$namespace = 'wcpw';
?>
<div class="<?php echo esc_attr($namespace); ?>-thumbnail"
    data-component="<?php echo esc_attr($namespace); ?>-thumbnail">
    <div class="<?php echo esc_attr($namespace); ?>-thumbnail-image"
        data-component="<?php echo esc_attr($namespace); ?>-thumbnail-image">
        <?php
        if ($arguments['value']) {
            echo wp_kses_post(wp_get_attachment_image($arguments['value']));
        }
        ?>
    </div>
    <input data-component="<?php echo esc_attr($namespace); ?>-thumbnail-id" type="hidden"
        name="<?php echo esc_attr($arguments['name']); ?>" value="<?php echo esc_attr($arguments['value']); ?>">
    <p class="hide-if-no-js">
        <?php esc_html_e('Image', 'products-wizard-lite-for-woocommerce'); ?>:
        <a href="#" data-component="<?php echo esc_attr($namespace); ?>-thumbnail-set" role="button"><?php
            esc_html_e('Change', 'products-wizard-lite-for-woocommerce');
            ?></a>
        /
        <a href="#" data-component="<?php echo esc_attr($namespace); ?>-thumbnail-remove" role="button"><?php
            esc_html_e('Remove', 'products-wizard-lite-for-woocommerce');
            ?></a>
    </p>
</div>
