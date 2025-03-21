<?php
namespace WCProductsWizard\DataBase;

/**
 * DB Entity Layer Class
 *
 * @class Entity
 * @version 1.1.1
 */
class Entity
{
    /**
     * Get entity data
     *
     * @param integer $id
     *
     * @return \WP_Post|null
     */
    public static function get($id)
    {
        return get_post($id);
    }

    /**
     * Get entities collection
     *
     * @param array $args
     *
     * @return \WP_Post[]|int[]
     */
    public static function getCollection($args)
    {
        return get_posts($args);
    }

    /**
     * Get entity meta value
     *
     * @param integer $id
     * @param string $key
     * @param boolean $single
     *
     * @return mixed
     */
    public static function getMeta($id, $key, $single = true)
    {
        return get_post_meta($id, $key, $single);
    }

    /**
     * Add or update entity meta value
     *
     * @param integer $id
     * @param string $key
     * @param mixed $value
     *
     * @return integer|boolean
     */
    public static function updateMeta($id, $key, $value)
    {
        return update_post_meta($id, $key, $value);
    }
}
