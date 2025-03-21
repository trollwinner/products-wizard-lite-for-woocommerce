<?php
namespace WCProductsWizard;

/**
 * Template Class
 *
 * @class Template
 * @version 4.1.0
 */
class Template
{
    /**
     * Array of template name aliases
     * @var array
     */
    public static $aliases = [];

    /**
     * Array of the showed templates
     * @var array
     */
    public static $showed = [];

    /**
     * Array of the showing template args
     * @var array
     */
    public static $currentHTMLArgs = [];

    /** Class constructor */
    public function __construct()
    {
        self::$aliases = [
            'buttons' => esc_html__('Buttons', 'products-wizard-lite-for-woocommerce'),
            'line' => esc_html__('Line', 'products-wizard-lite-for-woocommerce'),
            'line-horizontal' => esc_html__('Line horizontal', 'products-wizard-lite-for-woocommerce'),
            'pills' => esc_html__('Pills', 'products-wizard-lite-for-woocommerce'),
            'progress' => esc_html__('Progress', 'products-wizard-lite-for-woocommerce'),
            'tabs' => esc_html__('Tabs', 'products-wizard-lite-for-woocommerce')
        ];
    }

    /**
     * Get view files list by path or paths
     *
     * @param string|array $paths
     *
     * @return array - file names without .php extension
     */
    public static function getFilesList($paths)
    {
        $output = [];

        foreach ((array) $paths as $path) {
            if (!file_exists($path)) {
                continue;
            }

            foreach (scandir($path) as $file) {
                if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                    continue;
                }

                $name = pathinfo($file, PATHINFO_FILENAME);
                $output[$name] = isset(self::$aliases[$name]) ? self::$aliases[$name] : $name;
            }
        }

        natsort($output);

        return $output;
    }

    /**
     * Get view files list from the plugin and theme views by path
     *
     * @param string $path
     *
     * @return array - file names without .php extension
     */
    public static function getNativeAndCustomFilesList($path)
    {
        return self::getFilesList([
            implode(
                DIRECTORY_SEPARATOR,
                [WC_PRODUCTS_WIZARD_PLUGIN_PATH, 'views', $path]
            ),
            implode(
                DIRECTORY_SEPARATOR,
                [get_stylesheet_directory(), WC_PRODUCTS_WIZARD_THEME_TEMPLATES_DIR, $path]
            )
        ]);
    }

    /**
     * Get available form templates from plugin and theme directory
     *
     * @return array $templates
     */
    public static function getFormList()
    {
        return apply_filters('wcpw_form_templates', self::getNativeAndCustomFilesList('form/layouts'));
    }

    /**
     * Get available form item templates from plugin and theme directory
     *
     * @return array $templates
     */
    public static function getFormItemList()
    {
        return apply_filters('wcpw_form_item_templates', self::getNativeAndCustomFilesList('form/item'));
    }

    /**
     * Get available variation type templates from plugin and theme directory
     *
     * @return array $templates
     */
    public static function getVariationsTypeList()
    {
        return apply_filters(
            'wcpw_variations_type_templates',
            self::getNativeAndCustomFilesList('form/item/prototype/variations/item')
        );
    }

    /**
     * Get available nav list templates from plugin and theme directory
     *
     * @return array $templates
     */
    public static function getNavList()
    {
        return apply_filters('wcpw_nav_list_templates', self::getNativeAndCustomFilesList('nav/views'));
    }

    /**
     * Include php-template by the name.
     * First looking in the "theme folder/woocommerce-products-wizard (WC_PRODUCTS_WIZARD_THEME_TEMPLATES_DIR)"
     * Second looking in the "plugin folder/views"
     * Making extraction of the arguments as variables
     *
     * @param string $name
     * @param array $arguments
     * @param array $templateSettings
     *
     * @return string
     */
    public static function html($name = '', $arguments = [], $templateSettings = [])
    {
        static $pathCache = [];
        static $fileCache = [];

        $defaultSettings = [
            'echo' => true,
            'once' => false,
            'compress' => !WC_PRODUCTS_WIZARD_DEBUG
        ];

        $templateSettings = array_merge($defaultSettings, $templateSettings);

        // show template only once
        if ($templateSettings['once'] && in_array($name, self::$showed)) {
            return null;
        }

        // save template as showed
        self::$showed[] = $name;

        if (is_array($arguments)) {
            extract($arguments, EXTR_PREFIX_SAME, 'data');

            self::$currentHTMLArgs = $arguments;
        }

        if (isset($pathCache[$name])) {
            $path = $pathCache[$name];
        } else {
            $path = get_stylesheet_directory() . DIRECTORY_SEPARATOR . WC_PRODUCTS_WIZARD_THEME_TEMPLATES_DIR
                . DIRECTORY_SEPARATOR . $name . '.php';

            if (!file_exists($path)) {
                $path = WC_PRODUCTS_WIZARD_PLUGIN_PATH . 'views' . DIRECTORY_SEPARATOR . $name . '.php';
            }

            $pathCache[$name] = $path = apply_filters('wcpw_template_html_path', $path, $name, $arguments, $templateSettings);
        }

        if (!isset($fileCache[$path])) {
            $fileCache[$path] = file_exists($path);
        }

        if (!$fileCache[$path]) {
            return '';
        }

        ob_start();

        include($path);

        $html = ob_get_clean();

        if ($templateSettings['compress']) {
            $replace = [
                '/\>[^\S ]+/s' => '>',      // strip whitespaces after tags, except space
                '/[^\S ]+\</s' => '<',      // strip whitespaces before tags, except space
                '/(\s)+/s' => '\\1',        // shorten multiple whitespace sequences
                '/<!--spacer-->/' => ' ',   // replace spacer tag
                '/<!--(.|\s)*?-->/' => ''   // remove HTML comments
            ];

            $html = preg_replace(array_keys($replace), array_values($replace), $html);
        }

        if ($templateSettings['echo']) {
            echo $html; // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

            return null;
        }

        return $html;
    }

    /**
     * Get requested HTML part arguments
     *
     * @param array $defaults
     * @param array $settings
     *
     * @return array
     */
    public static function getHTMLArgs($defaults = [], $settings = [])
    {
        $defaultsSettings = ['recursive' => false];
        $settings = array_replace($defaultsSettings, $settings);
        $arguments = self::$currentHTMLArgs;

        if (!empty($arguments)) {
            foreach ($defaults as $defaultKey => $_) {
                // find arguments from shortcode attributes
                if (strtolower($defaultKey) === $defaultKey || !isset($arguments[strtolower($defaultKey)])) {
                    continue;
                }

                $arguments[$defaultKey] = $arguments[strtolower($defaultKey)];

                unset($arguments[strtolower($defaultKey)]);
            }

            if ($settings['recursive']) {
                $arguments = array_replace_recursive($defaults, $arguments);
            } else {
                $arguments = array_replace($defaults, $arguments);
            }

            return $arguments;
        }

        return $defaults;
    }
}
