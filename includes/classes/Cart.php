<?php
namespace WCProductsWizard;

use WCProductsWizard\Entities\Product;
use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Entities\WizardStep;

/**
 * Cart Class
 *
 * @class Cart
 * @version 9.1.1
 */
class Cart
{
    // <editor-fold desc="Properties">
    /**
     * Cart content cache
     * @var array
     */
    protected static $cache = [];

    /**
     * Session key variable
     * @var string
     */
    public static $sessionKey = 'woocommerce-products-wizard-cart';

    /**
     * Array of items for a further work
     * @var array
     */
    public static $itemsBuffer = [];
    // </editor-fold>

    // <editor-fold desc="Core">
    /** Class Constructor */
    public function __construct()
    {
        // cart output filters
        add_filter('woocommerce_cart_item_remove_link', [$this, 'itemRemoveLinkFilter'], 10, 2);
        add_filter('woocommerce_cart_item_quantity', [$this, 'itemQuantityFilter'], 10, 3);
        add_filter('woocommerce_cart_item_class', [$this, 'itemClass'], 10, 2);
        add_filter('woocommerce_cart_item_price', [$this, 'itemPriceFilter'], 30, 2);
        add_filter('woocommerce_cart_item_subtotal', [$this, 'itemSubTotalFilter'], 30, 2);
        add_filter('woocommerce_cart_item_thumbnail', [$this, 'itemThumbnailFilter'], 10, 3);
        add_filter('woocommerce_cart_item_visible', [$this, 'itemVisibilityFilter'], 20, 2);
        add_filter('woocommerce_widget_cart_item_visible', [$this, 'itemVisibilityFilter'], 20, 2);
        add_filter('woocommerce_checkout_cart_item_visible', [$this, 'itemVisibilityFilter'], 20, 2);
        add_filter('woocommerce_get_item_data', [$this, 'itemDataFilter'], 10, 2);
        add_action('woocommerce_before_calculate_totals', [$this, 'beforeCalculateAction'], 20);

        // item quantity update
        add_action('woocommerce_after_cart_item_quantity_update', [$this, 'quantityUpdateAction'], 10, 4);

        // items remove filters
        add_action('woocommerce_remove_cart_item', [$this, 'itemRemoveAction']);
        add_action('woocommerce_cart_item_removed', [$this, 'itemAfterRemoveAction']);
        add_action('woocommerce_before_cart_item_quantity_zero', [$this, 'itemRemoveAction'], 10);
        add_action('woocommerce_before_cart_item_quantity_zero', [$this, 'itemAfterRemoveAction'], 11);

        // items restore filters
        add_action('woocommerce_restore_cart_item', [$this, 'itemRestoreAction']);
        add_action('woocommerce_cart_item_restored', [$this, 'itemAfterRestoreAction']);
    }

    /**
     * Clear cart cache
     *
     * @param integer $wizardId
     */
    public static function clearCache($wizardId = null)
    {
        if ($wizardId) {
            self::$cache[$wizardId] = [];
        } else {
            self::$cache = [];
        }
    }
    // </editor-fold>

    // <editor-fold desc="Get content">
    /**
     * Get cart from storage. Set and return shared or default cart content if necessary
     *
     * @param $wizardId
     *
     * @return array - storage value
     */
    public static function getStorage($wizardId)
    {
        return array_filter((array) Storage::get(self::$sessionKey, $wizardId));
    }

    /**
     * Get cart from the session
     *
     * @param integer $wizardId
     * @param array $args
     *
     * @return array
     */
    public static function get($wizardId, $args = [])
    {
        do_action('wcpw_get_cart', $wizardId, $args);

        $defaults = [
            'getProducts' => true,
            'getStepsData' => true,
            'includeSteps' => [],
            'excludeSteps' => []
        ];

        $args = array_replace($defaults, $args);
        $argsHash = crc32(serialize($args));

        if (isset(self::$cache[$wizardId][$argsHash])) {
            return apply_filters('wcpw_cart', self::$cache[$wizardId][$argsHash], $wizardId, $args);
        }

        if (!empty($args['includeSteps']) && !is_array($args['includeSteps'])) {
            $args['includeSteps'] = [(int) $args['includeSteps']];
        }

        if (!empty($args['excludeSteps']) && !is_array($args['excludeSteps'])) {
            $args['excludeSteps'] = [(int) $args['excludeSteps']];
        }

        $output = [];
        $storage = self::getStorage($wizardId);

        if (!empty(array_filter($storage))) {
            foreach ($storage as $key => $item) {
                $item = is_array($item) ? $item : (array) unserialize($item);

                if (empty(array_filter($item))) {
                    continue;
                }

                // handle product
                if (isset($item['product_id'], $item['step_id']) && $item['product_id']) {
                    if (!$args['getProducts']
                        || (!empty($args['includeSteps']) && !in_array((int) $item['step_id'], $args['includeSteps']))
                        || (!empty($args['excludeSteps']) && in_array((int) $item['step_id'], $args['excludeSteps']))
                    ) {
                        continue;
                    }

                    $productId = !empty($item['variation_id']) ? $item['variation_id'] : $item['product_id'];
                    $item = apply_filters(
                        'woocommerce_get_cart_item_from_session',
                        array_merge($item, ['data' => wc_get_product($productId)]),
                        $item,
                        $key
                    );
                }

                // handle step data
                if (isset($item['key'], $item['value'])) {
                    if (!$args['getStepsData']
                        || (!empty($args['includeSteps']) && !in_array((int) $item['step_id'], $args['includeSteps']))
                        || (!empty($args['excludeSteps']) && in_array((int) $item['step_id'], $args['excludeSteps']))
                    ) {
                        continue;
                    }

                    $item = apply_filters('wcpw_cart_step_data', $item, $wizardId, $key);
                }

                if (!empty($item)) {
                    $output[$key] = $item;
                }
            }
        }

        // clear cart from any empty values
        $output = array_filter($output);

        // place steps data upper the products
        uasort($output, function (array $a) use ($wizardId, $args, $output) {
            return (int) (isset($a['product_id'])
                + apply_filters('wcpw_move_cart_step_data_to_end', false, $wizardId, $args, $output));
        });

        // sort cart items by steps
        $cartCopy = $output;
        $output = [];

        foreach (Wizard::getStepsIds($wizardId) as $stepId) {
            foreach ($cartCopy as $cartItemKey => $cartItem) {
                if (!isset($cartItem['step_id']) || $cartItem['step_id'] != $stepId) {
                    continue;
                }

                $output[$cartItemKey] = $cartItem;

                unset($cartCopy[$cartItemKey]);
            }
        }

        // add items out the steps (added through a redirect)
        if (empty($args['includeSteps'])) {
            $output = array_diff_key($cartCopy, $output) + $output;
        }

        // save cache
        self::$cache[$wizardId][$argsHash] = $output;

        return apply_filters('wcpw_cart', $output, $wizardId, $args);
    }

    /**
     * Get cart steps IDs from the session
     *
     * @param integer $wizardId
     * @param array $args
     *
     * @return array
     */
    public static function getStepsIds($wizardId, $args = [])
    {
        $output = [];

        foreach (self::get($wizardId, $args) as $cartItem) {
            if (!isset($cartItem['step_id'])) {
                continue;
            }

            $output[$cartItem['step_id']] = $cartItem['step_id'];
        }

        return apply_filters('wcpw_cart_steps_ids', $output, $wizardId, $args);
    }

    /**
     * Get cart products and variations IDs
     *
     * @param integer $wizardId
     * @param array $args
     *
     * @return array
     */
    public static function getProductsAndVariationsIds($wizardId, $args = [])
    {
        $output = [];

        foreach (self::get($wizardId, $args) as $cartItem) {
            if (!isset($cartItem['product_id'])) {
                continue;
            }

            $output[] = $cartItem['product_id'];

            if (!empty($cartItem['variation_id'])) {
                $output[] = $cartItem['variation_id'];
            }
        }

        return apply_filters('wcpw_cart_products_and_variations_ids', $output, $wizardId, $args);
    }

    /**
     * Get cart categories IDs
     *
     * @param integer $wizardId
     * @param array $args
     *
     * @return array
     */
    public static function getCategoriesIds($wizardId, $args = [])
    {
        $output = [];

        foreach (self::get($wizardId, $args) as $cartItem) {
            if (!isset($cartItem['product_id'])) {
                continue;
            }

            $output = array_merge($output, Product::getTermsIds($cartItem['product_id']));
        }

        $output = array_unique($output);

        return apply_filters('wcpw_cart_categories_ids', $output, $wizardId, $args);
    }

    /**
     * Get cart by step ID
     *
     * @param integer $wizardId
     * @param integer|string $stepId
     *
     * @return array
     */
    public static function getByStepId($wizardId, $stepId)
    {
        $output = self::get($wizardId, ['includeSteps' => [$stepId]]);

        return apply_filters('wcpw_cart_by_step_id', $output, $wizardId, $stepId);
    }
    // </editor-fold>

    // <editor-fold desc="Get items">
    /**
     * Get cart item by key
     *
     * @param integer $wizardId
     * @param string $key
     *
     * @return array
     */
    public static function getItemByKey($wizardId, $key)
    {
        $cart = self::get($wizardId);
        $output = isset($cart[$key]) ? $cart[$key] : null;

        return apply_filters('wcpw_cart_item_by_key', $output, $wizardId, $key);
    }

    /**
     * Get cart product data by ID
     *
     * @param integer $wizardId
     * @param integer $productId
     * @param null|integer|string $stepId - for specific step only
     *
     * @return boolean|null
     */
    public static function getProductById($wizardId, $productId, $stepId = null)
    {
        $output = null;

        foreach (self::get($wizardId) as $cartItem) {
            if (isset($cartItem['product_id'], $cartItem['step_id'])
                && $cartItem['product_id'] == $productId && (!$stepId || $cartItem['step_id'] == $stepId)
            ) {
                $output = $cartItem;

                break;
            }
        }

        return apply_filters('wcpw_cart_product_by_id', $output, $wizardId, $productId, $stepId);
    }
    // </editor-fold>

    // <editor-fold desc="Get keys">
    /**
     * Get cart array key by product ID
     *
     * @param integer $wizardId
     * @param integer $productId
     * @param null|integer|string $stepId - for specific step only
     *
     * @return boolean|null
     */
    public static function getKeyByProductId($wizardId, $productId, $stepId = null)
    {
        $output = null;

        foreach (self::get($wizardId) as $cartItemKey => $cartItem) {
            if (isset($cartItem['product_id'], $cartItem['step_id'])
                && $cartItem['product_id'] == $productId && (!$stepId || $cartItem['step_id'] == $stepId)
            ) {
                $output = $cartItemKey;

                break;
            }
        }

        return apply_filters('wcpw_cart_key_by_product_id', $output, $wizardId, $productId, $stepId);
    }

    /**
     * Get cart array key by product variation data
     *
     * @param integer $wizardId
     * @param integer $variationId
     * @param array $variation
     * @param null|integer|string $stepId - for specific step only
     *
     * @return boolean|null
     */
    public static function getKeyByVariationData($wizardId, $variationId, $variation, $stepId = null)
    {
        $output = null;

        foreach (self::get($wizardId) as $cartItemKey => $cartItem) {
            if (!empty($cartItem['variation_id']) && $cartItem['variation_id'] == $variationId
                && $variation == $cartItem['variation'] && (!$stepId || $cartItem['step_id'] == $stepId)
            ) {
                $output = $cartItemKey;

                break;
            }
        }

        return apply_filters('wcpw_cart_key_by_product_variation_data', $output, $wizardId, $variationId, $variation, $stepId); // phpcs:ignore
    }
    // </editor-fold>

    // <editor-fold desc="Get items data">
    /**
     * Get product meta data string
     *
     * @param array $cartItem
     * @param boolean $flat
     *
     * @return string
     */
    public static function getProductMeta($cartItem, $flat = false)
    {
        if (isset($cartItem['variation']) && is_array($cartItem['variation'])) {
            foreach ($cartItem['variation'] as &$variationsItem) {
                $variationsItem = urldecode($variationsItem);
            }

            unset($variationsItem);
        }

        if (function_exists('wc_get_formatted_cart_item_data')) {
            $output = wc_get_formatted_cart_item_data($cartItem, $flat);
        } else {
            $output = WC()->cart->get_item_data($cartItem, $flat);
        }

        return apply_filters('wcpw_cart_product_meta', $output, $cartItem);
    }

    /**
     * Get kit child data array
     *
     * @param array $cartItem
     * @param integer $wizardId
     * @param array $args
     *
     * @return array
     */
    public static function getKitChildData($cartItem, $wizardId, $args = [])
    {
        $defaults = [
            'getProducts' => true,
            'getStepsData' => true,
            'uploadsSourceType' => 'basename',
            'uploadsImagesAsTag' => true
        ];

        $args = array_replace($defaults, $args);
        $key = null;
        $value = null;
        $display = null;
        $output = [];

        if ($args['getProducts'] && isset($cartItem['data']) && $cartItem['data'] instanceof \WC_Product) {
            $data = wc_get_formatted_cart_item_data($cartItem, true);
            $price = wc_price(self::getItemPrice($cartItem));
            $key = $cartItem['data']->get_name();
            $valueParts = [trim(preg_replace("/\r|\n/", ', ', $data)), $price, '&times;', $cartItem['quantity']];
            $value = implode(' ', $valueParts);
            $display = "<span class=\"wcpw-kit-child is-id-{$cartItem['data']->get_id()}\">"
                . "<span class=\"wcpw-kit-child-meta\">$data</span> "
                . "<span class=\"wcpw-kit-child-price\">$price</span> "
                . '<bdi class="wcpw-kit-child-times">&times;</bdi> '
                . "<span class=\"wcpw-kit-child-quantity\">{$cartItem['quantity']}</span></span>";
        }

        if (!is_null($value)) {
            $output = [
                'key' => $key,
                'value' => apply_filters('wcpw_cart_kit_child_value_parts', $value, $cartItem, $wizardId, $args),
                'display' => apply_filters('wcpw_cart_kit_child_display', $display, $cartItem, $wizardId, $args),
                'hidden' => false
            ];
        }

        return apply_filters('wcpw_kit_child_data', $output);
    }
    // </editor-fold>

    // <editor-fold desc="Add content">
    /**
     * Add a product to the cart
     *
     * @param integer $wizardId
     * @param array $itemData - product data
     *
     * @return string - cart item key
     *
     * @throws \Exception
     */
    public static function addProduct($wizardId, $itemData)
    {
        $defaults = [
            'step_id' => null,
            'product_id' => null,
            'quantity' => null,
            'variation_id' => '',
            'variation' => [],
            'data' => null,
            'request' => []
        ];

        $cartItem = array_replace($defaults, $itemData);

        // ensure we don't add a variation to the cart directly by variation ID
        $id = (!empty($cartItem['variation_id'])) ? (int) $cartItem['variation_id'] : (int) $cartItem['product_id'];
        $variation = isset($cartItem['variation']) ? $cartItem['variation'] : [];
        $variationId = (!empty($cartItem['variation_id'])) ? $cartItem['variation_id'] : 0;
        $cartItem['data'] = !empty($cartItem['data']) ? $cartItem['data'] : [];
        $cartItem['data']['step_id'] = $cartItem['step_id'];

        // load cart item data - might be added by other plugins
        $cartItemData = (array) apply_filters(
            'woocommerce_add_cart_item_data',
            $cartItem['data'],
            $cartItem['product_id'],
            $variationId,
            $cartItem['quantity']
        );

        // sanitize variations
        if (isset($variation) && is_array($variation)) {
            foreach ($variation as &$variationItem) {
                $variationItem = sanitize_text_field($variationItem);
            }
        }

        // generate key
        $cartItemKey = WC()->cart->generate_cart_id(
            $cartItem['product_id'],
            $variationId,
            $variation,
            $cartItemData
        );

        $cartItem = array_merge(
            $cartItemData,
            [
                'key' => $cartItemKey,
                'product_id' => (int) $cartItem['product_id'],
                'variation_id' => isset($cartItem['variation_id']) ? (int) $cartItem['variation_id'] : '',
                'variation' => isset($cartItem['variation']) ? $cartItem['variation'] : [],
                'step_id' => $cartItem['step_id'],
                'wizard_id' => $wizardId,
                'quantity' => (float) $cartItem['quantity'],
                'request' => $cartItem['request'],
                'sold_individually' => isset($cartItem['sold_individually'])
                    ? $cartItem['sold_individually']
                    : WizardStep::getSetting($wizardId, $cartItem['step_id'], 'sold_individually'),
                'data' => wc_get_product($id)
            ]
        );

        $cartItem = apply_filters('woocommerce_add_cart_item', $cartItem, $cartItemKey);
        $cartItem = apply_filters('wcpw_add_to_cart_item', $cartItem, $wizardId, self::$sessionKey);

        do_action('wcpw_before_add_to_cart', $wizardId, $cartItemKey, $cartItem);

        // add to the session variable
        self::set($wizardId, $cartItem, $cartItemKey);

        // clear caches
        self::clearCache($wizardId);
        Utils::clearAvailabilityRulesCache($wizardId);

        do_action('wcpw_after_add_to_cart', $wizardId, $cartItemKey, $cartItem);

        return $cartItemKey;
    }
    // </editor-fold>

    // <editor-fold desc="Remove content">
    /**
     * Remove cart item by the cart array item key
     *
     * @param integer $wizardId
     * @param integer|string $key
     *
     * @return bool
     */
    public static function removeByCartKey($wizardId, $key)
    {
        do_action('wcpw_before_remove_by_cart_key', $wizardId, $key);

        self::$itemsBuffer[] = Storage::get(self::$sessionKey, $wizardId, $key);
        Storage::remove(self::$sessionKey, $wizardId, $key);

        // clear caches
        self::clearCache($wizardId);
        Utils::clearAvailabilityRulesCache($wizardId);

        do_action('wcpw_after_remove_by_cart_key', $wizardId, $key);

        return true;
    }

    /**
     * Remove product from the cart by the product id
     *
     * @param integer $wizardId
     * @param integer|string $productId
     * @param null|integer|string $stepId - for specific step only
     *
     * @return bool
     */
    public static function removeByProductId($wizardId, $productId, $stepId = null)
    {
        do_action('wcpw_before_remove_by_product_id', $wizardId, $productId, $stepId);

        $keyToRemove = self::getKeyByProductId($wizardId, $productId, $stepId);

        if (!$keyToRemove) {
            return false;
        }

        self::removeByCartKey($wizardId, $keyToRemove);

        do_action('wcpw_after_remove_by_product_id', $wizardId, $productId, $stepId);

        return true;
    }

    /**
     * Remove items from the cart by step id
     *
     * @param integer $wizardId
     * @param integer|string $stepId
     * @param array $args
     */
    public static function removeByStepId($wizardId, $stepId, $args = [])
    {
        $defaults = ['removeProducts' => true];
        $args = array_replace($defaults, $args);

        do_action('wcpw_before_remove_by_step_id', $wizardId, $stepId, $args);

        foreach (self::get($wizardId) as $cartItemKey => $cartItem) {
            if (($cartItem['step_id'] == $stepId)
                && ($args['removeProducts'] && !empty($cartItem['product_id']))
            ) {
                self::removeByCartKey($wizardId, $cartItemKey);
            }
        }

        do_action('wcpw_after_remove_by_step_id', $wizardId, $stepId, $args);
    }

    /**
     * Truncate the cart
     *
     * @param integer $wizardId
     */
    public static function truncate($wizardId)
    {
        do_action('wcpw_before_truncate', $wizardId);

        Storage::remove(self::$sessionKey, $wizardId);

        // clear caches
        self::clearCache($wizardId);
        Utils::clearAvailabilityRulesCache($wizardId);

        do_action('wcpw_after_truncate', $wizardId);
    }
    // </editor-fold>

    // <editor-fold desc="Remove content actions">
    /**
     * Woocommerce cart item removing action
     *
     * @param string $itemKey
     */
    public function itemRemoveAction($itemKey)
    {
        // avoid for recursion of actions calls
        add_filter('wcpw_remove_main_cart_reflected_products', '__return_false');

        $cart = WC()->cart->get_cart();
        $itemData = WC()->cart->get_cart_item($itemKey);

        // remove this product from the wizard with the reflecting cart option
        if (isset($itemData['wcpw_id'], $itemData['wcpw_is_cart_bond']) && $itemData['wcpw_is_cart_bond']) {
            self::removeByProductId($itemData['wcpw_id'], $itemData['product_id']);
        }

        // remove products from the same kit
        if (isset($itemData['wcpw_kit_id'], $itemData['wcpw_is_kit_root']) && $itemData['wcpw_kit_id']
            && $itemData['wcpw_is_kit_root']
        ) {
            foreach ($cart as $cartItemKey => $cartItem) {
                if (!isset($cartItem['wcpw_kit_id'])
                    || $cartItem['wcpw_is_kit_root']
                    || $cartItem['wcpw_kit_id'] != $itemData['wcpw_kit_id']
                    || $itemKey == $cartItemKey
                ) {
                    continue;
                }

                self::$itemsBuffer[] = $cartItemKey;
            }
        }
    }

    /** Woocommerce cart item after removing action */
    public function itemAfterRemoveAction()
    {
        // remove all items in the buffer
        if (!empty(self::$itemsBuffer)) {
            foreach (self::$itemsBuffer as $cartItemKey) {
                if (is_string($cartItemKey) && WC()->cart->find_product_in_cart($cartItemKey)) {
                    WC()->cart->remove_cart_item($cartItemKey);
                }
            }

            // clear buffer
            self::$itemsBuffer = [];
        }
    }

    /**
     * Woocommerce cart item restoring action
     *
     * @param string $itemKey
     */
    public function itemRestoreAction($itemKey)
    {
        $removed = WC()->cart->get_removed_cart_contents();
        $itemData = $removed[$itemKey];

        // restore products from the same kit
        if (isset($itemData['wcpw_kit_id'], $itemData['wcpw_is_kit_root']) && $itemData['wcpw_kit_id']
            && $itemData['wcpw_is_kit_root']
        ) {
            foreach ($removed as $cartItemKey => $cartItem) {
                if (!isset($cartItem['wcpw_kit_id'])
                    || $cartItem['wcpw_is_kit_root']
                    || $cartItem['wcpw_kit_id'] != $itemData['wcpw_kit_id']
                    || $itemKey == $cartItemKey
                ) {
                    continue;
                }

                self::$itemsBuffer[] = $cartItemKey;
            }
        }
    }

    /** Woocommerce cart item after restoring action */
    public function itemAfterRestoreAction()
    {
        // restore all items in the buffer
        if (!empty(self::$itemsBuffer)) {
            foreach (self::$itemsBuffer as $cartItemKey) {
                WC()->cart->restore_cart_item($cartItemKey);
            }

            // clear buffer
            self::$itemsBuffer = [];
        }
    }
    // </editor-fold>

    // <editor-fold desc="Get price">
    /**
     * Get the total value of the cart
     *
     * @param integer $wizardId
     * @param array $args
     *
     * @return float
     */
    public static function getTotal($wizardId, $args = [])
    {
        $defaults = ['cart' => null];
        $args = array_replace($defaults, $args);

        $output = 0;
        $cart = is_null($args['cart']) ? self::get($wizardId) : $args['cart'];

        foreach ($cart as $cartItem) {
            // should be step input or existent product with qty
            if (!((isset($cartItem['key'], $cartItem['value'], $cartItem['price']) && $cartItem['price'])
                || (isset($cartItem['data'], $cartItem['quantity']) && $cartItem['data'] && $cartItem['quantity'] > 0
                    && ($product = $cartItem['data']) && $product instanceof \WC_Product && $product->exists()))
            ) {
                continue;
            }

            $output += (float) (self::getItemPrice($cartItem) * (isset($cartItem['quantity']) ? $cartItem['quantity'] : 1));
        }

        return apply_filters('wcpw_cart_total', $output, $wizardId);
    }

    /**
     * Get the total price string of the cart
     *
     * @param integer $wizardId
     * @param array $args
     *
     * @return string
     */
    public static function getTotalPrice($wizardId, $args = [])
    {
        $output = wc_price(self::getTotal($wizardId, $args));

        if (self::displayPricesIncludesTax()) {
            if (!self::pricesIncludeTax() && property_exists(WC(), 'cart') && WC()->cart
                && method_exists(WC()->cart, 'get_subtotal_tax') && WC()->cart->get_subtotal_tax() > 0
            ) {
                $output .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat()
                    . '</small>';
            }
        } else {
            if (self::pricesIncludeTax() && property_exists(WC(), 'cart') && WC()->cart
                && method_exists(WC()->cart, 'get_subtotal_tax') && WC()->cart->get_subtotal_tax() > 0
            ) {
                $output .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat()
                    . '</small>';
            }
        }

        return apply_filters('wcpw_cart_total_price', $output, $wizardId);
    }

    /**
     * Get root item step input children price
     *
     * @param array $cartItem
     *
     * @return float
     */
    public static function getItemStepInputChildrenPrice($cartItem)
    {
        $output = 0;

        if (!empty($cartItem['wcpw_kit_children']) && is_array($cartItem['wcpw_kit_children'])) {
            foreach ($cartItem['wcpw_kit_children'] as $child) {
                if (!isset($child['key'], $child['value'])) {
                    continue;
                }

                $output += self::getItemPrice($child, ['parentItem' => $cartItem]);
            }
        }

        return $output;
    }

    /**
     * Get pure item price
     *
     * @param array $cartItem
     * @param array $args
     *
     * @return float
     */
    public static function getItemPrice($cartItem, $args = [])
    {
        $defaults = [
            'parentItem' => null,
            'checkTax' => self::displayPricesIncludesTax() || self::pricesIncludeTax(),
            'displayIncludeTax' => self::displayPricesIncludesTax(),
            'pricesIncludeTax' => self::pricesIncludeTax(),
            'context' => 'view'
        ];

        $args = array_replace($defaults, $args);
        $output = 0;

        // is step input
        if (isset($cartItem['key'], $cartItem['value'], $cartItem['price']) && $cartItem['price']) {
            $output = $cartItem['price'];

            if ($args['parentItem']) {
                $output = self::modifyPriceAccordingItemTaxes($output, $args['parentItem'], $args);
            }

            return apply_filters('wcpw_cart_item_price', $output, $cartItem, $args);
        }

        if (empty($cartItem['data'])) {
            return apply_filters('wcpw_cart_item_price', $output, $cartItem, $args);
        }

        // is product
        $product = $cartItem['data'];

        if (!$product instanceof \WC_Product) {
            return apply_filters('wcpw_cart_item_price', $output, $cartItem, $args);
        }

        if (!$args['checkTax']) {
            $output = (float) $product->get_price($args['context']);

            // if qty was changed and there is an initial value
            if (!empty($cartItem['wcpw_kit_root_initial_qty'])) {
                $output *= $cartItem['wcpw_kit_root_initial_qty'];
            }

            return apply_filters('wcpw_cart_item_price', $output, $cartItem, $args);
        }

        $output = $args['displayIncludeTax'] || !$args['pricesIncludeTax']
            ? wc_get_price_including_tax($product)
            : wc_get_price_excluding_tax($product);

        // if qty was changed and there is an initial value
        if (!empty($cartItem['wcpw_kit_root_initial_qty'])) {
            $output *= $cartItem['wcpw_kit_root_initial_qty'];
        }

        return apply_filters('wcpw_cart_item_price', $output, $cartItem, $args);
    }

    /**
     * Get final item price with children
     *
     * @param array $cartItem
     * @param array $args
     *
     * @return float
     */
    public static function getItemFinalPrice($cartItem, $args = [])
    {
        $output = self::getItemPrice($cartItem, $args);

        // calculate total children price
        if (!empty($cartItem['wcpw_kit_children']) && is_array($cartItem['wcpw_kit_children'])) {
            $args['parentItem'] = $cartItem;

            foreach ($cartItem['wcpw_kit_children'] as $child) {
                $output += self::getItemPrice($child, $args)
                    * (isset($child['quantity']) ? $child['quantity'] : 1);
            }
        }

        return apply_filters('wcpw_cart_item_final_price', $output, $cartItem);
    }
    // </editor-fold>

    // <editor-fold desc="Setters">
    /**
     * Set cart content
     *
     * @param integer $wizardId
     * @param array $value
     * @param string $key
     */
    public static function set($wizardId, $value, $key = null)
    {
        Storage::set(self::$sessionKey, $wizardId, $value, $key);
    }

    /**
     * Set item price
     *
     * @param array $cartItem
     * @param float $value
     */
    public static function setItemPrice($cartItem, $value)
    {
        if (method_exists($cartItem['data'], 'set_price')) {
            $cartItem['data']->set_price($value);
        } else {
            $cartItem['data']->price = $value;
        }
    }

    /**
     * Handles on cart item quantity update
     *
     * @param string $itemKey
     * @param integer $newQuantity
     * @param integer $oldQuantity
     * @param \WC_Cart $cart
     */
    public function quantityUpdateAction($itemKey, $newQuantity, $oldQuantity, $cart)
    {
        // change kit products quantity accordingly the root product
        if (isset($cart->cart_contents[$itemKey]['wcpw_is_kit_root'])
            && $cart->cart_contents[$itemKey]['wcpw_is_kit_root']
            && !$cart->cart_contents[$itemKey]['wcpw_is_kit_quantity_fixed']
        ) {
            foreach ($cart->cart_contents as $cartItemKey => $cartItem) {
                if (!isset($cartItem['wcpw_kit_id'])
                    || $itemKey == $cartItemKey
                    || $cart->cart_contents[$itemKey]['wcpw_kit_id'] != $cartItem['wcpw_kit_id']
                ) {
                    continue;
                }

                if ($oldQuantity == $cartItem['quantity']) {
                    $newChildQuantity = $newQuantity;
                } else {
                    $newChildQuantity = $newQuantity >= $oldQuantity
                        ? $cartItem['quantity'] * $newQuantity
                        : round($cartItem['quantity'] / $oldQuantity);
                }

                $cart->cart_contents[$cartItemKey]['quantity'] = $newChildQuantity;
            }
        }
    }
    // </editor-fold>

    // <editor-fold desc="Utils">
    /**
     * Modify the price value accordingly the cart item taxes to keep it the same
     *
     * @param float $price
     * @param array $cartItem
     * @param array $args
     *
     * @return float
     */
    public static function modifyPriceAccordingItemTaxes($price, $cartItem, $args = [])
    {
        $defaults = [
            'checkTax' => self::displayPricesIncludesTax() || self::pricesIncludeTax(),
            'displayIncludeTax' => self::displayPricesIncludesTax(),
            'pricesIncludeTax' => self::pricesIncludeTax()
        ];

        $args = array_replace($defaults, $args);

        if (empty($cartItem['data'])) {
            return 0;
        }

        $product = $cartItem['data'];

        if (!$product instanceof \WC_Product) {
            return 0;
        }

        // change price by the product's tax to keep it the same
        if ($args['checkTax'] && $product->is_taxable()) {
            $taxRates = \WC_Tax::get_rates($product->get_tax_class());
            $baseTaxRates = \WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));
            $removeTax = apply_filters('woocommerce_adjust_non_base_location_prices', true)
                ? \WC_Tax::calc_tax($price, $baseTaxRates, true)
                : \WC_Tax::calc_tax($price, $taxRates, true);

            if ($args['displayIncludeTax'] && !$args['pricesIncludeTax']) {
                $price -= array_sum($removeTax);
            } elseif (!$args['displayIncludeTax'] && $args['pricesIncludeTax']) {
                $price += array_sum($removeTax);
            }
        }

        return $price;
    }

    /**
     * Are taxes showed included in prices
     *
     * @return bool
     */
    public static function displayPricesIncludesTax()
    {
        // some problems are possible while PDF sending via CF7
        if (function_exists('WC') && property_exists(WC(), 'cart')
            && WC()->cart && method_exists(WC()->cart, 'display_prices_including_tax')
        ) {
            return WC()->cart->display_prices_including_tax();
        }

        return DataBase\Option::get('woocommerce_tax_display_cart') == 'incl';
    }

    /**
     * Are taxes included in prices
     *
     * @return bool
     */
    public static function pricesIncludeTax()
    {
        if (function_exists('wc_prices_include_tax()')) {
            return wc_prices_include_tax();
        }

        return DataBase\Option::get('woocommerce_prices_include_tax') === 'yes';
    }

    /**
     * Generate cart item ID
     *
     * @param array $cartItem
     *
     * @return string|null
     */
    public static function generateProductId($cartItem)
    {
        if (!isset($cartItem['product_id'], $cartItem['step_id'])) {
            return null;
        }

        return WC()->cart->generate_cart_id(
            $cartItem['product_id'],
            isset($cartItem['variation_id']) ? $cartItem['variation_id'] : '',
            isset($cartItem['variation']) ? $cartItem['variation'] : [],
            ['step_id' => $cartItem['step_id']]
        );
    }
    // </editor-fold>

    // <editor-fold desc="Output">
    /**
     * WC cart item remove button filter
     *
     * @param string $html
     * @param string $cartItemKey
     *
     * @return string
     */
    public function itemRemoveLinkFilter($html, $cartItemKey)
    {
        $cartItem = WC()->cart->get_cart_item($cartItemKey);

        // if isn't from a kit or kit root item
        if (!isset($cartItem['wcpw_kit_id']) || $cartItem['wcpw_is_kit_root']) {
            return $html;
        }

        return '';
    }

    /**
     * WC cart item quantity input filter
     *
     * @param string $html
     * @param string $cartItemKey
     * @param array $cartItem
     *
     * @return string
     */
    public function itemQuantityFilter($html, $cartItemKey, $cartItem)
    {
        if ($cartItemKey && isset($cartItem['wcpw_is_kit_root'])
            && $cartItem['wcpw_is_kit_root'] && !$cartItem['wcpw_is_kit_quantity_fixed']
        ) {
            return $html;
        }

        if (!isset($cartItem['wcpw_kit_id'])) {
            return $html;
        }

        return $cartItem['quantity'];
    }

    /**
     * WC table row class filter
     *
     * @param string $class
     * @param string $cartItem
     *
     * @return string
     */
    public function itemClass($class, $cartItem)
    {
        if (is_array($cartItem)) {
            if (!empty($cartItem['wcpw_is_kit_root'])) {
                $class .= ' wcpw-kit-root';
            }

            if (!empty($cartItem['wcpw_kit_parent_key'])) {
                $class .= ' wcpw-kit-child';
            }
        }

        return $class;
    }

    /**
     * WC cart item data filter
     *
     * @param array $itemData
     * @param array $cartItem
     *
     * @return array
     */
    public function itemDataFilter($itemData, $cartItem)
    {
        // add children to a combined kit product
        if (isset($cartItem['wcpw_kit_children'], $cartItem['wcpw_kit_type'])) {
            if (!empty($cartItem['wcpw_default_price'])) {
                $price = wc_price($cartItem['wcpw_default_price']);
                $quantity = !empty($cartItem['wcpw_kit_root_initial_qty']) ? $cartItem['wcpw_kit_root_initial_qty'] : $cartItem['quantity'];
                $display = "<span class=\"wcpw-kit-child\"><span class=\"wcpw-kit-child-price\">$price</span> "
                    . '<bdi class="wcpw-kit-child-times">&times;</bdi> '
                    . "<span class=\"wcpw-kit-child-quantity\">$quantity</span></span>";

                $itemData[] = [
                    'key' => esc_html__('Price', 'products-wizard-lite-for-woocommerce'),
                    'value' => implode(' ', [$price, '&times;', $quantity]),
                    'display' => $display,
                    'hide' => false
                ];
            }

            foreach ($cartItem['wcpw_kit_children'] as $child) {
                $childData = self::getKitChildData(
                    $child,
                    $cartItem['wcpw_id'],
                    ['getProducts' => $cartItem['wcpw_kit_type'] != 'separated']
                );

                if (!empty($childData)) {
                    $itemData[] = $childData;
                }
            }

            return $itemData;
        }

        // add kit id to an order's lines
        if (!empty($cartItem['wcpw_kit_title']) && !empty($cartItem['wcpw_kit_id'])) {
            $itemData[] = [
                'key' => $cartItem['wcpw_kit_title'],
                'value' => $cartItem['wcpw_kit_id'],
                'display' => '',
                'hidden' => true
            ];
        }

        return $itemData;
    }

    /**
     * WC cart product visibility filter
     *
     * @param boolean $visible
     * @param array $cartItem
     *
     * @return bool
     */
    public function itemVisibilityFilter($visible, $cartItem)
    {
        if (!empty($cartItem['wcpw_is_hidden_product'])) {
            $visible = !$cartItem['wcpw_is_hidden_product'];
        }

        return $visible;
    }

    /**
     * WC cart item price filter
     *
     * @param string $price
     * @param array $cartItem
     *
     * @return string
     */
    public function itemPriceFilter($price, $cartItem)
    {
        if (empty($cartItem['wcpw_kit_price'])) {
            return $price;
        }

        $product = $cartItem['data'];
        $price = wc_price($cartItem['wcpw_kit_price']);

        if (!$product->is_taxable()) {
            return $price;
        }

        if (self::displayPricesIncludesTax()) {
            if (!self::pricesIncludeTax() && property_exists(WC(), 'cart') && WC()->cart
                && method_exists(WC()->cart, 'get_subtotal_tax') && WC()->cart->get_subtotal_tax() > 0
            ) {
                $price .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat()
                    . '</small>';
            }
        } elseif (self::pricesIncludeTax() && property_exists(WC(), 'cart') && WC()->cart
            && method_exists(WC()->cart, 'get_subtotal_tax') && WC()->cart->get_subtotal_tax() > 0
        ) {
            $price .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat()
                . '</small>';
        }

        return $price;
    }

    /**
     * WC cart item sub total price filter
     *
     * @param string $price
     * @param array $cartItem
     *
     * @return string
     */
    public function itemSubTotalFilter($price, $cartItem)
    {
        if (empty($cartItem['wcpw_kit_price'])) {
            return $price;
        }

        $product = $cartItem['data'];
        $price = wc_price($cartItem['wcpw_kit_price'] * $cartItem['quantity']);

        if (!$product->is_taxable()) {
            return $price;
        }

        if (self::displayPricesIncludesTax()) {
            if (!self::pricesIncludeTax() && property_exists(WC(), 'cart') && WC()->cart
                && method_exists(WC()->cart, 'get_subtotal_tax') && WC()->cart->get_subtotal_tax() > 0
            ) {
                $price .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat()
                    . '</small>';
            }
        } elseif (self::pricesIncludeTax() && property_exists(WC(), 'cart') && WC()->cart
            && method_exists(WC()->cart, 'get_subtotal_tax') && WC()->cart->get_subtotal_tax() > 0
        ) {
            $price .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat()
                . '</small>';
        }

        return $price;
    }

    /**
     * WC cart item image filter
     *
     * @param string $image
     * @param array $cartItem
     * @param string $cartItemKey
     *
     * @return string
     */
    public function itemThumbnailFilter($image, $cartItem, $cartItemKey)
    {
        if (isset($cartItem['wcpw_kit_thumbnail_url'])) {
            $attributes = [
                'class' => 'wcpw-cart-item-generated-thumbnail',
                'src' => $cartItem['wcpw_kit_thumbnail_url'],
                'alt' => get_the_title($cartItem['product_id'])
            ];

            $attributes = apply_filters('wcpw_cart_item_generated_thumbnail_attributes', $attributes, $cartItem, $cartItemKey); // phpcs:ignore
            $output = '<img ' . Utils::attributesArrayToString($attributes) . '>';

            return apply_filters('wcpw_cart_item_generated_thumbnail', $output, $cartItem, $cartItemKey);
        }

        return $image;
    }

    /**
     * WC cart calculation action
     *
     * @param \WC_Cart $cart
     */
    public function beforeCalculateAction($cart)
    {
        if (Utils::isAdminSide() || did_action('wcpw_before_calculate_totals')) {
            return;
        }

        do_action('wcpw_before_calculate_totals', $cart);

        $cartContent = $cart->get_cart();

        foreach ($cartContent as $cartItemKey => &$cartItem) {
            // only for WCPW products
            if (empty($cartItem['wcpw_id'])) {
                continue;
            }

            // is a kit child product but have no parent
            if (array_key_exists('wcpw_kit_parent_key', $cartItem)
                && !isset($cartContent[$cartItem['wcpw_kit_parent_key']])
            ) {
                $cart->remove_cart_item($cartItemKey);

                continue;
            }

            $product = $cartItem['data'];

            if (!$product instanceof \WC_Product) {
                continue;
            }

            // is a kit root product
            if (!empty($cartItem['wcpw_kit_children']) && !empty($cartItem['wcpw_kit_type'])
                && is_array($cartItem['wcpw_kit_children'])
            ) {
                // change image
                if (!empty($cartItem['wcpw_kit_thumbnail_id'])) {
                    if (method_exists($product, 'set_image_id')) {
                        $product->set_image_id($cartItem['wcpw_kit_thumbnail_id']);
                    } else {
                        $cartItem['data']->image_id = $cartItem['wcpw_kit_thumbnail_id'];
                    }
                }

                // change visibility
                if (method_exists($product, 'set_catalog_visibility')) {
                    try {
                        $product->set_catalog_visibility('hidden');
                    } catch (\Exception $exception) {
                        continue;
                    }

                    // variable products fix
                    if (method_exists($product, 'set_parent_data') && method_exists($product, 'get_parent_data')) {
                        $parentData = $product->get_parent_data();
                        $parentData['catalog_visibility'] = 'hidden';
                        $product->set_parent_data($parentData);
                    }
                } else {
                    $cartItem['data']->catalog_visibility = 'hidden';
                }

                // set prices
                if ($cartItem['wcpw_kit_type'] == 'combined' || !empty($cartItem['wcpw_is_base_kit_product'])) {
                    // is a pre-defined kit base or combined kit

                    // use the fixed price
                    if (!empty($cartItem['wcpw_kit_fixed_price'])) {
                        self::setItemPrice($cartItem, $cartItem['wcpw_kit_fixed_price']);

                        continue;
                    }

                    // replace the real price and show the final price instead
                    if (empty($cartItem['wcpw_is_base_kit_product'])) {
                        WC()->cart->cart_contents[$cartItemKey]['wcpw_default_price']
                            = $cartItem['wcpw_default_price']
                            = self::getItemPrice($cartItem);
                    }

                    WC()->cart->cart_contents[$cartItemKey]['wcpw_kit_price']
                        = $cartItem['wcpw_kit_price']
                        = self::getItemFinalPrice($cartItem);

                    self::setItemPrice(
                        $cartItem,
                        self::getItemStepInputChildrenPrice($cartItem)
                        + (!empty($cartItem['wcpw_is_base_kit_product'])
                            ? 0
                            : self::getItemPrice($cartItem, ['checkTax' => false, 'context' => 'wcpw_cart'])
                        )
                    );
                } else {
                    // set product price
                    self::setItemPrice(
                        $cartItem,
                        self::getItemPrice($cartItem, ['checkTax' => false, 'context' => 'wcpw_cart'])
                    );
                }

                continue;
            }

            // null the child price if the parent have a fixed price
            if (!empty($cartItem['wcpw_kit_parent_key'])
                && !empty($cartContent[$cartItem['wcpw_kit_parent_key']]['wcpw_kit_fixed_price'])
            ) {
                self::setItemPrice($cartItem, 0);

                continue;
            }

            // set product price
            self::setItemPrice(
                $cartItem,
                self::getItemPrice($cartItem, ['checkTax' => false, 'context' => 'wcpw_cart'])
            );
        }

        unset($cartItem);
    }
    // </editor-fold>
}
