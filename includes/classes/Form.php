<?php
namespace WCProductsWizard;

use WCProductsWizard\Entities\Product;
use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Entities\WizardStep;

/**
 * Form Class
 *
 * @class Form
 * @version 8.5.1
 */
class Form
{
    //<editor-fold desc="Properties">
    /**
     * Active steps session keys variable
     * @var string
     */
    public static $activeStepsSessionKey = 'woocommerce-products-wizard-active-step';

    /**
     * Previous steps session keys variable
     * @var string
     */
    public static $previousStepsSessionKey = 'woocommerce-products-wizard-previous-step';

    /**
     * Ajax actions variable
     * @var array
     */
    public $ajaxActions = [];

    /**
     * Notices array
     * @var array
     */
    public $notices = [];
    //</editor-fold>

    // <editor-fold desc="Core">
    /** Class Constructor */
    public function __construct()
    {
        $this->ajaxActions = apply_filters(
            'wcpw_form_ajax_actions',
            [
                'wcpwSubmit' => 'submitAjax',
                'wcpwAddToMainCart' => 'addToMainCartAjax',
                'wcpwGetStep' => 'requestStepAjax',
                'wcpwGetStepPage' => 'requestStepPageAjax',
                'wcpwSkipStep' => 'skipStepAjax',
                'wcpwSubmitAndSkipAll' => 'submitAndSkipAllAjax',
                'wcpwSkipAll' => 'skipAllAjax',
                'wcpwReset' => 'resetAjax',
                'wcpwAddCartProduct' => 'addCartProductAjax',
                'wcpwRemoveCartProduct' => 'removeCartProductAjax',
                'wcpwUpdateCartProduct' => 'updateCartProductAjax'
            ]
        );

        add_action('wp_loaded', [$this, 'requests']);

        // verify requests
        add_action('wcpw_before_submit_form', [$this, 'checkNonce']);
        add_action('wcpw_before_get_form', [$this, 'checkNonce']);
        add_action('wcpw_before_skip_form', [$this, 'checkNonce']);
        add_action('wcpw_before_skip_all', [$this, 'checkNonce']);
        add_action('wcpw_before_reset_form', [$this, 'checkNonce']);
        add_action('wcpw_before_add_all_to_main_cart', [$this, 'checkCartRules'], 10, 2);
        add_action('wcpw_after_add_all_to_main_cart', [$this, 'addNoticesAfterAddAllToMainCart'], 10, 3);

        foreach ($this->ajaxActions as $key => $action) {
            add_action("wp_ajax_nopriv_$key", [$this, $action]);
            add_action("wp_ajax_$key", [$this, $action]);
        }
    }

    /** Add request actions */
    public function requests()
    {
        if (is_admin()) {
            return;
        }

        $request = self::getRequestVariable();

        // handle no-js forms actions
        if (isset($request['woocommerce-products-wizard'], $request['id'])) {
            try {
                // adding a product to the cart handler
                if (isset($request['add-cart-product'])) {
                    $request['productToAddKey'] = $request['add-cart-product'];
                    self::addCartProduct($request);
                }

                // updating a product in the cart handler
                if (isset($request['update-cart-product'])) {
                    $request['productCartKey'] = $request['update-cart-product'];
                    self::updateCartProduct($request);
                }

                // removing a product from the cart handler
                if (isset($request['remove-cart-product'])) {
                    $request['productCartKey'] = $request['remove-cart-product'];
                    self::removeCartProduct($request);
                }

                // add all to main cart but not for attached to a product wizard
                if (isset($request['add-to-main-cart']) && !isset($request['attach-to-product'])) {
                    self::addToMainCart($request);
                }

                // submit form handler
                if (isset($request['submit'])) {
                    if (is_numeric($request['submit'])) {
                        // get specific step
                        $request['stepId'] = $request['submit'];
                    }

                    self::submit($request);
                }

                // submit and skip all form handler
                if (isset($request['submit-and-skip-all'])) {
                    self::submitAndSkipAll($request);
                }

                // "reset-all" is needed cause of the native "reset" action
                if (isset($request['reset-all']) || isset($request['reset'])) {
                    self::reset($request);
                }

                // skip a step
                if (isset($request['skip-step'])) {
                    self::skipStep($request);
                }

                // skip all steps
                if (isset($request['skip-all'])) {
                    self::skipAll($request);
                }

                // get a step
                if (isset($request['get-step'])) {
                    $request['stepId'] = $request['get-step'];

                    self::requestStep($request);
                }
            } catch (\Exception $exception) {
                $this->addNotice(
                    $exception->getCode() ?: self::getActiveStepId($request['id']),
                    [
                        'view' => 'custom',
                        'message' => $exception->getMessage()
                    ]
                );

                return;
            }
        } elseif (!empty($request['wcpwId']) && !empty($request['wcpwStep'])) {
            // set active step from URL
            self::setActiveStepId($request['wcpwId'], $request['wcpwStep']);
        }
    }

    /**
     * Return an AJAX reply and exit
     *
     * @param array $data
     */
    public static function ajaxReply($data)
    {
        Utils::sendJSON($data);
    }

    /**
     * Reply with the form HTML
     *
     * @param array $postData - submitted data
     * @param array $data - extra data to pass
     * @param string $view - view template to output
     */
    public function ajaxReplyHandler($postData = [], $data = [], $view = 'router')
    {
        self::ajaxReply(array_replace(
            ['content' => Template::html($view, $postData, ['echo' => false])],
            $data
        ));
    }

    /**
     * Add error notice and reply with the form HTML
     *
     * @param \Exception $exception
     * @param array $postData
     * @param string $view - view template to output
     */
    public function ajaxErrorHandler($exception, $postData = [], $view = 'router')
    {
        $postData = array_replace(['id' => null], $postData);
        $this->addNotice(
            $exception->getCode() ?: self::getActiveStepId($postData['id']),
            [
                'view' => 'custom',
                'message' => $exception->getMessage()
            ]
        );

        $this->ajaxReplyHandler(
            $postData,
            [
                'hasError' => true,
                'message' => $exception->getMessage()
            ],
            $view
        );
    }
    // </editor-fold>

    // <editor-fold desc="Notices">
    /**
     * Add notice by type into a queue
     *
     * @param string $stepId
     * @param array{view: string, message: string} $massageData
     */
    public function addNotice($stepId, $massageData)
    {
        $this->notices[$stepId][] = $massageData;
    }

    /**
     * Return the queue of notices
     *
     * @param string $stepId - try to get messages from one step by id or output all of messages
     *
     * @return array
     */
    public function getNotices($stepId = null)
    {
        if ($stepId) {
            // return step's messages array or nothing
            return isset($this->notices[$stepId]) ? $this->notices[$stepId] : [];
        } else {
            // return all steps messages
            return array_reduce($this->notices, 'array_merge', []);
        }
    }

    /**
     * Add successful add-to-cart action notice
     *
     * @param array $args
     * @param array $cart
     * @param array $output
     */
    public function addNoticesAfterAddAllToMainCart($args, $cart, $output)
    {
        $args = array_replace(['id' => null], $args);
        $id = (int) $args['id'];

        if (Wizard::getSetting($id, 'group_products_into_kits')
            && Wizard::getSetting($id, 'kits_type') == 'combined'
        ) {
            // output kit root product name only
            foreach ($output as $productData) {
                if (!empty($productData['data']['wcpw_is_kit_root'])) {
                    $message = [$productData['product_id'] => $productData['quantity']];
                }
            }
        } elseif (!empty($output)) {
            // output all products
            $message = [];

            foreach ($output as $productData) {
                $message[$productData['product_id']] = $productData['quantity'];
            }
        }

        if (!empty($message)) {
            $this->addNotice(
                self::getActiveStepId($id),
                [
                    'view' => 'custom',
                    'type' => 'message',
                    'message' => wc_add_to_cart_message($message, true, true)
                ]
            );
        }
    }
    // </editor-fold>

    // <editor-fold desc="Check rules">
    /**
     * Get a result value of a products min/max quantities rule
     *
     * @param integer $wizardId
     * @param array $rule
     *
     * @return integer
     */
    public static function getQuantityRuleValue($wizardId, $rule)
    {
        $output = 0;

        switch ($rule['type']) {
            case 'selected-from-step': {
                foreach (Cart::get($wizardId, ['includeSteps' => wp_parse_id_list($rule['value'])]) as $cartItem) {
                    if (isset($cartItem['product_id'])) {
                        $output++;
                    }
                }

                break;
            }

            default:
            case 'count':
                $output = (float) $rule['value'];
        }

        return apply_filters('wcpw_step_quantities_rule', (float) $output, $wizardId, $rule);
    }

    /**
     * Check step quantities and other rules
     *
     * @param array $args
     * @param string $stepId
     *
     * @throws \Exception
     */
    public static function checkStepRules($args, $stepId)
    {
        $defaults = [
            'id' => null,
            'checkMinProductsSelected' => true,
            'checkMaxProductsSelected' => true,
            'checkMinTotalProductsQuantity' => true,
            'checkMaxTotalProductsQuantity' => true,
            'productsToAdd' => [],
            'productsToAddChecked' => []
        ];

        $args = array_merge($defaults, $args);
        $totalQuantity = 0;
        $selectedCount = !isset($args['productsToAddChecked'][$stepId])
            || !is_array($args['productsToAddChecked'][$stepId])
            ? 0
            : count(array_filter($args['productsToAddChecked'][$stepId]));

        foreach ((array) $args['productsToAdd'] as $product) {
            if (!isset($product['step_id']) || $product['step_id'] != $stepId // wrong step ID
                || !isset($args['productsToAddChecked'][$stepId])
                || !is_array($args['productsToAddChecked'][$stepId]) // step have no selected products
                || (isset($product['product_id'])
                    && !in_array($product['product_id'], $args['productsToAddChecked'][$stepId]))
                    // step have no this product as selected
            ) {
                continue;
            }

            $totalQuantity += isset($product['quantity']) ? $product['quantity'] : 1;
        }

        // min products selected check
        if ($args['checkMinProductsSelected']) {
            $setting = WizardStep::getSetting($args['id'], $stepId, 'min_products_selected');

            if (!empty($setting['value'])) {
                $value = self::getQuantityRuleValue($args['id'], $setting);

                if ($value && $selectedCount < $value) {
                    $message = Wizard::getMinimumProductsSelectedMessage($args['id'], $value, $selectedCount);

                    throw new \Exception(wp_kses_post($message), (int) $stepId);
                }
            }
        }

        // max products selected check
        if ($args['checkMaxProductsSelected']) {
            $setting = WizardStep::getSetting($args['id'], $stepId, 'max_products_selected');

            if (!empty($setting['value'])) {
                $value = self::getQuantityRuleValue($args['id'], $setting);

                if ($value && $selectedCount > $value) {
                    $message = Wizard::getMaximumProductsSelectedMessage($args['id'], $value, $selectedCount);

                    throw new \Exception(wp_kses_post($message), (int) $stepId);
                }
            }
        }

        // min total products selected check
        if ($args['checkMinTotalProductsQuantity']) {
            $setting = WizardStep::getSetting($args['id'], $stepId, 'min_total_products_quantity');

            if ($setting && !empty($setting['value'])) {
                $value = self::getQuantityRuleValue($args['id'], $setting);

                if ($value && $totalQuantity < $value) {
                    $message = Wizard::getMinimumProductsSelectedMessage($args['id'], $value, $totalQuantity);

                    throw new \Exception(wp_kses_post($message), (int) $stepId);
                }
            }
        }

        // max total products quantity check
        if ($args['checkMaxTotalProductsQuantity']) {
            $setting = WizardStep::getSetting($args['id'], $stepId, 'max_total_products_quantity');

            if ($setting && !empty($setting['value'])) {
                $value = self::getQuantityRuleValue($args['id'], $setting);

                if ($value && $totalQuantity > $value) {
                    $message = Wizard::getMaximumProductsSelectedMessage($args['id'], $value, $totalQuantity);

                    throw new \Exception(wp_kses_post($message), (int) $stepId);
                }
            }
        }
    }

    /**
     * Check form nonce
     *
     * @param array $args
     *
     * @throws \Exception
     */
    public static function checkNonce($args = [])
    {
        $defaults = [
            'step_id' => null,
            'nonce' => null
        ];

        $args = array_replace($defaults, (array) $args);

        if (empty($args['nonce']) || !wp_verify_nonce($args['nonce'], 'wcpw')) {
            throw new \Exception(wp_kses_post('Nonce error. Please, try to refresh the page.'), (int) $args['step_id']);
        }
    }

    /**
     * Check quantity and price rules of wizard and steps
     *
     * @param array $args
     * @param array $cart
     *
     * @throws \Exception
     */
    public static function checkCartRules($args, $cart)
    {
        $args = array_replace(['id' => null], $args);
        $id = (int) $args['id'];

        // check steps level rules
        $qtyCheckArgs = [
            'productsToAddChecked' => [],
            'productsToAdd' => []
        ];

        $qtyCheckArgs = self::getCartQuantitiesToCheck($id, $qtyCheckArgs);

        foreach (self::getStepsIds($id) as $stepId) {
            self::checkStepRules($qtyCheckArgs, $stepId);
        }

        // check wizard level rules
        $totalProductsQuantity = 0;
        $productsSelectedCount = 0;

        foreach ((array) $cart as $cartItem) {
            if (!isset($cartItem['quantity'])) {
                continue;
            }

            $productsSelectedCount++;
            $totalProductsQuantity += $cartItem['quantity'];
        }

        // min products selected check
        $limit = Wizard::getSetting($id, 'min_products_selected');

        if ($limit && $productsSelectedCount < $limit) {
            $message = Wizard::getMinimumProductsSelectedMessage($id, $limit, $productsSelectedCount);

            throw new \Exception(wp_kses_post($message));
        }

        // max products selected check
        $limit = Wizard::getSetting($id, 'max_products_selected');

        if ($limit && $productsSelectedCount > $limit) {
            $message = Wizard::getMaximumProductsSelectedMessage($id, $limit, $productsSelectedCount);

            throw new \Exception(wp_kses_post($message));
        }

        // min total products quantity check
        $limit = Wizard::getSetting($id, 'min_total_products_quantity');

        if ($limit && $totalProductsQuantity < $limit) {
            $message = Wizard::getMinimumProductsSelectedMessage($id, $limit, $totalProductsQuantity);

            throw new \Exception(wp_kses_post($message));
        }

        // max total products quantity check
        $limit = Wizard::getSetting($id, 'max_total_products_quantity');

        if ($limit && $totalProductsQuantity > $limit) {
            $message = Wizard::getMaximumProductsSelectedMessage($id, $limit, $totalProductsQuantity);

            throw new \Exception(wp_kses_post($message));
        }
    }

    /**
     * Blend in cart quantities to check the rules
     *
     * @param integer $wizardId
     * @param array $qtyCheckArgs
     *
     * @return array
     */
    public static function getCartQuantitiesToCheck($wizardId, $qtyCheckArgs)
    {
        $cart = Cart::get($wizardId);
        $productsToAdd = (array) $qtyCheckArgs['productsToAdd'];
        $qtyCheckArgs['productsToAdd'] = [];

        // convert form input keys to hash keys
        foreach ($productsToAdd as $data) {
            $cartKey = Cart::generateProductId($data);
            $qtyCheckArgs['productsToAdd'][$cartKey] = $data;
        }

        foreach ($cart as $cartItem) {
            if (!isset($cartItem['product_id'])) {
                continue;
            }

            $cartKey = Cart::generateProductId($cartItem);

            // have no this product passed - take it from the cart
            if (!isset($qtyCheckArgs['productsToAdd'][$cartKey])) {
                $qtyCheckArgs['productsToAdd'][$cartKey] = $cartItem;
                $qtyCheckArgs['productsToAddChecked'][$cartItem['step_id']][] = $cartItem['product_id'];
            }
        }

        return $qtyCheckArgs;
    }
    // </editor-fold>

    // <editor-fold desc="Main actions">
    /**
     * Handles form submit
     *
     * @param array $args
     *
     * @return boolean
     *
     * @throws \Exception
     */
    public static function submit($args)
    {
        $defaults = [
            'id' => null, // wizard ID
            'stepId' => null,
            'incrementActiveStep' => true,
            'dropNotCheckedProducts' => true,
            'passProducts' => true,
            'productsToAdd' => [],
            'productsToAddChecked' => []
        ];

        $args = array_merge($defaults, $args);
        $notCheckedProductsIds = [];

        do_action('wcpw_before_submit_form', $args);

        // make it an array for sure
        $args['productsToAddChecked'] = $args['productsToAddChecked'] == '[]'
            ? []
            : (array) $args['productsToAddChecked'];

        $stepsIds = array_unique(array_filter(array_keys($args['productsToAddChecked'])));
        $allStepsIds = self::getStepsIds($args['id'], ['checkAvailabilityRules' => false]);
        $qtyCheckArgs = self::getCartQuantitiesToCheck($args['id'], $args);

        if (is_array($args['productsToAdd']) && !empty($args['productsToAdd'])) {
            foreach ($args['productsToAdd'] as $key => $data) {
                if (!is_array($data) || !isset($data['step_id'], $data['product_id'])) {
                    continue;
                }

                // emulate product selection for positive quantities according to the setting
                if (WizardStep::getSetting($args['id'], $data['step_id'], 'add_to_cart_by_quantity')
                    && isset($data['quantity']) && (float) $data['quantity'] > 0
                ) {
                    $args['productsToAddChecked'][$data['step_id']][] = $data['product_id'];
                    $qtyCheckArgs['productsToAddChecked'][$data['step_id']][] = $data['product_id'];
                }

                if (isset($args['productsToAddChecked'][$data['step_id']])
                    && is_array($args['productsToAddChecked'][$data['step_id']])
                    && in_array($data['product_id'], $args['productsToAddChecked'][$data['step_id']])
                ) {
                    continue;
                }

                // collect product as not-checked and remove it from all args
                $notCheckedProductsIds[$data['step_id']][] = $data['product_id'];

                unset($args['productsToAdd'][$key]);

                $cartKey = Cart::generateProductId($data);

                if (isset($qtyCheckArgs['productsToAdd'][$cartKey])) {
                    unset($qtyCheckArgs['productsToAdd'][$cartKey]);
                }
            }
        }

        // need to check step rules before any actions
        foreach ($stepsIds as $stepId) {
            self::checkStepRules($qtyCheckArgs, $stepId);
        }

        foreach ($stepsIds as $stepId) {
            if (!WizardStep::getSetting($args['id'], $stepId, 'several_products')) {
                Cart::removeByStepId($args['id'], $stepId, ['removeStepData' => false]);
            }

            if ($args['dropNotCheckedProducts'] && !empty($notCheckedProductsIds)) {
                foreach ($notCheckedProductsIds as $stepId => $productsIds) {
                    foreach ($productsIds as $productId) {
                        Cart::removeByProductId($args['id'], $productId, $stepId);
                    }
                }
            }

            if (Wizard::getSetting($args['id'], 'strict_cart_workflow')) {
                // remove products from the next steps
                $skip = true;

                foreach ($allStepsIds as $allStepId) {
                    if (!$skip) {
                        Cart::removeByStepId($args['id'], $allStepId);
                    }

                    if ((string) $allStepId == (string) $stepId) {
                        $skip = false;
                    }
                }
            }
        }

        if ($args['passProducts'] && is_array($args['productsToAdd']) && !empty($args['productsToAdd'])) {
            foreach ($args['productsToAdd'] as $data) {
                if (!is_array($data)) {
                    continue;
                }

                $defaultData = [
                    'product_id' => null,
                    'variation_id' => null,
                    'variation' => [],
                    'quantity' => 1,
                    'step_id' => null,
                    'data' => [],
                    'request' => []
                ];

                $data = array_replace($defaultData, $data);

                // if product isn't selected
                if (!($data['product_id'] && $data['step_id'] && $data['quantity'])
                    || !isset($args['productsToAddChecked'][$data['step_id']])
                    || !in_array($data['product_id'], $args['productsToAddChecked'][$data['step_id']])
                ) {
                    continue;
                }

                // find variation ID if necessary
                if (!empty($data['variation']) && !$data['variation_id']) {
                    $product = wc_get_product($data['product_id']);

                    if (!$product instanceof \WC_Product_Variable) {
                        continue;
                    }

                    $variations = $product->get_available_variations();
                    $excludedProductsIds = (array) WizardStep::getSetting($args['id'], $data['step_id'], 'excluded_products');

                    foreach ($variations as $variationKey => $variation) {
                        if (in_array($variation['variation_id'], $excludedProductsIds)) {
                            unset($variations[$variationKey]);

                            continue;
                        }

                        $attributesMet = 0;

                        foreach ($variation['attributes'] as $attribute => $value) {
                            if (isset($data['variation'][$attribute])
                                && ($data['variation'][$attribute] == $value || $value == '')
                            ) {
                                $attributesMet++;
                            }
                        }

                        if (count($data['variation']) == $attributesMet) {
                            $data['variation_id'] = $variation['variation_id'];
                        }
                    }
                }

                try {
                    if (WizardStep::getSetting($args['id'], $data['step_id'], 'several_variations_per_product')
                        && $data['variation_id'] && !empty($data['variation'])
                    ) {
                        $key = Cart::getKeyByVariationData($args['id'], $data['variation_id'], $data['variation'], $data['step_id']); // phpcs:ignore
                    } else {
                        $key = Cart::getKeyByProductId($args['id'], $data['product_id'], $data['step_id']);
                    }

                    if ($key) {
                        Cart::removeByCartKey($args['id'], $key);
                    }

                    Cart::addProduct($args['id'], apply_filters('wcpw_submit_form_item_data', $data, $args));
                } catch (\Exception $exception) {
                    throw new \Exception(wp_kses_post($exception->getMessage()), (int) $data['step_id']);
                }
            }
        }

        // change active step
        if ($args['stepId']) {
            self::setActiveStepId($args['id'], $args['stepId']);
        } elseif (filter_var($args['incrementActiveStep'], FILTER_VALIDATE_BOOLEAN)) {
            self::setActiveStepId($args['id'], self::getNextStepId($args['id']));
        }

        // clear cart cache
        Cart::clearCache($args['id']);
        Utils::clearAvailabilityRulesCache($args['id']);

        do_action('wcpw_after_submit_form', $args);

        return true;
    }

    /** Handles form submit via ajax */
    public function submitAjax()
    {
        $postData = self::getPostVariable();

        try {
            self::submit($postData);
            $this->ajaxReplyHandler($postData);
        } catch (\Exception $exception) {
            $this->ajaxErrorHandler($exception, $postData);
        }
    }

    /**
     * Handles adding products to the cart
     *
     * @param array $args
     *
     * @return array - products added with keys
     *
     * @throws \Exception
     */
    public static function addToMainCart($args)
    {
        $defaults = [
            'id' => null,
            'stepId' => null,
            'incrementActiveStep' => false
        ];

        $args = array_merge($defaults, $args);
        $id = (int) $args['id'];
        $output = [];

        // don't pass step id to the submit method
        unset($args['stepId']);

        // submit step once again
        self::submit($args);
        $cart = Cart::get($id);
        $cart = apply_filters('wcpw_add_all_to_main_cart_items', $cart, $id);

        // fire action to check rules and other
        do_action('wcpw_before_add_all_to_main_cart', $args, $cart);

        if (Wizard::getSetting($id, 'clear_main_cart_on_confirm')) {
            // clear main cart
            WC()->cart->empty_cart();
        }

        // main work
        if (!empty($cart)) {
            $groupProductsIntoKits = Wizard::getSetting($id, 'group_products_into_kits');
            $kitsType = Wizard::getSetting($id, 'kits_type');
            $kitBaseProduct = Wizard::getSetting($id, 'kit_base_product');
            $kitId = null;
            $kitTitle = null;
            $isKitQuantityFixed = false;
            $rootKitItemCartKey = null;
            $rootKitItemKey = null;
            $rootKitItem = null;

            // define the root of the kit
            if ($groupProductsIntoKits) {
                $kitId = apply_filters('wcpw_kit_id', gmdate('d-m-Y H:i:s'), $id, $cart);

                // add pre-defined base product to the cart
                if ($kitBaseProduct) {
                    $productId = get_post_type($kitBaseProduct) != 'product'
                        ? wp_get_post_parent_id($kitBaseProduct)
                        : $kitBaseProduct;

                    $variationId = $productId != $kitBaseProduct ? $kitBaseProduct : '';
                    $variation = '';
                    $cartItemKey = WC()->cart->generate_cart_id($productId, $variationId, $variation);
                    $productData = [
                        'key' => $cartItemKey,
                        'step_id' => self::getFirstStepId($id),
                        'product_id' => $productId,
                        'variation_id' => $variationId,
                        'variation' => $variation,
                        'quantity' => 1,
                        'sold_individually' => 0,
                        'data' => wc_get_product($kitBaseProduct)
                    ];

                    $productData = apply_filters('wcpw_kit_base_product_data', $productData, $id, $cart);
                    $cart = [$cartItemKey => $productData] + $cart;
                }
            }

            foreach ($cart as $key => $cartItem) {
                $skipItems = (array) WizardStep::getSetting($id, $cartItem['step_id'], 'dont_add_to_cart_products');

                // should have a step ID and be not an excluded product/variation/step
                if (!isset($cartItem['step_id'], $cartItem['product_id'])
                    || (isset($cartItem['key'], $cartItem['value']) && empty($cartItem['value']))
                    || WizardStep::getSetting($id, $cartItem['step_id'], 'dont_add_to_cart')
                    || in_array($cartItem['product_id'], $skipItems)
                    || (!empty($cartItem['variation_id']) && in_array($cartItem['variation_id'], $skipItems))
                ) {
                    continue;
                }

                $productData = [
                    'product_id' => $cartItem['product_id'],
                    'quantity' => $cartItem['quantity'],
                    'variation_id' => isset($cartItem['variation_id']) ? $cartItem['variation_id'] : null,
                    'variation' => isset($cartItem['variation']) ? $cartItem['variation'] : [],
                    'data' => isset($cartItem['data']) && is_array($cartItem['data']) ? $cartItem['data'] : [],
                    'request' => isset($cartItem['request']) && is_array($cartItem['request'])
                        ? $cartItem['request']
                        : null
                ];

                $stepTitle = WizardStep::getSetting($id, $cartItem['step_id'], 'title');
                $productData['data']['wcpw_id'] = $id;
                $productData['data']['wcpw_step_id'] = $cartItem['step_id'];
                $productData['data']['wcpw_request'] = isset($cartItem['request']) ? $cartItem['request'] : [];
                $productData['data']['wcpw_step_name'] = $stepTitle ?: $cartItem['step_id'];

                // add kit data to the product
                if ($groupProductsIntoKits) {
                    $productData['data']['wcpw_kit_type'] = $kitsType;

                    // if the root item isn't defined yet make it from the first product
                    if (!$rootKitItem) {
                        $productData['data']['wcpw_kit_children'] = [];
                        $rootKitItem = $cartItem;
                        $rootKitItemKey = $key;
                        $kitTitle = apply_filters('wcpw_kit_title', get_the_title($rootKitItem['product_id']), $id, $cart); // phpcs:ignore

                        $isKitQuantityFixed = isset($cartItem['sold_individually'])
                            ? !$cartItem['sold_individually']
                            : !WizardStep::getSetting($id, $rootKitItem['step_id'], 'sold_individually');

                        // save info about pre-defined base product
                        if ($kitBaseProduct) {
                            $productData['data']['wcpw_is_base_kit_product'] = true;
                            $isKitQuantityFixed = false;
                        }

                        // check prices
                        if (!$kitBaseProduct && $kitsType == 'combined') {
                            // root product qty should be 1, but need to modify its price to keep the totals correct
                            $productData['data']['wcpw_kit_root_initial_qty'] = $productData['quantity'];
                            $productData['quantity'] = 1;
                            $isKitQuantityFixed = false;
                        }

                        // change the thumbnail
                        if (($thumbnailId = get_post_thumbnail_id($id)) && $thumbnailId) {
                            $productData['data']['wcpw_kit_thumbnail_id'] = $thumbnailId;
                        }

                        // collect children
                        foreach ($cart as $childKey => $child) {
                            $skipItems = (array) WizardStep::getSetting($id, $cartItem['step_id'], 'dont_add_to_cart_products');

                            // should have a step ID, be not an excluded product/variation/step or input field
                            if (!isset($child['step_id'])
                                || (isset($child['key'], $child['value']) && empty($child['value']))
                                || WizardStep::getSetting($id, $child['step_id'], 'dont_add_to_cart')
                                || (isset($child['product_id']) && in_array($child['product_id'], $skipItems))
                                || (!empty($child['variation_id']) && in_array($child['variation_id'], $skipItems))
                            ) {
                                continue;
                            }

                            // don't add itself
                            if ($rootKitItem && $childKey == $key) {
                                continue;
                            }

                            $productData['data']['wcpw_kit_children'][] = $child;
                        }
                    }

                    // is a child product
                    if ($rootKitItemKey != $key) {
                        $productData['data']['wcpw_kit_parent_key'] = $rootKitItemCartKey;

                        if ($kitsType == 'combined') {
                            $productData['data']['wcpw_is_hidden_product'] = true;
                        }
                    }

                    $productData['data']['wcpw_kit_id'] = $kitId;
                    $productData['data']['wcpw_kit_title'] = $kitTitle;
                    $productData['data']['wcpw_is_kit_root'] = (int) ($key == $rootKitItemKey);
                    $productData['data']['wcpw_is_kit_quantity_fixed'] = (int) $isKitQuantityFixed;
                }

                $productData = apply_filters('wcpw_main_cart_product_data', $productData, $id, $cartItem);

                try {
                    $cartItemKey = Product::addToMainCart($productData);

                    // save kit root product key
                    if ($groupProductsIntoKits && $cartItemKey && !$rootKitItemCartKey) {
                        $rootKitItemCartKey = $cartItemKey;
                    }

                    // save product data to output
                    if ($cartItemKey) {
                        $output[$cartItemKey] = $productData;
                    } else {
                        // drop all added products in case of exception
                        foreach (array_keys($output) as $outputKey) {
                            WC()->cart->remove_cart_item($outputKey);
                        }

                        foreach (wc_get_notices('error') as $notice) {
                            throw new \Exception(get_the_title($productData['product_id']) . ': ' . $notice['notice']);
                        }
                    }
                } catch (\Exception $exception) {
                    // drop all added products in case of exception
                    foreach (array_keys($output) as $outputKey) {
                        WC()->cart->remove_cart_item($outputKey);
                    }

                    throw new \Exception(wp_kses_post($exception->getMessage()));
                }
            }
        }

        // truncate the cart
        Cart::truncate($id);

        // reset active step to the first
        self::resetPreviousStepId($id);
        self::setActiveStepId($id, self::getFirstStepId($id));

        do_action('wcpw_after_add_all_to_main_cart', $args, $cart, $output);

        return $output;
    }

    /** Handles adding products to the cart via ajax */
    public function addToMainCartAjax()
    {
        $postData = self::getPostVariable();

        try {
            self::addToMainCart($postData);
            $this->ajaxReplyHandler(
                $postData,
                [
                    'finalRedirectURL' => Wizard::getFinalRedirectURL($postData['id']),
                    'content' => !empty($postData['getContent'])
                        ? Template::html('router', $postData, ['echo' => false])
                        : ''
                ]
            );
        } catch (\Exception $exception) {
            $this->ajaxErrorHandler($exception, $postData);
        }
    }

    /**
     * Request a step and change other states
     *
     * @param array $args
     *
     * @throws \Exception
     */
    public static function requestStep($args)
    {
        $defaults = [
            'id' => null,
            'stepId' => null,
            'cartTotalModifier' => null
        ];

        $args = array_merge($defaults, $args);

        do_action('wcpw_before_get_form', $args);

        // get the active step if step ID isn't passed
        if (is_null($args['stepId'])) {
            $args['stepId'] = self::getActiveStepId($args['id']);
        }

        if (Wizard::getSetting($args['id'], 'strict_cart_workflow')) {
            // remove products from the next steps
            $skip = true;

            foreach (self::getStepsIds($args['id']) as $stepId) {
                if (!$skip) {
                    Cart::removeByStepId($args['id'], $stepId);
                }

                if ($stepId == $args['stepId']) {
                    $skip = false;
                }
            }
        }

        self::resetPreviousStepId($args['id']);
        self::setActiveStepId($args['id'], $args['stepId']);

        do_action('wcpw_after_get_form', $args);
    }

    /** Request a step and change other states via ajax */
    public function requestStepAjax()
    {
        $postData = self::getPostVariable();

        try {
            self::requestStep($postData);
            $this->ajaxReplyHandler($postData);
        } catch (\Exception $exception) {
            $this->ajaxErrorHandler($exception, $postData);
        }
    }

    /** Request a step page and change other states via ajax */
    public function requestStepPageAjax()
    {
        $postData = self::getPostVariable();

        try {
            self::requestStep($postData);
            $this->ajaxReplyHandler($postData, [], 'body/step/index');
        } catch (\Exception $exception) {
            $this->ajaxErrorHandler($exception, $postData);
        }
    }

    /**
     * Reset step active step
     *
     * @param array $args
     *
     * @throws \Exception
     */
    public static function skipStep($args)
    {
        $args = array_replace(['id' => null], $args);

        do_action('wcpw_before_skip_form', $args);

        Cart::removeByStepId($args['id'], self::getActiveStepId($args['id']));
        self::setActiveStepId($args['id'], self::getNextStepId($args['id']));

        do_action('wcpw_after_skip_form', $args);
    }

    /** Reset step active step via ajax */
    public function skipStepAjax()
    {
        $postData = self::getPostVariable();

        try {
            self::skipStep($postData);
            $this->ajaxReplyHandler($postData);
        } catch (\Exception $exception) {
            $this->ajaxErrorHandler($exception, $postData);
        }
    }

    /**
     * Submit and skip active and other steps
     *
     * @param array $args
     *
     * @throws \Exception
     */
    public static function submitAndSkipAll($args)
    {
        $args = array_replace(['id' => null], $args);

        do_action('wcpw_before_submit_and_skip_all', $args);

        self::submit($args);

        // get steps after submit
        $stepsIds = self::getStepsIds($args['id']);

        self::setPreviousStepId($args['id'], self::getActiveStepId($args['id']));
        self::setActiveStepId($args['id'], end($stepsIds));

        do_action('wcpw_after_submit_and_skip_all', $args);
    }

    /** Submit and skip active and other steps via ajax */
    public function submitAndSkipAllAjax()
    {
        $postData = self::getPostVariable();

        try {
            self::submitAndSkipAll($postData);
            $this->ajaxReplyHandler($postData);
        } catch (\Exception $exception) {
            $this->ajaxErrorHandler($exception, $postData);
        }
    }

    /**
     * Skip active and other steps
     *
     * @param array $args
     *
     * @throws \Exception
     */
    public static function skipAll($args)
    {
        $args = array_replace(['id' => null], $args);

        do_action('wcpw_before_skip_all', $args);

        $stepsIds = self::getStepsIds($args['id']);

        self::setPreviousStepId($args['id'], self::getActiveStepId($args['id']));
        self::setActiveStepId($args['id'], end($stepsIds));

        do_action('wcpw_after_skip_all', $args);
    }

    /** Skip active and other steps via ajax */
    public function skipAllAjax()
    {
        $postData = self::getPostVariable();

        try {
            self::skipAll($postData);
            $this->ajaxReplyHandler($postData);
        } catch (\Exception $exception) {
            $this->ajaxErrorHandler($exception, $postData);
        }
    }

    /**
     * Reset cart and set the form to the first step
     *
     * @param array $args
     *
     * @throws \Exception
     */
    public static function reset($args)
    {
        $args = array_replace(['id' => null], $args);

        do_action('wcpw_before_reset_form', $args);

        Cart::truncate($args['id']);
        self::resetPreviousStepId($args['id']);
        self::setActiveStepId($args['id'], self::getFirstStepId($args['id']));
        self::getNavItems($args['id'], false); // invalidate nav cache - issues occurs sometimes

        do_action('wcpw_after_reset_form', $args);
    }

    /** Reset cart and set the form to the first step via ajax */
    public function resetAjax()
    {
        $postData = array_replace(['id' => null], self::getPostVariable());

        // unset for sure because this leads to a wrong router view
        unset($postData['stepId']);

        try {
            self::reset($postData);
            $this->ajaxReplyHandler($postData);
        } catch (\Exception $exception) {
            $this->ajaxErrorHandler($exception, $postData);
        }
    }
    // </editor-fold>

    // <editor-fold desc="Product actions">
    /**
     * Add product to the cart
     *
     * @param array $args
     *
     * @return boolean|array
     *
     * @throws \Exception
     */
    public static function addCartProduct($args)
    {
        $defaults = [
            'id' => null,
            'productToAddKey' => null,
            'productsToAdd' => [],
            'incrementActiveStep' => false,
            'dropNotCheckedProducts' => false,
            'checkMinProductsSelected' => false,
            'checkMinTotalProductsQuantity' => false,
            'passStepData' => false
        ];

        $args = array_merge($defaults, $args);
        $args['productsToAdd'] = (array) $args['productsToAdd'];
        $productToAdd = reset($args['productsToAdd']);
        $stepId = !empty($productToAdd['step_id']) ? $productToAdd['step_id'] : null;
        $behavior = WizardStep::getSetting($args['id'], $stepId, 'add_to_cart_behavior');

        if ($args['productToAddKey'] && isset($args['productsToAdd'][$args['productToAddKey']])) {
            $productData = $args['productsToAdd'][$args['productToAddKey']];
            $args['productsToAddChecked'] = [$productData['step_id'] => [$productData['product_id']]];
        }

        if ($behavior == 'submit') {
            // set active step once again to go to the real next step
            self::setActiveStepId($args['id'], $stepId);
            $args['incrementActiveStep'] = true;
        } elseif ($behavior == 'add-to-main-cart') {
            do_action('wcpw_before_add_cart_product', $args);

            return self::addToMainCart($args);
        }

        do_action('wcpw_before_add_cart_product', $args);

        return self::submit($args);
    }

    /**
     * Add product to the cart via ajax
     *
     * @throws \Exception
     */
    public function addCartProductAjax()
    {
        $postData = self::getPostVariable();

        try {
            self::addCartProduct($postData);
            $this->ajaxReplyHandler($postData);
        } catch (\Exception $exception) {
            $this->ajaxErrorHandler($exception, $postData);
        }
    }

    /**
     * Remove product from the cart
     *
     * @param array $args
     *
     * @throws \Exception
     */
    public static function removeCartProduct($args)
    {
        $defaults = [
            'id' => null,
            'productCartKey' => null
        ];

        $args = array_merge($defaults, $args);
        $cart = Cart::get($args['id']);
        $product = isset($cart[$args['productCartKey']]) ? $cart[$args['productCartKey']] : null;

        if ($product && $product['step_id'] != self::getActiveStepId($args['id'])) {
            // collect all other cart products from the same step to check minimum quantities rules
            $qtyCheckArgs = [
                'id' => $args['id'],
                'productsToAdd' => [],
                'productsToAddChecked' => []
            ];

            foreach (Cart::getByStepId($args['id'], $product['step_id']) as $cartItem) {
                if (!isset($cartItem['product_id']) || $cartItem['product_id'] == $product['product_id']) {
                    continue;
                }

                $qtyCheckArgs['productsToAddChecked'][$cartItem['step_id']][] = $cartItem['product_id'];
                $qtyCheckArgs['productsToAdd'][Cart::generateProductId($cartItem)] = [
                    'product_id' => $cartItem['product_id'],
                    'step_id' => $cartItem['step_id'],
                    'quantity' => $cartItem['quantity']
                ];
            }

            self::checkStepRules($qtyCheckArgs, $product['step_id']);
        }

        do_action('wcpw_before_remove_cart_product', $args);

        Cart::removeByCartKey($args['id'], $args['productCartKey']);
    }

    /** Remove product from the cart via ajax */
    public function removeCartProductAjax()
    {
        $postData = self::getPostVariable();

        try {
            self::removeCartProduct($postData);
            $this->ajaxReplyHandler($postData);
        } catch (\Exception $exception) {
            $this->ajaxErrorHandler($exception, $postData);
        }
    }

    /**
     * Update product in the cart
     *
     * @param array $args
     *
     * @return boolean
     *
     * @throws \Exception
     */
    public static function updateCartProduct($args)
    {
        $defaults = [
            'id' => null,
            'productCartKey' => null
        ];

        $args = array_merge($defaults, $args);
        $product = Cart::getItemByKey($args['id'], $args['productCartKey']);
        $args['productToAddKey'] = $product ? "{$product['step_id']}-{$product['product_id']}" : null;

        do_action('wcpw_before_update_cart_product', $args);

        Cart::removeByCartKey($args['id'], $args['productCartKey']);

        return self::addCartProduct($args);
    }

    /** Update product in the cart via ajax */
    public function updateCartProductAjax()
    {
        $postData = self::getPostVariable();

        try {
            self::updateCartProduct($postData);
            $this->ajaxReplyHandler($postData);
        } catch (\Exception $exception) {
            $this->ajaxErrorHandler($exception, $postData);
        }
    }
    // </editor-fold>

    // <editor-fold desc="Steps">
    /**
     * Get an array of the steps IDs with other settings applied
     *
     * @param integer $wizardId
     * @param array $args
     *
     * @return string[]
     */
    public static function getStepsIds($wizardId, $args = [])
    {
        // have no cache because of the dynamic workflow
        $defaults = ['checkAvailabilityRules' => true];
        $args = array_merge($defaults, $args);
        $output = [];

        foreach (Wizard::getStepsIds($wizardId) as $stepId) {
            if ($args['checkAvailabilityRules']) {
                if (!Utils::getAvailabilityByRules(
                    $wizardId, WizardStep::getSetting($wizardId, $stepId, 'availability_rules'),
                    "step-$stepId"
                )) {
                    continue;
                }
            }

            $output[] = $stepId;
        }

        if (Wizard::getSetting($wizardId, 'enable_description_step')) {
            // add description tab
            array_unshift($output, 'start');
        }

        if (Wizard::getSetting($wizardId, 'enable_results_step')) {
            // add results tab
            $output[] = 'result';
        }

        return apply_filters('wcpw_steps_ids', $output, $wizardId, $args);
    }

    /**
     * Get an array of the steps which used in the wizard
     *
     * @param integer $wizardId
     * @param array $args
     *
     * @return array[]
     */
    public static function getSteps($wizardId, $args = [])
    {
        // have no cache because of the dynamic workflow
        $defaults = ['checkAvailabilityRules' => true];
        $args = array_merge($defaults, $args);
        $output = [];

        foreach (self::getStepsIds($wizardId, $args) as $stepId) {
            $output[$stepId] = self::getStep($wizardId, $stepId);
        }

        return apply_filters('wcpw_steps', $output, $wizardId, $args);
    }

    /**
     * Get step data
     *
     * @param integer $wizardId
     * @param integer|string $stepId
     * @param boolean $useCache
     *
     * @return array
     */
    public static function getStep($wizardId, $stepId, $useCache = true)
    {
        static $cache = [];

        // set global variables
        Instance()->wizard->setCurrentId($wizardId);
        Instance()->wizardStep->setCurrentId($stepId);

        if ($useCache && isset($cache[$wizardId][$stepId])) {
            return apply_filters('wcpw_step', $cache[$wizardId][$stepId], $wizardId, $stepId);
        }

        $output = [];

        if (is_numeric($stepId)) {
            $description = WizardStep::getSetting($wizardId, $stepId, 'description');
            $title = WizardStep::getSetting($wizardId, $stepId, 'title');
            $output = [
                'id' => $stepId,
                'title' => $title ?: $stepId,
                'navTitle' => WizardStep::getSetting($wizardId, $stepId, 'nav_title') ?: ($title ?: $stepId),
                'navSubtitle' => WizardStep::getSetting($wizardId, $stepId, 'nav_subtitle'),
                'thumbnail' => WizardStep::getSetting($wizardId, $stepId, 'thumbnail'),
                'description' => do_shortcode(wpautop($description))
            ];
        } elseif ($stepId == 'start') {
            $title = Wizard::getSetting($wizardId, 'description_step_title');
            $output = [
                'id' => 'start',
                'title' => $title,
                'navTitle' => $title,
                'navSubtitle' => Wizard::getSetting($wizardId, 'description_step_subtitle'),
                'thumbnail' => Wizard::getSetting($wizardId, 'description_step_thumbnail'),
                'description' => do_shortcode(wpautop(get_post_field('post_content', $wizardId)))
            ];
        } elseif ($stepId == 'result') {
            $title = Wizard::getSetting($wizardId, 'results_step_title');
            $output = [
                'id' => 'result',
                'title' => $title,
                'navTitle' => $title,
                'navSubtitle' => Wizard::getSetting($wizardId, 'results_step_subtitle'),
                'thumbnail' => Wizard::getSetting($wizardId, 'results_step_thumbnail'),
                'description' => do_shortcode(wpautop(Wizard::getSetting($wizardId, 'results_step_description')))
            ];
        }

        $cache[$wizardId][$stepId] = $output;

        return apply_filters('wcpw_step', $output, $wizardId, $stepId);
    }

    /**
     * Get active wizard step id from the session variable
     *
     * @param integer $wizardId
     *
     * @return string
     */
    public static function getActiveStepId($wizardId)
    {
        $stepsIds = self::getStepsIds($wizardId);
        $output = Storage::get(self::$activeStepsSessionKey, $wizardId);

        if (!$output || !in_array($output, $stepsIds)) {
            $output = self::getFirstStepId($wizardId);

            self::setActiveStepId($wizardId, $output);
        }

        return apply_filters('wcpw_active_step_id', $output, $wizardId);
    }

    /**
     * Set active wizard step ID to the session variable
     *
     * @param integer $wizardId
     * @param integer|string $stepId
     */
    public static function setActiveStepId($wizardId, $stepId)
    {
        Storage::set(self::$activeStepsSessionKey, $wizardId, $stepId);
    }

    /**
     * Get wizard first step ID
     *
     * @param integer $wizardId
     *
     * @return null|string
     */
    public static function getFirstStepId($wizardId)
    {
        $stepsIds = self::getStepsIds($wizardId);

        return !empty($stepsIds) ? reset($stepsIds) : null;
    }

    /**
     * Get the next active wizard step from the session variable
     *
     * @param integer $wizardId
     *
     * @return string|null
     */
    public static function getNextStepId($wizardId)
    {
        $stepsIds = self::getStepsIds($wizardId);
        $activeStep = self::getActiveStepId($wizardId);
        $fitSteps = [];
        $activeIsFound = false;

        foreach ($stepsIds as $stepId) {
            if ($activeIsFound) {
                $fitSteps[] = $stepId;
            }

            if ($stepId == $activeStep) {
                $activeIsFound = true;
            }
        }

        foreach ($fitSteps as $stepId) {
            return $stepId;
        }

        return null;
    }

    /**
     * Set the previous active wizard step id
     *
     * @param integer $wizardId
     * @param integer $value
     */
    public static function setPreviousStepId($wizardId, $value)
    {
        Storage::set(self::$previousStepsSessionKey, $wizardId, $value);
    }

    /**
     * Reset the previous active wizard step id
     *
     * @param integer $wizardId
     */
    public static function resetPreviousStepId($wizardId)
    {
        Storage::remove(self::$previousStepsSessionKey, $wizardId);
    }

    /**
     * Get the previous active wizard step id
     *
     * @param integer $wizardId
     * @param string $activeStep
     *
     * @return string|null
     */
    public static function getPreviousStepId($wizardId, $activeStep = null)
    {
        $value = Storage::get(self::$previousStepsSessionKey, $wizardId);

        if ($value) {
            return $value;
        }

        $stepsIds = self::getStepsIds($wizardId);
        $activeStep = !is_null($activeStep) ? $activeStep : self::getActiveStepId($wizardId);
        $fitSteps = [];

        foreach ($stepsIds as $stepId) {
            if ($stepId == $activeStep) {
                break;
            }

            $fitSteps[] = $stepId;
        }

        $fitSteps = array_reverse($fitSteps);

        foreach ($fitSteps as $stepId) {
            return $stepId;
        }

        return null;
    }
    // </editor-fold>

    // <editor-fold desc="Navigation">
    /**
     * Check previous step existence
     *
     * @param integer $wizardId
     *
     * @return boolean
     */
    public static function canGoBack($wizardId)
    {
        return self::getFirstStepId($wizardId) != self::getActiveStepId($wizardId);
    }

    /**
     * Check next step existence
     *
     * @param integer $wizardId
     *
     * @return boolean
     */
    public static function canGoForward($wizardId)
    {
        $stepsIds = self::getStepsIds($wizardId);

        return end($stepsIds) != self::getActiveStepId($wizardId);
    }

    /**
     * Get pagination items array
     *
     * @param array $args
     *
     * @return array
     */
    public static function getPaginationItems($args)
    {
        $output = [];
        $defaults = [
            'stepId' => null,
            'page' => 1,
            'productsQuery' => []
        ];

        $args = array_merge($defaults, $args);

        if (empty($args['productsQuery'])) {
            return [];
        }

        $paginationArgs = [
            'format' => '?wcpwPage[' . $args['stepId'] . ']=%#%',
            'base' => '%_%',
            'total' => $args['productsQuery']->max_num_pages,
            'current' => self::getStepPageValue($args['stepId'], $args['page']),
            'show_all' => false,
            'end_size' => 1,
            'mid_size' => 2,
            'prev_next' => true,
            'prev_text' => esc_html__('« Previous', 'products-wizard-lite-for-woocommerce'),
            'next_text' => esc_html__('Next »', 'products-wizard-lite-for-woocommerce'),
            'type' => 'array'
        ];

        $paginationArgs = apply_filters('wcpw_pagination_args', $paginationArgs, $args);

        $links = paginate_links($paginationArgs);

        foreach ((array) $links as $link) {
            // add custom classes
            $link = str_replace('page-numbers', 'page-numbers page-link', $link);

            // replace empty href
            $link = str_replace('href=""', 'href="?paged=1"', $link);
            $link = str_replace("href=''", 'href="?paged=1"', $link);

            preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $link, $result);

            if (!empty($result) && !empty($result['href'][0])) {
                $href = $result['href'][0];
                $linkParts = wp_parse_url($href);

                parse_str($linkParts['query'], $linkPartsQuery);

                // add custom attributes
                $link = str_replace(
                    ' href=',
                    ' data-component="wcpw-form-pagination-link" data-step-id="' . $args['stepId']
                    . '" data-page="'
                    . (isset($linkPartsQuery['wcpwPage'], $linkPartsQuery['wcpwPage'][$args['stepId']])
                        ? $linkPartsQuery['wcpwPage'][$args['stepId']] : 1)
                    . '" href=',
                    $link
                );
            }

            $output[] = [
                'class' => strpos($link, 'current') !== false ? 'active' : '',
                'innerHtml' => $link
            ];
        }

        return apply_filters('wcpw_pagination_items', $output, $args);
    }

    /**
     * Return nav tabs items array
     *
     * @param integer $wizardId
     * @param boolean $useCache
     *
     * @return array
     */
    public static function getNavItems($wizardId, $useCache = true)
    {
        static $cache = [];

        if ($useCache && isset($cache[$wizardId])) {
            return apply_filters('wcpw_nav_items', $cache[$wizardId], $wizardId);
        }

        $cartSteps = Cart::getStepsIds($wizardId);
        $output = self::getSteps($wizardId);
        $activeStepId = self::getActiveStepId($wizardId);
        $nextStepId = self::getNextStepId($wizardId);
        $previousStepId = self::getPreviousStepId($wizardId);
        $isPreviousStepIdDefined = Storage::exists(self::$previousStepsSessionKey, $wizardId);
        $navAction = Wizard::getSetting($wizardId, 'nav_action');
        $isStrictWalk = Wizard::getSetting($wizardId, 'mode') == 'step-by-step';
        $activeNavItem = null;
        $previousNavItem = null;

        foreach ($output as &$step) {
            $step['title'] = !empty($step['navTitle']) ? $step['navTitle'] : $step['title'];
            $step['subtitle'] = !empty($step['navSubtitle']) ? $step['navSubtitle'] : null;
            $step['selected'] = isset($cartSteps[$step['id']]);
            $step['state'] = $activeNavItem && $isStrictWalk ? 'disabled' : 'default';
            $step['class'] = $activeNavItem && $isStrictWalk ? 'disabled' : ($activeNavItem ? 'default' : 'past');
            $step['value'] = $step['id'];

            if ($activeStepId == $step['id']) {
                // active step
                $activeNavItem = $step['id'];
                $step['action'] = 'none';
                $step['state'] = 'active';
                $step['class'] = 'active';
                $step['value'] = $step['id'];
            } elseif ($nextStepId == $step['id']) {
                // next active step
                $step['action'] = $navAction == 'auto' ? 'submit' : $navAction;
                $step['state'] = 'next-active';
                $step['class'] = 'next-active';
                $step['value'] = $navAction == 'get-step' ? $step['id'] : ''; // empty is needed for step dependencies
            } else {
                // other steps
                if ($activeNavItem && $isStrictWalk) {
                    $step['action'] = 'none';
                } elseif ($navAction == 'auto') {
                    $step['action'] = $activeNavItem ? 'submit' : 'get-step';
                } else {
                    $step['action'] = $navAction;
                }
            }

            // if was "skip all" action
            if ($isStrictWalk && $isPreviousStepIdDefined) {
                if ($activeStepId == $step['id']) {
                    // active step
                    $step['action'] = 'none';
                    $step['state'] = 'active';
                    $step['class'] = 'active';
                } elseif ($previousStepId == $step['id']) {
                    // previous active step
                    $previousNavItem = $step['id'];
                    $step['action'] = 'get-step';
                    $step['state'] = 'default';
                    $step['class'] = 'last-active' . ($activeNavItem ? '' : ' past');
                } elseif (!$previousNavItem) {
                    // previous steps
                    $step['action'] = 'get-step';
                    $step['state'] = 'default';
                    $step['class'] = 'past';
                } else {
                    // other items
                    $step['action'] = 'none';
                    $step['state'] = 'disabled';
                    $step['class'] = 'disabled';
                }
            }

            if (!empty($step['thumbnail'])) {
                $step['class'] .= ' has-thumbnail';
            }
        }

        $cache[$wizardId] = $output;

        return apply_filters('wcpw_nav_items', $output, $wizardId);
    }
    // </editor-fold>

    // <editor-fold desc="Query arguments">
    /**
     * Get step order-by value from the request string
     *
     * @param integer|string $stepId
     *
     * @return string
     */
    public static function getStepOrderByValue($stepId)
    {
        $output = [];
        $request = self::getRequestVariable();

        if (isset($request['wcpwOrderBy'])) {
            if (is_string(wp_unslash($request['wcpwOrderBy']))) {
                parse_str(sanitize_text_field(wp_unslash($request['wcpwOrderBy'])), $output);
            } else {
                $output = map_deep(wp_unslash($request['wcpwOrderBy']), 'sanitize_text_field');
            }
        }

        return isset($output[$stepId]) ? $output[$stepId] : null;
    }

    /**
     * Get step page value from the request string
     *
     * @param integer|string $stepId
     * @param integer $default
     *
     * @return integer
     */
    public static function getStepPageValue($stepId, $default = 1)
    {
        $output = [];
        $request = self::getRequestVariable();

        if (isset($request['wcpwPage'])) {
            if (is_string(wp_unslash($request['wcpwPage']))) {
                parse_str(sanitize_text_field(wp_unslash($request['wcpwPage'])), $output);
            } else {
                $output = map_deep(wp_unslash($request['wcpwPage']), 'sanitize_text_field');
            }
        }

        return isset($output[$stepId]) ? (int) $output[$stepId] : (int) $default;
    }

    /**
     * Get escaped $_POST variable
     *
     * @return array
     */
    protected static function getPostVariable()
    {
        return Utils::parseArrayOfJSONs($_POST); // phpcs:disable WordPress.Security.NonceVerification.Missing
    }

    /**
     * Get escaped $_REQUEST variable
     *
     * @return array
     */
    protected static function getRequestVariable()
    {
        return $_REQUEST; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }
    // </editor-fold>
}
