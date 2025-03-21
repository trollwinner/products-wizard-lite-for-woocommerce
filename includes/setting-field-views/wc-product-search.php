<?php
defined('ABSPATH') || exit;

global $woocommerce;

if (!$woocommerce) {
    return;
}

$arguments = isset($arguments) ? $arguments : [];
$field = isset($field) ? $field : [];
$default = [
    'action' => 'woocommerce_json_search_products_and_variations',
    'limit' => 30,
    'include' => '',
    'allowClear' => true,
    'multiple' => true,
    'minimumInputLength' => 3,
    'placeholder' => esc_html__('Search for a product&hellip;', 'products-wizard-lite-for-woocommerce')
];

$defaultQueryArgs = [
    'post_type' => ['product', 'product_variation'],
    'post_status' => ['publish', 'future', 'private'],
    'include' => $arguments['value']
];

$queryArgs = isset($field['queryArgs'])
    ? array_replace($defaultQueryArgs, $field['queryArgs'])
    : $defaultQueryArgs;

$inputAttributes = array_replace($default, $field);
$isMultiply = filter_var($inputAttributes['multiple'], FILTER_VALIDATE_BOOLEAN);
$values = [];

if (!empty($arguments['value'])) {
    foreach (get_posts($queryArgs) as $product) {
        $values[$product->ID] = rawurldecode($product->post_title . ' (#' . $product->ID . ')');
    }
}
?>
<input type="hidden" value="" name="<?php echo esc_attr($arguments['name']); ?>">
<select class="wc-product-search" name="<?php echo esc_attr($arguments['name']) . ($isMultiply ? '[]' : ''); ?>"
    <?php
    echo $isMultiply ? 'multiple ' : '';

    if (!empty($field['HTMLAttributes'])) {
        // remove null elements - use only strings and numbers
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
    ?>
    data-placeholder="<?php echo esc_attr($inputAttributes['placeholder']); ?>"
    data-multiple="<?php echo wp_json_encode($isMultiply); ?>"
    data-action="<?php echo esc_attr($inputAttributes['action']); ?>"
    data-allow_clear="<?php echo esc_attr($inputAttributes['allowClear']); ?>"
    data-limit="<?php echo esc_attr($inputAttributes['limit']); ?>"
    data-include="<?php echo esc_attr($inputAttributes['include']); ?>"
    data-minimum_input_length="<?php echo esc_attr($inputAttributes['minimumInputLength']); ?>">
    <?php
    foreach ($values as $key => $item) {
        echo '<option value="' . esc_attr($key) . '" ' . selected(true, true, false) . '>'
            . esc_html($item) . '</option>';
    }
    ?>
</select>
