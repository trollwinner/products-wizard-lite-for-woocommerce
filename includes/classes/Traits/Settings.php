<?php
namespace WCProductsWizard\Traits;

use WCProductsWizard\Utils;

/**
 * Entity with settings trait
 *
 * @version 1.0.1
 *
 * @property string $namespace
 */
trait Settings
{
    /**
     * Models cache
     * @var array
     */
    protected static $cache = [];

    /** Init class handlers */
    public function initSettings()
    {
        // nothing
    }

    /**
     * Get settings model array
     *
     * @return array
     */
    public static function getSettingsModel()
    {
        return [];
    }

    /**
     * Get entity settings
     *
     * @param integer $id
     * @param array $args
     *
     * @return array
     */
    public static function getSettings($id, $args = [])
    {
        $defaults = ['public' => false];
        $args = array_merge($defaults, $args);
        $output = [];

        foreach (self::getSettingsModel() as $key => $setting) {
            if ($args['public'] && empty($setting['public'])) {
                continue;
            }

            $output[$args['public'] ? $key : $setting['key']] = self::getSetting($id, $key);
        }

        return apply_filters(self::$namespace . '_settings', $output, $id, $args);
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
    public static function getSetting($id, $setting, $default = null)
    {
        static $cache = [];

        if (isset($cache[$id], $cache[$id][$setting])) {
            return apply_filters(self::$namespace . '_setting', $cache[$id][$setting], $id, $setting);
        }

        $model = self::getSettingsModel();

        if (!isset($model[$setting])) {
            $cache[$id][$setting] = $default;

            return apply_filters(self::$namespace . '_setting', $default, $id, $setting);
        }

        $value = null;

        if (isset($model[$setting]['key'])) {
            $value = self::getSettingValue($id, $model[$setting]['key'], false);
        }

        if (empty($value)) {
            if ($default) {
                $value = $default;
            } elseif (isset($model[$setting]['default'])) {
                $value = $model[$setting]['default'];
            }
        } elseif (isset($value[0])) {
            $value = $value[0];
        }

        $value = Utils::handleSettingType($value, $model[$setting]['type']);

        if (!isset($cache[$id])) {
            $cache[$id] = [];
        }

        $cache[$id][$setting] = $value;

        return apply_filters(self::$namespace . '_setting', $value, $id, $setting);
    }

    /**
     * Method to get a setting value from database
     *
     * @param integer $id
     * @param string $setting
     * @param boolean $single
     *
     * @return string|float|boolean|array
     */
    abstract public static function getSettingValue($id, $setting, $single = true);

    /**
     * Set a setting value into database
     *
     * @param integer $id
     * @param string $setting
     * @param mixed $value
     *
     * @return integer|boolean
     */
    public static function setSetting($id, $setting, $value)
    {
        $model = self::getSettingsModel();

        if (!isset($model[$setting]['key'])) {
            return false;
        }

        $value = Utils::handleSettingType($value, $model[$setting]['type']);

        return self::setSettingValue($id, $model[$setting]['key'], $value);
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
    abstract public static function setSettingValue($id, $setting, $value);
}
