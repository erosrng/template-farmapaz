<?php
defined('ABSPATH') || exit;

$saved_methods = wc_get_customer_saved_methods_list(get_current_user_id());
$has_methods   = (bool) $saved_methods;
$paypal_ec     = get_option('woocommerce_paypal_express_settings', []);
?>

<div class="farmapaz-payment-wrap">
    <div class="farmapaz-payment-header">
        <h1 class="farmapaz-payment-title">Métodos de Pago</h1>
        <p class="farmapaz-payment-subtitle">Gestiona tus métodos de pago guardados.</p>
    </div>

    <?php wc_print_notices(); ?>

    <?php if ($has_methods): ?>
    <div class="farmapaz-payment-table-wrap">
        <table class="farmapaz-payment-table">
            <thead>
                <tr>
                    <th>Método</th>
                    <th>Detalles</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($saved_methods as $type => $methods): ?>
                    <?php foreach ($methods as $method): ?>
                    <tr>
                        <td><?= esc_html(wc_get_credit_card_type_label($method['method']['brand'] ?? '')); ?></td>
                        <td>
                            <?= esc_html($method['method']['last4'] ?? ''); ?>
                            <?php if (!empty($method['method']['expiry'])): ?>
                                <span class="farmapaz-payment-expiry">(<?= esc_html($method['method']['expiry']); ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td class="farmapaz-payment-actions">
                            <?php if (!empty($method['method_id'])): ?>
                            <a class="farmapaz-payment-delete-btn" href="<?= esc_url(wc_get_account_endpoint_url('delete-payment-method/' . $method['method_id'])); ?>">
                                Eliminar
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="farmapaz-payment-empty">
        <div class="farmapaz-payment-empty-icon">
            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: rgba(9,20,110,0.2);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>
        <h3 class="farmapaz-payment-empty-title">Sin métodos de pago</h3>
        <p class="farmapaz-payment-empty-text">Aún no has guardado ningún método de pago.</p>
    </div>
    <?php endif; ?>
</div>
