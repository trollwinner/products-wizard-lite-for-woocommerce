<?php
namespace WCProductsWizard\Entities;

use WC_Product_Variable;
use WC_Product_Variation;
use WCProductsWizard\DataBase;

/**
 * Product Variation Class
 *
 * @class ProductVariation
 * @version 2.0.1
 */
class ProductVariation implements Interfaces\PostType
{
    use Traits\PostType;

    //<editor-fold desc="Properties">
    /**
     * Post type string
     * @var string
     */
    public static $postType = 'product_variation';

    /**
     * Namespace string
     * @var string
     */
    public static $namespace = 'wcpw_product_variation';
    //</editor-fold>

    //<editor-fold desc="Core">
    /** Class Constructor */
    public function __construct()
    {
        $this->initSettings();

        if (is_admin()) {
            $this->initAdmin();
        }
    }
    //</editor-fold>

    //<editor-fold desc="API">
    /**
     * Get, filter and return available product attributes and variables
     *
     * @param array $arguments
     *
     * @return array
     */
    public static function getArguments($arguments)
    {
        $defaults = [
            'id' => null,
            'stepId' => null,
            'product' => false,
            'cart' => [],
            'defaultAttributes' => []
        ];

        $arguments = array_replace($defaults, $arguments);
        $product = $arguments['product'];
        $output = [
            'variations' => [],
            'attributes' => []
        ];

        if (!($product instanceof WC_Product_Variable || $product instanceof WC_Product_Variation)) {
            return apply_filters('wcpw_variation_arguments', $output, $arguments);
        }

        $defaultSelectedVariations = null;
        $productId = $product->get_id();
        $variations = method_exists($product, 'get_available_variations') ? $product->get_available_variations() : [];
        $attributes = $product->get_variation_attributes(false);
        $cartProduct = null;
        $attributesToRemove = [];
        $attributesToSave = [];
        $attributesOutput = [];

        if (empty($variations) && $product instanceof WC_Product_Variation) {
            $parent = wc_get_product($product->get_parent_id());

            // find product variation parent
            if ($parent instanceof WC_Product_Variable) {
                $parentVariations = $parent->get_available_variations();
                $parentAttributes = $parent->get_variation_attributes();

                // find variation data from parent variations
                foreach ($parentVariations as $parentVariation) {
                    if ($parentVariation['variation_id'] == $productId) {
                        $variations = [$parentVariation];
                    }
                }
            }
        }

        foreach ($variations as $key => &$variation) {
            if (!apply_filters('wcpw_variation_available', true, $variation, $arguments)) {
                // save attributes to remove
                foreach ($variation['attributes'] as $attributeItemKey => $attributeItemValue) {
                    $attributesToRemove[$attributeItemKey][] = $attributeItemValue;
                }

                // remove the unmet variation at all
                unset($variations[$key]);

                continue;
            }

            // collect attributes to save
            foreach ($variation['attributes'] as $attributeItemKey => $attributeItemValue) {
                $attributesToSave[$attributeItemKey][] = $attributeItemValue;
            }

            // change image size
            if (!empty($variation['image_src'])) {
                $src = wp_get_attachment_image_src(get_post_thumbnail_id($variation['variation_id']), 'shop_catalog');
                $variation['image_src'] = $src[0];
            } elseif (!empty($variation['image']['src'])) {
                $src = wp_get_attachment_image_src(get_post_thumbnail_id($variation['variation_id']), 'shop_catalog');
                $variation['image']['src'] = is_array($src) && isset($src[0]) ? $src[0] : '';
            }

            // need to show the price for all variations because of the possible discounts
            $_variation = wc_get_product($variation['variation_id']);
            $variation['price_html'] = '<span class="price">' . $_variation->get_price_html() . '</span>';
        }

        unset($variation);
        unset($_variation);

        // clean attributes to remove from attributes to save
        foreach ($attributesToSave as $attributeKey => $attributeValue) {
            if (!isset($attributesToRemove[$attributeKey])) {
                continue;
            }

            $attributesToRemove[$attributeKey] = array_diff($attributesToRemove[$attributeKey], $attributeValue);
        }

        // find and remove unmet product attributes
        foreach ($attributesToRemove as $attributeToRemoveItemKey => $attributeToRemoveItemValue) {
            foreach ($attributes as $attributeKey => $attributeValue) {
                if (urldecode(str_replace('attribute_', '', $attributeToRemoveItemKey))
                    != mb_strtolower(str_replace(' ', '-', $attributeKey))
                ) {
                    continue;
                }

                foreach ($attributeToRemoveItemValue as $attributeToRemoveItemValueItem) {
                    foreach ($attributeValue as $attributeItemValueItemKey => $attributeItemValueItemValue) {
                        if (urldecode($attributeToRemoveItemValueItem) != urldecode($attributeItemValueItemValue)) {
                            continue;
                        }

                        // unset product attribute
                        unset($attributes[$attributeKey][$attributeItemValueItemKey]);
                    }
                }
            }
        }

        // find this product in the cart
        foreach ($arguments['cart'] as $cartItem) {
            if (isset($cartItem['product_id'], $cartItem['step_id'])
                && $productId == $cartItem['product_id'] && $cartItem['step_id'] == $arguments['stepId']
            ) {
                $cartProduct = $cartItem;

                break;
            }
        }

        // get pure attributes array
        foreach ($attributes as $attributeKey => $attributeValue) {
            $selectedAttribute = '';
            $key = strtolower(sanitize_title($attributeKey));

            // set active product if have it in the cart
            if (isset($_REQUEST['attribute_' . $key])) { // phpcs:disable WordPress.Security.NonceVerification.Recommended
                $selectedAttribute = sanitize_key(wp_unslash($_REQUEST['attribute_' . $key])); // phpcs:disable WordPress.Security.NonceVerification.Recommended
            } elseif ($cartProduct && isset($cartProduct['variation']['attribute_' . $key])) {
                $selectedAttribute = $cartProduct['variation']['attribute_' . $key];
            } elseif ($defaultSelectedVariations
                && isset($defaultSelectedVariations['attributes']['attribute_' . $key])
            ) {
                $selectedAttribute = $defaultSelectedVariations['attributes']['attribute_' . $key];
            } elseif (isset($arguments['defaultAttributes'][$key])) {
                $selectedAttribute = $arguments['defaultAttributes'][$key];
            }

            // Get terms if this is a taxonomy - ordered
            if (taxonomy_exists($attributeKey)) {
                switch (wc_attribute_orderby($attributeKey)) {
                    case 'name': {
                        $args = [
                            'taxonomy' => $attributeKey,
                            'orderby' => 'name',
                            'hide_empty' => false,
                            'menu_order' => false
                        ];

                        break;
                    }

                    case 'id': {
                        $args = [
                            'taxonomy' => $attributeKey,
                            'orderby' => 'id',
                            'order' => 'ASC',
                            'menu_order' => false,
                            'hide_empty' => false
                        ];

                        break;
                    }

                    case 'menu_order':
                        $args = ['taxonomy' => $attributeKey, 'menu_order' => 'ASC', 'hide_empty' => false];
                        break;

                    default:
                        $args = ['taxonomy' => $attributeKey];
                }

                // take all possible attributes instead of the empty (not defined) one
                if ($product instanceof WC_Product_Variation && empty($attributeValue)
                    && isset($parentAttributes[$attributeKey])
                ) {
                    $attributeValue = $parentAttributes[$attributeKey];
                }

                foreach (DataBase\Term::getCollection($args) as $term) {
                    if (!in_array($term->slug, (array) $attributeValue)) {
                        continue;
                    }

                    if (!$selectedAttribute
                        && (!isset($attributesOutput[$attributeKey]) || count($attributesOutput[$attributeKey]) == 0)
                    ) {
                        $selected = true;
                    } else {
                        $selected = strtolower(sanitize_title($selectedAttribute))
                            == strtolower(sanitize_title($term->slug));
                    }

                    $attributesOutput[$attributeKey][] = [
                        'id' => $term->term_id,
                        'name' => $term->name,
                        'value' => $term->slug,
                        'selected' => $selected
                    ];
                }
            } elseif (is_array($attributeValue) && !empty($attributeValue)) {
                foreach ($attributeValue as $option) {
                    if (!$selectedAttribute
                        && (!isset($attributesOutput[$attributeKey]) || count($attributesOutput[$attributeKey]) == 0)
                    ) {
                        $selected = true;
                    } else {
                        $selected = strtolower(sanitize_title($selectedAttribute))
                            == strtolower(sanitize_title($option));
                    }

                    $attributesOutput[$attributeKey][] = [
                        'name' => $option,
                        'value' => $option,
                        'selected' => $selected
                    ];
                }
            }
        }

        $output = [
            'variations' => $variations,
            'attributes' => $attributesOutput
        ];

        return apply_filters('wcpw_variation_arguments', $output, $arguments);
    }
    //</editor-fold>
}
