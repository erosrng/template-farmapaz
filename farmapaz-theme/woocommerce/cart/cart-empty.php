<?php
/**
 * Empty cart page
 */

defined('ABSPATH') || exit;

do_action('woocommerce_cart_is_empty'); ?>

<div class="cart-empty-state">
    <div class="cart-empty-icon">
        <svg width="56" height="56" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
    </div>
    <h2 class="cart-empty-heading">Tu carrito está vacío</h2>
    <p class="cart-empty-desc">Agrega productos desde nuestra tienda para empezar.</p>
    <a href="<?= get_permalink(wc_get_page_id('shop')); ?>" class="cart-empty-btn">
        Ir a la tienda
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
    </a>
</div>
