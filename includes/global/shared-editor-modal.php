<?php
defined('ABSPATH') || exit;

$namespace = 'wcpw';
$sharedEditorSettings = apply_filters('wcpw_shared_editor_settings', []);
?>
<div id="<?php echo esc_attr($namespace); ?>-shared-editor-modal"
    class="<?php echo esc_attr($namespace); ?>-modal <?php echo esc_attr($namespace); ?>-shared-editor-modal"
    data-component="<?php echo esc_attr($namespace); ?>-modal"
    data-hash="<?php echo esc_attr($namespace); ?>-shared-editor-modal">
    <div class="<?php echo esc_attr($namespace); ?>-modal-dialog">
        <a href="#close"
            id="<?php echo esc_attr($namespace); ?>-shared-editor-close"
            title="<?php esc_attr_e('Close', 'products-wizard-lite-for-woocommerce'); ?>"
            class="<?php echo esc_attr($namespace); ?>-modal-close"
            data-component="<?php echo esc_attr($namespace); ?>-modal-close"
            data-hash="<?php echo esc_attr($namespace); ?>-shared-editor-modal">&times;</a>
        <div class="<?php echo esc_attr($namespace); ?>-modal-dialog-body"><?php
            wp_editor('', $namespace . '-shared-editor', $sharedEditorSettings);
            ?></div>
        <div class="<?php echo esc_attr($namespace); ?>-modal-dialog-footer">
            <a href="#close"
                id="<?php echo esc_attr($namespace); ?>-shared-editor-save"
                class="button button-primary"
                data-component="<?php echo esc_attr($namespace); ?>-modal-close"
                data-hash="<?php echo esc_attr($namespace); ?>-shared-editor-modal"><?php
                esc_html_e('Update', 'products-wizard-lite-for-woocommerce');
                ?></a>
        </div>
    </div>
</div>
