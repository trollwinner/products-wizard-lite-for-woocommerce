<?php
namespace WCProductsWizard;

/**
 * Settings Class
 *
 * @class Settings
 * @version 11.0.0
 */
class Settings
{
    // <editor-fold desc="Models">
    /**
     * Get availability rules common settings model
     *
     * @return array
     */
    public static function getAvailabilityRulesModel()
    {
        return [
            'label' => esc_html__('Availability rules', 'products-wizard-lite-for-woocommerce'),
            'key' => 'availability_rules',
            'type' => 'data-table',
            'inModal' => true,
            'default' => [],
            'description' => esc_html__('Show/hide the step according to the specific rules', 'products-wizard-lite-for-woocommerce'),
            'values' => [
                'source' => [
                    'label' => esc_html__('Source', 'products-wizard-lite-for-woocommerce'),
                    'key' => 'source',
                    'type' => 'select',
                    'default' => 'product',
                    'values' => [
                        'product' => esc_html__('Product/variation', 'products-wizard-lite-for-woocommerce'),
                        'category' => esc_html__('Category', 'products-wizard-lite-for-woocommerce'),
                    ]
                ],
                'product' => [
                    'label' => esc_html__('Products', 'products-wizard-lite-for-woocommerce'),
                    'key' => 'product',
                    'type' => 'wc-product-search',
                    'default' => []
                ],
                'category' => [
                    'label' => esc_html__('Categories', 'products-wizard-lite-for-woocommerce'),
                    'key' => 'category',
                    'type' => 'wc-terms-search',
                    'default' => []
                ],
                'condition' => [
                    'label' => esc_html__('Condition', 'products-wizard-lite-for-woocommerce'),
                    'key' => 'condition',
                    'type' => 'select',
                    'default' => 'in_cart',
                    'values' => [
                        'in_cart' => esc_html__('In cart', 'products-wizard-lite-for-woocommerce'),
                        'not_in_cart' => esc_html__('Not in cart', 'products-wizard-lite-for-woocommerce')
                    ]
                ],
                'inner_relation' => [
                    'label' => esc_html__('Relation within the items', 'products-wizard-lite-for-woocommerce'),
                    'key' => 'inner_relation',
                    'type' => 'select',
                    'default' => 'and',
                    'values' => [
                        'or' => esc_html__('OR', 'products-wizard-lite-for-woocommerce'),
                        'and' => esc_html__('AND', 'products-wizard-lite-for-woocommerce')
                    ]
                ],
                'outer_relation' => [
                    'label' => esc_html__('Relation with the next rule', 'products-wizard-lite-for-woocommerce'),
                    'key' => 'outer_relation',
                    'type' => 'select',
                    'default' => 'or',
                    'values' => [
                        'or' => esc_html__('OR', 'products-wizard-lite-for-woocommerce'),
                        'and' => esc_html__('AND', 'products-wizard-lite-for-woocommerce')
                    ]
                ]
            ],
            'group' => esc_html__('Query', 'products-wizard-lite-for-woocommerce')
        ];
    }

    /**
     * Get product number common settings model
     *
     * @return array
     */
    public static function getProductNumbersModel()
    {
        return [
            'type' => [
                'label' => esc_html__('Type', 'products-wizard-lite-for-woocommerce'),
                'key' => 'type',
                'type' => 'select',
                'default' => 'number',
                'values' => [
                    'count' => esc_html__('Fixed value', 'products-wizard-lite-for-woocommerce'),
                    'selected-from-step' => esc_html__('Count of selected products of steps', 'products-wizard-lite-for-woocommerce')
                ]
            ],
            'value' => [
                'label' => esc_html__('Value', 'products-wizard-lite-for-woocommerce'),
                'key' => 'value',
                'type' => 'text',
                'default' => ''
            ]
        ];
    }
    // </editor-fold>
}
