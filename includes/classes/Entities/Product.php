<?php
namespace WCProductsWizard\Entities;

use WCProductsWizard\Cart;
use WCProductsWizard\DataBase;
use WCProductsWizard\Template;

/**
 * Product Class
 *
 * @class Product
 * @version 11.0.1
 */
class Product implements Interfaces\PostType
{
    use Traits\PostType;

    // <editor-fold desc="Properties">
    /**
     * Post type string
     * @var string
     */
    public static $postType = 'product';

    /**
     * Namespace string
     * @var string
     */
    public static $namespace = 'wcpw_product';
    // </editor-fold>

    // <editor-fold desc="Core">
    /** Class Constructor */
    public function __construct()
    {
        $this->initPostType();
    }
    // </editor-fold>

    // <editor-fold desc="Get data">
    /**
     * Get product's terms ids array
     *
     * @param integer $productId
     * @param array $args
     *
     * @return array
     */
    public static function getTermsIds($productId, $args = [])
    {
        $defaults = [
            'taxonomy' => 'product_cat',
            'all' => true // get also parent terms even aren't attached
        ];

        $args = array_replace($defaults, $args);
        $taxonomy = $args['taxonomy'];

        static $termsIds = [];

        if (isset($termsIds[$taxonomy][$productId])) {
            return $termsIds[$taxonomy][$productId];
        }

        $output = wp_get_post_terms($productId, $taxonomy, ['fields' => 'ids']);

        if ($args['all']) {
            foreach ($output as $termId) {
                $output = array_merge($output, get_ancestors($termId, $taxonomy, 'taxonomy'));
            }
        }

        $termsIds[$taxonomy][$productId] = $output;

        return $output;
    }

    /**
     * Merge arguments with products query part
     *
     * @param array $args
     * @param array $productsIds - specific products only
     *
     * @return array
     */
    public static function addRequestArgs($args, $productsIds = null)
    {
        $defaults = [
            'id' => null,
            'stepId' => null,
            'page' => 1,
            'orderBy' => null
        ];

        $args = array_replace($defaults, $args);
        $productsPerPage = WizardStep::getSetting($args['id'], $args['stepId'], 'products_per_page');
        $activeProductsIds = Cart::getProductsAndVariationsIds($args['id'], ['includeSteps' => $args['stepId']]);

        // get order from setting
        if (empty($args['orderBy'])) {
            $args['orderBy'] = WizardStep::getSetting($args['id'], $args['stepId'], 'order_by');
        }

        // get products by filtered ids
        if (is_null($productsIds)) {
            $productsIds = self::getStepProductsIds($args['id'], $args['stepId'], $args);
        }

        $queryArgs = [
            'post_type' => ['product', 'product_variation'],
            'post__in' => $productsIds,
            'posts_per_page' => -1,
            'numberposts' => -1,
            'ignore_sticky_posts' => true,
            'paged' => $args['page']
        ];

        // change query order by
        $orderByValue = explode('-', $args['orderBy']);
        $queryArgs = array_replace(
            $queryArgs,
            WC()->query->get_catalog_ordering_args(
                esc_attr($orderByValue[0]),
                !empty($orderByValue[1]) ? $orderByValue[1] : WizardStep::getSetting($args['id'], $args['stepId'], 'order')
            )
        );

        // order by price according to individual discounts to sort products appropriately
        if ($queryArgs['orderby'] == 'price') {
            $productsSort = [];

            foreach ($productsIds as $productId) {
                // different types might be requested
                switch (get_post_type($productId)) {
                    default:
                    case 'product':
                        $product = new \WC_Product((int) $productId);
                        break;

                    case 'product_variation':
                        $product = new \WC_Product_Variation((int) $productId);
                }

                $productsSort[$productId] = (float) $product->get_price();
            }

            if ($queryArgs['order'] == 'ASC') {
                asort($productsSort);
            } else {
                arsort($productsSort);
            }

            $queryArgs['post__in'] = array_keys($productsSort);
            $queryArgs['orderby'] = 'post__in';
        }

        // change products per page value
        if ($productsPerPage != 0) {
            $queryArgs['posts_per_page'] = $queryArgs['numberposts'] = $productsPerPage;
        }

        if ((empty($activeProductsIds) || empty(array_intersect($activeProductsIds, $productsIds)))) {
            // set the first product as active
            $productsQuery = DataBase\Entity::getCollection(array_replace(
                $queryArgs,
                [
                    'numberposts' => 1,
                    'posts_per_page' => 1,
                    'fields' => 'ids'
                ]
            ));

            if (isset($productsQuery[0])) {
                $activeProductsIds[] = $productsQuery[0];
            }
        }

        $output = array_replace(
            $args,
            [
                'queryArgs' => $queryArgs,
                'itemTemplate' => 'form/item/' . WizardStep::getSetting($args['id'], $args['stepId'], 'item_template'),
                'cart' => Cart::get($args['id']),
                'activeProductsIds' => $activeProductsIds,
                'severalProducts' => WizardStep::getSetting($args['id'], $args['stepId'], 'several_products'),
                'soldIndividually' => WizardStep::getSetting($args['id'], $args['stepId'], 'sold_individually'),
                'enableTitleLink' => WizardStep::getSetting($args['id'], $args['stepId'], 'enable_item_title_link'),
                'enableThumbnailLink' => WizardStep::getSetting($args['id'], $args['stepId'], 'enable_item_thumbnail_link')
            ]
        );

        return apply_filters('wcpw_products_request_args', $output, $productsIds, $args);
    }

    /**
     * Makes the products query considering all conditions
     *
     * @param array $args
     *
     * @return string
     */
    public static function request($args)
    {
        $defaults = [
            'id' => null,
            'stepId' => null
        ];

        $args = array_replace($defaults, $args);

        if (!$args['id'] || !$args['stepId']) {
            return '';
        }

        // for 3rd party plugins compatibility
        if (!\WCProductsWizard\Instance()->wizard->getCurrentId()) {
            \WCProductsWizard\Instance()->wizard->setCurrentId($args['id']);
        }

        if (!\WCProductsWizard\Instance()->wizardStep->getCurrentId()) {
            \WCProductsWizard\Instance()->wizardStep->setCurrentId($args['stepId']);
        }

        // there are no products requested - show nothing
        if (empty(WizardStep::getSetting($args['id'], $args['stepId'], 'categories'))
            && empty(array_filter((array) WizardStep::getSetting($args['id'], $args['stepId'], 'included_products')))
        ) {
            return '';
        }

        $productsIds = self::getStepProductsIds($args['id'], $args['stepId'], $args);

        if (!empty($productsIds)) {
            $args = self::addRequestArgs($args, $productsIds);
            $template = WizardStep::getSetting($args['id'], $args['stepId'], 'template');

            return Template::html("form/layouts/$template", $args);
        }

        // should have products but nothing found
        return Template::html('messages/nothing-found', $args);
    }

    /**
     * Get products request instance
     *
     * @param array $args{id: integer, stepId: string, queryArgs: array}
     *
     * @return \WP_Query
     */
    public static function getQueryObject($args)
    {
        $defaults = [
            'id' => null,
            'stepId' => null,
            'queryArgs' => []
        ];

        $args = array_replace($defaults, $args);
        $output = new \WP_Query($args['queryArgs']);

        return apply_filters('wcpw_products_query_object', $output, $args);
    }

    /**
     * Add a product to the main woocommerce cart
     *
     * @param array $args
     *
     * @throws \Exception
     *
     * @return string|boolean
     */
    public static function addToMainCart($args)
    {
        $defaults = [
            'product_id' => null,
            'quantity' => 1,
            'variation_id' => null,
            'variation' => [],
            'data' => [
                'wcpw_id' => null,
                'wcpw_step_id' => null
            ],
            'request' => null
        ];

        $args = array_replace_recursive($defaults, $args);
        $cartQuantity = 0;

        do_action('wcpw_before_add_to_main_cart', $args);

        // get the same product's quantity from the main cart and remove it
        if (apply_filters('wcpw_add_to_main_cart_merge_products_quantity', false, $args)) {
            $cart = WC()->cart->get_cart();

            foreach ($cart as $cartItemKey => $cartItem) {
                if ($cartItem['product_id'] != $args['product_id']
                    || $cartItem['variation_id'] != $args['variation_id']
                    || $cartItem['variation'] != $args['variation']
                    || $cartItem['wcpw_id'] != $args['data']['wcpw_id']
                    || $cartItem['wcpw_step_id'] != $args['data']['wcpw_step_id']
                ) {
                    continue;
                }

                $cartQuantity += (float) $cartItem['quantity'];

                WC()->cart->remove_cart_item($cartItemKey);
            }
        }

        return WC()->cart->add_to_cart(
            $args['product_id'],
            $args['quantity'] + $cartQuantity,
            $args['variation_id'],
            $args['variation'],
            $args['data']
        );
    }

    /**
     * Prepare step products request query considering all conditions
     *
     * @param integer $wizardId
     * @param integer|string $stepId
     * @param array $args
     *
     * @return array
     */
    public static function getStepProductsIds($wizardId, $stepId, $args = [])
    {
        $defaults = [
            'queryArgs' => [
                'post_type' => 'product',
                'fields' => 'ids',
                'nopaging' => true,
                'tax_query' => ['relation' => 'AND'], // phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                'post__in' => []
            ]
        ];

        $args = array_replace_recursive($defaults, $args);
        $output = [];

        if (!$stepId || !is_numeric($stepId)) {
            return apply_filters('wcpw_step_products_ids', $output, $wizardId, $stepId, $args);
        }

        $categories = WizardStep::getSetting($wizardId, $stepId, 'categories');
        $includedProductsIds = array_filter((array) WizardStep::getSetting($wizardId, $stepId, 'included_products'));

        // there are no products selected
        if (empty($categories) && empty(array_filter($includedProductsIds))) {
            return apply_filters('wcpw_step_products_ids', $output, $wizardId, $stepId, $args);
        }

        // product request by current category
        $queryArgs = $args['queryArgs'];

        if (current_user_can('manage_woocommerce')) {
            $queryArgs['post_status'] = 'any';
        }

        // query specific products only
        if (!empty($includedProductsIds)) {
            $queryArgs['post__in'] += $includedProductsIds;
            $queryArgs['post_type'] = ['product', 'product_variation'];
        }

        // query by categories
        if (!empty($categories)) {
            // have some categories to request
            $queryArgs['tax_query'][] = [
                'taxonomy' => 'product_cat',
                'field' => 'id',
                'terms' => $categories,
                'operator' => 'IN'
            ];
        }

        // blend in filter args query
        $queryArgs = apply_filters('wcpw_step_products_query_args', $queryArgs, $wizardId, $stepId, $args);

        // make a query
        foreach (DataBase\Entity::getCollection($queryArgs) as $productId) {
            // different types might be requested
            switch (get_post_type($productId)) {
                default:
                case 'product':
                    $product = new \WC_Product((int) $productId);
                    break;

                case 'product_variation':
                    $product = new \WC_Product_Variation((int) $productId);
            }

            $available = $product->is_visible() && $product->is_purchasable()
                && ($product->is_in_stock() || $product->backorders_allowed());

            if (apply_filters('wcpw_product_availability', $available, $productId, $wizardId, $stepId, $args)) {
                $output[] = (int) $productId;
            }
        }

        return apply_filters('wcpw_step_products_ids', $output, $wizardId, $stepId, $args);
    }
    // </editor-fold>
}
