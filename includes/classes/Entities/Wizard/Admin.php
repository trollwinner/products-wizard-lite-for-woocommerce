<?php
namespace WCProductsWizard\Entities\Wizard;

use WCProductsWizard\Admin as AdminController;
use WCProductsWizard\Entities;
use WCProductsWizard\Template;

/**
 * Wizard Admin Class
 *
 * @class Admin
 * @version 1.0.1
 */
class Admin extends Entities\Extensions\Admin
{
    //<editor-fold desc="Properties">
    /**
     * Post type string
     * @var Entities\Wizard
     */
    public $parent;
    //</editor-fold>

    //<editor-fold desc="Core">
    /** Class constructor */
    public function __construct($parent)
    {
        parent::__construct($parent);

        add_action('admin_footer', [$this, 'footerAction'], 9);
        add_action('save_post_' . $this->parent->getPostType(), [$this, 'saveStepSettings']);

        // settings
        add_action($this->parent->getNamespace() . '_before_output_settings_group', [$this, 'beforeOutputSettingsGroup'], 10, 3);

        // list
        add_filter('manage_' . $this->parent->getPostType() . '_posts_columns', [$this, 'columnsFilter']);
        add_action('manage_' . $this->parent->getPostType() . '_posts_custom_column', [$this, 'columnsAction'], 10, 2);
    }

    /** WP footer hook */
    public function footerAction()
    {
        if ((!empty($_GET['post']) && get_post_type((int) $_GET['post']) == $this->parent->getPostType()) // phpcs:disable WordPress.Security.NonceVerification.Recommended
            || (!empty($_GET['post_type']) && sanitize_key(wp_unslash($_GET['post_type'])) == $this->parent->getPostType()) // phpcs:disable WordPress.Security.NonceVerification.Recommended
        ) {
            ?>
            <div class="wcpw-modal" data-component="wcpw-step-modal">
                <div class="wcpw-modal-dialog">
                    <a href="#close"
                        title="<?php esc_html_e('Close', 'products-wizard-lite-for-woocommerce'); ?>"
                        data-component="wcpw-step-modal-close"
                        class="wcpw-modal-close">&times;</a>
                    <div class="wcpw-modal-dialog-body" data-component="wcpw-step-modal-body"></div>
                </div>
            </div>
            <?php
        }
    }
    //</editor-fold>

    //<editor-fold desc="Output">
    /**
     * Save wizard steps settings
     *
     * @param integer $postId
     *
     * @throws \Exception
     */
    public function saveStepSettings($postId)
    {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || !current_user_can('edit_post', $postId)
            || (isset($_REQUEST['action']) && sanitize_key(wp_unslash($_REQUEST['action'])) == 'inline-save')
            || get_post_status($postId) == 'auto-draft'
            || !isset($_REQUEST['_wcpw_nonce'])
        ) {
            return;
        }

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wcpw_nonce'])), 'wcpw_' . $postId)) {
            throw new \Exception(esc_html__('Error while saving.', 'products-wizard-lite-for-woocommerce'));
        }

        // update steps settings
        $stepsIds = isset($_POST['_steps_ids']) ? wp_parse_id_list(wp_unslash($_POST['_steps_ids'])) : [];
        $stepsSettings = $this->parent->getSettingValue($postId, '_steps_settings') ?: [];

        foreach ($stepsSettings as $stepId => $_) {
            if (!in_array($stepId, $stepsIds)) {
                unset($stepsSettings[$stepId]);
            }
        }

        $this->parent->setSettingValue($postId, '_steps_settings', $stepsSettings);
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
        // get nav templates
        $model['nav_template']['values'] += Template::getNavList();

        // move basic group to the top
        $stepsIds = $model['steps_ids'];

        unset($model['steps_ids']);

        return ['steps_ids' => $stepsIds] + $model;
    }

    /**
     * Before group fields output
     *
     * @param string $group
     * @param array $model
     * @param array $args
     */
    public function beforeOutputSettingsGroup($group, $model, $args)
    {
        $args = array_replace(['post' => null], $args);
        $postId = $args['post'] instanceof \WP_Post ? $args['post']->ID : 0;

        if ($group == esc_html__('Basic', 'products-wizard-lite-for-woocommerce')) {
            $stepsSettings = Entities\WizardStep::getSettings($postId);
            $defaultSettingsURL = [
                'action' => 'wcpwGetStepSettingsForm',
                'post_id' => $postId,
                'step_id' => '%STEP_ID%'
            ];
            ?>
            <tr class="wcpw-settings-table-row form-field">
                <th scope="row" class="wcpw-settings-table-row-name">
                    <label for="shortcode"><?php esc_html_e('ShortCode', 'products-wizard-lite-for-woocommerce'); ?></label>
                    <a href="#" role="button" class="dashicons dashicons-admin-page" title="Copy"
                        data-component="wcpw-set-clipboard"
                        data-clipboard-success-class="dashicons dashicons-saved"
                        data-clipboard-error-class="dashicons dashicons-warning"
                        data-clipboard-value="[woocommerce-products-wizard id=&quot;<?php echo esc_attr($postId); ?>&quot;]"
                        data-clipboard-initial-class="dashicons dashicons-admin-page"></a>
                </th>
                <td class="wcpw-settings-table-row-value">
                    <input type="text" id="shortcode" readonly
                        value="<?php echo esc_attr('[woocommerce-products-wizard id="' . $postId . '"]'); ?>">
                </td>
            </tr>
            <tr class="wcpw-settings-table-row form-field" data-component="wcpw-steps"
                data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                <th scope="row" class="wcpw-settings-table-row-name">
                    <button class="button" data-component="wcpw-steps-add"><?php
                        esc_html_e('Add step', 'products-wizard-lite-for-woocommerce');
                        ?></button>
                </th>
                <td class="wcpw-settings-table-row-value">
                    <table class="wcpw-settings-table wcpw-data-table wcpw-steps-list wp-list-table widefat striped">
                        <tbody class="wcpw-steps-list" data-component="wcpw-steps-list"
                            data-empty-message="<?php echo esc_attr(esc_html__('← Add steps here', 'products-wizard-lite-for-woocommerce')); ?>"><?php
                            foreach ($this->parent->getStepsIds($postId) as $stepId) {
                                $settingsURL = [
                                    'action' => 'wcpwGetStepSettingsForm',
                                    'post_id' => $postId,
                                    'step_id' => $stepId
                                ];
                                ?><tr class="wcpw-steps-list-row wcpw-data-table-item"
                                    data-component="wcpw-steps-list-item"
                                    data-id="<?php echo esc_attr($stepId); ?>">
                                    <td>
                                        <span data-component="wcpw-steps-list-item-name"><?php
                                            echo wp_kses_post("#$stepId ");

                                            if (!empty($stepsSettings[$stepId]['title'])) {
                                                echo wp_kses_post($stepsSettings[$stepId]['title']);
                                            }

                                            if (!empty($stepsSettings[$stepId]['notes'])) {
                                                echo ' <small>('
                                                    . wp_kses_post($stepsSettings[$stepId]['notes'])
                                                    . ')</small>';
                                            }
                                            ?></span>
                                        <input type="hidden"
                                            data-component="wcpw-steps-list-item-id"
                                            name="_steps_ids[<?php echo esc_attr($stepId); ?>]"
                                            value="<?php echo esc_attr($stepId); ?>">
                                    </td>
                                    <td class="wcpw-data-table-item-controls">
                                        <button class="button wcpw-steps-list-item-clone"
                                            data-component="wcpw-steps-list-item-clone"
                                            data-settings="<?php
                                            echo esc_attr(wp_json_encode($settingsURL));
                                            ?>"><?php esc_html_e('Clone', 'products-wizard-lite-for-woocommerce'); ?></button>
                                    </td>
                                    <td class="wcpw-data-table-item-controls">
                                        <button class="button wcpw-steps-list-item-settings"
                                            data-component="wcpw-steps-list-item-settings"
                                            data-settings="<?php
                                            echo esc_attr(wp_json_encode($settingsURL));
                                            ?>"><?php esc_html_e('Settings', 'products-wizard-lite-for-woocommerce'); ?></button>
                                    </td>
                                    <td class="wcpw-data-table-item-controls">
                                        <button class="button"
                                            data-component="wcpw-steps-list-item-remove">&times;</button>
                                    </td>
                                </tr><?php
                            }
                            ?></tbody>
                        <tfoot hidden>
                            <tr class="wcpw-steps-list-row wcpw-data-table-item"
                                data-component="wcpw-steps-list-item-template">
                                <td>
                                    <span data-component="wcpw-steps-list-item-name"></span>
                                    <input type="hidden" data-component="wcpw-steps-list-item-id">
                                </td>
                                <td class="wcpw-data-table-item-controls">
                                    <button class="button wcpw-steps-list-item-clone"
                                        data-component="wcpw-steps-list-item-clone"
                                        data-settings="<?php
                                        echo esc_attr(wp_json_encode($defaultSettingsURL));
                                        ?>"><?php esc_html_e('Clone', 'products-wizard-lite-for-woocommerce'); ?></button>
                                </td>
                                <td class="wcpw-data-table-item-controls">
                                    <button class="button wcpw-steps-list-item-settings"
                                        data-component="wcpw-steps-list-item-settings"
                                        data-settings="<?php
                                        echo esc_attr(wp_json_encode($defaultSettingsURL));
                                        ?>"><?php esc_html_e('Settings', 'products-wizard-lite-for-woocommerce'); ?></button>
                                </td>
                                <td class="wcpw-data-table-item-controls">
                                    <button class="button"
                                        data-component="wcpw-steps-list-item-remove">&times;</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
            <?php
        }
    }

    /**
     * Output wizard meta fields
     *
     * @param \WP_Post $post
     */
    public function outputPostFields($post)
    {
        AdminController::outputSettingsGroups(
            self::getSettingsModel(),
            [
                'post' => $post,
                'values' => $this->getSettings($post->ID)
            ]
        );

        include(
            WC_PRODUCTS_WIZARD_PLUGIN_PATH  . 'includes' . DIRECTORY_SEPARATOR . 'global' . DIRECTORY_SEPARATOR
            . 'shared-editor-modal.php'
        );
    }
    //</editor-fold>

    //<editor-fold desc="List">
    /**
     * Wizards list columns filter
     *
     * @param array $columns
     *
     * @return array
     */
    public function columnsFilter($columns)
    {
        $columns['shortcode'] = esc_html__('ShortCode', 'products-wizard-lite-for-woocommerce');

        return $columns;
    }

    /**
     * Wizards list line cell
     *
     * @param array $columns
     * @param integer $postId
     */
    public function columnsAction($columns, $postId)
    {
        if ($columns == 'shortcode') {
            echo '[woocommerce-products-wizard id="' . (int) $postId . '"] '
                . '<a href="#" role="button" class="dashicons dashicons-admin-page" title="' . esc_attr('Copy') . '"'
                . 'data-component="wcpw-set-clipboard" data-clipboard-success-class="dashicons dashicons-saved" '
                . 'data-clipboard-error-class="dashicons dashicons-warning" '
                . 'data-clipboard-value=\'[woocommerce-products-wizard id="' . (int) $postId . '"]\'></a>';
        }
    }
    //</editor-fold>
}
