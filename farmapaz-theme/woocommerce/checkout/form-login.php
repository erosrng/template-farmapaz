<?php
defined('ABSPATH') || exit;

if (is_user_logged_in() || 'yes' !== get_option('woocommerce_enable_checkout_login_reminder')) return;
?>
<div class="farmapaz-checkout-login">
    <div class="farmapaz-checkout-login-toggle" onclick="this.nextElementSibling.classList.toggle('active'); this.querySelector('.farmapaz-chevron').classList.toggle('open');">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
        <span>¿Eres un cliente habitual? <strong>Inicia sesión</strong></span>
        <svg class="farmapaz-chevron" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </div>

    <div class="farmapaz-checkout-login-form">
        <p class="farmapaz-checkout-login-desc">Si ya tienes una cuenta, inicia sesión para agilizar tu compra.</p>

        <form class="farmapaz-checkout-login-fields" method="post">
            <div class="farmapaz-checkout-login-row">
                <div class="farmapaz-checkout-login-field">
                    <label for="username">Usuario o correo electrónico <span class="required">*</span></label>
                    <input type="text" name="username" id="username" placeholder="tu@email.com" autocomplete="username">
                </div>
                <div class="farmapaz-checkout-login-field">
                    <label for="password">Contraseña <span class="required">*</span></label>
                    <input type="password" name="password" id="password" placeholder="••••••••" autocomplete="current-password">
                </div>
            </div>

            <?php do_action('woocommerce_checkout_login_form'); ?>

            <div class="farmapaz-checkout-login-actions">
                <label class="farmapaz-checkout-remember">
                    <input type="checkbox" name="rememberme" value="forever">
                    <span class="farmapaz-checkout-remember-check"></span>
                    <span>Recordarme</span>
                </label>
                <button type="submit" class="farmapaz-checkout-login-submit" name="login" value="Iniciar Sesión">Iniciar Sesión</button>
            </div>

            <a href="<?= esc_url(wp_lostpassword_url()); ?>" class="farmapaz-checkout-lostpw">¿Olvidaste tu contraseña?</a>

            <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
        </form>
    </div>
</div>
