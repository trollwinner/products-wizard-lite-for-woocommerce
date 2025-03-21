<?php
defined('ABSPATH') || exit;

$namespace = 'wcpw';
$defaults = [
    'name' => "$namespace-items-selected",
    'values' => []
];

$arguments = isset($arguments) ? array_replace_recursive($defaults, $arguments) : $defaults;
$field = isset($field) ? $field : [];
$size = isset($field['size']) ? $field['size'] : 10;
$id = md5(serialize($arguments));
$arguments['values'] = (array) $arguments['values'];

if (!isset($arguments['values'][$field['key']])) {
    // should exist
    $arguments['values'][$field['key']] = isset($field['default']) ? (array) $field['default'] : [];
} elseif (!is_array($arguments['values'][$field['key']])) {
    // should be an array
    $arguments['values'][$field['key']] = [$arguments['values'][$field['key']]];
}

// should contain strings only
$arguments['values'][$field['key']] = array_map(function ($item) {
    return is_string($item) || is_numeric($item) ? $item : '';
}, $arguments['values'][$field['key']]);

$valuesSortedBySelected = array_flip((array) $arguments['values'][$field['key']]);
$valuesSortedBySelected = array_filter(array_replace($valuesSortedBySelected, (array) $field['values']));
?>
<table class="form-table <?php echo esc_attr($namespace); ?>-multi-select"
    data-component="<?php echo esc_attr($namespace); ?>-multi-select">
    <tr>
        <td>
            <label for="<?php echo esc_attr("$namespace-multi-select-items-available-$id"); ?>"><?php
                esc_html_e('All', 'products-wizard-lite-for-woocommerce');
                ?></label>
            <p>
                <label>
                    <span class="screen-reader-text"><?php esc_html_e('Filter', 'products-wizard-lite-for-woocommerce'); ?></span>
                    <input type="text" placeholder="<?php esc_attr_e('Filter', 'products-wizard-lite-for-woocommerce'); ?>"
                        data-component="<?php echo esc_attr($namespace); ?>-multi-select-items-available-filter">
                </label>
            </p>
            <p>
                <select name="<?php echo esc_attr($namespace); ?>-multi-select-items-available"
                    id="<?php echo esc_attr("$namespace-multi-select-items-available-$id"); ?>"
                    data-component="<?php echo esc_attr($namespace); ?>-multi-select-items-available"
                    size="<?php echo esc_attr($size); ?>" multiple>
                    <?php
                    foreach ((array) $field['values'] as $key => $name) {
                        if (in_array($key, $arguments['values'][$field['key']])) {
                            continue;
                        }
                        ?>
                        <option value="<?php echo esc_attr($key); ?>"
                            title="<?php echo esc_attr($name); ?>"><?php echo esc_html($name); ?></option>
                        <?php
                    }
                    ?>
                </select>
            </p>
        </td>
        <td width="35">
            <button class="button" data-component="<?php echo esc_attr($namespace); ?>-multi-select-add">&#9658;</button>
            <br><br>
            <button class="button" data-component="<?php echo esc_attr($namespace); ?>-multi-select-remove">&#9668;</button>
        </td>
        <td>
            <input type="hidden" name="<?php echo esc_attr($arguments['name']); ?>" value="">
            <label for="<?php echo esc_attr("$namespace-multi-select-items-selected-$id"); ?>"><?php
                esc_html_e('Enabled', 'products-wizard-lite-for-woocommerce');
                ?></label>
            <p>
                <label>
                    <span class="screen-reader-text"><?php esc_html_e('Filter', 'products-wizard-lite-for-woocommerce'); ?></span>
                    <input type="text" placeholder="<?php esc_attr_e('Filter', 'products-wizard-lite-for-woocommerce'); ?>"
                        data-component="<?php echo esc_attr($namespace); ?>-multi-select-items-selected-filter">
                </label>
            </p>
            <p>
                <select name="<?php echo esc_attr("$namespace-multi-select-items-selected-$id"); ?>"
                    id="<?php echo esc_attr("$namespace-multi-select-items-selected-$id"); ?>"
                    data-component="<?php echo esc_attr($namespace); ?>-multi-select-items-selected"
                    size="<?php echo esc_attr($size); ?>" multiple>
                    <?php
                    foreach ($arguments['values'][$field['key']] as $key) {
                        if (!is_array($field['values']) || !isset($field['values'][$key])) {
                            continue;
                        }
                        ?>
                        <option value="<?php echo esc_attr($key); ?>"
                            title="<?php echo esc_attr($field['values'][$key]); ?>"><?php
                            echo esc_html($field['values'][$key]);
                            ?></option>
                        <?php
                    }
                    ?>
                </select>
            </p>
            <div data-component="<?php echo esc_attr($namespace); ?>-multi-select-inputs">
                <?php foreach ($valuesSortedBySelected as $key => $value) { ?>
                    <input type="hidden" name="<?php echo esc_attr($arguments['name']); ?>[]"
                        value="<?php echo esc_attr($key); ?>"
                        <?php disabled(!in_array($key, $arguments['values'][$field['key']])); ?>>
                <?php } ?>
            </div>
        </td>
        <td width="35">
            <button class="button" data-component="<?php echo esc_attr($namespace); ?>-multi-select-move-up">&#9650;</button>
            <br><br>
            <button class="button" data-component="<?php echo esc_attr($namespace); ?>-multi-select-move-down">&#9660;</button>
        </td>
    </tr>
</table>
