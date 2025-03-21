<?php
namespace WCProductsWizard;

use WCProductsWizard\Entities\Wizard;

/**
 * Admin Class
 *
 * @class Admin
 * @version 11.1.0
 */
class Admin
{
    // <editor-fold desc="Core">
    /** Class Constructor */
    public function __construct()
    {
        // scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets'], 9);

        // main actions
        add_action('admin_notices', [$this, 'noticesAction'], 10, 2);

        // settings
        add_action('wcpw_output_settings_table_row', [$this, 'outputSettingsTableRow'], 10, 3);
        add_action('wcpw_output_setting_field', [$this, 'outputSettingField'], 10, 3);
        add_action('edit_form_top', [$this, 'outputPostNonce']);

        // plugin links
        add_filter('plugin_action_links', [$this, 'actionLinksFilter'], 10, 2);
        add_filter('plugin_row_meta', [$this, 'metaLinksFilter'], 10, 2);
    }

    /** Styles and scripts enqueue in admin */
    public function enqueueAssets()
    {
        wp_enqueue_media();
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-sortable');

        if (defined('WC_VERSION')
            && ((isset($_GET['post']) && get_post_type((int) $_GET['post']) == Wizard::$postType) // phpcs:disable WordPress.Security.NonceVerification.Recommended
                || (isset($_GET['post_type']) && sanitize_key(wp_unslash($_GET['post_type'])) == Wizard::$postType)) // phpcs:disable WordPress.Security.NonceVerification.Recommended
        ) {
            wp_register_script(
                'select2',
                WC()->plugin_url() . "/assets/js/select2/select2.full.min.js",
                ['jquery'],
                '4.0.3',
                true
            );

            wp_register_script(
                'selectWoo',
                WC()->plugin_url() . "/assets/js/selectWoo/selectWoo.full.min.js",
                ['jquery'],
                '1.0.0',
                true
            );

            wp_register_script(
                'wc-enhanced-select',
                WC()->plugin_url() . "/assets/js/admin/wc-enhanced-select.min.js",
                ['jquery', 'selectWoo'],
                WC_VERSION,
                true
            );

            wp_enqueue_script('select2');
            wp_enqueue_script('selectWoo');
            wp_enqueue_script('wc-enhanced-select');
            wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', [], WC_VERSION);
        }

        wp_enqueue_script(
            'wcpw-shared-editor-modal',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . 'assets/admin/js/shared-editor-modal.min.js',
            ['jquery'],
            WC_PRODUCTS_WIZARD_VERSION,
            true
        );

        wp_enqueue_script(
            'wcpw-data-table',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . 'assets/admin/js/data-table.min.js',
            [
                'jquery',
                'jquery-ui-sortable'
            ],
            WC_PRODUCTS_WIZARD_VERSION,
            true
        );

        wp_enqueue_script(
            'wcpw-steps',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . 'assets/admin/js/steps.min.js',
            [
                'jquery',
                'jquery-ui-sortable'
            ],
            WC_PRODUCTS_WIZARD_VERSION,
            true
        );

        wp_enqueue_script(
            'wcpw-thumbnail',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . 'assets/admin/js/thumbnail.min.js',
            ['jquery'],
            WC_PRODUCTS_WIZARD_VERSION,
            true
        );

        wp_enqueue_script(
            'wcpw-hooks',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . 'assets/admin/js/hooks.min.js',
            ['jquery'],
            WC_PRODUCTS_WIZARD_VERSION,
            true
        );

        wp_enqueue_style(
            'wcpw-app',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . 'assets/admin/css/app.min.css',
            [],
            WC_PRODUCTS_WIZARD_VERSION
        );
    }

    /** Admin notices hook */
    public function noticesAction()
    {
        global $pagenow;

        if ($pagenow == 'post.php' && !empty($_GET['post']) && get_post_type((int) $_GET['post']) == Wizard::$postType) { // phpcs:disable WordPress.Security.NonceVerification.Recommended
            ?>
            <div class="notice notice-success">
                <p style="font-size: 1.2em;">
                    😉
                    <a href="https://products-wizard.troll-winner.com/plugin-shop-page/" target="_blank"><?php // phpcs:ignore
                        esc_html_e('Go PRO to unlock all wizard possibilities!', 'products-wizard-lite-for-woocommerce');
                        ?></a>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Plugins list meta link filter
     *
     * @param array $links
     * @param string $plugin
     *
     * @return array
     */
    public function metaLinksFilter($links, $plugin)
    {
        if (false === strpos($plugin, basename(WC_PRODUCTS_WIZARD_ROOT_FILE))) {
            return $links;
        }

        $extraLinks = [
            'docs' => '<a href="https://products-wizard.troll-winner.com/docs/" target="_blank">'
                . esc_html__('Docs', 'products-wizard-lite-for-woocommerce') . '</a>'
        ];

        return array_merge($links, $extraLinks);
    }

    /**
     * Plugins list action links filter
     *
     * @param array $links
     * @param string $plugin
     *
     * @return array
     */
    public function actionLinksFilter($links, $plugin)
    {
        if (false === strpos($plugin, basename(WC_PRODUCTS_WIZARD_ROOT_FILE))) {
            return $links;
        }

        $extraLinks = [
            'upgrade' => '<a href="https://products-wizard.troll-winner.com/plugin-shop-page/" '
                . 'target="_blank" rel="nofollow" style="font-weight:bold;color:tomato">' . esc_html__('Go PRO!', 'products-wizard-lite-for-woocommerce') . '</a>',
            'settings' => '<a href="admin.php?page=wc-settings&tab=products_wizard">'
                . esc_html__('Settings', 'products-wizard-lite-for-woocommerce') . '</a>'
        ];

        return array_merge($extraLinks, $links);
    }
    // </editor-fold>

    // <editor-fold desc="Settings">
    /**
     * Generate html field for a settings model item
     * Both variables are global and used within setting views
     *
     * @param array $field
     * @param array $arguments
     */
    public static function outputSettingField($field, $arguments = [])
    {
        $typeAliases = apply_filters(
            'wcpw_setting_field_type_aliases',
            [
                'color' => 'text',
                'number' => 'text'
            ],
            $field,
            $arguments
        );

        $propertiesToAttributes = apply_filters(
            'wcpw_setting_field_properties_to_attributes',
            [
                'min',
                'max',
                'step',
                'pattern',
                'placeholder',
                'readonly',
                'required'
            ],
            $field,
            $arguments
        );

        $defaults = [
            'type' => 'text',
            'values' => [],
            'namePattern' => '%key%',
            'idPattern' => '%key%'
        ];

        $fieldDefaults = [
            'key' => '',
            'type' => 'text',
            'HTMLAttributes' => []
        ];

        $arguments = array_replace($defaults, $arguments);
        $field = array_replace($fieldDefaults, $field);

        foreach ($propertiesToAttributes as $propertyToAttribute) {
            if (isset($field[$propertyToAttribute])) {
                $field['HTMLAttributes'][$propertyToAttribute] = $field[$propertyToAttribute];
            }
        }

        // create name from pattern
        $arguments['name'] = str_replace('%key%', $field['key'], $arguments['namePattern']);

        // define value
        $arguments['value'] = isset($arguments['values'][$field['key']])
            ? $arguments['values'][$field['key']]
            : (isset($field['default']) ? $field['default'] : '');

        if (is_string($arguments['value'])) {
            $field['HTMLAttributes']['data-value'] = esc_attr($arguments['value']);
        }

        // define id attribute
        if ($arguments['idPattern']) {
            $field['HTMLAttributes']['id'] = str_replace('%key%', $field['key'], $arguments['idPattern']);
        }

        // extra filters
        $arguments = apply_filters('wcpw_setting_field_args', $arguments, $field);
        $type = !empty($typeAliases[$field['type']]) ? $typeAliases[$field['type']] : $field['type'];
        $viewPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
            . 'setting-field-views' . DIRECTORY_SEPARATOR . $type . '.php';

        echo wp_kses_post(isset($field['before']) ? $field['before'] : '');

        if (file_exists($viewPath)) {
            include($viewPath);
        }

        echo wp_kses_post(isset($field['after']) ? $field['after'] : '');

        if (isset($field['description'])) {
            echo '<p class="description">' . wp_kses_post($field['description']) . '</p>';
        }
    }

    /**
     * Generate table row with setting field
     *
     * @param string $key
     * @param array $field
     * @param array $args - setting field view args
     */
    public static function outputSettingsTableRow($key, $field, $args = [])
    {
        $defaultArgs = [
            'namespace' => 'wcpw_post',
            'tableSelector' => '.wcpw-settings-table',
            'tableBodySelector' => '> tbody >',
            'idPattern' => '%key%',
            'rowAttributes' => ['data-component' => 'wcpw-setting']
        ];

        $args = array_replace_recursive($defaultArgs, $args);
        $fieldDefaults = [
            'label' => '',
            'key' => ''
        ];

        $field = array_replace($fieldDefaults, $field);
        $id = $args['idPattern'] ? str_replace('%key%', $field['key'], $args['idPattern']) : null;

        if (empty($field['label'])) {
            return;
        }
        ?>
        <tr class="wcpw-settings-table-row form-field<?php
            echo !empty($field['separate'])
                ? ' separate-' . esc_attr(is_bool($field['separate']) ? 'bottom' : $field['separate'])
                : '';
            ?>"
            data-key="<?php echo esc_attr($key); ?>"
            <?php echo wp_kses_post(Utils::attributesArrayToString($args['rowAttributes'])); ?>>
            <th scope="row" class="wcpw-settings-table-row-name">
                <label for="<?php echo esc_attr($id); ?>"><?php echo wp_kses_post($field['label']); ?></label>
                <?php do_action($args['namespace'] . '_after_output_setting_label', $key, $field, $args); ?>
            </th>
            <td class="wcpw-settings-table-row-value"><?php
                self::outputSettingField($field, $args); // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
                ?></td>
        </tr>
        <?php
    }

    /**
     * Generate table rows with setting field
     *
     * @param array $settingsModel
     * @param array $args - setting field view args
     */
    public static function outputSettingsTableRows($settingsModel, $args = [])
    {
        foreach ($settingsModel as $key => $setting) {
            if (empty($setting['key'])) {
                continue;
            }

            self::outputSettingsTableRow($key, $setting, $args);
        }
    }

    /**
     * Generate table rows with setting field
     *
     * @param array $settingsModel
     * @param array $args - setting field view args
     */
    public static function outputSettingsGroups($settingsModel, $args = [])
    {
        $defaults = [
            'namespace' => 'wcpw_post',
            'tableClass' => 'wcpw-settings-table form-table'
        ];

        $args = array_replace($defaults, $args);
        $groups = [];

        foreach ($settingsModel as $key => $setting) {
            $group = isset($setting['group'])
                ? $setting['group']
                : esc_html__('Undefined group', 'products-wizard-lite-for-woocommerce');

            $groups[$group][$key] = $setting;
        }

        $keys = array_keys($groups);
        $active = reset($keys);

        foreach ($groups as $group => $model) {
            ?>
            <details class="wcpw-settings-group" name="<?php echo esc_attr($args['namespace']); ?>"
                <?php echo $active == $group ? ' open' : ''; ?>>
                <summary class="button button-large wcpw-settings-group-toggle"><?php
                    echo wp_kses_post($group);
                    ?></summary>
                <table class="<?php echo esc_attr($args['tableClass']); ?> wcpw-settings-group-content"><?php
                    do_action($args['namespace'] . '_before_output_settings_group', $group, $model, $args);

                    self::outputSettingsTableRows($model, $args);

                    do_action($args['namespace'] . '_after_output_settings_group', $group, $model, $args);
                    ?></table>
            </details>
            <?php
        }
    }

    /**
     * Post nonce field
     *
     * @param \WP_Post $post
     */
    public function outputPostNonce($post)
    {
        wp_nonce_field('wcpw_' . $post->ID, '_wcpw_nonce');
    }
    // </editor-fold>
}
