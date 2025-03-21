<?php
namespace WCProductsWizard\Entities\Extensions;

use WCProductsWizard\Admin as AdminController;
use WCProductsWizard\Entities;

/**
 * Entity Admin Class
 *
 * @version 1.0.0
 */
class Admin
{
    /**
     * Post type string
     * @var Entities\Traits\PostType
     */
    public $parent;

    /** Class constructor */
    public function __construct($parent)
    {
        $this->parent = $parent;

        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_filter($this->parent->getNamespace() . '_admin_settings_model', [$this, 'settingsModelFilter']);
    }

    //<editor-fold desc="Settings">
    /**
     * Get settings model array
     *
     * @return array
     */
    public function getSettingsModel()
    {
        return apply_filters($this->parent->getNamespace() . '_admin_settings_model', $this->parent->getSettingsModel());
    }

    /**
     * Get entity settings
     *
     * @param integer $id
     * @param array $args
     *
     * @return array
     */
    public function getSettings($id, $args = [])
    {
        return apply_filters($this->parent->getNamespace() . '_admin_settings', $this->parent->getSettings($id, $args), $id, $args);
    }

    /**
     * Get one of entity settings
     *
     * @param integer $id
     * @param string $setting
     * @param mixed $default
     *
     * @return string|float|boolean|array
     */
    public function getSetting($id, $setting, $default = null)
    {
        return apply_filters($this->parent->getNamespace() . '_admin_setting', $this->parent->getSetting($id, $setting, $default), $id, $setting, $default);
    }

    /**
     * Settings model filter
     *
     * @param array $model
     *
     * @return array
     */
    public function settingsModelFilter($model)
    {
        return ['group' => esc_html__('Basic', 'products-wizard-lite-for-woocommerce')] + $model;
    }
    //</editor-fold>

    //<editor-fold desc="Output">
    /** Add meta-boxes */
    public function addMetaBoxes()
    {
        add_meta_box(
            'settings',
            esc_html__('Settings', 'products-wizard-lite-for-woocommerce'),
            [$this, 'outputPostFields'],
            $this->parent->getPostType(),
            'normal'
        );
    }

    /**
     * Post meta-box view
     *
     * @param \WP_Post $post
     */
    public function outputPostFields($post)
    {
        echo '<table class="wcpw-settings-table form-table">';

        AdminController::outputSettingsTableRows($this->getSettingsModel(), ['values' => $this->getSettings($post->ID)]);

        echo '</table>';
    }
    //</editor-fold>
}
