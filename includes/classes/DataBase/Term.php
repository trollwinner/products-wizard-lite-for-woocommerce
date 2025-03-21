<?php
namespace WCProductsWizard\DataBase;

/**
 * DB Term Layer Class
 *
 * @class Term
 * @version 2.1.0
 */
class Term
{
    /**
     * Get term by a property
     *
     * @param integer $id
     * @param string $taxonomy
     * @param string $property
     *
     * @return \WP_Term|array|\WP_Error|null
     */
    public static function get($id, $taxonomy = '', $property = 'id')
    {
        if (!$taxonomy) {
            return get_term($id);
        }

        return get_term_by($property, $id, $taxonomy);
    }

    /**
     * Get terms collection
     *
     * @param array $args
     *
     * @return array
     */
    public static function getCollection($args)
    {
        return get_terms($args);
    }
}
