<?php
namespace WCProductsWizard;

/**
 * Utils Class
 *
 * @class Utils
 * @version 3.3.0
 */
class Utils
{
    /**
     * getAvailabilityByRules method cache
     * @var array
     */
    protected static $availabilityRulesCache = [];

    /**
     * Make string of attributes from the array
     *
     * @param array $attributes
     *
     * @return string
     */
    public static function attributesArrayToString($attributes)
    {
        return implode(
            ' ',
            array_map(
                function ($key, $value) {
                    if (is_array($value)) {
                        $value = wp_json_encode($value);
                    } elseif (is_integer($key)) {
                        return esc_attr($value);
                    }

                    return esc_attr($key) . '="' . esc_attr($value) . '"';
                },
                array_keys($attributes),
                $attributes
            )
        );
    }

    /**
     * Clear availability rules results cache
     *
     * @param integer $wizardId
     * @param integer $itemId
     */
    public static function clearAvailabilityRulesCache($wizardId = null, $itemId = null)
    {
        if ($wizardId && $itemId) {
            self::$availabilityRulesCache[$wizardId][$itemId] = [];
        } elseif ($wizardId) {
            self::$availabilityRulesCache[$wizardId] = [];
        } else {
            self::$availabilityRulesCache = [];
        }
    }

    /**
     * Check the availability rules according to the current state
     *
     * @param integer $wizardId
     * @param array $rules - array of rules
     * @param integer $itemId
     *
     * @return boolean
     */
    public static function getAvailabilityByRules($wizardId, $rules = [], $itemId = null)
    {
        if ($itemId && isset(self::$availabilityRulesCache[$wizardId][$itemId])) {
            $output = self::$availabilityRulesCache[$wizardId][$itemId];

            return apply_filters('wcpw_availability_by_rules', $output, $wizardId, $rules, $itemId);
        }

        $output = true;

        if (!is_array($rules) || empty($rules)) {
            return apply_filters('wcpw_availability_by_rules', true, $wizardId, $rules, $itemId);
        }

        $cartProductsIds = Cart::getProductsAndVariationsIds($wizardId);
        $cartCategories = Cart::getCategoriesIds($wizardId);
        $metRules = [];
        $previousMet = null;

        foreach ($rules as $rule) {
            if (!isset($rule['source'], $rule['condition'], $rule['inner_relation'])
                || !($rule['source'] && $rule['condition'] && $rule['inner_relation'])
                || (!empty($rule['wizard']) && $rule['wizard'] != $wizardId)
            ) {
                continue;
            }

            $isMet = true;

            switch ($rule['source']) {
                case 'none':
                    continue 2;

                case 'product': {
                    if (empty($rule['product'])) {
                        continue 2;
                    }

                    $rule['product'] = !is_array($rule['product']) ? [trim($rule['product'])] : $rule['product'];
                    $isMet = $rule['inner_relation'] == 'and'
                        ? count(array_intersect($rule['product'], $cartProductsIds)) == count($rule['product'])
                        : !empty(array_intersect($rule['product'], $cartProductsIds));

                    break;
                }

                case 'category': {
                    if (empty($rule['category'])) {
                        continue 2;
                    }

                    $rule['category'] = !is_array($rule['category']) ? [trim($rule['category'])] : $rule['category'];
                    $isMet = $rule['inner_relation'] == 'and'
                        ? count(array_intersect($rule['category'], $cartCategories)) == count($rule['category'])
                        : !empty(array_intersect($rule['category'], $cartCategories));
                }
            }

            if ($rule['condition'] == 'not_in_cart') {
                $isMet = !$isMet;
            }

            if (isset($rule['outer_relation']) && $rule['outer_relation'] == 'and' && end($rules) != $rule) {
                if (!is_null($previousMet)) {
                    $previousMet = (int) $previousMet && $isMet;
                } else {
                    $previousMet = (int) $isMet;
                }
            } else {
                if (!is_null($previousMet)) {
                    $metRules[] = (int) $previousMet && $isMet;
                    $previousMet = null;
                } else {
                    $metRules[] = (int) $isMet;
                }
            }
        }

        if (!empty($metRules) && !in_array(1, $metRules)) {
            $output = false;
        }

        if ($itemId) {
            self::$availabilityRulesCache[$wizardId][$itemId] = $output;
        }

        return apply_filters('wcpw_availability_by_rules', $output, $wizardId, $rules, $itemId);
    }

    /**
     * Handle the setting value according to the type
     *
     * @param mixed $value
     * @param string $type
     *
     * @return string|float|boolean|array
     */
    public static function handleSettingType($value, $type = 'string')
    {
        switch ($type) {
            case 'checkbox':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);

            case 'number':
                return is_numeric($value) ? $value : '';

            case 'array':
            case 'data-table':
            case 'group':
            case 'multi-select':
                return (array) $value;

            case 'string':
                return (string) $value;
        }

        return $value;
    }

    /**
     * Is admin part and not ajax process
     *
     * @return boolean
     */
    public static function isAdminSide()
    {
        return is_admin() && !wp_doing_ajax();
    }

    /**
     * Parse JSONed request to an array
     *
     * @param array $postData
     *
     * @return array
     */
    public static function parseArrayOfJSONs($postData)
    {
        foreach ($postData as &$value) {
            if (is_string($value)) {
                $value = json_decode(stripslashes($value), true) ?: $value;
            }
        }

        return $postData;
    }

    /** Init session if isn't started and not an AJAX request */
    public static function startSession()
    {
        if (!session_id() && !(is_admin() && !wp_doing_ajax()) && apply_filters('wcpw_start_session', true)) {
            @session_start();
        }
    }

    /**
     * Send a JSON request
     *
     * @param array $data
     */
    public static function sendJSON($data = [])
    {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            _doing_it_wrong(
                __FUNCTION__,
                sprintf(
                    /* translators: 1: WP_REST_Response, 2: WP_Error */
                    esc_html__('Return a %1$s or %2$s object from your callback when using the REST API.', 'products-wizard-lite-for-woocommerce'),
                    'WP_REST_Response',
                    'WP_Error'
                ),
                '5.5.0'
            );
        }

        $output = wp_json_encode(apply_filters('wcpw_send_json_data', $data));

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=' . get_option('blog_charset'));
            header('Content-Length: ' . strlen($output));
        }

        echo $output; // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

        if (wp_doing_ajax()) {
            wp_die('', '', ['response' => null]);
        } else {
            die;
        }
    }

    /**
     * Implode styles array to inline string
     *
     * @param array $array
     *
     * @return string
     */
    public static function stylesArrayToString($array)
    {
        if (!is_array($array)) {
            return '';
        }

        return implode(
            ';',
            array_map(
                function ($value, $key) {
                    return "$key:$value" ;
                },
                array_values($array),
                array_keys($array)
            )
        );
    }
}
