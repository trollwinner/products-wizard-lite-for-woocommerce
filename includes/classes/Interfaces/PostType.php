<?php
namespace WCProductsWizard\Interfaces;

use WP_Post;

/**
 * PostType Entity Interface
 *
 * @version 1.0.0
 *
 * @property string $postType
 */
interface PostType
{
    /** Register entity post type */
    public function registerPostType();

    /**
     * Get settings model array
     *
     * @return array
     */
    public static function getSettingsModel();

    /**
     * Get post settings
     *
     * @param integer $id
     * @param array $args
     *
     * @return array
     */
    public static function getSettings($id, $args = []);

    /**
     * Get one of post settings
     *
     * @param integer $id
     * @param string $setting
     * @param mixed $default
     *
     * @return string|float|boolean|array
     */
    public static function getSetting($id, $setting, $default = null);

    /** Add meta-boxes */
    public function addMetaBoxes();

    /**
     * Post meta-box view
     *
     * @param WP_Post $post
     */
    public function outputPostFields($post);

    /**
     * Save meta values
     *
     * @param integer $postId
     */
    public function savePostSettings($postId);

    /**
     * Returns array of ids and titles of posts
     *
     * @param array $args
     *
     * @return array
     */
    public static function getPostsIds($args = []);
}
