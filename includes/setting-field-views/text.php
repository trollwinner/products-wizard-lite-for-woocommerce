<?php
defined('ABSPATH') || exit;

$defaults = [
    'name' => null,
    'value' => null
];

$arguments = isset($arguments) ? array_replace($defaults, $arguments) : $defaults;
$field = isset($field) ? $field : [];
?>
<input type="<?php echo !empty($field['type']) ? esc_attr($field['type']) : 'text'; ?>"
    name="<?php echo esc_attr($arguments['name']); ?>"
    value="<?php echo esc_attr($arguments['value']); ?>"<?php
    if (!empty($field['HTMLAttributes'])) {
        $field['HTMLAttributes'] = array_filter($field['HTMLAttributes'], function ($value) {
            return !is_null($value);
        });

        if (!empty($field['datalist']) && !empty($field['HTMLAttributes']['id'])) {
            $field['HTMLAttributes']['list'] = $field['HTMLAttributes']['id'] . '-datalist';
        }

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
<?php
if (!empty($field['datalist']) && !empty($field['HTMLAttributes']['id'])) {
    echo '<datalist id="' . esc_attr($field['HTMLAttributes']['id']) . '-datalist">';

    foreach ($field['datalist'] as $item) {
        echo '<option value="' . esc_attr($item) . '"></option>';
    }

    echo '</datalist>';
}
?>
