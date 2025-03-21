<?php
defined('ABSPATH') || exit;

$id = isset($id) ? $id : null;

if (!$id) {
    throw new Exception('Empty wizard id');
}

use WCProductsWizard\Template;

$arguments = Template::getHTMLArgs([
    'class' => 'is-tabs nav nav-tabs',
    'itemClass' => 'nav-item',
    'itemButtonClass' => 'nav-link'
]);

Template::html('nav/list', $arguments);
