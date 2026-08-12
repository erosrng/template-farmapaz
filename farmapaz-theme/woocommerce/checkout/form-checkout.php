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
                <h2 class="checkout-section-title">¿Cómo prefieres recibir tu pedido?</h2>
                <?php
                $delivery_method = farmapaz_delivery_method();
                $delivery_checked = $delivery_method !== 'pickup' ? 'checked' : '';
                $pickup_checked   = $delivery_method === 'pickup' ? 'checked' : '';
                ?>
                <div class="farmapaz-delivery-methods" data-delivery-methods>
                    <label class="farmapaz-delivery-option <?= $delivery_method !== 'pickup' ? 'is-selected' : ''; ?>">
                        <input type="radio" name="farmapaz_delivery_method" value="delivery" <?= $delivery_checked; ?>>
                        <span class="farmapaz-delivery-option-icon">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2-1m4-1l-2 1m4-2l-2 1m6-5v5a1 1 0 001 1h3l2-4v-3a1 1 0 00-1-1h-4a1 1 0 00-1 1z"/></svg>
                        </span>
                        <span class="farmapaz-delivery-option-body">
                            <span class="farmapaz-delivery-option-title">Envío a domicilio</span>
                            <span class="farmapaz-delivery-option-desc">Te lo llevamos a la puerta de tu casa. Delivery de <?= esc_html(farmapaz_delivery_schedule()); ?>.</span>
                        </span>
                        <span class="farmapaz-delivery-option-fee"><?php
                            $farmapaz_fee = farmapaz_shipping_fee_amount();
                            echo $farmapaz_fee > 0 ? 'Desde $' . esc_html($farmapaz_fee) : 'Gratis';
                        ?></span>
                    </label>
                    <?php if (farmapaz_pickup_enabled()): ?>
                    <label class="farmapaz-delivery-option <?= $delivery_method === 'pickup' ? 'is-selected' : ''; ?>">
                        <input type="radio" name="farmapaz_delivery_method" value="pickup" <?= $pickup_checked; ?>>
                        <span class="farmapaz-delivery-option-icon">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14-13h2a2 2 0 012 2v9a2 2 0 01-2 2h-2m-14-9H3a2 2 0 00-2 2v9a2 2 0 002 2h2M9 7h2m-2 4h2m-2 4h2m4-4h2m-2 4h2"/></svg>
                        </span>
                        <span class="farmapaz-delivery-option-body">
                            <span class="farmapaz-delivery-option-title">Retiro en tienda</span>
                            <span class="farmapaz-delivery-option-desc"><?= esc_html(farmapaz_store_address()); ?></span>
                        </span>
                        <span class="farmapaz-delivery-option-fee farmapaz-delivery-option-free">Gratis</span>
                    </label>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!farmapaz_delivery_is_open()): ?>
            <div class="farmapaz-delivery-note" data-delivery-note<?= $delivery_method === 'pickup' ? ' style="display:none;"' : ''; ?>">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l2.5 2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="farmapaz-delivery-note-text">Estamos fuera del horario de delivery (<strong><?= esc_html(farmapaz_delivery_schedule()); ?></strong>). Los pedidos que hagas ahora se procesarán <strong>mañana</strong>, cuando el delivery vuelva a estar activo.</p>
            </div>
            <?php endif; ?>

            <div class="checkout-section checkout-billing-section">
                <h2 class="checkout-section-title">Detalles de facturación</h2>
                <?php do_action('woocommerce_checkout_billing'); ?>
            </div>

            <div class="checkout-section checkout-shipping-section">
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
