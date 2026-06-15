<?php
defined('ABSPATH') || exit;

$customer_orders = wc_get_orders([
    'customer' => get_current_user_id(),
    'limit'    => 20,
    'orderby'  => 'date',
    'order'    => 'DESC',
]);
?>

<div class="farmapaz-orders-wrap">
    <div class="farmapaz-orders-header">
        <h1 class="farmapaz-orders-title">Mis Pedidos</h1>
        <p class="farmapaz-orders-subtitle">Historial de tus compras en Farmapaz.</p>
    </div>

    <?php if (!empty($customer_orders)): ?>
    <div class="farmapaz-orders-table-wrap">
        <table class="farmapaz-orders-table">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customer_orders as $order): ?>
                <tr>
                    <td class="farmapaz-orders-number">#<?= esc_html($order->get_order_number()); ?></td>
                    <td class="farmapaz-orders-date"><?= esc_html(wc_format_datetime($order->get_date_created())); ?></td>
                    <td>
                        <span class="farmapaz-order-status status-<?= esc_attr($order->get_status()); ?>">
                            <?= esc_html(wc_get_order_status_name($order->get_status())); ?>
                        </span>
                    </td>
                    <td class="farmapaz-orders-total"><?= wp_kses_post($order->get_formatted_order_total()); ?></td>
                    <td class="farmapaz-orders-actions">
                        <a href="<?= esc_url($order->get_view_order_url()); ?>"
                           class="farmapaz-orders-view-btn">
                            Ver
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="farmapaz-orders-empty">
        <div class="farmapaz-orders-empty-icon">
            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: rgba(9,20,110,0.2);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <h3 class="farmapaz-orders-empty-title">No hay pedidos</h3>
        <p class="farmapaz-orders-empty-text">Aún no has realizado ninguna compra.</p>
        <a href="<?= esc_url(get_permalink(wc_get_page_id('shop'))); ?>"
           class="farmapaz-orders-shop-btn">
            Ir a la Tienda
        </a>
    </div>
    <?php endif; ?>
</div>
