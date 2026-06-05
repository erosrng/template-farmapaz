<?php
defined('ABSPATH') || exit;

if (!wc_coupons_enabled() || !empty(WC()->cart->applied_coupons)) return;

$info_message = apply_filters('woocommerce_checkout_coupon_message', '¿Tienes un cupón? <strong>Ingresa tu código</strong>');
?>
<div class="farmapaz-checkout-coupon">
    <div class="farmapaz-checkout-login-toggle" onclick="this.nextElementSibling.classList.toggle('active'); this.querySelector('.farmapaz-chevron').classList.toggle('open');">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
        <span><?= wp_kses_post($info_message); ?></span>
        <svg class="farmapaz-chevron" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </div>

    <div class="farmapaz-checkout-coupon-form">
        <p class="farmapaz-checkout-login-desc">Si tienes un código de descuento, ingrésalo aquí.</p>

        <div class="farmapaz-checkout-coupon-row">
            <input type="text" name="coupon_code" class="farmapaz-checkout-coupon-input" placeholder="Código de descuento" id="coupon_code">
            <button type="submit" class="farmapaz-checkout-login-submit" name="apply_coupon" value="Aplicar">Aplicar</button>
        </div>

        <div class="clear"></div>
    </div>
</div>
