<?php
defined('ABSPATH') || exit;

do_action('woocommerce_before_checkout_form');

if (!WC()->cart->is_empty() && !WC()->checkout()->is_registration_enabled() && WC()->checkout()->is_registration_required() && !is_user_logged_in()) {
    echo apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce'));
    return;
}
?>

<form name="checkout" method="post" class="checkout-wrap" action="<?= esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

    <?php if (WC()->cart->is_empty()): ?>
        <div class="checkout-empty">
            <div class="checkout-empty-icon">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
            </div>
            <p class="checkout-empty-text">Tu carrito está vacío.</p>
            <a href="<?= get_permalink(wc_get_page_id('shop')); ?>" class="checkout-empty-link">Ir a la tienda</a>
        </div>
    <?php else: ?>

    <div class="checkout-layout">
        <!-- Billing + Shipping -->
        <div class="checkout-fields-col">

            <!-- Login / Coupon toggles -->
            <?php do_action('woocommerce_checkout_before_customer_details'); ?>

            <div class="checkout-section">
                <h2 class="checkout-section-title">Detalles de facturación</h2>
                <?php do_action('woocommerce_checkout_billing'); ?>
            </div>

            <div class="checkout-section">
                <h2 class="checkout-section-title">Información de envío</h2>
                <?php do_action('woocommerce_checkout_shipping'); ?>
            </div>

            <?php do_action('woocommerce_checkout_after_customer_details'); ?>

            <!-- Order notes -->
            <div class="checkout-section">
                <h2 class="checkout-section-title">Notas del pedido</h2>
                <textarea name="order_comments" class="checkout-notes" placeholder="¿Algo que debamos saber?" rows="3"></textarea>
            </div>
        </div>

        <!-- Order Review -->
        <div class="checkout-review-col">
            <div class="checkout-review-sticky">
                <h2 class="checkout-review-title">Tu pedido</h2>

                <?php do_action('woocommerce_checkout_before_order_review'); ?>

                <div id="order_review">
                    <?php do_action('woocommerce_checkout_order_review'); ?>
                </div>

                <?php do_action('woocommerce_checkout_after_order_review'); ?>
            </div>
        </div>
    </div>

    <?php endif; ?>
</form>

<?php do_action('woocommerce_after_checkout_form'); ?>
