<?php
defined('ABSPATH') || exit;

$arguments = isset($arguments) ? $arguments : [];
$field = isset($field) ? $field : [];
$hash = crc32(serialize($field));
$items = isset($arguments['values'][$field['key']]) ? $arguments['values'][$field['key']] : $field['default'];
$items = empty($items) ? [[]] : $items;
$isSingleValue = count($field['values']) <= 1;
$namespace = 'wcpw';
$field['showHeader'] = !empty($field['inModal']) ? false : (isset($field['showHeader']) ? $field['showHeader'] : true);

$itemTemplate = function ($field, $index) use ($arguments, $namespace, $hash, $isSingleValue) {
    $values = [];

    // get actual values from args
    if (isset($arguments['values'][$field['key']][$index])) {
        if (!$isSingleValue) {
            $values = $arguments['values'][$field['key']][$index];
        } else {
            $values = [$field['key'] => $arguments['values'][$field['key']][$index]];
        }
    }
    ?>
    <tr class="<?php echo esc_attr($namespace); ?>-data-table-item"
        data-component="<?php echo esc_attr($namespace); ?>-data-table-item"
        data-hash="<?php echo esc_attr($hash); ?>">
        <td class="<?php echo esc_attr($namespace); ?>-data-table-item-controls">
            <span class="button" role="button"
                data-component="<?php echo esc_attr($namespace); ?>-data-table-item-add"
                data-hash="<?php echo esc_attr($hash); ?>">+</span>
        </td>
        <?php
        if (!empty($field['inModal'])) {
            $id = "$namespace-data-table-modal-$hash-" . wp_rand();
            ?>
            <td>
                <a href="<?php echo esc_attr("#$id"); ?>"
                    class="button <?php echo esc_attr($namespace); ?>-data-table-item-open-modal"
                    data-component="<?php echo esc_attr($namespace); ?>-data-table-item-open-modal <?php echo esc_attr($namespace); ?>-open-modal"
                    data-hash="<?php echo esc_attr($hash); ?>"><?php esc_html_e('Settings', 'products-wizard-lite-for-woocommerce'); ?></a>
                <div id="<?php echo esc_attr($id); ?>"
                    class="<?php echo esc_attr($namespace); ?>-modal"
                    data-component="<?php echo esc_attr("$namespace-data-table-item-modal $namespace-modal"); ?>"
                    data-hash="<?php echo esc_attr($hash); ?>">
                    <div class="<?php echo esc_attr($namespace); ?>-modal-dialog">
                        <a href="#close" title="<?php esc_attr_e('Close', 'products-wizard-lite-for-woocommerce'); ?>"
                            class="<?php echo esc_attr($namespace); ?>-modal-close"
                            data-component="<?php echo esc_attr("$namespace-data-table-item-modal-close $namespace-modal-close"); ?>"
                            data-hash="<?php echo esc_attr($hash); ?>">&times;</a>
                        <div class="<?php echo esc_attr($namespace); ?>-modal-dialog-body">
                            <table class="<?php echo esc_attr($namespace); ?>-settings-table form-table">
                                <?php
                                foreach ($field['values'] as $fieldKey => $fieldChild) {
                                    $namePattern = "{$arguments['name']}[$index]" . (!$isSingleValue ? '[%key%]' : '');

                                    do_action(
                                        $namespace . '_output_settings_table_row',
                                        $fieldKey,
                                        $fieldChild,
                                        [
                                            'values' => $values,
                                            'namePattern' => $namePattern,
                                            'idPattern' => $namePattern,
                                            'rowAttributes' => [
                                                'data-component' => "$namespace-setting $namespace-data-table-body-item",
                                                'data-hash' => $hash
                                            ]
                                        ]
                                    );
                                }
                                ?>
                            </table>
                        </div>
                        <div class="<?php echo esc_attr($namespace); ?>-modal-dialog-footer">
                            <button class="button <?php echo esc_attr("$namespace-data-table-item-modal-switch"); ?>"
                                data-component="<?php echo esc_attr("$namespace-data-table-item-modal-close $namespace-modal-close $namespace-data-table-item-modal-switch"); ?>"
                                data-direction="prev"
                                data-hash="<?php echo esc_attr($hash); ?>"><?php esc_html_e('Prev', 'products-wizard-lite-for-woocommerce'); ?></button>
                            <a href="#close" class="button button-primary"
                                data-component="<?php echo esc_attr("$namespace-data-table-item-modal-close $namespace-modal-close"); ?>"
                                data-hash="<?php echo esc_attr($hash); ?>"><?php esc_html_e('Save', 'products-wizard-lite-for-woocommerce'); ?></a>
                            <button class="button <?php echo esc_attr("$namespace-data-table-item-modal-switch"); ?>"
                                data-component="<?php echo esc_attr("$namespace-data-table-item-modal-close $namespace-modal-close $namespace-data-table-item-modal-switch"); ?>"
                                data-direction="next"
                                data-hash="<?php echo esc_attr($hash); ?>"><?php esc_html_e('Next', 'products-wizard-lite-for-woocommerce'); ?></button>
                        </div>
                    </div>
                </div>
            </td>
            <?php
        } else {
            foreach ($field['values'] as $fieldChild) {
                ?>
                <td data-component="<?php echo esc_attr($namespace); ?>-data-table-body-item"
                    data-key="<?php echo esc_attr($fieldChild['key']); ?>"<?php
                    echo !empty($fieldChild['width'])
                        ? ' width="' . esc_attr($fieldChild['width']) . '"'
                        : ''
                    ?>><?php
                    $namePattern = "{$arguments['name']}[$index]" . (!$isSingleValue ? '[%key%]' : '');

                    do_action(
                        $namespace . '_output_setting_field',
                        $fieldChild,
                        [
                            'values' => $values,
                            'namePattern' => $namePattern,
                            'idPattern' => $namePattern
                        ]
                    );
                    ?></td>
                <?php
            }
        }
        ?>
        <td width="10">
            <span role="button" class="button <?php echo esc_attr($namespace); ?>-data-table-item-clone"
                data-component="<?php echo esc_attr($namespace); ?>-data-table-item-clone"
                data-hash="<?php echo esc_attr($hash); ?>"><?php esc_html_e('Copy', 'products-wizard-lite-for-woocommerce'); ?></span>
        </td>
        <td class="<?php echo esc_attr($namespace); ?>-data-table-item-controls">
            <span class="button" role="button"
                data-component="<?php echo esc_attr($namespace); ?>-data-table-item-remove"
                data-hash="<?php echo esc_attr($hash); ?>">-</span>
        </td>
    </tr>
    <?php
};
?>
<div class="<?php echo esc_attr($namespace); ?>-data-table"
    data-component="<?php echo esc_attr($namespace); ?>-data-table"
    data-hash="<?php echo esc_attr($hash); ?>"
    data-key="<?php echo esc_attr(str_replace('%key%', $field['key'], $arguments['namePattern'])); ?>">
    <table class="<?php echo esc_attr($namespace); ?>-data-table-main wp-list-table widefat striped"
        data-component="<?php echo esc_attr($namespace); ?>-data-table-main">
        <?php if (!empty($field['showHeader'])) { ?>
            <thead>
                <tr>
                    <td><span class="screen-reader-text"><?php esc_html_e('Add', 'products-wizard-lite-for-woocommerce'); ?></span></td>
                    <?php foreach ($field['values'] as $fieldChild) { ?>
                        <td data-component="<?php echo esc_attr($namespace); ?>-data-table-header-cell"
                            data-key="<?php echo esc_attr($fieldChild['key']); ?>"><?php
                            echo wp_kses_post($fieldChild['label']);
                            ?></td>
                    <?php } ?>
                    <td><span class="screen-reader-text"><?php esc_html_e('Copy', 'products-wizard-lite-for-woocommerce'); ?></span></td>
                    <td><span class="screen-reader-text"><?php esc_html_e('Remove', 'products-wizard-lite-for-woocommerce'); ?></span></td>
                </tr>
            </thead>
        <?php } ?>
        <tbody><?php
            foreach ($items as $index => $_) {
                $itemTemplate($field, $index);
            }
            ?></tbody>
    </table>
    <template hidden data-component="<?php echo esc_attr($namespace); ?>-data-table-template"><?php
        $itemTemplate($field, -1);
        ?></template>
</div>
