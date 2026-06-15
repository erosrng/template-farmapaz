<?php
defined('ABSPATH') || exit;

$order = wc_get_order($order_id);
if (!$order) return;

$order_items        = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
$show_purchase_note = $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', array('completed', 'processing')));
$downloads          = $order->get_downloadable_items();
$show_customer_details = $order->get_user_id() === get_current_user_id();

if ($show_downloads) {
    wc_get_template('order/order-downloads.php', array('downloads' => $downloads, 'show_title' => true));
}
?>
<section class="woocommerce-order-details">
    <?php do_action('woocommerce_order_details_before_order_table', $order); ?>

    <h2 class="woocommerce-order-details__title">Detalles del pedido</h2>

    <table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
        <thead>
            <tr>
                <th class="woocommerce-table__product-name product-name">Producto</th>
                <th class="woocommerce-table__product-table product-total">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            do_action('woocommerce_order_details_before_order_table_items', $order);
            foreach ($order_items as $item_id => $item) {
                $product = $item->get_product();
                wc_get_template('order/order-details-item.php', array(
                    'order' => $order, 'item_id' => $item_id, 'item' => $item,
                    'show_purchase_note' => $show_purchase_note,
                    'purchase_note' => $product ? $product->get_purchase_note() : '',
                    'product' => $product,
                ));
            }
            do_action('woocommerce_order_details_after_order_table_items', $order);
            ?>
        </tbody>
        <tfoot>
            <?php foreach ($order->get_order_item_totals() as $key => $total): ?>
                <tr>
                    <th scope="row"><?php echo esc_html($total['label']); ?></th>
                    <td><?php echo wp_kses_post($total['value']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($order->get_customer_note()): ?>
                <tr>
                    <th>Nota:</th>
                    <td><?php echo wp_kses(nl2br(wptexturize($order->get_customer_note())), array('br' => array())); ?></td>
                </tr>
            <?php endif; ?>
        </tfoot>
    </table>

    <?php do_action('woocommerce_order_details_after_order_table', $order); ?>
</section>

<?php
do_action('woocommerce_after_order_details', $order);

if ($show_customer_details) {
    wc_get_template('order/order-details-customer.php', array('order' => $order));
}
