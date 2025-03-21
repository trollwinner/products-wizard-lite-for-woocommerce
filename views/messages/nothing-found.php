<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Entities\Wizard;
use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs(['nothingFoundMessage' => Wizard::getSetting($id, 'nothing_found_message')]);
?>
<div class="woocommerce-products-wizard-message no-results woocommerce-info"
    aria-live="polite"><?php
    echo wp_kses_post($arguments['nothingFoundMessage']);
    ?></div>
