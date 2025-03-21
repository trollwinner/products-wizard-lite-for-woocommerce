<?php
defined('ABSPATH') || exit;

$defaults = [
    'editorSettings' => [
        'wpautop' => 1,
        'media_buttons' => 1,
        'textarea_rows' => 10,
        'tabindex' => null,
        'editor_css' => '',
        'editor_class' => '',
        'teeny' => 0,
        'dfw' => 0,
        'tinymce' => 1,
        'quicktags' => 1,
        'drag_drop_upload' => true
    ]
];

$arguments = isset($arguments) ? array_replace(['value' => ''], $arguments) : $defaults;
$field = isset($field) ? array_replace_recursive($defaults, $field) : $defaults;
$field['editorSettings']['textarea_name'] = $arguments['name'];
$namespace = 'wcpw';

if (!empty($field['inModal'])) {
    ?>
    <button class="button" data-component="<?php echo esc_attr($namespace); ?>-shared-editor-open"><?php
        esc_html_e('Edit', 'products-wizard-lite-for-woocommerce');
        ?></button>
    <input type="hidden" value="<?php echo esc_attr($arguments['value']); ?>"
        name="<?php echo esc_attr($arguments['name']); ?>"
        data-component="<?php echo esc_attr($namespace); ?>-shared-editor-target">
    <?php
    return;
}

wp_editor($arguments['value'], $field['key'], $field['editorSettings']);
