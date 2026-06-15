<?php
defined('ABSPATH') || exit;

$user = wp_get_current_user();
?>

<div class="farmapaz-edit-account-wrap">
    <div class="farmapaz-edit-account-header">
        <h1 class="farmapaz-edit-account-title">Detalles de la Cuenta</h1>
        <p class="farmapaz-edit-account-subtitle">Actualiza tu información personal y contraseña.</p>
    </div>

    <?php wc_print_notices(); ?>

    <form class="farmapaz-edit-account-form" method="post" autocomplete="off">
        <?php do_action('woocommerce_edit_account_form_start'); ?>

        <div class="farmapaz-edit-account-fields">
            <div class="farmapaz-edit-account-row">
                <div class="farmapaz-edit-account-field">
                    <label for="account_first_name" class="farmapaz-edit-account-label">Nombre</label>
                    <div class="farmapaz-edit-account-input-wrap">
                        <input type="text" name="account_first_name" id="account_first_name"
                               class="farmapaz-edit-account-input"
                               value="<?= esc_attr($user->first_name); ?>"
                               autocomplete="given-name">
                    </div>
                </div>
                <div class="farmapaz-edit-account-field">
                    <label for="account_last_name" class="farmapaz-edit-account-label">Apellido</label>
                    <div class="farmapaz-edit-account-input-wrap">
                        <input type="text" name="account_last_name" id="account_last_name"
                               class="farmapaz-edit-account-input"
                               value="<?= esc_attr($user->last_name); ?>"
                               autocomplete="family-name">
                    </div>
                </div>
            </div>

            <div class="farmapaz-edit-account-field">
                <label for="account_display_name" class="farmapaz-edit-account-label">Nombre público</label>
                <div class="farmapaz-edit-account-input-wrap">
                    <input type="text" name="account_display_name" id="account_display_name"
                           class="farmapaz-edit-account-input"
                           value="<?= esc_attr($user->display_name); ?>"
                           autocomplete="username">
                    <p class="farmapaz-edit-account-hint">Así te verán los demás en tu perfil.</p>
                </div>
            </div>

            <div class="farmapaz-edit-account-field">
                <label for="account_email" class="farmapaz-edit-account-label">Correo electrónico</label>
                <div class="farmapaz-edit-account-input-wrap">
                    <input type="email" name="account_email" id="account_email"
                           class="farmapaz-edit-account-input"
                           value="<?= esc_attr($user->user_email); ?>"
                           autocomplete="email">
                </div>
            </div>
        </div>

        <div class="farmapaz-edit-account-divider">
            <span>Cambiar contraseña (opcional)</span>
        </div>

        <div class="farmapaz-edit-account-fields">
            <div class="farmapaz-edit-account-field">
                <label for="password_current" class="farmapaz-edit-account-label">Contraseña actual</label>
                <div class="farmapaz-edit-account-input-wrap">
                    <input type="password" name="password_current" id="password_current"
                           class="farmapaz-edit-account-input"
                           autocomplete="current-password">
                </div>
            </div>
            <div class="farmapaz-edit-account-row">
                <div class="farmapaz-edit-account-field">
                    <label for="password_1" class="farmapaz-edit-account-label">Nueva contraseña</label>
                    <div class="farmapaz-edit-account-input-wrap">
                        <input type="password" name="password_1" id="password_1"
                               class="farmapaz-edit-account-input"
                               autocomplete="new-password">
                    </div>
                </div>
                <div class="farmapaz-edit-account-field">
                    <label for="password_2" class="farmapaz-edit-account-label">Confirmar nueva contraseña</label>
                    <div class="farmapaz-edit-account-input-wrap">
                        <input type="password" name="password_2" id="password_2"
                               class="farmapaz-edit-account-input"
                               autocomplete="new-password">
                    </div>
                </div>
            </div>
        </div>

        <?php do_action('woocommerce_edit_account_form'); ?>

        <div class="farmapaz-edit-account-submit">
            <?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
            <button type="submit" class="farmapaz-edit-account-btn" name="save_account_details" value="Guardar cambios">
                <span>Guardar Cambios</span>
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </button>
        </div>

        <?php do_action('woocommerce_edit_account_form_end'); ?>
    </form>
</div>
