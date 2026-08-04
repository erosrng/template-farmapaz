<?php
defined('ABSPATH') || exit;
?>

<div class="farmapaz-view-order-wrap">
    <div class="farmapaz-view-order-header">
        <h1 class="farmapaz-view-order-title">Pedido #<?= $order->get_order_number(); ?></h1>
        <a href="<?= esc_url(wc_get_account_endpoint_url('orders')); ?>"
           class="farmapaz-view-order-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
            </svg>
            Volver a Pedidos
        </a>
    </div>

    <div class="farmapaz-view-order-meta">
        <div class="farmapaz-view-order-meta-item">
            <span class="farmapaz-view-order-meta-label">Fecha</span>
            <span class="farmapaz-view-order-meta-value"><?= esc_html(wc_format_datetime($order->get_date_created())); ?></span>
        </div>
        <div class="farmapaz-view-order-meta-item">
            <span class="farmapaz-view-order-meta-label">Estado</span>
            <span class="farmapaz-order-status status-<?= esc_attr($order->get_status()); ?>">
                <?= esc_html(wc_get_order_status_name($order->get_status())); ?>
            </span>
        </div>
        <div class="farmapaz-view-order-meta-item">
            <span class="farmapaz-view-order-meta-label">Total</span>
            <span class="farmapaz-view-order-meta-value"><?= wp_kses_post($order->get_formatted_order_total()); ?></span>
        </div>
        <?php if ($order->get_payment_method_title()): ?>
        <div class="farmapaz-view-order-meta-item">
            <span class="farmapaz-view-order-meta-label">Método de Pago</span>
            <span class="farmapaz-view-order-meta-value"><?= esc_html($order->get_payment_method_title()); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <?php
    $show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
    $order_items           = $order->get_items();
    $show_purchase_note    = $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', ['completed', 'processing']));
    $downloads             = $order->get_downloadable_items();
    ?>

    <?php if (!empty($order_items)): ?>
    <div class="farmapaz-view-order-items">
        <h2 class="farmapaz-view-order-section-title">Productos</h2>
        <div class="farmapaz-view-order-table-wrap">
            <table class="farmapaz-view-order-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item_id => $item):
                        $product = $item->get_product();
                    ?>
                    <tr>
                        <td>
                            <div class="farmapaz-view-order-product">
                                <?php if ($product): ?>
                                <div class="farmapaz-view-order-product-img">
                                    <?php $img = farmapaz_product_img_fallback(get_the_post_thumbnail_url($product->get_id(), 'thumbnail')); ?>
                                    <img src="<?= esc_url($img); ?>" alt="<?= esc_attr($item->get_name()); ?>" width="48" height="48"
                                         onerror="this.onerror=null;this.src='<?= get_template_directory_uri(); ?>/assets/images/producto.svg'">
                                </div>
                                <?php endif; ?>
                                <div class="farmapaz-view-order-product-info">
                                    <span class="farmapaz-view-order-product-name">
                                        <?= esc_html($item->get_name()); ?>
                                    </span>
                                    <?php if ($show_purchase_note && $product && $product->get_purchase_note()): ?>
                                    <div class="farmapaz-view-order-product-note">
                                        <?= wp_kses_post(wpautop($product->get_purchase_note())); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><?= esc_html($item->get_quantity()); ?></td>
                        <td><?= wp_kses_post($order->get_formatted_line_subtotal($item)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <?php foreach ($order->get_order_item_totals() as $key => $total): ?>
                    <tr>
                        <th colspan="2"><?= esc_html($total['label']); ?></th>
                        <td><?= wp_kses_post($total['value']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($show_customer_details): ?>
    <div class="farmapaz-view-order-addresses">
        <?php if ($order->get_billing_address_1()): ?>
        <div class="farmapaz-view-order-address">
            <h3 class="farmapaz-view-order-section-title">Dirección de Facturación</h3>
            <address>
                <?= wp_kses_post($order->get_formatted_billing_address()); ?>
            </address>
        </div>
        <?php endif; ?>
        <?php if ($order->get_shipping_address_1()): ?>
        <div class="farmapaz-view-order-address">
            <h3 class="farmapaz-view-order-section-title">Dirección de Envío</h3>
            <address>
                <?= wp_kses_post($order->get_formatted_shipping_address()); ?>
            </address>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
