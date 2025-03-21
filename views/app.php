<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    esc_html_e('Empty wizard id', 'products-wizard-lite-for-woocommerce');

    return;
}

if (!get_post_status($id)) {
    esc_html_e('Wizard does not exists', 'products-wizard-lite-for-woocommerce');

    return;
}

use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'id' => $id,
    'formId' => "wcpw-form-$id",
    'ajaxURL' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('wcpw'),
    'scrollingTopOnUpdate' => Wizard::getSetting($id, 'scrolling_top_on_update'),
    'scrollingUpGap' => Wizard::getSetting($id, 'scrolling_up_gap')
]);

do_action('wcpw_before', $arguments);
?>
<section class="woocommerce-products-wizard <?php echo 'is-id-' . esc_attr($arguments['id']); ?>"
    data-component="wcpw" data-id="<?php echo esc_attr($arguments['id']); ?>"
    data-options="<?php echo esc_attr(wp_json_encode($arguments)); ?>"><?php
    Template::html('router', $arguments);
    ?></section>
<?php do_action('wcpw_after', $arguments); ?>
