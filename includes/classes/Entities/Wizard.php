<?php
namespace WCProductsWizard\Entities;

use WCProductsWizard\Cart;
use WCProductsWizard\Form;

/**
 * Wizard Class
 *
 * @class Wizard
 * @version 2.0.1
 */
class Wizard implements Interfaces\PostType
{
    use Traits\PostType;

    // <editor-fold desc="Properties">
    /**
     * Current working wizard ID
     * @var integer
     */
    protected $currentId = 0;

    /**
     * Post type string
     * @var string
     */
    public static $postType = 'wc_product_wizard';

    /**
     * Namespace string
     * @var string
     */
    public static $namespace = 'wcpw_post';
    // </editor-fold>

    // <editor-fold desc="Core">
    /** Class Constructor */
    public function __construct()
    {
        $this->initPostType();
    }

    /** Init admin class */
    public function initAdmin()
    {
        if (!class_exists('\WCProductsWizard\Entities\Wizard\Admin')) {
            require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Wizard' . DIRECTORY_SEPARATOR . 'Admin.php');
        }

        $this->admin = new Wizard\Admin($this);
    }

    /** Register entity post type */
    public function registerPostType()
    {
        register_post_type(
            self::$postType,
            apply_filters(
                'wcpw_post_type_args',
                [
                    'label' => esc_html__('Products Wizard for WooCommerce', 'products-wizard-lite-for-woocommerce'),
                    'labels' => [
                        'name' => esc_html__('Products Wizard for WooCommerce', 'products-wizard-lite-for-woocommerce'),
                        'singular_name' => esc_html__('Products Wizard for WooCommerce', 'products-wizard-lite-for-woocommerce'),
                        'menu_name' => esc_html__('Products Wizard', 'products-wizard-lite-for-woocommerce'),
                        'all_items' => esc_html__('Products Wizard', 'products-wizard-lite-for-woocommerce')
                    ],
                    'description' => esc_html__('This is where you can add new products wizard items.', 'products-wizard-lite-for-woocommerce'),
                    'public' => false,
                    'show_ui' => true,
                    'map_meta_cap' => true,
                    'publicly_queryable' => false,
                    'exclude_from_search' => true,
                    'show_in_menu' => current_user_can('manage_woocommerce') ? 'woocommerce' : true,
                    'hierarchical' => false,
                    'rewrite' => false,
                    'query_var' => false,
                    'supports' => [
                        'title',
                        'editor',
                        'thumbnail'
                    ],
                    'show_in_nav_menus' => false,
                    'show_in_admin_bar' => true
                ]
            )
        );
    }

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

        return apply_filters(
            self::$namespace . '_settings_model',
            [
                //<editor-fold desc="Basic">
                'steps_ids' => [
                    'key' => '_steps_ids',
                    'type' => 'array',
                    'group' => esc_html__('Basic', 'products-wizard-lite-for-woocommerce')
                ],
                //</editor-fold>
                // <editor-fold desc="Behavior">
                'mode' => [
                    'label' => esc_html__('Work mode', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_mode',
                    'type' => 'select',
                    'default' => 'step-by-step',
                    'values' => [
                        'free-walk' => esc_html__('Free walk', 'products-wizard-lite-for-woocommerce'),
                        'single-step' => esc_html__('Single step', 'products-wizard-lite-for-woocommerce'),
                    ],
                    'group' => esc_html__('Behavior', 'products-wizard-lite-for-woocommerce')
                ],
                'nav_action' => [
                    'label' => esc_html__('Navigation action', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_nav_action',
                    'type' => 'select',
                    'default' => 'auto',
                    'values' => [
                        'auto' => esc_html__('Auto', 'products-wizard-lite-for-woocommerce'),
                        'submit' => esc_html__('Submit', 'products-wizard-lite-for-woocommerce'),
                        'get-step' => esc_html__('Get step', 'products-wizard-lite-for-woocommerce'),
                        'none' => esc_html__('None', 'products-wizard-lite-for-woocommerce')
                    ],
                    'group' => esc_html__('Behavior', 'products-wizard-lite-for-woocommerce')
                ],
                'final_redirect_url' => [
                    'label' => esc_html__('Final redirect URL', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_final_redirect_url',
                    'type' => 'text',
                    'default' => get_permalink(function_exists('wc_get_page_id') ? wc_get_page_id('cart') : ''),
                    'description' => esc_html__('Open a page after the "Add to main cart" action', 'products-wizard-lite-for-woocommerce'), // phpcs:ignore
                    'group' => esc_html__('Behavior', 'products-wizard-lite-for-woocommerce')
                ],
                'scrolling_top_on_update' => [
                    'label' => esc_html__('Scrolling top on the form update', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_scrolling_top_on_update',
                    'type' => 'checkbox',
                    'default' => true,
                    'group' => esc_html__('Behavior', 'products-wizard-lite-for-woocommerce')
                ],
                'scrolling_up_gap' => [
                    'label' => esc_html__('The gap on scrolling up', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_scrolling_up_gap',
                    'type' => 'number',
                    'default' => 0,
                    'description' => 'px',
                    'separate' => true,
                    'group' => esc_html__('Behavior', 'products-wizard-lite-for-woocommerce')
                ],
                // </editor-fold>
                // <editor-fold desc="Cart">
                'strict_cart_workflow' => [
                    'label' => esc_html__('Strict cart workflow', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_strict_cart_workflow',
                    'type' => 'checkbox',
                    'default' => true,
                    'description' => esc_html__('Drop products from steps after the current', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Cart', 'products-wizard-lite-for-woocommerce')
                ],
                'group_products_into_kits' => [
                    'label' => esc_html__('Group products into kits', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_group_products_into_kits',
                    'type' => 'checkbox',
                    'default' => false,
                    'description' => esc_html__('Group products into kits after adding to the main cart', 'products-wizard-lite-for-woocommerce'), // phpcs:ignore
                    'group' => esc_html__('Cart', 'products-wizard-lite-for-woocommerce')
                ],
                'kits_type' => [
                    'label' => esc_html__('Kits type', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_kits_type',
                    'type' => 'select',
                    'default' => 'separated',
                    'values' => [
                        'separated' => esc_html__('Separated products', 'products-wizard-lite-for-woocommerce'),
                        'combined' => esc_html__('Combined product', 'products-wizard-lite-for-woocommerce')
                    ],
                    'group' => esc_html__('Cart', 'products-wizard-lite-for-woocommerce')
                ],
                // </editor-fold>
                // <editor-fold desc="Layout">
                'nav_template' => [
                    'label' => esc_html__('Navigation template', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_nav_template',
                    'type' => 'select',
                    'default' => 'tabs',
                    'values' => ['none' => esc_html__('None', 'products-wizard-lite-for-woocommerce')],
                    'description' => esc_html__('For modes with navigation', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Layout', 'products-wizard-lite-for-woocommerce')
                ],
                'nav_button_class' => [
                    'label' => esc_html__('Navigation button class', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_nav_button_class',
                    'type' => 'text',
                    'default' => 'btn-default btn-light',
                    'description' => esc_html__('Used for mobile nav and buttons template', 'products-wizard-lite-for-woocommerce') . '<br>'
                        . '<a href="https://getbootstrap.com/docs/5.0/components/buttons/" target="_blank">'
                        . esc_html__('Use any of the available styling classes for Buttons', 'products-wizard-lite-for-woocommerce')
                        . '</a>',
                    'separate' => true,
                    'group' => esc_html__('Layout', 'products-wizard-lite-for-woocommerce')
                ],
                'sidebar_position' => [
                    'label' => esc_html__('Sidebar position', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_sidebar_position',
                    'type' => 'select',
                    'default' => 'right',
                    'values' => [
                        'right' => esc_html__('Right', 'products-wizard-lite-for-woocommerce'),
                        'left' => esc_html__('Left', 'products-wizard-lite-for-woocommerce'),
                        'top' => esc_html__('Top', 'products-wizard-lite-for-woocommerce')
                    ],
                    'group' => esc_html__('Layout', 'products-wizard-lite-for-woocommerce')
                ],
                'sidebar_width' => [
                    'label' => esc_html__('Sidebar width', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_sidebar_width',
                    'type' => 'group',
                    'default' => [],
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
                    'description' => esc_html__('CSS value in px, rem, em, etc...', 'products-wizard-lite-for-woocommerce'),
                    'showHeader' => true,
                    'separate' => true,
                    'group' => esc_html__('Layout', 'products-wizard-lite-for-woocommerce')
                ],
                'show_widget' => [
                    'label' => esc_html__('Show cart widget', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_show_sidebar',
                    'type' => 'select',
                    'default' => 'not_empty_until_result_step',
                    'values' => [
                        'always' => esc_html__('Always', 'products-wizard-lite-for-woocommerce'),
                        'always_until_result_step' => esc_html__('Always until the results step', 'products-wizard-lite-for-woocommerce'),
                        'not_empty' => esc_html__('When isn\'t empty', 'products-wizard-lite-for-woocommerce'),
                        'not_empty_until_result_step' => esc_html__('When isn\'t empty and until the results step', 'products-wizard-lite-for-woocommerce'),
                        'never' => esc_html__('Never', 'products-wizard-lite-for-woocommerce')
                    ],
                    'group' => esc_html__('Layout', 'products-wizard-lite-for-woocommerce')
                ],
                'sticky_widget' => [
                    'label' => esc_html__('Sticky widget', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_sticky_widget',
                    'type' => 'select',
                    'default' => 'always',
                    'values' => [
                        'never' => esc_html__('Never', 'products-wizard-lite-for-woocommerce'),
                        'xxs' => esc_html__('XXS and smaller', 'products-wizard-lite-for-woocommerce'),
                        'xs' => esc_html__('XS and smaller', 'products-wizard-lite-for-woocommerce'),
                        'sm' => esc_html__('S and smaller', 'products-wizard-lite-for-woocommerce'),
                        'md' => esc_html__('M and smaller', 'products-wizard-lite-for-woocommerce'),
                        'lg' => esc_html__('L and smaller', 'products-wizard-lite-for-woocommerce'),
                        'xl' => esc_html__('XL and smaller', 'products-wizard-lite-for-woocommerce'),
                        'xxl' => esc_html__('XXL and smaller', 'products-wizard-lite-for-woocommerce'),
                        'up-xs' => esc_html__('Larger XS', 'products-wizard-lite-for-woocommerce'),
                        'up-sm' => esc_html__('Larger S', 'products-wizard-lite-for-woocommerce'),
                        'up-md' => esc_html__('Larger M', 'products-wizard-lite-for-woocommerce'),
                        'up-lg' => esc_html__('Larger L', 'products-wizard-lite-for-woocommerce'),
                        'up-xl' => esc_html__('Larger XL', 'products-wizard-lite-for-woocommerce'),
                        'always' => esc_html__('Always', 'products-wizard-lite-for-woocommerce')
                    ],
                    'description' => esc_html__('Select required screen size', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Layout', 'products-wizard-lite-for-woocommerce')
                ],
                'show_header' => [
                    'label' => esc_html__('Show header', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_show_header',
                    'type' => 'checkbox',
                    'default' => true,
                    'group' => esc_html__('Layout', 'products-wizard-lite-for-woocommerce')
                ],
                'show_footer' => [
                    'label' => esc_html__('Show footer', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_show_footer',
                    'type' => 'checkbox',
                    'default' => true,
                    'group' => esc_html__('Layout', 'products-wizard-lite-for-woocommerce')
                ],
                // </editor-fold>
                // <editor-fold desc="Steps">
                'enable_description_step' => [
                    'label' => esc_html__('Enable description step', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_enable_description_tab',
                    'type' => 'checkbox',
                    'default' => true,
                    'group' => esc_html__('Steps', 'products-wizard-lite-for-woocommerce')
                ],
                'description_step_title' => [
                    'label' => esc_html__('Description step title', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_description_tab_title',
                    'type' => 'text',
                    'default' => esc_html__('Welcome', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Steps', 'products-wizard-lite-for-woocommerce')
                ],
                'description_step_subtitle' => [
                    'label' => esc_html__('Description step subtitle', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_description_step_subtitle',
                    'type' => 'text',
                    'default' => '',
                    'group' => esc_html__('Steps', 'products-wizard-lite-for-woocommerce')
                ],
                'description_step_thumbnail' => [
                    'label' => esc_html__('Description step thumbnail', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_description_tab_thumbnail',
                    'type' => 'thumbnail',
                    'default' => '',
                    'separate' => true,
                    'group' => esc_html__('Steps', 'products-wizard-lite-for-woocommerce')
                ],
                'enable_results_step' => [
                    'label' => esc_html__('Enable results step', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_enable_results_tab',
                    'type' => 'checkbox',
                    'default' => true,
                    'group' => esc_html__('Steps', 'products-wizard-lite-for-woocommerce')
                ],
                'results_step_title' => [
                    'label' => esc_html__('Results step title', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_results_tab_title',
                    'type' => 'text',
                    'default' => esc_html__('Total', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Steps', 'products-wizard-lite-for-woocommerce')
                ],
                'results_step_subtitle' => [
                    'label' => esc_html__('Results step subtitle', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_results_step_subtitle',
                    'type' => 'text',
                    'default' => '',
                    'group' => esc_html__('Steps', 'products-wizard-lite-for-woocommerce')
                ],
                'results_step_thumbnail' => [
                    'label' => esc_html__('Results step thumbnail', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_results_tab_thumbnail',
                    'type' => 'thumbnail',
                    'default' => '',
                    'group' => esc_html__('Steps', 'products-wizard-lite-for-woocommerce')
                ],
                'results_step_description' => [
                    'label' => esc_html__('Results step description', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_results_tab_description',
                    'type' => 'editor',
                    'default' => '',
                    'group' => esc_html__('Steps', 'products-wizard-lite-for-woocommerce')
                ],
                'results_remove_string' => [
                    'label' => esc_html__('Results remove string', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_results_remove_string',
                    'type' => 'text',
                    'default' => esc_html__('Remove item', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Steps', 'products-wizard-lite-for-woocommerce')
                ],
                'results_thumbnail_string' => [
                    'label' => esc_html__('Results thumbnail string', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_results_thumbnail_string',
                    'type' => 'text',
                    'default' => esc_html__('Thumbnail image', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Steps', 'products-wizard-lite-for-woocommerce')
                ],
                'results_product_string' => [
                    'label' => esc_html__('Results product string', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_results_data_string',
                    'type' => 'text',
                    'default' => esc_html__('Product', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Steps', 'products-wizard-lite-for-woocommerce')
                ],
                'results_quantity_string' => [
                    'label' => esc_html__('Results quantity string', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_results_quantity_string',
                    'type' => 'text',
                    'default' => esc_html__('Quantity', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Steps', 'products-wizard-lite-for-woocommerce')
                ],
                'results_price_string' => [
                    'label' => esc_html__('Results price string', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_results_price_string',
                    'type' => 'text',
                    'default' => esc_html__('Price', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Steps', 'products-wizard-lite-for-woocommerce')
                ],
                // </editor-fold>
                // <editor-fold desc="Controls">
                'header_controls' => [
                    'label' => esc_html__('Header controls', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_header_controls',
                    'type' => 'multi-select',
                    'default' => [
                        'widget-toggle',
                        'spacer',
                        'start',
                        'reset',
                        'back',
                        'skip',
                        'next',
                        'to-results',
                        'add-to-cart'
                    ],
                    'values' => [
                        'widget-toggle' => esc_html__('Widget toggle', 'products-wizard-lite-for-woocommerce'),
                        'spacer' => esc_html__('Free space', 'products-wizard-lite-for-woocommerce'),
                        'spacer-2' => esc_html__('Free space', 'products-wizard-lite-for-woocommerce'),
                        'start' => esc_html__('Start', 'products-wizard-lite-for-woocommerce'),
                        'reset' => esc_html__('Reset', 'products-wizard-lite-for-woocommerce'),
                        'back' => esc_html__('Back', 'products-wizard-lite-for-woocommerce'),
                        'skip' => esc_html__('Skip', 'products-wizard-lite-for-woocommerce'),
                        'next' => esc_html__('Next', 'products-wizard-lite-for-woocommerce'),
                        'to-results' => esc_html__('To results', 'products-wizard-lite-for-woocommerce'),
                        'add-to-cart' => esc_html__('Add to cart', 'products-wizard-lite-for-woocommerce')
                    ],
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'footer_controls' => [
                    'label' => esc_html__('Footer controls', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_footer_controls',
                    'type' => 'multi-select',
                    'default' => [
                        'widget-toggle',
                        'spacer',
                        'start',
                        'reset',
                        'back',
                        'skip',
                        'next',
                        'to-results',
                        'add-to-cart'
                    ],
                    'values' => [
                        'widget-toggle' => esc_html__('Widget toggle', 'products-wizard-lite-for-woocommerce'),
                        'spacer' => esc_html__('Free space', 'products-wizard-lite-for-woocommerce'),
                        'spacer-2' => esc_html__('Free space', 'products-wizard-lite-for-woocommerce'),
                        'start' => esc_html__('Start', 'products-wizard-lite-for-woocommerce'),
                        'reset' => esc_html__('Reset', 'products-wizard-lite-for-woocommerce'),
                        'back' => esc_html__('Back', 'products-wizard-lite-for-woocommerce'),
                        'skip' => esc_html__('Skip', 'products-wizard-lite-for-woocommerce'),
                        'next' => esc_html__('Next', 'products-wizard-lite-for-woocommerce'),
                        'to-results' => esc_html__('To results', 'products-wizard-lite-for-woocommerce'),
                        'add-to-cart' => esc_html__('Add to cart', 'products-wizard-lite-for-woocommerce')
                    ],
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'start_button_text' => [
                    'label' => esc_html__('"Start" button text', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_start_button_text',
                    'type' => 'text',
                    'default' => esc_html__('Start', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'start_button_class' => [
                    'label' => esc_html__('"Start" button class', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_start_button_class',
                    'type' => 'text',
                    'default' => 'btn-primary',
                    'description' => $controlsClassDescription,
                    'separate' => true,
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'add_to_cart_button_text' => [
                    'label' => esc_html__('"Add to cart" button text', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_add_to_cart_button_text',
                    'type' => 'text',
                    'default' => esc_html__('Add to cart', 'products-wizard-lite-for-woocommerce'),
                    'description' =>
                        esc_html__('Use the [wcpw-cart-total-price] special shortcode to output the cart total price', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'add_to_cart_button_class' => [
                    'label' => esc_html__('"Add to cart" button class', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_add_to_cart_button_class',
                    'type' => 'text',
                    'default' => 'btn-danger',
                    'description' => $controlsClassDescription,
                    'separate' => true,
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'back_button_text' => [
                    'label' => esc_html__('"Back" button text', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_back_button_text',
                    'type' => 'text',
                    'default' => esc_html__('Back', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'back_button_class' => [
                    'label' => esc_html__('"Back" button class', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_back_button_class',
                    'type' => 'text',
                    'default' => 'btn-default btn-light show-icon-on-mobile icon-left hide-text-on-mobile',
                    'description' => $controlsClassDescription,
                    'separate' => true,
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'next_button_text' => [
                    'label' => esc_html__('"Next" button text', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_next_button_text',
                    'type' => 'text',
                    'default' => esc_html__('Next', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'next_button_class' => [
                    'label' => esc_html__('"Next" button class', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_next_button_class',
                    'type' => 'text',
                    'default' => 'btn-primary show-icon-on-mobile icon-right hide-text-on-mobile',
                    'description' => $controlsClassDescription,
                    'separate' => true,
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'reset_button_text' => [
                    'label' => esc_html__('"Reset" button text', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_reset_button_text',
                    'type' => 'text',
                    'default' => esc_html__('Reset', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'reset_button_class' => [
                    'label' => esc_html__('"Reset" button class', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_reset_button_class',
                    'type' => 'text',
                    'default' => 'btn-warning show-icon-on-mobile icon-right hide-text-on-mobile',
                    'description' => $controlsClassDescription,
                    'separate' => true,
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'skip_button_text' => [
                    'label' => esc_html__('"Skip" button text', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_skip_button_text',
                    'type' => 'text',
                    'default' => esc_html__('Skip', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'skip_button_class' => [
                    'label' => esc_html__('"Skip" button class', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_skip_button_class',
                    'type' => 'text',
                    'default' => 'btn-default btn-light show-icon-on-mobile icon-right hide-text-on-mobile',
                    'description' => $controlsClassDescription,
                    'separate' => true,
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'to_results_button_behavior' => [
                    'label' => esc_html__('"To results" button behavior', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_to_results_button_behavior',
                    'type' => 'select',
                    'default' => 'skip-all',
                    'values' => [
                        'skip-all' => esc_html__('Skip all', 'products-wizard-lite-for-woocommerce'),
                        'submit-and-skip-all' => esc_html__('Submit step and skip all', 'products-wizard-lite-for-woocommerce')
                    ],
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'to_results_button_text' => [
                    'label' => esc_html__('"To results" button text', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_to_results_button_text',
                    'type' => 'text',
                    'default' => esc_html__('To results', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'to_results_button_class' => [
                    'label' => esc_html__('"To results" button class', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_to_results_button_class',
                    'type' => 'text',
                    'default' => 'btn-success show-icon-on-mobile icon-right hide-text-on-mobile',
                    'description' => $controlsClassDescription,
                    'separate' => true,
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'widget_toggle_button_text' => [
                    'label' => esc_html__('"Toggle widget" button text', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_widget_toggle_button_text',
                    'type' => 'text',
                    'default' => esc_html__('Toggle cart', 'products-wizard-lite-for-woocommerce'),
                    'description' =>
                        esc_html__('Use the [wcpw-cart-total-price] special shortcode to output the cart total price', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'widget_toggle_button_class' => [
                    'label' => esc_html__('"Toggle widget" button class', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_widget_toggle_button_class',
                    'type' => 'text',
                    'default' => 'd-inline-block d-md-none btn-default btn-light show-icon icon-left hide-text',
                    'description' => $controlsClassDescription,
                    'separate' => true,
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'enable_remove_button' => [
                    'label' => esc_html__('Enable "Remove" button', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_enable_remove_button',
                    'type' => 'checkbox',
                    'default' => false,
                    'description' => esc_html__('Appears in the cart widget and results table', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'remove_button_text' => [
                    'label' => esc_html__('"Remove" button text', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_remove_button_text',
                    'type' => 'text',
                    'default' => esc_html__('Remove', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'remove_button_class' => [
                    'label' => esc_html__('"Remove" button class', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_remove_button_class',
                    'type' => 'text',
                    'default' => 'btn-light btn-sm show-icon icon-left hide-text',
                    'description' => $controlsClassDescription,
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce'),
                    'separate' => true
                ],
                'enable_edit_button' => [
                    'label' => esc_html__('Enable "Edit" button', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_enable_edit_button',
                    'type' => 'checkbox',
                    'default' => false,
                    'description' => esc_html__('Appears in the cart widget and results table', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'edit_button_text' => [
                    'label' => esc_html__('"Edit" button text', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_edit_button_text',
                    'type' => 'text',
                    'default' => esc_html__('Edit', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce')
                ],
                'edit_button_class' => [
                    'label' => esc_html__('"Edit" button class', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_edit_button_class',
                    'type' => 'text',
                    'default' => 'btn-link btn-sm show-icon icon-left hide-text',
                    'description' => $controlsClassDescription,
                    'group' => esc_html__('Controls', 'products-wizard-lite-for-woocommerce'),
                    'separate' => true
                ],
                // </editor-fold>
                // <editor-fold desc="Strings">
                'empty_cart_message' => [
                    'label' => esc_html__('Empty cart', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_empty_cart_message',
                    'type' => 'text',
                    'default' => esc_html__('Your cart is empty', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Strings', 'products-wizard-lite-for-woocommerce')
                ],
                'nothing_found_message' => [
                    'label' => esc_html__('Nothing found', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_nothing_found_message',
                    'type' => 'text',
                    'default' => esc_html__('No products were found matching your selection.', 'products-wizard-lite-for-woocommerce'),
                    'separate' => true,
                    'group' => esc_html__('Strings', 'products-wizard-lite-for-woocommerce')
                ],
                'minimum_products_selected_message' => [
                    'label' => esc_html__('Minimum products selected', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_minimum_products_selected_message',
                    'type' => 'text',
                    'default' => esc_html__('Minimum selected items are required: %limit%', 'products-wizard-lite-for-woocommerce'),
                    'description' => esc_html__('"%limit%" - products limit', 'products-wizard-lite-for-woocommerce')
                        . '<br>'
                        . esc_html__('"%value%" - current products count', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Strings', 'products-wizard-lite-for-woocommerce')
                ],
                'maximum_products_selected_message' => [
                    'label' => esc_html__('Maximum products selected', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_maximum_products_selected_message',
                    'type' => 'text',
                    'default' => esc_html__('Maximum items selected: %limit%', 'products-wizard-lite-for-woocommerce'),
                    'description' => esc_html__('"%limit%" - products limit', 'products-wizard-lite-for-woocommerce')
                        . '<br>'
                        . esc_html__('"%value%" - current products count', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Strings', 'products-wizard-lite-for-woocommerce')
                ],
                'subtotal_string' => [
                    'label' => esc_html__('Subtotal string', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_subtotal_string',
                    'type' => 'text',
                    'default' => esc_html__('Subtotal', 'products-wizard-lite-for-woocommerce'),
                    'group' => esc_html__('Strings', 'products-wizard-lite-for-woocommerce')
                ],
                'total_string' => [
                    'label' => esc_html__('Total string', 'products-wizard-lite-for-woocommerce'),
                    'key' => '_total_string',
                    'type' => 'text',
                    'default' => esc_html__('Total', 'products-wizard-lite-for-woocommerce'),
                    'separate' => true,
                    'group' => esc_html__('Strings', 'products-wizard-lite-for-woocommerce')
                ],
                // </editor-fold>
            ]
        );
    }

    /**
     * Get current working wizard ID
     *
     * @return integer
     */
    public function getCurrentId()
    {
        return $this->currentId;
    }

    /**
     * Set current working wizard ID
     *
     * @param integer $currentId
     */
    public function setCurrentId($currentId)
    {
        $this->currentId = (int) $currentId;
    }
    //</editor-fold>

    // <editor-fold desc="Settings shortcuts">
    /**
     * Get an array of the steps IDs from DB
     *
     * @param integer $wizardId
     *
     * @return array
     */
    public static function getStepsIds($wizardId)
    {
        static $cache = [];

        if (!isset($cache[$wizardId])) {
            $cache[$wizardId] = array_filter((array) self::getSettingValue($wizardId, '_steps_ids'), 'strlen');
        }

        return apply_filters('wcpw_steps_ids_setting', $cache[$wizardId], $wizardId);
    }

    /**
     * Is sidebar should be visible according to the widget and filter settings
     *
     * @param integer $wizardId
     *
     * @return bool
     */
    public static function isSidebarShowed($wizardId)
    {
        $output = self::isWidgetShowed($wizardId);

        return apply_filters('wcpw_is_sidebar_showed', $output, $wizardId);
    }

    /**
     * Get sidebar width sizes array
     *
     * @param integer $wizardId
     *
     * @return array
     */
    public static function getSidebarWidth($wizardId) {
        $output = [];

        foreach (array_filter((array) Wizard::getSetting($wizardId, 'sidebar_width', [])) as $size => $value) {
            if ($value == '') {
                continue;
            }

            if (is_numeric($value)) {
                $value .= 'px';
            }

            $output[$size] = $value;
        }

        return apply_filters('wcpw_sidebar_width', $output, $wizardId);
    }

    /**
     * Get final redirect URL
     *
     * @param integer $wizardId
     *
     * @return string
     */
    public static function getFinalRedirectURL($wizardId)
    {
        $output = trim(self::getSetting($wizardId, 'final_redirect_url'));

        // if the settings is empty
        if (!$output && function_exists('wc_get_page_id')) {
            $output = get_permalink(wc_get_page_id('cart'));
        }

        // if url is absolute
        if (strpos($output, home_url()) === false) {
            $output = home_url() . '/' . $output;
        }

        return apply_filters('wcpw_final_redirect_url', $output, $wizardId);
    }

    /**
     * Get body template according to the selected mode
     *
     * @param integer $wizardId
     *
     * @return string
     */
    public static function getBodyTemplate($wizardId)
    {
        switch (self::getSetting($wizardId, 'mode')) {
            case 'single-step':
            case 'sequence':
            case 'expanded-sequence':
                $output = 'single';
                break;

            case 'step-by-step':
            case 'free-walk':
                $output = 'tabs';
                break;

            default:
                $output = '';
        }

        return apply_filters('wcpw_body_template', $output, $wizardId);
    }

    /**
     * Is widget should be visible according to the "Show widget" setting
     *
     * @param integer $wizardId
     *
     * @return bool
     */
    public static function isWidgetShowed($wizardId)
    {
        switch (self::getSetting($wizardId, 'show_widget')) {
            case 'always':
                $output = true;
                break;

            case 'never':
            case '0':
                $output = false;
                break;

            case 'always_until_result_step':
                $output = is_numeric(Form::getActiveStepId($wizardId));
                break;

            case 'not_empty_until_result_step':
                $output = is_numeric(Form::getActiveStepId($wizardId)) && !empty(Cart::get($wizardId));
                break;

            default:
            case 'not_empty':
                $output = !empty(Cart::get($wizardId));
        }

        return apply_filters('wcpw_is_widget_showed', $output, $wizardId);
    }

    /**
     * Get min products selected message
     *
     * @param integer $wizardId
     * @param integer $limit - products limit
     * @param integer $value - products current value
     *
     * @return string
     */
    public static function getMinimumProductsSelectedMessage($wizardId, $limit, $value)
    {
        $output = self::replaceMessageVariables(
            self::getSetting($wizardId, 'minimum_products_selected_message'),
            [
                'limit' => $limit,
                'value' => $value
            ]
        );

        return apply_filters('wcpw_minimum_products_selected_message', $output, $wizardId, $limit, $value);
    }

    /**
     * Get max products selected message
     *
     * @param integer $wizardId
     * @param integer $limit - products limit
     * @param integer $value - products current value
     *
     * @return string
     */
    public static function getMaximumProductsSelectedMessage($wizardId, $limit, $value)
    {
        $output = self::replaceMessageVariables(
            self::getSetting($wizardId, 'maximum_products_selected_message'),
            [
                'limit' => $limit,
                'value' => $value
            ]
        );

        return apply_filters('wcpw_maximum_products_selected_message', $output, $wizardId, $limit, $value);
    }

    /**
     * Replace variables rounded by a percent symbol in a string
     *
     * @param string $message
     * @param array $variables
     *
     * @return string
     */
    private static function replaceMessageVariables($message, $variables = [])
    {
        $replacements = [];

        foreach ($variables as $key => $value) {
            $replacements["%$key%"] = $value;
        }

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }
    // </editor-fold>
}
