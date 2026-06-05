<?php
defined('ABSPATH') || exit;

do_action('woocommerce_before_lost_password_form');
?>

<div class="farmapaz-login-wrap">
    <div class="farmapaz-login-card" style="max-width: 400px;">
        <div class="farmapaz-login-glow"></div>

        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="width: 56px; height: 56px; border-radius: 16px; background: rgba(9,20,110,0.06); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <svg width="28" height="28" fill="none" stroke="#09146E" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h2 style="font-size: 1.35rem; font-weight: 800; color: #09146E; margin: 0 0 0.35rem;">¿Olvidaste tu contraseña?</h2>
            <p style="font-size: 0.875rem; color: rgba(9,20,110,0.45); margin: 0; line-height: 1.5;">
                Ingresa tu correo electrónico y te enviaremos un enlace para restablecerla.
            </p>
        </div>

        <form method="post" class="farmapaz-login-form">
            <div class="farmapaz-login-field">
                <label for="user_login" class="farmapaz-login-label">Correo electrónico o usuario</label>
                <div class="farmapaz-login-input-wrap">
                    <svg class="farmapaz-login-input-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <input type="text" name="user_login" id="user_login" class="farmapaz-login-input" placeholder="tu@email.com" autocomplete="username">
                </div>
            </div>

            <?php do_action('woocommerce_lostpassword_form'); ?>

            <p class="farmapaz-login-submit">
                <?php wp_nonce_field('lost_password', 'woocommerce-lost-password-nonce'); ?>
                <button type="submit" class="farmapaz-login-btn" name="wc_reset_password" value="Enviar enlace">
                    <span>Enviar enlace</span>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </button>
            </p>

            <p style="text-align: center; margin-top: 1rem;">
                <a href="<?= esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>"
                   style="font-size: 0.8125rem; color: #5A7D43; font-weight: 500; text-decoration: none;">
                    Volver a iniciar sesión
                </a>
            </p>
        </form>
    </div>

    <div class="farmapaz-login-shape farmapaz-login-shape-1"></div>
    <div class="farmapaz-login-shape farmapaz-login-shape-2"></div>
</div>

<?php do_action('woocommerce_after_lost_password_form'); ?>
