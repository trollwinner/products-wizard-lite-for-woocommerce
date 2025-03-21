<?php
namespace WCProductsWizard;

/**
 * Storage Class
 *
 * @class Storage
 * @version 2.1.2
 */
class Storage
{
    /** Start WC session if needed */
    public static function maybeStartSession()
    {
        // start WC session variable if needed
        if (!(function_exists('WC') && WC()
            && property_exists(WC(), 'session') && WC()->session
            && method_exists(WC()->session, 'has_session')
            && WC()->session->has_session()
        )) {
            return;
        }

        if (method_exists(WC(), 'initialize_session')) {
            WC()->initialize_session();
        }

        if (method_exists(WC()->session, 'set_customer_session_cookie')) {
            WC()->session->set_customer_session_cookie(true);
        }
    }

    /**
     * Set variable value
     *
     * @param string $nameSpace
     * @param integer $postId
     * @param string|integer|array $value
     * @param string $key - key of the value array to replace
     */
    public static function set($nameSpace, $postId, $value = null, $key = null)
    {
        self::maybeStartSession();

        $storedValue = self::getValue($nameSpace);

        if (!is_null($key)) {
            $storedValue[$postId][$key] = $value;
        } else {
            $storedValue[$postId] = $value;
        }

        self::setValue($nameSpace, $storedValue);
    }

    /**
     * Get the variable from the storage
     *
     * @param string $nameSpace
     * @param integer $postId
     * @param string $key
     *
     * @return array|string|number
     */
    public static function get($nameSpace, $postId = null, $key = null)
    {
        $storedValue = self::getValue($nameSpace);

        if ($key) {
            if (isset($storedValue[$postId][$key])) {
                return $storedValue[$postId][$key];
            } else {
                return '';
            }
        } elseif ($postId) {
            if (isset($storedValue[$postId])) {
                return $storedValue[$postId];
            } else {
                return '';
            }
        }

        return $storedValue;
    }

    /**
     * Check is variable exists
     *
     * @param string $nameSpace
     * @param integer $postId
     * @param string $key
     *
     * @return boolean
     */
    public static function exists($nameSpace, $postId, $key = null)
    {
        $storedValue = self::getValue($nameSpace);

        if (($key && isset($storedValue[$postId][$key])) || (!$key && isset($storedValue[$postId]))) {
            return true;
        }

        return false;
    }

    /**
     * Remove the key from the storage
     *
     * @param string $nameSpace
     * @param integer $postId
     * @param string $key
     */
    public static function remove($nameSpace, $postId, $key = null)
    {
        $storedValue = self::getValue($nameSpace);

        if ($key) {
            unset($storedValue[$postId][$key]);
        } else {
            unset($storedValue[$postId]);
        }

        self::setValue($nameSpace, $storedValue);
    }

    /**
     * Get value from session variable by namespace
     *
     * @param string $nameSpace
     *
     * @return array
     */
    private static function getSession($nameSpace)
    {
        Utils::startSession();

        $key = sanitize_text_field(wp_unslash($nameSpace));

        return session_id() && isset($_SESSION[$key]) ? map_deep(wp_unslash($_SESSION[$key]), 'sanitize_text_field') : null;
    }

    /**
     * Get value by namespace
     *
     * @param string $nameSpace
     *
     * @return array
     */
    private static function getValue($nameSpace)
    {
        if (function_exists('WC')
            && property_exists(WC(), 'session') && WC()->session && method_exists(WC()->session, 'get')
        ) {
            return WC()->session->get($nameSpace);
        }

        return self::getSession($nameSpace);
    }

    /**
     * Set a value to the storage
     *
     * @param string $nameSpace
     * @param string|integer|array $value
     */
    private static function setValue($nameSpace, $value)
    {
        if (function_exists('WC')
            && property_exists(WC(), 'session') && WC()->session && method_exists(WC()->session, 'set')
        ) {
            WC()->session->set($nameSpace, $value);

            return;
        }

        Utils::startSession();

        $_SESSION[$nameSpace] = map_deep(wp_unslash($value), 'sanitize_text_field');
    }
}
