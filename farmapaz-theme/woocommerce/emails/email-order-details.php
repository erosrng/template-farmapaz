<?php
defined('ABSPATH') || exit;
?>
<?php do_action('woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email); ?>

<h2>
    <?php if ($sent_to_admin): ?>
        <a class="link" href="<?php echo esc_url($order->get_edit_order_url()); ?>"><?php printf('Pedido #%s', esc_html($order->get_order_number())); ?></a>
    <?php else: ?>
        <?php printf('Pedido #%s', esc_html($order->get_order_number())); ?>
    <?php endif; ?>
    (<time datetime="<?php echo esc_attr($order->get_date_created()->format('c')); ?>"><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></time>)
</h2>

<div style="margin-bottom: 40px;">
    <table class="td font-family" cellspacing="0" cellpadding="6" style="width: 100%;" border="1">
        <thead>
            <tr>
                <th class="td text-align-left" scope="col">Producto</th>
                <th class="td text-align-left" scope="col">Cantidad</th>
                <th class="td text-align-left" scope="col">Precio</th>
            </tr>
        </thead>
        <tbody>
            <?php
            echo wc_get_email_order_items(
                $order,
                array(
                    'show_sku'      => $sent_to_admin,
                    'show_image'    => false,
                    'image_size'    => array(32, 32),
                    'plain_text'    => $plain_text,
                    'sent_to_admin' => $sent_to_admin,
                )
            );
            ?>
        </tbody>
        <tfoot>
            <?php
            $item_totals = $order->get_order_item_totals();
            if ($item_totals) {
                $i = 0;
                foreach ($item_totals as $total) {
                    ++$i;
                    ?>
                    <tr>
                        <th class="td text-align-left" scope="row" colspan="2" style="<?php echo ($i === 1) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post($total['label']); ?></th>
                        <td class="td text-align-left" style="<?php echo ($i === 1) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post($total['value']); ?></td>
                    </tr>
                    <?php
                }
            }
            if ($order->get_customer_note()) {
                ?>
                <tr>
                    <th class="td text-align-left" scope="row" colspan="2">Nota:</th>
                    <td class="td text-align-left"><?php echo wp_kses(nl2br(wptexturize($order->get_customer_note())), array()); ?></td>
                </tr>
                <?php
            }
            ?>
        </tfoot>
    </table>
</div>

<?php do_action('woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email); ?>
