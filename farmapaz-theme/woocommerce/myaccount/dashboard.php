<?php
defined('ABSPATH') || exit;

$current_user = wp_get_current_user();
$orders       = wc_get_customer_order_count(get_current_user_id());
$downloads    = wc_get_customer_download_count(get_current_user_id());
$address      = WC()->countries->get_address_formats();
?>

<div class="farmapaz-dashboard">
    <div class="farmapaz-dashboard-header">
        <h1 class="farmapaz-dashboard-title">Panel de Control</h1>
        <p class="farmapaz-dashboard-subtitle">
            Desde aquí puedes gestionar tus pedidos y tu información personal.
        </p>
    </div>

    <div class="farmapaz-dashboard-cards">
        <a href="<?= esc_url(wc_get_account_endpoint_url('orders')); ?>" class="farmapaz-dash-card">
            <div class="farmapaz-dash-card-icon" style="background: rgba(9,20,110,0.08); color: #09146E;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <span class="farmapaz-dash-card-count"><?= $orders; ?></span>
            <span class="farmapaz-dash-card-label">Pedidos</span>
        </a>

        <a href="<?= esc_url(wc_get_account_endpoint_url('downloads')); ?>" class="farmapaz-dash-card">
            <div class="farmapaz-dash-card-icon" style="background: rgba(90,125,67,0.1); color: #5A7D43;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <span class="farmapaz-dash-card-count"><?= $downloads; ?></span>
            <span class="farmapaz-dash-card-label">Descargas</span>
        </a>

        <a href="<?= esc_url(wc_get_account_endpoint_url('edit-address')); ?>" class="farmapaz-dash-card">
            <div class="farmapaz-dash-card-icon" style="background: rgba(254,171,13,0.12); color: #b45309;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <span class="farmapaz-dash-card-label" style="margin-top: 0.75rem;">Direcciones</span>
        </a>

        <a href="<?= esc_url(wc_get_account_endpoint_url('edit-account')); ?>" class="farmapaz-dash-card">
            <div class="farmapaz-dash-card-icon" style="background: rgba(249,115,22,0.1); color: #ea580c;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <span class="farmapaz-dash-card-label" style="margin-top: 0.75rem;">Perfil</span>
        </a>
    </div>

    <div class="farmapaz-dashboard-info">
        <p>
            Desde tu panel de control puedes ver tus <a href="<?= esc_url(wc_get_account_endpoint_url('orders')); ?>">pedidos recientes</a>,
            gestionar tus <a href="<?= esc_url(wc_get_account_endpoint_url('edit-address')); ?>">direcciones de envío y facturación</a>,
            y <a href="<?= esc_url(wc_get_account_endpoint_url('edit-account')); ?>">editar tu contraseña y los detalles de tu cuenta</a>.
        </p>
    </div>

    <?php
    $customer_orders = wc_get_orders([
        'customer' => get_current_user_id(),
        'limit'    => 3,
        'orderby'  => 'date',
        'order'    => 'DESC',
    ]);

    if (!empty($customer_orders)):
    ?>
    <div class="farmapaz-dashboard-recent">
        <h2 class="farmapaz-dashboard-section-title">Pedidos recientes</h2>
        <div class="farmapaz-dashboard-table-wrap">
            <table class="farmapaz-dashboard-table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customer_orders as $order): ?>
                    <tr>
                        <td>
                            <a href="<?= esc_url($order->get_view_order_url()); ?>">#<?= $order->get_order_number(); ?></a>
                        </td>
                        <td><?= esc_html(wc_format_datetime($order->get_date_created())); ?></td>
                        <td>
                            <span class="farmapaz-order-status status-<?= esc_attr($order->get_status()); ?>">
                                <?= esc_html(wc_get_order_status_name($order->get_status())); ?>
                            </span>
                        </td>
                        <td><?= wp_kses_post($order->get_formatted_order_total()); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
