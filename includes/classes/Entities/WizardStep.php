<?php
namespace WCProductsWizard\Entities;

use WCProductsWizard\DataBase;
use WCProductsWizard\Settings;
use WCProductsWizard\Utils;

/**
 * Wizard Step Class
 *
 * @class WizardStep
 * @version 2.0.0
 */
class WizardStep
{
    use \WCProductsWizard\Traits\Settings;

    // <editor-fold desc="Properties">
    /**
     * Current working wizard step ID
     * @var integer
     */
    protected $currentId = 0;

    /**
     * Namespace string
     * @var string
     */
    public static $namespace = 'wcpw_step';

    /**
     * Admin class instance
     * @var WizardStep\Admin
     */
    public $admin;
    // </editor-fold>

    // <editor-fold desc="Core">
    /** Class Constructor */
    public function __construct()
    {
        $this->initSettings();

        if (is_admin()) {
            $this->initAdmin();
        }
    }

    /** Init admin class */
    public function initAdmin()
    {
        if (!class_exists('\WCProductsWizard\Entities\WizardStep\Admin')) {
            require_once(__DIR__ . DIRECTORY_SEPARATOR . 'WizardStep' . DIRECTORY_SEPARATOR . 'Admin.php');
        }

        $this->admin = new WizardStep\Admin($this);
    }

    /**
     * Get class namespace property
     *
     * @return string
     */
    public function getNamespace()
    {
        return self::$namespace;
    }

    /**
     * Get current working wizard step ID
     *
     * @return integer
     */
    public function getCurrentId()
    {
        return $this->currentId;
    }

    /**
     * Set current working wizard step ID
     *
     * @param string $currentId
     */
    public function setCurrentId($currentId)
    {
        $this->currentId = $currentId;
    }
    // </editor-fold>

    // <editor-fold desc="Settings">
    /**
     * Get settings model array
     *
     * @return array
     */
    public static function getSettingsModel()
    {
        $controlsClassDescription = '<a href="' . WC_PRODUCTS_WIZARD_PLUGIN_URL
            . 'documentation/index.html#wizard-settings-controls" target="_blank">'
            . esc_html__('See documentation', 'products-wizard-lite-for-woocommerce') . '</a>';

        $output = [
            // <editor-fold desc="Basic">
            'title' => [
                'label' => esc_html__('Title', 'products-wizard-lite-for-woocommerce'),
                'key' => 'title',
                'type' => 'text',
                'default' => '',
                'group' => esc_html__('Basic', 'products-wizard-lite-for-woocommerce')
            ],
            'nav_title' => [
                'label' => esc_html__('Navigation title', 'products-wizard-lite-for-woocommerce'),
                'key' => 'nav_title',
                'type' => 'text',
                'default' => '',
                'description' => esc_html__('Navigation item title. Optional', 'products-wizard-lite-for-woocommerce'),
                'group' => esc_html__('Basic', 'products-wizard-lite-for-woocommerce')
            ],
            'nav_subtitle' => [
                'label' => esc_html__('Navigation subtitle', 'products-wizard-lite-for-woocommerce'),
                'key' => 'nav_subtitle',
                'type' => 'text',
                'default' => '',
                'group' => esc_html__('Basic', 'products-wizard-lite-for-woocommerce')
            ],
            'notes' => [
                'label' => esc_html__('Notes', 'products-wizard-lite-for-woocommerce'),
                'key' => 'notes',
                'type' => 'text',
                'default' => '',
                'description' => esc_html__('Step notes only for developers', 'products-wizard-lite-for-woocommerce'),
                'group' => esc_html__('Basic', 'products-wizard-lite-for-woocommerce')
            ],
            'thumbnail' => [
                'label' => esc_html__('Thumbnail', 'products-wizard-lite-for-woocommerce'),
                'key' => 'thumbnail',
                'type' => 'thumbnail',
                'default' => '',
                'separate' => true,
                'group' => esc_html__('Basic', 'products-wizard-lite-for-woocommerce')
            ],
            'description' => [
                'label' => esc_html__('Top description', 'products-wizard-lite-for-woocommerce'),
                'key' => 'description',
                'type' => 'editor',
                'default' => '',
                'inModal' => true,
                'group' => esc_html__('Basic', 'products-wizard-lite-for-woocommerce')
            ],
            // </editor-fold>
            // <editor-fold desc="Query">
            'categories' => [
                'label' => esc_html__('Categories for using', 'products-wizard-lite-for-woocommerce'),
                'key' => 'categories',
                'type' => 'wc-terms-search',
                'default' => [],
                'values' => [],
                'description' => esc_html__('Select categories to request products', 'products-wizard-lite-for-woocommerce'),
                'group' => esc_html__('Query', 'products-wizard-lite-for-woocommerce'),
            ],
            'included_products' => [
                'label' => esc_html__('Included products', 'products-wizard-lite-for-woocommerce'),
                'key' => 'included_products',
                'type' => 'wc-product-search',
                'default' => [],
                'description' => esc_html__('Define specific products to output', 'products-wizard-lite-for-woocommerce'),
                'group' => esc_html__('Query', 'products-wizard-lite-for-woocommerce')
            ],
            'availability_rules' => array_replace(Settings::getAvailabilityRulesModel(), ['separate' => true]),
            'order' => [
                'label' => esc_html__('Order', 'products-wizard-lite-for-woocommerce'),
                'key' => 'order',
                'type' => 'select',
                'default' => 'ASC',
                'values' => [
                    'ASC' => esc_html__('Ascending', 'products-wizard-lite-for-woocommerce'),
                    'DESC' => esc_html__('Descending', 'products-wizard-lite-for-woocommerce')
                ],
                'group' => esc_html__('Query', 'products-wizard-lite-for-woocommerce')
            ],
            'order_by' => [
                'label' => esc_html__('Order by', 'products-wizard-lite-for-woocommerce'),
                'key' => 'order_by',
                'type' => 'select',
                'default' => 'menu_order',
                'values' => [
                    'ID' => esc_html__('ID', 'products-wizard-lite-for-woocommerce'),
                    'author' => esc_html__('Author', 'products-wizard-lite-for-woocommerce'),
                    'name' => esc_html__('Name', 'products-wizard-lite-for-woocommerce'),
                    'date' => esc_html__('Date', 'products-wizard-lite-for-woocommerce'),
                    'modified' => esc_html__('Modified', 'products-wizard-lite-for-woocommerce'),
                    'rand' => esc_html__('Rand', 'products-wizard-lite-for-woocommerce'),
                    'comment_count' => esc_html__('Comment count', 'products-wizard-lite-for-woocommerce'),
                    'menu_order' => esc_html__('Menu order', 'products-wizard-lite-for-woocommerce'),
                    'post__in' => esc_html__('Included products', 'products-wizard-lite-for-woocommerce'),
                    'price' => esc_html__('Price', 'products-wizard-lite-for-woocommerce')
                ],
                'group' => esc_html__('Query', 'products-wizard-lite-for-woocommerce')
            ],
            'products_per_page' => [
                'label' => esc_html__('Products per page', 'products-wizard-lite-for-woocommerce'),
                'key' => 'products_per_page',
                'type' => 'number',
                'default' => 0,
                'min' => 0,
                'description' => esc_html__('Zero is equal infinity', 'products-wizard-lite-for-woocommerce'),
                'group' => esc_html__('Query', 'products-wizard-lite-for-woocommerce')
            ],
            // </editor-fold>
            // <editor-fold desc="Cart">
            'several_products' => [
                'label' => esc_html__('Can select multiple products', 'products-wizard-lite-for-woocommerce'),
                'key' => 'several_products',
                'type' => 'checkbox',
                'default' => false,
                'description' => esc_html__('Replace radio-inputs with checkboxes to select multiple products', 'products-wizard-lite-for-woocommerce'),
                'group' => esc_html__('Cart', 'products-wizard-lite-for-woocommerce')
            ],
            'sold_individually' => [
                'label' => esc_html__('Sold individually', 'products-wizard-lite-for-woocommerce'),
                'key' => 'sold_individually',
                'type' => 'checkbox',
                'default' => false,
                'description' => esc_html__('Hide products quantity input', 'products-wizard-lite-for-woocommerce'),
                'group' => esc_html__('Cart', 'products-wizard-lite-for-woocommerce')
            ],
            'min_products_selected' => [
                'label' => esc_html__('Minimum products selected', 'products-wizard-lite-for-woocommerce'),
                'key' => 'min_products_selected',
                'type' => 'group',
                'default' => [],
                'values' => Settings::getProductNumbersModel(),
                'showHeader' => true,
                'description' => esc_html__('Count of selected products NOT including their quantities', 'products-wizard-lite-for-woocommerce')
                    . '</br>'
                    . esc_html__('Define fixed value or steps IDs separated by a comma', 'products-wizard-lite-for-woocommerce'),
                'group' => esc_html__('Cart', 'products-wizard-lite-for-woocommerce')
            ],
            'max_products_selected' => [
                'label' => esc_html__('Maximum products selected', 'products-wizard-lite-for-woocommerce'),
                'key' => 'max_products_selected',
                'type' => 'group',
                'default' => [],
                'values' => Settings::getProductNumbersModel(),
                'showHeader' => true,
                'description' => esc_html__('Count of selected products NOT including their quantities', 'products-wizard-lite-for-woocommerce')
                    . '</br>'
                    . esc_html__('Define fixed value or steps IDs separated by a comma', 'products-wizard-lite-for-woocommerce'),
                'separate' => true,
                'group' => esc_html__('Cart', 'products-wizard-lite-for-woocommerce')
            ],
            // </editor-fold>
            // <editor-fold desc="Controls">
            'enable_add_to_cart_button' => [
                'label' => esc_html__('Enable "Add to cart" button', 'products-wizard-lite-for-woocommerce'),
                'key' => 'enable_add_to_cart_button',
                'type' => 'checkbox',
                'default' => false,
                'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
            ],
            'add_to_cart_behavior' => [
                'label' => esc_html__('"Add to cart" button behavior', 'products-wizard-lite-for-woocommerce'),
                'key' => 'add_to_cart_behavior',
                'type' => 'select',
                'default' => 'default',
                'values' => [
                    'default' => esc_html__('Stay on the same step', 'products-wizard-lite-for-woocommerce'),
                    'submit' => esc_html__('Go next', 'products-wizard-lite-for-woocommerce'),
                    'add-to-main-cart' => esc_html__('Add to main cart', 'products-wizard-lite-for-woocommerce')
                ],
                'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
            ],
            'add_to_cart_button_text' => [
                'label' => esc_html__('"Add to cart" button text', 'products-wizard-lite-for-woocommerce'),
                'key' => 'add_to_cart_button_text',
                'type' => 'text',
                'default' => esc_html__('Add to cart', 'products-wizard-lite-for-woocommerce'),
                'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
            ],
            'add_to_cart_button_class' => [
                'label' => esc_html__('"Add to cart" button class', 'products-wizard-lite-for-woocommerce'),
                'key' => 'add_to_cart_button_class',
                'type' => 'text',
                'default' => 'btn-primary btn-sm show-icon icon-left hide-text',
                'description' => $controlsClassDescription,
                'separate' => true,
                'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
            ],
            'enable_update_button' => [
                'label' => esc_html__('Enable "Update" button', 'products-wizard-lite-for-woocommerce'),
                'key' => 'enable_update_button',
                'type' => 'checkbox',
                'default' => false,
                'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
            ],
            'update_button_text' => [
                'label' => esc_html__('"Update" button text', 'products-wizard-lite-for-woocommerce'),
                'key' => 'update_button_text',
                'type' => 'text',
                'default' => esc_html__('Update', 'products-wizard-lite-for-woocommerce'),
                'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
            ],
            'update_button_class' => [
                'label' => esc_html__('"Update" button class', 'products-wizard-lite-for-woocommerce'),
                'key' => 'update_button_class',
                'type' => 'text',
                'default' => 'btn-primary btn-sm show-icon icon-left hide-text',
                'description' => $controlsClassDescription,
                'separate' => true,
                'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
            ],
            'enable_remove_button' => [
                'label' => esc_html__('Enable "Remove" button', 'products-wizard-lite-for-woocommerce'),
                'key' => 'enable_remove_button',
                'type' => 'checkbox',
                'default' => false,
                'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
            ],
            'remove_button_text' => [
                'label' => esc_html__('"Remove" button text', 'products-wizard-lite-for-woocommerce'),
                'key' => 'remove_button_text',
                'type' => 'text',
                'default' => esc_html__('Remove', 'products-wizard-lite-for-woocommerce'),
                'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
            ],
            'remove_button_class' => [
                'label' => esc_html__('"Remove" button class', 'products-wizard-lite-for-woocommerce'),
                'key' => 'remove_button_class',
                'type' => 'text',
                'default' => 'btn-danger btn-sm show-icon icon-left hide-text',
                'description' => $controlsClassDescription,
                'separate' => true,
                'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
            ],
            // </editor-fold>
            // <editor-fold desc="View">
            'template' => [
                'label' => esc_html__('Template', 'products-wizard-lite-for-woocommerce'),
                'key' => 'template',
                'type' => 'select',
                'default' => 'list',
                'values' => [],
                'separate' => true,
                'group' => esc_html__('View', 'products-wizard-lite-for-woocommerce')
            ],
            'grid_column_width' => [
                'label' => esc_html__('Grid column width', 'products-wizard-lite-for-woocommerce'),
                'key' => 'grid_column_width',
                'type' => 'group',
                'default' => [
                    'xxs' => '16rem',
                    'xs' => '16rem',
                    'sm' => '16rem',
                    'md' => '16rem',
                    'lg' => '16rem',
                    'xl' => '16rem',
                    'xxl' => '16rem'
                ],
                'values' => [
                    [
                        'label' => esc_html__('XXS', 'products-wizard-lite-for-woocommerce'),
                        'key' => 'xxs',
                        'type' => 'text',
                        'default' => '16rem'
                    ],
                    [
                        'label' => esc_html__('XS', 'products-wizard-lite-for-woocommerce'),
                        'key' => 'xs',
                        'type' => 'text',
                        'default' => '16rem'
                    ],
                    [
                        'label' => esc_html__('S', 'products-wizard-lite-for-woocommerce'),
                        'key' => 'sm',
                        'type' => 'text',
                        'default' => '16rem'
                    ],
                    [
                        'label' => esc_html__('M', 'products-wizard-lite-for-woocommerce'),
                        'key' => 'md',
                        'type' => 'text',
                        'default' => '16rem'
                    ],
                    [
                        'label' => esc_html__('L', 'products-wizard-lite-for-woocommerce'),
                        'key' => 'lg',
                        'type' => 'text',
                        'default' => '16rem'
                    ],
                    [
                        'label' => esc_html__('XL', 'products-wizard-lite-for-woocommerce'),
                        'key' => 'xl',
                        'type' => 'text',
                        'default' => '16rem'
                    ],
                    [
                        'label' => esc_html__('XXL', 'products-wizard-lite-for-woocommerce'),
                        'key' => 'xxl',
                        'type' => 'text',
                        'default' => '16rem'
                    ]
                ],
                'showHeader' => true,
                'description' => esc_html__('CSS value in px, rem, em, %, etc...', 'products-wizard-lite-for-woocommerce'),
                'group' => esc_html__('View', 'products-wizard-lite-for-woocommerce')
            ],
            'item_template' => [
                'label' => esc_html__('Product template', 'products-wizard-lite-for-woocommerce'),
                'key' => 'item_template',
                'type' => 'select',
                'default' => 'type-1',
                'values' => [],
                'after' => '<div class="wcpw-form-item-template-preview" '
                    . 'data-component="wcpw-form-item-template-preview" '
                    . 'data-src="' . WC_PRODUCTS_WIZARD_PLUGIN_URL . "assets/admin/images/item-template/" . '" '
                    . '></div>',
                'group' => esc_html__('View', 'products-wizard-lite-for-woocommerce')
            ],
            'item_variations_template' => [
                'label' => esc_html__('Product variation template', 'products-wizard-lite-for-woocommerce'),
                'key' => 'variations_type',
                'type' => 'select',
                'default' => 'select',
                'values' => [],
                'separate' => true,
                'group' => esc_html__('View', 'products-wizard-lite-for-woocommerce')
            ],
            'show_item_thumbnails' => [
                'label' => esc_html__('Show product thumbnails', 'products-wizard-lite-for-woocommerce'),
                'key' => 'show_thumbnails',
                'type' => 'checkbox',
                'default' => true,
                'group' => esc_html__('View', 'products-wizard-lite-for-woocommerce')
            ],
            'item_thumbnail_size' => [
                'label' => esc_html__('Thumbnail size', 'products-wizard-lite-for-woocommerce'),
                'key' => 'thumbnail_size',
                'type' => 'text',
                'default' => 'shop_catalog',
                'description' => esc_html__('Set width and height separated by a comma or use string value. For example thumbnail, medium, large', 'products-wizard-lite-for-woocommerce'), // phpcs:ignore
                'group' => esc_html__('View', 'products-wizard-lite-for-woocommerce')
            ],
            'enable_item_thumbnail_link' => [
                'label' => esc_html__('Enable thumbnail link', 'products-wizard-lite-for-woocommerce'),
                'key' => 'enable_thumbnail_link',
                'type' => 'checkbox',
                'default' => true,
                'group' => esc_html__('View', 'products-wizard-lite-for-woocommerce')
            ],
            'show_item_descriptions' => [
                'label' => esc_html__('Show product description', 'products-wizard-lite-for-woocommerce'),
                'key' => 'show_descriptions',
                'type' => 'checkbox',
                'default' => true,
                'group' => esc_html__('View', 'products-wizard-lite-for-woocommerce')
            ],
            'item_description_source' => [
                'label' => esc_html__('Description source', 'products-wizard-lite-for-woocommerce'),
                'key' => 'item_description_source',
                'type' => 'select',
                'default' => 'content',
                'values' => [
                    'content' => esc_html__('Product content', 'products-wizard-lite-for-woocommerce'),
                    'excerpt' => esc_html__('Product short description', 'products-wizard-lite-for-woocommerce'),
                    'none' => esc_html__('None', 'products-wizard-lite-for-woocommerce')
                ],
                'separate' => true,
                'group' => esc_html__('View', 'products-wizard-lite-for-woocommerce')
            ],
            'enable_item_title_link' => [
                'label' => esc_html__('Enable product title link', 'products-wizard-lite-for-woocommerce'),
                'key' => 'enable_title_link',
                'type' => 'checkbox',
                'default' => false,
                'group' => esc_html__('View', 'products-wizard-lite-for-woocommerce')
            ]
            // </editor-fold>
        ];

        return apply_filters(self::$namespace . '_settings_model', $output);
    }

    /**
     * Method to get a setting value from database
     *
     * @param integer $id
     * @param string $setting
     * @param boolean $single
     *
     * @return string|float|boolean|array
     */
    public static function getSettingValue($id, $setting, $single = true)
    {
        return DataBase\Entity::getMeta($id, $setting, $single);
    }

    /**
     * Method to set a setting value into database
     *
     * @param integer $id
     * @param string $setting
     * @param mixed $value
     *
     * @return integer|boolean
     */
    public static function setSettingValue($id, $setting, $value)
    {
        return DataBase\Entity::updateMeta($id, $setting, $value);
    }

    /**
     * Get one of wizard step setting
     *
     * @param integer $wizardId
     * @param integer|string $stepId
     * @param string $setting
     * @param mixed $default
     *
     * @return string|float|boolean|array
     */
    public static function getSetting($wizardId, $stepId, $setting, $default = null)
    {
        static $cache = [];

        if (isset($cache[$wizardId], $cache[$wizardId][$stepId], $cache[$wizardId][$stepId][$setting])) {
            return apply_filters('wcpw_step_setting', $cache[$wizardId][$stepId][$setting], $wizardId, $stepId, $setting); // phpcs:ignore
        }

        $model = self::getSettingsModel();

        if (!isset($model[$setting])) {
            $cache[$wizardId][$stepId][$setting] = $default;

            return apply_filters('wcpw_step_setting', $default, $wizardId, $stepId, $setting);
        }

        $meta = self::getSettings($wizardId);

        if ($meta && isset($meta[$stepId][$model[$setting]['key']])) {
            $value = Utils::handleSettingType($meta[$stepId][$model[$setting]['key']], $model[$setting]['type']);
            $cache[$wizardId][$stepId][$setting] = $value;

            return apply_filters('wcpw_step_setting', $value, $wizardId, $stepId, $setting);
        }

        if ($default) {
            $value = $default;
        } elseif (isset($model[$setting]['default'])) {
            $value = Utils::handleSettingType($model[$setting]['default'], $model[$setting]['type']);
        } else {
            $value = null;
        }

        $cache[$wizardId][$stepId][$setting] = $value;

        return apply_filters('wcpw_step_setting', $value, $wizardId, $stepId, $setting);
    }

    /**
     * Get steps settings record from DB
     *
     * @param integer $id
     * @param array $args
     *
     * @return array
     */
    public static function getSettings($id, $args = [])
    {
        static $cache = [];

        if (!isset($cache[$id])) {
            $cache[$id] = (array) self::getSettingValue($id, '_steps_settings');
        }

        return apply_filters('wcpw_steps_settings', $cache[$id], $id);
    }

    /**
     * Save wizard step settings
     *
     * @param array $args
     *
     * @throws \Exception if empty step or post id
     */
    public function saveSettings($args)
    {
        $postId = !empty($args['post_id']) ? (int) $args['post_id'] : null;
        $stepId = isset($args['step_id']) ? (int) $args['step_id'] : null;
        $nonce = !empty($args['_wcpw_nonce']) ? sanitize_key(wp_unslash($args['_wcpw_nonce'])) : null;

        if (is_null($stepId) || !$postId) {
            throw new \Exception(esc_html__('Empty step or post id', 'products-wizard-lite-for-woocommerce'));
        }

        if (!wp_verify_nonce($nonce, 'wcpw_' . $postId)) {
            throw new \Exception(esc_html__('Error while saving.', 'products-wizard-lite-for-woocommerce'));
        }

        if (!current_user_can('edit_post', $postId)) {
            throw new \Exception(esc_html__('Sorry, you are not allowed to edit this item.', 'products-wizard-lite-for-woocommerce'));
        }

        $applyAllSteps = null;
        $settings = self::getSettings($postId);

        if (!empty($args['values']['apply-all-steps']) && is_array($args['values']['apply-all-steps'])) {
            $applyAllSteps = $args['values']['apply-all-steps'];

            unset($args['values']['apply-all-steps']);
        }

        $settings[$stepId] = isset($args['values']) ? $args['values'] : [];

        if (!empty($applyAllSteps)) {
            // apply some settings to all steps
            $steps = Wizard::getStepsIds($postId);

            foreach ($steps as $step) {
                if ($stepId == $step) {
                    continue;
                }

                $settings[$step] = isset($settings[$step]) ? (array) $settings[$step] : [];

                foreach ($applyAllSteps as $key) {
                    if (!isset($args['values'][$key])) {
                        continue;
                    }

                    $settings[$step][$key] = $args['values'][$key];
                }
            }
        }

        self::setSettingValue($postId, '_steps_settings', $settings);
    }
    // </editor-fold>

    // <editor-fold desc="Settings shortcuts">
    /**
     * Get grid column width according to the step setting
     *
     * @param integer $wizardId
     *
     * @return bool
     */
    public static function getGridColumnWidth($wizardId, $stepId)
    {
        $output = (array) self::getSetting($wizardId, $stepId, 'grid_column_width');

        foreach ($output as $size => $value) {
            if ($value == '') {
                continue;
            }

            if (is_numeric($value)) {
                $output[$size] .= 'px';
            }
        }

        return apply_filters('wcpw_step_grid_column_width', $output, $wizardId);
    }
    // </editor-fold>
}
