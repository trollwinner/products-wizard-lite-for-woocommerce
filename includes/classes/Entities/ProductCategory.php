<?php
namespace WCProductsWizard\Entities;

use WCProductsWizard\DataBase;

/**
 * Product Category Class
 *
 * @class ProductCategory
 * @version 2.0.1
 */
class ProductCategory
{
    /**
     * Taxonomy string
     * @var string
     */
    public static $taxonomy = 'product_cat';

    /**
     * Namespace string
     * @var string
     */
    public static $namespace = 'wcpw_product_category';

    /** Class Constructor */
    public function __construct()
    {
        add_action('wp_ajax_wcpw_json_search_category', [$this, 'jsonSearch'], 10, 3);
    }

    /** Search for categories and echo json */
    public function jsonSearch()
    {
        check_ajax_referer('search-products', 'security');

        $output = [];
        $args = [
            'taxonomy' => 'product_cat',
            'orderby' => 'id',
            'order' => 'ASC',
            'hide_empty' => false,
            'include' => !empty($_GET['include']) ? wp_parse_id_list(wp_unslash($_GET['include'])) : '',
            'number' => !empty($_GET['limit']) ? intval(wp_unslash($_GET['limit'])) : '',
            'name__like' => !empty($_GET['term']) ? sanitize_text_field(wp_unslash($_GET['term'])) : ''
        ];

        foreach (DataBase\Term::getCollection($args) as $term) {
            $output[$term->term_id] = rawurldecode($term->name);
        }

        wp_send_json(apply_filters('wcpw_json_search_found_categories', $output));
    }
}
