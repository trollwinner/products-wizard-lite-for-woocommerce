<?php
defined('ABSPATH') || exit;

$defaults = [
    'name' => null,
    'value' => null
];

$arguments = isset($arguments) ? array_replace($defaults, $arguments) : $defaults;
$field = isset($field) ? $field : [];
?>
<input type="hidden" value="0" name="<?php echo esc_attr($arguments['name']); ?>">
<input type="checkbox" value="1" name="<?php echo esc_attr($arguments['name']); ?>"<?php
    checked(filter_var($arguments['value'], FILTER_VALIDATE_BOOLEAN));

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

                    return $k . "='" . esc_attr(is_bool($v) ? wp_json_encode($v) : $v) . "'";
                },
                array_keys($field['HTMLAttributes']),
                $field['HTMLAttributes']
            )
        ));
    }
    ?>>
