<?php
defined('ABSPATH') || exit;

$arguments = isset($arguments) ? $arguments : [];
$field = isset($field) ? $field : [];
$items = isset($arguments['values'][$field['key']]) ? $arguments['values'][$field['key']] : $field['default'];
$items = empty($items) ? [[]] : $items;
$namespace = 'wcpw';
?>
<table class="<?php echo esc_attr($namespace); ?>-group widefat"
    data-component="<?php echo esc_attr($namespace); ?>-group-item">
    <?php if (!empty($field['showHeader'])) { ?>
        <thead>
            <tr>
                <?php foreach ($field['values'] as $fieldChild) { ?>
                    <td class="<?php echo esc_attr($fieldChild['key'] . ' ' . $namespace); ?>-group-item"
                        data-component="<?php echo esc_attr($namespace); ?>-group-head"
                        data-key="<?php echo esc_attr($fieldChild['key']); ?>"
                        <?php
                        echo !empty($fieldChild['width'])
                            ? ' width="' . esc_attr($fieldChild['width']) . '"'
                            : ''
                        ?>><?php echo wp_kses_post($fieldChild['label']); ?></td>
                <?php } ?>
            </tr>
        </thead>
    <?php } ?>
    <tbody>
        <tr>
            <?php foreach ($field['values'] as $fieldChild) { ?>
                <td class="<?php echo esc_attr($fieldChild['key'] . ' ' . $namespace); ?>-group-item"
                    data-component="<?php echo esc_attr($namespace); ?>-group-item"
                    data-key="<?php echo esc_attr($fieldChild['key']); ?>">
                    <?php
                    $namePattern = "{$arguments['name']}[%key%]";

                    do_action(
                        $namespace . '_output_setting_field',
                        $fieldChild,
                        [
                            'values' => $items,
                            'idPattern' => $namePattern,
                            'namePattern' => $namePattern
                        ]
                    );
                    ?>
                </td>
            <?php } ?>
        </tr>
    </tbody>
</table>
