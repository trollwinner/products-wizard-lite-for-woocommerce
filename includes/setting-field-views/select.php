<?php
defined('ABSPATH') || exit;

$arguments = isset($arguments) ? $arguments : [];
$field = isset($field) ? $field : [];
$isMultiply = !empty($field['multiple']) ? filter_var($field['multiple'], FILTER_VALIDATE_BOOLEAN) : false;
?>
<select name="<?php echo esc_attr($arguments['name']) . ($isMultiply ? '[]' : ''); ?>"
    <?php
    echo $isMultiply ? ' multiple ' : '';

    if (!empty($field['HTMLAttributes'])) {
        $field['HTMLAttributes'] = array_filter($field['HTMLAttributes'], function ($value) {
            return !is_null($value);
        });

        echo wp_kses_post(implode(
            ' ',
            array_map(
                function ($k, $v) {
                    if (is_integer($k)) {
                        return htmlspecialchars($v);
                    }

                    return $k . "='" . (is_bool($v) ? wp_json_encode($v) : $v) . "'";
                },
                array_keys($field['HTMLAttributes']),
                $field['HTMLAttributes']
            )
        ));
    }
    ?>>
    <?php foreach ($field['values'] as $key => $name) { ?>
        <option value="<?php echo esc_attr($key); ?>"
        <?php
        selected(
            (is_array($arguments['value']) && in_array($key, $arguments['value']))
            || ((string) $arguments['value'] != '' && (string) $key == (string) $arguments['value'])
        );
        ?>><?php echo esc_html($name); ?></option>
    <?php } ?>
</select>
