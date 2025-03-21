<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;
$stepId = isset($stepId) ? $stepId : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Entities\WizardStep;
use WCProductsWizard\Template;
use WCProductsWizard\Utils;

$thumbnailLink = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
$arguments = Template::getHTMLArgs(
    [
        'class' => 'woocommerce-products-wizard-form-item',
        'enableThumbnailLink' => true,
        'thumbnailSize' => WizardStep::getSetting($id, $stepId, 'item_thumbnail_size'),
        'thumbnailLink' => isset($thumbnailLink[0]) ? $thumbnailLink[0] : '',
        'thumbnailAttributes' => ['data-component' => 'wcpw-product-thumbnail-image'],
        'showThumbnails' => WizardStep::getSetting($id, $stepId, 'show_item_thumbnails'),
        'product' => null
    ],
    ['recursive' => true]
);

$product = $arguments['product'];

if (!$product instanceof WC_Product || !$arguments['showThumbnails']) {
    return;
}

if (is_string($arguments['thumbnailSize']) && strpos($arguments['thumbnailSize'], ',') !== false) {
    $arguments['thumbnailSize'] = explode(',', $arguments['thumbnailSize']);
}

$dimensions = wc_get_image_size($arguments['thumbnailSize']);
$placeholderAttributes = [
    'src' => wc_placeholder_img_src(),
    'alt' => esc_html__('Placeholder', 'products-wizard-lite-for-woocommerce'),
    'width' => $dimensions['width'],
    'height' => $dimensions['height']
];

$placeholderAttributes = array_replace($placeholderAttributes, $arguments['thumbnailAttributes']);
$placeholder = '<img ' . Utils::attributesArrayToString($placeholderAttributes) . '/>';
?>
<figure class="<?php echo esc_attr($arguments['class']); ?>-thumbnail thumbnail img-thumbnail"
    data-component="wcpw-product-thumbnail"><?php
    Template::html('form/item/prototype/tags', $arguments);

    if ($arguments['thumbnailLink'] && $arguments['enableThumbnailLink']) {
        echo '<a href="' . esc_attr($arguments['thumbnailLink']) . '"
            class="' . esc_attr($arguments['class']) . '-thumbnail-link"
            title="' . esc_attr(get_the_title(get_post_thumbnail_id())) . '"
            data-component="wcpw-product-thumbnail-link"
            data-rel="prettyPhoto[product-gallery-' . esc_attr($product->get_id()) . ']"
            rel="lightbox[' . esc_attr($product->get_id()) . ']">';
    }

    echo wp_kses_post($product->get_image($arguments['thumbnailSize'], $arguments['thumbnailAttributes']));

    if ($arguments['thumbnailLink'] && $arguments['enableThumbnailLink']) {
        echo '</a>';
    }
    ?></figure>
