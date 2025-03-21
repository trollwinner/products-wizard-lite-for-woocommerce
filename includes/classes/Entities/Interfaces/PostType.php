<?php
namespace WCProductsWizard\Entities\Interfaces;

use WCProductsWizard\Interfaces\Settings;

/**
 * PostType Entity Interface
 *
 * @version 2.0.0
 *
 * @property string $postType
 */
interface PostType extends Settings
{
    /** Register entity post type */
    public function registerPostType();

    /**
     * Save meta values
     *
     * @param integer $id
     */
    public function savePostSettings($id);

    /**
     * Returns array of ids and titles of posts
     *
     * @param array $args
     *
     * @return array
     */
    public static function getPostsIds($args = []);
}
