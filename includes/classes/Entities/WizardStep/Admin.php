<?php
namespace WCProductsWizard\Entities\WizardStep;

use WCProductsWizard\Admin as AdminController;
use WCProductsWizard\Entities;
use WCProductsWizard\Template;

/**
 * Wizard Step Admin Class
 *
 * @class Admin
 * @version 1.0.0
 */
class Admin extends Entities\Extensions\Admin
{
    //<editor-fold desc="Properties">
    /**
     * Post type string
     * @var Entities\WizardStep
     */
    public $parent;
    //</editor-fold>

    //<editor-fold desc="Core">
    /** Class constructor */
    public function __construct($parent)
    {
        parent::__construct($parent);

        // settings
        add_action($this->parent->getNamespace() . '_after_output_setting_label', [$this, 'afterOutputSettingLabel'], 10, 2);

        add_action('wp_ajax_wcpwGetStepSettingsForm', [$this, 'outputSettingsFormAjax']);
        add_action('wp_ajax_wcpwSaveStepSettings', [$this, 'saveStepSettingsAjax']);
        add_action('wp_ajax_wcpwCloneStepSettings', [$this, 'cloneStepAjax']);
    }

    /**
     * After output setting field label
     *
     * @param string $key
     * @param array $field
     */
    public function afterOutputSettingLabel($key, $field)
    {
        ?>
        <label class="wcpw-step-setting-apply-to-all-label"
            data-component="wcpw-step-setting-apply-to-all-label">
            <input type="checkbox" name="apply-all-steps[]"
                value="<?php echo esc_attr($field['key']); ?>"
                data-component="wcpw-step-setting-apply-to-all-input">
            <?php esc_html_e('Apply to all steps', 'products-wizard-lite-for-woocommerce'); ?>
        </label>
        <?php
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
        // get all registered image sizes and use
        if (isset($model['item_thumbnail_size'], $model['filter_thumbnail_size'])) {
            global $_wp_additional_image_sizes;

            $imageSizes = [];

            foreach ($_wp_additional_image_sizes as $imageSizeName => $imageSize) {
                $crop = $imageSize['crop'] ? ' crop' : '';
                $imageSizes[] = $imageSizeName != "{$imageSize['width']}x{$imageSize['height']}$crop"
                    ? "$imageSizeName ({$imageSize['width']}x{$imageSize['height']}$crop)"
                    : $imageSizeName;
            }

            $sizesString = '<details><summary>' . esc_html__('More', 'products-wizard-lite-for-woocommerce') . '</summary>'
                . implode(', ', $imageSizes) . '</details>';

            $model['item_thumbnail_size']['description'] .= $sizesString;
            $model['filter_thumbnail_size']['description'] .= $sizesString;
        }

        // get templates
        if (isset($model['template'])) {
            $model['template']['values'] = Template::getFormList();
        }

        if (isset($model['item_template'])) {
            $model['item_template']['values'] = Template::getFormItemList();
        }

        if (isset($model['item_variations_template'])) {
            $model['item_variations_template']['values'] = Template::getVariationsTypeList();
        }

        return $model;
    }

    /** Add meta-boxes */
    public function addMetaBoxes()
    {
        // nothing
    }

    /** Output wizard step fields via ajax */
    public function outputSettingsFormAjax()
    {
        try {
            $this->outputSettingsForm($_GET); // phpcs:disable WordPress.Security.NonceVerification.Recommended
        } catch (\Exception $exception) {
            exit(wp_kses_post($exception->getMessage()));
        }

        exit;
    }

    /**
     * Output wizard step fields
     *
     * @param array $args
     *
     * @throws \Exception if empty step or post id
     */
    public function outputSettingsForm($args)
    {
        $postId = !empty($args['post_id']) ? (int) $args['post_id'] : null;
        $stepId = isset($args['step_id']) ? (int) $args['step_id'] : null;
        $nonce = !empty($args['_wcpw_nonce']) ? sanitize_key(wp_unslash($args['_wcpw_nonce'])) : null;

        if (is_null($stepId) || !$postId) {
            throw new \Exception(esc_html__('Empty step or post id', 'products-wizard-lite-for-woocommerce'));
        }

        if (!wp_verify_nonce($nonce, 'wcpw_' . $postId)) {
            throw new \Exception(esc_html__('Nonce error', 'products-wizard-lite-for-woocommerce'));
        }

        $meta = $this->parent->getSettings($postId);

        do_action('wcpw_step_settings_form', $this, $args);
        ?>
        <form class="wcpw-step-settings-form"
            data-component="wcpw-step-settings-form wcpw-settings-groups"
            data-step-id="<?php echo esc_attr($stepId); ?>"
            data-post-id="<?php echo esc_attr($postId); ?>">
            <?php
            AdminController::outputSettingsGroups(
                self::getSettingsModel(),
                [
                    'namespace' => 'wcpw_step',
                    'values' => isset($meta[$stepId]) ? $meta[$stepId] : []
                ]
            );
            ?>
            <footer class="wcpw-step-settings-form-footer">
                <button class="button button-primary button-large" type="submit"
                    data-component="wcpw-step-settings-form-submit"><?php
                    esc_html_e('Save', 'products-wizard-lite-for-woocommerce');
                    ?></button>
                 
                <button class="button button-primary button-large" type="submit"
                    data-component="wcpw-step-modal-close"><?php
                    esc_html_e('Save & Close', 'products-wizard-lite-for-woocommerce');
                    ?></button>
            </footer>
        </form>
        <?php
    }

    /** Save wizard step fields via ajax */
    public function saveStepSettingsAjax()
    {
        $args = $_POST; // phpcs:ignore
        $values = [];
        parse_str($args['values'], $values);
        $args['values'] = $values;

        try {
            $this->parent->saveSettings($args);
        } catch (\Exception $exception) {
            exit(wp_kses_post($exception->getMessage()));
        }

        exit;
    }

    /** Clone wizard step via ajax */
    public function cloneStepAjax()
    {
        try {
            $this->cloneStep($_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        } catch (\Exception $exception) {
            exit(wp_kses_post($exception->getMessage()));
        }

        exit;
    }

    /**
     * Clone wizard step
     *
     * @param array $args
     *
     * @throws \Exception if empty step or post id
     */
    public function cloneStep($args)
    {
        $postId = !empty($args['post_id']) ? (int) $args['post_id'] : null;
        $sourceStep = isset($args['source_step']) ? (int) $args['source_step'] : null;
        $targetStep = isset($args['target_step']) ? (int) $args['target_step'] : null;
        $nonce = !empty($args['_wcpw_nonce']) ? sanitize_key(wp_unslash($args['_wcpw_nonce'])) : null;

        if (!$postId || is_null($sourceStep) || is_null($targetStep)) {
            throw new \Exception(esc_html__('Empty step or post id', 'products-wizard-lite-for-woocommerce'));
        }

        if (!wp_verify_nonce($nonce, 'wcpw_' . $postId)) {
            throw new \Exception(esc_html__('Error while saving.', 'products-wizard-lite-for-woocommerce'));
        }

        if (!current_user_can('edit_post', $postId)) {
            throw new \Exception(esc_html__('Sorry, you are not allowed to edit this item.', 'products-wizard-lite-for-woocommerce'));
        }

        $settings = $this->parent->getSettings($postId);
        $settings[$targetStep] = isset($settings[$sourceStep]) ? $settings[$sourceStep] : [];

        $this->parent->setSettingValue($postId, '_steps_settings', $settings);
    }
}
