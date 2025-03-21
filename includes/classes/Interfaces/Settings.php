<?php
namespace WCProductsWizard\Interfaces;

/**
 * Abstract Entity Interface
 *
 * @version 1.0.0
 *
 * @property string $postType
 */
interface Settings
{
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

    /**
     * Method to get a setting value from database
     *
     * @param integer $id
     * @param string $setting
     * @param boolean $single
     *
     * @return string|float|boolean|array
     */
    public static function getSettingValue($id, $setting, $single = true);

    /**
     * Method to set a setting value into database
     *
     * @param integer $id
     * @param string $setting
     * @param mixed $value
     *
     * @return integer|boolean
     */
    public static function setSettingValue($id, $setting, $value);
}
