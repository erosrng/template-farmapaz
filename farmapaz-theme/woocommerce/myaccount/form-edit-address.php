<?php
defined('ABSPATH') || exit;

$page_title   = ('billing' === $load_address) ? 'Dirección de Facturación' : 'Dirección de Envío';
?>

<div class="farmapaz-edit-address-wrap">
    <?php if (!$load_address): ?>
        <!-- Address overview -->
        <div class="farmapaz-edit-address-header">
            <h1 class="farmapaz-edit-address-title">Direcciones</h1>
            <p class="farmapaz-edit-address-subtitle">Las siguientes direcciones se usarán por defecto al momento de realizar tus compras.</p>
        </div>

        <div class="farmapaz-edit-address-grid">
            <div class="farmapaz-edit-address-card">
                <div class="farmapaz-edit-address-card-header">
                    <h2 class="farmapaz-edit-address-card-title">Dirección de Facturación</h2>
                    <a href="<?= esc_url(wc_get_account_endpoint_url('edit-address/billing')); ?>" class="farmapaz-edit-address-card-edit">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar
                    </a>
                </div>
                <div class="farmapaz-edit-address-card-body">
                    <address>
                        <?= wp_kses_post($addresses['billing'] ? $addresses['billing']->get_formatted() : 'Aún no has configurado esta dirección.'); ?>
                    </address>
                </div>
            </div>

            <div class="farmapaz-edit-address-card">
                <div class="farmapaz-edit-address-card-header">
                    <h2 class="farmapaz-edit-address-card-title">Dirección de Envío</h2>
                    <a href="<?= esc_url(wc_get_account_endpoint_url('edit-address/shipping')); ?>" class="farmapaz-edit-address-card-edit">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar
                    </a>
                </div>
                <div class="farmapaz-edit-address-card-body">
                    <address>
                        <?= wp_kses_post($addresses['shipping'] ? $addresses['shipping']->get_formatted() : 'Aún no has configurado esta dirección.'); ?>
                    </address>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Edit single address form -->
        <div class="farmapaz-edit-address-header">
            <a href="<?= esc_url(wc_get_account_endpoint_url('edit-address')); ?>" class="farmapaz-edit-address-back">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                </svg>
                Volver a Direcciones
            </a>
            <h1 class="farmapaz-edit-address-title"><?= esc_html($page_title); ?></h1>
        </div>

        <?php wc_print_notices(); ?>

        <form method="post" class="farmapaz-edit-address-form" novalidate>
            <div class="farmapaz-edit-address-fields">
                <?php
                foreach ($address as $key => $field) {
                    $field['return'] = false;
                    woocommerce_form_field($key, $field, wc_get_post_data_by_key($key, $field['value']));
                }
                ?>
            </div>

            <div class="farmapaz-edit-address-submit">
                <button type="submit" class="farmapaz-edit-address-save-btn" name="save_address" value="<?= esc_attr($page_title); ?>">
                    <span>Guardar Dirección</span>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
                <?php wp_nonce_field('woocommerce-edit_address', 'woocommerce-edit-address-nonce'); ?>
                <input type="hidden" name="action" value="edit_address" />
            </div>
        </form>
    <?php endif; ?>
</div>
