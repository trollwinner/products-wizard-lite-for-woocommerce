<?php defined('ABSPATH') || exit; ?>
<div class="woocommerce-products-wizard-spinner-wrapper">
    <svg class="woocommerce-products-wizard-spinner" width="120" height="120" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
        <g>
            <circle cx="60" cy="60" r="50" class="woocommerce-products-wizard-spinner-bar"></circle>
            <circle cx="60" cy="60" r="50" class="woocommerce-products-wizard-spinner-fill" stroke-dasharray="100" stroke-linecap="round" pathLength="100"></circle>
            <animateTransform attributeName="transform" type="rotate" from="0 60 60" to="360 60 60" dur="1s" repeatCount="indefinite"/>
        </g>
    </svg>
</div>
