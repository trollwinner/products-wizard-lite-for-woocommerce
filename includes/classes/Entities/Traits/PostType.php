<?php
namespace WCProductsWizard\Entities\Traits;

use WCProductsWizard\DataBase;
use WCProductsWizard\Entities;
use WCProductsWizard\Traits\Settings;

/**
 * Post-Type Entity Trait
 *
 * @version 1.1.0
 *
 * @property string $postType
 * @property string $namespace
 */
trait PostType
{
    use Settings;

    /**
     * Admin class instance
     * @var Entities\Extensions\Admin
     */
    public $admin;

    //<editor-fold desc="Core">
    /** Init class handlers */
    public function initPostType()
    {
        $this->initSettings();

        if (is_admin()) {
            $this->initAdmin();
        }

        add_action('init', [$this, 'registerPostType']);
        add_action('save_post_' . self::$postType, [$this, 'savePostSettings']);
    }

    /** Init admin class */
    public function initAdmin()
    {
        $this->admin = new Entities\Extensions\Admin($this);
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
     * Get class post type property
     *
     * @return string
     */
    public function getPostType()
    {
        return self::$postType;
    }

    /** Register entity post type */
    public function registerPostType()
    {
        // register if necessary
    }
    //</editor-fold>

    //<editor-fold desc="Settings">
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
     * Save meta values
     *
     * @param integer $id
     *
     * @throws \Exception
     */
    public function savePostSettings($id)
    {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || !current_user_can('edit_post', $id)
            || (isset($_REQUEST['action']) && sanitize_key(wp_unslash($_REQUEST['action'])) == 'inline-save')
            || get_post_status($id) == 'auto-draft'
            || !isset($_REQUEST['_wcpw_nonce'])
        ) {
            return;
        }

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wcpw_nonce'])), 'wcpw_' . $id)) {
            throw new \Exception(esc_html__('Error while saving.', 'products-wizard-lite-for-woocommerce'));
        }

        foreach (self::getSettingsModel() as $setting) {
            if (!isset($setting['key']) || !isset($_POST[$setting['key']])) {
                continue;
            }

            $value = sanitize_meta($setting['key'], wp_unslash($_POST[$setting['key']]), 'post');

            self::setSettingValue($id, $setting['key'], $value);
        }
    }
    //</editor-fold>

    //<editor-fold desc="API">
    /**
     * Returns array of ids and titles of posts
     *
     * @param array $args
     * @param boolean $addNameIds
     *
     * @return array
     */
    public static function getPostsIds($args = [], $addNameIds = true)
    {
        static $cache;

        $saveCache = false;

        if (empty($args) && $cache) {
            return $cache;
        }

        if (empty($args)) {
            $saveCache = true;
        }

        $defaults = [
            'post_type' => self::$postType,
            'numberposts' => -1,
            'post_status' => 'publish'
        ];

        $args = array_replace($defaults, $args);
        $output = [];

        foreach (DataBase\Entity::getCollection($args) as $post) {
            $output[$post->ID] = ($addNameIds ? ('#' . $post->ID . ': ') : '') . $post->post_title;
        }

        if ($saveCache) {
            $cache = $output;
        }

        return $output;
    }
    //</editor-fold>
}
