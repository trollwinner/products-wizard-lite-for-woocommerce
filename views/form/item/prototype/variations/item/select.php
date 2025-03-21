<?php
defined('ABSPATH') || exit;

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'class' => 'woocommerce-products-wizard-form-item',
    'formId' => null,
    'addToCartKey' => '',
    'attributeKey' => null,
    'attributeValues' => []
]);

$fieldName = "productsToAdd[{$arguments['addToCartKey']}][variation][attribute_"
    . sanitize_title($arguments['attributeKey']) . ']';
?>
<div class="<?php echo esc_attr($arguments['class']); ?>-variations-item form-group is-select<?php
    echo ' has-values-count-' . count($arguments['attributeValues']);
    ?>">
    <dt class="<?php echo esc_attr($arguments['class']); ?>-variations-item-name-wrapper">
        <label class="<?php echo esc_attr($arguments['class']); ?>-variations-item-name form-label"
            for="<?php echo esc_attr($fieldName); ?>"><?php
            echo wp_kses_post(wc_attribute_label($arguments['attributeKey']));
            ?></label>
    </dt>
    <dd class="<?php echo esc_attr($arguments['class']); ?>-variations-item-value-wrapper">
        <select name="<?php echo esc_attr($fieldName); ?>"
            id="<?php echo esc_attr($fieldName); ?>"
            class="<?php echo esc_attr($arguments['class']); ?>-variations-item-value form-select form-control is-select"
            form="<?php echo esc_attr($arguments['formId']); ?>"
            data-name="attribute_<?php echo esc_attr(sanitize_title($arguments['attributeKey'])); ?>"
            data-component="wcpw-product-variations-item wcpw-product-variations-item-input">
            <?php foreach ($arguments['attributeValues'] as $attributeValue) { ?>
                <option value="<?php echo esc_attr($attributeValue['value']); ?>"
                    data-component="wcpw-product-variations-item-value"<?php
                    selected($attributeValue['selected']);
                    ?>><?php echo esc_html($attributeValue['name']); ?></option>
            <?php } ?>
        </select>
    </dd>
</div>
