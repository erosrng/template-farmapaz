<?php
defined('ABSPATH') || exit;

do_action('woocommerce_before_customer_login_form');
$registration_enabled = get_option('woocommerce_enable_myaccount_registration') === 'yes';
?>
<div class="farmapaz-login-wrap">
    <div class="farmapaz-login-card">
        <!-- Decorative glow -->
        <div class="farmapaz-login-glow"></div>

        <!-- Logo -->
        <div class="farmapaz-login-logo">
            <?php
            $logo_svg_path = get_template_directory() . '/assets/images/logo.svg';
            $logo_png_path = get_template_directory() . '/assets/images/logo.png';
            $logo_svg = get_template_directory_uri() . '/assets/images/logo.svg';
            $logo_png = get_template_directory_uri() . '/assets/images/logo.png';
            if (file_exists($logo_svg_path)):
            ?>
            <img src="<?= $logo_svg; ?>" alt="Farmapaz" class="farmapaz-login-logo-img">
            <?php elseif (file_exists($logo_png_path)): ?>
            <img src="<?= $logo_png; ?>" alt="Farmapaz" class="farmapaz-login-logo-img">
            <?php elseif (has_custom_logo()):
                the_custom_logo();
            endif; ?>
        </div>

        <!-- Tabs -->
        <div class="farmapaz-login-tabs" role="tablist">
            <button class="farmapaz-login-tab active" data-tab="login" role="tab" aria-selected="true">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                <span>Iniciar Sesión</span>
            </button>
            <?php if ($registration_enabled): ?>
            <button class="farmapaz-login-tab" data-tab="register" role="tab" aria-selected="false">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Crear Cuenta</span>
            </button>
            <?php endif; ?>
            <div class="farmapaz-login-tab-indicator"></div>
        </div>

        <!-- Forms container -->
        <div class="farmapaz-login-forms">
            <!-- LOGIN FORM -->
            <div class="farmapaz-login-form-panel active" id="farmapaz-login-panel">
                <form class="farmapaz-login-form" method="post" autocomplete="off">
                    <?php do_action('woocommerce_login_form_start'); ?>

                    <div class="farmapaz-login-field">
                        <label for="username" class="farmapaz-login-label">Usuario o correo electrónico</label>
                        <div class="farmapaz-login-input-wrap">
                            <svg class="farmapaz-login-input-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <input type="text" name="username" id="username" class="farmapaz-login-input" placeholder="tu@email.com" value="<?= !empty($_POST['username']) ? esc_attr($_POST['username']) : ''; ?>" autocomplete="username">
                        </div>
                    </div>

                    <div class="farmapaz-login-field">
                        <label for="password" class="farmapaz-login-label">Contraseña</label>
                        <div class="farmapaz-login-input-wrap">
                            <svg class="farmapaz-login-input-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <input type="password" name="password" id="password" class="farmapaz-login-input" placeholder="••••••••" autocomplete="current-password">
                            <button type="button" class="farmapaz-login-pw-toggle" aria-label="Mostrar contraseña">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>

                    <?php do_action('woocommerce_login_form'); ?>

                    <div class="farmapaz-login-options">
                        <label class="farmapaz-login-remember">
                            <input type="checkbox" name="rememberme" id="rememberme" value="forever">
                            <span class="farmapaz-login-checkmark"></span>
                            <span>Recordarme</span>
                        </label>
                        <a href="<?= esc_url(wp_lostpassword_url()); ?>" class="farmapaz-login-lostpw">¿Olvidaste tu contraseña?</a>
                    </div>

                    <p class="farmapaz-login-submit">
                        <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
                        <button type="submit" class="farmapaz-login-btn" name="login" value="Iniciar Sesión">
                            <span>Iniciar Sesión</span>
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </button>
                    </p>

                    <?php do_action('woocommerce_login_form_end'); ?>
                </form>
            </div>

            <!-- REGISTER FORM -->
            <?php if ($registration_enabled): ?>
            <div class="farmapaz-login-form-panel" id="farmapaz-register-panel">
                <form class="farmapaz-login-form" method="post" autocomplete="off">
                    <?php do_action('woocommerce_register_form_start'); ?>

                    <div class="farmapaz-login-field">
                        <label for="reg_username" class="farmapaz-login-label">Nombre de usuario</label>
                        <div class="farmapaz-login-input-wrap">
                            <svg class="farmapaz-login-input-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <input type="text" name="username" id="reg_username" class="farmapaz-login-input" placeholder="usuario123" value="<?= !empty($_POST['username']) ? esc_attr($_POST['username']) : ''; ?>" autocomplete="username">
                        </div>
                    </div>

                    <div class="farmapaz-login-field">
                        <label for="reg_email" class="farmapaz-login-label">Correo electrónico</label>
                        <div class="farmapaz-login-input-wrap">
                            <svg class="farmapaz-login-input-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <input type="email" name="email" id="reg_email" class="farmapaz-login-input" placeholder="tu@email.com" value="<?= !empty($_POST['email']) ? esc_attr($_POST['email']) : ''; ?>" autocomplete="email">
                        </div>
                    </div>

                    <div class="farmapaz-login-field">
                        <label for="reg_password" class="farmapaz-login-label">Contraseña</label>
                        <div class="farmapaz-login-input-wrap">
                            <svg class="farmapaz-login-input-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <input type="password" name="password" id="reg_password" class="farmapaz-login-input" placeholder="Mín. 6 caracteres" autocomplete="new-password">
                            <button type="button" class="farmapaz-login-pw-toggle" aria-label="Mostrar contraseña">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>

                    <?php do_action('woocommerce_register_form'); ?>
                    <?php do_action('woocommerce_register_form_password'); ?>

                    <div class="farmapaz-login-terms">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Al registrarte aceptas nuestros <a href="<?= get_permalink(wc_get_page_id('terms')); ?>" target="_blank">Términos y Condiciones</a></span>
                    </div>

                    <p class="farmapaz-login-submit">
                        <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
                        <button type="submit" class="farmapaz-login-btn" name="register" value="Crear Cuenta">
                            <span>Crear Cuenta</span>
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        </button>
                    </p>

                    <?php do_action('woocommerce_register_form_end'); ?>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Decorative shapes -->
    <div class="farmapaz-login-shape farmapaz-login-shape-1"></div>
    <div class="farmapaz-login-shape farmapaz-login-shape-2"></div>
</div>

<script>
(function() {
    var tabs = document.querySelectorAll('.farmapaz-login-tab');
    var panels = document.querySelectorAll('.farmapaz-login-form-panel');
    var indicator = document.querySelector('.farmapaz-login-tab-indicator');

    function switchTab(tab) {
        var target = tab.dataset.tab;

        tabs.forEach(function(t) {
            t.classList.remove('active');
            t.setAttribute('aria-selected', 'false');
        });
        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');

        panels.forEach(function(p) {
            p.classList.remove('active');
        });
        document.getElementById('farmapaz-' + target + '-panel').classList.add('active');

        if (indicator) {
            indicator.style.width = tab.offsetWidth + 'px';
            indicator.style.transform = 'translateX(' + tab.offsetLeft + 'px)';
        }
    }

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() { switchTab(tab); });
    });

    // Set initial indicator position
    var activeTab = document.querySelector('.farmapaz-login-tab.active');
    if (activeTab && indicator) {
        indicator.style.width = activeTab.offsetWidth + 'px';
        indicator.style.transform = 'translateX(' + activeTab.offsetLeft + 'px)';
    }
    window.addEventListener('resize', function() {
        var at = document.querySelector('.farmapaz-login-tab.active');
        if (at && indicator) {
            indicator.style.width = at.offsetWidth + 'px';
            indicator.style.transform = 'translateX(' + at.offsetLeft + 'px)';
        }
    });

    // Password visibility toggle
    document.querySelectorAll('.farmapaz-login-pw-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = this.parentNode.querySelector('input');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/></svg>';
            } else {
                input.type = 'password';
                this.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
            }
        });
    });

    // Auto-show register tab if there are validation errors for registration
    var regForm = document.querySelector('#farmapaz-register-panel form');
    if (regForm) {
        var regFields = regForm.querySelectorAll('input[name="username"], input[name="email"]');
        var hasRegValues = false;
        regFields.forEach(function(f) {
            if (f.value) hasRegValues = true;
        });
        if (hasRegValues) {
            var regTab = document.querySelector('.farmapaz-login-tab[data-tab="register"]');
            if (regTab) switchTab(regTab);
        }
    }
})();
</script>

<?php do_action('woocommerce_after_customer_login_form'); ?>
