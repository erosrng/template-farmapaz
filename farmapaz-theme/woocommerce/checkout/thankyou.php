<?php
defined('ABSPATH') || exit;
?>
<div class="woocommerce-order">

    <?php if ($order):

        do_action('woocommerce_before_thankyou', $order->get_id());
    ?>

        <?php if ($order->has_status('failed')): ?>
            <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e('Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce'); ?></p>
            <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
                <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="button pay"><?php esc_html_e('Pay', 'woocommerce'); ?></a>
                <?php if (is_user_logged_in()): ?>
                    <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="button pay"><?php esc_html_e('My account', 'woocommerce'); ?></a>
                <?php endif; ?>
            </p>
        <?php else: ?>

            <?php wc_get_template('checkout/order-received.php', ['order' => $order]); ?>

            <ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

                <li class="woocommerce-order-overview__order order">
                    Número de pedido:
                    <strong><?php echo $order->get_order_number(); ?></strong>
                </li>

                <li class="woocommerce-order-overview__date date">
                    Fecha:
                    <strong><?php echo wc_format_datetime($order->get_date_created()); ?></strong>
                </li>

                <?php if (is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email()): ?>
                    <li class="woocommerce-order-overview__email email">
                        Correo electrónico:
                        <strong><?php echo $order->get_billing_email(); ?></strong>
                    </li>
                <?php endif; ?>

                <li class="woocommerce-order-overview__total total">
                    Total:
                    <strong><?php echo $order->get_formatted_order_total(); ?></strong>
                </li>

                <?php if ($order->get_payment_method_title()): ?>
                    <li class="woocommerce-order-overview__payment-method method">
                        Método de pago:
                        <strong><?php echo wp_kses_post($order->get_payment_method_title()); ?></strong>
                    </li>
                <?php endif; ?>

            </ul>

            <?php
            // Display custom checkout fields on thankyou page
            $custom_fields = [
                '_billing_cedula'            => 'Cédula',
                '_billing_phone_banco'       => 'Teléfono afiliado al banco',
                '_billing_numero_referencia' => 'Número de Referencia',
            ];
            ?>
            <div class="farmapaz-thankyou-custom-fields" style="margin-top: 16px;">
                <?php foreach ($custom_fields as $meta_key => $label):
                    $value = $order->get_meta($meta_key);
                    if ($value):
                ?>
                    <p><strong><?= $label; ?>:</strong> <?= esc_html($value); ?></p>
                <?php endif; endforeach; ?>

                <?php
                $capture = $order->get_meta('_billing_capture_pago');
                if ($capture):
                ?>
                    <p>
                        <strong>Comprobante de pago:</strong><br>
                        <a href="<?= esc_url($capture); ?>" target="_blank">
                            <img src="<?= esc_url($capture); ?>" alt="Comprobante de pago" style="max-width: 240px; border: 1px solid #ddd; border-radius: 8px; margin-top: 4px;">
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>

        <?php
        // Order details section
        woocommerce_order_details_table($order->get_id());
        ?>

        <?php do_action('woocommerce_thankyou', $order->get_id()); ?>

    <?php else: ?>
        <?php wc_get_template('checkout/order-received.php', ['order' => false]); ?>
    <?php endif; ?>

</div>
