<?php
namespace WCProductsWizard\DataBase;

/**
 * DB Option Layer Class
 *
 * @class Option
 * @version 1.0.0
 */
class Option
{
    /**
     * Get global option value
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return get_option($key, $default);
    }
}
